import template from './enterprise-order-integration-list.html.twig';

const { Component } = Shopware;

Component.register('enterprise-order-integration-list', {
    template,
    inject: ['loginService'],
    data() {
        return {
            exports: [],
            isLoading: false,
            columns: [
                { property: 'orderId', label: 'Order UUID', rawData: true, allowResize: true },
                { property: 'status', label: 'Status', rawData: true, allowResize: true },
                { property: 'attempts', label: 'Attempts', rawData: true, allowResize: true },
                { property: 'erp_reference', label: 'ERP reference', rawData: true, allowResize: true },
                { property: 'last_error', label: 'Last error', rawData: true, allowResize: true },
                { property: 'correlation_id', label: 'Correlation ID', rawData: true, allowResize: true },
                { property: 'created_at', label: 'Created', rawData: true, allowResize: true },
            ],
        };
    },
    created() {
        this.loadExports();
    },
    methods: {
        getHeaders() {
            return { Authorization: 'Bearer ' + this.loginService.getToken() };
        },
        loadExports() {
            this.isLoading = true;
            const client = Shopware.Application.getContainer('init').httpClient;
            client.get('/api/_action/enterprise-order-integration/exports', { headers: this.getHeaders() })
                .then((response) => {
                    this.exports = response.data.data || [];
                })
                .catch(() => {
                    this.createNotificationError({ message: 'Could not load ERP exports. Check the application logs and permissions.' });
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        retryExport(item) {
            const client = Shopware.Application.getContainer('init').httpClient;
            client.post('/api/_action/enterprise-order-integration/exports/' + item.orderId + '/retry', {}, { headers: this.getHeaders() })
                .then(() => {
                    this.createNotificationSuccess({ message: 'Export queued for retry.' });
                    this.loadExports();
                })
                .catch((error) => {
                    this.createNotificationError({ message: error?.response?.data?.error || 'Could not queue export retry.' });
                });
        },
        createNotificationSuccess(notification) {
            Shopware.Notification.createNotificationSuccess(notification);
        },
        createNotificationError(notification) {
            Shopware.Notification.createNotificationError(notification);
        },
    },
});