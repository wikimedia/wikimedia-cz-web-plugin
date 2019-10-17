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
    edit: (props) => {
        class GenerateSlide extends React.Component {
            render() {
                return (React.createElement(
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
                                onChange: (value) => {
                                    let arrayNew = props.attributes.headline.slice(); // HACK
                                    arrayNew[this.props.index] = value;
                                    props.setAttributes({
                                        headline: arrayNew
                                    });
                                },
                                value: this.props.headline
                            }
                        ),
                        el(
                            RichText,
                            {
                                tagName: 'p',
                                className: 'wmcz-caurosel-description',
                                onChange: (value) => {
                                    let arrayNew = props.attributes.description.slice(); // HACK
                                    arrayNew[this.props.index] = value;
                                    props.setAttributes({
                                        description: arrayNew
                                    });
                                },
                                value: this.props.description
                            }
                        )
                    )
                ))
            }
        }

        class Carousel extends React.Component {
            render() {
                let screenControls = [];
                for (let i = 0; i < props.attributes.headline.length; i++) {
                    screenControls.push(React.createElement(
                        'a',
                        {
                            class: "wmcz-caurosel-screen-control",
                            href: "#",
                            onClick: () => {
                                this.props.index = i;
                                this.props.headline = props.attributes.headline[this.props.index];
                                this.props.description = props.attributes.description[this.props.index];
                                this.forceUpdate();
                            }
                        },
                        i + 1
                    ));
                }

                return (
                    React.createElement(
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
                                            props.attributes.headline.push('')
                                            props.attributes.description.push('')
                                            this.props.index = props.attributes.headline.length - 1;
                                            this.props.headline = props.attributes.headline[this.props.index];
                                            this.props.description = props.attributes.description[this.props.index];
                                            this.forceUpdate()
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
                            React.createElement(
                                GenerateSlide, {
                                description: this.props.description,
                                headline: this.props.headline,
                                index: this.props.index
                            },
                                null
                            )
                        )
                    )
                );
            }
        }

        return React.createElement(
            Carousel, {
            headline: props.attributes.headline[props.attributes.headline.length - 1],
            description: props.attributes.description[props.attributes.headline.length - 1],
            index: props.attributes.headline.length - 1
        },
            null
        )
    }
});