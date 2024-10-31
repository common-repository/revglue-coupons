<?php

// Exit if accessed directly

if ( !defined( 'ABSPATH' ) ) exit;

function rg_coupons_coupon_import_page()

{

	global $wpdb;

	$banner_table = $wpdb->prefix.'rg_banner';

	$coupons_table = $wpdb->prefix.'rg_coupons';

	$stores_table = $wpdb->prefix.'rg_stores';

	$categories_table = $wpdb->prefix.'rg_categories';

	$project_table = $wpdb->prefix.'rg_projects';

	$sql5 = "SELECT MAX(date) FROM $coupons_table";

	$last_updated_coupon = $wpdb->get_var($sql5);

	$last_updated_coupon = ( $last_updated_coupon ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_coupon ) ) : '-' );

	$sql_1 = "SELECT MAX(date) FROM $categories_table";

	$last_updated_category = $wpdb->get_var($sql_1);

	$last_updated_category = ( $last_updated_category ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_category ) ) : '-' );

	$sql = "SELECT MAX(date) FROM $stores_table";

	$last_updated_store = $wpdb->get_var($sql);

	$last_updated_store = ( $last_updated_store ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_store ) ) : '-' );

	$sql_6 = "SELECT count(*) as coupons FROM $coupons_table";

	$count_coupon = $wpdb->get_results($sql_6);

	$sql_2 = "SELECT count(*) as categories FROM $categories_table";

	$count_category = $wpdb->get_results($sql_2);

	$sql_3 = "SELECT count(*) as stores FROM $stores_table";

	$count_store = $wpdb->get_results($sql_3);

	$sql4 = "SELECT *FROM $project_table WHERE PROJECT IN ( 'Coupons UK', 'Coupons')"; 

	$project_detail = $wpdb->get_row($sql4, ARRAY_A);

	$rows = $wpdb->num_rows;

	$qry_response = '';

	if( !empty ( $rows ) && !empty($project_detail ) )

	{  

		/* $qry_response = "<div class='panel-white mgBot'>";

		$qry_response .= "<p><b>Your coupons subscription data is ". $project_detail['status'].". </b><img  class='tick-icon' src=".RGCOUPON_PLUGIN_URL. 'admin/images/ticks_icon.png'." />  </p>";

		$qry_response .= "<p><b>Name = </b>".$project_detail['user_name']."</p>";

		$qry_response .= "<p><b>Project = </b>".$project_detail['project']."</p>";

		$qry_response .= "<p><b>Email = </b>".$project_detail['email']."</p>";

		$qry_response .= "<p><b>Expiry Date =</b> ".date("d-M-Y", strtotime($project_detail['expiry_date']))."</p>";

		$qry_response .= "</div>"; */





				$qry_response .= "<div class='panel-white mgBot'>"; 

				if($project_detail['subscription_type']=="Paid"){

$qry_response .= "<div class='panel-white mgBot'>";
				$qry_response .= "<p><b>".esc_html("Your RevEmbed coupons data subscription is ".$project_detail['status']).".  </b>
				<img  class='tick-icon' src=".esc_url(RGCOUPON_PLUGIN_URL.'admin/images/ticks_icon.png')." /> </p>";


				$qry_response .= "<p><b>".esc_html("Name = ")."</b>".esc_html($project_detail['user_name'])."</p>";
				$qry_response .= "<p><b>".esc_html("Project = ")."</b>".esc_html($project_detail['project'])."</p>";
				$qry_response .= "<p><b>".esc_html("Email = ")."</b>".esc_html($project_detail['email'])."</p>";
				$qry_response .= "<p><b>".esc_html("Expiry Date = ")."</b>".esc_html($project_detail['expiry_date'])."</p>";
				$qry_response .= "</div>";

				}else{

					$qry_response .= "<div class='panel-white mgBot'>"; 
				$qry_response .= "<p><b>".esc_html("Your RevEmbed cashback data subscription is ".$project_detail['status']).".  </b>
				<img  class='tick-icon' src=".esc_url(RGCOUPON_PLUGIN_URL.'admin/images/ticks_icon.png')." /></p>";


				$qry_response .= "<p><b>".esc_html("Name = ")."</b>".esc_html("RevEmbed Data")."</p>";
				$qry_response .= "<p><b>".esc_html("Project = ")."</b>".esc_html("Coupons UK")."</p>";
				$qry_response .= "<p><b>".esc_html("Email = ")."</b>".esc_html($project_detail['email'])."</p>";
				$qry_response .= "</div>";
			}

			}



 

	?><div class="rg-admin-container">

		<h1 class="rg-admin-heading "><?php esc_html_e("Import RevGlue Coupons");?></h1>

		<div style="clear:both;"></div>

		<hr/>

		<form id="subscription_form" method="post">

			<table class="inline-table">

				<tr>

					<td style="text-align:right;padding-right: 10px;">

						<label> <?php esc_html_e("Subscription ID:");?> </label>

					</td>

					<td>

						<input id="rg_coupon_sub_id" type="text" name="rg_coupon_sub_id" class="regular-text revglue-input lg-input">

					</td>

					<td style="text-align:right;padding-right: 10px;">

						<label > <?php esc_html_e("RevGlue Email:");?> </label>

					</td>

					<td>

						<input id="rg_coupon_sub_email" type="text" name="rg_coupon_sub_email" class="regular-text revglue-input lg-input">

					</td>

					<td style="text-align:right;padding-right: 10px;">

						<label > <?php esc_html_e("RevGlue Password:");?> </label>

					</td>

					<td>

						<input id="rg_coupon_sub_password" type="password" name="rg_coupon_sub_password" class="regular-text revglue-input lg-input">

					</td>

					<td>

						<button id="rg_coupon_sub_activate" class="button-primary float-left" style="margin-right:5px;"><?php esc_html_e("Validate Account");?></button>

					</td>	

				</tr>

				<tr>

					<td colspan="7">

						<span id="subscription_error"></span>

					</td>

				</tr>

			</table>

		</form>

		<div id="sub_loader" align="center" style="display:none"><img src="<?php echo RGCOUPON_PLUGIN_URL; ?>/admin/images/loading.gif" /></div>

		<hr>

		<div id="subscription_response"><?php echo $qry_response; ?></div>

		<h3><?php esc_html_e("RevGlue Coupons Data Set");?> </h3>

		<div class="sub_page_table">

			<table class="widefat revglue-admin-table">

				<thead>

					<tr>

						<th style="width:15%;"><?php esc_html_e("Data Type");?> </th>

						<th style="width:25%;"><?php esc_html_e("No. of Entries");?></th>

						<th style="width:40%;"><?php esc_html_e("Last Updated");?></th>

						<th style="width:20%;"><?php esc_html_e("Action");?></th>

					</tr>	

				</thead>

					<tr>

						<td><?php esc_html_e("Coupons");?></td>

						<td><span id="rg_coupon_count"><?php esc_html_e( $count_coupon[0]->coupons ); ?></span></td>

						<td><span id="rg_coupon_date"><?php esc_html_e( $last_updated_coupon ); ?></span></td>

						<td class="store-table">

							<a href='rg_coupons_import' class="rg_coupons_open_import_popup">Import</a> | <a href='rg_coupons_delete' class="rg_coupons_open_delete_popup">Delete</a>

							<div id="rg_coupons_import_popup" style="background: #ececec; min-width:350px; right: 5%; margin: 5px 0; padding: 10px; position: absolute; bottom:20px; display:none; border-radius: 8px; border: 1px solid #ccc">This request will validate your API key and update current data. 

							Your current data will be removed and updated with latest data set.

							Please click on confirm if you wish to run the process.<br/>

							<a href="" id="rg_coupon_import" class="rg_coupons_start_import">Import</a> | <a href="javascript:{}" onClick="jQuery('#rg_coupons_import_popup').hide()">Cancel</a>

							</div>

							<div id="rg_coupons_delete_popup" style="background: #ececec; right: 5%; margin: 5px 0; padding: 10px; position: absolute; bottom:20px; display:none; border-radius: 8px; min-width:350px; border: 1px solid #ccc">This request will delete all your current data. Please confirm if you wish to run the process. You will have to import again.<br/>

							<a href="" id="rg_coupon_delete"  class="rg_coupons_start_delete">Delete</a> | <a href="javascript:{}" onClick="jQuery('#rg_coupons_delete_popup').hide()">Cancel</a>

							</div>

						</td>

					</tr>	

					<tr>

						<td>Categories</td>

						<td><span id="rg_category_count"><?php esc_html_e( $count_category[0]->categories ); ?></span></td>

						<td><span id="rg_category_date"><?php esc_html_e( $last_updated_category ); ?></span></td>

						<td>

							<a href='rg_categories_import' class="rg_coupons_open_import_popup">Import</a> | <a href='rg_categories_delete' class="rg_coupons_open_delete_popup">Delete</a>

						</td>

					</tr>

					<tr>

						<td>Stores</td>

						<td><span id="rg_store_count"><?php esc_html_e( $count_store[0]->stores ); ?></span></td> 

						<td><span id="rg_store_date"><?php esc_html_e( $last_updated_store ); ?></span></td>

						<td>

							<a href='rg_stores_import' class="rg_coupons_open_import_popup">Import</a> | <a href='rg_stores_delete' class="rg_coupons_open_delete_popup">Delete</a>

						</td>

					</tr>

			</table>

		</div>

		<div id="store_loader" align="center" style="display:none"><img src="<?php echo RGCOUPON_PLUGIN_URL; ?>/admin/images/loading.gif" /></div>

		<div class="panel-white">

			<h4>Setup Auto Import</h4>

			<p>If you wish to setup auto import of RevGlue Stores Data then go to your server panel and setup CRON JOB. Your server may ask you path for the file to setup. The file path for auto data update is provided below. Import time will depand upon the size of data and server time out.</p> 

		</div>

		<table class="form-table">

			<tr>

				<th><label title="File Path">File Path:</label></th>

				<td><input type="text" class="regular-text revglue-input lg-input" value="<?php echo site_url() . '/revglue-coupons/auto_import_data/'; ?>">

				  </td>

			  </tr>

		</table>

	</div>

	</div>

	<?php

}

?>