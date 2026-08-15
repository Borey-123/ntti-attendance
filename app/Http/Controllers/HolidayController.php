<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HolidayController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'required|string|max:255',
            'name_kh' => 'nullable|string|max:255',
        ]);

        $holiday = Holiday::create($validated);
        return response()->json(['status' => 'success', 'holiday' => $holiday], 201);
    }

    public function destroy(Holiday $holiday): JsonResponse
    {
        $holiday->delete();
        return response()->json(['status' => 'success', 'message' => 'Holiday deleted.']);
    }

    public function autoFillCambodia(Request $request): JsonResponse
    {
        $year = (int)($request->input('year', now()->year));

        // Fixed-date holidays (same day every year)
        $fixed = [
            ['month' => 1,  'day' => 1,  'name' => 'International New Year Day',   'name_kh' => 'ទិវាចូលឆ្នាំសកល'],
            ['month' => 1,  'day' => 7,  'name' => 'Victory over Genocide Day',     'name_kh' => 'ទិវាជ័យជម្នះលើរបបប្រល័យពូជសាសន៍'],
            ['month' => 3,  'day' => 8,  'name' => "International Women's Day",     'name_kh' => 'ទិវានារីអន្តរជាតិ'],
            ['month' => 5,  'day' => 1,  'name' => 'International Labor Day',        'name_kh' => 'ទិវាពលកម្មអន្តរជាតិ'],
            ['month' => 5,  'day' => 14, 'name' => "King Norodom Sihamoni's Birthday", 'name_kh' => 'ព្រះរាជពិធីបុណ្យចម្រើនព្រះជន្ម ព្រះបាទសម្តេចព្រះបរមនាថ នរោត្តម សីហមុនី'],
            ['month' => 6,  'day' => 18, 'name' => "Queen Mother's Birthday",        'name_kh' => 'ព្រះរាជពិធីបុណ្យចម្រើនព្រះជន្ម ព្រះមហាក្សត្រី នរោត្តម មុនិនាថ សីហនុ'],
            ['month' => 9,  'day' => 24, 'name' => 'Constitution Day',               'name_kh' => 'ទិវាប្រកាសរដ្ឋធម្មនុញ្ញ'],
            ['month' => 10, 'day' => 15, 'name' => 'Commemoration Day of King Father','name_kh' => 'ទិវាប្រារព្ធពិធីគោរពព្រះវិញ្ញាណក្ខន្ធ ព្រះបរមរតនកោដ្ឋ'],
            ['month' => 10, 'day' => 29, 'name' => "King Norodom Sihamoni's Coronation Day", 'name_kh' => 'ព្រះរាជពិធីគ្រងព្រះបរមសិរីរាជសម្បត្តិ ព្រះបាទសម្តេចព្រះបរមនាថ នរោត្តម សីហមុនី'],
            ['month' => 11, 'day' => 9,  'name' => 'Independence Day',               'name_kh' => 'ទិវាបុណ្យឯករាជ្យជាតិ'],
            ['month' => 12, 'day' => 29, 'name' => 'Peace Day in Cambodia',          'name_kh' => 'ទិវាសន្តិភាពនៅកម្ពុជា'],
        ];

        // Lunar-based holidays — vary each year (add future years here when known)
        $lunar = [
            2026 => [
                ['dates' => ['04-14','04-15','04-16'], 'name' => 'Khmer New Year',          'name_kh' => 'ពិធីបុណ្យចូលឆ្នាំថ្មី ប្រពៃណីជាតិខ្មែរ'],
                ['dates' => ['05-24'],                  'name' => 'Visak Bochea Day',         'name_kh' => 'ពិធីបុណ្យវិសាខបូជា'],
                ['dates' => ['05-28'],                  'name' => 'Royal Ploughing Ceremony', 'name_kh' => 'ព្រះរាជពិធីច្រត់ព្រះនង្គ័ល'],
                ['dates' => ['10-10','10-11','10-12'],  'name' => 'Pchum Ben Festival',       'name_kh' => 'ពិធីបុណ្យភ្ជុំបិណ្ឌ'],
                ['dates' => ['11-23','11-24','11-25'],  'name' => 'Water Festival',            'name_kh' => 'ពិធីបុណ្យអុំទូក បណ្តែតប្រទីប និងសំពះព្រះខែ អកអំបុក'],
            ],
            2027 => [
                ['dates' => ['04-14','04-15','04-16'], 'name' => 'Khmer New Year',          'name_kh' => 'ពិធីបុណ្យចូលឆ្នាំថ្មី ប្រពៃណីជាតិខ្មែរ'],
                ['dates' => ['05-13'],                  'name' => 'Visak Bochea Day',         'name_kh' => 'ពិធីបុណ្យវិសាខបូជា'],
                ['dates' => ['05-12'],                  'name' => 'Royal Ploughing Ceremony', 'name_kh' => 'ព្រះរាជពិធីច្រត់ព្រះនង្គ័ល'],
                ['dates' => ['09-29','09-30','10-01'],  'name' => 'Pchum Ben Festival',       'name_kh' => 'ពិធីបុណ្យភ្ជុំបិណ្ឌ'],
                ['dates' => ['11-12','11-13','11-14'],  'name' => 'Water Festival',            'name_kh' => 'ពិធីបុណ្យអុំទូក បណ្តែតប្រទីប និងសំពះព្រះខែ អកអំបុក'],
            ],
        ];

        if (!isset($lunar[$year])) {
            return response()->json([
                'status'  => 'partial',
                'message' => "Fixed holidays for {$year} were added, but lunar-based holidays (Khmer New Year, Pchum Ben, Water Festival) are not yet defined for this year. Please add them manually.",
            ], 200);
        }

        $toInsert = [];

        // Build fixed-date entries
        foreach ($fixed as $h) {
            $toInsert[] = [
                'date'    => sprintf('%04d-%02d-%02d', $year, $h['month'], $h['day']),
                'name'    => $h['name'],
                'name_kh' => $h['name_kh'],
            ];
        }

        // Build lunar entries
        foreach ($lunar[$year] as $group) {
            foreach ($group['dates'] as $md) {
                $toInsert[] = [
                    'date'    => "{$year}-{$md}",
                    'name'    => $group['name'],
                    'name_kh' => $group['name_kh'],
                ];
            }
        }

        foreach ($toInsert as $h) {
            Holiday::firstOrCreate(['date' => $h['date']], $h);
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Cambodia public holidays for {$year} have been imported successfully.",
            'count'   => count($toInsert),
        ], 201);
    }
}
