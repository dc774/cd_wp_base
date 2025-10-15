// Cornell Design System: Menus (last update: 12/10/24)
// - Script support for nested menus (accordions on mobile, and dropdown or "megamenu" behavior for desktop)
// ---------------------------------------------------------------------------
// 01. Mobile Expanding Menus
// 02. Dropdown Menus (ported from the previous generation CSS Framework)


jQuery(document).ready(function($) {	
	
	// 01. Mobile Expanding Menus ----------------------------------------------
	
	$('.mobile-expander').each(function() {
		// Generate a label
		let default_name = 'More'; // default for general content
		if ( $(this).get(0).tagName.toLowerCase() == 'nav' ) {
			default_name = 'Menu'; // default for navigations
		}
		let label = $(this).attr('data-custom-label') || $(this).attr('aria-label') || default_name; // custom label, aria-label, or fallback to default
		
		$(this).before('<button class="mobile-menu-toggle" aria-expanded="false"><span class="sr-only">Show </span>'+label+'</button>');
		$(this).wrap('<div class="expander-content"></div>').addClass('mobile-menu');
		
		if ( $(this).hasClass('animate') ) { $(this).parent().addClass('animate'); }
		if ( $(this).hasClass('slide-down') ) { $(this).parent().addClass('slide-down'); }
		
		$(this).parent().prevAll('.menu-block-title').first().insertBefore($(this)); // gobble up the block title as well in Drupal and WordPress
	});
	$('.mobile-menu-toggle').click(function(e) {
		$(this).toggleClass('open').next('.expander-content').toggleClass('open');
		if ( $(this).hasClass('open') ) {
			$(this).attr('aria-expanded','true').find('.sr-only').text('Hide ');
		}
		else {
			$(this).attr('aria-expanded','false').find('.sr-only').text('Show ');
		}
	});
	
	
	// 02. Dropdown Menus ------------------------------------------------------
	
	var nav_breakpoint = 960; // viewport pixel width at which desktop nav appears (should match the media query in the project's css)
	
	// Mobile Nav Setup
	$('#main-navigation ul').first().before('<button id="mobile-close"><span class="sr-only">Close menu</span></button>');
	$('#main-navigation ul').first().parent().prepend('<div class="focus-bounds" tabindex="-1">').append('<div class="focus-bounds" tabindex="-1">');
	$('#mobile-nav, #mobile-close').click(function() {
		$('body').toggleClass('mobile-menu-open');
		$('#main-navigation').toggleClass('open');
		
		if ($('#main-navigation').hasClass('open')) {
			$('#main-navigation').attr('role','dialog').attr('aria-modal','true');
			$('#main-navigation .focus-bounds').attr('tabindex','0');
			if ($('#mobile-home').length > 0) {
				$('#mobile-home').focus();
			}
			else {
				$('#mobile-close').focus();
			}
		}
		else {
			$('#main-navigation').removeAttr('role aria-modal');
			$('#main-navigation .focus-bounds').attr('tabindex','-1');
			$('#mobile-nav').focus();
		}
	});
	// Focus Bounds
	$('#main-navigation .focus-bounds').focus(function() {
		$('#mobile-home, #mobile-close').first().focus(); // Close buttons
	});
	
	function resetMenus() {
		$('.dropdown-menu, .dropdown-menu .open, .expander-content').removeClass('open');
		$('.dropdown-menu .children').attr('aria-hidden','true').find('a').attr('tabindex','-1');
		$('.mobile-menu-toggle, .mobile-submenu-toggle').removeClass('open').attr('aria-expanded','false');
	}

	
	// Window Size Tracking
	function resizeChecks() {

		// Mobile Nav
		if ($(window).width() < nav_breakpoint) {
			$('body').addClass('mobile'); // mobile nav breakpoint
			menuClearMasonry();
		}
		else {
			if ( $('body').hasClass('mobile') ) { // reset mobile menu for desktop
				resetMenus();
			}
			$('body').removeClass('mobile mobile-menu-open');
			$('.megamenu-top').removeClass('open'); // used by megamenu "nom nom" mode
			$('#main-navigation li.parent').removeClass('open');
			$('#main-navigation').removeAttr('style');
		}
	}
	$(window).resize(resizeChecks);
	resizeChecks();
	
	
	// Main Navigation
	var mousedown = false; // extra control variable for precise click and focus event interaction
	
	// Megamenu settings saved to each menu
	$('.dropdown-menu').each(function() {
		$(this).addClass('scripted'); // allows CSS to know that JavaScript is active 
		$(this).attr('data-megamenu','false'); // "megamenu" mode
		$(this).attr('data-megamenu-masonry','false'); // "megamenu" masonry mode
		if ( $(this).hasClass('dropdown-megamenu') ) {
			$(this).attr('data-megamenu','true'); // activate megamenu design and adjustments to keyboard navigation
			$(this).addClass('dropdown-menu-on-demand'); // ensure on-demand mode is active as well
			if ( $(this).hasClass('megamenu-masonry') ) {
				$(this).attr('data-megamenu-masonry','true'); // activate megamenu masonry design
			}
		}
	});

	// Utility Navigation (appended for mobile)
	if ($('#utility-navigation li').length > 0) {
		let more_label = $('#utility-navigation').attr('data-custom-label') || $('#utility-navigation').attr('aria-label') || 'More...';
		if ($('#main-navigation').length > 0) {
			$('#main-navigation ul').first().append('<li class="parent mobile-nav-only"><a class="more-links-button" href="#" role="heading" aria-level="2">'+more_label+'</a><ul aria-hidden="true" class="children">' + $('#utility-navigation ul').first().html() + '</ul></li>');
			
			$('.more-links-button').click(function(e) {
				e.preventDefault();
				$(this).next('.mobile-submenu-toggle').trigger('click');
			});
		}
	}

	// Dropdown Menus
	$('li:has(li)').addClass('parent'); // mark menu items with submenus
	$('.dropdown-menu li a').wrapInner('<span></span>'); // wrap text in a span
	
	$('.dropdown-menu li.parent').parent().removeClass('menu');
	$('.dropdown-menu li.parent > a').attr('aria-haspopup','true').append('<span class="fa fa-angle-down"></span>'); // add dropdown caret icons
	$('.dropdown-menu li.parent li.parent > a .fa').removeClass('fa-angle-down').addClass('fa-angle-right'); // change sub-dropdown caret icons
	$('.dropdown-menu-on-demand li.parent ul a').attr('tabindex','-1'); // in on-demand mode, links in dropdowns are not initially accessible by tab order
	$('.dropdown-menu-on-demand li.parent > ul').attr('aria-hidden','true'); // in on-demand mode, links in dropdowns are not initially accessible to screen reader (including rotor)
	$('.dropdown-megamenu li.parent > ul > li:first-child > a').addClass('first-child'); // in megamenu mode, used to aid with keyboard navigation
	$('.dropdown-megamenu li.parent > ul > li:last-child > a').addClass('last-child'); // in megamenu mode, used to aid with keyboard navigation
	$('.dropdown-menu li.parent > ul').each(function() {
		$(this).removeClass('menu').addClass('children');
		if ( !$('body').hasClass('mobile') ) {
			var min_width = $(this).parent('li').width();
			if (min_width < 150) {
				min_width = 150;
			}
			$(this).css('min-width',min_width+'px' ); // smart min-width to prevent dropdown from being narrower than its parent (and no smaller than 150)
		}
	});
	
	$('.dropdown-megamenu > .container > ul').addClass('megamenu-top'); // used by megamenu "nom nom" mode
	$('.dropdown-megamenu > .container > ul > li.parent > ul').each(function(n) {
		
		// in megamenu mode, catalog the number of standard menu items to allow for column-based tab order
		if ( $(this).parent().hasClass('mobile-nav-only') ) {
			return; // skip mobile-only utility nav if present
		}
		
		var row_ids = 'abcdefgh';
		var menu_items = $(this).children(':not(.menu-feature)').length;
		var max_cols = 3;
		if ($(this).children('.menu-feature').length > 0) {
			 max_cols = 2;
		}
		var max_rows = menu_items / max_cols;
		$(this).attr('data-max-rows',max_rows).attr('data-max-cols',max_cols);
		
		var col1 = Math.ceil(max_rows);
		var col2 = Math.round(max_rows);
		for (let i=0; i<col1; i++) {
			$(this).children(':not(.menu-feature)').eq(i).attr('data-row',i+1).attr('data-col','1').attr('data-position','col1'+row_ids.charAt(i)).addClass('col1'+row_ids.charAt(i));
		}
		for (let i=0; i<col2; i++) {
			$(this).children(':not(.menu-feature)').eq(i+col1).attr('data-row',i+1).attr('data-col','2').attr('data-position','col2'+row_ids.charAt(i)).addClass('col2'+row_ids.charAt(i));
		}
		if (max_cols == 3) {
			var col3 = Math.floor(max_rows);
			for (let i=0; i<col3; i++) {
			$(this).children(':not(.menu-feature)').eq(i+col1+col2).attr('data-row',i+1).attr('data-col','3').attr('data-position','col3'+row_ids.charAt(i)).addClass('col3'+row_ids.charAt(i));
			}
		}
		
		// catalog the number of sub-items for masonry calculation
		$(this).children(':not(.menu-feature)').each(function() {
			var children = $(this).children('ul').children('li').length;
			$(this).attr('data-children',children);
		});
		
	});
	
	$('.dropdown-menu li.parent li.parent > ul').removeAttr('style'); // reset min-width to allow smaller submenus
	$('.dropdown-menu').each(function() {
		var this_menu = $(this);
		var hover_intent_in; // will be a Timeout() below, used for nuanced hover detection
		var hover_intent_out; // will be a Timeout() below, used for nuanced hover detection
		
		$(this).find('li.parent').hover(function() {
			// Delay menu response on hover INTO a top level item (desktop only)
			if ( $(this).hasClass('top-level-li') && !$('body').hasClass('mobile') ) {
				clearTimeout(hover_intent_out); // cancel any hover OUT timer
				$(this_menu).find('li.parent').removeClass('open');
				$(this_menu).find('li.focused').addClass('open').parent('ul').addClass('open'); // leave the menu visible if it contains current focus
				$(this_menu).find('a:not(.top-level-link):focus').closest('.parent').addClass('open'); // account for sub-submenus as well
				menuUpdateMasonry( $(this).children('ul').first() );
				var this_link = $(this);
				if ( !$(this_link).parent().hasClass('open') ) { // if the menu is not already open
					hover_intent_in = setTimeout(function() { // start new timer
						$(this_link).addClass('open');
						$(this_link).parent().addClass('open');
					},200); // 200ms delay in hover response, to reduce unintentional trigger
				}
				else { // if the menu is already open, skip the timer and respond immediately
					$(this_link).addClass('open');
					$(this_link).parent().addClass('open');
				}
			}
			else if ( !$('body').hasClass('mobile') ) {
				$(this).addClass('open');
			}
			if ( !$('body').hasClass('mobile') ) {
				// horizontal edge-detection
				var submenu_offset = $(this).children('ul').offset();
				try {
					if ( submenu_offset.left + $(this).children('ul').width() > $(window).width() ) {
						$(this).children('ul').addClass('flip');
					}
				} catch {}
			}
		}, function() {
			// Delay menu response on hover OUT of a top level item (desktop only)
			if ( $(this).hasClass('top-level-li') && !$('body').hasClass('mobile') ) {
				clearTimeout(hover_intent_in); // cancel any hover IN timer
				var this_link = $(this);
				hover_intent_out = setTimeout(function() { // start new timer
					$(this_link).removeClass('open');
					$(this_link).parent().removeClass('open');
					$(this_menu).find('li.focused').addClass('open').parent('ul').addClass('open'); // leave the menu visible if it contains current focus
					$(this_menu).find('a:not(.top-level-link):focus').closest('.parent').addClass('open'); // account for sub-submenus as well
					if ( !$('body').hasClass('mobile') ) {
						$(this).children('ul').removeClass('flip');
					}
				},400); // 400ms delay in hover response, to reduce unintentional loss of menu
			}
			else if ( !$('body').hasClass('mobile') ) {
				$(this).removeClass('open');
				$(this).children('ul').removeClass('flip');
			}
		});
	});
	$('.dropdown-menu li.parent a').focus(function() {
		$(this).closest('.dropdown-menu').find('.focused').removeClass('focused').find('.focused-top-level').removeClass('focused-top-level');
		if ( $(this).hasClass('top-level-link') ) {
			$(this).closest('.top-level-li').addClass('focused-top-level');
		}
		else {
			$(this).closest('.top-level-li').addClass('focused');
		}
		
		if ( !$('body').hasClass('mobile') ) {
			// horizontal edge-detection
			var submenu_offset = $(this).closest('.parent').children('ul').offset();
			try {
				if ( submenu_offset.left + $(this).closest('.parent').children('ul').width() > $(window).width() ) {
					$(this).closest('.parent').children('ul').addClass('flip');
				}
			} catch {}
			if (!mousedown) {
				if ( $(this).hasClass('top-level-link') ) {
					menuUpdateMasonry( $(this).nextAll('ul').first() );
				}
				$(this).parents('.parent').addClass('open');
				$(this).closest('.megamenu-top').addClass('open'); // used by megamenu "nom nom" mode
				$(this).closest('.mobile-expander').children('.mobile-expander-heading').addClass('open');
			}
			mousedown = false;
		}
	}).blur(function() {
		if ( !$('body').hasClass('mobile') ) {
			$(this).parents('.parent').removeClass('open');
			$(this).closest('.megamenu-top').removeClass('open'); // used by megamenu "nom nom" mode
			$(this).closest('.mobile-expander').children('.mobile-expander-heading').removeClass('open');
		}
	});
	
	// Keyboard Navigation
	$('.dropdown-menu').each(function() {
		var megamenu = eval( $(this).attr('data-megamenu') ); // get megamenu setting
		
		$(this).find('ul').first().children('li').addClass('top-level-li').children('a').addClass('top-level-link');
		if ( megamenu ) {
			$(this).find('.top-level-link').nextAll('ul').children('li').children('a').addClass('megamenu-top-level-link');
		}
	});
	
	$('.dropdown-menu-on-demand').find('ul').find('a').each(function() { // on-demand mode only (includes megamenu mode)
		var megamenu = eval( $(this).closest('.dropdown-menu').attr('data-megamenu') ); // get megamenu setting
		
		$(this).attr('data-label',$(this).children('span:first-child').text()); // -> generate initial label text
		$(this).attr('aria-label',$(this).attr('data-label')); // -> apply initial label
		
		$(this).focus(function() {
			if ( !$('body').hasClass('mobile') ) {
				if ( $(this).hasClass('top-level-link') ) { // top level
					$(this).closest('ul').find('.children').attr('aria-hidden','true').find('a').attr('tabindex','-1'); // -> lock all submenus
					if ( $(this).attr('aria-haspopup') == 'true' ) {
						$(this).attr('aria-label', $(this).attr('data-label') + ': To enter this sub menu, press Down Arrow.'); // -> append help text
					}
				}
				else {
					if ( !megamenu ) {
						$(this).nextAll('ul').first().attr('aria-hidden','true').find('a').attr('tabindex','-1'); // -> lock children
					}
					if ( $(this).attr('aria-haspopup') == 'true' ) {
						if ( megamenu ) {
							$(this).attr('aria-haspopup','false'); // megamenu submenus are always visible
						}
						else if ( $(this).nextAll('ul').first().hasClass('flip') ) {
							$(this).attr('aria-label', $(this).attr('data-label') + ': To enter this sub menu, press Left Arrow.'); // -> append help text
						}
						else {
							$(this).attr('aria-label', $(this).attr('data-label') + ': To enter this sub menu, press Right Arrow.'); // -> append help text
						}
					}
				}
			}
		}).blur(function() {
			$(this).attr('aria-label',$(this).attr('data-label')); // -> reset initial label
		});
	});
	
	$('.dropdown-menu li a').keydown(function(e) {
		var megamenu = eval( $(this).closest('.dropdown-menu').attr('data-megamenu') ); // get megamenu setting
		
		// Only accept arrow key input without modifier keys, to avoid interfering with system commands
		if (!$('body').hasClass('mobile') && e.ctrlKey == false && e.altKey == false && e.shiftKey == false && e.metaKey == false) {
			
			// RIGHT arrow key -------------------------------------------------------
			if (e.keyCode == 39) {
				e.preventDefault();
				if ( $(this).hasClass('top-level-link') ) { // top level
					$(this).parent().next().children('a').focus(); // -> next top level item
				}
				else if ( $(this).attr('aria-haspopup') == 'true' ) { // dropdown item with submenu
					if ( $(this).nextAll('ul').first().hasClass('flip') ) { // submenu positioned left
						$(this).closest('.top-level-li').next().children('a').first().focus(); // -> next top level item
					}
					else {
						$(this).nextAll('ul').first().attr('aria-hidden','false').children().children('a').attr('tabindex','0'); // -> unlock sub-submenu
						$(this).nextAll('ul').first().find('a').first().focus(); // -> enter sub-submenu
					}
				}
				else { // basic dropdown item (or megamenu)
					if ( $(this).closest('ul').closest('li').hasClass('top-level-li') ) { // 1 level down
						if ( megamenu ) {
							// jump over one column to the right
							var position_current = $(this).parent().attr('data-position');
							if ( position_current.charAt(3) == '1' ) {
								var position_target = position_current.replace('1','2');
							}
							else {
								var position_target = position_current.replace('2','3');
							}
							// if this row doesn't exist in the new column, target the previous row
							if ( $(this).closest('.children').find('.'+position_target).length == 0 ) {
								var alternate_target = position_target.substring(0,4) + String.fromCharCode(position_target.substring(4).charCodeAt(0) - 1);
								position_target = alternate_target;
							}
							$(this).closest('.children').find('.'+position_target).children('a').focus(); // -> same row, next column
						}
						else {
							$(this).closest('.top-level-li').next().children('a').first().focus(); // -> next top level item
						}
					}
					else { // 2+ levels down
						if ( megamenu ) {
							// jump over one column to the right
							var position_current = $(this).closest('ul').closest('li').attr('data-position');
							if ( position_current.charAt(3) == '3' ) {
								// no action in 3rd column
							}
							else if ( position_current.charAt(3) == '1' ) {
								var position_target = position_current.replace('1','2');
							}
							else {
								var position_target = position_current.replace('2','3');
							}
							// if this row doesn't exist in the new column, target the previous row
							if ( position_current.charAt(3) != '3' && $(this).closest('ul').closest('li').closest('.children').find('.'+position_target).length == 0 ) {
								var alternate_target = position_target.substring(0,4) + String.fromCharCode(position_target.substring(4).charCodeAt(0) - 1);
								position_target = alternate_target;
							}
							$(this).closest('ul').closest('li').closest('.children').find('.'+position_target).children('a').focus(); // -> same row, next column
						}
						else if ( $(this).closest('ul').hasClass('flip') ) { // current menu positioned left
							$(this).closest('ul').prevAll('a').first().focus(); // -> return to parent
						}
						else {
							$(this).closest('.top-level-li').next().children('a').first().focus(); // -> next top level item
						}
					}
				}
			}
			
			// DOWN arrow key --------------------------------------------------------
			else if (e.keyCode == 40) {
				e.preventDefault();
				if ( $(this).hasClass('top-level-link') && megamenu ) {
					$(this).nextAll('ul').first().children().children('ul').attr('aria-hidden','false').children().children('a').attr('tabindex','0'); // -> unlock all submenus for megamenu
					$(this).nextAll('ul').first().find('.menu-feature *[tabindex=-1]').removeAttr('tabindex'); // safeguard arbitrary elements within menu feature
				}
				if ( $(this).hasClass('top-level-link') || $(this).hasClass('megamenu-top-level-link') ) { // top level (or top level item within megamenu)
					if ( $(this).hasClass('megamenu-top-level-link') && $(this).nextAll('ul').first().children('li').length <= 0 ) { // -> top level megamenu item with no submenu
						$(this).parent().next().children('a').focus(); // -> next top-level megamenu item
					}
					else {
						$(this).nextAll('ul').first().attr('aria-hidden','false').children().children('a').attr('tabindex','0'); // -> unlock submenu
						$(this).nextAll('ul').first().find('a').first().focus(); // -> enter submenu
					}
				}
				else {
					if ( $(this).hasClass('last-child') ) {
						$(this).closest('ul').closest('li').next().children('a').first().focus(); // -> next top level item within megamenu
					}
					else {
						$(this).parent().next().children('a').focus(); // -> next menu item
					}
				}
			}
			
			// LEFT arrow key --------------------------------------------------------
			else if (e.keyCode == 37) {
				e.preventDefault();
				if ( $(this).hasClass('top-level-link') ) { // top level
					$(this).parent().prev().children('a').focus(); // -> previous top level item
				}
				else if ( $(this).attr('aria-haspopup') == 'true' ) { // dropdown item with submenu
					if ( !$(this).nextAll('ul').first().hasClass('flip') ) {  // submenu positioned right
						$(this).closest('.top-level-li').prev().children('a').first().focus(); // -> next top level item
					}
					else {
						$(this).nextAll('ul').first().attr('aria-hidden','false').children().children('a').attr('tabindex','0'); // -> unlock sub-submenu
						$(this).nextAll('ul').first().find('a').first().focus(); // -> enter sub-submenu
					}
				}
				else { // basic dropdown item (or megamenu)
					if ( $(this).closest('ul').closest('li').hasClass('top-level-li') ) { // 1 level down
						if ( megamenu ) {
							// jump back one column to the left
							var position_current = $(this).parent().attr('data-position');
							if ( position_current.charAt(3) == '2' ) {
								var position_target = position_current.replace('2','1');
							}
							else {
								var position_target = position_current.replace('3','2');
							}
							$(this).closest('.children').find('.'+position_target).children('a').focus(); // -> same row, previous column
						}
						else {
							$(this).closest('.top-level-li').prev().children('a').first().focus(); // -> previous top level item
						}
					}
					else { // 2+ levels down
						if ( megamenu ) {
							// jump back one column to the left
							var position_current = $(this).closest('ul').closest('li').attr('data-position');
							if ( position_current.charAt(3) == '1' ) {
								// no action in 1st column
							}
							else if ( position_current.charAt(3) == '2' ) {
								var position_target = position_current.replace('2','1');
							}
							else {
								var position_target = position_current.replace('3','2');
							}
							$(this).closest('ul').closest('li').closest('.children').find('.'+position_target).children('a').focus(); // -> same row, previous column
						}
						else if ( !$(this).closest('ul').hasClass('flip') ) { // current menu positioned right
							$(this).closest('ul').prevAll('a').first().focus(); // -> return to parent
						}
						else {
							$(this).closest('.top-level-li').prev().children('a').first().focus(); // -> previous top level item
						}
					}
				}
			}
			
			// UP arrow key ----------------------------------------------------------
			else if (e.keyCode == 38) {
				e.preventDefault();
				if ( $(this).hasClass('top-level-link') ) { // top level
					$(this).parent().removeClass('open'); // -> visually hide submenu
				}
				else if ( $(this).hasClass('megamenu-top-level-link') ) { // top level within a megamenu
					if ( $(this).hasClass('first-child') ) { // also first link within megamenu
						$(this).closest('ul').prevAll('.top-level-link').first().focus(); // -> return to top level
					}
					else {
						if ( $(this).parent().prev('li').children('ul').first().children('li').length <= 0 ) {
							 $(this).parent().prev().children('a').focus(); // -> previous top-level item in megamenu has no submenu, move to that item
						}
						else {
							$(this).closest('li').prev().find('.last-child').first().focus(); // -> back to end of previous submenu within megamenu
						}
					}
				}
				else if ( $(this).hasClass('first-child') ) { // first submenu item in megamenu
					$(this).closest('ul').prevAll('a').first().focus(); // -> return to parent within megamenu
				}
				else if ( $(this).parent().prev('li').length <= 0 ) { // first submenu item
					$(this).closest('ul').prevAll('.top-level-link').first().focus(); // -> return to top level
				}
				else {
					$(this).parent().prev().children('a').focus(); // -> previous menu item
				}
			}
			
			// ESCAPE key ------------------------------------------------------------
			else if (e.keyCode == 27) {
				// Hide any open menus that are outside of current focus
				$(this).closest('.dropdown-menu').find('.open').removeClass('open');
				$(this).closest('.dropdown-menu').find('.focused').addClass('open').parent('ul').addClass('open');
				
				// Back out of current menu or close menu when at the top level
				if ( $(this).hasClass('top-level-link') ) { // top level
					$(this).parent().removeClass('open'); // -> visually hide submenu
					$(this).parent().parent().removeClass('open'); // -> hide top fill in nom-nom megamenu
				}
				else {
					$(this).closest('ul').prevAll('a').first().focus(); // -> return to parent
				}
			}
		}
	});
	
	// Escape key outside of menu scope (when a menu does not currently contain focus, but may be open by mousover) 
	$('body').keydown(function(e) {
		// ESCAPE key ------------------------------------------------------------
		if (e.keyCode == 27) {
			$('.dropdown-menu').each(function() {
				if ( $(this).find('.focused').length == 0 && $(this).find('.focused-top-level').length == 0 ) {
					$(this).find('.open').removeClass('open');
				}
			});
		}
	});
	
	// Additional focus handling (remove errant "focus" classes if needed)
	var focus_cleanup;
	$('.dropdown-menu').focusout(function() {
		var this_menu = $(this);
		focus_cleanup = setTimeout(function(){
			$(this_menu).find('.focused, .focused-top-level').removeClass('focused focused-top-level');
		}, 50);
	}).focusin(function() {
		clearTimeout(focus_cleanup);
	});
	
	// Mobile Navigation
	$('.dropdown-menu li.parent > a').after('<button class="mobile-submenu-toggle" aria-expanded="false"><span class="sr-only">Open sub-menu</span></button>') // mobile toggles
	$('.dropdown-menu ul ul .mobile-submenu-toggle').attr('tabindex','-1'); // toggles in submenus are locked initially 
	$('.mobile-submenu-toggle').click(function() {
		if ( $(this).closest('.parent').hasClass('open') ) {
			$(this).closest('.parent').removeClass('open');
			$(this).attr('aria-expanded','false');
			$(this).nextAll('ul').first().attr('aria-hidden','true').children('li').children('a, button').attr('tabindex','-1'); // -> lock children
			$(this).children('.sr-only').text('Open sub-menu');
		}
		else {
			$(this).closest('.parent').addClass('open');
			$(this).attr('aria-expanded','true');
			$(this).nextAll('ul').first().attr('aria-hidden','false').children('li').children('a, button').attr('tabindex','0'); // -> unlock children
			$(this).children('.sr-only').text('Close sub-menu');
		}
	});
	
	$('.dropdown-menu li.parent > a .fa').addClass('aria-target').attr('tabindex','-1').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
	}).mousedown(function(e) {
		e.stopPropagation();
		mousedown = true;
		if ( $('body').hasClass('mobile') ) {
			$(this).closest('.parent').toggleClass('open');
		}
	});
	
	$(document).keyup(function(e) {
		if (e.keyCode == 27) { // escape key
			if ( $('body').hasClass('mobile-menu-open') ) {
				$('#mobile-close').trigger('click');
			}
		}
	});
	
	// Recalculate Megamenu Masonry
	function menuUpdateMasonry(menu) {
		
		// if this is the mobile menu...
		if ( $('body').hasClass('mobile') ) {
			menuClearMasonry(); // reset
			return; // and take no further action
		}
		// if this is not a masonry menu...
		if ( !$(menu).parent().hasClass('mobile-nav-only') && $(menu).closest('.megamenu-masonry').length == 0 ) {
			return; // take no action
		}
		
		// otherwise, now entering a hardhat area...
		var masonry = [0,0,0]; // a running tally of masonry offset for each column
		var column_heights = [0,0,0]; // and the height of each column after adjustment
		var cols = $(menu).attr('data-max-cols');
		var rows = $(menu).attr('data-max-rows');

		$(menu).each(function() {
			var menu_feature = false;
			var this_menu = $(this);
			var this_menu_feature = $(this).find('.menu-feature').last();
			var this_menu_feature_extraspace = menu_feature_content_height = 0;
			var this_menu_vertical_padding = parseInt($(this).css('font-size')) * 2; // assumes 2em top spacer and bottom margins (font-size * 2em * 2)
			if ( $(this_menu_feature).length > 0 ) {
				menu_feature = true;
				menu_feature_content_height = $(this_menu_feature).find('.feature-content').height();
			}
			
			$(this).children(':not(.menu-feature)').each(function() {
				$(this).removeAttr('style');
				var this_row = parseInt( $(this).attr('data-row') );
				var this_col = parseInt( $(this).attr('data-col') );
				
				var children_extra_margin = parseInt( parseInt($(this).css('font-size')) * 0.4); // represents the extra ~0.4em of bottom margin that a children menu adds
				var min_item_height = parseInt($(this).css('line-height')) + children_extra_margin; // establish a minumum height to account for submenus with no children menu below them
				
				if ( this_row > 1 ) {
					var prev_row = this_row - 1;
					var masonry_offset = 0;
					var max_height = 0;
					var this_height = 0;
					var prev_height = 0;
					var row_query = $(this).parent().find('[data-row='+prev_row+']');
					
					// calculate true height of this menu item and any children
					for (let i=0; i<$(this).children().length; i++) {
						this_height += $(this).children().eq(i).height();
					}
					this_height = parseInt(this_height);
					
					// calculate true height of previous menu item and any children
					for (let i=0; i<$(this).prev().children().length; i++) {
						prev_height += $(this).prev().children().eq(i).height();
						
						if ($(this).prev().attr('data-children') == 0) {
							prev_height += children_extra_margin; // fine tuning for items with no submenu
						}
					}
					prev_height = parseInt(prev_height);
				
					// determine the the tallest menu item of the previous row
					for (let i=0; i<$(row_query).length; i++) {
						let temp_height = 0;
						for (let j=0; j<$(row_query).eq(i).children().length; j++) {
							temp_height += $(row_query).eq(i).children().eq(j).height();
						}
						temp_height = parseInt(temp_height);
						if ( temp_height > max_height ) {
							max_height = temp_height;
						}
					}
				
					masonry_offset = max_height - prev_height;
					masonry[this_col-1] += masonry_offset;
					
					if ($(this).attr('data-children') == 0) {
						masonry[this_col-1] += children_extra_margin; // fine tuning for items with no submenu
					}
					$(this).css('top','-' + masonry[this_col-1] + 'px'); // offset by the amount the previous item is smaller than the max for that row
					$(this).height(this_height);
					if (masonry_offset < $(this).prev().height()) {
						$(this).prev().height(prev_height);
					}
					if ($(this).prev().height() < min_item_height) {
						$(this).prev().height(min_item_height);
					}
					if ($(this).height() < min_item_height) {
						$(this).height(min_item_height);
					}
				}			
			});
			
			if (menu_feature) {
				menu_feature = true;
				this_menu_feature_extraspace = parseInt( $(this_menu_feature).css('height','').height() - menu_feature_content_height );
			}
			
			// Calculate new column heights
			column_heights[0] = column_heights[1] = column_heights[2] = 0;
			$(this).find('[data-col]').each(function() {
				column_heights[parseInt( $(this).attr('data-col') ) - 1] += Math.round($(this).height() + this_menu_vertical_padding);
			});
			if (!menu_feature) {
				column_heights[0] += this_menu_vertical_padding;
				column_heights[1] += this_menu_vertical_padding;
				column_heights[2] += this_menu_vertical_padding;
			}
			var suggested_height = tallest_link_column = column_heights[0];
			if (column_heights[1] > column_heights[0]) {
				suggested_height = column_heights[1];
				tallest_link_column = column_heights[1];
			}
			if (!menu_feature && column_heights[2] > suggested_height) {
				suggested_height = column_heights[2];
				tallest_link_column = column_heights[2];
			}
			else if (menu_feature && parseInt(this_menu_feature.height() + this_menu_vertical_padding) > suggested_height) {
				suggested_height = parseInt(this_menu_feature.height() + this_menu_vertical_padding);
			}
			
			// collapse menu to account for masonry offset (but only if the menu-feature is shorter than the height of the menu items, or there is no menu-feature to account for)
			var menu_collapse = largest_masonry = masonry[0];
			if (masonry[1] < masonry[0] && column_heights[0] < column_heights[1]) {
				menu_collapse = masonry[1];
			}
			if (!menu_feature && masonry[2] < menu_collapse && column_heights[2] < column_heights[0] && column_heights[2] < column_heights[1]) {
				menu_collapse = masonry[2];
			}
			if (menu_feature && menu_collapse > this_menu_feature_extraspace) {
				menu_collapse = this_menu_feature_extraspace;
			}
			if (masonry[1] > largest_masonry) {
				largest_masonry = masonry[1];
			}
			if (masonry[2] > largest_masonry) {
				largest_masonry = masonry[2];
			}
			
			$(this_menu_feature).css('height','').height( $(this_menu_feature).height() - menu_collapse );
			$(this_menu).css('height','auto').css('max-height','');
			var natural_height = $(this_menu).height();
			
			
			// For the final menu height, we need different approaches for different combinations of menu content:
			
			// If a menu feature is present...
			if (menu_feature) {
				var final_menu_collapse = menu_collapse;
				
				if ( tallest_link_column < menu_feature_content_height ) { // menu feature is TALLER than the links after masonry
					final_menu_collapse = largest_masonry;
					if (this_menu_feature_extraspace < final_menu_collapse) {
						final_menu_collapse = this_menu_feature_extraspace;
					}
					$(this_menu).css('max-height', ($(this_menu).height() - final_menu_collapse) + 'px' ).css('height','');
				}
				else {  // menu feature is SHORTER than the links after masonry
					if (natural_height - final_menu_collapse > tallest_link_column + this_menu_vertical_padding && this_menu_feature_extraspace != menu_collapse) {
						final_menu_collapse = natural_height - tallest_link_column - this_menu_vertical_padding;
						$(this_menu_feature).height($(this_menu).height() - final_menu_collapse - this_menu_vertical_padding - this_menu_vertical_padding);
					}
					$(this_menu).css('max-height', ($(this_menu).height() - final_menu_collapse) + 'px' ).css('height','');
				}
			}
			// Else, no menu feature...
			else {
				$(this_menu).css('max-height', suggested_height + 'px' ).css('height','');
			}
		});
	}
	
	// Clear Megamenu Masonry
	function menuClearMasonry() {
		$('.megamenu-masonry .top-level-li > .children').removeAttr('style');
		$('.megamenu-masonry .children > li').removeAttr('style');
	}
});

