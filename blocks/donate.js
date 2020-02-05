wp.blocks.registerBlockType('wmcz/donate', {
    title: 'WMCZ Donate',
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
                'WMCZ Donate'
            )
        );
    }
});