wp.blocks.registerBlockType('wmcz/events', {
  title: 'WMCZ Events',
  icon: 'megaphone',
  category: 'widgets',
  attributes: {
    cols: { type: 'string' },
    rows: { type: 'string' },
    ical: { type: 'string' },
  },
  save: function () {
    return null
  },
  edit: function( props ) {
    function updateCols( event ) {
      props.setAttributes({
        cols: event.target.value
      });
    }

    function updateRows( event ) {
      props.setAttributes({
        rows: event.target.value
      });
    }

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
        'WMCZ Events'
      ),
      React.createElement(
        'label',
        {
          for: 'wmcz-events-cols',
        },
        'Number of columns'
      ),
      React.createElement(
        'input',
        {
          id: 'wmcz-events-cols',
          type: 'number',
          value: props.attributes.cols,
          onChange: updateCols
        }
      ),
      React.createElement(
        'label',
        {
          for: 'wmcz-events-rows',
        },
        'Number of rows'
      ),
      React.createElement(
        'input',
        {
          id: 'wmcz-events-rows',
          type: 'number',
          value: props.attributes.rows,
          onChange: updateRows
        }
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