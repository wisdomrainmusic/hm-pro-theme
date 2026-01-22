( function ( wp ) {
  const { registerBlockType } = wp.blocks;
  const { __ } = wp.i18n;
  const { InspectorControls, useBlockProps } = wp.blockEditor;
  const {
    PanelBody,
    ToggleControl,
    RangeControl,
    SelectControl,
    TextControl,
    Button,
    ButtonGroup,
    BaseControl,
    ColorPalette,
    Notice,
  } = wp.components;

  function normalizeInt( val, fallback ) {
    const n = parseInt( String( val ).replace( ',', '.' ), 10 );
    return Number.isFinite( n ) ? n : fallback;
  }

  function createEmptyTab() {
    return {
      tabTitle: 'New Arrivals',
      queryType: 'category', // category|tag
      categoryId: 0,
      tagId: 0,
      productsPerTab: 12
    };
  }

  function clamp( n, min, max ) {
    return Math.max( min, Math.min( max, n ) );
  }

  registerBlockType( 'hmpro/product-full-tabs', {
    edit: function ( props ) {
      const { attributes, setAttributes } = props;
      const {
        columnsPerView,
        fullBleed,
        tabs,
        tabsAlignment,
        boxBg,
        boxRadius,
        boxPadding,
        tabBg,
        tabColor,
        tabBgActive,
        tabColorActive,
        titleColor,
        priceColor,
      } = attributes;

      const safeTabs = Array.isArray( tabs ) ? tabs.slice( 0 ) : [];

      function updateTab( index, patch ) {
        const next = safeTabs.slice( 0 );
        next[ index ] = Object.assign( {}, next[ index ] || createEmptyTab(), patch );
        setAttributes( { tabs: next } );
      }

      function addTab() {
        const next = safeTabs.slice( 0 );
        next.push( createEmptyTab() );
        setAttributes( { tabs: next } );
      }

      function removeTab( index ) {
        const next = safeTabs.slice( 0 );
        next.splice( index, 1 );
        setAttributes( { tabs: next } );
      }

      const blockProps = useBlockProps( {
        className: [
          'hmpro-block',
          'hmpro-product-full-tabs',
          fullBleed ? 'hm-pft--fullbleed' : ''
        ].filter(Boolean).join(' ')
      } );

      const chips = safeTabs.length ? safeTabs.map( (t, i) => ( t && t.tabTitle ? t.tabTitle : ('Tab ' + (i+1)) ) ) : [];

      return [
        wp.element.createElement(
          InspectorControls,
          { key: 'inspector' },
          wp.element.createElement(
            PanelBody,
            { title: __( 'Tabs', 'hmpro' ), initialOpen: true },
            wp.element.createElement(
              SelectControl,
              {
                label: __( 'Cards Per View (Desktop)', 'hmpro' ),
                value: String( columnsPerView || 4 ),
                options: [
                  { label: '3', value: '3' },
                  { label: '4', value: '4' },
                ],
                onChange: ( v ) => setAttributes( { columnsPerView: normalizeInt( v, 4 ) } ),
              }
            ),
            wp.element.createElement(
              ToggleControl,
              {
                label: __( 'Full Width (Hero Like)', 'hmpro' ),
                checked: !! fullBleed,
                onChange: ( v ) => setAttributes( { fullBleed: !! v } )
              }
            ),
            wp.element.createElement(
              BaseControl,
              { label: __( 'Product Tabs', 'hmpro' ) },
              safeTabs.length ? null : wp.element.createElement(
                Notice,
                { status: 'info', isDismissible: false },
                __( 'Add at least one tab.', 'hmpro' )
              )
            ),
            safeTabs.map( ( t, index ) => {
              const tab = Object.assign( {}, createEmptyTab(), t || {} );
              return wp.element.createElement(
                PanelBody,
                { key: 'tab-'+index, title: tab.tabTitle || ('Tab ' + (index+1)), initialOpen: false },
                wp.element.createElement(
                  TextControl,
                  {
                    label: __( 'Tab Title', 'hmpro' ),
                    value: tab.tabTitle || '',
                    onChange: ( v ) => updateTab( index, { tabTitle: v } ),
                  }
                ),
                wp.element.createElement(
                  SelectControl,
                  {
                    label: __( 'Query Type', 'hmpro' ),
                    value: tab.queryType || 'category',
                    options: [
                      { label: __( 'By Category', 'hmpro' ), value: 'category' },
                      { label: __( 'By Tag', 'hmpro' ), value: 'tag' },
                    ],
                    onChange: ( v ) => updateTab( index, { queryType: v } ),
                  }
                ),
                wp.element.createElement(
                  TextControl,
                  {
                    label: (tab.queryType === 'tag') ? __( 'Tag ID', 'hmpro' ) : __( 'Category ID', 'hmpro' ),
                    help: __( 'Use WooCommerce term ID. (Admin: Products > Categories/Tags)', 'hmpro' ),
                    value: String( tab.queryType === 'tag' ? (tab.tagId || 0) : (tab.categoryId || 0) ),
                    onChange: ( v ) => {
                      const id = clamp( normalizeInt( v, 0 ), 0, 9999999 );
                      if ( tab.queryType === 'tag' ) updateTab( index, { tagId: id } );
                      else updateTab( index, { categoryId: id } );
                    }
                  }
                ),
                wp.element.createElement(
                  RangeControl,
                  {
                    label: __( 'Products Per Tab (Max 24)', 'hmpro' ),
                    value: clamp( normalizeInt( tab.productsPerTab, 12 ), 1, 24 ),
                    min: 1,
                    max: 24,
                    onChange: ( v ) => updateTab( index, { productsPerTab: clamp( normalizeInt( v, 12 ), 1, 24 ) } ),
                  }
                ),
                wp.element.createElement(
                  Button,
                  { isDestructive: true, onClick: () => removeTab( index ) },
                  __( 'Remove Tab', 'hmpro' )
                )
              );
            } ),
            wp.element.createElement(
              Button,
              { variant: 'primary', onClick: addTab },
              __( 'Add Tab', 'hmpro' )
            )
          ),
          wp.element.createElement(
            PanelBody,
            { title: __( 'Style', 'hmpro' ), initialOpen: false },
            wp.element.createElement(
              SelectControl,
              {
                label: __( 'Tabs Alignment', 'hmpro' ),
                value: tabsAlignment || 'center',
                options: [
                  { label: __( 'Left', 'hmpro' ), value: 'flex-start' },
                  { label: __( 'Center', 'hmpro' ), value: 'center' },
                  { label: __( 'Right', 'hmpro' ), value: 'flex-end' },
                ],
                onChange: ( v ) => setAttributes( { tabsAlignment: v } ),
              }
            ),
            wp.element.createElement(
              RangeControl,
              {
                label: __( 'Box Radius', 'hmpro' ),
                value: normalizeInt( boxRadius, 22 ),
                min: 0,
                max: 60,
                onChange: ( v ) => setAttributes( { boxRadius: normalizeInt( v, 22 ) } ),
              }
            ),
            wp.element.createElement(
              RangeControl,
              {
                label: __( 'Box Padding', 'hmpro' ),
                value: normalizeInt( boxPadding, 34 ),
                min: 0,
                max: 80,
                onChange: ( v ) => setAttributes( { boxPadding: normalizeInt( v, 34 ) } ),
              }
            ),
            wp.element.createElement(
              BaseControl,
              { label: __( 'Box Background', 'hmpro' ) },
              wp.element.createElement( ColorPalette, {
                value: boxBg || '',
                onChange: ( v ) => setAttributes( { boxBg: v || '' } )
              } )
            ),
            wp.element.createElement(
              BaseControl,
              { label: __( 'Tab Background', 'hmpro' ) },
              wp.element.createElement( ColorPalette, {
                value: tabBg || '',
                onChange: ( v ) => setAttributes( { tabBg: v || '' } )
              } )
            ),
            wp.element.createElement(
              BaseControl,
              { label: __( 'Tab Text', 'hmpro' ) },
              wp.element.createElement( ColorPalette, {
                value: tabColor || '',
                onChange: ( v ) => setAttributes( { tabColor: v || '' } )
              } )
            ),
            wp.element.createElement(
              BaseControl,
              { label: __( 'Active Tab Background', 'hmpro' ) },
              wp.element.createElement( ColorPalette, {
                value: tabBgActive || '',
                onChange: ( v ) => setAttributes( { tabBgActive: v || '' } )
              } )
            ),
            wp.element.createElement(
              BaseControl,
              { label: __( 'Active Tab Text', 'hmpro' ) },
              wp.element.createElement( ColorPalette, {
                value: tabColorActive || '',
                onChange: ( v ) => setAttributes( { tabColorActive: v || '' } )
              } )
            ),
            wp.element.createElement(
              BaseControl,
              { label: __( 'Title Color', 'hmpro' ) },
              wp.element.createElement( ColorPalette, {
                value: titleColor || '',
                onChange: ( v ) => setAttributes( { titleColor: v || '' } )
              } )
            ),
            wp.element.createElement(
              BaseControl,
              { label: __( 'Price Color', 'hmpro' ) },
              wp.element.createElement( ColorPalette, {
                value: priceColor || '',
                onChange: ( v ) => setAttributes( { priceColor: v || '' } )
              } )
            )
          )
        ),
        wp.element.createElement(
          'div',
          Object.assign( {}, blockProps, { key: 'view' } ),
          wp.element.createElement(
            'div',
            { className: 'hm-pft__note' },
            wp.element.createElement( 'strong', null, 'HM Product Full Tabs' ),
            'Editor preview is lightweight. Frontend renders real WooCommerce products (tabs + slider).',
            chips.length ? wp.element.createElement(
              'div',
              { className: 'hm-pft__mini' },
              chips.map( (c, i) => wp.element.createElement('span', { key: 'c'+i, className: 'hm-pft__chip' }, c ) )
            ) : null
          )
        )
      ];
    },
    save: function () {
      return null;
    }
  } );
} )( window.wp );
