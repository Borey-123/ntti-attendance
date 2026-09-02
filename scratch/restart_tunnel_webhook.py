import paramiko
import time
import re

host     = '66.42.61.106'
user     = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    print("Restarting cloudflared tunnel on Vultr...")
    ssh.exec_command("pkill -9 cloudflared")
    time.sleep(2)
    
    ssh.exec_command("nohup cloudflared tunnel --url http://127.0.0.1:80 > /var/log/cloudflared.log 2>&1 &")
    time.sleep(5)
    
    stdin, stdout, stderr = ssh.exec_command("cat /var/log/cloudflared.log")
    log_content = stdout.read().decode('utf-8', errors='replace')
    
    matches = re.findall(r'https://[a-zA-Z0-9-]+\.trycloudflare\.com', log_content)
    if matches:
        cf_url = matches[-1]
        print("Fresh HTTPS URL:", cf_url)
        webhook_url = f"{cf_url}/api/telegram/webhook"
        cmd = f"cd /var/www/ntti-attendance && php artisan telegram:set-webhook {webhook_url}"
        print(f">> {cmd}")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        print(stdout.read().decode())
        print(stderr.read().decode())
    else:
        print("Could not find trycloudflare URL in log:\n", log_content)
finally:
    ssh.close()
