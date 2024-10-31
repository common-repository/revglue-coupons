<?php

// Exit if accessed directly

if ( !defined( 'ABSPATH' ) ) exit;

function rg_coupons_admin_enqueue()

{

	global $hook_suffix;

	// List of Plugin Pages

	$rg_coupons_hook_suffixes = array(

		'toplevel_page_revglue-dashboard',

		'revglue-coupons_page_revglue-import-coupons',

		'revglue-coupons_page_revglue-import-banners',

		'revglue-coupons_page_revglue-coupons',

		'revglue-coupons_page_revglue-stores',

		'revglue-coupons_page_revglue-categories',

		'revglue-coupons_page_revglue-banners'

	);

	// Only enqueue if current page is one of plugin pages

	if ( in_array( $hook_suffix, $rg_coupons_hook_suffixes ) ) 

	{

		// Register Admin Styles

		wp_register_style( 'rg-coupon-confirm', RGCOUPON_PLUGIN_URL . 'admin/css/jquery-confirm.css' );

		wp_register_style( 'rg-coupon-confirm-bundled', RGCOUPON_PLUGIN_URL . 'admin/css/bundled.css' );

		wp_register_style( 'rg-coupon-chosen', RGCOUPON_PLUGIN_URL . 'admin/css/chosen.css' );

		wp_register_style( 'rg-coupon-jqueryui', RGCOUPON_PLUGIN_URL . 'admin/css/jquery-ui.min.css' );

		wp_register_style( 'rg-coupon-main', RGCOUPON_PLUGIN_URL . 'admin/css/admin_style.css' );

		wp_register_style( 'rg-coupon-checkbox', RGCOUPON_PLUGIN_URL . 'admin/css/iphone_style.css' );

		wp_register_style( 'rg-coupon-datatables', RGCOUPON_PLUGIN_URL . 'admin/css/jquery.dataTables.css' );

		wp_register_style( 'rg-coupon-fontawesome', RGCOUPON_PLUGIN_URL . 'admin/css/font-awesome.css' );

		// Register Admin Scripts

		wp_register_script( 'rg-coupon-datatables', RGCOUPON_PLUGIN_URL . 'admin/js/jquery.dataTables.js', array ( 'jquery' ) );

		wp_register_script( 'rg-coupon-unveil', RGCOUPON_PLUGIN_URL . 'admin/js/jquery.unveil.js', array ( 'jquery' ) );

		wp_register_script( 'rg-coupon-checkbox', RGCOUPON_PLUGIN_URL . 'admin/js/iphone-style-checkboxes.js', array ( 'jquery' ) );

		wp_register_script( 'rg-coupon-confirm', RGCOUPON_PLUGIN_URL . 'admin/js/jquery-confirm.js', array ( 'jquery' ) );

		wp_register_script( 'rg-coupon-chosen', RGCOUPON_PLUGIN_URL . 'admin/js/chosen.jquery.js', array ( 'jquery' ) );

		wp_register_script( 'rg-coupon-main', RGCOUPON_PLUGIN_URL . 'admin/js/main.js', array ( 'jquery', 'jquery-form' ) );

		wp_localize_script( 'rg-coupon-main', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		wp_enqueue_media();

		// Enqueue sAdmin Styles

		wp_enqueue_style( 'rg-coupon-confirm' );

		wp_enqueue_style( 'rg-coupon-confirm-bundled' );

		wp_enqueue_style( 'rg-coupon-chosen' );

		wp_enqueue_style( 'rg-coupon-jqueryui' );

		wp_enqueue_style( 'rg-coupon-main' );

		wp_enqueue_style( 'rg-coupon-checkbox' );

		wp_enqueue_style( 'rg-coupon-datatables' );

		wp_enqueue_style( 'rg-coupon-fontawesome' );

		// Enqueue Admin Scripts

		wp_enqueue_script( 'rg-coupon-datatables' );

		wp_enqueue_script( 'rg-coupon-unveil' );

		wp_enqueue_script( 'rg-coupon-checkbox' );

		wp_enqueue_script( 'rg-coupon-confirm' );

		wp_enqueue_script( 'rg-coupon-chosen' );

		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_script( 'rg-coupon-main' );

	}

}

add_action( 'admin_enqueue_scripts', 'rg_coupons_admin_enqueue' );

function rg_coupons_admin_actions() 

{

	add_menu_page('RevGlue Coupons', 'RevGlue Coupons', 'manage_options', 'revglue-dashboard', 'rg_coupons_main_page', RGCOUPON_PLUGIN_URL .'admin/images/menuicon.png' );

	add_submenu_page('revglue-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'revglue-dashboard', 'rg_coupons_main_page');

	add_submenu_page('revglue-dashboard', 'Import Coupons', 'Import Coupons', 'manage_options', 'revglue-import-coupons', 'rg_coupons_coupon_import_page');

	add_submenu_page('revglue-dashboard', 'Coupons', 'Coupons', 'manage_options', 'revglue-coupons', 'rg_coupons_listing_page');

	add_submenu_page('revglue-dashboard', 'Categories', 'Categories', 'manage_options', 'revglue-categories', 'rg_coupons_category_listing_page');

	add_submenu_page('revglue-dashboard', 'Stores', 'Stores', 'manage_options', 'revglue-stores', 'rg_coupons_store_listing_page');

}

add_action( 'admin_menu', 'rg_coupons_admin_actions' );

function rg_coupons_create_directory_structures( $dir_structure_array )

{

	$upload = wp_upload_dir();

	$base_dir = $upload['basedir'];

	foreach( $dir_structure_array as $single_dir )

	{

		$create_dir = $base_dir.'/'.$single_dir;

		if ( ! is_dir( $create_dir ) ) 

		{

			mkdir( $create_dir, 0755 );

		}

		$base_dir = $create_dir;

	}

}

function rg_coupons_remove_directory_structures()

{

	$upload = wp_upload_dir();

	$base_dir = $upload['basedir'].'\revglue';

	rg_coupons_folder_cleanup($base_dir);

}

function rg_coupons_folder_cleanup( $dirpath )

{

	if( substr( $dirpath, strlen($dirpath) - 1, 1 ) != '/' )

	{

        $dirpath .= '/';

    }

	$files = glob($dirpath . '*', GLOB_MARK);

	foreach( $files as $file )

	{

		if( is_dir( $file ) )

		{

			deleteDir($file);

		}

		else

		{

			unlink($file);

        }

    }

	rmdir($dirpath);

}

function rg_coupons_auto_import_data()

{

    $auto_var = basename( $_SERVER["REQUEST_URI"] );

	if ( $auto_var ==  'auto_import_data') 

	{

		include( RGCOUPON_PLUGIN_DIR . 'includes/auto-import-data.php');

	}

}

add_action( 'template_redirect', 'rg_coupons_auto_import_data' );

function rg_coupons_populate_recursive_categories( $category_object, $parent_title, &$counter )

{

	global $wpdb;

	$categories_table = $wpdb->prefix.'rg_categories';

	$sql = "SELECT *FROM $categories_table WHERE `parent` = $category_object->rg_category_id ORDER BY `title` ASC";

	$subcategories = $wpdb->get_results($sql);

	if ( !empty($parent_title) )

	{

		$title = $parent_title.'->'.$category_object->title;

		$strong_title = $parent_title.'-><strong>'.$category_object->title.'</strong>';

	} else 

	{

		$title = $category_object->title;

		$strong_title = '<strong>'.$title.'</strong>';

	}

	$catid =	$category_object->rg_category_id;

	?><tr class="ui-state-default">

		<td>

			<?php esc_html_e( $counter ); ?>

		</td>

		<td style="text-align:left;">

			<?php _e( $strong_title ); ?>

		</td> 

		<td>

			<?php echo get_offers_coupons_count_by_catid($catid); ?>



		</td>

		<td style="text-align:left;">

			<div class="revglue-banner-thumb rg_store_icon_thumb_<?php esc_attr_e( $category_object->rg_category_id ); ?>">

				<?php 

				$iconurl = $category_object->icon_url;

				 if (is_numeric(substr($iconurl, 0, 1))) {

					?><a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" data-type="image_url" class="rg_category_delete_icons" href="javascript;"><i class="fa fa-times" aria-hidden="true"></i></a>

					<img alt="image" src="<?php echo  REVGLUE_STORE_ICONS. $iconurl.'.png' ; ?>"><?php

				} else { ?>

				<a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" data-type="image_url" class="rg_category_delete_icons" href="javascript;"><i class="fa fa-times" aria-hidden="true"></i></a>

					<img alt="image" src="<?php  esc_html_e( $category_object->icon_url ) ; ?>">

				<?php }

				?>

			</div>

		</td>

		<td style="text-align:left;">

			<div class="revglue-banner-thumb rg_store_image_thumb_<?php esc_attr_e( $category_object->rg_category_id ); ?>">

				<?php 

				$imageurl = $category_object->image_url;

				 if (is_numeric(substr($imageurl, 0, 1))) { 

					?><a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" data-type="icon_url" class="rg_category_delete_images" href="javascript;"><i class="fa fa-times" aria-hidden="true"></i></a>

					<img alt="image" src="<?php echo  REVGLUE_CATEGORY_BANNERS. $imageurl.'.jpg' ; ?>"><?php

				} else { ?>

					<a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" data-type="icon_url" class="rg_category_delete_images" href="javascript;"><i class="fa fa-times" aria-hidden="true"></i></a>

					<img alt="image" src="<?php esc_html_e( $category_object->image_url) ; ?>">

			 <?php	}

				?>

			</div>

		</td>

		<td>

			<?php 

			if( $category_object->header_category_tag == 'yes' )

			{

				$checked = 'checked="checked"';

			} else

			{

				$checked = '';

			}

			if ($category_object->parent == "0"){

			?>

			<input <?php esc_attr_e( $checked ); ?> type="checkbox" id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" class="rg_store_cat_tag_head" />

			<?php } ?>

		</td>

		<td>

			<?php 

			if( $category_object->popular_category_tag == 'yes' )

			{

				$checked = 'checked="checked"';

			} else

			{

				$checked = '';

			}

			?>

			<input <?php esc_attr_e( $checked ); ?> type="checkbox" id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" class="rg_store_cat_tag" />

		</td>

		<td>

			<a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" class="rg_add_category_icon rg_add_category_icon_<?php esc_attr_e($category_object->rg_category_id ); ?>" href="javascript;">

				<?php if(!empty($category_object->icon_url))

				{

					esc_html_e( 'Edit Icon' );

				} else 

				{

					esc_html_e( 'Add Icon' );

				}

				?>

			</a>

			<a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" class="rg_add_category_image rg_add_category_image_<?php esc_attr_e( $category_object->rg_category_id ); ?>" href="javascript;">

				<?php if(!empty($category_object->image_url))

				{

					esc_html_e( 'Edit Image' );

				} else 

				{

					esc_html_e( 'Add Image' );

				}

				?>

			</a>

		</td>

	</tr><?php

	if( !empty( $subcategories ) )

	{

		foreach( $subcategories as $single_cateogory )

		{

			++$counter;

			rg_coupons_populate_recursive_categories( $single_cateogory, $title, $counter );

		}

	}

}

function rg_admin_notice_if_user_has_not_subscription_id() {

		global $wpdb;

		$rg_projects_table = $wpdb->prefix.'rg_projects'; 

		$sql = "SELECT  email FROM $rg_projects_table WHERE email !='' LIMIT 1";

		$email = $wpdb->get_var($sql);

		if ($email =='') {

		echo '<div class="notice notice-success customstyle  subscriptiondone ">  ';

		echo  '<p>Please read the instructions on  <a class="clicktodash" href=" '.get_home_url()  .'/wp-admin/admin.php?page=revglue-dashboard" target="_blank">RevGlue Dashbaord</a> for importing your RevGlue projects data.  </p>';

		echo  '</div>'; 

		} 

}

add_action( 'admin_notices', 'rg_admin_notice_if_user_has_not_subscription_id' );   

/**************************************************************************************************

*

* Remove Wordpress dashboard default widgets

*

***************************************************************************************************/

function rg_remove_default_widgets(){

	remove_action('welcome_panel', 'wp_welcome_panel');

	remove_meta_box('dashboard_right_now', 'dashboard', 'normal');

	remove_meta_box( 'dashboard_quick_press',   'dashboard', 'side' );      //Quick Press widget

	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );      //Recent Drafts

	remove_meta_box( 'dashboard_primary',       'dashboard', 'side' );      //WordPress.com Blog

	remove_meta_box( 'dashboard_incoming_links','dashboard', 'normal' );    //Incoming Links

	remove_meta_box( 'dashboard_plugins',       'dashboard', 'normal' );    //Plugins

	remove_meta_box('dashboard_activity', 'dashboard', 'normal');

}

add_action('wp_dashboard_setup', 'rg_remove_default_widgets');

?>