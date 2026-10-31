import template from './enterprise-order-integration-list.html.twig';

const { Component } = Shopware;

Component.register('enterprise-order-integration-list', {
    template,
    inject: ['loginService'],

    data() {
        return {
            exports: [],
            isLoading: false,
            searchTerm: '',
            appliedSearch: '',
            statusFilter: '',
            fromDate: '',
            toDate: '',
            page: 1,
            limit: 25,
            total: 0,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            statusOptions: [
                { value: '', label: 'All statuses' },
                { value: 'pending', label: 'Pending' },
                { value: 'processing', label: 'Processing' },
                { value: 'succeeded', label: 'Succeeded' },
                { value: 'failed', label: 'Failed' },
            ],
            columns: [
                { property: 'orderId', label: 'Order UUID', rawData: true, allowResize: true, sortable: true },
                { property: 'status', label: 'Status', rawData: true, allowResize: true, sortable: true },
                { property: 'attempts', label: 'Attempts', rawData: true, allowResize: true, sortable: true },
                { property: 'erpReference', label: 'ERP reference', rawData: true, allowResize: true, sortable: true },
                { property: 'lastError', label: 'Last error', rawData: true, allowResize: true },
                { property: 'correlationId', label: 'Correlation ID', rawData: true, allowResize: true },
                { property: 'createdAt', label: 'Created', rawData: true, allowResize: true, sortable: true },
                { property: 'isDemo', label: 'Demo', rawData: true, allowResize: true },
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
            const params = {
                page: this.page,
                limit: this.limit,
                sortBy: this.sortBy,
                sortDirection: this.sortDirection,
                search: this.appliedSearch,
                status: this.statusFilter,
                fromDate: this.fromDate,
                toDate: this.toDate,
            };

            client.get('/_action/enterprise-order-integration/exports', {
                headers: this.getHeaders(),
                params,
            })
                .then((response) => {
                    this.exports = response.data.data || [];
                    this.total = response.data.meta?.total || 0;
                })
                .catch(() => {
                    this.createNotificationError({
                        message: 'Could not load ERP exports. Check the application logs and permissions.',
                    });
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        applyFilters() {
            this.appliedSearch = this.searchTerm.trim();
            this.page = 1;
            this.loadExports();
        },

        resetFilters() {
            this.searchTerm = '';
            this.appliedSearch = '';
            this.statusFilter = '';
            this.fromDate = '';
            this.toDate = '';
            this.page = 1;
            this.loadExports();
        },

        onPageChange(page) {
            this.page = page;
            this.loadExports();
        },

        onLimitChange(limit) {
            this.limit = Number(limit);
            this.page = 1;
            this.loadExports();
        },

        onColumnSort(column) {
            if (!column || !column.property) {
                return;
            }

            if (this.sortBy === column.property) {
                this.sortDirection = this.sortDirection === 'ASC' ? 'DESC' : 'ASC';
            } else {
                this.sortBy = column.property;
                this.sortDirection = 'ASC';
            }

            this.page = 1;
            this.loadExports();
        },

        retryExport(item) {
            if (item.isDemo) {
                return;
            }

            const client = Shopware.Application.getContainer('init').httpClient;
            client.post(
                '/_action/enterprise-order-integration/exports/' + item.orderId + '/retry',
                {},
                { headers: this.getHeaders() },
            )
                .then(() => {
                    this.createNotificationSuccess({ message: 'Export queued for retry.' });
                    this.loadExports();
                })
                .catch((error) => {
                    this.createNotificationError({
                        message: error?.response?.data?.error || 'Could not queue export retry.',
                    });
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
