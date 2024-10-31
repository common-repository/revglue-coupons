<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
function revglue_coupons_subscription_validate() 
{
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$sanitized_sub_id	= sanitize_text_field( $_POST['sub_id'] );
	$sanitized_email	= sanitize_email( $_POST['sub_email'] );
	$password  			= $_POST['sub_pass'];
	//die(RGCOUPON_API_URL . "api/validate_subscription_key/$sanitized_email/$password/$sanitized_sub_id");

	$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGCOUPON_API_URL . "api/validate_subscription_key/$sanitized_email/$password/$sanitized_sub_id", array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
	$result = $resp_from_server['response']['result'];
	$iFrameid =$result['iframe_id'];
	$subscriptionType= '';
	$data=array();
	if($iFrameid!=""){
		$data=array( 
			'subcription_id' 				=> $sanitized_sub_id, 
			'user_name' 					=> $result['user_name'], 
			'email' 						=> $result['email'], 
			'project' 						=> $result['project'], 
			'expiry_date' 					=> $result['expiry_date'], 
			'partner_iframe_id' 			=> $result['iframe_id'], 
			'password' 						=> $password, 
			'status' 						=> $result['status']
		) ;
		$subscriptionType="Free";
	}else{
		$data=array( 
			'subcription_id' 				=> $sanitized_sub_id, 
			'user_name' 					=> $result['user_name'], 
			'email' 						=> $result['email'], 
			'project' 						=> $result['project'], 
			'expiry_date' 					=> $result['expiry_date'],
			'password' 						=> $password,  
			'status' 						=> $result['status']
			) ;
	$subscriptionType="Paid";
	}
	$string = '';
	if( $resp_from_server['response']['success'] == 'true' )
	{
		$sql = "SELECT * FROM $project_table WHERE project LIKE '".$result['project']."' AND status = 'active'";
	    $execute_query = $wpdb->get_results( $sql );
		$rows = $wpdb->num_rows;
		if( empty ( $rows ) )
		{	
			$wpdb->insert( 
				$project_table, 
				$data
			);
			$lastid = $wpdb->insert_id;
			$wpdb->query("UPDATE $project_table SET `subscription_type`= '$subscriptionType' WHERE `rg_project_id`=$lastid");
			$sql = "SELECT *FROM $project_table WHERE project LIKE '".$result['project']."' AND status = 'active'";
			// die($sql);
			$execute_query = $wpdb->get_results( $sql );
			// pre($execute_query);
			if(!empty($execute_query)){
				$string .= "<div class='panel-white mgBot'>"; 
				if($execute_query[0]->subscription_type=="Paid"){
					

$string .= "<p><b>".esc_html('Your coupons subscription data is '.$execute_query['status'])."</b><img  class='tick-icon' src=".esc_url(RGCOUPON_PLUGIN_URL.'admin/images/ticks_icon.png')." /> </p>";


$string .= "<p><b>".esc_html("Name = ")."</b>".esc_html($execute_query[0]->user_name)."</p>";
$string .= "<p><b>".esc_html("Project = ")."</b>".esc_html($execute_query[0]->project)."</p>";
$string .= "<p><b>".esc_html("Email = ")."</b>".esc_html($execute_query[0]->email)."</p>";

$string .= "<p><b>".esc_html("Expiry Date = ")."</b>".date('d-M-Y' ,  strtotime($execute_query[0]->expiry_date))."</p>";

}else{

$string .= "<p><b>".esc_html('Your RevEmbed Free Coupons Data is '.$execute_query[0]->status)."</b><img  class='tick-icon' src=".RGCOUPON_PLUGIN_URL. 'admin/images/ticks_icon.png'." /> </p>";

$string .= "<p><b>".esc_html("Name = ")."</b>".esc_html("RevEmbed Data")."</p>";

$string .= "<p><b>".esc_html("Project = ")."</b>".esc_html("Coupons UK")."</p>";

$string .= "<p><b>".esc_html("Email = ")."</b>".esc_html($execute_query[0]->email)."</p>";

				}
				$string .= "</div>";
			}else{

				$string .= "<p>".esc_html("» Your subscription unique ID ")."<b class='grmsg'>".esc_html($sanitized_sub_id)."</b>".esc_html("is Invalid.")."</p>";



			}
		} else 
		{	


			$string .= "<div style='color: green;'>".esc_html('You already have subscription of this project, thankyou!')."</div>";	
		}
	} else 
	{
		$string .= "<p>".esc_html("» Your subscription unique ID ")."<b class='grmsg'>".esc_html($sanitized_sub_id)."</b>".esc_html("is Invalid.")."</p>";
	}
	echo $string;
	wp_die();
}
add_action( 'wp_ajax_revglue_coupons_subscription_validate', 'revglue_coupons_subscription_validate' );
function revglue_coupons_data_import()
{
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$coupons_table = $wpdb->prefix.'rg_coupons';
	$categories_table = $wpdb->prefix.'rg_categories';
	$stores_table = $wpdb->prefix.'rg_stores';
	$date = date("Y-m-d H:i:s");
	$date_only = date("Y-m-d");
	$string = '';
	$import_type = sanitize_text_field( $_POST['import_type'] );
	$sql = "SELECT * FROM $project_table WHERE `project` IN( 'Coupons UK', 'Coupons')";
	$project_detail = $wpdb->get_results($sql);
	$rows = $wpdb->num_rows;
	if( !empty ( $rows ) )
	{
		
		$subscriptionid = $project_detail[0]->subcription_id;
		$useremail = $project_detail[0]->email;
		$userpassword = $project_detail[0]->password;
		$projectid = $project_detail[0]->partner_iframe_id;
		if( $import_type == 'rg_coupons_import')
		{
			revglue_coupons_update_subscription_expiry_date($subscriptionid, $userpassword, $useremail, $projectid);

			if($project_detail[0]->expiry_date=="Free" && $project_detail[0]->partner_iframe_id!="" ){
				$partner_coupons_url ="https://www.revglue.com/partner/coupons/".$project_detail[0]->partner_iframe_id."/json/wp";
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $partner_coupons_url, array( 'timeout' => 12000, 'sslverify'   => false ) ) ), true);
			}else{
				// die("YY");
				// echo "Paid API";
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGCOUPON_API_URL . "api/vouchers/json/".$project_detail[0]->subcription_id, array( 'timeout' => 12000, 'sslverify'   => false ) ) ), true);
				// die(RGCOUPON_API_URL . "api/vouchers/json".$project_detail[0]->subcription_id);
			}
			if($resp_from_server['response']['success'] == 1 )
			{
				$rescoupons = $resp_from_server['response']['coupons']; 
				foreach($rescoupons as $row)
				{	
					$sqlincat = "SELECT rg_coupon_id FROM $coupons_table WHERE rg_coupon_id = '".$row['coupons_id']."'";
					$rg_coupon_exists = $wpdb->get_var( $sqlincat );
					$issue_date  =		 date( "Y-m-d", strtotime($row['issue_date']));
					$expiry_date  =		 date( "Y-m-d", strtotime($row['expiry_date']));
					if( empty( $rg_coupon_exists ) )
					{
						 $wpdb->insert( 
							$coupons_table, 
							array( 
								'rg_coupon_id' 	=> $row['coupons_id'], 
								'rg_store_id' 	=> $row['rg_store_id'], 
								'title' 		=> $row['coupons_title'], 
								'description' 	=> $row['coupons_description'], 
								'deeplink' 		=> $row['deeplink'], 
								'coupon_code' 	=> $row['coupon_code'], 
								'coupon_type' 	=> $row['coupon_type'], 
								'category_ids' 	=> $row['coupon_category_ids'], 
								'issue_date' 	=> $issue_date,
								'expiry_date' 	=> $expiry_date,
								'date' 			=> $date
							) 
						); 
					} else 
					{
						$wpdb->update( 
							$coupons_table, 
							array(
								'rg_store_id' 	=> $row['rg_store_id'], 
								'title' 		=> $row['coupons_title'], 
								'description' 	=> $row['coupons_description'], 
								'deeplink' 		=> $row['deeplink'], 
								'coupon_code' 	=> $row['coupon_code'], 
								'coupon_type' 	=> $row['coupon_type'], 
								'category_ids' 	=> $row['coupon_category_ids'], 
								'issue_date' 	=> $issue_date,
								'expiry_date' 	=> $expiry_date, 
								'date' 			=> $date
							),
							array( 'rg_coupon_id' => $rg_coupon_exists )
						); 
					}
					// echo $wpdb->last_query;
					// die();
				}
				$wpdb->query( "DELETE FROM $coupons_table WHERE Date(`date`) != '$date_only' " );
			} else 
			{
				$string .= '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
			}
		} else
		 if( $import_type == 'rg_categories_import'  )
		{
			revglue_coupons_update_subscription_expiry_date($subscriptionid, $userpassword, $useremail, $projectid);

			if($project_detail[0]->expiry_date=="Free" && $project_detail[0]->partner_iframe_id!="" ){
				$partner_coupons_url ="https://www.revglue.com/partner/coupon_categories/json";
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $partner_coupons_url, array( 'timeout' => 12000, 'sslverify'   => false ) ) ), true);
			}else{
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGCOUPON_API_URL . "api/voucher_categories/json/".$project_detail[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
			}
			$resultCategories = $resp_from_server['response']['categories'];
			if($resp_from_server['response']['success'] == 1 )
			{
				foreach($resultCategories as $row)
				{	
					$sqlincat = "SELECT rg_category_id FROM $categories_table WHERE rg_category_id = '".$row['coupon_category_id']."'";
					$rg_category_exists = $wpdb->get_var( $sqlincat );
					if( empty( $rg_category_exists ) )
					{					
						$title 		= $row['coupon_cateogry_title'];
						$url_key 	= preg_replace('/[^\w\d_ -]/si', '', $title); 	// remove any special character
						$url_key 	= preg_replace('/\s\s+/', ' ', $url_key);		// replacing multiple spaces to signle
						$url_key 	= strtolower(str_replace(" ","-",$url_key));
						$wpdb->insert( 
							$categories_table, 
							array( 
								'rg_category_id' 		=> $row['coupon_category_id'], 
								'title' 				=> $row['coupon_cateogry_title'], 
								'url_key' 				=> $url_key, 
								'parent' 				=> $row['parent_category_id'] ,
								'date' 			        => $date
							) 
						);
					} else 
					{
						$title 		= $row['coupon_cateogry_title'];
						$url_key 	= preg_replace('/[^\w\d_ -]/si', '', $title); 	// remove any special character
						$url_key 	= preg_replace('/\s\s+/', ' ', $url_key);		// replacing multiple spaces to signle
						$url_key 	= strtolower(str_replace(" ","-",$url_key));
						$wpdb->update( 
							$categories_table, 
							array( 
								'title' 				=> $row['coupon_cateogry_title'], 
								'url_key' 				=> $url_key, 
								'date' 			        => $date,
								'parent' 				=> $row['parent_category_id']
							),
							array( 'rg_category_id' => $rg_category_exists )
						);
					}
				}
				$wpdb->query( "DELETE FROM $categories_table WHERE `date` != '$date' " );
			    $sqlParentCat = "SELECT * FROM $categories_table ";
				$CateIDs = $wpdb->get_results( $sqlParentCat ); 
				foreach ($CateIDs as $key => $cID) {
								$update_array = array();
								if($cID->parent == '0'){
									$update_array['header_category_tag'] = 'yes';
									$catid = $cID->rg_category_id;
								}else{
									$catid = $cID->parent;
									//echo $catid.",";
								}
								$update_array['icon_url'] = $catid;
								$update_array['image_url'] = $catid;
								if($key < 15 && $catid != 41){
									$update_array['popular_category_tag'] = 'yes';
								}
								$wpdb->update( 
										$categories_table, 
										$update_array,
										array( 'rg_category_id' => $cID->rg_category_id )
									); 
								}
				$sqlecats = "SELECT rg_category_id FROM $categories_table";
				$existing_categories = $wpdb->get_results($sqlecats);
				foreach( $existing_categories as $single_category )
				{
					$activate = array_search( $single_category->rg_category_id, array_column( $resultCategories, 'coupon_category_id' ) );
					if( $activate === FALSE )
					{
						$wpdb->update( 
							$categories_table, 
							array( 
								'status' 				=> 'in-active', 
							),
							array( 'rg_category_id' => $single_category->rg_category_id )
						);
					}
				}
			} else 
			{
				$string .= '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
			}
		} else if( $import_type == 'rg_stores_import'  )
		{
			revglue_coupons_update_subscription_expiry_date($subscriptionid, $userpassword, $useremail, $projectid);

			if($project_detail[0]->expiry_date=="Free" && $project_detail[0]->partner_iframe_id!="" ){
				$partner_coupons_url ="https://www.revglue.com/partner/coupon_stores/".$project_detail[0]->partner_iframe_id."/json/wp";
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $partner_coupons_url, array( 'timeout' => 12000, 'sslverify'   => false ) ) ), true);
			}else{
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGCOUPON_API_URL . "api/voucher_stores/json/".$project_detail[0]->subcription_id, array( 'timeout' => 12000, 'sslverify'   => false ) ) ), true);
			}
			$result = $resp_from_server['response']['stores'];
	  		if($resp_from_server['response']['success'] == 1 )
			{
				foreach($result as $row)
				{
					$sqlinstore = "SELECT rg_store_id FROM $stores_table WHERE rg_store_id = '".$row['rg_store_id']."'";
					$rg_store_exists = $wpdb->get_var( $sqlinstore );
					if( empty( $rg_store_exists ) )
					{
						$wpdb->insert( 
							$stores_table, 
							array( 
								'rg_store_id' 				=> $row['rg_store_id'], 
								'mid' 						=> $row['affiliate_network_mid'], 
								'title' 					=> $row['store_title'], 
								'url_key' 					=> $row['url_key'], 
								'description' 				=> $row['store_description'], 
								'image_url' 				=> $row['image_url'], 
								'affiliate_network' 		=> $row['affiliate_network'], 
								'affiliate_network_link'	=> $row['deeplink'], 
								'store_base_currency' 		=> $row['store_base_currency'], 
								'date' 			    		=> $date,
								'store_base_country' 		=> $row['store_base_country'], 
								'category_ids' 				=> $row['category_ids'],
							) 
						);
					} else 
					{
						$wpdb->update( 
							$stores_table, 
							array( 
								'mid' 						=> $row['affiliate_network_mid'], 
								'title' 					=> $row['store_title'], 
								'url_key' 					=> $row['url_key'], 
								'description' 				=> $row['store_description'], 
								'image_url' 				=> $row['image_url'], 
								'affiliate_network' 		=> $row['affiliate_network'], 
								'affiliate_network_link'	=>  $row['deeplink'], 
								'store_base_currency' 		=> $row['store_base_currency'], 
								'store_base_country' 		=> $row['store_base_country'], 
								'date' 			    		=> $date,
								'category_ids' 				=> $row['category_ids']
							),
							array( 'rg_store_id' => $rg_store_exists )
						);
					}
				}
				$wpdb->query( "DELETE FROM $stores_table WHERE `date` != '$date' " ); 
				$sql = "SELECT rg_store_id FROM $stores_table WHERE  homepage_store_tag ='no' LIMIT 20";
						$storeIDs = $wpdb->get_results( $sql );
						if ( count($storeIDs) > 0 ){
							foreach ($storeIDs as $sID) {
								$wpdb->update( 
										$stores_table, 
										array( 
											'homepage_store_tag' 	=> 'yes'  
										),
										array( 'rg_store_id' => $sID->rg_store_id )
									);
								}
						} 
			} else 
			{
				$string .= '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
			}
		}
	} else 
	{
		$string .= "<p style='color:red'>Please subscribe for your RevGlue project first, then you have the facility to import the data";
	}
	$response_array = array();
	$response_array['error_msgs'] = $string;
	$sql_4 = "SELECT MAX(date) FROM $coupons_table";
	$last_updated_coupon = $wpdb->get_var($sql_4);
	$response_array['last_updated_coupon'] = ( $last_updated_coupon ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_coupon ) ) : '-' );
	$sql_1 = "SELECT MAX(date) FROM $categories_table";
	$last_updated_category = $wpdb->get_var($sql_1);
	$response_array['last_updated_category'] = ( $last_updated_category ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_category ) ) : '-' );
	$sql = "SELECT MAX(date) FROM $stores_table";
	$last_updated_store = $wpdb->get_var($sql);
	$response_array['last_updated_store'] = ( $last_updated_store ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_store ) ) : '-' );
	$sql_5 = "SELECT count(*) as coupons FROM $coupons_table";
	$count_coupon = $wpdb->get_results($sql_5);
	$response_array['count_coupon'] = $count_coupon[0]->coupons;
	$sql_2 = "SELECT count(*) as categories FROM $categories_table";
	$count_category = $wpdb->get_results($sql_2);
	$response_array['count_category'] = $count_category[0]->categories;
	$sql_3 = "SELECT count(*) as stores FROM $stores_table";
	$count_store = $wpdb->get_results($sql_3);
	$response_array['count_store'] = $count_store[0]->stores; 
	echo json_encode($response_array); 
	wp_die();
}
add_action( 'wp_ajax_revglue_coupons_data_import', 'revglue_coupons_data_import' );
function revglue_banner_data_import()
{
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$banner_table = $wpdb->prefix.'rg_banner';
	$string = '';
	$date_only = date("Y-m-d");
	$import_type = sanitize_text_field( @$_POST['import_type'] );
	$sql = "SELECT *FROM $project_table WHERE project LIKE 'Banners UK'";
	$project_detail = $wpdb->get_results($sql);
	$rows = $wpdb->num_rows;
	if( !empty ( $rows ) )
	{
		if( $import_type == 'rg_banners_import'  )
		{
			$i = 0;
			$page = 1;
			do {
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGCOUPON_API_URL . "api/banners/json/".$project_detail[0]->subcription_id."/".$page, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
				update_option("rg_banners_status", $page);
				$total = ceil( $resp_from_server['response']['banners_total'] / 1000 ) ;
				$result = $resp_from_server['response']['banners'];
				if($resp_from_server['response']['success'] == true )
				{
					foreach($result as $row)
					{
						$sqlinstore = "SELECT rg_store_banner_id FROM $banner_table WHERE rg_store_banner_id = '".$row['rg_banner_id']."' AND `banner_type` = 'imported'";
						$rg_banner_exists = $wpdb->get_var( $sqlinstore );
						if( empty( $rg_banner_exists ) )
						{
							$wpdb->insert( 
								$banner_table, 
								array( 
									'rg_store_banner_id' 	=> $row['rg_banner_id'], 
									'rg_store_id' 			=> $row['rg_store_id'], 
									'title' 				=> $row['banner_alt_text'], 
									'image_url' 			=> $row['banner_image_url'], 
									'url' 					=> $row['deep_link'], 
									'date' 			    	=> $date_only,
									'rg_size' 			    => $row['width_pixels'].'x'.$row['height_pixels'], 
									'placement' 			=> 'unassigned', 
									'banner_type' 			=> 'imported'
								) 
							);
						} else 
						{
							$wpdb->update( 
								$banner_table, 
								array( 
									'rg_store_id' 			=> $row['rg_store_id'], 
									'title' 				=> $row['banner_alt_text'], 
									'image_url' 			=> $row['banner_image_url'],	
									'date' 			    	=> $date_only,
									'url' 					=> $row['deep_link']
								),
								array( 'rg_store_banner_id' => $rg_banner_exists )
							);
						}										
					}
				} else 
				{
					$string .= '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
				}
				$i++;
				$page++;
			} while ( $i < $total );
			$wpdb->query( "DELETE FROM $banner_table WHERE `date` != '$date_only' " );
		}
	} else 
	{
		$string .= "<p style='color:red'>Please subscribe for your RevGlue project first, then you have the facility to import the data";
	}
	$response_array = array();
	$response_array['error_msgs'] = $string;
	$sql1 = "SELECT count(*) as banner FROM $banner_table WHERE banner_type= 'imported'";
	$count_banner = $wpdb->get_results($sql1);
	$response_array['count_banner'] = $count_banner[0]->banner; 
	echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_revglue_banner_data_import', 'revglue_banner_data_import' );
function revglue_coupons_data_delete()
{
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores';
	$coupons_table = $wpdb->prefix.'rg_coupons';
	$categories_table = $wpdb->prefix.'rg_categories';
	$banner_table = $wpdb->prefix.'rg_banner';
	$data_type = sanitize_text_field( $_POST['data_type'] );
	$response_array = array();
	if( $data_type == 'rg_coupons_delete' )
	{
		$response_array['data_type'] = 'rg_coupons';
		$wpdb->query( "DELETE FROM $coupons_table" );	
		$sql = "SELECT MAX(date) FROM $coupons_table";
		$last_updated_store = $wpdb->get_var($sql);
		$response_array['last_updated_coupon'] = ( $last_updated_store ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_store ) ) : '-' );
		$sql2 = "SELECT count(*) as stores FROM $coupons_table";
		$count_coupon = $wpdb->get_results($sql2);
		$response_array['count_coupon'] = $count_coupon[0]->stores;
	} else if( $data_type == 'rg_categories_delete' )
	{
		$response_array['data_type'] = 'rg_categories';
		$wpdb->query( "DELETE FROM $categories_table" );	
		$sql = "SELECT MAX(date) FROM $categories_table";
		$last_updated_category = $wpdb->get_var($sql);
		$response_array['last_updated_category'] = ( $last_updated_category ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_category ) ) : '-' );
		$sql2 = "SELECT count(*) as categories FROM $categories_table";
		$count_category = $wpdb->get_results($sql2);
		$response_array['count_category'] = $count_category[0]->categories;
	} else if( $data_type == 'rg_stores_delete' )
	{
		$response_array['data_type'] = 'rg_stores';
		$wpdb->query( "DELETE FROM $stores_table" );	
		$sql = "SELECT MAX(date) FROM $stores_table";
		$last_updated_store = $wpdb->get_var($sql);
		$response_array['last_updated_store'] = ( $last_updated_store ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_store ) ) : '-' );
		$sql2 = "SELECT count(*) as stores FROM $stores_table";
		$count_store = $wpdb->get_results($sql2);
		$response_array['count_store'] = $count_store[0]->stores;
	} else if( $data_type == 'rg_banners_delete' )
	{
		$response_array['data_type'] = 'rg_banners';
		$wpdb->query( "DELETE FROM $banner_table where banner_type='imported'" );	
		$sql1 = "SELECT count(*) as banner FROM $banner_table where banner_type= 'imported'";
		$count_banner = $wpdb->get_results($sql1);
		$response_array['count_banner'] = $count_banner[0]->banner;
	}
	echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_revglue_coupons_data_delete', 'revglue_coupons_data_delete' );
