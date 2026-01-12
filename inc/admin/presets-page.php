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
		} elseif ( 'csv_missing' === $notice ) {
			echo '<div class="notice notice-error is-dismissible"><p>Please choose a CSV file.</p></div>';
		} elseif ( 'csv_imported' === $notice ) {
			$i = isset( $_GET['i'] ) ? (int) $_GET['i'] : 0;
			$u = isset( $_GET['u'] ) ? (int) $_GET['u'] : 0;
			$c = isset( $_GET['c'] ) ? (int) $_GET['c'] : 0;
			$s = isset( $_GET['s'] ) ? (int) $_GET['s'] : 0;
			echo '<div class="notice notice-success is-dismissible"><p>CSV imported. Imported: ' . esc_html( $i ) . ', Updated: ' . esc_html( $u ) . ', Created: ' . esc_html( $c ) . ', Skipped: ' . esc_html( $s ) . '.</p></div>';
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
		<?php
		$template_url = wp_nonce_url(
			admin_url( 'admin.php?page=hmpro-presets&hmpro_action=download_csv_template' ),
			'hmpro_csv_template'
		);
		?>

		<h2>Import Presets from CSV</h2>
		<p>
			<a class="button" href="<?php echo esc_url( $template_url ); ?>">Download CSV Template</a>
		</p>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'hmpro_import_csv' ); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">CSV File</th>
						<td>
							<input type="file" name="csv_file" accept=".csv" />
							<p class="description">CSV columns: <code>name, primary, dark, bg, footer, link, body_font, heading_font</code></p>
						</td>
					</tr>
					<tr>
						<th scope="row">Import Mode</th>
						<td>
							<label>
								<input type="radio" name="import_mode" value="update" checked>
								Update if name matches (recommended)
							</label><br>
							<label>
								<input type="radio" name="import_mode" value="create">
								Always create new presets
							</label>
						</td>
					</tr>
				</tbody>
			</table>

			<p>
				<button type="submit" class="button button-primary" name="hmpro_import_csv" value="1">Import CSV</button>
			</p>
		</form>

		<hr />

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
							<?php
							$edit_url = admin_url( 'admin.php?page=hmpro-preset-edit&preset=' . rawurlencode( sanitize_key( (string) ( $preset['id'] ?? '' ) ) ) );
							?>
							<a class="button" href="<?php echo esc_url( $edit_url ); ?>">Edit</a>
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
