<?php
// ----------------------------------
// --  REMOVE SELECT HOOK ACTIONS  --
// ----------------------------------

function pc_remove_selected_hook_actions()
{
	global $pc_theme_object;

	/* Don't show theme option logo on login screen. */
	remove_action( 'login_head', array( $pc_theme_object->_utility_callbacks_class, 'theme_custom_login_logo' ) );

	/* Disable Autochimp Plugin persistent admin notice. */
	remove_action( 'admin_notices', 'AC_OnAdminNotice' );
}
add_action( 'init', 'pc_remove_selected_hook_actions' );

// *************************************************************************
// **                                                                     ** 
// **  SECTION [2] RESTRICT WP ADMIN FEATURES FOR NON-SUPER-ADMIN USERS.  **   
// **                                                                     **
// *************************************************************************

// --------------------------------------------------------------------------
// --  RESTRICT USER SITE ADMIN FEATURES FOR ALL ROLES EXCEPT SUPER ADMIN  --
// --------------------------------------------------------------------------

function pc_check_user_role() {

	if ( !current_user_can( 'manage_network' ) ) {
		add_action( 'admin_enqueue_scripts', 'pc_add_admin_css' );
		add_action( 'wp_dashboard_setup', 'pc_remove_dashboard_widgets', 11 );
		add_filter( 'screen_layout_columns', 'pc_single_screen_columns' );
		add_filter( 'get_user_option_screen_layout_dashboard', 'pc_single_screen_dashboard' );
		add_action( 'wp_dashboard_setup', 'pc_wpc_add_dashboard_widgets' );
		// add_action( 'widgets_init', 'pc_unregister_default_widgets', 11 );
		add_action( 'admin_menu', 'pc_remove_links_menu' );
		add_action( 'admin_menu', 'pc_remove_submenus' );
		// add_action( 'admin_init','pc_customize_meta_boxes' );
		// add_action( 'admin_menu', 'pc_remove_menu_links', 9999 );
		add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) ); // Remove upgrade notices
		// add_filter( 'screen_options_show_screen', 'pc_remove_screen_options_tab' );
		// add_action( 'admin_head', 'pc_remove_dashboard_help_tab' );
		add_action( 'wp_before_admin_bar_render', 'pc_annointed_admin_bar_remove', 0 );
		add_filter( 'tiny_mce_before_init', 'pc_unhide_kitchensink' );
		add_filter( 'admin_footer_text', 'pc_remove_footer_admin' );
		add_filter( 'manage_edit-post_columns', 'pc_my_columns_filter', 10, 1 );
		add_filter( 'manage_pages_columns', 'pc_my_custom_pages_columns' );
		add_action( 'wp_before_admin_bar_render', 'pc_mytheme_admin_bar_render' );
		// add_action( 'admin_init', 'pc_restrict_admin_themes_with_redirect' );
		// add_action( 'admin_init', 'pc_restrict_admin_export_with_redirect' );
		// add_action( 'admin_bar_menu', 'pc_toolbar_help_link_parent', 999 );
		//add_action( 'admin_menu', 'pc_move_page_attributes_metabox' );
	}
}
add_action( 'init', 'pc_check_user_role' );

// ----------------------------
// --  ADD CUSTOM ADMIN CSS  --
// ----------------------------

function pc_add_admin_css()
{
	// wp_enqueue_style( 'mh_admin_css', plugin_dir_url(__FILE__).'css/mh_admin.css' );

	wp_enqueue_script( 'pc_fancybox_js', plugin_dir_url(__FILE__).'fancybox/jquery.fancybox.pack.js', 'jquery' );
	wp_enqueue_style( 'pc_fancybox_css', plugin_dir_url(__FILE__).'fancybox/jquery.fancybox.css' );

}

// --------------------------------
// --  REMOVE DASHBOARD WIDGETS  --
// --------------------------------

function pc_remove_dashboard_widgets(){
  global$wp_meta_boxes;
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['rg_forms_dashboard']);
}

// ------------------------------------
// --  MAKE DASHBOARD SINGLE COLUMN  --
// ------------------------------------

function pc_single_screen_columns( $columns ) {
    $columns['dashboard'] = 1;
    return $columns;
}

function pc_single_screen_dashboard(){
	return 1;
}

// ----------------------------------------
// --  ADD CUSTOM META BOX TO DASHBOARD  --
// ----------------------------------------

// Add a widget in WordPress Dashboard
function pc_wpc_dashboard_widget_function() {

	$current_theme = wp_get_theme();
	/* $current_theme->Template should always be the parent theme name. */
	$current_theme_name = strtolower( str_replace(" ","-", $current_theme->Template) );
	$current_theme_path = plugin_dir_path(__FILE__).'themes/'.$current_theme_name.'/dashboard.php';

	if( file_exists($current_theme_path) ) {
		/* Include the dashboard page. */
		require_once( $current_theme_path );
	}
	else {
		echo "<p>Dashboard page not found.</p>";
	}
}

