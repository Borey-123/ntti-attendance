const fs = require('fs');

async function testUpload() {
  try {
    const loginRes = await fetch('http://66.42.61.106/login', { headers: { 'Accept': 'text/html' } });
    const html = await loginRes.text();
    const cookies = loginRes.headers.getSetCookie ? loginRes.headers.getSetCookie() : [loginRes.headers.get('set-cookie')];
    let cookieStr = cookies.map(c => c ? c.split(';')[0] : '').join('; ');
    const token = html.match(/name="_token" value="([^"]+)"/)[1];

    const body = new URLSearchParams();
    body.append('_token', token);
    body.append('email', 'admin@ntti.edu.kh');
    body.append('password', 'admin123');

    const postLogin = await fetch('http://66.42.61.106/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Cookie': cookieStr, 'Accept': 'text/html' },
      body: body.toString(),
      redirect: 'manual'
    });

    const sessionCookies = postLogin.headers.getSetCookie ? postLogin.headers.getSetCookie() : [postLogin.headers.get('set-cookie')];
    let authedCookie = sessionCookies.map(c => c ? c.split(';')[0] : '').filter(Boolean).join('; ');
    if (!authedCookie) authedCookie = cookieStr;

    const settingsRes = await fetch('http://66.42.61.106/settings', {
      headers: { 'Cookie': authedCookie, 'Accept': 'text/html' }
    });
    const settingsHtml = await settingsRes.text();
    const settingsTokenMatch = settingsHtml.match(/name="_token" value="([^"]+)"/);
    const settingsToken = settingsTokenMatch ? settingsTokenMatch[1] : token;

    const sqlPath = 'c:/xampp/htdocs/sana_project/Final_Project/NTTI_Teacher_Attendent/ntti-attendance/database_backup_clean.sql';
    const fileData = fs.readFileSync(sqlPath);
    const boundary = '----WebKitFormBoundary' + Math.random().toString(16).substring(2);

    let multipartBody = '';
    multipartBody += '--' + boundary + '\r\n';
    multipartBody += 'Content-Disposition: form-data; name="_token"\r\n\r\n' + settingsToken + '\r\n';
    multipartBody += '--' + boundary + '\r\n';
    multipartBody += 'Content-Disposition: form-data; name="db_file"; filename="database_backup_clean.sql"\r\n';
    multipartBody += 'Content-Type: application/sql\r\n\r\n';

    const payload = Buffer.concat([
      Buffer.from(multipartBody, 'utf8'),
      fileData,
      Buffer.from('\r\n--' + boundary + '--\r\n', 'utf8')
    ]);

    console.log('Uploading database_backup_clean.sql...');
    const importRes = await fetch('http://66.42.61.106/settings/database/import', {
      method: 'POST',
      headers: {
        'Content-Type': 'multipart/form-data; boundary=' + boundary,
        'Cookie': authedCookie,
        'Accept': 'application/json' // to get JSON error
      },
      body: payload
    });

    const responseText = await importRes.text();
    console.log('Status:', importRes.status);
    console.log('Response:', responseText.substring(0, 1000));
  } catch (err) {
    console.error('Upload error:', err);
  }
}

testUpload();