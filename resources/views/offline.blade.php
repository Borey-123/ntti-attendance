<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>គ្មានអ៊ីនធឺណិត - NTTI Attendance</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700;900&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #00d4a0;
            --bg: #0b0f19;
            --card: #131929;
            --border: rgba(255,255,255,0.08);
            --text: #f1f5f9;
            --muted: #64748b;
            --danger: #ef4444;
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
            padding: 2rem;
            background-image:
                radial-gradient(ellipse at 20% 30%, rgba(0,212,160,0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 70%, rgba(99,102,241,0.06) 0%, transparent 50%);
        }
        .offline-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .offline-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, rgba(239,68,68,0.15), rgba(239,68,68,0.05));
            border: 2px solid rgba(239,68,68,0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239,68,68,0.3); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 12px rgba(239,68,68,0); }
        }
        h1 { font-size: 1.75rem; font-weight: 900; margin-bottom: 0.5rem; }
        h1 span { color: var(--primary); }
        .subtitle { color: var(--muted); font-size: 0.95rem; margin-bottom: 2rem; line-height: 1.6; }
        .status-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: left;
        }
        .status-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            background: var(--danger);
            animation: pulse 1.5s ease-in-out infinite;
        }
        .status-dot.ok { background: var(--primary); }
        .status-label { font-size: 0.85rem; font-weight: 700; color: var(--text); flex: 1; }
        .status-value { font-size: 0.8rem; color: var(--muted); }

        /* Offline Attendance Queue */
        .queue-section {
            background: rgba(0,212,160,0.05);
            border: 1px solid rgba(0,212,160,0.2);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .queue-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }
        .queue-item {
            font-size: 0.8rem;
            color: var(--muted);
            padding: 0.3rem 0;
            border-bottom: 1px solid var(--border);
        }
        .queue-item:last-child { border-bottom: none; }
        .queue-empty { font-size: 0.85rem; color: var(--muted); text-align: center; padding: 0.5rem; }

        .btn-group { display: flex; flex-direction: column; gap: 0.75rem; }
        .btn {
            padding: 0.875rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.95rem;
            font-family: inherit;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #00b894);
            color: #000;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-secondary {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: var(--text);
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.08); }

        .footer-text { font-size: 0.75rem; color: var(--muted); margin-top: 1.5rem; }
        .conn-status { font-size: 0.8rem; margin-top: 1rem; padding: 0.5rem 1rem; border-radius: 2rem; background: rgba(255,255,255,0.04); border: 1px solid var(--border); display: inline-block; }
        .conn-status.online { background: rgba(0,212,160,0.1); border-color: rgba(0,212,160,0.3); color: var(--primary); }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="offline-icon">📡</div>
        <h1>គ្មាន<span>អ៊ីនធឺណិត</span></h1>
        <p class="subtitle">
            ការតភ្ជាប់អ៊ីនធឺណិតរបស់អ្នកត្រូវបានរំខាន។<br>
            ប្រព័ន្ធ offline mode ត្រូវបានបើក — អ្នកអាចបន្តប្រើប្រាស់ portal ខ្លះ។
        </p>

        <div class="status-row">
            <span class="status-dot" id="netDot"></span>
            <span class="status-label">ស្ថានភាពអ៊ីនធឺណិត</span>
            <span class="status-value" id="netStatus">Offline</span>
        </div>
        <div class="status-row">
            <span class="status-dot ok"></span>
            <span class="status-label">Cached Data</span>
            <span class="status-value" id="cacheStatus">Available</span>
        </div>

        <div class="queue-section">
            <div class="queue-title">📋 Offline Queue (<span id="queueCount">0</span> items)</div>
            <div id="queueList"><div class="queue-empty">គ្មានទិន្នន័យក្នុង queue</div></div>
        </div>

        <div class="btn-group">
            <button class="btn btn-primary" onclick="tryReconnect()">
                🔄 ព្យាយាម Reconnect
            </button>
            <a href="/portal" class="btn btn-secondary">
                👤 Teacher Portal (Cached)
            </a>
            <button class="btn btn-secondary" onclick="clearQueue()">
                🗑️ Clear Queue
            </button>
        </div>

        <div id="connIndicator" class="conn-status">⚡ Checking connection...</div>
        <p class="footer-text">NTTI Attendance System &copy; {{ date('Y') }} — Offline Mode Active</p>
    </div>

    <script>
        let checkInterval;

        // Check offline queue from IndexedDB
        async function loadOfflineQueue() {
            try {
                const db = await openDB();
                const queue = await getAll(db, 'attendance_queue');
                const list = document.getElementById('queueList');
                const count = document.getElementById('queueCount');
                count.textContent = queue.length;
                if (queue.length === 0) {
                    list.innerHTML = '<div class="queue-empty">គ្មានទិន្នន័យក្នុង queue</div>';
                } else {
                    list.innerHTML = queue.map(item => `
                        <div class="queue-item">
                            📍 ${item.data?.rfid_uid || item.data?.teacher_id || 'Unknown'} 
                            — ${new Date(item.data?.scanned_at || Date.now()).toLocaleTimeString()}
                        </div>
                    `).join('');
                }
            } catch(e) {
                console.warn('IndexedDB not available');
            }
        }

        function openDB() {
            return new Promise((resolve, reject) => {
                const req = indexedDB.open('ntti-offline', 1);
                req.onupgradeneeded = e => {
                    if (!e.target.result.objectStoreNames.contains('attendance_queue'))
                        e.target.result.createObjectStore('attendance_queue', { keyPath: 'id', autoIncrement: true });
                };
                req.onsuccess = e => resolve(e.target.result);
                req.onerror = e => reject(e.target.error);
            });
        }

        function getAll(db, store) {
            return new Promise((resolve, reject) => {
                const req = db.transaction(store, 'readonly').objectStore(store).getAll();
                req.onsuccess = e => resolve(e.target.result);
                req.onerror = e => reject(e.target.error);
            });
        }

        async function clearQueue() {
            try {
                const db = await openDB();
                const tx = db.transaction('attendance_queue', 'readwrite');
                tx.objectStore('attendance_queue').clear();
                await loadOfflineQueue();
            } catch(e) { alert('Cannot clear queue: ' + e.message); }
        }

        function updateConnectionStatus() {
            const online = navigator.onLine;
            const dot = document.getElementById('netDot');
            const status = document.getElementById('netStatus');
            const indicator = document.getElementById('connIndicator');

            dot.className = 'status-dot' + (online ? ' ok' : '');
            status.textContent = online ? 'Online ✓' : 'Offline';
            indicator.textContent = online ? '✅ Connected! Redirecting...' : '❌ Still offline';
            indicator.className = 'conn-status' + (online ? ' online' : '');

            if (online) {
                setTimeout(() => window.location.href = '/', 1500);
            }
        }

        function tryReconnect() {
            updateConnectionStatus();
            if (!navigator.onLine) {
                document.getElementById('connIndicator').textContent = '❌ Still no connection. Please check your network.';
            }
        }

        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        updateConnectionStatus();
        loadOfflineQueue();
        checkInterval = setInterval(updateConnectionStatus, 5000);
    </script>
</body>
</html>