function revglue_coupons_update_home_store()
{
	global $wpdb; 
	$stores_table = $wpdb->prefix.'rg_stores';
	$store_id		= absint( $_POST['store_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$stores_table, 
		array( 'homepage_store_tag' => $cat_state ), 
		array( 'rg_store_id' => $store_id )
	);
	echo $store_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_coupons_update_home_store', 'revglue_coupons_update_home_store' );
function revglue_coupons_update_popular_store()
{
	global $wpdb; 
	$stores_table = $wpdb->prefix.'rg_stores';
	$store_id		= absint( $_POST['store_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$stores_table, 
		array( 'popular_store_tag' => $cat_state ), 
		array( 'rg_store_id' => $store_id )
	);
	echo $store_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_coupons_update_popular_store', 'revglue_coupons_update_popular_store' );
function revglue_coupons_update_header_category()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$categories_table, 
		array( 'header_category_tag' => $cat_state ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_coupons_update_header_category', 'revglue_coupons_update_header_category' );
function revglue_coupons_update_popular_category()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$categories_table, 
		array( 'popular_category_tag' => $cat_state ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_coupons_update_popular_category', 'revglue_coupons_update_popular_category' );
function revglue_coupons_update_category_icon()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$icon_url 	= esc_url_raw( $_POST['icon_url'] );
	$wpdb->update( 
		$categories_table, 
		array( 'icon_url' => $icon_url ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_coupons_update_category_icon', 'revglue_coupons_update_category_icon' );
function revglue_coupons_delete_category_icon()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$wpdb->update( 
		$categories_table, 
		array( 'icon_url' => '' ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_coupons_delete_category_icon', 'revglue_coupons_delete_category_icon' );
function revglue_coupons_update_category_image()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$image_url 	= esc_url_raw( $_POST['image_url'] );
	$wpdb->update( 
		$categories_table, 
		array( 'image_url' => $image_url ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_coupons_update_category_image', 'revglue_coupons_update_category_image' );
function revglue_coupons_delete_category_image()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$wpdb->update( 
		$categories_table, 
		array( 'image_url' => '' ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_coupons_delete_category_image', 'revglue_coupons_delete_category_image' );
function revglue_coupons_load_stores()
{
	global $wpdb; 
	$sTable = $wpdb->prefix.'rg_stores';
	$counpons_table = $wpdb->prefix.'rg_coupons';
	$aColumns = array( 
		'rg_store_id',
		'affiliate_network',
		'mid', 
		'image_url',
		'title',
		'store_base_country',
		'affiliate_network_link', 
		'homepage_store_tag' 
		 ); 
	$sIndexColumn = "rg_store_id"; 
	$sLimit = "LIMIT 1, 50";
	if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length'])  != '-1' )
	{
		$sLimit = "LIMIT ".intval( sanitize_text_field($_REQUEST['start']) ).", ".intval( sanitize_text_field($_REQUEST['length'])  );
	}

	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value']) != '' ) {
		$str = sanitize_text_field($_REQUEST['search']['value']);
		// remove last column from the requested columns to implement coupons count



		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}



		for ( $i=0, $ien=count( array_slice($request_columns, 0, 8) ) ; $i<$ien ; $i++ ) 
		{
			$requestColumn = sanitize_text_field($request_columns[$i]) ;

			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}


	/*	for ( $i=0, $ien=count(array_slice($_REQUEST['columns'], 0, 8)) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/


	}
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {

		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}

			for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]);
			//$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']) ;
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}

		
		/*for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			$str = $requestColumn['search']['value'];
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	}
	$sQuery = "SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."` FROM   $sTable $where $sOrder $sLimit";
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	$sQuery = "SELECT FOUND_ROWS()";
	$rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM   $sTable";
	$rResultTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		"draw"            => isset ( $_REQUEST['draw'] ) ? intval( sanitize_text_field($_REQUEST['draw'])  ) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	//$aColumns = array_slice($aColumns, 0, 9, true) + array("9" => "countofCoupons");
	// + array_slice($aColumns, 8, count($aColumns) - 1, true) ; 
	 /*print_r($aColumns);
	die(); */ 
	foreach($rResult as $aRow)
	{
		$row = array();
		for ( $i=0 ; $i<9 ; $i++ )
		{
			if( $i == 0 )
			{
				$row[] = $aRow[ $aColumns[$i] ];
			} else if( $i == 1 )
			{
				$row[] = $aRow[ $aColumns[$i] ];
			} else if( $i == 2 )
			{
				$row[] = $aRow[ $aColumns[$i] ];
			} else if( $i == 3 )
			{
				$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil" src="' . RGCOUPON_PLUGIN_URL . '/admin/images/loading.gif" data-src="' . esc_url( $aRow[ $aColumns[$i] ] ) . '" /></div>';
			} else if( $i == 4 )
			{
				$row[] = $aRow[ $aColumns[$i] ];
			} else if( $i == 5 )
			{
				$row[] = $aRow[ $aColumns[$i] ];
			} else if( $i == 6 )
			{
				$row[] = '<a class="rg_store_link_pop_up" id="'. esc_html( $aRow[ $aColumns[0] ] )  .'"  href="'. str_replace(array("subid-value","{}"), "",  esc_url( $aRow[ $aColumns[$i] ] )).'" target="_blank"><img src="'. RGCOUPON_PLUGIN_URL .'/admin/images/linkicon.png" style="width:50px;"/></a>';
			} else if( $i == 7 )
			{
				if( $aRow[ $aColumns[$i] ] == 'yes' )
				{
					$checked = 'checked="checked"';
				} else
				{
					$checked = '';
				}
				$row[] = '<input '.$checked.' type="checkbox" id="'.$aRow[ $aColumns[0] ].'" class="rg_store_homepage_tag" />';
			} else if( $i == 8 )
			{
				$storeid = $aRow[ $aColumns[0] ];
				$row[] = revglue_coupons_get_offers_and_coupons_count($storeid);
			} 
		}
		$output['data'][] = $row;
	}
	echo json_encode( $output );
	die(); 
}
add_action( 'wp_ajax_revglue_coupons_load_stores', 'revglue_coupons_load_stores' );
function revglue_coupons_load_banners()
{
	global $wpdb; 
	$stores_table = $wpdb->prefix.'rg_stores';
	$sTable = $wpdb->prefix.'rg_banner';
	$upload = wp_upload_dir();
	$base_url = $upload['baseurl'];
	$uploadurl = $base_url.'/revglue/coupons/banners/';
	$placements = array(
		'home-top'				=> esc_html('Home:: Top Header'),
		'home-slider'			=> esc_html('Home:: Main Banners'),
		'home-mid'				=> esc_html('Home:: After Categories'),
		'home-bottom'			=> esc_html('Home:: Before Footer'),
		'cat-top'				=> esc_html('Category:: Top Header'),
		'cat-side-top'			=> esc_html('Category:: Top Sidebar'),
		'cat-side-bottom'		=> esc_html('Category:: Bottom Sidebar 1'),
		'cat-side-bottom-two'	=> esc_html('Category:: Bottom Sidebar 2'),
		'cat-bottom'			=> esc_html('Category:: Before Footer'),
		'store-top'				=> esc_html('Store:: Top Header'),
		'store-side-top'		=> esc_html('Store:: Top Sidebar'),
		'store-side-bottom'		=> esc_html('Store:: Bottom Sidebar 1'),
		'store-side-bottom-two'	=> esc_html('Store:: Bottom Sidebar 2'),
		'store-main-bottom'		=> esc_html('Store:: After Review'),
		'store-bottom'			=> esc_html('Store:: Before Footer'),
		'unassigned' 			=> esc_html('Unassigned Banners')
	);
	$aColumns = array( 'banner_type', 'placement', 'status', 'title', 'url', 'image_url', 'rg_store_id', 'rg_id', 'rg_store_banner_id', 'rg_store_name', 'rg_size'  ); 
	$sIndexColumn = "rg_store_id"; 
	$sLimit = "LIMIT 1, 50"; 
	if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length'])  != '-1' )
	{
		$sLimit = "LIMIT ".intval( sanitize_text_field($_REQUEST['start']) ).", ".intval( sanitize_text_field($_REQUEST['length'])  );
	}
	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value'])  != '' ) {
		$str = sanitize_text_field($_REQUEST['search']['value']) ;



		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}

		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;

			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}



	/*	for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/







	}
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {


		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}

			for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			//$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']) ;
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}



	/*	for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			//$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = $dtColumns[ $requestColumn['data'] ];
			$str = $requestColumn['search']['value'];
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/






	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	}
	$sQuery = "SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."` FROM   $sTable $where $sOrder $sLimit";
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	$sQuery = "SELECT FOUND_ROWS()";
	$rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM   $sTable";
	$rResultTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		
		"draw"            => isset ( $_REQUEST['draw'] ) ? intval(sanitize_text_field($_REQUEST['draw'])) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	foreach($rResult as $aRow)
	{
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if( $i == 0 )
			{
				if( $aRow[ $aColumns[5] ] == '' )
				{
					$uploadedbanner = $uploadurl . $aRow[ $aColumns[3] ];
					$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil" src="'. RGSTORE__PLUGIN_URL .'/admin/images/loading.gif" data-src="'. esc_url( $uploadedbanner ) .'"/></div>';
				} else
				{
					$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil" src="'. RGSTORE__PLUGIN_URL .'/admin/images/loading.gif" data-src="'. esc_url( $aRow[ $aColumns[5] ] ) .'" /></div>';
				}
			}else if( $i == 1 )
			{
				$row[] = $aRow[ $aColumns[8] ];
			} else if( $i == 2 )
			{
				$row[] = $aRow[ $aColumns[3] ];
			} else if( $i == 3 )
			{
				$row[] = ( $aRow[ $aColumns[0] ] == 'local' ? 'Local' : 'RevGlue Banner' );
			} else if( $i == 4 )
			{
				$row[] = $placements[$aRow[ $aColumns[1]]];
			} else if( $i == 5 )
			{
				$row[] = $aRow[ $aColumns[10]];
			} else if( $i == 6 )
			{
				if( ! empty( $aRow[ $aColumns[4]] ) )
				{
					$url_to_show = esc_url( $aRow[ $aColumns[4]] ); 
				} else if( ! empty( $aRow[ $aColumns[6]] ) )
				{
					$sql_1 = "SELECT affiliate_network_link FROM $stores_table where rg_store_id = ".$aRow[ $aColumns[6]];
					$deep_link = $wpdb->get_results($sql_1);
					$url_to_show = ( !empty( $deep_link[0]->affiliate_network_link ) ?
					 esc_url(  $deep_link[0]->affiliate_network_link ) : 'No Link'  );
				} else
				{
					$url_to_show = 'No Link';
				}
				$row[] = '<a class="rg_store_link_pop_up" id="'. $aRow[ $aColumns[7]] .'" title="'. str_replace("subid-value", "",$url_to_show) .'"  href="'. str_replace("subid-value", "",$url_to_show) .'" target="_blank"><img src="'. RGCOUPON_PLUGIN_URL .'/admin/images/linkicon.png" style="width:50px;"/></a>';
			} else if( $i == 7 )
			{
				$row[] = $aRow[ $aColumns[2]];
			} else if( $i == 8 )
			{
				$row[] = '<a href="'. admin_url( 'admin.php?page=revglue-banners&action=edit&banner_id='.$aRow[ $aColumns[7]] ) .'">Edit</a>';
			} else if ( $aColumns[$i] != ' ' )
			{    
				$row[] = $aRow[ $aColumns[$i] ];
			}
		}
		$output['data'][] = $row;
	}
	echo json_encode( $output );
	die(); 
}
add_action( 'wp_ajax_revglue_coupons_load_banners', 'revglue_coupons_load_banners' );
function revglue_coupons_load_coupons()
{
	global $wpdb; 
	$stores_table = $wpdb->prefix.'rg_stores';
	$categories_table = $wpdb->prefix.'rg_categories';
	$sTable = $wpdb->prefix.'rg_coupons';
	$aColumns = array( 'title',  'rg_store_id', 'coupon_type', 'coupon_code', 'deeplink', 'category_ids', 'issue_date', 'expiry_date', 'status', 'rg_id' ); 
	$sIndexColumn = "rg_id"; 
	$sLimit = "LIMIT 1, 50";
	if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length']) != '-1' )
	
	{
		$sLimit = "LIMIT ".intval(sanitize_text_field($_REQUEST['start'])).", ".intval(sanitize_text_field($_REQUEST['length']));
	}
	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value']) != '' )
		 {
		 	$str = sanitize_text_field($_REQUEST['search']['value']);

		 	$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}

		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}


/*
		for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/





	}
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {

		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}
		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']) ;
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}




/*
		for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			$str = $requestColumn['search']['value'];
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/





	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	}
	$sQuery = "SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."` FROM   $sTable $where $sOrder $sLimit";
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	$sQuery = "SELECT FOUND_ROWS()";
	$rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM   $sTable";
	$rResultTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		"draw"            => isset ($_REQUEST['draw']) ? intval( sanitize_text_field($_REQUEST['draw']) ) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	foreach($rResult as $aRow)
	{
		// print_r($aRow);
		// die();
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if( $i == 0 )
			{
				$sqlst = "SELECT `title` FROM $stores_table WHERE `rg_store_id` = '".$aRow[ $aColumns[1] ]."'";				
				$st_tt = $wpdb->get_var($sqlst);
				$row[] = $st_tt;
			} else if( $i == 4 )
			{
				$row[] = '<a class="rg_store_link_pop_up" id="'. $aRow[ $aColumns[9] ] .'"  title="'. esc_url( str_replace("subid-value", "",  $aRow[ $aColumns[$i] ]  ) ) .'"  href="'. esc_url( str_replace("subid-value", "",  $aRow[ $aColumns[$i] ]  ) ) .'" target="_blank"><img src="'. RGCOUPON_PLUGIN_URL .'/admin/images/linkicon.png" style="width:50px;"/></a>';
			} else if( $i == 5 )
			{
				$catID = esc_html($aRow[ $aColumns[$i] ]) ;
				$pieces = explode(",", $catID);
				$impname = array();
				foreach($pieces as $value){
					if ( !empty($value) )
					{
						$sqlcat = "SELECT *FROM $categories_table WHERE rg_category_id = $value";
						$catrows = $wpdb->get_results($sqlcat);
						foreach($catrows as $storecatnames)
						{
							$impname[] = $storecatnames->title;
						}
					}
				}
				 $row[] = ( $impname[0] ? $impname[0]  : 'No Category Available' );
			} else if( $i == 6  )
			{
				$issue_date = esc_html($aRow[ $aColumns[6] ]) ;
				$issue_date =  date("d-M-Y" , strtotime($issue_date) );
				 $row[] =  $issue_date;
			} else if( $i == 9 )
			{
				$row[] = '<a href="'. admin_url( 'admin.php?page=revglue-coupons&action=edit&coupon_id='.$aRow[ $aColumns[$i] ] ) .'">Edit</a>';
			}
			else if( $i == 7  )
			{
				$expiry = esc_html($aRow[ $aColumns[7] ])   ;
				$expiry_date =  date("d-M-Y", strtotime($expiry) );
				$row[] =  $expiry_date;
			} 
			else if( $i == 8  )
			{
				$date =  esc_html($aRow[ $aColumns[$i] ]) ;
				$row[] =  $date;
			} 
			else if ( $aColumns[$i] != ' ' )
			{    
				$row[] = esc_html($aRow[ $aColumns[$i] ]) ;
			}
		}
		$output['data'][] = $row;
	}
	echo json_encode( $output );
	die(); 
}
add_action( 'wp_ajax_revglue_coupons_load_coupons', 'revglue_coupons_load_coupons' );
function revglue_coupons_get_offers_and_coupons_count($id){
      $offer_count = rc_offer_count($id);
      $code_count = rc_coupons_count($id);
      $code_count = isset($code_count) && $code_count >= 1 ?$code_count :"0 coupon";
      $offer_count = isset($offer_count) && $offer_count >= 1 ?$offer_count :"0 Offer";
      $strcoupon = "";
      $andsign ="";
      $andsign = $offer_count >=1 && $code_count >=1? " & ":"";
      if ( $code_count > 0  && $code_count < 2  ) {
        $strcoupon .= "Coupon";
      } elseif ( $code_count > 1){
        $strcoupon .= "Coupons";
      }
      $stroffer = "";
      if ( $offer_count > 0  && $offer_count < 2  ) {
        $stroffer .= "Offer";
      } elseif ( $offer_count > 1){
        $stroffer .= "Offers";
      } 
    return  $code_count." ". $strcoupon.$andsign." ".$offer_count ." ". $stroffer;
  }
function revglue_coupons_fetch_project_id(){
	global $wpdb;
	$project_table	= $wpdb->prefix.'rg_projects';
	$sql = "SELECT `partner_iframe_id` FROM $project_table WHERE `PROJECT` LIKE 'Coupons'  AND status = 'active' AND `expiry_date`='Free'";
	$projectid = $wpdb->get_var( $sql );  
	return $projectid;
}
function revglue_coupons_fetch_subscription_id(){
	global $wpdb;
	$project_table	= $wpdb->prefix.'rg_projects';
	$sql = "SELECT `subcription_id` FROM $project_table WHERE `PROJECT` LIKE 'Coupons'  AND status = 'active' AND `expiry_date`='Free'";
	$subcriptionid = $wpdb->get_var( $sql );  
	return $subcriptionid;
}
function revglue_coupons_update_subscription_expiry_date($purchasekey, $userpassword, $useremail, $projectid){
	global $wpdb; 
	$projects_table = $wpdb->prefix.'rg_projects';
	$apiurl = RGCOUPON_API_URL . "api/validate_subscription_key/$useremail/$userpassword/$purchasekey";
	$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $apiurl , array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
	$expiry_date = $resp_from_server['response']['result']['expiry_date'];
	if ( empty($projectid)){
		$sql ="UPDATE $projects_table SET `expiry_date` = $expiry_date WHERE `subcription_id` ='$purchasekey'";
		$wpdb->query($sql);
	} 
}
?>