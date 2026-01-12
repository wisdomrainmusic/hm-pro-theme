<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function hmpro_render_presets_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Handle actions (Set Active).
	if ( isset( $_GET['hmpro_action'], $_GET['preset'], $_GET['_wpnonce'] ) && $_GET['hmpro_action'] === 'set_active' ) {
		$preset_id = sanitize_key( wp_unslash( $_GET['preset'] ) );
		$nonce_ok  = wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'hmpro_set_active_preset' );

		if ( $nonce_ok ) {
			hmpro_set_active_preset_id( $preset_id );
			wp_safe_redirect( admin_url( 'admin.php?page=hmpro-presets' ) );
			exit;
		}
	}

	$presets   = hmpro_get_presets();
	$active_id = hmpro_get_active_preset_id();
	?>
	<div class="wrap hmpro-admin">
		<h1>HM Pro Theme — Presets</h1>

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
									admin_url( 'admin.php?page=hmpro-presets&hmpro_action=set_active&preset=' . rawurlencode( $preset['id'] ) ),
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
