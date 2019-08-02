var Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild(['public/build/'])
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(!Encore.isProduction())
    .disableSingleRuntimeChunk()

    .addEntry('twitterWidget', [
        './assets/js/widget.js'
    ])
    .addStyleEntry('twitterWidgetStyle', [
        './assets/css/widget.css'
    ])
;

module.exports = Encore.getWebpackConfig();
module.exports.output.library = 'TwitterWidget';
module.exports.output.libraryTarget = 'var';