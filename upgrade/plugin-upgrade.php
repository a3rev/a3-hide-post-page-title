<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

class a3_Hppt_Upgrade
{

	protected $plugin_key     = A3_HPPT_KEY;
	protected $plugin_version = A3_HPPT_VERSION;
	protected $plugin_path    = A3_HPPT_NAME;
	protected $cloudfront_url = 'https://db2oxwmn8orjn.cloudfront.net';

	public function __construct() {

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );

		add_action( 'install_plugins_pre_plugin-information', array( $this, 'display_changelog' ) );

		add_filter( 'plugins_api_result', array( $this, 'make_compatibility' ), 11, 3 );

		// Defined this plugin as external so that WordPress don't call to the WordPress.org Plugin Install API
		add_filter( 'plugins_api', array( $this, 'is_external' ), 11, 3 );

	}

	public function check_update( $update_plugins_option ) {

		// Don't use this feature if a3rev Dashboard plugin is activated on this customer's website and logged in as a3 member
		if ( function_exists( 'is_a3_club_membership' ) && is_a3_club_membership() ) {
			return $update_plugins_option;
		}

		$request = wp_remote_get( $this->cloudfront_url . '/' . $this->plugin_key . '/info.json' );

		if ( is_wp_error( $request ) ) {
			return $update_plugins_option;
		}

		$body = wp_remote_retrieve_body( $request );

		$remote_data = json_decode( $body );

		if ( empty( $remote_data ) ) {
			return $update_plugins_option;
		}

		$remote_data = (array) $remote_data;

		if ( ! isset( $remote_data['version'] ) || empty( $remote_data['version'] ) ) {
			return $update_plugins_option;
		}

		if ( version_compare( $this->plugin_version, $remote_data['version'], '<' ) ) {
			global $wp_version;

			if ( empty( $update_plugins_option->response[$this->plugin_path] ) ) {
				$update_plugins_option->response[$this->plugin_path] = new stdClass();
			}

			$update_plugins_option->response[$this->plugin_path]->url            = "https://www.a3rev.com";
			$update_plugins_option->response[$this->plugin_path]->slug           = $this->plugin_key;
			$update_plugins_option->response[$this->plugin_path]->plugin         = $this->plugin_path;
			$update_plugins_option->response[$this->plugin_path]->package        = '';
			$update_plugins_option->response[$this->plugin_path]->new_version    = $remote_data['version'];
			$update_plugins_option->response[$this->plugin_path]->upgrade_notice = '';
			$update_plugins_option->response[$this->plugin_path]->tested         = $wp_version;
			$update_plugins_option->response[$this->plugin_path]->id             = "0";
		}

		return $update_plugins_option;
	}

	//Displays current version details on Plugin's page
	public function display_changelog() {
		if ( $_REQUEST["plugin"] != $this->plugin_key ) {
			return;
		}

		$request = wp_remote_get( $this->cloudfront_url . '/' . $this->plugin_key . '/changelog.txt' );

		if ( is_wp_error( $request ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $request );

		if ( ! empty( $body ) ) {
			echo wpautop( $body );
			exit;
		}

		return;
	}

	public function make_compatibility( $info, $action, $args ) {
		global $wp_version;
		$cur_wp_version = preg_replace('/-.*$/', '', $wp_version);
		if ( $action == 'plugin_information' ) {
			if ( version_compare( $wp_version, '3.7', '<=' ) ) {
				if ( is_object( $args ) && isset( $args->slug ) && $args->slug == $this->plugin_key ) {
					$info->tested = $wp_version;
				}
			} elseif ( version_compare( $wp_version, '3.7', '>' ) && is_array( $args ) && isset( $args['body']['request'] ) ) {
				$request = unserialize( $args['body']['request'] );
				if ( $request->slug == $this->plugin_key ) {
					$info->tested = $wp_version;
				}
			}
		}
		return $info;
	}

	public function is_external( $external, $action, $args ) {
		if ( 'plugin_information' == $action ) {
			if ( is_object( $args ) && isset( $args->slug ) &&  $this->plugin_key == $args->slug ) {
				global $wp_version;
				$external = array(
					'tested'  => $wp_version
				);
				$external = (object) $external;
			}
		}
		return $external;
	}
}

new a3_Hppt_Upgrade();

?>