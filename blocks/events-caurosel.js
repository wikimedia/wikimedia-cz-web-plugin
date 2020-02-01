wp.blocks.registerBlockType('wmcz/events-caurosel', {
    title: 'WMCZ Events caurosel',
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
                'WMCZ caurosel'
            )
        );
    }
});