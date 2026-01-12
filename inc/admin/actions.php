<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'hmpro_handle_admin_actions' );

function hmpro_handle_admin_actions() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( empty( $_GET['hmpro_action'] ) ) {
		return;
	}

	$action = sanitize_key( wp_unslash( $_GET['hmpro_action'] ) );

	if ( 'seed_presets' === $action ) {
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'hmpro_seed_presets' ) ) {
			$back = wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=hmpro-presets' );
			wp_safe_redirect( add_query_arg( [ 'hmpro_notice' => 'nonce_failed' ], $back ) );
			exit;
		}

		$ok   = hmpro_seed_sample_presets();
		$back = wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=hmpro-presets' );
		$back = remove_query_arg( [ 'hmpro_action', '_wpnonce' ], $back );
		wp_safe_redirect( add_query_arg( [ 'hmpro_notice' => ( $ok ? 'seeded' : 'already_seeded' ) ], $back ) );
		exit;
	}

	if ( 'set_active' !== $action ) {
		return;
	}

	$preset_id = isset( $_GET['preset'] ) ? sanitize_key( wp_unslash( $_GET['preset'] ) ) : '';

	if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'hmpro_set_active_preset' ) ) {
		$back = wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=hmpro-theme' );
		wp_safe_redirect( add_query_arg( [ 'hmpro_notice' => 'nonce_failed' ], $back ) );
		exit;
	}

	$ok = hmpro_set_active_preset_id( $preset_id );

	$back = wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=hmpro-theme' );
	$back = remove_query_arg( [ 'hmpro_action', 'preset', '_wpnonce' ], $back );

	wp_safe_redirect( add_query_arg( [ 'hmpro_notice' => ( $ok ? 'preset_activated' : 'preset_not_found' ) ], $back ) );
	exit;
}
