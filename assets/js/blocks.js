(function (wp) {
	const registerBlockType = wp.blocks.registerBlockType;
	const ServerSideRender = wp.serverSideRender;
	const el = wp.element.createElement;
	const InspectorControls = wp.blockEditor.InspectorControls;
	const PanelBody = wp.components.PanelBody;
	const SelectControl = wp.components.SelectControl;
	const TextControl = wp.components.TextControl;
	const __ = wp.i18n.__;

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
			unit: {
				// Optional: if we want to force a unit
				type: 'string',
				default: '',
			},
		},
		edit(props) {
			const attributes = props.attributes;
			const setAttributes = props.setAttributes;

			return [
				el(
					InspectorControls,
					{ key: 'inspector' },
					el(
						PanelBody,
						{
							title: __('Einstellungen', 'wp-pfadi-manager'),
							initialOpen: true,
						},
						el(SelectControl, {
							label: __('Ansicht', 'wp-pfadi-manager'),
							value: attributes.view,
							options: [
								{
									label: __('Kacheln', 'wp-pfadi-manager'),
									value: 'cards',
								},
								{
									label: __('Tabelle', 'wp-pfadi-manager'),
									value: 'table',
								},
							],
							onChange(newVal) {
								setAttributes({ view: newVal });
							},
						})
					)
				),
				el(ServerSideRender, {
					block: 'pfadi/board',
					attributes,
				}),
			];
		},
		save() {
			return null; // Rendered in PHP
		},
	});

	// --- Pfadi Subscribe Block ---
	registerBlockType('pfadi/subscribe', {
		title: __('Pfadi Abo Formular', 'wp-pfadi-manager'),
		icon: 'email',
		category: 'widgets',
		edit(props) {
			return el(ServerSideRender, {
				block: 'pfadi/subscribe',
				attributes: props.attributes,
			});
		},
		save() {
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
			},
		},
		edit(props) {
			const attributes = props.attributes;
			const setAttributes = props.setAttributes;

			return [
				el(
					InspectorControls,
					{ key: 'inspector' },
					el(
						PanelBody,
						{
							title: __('Einstellungen', 'wp-pfadi-manager'),
							initialOpen: true,
						},
						el(SelectControl, {
							label: __('Ansicht', 'wp-pfadi-manager'),
							value: attributes.view,
							options: [
								{
									label: __('Karussell', 'wp-pfadi-manager'),
									value: 'carousel',
								},
								{
									label: __(
										'Banner (Neuste)',
										'wp-pfadi-manager'
									),
									value: 'banner',
								},
							],
							onChange(newVal) {
								setAttributes({ view: newVal });
							},
						}),
						el(TextControl, {
							label: __('Limit (Anzahl)', 'wp-pfadi-manager'),
							value: attributes.limit,
							type: 'number',
							onChange(newVal) {
								setAttributes({ limit: parseInt(newVal) });
							},
						})
					)
				),
				el(ServerSideRender, {
					block: 'pfadi/news',
					attributes,
				}),
			];
		},
		save() {
			return null;
		},
	});
})(window.wp);
