(function(wp){
	'use strict';

	if(!wp || !wp.hooks || !wp.richText || !wp.element || !wp.data || !wp.components || !wp.i18n) return;

	// Featured Image help text
	const {addFilter} = wp.hooks;
	const {Notice} = wp.components;
	const {__} = wp.i18n;
	const {Fragment, createElement: el, createInterpolateElement} = wp.element;

	function withFeaturedImageHelp(Original){
		return function(props){
			const type = wp.data.select('core/editor').getCurrentPostType();
			const pt = wp.data.select('core').getPostType(type);
			if(!pt?.supports?.thumbnail) return el(Original, props);

			const mediaId = wp.data.select('core/editor').getEditedPostAttribute('featured_media');

			return el(
				Fragment,
				null,
				el(Original, props),
				el(
					'div',
					{className:'cds-block-editor'},
					!mediaId && el(
						Notice,
						{status:'warning', isDismissible:false},
						createInterpolateElement(
							__('<strong>Set a featured image</strong> before publishing. If you do not, a default image will be used.', 'cd-wp-base'),
							{strong: el('strong', null)}
						)
					),
					el(
						'p', {style:{marginBottom:'8px'}}, null,
						createInterpolateElement(
							__('<strong>Featured Image Guidelines</strong>', 'cd-wp-base'),
							{strong: el('strong', null)}
						)
					),
					el(
						'p', {style:{marginBottom:'8px'}}, null,
						__('You may replace the default featured image for this post with a custom one.', 'cd-wp-base')
					),
					el(
						'p', {style:{marginBottom:'8px'}}, null,
						__('Featured images should be at least 480×480 pixels in jpg, png, or webp format and ideally less than 300KB.', 'cd-wp-base')
					),
					el(
						'p', {style:{marginBottom:'8px'}}, null,
						__('The featured image is used only as a thumbnail in places like search results and archive listings.', 'cd-wp-base')
					)
				)
			);
		};
	}
	wp.hooks.addFilter(
		'editor.PostFeaturedImage',
		'cds-block-editor/featured-image-help-text',
		withFeaturedImageHelp
	);

	// Unregister unwanted formats from the paragraph block and add custom ones
	function cd_wp_base_unregister_formats(){
		if(!wp.richText.unregisterFormatType) return;

		var remove = [
			'core/code',
			'core/image',
			'core/keyboard',
			'core/language',
			'core/subscript',
			'core/superscript',
			'core/strikethrough'
		];
		for(var i = 0; i < remove.length; i++){
			try{ wp.richText.unregisterFormatType(remove[i]); }catch(e){}
		}
	}

	if(window.wp && wp.domReady){
		wp.domReady(function(){
			cd_wp_base_unregister_formats();
		});
	}

	// Custom formats
	var cd_el = wp.element.createElement;
	var cd_RichTextToolbarButton = (wp.blockEditor || wp.editor).RichTextToolbarButton;
	var cd_toggleFormat = wp.richText.toggleFormat;
	var cd_registerFormatType = wp.richText.registerFormatType;

	// Check if paragraph block is selected
	function cd_wp_base_is_paragraph_selected(){
		var sel = wp.data.select('core/block-editor');
		var block = sel.getSelectedBlock();
		return block && block.name === 'core/paragraph';
	}

	// Intro text format
	function cd_wp_base_intro_edit(props){
		function cd_wp_base_intro_toggle(){
			props.onChange(cd_toggleFormat(props.value, {type:'cd/intro'}));
		}
		if(!cd_wp_base_is_paragraph_selected()) return null;
		return cd_el(cd_RichTextToolbarButton, {icon:'editor-textcolor', title:'Intro Text', onClick:cd_wp_base_intro_toggle, isActive:props.isActive});
	}
	cd_registerFormatType('cd/intro', {title:'Intro Text', tagName:'span', className:'intro', edit: cd_wp_base_intro_edit});

	// Link button format
	function cd_wp_base_link_btn_edit(props){
		function cd_wp_base_link_btn_toggle(){
			props.onChange(cd_toggleFormat(props.value, {type:'cd/link-button'}));
		}
		if(!cd_wp_base_is_paragraph_selected()) return null;
		return cd_el(cd_RichTextToolbarButton, {icon:'admin-links', title:'Link Button', onClick:cd_wp_base_link_btn_toggle, isActive:props.isActive});
	}
	cd_registerFormatType('cd/link-button', {title:'Link Button', tagName:'a', className:'link-button', edit: cd_wp_base_link_btn_edit});

	// Note format: strong + tutorial class. Can only add one className, so can't use the "note" class
	function cd_wp_base_note_edit(props){
		function cd_wp_base_note_toggle(){
			props.onChange(cd_toggleFormat(props.value, {type:'cd/note'}));
		}
		if(!cd_wp_base_is_paragraph_selected()) return null;
		return cd_el(cd_RichTextToolbarButton, {icon:'admin-post', title:'Note', onClick:cd_wp_base_note_toggle, isActive:props.isActive});
	}
	cd_registerFormatType('cd/note', {title:'Note', tagName:'strong', className:'tutorial', edit: cd_wp_base_note_edit});

})(window.wp);
