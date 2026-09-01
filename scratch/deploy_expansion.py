import paramiko
import os

host = '66.42.61.106'
user = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

files_to_sync = [
    ('database/migrations/2026_09_01_000001_create_leave_requests_table.php', '/var/www/ntti-attendance/database/migrations/2026_09_01_000001_create_leave_requests_table.php'),
    ('database/migrations/2026_09_01_000002_create_teacher_schedules_table.php', '/var/www/ntti-attendance/database/migrations/2026_09_01_000002_create_teacher_schedules_table.php'),
    ('app/Models/LeaveRequest.php', '/var/www/ntti-attendance/app/Models/LeaveRequest.php'),
    ('app/Models/TeacherSchedule.php', '/var/www/ntti-attendance/app/Models/TeacherSchedule.php'),
    ('app/Http/Controllers/LeaveRequestController.php', '/var/www/ntti-attendance/app/Http/Controllers/LeaveRequestController.php'),
    ('app/Http/Controllers/ScheduleController.php', '/var/www/ntti-attendance/app/Http/Controllers/ScheduleController.php'),
    ('app/Http/Controllers/TelegramWebhookController.php', '/var/www/ntti-attendance/app/Http/Controllers/TelegramWebhookController.php'),
    ('app/Http/Controllers/AnalyticsController.php', '/var/www/ntti-attendance/app/Http/Controllers/AnalyticsController.php'),
    ('resources/views/leave/index.blade.php', '/var/www/ntti-attendance/resources/views/leave/index.blade.php'),
    ('resources/views/schedules/index.blade.php', '/var/www/ntti-attendance/resources/views/schedules/index.blade.php'),
    ('resources/views/analytics/index.blade.php', '/var/www/ntti-attendance/resources/views/analytics/index.blade.php'),
    ('resources/views/layouts/app.blade.php', '/var/www/ntti-attendance/resources/views/layouts/app.blade.php'),
    ('resources/views/portal/index.blade.php', '/var/www/ntti-attendance/resources/views/portal/index.blade.php'),
    ('routes/web.php', '/var/www/ntti-attendance/routes/web.php'),
    ('routes/api.php', '/var/www/ntti-attendance/routes/api.php'),
]

base_local = r'c:\xampp\htdocs\sana_project\Final_Project\NTTI_Teacher_Attendent\ntti-attendance'

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    sftp = ssh.open_sftp()
    
    # Ensure directories exist
    dirs_to_make = [
        '/var/www/ntti-attendance/resources/views/leave',
        '/var/www/ntti-attendance/resources/views/schedules',
        '/var/www/ntti-attendance/resources/views/analytics',
    ]
    for d in dirs_to_make:
        try:
            sftp.mkdir(d)
        except:
            pass

    for local_rel, remote_path in files_to_sync:
        local_full = os.path.join(base_local, local_rel)
        print(f"Uploading {local_rel} -> {remote_path}")
        sftp.put(local_full, remote_path)
        
    sftp.close()

    print("Running php artisan migrate & view:clear on Vultr...")
    stdin, stdout, stderr = ssh.exec_command('cd /var/www/ntti-attendance && php artisan migrate --force && php artisan view:clear && php artisan cache:clear')
    print("MIGRATE_OUTPUT:", stdout.read().decode('utf-8', errors='ignore'))
    print("MIGRATE_ERR:", stderr.read().decode('utf-8', errors='ignore'))

finally:
    ssh.close()
