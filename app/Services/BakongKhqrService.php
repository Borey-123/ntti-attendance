<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Payroll;
use App\Models\Setting;
use Illuminate\Support\Str;
use KHQR\BakongKHQR;
use KHQR\Models\IndividualInfo;
use KHQR\Helpers\KHQRData;

class BakongKhqrService
{
    /**
     * Standard Exchange Rate USD to KHR (fallback if not configured).
     */
    public const DEFAULT_KHR_RATE = 4100;

    /**
     * Format an EMVCo Tag-Length-Value string.
     */
    public static function formatTlv(string $tag, string $value): string
    {
        $length = str_pad((string)strlen($value), 2, '0', STR_PAD_LEFT);
        return $tag . $length . $value;
    }

    /**
     * Calculate 16-bit CRC (CCITT polynomial 0x1021, init 0xFFFF).
     */
    public static function calculateCrc16(string $data): string
    {
        $crc = 0xFFFF;
        $len = strlen($data);

        for ($i = 0; $i < $len; $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Clean a name string for EMV compliance (Latin characters and numbers, uppercase, max 25 chars).
     */
    public static function cleanName(string $name): string
    {
        // Strip out non-ascii or Khmer characters for EMV Tag 59 compliance
        $clean = preg_replace('/[^A-Za-z0-9\s\.\-]/', '', $name);
        $clean = strtoupper(trim(preg_replace('/\s+/', ' ', $clean)));
        if (empty($clean)) {
            $clean = 'NTTI INSTRUCTOR';
        }
        return substr($clean, 0, 25);
    }

    /**
     * Generate Bakong Individual KHQR Payload string for a Payroll slip.
     */
    public static function generatePayrollKhqr(Payroll $payroll, string $currency = 'USD'): array
    {
        $teacher = $payroll->teacher;
        
        // 1. Determine Bakong ID & Account Info
        $bakongId = !empty($teacher->bakong_account_id)
            ? trim($teacher->bakong_account_id)
            : (!empty($teacher->phone) ? preg_replace('/[^0-9]/', '', $teacher->phone) . '@bakong' : 'teacher@bakong');

        $accountName = !empty($teacher->bank_account_name)
            ? self::cleanName($teacher->bank_account_name)
            : self::cleanName($teacher->name);

        $bankName = $teacher->bank_name ?: 'Bakong / Any Bank';
        $accountNumber = $teacher->bank_account_number ?: ($teacher->employee_id ?: 'N/A');

        // 2. Amount calculation
        $usdAmount = (float)$payroll->net_salary;
        $khrRate = (int)Setting::getValue('khr_exchange_rate', self::DEFAULT_KHR_RATE);
        $khrAmount = (float)round($usdAmount * $khrRate, -2); // Round to hundreds

        $isKhr = strtoupper($currency) === 'KHR';
        $amountFloat = $isKhr ? $khrAmount : round($usdAmount, 2);
        $khqrCurrency = $isKhr ? KHQRData::CURRENCY_KHR : KHQRData::CURRENCY_USD;
        $billNo = 'PAY-' . str_pad((string)$payroll->id, 6, '0', STR_PAD_LEFT);

        $finalKhqr = null;

        // 3. Attempt Generation using Official NBC-compliant BakongKHQR Library
        try {
            $individualInfo = new IndividualInfo(
                bakongAccountID: $bakongId,
                merchantName: $accountName,
                merchantCity: 'Phnom Penh',
                currency: $khqrCurrency,
                amount: $amountFloat,
                billNumber: $billNo,
                terminalLabel: 'NTTI'
            );

            $khqrResponse = BakongKHQR::generateIndividual($individualInfo);
            if (!empty($khqrResponse->data['qr'])) {
                $finalKhqr = $khqrResponse->data['qr'];
            }
        } catch (\Throwable $e) {
            // Fallback to manual standard EMV assembly below
        }

        // 4. Fallback Manual EMV Generation (Exact NBC Specification including Tag 99)
        if (empty($finalKhqr)) {
            $currencyCode = $isKhr ? '116' : '840';
            $amountStr = $isKhr ? (string)round($khrAmount) : (string)round($usdAmount, 2);

            // Tag 29 (Individual Merchant Account Information) -> ONLY Subtag 00
            $tag29_sub00 = self::formatTlv('00', $bakongId);
            $tag29       = self::formatTlv('29', $tag29_sub00);

            // Tag 62 (Additional Data Template)
            $tag62_sub01 = self::formatTlv('01', $billNo);
            $tag62_sub07 = self::formatTlv('07', 'NTTI');
            $tag62       = self::formatTlv('62', $tag62_sub01 . $tag62_sub07);

            // Tag 99 (Timestamp millisecond - required by NBC to prevent expiry errors)
            $timestampMs = (string)floor(microtime(true) * 1000);
            $tag99       = self::formatTlv('99', self::formatTlv('00', $timestampMs));

            $payload = self::formatTlv('00', '01')                   // Tag 00: Format Indicator
                     . self::formatTlv('01', '12')                   // Tag 01: Dynamic with Amount
                     . $tag29                                        // Tag 29: Bakong Account
                     . self::formatTlv('52', '5999')                 // Tag 52: MCC 5999 (General)
                     . self::formatTlv('53', $currencyCode)          // Tag 53: Currency
                     . self::formatTlv('54', $amountStr)             // Tag 54: Amount
                     . self::formatTlv('58', 'KH')                   // Tag 58: Country
                     . self::formatTlv('59', $accountName)           // Tag 59: Merchant Name
                     . self::formatTlv('60', 'Phnom Penh')           // Tag 60: City
                     . $tag62                                        // Tag 62: Bill & Terminal
                     . $tag99;                                       // Tag 99: Current Timestamp

            $toCrc = $payload . '6304';
            $crc = self::calculateCrc16($toCrc);
            $finalKhqr = $toCrc . $crc;
        }

        // 5. Generate QR image URLs / embed data
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($finalKhqr);

        return [
            'khqr_string'    => $finalKhqr,
            'qr_image_url'   => $qrImageUrl,
            'bakong_id'      => $bakongId,
            'account_name'   => $accountName,
            'bank_name'      => $bankName,
            'account_number' => $accountNumber,
            'currency'       => $isKhr ? 'KHR' : 'USD',
            'amount_usd'     => $usdAmount,
            'amount_khr'     => $khrAmount,
            'khr_rate'       => $khrRate,
            'bill_number'    => $billNo,
        ];
    }
}
