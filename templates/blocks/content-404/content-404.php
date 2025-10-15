<?php // Page Not Found Content

$content_vars = cd_wp_base_content_vars();
$is_preview = (bool) ($is_preview ?? false);
$content    = (string) ($content ?? '');

// Default inner blocks shown inside the article area (editor only)
$default_404_inner = array(
	['cd/breadcrumb', []],
	['core/heading', ['level' => 1, 'content' => 'Page Not Found']],
	['core/paragraph', ['content' => 'The page you are looking for doesn\'t exist, or it has been moved. Try a <span class="send-to-search">search</span>?']],
);

// Editor templates
$template_no_sidebar = esc_attr(
	wp_json_encode(
		[
			['core/group', ['className' => 'article'], $default_404_inner],
		]
	)
);
$template_with_sidebars = esc_attr(
	wp_json_encode(
		[
			['core/template-part', ['slug' => 'sidebar-top']],
			['core/group', ['className' => 'article'], $default_404_inner],
			['core/template-part', ['slug' => 'sidebar-bottom']],
		]
	)
);
?>
<main id="main" class="band" tabindex="-1" data-offset="480">
	<article id="main-article">
		<div class="<?php echo $content_vars['sidebar_classes']; ?>">
			<div class="<?php echo $content_vars['layout_classes']; ?>">
				<div class="layout">
					<?php if ($is_preview) : ?>
						<?php if ($content_vars['sidebars'] === 'no-sidebar') : ?>
							<InnerBlocks
								template="<?php echo $template_no_sidebar; ?>"
								allowedBlocks='["core/group"]'
							/>
						<?php else : ?>
							<InnerBlocks
								template="<?php echo $template_with_sidebars; ?>"
								allowedBlocks='["core/template-part","core/group"]'
							/>
						<?php endif;
					else :
						echo $content; // Rendered inner blocks
					endif; ?>
				</div>
			</div>
		</div>
	</article>
</main>
