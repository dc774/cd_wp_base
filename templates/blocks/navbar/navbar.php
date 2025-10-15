<?php
	$navbar = cd_wp_base_setup_navbar_vars();
?>

<?php if ($navbar['menu_exists']): ?>
	<nav id="main-navigation" class="<?php echo $navbar['classes']; ?>" aria-label="Bottom Main Navigation" data-megamenu="false" data-megamenu-masonry="false">
		<div class="container" role="application" aria-label="Dropdown Menus"><div class="focus-bounds" tabindex="-1"></div>
			<a id="mobile-home" href="#"><span class="sr-only">Home</span></a>

			<?php
				wp_nav_menu(
					array(
						$navbar['menu_arg_key'] => $navbar['menu_arg_val'],
						'echo' => true,
						'container' => false,
						'fallback_cb' => false,
					)
				);
			?>
		</div>
	</nav>
<?php endif; ?>
