wp.blocks.registerBlockType('wmcz/calendar-list', {
    title: 'WMCZ Calendar List',
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
          'WMCZ Calendar List'
        ),
        React.createElement(
          'label',
          {
            for: 'wmcz-calendar-ical',
          },
          'Calendar URL'
        ),
        React.createElement(
          'input',
          {
            id: 'wmcz-calendar-ical',
            type: 'text',
            value: props.attributes.ical,
            onChange: updateIcal
          }
        )
      );
    }
  });