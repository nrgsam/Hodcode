<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

// dist subfolder - defined in vite.config.json
define('DIST_DEF', 'dist');

// defining some base urls and paths
define('DIST_URI', get_template_directory_uri() . '/' . DIST_DEF);
define('DIST_PATH', get_template_directory() . '/' . DIST_DEF);

// js enqueue settings
define('JS_DEPENDENCY', array("jquery")); // array('jquery') as example
define('JS_LOAD_IN_FOOTER', true); // load scripts in footer?

// deafult server address, port and entry point can be customized in vite.config.json
define('VITE_SERVER', 'http://localhost:3000');
define('VITE_ENTRY_POINT', '/main.js');

// enqueue hook
add_action('wp_enqueue_scripts', function () {

    if (defined('IS_VITE_DEVELOPMENT') && IS_VITE_DEVELOPMENT === true) {

        // insert hmr into head for live reload
        add_action('wp_head',  function () {
            echo '<script type="module">
            import RefreshRuntime from "http://localhost:3000/@react-refresh"
            RefreshRuntime.injectIntoGlobalHook(window)
            window.$RefreshReg$ = () => { }
            window.$RefreshSig$ = () => (type) => type
            window.__vite_plugin_react_preamble_installed__ = true
        </script>';

            // echo '<script type="module" crossorigin src="' . VITE_SERVER . VITE_ENTRY_POINT . '"></script>';
                    wp_enqueue_script( 'main', VITE_SERVER . VITE_ENTRY_POINT , JS_DEPENDENCY, '', JS_LOAD_IN_FOOTER );

        });
    } else {

        // production version, 'npm run build' must be executed in order to generate assets

        // read manifest.json to figure out what to enqueue
        $manifest = json_decode(file_get_contents(DIST_PATH . '/manifest.json'), true);

        // is ok
        if (is_array($manifest)) {

            if (isset($manifest['main.js'])) {

                // enqueue CSS files
                foreach (@$manifest['main.js']['css'] as $css_file) {
                    wp_enqueue_style('main', DIST_URI . '/' . $css_file);
                }

                // enqueue main JS file
                $js_file = @$manifest['main.js']['file'];
                if (!empty($js_file)) {
                    wp_enqueue_script('main', DIST_URI . '/' . $js_file, JS_DEPENDENCY, '', JS_LOAD_IN_FOOTER);
                }
            }
        }
    }
});


add_action('enqueue_block_editor_assets', function () {
    if (defined('IS_VITE_DEVELOPMENT') && IS_VITE_DEVELOPMENT === true) {
        echo '<script type="module" crossorigin src="' . VITE_SERVER . VITE_ENTRY_POINT . '"></script>';
    } else {
        $manifest = json_decode(file_get_contents(DIST_PATH . '/manifest.json'), true);

        if (is_array($manifest)) {

            if (isset($manifest['main.js'])) {

                // enqueue CSS files
                foreach (@$manifest['main.js']['css'] as $css_file) {
                    wp_enqueue_style('main', DIST_URI . '/' . $css_file);
                }
            }
        }
    }
});
