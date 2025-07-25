export async function mountSvelte(targetSelector, props = {}) {
  const target = document.querySelector(targetSelector);
  if (!target) {
    console.error(`Mount target "${targetSelector}" not found`);
    return null;
  }

  const { default: SvelteComponent } = await import('./App.svelte');
  return new SvelteComponent({ target, props });
}
