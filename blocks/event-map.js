wp.blocks.registerBlockType('wmcz/event-map', {
  title: 'WMCZ Event map',
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
        'WMCZ Event Map'
      ),
      React.createElement(
        'label',
        {
          for: 'wmcz-events-ical',
        },
        'Calendar URL'
      ),
      React.createElement(
        'input',
        {
          id: 'wmcz-events-ical',
          type: 'text',
          value: props.attributes.ical,
          onChange: updateIcal
        }
      )
    );
  }
});