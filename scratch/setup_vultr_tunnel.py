import paramiko
import time

host = '66.42.61.106'
user = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    print(f"Connecting to {host}...")
    ssh.connect(host, username=user, password=password, timeout=10)
    print("Connected.")
    
    cmd = """
    if ! command -v cloudflared &> /dev/null; then
        curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
        dpkg -i cloudflared.deb
        rm cloudflared.deb
    fi
    pkill -f cloudflared || true
    nohup cloudflared tunnel --url http://127.0.0.1:80 > /var/log/cloudflared.log 2>&1 &
    sleep 4
    grep -o 'https://.*\.trycloudflare\.com' /var/log/cloudflared.log | tail -n 1
    """
    
    stdin, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode().strip()
    err = stderr.read().decode().strip()
    
    print("Vultr HTTPS Tunnel URL:")
    print(out)
    if err:
        print("Log/Err:", err)

except Exception as e:
    print(f"Error: {e}")
finally:
    ssh.close()
