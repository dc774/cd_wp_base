<?php
/**
 * Drop-in template tag. Works for archives, search, custom WP_Query.
 * Usage (global query): echo cd_wp_base_pagination();
 * Usage (custom query): echo cd_wp_base_pagination($custom_query);
 */
function cd_wp_base_pagination(WP_Query $query = null, array $opts = []): string {
	// Resolve the query. Fall back to the main/global query.
	if (!$query) {
		global $wp_query;
		$query = $wp_query;
	}

	// Total page count. If under 2, no pagination is necessary.
	$total = (int) $query->max_num_pages;
	if ($total < 2) {
		return '';
	}

	// Current page number.
	// 'paged' is used by most archive contexts.
	// 'page' is used by static front page and some special cases.
	$paged = max(
		1,
		(int) get_query_var('paged') ?: (int) get_query_var('page')
	);

	// Build the "base" pattern that paginate_links() needs
	$big  = 999999999;
	$base = str_replace($big, '%#%', esc_url(get_pagenum_link($big)));

	// Assemble paginate_links() args
	// You can pass any other paginate_links() args via $opts
	$args = wp_parse_args(
		$opts,
		[
			'base'      => $base,
			'format'    => '',
			'current'   => $paged,
			'total'     => $total,
			'type'      => 'array',
			'prev_text' => '«',
			'next_text' => '»',
			'mid_size'  => 2, // Count on each side of current page.
			'end_size'  => 1, // Count at each end (first/last).
		]
	);

	// Generate the pagination items.
	$items = paginate_links($args);
	if (empty($items) || !is_array($items)) {
		return '';
	}

	// Start building the final HTML.
	$out   = [];
	$out[] = '<nav class="pager page-numbers" aria-labelledby="pagination-label">';
	$out[] = '<h4 id="pagination-label" class="visually-hidden">Pages</h4><ul>';

	// Enhance prev/next for semantics, then wrap each item in <li>.
	foreach ($items as $html) {
		if (strpos($html, 'class="prev') !== false) {
			$html = preg_replace('#<a #', '<a rel="prev" aria-label="Previous page" ', $html, 1);
		}

		if (strpos($html, 'class="next') !== false) {
			$html = preg_replace('#<a #', '<a rel="next" aria-label="Next page" ', $html, 1);
		}

		$out[] = '<li>' . $html . '</li>';
	}

	// Close containers and return the string.
	$out[] = '</ul></nav>';

	return implode('', $out);
}
