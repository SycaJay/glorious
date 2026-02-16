const CACHE_NAME = "glorious-visions-cache-v1";
const urlsToCache = [
  "/",
  "/index.php",
  "/assets/TvLogo.png",
  "/assets/TvLogo.png",
  "/assets/screenshot1.png",
  "/assets/screenshot2.png"
];

// Install Service Worker & Cache Files
self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(urlsToCache);
    })
  );
});

// Fetch & Serve Cached Content Offline
self.addEventListener("fetch", event => {
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request);
    })
  );
});

// Activate & Remove Old Caches
self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => {
          if (cache !== CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    })
  );
});

// Background Sync Example
self.addEventListener("sync", event => {
  if (event.tag === "sync-content") {
    event.waitUntil(updateContent());
  }
});

async function updateContent() {
  console.log("Syncing data...");
}
