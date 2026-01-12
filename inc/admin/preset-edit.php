<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_enqueue_scripts', 'hmpro_admin_enqueue_color_picker' );

function hmpro_admin_enqueue_color_picker( $hook ) {
	// Only load on our edit page.
	if ( empty( $_GET['page'] ) || 'hmpro-preset-edit' !== $_GET['page'] ) {
		return;
	}

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	// Small inline init.
	wp_add_inline_script(
		'wp-color-picker',
		"jQuery(function($){ $('.hmpro-color').wpColorPicker(); });"
	);
}

function hmpro_render_preset_edit_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$preset_id = isset( $_GET['preset'] ) ? sanitize_key( wp_unslash( $_GET['preset'] ) ) : '';
	$preset    = hmpro_get_preset_by_id( $preset_id );

	if ( ! $preset ) {
		echo '<div class="wrap"><h1>Preset not found</h1><p>Invalid preset id.</p></div>';
		return;
	}

	// Save handler.
	if ( isset( $_POST['hmpro_save_preset'] ) ) {
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

		hmpro_update_preset( $preset_id, $data );

		wp_safe_redirect( admin_url( 'admin.php?page=hmpro-preset-edit&preset=' . rawurlencode( $preset_id ) . '&hmpro_saved=1' ) );
		exit;
	}

	$back_url = admin_url( 'admin.php?page=hmpro-presets' );
	?>
	<div class="wrap hmpro-admin">
		<h1>Edit Preset</h1>

		<?php if ( isset( $_GET['hmpro_saved'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>Preset saved.</p></div>
		<?php endif; ?>

		<p><a class="button" href="<?php echo esc_url( $back_url ); ?>">← Back to Presets</a></p>

		<form method="post">
			<?php wp_nonce_field( 'hmpro_save_preset_' . $preset_id ); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="name">Name</label></th>
						<td><input name="name" id="name" type="text" class="regular-text" value="<?php echo esc_attr( $preset['name'] ?? '' ); ?>"></td>
					</tr>
				</tbody>
			</table>

			<h2>Colors</h2>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="primary">Primary</label></th>
						<td><input name="primary" id="primary" class="hmpro-color" type="text" value="<?php echo esc_attr( $preset['primary'] ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="dark">Dark</label></th>
						<td><input name="dark" id="dark" class="hmpro-color" type="text" value="<?php echo esc_attr( $preset['dark'] ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="bg">Background</label></th>
						<td><input name="bg" id="bg" class="hmpro-color" type="text" value="<?php echo esc_attr( $preset['bg'] ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="footer">Footer</label></th>
						<td><input name="footer" id="footer" class="hmpro-color" type="text" value="<?php echo esc_attr( $preset['footer'] ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="link">Link</label></th>
						<td><input name="link" id="link" class="hmpro-color" type="text" value="<?php echo esc_attr( $preset['link'] ?? '' ); ?>"></td>
					</tr>
				</tbody>
			</table>

			<h2>Fonts</h2>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="body_font">Body Font</label></th>
						<td>
							<select name="body_font" id="body_font">
								<?php
								$fonts = [ 'system', 'Inter', 'Poppins', 'Lato', 'Open Sans', 'Playfair Display' ];
								foreach ( $fonts as $f ) {
									printf(
										'<option value="%1$s" %2$s>%3$s</option>',
										esc_attr( $f ),
										selected( (string) ( $preset['body_font'] ?? 'system' ), (string) $f, false ),
										esc_html( $f )
									);
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="heading_font">Heading Font</label></th>
						<td>
							<select name="heading_font" id="heading_font">
								<?php
								$fonts = [ 'system', 'Inter', 'Poppins', 'Lato', 'Open Sans', 'Playfair Display' ];
								foreach ( $fonts as $f ) {
									printf(
										'<option value="%1$s" %2$s>%3$s</option>',
										esc_attr( $f ),
										selected( (string) ( $preset['heading_font'] ?? 'system' ), (string) $f, false ),
										esc_html( $f )
									);
								}
								?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>

			<p>
				<button type="submit" class="button button-primary" name="hmpro_save_preset" value="1">Save Preset</button>
				<a class="button" href="<?php echo esc_url( $back_url ); ?>">Cancel</a>
			</p>
		</form>
	</div>
	<?php
}
