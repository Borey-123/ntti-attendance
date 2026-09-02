import paramiko

host     = '66.42.61.106'
user     = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    
    # 1. Pull latest code
    cmd_git = "cd /var/www/ntti-attendance && git fetch origin main && git reset --hard origin/main && php artisan cache:clear"
    print(">> Pulling latest code on Vultr...")
    stdin, stdout, stderr = ssh.exec_command(cmd_git)
    print(stdout.read().decode())
    
    # 2. Configure systemd service for Long Polling
    service_content = """[Unit]
Description=NTTI Attendance Telegram Long Polling Worker
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/var/www/ntti-attendance
ExecStart=/usr/bin/php /var/www/ntti-attendance/artisan telegram:poll
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
"""

    cmd_svc = f"""cat << 'EOF' > /etc/systemd/system/ntti-telegram-sync.service
{service_content}
EOF
systemctl daemon-reload
systemctl restart ntti-telegram-sync
systemctl enable ntti-telegram-sync
"""
    print(">> Updating systemd service for telegram:poll...")
    stdin, stdout, stderr = ssh.exec_command(cmd_svc)
    print(stdout.read().decode())
    print(stderr.read().decode())
    print("Long polling worker deployed successfully!")
finally:
    ssh.close()
