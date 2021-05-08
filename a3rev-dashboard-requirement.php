<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class a3rev_Dashboard_Plugin_Requirement
{

	protected $plugin       = 'a3rev-dashboard';
	protected $plugin_path  = 'a3rev-dashboard/a3rev-dashboard.php';
	protected $download_url = 'https://db2oxwmn8orjn.cloudfront.net/a3rev_dashboard/a3rev-dashboard.zip';

	public function __construct() {

		add_action( 'admin_notices', array( $this, 'install_notice' ), 11 );
		add_action( 'update-custom_install-a3rev-dashboard-plugin', array( $this, 'install_plugin' ) );
	}

	public function is_installed() {

		if ( file_exists( WP_PLUGIN_DIR . '/' . $this->plugin ) || is_dir( WP_PLUGIN_DIR . '/' . $this->plugin ) ) {
			return true;
		}

		return false;
	}

	public function is_activated() {

		if ( $this->is_installed() && is_plugin_active( $this->plugin_path ) ) {
			return true;
		}

		return false;
	}

	public function activate_url() {

		$activate_url = add_query_arg( array(
			'action' => 'activate',
			'plugin' => $this->plugin_path,
		), self_admin_url( 'plugins.php' ) );

		$activate_url = esc_url( wp_nonce_url( $activate_url, 'activate-plugin_' . $this->plugin_path ) );

		return $activate_url;
	}

	public function install_url() {
		$install_url = add_query_arg( array(
			'action' 		=> 'install-a3rev-dashboard-plugin',
			'plugin'		=> $this->plugin,
		), self_admin_url( 'update.php' ) );

		$install_url = esc_url( wp_nonce_url( $install_url, 'install-a3rev-dashboard-plugin_' . $this->plugin ) );

		return $install_url;
	}

	public function install_notice() {

		if ( $this->is_activated() ) return;

		// Check if it's installed so need to ask customer activate a3rev Dashboard plugin
		if ( $this->is_installed() ) {
?>
	<div class="error below-h2" style="display:block !important; margin-left:2px;">
		<p><?php echo sprintf( __( 'IMPORTANT! a3 License Manager plugin has been discontinued. To continue to receive plugin updates, support and whole lot of great new features please click this link <a title="" href="%s" target="_parent">a3rev Dashboard</a> to activate it.' , 'woocommerce-categories-ultra-widget' ), $this->activate_url() ); ?></p>
	</div>
    <?php

    	// If it was not installed, need to ask customer download a3rev Dashboard plugin
		} else {		
	?>
    	<div class="error below-h2" style="display:block !important; margin-left:2px;">
    		<p><?php echo sprintf( __( 'IMPORTANT! a3 License Manager plugin has been discontinued. To continue to receive plugin updates, support and whole lot of great new features please click this link <a title="" href="%s" target="_parent">a3rev Dashboard</a> to install its replacement. Once installed, please activate it.' , 'woocommerce-categories-ultra-widget' ), $this->install_url() ); ?></p>
    	</div>
    <?php
		}
	}

	public function install_plugin() {
		$plugin = isset( $_REQUEST['plugin'] ) ? trim( $_REQUEST['plugin'] ) : '';
		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

		if ( ! current_user_can('install_plugins') )
			wp_die( __( 'You do not have sufficient permissions to install plugins on this site.', 'woocommerce-categories-ultra-widget' ) );

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		check_admin_referer( 'install-a3rev-dashboard-plugin_' . $plugin );

		$api = plugins_api( 'plugin_information', array( 'slug' => $plugin, 'fields' => array( 'sections' => false ) ) );

		$api                 = new stdClass();
		$api->name           = __( 'a3rev Dashboard', 'woocommerce-categories-ultra-widget' );
		$api->slug           = $plugin;
		$api->version        = '2.0.0';
		$api->author         = __( 'a3rev Software', 'woocommerce-categories-ultra-widget' );
		$api->screenshot_url = '';
		$api->homepage       = 'https://a3rev.com';
		$api->download_link  = $this->download_url;

		$title        = __( 'a3rev Dashboard Install', 'woocommerce-categories-ultra-widget' );
		$parent_file  = 'plugins.php';
		$submenu_file = 'plugin-install.php';

		load_template(ABSPATH . 'wp-admin/admin-header.php');

		$title = sprintf( __( 'Installing a3rev Dashboard Plugin: %s', 'woocommerce-categories-ultra-widget' ), $api->name . ' ' . $api->version );
		$nonce = 'install-a3rev-dashboard-plugin_' . $plugin;
		$url   = 'update.php?action=install-a3rev-dashboard-plugin&plugin=' . urlencode( $plugin );

		if ( isset($_GET['from']) ) {
			$url   .= '&from=' . urlencode(stripslashes($_GET['from']));
		}

		$type  = 'web'; //Install plugin type, From Web or an Upload.

		$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) );
		$upgrader->install( $api->download_link );

		load_template( ABSPATH . 'wp-admin/admin-footer.php' );
	}
}

global $a3_dashboard_plugin_requirement;
$a3_dashboard_plugin_requirement = new a3rev_Dashboard_Plugin_Requirement();

?>