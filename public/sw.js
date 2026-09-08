// NTTI Attendance - Service Worker v3.0 (Enhanced PWA)
const CACHE_VERSION = 'v3';
const STATIC_CACHE = `ntti-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `ntti-dynamic-${CACHE_VERSION}`;
const API_CACHE = `ntti-api-${CACHE_VERSION}`;

const STATIC_ASSETS = [
  '/',
  '/offline',
  '/manifest.json',
  '/css/style.css',
];

const NEVER_CACHE = [
  '/api/kiosk/scan',
  '/api-web/attendance',
  '/logout',
  '/login',
];

// ──────────────────────────────────────────────────────────
// INSTALL: Pre-cache static assets
// ──────────────────────────────────────────────────────────
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(async cache => {
        for (const url of STATIC_ASSETS) {
          try {
            await cache.add(url);
          } catch (e) {
            console.warn('[SW] Could not pre-cache:', url, e.message);
          }
        }
      })
      .then(() => self.skipWaiting())
  );
});

// ──────────────────────────────────────────────────────────
// ACTIVATE: Clean up old caches
// ──────────────────────────────────────────────────────────
self.addEventListener('activate', event => {
  const currentCaches = [STATIC_CACHE, DYNAMIC_CACHE, API_CACHE];
  event.waitUntil(
    caches.keys()
      .then(cacheNames => Promise.all(
        cacheNames
          .filter(name => !currentCaches.includes(name))
          .map(name => caches.delete(name))
      ))
      .then(() => self.clients.claim())
  );
});

// ──────────────────────────────────────────────────────────
// FETCH: Stale-While-Revalidate strategy
// ──────────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET, cross-origin, and never-cache URLs
  if (request.method !== 'GET') return;
  if (!url.origin.includes(self.location.origin)) return;
  if (NEVER_CACHE.some(path => url.pathname.startsWith(path))) return;

  // Navigation requests: network-first with offline fallback
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then(response => {
          if (response.ok) {
            const clone = response.clone();
            caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
          }
          return response;
        })
        .catch(() => 
          caches.match(request)
            .then(cached => cached || caches.match('/offline') || caches.match('/'))
        )
    );
    return;
  }

  // Static assets: cache-first
  if (isStaticAsset(url.pathname)) {
    event.respondWith(
      caches.match(request)
        .then(cached => {
          const fetchPromise = fetch(request).then(response => {
            if (response.ok) {
              caches.open(STATIC_CACHE).then(cache => cache.put(request, response.clone()));
            }
            return response;
          });
          return cached || fetchPromise;
        })
    );
    return;
  }

  // API/data requests: network-first with short cache
  if (url.pathname.startsWith('/api-web/')) {
    event.respondWith(
      fetch(request)
        .then(response => {
          if (response.ok) {
            caches.open(API_CACHE).then(cache => cache.put(request, response.clone()));
          }
          return response;
        })
        .catch(() => caches.match(request))
    );
    return;
  }

  // Default: stale-while-revalidate
  event.respondWith(
    caches.match(request).then(cached => {
      const networkFetch = fetch(request).then(response => {
        if (response.ok) {
          caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, response.clone()));
        }
        return response;
      }).catch(() => cached);
      return cached || networkFetch;
    })
  );
});

// ──────────────────────────────────────────────────────────
// BACKGROUND SYNC: Queue offline check-ins
// ──────────────────────────────────────────────────────────
self.addEventListener('sync', event => {
  if (event.tag === 'ntti-offline-attendance') {
    event.waitUntil(syncOfflineAttendance());
  }
});

async function syncOfflineAttendance() {
  try {
    const db = await openOfflineDB();
    const queue = await getAllFromDB(db, 'attendance_queue');
    for (const item of queue) {
      try {
        const response = await fetch('/api/kiosk/sync-offline', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: JSON.stringify(item.data)
        });
        if (response.ok) {
          await deleteFromDB(db, 'attendance_queue', item.id);
        }
      } catch (e) {
        // Keep in queue for next sync
      }
    }
  } catch (e) {
    console.warn('[SW] Sync failed:', e);
  }
}

// ──────────────────────────────────────────────────────────
// PUSH NOTIFICATIONS
// ──────────────────────────────────────────────────────────
self.addEventListener('push', event => {
  if (!event.data) return;
  let data = {};
  try { data = event.data.json(); } catch(e) { data = { title: 'NTTI Attendance', body: event.data.text() }; }

  const options = {
    body: data.body || 'New notification',
    icon: '/images/logo.png',
    badge: '/images/logo.png',
    vibrate: [200, 100, 200],
    tag: data.tag || 'ntti-notification',
    data: { url: data.url || '/' },
    actions: data.actions || []
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'NTTI Attendance', options)
  );
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  const targetUrl = event.notification.data?.url || '/';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
      for (const client of clientList) {
        if (client.url === targetUrl && 'focus' in client) return client.focus();
      }
      return clients.openWindow(targetUrl);
    })
  );
});

// ──────────────────────────────────────────────────────────
// HELPERS
// ──────────────────────────────────────────────────────────
function isStaticAsset(pathname) {
  return /\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|webp)$/.test(pathname);
}

function openOfflineDB() {
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

function getAllFromDB(db, storeName) {
  return new Promise((resolve, reject) => {
    const tx = db.transaction(storeName, 'readonly');
    const req = tx.objectStore(storeName).getAll();
    req.onsuccess = e => resolve(e.target.result);
    req.onerror = e => reject(e.target.error);
  });
}

function deleteFromDB(db, storeName, id) {
  return new Promise((resolve, reject) => {
    const tx = db.transaction(storeName, 'readwrite');
    const req = tx.objectStore(storeName).delete(id);
    req.onsuccess = () => resolve();
    req.onerror = e => reject(e.target.error);
  });
}
