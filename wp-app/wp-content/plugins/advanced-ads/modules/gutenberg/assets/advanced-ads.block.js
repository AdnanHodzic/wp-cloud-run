(() => {
	window.wp.domReady(() => {
		init(window.wp);
	});

	function init(wp) {
		/**
		 *  Shortcut variables
		 */
		const el = wp.element.createElement,
			registerBlockType = wp.blocks.registerBlockType,
			RawHTML = wp.element.RawHTML,
			aaGut = window.advadsGutenberg,
			i18n = aaGut.i18n,
			textFlow = aaGut.textFlow,
			safeHTML = wp.dom.safeHTML,
			editor = wp.blockEditor,
			comp = wp.components;

		/**
		 * Custom SVG icon
		 * could move to a separated file if we need it in other places, too
		 *
		 * <source> https://gist.github.com/zgordon/e837e29f77c343d29ebb7290a1a75eea
		 */
		const advadsIconEl = el(
			'svg',
			{
				width: '24px',
				height: '24px',
				viewBox: '1.396 3276 24 24',
				xmlns: 'http://www.w3.org/2000/svg',
				x: '0px',
				y: '0px',
			},
			el(
				'g',
				{},
				el('path', {
					fill: '#1C1B3A',
					d: 'M18.602,3286.2v8.53H6.677v-11.925h8.53c-0.355-0.804-0.545-1.684-0.545-2.625s0.205-1.82,0.545-2.625 h-2.57H1.406v18.266l0.6,0.6l-0.6-0.6c0,2.304,1.875,4.179,4.18,4.179l0,0h7.05h11.216v-13.821 c-0.805,0.355-1.705,0.566-2.645,0.566C20.286,3286.745,19.406,3286.541,18.602,3286.2z',
				}),
				el('circle', {
					fill: '#0E75A4',
					cx: '21.206',
					cy: '3280.179',
					r: '4.18',
				})
			)
		);

		/**
		 * Register the single ad block type
		 */
		registerBlockType('advads/gblock', {
			apiVersion: 2,

			title: i18n.advads,

			icon: advadsIconEl,

			category: 'common',

			attributes: {
				className: {
					type: 'string',
					default: '',
				},
				itemID: {
					type: 'string',
					default: '',
				},
				width: {
					type: 'string',
					default: '',
				},
				height: {
					type: 'string',
					default: '',
				},
				align: {
					type: 'string',
					default: 'default',
				},
			},

			// todo: make the keywords translatable
			keywords: ['advert', 'adsense', 'banner'],

			edit: (props) => {
				const itemID = props.attributes.itemID;

				/**
				 * Update itemID
				 *
				 * @param {Event} event change event on the select input.
				 */
				function setID(event) {
					props.setAttributes({
						itemID: event.target.querySelector('option:checked')
							.value,
					});
				}

				/**
				 * Update width
				 *
				 * @param {Event} event change event on the number input.
				 */
				function setWidth(event) {
					props.setAttributes({ width: event.target.value });
				}

				/**
				 * Update height
				 *
				 * @param {Event} event change event on the number input.
				 */
				function setHeight(event) {
					props.setAttributes({ height: event.target.value });
				}

				/**
				 * Request hints related to the item.
				 *
				 * @param {string} ID Item ID.
				 */
				function requestHints(ID) {
					if (!ID || 0 !== ID.indexOf('group_')) {
						setHints([]);

						return;
					}

					const data = new FormData();
					data.append('action', 'advads-get-block-hints');
					data.append('nonce', window.advadsglobal.ajax_nonce);
					data.append('itemID', itemID);

					fetch(window.ajaxurl, {
						method: 'POST',
						credentials: 'same-origin',
						body: data,
					})
						.then((response) => response.json())
						.then((json) => {
							if (json.success) {
								setHints(json.data);
							}
						})
						.catch((error) => {
							// eslint-disable-next-line no-console -- Might help experienced users
							console.info(error);
						});
				}

				function createSizeInputs(label, name, onchange) {
					const randomID =
						'advanced-ads-size' +
						(Math.random() + 1).toString(36).substring(1);
					return el(
						'div',
						{ className: 'size-group' },
						el(
							'label',
							{ htmlFor: randomID },
							el('span', { className: 'head' }, label)
						),
						el(
							'div',
							{ className: 'size-input' },
							el('input', {
								type: 'number',
								inputMode: 'numeric',
								id: randomID,
								value: props.attributes[name],
								name,
								min: 0,
								max: Infinity,
								step: 1,
								onChange: onchange,
							}),
							el('span', { className: 'suffix' }, 'px')
						)
					);
				}

				const [hints, setHints] = window.wp.element.useState([]);
				window.wp.element.useEffect(() => {
					requestHints(itemID);
				}, [itemID]);

				// the form children elements
				const children = [];

				// argument list (in array form) for the children creation
				const args = [],
					ads = [],
					groups = [],
					placements = [];

				args.push('select');
				args.push({
					value: props.attributes.itemID,
					onChange: setID,
					key: 'select',
				});
				args.push(el('option', { key: 'empty' }, i18n['--empty--']));

				for (const adID in aaGut.ads) {
					if (typeof aaGut.ads[adID].id === 'undefined') {
						continue;
					}
					ads.push(
						el(
							'option',
							{
								value: 'ad_' + aaGut.ads[adID].id,
								key: adID,
							},
							aaGut.ads[adID].title
						)
					);
				}

				for (const GID in aaGut.groups) {
					if ('undefined' === typeof aaGut.groups[GID].id) {
						continue;
					}
					groups.push(
						el(
							'option',
							{
								value: 'group_' + aaGut.groups[GID].id,
								key: GID,
							},
							aaGut.groups[GID].name
						)
					);
				}

				if (aaGut.placements) {
					for (const pid in aaGut.placements) {
						if ('undefined' === typeof aaGut.placements[pid].id) {
							continue;
						}
						placements.push(
							el(
								'option',
								{
									value: 'place_' + aaGut.placements[pid].id,
									key: pid,
								},
								aaGut.placements[pid].name
							)
						);
					}
				}

				if (aaGut.placements) {
					args.push(
						el(
							'optgroup',
							{
								label: i18n.placements,
								key: 'placements',
							},
							placements
						)
					);
				}

				args.push(
					el(
						'optgroup',
						{
							label: i18n.adGroups,
							key: 'adGroups',
						},
						groups
					)
				);

				args.push(el('optgroup', { label: i18n.ads, key: 'ads' }, ads));

				// add a <label /> first and style it.
				children.push(
					el(
						'div',
						{
							className: 'components-placeholder__label',
							key: 'components-placeholder__label',
						},
						advadsIconEl,
						el(
							'label',
							{ style: { display: 'block' } },
							i18n.advads
						)
					)
				);

				if (itemID && i18n['--empty--'] !== itemID) {
					let url = '#';
					if (0 === itemID.indexOf('place_')) {
						url = aaGut.editLinks.placement;
					} else if (0 === itemID.indexOf('group_')) {
						url = aaGut.editLinks.group;
					} else if (0 === itemID.indexOf('ad_')) {
						url = aaGut.editLinks.ad.replace(
							'%ID%',
							itemID.substr(3)
						);
					}

					children.push(
						el(
							'div',
							{
								className: 'components-placeholder__fieldset',
								key: 'components-placeholder__fieldset',
							},
							// then add the <select /> input with its own children
							el.apply(null, args),
							el('a', {
								className: 'dashicons dashicons-external',
								style: {
									margin: 5,
								},
								href: url,
								target: '_blank',
							})
						)
					);

					hints.forEach(function (item, index) {
						children.push(
							el(
								RawHTML,
								{
									key: index,
									className:
										'advads-block-hint advads-notice-inline advads-error',
								},
								safeHTML(item)
							)
						);
					});
				} else {
					children.push(el.apply(null, args));
				}

				if (!aaGut.ads.length) {
					children.push(
						el(
							'div',
							{
								className: 'components-placeholder__label',
								key: 'components-placeholder__label',
							},
							'',
							el(
								'a',
								{
									href: window.advadsglobal.create_ad_url,
									class: 'button',
									target: '_blank',
									style: {
										display: 'block',
										'margin-top': '10px',
									},
								},
								window.advadsglobal.create_your_first_ad
							)
						)
					);
				}

				const sizePanel = el(
					'div',
					{ id: 'advanced-ads-size-wrap' },
					el(
						'div',
						null,
						createSizeInputs(i18n.width, 'width', setWidth),
						createSizeInputs(i18n.height, 'height', setHeight)
					)
				);

				const sidebar = el(
					editor.InspectorControls,
					{ key: 'advads-sidebar' },
					el(
						comp.PanelBody,
						{
							title: i18n.size,
							initialOpen: true,
						},
						sizePanel
					)
				);

				children.push(sidebar);

				const alignmentItems = [];

				for (const slug in textFlow) {
					const isSelected = props.attributes.align === slug;
					alignmentItems.push(
						el(
							comp.MenuItem,
							{
								key: slug,
								label: textFlow[slug].label,
								onClick: () =>
									props.setAttributes({ align: slug }),
								isSelected,
							},
							el(
								'div',
								{
									className:
										'text-flow-wrap' +
										(isSelected ? ' current' : ''),
								},
								el(
									'div',
									{
										className: 'text-flow-icon',
									},
									el('img', {
										src: `${aaGut.imagesUrl}${slug}.png`,
										alt: slug,
										title: textFlow[slug].label,
										className: 'standard',
									}),
									el('img', {
										src: `${aaGut.imagesUrl}${slug}-alt.png`,
										alt: slug,
										title: textFlow[slug].label,
										className: 'alternate',
									})
								),
								el(
									'div',
									{
										className: 'text-flow-label',
										title: textFlow[slug].description,
									},
									el('span', {}, textFlow[slug].label)
								)
							)
						)
					);
				}

				const toolBar = el(
					editor.BlockControls,
					{
						key: 'advads-toolbar',
						group: 'block',
					},
					el(
						comp.ToolbarGroup,
						{
							title: 'Alignment',
						},
						el(comp.ToolbarDropdownMenu, {
							icon: 'editor-alignleft',
							label: 'Choose an alignment',
							children: () =>
								el(
									'div',
									{ className: 'advads-align-dropdown' },
									alignmentItems
								),
						})
					)
				);

				// return the complete form
				return el(
					'div',
					editor.useBlockProps(),
					el(
						'form',
						{
							className: 'components-placeholder is-large',
						},
						children
					),
					toolBar
				);
			},

			save: () => {
				// server side rendering
				return null;
			},

			// Transforms legacy widget to Advanced Ads block.
			transforms: {
				from: [
					{
						type: 'block',
						blocks: ['core/legacy-widget'],
						isMatch: (attributes) => {
							if (
								!attributes.instance ||
								!attributes.instance.raw
							) {
								// Can't transform if raw instance is not shown in REST API.
								return false;
							}
							return attributes.idBase === 'advads_ad_widget';
						},
						transform: (attributes) => {
							const instance = attributes.instance.raw;
							const transformedBlock = wp.blocks.createBlock(
								'advads/gblock',
								{
									name: instance.name,
									itemID: instance.item_id,
								}
							);
							if (!instance.title) {
								return transformedBlock;
							}
							return [
								wp.blocks.createBlock('core/heading', {
									content: instance.title,
								}),
								transformedBlock,
							];
						},
					},
				],
			},
		});
	}
})();
