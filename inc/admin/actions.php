<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'hmpro_handle_admin_actions' );

function hmpro_handle_admin_actions() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Handle preset save (POST) early to allow redirects before admin output.
	if ( isset( $_GET['page'] ) && 'hmpro-preset-edit' === sanitize_key( wp_unslash( $_GET['page'] ) ) && isset( $_POST['hmpro_save_preset'] ) ) {
		$preset_id = isset( $_GET['preset'] ) ? sanitize_key( wp_unslash( $_GET['preset'] ) ) : '';

		check_admin_referer( 'hmpro_save_preset_' . $preset_id );

		$data = [
			'name'         => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'primary'      => isset( $_POST['primary'] ) ? sanitize_text_field( wp_unslash( $_POST['primary'] ) ) : '',
			'dark'         => isset( $_POST['dark'] ) ? sanitize_text_field( wp_unslash( $_POST['dark'] ) ) : '',
			'bg'           => isset( $_POST['bg'] ) ? sanitize_text_field( wp_unslash( $_POST['bg'] ) ) : '',
			'footer'       => isset( $_POST['footer'] ) ? sanitize_text_field( wp_unslash( $_POST['footer'] ) ) : '',
			'link'         => isset( $_POST['link'] ) ? sanitize_text_field( wp_unslash( $_POST['link'] ) ) : '',
			'body_font'    => isset( $_POST['body_font'] ) ? sanitize_text_field( wp_unslash( $_POST['body_font'] ) ) : 'system',
			'heading_font' => isset( $_POST['heading_font'] ) ? sanitize_text_field( wp_unslash( $_POST['heading_font'] ) ) : 'system',
		];

		$ok = hmpro_update_preset( $preset_id, $data );

		$redirect = admin_url( 'admin.php?page=hmpro-preset-edit&preset=' . rawurlencode( $preset_id ) . '&hmpro_saved=' . ( $ok ? '1' : '0' ) );
		wp_safe_redirect( $redirect );
		exit;
	}

	$action = '';
	if ( ! empty( $_POST['hmpro_action'] ) ) {
		$action = sanitize_key( wp_unslash( $_POST['hmpro_action'] ) );
	} elseif ( ! empty( $_GET['hmpro_action'] ) ) {
		$action = sanitize_key( wp_unslash( $_GET['hmpro_action'] ) );
	}

	if ( '' === $action ) {
		return;
	}

	if ( 'update_preset' === $action ) {
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'hmpro_update_preset' ) ) {
			$back = wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=hmpro-presets' );
			wp_safe_redirect( add_query_arg( [ 'hmpro_notice' => 'nonce_failed' ], $back ) );
			exit;
		}

		$preset_id = isset( $_POST['preset_id'] ) ? sanitize_key( wp_unslash( $_POST['preset_id'] ) ) : '';
		$fields    = [
			'name',
			'primary',
			'dark',
			'bg',
			'footer',
			'link',
			'body_font',
			'heading_font',
		];
		$data      = [];

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$data[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
			}
		}

		$ok   = hmpro_update_preset( $preset_id, $data );
		$back = admin_url( 'admin.php?page=hmpro-preset-edit&preset=' . rawurlencode( $preset_id ) );
		wp_safe_redirect( add_query_arg( [ 'hmpro_notice' => ( $ok ? 'updated' : 'update_failed' ) ], $back ) );
		exit;
	}

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
