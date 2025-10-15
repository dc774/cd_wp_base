<?php

/**
 * Custom category for template blocks
 *
 * This makes it easier to disable them from being used in page content
*/
add_filter('block_categories_all', 'cd_wp_base_template_block_category', 10, 2);

function cd_wp_base_template_block_category($block_categories, $block_editor_context) {
	// This category should be used in block.json for all template blocks
	array_unshift(
		$block_categories,
		array(
			'slug'	=> 'cd-templates',
			'title' => 'CD Template Blocks'
		)
	);

	return $block_categories;
}

/**
 * Custom blocks used in templates and template parts
 *
 * These blocks are distinct from custom blocks used in page content, and should
 * be located in the templates/blocks/ directory
 *
 * To override any of these blocks in a child theme, copy the block folder into
 * child-theme/templates/blocks/ and edit as needed
*/
add_action('init', 'cd_wp_base_register_template_blocks');

function cd_wp_base_register_template_blocks() {
	// Array of block folders (located in templates/blocks/)
	$template_blocks = array(
		'header',
		'navbar',
		'breadcrumb',
		'title',
		'sidebar-top',
		'content',
		'article',
		'content-search',
		'content-404',
		'sidebar-bottom',
		'footer',
	);

	// Iterate over template blocks
	foreach ($template_blocks as $template_block) {
		// Get the path for each block (checks child theme first, then base theme)
		$block_path = get_theme_file_path('templates/blocks/' . $template_block);

		// Register the block
		register_block_type($block_path);
	}
}

/**
 * Setup function for CD Site Header template block
 *
*/
if (!function_exists('cd_wp_base_setup_header_vars')) {
	function cd_wp_base_setup_header_vars() {
		// Path for all CDS images (Cornell seals & preset banner images)
		$cds_image_path = get_template_directory_uri() . '/assets/cds/images/';

		/* Configure main header wrapper */
		$header_classes_array = array('band');

		// Banner layout (stacked banner & photo vs banner overlaid on top of photo)
		$banner_layout = get_field('header_layout') ?? 'stacked';

		if ($banner_layout == 'overlay') {
			array_push($header_classes_array, 'photo');
		}

		// Banner background color
		$banner_color = get_field('header_color') ?? 'white';

		array_push($header_classes_array, $banner_color);

		// Check if the banner is dark (it's always dark in 'overlay' layout)
		$banner_is_dark = $banner_layout == 'overlay' || in_array($banner_color, array('dark-gray', 'black', 'red'));

		if ($banner_is_dark) {
			array_push($header_classes_array, 'dark');
		}

		// Combine header classes into single string
		$header_classes = implode(' ', $header_classes_array);

		/* Configure top bar (mini seal & utility nav) */
		// Check if mini seal & utility nav should be rendered
		$swap_seal_on_mobile = get_field('header_mini_seal');
		$has_top_nav = has_nav_menu('top-menu');

		// The path for the mini seal file
		$mini_seal_path = "{$cds_image_path}/cornell/cornell_reduced_white_41.svg";

		// Only render the top bar if mini seal or utility nav are present
		$include_top_bar = $swap_seal_on_mobile || $has_top_nav;

		// Setup top bar classes
		$top_bar_classes = 'navbar navbar-logo dark red fill';

		// If there's no utility nav, only show top bar (if present) on mobile
		$top_bar_classes .= !$has_top_nav ? ' mobile-only' : '';

		/* Configure seal */
		// Check if the seal is on the left or right (site name will be opposite)
		$seal_position = get_field('header_seal_position');
		$seal_wrapper_classes = "content brand logo-{$seal_position}";

		// Use white seal on dark banners and red seal on light banners
		$seal_suffix = $banner_is_dark ? 'white' : 'b31b1b';
		$seal_path = "{$cds_image_path}/cornell/cornell_seal_simple_web_{$seal_suffix}.svg";

		// If the mini seal is present on mobile, the main seal is desktop-only
		$seal_classes = 'site-logo';
		$seal_classes .= $swap_seal_on_mobile ? ' desktop-only' : '';

		/* Configure banner image(s) */
		// User can select from several preset theme images
		$theme_banner_images = get_field('header_theme_images') ?: array('plantations');

		// If 'custom' is selected, it will be the last item in the theme images array
		$use_custom_image = end($theme_banner_images) == 'custom';

		// Get custom images
		$custom_banner_images = get_field('header_custom_images');

		// If custom images are included
		if ($use_custom_image) {
			// Remove the 'custom' item from the end of the theme images array
			array_pop($theme_banner_images);
		}

		// Combine theme & custom images into single array
		$all_banner_images = array();

		// Get the path for any theme images selected
		foreach ($theme_banner_images as $banner_image) {
			$image_path = "{$cds_image_path}/slider/{$banner_image}.jpg";
			array_push($all_banner_images, $image_path);
		}

		// Get the path for any custom images uploaded
		if ($use_custom_image && $custom_banner_images) {
			foreach ($custom_banner_images as $banner_image) {
				$image_path = wp_get_attachment_image_url($banner_image, 'large');
				array_push($all_banner_images, $image_path);
			}
		}

		// Select one image at random every time the page loads
		$banner_image_url = $all_banner_images[array_rand($all_banner_images)];

		/* Configure main navigation */

		// By default, include a navbar block where the main navigation usually lives
		$default_blocks = esc_attr(
			wp_json_encode(
				array(array('cd/navbar', array()))
			)
		);

		// Only a navbar can be placed in this location
		$allowed_blocks = esc_attr(
			wp_json_encode(array('cd/navbar'))
		);

		// Wrap up all the variables that the template needs access to
		$header_vars = array(
			'classes' => $header_classes,
			'has_top_bar' => $include_top_bar,
			'top_bar_classes' => $top_bar_classes,
			'swap_seal_on_mobile' => $swap_seal_on_mobile,
			'mini_seal_path' => $mini_seal_path,
			'has_top_nav' => $has_top_nav,
			'layout' => $banner_layout,
			'seal_wrapper_classes' => $seal_wrapper_classes,
			'seal_classes' => $seal_classes,
			'seal_path' => $seal_path,
			'banner_image' => $banner_image_url,
			'default_blocks' => $default_blocks,
			'allowed_blocks' => $allowed_blocks,
		);

		return $header_vars;
	}
}

