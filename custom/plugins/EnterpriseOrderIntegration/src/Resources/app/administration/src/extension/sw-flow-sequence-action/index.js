import { ACTION, GROUP } from '../../constant/enterprise-order-flow-action.constant';

const { Component } = Shopware;

Component.override('sw-flow-sequence-action', {
    computed: {
        groups() {
            if (!this.actionGroups.includes(GROUP)) {
                this.actionGroups.unshift(GROUP);
            }

            return this.$super('groups');
        },
    },

    methods: {
        openDynamicModal(value) {
            if (value === ACTION.EXPORT_ORDER_TO_ERP) {
                this.selectedAction = value;
                this.onSaveActionSuccess({ config: {} });
                return;
            }

            return this.$super('openDynamicModal', value);
        },

        getActionTitle(actionName) {
            if (actionName === ACTION.EXPORT_ORDER_TO_ERP) {
                return {
                    value: actionName,
                    icon: 'regular-cloud-upload',
                    label: this.$tc('enterprise-order-flow-action.titleExportOrderToErp'),
                    group: GROUP,
                };
            }

            return this.$super('getActionTitle', actionName);
        },

        getActionDescriptions(sequence) {
            if (sequence.actionName === ACTION.EXPORT_ORDER_TO_ERP) {
                return this.$tc('enterprise-order-flow-action.descriptionExportOrderToErp');
            }

            return this.$super('getActionDescriptions', sequence);
        },
    },
});
