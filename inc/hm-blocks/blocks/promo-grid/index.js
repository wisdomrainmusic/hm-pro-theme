( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { __ } = wp.i18n;
	const {
		InspectorControls,
		MediaUpload,
		MediaUploadCheck,
		useBlockProps,
	} = wp.blockEditor;
	const {
		PanelBody,
		ToggleControl,
		SelectControl,
		TextControl,
		TextareaControl,
		RangeControl,
		Button,
		ButtonGroup,
		Notice,
	} = wp.components;

	const PRESETS = [
		{ label: 'Two Equal', value: 'two_equal' },
		{ label: 'Two 70/30', value: 'two_split_70_30' },
		{ label: 'Two 30/70', value: 'two_split_30_70' },
		{ label: 'Three Equal', value: 'three_equal' },
		{ label: '3 Mosaic Left', value: 'three_mosaic_left' },
		{ label: '3 Mosaic Right', value: 'three_mosaic_right' },
		{ label: 'Four Checker', value: 'four_checker' },
		{ label: '4 Mosaic Left', value: 'four_mosaic_left' },
		{ label: '4 Mosaic Right', value: 'four_mosaic_right' },
		{ label: 'Six Grid', value: 'six_grid' },
	];

	const TILE_LIMITS = {
		two_equal: 2,
		two_split_70_30: 2,
		two_split_30_70: 2,
		three_equal: 3,
		three_mosaic_left: 3,
		three_mosaic_right: 3,
		four_checker: 4,
		four_mosaic_left: 4,
		four_mosaic_right: 4,
		six_grid: 6,
	};

	const POSITIONS = [
		{ label: 'Bottom Left', value: 'bottom-left' },
		{ label: 'Bottom Center', value: 'bottom-center' },
		{ label: 'Bottom Right', value: 'bottom-right' },
		{ label: 'Center Left', value: 'center-left' },
		{ label: 'Center', value: 'center' },
		{ label: 'Center Right', value: 'center-right' },
		{ label: 'Top Left', value: 'top-left' },
		{ label: 'Top Center', value: 'top-center' },
		{ label: 'Top Right', value: 'top-right' },
	];

	const HOVER = [
		{ label: 'None', value: 'none' },
		{ label: 'Zoom', value: 'zoom' },
		{ label: 'Dim', value: 'dim' },
	];

	function normalizeTiles( tiles, maxTiles ) {
		const arr = Array.isArray( tiles ) ? tiles.slice( 0 ) : [];
		if ( arr.length > maxTiles ) {
			return arr.slice( 0, maxTiles );
		}
		return arr;
	}

	function createEmptyTile() {
		return {
			show: true,
			imageId: 0,
			imageUrl: '',
			title: '',
			subtitle: '',
			buttonText: '',
			linkUrl: '',
			newTab: false,
			nofollow: false,
			overlay: true,
			position: 'bottom-left',
			offsetX: 0,
			offsetY: 0,
			contentMaxWidth: 520,
			contentPadding: 18
		};
	}

	registerBlockType( 'hmpro/promo-grid', {
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const {
				preset,
				fullWidth,
				fixedHeight,
				containerMinHeight,
				containerHeight,
				gridGap,
				tileMinHeight,
				hoverEffect,
				mediaScale,
				overlayOpacity,
				tiles,
			} = attributes;

			const maxTiles = TILE_LIMITS[ preset ] || 6;
			const safeTiles = normalizeTiles( tiles, maxTiles );

			// Keep tiles trimmed when preset changes.
			if ( safeTiles.length !== ( Array.isArray( tiles ) ? tiles.length : 0 ) ) {
				setAttributes( { tiles: safeTiles } );
			}

			const blockProps = useBlockProps( {
				className: [
					'hmpro-block',
					'hmpro-promo-grid',
					fullWidth ? 'hmpro-pg--fullwidth' : '',
					fixedHeight ? 'hmpro-pg--fixed-height' : '',
					hoverEffect && hoverEffect !== 'none' ? ( 'hmpro-pg--hover-' + hoverEffect ) : '',
				].filter( Boolean ).join( ' ' ),
			} );

			function updateTile( index, patch ) {
				const next = safeTiles.slice( 0 );
				next[ index ] = Object.assign( {}, next[ index ] || createEmptyTile(), patch );
				setAttributes( { tiles: next } );
			}

			function removeTile( index ) {
				const next = safeTiles.slice( 0 );
				next.splice( index, 1 );
				setAttributes( { tiles: next } );
			}

			function addTile() {
				const next = safeTiles.slice( 0 );
				if ( next.length >= maxTiles ) {
					return;
				}
				next.push( createEmptyTile() );
				setAttributes( { tiles: next } );
			}

			const previewStyle = {
				'--hm-pg-gap': ( gridGap || 0 ) + 'px',
				'--hm-pg-minh': ( containerMinHeight || 0 ) + 'px',
				'--hm-pg-tile-minh': ( tileMinHeight || 0 ) + 'px',
				'--hm-pg-media-scale': mediaScale || 1,
				'--hm-pg-overlay-opacity': overlayOpacity,
			};

			return wp.element.createElement(
				wp.element.Fragment,
				null,
				wp.element.createElement(
					InspectorControls,
					null,
					wp.element.createElement(
						PanelBody,
						{ title: __( 'Layout', 'hm-pro-theme' ), initialOpen: true },
						wp.element.createElement( SelectControl, {
							label: __( 'Preset', 'hm-pro-theme' ),
							value: preset,
							options: PRESETS,
							onChange: function ( v ) {
								setAttributes( { preset: v } );
							},
						} ),
						wp.element.createElement( ToggleControl, {
							label: __( 'Full Width (bleed)', 'hm-pro-theme' ),
							checked: !! fullWidth,
							onChange: function ( v ) {
								setAttributes( { fullWidth: !! v } );
							},
						} ),
						wp.element.createElement( ToggleControl, {
							label: __( 'Fixed Height Mode', 'hm-pro-theme' ),
							checked: !! fixedHeight,
							onChange: function ( v ) {
								setAttributes( { fixedHeight: !! v } );
							},
						} ),
						fixedHeight
							? wp.element.createElement( RangeControl, {
									label: __( 'Container Height (px)', 'hm-pro-theme' ),
									min: 200,
									max: 900,
									value: containerHeight,
									onChange: function ( v ) {
										setAttributes( { containerHeight: v || 520 } );
									},
							  } )
							: null,
						wp.element.createElement( RangeControl, {
							label: __( 'Min Height (px)', 'hm-pro-theme' ),
							min: 200,
							max: 900,
							value: containerMinHeight,
							onChange: function ( v ) {
								setAttributes( { containerMinHeight: v || 360 } );
							},
						} )
					),
					wp.element.createElement(
						PanelBody,
						{ title: __( 'Grid', 'hm-pro-theme' ), initialOpen: false },
						wp.element.createElement( RangeControl, {
							label: __( 'Grid Gap (px)', 'hm-pro-theme' ),
							min: 0,
							max: 80,
							value: gridGap,
							onChange: function ( v ) {
								setAttributes( { gridGap: v || 0 } );
							},
						} ),
						wp.element.createElement( RangeControl, {
							label: __( 'Tile Min Height (px)', 'hm-pro-theme' ),
							min: 100,
							max: 900,
							value: tileMinHeight,
							onChange: function ( v ) {
								setAttributes( { tileMinHeight: v || 0 } );
							},
						} )
					),
					wp.element.createElement(
						PanelBody,
						{ title: __( 'Effects', 'hm-pro-theme' ), initialOpen: false },
						wp.element.createElement( SelectControl, {
							label: __( 'Hover Effect', 'hm-pro-theme' ),
							value: hoverEffect,
							options: HOVER,
							onChange: function ( v ) {
								setAttributes( { hoverEffect: v } );
							},
						} ),
						wp.element.createElement( RangeControl, {
							label: __( 'Media Scale', 'hm-pro-theme' ),
							min: 1,
							max: 1.3,
							step: 0.05,
							value: mediaScale,
							onChange: function ( v ) {
								setAttributes( { mediaScale: v || 1 } );
							},
						} ),
						wp.element.createElement( RangeControl, {
							label: __( 'Overlay Opacity', 'hm-pro-theme' ),
							min: 0,
							max: 0.85,
							step: 0.05,
							value: overlayOpacity,
							onChange: function ( v ) {
								setAttributes( { overlayOpacity: ( typeof v === 'number' ? v : 0.35 ) } );
							},
						} )
					),
					wp.element.createElement(
						PanelBody,
						{ title: __( 'Tiles', 'hm-pro-theme' ), initialOpen: true },
						wp.element.createElement(
							Notice,
							{ status: 'info', isDismissible: false },
							__( 'Tip: Add images first. Preset controls how many tiles are used.', 'hm-pro-theme' )
						),
						wp.element.createElement(
							'div',
							{ className: 'hmpro-pg-editor__tiles' },
							safeTiles.map( function ( tile, idx ) {
								const t = Object.assign( createEmptyTile(), tile || {} );
								return wp.element.createElement(
									'div',
									{ key: idx, className: 'hmpro-pg-editor__tile' },
									wp.element.createElement(
										'div',
										{ className: 'hmpro-pg-editor__tile-head' },
										wp.element.createElement(
											'strong',
											null,
											( t.title && t.title.trim() ) ? t.title : ( 'Tile ' + ( idx + 1 ) )
										),
										wp.element.createElement(
											Button,
											{
												isDestructive: true,
												isSmall: true,
												onClick: function () { removeTile( idx ); }
											},
											__( 'Remove', 'hm-pro-theme' )
										)
									),
									wp.element.createElement( ToggleControl, {
										label: __( 'Show Tile', 'hm-pro-theme' ),
										checked: !! t.show,
										onChange: function ( v ) { updateTile( idx, { show: !! v } ); }
									} ),
									wp.element.createElement(
										MediaUploadCheck,
										null,
										wp.element.createElement( MediaUpload, {
											onSelect: function ( media ) {
												updateTile( idx, {
													imageId: media && media.id ? media.id : 0,
													imageUrl: media && media.url ? media.url : ''
												} );
											},
											allowedTypes: [ 'image' ],
											value: t.imageId || 0,
											render: function ( obj ) {
												return wp.element.createElement(
													Button,
													{ onClick: obj.open, isSecondary: true },
													t.imageUrl ? __( 'Change Image', 'hm-pro-theme' ) : __( 'Select Image', 'hm-pro-theme' )
												);
											}
										} )
									),
									t.imageUrl ? wp.element.createElement( 'div', { className: 'hmpro-pg-editor__thumb', style: { backgroundImage: 'url(' + t.imageUrl + ')' } } ) : null,
									wp.element.createElement( TextControl, {
										label: __( 'Title', 'hm-pro-theme' ),
										value: t.title,
										onChange: function ( v ) { updateTile( idx, { title: v } ); }
									} ),
									wp.element.createElement( TextareaControl, {
										label: __( 'Subtitle', 'hm-pro-theme' ),
										value: t.subtitle,
										onChange: function ( v ) { updateTile( idx, { subtitle: v } ); }
									} ),
									wp.element.createElement( TextControl, {
										label: __( 'Button Text', 'hm-pro-theme' ),
										value: t.buttonText,
										onChange: function ( v ) { updateTile( idx, { buttonText: v } ); }
									} ),
									wp.element.createElement( TextControl, {
										label: __( 'Link URL', 'hm-pro-theme' ),
										value: t.linkUrl,
										placeholder: 'https://',
										onChange: function ( v ) { updateTile( idx, { linkUrl: v } ); }
									} ),
									wp.element.createElement( ToggleControl, {
										label: __( 'Open in new tab', 'hm-pro-theme' ),
										checked: !! t.newTab,
										onChange: function ( v ) { updateTile( idx, { newTab: !! v } ); }
									} ),
									wp.element.createElement( ToggleControl, {
										label: __( 'Nofollow', 'hm-pro-theme' ),
										checked: !! t.nofollow,
										onChange: function ( v ) { updateTile( idx, { nofollow: !! v } ); }
									} ),
									wp.element.createElement( ToggleControl, {
										label: __( 'Overlay enabled', 'hm-pro-theme' ),
										checked: !! t.overlay,
										onChange: function ( v ) { updateTile( idx, { overlay: !! v } ); }
									} ),
									wp.element.createElement( SelectControl, {
										label: __( 'Content Position', 'hm-pro-theme' ),
										value: t.position,
										options: POSITIONS,
										onChange: function ( v ) { updateTile( idx, { position: v } ); }
									} ),
									wp.element.createElement( RangeControl, {
										label: __( 'Content Offset X (px)', 'hm-pro-theme' ),
										min: -120,
										max: 120,
										value: t.offsetX,
										onChange: function ( v ) { updateTile( idx, { offsetX: v || 0 } ); }
									} ),
									wp.element.createElement( RangeControl, {
										label: __( 'Content Offset Y (px)', 'hm-pro-theme' ),
										min: -120,
										max: 120,
										value: t.offsetY,
										onChange: function ( v ) { updateTile( idx, { offsetY: v || 0 } ); }
									} ),
									wp.element.createElement( RangeControl, {
										label: __( 'Content Max Width (px)', 'hm-pro-theme' ),
										min: 240,
										max: 900,
										value: t.contentMaxWidth,
										onChange: function ( v ) { updateTile( idx, { contentMaxWidth: v || 520 } ); }
									} ),
									wp.element.createElement( RangeControl, {
										label: __( 'Content Padding (px)', 'hm-pro-theme' ),
										min: 0,
										max: 48,
										value: t.contentPadding,
										onChange: function ( v ) { updateTile( idx, { contentPadding: v || 0 } ); }
									} )
								);
							} )
						),
						wp.element.createElement(
							ButtonGroup,
							null,
							wp.element.createElement(
								Button,
								{ isPrimary: true, onClick: addTile, disabled: safeTiles.length >= maxTiles },
								__( 'Add Tile', 'hm-pro-theme' )
							)
						),
						wp.element.createElement(
							'div',
							{ className: 'hmpro-pg-editor__limit' },
							__( 'Max tiles for this preset:', 'hm-pro-theme' ) + ' ' + maxTiles
						)
					)
				),
				wp.element.createElement(
					'div',
					Object.assign( {}, blockProps, { style: previewStyle, 'data-preset': preset } ),
					wp.element.createElement(
						'div',
						{ className: 'hmpro-pg__inner' },
						wp.element.createElement(
							'div',
							{ className: 'hmpro-pg__grid hmpro-pg__grid--' + preset },
							safeTiles.length
								? safeTiles.map( function ( tile, idx ) {
										const t = Object.assign( createEmptyTile(), tile || {} );
										if ( ! t.show ) return null;
										return wp.element.createElement(
											'div',
											{ key: idx, className: 'hmpro-pg__tile hmpro-pg__tile--preview' },
											t.imageUrl
												? wp.element.createElement( 'div', { className: 'hmpro-pg__media', style: { backgroundImage: 'url(' + t.imageUrl + ')' } } )
												: wp.element.createElement( 'div', { className: 'hmpro-pg__media hmpro-pg__media--empty' }, __( 'Select image', 'hm-pro-theme' ) ),
											wp.element.createElement(
												'div',
												{ className: 'hmpro-pg__overlay' },
												wp.element.createElement(
													'div',
													{ className: 'hmpro-pg__content' },
													t.title ? wp.element.createElement( 'div', { className: 'hmpro-pg__title' }, t.title ) : null,
													t.subtitle ? wp.element.createElement( 'div', { className: 'hmpro-pg__subtitle' }, t.subtitle ) : null,
													t.buttonText ? wp.element.createElement( 'span', { className: 'hmpro-pg__button' }, t.buttonText ) : null
												)
											)
										);
								  } )
								: wp.element.createElement(
										'div',
										{ className: 'hmpro-pg-editor__empty' },
										__( 'Add tiles from the right sidebar → Tiles panel.', 'hm-pro-theme' )
								  )
						)
					)
				)
			);
		},

		// Dynamic render in PHP (stable frontend HTML).
		save: function () {
			return null;
		},
	} );
} )( window.wp );
