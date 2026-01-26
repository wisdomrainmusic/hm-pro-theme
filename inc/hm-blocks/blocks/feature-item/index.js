( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { __ } = wp.i18n;
	const { InspectorControls, RichText, useBlockProps, URLInputButton } = wp.blockEditor;
	const { PanelBody, SelectControl, RangeControl, TextControl, TextareaControl } = wp.components;

	const PRESETS = [
		{ label: 'Check', value: 'check', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>' },
		{ label: 'Star', value: 'star', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.1 6.3 7 .9-5.1 5 1.2 7-6.2-3.3-6.2 3.3 1.2-7-5.1-5 7-.9z"/></svg>' },
		{ label: 'Shield', value: 'shield', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 9.4-8 10-4.5-.6-8-5-8-10V6l8-4z"/></svg>' },
		{ label: 'Bolt', value: 'bolt', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 12-14h-7z"/></svg>' },
		{ label: 'Heart', value: 'heart', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>' }
		,
		// E-commerce / utility
		{ label: 'Truck', value: 'truck', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v10H3z"/><path d="M14 10h4l3 3v4h-7z"/><circle cx="7" cy="19" r="1.5"/><circle cx="18" cy="19" r="1.5"/></svg>' },
		{ label: 'Cash', value: 'cash', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="10" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg>' },
		{ label: 'Recycle', value: 'recycle', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 19l-2-3 3-2"/><path d="M5 16a7 7 0 0 0 12 1"/><path d="M17 5l2 3-3 2"/><path d="M19 8a7 7 0 0 0-12-1"/><path d="M10 5h4l2 3"/></svg>' },
		{ label: 'Return', value: 'return', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14l-4-4 4-4"/><path d="M5 10h9a5 5 0 0 1 0 10H7"/></svg>' },
		{ label: 'Box', value: 'box', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v10l9 5 9-5V8"/><path d="M12 13v10"/></svg>' },
		{ label: 'Mail', value: 'mail', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/></svg>' },
		{ label: 'Phone', value: 'phone', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.5v3a2 2 0 0 1-2.2 2 19 19 0 0 1-8.3-3 19 19 0 0 1-6-6A19 19 0 0 1 2.5 4.2 2 2 0 0 1 4.5 2h3a2 2 0 0 1 2 1.7c.1.8.3 1.6.6 2.4a2 2 0 0 1-.5 2.1L8.5 9.3a16 16 0 0 0 6.2 6.2l1.1-1.1a2 2 0 0 1 2.1-.5c.8.3 1.6.5 2.4.6A2 2 0 0 1 22 16.5z"/></svg>' },
		{ label: 'Chat', value: 'chat', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>' },
		// Social
		{ label: 'WhatsApp', value: 'whatsapp', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5A8.5 8.5 0 0 1 7.3 19.2L4 20l.9-3.2A8.5 8.5 0 1 1 20 11.5z"/><path d="M9.2 9.1c.3 2 3 4.7 5 5 .6.1 1.2-.2 1.6-.6l.7-.7-1.6-1-.6.5c-.2.2-.4.2-.7.1-1-.4-2.2-1.6-2.6-2.6-.1-.3 0-.5.1-.7l.5-.6-1-1.6-.7.7c-.4.4-.7 1-.6 1.5z"/></svg>' },
		{ label: 'Facebook', value: 'facebook', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 8h3V5h-3a4 4 0 0 0-4 4v3H7v3h3v7h3v-7h3l1-3h-4V9a1 1 0 0 1 1-1z"/></svg>' },
		{ label: 'Instagram', value: 'instagram', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="4"/><circle cx="12" cy="12" r="3.5"/><path d="M16.5 7.5h0"/></svg>' },
		{ label: 'YouTube', value: 'youtube', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12s0-4-1-5-4-1-9-1-8 0-9 1-1 5-1 5 0 4 1 5 4 1 9 1 8 0 9-1 1-5 1-5z"/><path d="M10 9l6 3-6 3z"/></svg>' }
	];

	function getPresetSvg( key ) {
		const found = PRESETS.find( function ( p ) { return p.value === key; } );
		return found ? found.svg : PRESETS[0].svg;
	}

	registerBlockType( 'hmpro/feature-item', {
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const { layout, align, iconMode, iconPreset, customSvg, iconSize, titleSize, textSize, title, text, linkUrl, linkLabel } = attributes;

			const blockProps = useBlockProps( {
				className: [
					'hmpro-feature-item',
					'is-layout-' + layout,
					'is-align-' + align
				].join( ' ' ),
				style: {
					'--hmpro-fi-icon': ( iconSize || 28 ) + 'px',
					'--hmpro-fi-title': ( titleSize || 18 ) + 'px',
					'--hmpro-fi-text': ( textSize || 14 ) + 'px'
				}
			} );

			const iconHtml = ( iconMode === 'custom' && customSvg )
				? customSvg
				: ( iconMode === 'preset' ? getPresetSvg( iconPreset ) : '' );

			return wp.element.createElement(
				wp.element.Fragment,
				null,
				wp.element.createElement(
					InspectorControls,
					null,
					wp.element.createElement(
						PanelBody,
						{ title: __( 'Feature Item', 'hm-pro-theme' ), initialOpen: true },
						wp.element.createElement( RangeControl, {
							label: __( 'Title Font Size (px)', 'hm-pro-theme' ),
							value: titleSize,
							min: 12,
							max: 40,
							onChange: function ( v ) { setAttributes( { titleSize: v || 18 } ); }
						} ),
						wp.element.createElement( RangeControl, {
							label: __( 'Text Font Size (px)', 'hm-pro-theme' ),
							value: textSize,
							min: 10,
							max: 28,
							onChange: function ( v ) { setAttributes( { textSize: v || 14 } ); }
						} ),
						wp.element.createElement( SelectControl, {
							label: __( 'Layout', 'hm-pro-theme' ),
							value: layout,
							options: [
								{ label: 'Icon Top', value: 'top' },
								{ label: 'Icon Left', value: 'left' }
							],
							onChange: function ( v ) { setAttributes( { layout: v } ); }
						} ),
						wp.element.createElement( SelectControl, {
							label: __( 'Text Align', 'hm-pro-theme' ),
							value: align,
							options: [
								{ label: 'Left', value: 'left' },
								{ label: 'Center', value: 'center' }
							],
							onChange: function ( v ) { setAttributes( { align: v } ); }
						} ),
						wp.element.createElement( SelectControl, {
							label: __( 'Icon Mode', 'hm-pro-theme' ),
							value: iconMode,
							options: [
								{ label: 'Preset', value: 'preset' },
								{ label: 'Custom SVG', value: 'custom' },
								{ label: 'None', value: 'none' }
							],
							onChange: function ( v ) { setAttributes( { iconMode: v } ); }
						} ),
						( iconMode === 'preset' ) && wp.element.createElement( SelectControl, {
							label: __( 'Icon Preset', 'hm-pro-theme' ),
							value: iconPreset,
							options: PRESETS.map( function ( p ) { return { label: p.label, value: p.value }; } ),
							onChange: function ( v ) { setAttributes( { iconPreset: v } ); }
						} ),
						( iconMode === 'custom' ) && wp.element.createElement( TextareaControl, {
							label: __( 'Custom SVG', 'hm-pro-theme' ),
							help: __( 'Paste inline <svg>…</svg>. Script tags will be stripped on frontend.', 'hm-pro-theme' ),
							value: customSvg,
							onChange: function ( v ) { setAttributes( { customSvg: v } ); }
						} ),
						wp.element.createElement( RangeControl, {
							label: __( 'Icon Size (px)', 'hm-pro-theme' ),
							value: iconSize,
							min: 14,
							max: 96,
							onChange: function ( v ) { setAttributes( { iconSize: v || 28 } ); }
						} ),
						wp.element.createElement( TextControl, {
							label: __( 'Link Label', 'hm-pro-theme' ),
							value: linkLabel,
							onChange: function ( v ) { setAttributes( { linkLabel: v } ); }
						} ),
						wp.element.createElement( URLInputButton, {
							url: linkUrl,
							onChange: function ( url ) { setAttributes( { linkUrl: url } ); }
						} )
					)
				),
				wp.element.createElement(
					'div',
					blockProps,
					iconHtml ? wp.element.createElement( 'div', {
						className: 'hmpro-feature-item__icon',
						dangerouslySetInnerHTML: { __html: iconHtml }
					} ) : null,
					wp.element.createElement(
						'div',
						{ className: 'hmpro-feature-item__content' },
						wp.element.createElement( RichText, {
							tagName: 'h3',
							className: 'hmpro-feature-item__title',
							value: title,
							placeholder: __( 'Feature title…', 'hm-pro-theme' ),
							onChange: function ( v ) { setAttributes( { title: v } ); }
						} ),
						wp.element.createElement( RichText, {
							tagName: 'p',
							className: 'hmpro-feature-item__text',
							value: text,
							placeholder: __( 'Feature description…', 'hm-pro-theme' ),
							onChange: function ( v ) { setAttributes( { text: v } ); }
						} ),
						( linkUrl && linkLabel ) ? wp.element.createElement(
							'a',
							{ className: 'hmpro-feature-item__link', href: linkUrl, onClick: function ( e ) { e.preventDefault(); } },
							linkLabel
						) : null
					)
				)
			);
		},

		save: function () {
			return null;
		}
	} );
} )( window.wp );
