<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Payroll;
use App\Models\Setting;
use Illuminate\Support\Str;

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
            : (!empty($teacher->phone) ? preg_replace('/[^0-9]/', '', $teacher->phone) . '@bakong' : 'teacher@ntti.edu.kh');

        $accountName = !empty($teacher->bank_account_name)
            ? self::cleanName($teacher->bank_account_name)
            : self::cleanName($teacher->name);

        $bankName = $teacher->bank_name ?: 'Bakong / Any Bank';
        $accountNumber = $teacher->bank_account_number ?: ($teacher->employee_id ?: 'N/A');

        // 2. Amount calculation
        $usdAmount = (float)$payroll->net_salary;
        $khrRate = (int)Setting::getValue('khr_exchange_rate', self::DEFAULT_KHR_RATE);
        $khrAmount = round($usdAmount * $khrRate, -2); // Round to hundreds

        $isKhr = strtoupper($currency) === 'KHR';
        $amount = $isKhr ? number_format($khrAmount, 0, '', '') : number_format($usdAmount, 2, '.', '');
        $currencyCode = $isKhr ? '116' : '840'; // 840 = USD, 116 = KHR

        // 3. Build Tag 29 (Individual Merchant Account Information)
        $tag29_sub00 = self::formatTlv('00', $bakongId);
        $tag29_sub01 = self::formatTlv('01', self::cleanName($teacher->name));
        $tag29Value  = $tag29_sub00 . $tag29_sub01;
        $tag29       = self::formatTlv('29', $tag29Value);

        // 4. Build Tag 62 (Additional Data Template)
        $billNo      = 'PAY-' . str_pad((string)$payroll->id, 6, '0', STR_PAD_LEFT);
        $tag62_sub01 = self::formatTlv('01', $billNo);
        $tag62_sub07 = self::formatTlv('07', 'NTTI');
        $tag62Value  = $tag62_sub01 . $tag62_sub07;
        $tag62       = self::formatTlv('62', $tag62Value);

        // 5. Assemble Payload (Tags 00 through 60)
        $payload = self::formatTlv('00', '01')                             // Payload Format Indicator
                 . self::formatTlv('01', '12')                             // Point of Initiation (12 = Dynamic with amount)
                 . $tag29                                                  // Merchant Account Information
                 . self::formatTlv('52', '8220')                           // Merchant Category Code (8220 = Educational Services)
                 . self::formatTlv('53', $currencyCode)                    // Currency
                 . self::formatTlv('54', $amount)                          // Amount
                 . self::formatTlv('58', 'KH')                             // Country Code
                 . self::formatTlv('59', $accountName)                     // Merchant / Beneficiary Name
                 . self::formatTlv('60', 'Phnom Penh')                     // Merchant City
                 . $tag62;                                                 // Additional Data

        // 6. Calculate & Append CRC16 (Tag 63)
        $toCrc = $payload . '6304';
        $crc = self::calculateCrc16($toCrc);
        $finalKhqr = $toCrc . $crc;

        // 7. Generate QR image URLs / embed data
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
