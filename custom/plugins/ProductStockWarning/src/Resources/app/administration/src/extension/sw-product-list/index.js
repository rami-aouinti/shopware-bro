import template from './sw-product-list.html.twig';
import './sw-product-list.scss';

Shopware.Component.override('sw-product-list', {
    template,

    data() {
        return {
            stockWarningThreshold: 5,
        };
    },

    created() {
        this.loadStockWarningThreshold();
    },

    computed: {
        productColumns() {
            const columns = this.$super('productColumns');

            if (
                columns.some(
                    (column) => column.property === 'stockWarningStatus'
                )
            ) {
                return columns;
            }

            return [
                ...columns,
                {
                    property: 'stockWarningStatus',
                    label: this.$t('productStockWarning.admin.columnLabel'),
                    allowResize: true,
                    visible: true,
                    sortable: false,
                },
            ];
        },
    },

    methods: {
        async loadStockWarningThreshold() {
            try {
                const config = await Shopware.Service(
                    'systemConfigApiService'
                ).getValues('ProductStockWarning.config');
                const configuredThreshold = Number(
                    config['ProductStockWarning.config.stockThreshold']
                );

                if (
                    Number.isFinite(configuredThreshold) &&
                    configuredThreshold >= 1
                ) {
                    this.stockWarningThreshold = configuredThreshold;
                }
            } catch (error) {
                // Keep the documented default if the plugin configuration cannot be loaded.
                console.warn(
                    '[ProductStockWarning] Could not load the stock threshold. Using 5.',
                    error
                );
            }
        },

        getStockWarningStatus(stock) {
            const currentStock = Number(stock) || 0;

            if (currentStock <= 0) {
                return 'outOfStock';
            }

            if (currentStock <= this.stockWarningThreshold) {
                return 'lowStock';
            }

            return 'inStock';
        },
    },
});
