(function (blocks, element, components, blockEditor) {
    'use strict';

    var el = element.createElement;
    var Placeholder = components.Placeholder;
    var useBlockProps = blockEditor.useBlockProps;
    var cfg = window.BitHubReviewsBlock || {};

    blocks.registerBlockType('bithub/reviews-widget', {
        title: cfg.title || 'BitHub Reviews Widget',
        description: cfg.description || '',
        icon: 'star-filled',
        category: 'widgets',
        edit: function () {
            var props = useBlockProps();
            return el(
                'div',
                props,
                el(
                    Placeholder,
                    {
                        icon: 'star-filled',
                        label: cfg.title || 'BitHub Reviews Widget'
                    },
                    cfg.configured ? cfg.readyText : cfg.emptyText
                )
            );
        },
        save: function () {
            return null;
        }
    });
}(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor));
