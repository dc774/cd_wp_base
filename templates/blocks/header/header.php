<?php

$header = cd_wp_base_setup_header_vars();

?>

<header id="header" class="<?php echo $header['classes']; ?>" aria-label="Site Banner">
	<?php if ($header['layout'] == 'overlay'): ?>
		<style>
			#header.photo {
				background-image: url(<?php echo $header['banner_image']; ?>);
			}
		</style>
	<?php endif; ?>

	<div id="skipnav"><a href="#content">Skip to main content</a></div>

	<!-- TODO: replace with WP search -->
	<div id="cu-search" class="cu-search closed">
		<div class="container">
			<form id="cu-search-form" tabindex="-1" role="search" action="https://www.cornell.edu/search/">
				<label for="cu-search-query" class="sr-only">Search:</label>
				<input type="text" id="cu-search-query" name="q" value="" size="30">
				<button name="btnG" id="cu-search-submit" type="submit" value="go"><span class="sr-only">Submit Search</span></button>

				<fieldset class="search-filters" role="radiogroup">
					<legend class="sr-only">Search Filters</legend>
					<input type="radio" id="cu-search-filter1" name="sitesearch" value="thissite" checked="checked">
					<label for="cu-search-filter1"><span class="sr-only">Search </span>This Site</label>
					<input type="radio" id="cu-search-filter2" name="sitesearch" value="cornell">
					<label for="cu-search-filter2"><span class="sr-only">Search </span>Cornell</label>
				</fieldset>
			</form>
		</div>
	</div>

	<div id="cu-header" class="cu-header">
		<?php if ($header['has_top_bar']): ?>
			<div class="<?php echo $header['top_bar_classes']; ?>">
				<div class="container">
					<?php if ($header['swap_seal_on_mobile']): ?>
						<div class="logo">
							<a href="https://www.cornell.edu"><img src="<?php echo $header['mini_seal_path']; ?>" alt="Cornell University" width="183" height="41"></a>
						</div>
					<?php endif; ?>

					<?php if ($header['has_top_nav']): ?>
						<nav id="utility-navigation" class="navbar nav-right dropdown-menu dropdown-menu-on-demand scripted" aria-label="Top Navigation">
							<?php
								wp_nav_menu(
									array(
										'theme_location'  => 'top-menu',
										'echo'			  => true,
										'container'		  => false,
										'fallback_cb'	  => false,
									)
								);
							?>
						</nav>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="container padded">
			<div class="<?php echo $header['seal_wrapper_classes']; ?>">
				<div class="buttons">
					<button class="mobile-button" id="cu-search-button">Toggle Search Form</button>
					<button class="mobile-button" id="mobile-nav">Main Menu</button>
				</div>

				<div class="<?php echo $header['seal_classes']; ?>">
					<a href="https://www.cornell.edu"><img src="<?php echo $header['seal_path']; ?>" alt="Cornell University" width="100" height="100"></a>
				</div>

				<div class="site-branding">
					<p class="site-title serif">
						<a href="<?php echo home_url(); ?>">
							<?php echo get_bloginfo('name'); ?>
						</a>
					</p>

					<?php if ($site_subtitle = get_bloginfo('description')): ?>
						<p class="site-subtitle">
							<?php echo $site_subtitle; ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<InnerBlocks
			template="<?php echo $header['default_blocks']; ?>"
			allowedBlocks="<?php echo $header['allowed_blocks']; ?>"
		/>
	</div>

	<?php if ($header['layout'] == 'stacked'): ?>
		<div class="responsive-banner">
			<img src="<?php echo $header['banner_image']; ?>">
		</div>
	<?php endif; ?>
</header>
