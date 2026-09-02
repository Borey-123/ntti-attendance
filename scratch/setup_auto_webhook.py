import paramiko

host     = '66.42.61.106'
user     = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    
    remote_script = """#!/usr/bin/env python3
import time
import re
import subprocess
import urllib.request
import json

last_url = None

def get_latest_url():
    try:
        with open('/var/log/cloudflared.log', 'r') as f:
            content = f.read()
            matches = re.findall(r'https://[a-zA-Z0-9-]+\\.trycloudflare\\.com', content)
            if matches:
                return matches[-1]
    except Exception as e:
        print("Error reading log:", e, flush=True)
    return None

def set_webhook(cf_url):
    webhook_url = f"{cf_url}/api/telegram/webhook"
    print(f"Attempting to set webhook to: {webhook_url}", flush=True)
    cmd = ["php", "/var/www/ntti-attendance/artisan", "telegram:set-webhook", webhook_url]
    res = subprocess.run(cmd, capture_output=True, text=True)
    print("Artisan Output:", res.stdout, res.stderr, flush=True)
    return "successfully" in res.stdout or "ok" in res.stdout

print("Starting NTTI Telegram Auto Webhook Monitor...", flush=True)
while True:
    current_url = get_latest_url()
    if current_url and current_url != last_url:
        print(f"Detected new Cloudflare URL: {current_url}", flush=True)
        # Test if DNS resolves before setting
        for attempt in range(10):
            try:
                urllib.request.urlopen(current_url, timeout=5)
                print("DNS & HTTP reachable! Setting Telegram webhook...", flush=True)
                if set_webhook(current_url):
                    last_url = current_url
                    print(f"Successfully bound Webhook to {current_url}", flush=True)
                    break
            except Exception as ex:
                print(f"Waiting for DNS propagation ({attempt+1}/10)... ({ex})", flush=True)
                time.sleep(3)
    time.sleep(5)
"""

    cmd_write = f"""cat << 'EOF' > /usr/local/bin/auto-telegram-webhook.py
{remote_script}
EOF
chmod +x /usr/local/bin/auto-telegram-webhook.py
systemctl restart ntti-telegram-sync
"""
    ssh.exec_command(cmd_write)
    print("Updated auto-telegram-webhook.py with unbuffered logs.")
finally:
    ssh.close()
