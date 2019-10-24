wp.blocks.registerBlockType('wmcz/map', {
  title: 'WMCZ Map',
  icon: 'megaphone',
  category: 'widgets',
  attributes: {
    ical: { type: 'string' },
    lat: { type: 'string' },
    lon: { type: 'string' },
    zoom: { type: 'string' },
  },
  save: function () {
    return null
  },
  edit: function( props ) {
    function updateIcal( event ) {
      props.setAttributes({
        ical: event.target.value
      });
    }
    function updateDefaultLat( event ) {
      props.setAttributes({
        lat: event.target.value
      });
    }
    function updateDefaultLon( event ) {
      props.setAttributes({
        lon: event.target.value
      });
    }
    function updateDefaultZoom( event ) {
      props.setAttributes({
        zoom: event.target.value
      });
    }

    return React.createElement(
      'div',
      null,
      React.createElement(
        'h3',
        null,
        'WMCZ Map'
      ),
      React.createElement(
        'label',
        {
          for: 'wmcz-map-ical',
        },
        'Calendar URL'
      ),
      React.createElement(
        'input',
        {
          id: 'wmcz-map-ical',
          type: 'text',
          value: props.attributes.ical,
          onChange: updateIcal
        }
      ),
      React.createElement(
        'label',
        {
          'for': 'wmcz-map-def-lat'
        },
        'Default lat'
      ),
      React.createElement(
        'input',
        {
          id: 'wmcz-map-def-lat',
          type: 'text',
          value: props.attributes.lat,
          onChange: updateDefaultLat
        }
      ),
      React.createElement(
        'label',
        {
          'for': 'wmcz-map-def-lon'
        },
        'Default lon'
      ),
      React.createElement(
        'input',
        {
          id: 'wmcz-map-def-lon',
          type: 'text',
          value: props.attributes.lon,
          onChange: updateDefaultLon
        }
      ),
      React.createElement(
        'label',
        {
          'for': 'wmcz-map-def-zoom'
        },
        'Default zoom'
      ),
      React.createElement(
        'input',
        {
          id: 'wmcz-map-def-zoom',
          type: 'number',
          value: props.attributes.zoom,
          onChange: updateDefaultZoom
        }
      )
    );
  }
});