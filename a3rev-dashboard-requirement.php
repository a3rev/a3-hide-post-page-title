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
		add_action( 'admin_footer', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_notices', array( $this, 'install_notice' ), 11 );
		add_action( 'update-custom_install-a3rev-dashboard-plugin', array( $this, 'install_plugin' ) );
		add_action( 'wp_ajax_a3rev_dashboard_required_dismiss', array( $this, 'dismiss_notice' ) );
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

	public function admin_enqueue_scripts() {
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.a3rev-dashboard-required-dismiss').on('click', function() {
				$('.a3rev-dashboard-required-notice').hide();
				$.ajax({
					url: '<?php echo esc_url( admin_url( 'admin-ajax.php', 'relative' ) ); ?>',
					type: 'POST',
					data: {
						action: 'a3rev_dashboard_required_dismiss',
						security: '<?php echo esc_js( wp_create_nonce( 'a3rev_dashboard_required_dismiss' ) ); ?>'
					}
				});
			});
		});
	</script>
	<?php
	}

	public function install_notice() {

		if ( get_transient( 'a3rev_dashboard_required_dismiss' ) ) return;

		if ( $this->is_activated() ) return;

		// Check if it's installed so need to ask customer activate a3rev Dashboard plugin
	?>
	<div class="error below-h1 a3rev-dashboard-required-notice" style="display:block !important; margin-left:2px; position: relative;">
		<p><?php echo wp_kses_post( sprintf( __( 'Please install <a title="" href="%s" target="_parent">a3rev Dashboard</a> to receive the auto updates and streamlined support that is included in your a3rev.com subscriptions.', 'a3-hide-post-page-title' ), $this->is_installed() ? $this->activate_url() : $this->install_url() ) ); ?></p>
		<button type="button" class="notice-dismiss a3rev-dashboard-required-dismiss"><span class="screen-reader-text"><?php echo esc_html__( 'Dismiss this notice.', 'a3-hide-post-page-title' ); ?></span></button>
	</div>
    <?php
	}

	public function dismiss_notice() {
		check_ajax_referer( 'a3rev_dashboard_required_dismiss', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1, 403 );
		}

		// set dismiss notice for 1 week
		set_transient( 'a3rev_dashboard_required_dismiss', 1, WEEK_IN_SECONDS );

		wp_die();
	}

	public function install_plugin() {
		$plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

		if ( ! current_user_can('install_plugins') )
			wp_die( esc_html__( 'You do not have sufficient permissions to install plugins on this site.', 'a3-hide-post-page-title' ) );

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		check_admin_referer( 'install-a3rev-dashboard-plugin_' . $plugin );

		$api                 = new stdClass();
		$api->name           = __( 'a3rev Dashboard', 'a3-hide-post-page-title' );
		$api->slug           = $plugin;
		$api->version        = '3.6.0';
		$api->author         = __( 'a3rev Software', 'a3-hide-post-page-title' );
		$api->screenshot_url = '';
		$api->homepage       = 'https://a3rev.com';
		$api->download_link  = $this->download_url;

		$title        = __( 'a3rev Dashboard Install', 'a3-hide-post-page-title' );
		$parent_file  = 'plugins.php';
		$submenu_file = 'plugin-install.php';

		load_template(ABSPATH . 'wp-admin/admin-header.php');

		$title = sprintf( __( 'Installing a3rev Dashboard Plugin: %s', 'a3-hide-post-page-title' ), $api->name . ' ' . $api->version );
		$nonce = 'install-a3rev-dashboard-plugin_' . $plugin;
		$url   = 'update.php?action=install-a3rev-dashboard-plugin&plugin=' . urlencode( $plugin );

		if ( isset( $_GET['from'] ) ) {
			$url .= '&from=' . urlencode( sanitize_text_field( wp_unslash( $_GET['from'] ) ) );
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