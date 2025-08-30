<script>
  import { onMount } from 'svelte';
  import { Buffer } from 'buffer';
  import { walletStore } from '@aztemi/svelte-on-solana-wallet-adapter-core';
  import { WalletProvider, WalletModal } from '@aztemi/svelte-on-solana-wallet-adapter-ui';

  let modalVisible = false;
  let loginForm = null;
  let wallets = [];

  if (typeof window !== 'undefined' && !window.Buffer) {
    window.Buffer = Buffer;
  }

  $: ({ publicKey, wallet, connect, select } = $walletStore);

  const openModal = () => (modalVisible = true);
  const closeModal = () => (modalVisible = false);

  const bytesToBase64 = (bytes) => btoa(String.fromCharCode(...new Uint8Array(bytes)));

  // Find the nearest parent with the specified tag name.
  function getParent(el, tagName) {
    const targetTag = tagName.toLowerCase();

    while (el) {
      el = el.parentElement;
      if (el && el.tagName.toLowerCase() === targetTag) {
        return el;
      }
    }

    return null;
  }

  function removeErrorMessages() {
    const notices = document.querySelectorAll(
      '#login_error, #login-message, .wc-block-components-notice-banner, .woocommerce-error, .woocommerce-message',
    );
    notices?.forEach((el) => el.remove());

    loginForm?.classList.remove('shake');
  }

  function showErrorMessage(message) {
    const notice = `<strong>Error:</strong> ${message}`;

    const wcNoticesDiv = document.querySelector('.woocommerce-notices-wrapper');
    if (wcNoticesDiv) {
      wcNoticesDiv.insertAdjacentHTML('beforeend', `<div class="wc-block-components-notice-banner is-error" role="alert"><div>${notice}</div></div>`);
      jQuery.scroll_to_notices && jQuery.scroll_to_notices(jQuery('[role="alert"]'));
    } else {
      loginForm?.classList.add('shake');
      loginForm?.insertAdjacentHTML('beforebegin', `<div id="login_error" class="notice notice-error"><p>${notice}</p></div>`);
    }
  }

  async function signMessageAndLogin(event) {
    // remove previous notices if any
    removeErrorMessages();
    closeModal();

    // connect to selected wallet
    select(event.detail);
    await connect();
    if (!publicKey) {
      return;
    }

    const { ajaxUrl, action, nonce, message } = SignInWithSolana;

    // sign message
    const encodedMsg = new TextEncoder().encode(message);
    const signature = await wallet.adapter.signMessage(encodedMsg);

    // validate signature in backend
    jQuery
      .ajax({
        url: ajaxUrl,
        method: 'POST',
        data: { action, nonce, address: publicKey.toBase58(), signature: bytesToBase64(signature) },
      })
      .always((data_jqXHR, textStatus, jqXHR_errorThrown) => {
        const jqXHR = textStatus === 'success' ? jqXHR_errorThrown : data_jqXHR;
        const response = jqXHR.responseJSON;

        if (true === response.success) {
          // Sign-in OK, redirect
          window.location.assign(response.data.redirect);
        } else {
          // Sign-in failed, show login error
          showErrorMessage(response.data);
        }
      });
  }

  function handleSignInButtonClick(event) {
    event.preventDefault();
    openModal();
  }

  function updateSignInButton() {
    const signInBtns = document.querySelectorAll('[data-attr="sign_in_button"]');
    if (!signInBtns.length) {
      return;
    }

    signInBtns.forEach((signInBtn) => {
      signInBtn.addEventListener('click', handleSignInButtonClick);

      const containerDiv = getParent(signInBtn, 'div');
      if (containerDiv) {
        loginForm = getParent(containerDiv, 'form');
        if (loginForm) {
          loginForm.appendChild(containerDiv);
        }

        containerDiv.style.display = 'block';
      }
    });

    return () => {
      signInBtns?.forEach((signInBtn) => {
        signInBtn.removeEventListener('click', handleSignInButtonClick);
      });
    };
  }

  async function initWallets() {
    const { PhantomWalletAdapter, SolflareWalletAdapter, CoinbaseWalletAdapter, LedgerWalletAdapter } = await import(
      '@solana/wallet-adapter-wallets'
    );
    wallets = [new PhantomWalletAdapter(), new SolflareWalletAdapter(), new CoinbaseWalletAdapter(), new LedgerWalletAdapter()];
  }

  onMount(async () => {
    await initWallets();
    return updateSignInButton();
  });
</script>

<WalletProvider {wallets} />

{#if modalVisible}
  <WalletModal on:close={closeModal} on:connect={signMessageAndLogin} />
{/if}
