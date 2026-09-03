import paramiko

host = '66.42.61.106'
user = 'root'
password = 'Y2w_N7@MVhq_xn@K'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    print(f"Connecting to Vultr Server ({host})...")
    ssh.connect(host, username=user, password=password, timeout=15)
    
    cmd = 'cd /var/www/ntti-attendance && php -r "require \'vendor/autoload.php\'; \$app = require \'bootstrap/app.php\'; \$app->make(\'Illuminate\\Contracts\\Console\\Kernel\')->bootstrap(); try { \$req = Illuminate\\Http\\Request::create(\'/reports/pdf\', \'GET\'); \$res = (new App\\Http\\Controllers\\PdfReportController)->generate(\$req); echo \$res->render(); } catch (Throwable \$e) { echo \'EXCEPTION: \' . \$e->getMessage() . \' in \' . \$e->getFile() . \':\' . \$e->getLine(); }"'
    
    stdin, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='ignore')
    err = stderr.read().decode('utf-8', errors='ignore')
    
    print("OUTPUT:\n", out[:500])
    if err:
        print("STDERR:\n", err[:500])

except Exception as e:
    print(f"Error: {e}")
finally:
    ssh.close()
