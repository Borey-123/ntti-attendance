import paramiko
import os

host = '66.42.61.106'
user = 'root'
password = 'Y2w_N7@MVhq_xn@K'

local_path = r'C:\xampp\htdocs\sana_project\Final_Project\NTTI_Teacher_Attendent\ntti-attendance\resources\views\dashboard.blade.php'
remote_path = '/var/www/ntti-attendance/resources/views/dashboard.blade.php'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    print(f"Connecting to {host}...")
    ssh.connect(host, username=user, password=password, timeout=10)
    print("Connected.")
    
    sftp = ssh.open_sftp()
    print(f"Uploading {local_path} to {remote_path}...")
    sftp.put(local_path, remote_path)
    sftp.close()
    
    print("Upload successful. Clearing Laravel view cache...")
    stdin, stdout, stderr = ssh.exec_command('cd /var/www/ntti-attendance && php artisan view:clear')
    print("Output:", stdout.read().decode())
    print("Error:", stderr.read().decode())
    
except Exception as e:
    print(f"Error: {e}")
finally:
    ssh.close()
    print("Deployment script finished.")
