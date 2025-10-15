<?php
/**
 * Post-setup actions.
 *
 * This function contains any post-setup actions that need to be run
 * after the theme is initialized.
 */

add_action('after_setup_theme', 'cd_wp_base_post_setup_actions');

function cd_wp_base_post_setup_actions() {
	// Re-enable classic menus support
	add_theme_support('menus');
	add_theme_support('editor-styles');

	// Enable support for image styles
	add_image_size('cd-small', 150, 150, true);
	add_image_size('cd-medium', 480, 480, true);
	add_image_size('cd-full', 846, 0, false);
	add_image_size('cd-hero', 1440, 480, true);

	// Register menu locations
	register_nav_menus(
		array(
			'top-menu' => __('Top Menu', 'cd-wp-base'), // Top Navigation
			'main-menu' => __('Main Menu', 'cd-wp-base'), // Main Navigation
		)
	);

	// Remove core block patterns
	remove_theme_support('core-block-patterns');
}
