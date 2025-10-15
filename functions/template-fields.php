<?php
/**
 * template-fields.php
 * --------------------------------------------------------------------------
 * PURPOSE
 *   Read configuration values (ACF or plain block attrs) from the ACTIVE block
 *   template chain, preferring DB-edited copies over theme files, and following
 *   template parts. Works across all template contexts.
 *
 * EDITOR AWARENESS (uses cd_wp_base_is_site_editor)
 *   Site Editor preview does not populate $post or $_GET. It *does* pass
 *   a "p=" parameter in the referrer URL. Example:
 *     /wp-admin/site-editor.php?p=/wp_template/<theme>/<stylesheet>//<slug>&canvas=edit
 *   This file now parses that referrer key and resolves the exact template.
 *
 * SCOPE
 *   • Only slugs that exist in the theme baseline are considered, plus their DB edits.
 *   • Ad-hoc DB-only templates with no theme file are ignored.
 */

/* ============================================================================
 * PUBLIC API
 * ==========================================================================*/

/**
 * Return the FIRST matching value for ANY requested field from the ACTIVE template chain.
 *
 * @param string|array $fields One key/name or an ordered list (e.g. ['field_68a35548a985c','sidebars']).
 * @return string|null         The value, or null if nothing matched.
 */
function cd_wp_base_template_field(string|array $fields): ?string {
	static $memo = [];
	$needles    = is_array($fields) ? $fields : [$fields];

	$in_editor = function_exists('cd_wp_base_is_site_editor') && cd_wp_base_is_site_editor();
	$memo_key  = wp_json_encode([$needles, $in_editor, get_queried_object_id()]);
	if(isset($memo[$memo_key])) return $memo[$memo_key];

	$templates = cd_wp_base_template_maps();

	// 1) SITE EDITOR: scan the exact template/part being edited.
	if($in_editor){
		$content = cd_wp_base_editor_template_content();
		if(is_string($content) && $content !== ''){
			$val = cd_wp_base_template_scan_for($content, $templates, $needles);
			if($val !== '') return $memo[$memo_key] = $val;
		}
		// If nothing found, keep going to front-end resolution and heuristics.
	}

	// 2) FRONT-END (and fallback in editor): resolve active slug, DB-first then theme.
	$slug = cd_wp_base_template_slug($templates) ?: 'index';

	$template = $templates['custom']['wp_template'][$slug]
		?? $templates['theme']['wp_template'][$slug]
		?? ($templates['theme']['wp_template']['index'] ?? null);

	if($template && is_string($template->content ?? null)){
		$val = cd_wp_base_template_scan_for($template->content, $templates, $needles);
		if($val !== '') return $memo[$memo_key] = $val;
	}

	// 3) EDITOR LAST-RESORT HEURISTIC (keeps preview sane if exact template unknown).
	if($in_editor){
		$order = ['front-page','home','page','single','archive','index'];
		foreach(['custom','theme'] as $bucket){
			foreach($order as $s){
				if(isset($templates[$bucket]['wp_template'][$s]) && is_string($templates[$bucket]['wp_template'][$s]->content ?? null)){
					$val = cd_wp_base_template_scan_for($templates[$bucket]['wp_template'][$s]->content, $templates, $needles);
					if($val !== '') return $memo[$memo_key] = $val;
				}
			}
			foreach($templates[$bucket]['wp_template'] as $obj){
				if(is_string($obj->content ?? null)){
					$val = cd_wp_base_template_scan_for($obj->content, $templates, $needles);
					if($val !== '') return $memo[$memo_key] = $val;
				}
			}
		}
	}

	return $memo[$memo_key] = null;
}

/* ============================================================================
 * SITE EDITOR: RESOLVE CURRENT TEMPLATE CONTENT
 * ==========================================================================*/

/**
 * Get the template or template-part HTML the Site Editor is editing right now.
 *
 * Order of attempts:
 *   a) global $post (when it IS wp_template/wp_template_part)
 *   b) URL params (postType/postId, templateType/templateId) — not present in your env
 *   c) Referrer "p=" query (present in your env) → parse → resolve via get_block_template()
 *   d) Return null if not resolvable
 *
 * @return string|null post_content-like HTML, or null if not resolvable.
 */
