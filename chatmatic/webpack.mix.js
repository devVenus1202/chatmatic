let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js([
        'resources/assets/js/app.js'
    ], 'public/js')
    .scripts([
        'resources/assets/js/chatmatic.js', // Load our JS after the libs
    ], 'public/js/chatmatic.js')
    .sass(      'resources/assets/sass/app.scss', 'public/css')
    .styles([
        'resources/assets/css/chatmatic.css',
    ], 'public/css/chatmatic.css');

// If we're in production use versioned assets to bust caches on updates
if(mix.inProduction()) {
    mix.version();
}