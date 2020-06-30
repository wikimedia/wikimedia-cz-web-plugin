wp.blocks.registerBlockType('wmcz/latest-news', {
    title: 'WMCZ Latest News',
    icon: 'megaphone',
    category: 'widgets',
    attributes: {
        tag: {
            type: "string",
            default: ""
        }
    },
    save: function () {
        return null
    },
    edit: function (props) {
        return React.createElement(
            'div',
            null,
            React.createElement(
                'h3',
                null,
                'WMCZ Latest News'
            ),
            React.createElement(
                'label',
                {
                    for: 'wmcz-latest-news-tag'
                },
                'Tag'
            ),
            React.createElement(
                'input',
                {
                    type: 'text',
                    value: props.attributes.tag,
                    onChange: ( e ) => {
                        props.setAttributes({
                            tag: e.target.value
                        });
                    }
                }
            )
        );
    }
});