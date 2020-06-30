wp.blocks.registerBlockType('wmcz/latest-news', {
    title: 'WMCZ Latest News',
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
                'WMCZ Latest News'
            )
        );
    }
});