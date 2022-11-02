const path = require('path');

const basePath = path.resolve(__dirname);

module.exports = [
  {
    file: 'BO/modules/autoupgrade/index.js',
    versions: {
      '1.7.4': `${basePath}/pages/BO/modules/autoupgrade/index.js`,
      '1.7.5': `${basePath}/pages/BO/modules/autoupgrade/index.js`,
      '1.7.6': `${basePath}/pages/BO/modules/autoupgrade/index.js`,
      '1.7.7': `${basePath}/pages/BO/modules/autoupgrade/index.js`,
    },
  },
  {
    file: 'BO/shopParameters/general/index.js',
    versions: {
      '1.7.4': `${basePath}/pages/BO/shopParameters/general/index.js`,
      '1.7.5': `${basePath}/pages/BO/shopParameters/general/index.js`,
      '1.7.6': `${basePath}/pages/BO/shopParameters/general/index.js`,
      '1.7.7': `${basePath}/pages/BO/shopParameters/general/index.js`,
    },
  },
  {
    file: 'BO/shopParameters/general/maintenance/index.js',
    versions: {
      '1.7.4': `${basePath}/pages/BO/shopParameters/general/maintenance/index.js`,
      '1.7.5': `${basePath}/pages/BO/shopParameters/general/maintenance/index.js`,
      '1.7.6': `${basePath}/pages/BO/shopParameters/general/maintenance/index.js`,
      '1.7.7': `${basePath}/pages/BO/shopParameters/general/maintenance/index.js`,
    },
  },
];
