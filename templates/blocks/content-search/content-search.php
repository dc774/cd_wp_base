<?php // Search Results Content

$content_vars = cd_wp_base_content_vars();
$is_preview  = (bool) ($is_preview ?? false);
$search_term = cd_wp_base_is_site_editor() ? 'search term' : get_search_query();

// Editor placeholder inside the article area
$preview_inner = [
	['core/heading',   ['level' => 1, 'content' => 'Search Results for "search term"']],
	['core/paragraph', ['content' => 'Sorry, no results were found.']],
];

$template_no_sidebar = esc_attr(
	wp_json_encode(
		[
			['core/group', ['className' => 'article'], $preview_inner],
		]
	)
);

$template_with_sidebars = esc_attr(
	wp_json_encode(
		[
			['core/template-part', ['slug' => 'sidebar-top']],
			['core/group', ['className' => 'article'], $preview_inner],
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
						if ($content_vars['sidebars'] !== 'no-sidebar') :
							if (function_exists('block_template_part')) { ?>
								<div class="sidebar sidebar-priority">
									<?php block_template_part('sidebar-top'); ?>
								</div>
							<?php }
						endif; ?>
						<div class="article">
							<h1 id="content" tabindex="-1">
								Search Results for:
								<span class="search-term"><?php echo esc_html($search_term); ?></span>
							</h1>
							<div class="layout-cards cwd-basic">
								<div class="cards">
									<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
										<div class="card">
											<div<?php if (has_post_thumbnail()) echo ' class="group-image"'; ?>>
												<a href="<?php echo esc_url(get_permalink()); ?>">
													<?php if (has_post_thumbnail()) : ?>
														<img src="<?php echo esc_url(get_the_post_thumbnail_url(null, 'cd-medium')); ?>" alt="">
													<?php endif; ?>
													<div class="overlay">
														<h3><span class="deco"><?php the_title(); ?></span></h3>
													</div>
												</a>
											</div>
											<div class="group-fields">
												<div class="field summary">
													<?php the_excerpt(); ?>
												</div>
											</div>
										</div>
									<?php endwhile; else : ?>
										<div class="card"><?php esc_html_e('Sorry, no results were found.'); ?></div>
									<?php endif; ?>
								</div>
							</div>
							<?php echo cd_wp_base_pagination(); ?>
						</div>
						<?php if ($content_vars['sidebars'] !== 'no-sidebar') :
							if (function_exists('block_template_part')) { ?>
								<div class="sidebar sidebar-secondary">
									<?php block_template_part('sidebar-bottom'); ?>
								</div>
							<?php }
						endif;
					endif; ?>
				</div>
			</div>
		</div>
	</article>
</main>