function pc_wpc_add_dashboard_widgets() {
	wp_add_dashboard_widget('wp_dashboard_widget', 'Getting Started', 'pc_wpc_dashboard_widget_function');
}

// -----------------------------------
// --  REMOVE SOME DEFAULT WIDGETS  --
// -----------------------------------

function pc_unregister_default_widgets() {
	unregister_widget('WP_Widget_Pages');
    unregister_widget('WP_Widget_Calendar');
    unregister_widget('WP_Widget_Archives');
    unregister_widget('WP_Widget_Links');
    unregister_widget('WP_Widget_Categories');
    unregister_widget('WP_Widget_RSS');
    unregister_widget('WP_Widget_Tag_Cloud');
    unregister_widget('Twenty_Eleven_Ephemera_Widget');
}

// ----------------------------------
// --  REMOVE LEFT NAV MENU ITEMS  --
// ----------------------------------

function pc_remove_links_menu() {
     // remove_menu_page('upload.php'); // Media
     remove_menu_page('link-manager.php'); // Links
     // remove_menu_page('edit-comments.php'); // Comments
     remove_menu_page('plugins.php'); // Plugins
     // remove_menu_page('options-general.php'); // Settings
     // remove_menu_page('tools.php'); // Tools
}

// ----------------------------
// --  REMOVE NAV SUB MENUS  --
// ----------------------------

function pc_remove_submenus() {

  global $submenu;

  unset($submenu['index.php'][10]); // Removes 'Updates'.
  unset($submenu['index.php'][5]); // Removes 'My Sites'.
  unset($submenu['edit.php'][16]); // Removes 'Tags'.
  unset($submenu['edit.php'][15]); // Remove 'Categories'.
  unset($submenu['tools.php'][5]); // Removes 'Available Tools'.
  // unset($submenu['tools.php'][10]); // Removes 'Import'.
  // unset($submenu['tools.php'][15]); // Removes 'Export'.
  unset($submenu['tools.php'][25]); // Removes 'Delete Site'.
  unset($submenu['tools.php'][26]); // Removes 'Domain Mapping'.
}

// --------------------------------------------------
// --  REMOVE META BOXES ON POST/PAGE EDIT SCREEN  --
// --------------------------------------------------

function pc_customize_meta_boxes() {
  /* Removes meta boxes from Posts */
  remove_meta_box('postcustom','post','normal');
  remove_meta_box('trackbacksdiv','post','normal');
  remove_meta_box('commentstatusdiv','post','normal');
  remove_meta_box('commentsdiv','post','normal');
  remove_meta_box('tagsdiv-post_tag','post','normal');
  remove_meta_box('postexcerpt','post','normal');
  remove_meta_box('authordiv', 'post', 'normal');
  remove_meta_box('revisionsdiv', 'post', 'normal');
  remove_meta_box('slugdiv', 'post', 'normal');
  /* Removes meta boxes from pages */
  remove_meta_box('postcustom','page','normal');
  remove_meta_box('trackbacksdiv','page','normal');
  remove_meta_box('commentstatusdiv','page','normal');
  remove_meta_box('commentsdiv','page','normal');
  remove_meta_box('authordiv', 'page', 'normal');
  remove_meta_box('revisionsdiv', 'page', 'normal');
  remove_meta_box('slugdiv', 'page', 'normal');
}

// --------------------------------------
// --  REMOVE GRAVITY FORMS SUB MENUS  --
// --------------------------------------

function pc_remove_menu_links() {
        // remove_submenu_page( 'gf_edit_forms', 'gf_edit_forms' ); 
        // remove_submenu_page( 'gf_edit_forms', 'gf_new_form' ); 
        // remove_submenu_page( 'gf_edit_forms', 'gf_new_formf_help' ); 
        // remove_submenu_page( 'gf_edit_forms', 'gf_entries' ); 
        remove_submenu_page( 'gf_edit_forms', 'gf_settings' ); 
        remove_submenu_page( 'gf_edit_forms', 'gf_export' ); 
        remove_submenu_page( 'gf_edit_forms', 'gf_update' ); 
        remove_submenu_page( 'gf_edit_forms', 'gf_addons' ); 
        remove_submenu_page( 'gf_edit_forms', 'gf_help' );
}

// ---------------------------------------
// --  HIDE ADMIN 'SCREEN OPTIONS' TAB  --
// ---------------------------------------

// Hide screen options tab for all admin pages.
function pc_remove_screen_options_tab() {
    return false;
}

// -----------------------------
// --  HIDE ADMIN 'HELP' TAB  --
// -----------------------------

// Hide help tab for just the admin dashboard page.
function pc_remove_dashboard_help_tab() {
    $screen = get_current_screen();
	
	//
	if($screen->id == 'dashboard')
		$screen->remove_help_tabs();
}

