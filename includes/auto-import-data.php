<?php 

// Exit if accessed directly

if ( !defined( 'ABSPATH' ) ) exit;



global $wpdb;

$banner_table = $wpdb->prefix.'rg_banner';

$coupons_table = $wpdb->prefix.'rg_coupons';	

$stores_table = $wpdb->prefix.'rg_stores';

$categories_table = $wpdb->prefix.'rg_categories';

$project_table = $wpdb->prefix.'rg_projects';

 

$sql = "SELECT *FROM $project_table where project like 'Coupons UK'";

$project_detail = $wpdb->get_results($sql);
$subscriptionid = $project_detail[0]->subcription_id;
$useremail = $project_detail[0]->email;
$userpassword = $project_detail[0]->password;
$projectid = $project_detail[0]->partner_iframe_id;

$rows = $wpdb->num_rows;



$qry_response = '';



if( !empty ( $rows ) )

{

	$jsonDataCoupons = json_decode( wp_remote_retrieve_body( wp_remote_get( RGCOUPON_API_URL . "api/vouchers/json/".$project_detail[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true);


	$resultCoupons = $jsonDataCoupons['response']['coupons'];



	if($jsonDataCoupons['response']['success'] == 'true' )

	{

		foreach($resultCoupons as $row)

		{	

			$sqlincat = "Select rg_coupon_id FROM $coupons_table Where rg_coupon_id = '".$row['coupons_id']."'";



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

		}

		echo 'You successfully imported the coupons<br>';

	} else 

	{


		$qry_response = '<p style="color:red">'.esc_html($jsonDataCoupons['response']['message']).'</p>';

	}



revglue_coupons_update_subscription_expiry_date($subscriptionid, $userpassword, $useremail, $projectid);

if($project_detail[0]->expiry_date=="Free" && $project_detail[0]->partner_iframe_id!="" ){
				$partner_coupons_url ="https://www.revglue.com/partner/coupon_categories/json";
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $partner_coupons_url, array( 'timeout' => 12000, 'sslverify'   => false ) ) ), true);
			}else{
				$jsonDataCategories = json_decode( wp_remote_retrieve_body( wp_remote_get( RGCOUPON_API_URL . "api/voucher_categories/json/".$project_detail[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true);


			}




	

	 

	$resultCategories = $jsonDataCategories['response']['categories'];



	if($jsonDataCategories['response']['success'] == 'true' )

	{

		foreach($resultCategories as $row)

		{	

			$sqlincat = "Select rg_category_id FROM $categories_table Where rg_category_id = '".$row['coupon_category_id']."'";

			$rg_category_exists = $wpdb->get_var( $sqlincat );
			$issue_date  =		 date( "Y-m-d", strtotime($row['issue_date']));
					$expiry_date  =		 date( "Y-m-d", strtotime($row['expiry_date']));

			if( empty( $rg_category_exists ) )

			{					

				$title 		= $row['title'];

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

					$coupons_table, 

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

		echo 'You successfully imported the categories<br>';

	} else 

	{

		$qry_response = '<p style="color:red">'.esc_html($jsonDataCategories['response']['message']).'</p>';

	}

	revglue_coupons_update_subscription_expiry_date($subscriptionid, $userpassword, $useremail, $projectid);

			if($project_detail[0]->expiry_date=="Free" && $project_detail[0]->partner_iframe_id!="" ){
				$partner_coupons_url ="https://www.revglue.com/partner/coupon_stores/".$project_detail[0]->partner_iframe_id."/json/wp";
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $partner_coupons_url, array( 'timeout' => 12000, 'sslverify'   => false ) ) ), true);
			}else{
				$jsonData = json_decode( wp_remote_retrieve_body( wp_remote_get( RGCOUPON_API_URL . "api/voucher_stores/json/".$project_detail[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
			}

	

	$result = $jsonData['response']['stores'];

	

	if($jsonData['response']['success'] == 'true' )

	{

		foreach($result as $row)

		{

			$sqlinstore = "Select rg_store_id FROM $stores_table Where rg_store_id = '".$row['rg_store_id']."'";

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
								'date' 			    		=> date('Y-m-d H:i:s'),
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
								'date' 			    		=> date('Y-m-d H:i:s'),
								'category_ids' 				=> $row['category_ids']

					),

					array( 'rg_store_id' => $rg_store_exists )

				);

			}										

		}

		echo 'You successfully imported the stores<br>';

	} else 

	{

		$qry_response = '<p style="color:red">'.esc_html($jsonData['response']['message']).'</p>';

	}

}



$sql = "SELECT *FROM $project_table where project like 'Banners UK'";

$project_detail = $wpdb->get_results($sql);

$rows = $wpdb->num_rows;

    

if( !empty ( $rows ) )

{

	$banner_table = $wpdb->prefix.'rg_banner';

	$jsonDataBanners = json_decode( wp_remote_retrieve_body( wp_remote_get( RGCOUPON_API_URL . "api/banners/json/".$project_detail[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 

	$result = $jsonDataBanners['response']['banners'];

	

	if($jsonDataBanners['response']['success'] == 'true' )

	{

		foreach($result as $row)

		{

			$sqlinstore = "Select rg_store_banner_id FROM $banner_table Where rg_store_banner_id = '".$row['rg_banner_id']."' AND `banner_type` = 'imported'";

			$rg_banner_exists = $wpdb->get_var( $sqlinstore );

			if( empty( $rg_banner_exists ) )

			{

				$wpdb->insert( 

					$banner_table, 

					array( 

						'rg_store_banner_id' 	=> $row['rg_banner_id'], 

						'rg_store_id' 			=> $row['rg_store_id'], 

						'rg_store_name' 		=> $row['banner_alt_text'], 

						'title' 				=> $row['banner_alt_text'], 

						'image_url' 			=> str_replace("http", "https", $row['banner_image_url']), 

						'rg_size' 				=> $row['width_pixels'].'x'.$row['height_pixels'], 

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

						'rg_store_name' 		=> $row['banner_alt_text'], 

						'title' 				=> $row['banner_alt_text'], 

						'image_url' 			=> str_replace("http", "https", $row['banner_image_url']), 

						'rg_size' 				=> $row['width_pixels'].'x'.$row['height_pixels']	

					),

					array( 'rg_store_banner_id' => $rg_banner_exists )

				);

			}											

		}

		echo 'You successfully imported the banners<br>';

	} else 

	{


		$qry_response = '<p style="color:red">'.esc_html($jsonDataBanners['response']['message']).'</p>';

	}

	echo $qry_response;

}			

exit();

?>