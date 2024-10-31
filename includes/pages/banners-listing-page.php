<?php

// Exit if accessed directly

if ( !defined( 'ABSPATH' ) ) exit;



function rg_coupons_banner_listing_page()

{

	global $wpdb;

	$banner_table = $wpdb->prefix.'rg_banner';

	

	$hide_form = 'hide';

	$hide_table = '';

	$heading_text = 'Add New Banner';

	$rg_id = 0;

	$rg_store_id = 0;

	$title = '';

	$image_url = '';

	$url = '';

	$placement = '';

	$banner_type = '';

	$status = '';

	$upload = wp_upload_dir();

	$base_dir = $upload['basedir'];

	$base_url = $upload['baseurl'];

	$uploaddir = $base_dir.'/revglue/coupons/banners/';

	$uploadurl = $base_url.'/revglue/coupons/banners/';

	$placements = array(

		'home-top'				=> 'Home:: Top Header',

		'home-slider'			=> 'Home:: Main Banners',

		'home-mid'				=> 'Home:: After Categories',

		'home-bottom'			=> 'Home:: Before Footer',

		'cat-top'				=> 'Category:: Top Header',

		'cat-side-top'			=> 'Category:: Top Sidebar',

		'cat-side-bottom'		=> 'Category:: Bottom Sidebar 1',

		'cat-side-bottom-two'	=> 'Category:: Bottom Sidebar 2',

		'cat-bottom'			=> 'Category:: Before Footer',

		'store-top'				=> 'Store:: Top Header',

		'store-side-top'		=> 'Store:: Top Sidebar',

		'store-side-bottom'		=> 'Store:: Bottom Sidebar 1',

		'store-side-bottom-two'	=> 'Store:: Bottom Sidebar 2',

		'store-main-bottom'		=> 'Store:: After Review',

		'store-bottom'			=> 'Store:: Before Footer',

		'unassigned' 			=> 'Unassigned Banners'

	);

	

	if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'add' )

	{

		$hide_table = 'hide';

		$hide_form = '';

	}

	if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' )

	{

		$hide_table = 'hide';

		$hide_form = '';

		$heading_text = 'Edit Banner';

		

		$banner_id = absint( $_REQUEST['banner_id'] );

		$sql1 = "SELECT *FROM $banner_table WHERE rg_id = $banner_id";				

		$rows1 = $wpdb->get_results( $sql1 );

		

		$rg_id = $rows1[0]->rg_id;

		$rg_store_id = $rows1[0]->rg_store_id;

		$title = $rows1[0]->title;

		$image_url = $rows1[0]->image_url;

		$url = $rows1[0]->url;

		$placement = $rows1[0]->placement;

		$banner_type = $rows1[0]->banner_type;

		$status = $rows1[0]->status;

		$rg_size = $rows1[0]->rg_size;

	

	}

	if ( $_SERVER['REQUEST_METHOD'] === 'POST' )

	{

		$hide_form = 'hide';

		$rg_id = absint ( $_POST['rg_id'] ); 

		$url            	= esc_url_raw ( $_POST['rg_link_url'] );

		$placement      	= sanitize_text_field( $_POST['rg_banner_placement'] );

		$status         	= sanitize_text_field ( $_POST['rg_banner_status'] );

		$title	         	= sanitize_text_field ( $_POST['title'] );

		$image_url        	= esc_url_raw ( $_POST['rg_banner_image_url'] );

		$rg_store_id    	= absint ( $_POST['rg_store_id'] );

		

		if( $_FILES['rg_banner_image_file']['error'] == 0 )

		{

			$path = sanitize_text_field($_FILES['rg_banner_image_file']['name']) ;

			$ext = pathinfo($path, PATHINFO_EXTENSION);

			$title = uniqid() . '.' . $ext;

			$tmpfile = sanitize_text_field($_FILES['rg_banner_image_file']['tmp_name']) ;

			if ( file_exists( $tmpfile ) )

			{

				$imagesizedata = getimagesize( $tmpfile );

				if( $imagesizedata === FALSE )

				{}

				else

				{

					$uploadfile = $uploaddir . basename( $title );

					move_uploaded_file( $tmpfile, $uploadfile );

				}

			}

		}

		

		if( empty ( $rg_id ) )

		{

			$wpdb->insert( 

				$banner_table, 

				array( 

					'title' 		=> $title, 

					'image_url' 	=> $image_url, 

					'url' 			=> $url, 

					'rg_store_id' 	=> $rg_store_id, 

					'placement' 	=> $placement, 

					'status' 		=> $status

				) 

			);



		} else 

		{

			if( empty ( $title ) && empty ( $image_url )   )

			{

				$wpdb->update( 

					$banner_table, 

					array( 

						'url' 			=>  $url, 

						'placement' 	=> $placement, 

						'status' 		=> $status

					), 

					array( 'rg_id' => $rg_id ) 

				);

			} else

			{

				$wpdb->update( 

					$banner_table, 

					array( 

						'title' 		=> $title, 

						'image_url' 	=> $image_url, 

						'url' 			=> $url,

						'rg_store_id' 	=> $rg_store_id, 

						'placement' 	=> $placement, 

						'status' 		=> $status

					), 

					array( 'rg_id' => $rg_id )  

				);

			}

		}

		// echo $wpdb->last_query;

	// die();	

		

		$rg_id = '';

		$rg_store_id = '';

		$title = '';

		$image_url = '';

		$url = '';

		$placement = '';

		$banner_type = '';

		$status = '';

	}

	

	?><div class="rg-admin-container add_banner_div <?php esc_html_e( $hide_form ) ?>">

		<h1 class="rg-admin-heading "><?php esc_html_e( $heading_text ); ?></h1>

		<?php if( empty ( $rg_id ) )

		{

			?><a href="<?php echo admin_url( 'admin.php?page=revglue-banners' ) ?>"><button  class="button-primary float-right" style="margin-right:5px;" type="submit">Back to Banners</button></a>

		<?php } ?>

		<div style="clear:both;"></div>

		<hr/>	

		

		<form action="<?php echo admin_url( 'admin.php?page=revglue-banners' ) ?>" method="post" enctype="multipart/form-data">

		<input type="hidden" name="rg_id" value="<?php esc_html_e( $rg_id ); ?>">

		<input type="hidden" name="rg_store_id" value="<?php esc_html_e( $rg_store_id ); ?>">

		<table class="form-table">

			<tr <?php echo ( empty ( $rg_id ) ? 'style="display:none;"' : '' ); ?>>

				<th>Banner:</th>

				<td>

					<?php

					if( $image_url == '' ) 

					{  

						$uploadbanner = $uploadurl . $title;

						?><div class="revglue-banner-thumb"><img src="<?php echo esc_url( $uploadbanner ); ?>"/></div><?php 

					} else

					{

						?><div class="revglue-banner-thumb"><img src="<?php echo esc_url( $image_url ); ?>"/></div><?php 

					}

					?>

				</td>

			</tr>			

			<tr>

				<th>

					<label >Thumbnail Type :</label>

				</th>

				<td>

					<select id="rg_banner_image_type" name="rg_banner_image_type" class="regular-text revglue-input lg-input">

						<option value="url" <?php echo ( ! empty ( $image_url ) ? 'selected' : '' ); ?>>Url</option>

						<option value="upload" <?php echo ( empty ( $image_url ) ? 'selected' : '' ); ?>>Upload</option>      		

					</select>

				</td>	

			</tr>

			<tr id="rg_coupons_banner_image_url" <?php echo ( empty ( $image_url ) ? 'style="display:none;"' : '' ); ?>>

				<th>

					<label >Image URL :</label>

				</th>

				<td>

					<input type="text" name="rg_banner_image_url" class="regular-text revglue-input lg-input" id="rg_banner_image_url" value="<?php echo esc_url( $image_url ); ?>">

				</td>	

			</tr>

			<tr id="rg_stores_banner_image_url" <?php echo ( empty ( $image_url ) ? 'style="display:none;"' : '' ); ?>>

				<th>

					<label >Image Size :</label>

				</th>

				<td>

					<input type="text" readonly class="regular-text revglue-input lg-input" id="rg_banner_image_url" value="<?php echo  $rg_size ; ?>">

				</td>	

			</tr>

			<tr id="rg_stores_banner_image_url" <?php echo ( empty ( $image_url ) ? 'style="display:none;"' : '' ); ?>>

				<th>

					<label >Store Title :</label>

				</th>

				<td>

					<input type="text"  class="regular-text revglue-input lg-input" name="title" id="rg_store_title" value="<?php echo  $title ; ?>">

				</td>	

			</tr>

			 

			<tr id="rg_coupons_banner_image_upload" <?php echo ( ! empty ( $image_url ) ? 'style="display:none;"' : '' ); ?>>

				<th>

					<label >Choose your file:</label>

				</th>

				<td>

					<input type="file" name="rg_banner_image_file" id="rg_banner_image_file">

				</td>	

			</tr>

			<tr id="rg_stores_banner_url">

				<th>

					<label >Link URL :</label>

				</th>

				<td>

					<input type="text" name="rg_link_url" class="regular-text revglue-input lg-input" id="rg_link_url" value="<?php echo esc_url( $url ); ?>">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Placement:</label>

				</th>

				<td>

					<select name="rg_banner_placement" class="regular-text revglue-input lg-input">

						<option value="Select" <?php echo ( ( $placement == 'Select' ) ? 'selected' : '' ); ?>>Select</option>

						<?php foreach( $placements as $key => $value )

						{

							?><option value="<?php esc_html_e($key) ?>" <?php echo ( ( $placement == $key ) ? 'selected' : '' ); ?>><?php esc_html_e($value) ?></option><?php

						}

						?>

					</select>

				</td>	

			</tr>

			<tr>

				<th>

					<label >Status:</label>

				</th>

				<td>

					<select name="rg_banner_status" class="regular-text revglue-input lg-input">

						<option value="active" <?php echo ( ( $status == 'active' ) ? 'selected' : '' ); ?>>Active</option>

						<option value="inactive" <?php echo ( ( $status == 'inactive' ) ? 'selected' : '' ); ?>>InActive</option>

					</select>

				</td>	

			</tr>

			<tr>

				<th>&nbsp;</th>

				<td>

					<button  class="button-primary float-left" style="margin-right:5px;" type="submit">Save</button>

					<?php if( !empty ( $rg_id ) )

					{

						?><a href="<?php echo admin_url( 'admin.php?page=revglue-banners' ) ?>" class="revglue-dashbtn float-left">Cancel</a><?php

					}

					?>

				</td>	

			</tr>

		</table>

		</form>

	</div>

	<div class="rg-admin-container list_banner_div <?php esc_html_e( $hide_table ) ?>">

		<h1 class="rg-admin-heading ">Banners</h1>

		<a href="<?php echo admin_url( 'admin.php?page=revglue-banners&action=add' ) ?>"><button  class="button-primary float-right" style="margin-right:5px;" type="submit">Add New Banner</button></a>

		<div style="clear:both;"></div>

		<hr/>

		<div class="text-left bannersizedetail">

			<h4>Banners Size Detail</h4>

			<ul>

				<li>Top Position Banner Size: 468x60</li>

				<li>Sidebar Position Banner Size: 300x300 , 300x250, 120x600</li>

				<li>Bottom Position Banner Size: 728x90</li>

			</ul>

		</div>

		<div style="text-align: right;">You can filter banners by RG ID, Store Name, Banner type, Placement, or Size by typing in the Search box below. <br/><br/></div>

		<table id="banners_admin_screen" class="display" cellspacing="0" width="100%">

			<thead>

				<tr>

					<th>Banner</th>

					<th>RG ID</th>

					<th>Store Name</th>

					<th>Banner type</th>

					<th>Placement</th>

					<th>Size</th>

					<th>Affiliate network Link</th>

					<th>Status</th>

					<th>Action</th>

				</tr>

			</thead>

			<tfoot>

				<tr>

					<th>Banner</th>

					<th>RG ID</th>

					<th>Store Name</th>

					<th>Banner type</th>

					<th>Placement</th>

					<th>Size</th>

					<th>Affiliate network Link</th>

					<th>Status</th>

					<th>Action</th>

				</tr>

			</tfoot>

		</table>

	</div>

	<?php

}

?>