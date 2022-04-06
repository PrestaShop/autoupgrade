global.PS_VERSION_UPGRADE_FROM = global.PS_VERSION;
global.PS_VERSION_UPGRADE_TO = process.env.PS_VERSION_UPGRADE_TO || '1.7.8.5';
global.PS_RESOLVER_VERSION = {
  FROM: global.PS_VERSION.substr(0, global.PS_VERSION.lastIndexOf('.')),
  TO: global.PS_VERSION_UPGRADE_TO.substr(0, global.PS_VERSION_UPGRADE_TO.lastIndexOf('.')),
};
global.ZIP_NAME = process.env.ZIP_NAME;
global.AUTOUPGRADE_VERSION = process.env.AUTOUPGRADE_VERSION || 'dev';
