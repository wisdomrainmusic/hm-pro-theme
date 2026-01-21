<?php
/**
 * Asset file for HM Product Full Tabs editor script.
 *
 * NOTE:
 * block.json uses "file:./index.js" so WordPress expects this file.
 * If it's missing, WP emits PHP warnings which can break REST responses
 * ("Response is not a valid JSON response").
 */
return array(
	'dependencies' => array(
		'wp-blocks',
		'wp-element',
		'wp-i18n',
		'wp-components',
		'wp-block-editor',
		'wp-editor',
	),
	'version' => '0.1.0',
);
