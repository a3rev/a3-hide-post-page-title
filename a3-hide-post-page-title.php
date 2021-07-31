<?php
/*
Plugin Name: a3 Hide Post & Page Title
Description: Adds 'Hide Title' option across your WordPress website. The plugin has no settings, edit any post or page you will see the 'Hide Title' checkbox option on the editor sidebar.
Version: 1.0.2
Author: a3rev Software
Author URI: https://a3rev.com/
Update URI: a3-hide-post-page-title
Requires at least: 4.4
Tested up to: 5.8
Text Domain: a3-hide-post-page-title
Domain Path: /languages
License: This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007

    Responsi a3 Shortcode. Plugin for the Responsi Framework.
    Copyright © 2011 a3THEMES

    a3THEMES
    admin@a3rev.com
    PO Box 1170
    Gympie 4570
    QLD Australia
*/

    // File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'A3_HPPT_PATH', dirname(__FILE__));
define( 'A3_HPPT_FOLDER', dirname(plugin_basename(__FILE__)) );
define( 'A3_HPPT_NAME', plugin_basename(__FILE__) );
define( 'A3_HPPT_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'A3_HPPT_IMAGES_URL',  A3_HPPT_URL . '/assets/images' );
define( 'A3_HPPT_JS_URL',  A3_HPPT_URL . '/assets/js' );
define( 'A3_HPPT_CSS_URL',  A3_HPPT_URL . '/assets/css' );
define( 'A3_HPPT_DIR_NAME', basename(A3_HPPT_PATH));
define( 'A3_HPPT_DIR', WP_PLUGIN_DIR . '/' . A3_HPPT_FOLDER);

define( 'A3_HPPT_KEY', 'a3_hide_post_page_title' );
define( 'A3_HPPT_VERSION', '1.0.2' );

if ( version_compare( PHP_VERSION, '5.6.0', '>=' ) ) {
    require __DIR__ . '/vendor/autoload.php';
    global $a3_hide_post_page_title_class;
    $a3_hide_post_page_title_class              = new \A3Rev\A3HidePostPageTitle\Main();
} else {
    return;
}

/**
* Load Localisation files.
*
* Note: the first-loaded translation file overrides any following ones if the same translation is present.
*
* Locales found in:
*         - WP_LANG_DIR/a3-hide-post-page-title/a3-hide-post-page-title-LOCALE.mo
*          - /wp-content/plugins/a3-hide-post-page-title/languages/a3-hide-post-page-title-LOCALE.mo (which if not found falls back to)
*          - WP_LANG_DIR/plugins/a3-hide-post-page-title-LOCALE.mo
*/
function a3_hppt_load_plugin_textdomain() {
    $locale = apply_filters( 'plugin_locale', get_locale(), 'a3-hide-post-page-title' );

    load_textdomain( 'a3-hide-post-page-title', WP_LANG_DIR . '/a3-hide-post-page-title/a3-hide-post-page-title-' . $locale . '.mo' );
    load_plugin_textdomain( 'a3-hide-post-page-title', false, A3_HPPT_FOLDER . '/languages/' );
}

function a3_hppt_install() {
    update_option( 'a3_hppt_version', A3_HPPT_VERSION );
    delete_transient( 'a3_hppt_update_info' );

    update_option( 'a3_hppt_just_installed', true );
}

/**
 * Load languages file
 */
function a3_hppt_init() {
    if ( get_option( 'a3_hppt_just_installed' ) ) {
        delete_option( 'a3_hppt_just_installed' );
    }

    a3_hppt_load_plugin_textdomain();
}

// Add language
add_action( 'init', 'a3_hppt_init' );

// Check upgrade functions
add_action( 'init', 'a3_hppt_upgrade_plugin' );
function a3_hppt_upgrade_plugin () {
    update_option( 'a3_hppt_version', A3_HPPT_VERSION );
}

function a3_hide_post_page_title_check_pin() {
    return true;
}

include( 'upgrade/plugin-upgrade.php' );

if ( ! class_exists( 'a3rev_Dashboard_Plugin_Requirement' ) ) {
    require_once ( 'a3rev-dashboard-requirement.php' );
}

/**
* Call when the plugin is activated
*/
register_activation_hook( __FILE__ , 'a3_hppt_install' );
