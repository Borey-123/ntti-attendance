import json

km_file = r'c:\xampp\htdocs\sana_project\Final_Project\NTTI_Teacher_Attendent\ntti-attendance\lang\km.json'

with open(km_file, 'r', encoding='utf-8') as f:
    translations = json.load(f)

new_translations = {
    "Teaching Timetables & Schedules": "កាលវិភាគបង្រៀន",
    "Manage class schedules, subject allocations, and teacher room assignments.": "គ្រប់គ្រងកាលវិភាគថ្នាក់ មុខវិជ្ជា និងការបែងចែកបន្ទប់បង្រៀន។",
    "Add Teaching Slot": "បន្ថែមម៉ោងបង្រៀន",
    "Total Weekly Classes": "ថ្នាក់បង្រៀនសរុបក្នុងមួយសប្តាហ៍",
    "Active Instructors": "គ្រូបង្រៀនសកម្ម",
    "Assigned Rooms": "បន្ទប់បង្រៀនដែលបានចាត់តាំង",
    "Slots": "ម៉ោង",
    "No classes scheduled": "គ្មានថ្នាក់បង្រៀនទេ",
    "Monday": "ច័ន្ទ",
    "Tuesday": "អង្គារ",
    "Wednesday": "ពុធ",
    "Thursday": "ព្រហស្បតិ៍",
    "Friday": "សុក្រ",
    "Saturday": "សៅរ៍",
    "Sunday": "អាទិត្យ",
    "Leave & Absence Requests": "សំណើសុំច្បាប់ និងអវត្តមាន",
    "Manage and approve teacher leave applications.": "គ្រប់គ្រង និងអនុម័តពាក្យសុំច្បាប់របស់គ្រូបង្រៀន។",
    "Apply Leave": "សុំច្បាប់",
    "Apply for Leave": "ដាក់ពាក្យសុំច្បាប់",
    "Leave Type": "ប្រភេទច្បាប់",
    "Sick Leave": "ច្បាប់ឈឺ",
    "Official Mission": "បេសកកម្មផ្លូវការ",
    "Annual Leave": "ច្បាប់ប្រចាំឆ្នាំ",
    "Personal Leave": "ច្បាប់ផ្ទាល់ខ្លួន",
    "Start Date": "ថ្ងៃចាប់ផ្ដើម",
    "End Date": "ថ្ងៃបញ្ចប់",
    "Reason / Notes": "មូលហេតុ / កំណត់សម្គាល់",
    "Reason for leave...": "បញ្ជាក់មូលហេតុនៃការសុំច្បាប់...",
    "Submit Leave Request": "ផ្ញើសំណើសុំច្បាប់",
    "Leave request submitted successfully.": "បានផ្ញើសំណើសុំច្បាប់ដោយជោគជ័យ។",
    "Approved": "បានអនុម័ត",
    "Rejected": "បានបដិសេធ",
    "Pending": "រង់ចាំការពិនិត្យ",
    "Approve": "អនុម័ត",
    "Reject": "បដិសេធ",
    "Attendance Analytics & Heatmaps": "ការវិភាគ និងម៉ាទ្រីសវត្តមាន",
    "Institutional punctuality rankings, department metrics, and monthly heatmaps.": "ចំណាត់ថ្នាក់ភាពទៀងទាត់ ម៉ាទ្រីសតាមដេប៉ាតឺម៉ង់ និងគំនូសតាងវត្តមានប្រចាំខែ។",
    "Monthly Check-In Activity Matrix": "ម៉ាទ្រីសសកម្មភាពវត្តមានប្រចាំខែ",
    "Department Punctuality Ranking": "ចំណាត់ថ្នាក់ភាពទៀងទាត់តាមដេប៉ាតឺម៉ង់",
    "Most Active Teachers": "គ្រូបង្រៀនដែលមានវត្តមានច្រើនជាងគេ",
    "Punctuality Rate": "អត្រាភាពទៀងទាត់",
    "Check your attendance records instantly.": "ពិនិត្យមើលកំណត់ត្រាវត្តមានរបស់អ្នកភ្លាមៗ",
    "Today's Status": "ស្ថានភាពថ្ងៃនេះ",
    "Checked in at": "បានស្កែនចូលម៉ោង",
    "Checked out at": "បានស្កែនចេញម៉ោង",
    "Not checked in yet": "មិនទាន់បានស្កែននៅឡើយទេ",
    "On Duty": "កំពុងបំពេញភារកិច្ច",
    "Rate": "អត្រាវត្តមាន",
    "My QR Code": "កូដ QR របស់ខ្ញុំ",
    "Update Face ID": "ធ្វើបច្ចុប្បន្នភាពផ្ទៃមុខ",
    "Register Face ID": "ចុះឈ្មោះផ្ទៃមុខ",
    "Change PIN": "ផ្លាស់ប្តូរលេខកូដ PIN",
    "Logout": "ចាកចេញ",
    "Teacher Portal": "ច្រកទ្វារគ្រូបង្រៀន",
    "Save Slot": "រក្សាទុកម៉ោងបង្រៀន",
    "Subject / Class Name": "ឈ្មោះមុខវិជ្ជា / ថ្នាក់",
    "Room Number": "លេខបន្ទប់",
    "Start Time": "ម៉ោងចាប់ផ្ដើម",
    "End Time": "ម៉ោងបញ្ចប់"
}

translations.update(new_translations)

with open(km_file, 'w', encoding='utf-8') as f:
    json.dump(translations, f, ensure_ascii=False, indent=4)

print("Updated km.json successfully with", len(new_translations), "new translations.")
