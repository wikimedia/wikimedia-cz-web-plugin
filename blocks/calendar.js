wp.blocks.registerBlockType('wmcz/calendar', {
  title: 'WMCZ Calendar',
  icon: 'megaphone',
  category: 'widgets',
  attributes: {
    cols: { type: 'string' },
    rows: { type: 'string' },
    ical: { type: 'string' },
    tag: { type: 'string' },
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

    function updateTag( event ) {
      props.setAttributes({
        tag: event.target.value
      });
    }

    return React.createElement(
      'div',
      null,
      React.createElement(
        'h3',
        null,
        'WMCZ Calendar'
      ),
      React.createElement(
        'label',
        {
          for: 'wmcz-calendar-cols',
        },
        'Number of columns'
      ),
      React.createElement(
        'input',
        {
          id: 'wmcz-calendar-cols',
          type: 'number',
          value: props.attributes.cols,
          onChange: updateCols
        }
      ),
      React.createElement(
        'label',
        {
          for: 'wmcz-calendar-rows',
        },
        'Number of rows'
      ),
      React.createElement(
        'input',
        {
          id: 'wmcz-calendar-rows',
          type: 'number',
          value: props.attributes.rows,
          onChange: updateRows
        }
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
      ),
      React.createElement(
        'label',
        null,
        'Tag filter (enter tag name to apply, leave empty to display all events)'
      ),
      React.createElement(
        'input',
        {
          type: 'text',
          value: props.attributes.tag,
          onChange: updateTag
        }
      )
    );
  }
});