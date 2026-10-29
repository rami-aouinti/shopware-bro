import shopware from './.shopware/eslint.mjs';

export default [
    ...shopware,
    {
        files: ['**/*.html.twig'],
        rules: {
            'vue/valid-v-slot': 'off',
        },
    },
];