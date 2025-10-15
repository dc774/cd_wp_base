<?php // Block editor functions

// Keep only these blocks. Ref: https://developer.wordpress.org/block-editor/reference-guides/core-blocks/
add_filter('allowed_block_types_all','cd_wp_base_allowed_blocks',10,2);
	// Can be core blocks or custom blocks
if (!function_exists('cd_wp_base_allowed_blocks')) {
	function cd_wp_base_allowed_blocks($allowed,$context) {
		// No need to add block-based template parts
		return array(
			'core/audio',
			'core/block',
			'core/column',
			'core/columns',
			'cd/cover',
			'core/embed',
			'core/file',
			'core/freeform',
			'cd/gallery',
			'core/group',
			'core/heading',
			'core/html',
			'core/image',
			'cd/list',
			'cd/list-item',
			'cd/media-text',
			'cd/navigation',
			'cd/navigation-link',
			'cd/navigation-submenu',
			'core/nextpage',
			'core/paragraph',
			'core/pattern',
			'core/title',
			'cd/pullquote',
			'cd/quote',
			'core/separator',
			'core/social-link',
			'core/social-links',
			'core/template-part',
			'core/video'
		);
	} // Specific embed and social media block variations are listed in assets/js/block-variations.js
}

/**
 * Custom buttons and formats for the Classic block (TinyMCE editor)
 */
	// Add Formats dropdown to TinyMCE toolbar
if ( ! function_exists ( 'add_style_select_buttons' ) ) {
	function add_style_select_buttons( $buttons ) {
		array_unshift( $buttons, 'styleselect' );
		return $buttons;
	}
	add_filter( 'mce_buttons', 'add_style_select_buttons' );
}

// Customize the TinyMCE buttons
if( !function_exists('cwd_base_editor_mce_buttons') ){
    function cwd_base_editor_mce_buttons($buttons) { // First row
        return array(
            'formatselect','bold','italic','strikethrough',
			'alignleft','aligncenter','alignright','alignfull',
			'outdent','indent','bullist','numlist','charmap','removeformat','spellchecker',
			'undo','redo','link','unlink','image','anchor','fullscreen','table','wp_help'
        );
    }
    add_filter('mce_buttons', 'cwd_base_editor_mce_buttons', 0); // Use mce_buttons_2, 3, or 4 to add more rows.
}

if( !function_exists('cwd_base_editor_mce_buttons_2') ){
    function cwd_base_editor_mce_buttons_2($buttons) { // Second row is there by default; must be empty array to remove it
        return array('');
    }
    add_filter('mce_buttons_2', 'cwd_base_editor_mce_buttons_2', 0);
}

