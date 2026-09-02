import paramiko
import json

host     = '66.42.61.106'
user     = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    cmd = """cd /var/www/ntti-attendance && php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\\Contracts\\Console\\Kernel::class); \$kernel->bootstrap(); \$token = App\\Models\\Setting::getValue('telegram_bot_token'); echo file_get_contents('https://api.telegram.org/bot'.\$token.'/getWebhookInfo');" """
    stdin, stdout, stderr = ssh.exec_command(cmd)
    raw = stdout.read().decode('utf-8', errors='replace')
    print("Telegram getWebhookInfo Result:\n", raw)
finally:
    ssh.close()
