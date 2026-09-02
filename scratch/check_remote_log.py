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
        "grep -n -C 5 -i 'ERROR' /var/www/ntti-attendance/storage/logs/laravel.log | tail -n 40"
    ]

    for cmd in commands:
        print(f"\n>> {cmd}")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        out = stdout.read().decode().strip()
        err = stderr.read().decode().strip()
        if out: print(out)
        if err: print("[stderr]", err)

except Exception as e:
    print(f"Error: {e}")
finally:
    ssh.close()
