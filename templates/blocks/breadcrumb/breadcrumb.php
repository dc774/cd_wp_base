<?php
$breadcrumb_trail = get_post_ancestors(get_the_ID());
?>

<?php if (!is_front_page() && $breadcrumb_trail) : // Avoid displaying an empty nav element ?>
<nav class="breadcrumb" aria-label="Breadcrumb">
	<ul class="small">
		<?php foreach (array_reverse($breadcrumb_trail) as $crumb) : ?>
			<li>
				<a href="<?php echo get_permalink($crumb); ?>">
					<?php echo get_the_title($crumb); ?>
				</a>
			</li>
		<?php endforeach; ?>
		<?php if (get_field('show_current_page') === true)	: ?>
			<li>
				<?php echo get_the_title(get_the_ID()); ?>
			</li>
		<?php endif; ?>
	</ul>
</nav>
<?php endif;

// Fallback for the FSE view where no breadcrumbs exist
if (cd_wp_base_is_site_editor()) : ?>
	<nav class="breadcrumb" aria-label="Breadcrumb">
		<ul class="small">
			<li>
				<a href="<?php echo esc_url(home_url('/')); ?>">
					<span class="-limiter">Sample Breadcrumbs</span>
				</a>
			</li>
			<li>
				<a href="#">
					<span class="-limiter">Section Page</span>
				</a>
			</li>
			<li>
				<a href="#">
					<span class="-limiter">Subsection Page</span>
				</a>
			</li>
			<li>
				<span class="-limiter">Optional Current Page</span>
			</li>
		</ul>
	</nav>
<?php endif; ?>
