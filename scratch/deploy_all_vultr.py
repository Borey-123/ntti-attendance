import paramiko
import sys

host = '66.42.61.106'
user = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    print(f"Connecting to Vultr Server ({host})...")
    ssh.connect(host, username=user, password=password, timeout=15)
    print("Successfully connected via SSH.")

    cmds = [
        "cd /var/www/ntti-attendance && git pull origin main",
        "cd /var/www/ntti-attendance && php artisan migrate --force",
        "cd /var/www/ntti-attendance && php artisan view:clear && php artisan cache:clear && php artisan route:clear"
    ]

    for cmd in cmds:
        print(f"\n--- Executing: {cmd} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='ignore')
        err = stderr.read().decode('utf-8', errors='ignore')
        if out:
            print("Output:\n", out)
        if err:
            print("Stderr:\n", err)

    print("\n[SUCCESS] Deployment completed successfully!")

except Exception as e:
    print(f"[ERROR] Deployment error: {e}")
finally:
    ssh.close()
