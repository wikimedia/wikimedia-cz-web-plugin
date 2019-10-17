var el = wp.element.createElement;
var RichText = wp.editor.RichText;
wp.blocks.registerBlockType('wmcz/caurosel', {
    title: 'WMCZ Caurosel',
    icon: 'megaphone',
    category: 'widgets',
    attributes: {
        headline: {
            type: 'array',
        },
        description: {
            type: 'array',
        }
    },
    save: () => {
        return null;
    },
    edit: ( props ) => {
        function onChangeHeadline( value ) {
            props.setAttributes( {
                headline: [
                    value
                ]
            } );
        }

        function onChangeDescription( value ) {
            props.setAttributes( {
                description: [
                    value
                ]
            } );
        }


        function generateSlide( headline, description ) {
            return React.createElement(
                'div',
                {
                    class: "wmcz-caurosel-container"
                },
                React.createElement(
                    'div',
                    {
                        class: "wmcz-caurosel-left"
                    }
                ),
                React.createElement(
                    'div',
                    {
                        class: "wmcz-caurosel-right-colored"
                    },
                    el(
                        RichText,
                        {
                            tagName: 'h2',
                            className: 'wmcz-caurosel-headline',
                            onChange: onChangeHeadline,
                            value: headline
                        }
                    ),
                    el(
                        RichText,
                        {
                            tagName: 'p',
                            className: 'wmcz-caurosel-description',
                            onChange: onChangeDescription,
                            value: description
                        }
                    )
                )
            );
        }

        function getSlide( index ) {
            return generateSlide( props.attributes.headline[index], props.attributes.description[index] );
        }

        let screenControls = [];
        for (let i = 0; i < props.attributes.headline.length; i++) {
            screenControls.push(React.createElement(
                'a' ,
                {
                    class: "wmcz-caurosel-screen-control",
                    href: "#",
                    onClick: () => {
                        console.log('not implemented');
                        
                    }
                },
                i+1
            ));
        }

        return React.createElement(
            'div',
            {
                class: 'wmcz-caurosel-admin-container'
            },
            React.createElement(
                'div',
                {
                    class: "wmcz-caurosel-controls"
                },
                React.createElement(
                    'div',
                    {
                        class: "wmcz-caurosel-left"
                    },
                    React.createElement(
                        'div',
                        {
                            class: "wmcz-caurosel-screen-controls"
                        },
                        screenControls
                    )
                ),
                React.createElement(
                    'div',
                    {
                        class: "wmcz-caurosel-right"
                    },
                    React.createElement(
                        'button',
                        {
                            class: 'wmcz-caurosel-new-slide',
                            onClick: () => {
                                console.log('not implemented');
                            }
                        },
                        'Nová obrazovka'
                    )
                )
            ),
            React.createElement(
                'div',
                {
                    class: "wmcz-caurosel-outer-container"
                },
                getSlide(0)
            )
        );
    }
});