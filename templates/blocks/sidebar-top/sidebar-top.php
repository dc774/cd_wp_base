<?php // Top sidebar

$content    = (string)($content ?? ''); // HTML of InnerBlocks
$is_preview = (bool)($is_preview ?? false); // True when in the editor
$layout = cd_wp_base_template_field(['field_68a35548a985c','sidebars']);

$template = [
	[
		'core/heading',
		[
			'level'		=> 2,
			'content'	=> 'Priority (Top) Sidebar',
		]
	],
	[
		'core/paragraph',
		[
			'content' => 'Mussum Ipsum, cacilds vidis litro abertis. Suco de cevadiss deixa as pessoas mais interessantis. Si u mundo tá muito paradis? Toma um mé que o mundo vai girarzis! Casamentiss faiz malandris se pirulitá.',
		]
	]
];

if ($layout !== 'no-sidebar') :

	// Get section nav if needed
	cd_wp_base_section_nav();

	if ($is_preview) : // Seed the parent block in the editor ?>
		<InnerBlocks template="<?php echo esc_attr(wp_json_encode($template)); ?>" />
			<?php
	else:
		echo $content; // Rendered inner blocks on the front end
	endif;

endif;
