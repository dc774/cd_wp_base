<?php
/**
 * Put fallback.png at: /wp-content/themes/cd_wp_base/cd-wp-base/assets/images/fallback.png
 * This affects pages and any CPT that supports thumbnails.
 */

// Get fallback.png URL (filterable for overrides)
function cd_wp_base_fallback_image_url(): string {
	$url = trailingslashit(get_stylesheet_directory_uri()).'assets/images/fallback.png';
	return apply_filters('cd_wp_base_fallback_image_url', $url);
}

// Get the option key that stores the virtual attachment ID in the database
function cd_wp_base_fallback_option_key(): string {
	return 'cd_wp_base_virtual_fallback_attachment_id';
}

// Get/create the "virtual" attachment ID that points to the fallback URL
function cd_wp_base_get_virtual_fallback_attachment_id(): int {
	$key = cd_wp_base_fallback_option_key();
	$id  = (int)get_option($key, 0);
	$url = cd_wp_base_fallback_image_url();

	// Get mime type from URL path (png/jpg/webp). Default to image/png.
	$path   = (string)parse_url($url, PHP_URL_PATH);
	$ft     = wp_check_filetype($path);
	$mime   = $ft['type'] ?: 'image/png';

	// If we already have an attachment, keep it synced to the current $url and $mime
	if($id > 0) {
		$att = get_post($id);
		if($att instanceof WP_Post && $att->post_type === 'attachment') {
			$needs_update = false;
			// The attachment's "guid" is where WP thinks the file lives (the fallback image URL)
			if((string)get_post_field('guid', $id) !== $url) {
				$needs_update = true;
			}
			if(get_post_mime_type($id) !== $mime) {
				$needs_update = true;
			}
			if($needs_update) {
				wp_update_post(
					[
						'ID'            => $id,
						'guid'          => $url,
						'post_mime_type'=> $mime,
					]
				);
			}
			return $id; // Existing and synced
		}
	}

	// If there is no fallback image URL...
	if($url === '') {
		return 0;
	}

	// Create a placeholder media attachment that points to the fallback image URL
	$id = wp_insert_attachment(
		[
			'post_mime_type' => $mime,
			'post_title'     => 'Featured Image',
			'post_content'   => '',
			'post_status'    => 'inherit',
			'guid'           => $url,
		]
	);

	if(is_wp_error($id) || !$id) {
		return 0;
	}

	// Update the database
	update_option($key, (int)$id, true);
	return (int)$id;
}

// Get attachment ID - returns true if it is our virtual fallback ID
function cd_wp_base_is_virtual_fallback_id(int $attachment_id): bool {
	return $attachment_id > 0 && $attachment_id === cd_wp_base_get_virtual_fallback_attachment_id();
}

// Fool WP by creating a virtual thumbnail ID for posts that don't have a featured image
add_filter('post_thumbnail_id', 'cd_wp_base_virtual_fallback_thumbnail_id', 10, 2);
function cd_wp_base_virtual_fallback_thumbnail_id($thumbnail_id, $post) {
	$post_id = is_object($post) ? (int)$post->ID : (int)$post;
	if(!$post_id) {
		return $thumbnail_id;
	}
	// Leave real thumbnails alone
	if(!empty($thumbnail_id)) {
		return $thumbnail_id;
	}
	// Exclude the 'post' post type. Include CPTs that support thumbnails.
	$type = get_post_type($post_id);
	if($type === 'post' || !post_type_supports($type, 'thumbnail')) {
		return $thumbnail_id;
	}
	// Get virtual thumbnail ID if available
	$fallback_id = cd_wp_base_get_virtual_fallback_attachment_id();
	return $fallback_id ?: $thumbnail_id;
}

// Filter has_post_thumbnail() and return true when there is a fallback image
add_filter('has_post_thumbnail', 'cd_wp_base_has_thumbnail_consider_virtual', 10, 3);
function cd_wp_base_has_thumbnail_consider_virtual($has_thumbnail, $post, $thumbnail_id) {
	$post = get_post($post);
	if(!$post) {
		return $has_thumbnail;
	}
	if($has_thumbnail) {
		return true;
	}
	$type = get_post_type($post);
	if($type === 'post' || !post_type_supports($type, 'thumbnail')) {
		return false;
	}
	// If we can supply a virtual ID, report true (has_thumbnail)
	return cd_wp_base_get_virtual_fallback_attachment_id() > 0;
}

// Short-circuit image resolution for our fallback image (it will not be processed in the media library)
add_filter('image_downsize', 'cd_wp_base_image_downsize_for_virtual', 10, 3);
function cd_wp_base_image_downsize_for_virtual($out, $attachment_id, $size) {
	if(!cd_wp_base_is_virtual_fallback_id((int)$attachment_id)) {
		return $out; // Not ours. Let WP continue as usual.
	}
	$url = cd_wp_base_fallback_image_url();
	if($url === '') {
		return false;
	}
	// No physical sizes. Return URL and omit intrinsic dimensions.
	return [$url, 0, 0, false];
}

// Filter wp_get_attachment_image_src and return the fallback image URL (this is a safety net)
add_filter('wp_get_attachment_image_src', 'cd_wp_base_image_src_for_virtual', 10, 4);
function cd_wp_base_image_src_for_virtual($image, $attachment_id, $size, $icon) {
	if($image !== false) {
		return $image;
	}
	if(!cd_wp_base_is_virtual_fallback_id((int)$attachment_id)) {
		return $image;
	}
	$url = cd_wp_base_fallback_image_url();
	return $url ? [$url, 0, 0, false] : $image;
}

// Clean up attributes for our virtual image when rendering
add_filter('wp_get_attachment_image_attributes', 'cd_wp_base_image_attrs_for_virtual', 10, 3);
function cd_wp_base_image_attrs_for_virtual($attr, $attachment, $size) {
	if(!cd_wp_base_is_virtual_fallback_id((int)$attachment->ID)) {
		return $attr;
	}
	unset($attr['width'], $attr['height']);
	$classes = trim(($attr['class'] ?? '').' cd-fallback-image');
	if($classes !== '') {
		$attr['class'] = $classes;
	}
	$attr['loading'] = $attr['loading'] ?? 'lazy';
	$attr['decoding']= $attr['decoding'] ?? 'async';
	return $attr;
}
