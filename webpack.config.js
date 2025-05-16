const Encore = require("@symfony/webpack-encore");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath("public/build/")
  // public path used by the web server to access the output path
  .setPublicPath("/docauposte/build/")
  // only needed for CDN's or subdirectory deploy
  .setManifestKeyPrefix('docauposte/')

  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry("app", "./assets/app.js")
  .addEntry("confirmation", "./assets/js/confirmation.js")
  .addEntry("toast", "./assets/js/toast.js")
  .addEntry("server-variable", "./assets/js/server-variable.js")
  .addEntry("cascading-dropdowns", "./assets/js/cascading-dropdowns.js")
  .addEntry("incident-cascading-dropdowns", "./assets/js/incident-cascading-dropdowns.js")
  .addEntry("incident-checkbox-signature", "./assets/js/incident-checkbox-signature.js")
  .addEntry("department-creation", "./assets/js/department-creation.js")
  .addEntry("document-validator", "./assets/js/document-validator.js")
  .addEntry("inactivity-timer", "./assets/js/inactivity-timer.js")
  .addEntry("incident-cycler", "./assets/js/incident-cycler.js")
  .addEntry("views-modification-value-tracker", "./assets/js/views-modification-value-tracker.js")


  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
  .enableStimulusBridge('./assets/controllers.json')

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  // configure Babel
  // .configureBabel((config) => {
  //     config.plugins.push('@babel/a-babel-plugin');
  // })

  // enables and configure @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = "usage";
    config.corejs = "3.23";
  });

// enables Sass/SCSS support
Encore.enableSassLoader();

// uncomment if you use TypeScript
// .enableTypeScriptLoader()

// uncomment if you use React
//.enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
// Encore.autoProvidejQuery();

module.exports = Encore.getWebpackConfig();
