import paramiko

host     = '66.42.61.106'
user     = 'root'
password = 'r)E)3!R=q^A?~<9#'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    cmd = """cd /var/www/ntti-attendance && php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\\Contracts\\Console\\Kernel::class); \$kernel->bootstrap(); echo 'USERS:' . PHP_EOL; foreach(App\\Models\\User::all() as \$u) { echo 'User ID: '.\$u->id.' | Name: '.\$u->name.' | Email: '.\$u->email.' | TG: '.\$u->telegram_chat_id.' | 2FA: '.\$u->two_factor_enabled.PHP_EOL; } echo PHP_EOL . 'TEACHERS:' . PHP_EOL; foreach(App\\Models\\Teacher::whereNotNull('telegram_chat_id')->get() as \$t) { echo 'Teacher ID: '.\$t->id.' | Name: '.\$t->name.' | TG: '.\$t->telegram_chat_id.PHP_EOL; }" """
    stdin, stdout, stderr = ssh.exec_command(cmd)
    print(stdout.read().decode('utf-8', 'ignore'))
    print(stderr.read().decode('utf-8', 'ignore'))
finally:
    ssh.close()
