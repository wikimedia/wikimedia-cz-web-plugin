function wmczInitMap(id, ical, gestureHandling, defaults) {
	const containerEl = document.querySelector(`.wmcz-map-container[data-id="${id}"]`);
	const mapEl = document.querySelector('.wmcz-map[data-id="' + containerEl.getAttribute('data-id') + '"]');
	console.log(gestureHandling);
	const map = L.map( mapEl.id, {
		center: [ defaults.lat, defaults.lon ],
		zoom: defaults.zoom,
		gestureHandling: gestureHandling,
	});
	L.tileLayer( 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {
				maxZoom: 18,
				id: 'wikipedia-map-01',
				attribution: 'Wikimedia maps beta | Map data &copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap contributors</a>'
	}).addTo( map );

	fetch(`${jsVars.api}?action=getMapData&ical=${ical}`).then((resp) => resp.json()).then((data) => {
		for (let i = 0; i < data.data.points.length; i++) {
			const point = data.data.points[i];
			if ( point.lat !== null && point.lon !== null ) {
				let marker = L.marker( [ point.lat, point.lon ] ).addTo( map );
				if ( point.link !== null ) {
					marker.bindPopup('<a href="' + point.link + '">Zobrazit všechny akce v tomto městě</a>');
				}
			}
		}
	});
}