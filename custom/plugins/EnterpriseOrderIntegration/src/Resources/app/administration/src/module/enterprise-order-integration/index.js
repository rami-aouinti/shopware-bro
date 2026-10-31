import './page/enterprise-order-integration-list';

const { Module } = Shopware;

Module.register('enterprise-order-integration', {
    type: 'plugin',
    name: 'EnterpriseOrderIntegration',
    title: 'enterprise-order-integration.general.mainMenuItemGeneral',
    description: 'Monitor and retry ERP order exports',
    color: '#5B6AC4',
    icon: 'regular-shopping-bag',
    routes: {
        index: {
            component: 'enterprise-order-integration-list',
            path: 'index',
        },
    },
    navigation: [{
        label: 'ERP order exports',
        color: '#5B6AC4',
        path: 'enterprise.order.integration.index',
        icon: 'regular-shopping-bag',
        parent: 'sw-order',
        position: 100,
    }],
});