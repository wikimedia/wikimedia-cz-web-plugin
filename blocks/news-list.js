wp.blocks.registerBlockType('wmcz/news-list', {
    title: 'WMCZ List of News',
    icon: 'megaphone',
    category: 'widgets',
    attributes: {
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
                'WMCZ list of news'
            )
        );
    }
});