<?php
/**
 * Enqueue mandatory styles and scripts for the new Cornell Custom Dev WordPress theme.
 *
 * These files are pulled from Github using Composer.  You can use Composer to install the latest
 * version of CDS if one exists.  Do not manually add CDS files to the theme.
 * Do not copy this code block to your child theme.
 *
*/

add_action('enqueue_block_assets', 'cd_wp_base_enqueue_cds_required');

function cd_wp_base_enqueue_cds_required() {
	// Font files
	wp_enqueue_style('cds-typekit', 'https://use.typekit.net/nwp2wku.css');
	wp_enqueue_style('cds-fonts-custom', get_parent_theme_file_uri('assets/cds/fonts/cornell-custom.css'));
	wp_enqueue_style('cds-fonts-fa', get_parent_theme_file_uri('assets/cds/fonts/font-awesome.min.css'));
	wp_enqueue_style('cds-fonts-material', get_parent_theme_file_uri('assets/cds/fonts/material-design-iconic-font.min.css'));

	// Base CSS
	wp_enqueue_style('cds-style', get_parent_theme_file_uri('assets/cds/css/base.css'));

	// Globally required scripts
	wp_enqueue_script('cds-pep-js', get_parent_theme_file_uri('assets/cds/js/contrib/pep.js'));
	wp_enqueue_script('cds-script', get_parent_theme_file_uri('assets/cds/js/cds.js'), array('jquery'));
	wp_enqueue_script('cds-menus', get_parent_theme_file_uri('assets/cds/js/cds_menus.js'), array('jquery'));

	// Block Editor exclusive styles.
	if (is_admin()) {
		wp_enqueue_style('cds-block-editor-style', get_parent_theme_file_uri('assets/css/editor.css'));
	}

}

/**
 * Enqueue optional styles and scripts for the Cornell Custom Dev WordPress theme.
 *
 * If there are any optional styles or scripts that you want to include in the child theme,
 * copy the cd_wp_base_enqueue_cds_optional() function below and add it to your child theme's
 * functions.php file. You can then uncomment the lines you want to use.
 *
 * These scripts are only meant to fire on the front end, not the block editor.
 *
 */

add_action('wp_enqueue_scripts', 'cd_wp_base_enqueue_cds_optional', 100);

if (!function_exists('cd_wp_base_enqueue_cds_optional')) {
	function cd_wp_base_enqueue_cds_optional() {
		// Optional JS
		// wp_enqueue_script('cds-experimental', get_parent_theme_file_uri('assets/cds/js/cds_experimental.js'), array('jquery'));
		// wp_enqueue_script('cds-card-slider', get_parent_theme_file_uri('assets/cds/js/cds_card_slider.js'), array('jquery'));
		// wp_enqueue_script('cds-motion', get_parent_theme_file_uri('assets/cds/js/cds_motion.js'), array('jquery'));
		// wp_enqueue_script('cds-tooltips', get_parent_theme_file_uri('assets/cds/js/cds_tooltips.js'), array('jquery'));
	}
}

// Block editor exclusive scripts. Note all dependencies.
add_action('enqueue_block_editor_assets', 'cd_wp_base_block_editor_assets');
if (!function_exists('cd_wp_base_block_editor_assets')) {
	function cd_wp_base_block_editor_assets() {
		// Block editor JS
		wp_enqueue_script('cds-block-editor', get_parent_theme_file_uri('assets/js/block-editor.js'), array('wp-plugins', 'wp-edit-post', 'wp-edit-site', 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n', 'wp-dom-ready', 'wp-rich-text', 'wp-hooks', 'wp-block-editor'), null, true);
		// Block variations JS
		wp_enqueue_script('cds-block-variations', get_parent_theme_file_uri('assets/js/block-variations.js'), array('wp-blocks', 'wp-dom-ready'), false, true);
		wp_add_inline_style('wp-components', '.cds-block-editor p{margin:0;font-size:12px;opacity:.85}');
	}
}
