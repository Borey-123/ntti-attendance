import paramiko
import os

host = '66.42.61.106'
user = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

files_to_sync = [
    ('app/Http/Controllers/AnalyticsController.php', '/var/www/ntti-attendance/app/Http/Controllers/AnalyticsController.php'),
    ('resources/views/analytics/index.blade.php', '/var/www/ntti-attendance/resources/views/analytics/index.blade.php'),
    ('resources/views/schedules/index.blade.php', '/var/www/ntti-attendance/resources/views/schedules/index.blade.php'),
    ('resources/views/portal/index.blade.php', '/var/www/ntti-attendance/resources/views/portal/index.blade.php'),
    ('resources/views/leave/index.blade.php', '/var/www/ntti-attendance/resources/views/leave/index.blade.php'),
    ('lang/km.json', '/var/www/ntti-attendance/lang/km.json'),
]

base_local = r'c:\xampp\htdocs\sana_project\Final_Project\NTTI_Teacher_Attendent\ntti-attendance'

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    sftp = ssh.open_sftp()

    for local_rel, remote_path in files_to_sync:
        local_full = os.path.join(base_local, local_rel)
        print(f"Uploading {local_rel} -> {remote_path}")
        sftp.put(local_full, remote_path)
        
    sftp.close()

    print("Clearing view & cache on Vultr...")
    stdin, stdout, stderr = ssh.exec_command('cd /var/www/ntti-attendance && php artisan view:clear && php artisan cache:clear')
    print("OUTPUT:", stdout.read().decode('utf-8', errors='ignore'))
    print("ERR:", stderr.read().decode('utf-8', errors='ignore'))

finally:
    ssh.close()
