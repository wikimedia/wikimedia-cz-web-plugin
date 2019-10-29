document.addEventListener("DOMContentLoaded", () => {
	let maps = document.querySelectorAll('.wmcz-map-container');
	for (let i = 0; i < maps.length; i++) {
		const containerEl = maps[i];
		const el = document.querySelector('.wmcz-map[data-id="' + containerEl.getAttribute('data-id') + '"]');
		const dataEl = document.querySelector('.wmcz-map-data[data-id="' + containerEl.getAttribute('data-id') + '"]');
		const data = JSON.parse(dataEl.innerHTML);

		const map = L.map(el.id);
		L.tileLayer( 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    id: 'wikipedia-map-01',
                    attribution: 'Wikimedia maps beta | Map data &copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap contributors</a>'
		}).addTo( map );

		map.setView( [ data.defaults.lat, data.defaults.lon ], data.defaults.zoom );
		for (let j = 0; j < data.points.length; j++) {
			const point = data.points[j];
			if ( point.lat !== null && point.lon !== null ) {
				L.marker( [ point.lat, point.lon ] ).addTo( map );
			}
		}
	}
} );