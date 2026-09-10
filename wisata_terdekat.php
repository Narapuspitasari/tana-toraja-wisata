<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Wisata Terdekat';
$active = 'terdekat';

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="section">
    <div class="ed-eyebrow"><span class="num">05</span> Find Your Nearest Escape</div>
    <h1 class="page-banner-title">Wisata Terdekat</h1>
    <p>
      Sistem mengirim koordinat lokasi Anda ke server, lalu server menghitung jarak lurus (Proximity Analysis)
      ke setiap destinasi wisata di database menggunakan rumus Haversine, dan mengembalikan destinasi yang
      berada dalam radius pencarian (Buffer Analysis) yang Anda pilih.
    </p>
  </div>
</div>

<div class="section" style="padding-top:30px;">
  <div id="lbsIntro" class="lbs-intro">
    <p>
      📍 Klik tombol di bawah untuk mengizinkan browser membaca lokasi Anda saat ini. Lokasi tidak disimpan di
      server, hanya dikirim sekali untuk menghitung jarak lalu dibuang.
    </p>
    <button class="btn btn-sm" onclick="activateLocation()">Aktifkan Lokasi Saya</button>
    <span id="lbsStatus" style="margin-left:10px;font-size:12.5px;color:rgba(246,241,230,.8);"></span>
  </div>

  <div id="lbsContent" style="display:none;">
    <div class="radius-row">
      <label>Radius Pencarian (Buffer):</label>
      <?php foreach ([5, 10, 20, 50] as $r): ?>
        <button class="btn btn-sm radius-btn" data-radius="<?php echo $r; ?>" onclick="setRadius(<?php echo $r; ?>)">
          <?php echo $r; ?> km
        </button>
      <?php endforeach; ?>
    </div>

    <div class="peta-wrapper" style="height:520px;border-radius:12px;overflow:hidden;border:1px solid #ece7da;">
      <aside class="peta-sidebar" id="lbsList" style="width:340px;"></aside>
      <div id="lbsMap"></div>
    </div>
  </div>

  <div id="lbsEmpty" style="display:none;background:#fff;border:1px solid #ece7da;border-radius:12px;padding:24px;margin-top:20px;">
    <p style="margin:0;font-size:13.5px;color:#777;">
      Tidak ada destinasi wisata dalam radius yang dipilih. Coba perbesar radius pencarian.
    </p>
  </div>
</div>

<script>
  const baseUrl = "<?php echo BASE_URL; ?>";

  let userLat = null, userLng = null;
  let currentRadius = 10;
  let map, userMarker, bufferCircle;
  let wisataMarkers = [];

  function activateLocation() {
    if (!navigator.geolocation) {
      document.getElementById('lbsStatus').textContent = 'Browser Anda tidak mendukung fitur lokasi.';
      return;
    }
    document.getElementById('lbsStatus').textContent = 'Mendeteksi lokasi...';

    navigator.geolocation.getCurrentPosition(
      function (pos) {
        userLat = pos.coords.latitude;
        userLng = pos.coords.longitude;
        document.getElementById('lbsIntro').style.display = 'none';
        document.getElementById('lbsContent').style.display = 'block';
        initMap();
        setRadius(currentRadius);
      },
      function () {
        document.getElementById('lbsStatus').textContent =
          'Lokasi ditolak/tidak tersedia. Aktifkan izin lokasi di browser lalu coba lagi.';
      }
    );
  }

  function setRadius(r) {
    currentRadius = r;
    document.querySelectorAll('.radius-btn').forEach(function (btn) {
      const active = parseInt(btn.dataset.radius) === r;
      btn.style.background = active ? 'var(--toraja-green-dark)' : '#fff';
      btn.style.color = active ? '#fff' : 'var(--toraja-green-dark)';
    });
    updateBuffer();
    fetchNearby();
  }

  function initMap() {
    map = L.map('lbsMap').setView([userLat, userLng], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const userIcon = L.divIcon({
      className: '',
      html: '<div style="width:16px;height:16px;border-radius:50%;background:#16281d;border:3px solid #fff;box-shadow:0 0 0 3px rgba(22,40,29,.4);"></div>',
      iconSize: [16, 16]
    });
    userMarker = L.marker([userLat, userLng], { icon: userIcon }).addTo(map).bindPopup('Lokasi Anda');
  }

  function updateBuffer() {
    if (bufferCircle) map.removeLayer(bufferCircle);
    bufferCircle = L.circle([userLat, userLng], {
      radius: currentRadius * 1000, // meter
      color: '#16281d', weight: 1.5, fillColor: '#16281d', fillOpacity: 0.08
    }).addTo(map);
    map.fitBounds(bufferCircle.getBounds());
  }

  // Memanggil endpoint server: Proximity Analysis (Haversine) + Buffer Analysis
  // dijalankan sepenuhnya di sisi server lewat class Wisata (bukan di JavaScript).
  function fetchNearby() {
    document.getElementById('lbsStatus').textContent = 'Menghitung jarak ke server...';

    fetch(baseUrl + 'ajax/wisata_terdekat.php?lat=' + userLat + '&lon=' + userLng + '&radius=' + currentRadius)
      .then(function (res) { return res.json(); })
      .then(function (json) {
        document.getElementById('lbsStatus').textContent = '';
        renderNearby(json.data);
      })
      .catch(function () {
        document.getElementById('lbsStatus').textContent = 'Gagal menghubungi server. Coba lagi.';
      });
  }

  function renderNearby(within) {
    // Bersihkan marker wisata lama
    wisataMarkers.forEach(function (m) { map.removeLayer(m); });
    wisataMarkers = [];

    const listEl = document.getElementById('lbsList');
    const emptyEl = document.getElementById('lbsEmpty');

    if (within.length === 0) {
      listEl.innerHTML = '';
      emptyEl.style.display = 'block';
      return;
    }
    emptyEl.style.display = 'none';

    listEl.innerHTML = within.map(function (w, i) {
      return '<div class="peta-list-item" onclick="focusWisata(' + w.id_wisata + ')">' +
        '<div class="dot">' + (i + 1) + '</div>' +
        '<div><h5>' + w.nama_wisata + '</h5>' +
        '<span>' + w.jarak + ' km &middot; ' + w.kategori + '</span></div></div>';
    }).join('');

    within.forEach(function (w) {
      const marker = L.marker([parseFloat(w.latitude), parseFloat(w.longitude)]).addTo(map);
      const fotoHtml = w.foto_url
        ? '<img src="' + w.foto_url + '" alt="' + w.nama_wisata + '">'
        : '';
      marker.bindPopup(
        '<div class="torja-popup">' + fotoHtml +
        '<div class="tp-body"><strong>' + w.nama_wisata + '</strong>' +
        '<span class="tp-cat">' + w.jarak + ' km dari lokasi Anda</span><br>' +
        '<a href="' + baseUrl + 'detail.php?id=' + w.id_wisata + '">Lihat detail &rarr;</a></div></div>'
      );
      marker.wisataId = w.id_wisata;
      wisataMarkers.push(marker);
    });
  }

  function focusWisata(id) {
    const m = wisataMarkers.find(function (mk) { return mk.wisataId === id; });
    if (!m) return;
    map.setView(m.getLatLng(), 14, { animate: true });
    m.openPopup();
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
