import template from './techparts-erp-jobs-list.html.twig';

const { Criteria } = Shopware.Data;

Shopware.Component.register('techparts-erp-jobs-list', {
    template,
    inject: ['repositoryFactory'],

    data() {
        return {
            jobs: [],
            isLoading: false,
            total: 0,
            page: 1,
            limit: 25,
        };
    },

    computed: {
        jobRepository() {
            return this.repositoryFactory.create('tp_import_job');
        },
        columns() {
            return [
                {
                    property: 'source',
                    label: this.$t('techpartsErp.jobs.source'),
                    primary: true,
                },
                {
                    property: 'status',
                    label: this.$t('techpartsErp.jobs.status'),
                },
                {
                    property: 'received',
                    label: this.$t('techpartsErp.jobs.received'),
                },
                {
                    property: 'accepted',
                    label: this.$t('techpartsErp.jobs.accepted'),
                },
                {
                    property: 'rejected',
                    label: this.$t('techpartsErp.jobs.rejected'),
                },
                {
                    property: 'startedAt',
                    label: this.$t('techpartsErp.jobs.startedAt'),
                },
            ];
        },
    },

    created() {
        this.loadJobs();
    },

    methods: {
        async loadJobs() {
            this.isLoading = true;

            try {
                const criteria = new Criteria(this.page, this.limit);
                criteria.addSorting(Criteria.sort('startedAt', 'DESC'));

                const result = await this.jobRepository.search(
                    criteria,
                    Shopware.Context.api
                );

                this.jobs = result;
                this.total = result.total;
            } catch (error) {
                this.jobs = [];
                this.total = 0;
                console.error(
                    '[TechPartsErpConnector] Cannot load import jobs',
                    error
                );
            } finally {
                this.isLoading = false;
            }
        },
    },
});
