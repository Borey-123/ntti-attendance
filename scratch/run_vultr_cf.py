import paramiko
import re

host = '66.42.61.106'
user = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    stdin, stdout, stderr = ssh.exec_command('cloudflared tunnel --url http://127.0.0.1:80', get_pty=True)
    
    tunnel_url = None
    for _ in range(15):
        line = stdout.readline()
        if not line:
            break
        print(line.strip())
        match = re.search(r'https://[a-zA-Z0-9-]+\.trycloudflare\.com', line)
        if match:
            tunnel_url = match.group(0)
            break
            
    print("MATCHED_URL:", tunnel_url)
finally:
    ssh.close()
