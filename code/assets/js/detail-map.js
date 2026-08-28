document.addEventListener('DOMContentLoaded', function () {

    var map = L.map('issueMap').setView([issueLat, issueLon], 17); // χάρτης μέσα στο div με id, κεντράρισμα, zoom

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([issueLat, issueLon]) //πινέζα με popup
     .addTo(map)
     .bindPopup('<strong>' + issueTitle + '</strong><br>' + issueAddr)
     .openPopup();

    setTimeout(function () { map.invalidateSize(); }, 200);
});