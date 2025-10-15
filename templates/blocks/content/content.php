<?php

$content_vars = cd_wp_base_content_vars();
$is_preview   = (bool) ($is_preview ?? false); // True when in the editor
$content      = (string) ($content ?? ''); // HTML of InnerBlocks

?>

<main id="main" class="band" tabindex="-1" data-offset="480">
	<article id="main-article">
		<div class="<?php echo $content_vars['sidebar_classes']; ?>">
			<div class="<?php echo $content_vars['layout_classes']; ?>">
				<div class="layout">
					<?php if ($is_preview) : ?>
						<?php if ($content_vars['sidebars'] === 'no-sidebar') : ?>
							<InnerBlocks
								template='[
									["cd/article",{}]
								]'
								allowedBlocks='[
									"cd/article"
								]'
							/>
						<?php else : ?>
							<InnerBlocks
								template='[
									["core/template-part", {"slug":"sidebar-top"}],
									["cd/article",{}],
									["core/template-part", {"slug":"sidebar-bottom"}]
								]'
								allowedBlocks='[
									"core/template-part",
									"cd/article"
								]'
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
