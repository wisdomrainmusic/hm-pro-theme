<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function hmpro_render_presets_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Admin notices
	if ( isset( $_GET['hmpro_notice'] ) ) {
		$notice = sanitize_key( wp_unslash( $_GET['hmpro_notice'] ) );
		if ( 'preset_activated' === $notice ) {
			echo '<div class="notice notice-success is-dismissible"><p>Preset activated.</p></div>';
		} elseif ( 'seeded' === $notice ) {
			echo '<div class="notice notice-success is-dismissible"><p>Sample presets added.</p></div>';
		} elseif ( 'already_seeded' === $notice ) {
			echo '<div class="notice notice-info is-dismissible"><p>Sample presets already exist.</p></div>';
		} elseif ( 'preset_not_found' === $notice ) {
			echo '<div class="notice notice-error is-dismissible"><p>Preset not found.</p></div>';
		} elseif ( 'nonce_failed' === $notice ) {
			echo '<div class="notice notice-error is-dismissible"><p>Security check failed. Please try again.</p></div>';
		}
	}

	$presets   = hmpro_get_presets();
	$active_id = hmpro_get_active_preset_id();
	?>
	<div class="wrap hmpro-admin">
		<h1>HM Pro Theme — Presets</h1>
		<?php
		$seed_url = wp_nonce_url(
			admin_url( 'admin.php?page=hmpro-presets&hmpro_action=seed_presets' ),
			'hmpro_seed_presets'
		);
		?>
		<p>
			<a class="button" href="<?php echo esc_url( $seed_url ); ?>">Add Sample Presets</a>
		</p>

		<p>Manage global color and typography presets for HM Pro Theme.</p>

		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th>Name</th>
					<th>Primary</th>
					<th>Background</th>
					<th>Fonts</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $presets as $preset ) : ?>
					<?php
					$id        = esc_html( $preset['id'] ?? '' );
					$name      = esc_html( $preset['name'] ?? '' );
					$primary   = esc_html( $preset['primary'] ?? '' );
					$bg        = esc_html( $preset['bg'] ?? '' );
					$fonts     = esc_html( ( $preset['body_font'] ?? 'system' ) . ' / ' . ( $preset['heading_font'] ?? 'system' ) );
					$is_active = ( ! empty( $preset['id'] ) && $preset['id'] === $active_id );
					?>
					<tr>
						<td><strong><?php echo $name; ?></strong></td>
						<td><?php echo $primary; ?></td>
						<td><?php echo $bg; ?></td>
						<td><?php echo $fonts; ?></td>
						<td>
							<?php if ( $is_active ) : ?>
								<span class="button button-primary" style="pointer-events:none;">Active</span>
							<?php else : ?>
								<?php
								$url = wp_nonce_url(
				admin_url( 'admin.php?page=hmpro-presets&hmpro_action=set_active&preset=' . rawurlencode( sanitize_key( (string) ( $preset['id'] ?? '' ) ) ) ),
									'hmpro_set_active_preset'
								);
								?>
								<a class="button" href="<?php echo esc_url( $url ); ?>">Set Active</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
