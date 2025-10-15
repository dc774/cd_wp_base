// Cornell Design System UI (last update: 7/7/25)
// - @TODO: Most of these components will move to individual JavaScript files
// ---------------------------------------------------------------------------
// 01. Cornell Search
// 00. Tablists
// 00. Accordions
// 00. Modal Dialog
// 00. Back-to-Top Button
// 00. jQuery Easing Functions (mimicking jQuery UI)
	
		
jQuery(document).ready(function($) {	
	
	// 01. Cornell Search ------------------------------------------------------
	var mousedown = false;
	$('#cu-search, #basic-search, .navbar-search').addClass('closed');
	$('#cu-search-button').click(function(e) {
		mousedown = true;
		$('#cu-search, #basic-search, .navbar-search').toggleClass('open closed');
		$(this).toggleClass('open');		
		if ( $(this).hasClass('open') ) {
			$('#cu-search-query').focus();
		}
		else {
			$(this).focus();
			mousedown = false;
		}
	});
	$('#cu-search input, #cu-search-form').focus(function() {
		if (!mousedown) {
			$('#cu-search, #cu-search-button').removeClass('closed').addClass('open');
			mousedown = false;
		}
	});
	// Search field contrast toggle
	$('#cu-header input[type="search"], #cu-header input[type="text"]').on('change keyup', function() {
		console.log($(this).val());
		if ( $(this).val() != '' && $(this).val().length > 0 && $(this).val() != undefined ) {
			$(this).addClass('filled'); // ensure high opacity when the input is active with text
		}
		else {
			$(this).removeClass('filled'); // restore default opacity when inactive
		}
	});
	
	
	// 02. Tablists ------------------------------------------------------------
	$('.ui-tabs').each(function() {
		var tabs = $(this);
		var buttons = $(this).find('[role="tablist"] button');
		var button_count = $(buttons).length;
		
		$(buttons).click(function(e) {
			let target_panel = '#' + $(this).attr('aria-controls');
			$(tabs).find('[role="tablist"] button').attr('aria-selected','false').attr('tabindex','-1');
			$(tabs).find('[role="tabpanel"]').attr('hidden','hidden');
			$(this).attr('aria-selected','true').attr('tabindex','0');
			$(tabs).find(target_panel).removeAttr('hidden');
		}).keyup(function(e) {
			if (e.key === 'ArrowLeft') {
				let current_focus = $(this).index();
				if (current_focus == 0) {
					$(buttons).eq(button_count-1).focus();
				}
				else {
					$(buttons).eq(current_focus-1).focus();
				}
			}
			else if (e.key === 'ArrowRight') {
				let current_focus = $(this).index();
				if (current_focus == button_count-1) {
					$(buttons).eq(0).focus();
				}
				else {
					$(buttons).eq(current_focus+1).focus();
				}
			}
		});
	});
	
	
	// 03. Accordions ----------------------------------------------------------
	$('details').each(function() {
		var details = $(this);
		
		$(this).children('summary').hover(function() { // hover(): track the <summary>'s hover events at the parent <details> level 
			$(details).addClass('hovering');
		}, function() {
			$(details).removeClass('hovering');
		}).click(function(e) { // click(): reset CSS reveal animation for repeat use
			// accordion closing
			if ( $(details).attr('open') ) {
				$(details).children('.animate-reset').removeClass('animate-reset').addClass('animate');
			}
			// accordion opening
			else {
				if ( $(details).attr('name') ) { // if part of a single-select group
					$('details[name='+$(details).attr('name')+']').prop('open', false).children('.animate-reset').removeClass('animate-reset').addClass('animate');
				}
				let reset = setTimeout(function() {
					$(details).children('.animate').removeClass('animate').addClass('animate-reset');
				}, 600); // CSS animation should be no more than 500ms long
			}
		});
	});
	$('.accordion-set').each(function() {
		var accordion_set = $(this);
		var accordion_count = $(accordion_set).find('details').length;
		
		$(accordion_set).prepend('<button class="button expand-all">Expand all</button>'); // Insert button
		
		// Toggle accordions
		$(accordion_set).find('button.expand-all').click(function() {
			if ( $(accordion_set).find('details[open]').length < accordion_count) {
				$(this).text('Close all');
				//$(accordion_set).find('details').children('.animate').removeClass('animate').addClass('animate-reset');
				$(accordion_set).find('details').prop('open', true);
				let reset = setTimeout(function() {
					$(accordion_set).find('details').children('.animate').removeClass('animate').addClass('animate-reset'); // animation reset
				}, 600);
			}
			else {
				$(this).text('Expand all');
				$(accordion_set).find('details').prop('open', false);
				$(accordion_set).find('details').children('.animate-reset').removeClass('animate-reset').addClass('animate'); // animation reset
			}
		});
		
		// Update button text on accordion click
		$(accordion_set).find('details summary').click(function() {
			if ( $(this).parent('details').prop('open') == false && $(accordion_set).find('details[open]').length == (accordion_count-1) ) {
				$(accordion_set).find('button.expand-all').text('Close all');
			}
			else {
				$(accordion_set).find('button.expand-all').text('Expand all');
			}
		});
	});
	
	
	// 04. Modal Dialog --------------------------------------------------------
	$('.ui-modal').each(function() { // There should only be one modal, but we'll try not to preclude multiples
		var modal = $(this);
		var modal_id = '#' + $(this).attr('id');
		
		$(this).click(function(e) {
			e.stopPropagation();
		}).wrap('<div class="dialog-container">').prepend('<div class="focus-bounds" tabindex="0">').append('<div class="focus-bounds" tabindex="0">'); // Apply backdrop and focus bounds
		
		// Interface Action: Dismiss 
		$(this).find('.dialog-close, .dialog-cancel').click(function() {
			closeModal(modal_id); // Close buttons
		});
		$(this).parent('.dialog-container').click(function() {
			closeModal(modal_id); // Backdrop click
		});
		$(this).keyup(function(e) {
			if (e.key === 'Escape') {
				closeModal(modal_id); // Escape key
			}
		});
		$(this).find('.dialog-close').get(0).addEventListener('keydown', function(e) { // Convert to standard DOM object listener for e.repeat
			if (e.key === 'Enter') {
				e.preventDefault();
				if (!e.repeat) { // Ignore key repeat for Enter key
					closeModal(modal_id);
				}
			}
		});
		
		// Interface Action: Confirm
		$(this).find('.dialog-confirm').click(function() {
			
			// ** Send confirmed request and then closeModal() below **
			
			$('#messages').addClass('updated').find('.dialog-content').first().html('<p>Success! You have unsubscribed from all communications.</p>');
			animateDialog('#messages');
			closeModal(modal_id);
		});
		
		// Focus Bounds
		$(this).find('.focus-bounds').focus(function() {
			$(modal).find('.dialog-close').first().focus(); // Close buttons
		});
		
	});
	
	function openModal(modal_id) { // Note: "showModal()" is a reserved JavaScript function
		$(modal_id).parent('.dialog-container').addClass('open');
		$(modal_id).addClass('open').find('.dialog-close').first().focus();
	}
	
	function closeModal(modal_id) {
		$(modal_id).parent('.dialog-container').removeClass('open');
		$(modal_id).removeClass('open');
		$('[data-modal-requester]').first().focus().removeAttr('data-modal-requester'); // Return to requesting button and clear
	}
	
	// Unsubscribe from all communications (triggers modal)
	$('button.button-unsubscribe-all').each(function() {
		
		$(this).click(function() {
			$('[data-modal-requester]').removeAttr('data-modal-requester'); // Clear requesting button (just in case)
			$(this).attr('data-modal-requester','true'); // Set requesting button
			openModal('#modal');
		});
		var this_button = $(this); // store jQuery reference
		$(this).get(0).addEventListener('keydown', function(e) { // Convert to standard DOM object listener for e.repeat
			if (e.key === 'Enter') {
				e.preventDefault();
				if (!e.repeat) { // Ignore key repeat for Enter key
					$(this_button).trigger('click');
				}
			}
		});
	});
	
	// User Messages/Notifications Dialog
	var default_message = $('#messages .dialog-content').first().html(); // Store initial content
	var timeout_fade_reset, timeout_dismiss;
	
	$('#messages .dialog-close').click(function() {
		$('#messages').removeClass('updated show dismiss').find('.dialog-content').first().html(default_message); // Remove CSS classes and restore initial content
		if ( !$('#messages').hasClass('ui-toast') ) {
			$('#messages').focus();
		}
	});
	
	function animateDialog(dialog) { // Fade-in dialog on update
		$(dialog).removeClass('dismiss').addClass('show'); // Tied to CSS animation
		timeout_fade_reset = setTimeout(function() {
			$(dialog).removeClass('show'); // Reset for next animation
		}, 400);
		clearTimeout(timeout_dismiss);
		timeout_dismiss = setTimeout(function() {
			$(dialog).addClass('dismiss'); // Dismiss toast notification after 4 seconds
		}, 4000);
	}
	
	
	
	// 05. Back-to-Top Button --------------------------------------------------
	var show_offset = 800; // Pixels that the target must be scrolled out of view to trigger the floating button
	$('button.back-to-top').each(function() {
		var target = $(this).attr('data-target') || '#main'; // The main tag (#main) is the default target if not defined
		$(target).attr('data-offset', $(target).offset().top); // Add data-offset attribute and calculate initial offset
		
		$(this).click(function() {
			var target_offset = $(target).offset().top;
			$(target).focus();
			
			$('html, body').animate({
				scrollTop: target_offset
			}, 400, 'swing');
		});
	});
	
	function updateOffsets() { // Recalculate offset if viewport size changes
		$('[data-offset]').each(function() {
			$(this).attr('data-offset', $(this).offset().top);
		});
	}
	function updateBackToTop() { // Show or hide floating button based on scroll position
		$('button.back-to-top.floating').each(function() {
			var target = $(this).attr('data-target') || '#main';
			
			if ( parseInt($(target).attr('data-offset')) + show_offset < $(window).scrollTop() ) {
				$(this).addClass('active');
			}
			else {
				$(this).removeClass('active');
			}
		});
	}

	$(window).on('resize load', updateOffsets);
	$(window).on('scroll', updateBackToTop);
	updateBackToTop();
	
});



// 06. jQuery Easing Functions (mimicking jQuery UI) -------------------------
jQuery.easing['jswing'] = jQuery.easing['swing'];
jQuery.extend( jQuery.easing, {
	def: 'easeOutQuad',
	swing: function (x, t, b, c, d) {
		return jQuery.easing[jQuery.easing.def](x, t, b, c, d);
	},
	easeInQuad: function (x, t, b, c, d) {
		return c*(t/=d)*t + b;
	},
	easeOutQuad: function (x, t, b, c, d) {
		return -c *(t/=d)*(t-2) + b;
	},
	easeInOutQuad: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t + b;
		return -c/2 * ((--t)*(t-2) - 1) + b;
	}
});
