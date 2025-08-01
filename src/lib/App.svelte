<script>
  import { onMount } from 'svelte';
  import { WalletProvider, WalletModal } from '@aztemi/svelte-on-solana-wallet-adapter-ui';

  let modalVisible = false;

  const openModal = () => (modalVisible = true);
  const closeModal = () => (modalVisible = false);

  // Find the nearest parent with the specified tag name.
  function getParent(el, tagName) {
    const targetTag = tagName.toLowerCase();

    while (el) {
      el = el.parentElement;
      if (el && el.tagName.toLowerCase() === targetTag) return el;
    }

    return null;
  }

  function handleSignInButtonClick(event) {
    event.preventDefault();
    openModal();
  }

  function updateSignInButton() {
    const signInBtn = document.querySelector('[data-attr="sign_in_button"]');
    if (!signInBtn) return;
    signInBtn.addEventListener('click', handleSignInButtonClick);

    const containerDiv = getParent(signInBtn, 'div');
    if (containerDiv) {
      const form = getParent(containerDiv, 'form');
      if (form) form.appendChild(containerDiv);
      containerDiv.style.display = 'block';
    }

    return () => {
      signInBtn.removeEventListener('click', handleSignInButtonClick);
    };
  }

  onMount(() => {
    return updateSignInButton();
  });
</script>

<WalletProvider />

{#if modalVisible}
  <WalletModal on:close={closeModal} on:connect={() => {}} />
{/if}
