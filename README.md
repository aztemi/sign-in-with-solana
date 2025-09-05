# sign-in-with-solana

Enable Sign-In with Solana for your WordPress site. Authenticate users using their Solana wallets and provide a secure, decentralized login experience.

<img src="/.wordpress.org/assets/screenshot-1.png" alt="Login with Solana" height="400" />

## How to use on a WordPress site

This project is available on the official [WordPress Plugins](https://wordpress.org/plugins/sign-in-with-solana/) Marketplace. Install it from within your WP site following the [installation guidelines](https://wordpress.org/plugins/sign-in-with-solana/#installation).

## Features

- Seamless integration with the WordPress and WooCommerce login forms
- Secure wallet-based authentication
- Supports popular Solana wallets (Phantom, Solflare, etc.)
- Automatically creates new WordPress user accounts for new wallet sign-ins
- No passwords required. Users authenticate by signing a message with their wallet
- No setup required. The plugin works seamlessly after activation.

## How to build

1. Clone the repository and navigate to the project directory

```bash
git clone https://github.com/aztemi/sign-in-with-solana.git
cd sign-in-with-solana
```

2. Install the project dependencies

```bash
npm install
```

3. Create a release package by running the following commands

```bash
npm run build
npm run makepot
npm run package
```

4. Use the generated ZIP file to install on your WordPress site

   The ZIP file will be created in the root directory. Upload and install it through your WP admin dashboard.

## Buy me a coffee

Solana Wallet: BFSi8WeoE2bLJtMUpB6KVggJZ4Uv5DavVrVsm5kdrQwY

## License

[GPL-3.0](./LICENSE.txt)
