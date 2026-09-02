import paramiko

host     = '66.42.61.106'
user     = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, username=user, password=password, timeout=10)
    cmd = """cd /var/www/ntti-attendance && php -r '
        require "vendor/autoload.php";
        $app = require_once "bootstrap/app.php";
        $kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
        $kernel->bootstrap();
        $token = App\\Models\\Setting::getValue("telegram_bot_token");
        $commands = [
            ["command" => "status",   "description" => "Check today check-in and check-out status"],
            ["command" => "history",  "description" => "View last 7 days attendance records"],
            ["command" => "week",     "description" => "View this week attendance summary"],
            ["command" => "monthly",  "description" => "View this month attendance summary"],
            ["command" => "leave",    "description" => "View your leave requests"],
            ["command" => "profile",  "description" => "View your teacher profile"],
            ["command" => "schedule", "description" => "View today class teaching schedule"],
            ["command" => "help",     "description" => "Display bot commands and help"]
        ];
        $res = Illuminate\\Support\\Facades\\Http::post("https://api.telegram.org/bot{$token}/setMyCommands", ["commands" => $commands]);
        echo $res->body();
    '"""
    stdin, stdout, stderr = ssh.exec_command(cmd)
    print("setMyCommands:", stdout.read().decode())
finally:
    ssh.close()
