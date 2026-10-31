import './module/enterprise-order-integration';
import './extension/sw-flow-sequence-action';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Locale } = Shopware;
Locale.extend('de-DE', deDE);
Locale.extend('en-GB', enGB);
