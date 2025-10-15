<?php

// Load values and assign defaults
$color							= get_field('color');
$accessibility_contact_option	= get_field('accessibility_contact_option');
$contact_email					= get_field('contact_email');
$page_link						= get_field('page_link');

// Assigns classes for color options
$color_class = '';
if ($color === 'Light') {
	$color_class = 'tint';
} elseif ($color === 'Red') {
	$color_class = 'dark red';
} else {
	$color_class = 'dark gray';
}

// Formats contact email as HTML link
$inline_contact_email = '';
if ($contact_email && is_email($contact_email)) {
	$inline_contact_email = "<a href=\"mailto:" . esc_attr($contact_email) . "\">" . esc_html($contact_email) . "</a>";
} else {
	$inline_contact_email = "[UNIT CONTACT EMAIL HERE]";
}

// Sets Page Link default
if (!$page_link) {
	$page_link = "https://accessibility.cornell.edu/information-technology/web-accessibility/web-accessibility-assistance/";
}

// Inner Block template
$template = [
	[
		'core/heading',
		[
			'level'		=> 2,
			'content'	=> get_bloginfo('name'),
		]
	],
	[
		'core/paragraph',
		[
			'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vitae tellus ac sem auctor sodales. Aliquam et nibh nec magna pellentesque rutrum vel a quam. Nullam tempor nibh ac augue tristique pulvinar. Sed ornare pellentesque euismod.',
		]
	]
];
$allowed_blocks = [ 'core/heading', 'core/paragraph', 'core/group', 'core/row', 'core/stack', 'core/grid', 'core/buttons', 'core/page-list' ];

?>

<footer id="site-footer" class="<?php echo esc_attr($color_class); ?>" aria-label="Site Footer">
	<div class="band padded main-footer">
		<div class="container">
			<div class="<?php if ($accessibility_contact_option === 'sentence') { echo esc_attr('high-margin'); } ?>">
				<InnerBlocks
					template="<?php echo esc_attr(wp_json_encode($template)); ?>"
					allowedBlocks="<?php echo esc_attr(wp_json_encode($allowed_blocks)); ?>"
				/>
			</div>
			<?php if ($accessibility_contact_option === 'sentence') : ?>
				<p class="small dim">If you have a disability and are having trouble accessing information on this website or need materials in an alternate format, contact us at <?php echo $inline_contact_email; ?> for assistance.</p>
			<?php endif; ?>
		</div>
	</div>

	<div class="band padded-small small sub-footer">
		<div class="container">
			<div class="subfooter-links">
				<ul>
					<li><a href="https://www.cornell.edu">Cornell University</a> © <?php echo date("Y"); ?></li>
					<li><a href="https://privacy.cornell.edu/">University Privacy</a></li>
				</ul>
				<?php if ($accessibility_contact_option === 'link') : ?>
					<ul>
						<li><a href="<?php echo esc_url($page_link); ?>">Web Accessibility Assistance</a></li>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
</footer>
