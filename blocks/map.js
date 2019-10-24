wp.blocks.registerBlockType('wmcz/map', {
  title: 'WMCZ Map',
  icon: 'megaphone',
  category: 'widgets',
  attributes: {
    ical: { type: 'string' },
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
      )
    );
  }
});