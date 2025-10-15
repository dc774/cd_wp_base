<?php

// Theme functions

// Enqueue styles and scripts
require_once get_theme_file_path('functions/styles-and-scripts.php');

// Theme setup
require_once get_theme_file_path('functions/disable-posts-comments.php');

// Templating functions
require_once get_theme_file_path('functions/templating-functions.php');

// Post-setup actions
require_once get_theme_file_path('functions/post-setup.php');

// Configuration for custom fields
require_once get_theme_file_path('functions/acf-config.php');

// Section navigation
require_once get_theme_file_path('functions/section-nav.php');

// Pagination
require_once get_theme_file_path('functions/pagination.php');

// Images
require_once get_theme_file_path('functions/images.php');

// Template fields
require_once get_theme_file_path('functions/template-fields.php');

// Editor functions
require_once get_theme_file_path('functions/editor-functions.php');
