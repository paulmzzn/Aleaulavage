( function( blocks, editor, i18n, element ) {
    var el = element.createElement;
    var __ = i18n.__;
    blocks.registerBlockType( 'aleaulavage/random-products', {
        title: __( 'Random Products', 'aleaulavage' ),
        icon: 'store',
        category: 'widgets',
        attributes: {
            className: {
                type: 'string',
            },
        },
        edit: function( props ) {
            return el(
                'div',
                { className: props.className },
                __( 'Random Products', 'aleaulavage' )
            );
        },
        save: function( props ) {
            return el(
                'div',
                { className: props.className },
                __( 'Random Products', 'aleaulavage' )
            );
        },
    } );
} )(
    window.wp.blocks,
    window.wp.editor,
    window.wp.i18n,
    window.wp.element,
);