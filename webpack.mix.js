let mix = require('laravel-mix');

mix.setPublicPath('dist')
    .js('resources/js/tool.js', 'js')
    .sass('resources/sass/tool.scss', 'css')
    .webpackConfig({
        resolve: {
            alias: {
                '@app': path.resolve(__dirname, '../../resources/js/'),
                '@nova': path.resolve(__dirname, '../../vendor/laravel/nova/resources/js/'),
                'laravel-nova': path.resolve(__dirname, './node_modules/laravel-nova/dist/index.js'),
                '@ui': path.resolve(__dirname, '../../nova-components/nova-compact-ui/resources/js/'),
                '@': path.resolve(__dirname, '../../vendor/laravel/nova/resources/js/'),
                '@vendor': path.resolve(__dirname, '../../vendor/')
            },
        },
    });
