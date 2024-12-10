const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);

// Increase the max workers
config.maxWorkers = 2;

// Increase the transformer timeout
config.transformer = {
  ...config.transformer,
  minifierConfig: {
    ...config.transformer.minifierConfig,
    compress: {
      reduce_funcs: false,
    },
  },
};

// Increase buffer size and add vector icons
config.resolver = {
  ...config.resolver,
  sourceExts: ['jsx', 'js', 'ts', 'tsx', 'json', 'mjs'],
  assetExts: ['png', 'jpg', 'jpeg', 'gif', 'webp', 'ttf'],
  extraNodeModules: {
    '@expo/vector-icons': require.resolve('@expo/vector-icons')
  }
};

module.exports = config; 