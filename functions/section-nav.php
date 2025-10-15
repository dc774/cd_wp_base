<?php
/**
 * Section Navigation (menu-first; page-hierarchy fallback)
 */

if (!function_exists('cd_wp_base_section_nav')) {

	/**
	 * Echo the section navigation.
	 */
	function cd_wp_base_section_nav($args = array()) {
		echo cd_wp_base_get_section_nav($args);
	}

	/**
	 * Build the section navigation HTML.
	 *
	 * Prefers the menu hierarchy (location 'primary' by default).
	 * Falls back to page hierarchy only if the current page is not present in that menu.
	 *
	 */
	function cd_wp_base_get_section_nav($args = array()) {
		if (!is_page()) {
			return '';
		}

		$args = wp_parse_args(
			$args,
			array(
				'menu_location' => 'primary',
			)
		);

		$current_page_id = get_queried_object_id();
		if (!$current_page_id) {
			return '';
		}

		// Try menu-based structure first.
		$menu_index = cd_wp_base_build_menu_index($args['menu_location']);

		if ($menu_index && isset($menu_index['by_object_id'][ $current_page_id ])) {
			// If current page is represented in the menu, render using that tree.
			return cd_wp_base_render_menu_based_nav($current_page_id, $menu_index);
		}

		// Otherwise, render using page parent/child tree.
		return cd_wp_base_render_page_based_nav($current_page_id);
	}

	function cd_wp_base_build_menu_index($location) {
		static $cache = array();
		if (isset($cache[ $location ])) {
			return $cache[ $location ];
		}

		$locations = get_nav_menu_locations();
		if (empty($locations[ $location ])) {
			return $cache[ $location ] = null;
		}

		$menu_id = (int) $locations[ $location ];
		$items	 = wp_get_nav_menu_items($menu_id, array('update_post_term_cache' => false));
		if (empty($items)) {
			return $cache[ $location ] = null;
		}

		$by_id		  = array();
		$children_map = array();
		$by_object_id = array();

		foreach ($items as $it) {
			$by_id[ $it->ID ] = $it;
		}

		foreach ($items as $it) {
			$parent = (int) $it->menu_item_parent;
			if (!isset($children_map[ $parent ])) {
				$children_map[ $parent ] = array();
			}
			$children_map[ $parent ][] = $it->ID;

			if ($it->object === 'page' && $it->object_id) {
				$pid = (int) $it->object_id;
				if (!isset($by_object_id[ $pid ])) {
					$by_object_id[ $pid ] = array();
				}
				$by_object_id[ $pid ][] = $it->ID; // keep all; first is fine
			}
		}

		return $cache[ $location ] = array(
			'by_id'		   => $by_id,
			'children'	   => $children_map,
			'by_object_id' => $by_object_id,
			'menu_id'	   => $menu_id,
		);
	}

	/**
	 * Find the topmost ancestor menu item id for a given item id.
	 */
	function cd_wp_base_root_menu_item_id($menu_item_id, $by_id) {
		$cur = $menu_item_id;
		while (isset($by_id[ $cur ]) && (int) $by_id[ $cur ]->menu_item_parent) {
			$cur = (int) $by_id[ $cur ]->menu_item_parent;
		}
		return $cur;
	}

	/**
	 * True if $maybe_ancestor is on or above $descendant in the menu tree.
	 */
	function cd_wp_base_menu_is_ancestor($maybe_ancestor, $descendant, $by_id) {
		if ($maybe_ancestor === $descendant) {
			return true;
		}
		$cur = $descendant;
		while (isset($by_id[ $cur ]) && (int) $by_id[ $cur ]->menu_item_parent) {
			$cur = (int) $by_id[ $cur ]->menu_item_parent;
			if ($cur === $maybe_ancestor) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Menu-based renderer
	 *
	 * Suppression rule:
	 *	- If the current page is the root page AND the root has no page-children in the menu,
	 *	  return '' so nothing displays.
	 */
	function cd_wp_base_render_menu_based_nav($current_page_id, $index) {
		$by_id			   = $index['by_id'];
		$kids			   = $index['children'];
		$map_page		   = $index['by_object_id'];
		$current_item_id   = $map_page[ $current_page_id ][0];
		$root_item_id	   = cd_wp_base_root_menu_item_id($current_item_id, $by_id);
		$root_page_id	   = (int) $by_id[ $root_item_id ]->object_id;

		// Direct children of the root menu node.
		$level1_ids = isset($kids[ $root_item_id ]) ? $kids[ $root_item_id ] : array();

		// Filter to page-only children since non-page items are not rendered.
		$page_child_ids = array();
		foreach ($level1_ids as $id) {
			if (isset($by_id[ $id ]) && $by_id[ $id ]->object === 'page') {
				$page_child_ids[] = $id;
			}
		}

		// Suppress on a top-level page with no children.
		if ($current_page_id === $root_page_id && empty($page_child_ids)) {
			return '';
		}

		$heading_page_id = $root_page_id;

		$h_link_classes = array();
		if ($heading_page_id !== $current_page_id) {
			$h_link_classes[] = 'current_page_ancestor';
			if (isset($by_id[ $current_item_id ])
				&& (int) $by_id[ $current_item_id ]->menu_item_parent === $root_item_id) {
				$h_link_classes[] = 'current_page_parent';
			}
		}
		$h_cls = trim(implode(' ', $h_link_classes));

		$out  = '<h2 class="menu-block-title"><a href="' . esc_url(get_permalink($heading_page_id)) . '"' . ($h_cls ? ' class="' . esc_attr($h_cls) . '"' : '') . '>' . esc_html(get_the_title($heading_page_id)) . '</a></h2>';

		$out .= '<nav class="secondary-navigation mobile-expander animate slide-down" aria-label="More in this section">';

		if (!empty($page_child_ids)) {
			$out .= '<ul class="menu">';

			foreach ($page_child_ids as $child_item_id) {
				$child_item	 = $by_id[ $child_item_id ];
				$child_pid	 = (int) $child_item->object_id;
				$child_title = get_the_title($child_pid);
				$child_url	 = get_permalink($child_pid);

				$has_kids	= !empty($kids[ $child_item_id ]);
				$is_branch	= cd_wp_base_menu_is_ancestor($child_item_id, $current_item_id, $by_id);
				$is_current = ($child_pid === $current_page_id);

				// Only mark has_children if we will actually render a submenu.
				$render_children = $has_kids && $is_branch && $child_pid !== $root_page_id;

				$li_classes = array('page_item');
				if ($render_children) { $li_classes[] = 'page_item_has_children'; }
				if ($is_current)		{ $li_classes[] = 'current_page_item'; }

				$out .= '<li class="' . esc_attr(implode(' ', $li_classes)) . '">';
				$out .= '<a href="' . esc_url($child_url) . '"' . ($is_current ? ' aria-current="page"' : '') . '>' . esc_html($child_title) . '</a>';

				if ($render_children) {
					$out .= '<ul class="children">';
					foreach ($kids[ $child_item_id ] as $gc_item_id) {
						$gc_item = $by_id[ $gc_item_id ];
						if ($gc_item->object !== 'page') {
							continue;
						}
						$gc_pid		= (int) $gc_item->object_id;
						$gc_title	= get_the_title($gc_pid);
						$gc_url		= get_permalink($gc_pid);
						$gc_classes = array('page_item');
						if ($gc_pid === $current_page_id) { $gc_classes[] = 'current_page_item'; }

						$out .= '<li class="' . esc_attr(implode(' ', $gc_classes)) . '">';
						$out .= '<a href="' . esc_url($gc_url) . '"' . ($gc_pid === $current_page_id ? ' aria-current="page"' : '') . '>' . esc_html($gc_title) . '</a>';
						$out .= '</li>';
					}
					$out .= '</ul>';
				}

				$out .= '</li>';
			}

			$out .= '</ul>';
		}

		$out .= '</nav>';
		return $out;
	}

	/**
	 * Page-based renderer.
	 *
	 * Suppression rule:
	 *	- If the current page is the root and it has no direct child pages, return ''.
	 */
	function cd_wp_base_render_page_based_nav($current_page_id) {
		$ancestors = get_post_ancestors($current_page_id);
		$root_id   = $ancestors ? (int) end($ancestors) : $current_page_id;

		// Fetch direct children of the root.
		$children = get_pages(
			array(
				'parent'	  => $root_id,
				'sort_column' => 'menu_order,post_title',
				'post_status' => array('publish'),
			)
		);

		// Suppress on a top-level page with no children.
		if ($current_page_id === $root_id && empty($children)) {
			return '';
		}

		$heading_page_id = $root_id;

		$h_link_classes = array();
		if ($heading_page_id !== $current_page_id) {
			$h_link_classes[] = 'current_page_ancestor';
			$parent_id = (int) get_post_field('post_parent', $current_page_id);
			if ($parent_id === $root_id) {
				$h_link_classes[] = 'current_page_parent';
			}
		}
		$h_cls = trim(implode(' ', $h_link_classes));

		$out  = '<h2 class="menu-block-title"><a href="' . esc_url(get_permalink($heading_page_id)) . '"' . ($h_cls ? ' class="' . esc_attr($h_cls) . '"' : '') . '>' . esc_html(get_the_title($heading_page_id)) . '</a></h2>';

		$out .= '<nav class="secondary-navigation mobile-expander animate slide-down" aria-label="More in this section">';

		if ($children) {
			$out .= '<ul class="menu">';

			foreach ($children as $child) {
				$is_current = ((int) $child->ID === $current_page_id);
				$is_branch	= $is_current || cd_wp_base_is_descendant($current_page_id, (int) $child->ID);

				// Only load grandchildren when rendering them.
				$grandkids = array();
				if ($is_branch) {
					$grandkids = get_pages(
						array(
							'parent'	  => (int) $child->ID,
							'sort_column' => 'menu_order,post_title',
							'post_status' => array('publish'),
						)
					);
				}

				$li_classes = array('page_item');
				if (!empty($grandkids)) { $li_classes[] = 'page_item_has_children'; }
				if ($is_current)			 { $li_classes[] = 'current_page_item'; }

				$out .= '<li class="' . esc_attr(implode(' ', $li_classes)) . '">';
				$out .= '<a href="' . esc_url(get_permalink($child->ID)) . '"' . ($is_current ? ' aria-current="page"' : '') . '>' . esc_html(get_the_title($child->ID)) . '</a>';

				if ($grandkids) {
					$out .= '<ul class="children">';
					foreach ($grandkids as $gc) {
						$gc_is_current = ((int) $gc->ID === $current_page_id);
						$gc_classes	   = array('page_item');
						if ($gc_is_current) { $gc_classes[] = 'current_page_item'; }

						$out .= '<li class="' . esc_attr(implode(' ', $gc_classes)) . '">';
						$out .= '<a href="' . esc_url(get_permalink($gc->ID)) . '"' . ($gc_is_current ? ' aria-current="page"' : '') . '>' . esc_html(get_the_title($gc->ID)) . '</a>';
						$out .= '</li>';
					}
					$out .= '</ul>';
				}

				$out .= '</li>';
			}

			$out .= '</ul>';
		}

		$out .= '</nav>';
		return $out;
	}

	/**
	 * True if $descendant_id is anywhere below $ancestor_id in the page tree.
	 */
	function cd_wp_base_is_descendant($descendant_id, $ancestor_id) {
		$anc = get_post_ancestors($descendant_id);
		return in_array((int) $ancestor_id, array_map('intval', $anc), true);
	}
}
