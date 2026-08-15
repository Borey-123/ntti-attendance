<?php
$allStrings = <<<EOD
7-Day Attendance Trend
Absent
Action
Action Details
Actions
Active
Active Cards
Active HODs
Active Working Days
Add Department
Add new personnel
Add Teacher
Adjust
Adjust Photo
Adjust Record
Adjustments
Admin
Admin intervention and manual attendance override
Administrator
Afternoon
Afternoon Check-In
Afternoon Check-Out
Afternoon Late Cutoff
Afternoon Shift
Afternoon Status
All
All administrative actions are recorded with timestamps, admin identity, and originating IP to ensure full institutional accountability.
All Departments
All rights reserved.
All Teachers
Assign Hardware ID
Assign RFID
Attendance %
Attendance Database Backup
Attendance List
Attendance Rate
Attendance Reports
Attendance Rules
Attendance Volatility & Trend
Audit Log Details
Audit Trail
AUTHENTICATE
Authenticate & Scan
Authorized Terminal IP
Awaiting Scan...
Awaiting Scanned UID...
Brand Logo
Cache
Cancel
Check Another ID
Check In Time
Check your attendance records instantly.
Checking...
Check-ins Today
Choose teacher...
Clear Cache
Click "Run Integrity Check" to scan the database.
Click to re-adjust
Click to view staff list
Close
Commit Update
Configure core system parameters, security protocols, and visual branding.
Configure how your institution appears across the platform.
Confirm
Connected
Connecting...
Controls
Create
Credential UID
Crop & Apply
Currently In
Currently Out
Daily Records
Daily Report
Dark Mode
Dashboard
Database
Date
Days
Days Absent
Days Late
Days Present
Default Interface Theme
Define shifts, working days, and late thresholds.
Delete Actions
Department
Department Control Center
Department Head
Department Management
Department Name
Department Registry
Department Staff
Departments
Description
Detecting Shift...
Device Status
Device status & config
Disabled
Download a complete record of all attendance data in XLS format.
Duplicate UID Detected
Duration
e.g. Device failure, Forgot card
e.g. T0005
Edit
Edit Teacher
Email
Email Address
Employee ID
Enroll New Administrator
Error:
Excel
Export Date
Export XLS
Filter
Filter Department
Full administrative audit trail with system health monitoring.
Full Name
Full Name (English)
Full Name (Khmer)
Global Icon Weight
Greetings, Admin
Hardware Interface
Hardware Offline
Holder
How long the check-in/out popup stays visible on the Live Monitor and Dashboard.
Inactive
Initialing system
Initiate Scan
Institution Name
Instructor
Integrity Report
Interface Scaling (Font Size)
IP Address
Just now
Last checked
Last Scan
Late
Leave blank to allow any IP address.
Leave blank to keep existing
Light Mode
LIVE
Live Attendance Monitor
Live Monitor
Loading date...
Loading...
Login Events
Login Screen Wallpaper
Logs cannot be edited or deleted by administrators.
Maintenance Mode
Manage administrator access and credentials.
Manage Departments
Manage organizational hierarchy and leadership.
Manage personnel, credentials, and profile identities.
Manage RFID Cards
Manage secure hardware credentials and card assignments.
Manage smart cards
Manual Attendance Adjustment
Manual Terminal
Monthly Performance
Monthly Summary
Morning
Morning Check-In
Morning Check-Out
Morning Late Cutoff
Morning Shift
Morning Status
New Password
No activity detected.
No audit logs recorded yet. System is clean.
No data found.
No data yet
No departments found.
No Email
No Phone
No recent records
No recent scans.
No records found for this period.
No records found in last 30 days.
No staff assigned to this department.
No teachers found for today.
No teachers found in this selection.
None
OFFLINE
OK
On-Time
Open Live Monitor
Operational
Password
PDF
Pending Cards
Period
Personalize the visual identity and interface scaling.
Phone
Please tap your card to scan
PORTAL
Position / Title
Present
Primary Brand Color
Profile Photo
PROXIMITY SCANNING...
Punctuality
Quick Actions
Rate
Ready to assign
READY TO AUTHENTICATE
READY TO SCAN
Real-time updates enabled
Reason / Note
Re-assigning secure card for
Recent 30 Days
Recent Records
Recent Scans
Recommended: PNG with transparency, 512x512px.
recorded
Register & Assign
Register New Teacher
Register Teacher
Remaining
Remove current photo
Report Period
Reports
Reset
RFID Assigned
RFID Cards
RFID Control Center
RFID Management
RFID Teachers
ROOT ADMIN
Run Integrity Check
Save
Save Adjustment
Save Attendance Rules
Save Branding
Save Identity
Save Security Settings
Save Teacher
Scan Alert Duration (Seconds)
Scan Attendance
Scan New UID
Scan Station
Scanner Health Status
Scanner Offline
Scanner Online
Scanner restrictions and operating schedules.
Score
Search name, ID or position...
Search teacher name, Teacher ID or department...
Search teacher...
Search...
Secure Registry
Security & Audit Center
Security & Audit Logs
Security & Hardware
Security Level
Security Logs
Security Password
Security Policy
Select a profile to begin
Select department...
Select Personnel
Settings
Settings Hub
Shift End
Shift Start
Showing page
Sign in to your administrator account
Sign Out
Source
Status
Stay signed in
Storage
System
SYSTEM ADMIN
System Appearance
System Close
System Health
System Identity
System Open
System Operating Window
System Settings
System Status
Target
Target Department
Target Entity
Target Personnel
Teacher
Teacher Attendance Official Report
Teacher Directory
Teacher ID
Teacher Name
Teacher Portal
Teacher Total Hours / Month
Teachers
Team Management
Temporarily disable all RFID scanner inputs.
TERMINAL ONLINE
This is an electronically generated document. No signature is required.
Times
Timestamp
Tip: Verify your ID in the teacher list.
to
Today Total
Toggle Theme
Total Absent
Total Departments
total entries
Total Hours / Month
Total Issued
Total Late
Total Logs
Total Present
Total Staff
Total Teachers
Try adjusting your filters or register a new teacher.
UID Available
Unassigned
Update
Update Credential
Update Teacher
View and export
View My Attendance
View Staff
Welcome back
Working days
Working Hours
Writable
EOD;

$allStringsArr = explode("\n", trim($allStrings));
$kmFile = file_get_contents('lang/km.json');
$kmData = json_decode($kmFile, true) ?: [];

$missing = [];
foreach($allStringsArr as $str) {
    if (!isset($kmData[$str])) {
        $missing[] = $str;
    }
}

file_put_contents('scratch/missing_translations.txt', implode("\n", $missing));
echo "Missing count: " . count($missing);
?>
