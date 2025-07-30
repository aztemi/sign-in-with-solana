<script>
  import { onMount } from 'svelte';

  function updateSignInButton() {
    const containerDiv = document.querySelector('[data-attr="sign_in_button"]')?.parentElement;
    if (!containerDiv) return;

    const form = containerDiv.closest('form');
    if (form) form.appendChild(containerDiv);
    containerDiv.style.display = 'block';
  }

  onMount(() => {
    return updateSignInButton();
  });
</script>
