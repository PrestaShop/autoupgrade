const path = require('path');

const pages = {
  index: '_dev/main.js',
};

function resolve(dir) {
  return path.join(__dirname, dir);
}

module.exports = {
  devServer: {
    host: 'localhost',
    port: '8080',
    disableHostCheck: true,
  },
  chainWebpack: (config) => {
    Object.keys(pages).forEach((page) => {
      if (process.env.NODE_ENV === 'production') {
        // Avoid index.html to be created
        config.plugins.delete(`html-${page}`);
        config.plugins.delete(`preload-${page}`);
        config.plugins.delete(`prefetch-${page}`);
      }
    });
    config.resolve.alias.set('@', resolve('_dev'));
  },
  pages,
  filenameHashing: false,
  outputDir: 'views/',
  assetsDir: process.env.NODE_ENV === 'production'
           ? ''
           : '../modules/ps_checkout/views/',
  publicPath: process.env.NODE_ENV === 'production'
            ? '../modules/ps_checkout/views/'
            : './',
};
