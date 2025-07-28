jQuery(function ($) {
  const targetEl = `${SignInWithSolana.pluginId}-svelte-target`;
  let svelteInstance = null;

  async function loadSvelte() {
    // return if already loaded
    if (svelteInstance) return;

    // ensure target element exists
    if (!$(`#${targetEl}`).length) $('body').append(`<div id="${targetEl}"></div>`);

    try {
      const { mountSvelte } = await import('/src/lib/index.js');
      svelteInstance = await mountSvelte(`#${targetEl}`);
    } catch (err) {
      console.error('Failed to mount Svelte component:', err);
    }
  }

  $(() => {
    if ($(`.${SignInWithSolana.pluginId}`).length) loadSvelte();
  });
});
