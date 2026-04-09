<?php
namespace A3Rev\A3HidePostPageTitle;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Editor_Sidebar {

	const META_KEY = '_a3hpt_headertitle';

	public function __construct() {
		add_action( 'init', array( $this, 'register_meta_fields' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_sidebar_assets' ) );
	}

	public function register_meta_fields() {
		foreach ( Main::supported_post_types() as $post_type ) {
			if ( ! is_string( $post_type ) || '' === $post_type ) {
				continue;
			}
			register_post_meta(
				$post_type,
				self::META_KEY,
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'boolean',
					'auth_callback' => function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				)
			);
		}
	}

	public function enqueue_sidebar_assets() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || empty( $screen->post_type ) ) {
			return;
		}

		if ( ! in_array( $screen->post_type, Main::supported_post_types(), true ) ) {
			return;
		}

		if ( ! function_exists( 'use_block_editor_for_post_type' ) || ! use_block_editor_for_post_type( $screen->post_type ) ) {
			return;
		}

		$script_path = A3_HPPT_DIR . '/assets/js/a3-hppt-editor-sidebar.js';
		$version     = defined( 'A3_HPPT_VERSION' ) ? A3_HPPT_VERSION : '1.0.0';
		if ( is_readable( $script_path ) ) {
			$version .= '.' . (string) filemtime( $script_path );
		}

		wp_enqueue_script(
			'a3-hppt-editor-sidebar',
			A3_HPPT_URL . '/assets/js/a3-hppt-editor-sidebar.js',
			array( 'wp-plugins', 'wp-editor', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ),
			$version,
			true
		);

		wp_localize_script(
			'a3-hppt-editor-sidebar',
			'a3HpptEditorSidebar',
			array(
				'metaKey' => self::META_KEY,
				'i18n'    => array(
					'panelTitle'   => __( 'Hide Page and Post Title', 'a3-hide-post-page-title' ),
					'checkboxLabel' => __( 'Hide the title.', 'a3-hide-post-page-title' ),
				),
			)
		);
	}
}
