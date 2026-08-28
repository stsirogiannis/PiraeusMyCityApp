</main>

<footer class="bg-dark text-light py-4 mt-auto">
  <div class="container">
    <div class="row g-3 align-items-stretch">

      <!-- στοιχεία επικοινωνίας-->
      <div class="col-12 col-lg-6"> <!-- 1 στήλη για mobile, 2 στύλες για >992px -->
        <h5 class="mb-3">Piraeus MyCity</h5>
        <p class="mb-1">Καραολή και Δημητρίου 80</p>
        <p class="mb-1">Πειραιάς, Τ.Κ. 18534</p>
        <p class="mb-1">
          Τηλέφωνο: <a href="tel:+302104142000" class="link-light">210 414 2000</a>
        </p>
        <p class="mb-1">
          Email: <a href="mailto:info@piraeus-mycity.gr" class="link-light">info@piraeus-mycity.gr</a>
        </p>
        <p class="mt-3 mb-0 small text-secondary">
          &copy; <?php echo date('Y'); ?> Δήμος Πειραιά <!-- το έτος ενημερώνεται από μόνο του-->
        </p>
      </div>

      <!-- χάρτης-->
      <div class="col-12 col-lg-6">
        <h5 class="mb-3">Τοποθεσία</h5>
        <div id="footerMap" style="height: 190px; border-radius: 8px;"></div>
      </div>

    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

  var lat = 37.9415;
  var lon = 23.6528;

  var map = L.map('footerMap').setView([lat, lon], 16);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  L.marker([lat, lon])
    .addTo(map)
    .bindPopup('<strong>Δημαρχείο Πειραιά</strong><br>Έδρα');

    setTimeout(function () { map.invalidateSize(); }, 200);

});
</script>

</body>

</html>