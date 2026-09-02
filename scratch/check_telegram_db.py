import paramiko

host     = '66.42.61.106'
user     = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    cmd = """cd /var/www/ntti-attendance && php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\\Contracts\\Console\\Kernel::class); \$kernel->bootstrap(); \$teachers = App\\Models\\Teacher::select('id', 'name', 'telegram_chat_id')->get(); foreach(\$teachers as \$t) { echo 'ID: '.\$t->id.' | Name: '.\$t->name.' | TelegramChatId: '.\$t->telegram_chat_id.PHP_EOL; }" """
    stdin, stdout, stderr = ssh.exec_command(cmd)
    print(stdout.read().decode())
    print(stderr.read().decode())
finally:
    ssh.close()