/**
 * Setup function for CD Navbar template block
 *
*/
if (!function_exists('cd_wp_base_setup_navbar_vars')) {
	function cd_wp_base_setup_navbar_vars() {
		// Determine if we're using default main-menu location or a custom menu
		$use_custom_menu = get_field('navbar_use_custom_menu');

		// Set up menu arguments based on which menu we're using
		$menu_arg_key = $use_custom_menu ? 'menu' : 'theme_location';
		$menu_arg_val = $use_custom_menu ? get_field('navbar_custom_menu') : 'main-menu';

		// Make sure the menu actually exists
		$menu_exists = $use_custom_menu ? is_nav_menu($menu_arg_val) : has_nav_menu('main-menu');

		// Start with default navbar classes
		$navbar_classes = array('navbar dropdown-menu dropdown-menu-on-demand scripted');

		// Add position class
		$nav_position = get_field('navbar_position');

		$navbar_classes[] = 'nav-' . $nav_position;

		// If navbar is centered
		if ($nav_position == 'center') {
			// Add spacing class
			$navbar_classes[] = 'nav-' . get_field('navbar_spacing');
		}

		// Get background color
		$bg_color = get_field('navbar_bg');

		// If background is transparent
		if ($bg_color == 'none') {
			// Use 'theme' field to determine if 'dark' class needs to be added
			if (get_field('navbar_theme') == 'dark') {
				$navbar_classes[] = 'dark';
			}
		} else { // If a background color is set
			// If bg color is dark (field vals of dark colors are prefixed by 'dark-')
			if (str_contains($bg_color, 'dark')) {
				// Add 'dark' class to navbar
				$navbar_classes[] = 'dark';

				// Remove the 'dark-' prefix to get the actual bg color
				$bg_color = str_replace('dark-', '', $bg_color);
			}

			// Determine if we're using a gradient fill or regular fill & add class
			if ($bg_color == 'fill-gradient') {
				$navbar_classes[] = 'gray';
			} else {
				$navbar_classes[] = 'fill';
			}

			// Add bg color class
			$navbar_classes[] = $bg_color;
		}

		// Add tall class if field is checked
		if (get_field('navbar_tall')) {
			$navbar_classes[] = 'navbar-tall';
		}

		// If the bg color is *not* red
		if ($bg_color !== 'red') {
			// Add active-red class if field is checked
			if (get_field('navbar_active_red')) {
				$navbar_classes[] = 'active-red';
			}

			// Add accent-red class if field is checked
			if (get_field('navbar_hover_red')) {
				$navbar_classes[] = 'accent-red';
			}
		}

		// Wrap up all the variables that the template needs access to
		$navbar_vars = array(
			'menu_exists' => $menu_exists,
			'menu_arg_key' => $menu_arg_key,
			'menu_arg_val' => $menu_arg_val,
			'classes' => implode(' ', $navbar_classes),
		);

		return $navbar_vars;
	}
}

