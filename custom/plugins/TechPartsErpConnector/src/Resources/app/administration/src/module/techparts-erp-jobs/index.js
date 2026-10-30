import './page/techparts-erp-jobs-list';

Shopware.Module.register('techparts-erp-jobs', {
    type: 'plugin',
    name: 'TechParts ERP Jobs',
    title: 'techpartsErp.jobs.title',
    description: 'techpartsErp.jobs.description',
    color: '#2673B8',
    icon: 'regular-history',
    routes: {
        index: {
            component: 'techparts-erp-jobs-list',
            path: 'index',
        },
    },
    navigation: [{
        id: 'techparts-erp-jobs',
        label: 'techpartsErp.jobs.title',
        color: '#2673B8',
        icon: 'regular-history',
        path: 'techparts.erp.jobs.index',
        parent: 'sw-settings',
        position: 110,
    }],
});