// -------------------------------------
// --  REMOVE WP LOGO FROM ADMIN BAR  --
// -------------------------------------

function pc_annointed_admin_bar_remove() {
        global $wp_admin_bar;

        /* Remove their stuff */
        $wp_admin_bar->remove_menu('wp-logo');
}

// -----------------------------------------------------
// --  SHOW KITCHEN SINK IN VISUAL EDITOR BY DEFAULT  --
// -----------------------------------------------------

function pc_unhide_kitchensink( $args ) {
$args['wordpress_adv_hidden'] = false;
return $args;
}

// ------------------------------------
// --  ADD CUSTOM ADMIN FOOTER TEXT  --
// ------------------------------------

function pc_remove_footer_admin () {
  echo 'FitPro hosting is powered by <a href="http://wordpress.org" target="_blank">WordPress</a>.';
}

// --------------------------------------
// --  REMOVE COLUMNS FROM POSTS LIST  --
// --------------------------------------

function pc_my_columns_filter( $columns ) {
    unset($columns['author']);
    unset($columns['tags']);
    unset($columns['categories']);
    unset($columns['tags']);
    return $columns;
}

// --------------------------------------------
// --  REMOVE AUTHOR COLUMN FROM PAGES LIST  --
// --------------------------------------------

function pc_my_custom_pages_columns($columns) {

	unset(
		$columns['author']
	);
	
	return $columns;
}

// -----------------------------------------------
// --  REMOVE LINKS/MENUS FROM THE WP TOOL BAR  --
// -----------------------------------------------

function pc_mytheme_admin_bar_render() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('my-sites');
	$wp_admin_bar->remove_menu('new-content');
	$wp_admin_bar->remove_menu('new-themes');
}

// -----------------------------------------------------------------
// --  RESTRICT ACCESS TO ADMIN THEMES.PHP - FOR NON-SUPER-ADMIN  --
// -----------------------------------------------------------------

function pc_restrict_admin_themes_with_redirect() {

	/* If themes.php is accessed (with no query string appended) user redirected to dashboard. */
	if (!current_user_can('manage_network') && empty($_SERVER['QUERY_STRING']) && $_SERVER['PHP_SELF'] == '/wp-admin/themes.php') {
		wp_redirect(admin_url() );
		exit;
	}
}

// -----------------------------------------------------------------
// --  RESTRICT ACCESS TO ADMIN EXPORT.PHP - FOR NON-SUPER-ADMIN  --
// -----------------------------------------------------------------

function pc_restrict_admin_export_with_redirect() {

	/* If export.php is accessed (with no query string appended) user redirected to dashboard. */
	if (!current_user_can('manage_network') && empty($_SERVER['QUERY_STRING']) && $_SERVER['PHP_SELF'] == '/wp-admin/export.php') {
		wp_redirect(admin_url() );
		exit;
	}
}

// --------------------------
// --  ADD DASHBOARD LINK  --
// --------------------------

// This link will show the dashboard for site admin, but not super admin.
function pc_toolbar_help_link_parent( $wp_admin_bar ) {

	global $wp_admin_bar;
	global $blog_id;

	/* Don't show 'Need Help?' WP toolbar links on primary site (i.e. when $blog-id = 1). */
	if ($blog_id != 1) {

		/* Top level help menu item. */
		$parent_args = array(
			'id' => 'pc_toolbar_help_link_parent',
			'title' => 'Need Help?',
			'href' => get_admin_url(),
			'parent'    => 'top-secondary'
		);

		$tutorial_args = array(
			'id' => 'pc_toolbar_tutorial_link',
			'title' => 'Tutorials',
			'href' => get_admin_url(),
			'parent' => 'pc_toolbar_help_link_parent',
			//'class' => 'ab-top-secondary'
		);

		$support_args = array(
			'id' => 'pc_toolbar_support_link',
			'title' => 'Priority Support (Pro only)',
			'href' => get_admin_url().'admin.php?page=premium-support',
			'parent' => 'pc_toolbar_help_link_parent',
			//'class' => 'ab-top-secondary'
		);

		$wp_admin_bar->add_node($parent_args);
		$wp_admin_bar->add_node($tutorial_args);
		$wp_admin_bar->add_node($support_args);
	}
}

// ----------------------------------------------------------
// --  MOVE PAGE ATTRIBUTES META BOX TO BOTTOM OF SIDEBAR  --
// ----------------------------------------------------------

//function pc_move_page_attributes_metabox() {

	/*global $post;

	echo "pt: ".$post->post_type;

	if ( post_type_supports($post->post_type, 'page-attributes') ) {
		remove_meta_box('pageparentdiv', 'page', 'side');
		add_meta_box('pageparentdiv', 'page' == $post_type ? __('Page Attributes') : __('Attributes'), 'page_attributes_meta_box', null, 'side', 'low');
	}*/
//}
