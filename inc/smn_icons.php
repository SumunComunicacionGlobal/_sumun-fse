<?php
/**
 * sumun icon collection
 *
 * @package smn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('init', 'smn_icons_register');

function smn_icons_register(): void
{
	$theme_name = wp_get_theme()->get( 'Name' );
	$sanitized_theme_name = sanitize_title( $theme_name );

	wp_register_icon_collection( $sanitized_theme_name, [
		'label'       => $theme_name,
		/* translators: %s: Theme name. */
		'description' => sprintf( __( 'Iconos para %s.', 'tcore' ), $theme_name ),
	]);

	$files = glob( get_template_directory() . '/assets/icons-collection/*.svg' ) ?: [];

	foreach ( $files as $file ) {
		$name = basename( $file, '.svg' );

		wp_register_icon( $sanitized_theme_name . '/' . $name, [
			'label'     => ucfirst( str_replace( [ '-', '_' ], ' ', $name ) ),
			'file_path' => $file,
		] );
	}

}