function cd_wp_base_editor_template_content(): ?string {
	// a) $post is a template or part (not present in your logs but harmless to try)
	global $post;
	if($post && is_object($post) && isset($post->post_type) && in_array($post->post_type, ['wp_template','wp_template_part'], true)){
		$c = (string)($post->post_content ?? '');
		if($c !== '') return $c;
	}

	// b) $_GET route (your logs showed empty, so skip quickly)
	$content = cd_wp_base__resolve_editor_template_via_params($_GET ?? []);
	if(is_string($content) && $content !== '') return $content;

	// c) Referrer "p=" route. Example from your log:
	//    /wp-admin/site-editor.php?p=/wp_template/cd_wp_base/cd-wp-base//page-no-sidebars&canvas=edit
	$ref = wp_get_referer();
	if(is_string($ref) && $ref !== ''){
		$qs = parse_url($ref, PHP_URL_QUERY);
		if(is_string($qs) && $qs !== ''){
			parse_str($qs, $ref_qs);
			$p = isset($ref_qs['p']) ? (string)$ref_qs['p'] : '';
			if($p !== ''){
				$p = urldecode($p);

				// Determine type from the path.
				$type = (strpos($p, '/wp_template_part/') !== false) ? 'wp_template_part' : 'wp_template';

				// Extract slug after the first "//"
				$slug = null;
				$pos  = strpos($p, '//');
				if($pos !== false){
					$tail = substr($p, $pos + 2);          // e.g. "page-no-sidebars" or "page-no-sidebars/..."
					$slug = strtok($tail, '/');             // first segment
				}

				// Build "<theme>//<slug>" using the current stylesheet for safety.
				if($slug){
					$theme = get_stylesheet();
					$id    = $theme.'//'.$slug;

					if(function_exists('get_block_template')){
						$t = get_block_template($id, $type);
						if($t && !empty($t->content)) return (string)$t->content;
					}
				}
			}
		}
	}

	// d) Give up.
	return null;
}

/**
 * Helper: resolve editor template content from an array of params.
 * Accepts either:
 *   postType=wp_template|wp_template_part + postId=(int or theme//slug)
 *   templateType=wp_template|wp_template_part + templateId=(int or theme//slug)
 *
 * @param array $params
 * @return string|null
 */
function cd_wp_base__resolve_editor_template_via_params(array $params): ?string {
	$candidates = [
		['type' => (string)($params['postType']     ?? ''), 'id' => $params['postId']     ?? null],
		['type' => (string)($params['templateType'] ?? ''), 'id' => $params['templateId'] ?? null],
	];

	foreach($candidates as $p){
		$type = $p['type'];
		$raw  = $p['id'];
		if(!$type || !$raw) continue;
		if(!in_array($type, ['wp_template','wp_template_part'], true)) continue;

		$id = is_string($raw) ? urldecode($raw) : $raw;

		// Numeric ID path
		if(ctype_digit((string)$id)){
			$maybe = get_post((int)$id);
			if($maybe && $maybe->post_type === $type){
				$c = (string)($maybe->post_content ?? '');
				if($c !== '') return $c;
			}
			continue;
		}

		// Theme identifier "theme//slug"
		if(function_exists('get_block_template')){
			$t = get_block_template((string)$id, $type);
			if($t && !empty($t->content)) return (string)$t->content;
		}
	}
	return null;
}

/* ============================================================================
 * TEMPLATE + PART MAPS (THEME-DEFINED + DB EDITS)
 * ==========================================================================*/

/**
 * Build a catalog of theme templates/parts plus their DB edits (theme-backed only).
 *
 * Includes:
 *  - Theme files (page.html, search.html, parts/header.html, …)
 *  - DB edits of those files (source "custom")
 *
 * Skips:
 *  - DB-only templates made ad-hoc in the Site Editor with no theme file
 *
 * @return array{theme:array{wp_template:array,wp_template_part:array},custom:array{wp_template:array,wp_template_part:array}}
 */
function cd_wp_base_template_maps(): array {
	static $cache = null;
	if($cache !== null) return $cache;

	$theme = wp_get_theme()->get_stylesheet();

	$templates = [
		'custom' => ['wp_template'=>[], 'wp_template_part'=>[]],
		'theme'  => ['wp_template'=>[], 'wp_template_part'=>[]],
	];

	// Baseline: which slugs exist as real files in this theme
	$allow = ['wp_template'=>[], 'wp_template_part'=>[]];

	foreach(['wp_template','wp_template_part'] as $type) {
		foreach(get_block_templates(['theme'=>$theme], $type) as $template) {
			if(!empty($template->has_theme_file)) {
				$slug = (string)$template->slug;
				$allow[$type][$slug] = true;
				$templates['theme'][$type][$slug] = $template;
			}
		}
	}

	// DB edits for theme-backed slugs only
	foreach(['wp_template','wp_template_part'] as $type) {
		foreach(get_block_templates(['theme'=>$theme], $type) as $template) {
			$slug = (string)$template->slug;
			if(isset($allow[$type][$slug]) && $template->source === 'custom') {
				$templates['custom'][$type][$slug] = $template;
			}
		}
	}

	return $cache = $templates;
}

