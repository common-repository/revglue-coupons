function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};
jQuery( document ).ready(function() {
	jQuery(".chosen-select").chosen()
	// Adds lazy load to images
	jQuery("img.revglue-unveil").unveil();
	// Initialize datepicker
	jQuery( ".coupon_datepicker" ).datepicker();
	// Initialize Coupons Datatable
    jQuery('#coupons_admin_screen').DataTable({
		"processing": true,
        "serverSide": true,
        "ajax": ajaxurl+'?action=revglue_coupons_load_coupons',
		"pageLength": 50
	});
	// Initialize Categories Datatable
    jQuery('#categories_admin_screen').DataTable({
		"bPaginate": false
	});
	// Initialize Stores Datatable
    jQuery('#stores_admin_screen').DataTable({
		"processing": true,
        "serverSide": true,
        "ajax": ajaxurl+'?action=revglue_coupons_load_stores',
		"pageLength": 50,
		"order": [[ 4, 'asc' ]],
		"drawCallback": function( settings ) {
            jQuery("#stores_admin_screen img:visible").unveil();
			jQuery('.rg_store_homepage_tag').iphoneStyle();
			jQuery('.rg_store_popular_tag').iphoneStyle();
        }
	});
	// Initialize Banners Datatable
    jQuery('#banners_admin_screen').DataTable({
		"processing": true,
        "serverSide": true,
        "ajax": ajaxurl+'?action=revglue_coupons_load_banners',
		"pageLength": 50,
		"drawCallback": function( settings ) {
            jQuery("#banners_admin_screen img:visible").unveil();
        }
	});
	jQuery( "#rg_coupon_sub_activate" ).on( "click", function() {
		var sub_id 		= jQuery( "#rg_coupon_sub_id" ).val();
		var sub_email 	= jQuery( "#rg_coupon_sub_email" ).val();
		var sub_pass 	= jQuery( "#rg_coupon_sub_password" ).val();
		if( sub_id == "" )
		{
			jQuery('#subscription_error').text("Please First enter your unique Subscription ID");	
			return false;
		}
		if( sub_email == "" )
		{
			jQuery('#subscription_error').text( "Please First enter your Email" );	
			return false;
		}
		if( sub_pass == "" )
		{
			jQuery('#subscription_error').text("Please First enter your Password");	
			return false;
		}
		var subscription_data = {
			'action'	: 'revglue_coupons_subscription_validate',
			'sub_id'	: sub_id,
			'sub_email'	: sub_email,
			'sub_pass'	: sub_pass
		};
		jQuery('#subscription_error').html("");
		jQuery('#subscription_response').html("");
		jQuery("#sub_loader").show();
		jQuery.post(
			ajaxurl,
			subscription_data,
			function( response )
			{		
				jQuery("#rg_coupon_sub_id").val("");
				jQuery('#sub_loader').hide();
				jQuery('.subscriptiondone').hide();
				jQuery('.dataisnotimported').show();
				jQuery('#subscription_response').html(response);
			}
		);
		return false;
	});
	jQuery( "#rg_coupon_import" ).on( "click", function(e) {
		e.preventDefault();
		type = jQuery( this ).attr( 'href' );
		console.log(type);
		console.log("Developed by Imran Javed Twitter Handle @MrImranJaved");
		var import_data = {
			'action': 'revglue_coupons_data_import',
			'import_type': type
		};
		jQuery("#subscription_error").html("");
		jQuery(".sub_page_table").hide();
		jQuery('#store_loader').show();
		jQuery.post(
			ajaxurl, 
			import_data, 
			function(response) 
			{
				console.log("Developed by Imran Javed Twitter Handle @MrImranJaved");
				console.log("**********************RESPONSE****************************");
				console.log(response);
				jQuery('#store_loader').hide();
				jQuery(".sub_page_table").show();
				jQuery('#rg_coupons_import_popup').hide();
				var response_object = JSON.parse(response);
				jQuery(".sub_page_table").prepend(response_object.error_msgs);
				jQuery('#rg_coupon_count').text(response_object.count_coupon);	
				jQuery('#rg_category_count').text(response_object.count_category);	
				jQuery('#rg_store_count').text(response_object.count_store);	
				jQuery('#rg_coupon_date').text(response_object.last_updated_coupon);	
				jQuery('#rg_category_date').text(response_object.last_updated_category);
				jQuery('#rg_store_date').text(response_object.last_updated_store);
			}
		);
		return false;
	});
	jQuery( "#rg_banner_import" ).on( "click", function(e) {
		e.preventDefault();
		type = jQuery( this ).attr( 'href' );
		var import_data = {
			'action': 'revglue_banner_data_import',
			'import_type': type
		};
		jQuery("#subscription_error").html("");
		jQuery(".sub_page_table").hide();
		jQuery('#store_loader').show();
		jQuery.post(
			ajaxurl, 
			import_data, 
			function(response) 
			{
				jQuery('#store_loader').hide();
				jQuery(".sub_page_table").show();
				jQuery('#rg_coupons_import_popup').hide();
				var response_object = JSON.parse(response);
				jQuery(".sub_page_table").prepend(response_object.error_msgs);
				jQuery('#rg_banner_count').text(response_object.count_banner);
			}
		);
		return false;
	});
	jQuery( "#rg_coupon_delete" ).on( "click", function(e) {
		e.preventDefault();
		type = jQuery( this ).attr( 'href' );
		var delete_data = {
			'action': 'revglue_coupons_data_delete',
			'data_type': type
		};
		jQuery("#subscription_error").html("");
		jQuery(".sub_page_table").hide();
		jQuery('#store_loader').show();
		jQuery.post(
			ajaxurl, 
			delete_data, 
			function(response) 
			{
				console.log(response);
				jQuery('#store_loader').hide();
				jQuery(".sub_page_table").show();
				jQuery('#rg_coupons_delete_popup').hide();
				var response_object = JSON.parse(response);
				if( response_object.data_type == 'rg_coupons' )
				{
					jQuery('#rg_coupon_count').text(response_object.count_coupon);	
					jQuery('#rg_coupon_date').text(response_object.last_updated_coupon);
				} else if( response_object.data_type == 'rg_categories' )
				{
					jQuery('#rg_category_count').text(response_object.count_category);		
					jQuery('#rg_category_date').text(response_object.last_updated_category);
				} else if( response_object.data_type == 'rg_stores' )
				{
					jQuery('#rg_store_count').text(response_object.count_store);	
					jQuery('#rg_store_date').text(response_object.last_updated_store);
				} else if( response_object.data_type == 'rg_banners' )
				{
					jQuery('#rg_banner_count').text(response_object.count_banner);
				}
			}
		);
		return false;
	});
	jQuery('.rg-admin-container').on('mouseenter', '.rg_store_link_pop_up', function( event ) {
		var id = this.id;
		jQuery('#imp_popup'+id).show();
	}).on('mouseleave', '.rg_store_link_pop_up', function( event ) {
		var id = this.id;
		jQuery('#imp_popup'+id).hide();
	});
	jQuery('.rg_store_homepage_tag').iphoneStyle();
	jQuery( "#stores_admin_screen" ).on( "change",  ".rg_store_homepage_tag", function(e) {
		if( jQuery( this ).prop( 'checked' ) )
		{
		   var tag_checked = 'yes';
		} else
		{
		   var tag_checked = 'no';
		}	
		var store_tag_data = {
			'action': 'revglue_coupons_update_home_store',
			'store_id': this.id,
			'state' : tag_checked
		};
		jQuery.post(
			ajaxurl, 
			store_tag_data, 
			function(response) 
			{
			}
		);
	});
	jQuery('.rg_store_popular_tag').iphoneStyle();
	jQuery( "#stores_admin_screen" ).on( "change",  ".rg_store_popular_tag", function(e) {
		if( jQuery( this ).prop( 'checked' ) )
		{
		   var tag_checked = 'yes';
		} else
		{
		   var tag_checked = 'no';
		}	
		var store_tag_data = {
			'action': 'revglue_coupons_update_popular_store',
			'store_id': this.id,
			'state' : tag_checked
		};
		jQuery.post(
			ajaxurl, 
			store_tag_data, 
			function(response) 
			{
			}
		);
	});
	jQuery('.rg_store_cat_tag_head').iphoneStyle();
	jQuery( ".rg_store_cat_tag_head" ).on( "change", function(e) {
		if( jQuery( this ).prop( 'checked' ) )
		{
		   var tag_checked = 'yes';
		} else
		{
		   var tag_checked = 'no';
		}	
		var cat_tag_data = {
			'action': 'revglue_coupons_update_header_category',
			'cat_id': this.id,
			'state' : tag_checked
		};
		jQuery.post(
			ajaxurl, 
			cat_tag_data, 
			function(response) 
			{
			}
		);
	});
	jQuery('.rg_store_cat_tag').iphoneStyle();
	jQuery( ".rg_store_cat_tag" ).on( "change", function(e) {
		if( jQuery( this ).prop( 'checked' ) )
		{
		   var tag_checked = 'yes';
		} else
		{
		   var tag_checked = 'no';
		}	
		var cat_tag_data = {
			'action': 'revglue_coupons_update_popular_category',
			'cat_id': this.id,
			'state' : tag_checked
		};
		jQuery.post(
			ajaxurl, 
			cat_tag_data, 
			function(response) 
			{
			}
		);
	});
	jQuery( ".rg_coupons_open_import_popup" ).on( "click", function(e) {
		e.preventDefault();
		var type = jQuery( this ).attr( "href" );
		jQuery('#rg_coupons_delete_popup').hide();	
		jQuery('#rg_coupons_import_popup').show();
		jQuery('.rg_coupons_start_import').attr( "href", type );
	});
	jQuery( ".rg_coupons_open_delete_popup" ).on( "click", function(e) {
		e.preventDefault();
		var type = jQuery( this ).attr( "href" );
		jQuery('#rg_coupons_import_popup').hide();
		jQuery('#rg_coupons_delete_popup').show();	
		jQuery('.rg_coupons_start_delete').attr( "href", type );
	});
	jQuery('#rg_banner_image_type').on( "change", function(e) {
		var type = jQuery( this ).val();
		if( type == 'url' )
		{
			jQuery('#rg_banner_image_file').val('');
			jQuery('#rg_coupons_banner_image_upload').hide();
			jQuery('#rg_coupons_banner_image_url').show();
		} else
		{
			jQuery('#rg_banner_image_url').val('');
			jQuery('#rg_coupons_banner_image_url').hide();
			jQuery('#rg_coupons_banner_image_upload').show();
		}
	});
	// Set all variables to be used in scope
	var frame;
	// ADD ICON LINK
	jQuery( "#categories_admin_screen" ).on( "click", ".rg_add_category_icon", function( event ) {
		var the_cat_id = this.id
		event.preventDefault();
		/* // If the media frame already exists, reopen it.
		if ( frame ) 
		{
			frame.open();
			return;
		} */
		// Create a new media frame
		frame = wp.media({
			title: 'Select or Upload Media Of Your Chosen Persuasion',
			button: 
			{
				text: 'Use this media'
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});
		// When an image is selected in the media frame...
		frame.on( 'select', function() 
		{
			// Get media attachment details from the frame state
			var attachment = frame.state().get('selection').first().toJSON();
			var cat_img_data = {
				'action': 'revglue_coupons_update_category_icon',
				'cat_id': the_cat_id,
				'icon_url' : attachment.url
			};
			jQuery.post(
				ajaxurl, 
				cat_img_data, 
				function(response) 
				{
					jQuery( ".rg_store_icon_thumb_"+response ).html( 
					"<a id='"+response+"' class='rg_category_delete_icons' href='javascript;'>"+
					"<i class='fa fa-times' aria-hidden='true'></i></a>"+
					"<img alt='image' src='"+attachment.url+"'>" );
					jQuery( ".rg_add_category_icon_"+response ).text('Edit Icon');
				}
			);
		});
		// Finally, open the modal on click
		frame.open();
	});
	// DELETE ICON LINK
	jQuery( "#categories_admin_screen" ).on( "click",  ".rg_category_delete_icons", function( event ) {
		var the_cat_id = this.id
		event.preventDefault();
		jQuery.confirm({
			title: 'Category Icon',
			content: 'Are you sure you want to remove this icon ?',
			icon: 'fa fa-question-circle',
			animation: 'scale',
			closeAnimation: 'scale',
			opacity: 0.5,
			buttons: {
				'confirm': {
					text: 'Remove',
					btnClass: 'btn-blue',
					action: function () {
						var cat_img_data = {
							'action': 'revglue_coupons_delete_category_icon',
							'cat_id': the_cat_id,
						};
						jQuery.post(
							ajaxurl, 
							cat_img_data, 
							function(response) 
							{
								console.log(response);
								jQuery( ".rg_store_icon_thumb_"+response ).html( '' );
								jQuery( ".rg_add_category_icon_"+response ).text('Add Icon');
							}
						);
					}
				},
				cancel: function () {
				},
			}
		});	
	});
	// ADD IMAGE LINK
	jQuery( "#categories_admin_screen" ).on( "click",  ".rg_add_category_image", function( event ) {
		var the_cat_id = this.id
		event.preventDefault();
		/* // If the media frame already exists, reopen it.
		if ( frame ) 
		{
			frame.open();
			return;
		} */
		// Create a new media frame
		frame = wp.media({
			title: 'Select or Upload Media Of Your Chosen Persuasion',
			button: 
			{
				text: 'Use this media'
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});
		// When an image is selected in the media frame...
		frame.on( 'select', function() 
		{
			// Get media attachment details from the frame state
			var attachment = frame.state().get('selection').first().toJSON();
			var cat_img_data = {
				'action': 'revglue_coupons_update_category_image',
				'cat_id': the_cat_id,
				'image_url' : attachment.url
			};
			jQuery.post(
				ajaxurl, 
				cat_img_data, 
				function(response) 
				{
					jQuery( ".rg_store_image_thumb_"+response ).html( 
					"<a id='"+response+"' class='rg_category_delete_icons' href='javascript;'>"+
					"<i class='fa fa-times' aria-hidden='true'></i></a>"+
					"<img alt='image' src='"+attachment.url+"'>" );
					jQuery( ".rg_add_category_image_"+response ).text('Edit Image');
				}
			);
		});
		// Finally, open the modal on click
		frame.open();
	});
	// DELETE IMAGE LINK
	jQuery( "#categories_admin_screen" ).on( "click",  ".rg_category_delete_images", function( event ) {
		var the_cat_id = this.id
		event.preventDefault();
		jQuery.confirm({
			title: 'Category Image',
			content: 'Are you sure you want to remove this image ?',
			icon: 'fa fa-question-circle',
			animation: 'scale',
			closeAnimation: 'scale',
			opacity: 0.5,
			buttons: {
				'confirm': {
					text: 'Remove',
					btnClass: 'btn-blue',
					action: function () {
						var cat_img_data = {
							'action': 'revglue_coupons_delete_category_image',
							'cat_id': the_cat_id,
						};
						jQuery.post(
							ajaxurl, 
							cat_img_data, 
							function(response) 
							{
								console.log(response);
								jQuery( ".rg_store_image_thumb_"+response ).html( '' );
								jQuery( ".rg_add_category_image_"+response ).text('Add Image');
							}
						);
					}
				},
				cancel: function () {
				},
			}
		});	
	});
	jQuery('#rg_coupon_type').on( "change", function(e) {
		var type = jQuery( this ).val();
		if( type == 'code' )
		{
			jQuery('#rg_coupon_code_sec').show();
		} else
		{
			jQuery('#rg_coupon_code').val('');
			jQuery('#rg_coupon_code_sec').hide();
		}
	});
	jQuery('#rg_coupon_image_type').on( "change", function(e) {
		var type = jQuery( this ).val();
		if( type == 'url' )
		{
			jQuery('#rg_coupon_image_file').val('');
			jQuery('#rg_coupons_coupon_image_upload').hide();
			jQuery('#rg_coupons_coupon_image_url').show();
		} else
		{
			jQuery('#rg_coupon_image_url').val('');
			jQuery('#rg_coupons_coupon_image_url').hide();
			jQuery('#rg_coupons_coupon_image_upload').show();
		}
	});
});