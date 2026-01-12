<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function hmpro_render_presets_page() {
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
				<tr>
					<td><strong>Default</strong></td>
					<td>#111111</td>
					<td>#ffffff</td>
					<td>System / System</td>
					<td>
						<button class="button button-primary">Active</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php
}