/* ============================================================================
 * SCAN A TEMPLATE/PART FOR ANY OF THE REQUESTED FIELDS
 * ==========================================================================*/

/**
 * Search a template’s block tree for ANY of the requested keys/names.
 * For each key, checks attrs.data[key] (ACF) then attrs[key] (plain attr).
 * Recurses into innerBlocks and follows core/template-part (DB-first, then theme).
 *
 * @param string $html
 * @param array  $templates
 * @param array  $needles   Keys/names to try in order
 * @return string           First non-empty match, or '' if none
 */
function cd_wp_base_template_scan_for(string $html, array $templates, array $needles): string {
	if($html === '') return '';

	$blocks = parse_blocks($html);

	foreach($blocks as $block) {
		$attrs = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];

		// Try each requested key/name at common locations
		foreach($needles as $key) {
			if(isset($attrs['data'][$key])) return (string)$attrs['data'][$key];
			if(isset($attrs[$key]))        return (string)$attrs[$key];
		}

		// Recurse children
		if(!empty($block['innerBlocks'])) {
			$got = cd_wp_base_template_scan_for(serialize_blocks($block['innerBlocks']), $templates, $needles);
			if($got !== '') return $got;
		}

		// Follow template part
		if(($block['blockName'] ?? '') === 'core/template-part') {
			$slug = (string)($attrs['slug'] ?? '');
			if($slug !== '') {
				$part = $templates['custom']['wp_template_part'][$slug]
					?? $templates['theme']['wp_template_part'][$slug]
					?? null;

				if($part && is_string($part->content ?? null)) {
					$got = cd_wp_base_template_scan_for($part->content, $templates, $needles);
					if($got !== '') return $got;
				}
			}
		}
	}

	return '';
}

/* ============================================================================
 * RESOLVE THE ACTIVE TEMPLATE SLUG FOR THIS REQUEST
 * ==========================================================================*/

/**
 * Decide which wp_template slug applies to THIS front-end request.
 * Rules:
 *  - Singular with assigned template → normalize "wp_template:slug" → "slug"
 *  - is_front_page → front-page if present, else page, else index
 *  - is_home       → home if present, else page, else index
 *  - otherwise     → common slugs (search, 404, archive, …) else index
 *
 * @param array $templates
 * @return string|null
 */
function cd_wp_base_template_slug(array $templates): ?string {
	if(is_singular()) {
		$id = get_queried_object_id();
		$template = is_page() ? get_page_template_slug($id) : get_post_meta($id, '_wp_page_template', true);
		if(is_string($template) && $template !== '') {
			if(strpos($template, ':') !== false) {
				$template = explode(':', $template, 2)[1];
			}
			return $template;
		}
	}

	if(is_front_page()) {
		if(isset($templates['theme']['wp_template']['front-page'])) return 'front-page';
		if(isset($templates['theme']['wp_template']['page']))       return 'page';
		return 'index';
	}

	if(is_home()) {
		if(isset($templates['theme']['wp_template']['home'])) return 'home';
		if(isset($templates['theme']['wp_template']['page'])) return 'page';
		return 'index';
	}

	if(is_search())   return 'search';
	if(is_404())      return '404';
	if(is_page())     return 'page';
	if(is_single())   return 'single';
	if(is_category()) return 'category';
	if(is_tag())      return 'tag';
	if(is_author())   return 'author';
	if(is_date())     return 'date';
	if(is_tax())      return 'taxonomy';
	if(is_archive())  return 'archive';

	return 'index';
}

/* ============================================================================
 * USAGE
 * ==========================================================================*
 *
 * // Layout (prefer key, then name)
 * $layout = cd_wp_base_template_field(['field_68a35548a985c','sidebars']);
 *
 * // Any other ACF field
 * $hero_style = cd_wp_base_template_field(['field_hero_key','hero_style']);
 *
 * // Plain block attribute
 * $banner = cd_wp_base_template_field('banner_variant');
 */
