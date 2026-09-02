import paramiko

host     = '66.42.61.106'
user     = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    stdin, stdout, stderr = ssh.exec_command('grep -o "https://.*\.trycloudflare\.com" /var/log/cloudflared.log | tail -n 1')
    cf_url = stdout.read().decode().strip()
    print("Found HTTPS URL:", cf_url)
    
    if cf_url:
        webhook_url = f"{cf_url}/api/telegram/webhook"
        cmd = f"cd /var/www/ntti-attendance && php artisan telegram:set-webhook {webhook_url}"
        print(f">> {cmd}")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        print(stdout.read().decode())
        print(stderr.read().decode())
finally:
    ssh.close()