/**
 * Setup functions for CD Content block
 *
*/
if (!function_exists('cd_wp_base_content_vars')) {
	function cd_wp_base_content_vars() {

		$post_title = get_the_title();
		$sidebars = get_field('sidebars');
		$tinting = get_field('tinting');
		$tinting_options = get_field('tinting_options');
		$width = get_field('width');
		$size = get_field('size');
		$sidebar_classes = ['region'];
		$layout_classes = ['container'];

		// Sidebar classes
		switch ($sidebars) {
			case 'sidebar-left':
				$sidebar_classes[] = 'sidebar-left region-sidebar';
				break;
			case 'sidebar-right':
				$sidebar_classes[] = 'sidebar-right region-sidebar';
				break;
			case 'no-sidebar':
				$sidebar_classes[] = 'no-sidebar padded';
				break;
		}

		// Layout classes (content width)
		if($sidebars === 'no-sidebar' && $width === 'article-width') :
			switch ($size) {
				case 'small':
					$layout_classes[] = 'container-small';
					break;
				case 'medium':
					$layout_classes[] = 'container-medium';
					break;
				case 'large':
					$layout_classes[] = 'container-large';
					break;
				case 'x-large':
					$layout_classes[] = 'container-full';
					break;
			}
		endif;

		// Tinting options
		if($sidebars != 'no-sidebar' && $tinting == 1) :
			$sidebar_classes[] = 'sidebar-tint';
			if($tinting_options) :
				$sidebar_classes[] = 'sidebar-tint-' . $tinting_options;
			endif;
		endif;

		$sidebar_classes = implode(' ', array_filter($sidebar_classes));
		$layout_classes = implode(' ', array_filter($layout_classes));

		$default_blocks = esc_attr(
			wp_json_encode(
				array(
					array(
						'cd/breadcrumb',
						array()
					),
					array(
						'cd/title',
						array()
					),
					array(
						'core/post-content',
						array()
					)				)
			)
		);

		// Wrap up all the variables that the template needs access to
		$content_vars = array(
			'sidebars' => $sidebars,
			'width' => $width,
			'layout_classes' => $layout_classes,
			'sidebar_classes' => $sidebar_classes,
			'default_blocks' => $default_blocks,
		);

		return $content_vars;

	}
}

// Add a custom template part category (area): "Sidebars"
add_filter('default_wp_template_part_areas', 'cd_wp_base_template_part_areas');
function cd_wp_base_template_part_areas( array $areas ) {
	$areas[] = array(
		'area'        => 'sidebar',
		'area_tag'    => 'section', // Valid tags: div, header, main, section, article, aside and footer
		'label'       => __('Sidebars', 'cd-wp-base'),
		'description' => __('Use one or both sidebar areas to display blocks in your sidebar. The "top" and "bottom" designations refer to their placement above and below the content area on mobile devices.', 'cd-wp-base'),
		'icon'        => 'sidebar'
	);
	return $areas;
}

// Remove WordPress core CSS to prevent conflicts with the theme's styles
//add_action('wp_enqueue_scripts', 'cd_wp_base_remove_wp_css', 100);
if (!function_exists('cd_wp_base_remove_wp_css')) {
	function cd_wp_base_remove_wp_css(){
		wp_dequeue_style('wp-block-library'); // Remove WordPress core CSS
		wp_dequeue_style('wp-block-library-theme'); // Remove WordPress theme core CSS
		wp_dequeue_style('classic-theme-styles'); // Remove global styles inline CSS
		wp_dequeue_style('global-styles'); // Remove theme.json css
	}
}

