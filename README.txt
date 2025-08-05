=== Sign-in With Solana ===
Contributors: aztemi, t4top
Donate link: https://apps.aztemi.com/sign-in-with-solana/donate/
Tags: solana, wallet, web3, login, authentication, sign-in, crypto, blockchain, woocommerce
Requires at least: 5.2
Tested up to: 6.8
Requires PHP: 7.2
Stable tag: 0.1.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Authenticate users on your WordPress site using Solana wallets. A secure, decentralized login experience.

== Description ==

**Sign-in With Solana** enables secure, Web3-compatible authentication on your WordPress website using Solana wallets like Phantom, Solflare, etc. Allow users to sign in without traditional usernames and passwords.

This plugin adds a "Sign in with Solana" button to the login page, enabling wallet-based authentication using cryptographic signatures to verify user identity.

== Features ==

* Seamless integration with the WordPress and WooCommerce login form
* Secure wallet-based authentication
* Supports popular Solana wallets (Phantom, Solflare, etc.)
* No passwords required – users authenticate by signing a message with their wallet
* Automatically creates new WordPress user accounts for new wallet sign-ins
* No setup required. The plugin works seamlessly after activation.

== Installation ==

1. Go to **Plugins > Add New**.
2. Search for **Sign-in with Solana** plugin.
3. Click on **Install Now** and wait until the plugin is installed successfully.
4. Click on **Activate**. (You can also activate on the **Plugins > Installed Plugins** page).
5. Visit your login page to see the new "Sign-in with Solana" option.

= Minimum Requirements =

* WordPress 5.2
* PHP 7.2 with BCMath or GMP extension installed

== Frequently Asked Questions ==

= Which wallets are supported? =
Any wallet that supports Solana and message-based signing should work, including Phantom and Solflare.

= How is user identity verified? =
Users sign a challenge message with their wallet. The plugin verifies the signature to authenticate the user.

= What happens if a user signs in for the first time? =
A new WordPress user account is automatically created for them, using their wallet address as their username.

== Screenshots ==

1. Sign-in with Solana button on the login screen
2. Wallet authentication in progress
3. User logged in successfully

== Source Code ==

This plugin is an open source software with [GPLv3 or later](https://www.gnu.org/licenses/gpl-3.0.html) license. The code is available from our [repository on GitHub](https://github.com/aztemi/sign-in-with-solana).

== Changelog ==

= 0.1.0 =
* Initial release

== Upgrade Notice ==

= 0.1.0 =
First stable version.
