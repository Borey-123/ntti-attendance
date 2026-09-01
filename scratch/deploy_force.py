import paramiko

host     = '66.42.61.106'
user     = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    print(f"Connecting to {host}...")
    ssh.connect(host, username=user, password=password, timeout=10)
    print("Connected.")

    commands = [
        "cd /var/www/ntti-attendance && git fetch origin main",
        "cd /var/www/ntti-attendance && git reset --hard origin/main",
        "cd /var/www/ntti-attendance && php artisan cache:clear",
        "cd /var/www/ntti-attendance && php artisan view:clear",
    ]

    for cmd in commands:
        print(f"\n>> {cmd}")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        out = stdout.read().decode().strip()
        err = stderr.read().decode().strip()
        if out: print(out)
        if err: print("[stderr]", err)

    print("\nDeploy complete!")

except Exception as e:
    print(f"Error: {e}")
finally:
    ssh.close()
