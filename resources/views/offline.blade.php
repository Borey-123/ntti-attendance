<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ស្ថានភាពបណ្ដាញ (Network Status & Offline) - NTTI Attendance</title>
    <link rel="manifest" href="/manifest.json">
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700;900&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --primary: #00d4a0;
            --primary-glow: rgba(0,212,160,0.25);
            --bg: #0b0f19;
            --card: #131929;
            --border: rgba(255,255,255,0.08);
            --text: #f1f5f9;
            --muted: #64748b;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Battambang', 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-image:
                radial-gradient(ellipse at 20% 30%, rgba(0,212,160,0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 70%, rgba(99,102,241,0.08) 0%, transparent 50%);
        }
        .offline-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1.75rem;
            padding: 2.5rem 2rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }
        .offline-icon {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2.5rem;
            transition: all 0.3s ease;
        }
        .offline-icon.offline {
            background: linear-gradient(135deg, rgba(239,68,68,0.2), rgba(239,68,68,0.05));
            border: 2px solid rgba(239,68,68,0.4);
            animation: pulse-danger 2s ease-in-out infinite;
        }
        .offline-icon.online {
            background: linear-gradient(135deg, rgba(0,212,160,0.2), rgba(0,212,160,0.05));
            border: 2px solid rgba(0,212,160,0.4);
        }
        @keyframes pulse-danger {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239,68,68,0.3); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(239,68,68,0); }
        }
        h1 { font-size: 1.6rem; font-weight: 900; margin-bottom: 0.5rem; }
        h1 span { color: var(--primary); }
        .subtitle { color: var(--muted); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.6; }

        .banner-notice {
            background: rgba(0,212,160,0.08);
            border: 1px solid rgba(0,212,160,0.25);
            border-radius: 1rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.82rem;
            color: #10b981;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .status-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 0.85rem 1rem;
            margin-bottom: 0.75rem;
            text-align: left;
        }
        .status-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            background: var(--danger);
        }
        .status-dot.ok { background: var(--primary); box-shadow: 0 0 8px var(--primary); }
        .status-label { font-size: 0.85rem; font-weight: 700; color: var(--text); flex: 1; }
        .status-value { font-size: 0.8rem; font-weight: 600; color: var(--muted); }
        .status-value.ok { color: var(--primary); }

        /* Queue Section */
        .queue-section {
            background: rgba(0,212,160,0.04);
            border: 1px solid rgba(0,212,160,0.18);
            border-radius: 0.85rem;
            padding: 1rem;
            margin-bottom: 1.25rem;
            text-align: left;
        }
        .queue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .queue-title {
            font-size: 0.8rem;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .queue-list {
            max-height: 120px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .queue-item {
            font-size: 0.78rem;
            padding: 0.4rem 0.6rem;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 0.4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: monospace;
        }
        .queue-empty {
            font-size: 0.8rem;
            color: var(--muted);
            text-align: center;
            padding: 0.5rem 0;
        }

        .btn-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.65rem;
            margin-bottom: 1rem;
        }
        .btn {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: var(--primary);
            color: #000;
            border: none;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-secondary {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: var(--text);
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.08); }
        .btn-full { grid-column: span 2; }

        .conn-status {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            display: inline-block;
        }
        .conn-status.online { background: rgba(0,212,160,0.1); border-color: rgba(0,212,160,0.3); color: var(--primary); }
        .footer-text { font-size: 0.75rem; color: var(--muted); margin-top: 1.25rem; }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="offline-icon offline" id="mainIcon">
            <i class="ph ph-wifi-slash" id="mainIconPh"></i>
        </div>

        <h1 id="pageTitle">គ្មាន<span>អ៊ីនធឺណិត</span></h1>
        <p class="subtitle" id="pageSubtitle">
            ការតភ្ជាប់អ៊ីនធឺណិតត្រូវបានរំខាន។<br>
            ប្រព័ន្ធ Offline Mode កំពុងរក្សាទុកទិន្នន័យស្កេនលើម៉ាស៊ីនបណ្ដោះអាសន្ន។
        </p>

        <div class="banner-notice" id="onlineNotice" style="display: none;">
            <i class="ph ph-check-circle" style="font-size: 1.25rem; flex-shrink: 0;"></i>
            <div>
                <strong>អ្នកកំពុង Online (Connected)</strong><br>
                អ្នកអាចមើលទំព័រនេះដើម្បីតេស្តមុខងារ Offline Queue ឬចុចត្រឡប់ទៅផ្ទាំងគ្រប់គ្រងវិញ។
            </div>
        </div>

        <div class="status-row">
            <span class="status-dot" id="netDot"></span>
            <span class="status-label">ស្ថានភាពអ៊ីនធឺណិត (Network Status)</span>
            <span class="status-value" id="netStatus">Checking...</span>
        </div>

        <div class="status-row">
            <span class="status-dot ok"></span>
            <span class="status-label">Local Storage (IndexedDB Cache)</span>
            <span class="status-value ok">Active</span>
        </div>

        <div class="queue-section">
            <div class="queue-header">
                <div class="queue-title">📋 Offline Attendance Queue (<span id="queueCount">0</span>)</div>
                <button type="button" onclick="addTestScan()" style="background:none; border:none; color:var(--primary); font-size:0.75rem; font-weight:700; cursor:pointer; text-decoration:underline;">
                    + បន្ថែម Test Record
                </button>
            </div>
            <div class="queue-list" id="queueList">
                <div class="queue-empty">គ្មានទិន្នន័យក្នុង queue ទេ</div>
            </div>
        </div>

        <div class="btn-group">
            <a href="/" class="btn btn-primary btn-full" id="btnDashboard">
                <i class="ph ph-house"></i> ត្រឡប់ទៅ Dashboard
            </a>
            <button class="btn btn-secondary" onclick="manualSyncQueue()" id="btnSync">
                <i class="ph ph-arrows-clockwise"></i> Sync Queue ទៅ Server
            </button>
            <button class="btn btn-secondary" onclick="clearQueue()">
                <i class="ph ph-trash"></i> សម្អាត Queue
            </button>
        </div>

        <div id="connIndicator" class="conn-status">⚡ កំពុងពិនិត្យបណ្ដាញ...</div>
        <p class="footer-text">NTTI Attendance System &copy; {{ date('Y') }} — PWA & Offline Support</p>
    </div>

    <script>
        let wasOffline = false;

        function openDB() {
            return new Promise((resolve, reject) => {
                const req = indexedDB.open('ntti-offline', 1);
                req.onupgradeneeded = e => {
                    const db = e.target.result;
                    if (!db.objectStoreNames.contains('attendance_queue')) {
                        db.createObjectStore('attendance_queue', { keyPath: 'id', autoIncrement: true });
                    }
                };
                req.onsuccess = e => resolve(e.target.result);
                req.onerror = e => reject(e.target.error);
            });
        }

        async function loadOfflineQueue() {
            try {
                const db = await openDB();
                const tx = db.transaction('attendance_queue', 'readonly');
                const store = tx.objectStore('attendance_queue');
                const req = store.getAll();
                req.onsuccess = () => {
                    const items = req.result || [];
                    document.getElementById('queueCount').textContent = items.length;
                    const list = document.getElementById('queueList');
                    if (items.length === 0) {
                        list.innerHTML = '<div class="queue-empty">គ្មានទិន្នន័យក្នុង queue ទេ</div>';
                    } else {
                        list.innerHTML = items.map(item => `
                            <div class="queue-item">
                                <span>📍 ${item.teacher_id || item.rfid_uid || 'Scan #' + item.id}</span>
                                <span style="color: var(--muted);">${new Date(item.scanned_at || Date.now()).toLocaleTimeString()}</span>
                            </div>
                        `).join('');
                    }
                };
            } catch (err) {
                console.warn('IndexedDB not accessible', err);
            }
        }

        async function addTestScan() {
            try {
                const db = await openDB();
                const tx = db.transaction('attendance_queue', 'readwrite');
                const store = tx.objectStore('attendance_queue');
                store.add({
                    teacher_id: 'T' + Math.floor(1000 + Math.random() * 9000),
                    rfid_uid: 'TEST_' + Date.now().toString().slice(-6),
                    type: 'rfid',
                    scanned_at: new Date().toISOString()
                });
                tx.oncomplete = () => loadOfflineQueue();
            } catch(e) {
                alert('Could not add item: ' + e.message);
            }
        }

        async function clearQueue() {
            if (!confirm('តើអ្នកពិតជាចង់សម្អាត Queue មែនទេ?')) return;
            try {
                const db = await openDB();
                const tx = db.transaction('attendance_queue', 'readwrite');
                tx.objectStore('attendance_queue').clear();
                tx.oncomplete = () => loadOfflineQueue();
            } catch(e) {
                alert('Clear error: ' + e.message);
            }
        }

        async function manualSyncQueue() {
            const btn = document.getElementById('btnSync');
            btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> កំពុង Sync...';
            btn.disabled = true;

            try {
                const db = await openDB();
                const tx = db.transaction('attendance_queue', 'readonly');
                const items = await new Promise(res => {
                    const req = tx.objectStore('attendance_queue').getAll();
                    req.onsuccess = () => res(req.result);
                });

                if (!items || items.length === 0) {
                    alert('Queue ទទេ — គ្មានទិន្នន័យត្រូវ Sync ទេ');
                    btn.innerHTML = '<i class="ph ph-arrows-clockwise"></i> Sync Queue ទៅ Server';
                    btn.disabled = false;
                    return;
                }

                const res = await fetch('/api/kiosk/sync-offline', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ records: items })
                });

                const data = await res.json();
                if (data.status === 'success') {
                    // Clear synced
                    const clearTx = db.transaction('attendance_queue', 'readwrite');
                    clearTx.objectStore('attendance_queue').clear();
                    clearTx.oncomplete = () => {
                        loadOfflineQueue();
                        alert(`Sync ជោគជ័យ! បញ្ចូលទិន្នន័យបានចំនួន ${data.synced_count || items.length} កំណត់ត្រា។`);
                    };
                } else {
                    alert('Sync failed: ' + (data.message || 'Server error'));
                }
            } catch (err) {
                alert('Cannot sync while offline. Error: ' + err.message);
            } finally {
                btn.innerHTML = '<i class="ph ph-arrows-clockwise"></i> Sync Queue ទៅ Server';
                btn.disabled = false;
            }
        }

        function updateNetworkStatus() {
            const isOnline = navigator.onLine;
            const dot = document.getElementById('netDot');
            const status = document.getElementById('netStatus');
            const indicator = document.getElementById('connIndicator');
            const mainIcon = document.getElementById('mainIcon');
            const mainIconPh = document.getElementById('mainIconPh');
            const title = document.getElementById('pageTitle');
            const subtitle = document.getElementById('pageSubtitle');
            const notice = document.getElementById('onlineNotice');

            if (isOnline) {
                dot.className = 'status-dot ok';
                status.textContent = 'Online ✓';
                status.className = 'status-value ok';
                mainIcon.className = 'offline-icon online';
                mainIconPh.className = 'ph ph-wifi-high';
                title.innerHTML = 'អ៊ីនធឺណិត<span>ដំណើរការធម្មតា</span>';
                subtitle.innerHTML = 'ឧបករណ៍របស់អ្នកបានភ្ជាប់អ៊ីនធឺណិតរួចរាល់ហើយ។';
                notice.style.display = 'flex';
                indicator.textContent = '✅ Connected (អ៊ីនធឺណិតដំណើរការ)';
                indicator.className = 'conn-status online';

                // If user was genuinely offline and came back, auto-sync and redirect
                if (wasOffline) {
                    indicator.textContent = '✅ អ៊ីនធឺណិតមកវិញហើយ! កំពុងត្រឡប់ទៅប្រព័ន្ធ...';
                    manualSyncQueue();
                    setTimeout(() => window.location.href = '/', 2000);
                }
            } else {
                wasOffline = true;
                dot.className = 'status-dot';
                status.textContent = 'Offline ❌';
                status.className = 'status-value';
                mainIcon.className = 'offline-icon offline';
                mainIconPh.className = 'ph ph-wifi-slash';
                title.innerHTML = 'គ្មាន<span>អ៊ីនធឺណិត</span>';
                subtitle.innerHTML = 'ការតភ្ជាប់អ៊ីនធឺណិតត្រូវបានរំខាន។ ប្រព័ន្ធកំពុងដំណើរការក្នុង Offline Mode។';
                notice.style.display = 'none';
                indicator.textContent = '❌ Offline (គ្មានការតភ្ជាប់)';
                indicator.className = 'conn-status';
            }
        }

        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);
        updateNetworkStatus();
        loadOfflineQueue();
    </script>
</body>
</html>
