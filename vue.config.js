const path = require('path');

const isProduction = process.env.NODE_ENV === 'production';
const pages = {
  index: '_dev/main.js',
};

module.exports = {
  css: {
    loaderOptions: {
      sass: {
        data: '@import "~@/assets/variables.scss";',
      },
    },
  },
  devServer: {
    host: process.env.NODE_HOST,
    port: process.env.NODE_PORT,
    contentBase: ['./_dev'],
    disableHostCheck: true,
    inline: true,
    hot: true,
  },
  chainWebpack: (config) => {
    Object.keys(pages).forEach((page) => {
      if (isProduction) {
        // Avoid index.html to be created
        config.plugins.delete(`html-${page}`);
        config.plugins.delete(`preload-${page}`);
        config.plugins.delete(`prefetch-${page}`);
      }
    });
    config.resolve.alias.set('@', path.join(__dirname, '_dev'));
  },
  pages,
  filenameHashing: false,
  outputDir: 'views/',
  assetsDir: isProduction
    ? ''
    : '../modules/auoupgrade/views/',
  publicPath: isProduction
    ? '../modules/autoupgrade/views/'
    : './',
};
