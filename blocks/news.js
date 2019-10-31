wp.blocks.registerBlockType('wmcz/news', {
    title: 'WMCZ News',
    icon: 'megaphone',
    category: 'widgets',
    attributes: {
      more: { type: 'string' },
    },
    save: function () {
        return null
    },
    edit: function (props) {
        function updateMore( event ) {
            props.setAttributes({
                more: event.target.value
            });
        }

        return React.createElement(
            'div',
            null,
            React.createElement(
                'h3',
                null,
                'WMCZ news'
            ),
            React.createElement(
                'label',
                {
                    for: 'wmcz-news-more'
                },
                'More button link'
            ),
            React.createElement(
                'input',
                {
                    type: 'text',
                    id: 'wmcz-news-more',
                    value: props.attributes.more,
                    onChange: updateMore
                }
            )
        );
    }
});