<?php if ( ! defined( 'ABSPATH' ) ) exit;

	if(!function_exists('wcod_pre')){
	function wcod_pre($data){
			if(isset($_GET['debug'])){
				wcod_pree($data);
			}
		}
	}
	if(!function_exists('wcod_pree')){
	function wcod_pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';

		}
	}
	function sanitize_wcod_data( $input ) {
		if(is_array($input)){		
			$new_input = array();	
			foreach ( $input as $key => $val ) {
				$new_input[ $key ] = (is_array($val)?sanitize_wcod_data($val):sanitize_text_field( $val ));
			}			
		}else{
			$new_input = sanitize_text_field($input);			
			if(stripos($new_input, '@') && is_email($new_input)){
				$new_input = sanitize_email($new_input);
			}
			if(stripos($new_input, 'http') || wp_http_validate_url($new_input)){
				$new_input = sanitize_url($new_input);
			}			
		}	
		return $new_input;
	}	
	
	function wcod_get_private_order_Tracking( $order_id){
		global $wpdb;

		$table_perfixed = $wpdb->prefix . 'comments';
		$wcod_tracking_query = "
			SELECT 	comment_id, comment_date, comment_content,
					CASE 
						WHEN LOCATE('via USPS', comment_content) > 0 THEN 'USPS'
                        WHEN LOCATE('via UPS', comment_content) > 0 THEN 'UPS'
                        WHEN LOCATE('via FedEx', comment_content) > 0 THEN 'FEDEX'
						ELSE 'UPS'
					END AS comment_carrier,
					REPLACE(REPLACE(SUBSTRING_INDEX(comment_content,'tracking number ',-1),  ' (Shipstation)', ''), '.', '') AS comment_tracking
			FROM 	$table_perfixed
			WHERE  	`comment_post_ID` = $order_id
			AND  	`comment_type` LIKE  'order_note'
			AND 	`comment_content`  LIKE '%tracking%'
		";
		//pree($wcod_tracking_query);
		$results = $wpdb->get_results($wcod_tracking_query);

		$order_tracking = array();

		foreach($results as $tracking){
			//wcod_pre($tracking);
			if(!empty($tracking) && isset($tracking->comment_id)){
				$order_tracking[]  = array(
					'note_id'      => $tracking->comment_id,
					'note_date'    => $tracking->comment_date,
					'note_content' => $tracking->comment_content,
					'note_carrier' => $tracking->comment_carrier,
					'note_tracking' => $tracking->comment_tracking,
				);
			}
		}
		return $order_tracking;
	}
	function wcod_get_private_order_notes( $order_id){
		global $wpdb;

		$table_perfixed = $wpdb->prefix . 'comments';
		$wcod_notes_query = "
			SELECT *
			FROM $table_perfixed
			WHERE  `comment_post_ID` = $order_id
			AND  `comment_type` LIKE  'order_note'
		";
		//pree($wcod_notes_query);
		$results = $wpdb->get_results($wcod_notes_query);

		$order_note = array();

		foreach($results as $note){
			if(!empty($note)){
				//wcod_pre($note);
				$order_note[]  = array(
					'note_id'      => $note->comment_ID,
					'note_date'    => $note->comment_date,
					'note_author'  => $note->comment_author,
					'note_content' => $note->comment_content,
				);
			}
		}
		return $order_note;
	}
	function wcod_admin_menu()
	{
		global $wcod_data;
		$title = str_replace('WooCommerce', 'WC', $wcod_data['Name']);
		add_submenu_page('woocommerce', $title, 'Order Details', 'manage_woocommerce', 'wcod_settings', 'wcod_settings' );
	}
	function wcod_settings(){
		if ( !current_user_can( 'administrator' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'wc-order-details' ) );
		}
		global $wpdb;
		include('wcod_settings.php');
	}
	//add_filter( 'woocommerce_admin_order_actions', 'wcod_order_status_actions_button', 100, 2 );
	function wcod_order_status_actions_button( $actions, $order ) {


		$actions['order_details'] = array(
			'url'       => get_admin_url().'admin.php?page=wcod_&order_id='.$order->ID,
			'name'      => __( 'Select Order to View Details' , 'wc-order-details'),
			'action'    => (count($order->get_items())>1)?'wcod_btn':'wcod_btn_done',
		);

		return $actions;

	}
	add_action( 'wp_enqueue_scripts', 'wcod_enqueue_scripts' );
	add_action( 'admin_enqueue_scripts', 'wcod_enqueue_scripts' );

	function wcod_enqueue_scripts()
	{


		if(is_admin()){
			if(isset($_GET['page']) && in_array($_GET['page'], array('wcod_settings'))){
				
				wp_enqueue_script('wcod-boostrap-script', plugins_url('js/bootstrap.min.js', dirname(__FILE__)), array( 'jquery' ), date('Ymd'), true );
				wp_enqueue_style('wcod-boostrap-style', plugins_url('css/bootstrap.min.css', dirname(__FILE__)));
				wp_enqueue_script('wcod-fontawesome-script', plugins_url('js/fontawesome.min.js', dirname(__FILE__)), array( 'jquery' ), date('Ymd'), true );
				wp_enqueue_style('wcod-fontawesome-style', plugins_url('css/fontawesome.min.css', dirname(__FILE__)));
				
				wp_enqueue_script('wcod-scripts', plugins_url('js/admin-scripts.js?t='.time(), dirname(__FILE__)), array( 'jquery' ), date('Ymd'), true );
			}
			if(
					((isset($_GET['page']) && in_array($_GET['page'], array('wcod_settings')))
				||
				(isset($_GET['post_type']) && in_array($_GET['post_type'], array('shop_order'))))
			){
				wp_enqueue_style('wcod-style', plugins_url('css/admin-style.css?t='.time(), dirname(__FILE__)));
				
				wp_localize_script( 'wcod-scripts', 'wcod_obj',
	
						array(
								'ajax_url' => admin_url( 'admin-ajax.php' ),
								'ajax_nonce' => wp_create_nonce('wcod-ajax-safety'),
								'wcod_tab' => (isset($_GET['t'])?sanitize_wcod_data($_GET['t']):'0'),
								'this_url' => admin_url( 'admin.php?page=wcod_settings' ),
								
						)
				);
			}
		}else{
			global $post;
			
			if(is_object($post) && !empty($post)){
			
				$wcod_ss = get_option('wcod_styles_scripts', array());
				$wcod_ss = (is_array($wcod_ss)?$wcod_ss:array());
				//wcod_pre($post->ID);
				$wcod_styles_scripts = ((!empty($wcod_ss) && in_array($post->ID, $wcod_ss)) || get_page_template_slug()=='wcod-template.php');
	
				if($wcod_styles_scripts){
					wp_enqueue_script('wcod-boostrap-script', plugins_url('js/bootstrap.min.js', dirname(__FILE__)), array( 'jquery' ), '1.0', true );
					wp_enqueue_style('wcod-boostrap-style', plugins_url('css/bootstrap.min.css', dirname(__FILE__)));
					wp_enqueue_script('wcod-scripts', plugins_url('js/front-scripts.js?t='.time(), dirname(__FILE__)), array( 'jquery' ), '1.0', true );
					wp_enqueue_style('wcod-style', plugins_url('css/front-style.css?t='.time(), dirname(__FILE__)));
	
					wp_localize_script( 'wcod-scripts', 'wcod_obj',
	
						array(
								'ajax_url' => admin_url( 'admin-ajax.php' ),
								'ajax_nonce' => wp_create_nonce('wcod-ajax-safety')
						)
	
					);
				}
				
			}
		}

	}

	if(!function_exists('fetch_array')){
		function fetch_array ($result) {

			return json_decode(json_encode($result), true);
		}
	}


	add_shortcode( 'WC-ORDER-DETAILS', 'wcod_display_order_details' );

	if(!function_exists('wcod_display_order_details')){
		function wcod_display_order_details($atts=array(), $content = "", $tag){

			global $shortcode_tags;
			
			ob_start();

			global $affiliate_wp, $wpdb, $wcod_pro;

			$accessible = false;

			$user_aff_id = 0;
			$aff_orders_arr = array();
			
			
			
			$atts = shortcode_atts( array(
				'login-required' => 'yes',
				'logout-link' => 'no',
				'home-link' => 'no',
			), $atts, $tag );
			
			$login_required = ($atts['login-required']=='yes');
			$logout_link = ($atts['logout-link']=='yes');
			$home_link = ($atts['home-link']=='yes');

			
			if(is_user_logged_in()){

				$current_user = wp_get_current_user();
				//wcod_pre($current_user->ID);

				if($affiliate_wp){
					$user_aff_result = $wpdb->get_row("SELECT affiliate_id FROM ".$wpdb->prefix."affiliate_wp_affiliates WHERE	user_id = $current_user->ID");

					if(!empty($user_aff_result)){
						$user_aff_id = $user_aff_result->affiliate_id;
						//wcod_pre($get_aff_id);
						if(is_numeric($user_aff_id)){
							$user_aff_orders = $wpdb->get_results("SELECT reference FROM ".$wpdb->prefix."affiliate_wp_referrals WHERE affiliate_id = $user_aff_id AND context = 'woocommerce'");
							//wcod_pre($user_aff_orders);
							if(!empty($user_aff_orders)){
								foreach($user_aff_orders as $aff_orders){
									$aff_orders_arr[] = $aff_orders->reference;
								}
							}
						}
					}


				}

				//wcod_pre(get_user_meta($current_user->ID));
				$current_user_role = current($current_user->roles);

				$wcod_restrict = get_option('wcod_restrict', array());
				$wcod_restrict = (is_array($wcod_restrict)?$wcod_restrict:array());

				$accessible = in_array($current_user_role, $wcod_restrict);
			
			}else{
				if($login_required){
						
				}else{
					$accessible = true;
				}
			}

			if(!$accessible){
	?>
	<div class="container-fluid mb-5 mt-5">
		 <div class="row wcod-details-cols">
			<div class="col-md-6 mt-4 mx-auto">

				<div class="alert alert-danger fade in alert-dismissible show">
				<button type="button" class="close" data-dismiss="alert" aria-label="<?php _e('Close', 'wc-order-details'); ?>">
				<span aria-hidden="true" style="font-size:20px">×</span>
				</button>    <strong><?php _e('Sorry!', 'wc-order-details'); ?></strong> <?php _e('You are not allowed access this page.', 'wc-order-details'); ?> <?php if($login_required): ?><a title="<?php _e('Click here to Login', 'wc-order-details'); ?>" class="btn-sm btn-success" href="<?php echo wp_login_url( get_permalink() ); ?>"><?php _e('Click here to Login', 'wc-order-details'); ?></a><?php endif; ?>
				</div>

			</div>
		</div>
	</div>
	<?php
			}else{


			$order_id = 0;
			if(isset($_GET['order_id']) && is_numeric($_GET['order_id'])){
				$order_id = sanitize_wcod_data($_GET['order_id']);
			}

			$any_value = '..........';

			$is_administrator = false;
			$is_customer = false;
			
			if(is_user_logged_in()){
				
				$current_user = wp_get_current_user();		
						
				$super_roles = array( 'administrator' );
				
				if ( array_intersect( $super_roles, $current_user->roles ) ) {				  
				   $is_administrator = true;
				}
				
				$customer_roles = array('customer');
				
				if ( array_intersect( $customer_roles, $current_user->roles ) ) {				  
				   $is_customer = true;
				}
			}

			$args = array(
				'posts_per_page'   => -1,
				'offset'           => 0,
				'cat'         	   => '',
				'category_name'    => '',
				'orderby'          => 'date',
				'order'            => 'DESC',
				'include'          => '',
				'exclude'          => '',
				'post_type'        => 'shop_order',
				'post_mime_type'   => '',
				'post_parent'      => '',
				'author'	   	   => '',
				'author_name'	   => '',
				'post_status'      => 'any',
				'suppress_filters' => true,
				'fields'           => '',
			);
			if(is_user_logged_in() && $is_customer){
				$args['meta_key'] = '_customer_user';
				$args['meta_value'] = $current_user->ID;
			}
			//wcod_pre($args);
			if($affiliate_wp){
				$args['include'] = $aff_orders_arr;
				unset($args['meta_key']);
				unset($args['meta_value']);
			}

			$orders_array = get_posts( $args );
			
			if($login_required && is_user_logged_in() && $is_administrator && empty($orders_array)){
				unset($args['meta_key']);
				unset($args['meta_value']);
				$orders_array = get_posts( $args );
			}
			//wcod_pree($args);
			
			$orders_arr = array();
			$date = new DateTime();
			$date->sub(new DateInterval('P1D'));

		?>

		<div class="container-fluid mb-5 mt-5">
		
        <?php if(($logout_link && is_user_logged_in()) || $home_link): ?>
            <div class="row mb-5"><div class="col-md-12 mt-0">
            	 <?php if($home_link): ?>
	            	<a title="<?php echo get_bloginfo('description'); ?>" style="float:left;" href="<?php echo home_url(); ?>"><?php echo get_bloginfo('name'); ?></a>
                <?php endif; ?>
                <?php if(is_user_logged_in()): ?>
	                <a title="<?php _e('Click here to Logout', 'wc-order-details'); ?>" class="btn-sm btn-danger" style="width:58px; float:right;" href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php _e('Logout', 'wc-order-details'); ?></a>
                <?php else: ?>
                	<a title="<?php _e('Click here to Login', 'wc-order-details'); ?>" class="btn-sm btn-success" style="width:58px; float:right;" href="<?php echo wp_login_url( get_permalink() ); ?>"><?php _e('Login', 'wc-order-details'); ?></a>
                <?php endif; ?>
            </div></div>
		<?php endif; ?>
    
		<div class="row pb-5">

			<div class="col-md-4 mt-0">
			<h5 class="m-0 p-0"><?php _e('Orders List', 'wc-order-details'); ?>:</h5>
		
        
            <div class="col-md-12 mt-2 pl-0">
            <div class="form-group">
            <input type="text" name="filter_orders" id="filter_orders" class="form-control scripts_filter_input" placeholder="<?php _e('Filter Orders', 'wc-order-details'); ?>">
            </div>
            </div>
		<?php if(!empty($orders_array)): ?>
				<ul class="ml-0 pl-0 orders_list col-md-12">
		<?php foreach($orders_array as $order_item): $orders_arr[] = $order_item->ID; $permalink = get_permalink();
						$listed_order = wc_get_order($order_item->ID);
						$listed_order_customer = $listed_order->get_address();
						//wcod_pre($listed_order_customer);
						$listed_order_customer_name = '';
						
						if(is_array($listed_order_customer['first_name'])){
							$listed_order_customer_name_arr = array();
							foreach($listed_order_customer['first_name'] as $fn=>$fn_val){
								$listed_order_customer_name_arr[] = $listed_order_customer['first_name'][$fn].' '.$listed_order_customer['last_name'][$fn];								
							}
							$listed_order_customer_name = implode(' | ', $listed_order_customer_name_arr);
							
						}else{
							$listed_order_customer_name = $listed_order_customer['first_name'].' '.$listed_order_customer['last_name'];
						}
						
						
						$listed_order_date_created = $listed_order->get_date_created();
						$listed_order_date_created_object = new DateTime($listed_order->get_date_created());
						$listed_order_date_created_formatted = ($listed_order_date_created ?date('n/j/y', strtotime($listed_order_date_created)).' '.date('g:i a', strtotime($listed_order_date_created)):'');
						$listed_order_status = $listed_order->get_status();
						$listed_order_status_color = '';
						


						switch($listed_order_status){
							case 'processing':
								$listed_order_status_color = "#80a840";
							break;
							case 'completed':
								$listed_order_status_color = "#4281f5";
							break;
							case 'on-hold':
								$listed_order_status_color = "#d1cc36";
							break;
							case 'failed':
								$listed_order_status_color = "#f54269";
							break;
							case 'refunded':
								$listed_order_status_color = "#6d3970";
							break;
							default:
								$listed_order_status_color = "#000000";
							break;
						}

		?>
					<li id="wcod-list-item-<?php echo $order_item->ID; ?>" title="<?php echo $listed_order_status; ?>">
                    <span style="background-color:<?php echo $listed_order_status_color; ?>;border:1px solid <?php echo $listed_order_status_color; ?>;">&nbsp;</span>
                    <a href="<?php echo (stripos($permalink, '?')===false?$permalink.'?':$permalink.'&').'order_id='.$order_item->ID; ?>">
                    &nbsp;#
                    <?php if ($listed_order_date_created_object >= $date) echo '<strong>'; ?>
					<?php echo $order_item->ID; ?> - <?php echo $listed_order_customer_name; ?> - <?php echo $listed_order_date_created_formatted; ?>
                    <?php if ($listed_order_date_created_object >= $date) echo '</strong>'; ?>
                    </a></li>
		<?php endforeach; ?>
				</ul>
		<?php endif; ?>
			</div>

			<div class="col-md-8 cards-wrapper">


			<?php if($order_id && in_array($order_id, $orders_arr)): ?>
			<?php
				$order = wc_get_order($order_id);
				$order_meta = get_post_meta($order_id);
				$order_data = $order->get_data();
				$items = $order->get_items();
				$currency = get_woocommerce_currency_symbol();
				$payment_method = $order->get_payment_method();
				$payment_title = $order->get_payment_method_title();
				//wcod_pre($payment_title);
				$order_notes = wcod_get_private_order_notes($order_id);
				$order_tracking = wcod_get_private_order_tracking($order_id);
				$label_type = isset($order_meta['label_type'])?$order_meta['label_type'][0]:'';
				$customer_note = $order->get_customer_note();

				

				//customer, address, and  billing detail
				$customer = $order->get_address();
				

				$customer_full_name = $customer['first_name'].' '.$customer['last_name'];

				$customer_company = $customer['company'];
				$customer_address_1 = $customer['address_1'];
				$customer_address_2 = $customer['address_2'];
				$customer_city = $customer['city'];
				$customer_country = $customer['country'];
				$customer_state = $customer['state'];
				$all_coutries_states = wc()->countries->states;
				$customer_country_states = (array_key_exists($customer_country, $all_coutries_states)?$all_coutries_states[$customer_country]:array());
				$customer_state = (array_key_exists($customer_state, $customer_country_states)?$customer_country_states[$customer_state]:'');
				//$customer_country = wc()->countries->countries[$customer_country];
				$customer_postcode = $customer['postcode'];
				$customer_email = $customer['email'];
				$customer_phone = $customer['phone'];
				
				//wcod_pre($customer_email);
				$user = get_user_by('email', $customer_email);
				$customer_id = (is_object($user)?$user->ID:0);

				$wcod_display = get_option('wcod_display', array()); //wcod_pre($wcod_display);
				$wcod_display = (is_array($wcod_display)?$wcod_display:array());


				$wcod_tabs_none = in_array('-', $wcod_display);
				$wcod_tabs_all = empty($wcod_display);
				$wcod_tabs_order_details = (in_array('order_details', $wcod_display) || $wcod_tabs_all);
				$wcod_tabs_item_details = in_array('item_details', $wcod_display);
				$wcod_tabs_order_activity = in_array('order_activity', $wcod_display);
				$wcod_tabs_customer_details = in_array('customer_details', $wcod_display);
				$wcod_tabs_shipping = in_array('shipping', $wcod_display);
				$wcod_tabs_affiliate = in_array('affiliate', $wcod_display);
				$wcod_tabs_meta = in_array('meta', $wcod_display);

				$coupons  = $order->get_coupon_codes();
				$coupons  = count($coupons) > 0 ? implode(',', $coupons) : '';
				$shipping_total = $order_data['shipping_total'];
				$order_subtotal = $order->get_total()-$shipping_total;
				
				//$percent_formatter = new NumberFormatter('en_US', NumberFormatter::PERCENT);
				//wcod_pre($coupons);
				//wcod_pre($order);
				//wcod_pre($customer_id.'!='.$current_user->ID);
			?>

            	<?php if($wcod_pro): ?>
				<div class="row wcod_pro_search">


				<div class="col-md-6 col-md-offset-8 wcod-details-cols">
                    <div class="form-group">
                    <input type="text" name="wcod_search" id="wcod_search" class="form-control scripts_search_input" placeholder="<?php _e('Search By Customer Name or Order ID', 'wc-order-details'); ?>" />
                    <div class="wcod_loading"></div>
                    </div>
                    <ul class="ml-0 pl-0 orders_list col-md-12">
                    </ul>
                    <a class="wcod_clear_results"><?php _e('Clear', 'wc-order-details') ;?></a>
				</div>
				</div>
				<?php endif; ?>

				 <div class="row wcod-details-cols">
					<div class="col-md-12">
					 <?php //wcod_pre(!$wcod_tabs_none); wcod_pre($wcod_tabs_order_details); ?>
                    <?php if(!$wcod_tabs_none && $wcod_tabs_order_details): ?>

					<div class="card card-common">
						<div class="card-header">
							<h4 class="card-title">
								<a data-toggle="collapse" class="target_click" href="#card_basic"></a><?php _e('Order No.', 'wc-order-details') ;?> # <?php echo $order->get_order_number() ?> - <?php echo $customer_full_name;?></a>
								<div class="small-box bg-clr1"></div>
							</h4>
						</div>
						<div id="card_basic" class="card-collapse collapse show">
							<div class="card-body">
								<table class="table table-bordered">
									<thead></thead>
									<tbody>
										<tr>
											<th colspan="2"><?php _e('Order No.', 'wc-order-details') ;?> #</th>
											<td><?php echo $order->get_order_number() ?></td>
										</tr>
										<tr>
											<th colspan="2"><?php _e('Total Items', 'wc-order-details') ;?></th>
											<td><?php echo  sizeof($items) ?></td>
										</tr>
										<tr>
											<th colspan="2"><?php _e('Order Status', 'wc-order-details') ;?></th>
											<td><?php echo $order->get_status(); ?></td>
										</tr>
										<tr>
											<th colspan="2"><?php _e('Order Type', 'wc-order-details') ;?></th>
											<td><?php echo $order->get_type(); ?></td>
										</tr>

										<tr>
											<th colspan="2"><?php _e('Order Place Date', 'wc-order-details') ;?></th>
											<td><?php echo ($order->get_date_created()?date('n/j/y', strtotime($order->get_date_created())).' at '.date('g:i a', strtotime($order->get_date_created())):''); ?></td>
										</tr>
										<tr>
											<th colspan="2"><?php _e('Order Process Date', 'wc-order-details') ;?></th>
											<td><?php echo ($order->get_date_modified()?date('n/j/y', strtotime($order->get_date_modified())).' at '.date('g:i a', strtotime($order->get_date_modified())):''); ?></td>
										</tr>
										<tr>
											<th colspan="2"><?php _e('Order Completed Date', 'wc-order-details') ;?></th>
											<td><?php echo ($order->get_date_completed()?date('n/j/y', strtotime($order->get_date_completed())).' at '.date('g:i a', strtotime($order->get_date_completed())):''); ?></td>
										</tr>
                                        <tr>
											<th colspan="2"><?php _e('Order Shipping Info', 'wc-order-details') ;?></th>
											<td>
												<?php if(!empty($order_tracking)): $n=0; ?>
												<?php foreach($order_tracking as $tracking_data): $n++; ?>
												 <div class="row wcod-details-cols">
													<div class="col-sm-6 col-md-12">

															<?php echo 'Shipped '.date('n/j/y', strtotime($tracking_data['note_date'])).' '.date('g:i a', strtotime($tracking_data['note_date']));?>
															<?php echo ' Via '.$tracking_data['note_carrier'].' - Tracking Number ';?>
															<?php
																if($tracking_data['note_carrier'] == "UPS"){
																	echo '<a href="http://wwwapps.ups.com/WebTracking/processRequest?HTMLVersion=5.0&Requester=NES&AgreeToTermsAndConditions=yes&loc=en_US&tracknum='.$tracking_data['note_tracking'].'" target="_blank" style="color: #0000EE">'.$tracking_data['note_tracking'].'</a>';
																}elseif($tracking_data['note_carrier'] == "USPS"){
																	echo '<a href="https://tools.usps.com/go/TrackConfirmAction?tRef=fullpage&tLc=3&text28777=&tLabels='.$tracking_data['note_tracking'].'" target="_blank" style="color: #0000EE">'.$tracking_data['note_tracking'].'</a>';
																}elseif($tracking_data['note_carrier'] == "FEDEX"){
																	echo '<a href="https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber='.$tracking_data['note_tracking'].'" target="_blank" style="color: #0000EE">'.$tracking_data['note_tracking'].'</a>';
																}
															?>
															<br />


														<?php echo ($n<count($order_tracking)?'<hr>':''); ?>
													</div>
												</div>
												<?php endforeach; ?>
                                                <?php else: ?>
<?php
$order_shipping_info = array();
foreach( $order->get_items( 'shipping' ) as $item_id => $item ){
	$order_item_name             = $item->get_name();
	$order_item_type             = $item->get_type();
	$shipping_method_title       = $item->get_method_title();
	$shipping_method_id          = $item->get_method_id(); // The method ID
	$shipping_method_instance_id = $item->get_instance_id(); // The instance ID
	$shipping_method_total       = $item->get_total();
	$shipping_method_total_tax   = $item->get_total_tax();
	$shipping_method_taxes       = $item->get_taxes();
	
	$order_shipping_info[] = array(
									'order_item_name' => $order_item_name, 
									'order_item_type' => $order_item_type,
									'shipping_method_title' => $shipping_method_title,
									'shipping_method_id' => $shipping_method_id, 
									'shipping_method_instance_id' => $shipping_method_instance_id,
									'shipping_method_total' => $shipping_method_total,
									'shipping_method_total_tax' => $shipping_method_total_tax,
									'shipping_method_taxes' => $shipping_method_taxes
							);
	//wcod_pre($order_shipping_info);
	
	$order_item_name_parts = explode('-', $order_item_name);
	$order_item_name = (is_array($order_item_name)?current($order_item_name):$order_item_name);
	echo apply_filters('wcod-after-shipping-information', $order_item_name, $order_shipping_info, $order_id, $order);
}
?>                                                
												<?php endif; ?>
											</td>
										</tr>
                                         <?php if($payment_title): ?>
                                        <tr>
											<th colspan="2"><?php _e('Payment Title', 'wc-order-details') ;?></th>
											<td><span></span><?php echo $payment_title; ?></td>
										</tr>
                                        <?php endif; ?>
                                        <?php if($payment_method): ?>
										<tr>
											<th colspan="2"><?php _e('Payment Method', 'wc-order-details') ;?></th>
											<td><span></span><?php echo $payment_method; ?></td>
										</tr>
                                        <?php endif; ?>
                                        <?php if($label_type): ?>
                                        <tr>
											<th colspan="2"><?php _e('Label Type', 'wc-order-details') ;?></th>
											<td><span></span><?php echo $label_type; ?></td>
										</tr>
                                        <?php endif; ?>
                                        <?php if($customer_note): ?>
                                        <tr>
											<th colspan="2"><?php _e('Customer Note', 'wc-order-details') ;?></th>
											<td><span></span><?php echo $customer_note; ?></td>
										</tr>
                                        <?php endif; ?>
                                        <?php 
											if(function_exists('wcod_switch_nonce')){
											$switch_nonce = wcod_switch_nonce($order_id);
											if(!empty($switch_nonce) && $customer_id && $customer_id!=$current_user->ID && $customer_full_name && isset($switch_nonce->user_id)): 
											//wcod_pre($switch_nonce);
											?>
										<tr>
                                        	<th colspan="2"><?php _e('Customer Login', 'wc-order-details') ;?></th>
											<td><span></span><a href="<?php echo home_url(); ?>/?action=switch_to_customer&user_id=<?php echo $switch_nonce->user_id; ?>&reference=<?php echo base64_encode($order_id); ?>"><?php _e('Click here', 'wc-order-details'); ?> <?php _e('to login as', 'wc-order-details') ;?> <?php echo $customer_full_name; ?></a></td>
										</tr>
                                        <?php endif;
											}
										?>

									</tbody>
								</table>
							</div>
						</div>
					</div>

                    <?php endif; ?>

                    <?php if(!$wcod_tabs_none && $wcod_tabs_item_details): ?>

            		<div class="card card-common mt-2">
						<div class="card-header">
							<h4 class="card-title">
								<a data-toggle="collapse" class="target_click" href="#card_items"><?php _e('Item Details', 'wc-order-details'); ?></a>
								<div class="small-box bg-clr5"></div>
							</h4>
						</div>
						<div id="card_items" class="card-collapse collapse">
							<div class="card-body">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th style="text-align:center">#</th>
											<th style="text-align:center"><?php _e('Item Image', 'wc-order-details') ;?></th>
											<th style="text-align:center"><?php _e('Item Name', 'wc-order-details') ;?></th>
											<th style="text-align:center" nowrap><?php _e('Item Price', 'wc-order-details') ;?></th>
											<th style="text-align:center"><?php _e('Qty', 'wc-order-details') ;?></th>
											<th style="text-align:center" nowrap><?php _e('Line Total', 'wc-order-details') ;?></th>
										</tr>
									</thead>
									<tbody>
										<?php
											$i = 0;
											foreach ($items as $item_id => $item_data) { $i++;

												// Get an instance of corresponding the WC_Product object
											   // pree($item_data);
												$product = $item_data->get_product();
												$product_name = $product->get_name();
												$product_id =  $product->get_id(); // Get the product name
												$product_price = $product->get_price();
												$item_quantity = $item_data->get_quantity(); // Get the item quantity
												$item_total = $item_data->get_total(); // Get the item line total
												$item_subtotal = $item_data->get_subtotal();
												$item_discount = false; 
												$item_discount_percent = 0;
												$item_discount_off = 0;
												$product_discount_price = 0;
												
												if ($item_subtotal != $item_total){
													$item_discount = true;
													$item_discount_off = $item_subtotal - $item_total;
													$item_discount_percent = $item_total/$item_subtotal;
													$product_discount_price = $product_price * $item_discount_percent;
												}
										?>
										<tr>
											<td><?php echo $i; ?></td>
											<td><?php echo get_the_post_thumbnail( $product_id, 'thumbnail', array( 'height' => '50px', 'width' => '50px' ) );?></td>
											<td><?php echo $product_name; do_action('wcod-after-product-name', $item_id, $item_data, $product_id, $product, $order); ?></td>
											<td style="text-align:right" nowrap>
												<?php 
													if($item_discount){ 
														echo '<strike>'.$currency.number_format($product_price,2).'</strike>'.' '.$currency.number_format($product_discount_price,2);	
													} else {
														echo $currency.number_format($product_price,2);	
													}
												?>
											</td>
											<td style="text-align:right"><?php echo number_format($item_quantity) ;?></td>
											<td style="text-align:right">
												<?php 
													if ($item_discount){ 
														echo $currency.number_format( $item_total, 2).'</br><i>'.$currency.number_format( $item_discount_off, 2).' discount</i>'; 
													} else {
														echo $currency.number_format( $item_total, 2); 
													}														
												?>
											</td>
										</tr>

											<?php } ?>

										<!-- Loop end before this line -->
										<tr class="bg-info">
											<th colspan="6"><?php _e('Amount Details', 'wc-order-details') ;?>:</th>
										</tr>
										<?php if(!empty($coupons)){ ?>
										<tr>
											<th colspan="2"><?php _e('Coupon(s)', 'wc-order-details') ;?></th>
											<td colspan="3"><?php echo $coupons; ?></td>
											<td style="text-align:right"><?php echo $currency.number_format( $order_data['discount_total'], 2); ?></td>
										</tr>
										<?php } ?>
										<tr>
											<th colspan="5"><?php _e('Subtotal', 'wc-order-details') ;?></th>
											<td style="text-align:right"><?php echo $currency.number_format( $order_subtotal, 2); ?></td>
										</tr>
										<tr>
											<th colspan="5"><?php _e('Shipping', 'wc-order-details') ;?></th>
											<td style="text-align:right"><?php echo $currency.number_format( $shipping_total, 2); ?></td>
										</tr>
										<tr>
											<th colspan="5"><?php _e('Total', 'wc-order-details') ;?></th>
											<td style="text-align:right"><?php echo $currency.number_format( $order->get_total(), 2); ?></td>
										</tr>

									</tbody>
								</table>

							</div>
						</div>
					</div>

                    <?php endif; ?>

                    <?php if(!$wcod_tabs_none && $wcod_tabs_order_activity): ?>

					<div class="card card-common mt-2">
						<div class="card-header">
							<h4 class="card-title">
								<a data-toggle="collapse" class="target_click" href="#order_notes"><?php _e('Order Activity', 'wc-order-details') ;?></a>
								<div class="small-box bg-clr2-1"></div>
							</h4>
						</div>
						<div id="order_notes" class="card-collapse collapse">
							<div class="card-body">



								<?php if(!empty($order_notes)): $n=0; ?>
                                <?php foreach($order_notes as $notes_data): $n++; ?>
								 <div class="row wcod-details-cols">

									<div class="col-sm-6 col-md-12">

										<h5><?php //echo $notes_data['note_author']; ?></h5>

										<p><?php echo $notes_data['note_content']; ?><br /><?php echo date('n/j/y', strtotime($notes_data['note_date'])).' at '.date('g:i a', strtotime($notes_data['note_date'])); ?></p>
										<?php echo ($n<count($order_notes)?'<hr>':''); ?>



									</div>
								</div>
								<?php endforeach; ?>
                                <?php endif; ?>



							</div>
						</div>
					</div>

                    <?php endif; ?>

                    <?php if(!$wcod_tabs_none && $wcod_tabs_customer_details): ?>

					<div class="card card-common mt-2">
						<div class="card-header">
							<h4 class="card-title">
								<a data-toggle="collapse" class="target_click" href="#card_customer"><?php _e('Customer Details', 'wc-order-details') ;?></a>
								<div class="small-box bg-clr2"></div>
							</h4>
						</div>
						<div id="card_customer" class="card-collapse collapse">
							<div class="card-body">
								 <div class="row wcod-details-cols">
									<div class="col-sm-6 col-md-4 d-none">
										<img src="<?php echo plugins_url('img/350x400.png', dirname(__FILE__)); ?>" alt="" class="img-rounded img-responsive" />
									</div>
									<div class="col-sm-6 col-md-12">

										<table class="table table-bordered">
											<thead></thead>
											<tbody>
												<tr>
													<th><strong><?php _e('Bill To Info', 'wc-order-details') ;?></strong></th>
													<td>
														<h6><span class="glyphicon glyphicon-user"></span> <?php echo $customer_full_name; ?></h6>
														<h6><span class="glyphicon glyphicon-user"></span> <?php echo $customer_company; ?></h6>
														<h6><span class="glyphicon glyphicon-map-marker"></span> <?php echo $customer_address_1; ?></h6>
														<h6><span class="glyphicon glyphicon-map-marker"></span> <?php echo $customer_address_2; ?></h6>
														<h6><span class="glyphicon glyphicon-globe"></span> <?php echo $customer_city.', '.$customer_state.' '.$customer_postcode.' '.$customer_country ?></h6>
														<h6><span class="glyphicon glyphicon-phone"><?php _e('Phone', 'wc-order-details') ;?>: </span> <a href="tel:<?php echo $customer_phone ?>"> <?php echo $customer_phone ?></a></h6>
														<h6><span class="glyphicon glyphicon-envelope"><?php _e('Email', 'wc-order-details') ;?>: </span> <a href="mailto:<?php echo $customer_email ?>"> <?php echo $customer_email ?></a></h6>
													</td>
												</tr>
                                                
                                                <tr>
													<th><strong><?php _e('Ship To Info', 'wc-order-details') ;?></strong></th>
													<td>
														<h6><span class="glyphicon glyphicon-user"></span> <?php echo $order_data['shipping']['first_name'] . ' '	 . $order_data['shipping']['last_name']; ?></h6>
														<h6><span class="glyphicon glyphicon-user"></span> <?php echo $order_data['shipping']['company']; ?></h6>
														<h6><span class="glyphicon glyphicon-map-marker"></span> <?php echo $order_data['shipping']['address_1']; ?></h6>
														<h6><span class="glyphicon glyphicon-map-marker"></span> <?php echo $order_data['shipping']['address_2']; ?></h6>
														<h6><span class="glyphicon glyphicon-globe"></span> <?php echo $order_data['shipping']['city'].', '.$order_data['shipping']['state'].' '.$order_data['shipping']['postcode'].' '.$order_data['shipping']['country'] ?></h6>
														<h6><span class="glyphicon glyphicon-phone"><?php _e('Phone', 'wc-order-details') ;?>: </span> <a href="tel:<?php echo $customer_phone ?>"> <?php echo $customer_phone ?></a></h6>
														<h6><span class="glyphicon glyphicon-envelope"><?php _e('Email', 'wc-order-details') ;?>: </span> <a href="mailto:<?php echo $customer_email ?>"> <?php echo $customer_email ?></a></h6>
													</td>
												</tr>
                                                
											</tbody>
										</table>



									</div>

                                    

								</div>




							</div>
						</div>
					</div>

                    <?php endif; ?>

                    <?php if(!$wcod_tabs_none && $wcod_tabs_shipping): ?>

					<div class="card card-common mt-2">
						<div class="card-header">
							<h4 class="card-title">
								<a data-toggle="collapse"class="target_click" href="#card_shiping"><?php _e('Shipping Details', 'wc-order-details') ;?></a>
								<div class="small-box bg-clr4"></div>
							</h4>
						</div>
						<div id="card_shiping" class="card-collapse collapse">
							<div class="card-body">
							<h5><span class="glyphicon glyphicon-user"><?php _e('Name', 'wc-order-details') ;?>: </span> <?php echo $customer_full_name; ?></h5>
										<hr>
										<h6><span class="glyphicon glyphicon-phone"><?php _e('Phone', 'wc-order-details') ;?>: </span> <?php echo $customer_phone; ?></h6>
										<h6><span class="glyphicon glyphicon-envelope"><?php _e('Email', 'wc-order-details') ;?>: </span> <a href="mailto:<?php echo $customer_email ?>"> <?php echo $customer_email ?></a></h6>
										<h6><span class="glyphicon glyphicon-map-marker"><?php _e('Address', 'wc-order-details') ;?> 1: </span><?php echo $customer_address_1.', '.$customer_postcode; ?></h6>
										<h6><span class="glyphicon glyphicon-map-marker"><?php _e('Address', 'wc-order-details') ;?> 2: </span> <?php echo $customer_address_2 ?></h6>
										<h6><span class="glyphicon glyphicon-globe"><?php _e('State/Country', 'wc-order-details') ;?>: </span> <?php echo $customer_state.', '.$customer_country ?></h6>
							</div>
						</div>
					</div>

                    <?php endif; ?>




					<?php if($affiliate_wp): ?>
<?php

					$aff_data = $wpdb->get_row("
						SELECT
							r.referral_id,
							r.affiliate_id,
							r.`status`,
							r.amount,
							r.reference,
							u.display_name
						FROM wp_affiliate_wp_referrals r
						INNER join ".$wpdb->prefix."affiliate_wp_affiliates a on a.affiliate_id = r.affiliate_id
						INNER join ".$wpdb->prefix."users u on u.ID = a.user_id
						WHERE reference = '$order_id'
					");

					//wcod_pre($aff_data);
?>

					<?php if(!$wcod_tabs_none && $wcod_tabs_affiliate): ?>

					<div class="card card-common mt-2">
						<div class="card-header">
							<h4 class="card-title">
								<a data-toggle="collapse" class="target_click" href="#card_affiliate"><?php _e('Affiliate', 'wc-order-details') ;?></a>
								<div class="small-box bg-clr7"></div>
							</h4>
						</div>
						<div id="card_affiliate" class="card-collapse collapse">
						<?php if(!empty($aff_data)): ?>
							<div class="card-body">

                            <table class="table table-bordered">
									<thead></thead>
									<tbody>
										<tr>
											<th colspan="2"><?php _e('Affiliate Name', 'wc-order-details') ;?></th>
											<td><?php echo $aff_data->display_name; ?></td>
										</tr>
										<tr>
											<th colspan="2"><?php _e('Referral Amount', 'wc-order-details') ;?></th>
											<td><?php echo $currency.$aff_data->amount; ?></td>
										</tr>
                                        <tr>
											<th colspan="2"><?php _e('Referral Status', 'wc-order-details') ;?></th>
											<td><?php echo $aff_data->status; ?></td>
										</tr>
		                           </tbody>
                           </table>
                            </div>
                        <?php endif; ?>
						</div>
					</div>

                    <?php endif; ?>

                    <?php endif; ?>

                    <?php if(wcod_is_site_admin()): ?>

                    <?php if(!$wcod_tabs_none && $wcod_tabs_meta): ?>

                    <div class="card card-common mt-2">
						<div class="card-header">
							<h4 class="card-title">
								<a data-toggle="collapse" class="target_click" href="#card_meta"><?php _e('Meta', 'wc-order-details') ;?></a>
								<div class="small-box bg-clr6"></div>
							</h4>
						</div>
						<div id="card_meta" class="card-collapse collapse">
							<div class="card-body">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th><?php _e('Key', 'wc-order-details') ;?></th>
											<th><?php _e('Value', 'wc-order-details') ;?></th>
										</tr>
									</thead>
									<tbody>
<?php if(!empty($order_meta)): $mkv = 0; ?>
<?php foreach($order_meta as $key=>$val): $mkv++; ?>
										<tr>
											<td><?php echo $mkv; ?></td>
											<td><?php echo $key; ?></td>
											<td><?php echo is_array($val)?current($val):$val; ?></td>
										</tr>
<?php endforeach; ?>
<?php endif; ?>

									</tbody>
								</table>
							</div>
						</div>
					</div>

                    <?php endif; ?>
                    <?php endif; ?>


					</div>
					</div>
			<?php else: ?>
				<div class="alert alert-danger fade in alert-dismissible show">
				<button type="button" class="close" data-dismiss="alert" aria-label="<?php _e('Close', 'wc-order-details'); ?>">
				<span aria-hidden="true" style="font-size:20px">×</span>
				</button>    <strong><?php _e('Sorry', 'wc-order-details') ;?>!</strong> <?php _e('Please select an order to load details.', 'wc-order-details') ;?>
				</div>
			<?php endif; ?>

			</div>

		</div>
		<?php

				$out1 = ob_get_contents();


				ob_end_clean();

				return $out1;

			}
		}

	}

	if(!function_exists('wcod_remove_all_styles')){
		function wcod_remove_all_styles() {

			global $wp_styles, $post;
			
			if(is_object($post) && !empty($post)){

				$disable_theme_scripts = (get_option('wcod_theme_scripts', '')=='disable');
	
				$wcod_ss = get_option('wcod_styles_scripts', array());
				$wcod_ss = (is_array($wcod_ss)?$wcod_ss:array());
				$wcod_allowed_pages = ((!empty($wcod_ss) && in_array($post->ID, $wcod_ss)) || get_page_template_slug()=='wcod-template.php');
	
	
	
	
				if($wcod_allowed_pages && $disable_theme_scripts){
					foreach($wp_styles->queue as $queue_item){
						if(substr($queue_item, 0, strlen('wcod-'))=='wcod-' || in_array($queue_item, array('admin-bar'))){
							$queue_arr[] = $queue_item;
						}
					}
					$wp_styles->queue = $queue_arr;
				}
				
			}
		}
	}
	add_action('wp_print_styles', 'wcod_remove_all_styles', 100);

	function wcod_is_site_admin(){
		return in_array('administrator',  wp_get_current_user()->roles);
	}
	function wcod_plugin_links($links) {

		global $wcod_premium_link, $wcod_pro;


		$settings_link = '<a href="admin.php?page=wcod_settings">'.__('Settings', 'wc-order-details').'</a>';


		if($wcod_pro){
			array_unshift($links, $settings_link);
		}else{

			$wcod_premium_link = '<a href="'.esc_url($wcod_premium_link).'" title="'.__('Go Premium', 'wc-order-details').'" target="_blank">'.__('Go Premium', 'wc-order-details').'</a>';
			array_unshift($links, $settings_link, $wcod_premium_link);

		}


		return $links;
	}
	
	add_action('wcod-after-product-name', 'wcod_after_product_name_callback', 9, 5);
	if(!function_exists('wcod_after_product_name_callback')){
		function wcod_after_product_name_callback($item=0, $item_data=array(), $product_id=0, $product=array(), $order=array()){
			//wcod_pre($item);
			//wcod_pre(wc_get_order_item_meta($item, ''));
			//wcod_pre($item_data);
			//wcod_pre($product->get_sku());
			//wcod_pre(is_object($product));wcod_pre($order);wcod_pre($product);
			//wcod_pre($item_data);
			//wcod_pre($item_data->get_product_id());
			$product = (is_object($product)?$product:wc_get_product($product_id));

			$extra_info = array(
									(is_object($product) && $product->get_sku()?__( 'SKU', 'woocommerce' ).': '.$product->get_sku():'')
						);
			$extra_info = array_filter($extra_info);
			echo '<br />'.apply_filters('wcod-after-product-name-filter', implode('<br />', $extra_info));
		}
	}