let mix = require('laravel-mix');

mix.webpackConfig({
    externals: {
        'jquery': 'jQuery',
    }
});

mix.setPublicPath('dist');
mix.setResourceRoot('resources');

mix.js('resources/scripts/app.js', 'scripts');
