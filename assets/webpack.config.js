let Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('src/Bundle/DataSanitizeBundle/Resources/public/build/')
    .setPublicPath('/bundles/endroiddatasanitize/build')
    .setManifestKeyPrefix('/build')
    .cleanupOutputBeforeBuild()
    .createSharedEntry('base', './src/Bundle/DataSanitizeBundle/Resources/public/src/js/base.js')
    .addEntry('merge', './src/Bundle/DataSanitizeBundle/Resources/public/src/js/merge.js')
    .autoProvidejQuery()
    .enableReactPreset()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();