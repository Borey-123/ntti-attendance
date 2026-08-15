<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted — School Network Only</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', 'Kantumruy Pro', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.5rem;
            max-width: 480px;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.12);
            border: 2px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
        }
        h1 { font-size: 1.35rem; font-weight: 800; color: #ffffff; margin-bottom: 0.5rem; }
        p { font-size: 0.925rem; color: #94a3b8; line-height: 1.6; margin-bottom: 1.5rem; }
        .ip-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.6rem 1.2rem;
            border-radius: 0.75rem;
            font-family: monospace;
            font-size: 0.875rem;
            color: #f59e0b;
            margin-bottom: 1.8rem;
        }
        .info-note {
            font-size: 0.8rem;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-box">
            <i class="ph ph-shield-warning"></i>
        </div>
        <h1>ការចូលប្រើប្រាស់ត្រូវបានកម្រិត / Access Restricted</h1>
        <p>ប្រព័ន្ធគ្រប់គ្រងវត្តមាននេះ ត្រូវបានកម្រិតឲ្យប្រើប្រាស់តែនៅលើបណ្តាញ Wi-Fi របស់សាលាតែប៉ុណ្ណោះ។<br>Admin system login is strictly permitted from the official School Wi-Fi network.</p>
        
        <div class="ip-badge">
            <i class="ph ph-globe"></i> Your IP: {{ $clientIp }}
        </div>

        <div class="info-note">
            National Technical Training Institute (NTTI) — Security Protocol
        </div>
    </div>
</body>
</html>
