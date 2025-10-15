/**
 * Any and all block variations can be added to this file. Ref: https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations
*/

// Restrict embed and social media block variations to the ones listed here
var cd_wp_base_allowed_embed = ['twitter','youtube','vimeo','dailymotion','tiktok','tumblr','bluesky'];
var cd_wp_base_allowed_social = ['bluesky','facebook','feed','flickr','instagram','linkedin','mail','tiktok','tumblr','vimeo','whatsapp','x','youtube'];

// Unregister all variations except an allow-list
function cd_wp_base_unregister_all_except(blockName,allowed){
	var list = wp.blocks.getBlockVariations(blockName) || [];
	for (var i = 0; i < list.length; i++) {
		var v = list[i];
		if (allowed.indexOf(v.name) === -1) {
			wp.blocks.unregisterBlockVariation(blockName, v.name);
		}
	}
}

// Apply all removals
function cd_wp_base_apply_variations(){
	cd_wp_base_unregister_all_except('core/embed',cd_wp_base_allowed_embed);
	cd_wp_base_unregister_all_except('core/social-link',cd_wp_base_allowed_social);
}

// Initialize
function cd_wp_base_init_variations(){
	if (!window.wp || !wp.blocks || !wp.domReady) return;
	cd_wp_base_apply_variations();
}

// Hook
if (window.wp && wp.domReady) {
	wp.domReady(cd_wp_base_init_variations);
}
