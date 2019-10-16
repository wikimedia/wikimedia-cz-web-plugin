document.addEventListener("DOMContentLoaded", () => {
	let maps = document.querySelectorAll('.wmcz-map');
	for (let i = 0; i < maps.length; i++) {
		const el = maps[i];
		const map = L.map(el.id);
		L.tileLayer( 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    id: 'wikipedia-map-01',
                    attribution: 'Wikimedia maps beta | Map data &copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap contributors</a>'
		}).addTo( map );
		map.setView( [50.03861, 15.77916], 13 );
	}
} );