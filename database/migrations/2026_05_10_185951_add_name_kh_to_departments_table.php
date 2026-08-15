<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('name_kh')->nullable()->after('name');
        });

        // Pre-fill existing departments based on current translations
        $translations = [
            "Administration and Finance Office" => "ការិយាល័យរដ្ឋបាល និងហិរញ្ញវត្ថុ",
            "Curriculum Development Office" => "ការិយាល័យអភិវឌ្ឍកម្មវិធីសិក្សា",
            "Department Civil" => "ដេប៉ាតឺម៉ង់សំណង់ស៊ីវិល",
            "Department of Education SciencesTechnical and vocational training" => "ដេប៉ាតឺម៉ង់វិទ្យាសាស្ត្រអប់រំ បច្ចេកទេស និងបណ្ដុះបណ្ដាលវិជ្ជាជីវៈ",
            "Department of Electrical and Electronic" => "ដេប៉ាតឺម៉ង់អគ្គិសនី និងអេឡិចត្រូនិច",
            "Department of IT" => "ដេប៉ាតឺម៉ង់ព័ត៌មានវិទ្យា",
            "Department of Research and Statistic Planning" => "ដេប៉ាតឺម៉ង់ស្រាវជ្រាវ ស្ថិតិ និងផែនការ",
            "Human Resource Development and International Relations Office" => "ការិយាល័យអភិវឌ្ឍធនធានមនុស្ស និងទំនាក់ទំនងអន្តរជាតិ",
            "Learning Resources Development Office" => "ការិយាល័យអភិវឌ្ឍធនធានសិក្សា",
            "Technical" => "បច្ចេកទេស",
            "Information Technology" => "ព័ត៌មានវិទ្យា",
            "Electronics" => "អេឡិចត្រូនិច",
            "Electricity" => "អគ្គិសនី",
            "Civil Engineering" => "វិស្វកម្មសំណង់ស៊ីវិល",
            "Architecture" => "ស្ថាបត្យកម្ម",
            "Mechanical Engineering" => "វិស្វកម្មមេកានិក",
            "Automotive" => "យានយន្ត",
            "Air Conditioning" => "បរិក្ខារត្រជាក់",
            "General Science" => "វិទ្យាសាស្ត្រទូទៅ",
            "English" => "ភាសាអង់គ្លេស",
            "Japanese" => "ភាសាជប៉ុន",
            "Korean" => "ភាសាកូរ៉េ",
            "French" => "ភាសាបារាំង",
            "Business" => "ពាណិជ្ជកម្ម",
            "Hospitality" => "បដិសណ្ឋារកិច្ច",
            "General Knowledge" => "ចំណេះដឹងទូទៅ",
            "Social Science" => "វិទ្យាសាស្ត្រសង្គម",
            "Internal Combustion Engine" => "ម៉ាស៊ីនឆេះក្នុង",
            "Auto Mechanics" => "មេកានិកឡាន",
            "Welding" => "ផ្សារ",
            "Plumbing" => "បរិក្ខារទឹក",
            "Computer Science" => "វិទ្យាសាស្ត្រកុំព្យូទ័រ",
            "Tourism" => "ទេសចរណ៍"
        ];

        foreach ($translations as $en => $km) {
            DB::table('departments')
                ->where('name', $en)
                ->update(['name_kh' => $km]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('name_kh');
        });
    }
};
