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
	];

	function getPresetSvg( key ) {
		const found = PRESETS.find( function ( p ) { return p.value === key; } );
		return found ? found.svg : PRESETS[0].svg;
	}

	registerBlockType( 'hmpro/feature-item', {
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const { layout, align, iconMode, iconPreset, customSvg, iconSize, title, text, linkUrl, linkLabel } = attributes;

			const blockProps = useBlockProps( {
				className: [
					'hmpro-feature-item',
					'is-layout-' + layout,
					'is-align-' + align
				].join( ' ' ),
				style: { '--hmpro-fi-icon': ( iconSize || 28 ) + 'px' }
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
