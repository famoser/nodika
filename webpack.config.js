var Encore = require("@symfony/webpack-encore");

Encore
// the project directory where all compiled assets will be stored
    .setOutputPath("public/dist/")

    // the public path used by the web server to access the previous directory
    .setPublicPath("/dist")

    // will create public/build/app.js and public/build/app.css
    .addEntry("app", "./assets/js/app.js")

    .enableSingleRuntimeChunk()

    // allow sass/scss files to be processed
    .enableSassLoader()

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    // enable vue.js loader
    .enableVueLoader()

    // allow debugging of minified assets
    .enableSourceMaps(!Encore.isProduction())

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // show OS notifications when builds finish/fail
    // .enableBuildNotifications()

    // create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