// Modify TinyMCE editor to hide h1 heading
if ( ! function_exists ( 'tiny_mce_remove_unused_formats' ) ) {
	function tiny_mce_remove_unused_formats( $initFormats ) {
		// Add block format elements you want to show in dropdown
		$initFormats['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6';
		return $initFormats;
	}
	add_filter( 'tiny_mce_before_init', 'tiny_mce_remove_unused_formats' );
}

// Add custom styles to Formats menu
if ( ! function_exists ( 'cwd_base_custom_styles' ) ) {

	function cwd_base_custom_styles( $init_array ) {

		$style_formats = array(
			array(
				'title' => 'Intro Text',
				'block' => 'p',
				'classes' => 'intro',
				'wrapper' => true,
			),
			array(
				'title' => 'Note',
				'inline' => 'strong',
				'classes' => 'tutorial note',
				'styles' => array(
					'color'         => '#518212',
					'fontWeight'    => 'bold',
				),
				'wrapper' => true,
			),
			array(
				'title' => 'Link Button',
				'selector' => 'a',
				'classes' => 'link-button',
			),
			array(
				'title' => 'Text Highlights',
				'items' => array(
					array(
						'title' => 'Red',
						'inline' => 'mark',
						'classes' => 'text-highlight-red',
					),
					array(
						'title' => 'Green',
						'inline' => 'mark',
						'classes' => 'text-highlight-green',
					),
					array(
						'title' => 'Gold',
						'inline' => 'mark',
						'classes' => 'text-highlight-yellow',
					),
					array(
						'title' => 'Yellow',
						'inline' => 'mark',
					),
					array(
						'title' => 'Blue',
						'inline' => 'mark',
						'classes' => 'text-highlight-blue',
					),
					array(
						'title' => 'Purple',
						'inline' => 'mark',
						'classes' => 'text-highlight-purple',
					),
				),
			),
			array(
				'title' => 'Block Quotes',
				'items' => array(
					array(
						'title' => 'Block Quote (offset)',
						'block' => 'blockquote',
						'classes' => 'offset',
						'wrapper' => true,
					),
					array(
						'title' => 'Block Quote (impact)',
						'block' => 'blockquote',
						'classes' => 'impact',
						'wrapper' => true,
					),
				),
			),
			array(
				'title' => 'Asides',
				'items' => array(
					array(
						'title' => 'Aside',
						'block' => 'aside',
						'wrapper' => true,
					),
					array(
						'title' => 'Aside Right',
						'block' => 'aside',
						'classes' => 'sidebar',
						'wrapper' => true,
					),
					array(
						'title' => 'Aside Column',
						'block' => 'aside',
						'classes' => 'column',
						'wrapper' => true,
					),
				),
			),
			array(
				'title' => 'Unordered Lists',
				'items' => array(
					array(
						'title' => 'Simple',
						'selector' => 'ul',
						'classes' => 'custom',
						'wrapper' => true,
					),
					array(
						'title' => 'Recursive',
						'selector' => 'ul',
						'classes' => 'custom recursive',
						'wrapper' => true,
					),
					array(
						'title' => 'Chevrons',
						'selector' => 'ul',
						'classes' => 'custom chevrons',
						'wrapper' => true,
					),
					array(
						'title' => 'Success Checkmarks',
						'selector' => 'ul',
						'classes' => 'custom success',
						'wrapper' => true,
					),
					array(
						'title' => 'Failure Xs',
						'selector' => 'ul',
						'classes' => 'custom failure',
						'wrapper' => true,
					),
					array(
						'title' => 'Failure Warnings',
						'selector' => 'ul',
						'classes' => 'custom warning',
						'wrapper' => true,
					),
					array(
						'title' => 'Notifications',
						'selector' => 'ul',
						'classes' => 'custom notifications',
						'wrapper' => true,
					),
					array(
						'title' => 'Status Message',
						'selector' => 'ul',
						'classes' => 'custom status',
						'wrapper' => true,
					),
					array(
						'title' => 'Lists as Menus',
						'items' => array(
							array(
								'title' => 'Basic',
								'items' => array(
									array(
										'title' => 'Horizontal',
										'selector' => 'ul',
										'classes' => 'list-menu',
										'wrapper' => true,
									),
									array(
										'title' => 'Vertical',
										'selector' => 'ul',
										'classes' => 'list-menu vertical',
										'wrapper' => true,
									)
								)
							),
							array(
								'title' => 'With Dividers',
								'items' => array(
									array(
										'title' => 'Horizontal',
										'selector' => 'ul',
										'classes' => 'list-menu divs',
										'wrapper' => true,
									),
									array(
										'title' => 'Vertical',
										'selector' => 'ul',
										'classes' => 'list-menu vertical divs',
										'wrapper' => true,
									)
								)
									),
							array(
								'title' => 'With Button Links',
								'items' => array(
									array(
										'title' => 'Horizontal',
										'selector' => 'ul',
										'classes' => 'list-menu links',
										'wrapper' => true,
									),
									array(
										'title' => 'Vertical',
										'selector' => 'ul',
										'classes' => 'list-menu vertical links',
										'wrapper' => true,
									)
								)
							)
						)
					),
				),
			),
			array(
				'title' => 'Ordered Lists',
				'items' => array(
					array(
						'title' => 'Simple',
						'selector' => 'ol',
						'classes' => 'custom',
						'wrapper' => true,
					),
					array(
						'title' => 'Recursive',
						'selector' => 'ol',
						'classes' => 'custom recursive',
						'wrapper' => true,
					),
					array(
						'title' => 'Large',
						'selector' => 'ol',
						'classes' => 'custom large',
						'wrapper' => true,
					),
				),
			),
			array(
				'title' => 'Horizontal Rules',
				'items' => array(
					array(
						'title' => 'Default',
						'block' => 'hr',
						'wrapper' => false,
					),
					array(
						'title' => 'Blue-Green',
						'block' => 'hr',
						'classes' => 'accent1',
						'wrapper' => false,
					),
					array(
						'title' => 'Blue',
						'block' => 'hr',
						'classes' => 'accent2',
						'wrapper' => false,
					),
					array(
						'title' => 'Purple',
						'block' => 'hr',
						'classes' => 'accent3',
						'wrapper' => false,
					),
					array(
						'title' => 'Gold',
						'block' => 'hr',
						'classes' => 'accent4',
						'wrapper' => false,
					),
					array(
						'title' => 'Green',
						'block' => 'hr',
						'classes' => 'accent5',
						'wrapper' => false,
					),
					array(
						'title' => 'Invisible (no visible line, spacer only)',
						'block' => 'hr',
						'classes' => 'invisible',
						'wrapper' => false,
					),
					array(
						'title' => 'Dotted',
						'block' => 'hr',
						'classes' => 'dotted',
						'wrapper' => false,
					),
					array(
						'title' => 'Dashed',
						'block' => 'hr',
						'classes' => 'dashed',
						'wrapper' => false,
					),
					array(
						'title' => 'Double',
						'block' => 'hr',
						'classes' => 'double',
						'wrapper' => false,
					),
					array(
						'title' => 'Heavy',
						'block' => 'hr',
						'classes' => 'heavy',
						'wrapper' => false,
					),
					array(
						'title' => 'Extra-Heavy',
						'block' => 'hr',
						'classes' => 'extra-heavy',
						'wrapper' => false,
					),
					array(
						'title' => 'Faded',
						'block' => 'hr',
						'classes' => 'fade',
						'wrapper' => false,
					),
					array(
						'title' => 'Flourish',
						'block' => 'hr',
						'classes' => 'flourish',
						'wrapper' => false,
					),
					array(
						'title' => 'Cornell Icon',
						'block' => 'hr',
						'classes' => 'bigred',
						'wrapper' => false,
					),
					array(
						'title' => 'Section Break (section divider with extra spacing)',
						'block' => 'hr',
						'classes' => 'section-break',
						'wrapper' => false,
					),
				),
			),
			array(
				'title' => 'Panels',
				'items' => array(
					array(
						'title' => 'Default',
						'block' => 'div',
						'wrapper' => true,
						'classes' => 'fill panel',
					),
					array(
						'title' => 'Blue-Green',
						'block' => 'div',
						'wrapper' => true,
						'classes' => 'accent-blue-green fill panel',
					),
					array(
						'title' => 'Blue',
						'block' => 'div',
						'wrapper' => true,
						'classes' => 'accent-blue fill panel',
					),
					array(
						'title' => 'Purple',
						'block' => 'div',
						'wrapper' => true,
						'classes' => 'accent-purple fill panel',
					),
					array(
						'title' => 'Gold',
						'block' => 'div',
						'wrapper' => true,
						'classes' => 'accent-gold fill panel',
					),
					array(
						'title' => 'Green',
						'block' => 'div',
						'wrapper' => true,
						'classes' => 'accent-green fill panel',
					),
					array(
						'title' => 'Red',
						'block' => 'div',
						'wrapper' => true,
						'classes' => 'accent-red fill panel',
					),
				),
			),
			array(
				'title' => 'Tables',
				'items' => array(
					array(
						'title' => 'Default',
						'selector' => 'table',
						'classes' => 'table'
					),
					array(
						'title' => 'Bordered',
						'selector' => 'table',
						'classes' => 'table bordered',
					),
					array(
						'title' => 'Flat',
						'selector' => 'table',
						'classes' => 'table flat',
					),
					array(
						'title' => 'Striped',
						'selector' => 'table',
						'classes' => 'table striped',
					),
					array(
						'title' => 'Flat + Striped',
						'selector' => 'table',
						'classes' => 'table flat striped',
					),
					array(
						'title' => 'Colored',
						'selector' => 'table',
						'classes' => 'table striped colored',
					),
					array(
						'title' => 'Flat + Colored',
						'selector' => 'table',
						'classes' => 'table flat striped colored',
					),
					array(
						'title' => 'Rainbow',
						'selector' => 'table',
						'classes' => 'table striped rainbow',
					),
					array(
						'title' => 'Flat + Rainbow',
						'selector' => 'table',
						'classes' => 'table flat striped rainbow',
					),
				),
			),
		);
		$init_array['style_formats'] = json_encode( $style_formats );

		return $init_array;

	}
	add_filter( 'tiny_mce_before_init', 'cwd_base_custom_styles' );
}

// Load TinyMCE table plugin and add button in Classic block
add_filter('mce_external_plugins',function($plugins){
	$plugins['table']=get_stylesheet_directory_uri().'/assets/js/tinymce/plugins/table/plugin.min.js';
	return $plugins;
},10,1);
