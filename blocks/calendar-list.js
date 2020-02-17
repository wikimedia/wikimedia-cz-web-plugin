wp.blocks.registerBlockType('wmcz/calendar-list', {
    title: 'WMCZ Calendar List',
    icon: 'megaphone',
    category: 'widgets',
    attributes: {
      icals: {
        type: 'string',
        default: '{"names":[],"urls":[]}'
      },
      numOfCalendars: {
        type: 'number',
        default: 1
      }
    },
    save: function () {
      return null
    },
    edit: function( props ) {
      let icals = JSON.parse(props.attributes.icals);

      let calendarEls = [];
      for (let i = 0; i < props.attributes.numOfCalendars; i++) {
        let definedName = icals.names[i];
        let definedUrl = icals.urls[i];

        calendarEls.push(
          React.createElement(
            'tr',
            null,
            React.createElement(
              'td',
              null,
              React.createElement(
                'input',
                {
                  type: 'text',
                  class: 'wmcz-calendar-name',
                  value: definedName,
                  onChange: ( e ) => {
                    icals['names'][i] = e.target.value;
                    props.setAttributes({
                      icals: JSON.stringify(icals)
                    });
                  }
                }
              )
            ),
            React.createElement(
              'tr',
              null,
              React.createElement(
                'input',
                {
                  type: 'text',
                  class: 'wmcz-calendar-url',
                  value: definedUrl,
                  onChange: ( e ) => {
                    icals['urls'][i] = e.target.value;
                    props.setAttributes({
                      icals: JSON.stringify(icals)
                    });
                  }
                }
              )
            )
          )
        );
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
            for: 'wmcz-calendar-number'
          },
          'Number of calendars'
        ),
        React.createElement(
          'input',
          {
            id: 'wmcz-calendar-number',
            type: 'number',
            value: props.attributes.numOfCalendars,
            min: 1,
            max: 100,
            onChange: ( e ) => {
              props.setAttributes({
                numOfCalendars: parseInt(e.target.value)
              });
            }
          }
        ),
        React.createElement(
          'table',
          null,
          React.createElement(
            'thead',
            null,
            React.createElement(
              'tr',
              null,
              React.createElement(
                'th',
                null,
                'Název kalendáře'
              ),
              React.createElement(
                'th',
                null,
                'iCal URL'
              )
            )
          ),
          React.createElement(
            'tbody',
            null,
            calendarEls
          )
        )
      );
    }
  });