<?php

// Exit if accessed directly

if ( !defined( 'ABSPATH' ) ) exit;

function rg_coupons_listing_page()

{

	global $wpdb; 

	$hide_add_table = '';

	$hide_edit_table = '';

	$coupon_table = $wpdb->prefix.'rg_coupons';

	$categories_table = $wpdb->prefix.'rg_categories';

	$stores_table = $wpdb->prefix.'rg_stores';

	$hide_form = 'hide';

	$hide_table = '';

	$heading_text = 'Add New Coupon';

	$rg_id 			= 0;

	$rg_coupon_id 	= 0;

	$rg_store_id 	= 0;

	$title			= '';

	$description	= '';

	$image_url      = '';

	$deeplink       = '';

	$coupon_code    = '';

	$coupon_type    = '';

	$category_ids 	= '';

	$issue_date 	= '';

	$expiry_date 	= '';

	$status  		= '';

	$upload 		= wp_upload_dir();

	$base_dir 		= $upload['basedir'];

	$base_url 		= $upload['baseurl'];

	$uploaddir 		= $base_dir.'/revglue/coupons/';

	$uploadurl 		= $base_url.'/revglue/coupons/';

	if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'add' )

	{

		$hide_table = 'hide';

		$hide_form = '';

		$hide_edit_table = 'hide';

		$heading_add_text='Add Coupon';

	}

	if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' )

	{

		 $hide_table = 'hide';

		 $hide_add_table = 'hide';

		$hide_form = '';

		$heading_editing_text = 'Edit Coupon';

		$coupon_id = absint( $_REQUEST['coupon_id'] );

		$coupon_id = isset($coupon_id) ? $coupon_id: "0";



		$sql1 = "SELECT *FROM $coupon_table WHERE rg_id = $coupon_id";				

		$rows1 = $wpdb->get_results( $sql1 );

		// pre($rows1);

		// die();

		$sql2 = "SELECT *FROM $coupon_table WHERE rg_id = $coupon_id";				

		$rows2 = $wpdb->get_row( $sql2, ARRAY_A );

		//print_r($rows2);

		$sql3 = "SELECT title FROM $stores_table WHERE rg_store_id =". $rows2['rg_store_id'];	

		$rows3 = $wpdb->get_results( $sql3 );

		$sql4 = "SELECT category_ids FROM $coupon_table WHERE rg_id = $coupon_id";





		$rows4 = $wpdb->get_var( $sql4 );

		 $splitCatID =  explode(",", $rows4);

		$sql5 = "SELECT * FROM $categories_table WHERE rg_category_id = $splitCatID[0]";

		$rows5 =  $wpdb->get_results( $sql5 );

		$catName = $rows5[0]->title;

		$rg_id = $rows1[0]->rg_id;

		$rg_coupon_id = $rows1[0]->rg_coupon_id;

		$rg_store_id = $rows1[0]->rg_store_id;

		$title = $rows1[0]->title;

		$description = $rows1[0]->description;

		$image_url = $rows1[0]->image_url;

		$deeplink = $rows1[0]->deeplink;

		$coupon_code = $rows1[0]->coupon_code;

		$coupon_type = $rows1[0]->coupon_type;

		$category_ids = $rows1[0]->category_ids;

		$issue_date = date( "d-m-Y", strtotime( $rows1[0]->issue_date ) );

		$expiry_date = date( "d-m-Y", strtotime( $rows1[0]->expiry_date ) );

		$status = $rows1[0]->status;

	}

	if ( $_SERVER['REQUEST_METHOD'] === 'POST' )

	{	

		// Convert "Category One, Category Two, " to "1,2"

		$string =  sanitize_text_field(rtrim( $_POST['rg_coupon_categories'], ", " ));

		$pieces = explode(",", $string);

		foreach( $pieces as $single_piece )

		{

			$piece_title = trim($single_piece);

			$sql_piece_id = "SELECT `rg_category_id` FROM $categories_table WHERE `title` = '$piece_title'";

			$cat_ids_array[] = $wpdb->get_var($sql_piece_id);

		}

		$converted = implode(",", $cat_ids_array);

		$rg_coupon_store = sanitize_text_field( $_POST['rg_coupon_store'] );

		$sqlitot = "SELECT `rg_store_id` FROM $stores_table WHERE `title` = '$rg_coupon_store'";				

		$store_id = $wpdb->get_var($sqlitot); 

		$hide_form 		= 'hide';

		$rg_id 			= absint ( $_POST['rg_id'] );

		$rg_coupon_id 	= absint ( $_POST['rg_coupon_id'] );

		$rg_store_id 	= $store_id;

	$title			= sanitize_text_field( isset($_POST['rg_coupon_title'])? $_POST['rg_coupon_title']  :""  );

	$description	= sanitize_text_field( isset($_POST['rg_coupon_description']) ? $_POST['rg_coupon_description'] : "" );

	$coupon_type    = sanitize_text_field( isset($_POST['rg_coupon_type']) ? $_POST['rg_coupon_type'] : "" );

	$coupon_code    = sanitize_text_field ( isset($_POST['rg_coupon_code']) ? $_POST['rg_coupon_code'] :"" );

	$deeplink       = esc_url_raw (  isset($_POST['rg_coupon_deeplink'])?$_POST['rg_coupon_deeplink'] :"" );

	$image_url      = esc_url_raw ( isset($_POST['rg_coupon_image_url']) ? $_POST['rg_coupon_image_url'] :"" );

		$category_ids 	= $converted;

		$issue_date		= date( 'Y-M-d', strtotime( sanitize_text_field ( $_POST['rg_coupon_issue_date'] ) ) );

		$expiry_date	= date( 'Y-M-d', strtotime( sanitize_text_field ( $_POST['rg_coupon_expiry_date'] ) ) );

		$status  		= sanitize_text_field ( isset($_POST['rg_status'])?$_POST['rg_status'] : "" );

		if( @$_FILES['rg_coupon_image_file']['error'] == 0 )

		{

			$path = sanitize_text_field($_FILES['rg_coupon_image_file']['name']);
			

			$ext = pathinfo($path, PATHINFO_EXTENSION);

			$image_name = uniqid() . '.' . $ext;

			$tmpName = sanitize_text_field(@$_FILES['rg_coupon_image_file']['tmp_name']) ;


			if ( file_exists( $tmpfile ) )

			{

				$imagesizedata = getimagesize( $tmpfile );

				if( $imagesizedata === FALSE )

				{}

				else

				{

					$uploadfile = $uploaddir . basename( $image_name );

					move_uploaded_file( $tmpfile, $uploadfile );

					$image_url = $uploadurl . $image_name;

				}

			}

		}

		if( empty ( $rg_id ) )

		{

			$wpdb->insert( 

				$coupon_table, 

				array( 

					'rg_store_id' 	=> $rg_store_id,

					'title' 		=> $title, 

					'description' 	=> $description, 

					'coupon_type' 	=> $coupon_type, 

					'coupon_code' 	=> $coupon_code, 

					'deeplink' 		=> $deeplink, 

					'image_url' 	=> $image_url, 

					'category_ids' 	=> $category_ids, 

					'issue_date' 	=> $issue_date, 

					'expiry_date' 	=> $expiry_date, 

					'status' 		=> $status

				) 

			);

		} else 

		{

			$wpdb->update( 

				$coupon_table, 

				array( 

					'rg_store_id' 	=> $rg_store_id,

					'title' 		=> $title, 

					'description' 	=> $description, 

					'coupon_type' 	=> $coupon_type, 

					'coupon_code' 	=> $coupon_code, 

					'deeplink' 		=> $deeplink, 

					'image_url' 	=> $image_url, 

					'category_ids' 	=> $category_ids, 

					'issue_date' 	=> $issue_date, 

					'expiry_date' 	=> $expiry_date, 

					'status' 		=> $status

				), 

				array( 'rg_id' => $rg_id ) 

			);

		}

		$rg_id 			= 0;

		$rg_coupon_id 	= 0;

		$rg_store_id 	= 0;

		$title			= '';

		$description	= '';

		$image_url      = '';

		$deeplink       = '';

		$coupon_code    = '';

		$coupon_type    = '';

		$category_ids 	= '';

		$issue_date 	= '';

		$expiry_date 	= '';

		$status  		= '';

	}

	$sql = "SELECT *FROM $coupon_table ORDER BY rg_id DESC";				

	$coupons = $wpdb->get_results($sql);

	$sqlcatlist = "SELECT `title` FROM $categories_table";				

	$cat_list = $wpdb->get_results($sqlcatlist);

	$sqlstorelist = "SELECT `title` FROM $stores_table";				

	$store_list = $wpdb->get_results($sqlstorelist);

	$sqlattst = "SELECT `title` FROM $stores_table WHERE `rg_store_id` = '$rg_store_id'";		

	$store_title = $wpdb->get_var($sqlattst); 

	?>

	<div class="rg-admin-container add_coupon_div <?php esc_html_e( $hide_form . $hide_add_table  ) ?>">

		<h1 class="rg-admin-heading "><?php esc_html_e( $heading_text ); ?></h1>

		<?php if( empty ( $rg_id ) )

		{

			?><a href="<?php echo admin_url( 'admin.php?page=revglue-coupons' ) ?>"><button  class="button-primary float-right" style="margin-right:5px;" type="submit">Back to Coupons</button></a>

		<?php } ?>

		<div style="clear:both;"></div>

		<hr/>	

		<form action="<?php echo admin_url( 'admin.php?page=revglue-coupons' ) ?>" method="post" enctype="multipart/form-data">

		<input type="hidden" name="rg_id" value="<?php esc_html_e( $rg_id ); ?>">

		<input type="hidden" name="rg_coupon_id" value="<?php esc_html_e( $rg_coupon_id ); ?>">

		<table class="form-table">

			<tr>

				<th>

					<label >Store:</label>

				</th>

				<td>

					<select data-placeholder="Choose a Store..." class="chosen-select regular-text revglue-input lg-input" name="rg_coupon_store">

						<option></option>

						<?php

						foreach( $store_list as $single_store )

						{

							echo '<option value="'.$single_store->title.'" >'.$single_store->title.'</option>';

						}

					?></select>

				</td>	

			</tr>

			<tr>

				<th>

					<label >Title:</label>

				</th>

				<td>

					<input type="text" name="rg_coupon_title" id="rg_coupon_title" class="regular-text revglue-input lg-input" value="<?php esc_html_e( $title ); ?>">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Description:</label>

				</th>

				<td>

					<textarea name="rg_coupon_description" id="rg_coupon_description" class="regular-text lg-input" style="width:70%" rows="5"><?php esc_html_e( $description ); ?></textarea>

				</td>	

			</tr>

			<tr>

				<th>

					<label >Coupon Type :</label>

				</th>

				<td>

					<select id="rg_coupon_type" name="rg_coupon_type" class="regular-text revglue-input lg-input">

						<option value="offer" <?php echo ( $coupon_type != 'code' ? '' : 'selected' ); ?>>Offer</option>

						<option value="code" <?php echo ( $coupon_type == 'code' ? 'selected' : '' ); ?>>Code</option>      		

					</select>

				</td>	

			</tr>

			<tr id="rg_coupon_code_sec" <?php echo ( $coupon_type != 'code' ? 'style="display:none;"' : '' ); ?>>

				<th>

					<label >Coupon code :</label>

				</th>

				<td>

					<input type="text" name="rg_coupon_code" class="regular-text revglue-input lg-input" id="rg_coupon_code" value="<?php  esc_html_e( $coupon_code ); ?>">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Deeplink :</label>

				</th>

				<td>

					<input type="text" name="rg_coupon_deeplink" class="regular-text revglue-input lg-input" id="rg_coupon_deeplink" value="<?php echo esc_url( $deeplink ); ?>">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Categories:</label>

				</th>

				<td>

					<?php

						$catID = $category_ids;

						$pieces = explode(",", $catID);

						$impname = array();

						foreach($pieces as $value){

							if ( !empty($value) )

							{

								$sqlcat = "SELECT *FROM $categories_table where rg_category_id = $value";

								$catrows = $wpdb->get_results($sqlcat);

								foreach($catrows as $storecatnames)

								{

									$impname[] = $storecatnames->title;

								}

							}

						}

						$categories = ( $impname ? implode(', ',$impname) : '' );

					?>

					<select data-placeholder="Choose a Category..." multiple class="chosen-select regular-text revglue-input lg-input" name="rg_coupon_categories">

						<option></option>

						<?php

						foreach( $cat_list as $single_cat )

						{

							echo '<option value="'.$single_cat->title.'" >'.$single_cat->title.'</option>';

						}

					?></select>

				</td>	

			</tr>

			<tr>

				<th>

					<label >Issue Date:</label>

				</th>

				<td>

					<input type="text" readonly="yes" name="rg_coupon_issue_date" class=" regular-text revglue-input lg-input" value="<?php  esc_html_e( date("d-M-Y", strtotime($issue_date ))); ?>">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Expiry Date:</label>

				</th>

				<td>

					<input type="text" readonly="yes" name="rg_coupon_expiry_date" class=" regular-text revglue-input lg-input" value="<?php  esc_html_e(  date("d-M-Y", strtotime($expiry_date )) ); ?>">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Status:</label>

				</th>

				<td>

					<select name="rg_status" class="regular-text revglue-input lg-input">

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

						?><a href="<?php echo admin_url( 'admin.php?page=revglue-coupons' ) ?>" class="revglue-dashbtn float-left">Cancel</a><?php

					}

					?>

				</td>	

			</tr>

		</table>

		</form>

	</div>

	<div class="rg-admin-container edit_coupon_div <?php esc_html_e(  $hide_form . $hide_edit_table ) ?>">

	<h1 class="rg-admin-heading "><?php esc_html_e( $heading_editing_text ); ?></h1>

		<?php if( !empty ( $_REQUEST['coupon_id'] ) )

		{

			?><a href="<?php echo admin_url( 'admin.php?page=revglue-coupons' ) ?>"><button  class="button-primary float-right" style="margin-right:5px;" type="submit">Back to Coupons</button></a>

		<?php } ?>

		<div style="clear:both;"></div>

		<hr/>	

		<form action="<?php echo admin_url( 'admin.php?page=revglue-coupons' ) ?>" method="post" enctype="multipart/form-data">

		<input type="hidden" name="rg_id" value="<?php esc_html_e( $rg_id ); ?>">

		<input type="hidden" name="rg_coupon_id" value="<?php esc_html_e( $rg_coupon_id ); ?>">

		<table class="form-table">

			<tr>

				<th>

					<label >RG ID:</label>

				</th>

				<td>

					<input type="text" readonly="yes" name="rg_coupon_store" class="regular-text revglue-input lg-input" value="<?php echo $_REQUEST['coupon_id']; ?> ">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Store:</label>

				</th>

				<td>

					<input type="text" readonly="yes" name="rg_coupon_store" class="regular-text revglue-input lg-input" value="<?php echo $rows3[0]->title; ?> ">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Title:</label>

				</th>

				<td>

					<input type="text" name="rg_coupon_title" id="rg_coupon_title" class="regular-text revglue-input lg-input" value="<?php esc_html_e( $title ); ?>">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Description:</label>

				</th>

				<td>

					<textarea name="rg_coupon_description" id="rg_coupon_description" class="regular-text lg-input" style="width:70%" rows="5"><?php esc_html_e( $description ); ?></textarea>

				</td>	

			</tr>

			<tr>

				<th>

					<label >Coupon Type :</label>

				</th>

				<td>

					<input type="text" readonly="yes" name="rg_coupon_type" class="regular-text revglue-input lg-input" value="<?php echo $rows1[0]->coupon_type; ?> ">

				</td>	

			</tr>

			<tr id="rg_coupon_code_sec" <?php echo ( $coupon_type != 'code' ? 'style="display:none;"' : '' ); ?>>

				<th>

					<label >Coupon code :</label>

				</th>

				<td>

					<input type="text" id="rg_coupon_code" readonly="yes" name="rg_coupon_code" class="regular-text revglue-input lg-input" value="<?php esc_html_e( $coupon_code ); ?> ">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Deeplink :</label>

				</th>

				<td>

					<input type="text" id="rg_coupon_deeplink" readonly="yes" name="rg_coupon_deeplink" class="regular-text revglue-input lg-input" value="<?php echo esc_url( $deeplink ); ?> ">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Categories:</label>

				</th>

				<td>

					<?php

						$catID = $category_ids;

						$pieces = explode(",", $catID);

						$impname = array();

						foreach($pieces as $value){

							if ( !empty($value) )

							{

								$sqlcat = "SELECT *FROM $categories_table where rg_category_id = $value";

								$catrows = $wpdb->get_results($sqlcat);

								foreach($catrows as $storecatnames)

								{

									$impname[] = $storecatnames->title;

								}

							}

						}

						$categories = ( $impname ? implode(', ',$impname) : '' );

					?>

					<input type="text" readonly="yes" name="rg_coupon_categories" class="regular-text revglue-input lg-input" value="<?php echo $catName; ?> ">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Issue Date:</label>

				</th>

				<td>

					<input type="text" readonly="yes" name="rg_coupon_issue_date" class=" regular-text revglue-input lg-input" value="<?php  esc_html_e( date("d-M-Y", strtotime($issue_date ))  ); ?>">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Expiry Date:</label>

				</th>

				<td>

					<input type="text" readonly="yes" name="rg_coupon_expiry_date" class=" regular-text revglue-input lg-input" value="<?php  esc_html_e( date("d-M-Y", strtotime($expiry_date ))  ); ?>">

				</td>	

			</tr>

			<tr>

				<th>

					<label >Status:</label>

				</th>

				<td>

					<select name="rg_status" class="regular-text revglue-input lg-input">

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

						?><a href="<?php echo admin_url( 'admin.php?page=revglue-coupons' ) ?>" class="revglue-dashbtn float-left">Cancel</a><?php

					}

					?>

				</td>	

			</tr>

		</table>

		</form>

	</div>

	<div class="rg-admin-container list_coupon_div <?php esc_html_e(  $hide_add_table. $hide_edit_table ) ?>">

		<h1 class="rg-admin-heading ">Coupons</h1>

		<a href="<?php echo admin_url( 'admin.php?page=revglue-coupons&action=add' ) ?>"><button  class="button-primary float-right" style="margin-right:5px;" type="submit">Add New Coupon</button></a>

		<div style="clear:both;"></div>

		<hr/>

		<div class="text-right">You can filter coupons by Title, Description, Coupon type, or Coupon code by typing in the Search box below. <br/><br/></div>

		<table id="coupons_admin_screen" class="display" cellspacing="0" width="100%">

			<thead>

				<tr> 



					<th>Title</th> 

					<th>Store</th>

					<th>Coupon type</th>

					<th>Coupon code</th>

					<th>Deeplink</th>

					<th>Categories</th>

					<th>Issue date</th>

					<th>Expiry date</th>

					<th>Status</th>

					<th>Action</th>  

				</tr>

			</thead>

			<tfoot>

				<tr> 

					<th>Title</th> 

					<th>Store</th>

					<th>Coupon type</th>

					<th>Coupon code</th>

					<th>Deeplink</th>

					<th>Categories</th>

					<th>Issue date</th>

					<th>Expiry date</th>

					<th>Status</th>

					<th>Action</th>  

				</tr>

			</tfoot>

		</table>

	</div>

	<?php

}

?>