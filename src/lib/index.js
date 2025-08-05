export async function mountSvelte(targetSelector, props = {}) {
  const target = document.querySelector(targetSelector);
  if (!target) {
    // eslint-disable-next-line no-console
    console.error(`Mount target "${targetSelector}" not found`);
    return null;
  }

  const { default: SvelteComponent } = await import('./App.svelte');
  return new SvelteComponent({ target, props });
}
