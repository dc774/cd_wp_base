<?php // Bottom sidebar

$content    = (string)($content ?? ''); // HTML of InnerBlocks
$is_preview = (bool)($is_preview ?? false); // True when in the editor
$layout = cd_wp_base_template_field(['field_68a35548a985c','sidebars']);

$template = [
	[
		'core/heading',
		[
			'level'		=> 2,
			'content'	=> 'Secondary (Bottom) Sidebar',
		]
	],
	[
		'core/paragraph',
		[
			'content' => 'Mussum Ipsum, cacilds vidis litro abertis. Praesent vel viverra nisi. Mauris aliquet nunc non turpis scelerisque, eget.Atirei o pau no gatis, per gatis num morreus.Detraxit consequat et quo num tendi nada.',
		]
	]
];

if ($layout !== 'no-sidebar') :

	if ($is_preview) : // Seed the parent block in the editor ?>
		<InnerBlocks template="<?php echo esc_attr(wp_json_encode($template)); ?>" />
			<?php
	else:
		echo $content; // Rendered inner blocks on the front end
	endif;

endif;
