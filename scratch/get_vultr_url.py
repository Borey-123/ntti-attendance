import paramiko

host = '66.42.61.106'
user = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    stdin, stdout, stderr = ssh.exec_command('grep -o "https://.*\.trycloudflare\.com" /var/log/cloudflared.log | tail -n 1')
    print("VULTR_HTTPS_URL:" + stdout.read().decode().strip())
finally:
    ssh.close()
