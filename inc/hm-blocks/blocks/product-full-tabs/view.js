( function () {
  function qs( root, sel ) { return root.querySelector( sel ); }
  function qsa( root, sel ) { return Array.prototype.slice.call( root.querySelectorAll( sel ) ); }

  function activateTab( root, tabId ) {
    const btns = qsa( root, '.hm-pft__tab-btn' );
    const panes = qsa( root, '.hm-pft__pane' );

    btns.forEach( b => {
      const on = b.getAttribute('data-tab') === tabId;
      b.classList.toggle( 'is-active', on );
      b.setAttribute( 'aria-selected', on ? 'true' : 'false' );
    } );

    panes.forEach( p => p.classList.toggle( 'is-active', p.getAttribute('data-pane') === tabId ) );
  }

  function scrollByCards( track, dir ) {
    if ( ! track ) return;
    const gap = 18;
    const slide = track.querySelector('.hm-pft__slide');
    const w = slide ? slide.getBoundingClientRect().width : 320;
    track.scrollBy( { left: dir * ( w + gap ), behavior: 'smooth' } );
  }

  function initOne( root ) {
    if ( root.dataset.hmPftInited ) return;
    root.dataset.hmPftInited = '1';

    const btns = qsa( root, '.hm-pft__tab-btn' );
    if ( ! btns.length ) return;

    btns.forEach( b => {
      b.addEventListener( 'click', function () {
        activateTab( root, b.getAttribute('data-tab') );
      } );
    } );

    // Activate first
    activateTab( root, btns[0].getAttribute('data-tab') );

    // Nav controls (per pane)
    qsa( root, '.hm-pft__pane' ).forEach( pane => {
      const track = qs( pane, '.hm-pft__track' );
      const prev = qs( pane, '.hm-pft__nav--prev' );
      const next = qs( pane, '.hm-pft__nav--next' );
      if ( prev ) prev.addEventListener( 'click', () => scrollByCards( track, -1 ) );
      if ( next ) next.addEventListener( 'click', () => scrollByCards( track, 1 ) );
    } );
  }

  function initAll() {
    document.querySelectorAll( '.hm-pft' ).forEach( initOne );
  }

  if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', initAll );
  } else {
    initAll();
  }

  // Gutenberg editor preview updates
  document.addEventListener( 'readystatechange', initAll );
} )();
