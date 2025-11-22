(function (wp) {
    var registerBlockType = wp.blocks.registerBlockType;
    var ServerSideRender = wp.serverSideRender;
    var el = wp.element.createElement;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var SelectControl = wp.components.SelectControl;
    var TextControl = wp.components.TextControl;
    var __ = wp.i18n.__;

    // --- Pfadi Board Block ---
    registerBlockType('pfadi/board', {
        title: __('Pfadi Aktivitäten Board', 'wp-pfadi-manager'),
        icon: 'calendar-alt',
        category: 'widgets',
        attributes: {
            view: {
                type: 'string',
                default: 'cards',
            },
            unit: { // Optional: if we want to force a unit
                type: 'string',
                default: '',
            }
        },
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            return [
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, { title: __('Einstellungen', 'wp-pfadi-manager'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Ansicht', 'wp-pfadi-manager'),
                            value: attributes.view,
                            options: [
                                { label: __('Kacheln', 'wp-pfadi-manager'), value: 'cards' },
                                { label: __('Tabelle', 'wp-pfadi-manager'), value: 'table' },
                            ],
                            onChange: function (newVal) {
                                setAttributes({ view: newVal });
                            }
                        })
                    )
                ),
                el(ServerSideRender, {
                    block: 'pfadi/board',
                    attributes: attributes,
                })
            ];
        },
        save: function () {
            return null; // Rendered in PHP
        },
    });

    // --- Pfadi Subscribe Block ---
    registerBlockType('pfadi/subscribe', {
        title: __('Pfadi Abo Formular', 'wp-pfadi-manager'),
        icon: 'email',
        category: 'widgets',
        edit: function (props) {
            return el(ServerSideRender, {
                block: 'pfadi/subscribe',
                attributes: props.attributes,
            });
        },
        save: function () {
            return null;
        },
    });

    // --- Pfadi News Block ---
    registerBlockType('pfadi/news', {
        title: __('Pfadi Mitteilungen', 'wp-pfadi-manager'),
        icon: 'megaphone',
        category: 'widgets',
        attributes: {
            view: {
                type: 'string',
                default: 'carousel',
            },
            limit: {
                type: 'number',
                default: -1,
            }
        },
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            return [
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, { title: __('Einstellungen', 'wp-pfadi-manager'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Ansicht', 'wp-pfadi-manager'),
                            value: attributes.view,
                            options: [
                                { label: __('Karussell', 'wp-pfadi-manager'), value: 'carousel' },
                                { label: __('Banner (Neuste)', 'wp-pfadi-manager'), value: 'banner' },
                            ],
                            onChange: function (newVal) {
                                setAttributes({ view: newVal });
                            }
                        }),
                        el(TextControl, {
                            label: __('Limit (Anzahl)', 'wp-pfadi-manager'),
                            value: attributes.limit,
                            type: 'number',
                            onChange: function (newVal) {
                                setAttributes({ limit: parseInt(newVal) });
                            }
                        })
                    )
                ),
                el(ServerSideRender, {
                    block: 'pfadi/news',
                    attributes: attributes,
                })
            ];
        },
        save: function () {
            return null;
        },
    });

})(window.wp);
