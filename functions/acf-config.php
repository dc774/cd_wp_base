<?php

// Use field group title for JSON file names instead of group key
add_filter('acf/json/save_file_name', 'cd_wp_base_acf_json_filename', 10, 3);

if (!function_exists('cd_wp_base_acf_json_filename')) {
	function cd_wp_base_acf_json_filename($filename, $settings, $load_path) {
		$group_title = $settings['title'];

		$filename = strtolower(str_replace(' ', '-', $group_title)) . '.json';

		return $filename;
	}
}

// Add synchronized JSON folders for ACF
add_filter('acf/settings/load_json', 'cd_wp_base_acf_json_load_paths');

if (!function_exists('cd_wp_base_acf_json_load_paths')) {
	function cd_wp_base_acf_json_load_paths($paths) {
		$theme_path = get_template_directory();

		// Add ACF folder for template blocks
		$paths[] = $theme_path . '/templates/blocks/custom-fields';

		return $paths;
	}
}

// Save JSON data in custom folders instead of project root
add_filter('acf/json/save_paths', 'cd_wp_base_acf_save_paths', 10, 2);

if (!function_exists('cd_wp_base_acf_save_paths')) {
	function cd_wp_base_acf_save_paths($paths, $settings) {
		$theme_path = get_template_directory();

		// Get the location of the current field group
		$fg_location = $settings['location'][0][0];

		// Check if the field group is associated with a block
		$is_block_fg = $fg_location['param'] == 'block' && $fg_location['operator'] == '==';

		// Check if the field group is associated with a post type
		$is_post_type_fg = $fg_location['param'] == 'post_type' && $fg_location['operator'] == '==';

		// If the field group is on a block
		if ($is_block_fg) {
			// Get the specific block this field group is on
			$fg_block = $fg_location['value'];

			// Get the category of that block
			$block_cat = WP_Block_Type_Registry::get_instance()->get_registered($fg_block)->category;

			// If the block is in the CD Templates category
			if ($block_cat == 'cd-templates') {
				// Set the ACF save path to the templates/blocks directory
				$paths = array( $theme_path . '/templates/blocks/custom-fields' );
			}
		}

		// If the field group is on a post type
		if ($is_post_type_fg) {
			$paths = array( $theme_path . '/functions/custom-fields' );
		}

		return $paths;
	}
}
