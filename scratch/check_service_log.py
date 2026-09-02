import paramiko
import sys

sys.stdout.reconfigure(encoding='utf-8')

host     = '66.42.61.106'
user     = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    stdin, stdout, stderr = ssh.exec_command('journalctl -u ntti-telegram-sync -o cat --no-pager | tail -n 30')
    print(stdout.read().decode('utf-8', errors='replace'))
finally:
    ssh.close()
