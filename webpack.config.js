const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');

module.exports = {
  ...defaultConfig,
  resolve: {
    ...defaultConfig.resolve,
    alias: {
      ...defaultConfig.resolve.alias,
      svelte: path.resolve('node_modules', 'svelte/src/runtime'),
    },
    extensions: [...defaultConfig.resolve?.extensions, '.mjs', '.js', '.svelte'],
    mainFields: [...(defaultConfig.resolve?.mainFields ?? []), 'svelte', 'browser', '...'],
    conditionNames: [...(defaultConfig.resolve?.conditionNames ?? []), 'svelte', 'browser', '...'],
  },
  module: {
    ...defaultConfig.module,
    rules: [
      ...defaultConfig.module?.rules,
      {
        test: /\.(svelte|svelte\.js)$/,
        use: {
          loader: 'svelte-loader',
          options: {
            emitCss: true,
          },
        },
      },
      {
        test: /\.css$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader'],
      },
    ],
  },
  plugins: [
    ...defaultConfig.plugins,
    new MiniCssExtractPlugin({
      filename: '[name].css',
    }),
  ],
};
