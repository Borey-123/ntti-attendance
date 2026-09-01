import paramiko
import time
import re

host = '66.42.61.106'
user = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    
    service_file = """[Unit]
Description=Cloudflare Tunnel for NTTI Attendance
After=network.target

[Service]
ExecStart=/usr/local/bin/cloudflared tunnel --url http://127.0.0.1:80
Restart=always
RestartSec=5
StandardOutput=file:/var/log/cloudflared.log
StandardError=file:/var/log/cloudflared.log

[Install]
WantedBy=multi-user.target
"""
    
    cmd = f"""
    cat << 'EOF' > /etc/systemd/system/vultr-tunnel.service
{service_file}
EOF
    systemctl daemon-reload
    systemctl restart vultr-tunnel
    systemctl enable vultr-tunnel
    sleep 3
    grep -o 'https://.*\.trycloudflare\.com' /var/log/cloudflared.log | tail -n 1
    """
    
    stdin, stdout, stderr = ssh.exec_command(cmd)
    url = stdout.read().decode().strip()
    print("VULTR_TUNNEL_URL:", url)

finally:
    ssh.close()
