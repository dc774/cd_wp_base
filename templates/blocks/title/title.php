<?php // Show customizable page titles based on context

$post_title = get_the_title();

?>

<h1 id="content" tabindex="-1">

<?php
if(is_page() || is_single() || cd_wp_base_is_post_editor()) : // Regular pages and single posts
		echo $post_title;
	elseif(is_search()) : // Search results
		$search_term = sanitize_text_field($_GET['s']);
		echo 'Search Results for: <span class="search-term">"' . esc_html($search_term) . '"</span>';
	elseif(is_404()) : // 404 Page
		echo 'Page Not Found';
	elseif (cd_wp_base_is_site_editor()) : // Placeholder for the template editor where no title is set
		echo 'Page Title';
endif;
	?>

</h1>
