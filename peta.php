<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Peta Wisata';
$active = 'peta';

// Ambil seluruh data wisata lewat class Wisata (bukan query manual lagi)
$wisataList = $wisataModel->getAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="peta-wrapper">
  <aside class="peta-sidebar">
    <div class="search-box">
      <span class="sb-label">Cari &amp; Temukan</span>
      <input type="text" id="searchInput" placeholder="Cari destinasi wisata...">
      <button id="locateBtn" onclick="locateMe()" class="btn btn-sm btn-forest" style="width:100%;margin-top:10px;">
        📍 Gunakan Lokasi Saya
      </button>
      <div id="locateStatus" style="font-size:11.5px;color:#8a8578;margin-top:8px;"></div>
    </div>
    <div class="peta-count"><?php echo count($wisataList); ?> Destinasi Terdaftar</div>
    <div id="listWisata">
      <?php foreach ($wisataList as $i => $w): ?>
        <div class="peta-list-item" data-id="<?php echo $w['id_wisata']; ?>"
             data-nama="<?php echo strtolower(htmlspecialchars($w['nama_wisata'])); ?>"
             onclick="focusMarker(<?php echo (int)$w['id_wisata']; ?>)">
          <div class="dot"><?php echo $i + 1; ?></div>
          <div>
            <h5><?php echo htmlspecialchars($w['nama_wisata']); ?></h5>
            <span><?php echo htmlspecialchars($w['kategori']); ?> &middot; <?php echo htmlspecialchars($w['alamat']); ?></span>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($wisataList)): ?>
        <p style="padding:16px;font-size:13px;color:#777;">Belum ada data wisata.</p>
      <?php endif; ?>
    </div>
  </aside>
  <div id="map"></div>
</div>

<script>
  // Data wisata dikirim dari server (PHP) ke JavaScript untuk ditampilkan di peta Leaflet
  const wisataData = <?php echo json_encode($wisataList, JSON_UNESCAPED_UNICODE); ?>;
  const baseUrl = "<?php echo BASE_URL; ?>";
  const uploadUrl = "<?php echo UPLOAD_URL; ?>";

  // Titik tengah default: Kabupaten Tana Toraja
  const map = L.map('map').setView([-3.05, 119.85], 11);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const markers = {};

  wisataData.forEach(function (w, i) {
    const lat = parseFloat(w.latitude);
    const lng = parseFloat(w.longitude);
    if (isNaN(lat) || isNaN(lng)) return;

    const marker = L.marker([lat, lng]).addTo(map);
    const fotoHtml = w.foto
      ? `<img src="${uploadUrl}${w.foto}" alt="${w.nama_wisata}">`
      : '';

    marker.bindPopup(`
      <div class="torja-popup">
        ${fotoHtml}
        <div class="tp-body">
          <strong>${w.nama_wisata}</strong>
          <span class="tp-cat">${w.kategori}</span><br>
          <a href="${baseUrl}detail.php?id=${w.id_wisata}">Lihat detail &rarr;</a>
        </div>
      </div>
    `);
    markers[w.id_wisata] = marker;
  });

  function focusMarker(id) {
    const m = markers[id];
    if (!m) return;
    map.setView(m.getLatLng(), 15, { animate: true });
    m.openPopup();
  }

  // Pencarian sederhana di sidebar
  document.getElementById('searchInput').addEventListener('input', function (e) {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('.peta-list-item').forEach(function (el) {
      el.style.display = el.dataset.nama.includes(q) ? 'flex' : 'none';
    });
  });

  // ---------- Deteksi Lokasi Pengguna & Proximity Analysis (server-side) ----------
  let userMarker = null;

  function locateMe() {
    const statusEl = document.getElementById('locateStatus');

    if (!navigator.geolocation) {
      statusEl.textContent = 'Browser Anda tidak mendukung fitur lokasi.';
      return;
    }

    statusEl.textContent = 'Mendeteksi lokasi...';

    navigator.geolocation.getCurrentPosition(
      function (pos) {
        const userLat = pos.coords.latitude;
        const userLng = pos.coords.longitude;
        statusEl.textContent = 'Menghitung jarak ke server...';

        // Kirim koordinat ke server -> server menjalankan Proximity Analysis
        // (Haversine Formula) lewat class Wisata, lalu mengembalikan hasil terurut.
        fetch(baseUrl + 'ajax/wisata_terdekat.php?lat=' + userLat + '&lon=' + userLng)
          .then(function (res) { return res.json(); })
          .then(function (json) {
            statusEl.textContent = 'Lokasi Anda terdeteksi ✓';
            renderUserLocation(userLat, userLng);
            renderNearbyList(json.data);
          })
          .catch(function () {
            statusEl.textContent = 'Gagal menghubungi server. Coba lagi.';
          });
      },
      function () {
        statusEl.textContent = 'Lokasi ditolak/tidak tersedia. Izinkan akses lokasi di browser lalu coba lagi.';
      }
    );
  }

  function renderUserLocation(userLat, userLng) {
    if (userMarker) map.removeLayer(userMarker);
    const userIcon = L.divIcon({
      className: '',
      html: '<div style="width:16px;height:16px;border-radius:50%;background:#16281d;border:3px solid #fff;box-shadow:0 0 0 4px rgba(22,40,29,.35);"></div>',
      iconSize: [16, 16]
    });
    userMarker = L.marker([userLat, userLng], { icon: userIcon }).addTo(map).bindPopup('Lokasi Anda saat ini');
  }

  function renderNearbyList(data) {
    // Render ulang daftar sidebar berdasarkan hasil Proximity Analysis dari server
    const listEl = document.getElementById('listWisata');
    listEl.innerHTML = data.map(function (w, i) {
      return '<div class="peta-list-item" data-nama="' + w.nama_wisata.toLowerCase() + '" onclick="focusMarker(' + w.id_wisata + ')">' +
        '<div class="dot">' + (i + 1) + '</div>' +
        '<div><h5>' + w.nama_wisata + '</h5>' +
        '<span>📍 ' + w.jarak + ' km dari lokasi Anda &middot; ' + w.kategori + '</span></div></div>';
    }).join('');

    // Perbesar/fokuskan peta agar mencakup lokasi pengguna dan 3 wisata terdekat
    if (userMarker && data.length > 0) {
      const nearest = data.slice(0, 3);
      const bounds = L.latLngBounds([userMarker.getLatLng()]);
      nearest.forEach(function (w) { bounds.extend([parseFloat(w.latitude), parseFloat(w.longitude)]); });
      map.fitBounds(bounds, { padding: [40, 40] });
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