// Register custom pattern categories (only shows if patterns are assigned to them)
add_action('init','cd_wp_base_pattern_categories');
function cd_wp_base_pattern_categories(){
	$categories = [
		'cd/pages'     => 'Layout',
		'cd/cta'  => 'Call to Action',
		'cd/band'=> 'Full Width',
	];

	$register = WP_Block_Pattern_Categories_Registry::get_instance();

	foreach($categories as $slug => $label){
		if(!$register->is_registered($slug)){
			register_block_pattern_category(
				$slug,
				[
					'label' => __($label, 'cd-wp-base'),
				]
			);
		}
	}
}

// Detect Site Editor preview (not Customizer).
if (!function_exists('cd_wp_base_is_site_editor')) {
	/**
	 * True when rendering inside the Full Site Editor (templates or parts).
	 * Works for ACF AJAX previews and REST renders. Excludes Customizer.
	 */
	function cd_wp_base_is_site_editor(): bool {
		if (function_exists('is_customize_preview') && is_customize_preview()) return false;

		// Admin shell.
		if (is_admin()) {
			global $pagenow;
			if ($pagenow === 'site-editor.php') return true;
			if (isset($_GET['page']) && $_GET['page'] === 'gutenberg-edit-site') return true;
		}

		// Template-type signals.
		$pt	 = (string) ($_GET['postType'] ?? $_POST['postType'] ?? $_GET['post_type'] ?? $_POST['post_type'] ?? '');
		$pid = (string) ($_POST['postId'] ?? '');
		if ($pt && strpos($pt, 'wp_template') === 0) return true;
		if (strpos($pid, 'wp_template//') === 0 || strpos($pid, 'wp_template_part//') === 0) return true;

		// Preview iframe and param.
		if (!empty($_GET['wp_site_preview'])) return true;
		if (defined('IFRAME_REQUEST') && IFRAME_REQUEST && $pt && strpos($pt, 'wp_template') === 0) return true;

		// Referer (ACF ajax and REST calls).
		$ref = (string) ($_SERVER['HTTP_REFERER'] ?? '');
		if (strpos($ref, 'site-editor.php') !== false || strpos($ref, 'gutenberg-edit-site') !== false) return true;

		// current_screen if available.
		if (function_exists('get_current_screen')) {
			$screen = get_current_screen();
			if ($screen && ($screen->id === 'site-editor' || $screen->base === 'site-editor' || $screen->id === 'appearance_page_gutenberg-edit-site')) {
				return true;
			}
		}
		return false;
	}
}

if (!function_exists('cd_wp_base_is_post_editor')) {
	/**
	 * True when rendering inside the post/page block editor.
	 * Works for ACF AJAX previews and REST renders.
	 */
	function cd_wp_base_is_post_editor(): bool {
		// Admin shell.
		if (is_admin()) {
			global $pagenow;
			if ($pagenow === 'post.php' || $pagenow === 'post-new.php') return true;
		}

		// REST render inside editor canvas for non-template types.
		$ctx = (string) ($_GET['context'] ?? $_POST['context'] ?? '');
		$pt	 = (string) ($_GET['post_type'] ?? $_POST['post_type'] ?? $_GET['postType'] ?? $_POST['postType'] ?? '');
		if (defined('REST_REQUEST') && REST_REQUEST && $ctx === 'edit') {
			if ($pt === '' || strpos($pt, 'wp_template') !== 0) return true;
		}

		// Referer (ACF ajax and REST calls).
		$ref = (string) ($_SERVER['HTTP_REFERER'] ?? '');
		if (strpos($ref, 'post.php') !== false || strpos($ref, 'post-new.php') !== false) return true;

		// current_screen if available.
		if (function_exists('get_current_screen')) {
			$screen = get_current_screen();
			if ($screen && ($screen->base === 'post' || $screen->id === 'post')) return true;
		}
		return false;
	}
}

// Get page_links_to
add_filter('page_link', 'cd_wp_base_page_links_to',10,3); // Regular pages
add_filter('post_type_link', 'cd_wp_base_page_links_to',10,3); // CPTs

if (!function_exists('cd_wp_base_page_links_to')) {
	function cd_wp_base_page_links_to($link, $post_id) {
		$page_links_to = get_field('page_links_to', $post_id);
		if(!$page_links_to) {
			return esc_url($link);
		}
		else {
			return esc_url($page_links_to);
		}
	}
}
