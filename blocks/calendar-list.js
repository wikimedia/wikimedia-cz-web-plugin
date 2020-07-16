wp.blocks.registerBlockType('wmcz/calendar-list', {
    title: 'WMCZ Calendar List',
    icon: 'megaphone',
    category: 'widgets',
    attributes: {
      ical: {
        type: 'string',
        default: ''
      }
    },
    save: function () {
      return null
    },
    edit: function( props ) {
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
          null,
          'Ical URL'
        ),
        React.createElement(
          'input',
          {
            type: 'text',
            value: props.attributes.ical,
            onChange: ( e ) => {
              props.setAttributes({
                  ical: e.target.value
              });
            }
          }
        )
      );
    }
  });