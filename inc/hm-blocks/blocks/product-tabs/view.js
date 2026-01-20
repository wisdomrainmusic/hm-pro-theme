(() => {
  const scope = '.hmpro-product-tabs';

  const activateTab = (container, nextButton) => {
    const buttons = Array.from(container.querySelectorAll('.hmpro-product-tabs__tab'));
    buttons.forEach((button) => {
      button.classList.toggle('is-active', button === nextButton);
      button.setAttribute('aria-selected', button === nextButton ? 'true' : 'false');
    });
  };

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.hmpro-product-tabs__tab');
    if (!button) return;

    const container = button.closest(scope);
    if (!container) return;

    activateTab(container, button);
  });

  document.querySelectorAll(scope).forEach((container) => {
    const first = container.querySelector('.hmpro-product-tabs__tab');
    if (first) {
      activateTab(container, first);
    }
  });
})();
