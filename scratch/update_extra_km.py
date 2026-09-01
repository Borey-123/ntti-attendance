import json

km_file = r'c:\xampp\htdocs\sana_project\Final_Project\NTTI_Teacher_Attendent\ntti-attendance\lang\km.json'

with open(km_file, 'r', encoding='utf-8') as f:
    translations = json.load(f)

extra = {
    "Institutional Management": "ការគ្រប់គ្រងស្ថាប័ន",
    "Institutional Intelligence": "ការវិភាគឆ្លាតវៃស្ថាប័ន",
    "Total Requests": "សំណើសរុប",
    "Leave Applications List": "បញ្ជីពាក្យសុំច្បាប់",
    "Daily Institutional Check-In Density": "ដង់ស៊ីតេវត្តមានស្កែនប្រចាំថ្ងៃរបស់ស្ថាប័ន",
    "Punctual": "ទៀងទាត់",
    "Ranking by percentage of on-time check-ins": "ចំណាត់ថ្នាក់តាមភាគរយនៃវត្តមានទៀងទាត់",
    "Top teachers by total check-in count": "គ្រូបង្រៀនឆ្នើមតាមចំនួនស្កែនវត្តមានសរុប",
    "Teachers": "គ្រូបង្រៀន",
    "Total Scans": "ការស្កែនសរុប",
    "Less": "តិច",
    "More": "ច្រើន",
    "Filter": "តម្រង"
}

translations.update(extra)

with open(km_file, 'w', encoding='utf-8') as f:
    json.dump(translations, f, ensure_ascii=False, indent=4)

print("Updated km.json with extra keys:", len(extra))
