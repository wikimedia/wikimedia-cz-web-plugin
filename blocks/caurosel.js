wp.blocks.registerBlockType('wmcz/caurosel', {
    title: 'WMCZ Caurosel',
    icon: 'megaphone',
    category: 'widgets',
    attributes: {

    },
    save: () => {
        return null;
    },
    edit: () => {
        return React.createElement(
            'div',
            null,
            '<!-- add something here -->'
        );
    }
});