import paramiko

host     = '66.42.61.106'
user     = 'root'
password = 'r)E)3!R=q^A?~<9#'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    cmd = """cd /var/www/ntti-attendance && php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\\Contracts\\Console\\Kernel::class); \$kernel->bootstrap(); \$u = App\\Models\\User::find(4); if(\$u){ \$u->update(['telegram_chat_id'=>'8921362445']); echo 'SUCCESS: Admin User 4 (Borey) linked to Telegram 8921362445' . PHP_EOL; }" """
    stdin, stdout, stderr = ssh.exec_command(cmd)
    print(stdout.read().decode('utf-8', 'ignore'))
    print(stderr.read().decode('utf-8', 'ignore'))
finally:
    ssh.close()
