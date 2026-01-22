( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { __ } = wp.i18n;
	const { InspectorControls, RichText, useBlockProps } = wp.blockEditor;
	const {
		PanelBody,
		SelectControl,
		RangeControl,
		ToggleControl,
		ColorPalette,
		BaseControl,
		Notice,
		Button,
		ButtonGroup,
	} = wp.components;

	function clamp( n, min, max ) {
		n = parseFloat( n );
		if ( ! Number.isFinite( n ) ) return min;
		return Math.max( min, Math.min( max, n ) );
	}

	function ensureColumnsArray( columns, count ) {
		const arr = Array.isArray( columns ) ? columns.slice( 0 ) : [];
		while ( arr.length < count ) {
			arr.push( { heading: 'Column Title', body: '<p>Write your text here…</p>' } );
		}
		return arr.slice( 0, count );
	}

	function presetWidths( preset, count ) {
		if ( count === 1 ) return [ 100 ];
		if ( count === 2 ) {
			if ( preset === '60_40' ) return [ 60, 40 ];
			if ( preset === '40_60' ) return [ 40, 60 ];
			return [ 50, 50 ];
		}
		if ( count === 3 ) {
			if ( preset === '50_25_25' ) return [ 50, 25, 25 ];
			if ( preset === '25_50_25' ) return [ 25, 50, 25 ];
			if ( preset === '25_25_50' ) return [ 25, 25, 50 ];
			return [ 34, 33, 33 ];
		}
		// 4
		if ( preset === '40_20_20_20' ) return [ 40, 20, 20, 20 ];
		return [ 25, 25, 25, 25 ];
	}

	function normalizeWidths( widths, count ) {
		const arr = Array.isArray( widths ) ? widths.slice( 0 ) : [];
		while ( arr.length < count ) arr.push( Math.floor( 100 / count ) );
		const w = arr.slice( 0, count ).map( (x) => clamp( x, 5, 95 ) );
		let sum = w.reduce( (a,b) => a+b, 0 );
		if ( sum === 100 ) return w;
		// normalize to 100 by scaling then fixing rounding diff
		const scaled = w.map( (x) => Math.round( (x / sum) * 100 ) );
		sum = scaled.reduce( (a,b) => a+b, 0 );
		let diff = 100 - sum;
		if ( diff !== 0 ) {
			// add/subtract diff from the largest column
			let idx = 0;
			for ( let i = 1; i < scaled.length; i++ ) {
				if ( scaled[i] > scaled[idx] ) idx = i;
			}
			scaled[idx] = clamp( scaled[idx] + diff, 5, 95 );
		}
		return scaled;
	}

	function applyWidthChange( widths, idx, nextVal ) {
		const w = widths.slice( 0 );
		const count = w.length;
		const next = clamp( nextVal, 5, 95 );
		const prev = w[idx];
		if ( next === prev ) return w;

		// distribute delta across others proportionally
		const delta = next - prev;
		w[idx] = next;
		const others = [];
		for ( let i = 0; i < count; i++ ) if ( i !== idx ) others.push( i );
		const othersSum = others.reduce( (a,i) => a + w[i], 0 );
		if ( othersSum <= 0 ) {
			const each = Math.floor( (100 - next) / (count - 1) );
			others.forEach( (i) => w[i] = each );
			return normalizeWidths( w, count );
		}
		others.forEach( (i) => {
			const share = w[i] / othersSum;
			w[i] = clamp( w[i] - (delta * share), 5, 95 );
		} );
		return normalizeWidths( w, count );
	}

	registerBlockType( 'hmpro/text-panel', {
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const {
				columnsCount,
				layoutPreset,
				columnWidths,
				columnGap,
				stackOnMobile,
				textAlign,
				titleSize,
				titleWeight,
				bodySize,
				lineHeight,
				textColor,
				linkColor,
				linkHoverColor,
				panelEnabled,
				panelColor,
				panelOpacity,
				panelBlur,
				panelRadius,
				panelPadding,
				panelBorder,
				panelBorderColor,
				columns,
			} = attributes;

			const count = clamp( columnsCount, 1, 4 );
			const safeCols = ensureColumnsArray( columns, count );
			const safeWidths = normalizeWidths( columnWidths, count );

			if ( safeCols !== columns ) {
				setAttributes( { columns: safeCols } );
			}
			if ( safeWidths.join(',') !== normalizeWidths( columnWidths, count ).join(',') ) {
				// keep attribute normalized
				setAttributes( { columnWidths: safeWidths } );
			}

			const styleVars = {
				'--hm-tp-gap': clamp( columnGap, 0, 80 ) + 'px',
				'--hm-tp-title-size': clamp( titleSize, 12, 64 ) + 'px',
				'--hm-tp-title-weight': clamp( titleWeight, 300, 900 ),
				'--hm-tp-body-size': clamp( bodySize, 12, 28 ) + 'px',
				'--hm-tp-line': clamp( lineHeight, 1.1, 2.2 ),
				'--hm-tp-text': textColor || '',
				'--hm-tp-link': linkColor || '',
				'--hm-tp-link-hover': linkHoverColor || '',
				'--hm-tp-panel-opacity': clamp( panelOpacity, 0, 100 ) / 100,
				'--hm-tp-panel-blur': clamp( panelBlur, 0, 20 ) + 'px',
				'--hm-tp-panel-radius': clamp( panelRadius, 0, 40 ) + 'px',
				'--hm-tp-panel-pad': clamp( panelPadding, 0, 60 ) + 'px',
				'--hm-tp-panel-color': panelColor || '',
				'--hm-tp-panel-border': panelBorder ? '1px solid ' + ( panelBorderColor || 'rgba(0,0,0,0.10)' ) : '0',
			};

			const classes = [
				'hmpro-block',
				'hmpro-text-panel',
				panelEnabled ? 'is-panel' : '',
				stackOnMobile ? 'is-stack-mobile' : '',
				'is-align-' + textAlign,
			].filter(Boolean).join(' ');

			const blockProps = useBlockProps( { className: classes, style: styleVars } );

			const presetOptionsByCount = () => {
				if ( count === 1 ) return [ { label: 'Single', value: 'equal' } ];
				if ( count === 2 ) return [
					{ label: 'Equal (50/50)', value: 'equal' },
					{ label: '60 / 40', value: '60_40' },
					{ label: '40 / 60', value: '40_60' },
				];
				if ( count === 3 ) return [
					{ label: 'Equal', value: 'equal' },
					{ label: '50 / 25 / 25', value: '50_25_25' },
					{ label: '25 / 50 / 25', value: '25_50_25' },
					{ label: '25 / 25 / 50', value: '25_25_50' },
				];
				return [
					{ label: 'Equal', value: 'equal' },
					{ label: '40 / 20 / 20 / 20', value: '40_20_20_20' },
				];
			};

			function setCount( nextCount ) {
				const c = clamp( nextCount, 1, 4 );
				const widths = presetWidths( 'equal', c );
				const cols = ensureColumnsArray( safeCols, c );
				setAttributes( { columnsCount: c, layoutPreset: 'equal', columnWidths: widths, columns: cols } );
			}

			function applyPreset( preset ) {
				const widths = presetWidths( preset, count );
				setAttributes( { layoutPreset: preset, columnWidths: widths } );
			}

			return wp.element.createElement(
				wp.element.Fragment,
				{},
				wp.element.createElement(
					InspectorControls,
					{},
					wp.element.createElement(
						PanelBody,
						{ title: __( 'Layout', 'hm-pro-theme' ), initialOpen: true },
						wp.element.createElement(
							BaseControl,
							{ label: __( 'Columns', 'hm-pro-theme' ) },
							wp.element.createElement(
								ButtonGroup,
								{},
								[1,2,3,4].map( (n) =>
									wp.element.createElement(
										Button,
										{
											key: n,
											isPrimary: count === n,
											isSecondary: count !== n,
											onClick: () => setCount( n )
										},
										String( n )
									)
								)
							)
						),
						wp.element.createElement(
							SelectControl,
							{
								label: __( 'Width Preset', 'hm-pro-theme' ),
								value: layoutPreset,
								options: presetOptionsByCount(),
								onChange: (v) => applyPreset( v ),
							}
						),
						wp.element.createElement(
							RangeControl,
							{
								label: __( 'Column Gap (px)', 'hm-pro-theme' ),
								value: columnGap,
								min: 0,
								max: 80,
								step: 1,
								onChange: (v) => setAttributes( { columnGap: v } ),
							}
						),
						wp.element.createElement(
							ToggleControl,
							{
								label: __( 'Stack on Mobile', 'hm-pro-theme' ),
								checked: !!stackOnMobile,
								onChange: (v) => setAttributes( { stackOnMobile: !!v } ),
							}
						),
						wp.element.createElement(
							SelectControl,
							{
								label: __( 'Text Align', 'hm-pro-theme' ),
								value: textAlign,
								options: [
									{ label: 'Left', value: 'left' },
									{ label: 'Center', value: 'center' },
									{ label: 'Right', value: 'right' },
								],
								onChange: (v) => setAttributes( { textAlign: v } ),
							}
						),
						wp.element.createElement(
							BaseControl,
							{ label: __( 'Column Widths (%)', 'hm-pro-theme' ) },
							wp.element.createElement(
								Notice,
								{ status: 'info', isDismissible: false },
								__( 'Adjust widths. Total stays at 100%.', 'hm-pro-theme' )
							),
							safeWidths.map( (w, i) =>
								wp.element.createElement( RangeControl, {
									key: i,
									label: __( 'Column ', 'hm-pro-theme' ) + ( i + 1 ),
									value: Math.round(w),
									min: 5,
									max: 95,
									step: 1,
									onChange: (v) => {
										const next = applyWidthChange( safeWidths, i, v );
										setAttributes( { columnWidths: next } );
									},
								} )
							)
						)
					),
					wp.element.createElement(
						PanelBody,
						{ title: __( 'Typography', 'hm-pro-theme' ), initialOpen: false },
						wp.element.createElement( RangeControl, {
							label: __( 'Title Size (px)', 'hm-pro-theme' ),
							value: titleSize, min: 12, max: 64, step: 1,
							onChange: (v) => setAttributes( { titleSize: v } ),
						} ),
						wp.element.createElement( RangeControl, {
							label: __( 'Title Weight', 'hm-pro-theme' ),
							value: titleWeight, min: 300, max: 900, step: 50,
							onChange: (v) => setAttributes( { titleWeight: v } ),
						} ),
						wp.element.createElement( RangeControl, {
							label: __( 'Body Size (px)', 'hm-pro-theme' ),
							value: bodySize, min: 12, max: 28, step: 1,
							onChange: (v) => setAttributes( { bodySize: v } ),
						} ),
						wp.element.createElement( RangeControl, {
							label: __( 'Line Height', 'hm-pro-theme' ),
							value: lineHeight, min: 1.1, max: 2.2, step: 0.05,
							onChange: (v) => setAttributes( { lineHeight: v } ),
						} )
					),
					wp.element.createElement(
						PanelBody,
						{ title: __( 'Colors', 'hm-pro-theme' ), initialOpen: false },
						wp.element.createElement( BaseControl, { label: __( 'Text Color', 'hm-pro-theme' ) },
							wp.element.createElement( ColorPalette, {
								value: textColor,
								onChange: (v) => setAttributes( { textColor: v || '' } ),
							} )
						),
						wp.element.createElement( BaseControl, { label: __( 'Link Color', 'hm-pro-theme' ) },
							wp.element.createElement( ColorPalette, {
								value: linkColor,
								onChange: (v) => setAttributes( { linkColor: v || '' } ),
							} )
						),
						wp.element.createElement( BaseControl, { label: __( 'Link Hover Color', 'hm-pro-theme' ) },
							wp.element.createElement( ColorPalette, {
								value: linkHoverColor,
								onChange: (v) => setAttributes( { linkHoverColor: v || '' } ),
							} )
						)
					),
					wp.element.createElement(
						PanelBody,
						{ title: __( 'Backdrop Panel', 'hm-pro-theme' ), initialOpen: false },
						wp.element.createElement( ToggleControl, {
							label: __( 'Enable Panel', 'hm-pro-theme' ),
							checked: !!panelEnabled,
							onChange: (v) => setAttributes( { panelEnabled: !!v } ),
						} ),
						panelEnabled && wp.element.createElement(
							wp.element.Fragment,
							{},
							wp.element.createElement( BaseControl, { label: __( 'Panel Color', 'hm-pro-theme' ) },
								wp.element.createElement( ColorPalette, {
									value: panelColor,
									onChange: (v) => setAttributes( { panelColor: v || '' } ),
								} )
							),
							wp.element.createElement( RangeControl, {
								label: __( 'Opacity (%)', 'hm-pro-theme' ),
								value: panelOpacity, min: 0, max: 100, step: 1,
								onChange: (v) => setAttributes( { panelOpacity: v } ),
							} ),
							wp.element.createElement( RangeControl, {
								label: __( 'Blur (px)', 'hm-pro-theme' ),
								value: panelBlur, min: 0, max: 20, step: 1,
								onChange: (v) => setAttributes( { panelBlur: v } ),
							} ),
							wp.element.createElement( RangeControl, {
								label: __( 'Radius (px)', 'hm-pro-theme' ),
								value: panelRadius, min: 0, max: 40, step: 1,
								onChange: (v) => setAttributes( { panelRadius: v } ),
							} ),
							wp.element.createElement( RangeControl, {
								label: __( 'Padding (px)', 'hm-pro-theme' ),
								value: panelPadding, min: 0, max: 60, step: 1,
								onChange: (v) => setAttributes( { panelPadding: v } ),
							} ),
							wp.element.createElement( ToggleControl, {
								label: __( 'Border', 'hm-pro-theme' ),
								checked: !!panelBorder,
								onChange: (v) => setAttributes( { panelBorder: !!v } ),
							} ),
							panelBorder && wp.element.createElement( BaseControl, { label: __( 'Border Color', 'hm-pro-theme' ) },
								wp.element.createElement( ColorPalette, {
									value: panelBorderColor,
									onChange: (v) => setAttributes( { panelBorderColor: v || 'rgba(0,0,0,0.10)' } ),
								} )
							)
						)
					)
				),
				wp.element.createElement(
					'section',
					blockProps,
					wp.element.createElement(
						'div',
						{ className: 'hmpro-tp__inner' + ( panelEnabled ? ' hmpro-tp__panel' : '' ) },
						wp.element.createElement(
							'div',
							{
								className: 'hmpro-tp__grid',
								style: {
									gridTemplateColumns: safeWidths.map( (w) => w + 'fr' ).join(' '),
								}
							},
							safeCols.map( (col, i) =>
								wp.element.createElement(
									'div',
									{ key: i, className: 'hmpro-tp__col' },
									wp.element.createElement( RichText, {
										tagName: 'h3',
										className: 'hmpro-tp__title',
										value: col.heading || '',
										placeholder: __( 'Column title…', 'hm-pro-theme' ),
										allowedFormats: [],
										onChange: (v) => {
											const next = safeCols.slice(0);
											next[i] = Object.assign( {}, next[i], { heading: v } );
											setAttributes( { columns: next } );
										}
									} ),
									wp.element.createElement( RichText, {
										tagName: 'div',
										className: 'hmpro-tp__body',
										value: col.body || '',
										placeholder: __( 'Write text…', 'hm-pro-theme' ),
										allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ],
										multiline: 'p',
										onChange: (v) => {
											const next = safeCols.slice(0);
											next[i] = Object.assign( {}, next[i], { body: v } );
											setAttributes( { columns: next } );
										}
									} )
								)
							)
						)
					)
				)
			);
		},
		save: function () {
			// Dynamic render (PHP).
			return null;
		}
	} );
} )( window.wp );
