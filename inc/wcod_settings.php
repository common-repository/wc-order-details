<?php defined( 'ABSPATH' ) or die( __('No script kiddies please!', 'wc-order-details') );
	if ( !current_user_can( 'administrator' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'wc-order-details' ) );
	}
	global $wcod_data, $wcod_pro, $wcod_premium_link, $wp_roles;
	$successfully_saved = false;
	$successfully_created = false;
	$submitted_action = (isset($_POST['wcod_remove_selected']) && $_POST['wcod_remove_selected']==1);
	
	 
	$file = 'wcod-template.php';
	$wcod_template_target = get_template_directory().'/'.$file;
	$wcod_template_source = WCOD_PLUGIN_DIR.'/assets/'.$file;
	$wcod_template_exists = file_exists($wcod_template_target);
	
	$tabs = array(
		'order_details' => __('Order Details', 'wc-order-details'),
		'item_details' => __('Item Details', 'wc-order-details'),
		'order_activity' => __('Order Activity', 'wc-order-details'),
		'customer_details' => __('Customer Details', 'wc-order-details'),
		'shipping' => __('Shipping Details', 'wc-order-details'),
		'affiliate' => __('Affiliate', 'wc-order-details'),
		'meta' => __('Meta', 'wc-order-details'),
	);
	
	
	$pages_args = array(
		'posts_per_page'   => -1,
		'offset'           => 0,
		'cat'         => '',
		'category_name'    => '',
		'orderby'          => 'title',
		'order'            => 'ASC',
		'include'          => '',
		'exclude'          => '',
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => 'page',
		'post_mime_type'   => '',
		'post_parent'      => '',
		'author'	   => '',
		'author_name'	   => '',
		'post_status'      => 'publish',
		'suppress_filters' => true,
		'fields'           => '',
	);
	$pages_list  = get_posts($pages_args);
	
	//echo $wcod_template_source.'<br />'.$wcod_template_target;
	
	$roles = $wp_roles->roles;
	//wcod_pree($roles);

	if(isset($_GET['wcod-template']) && $_GET['wcod-template']=='create'){
		if(!$wcod_template_exists){
			$successfully_created = copy($wcod_template_source, $wcod_template_target);
		}
	}
			
	if(isset($_POST['submit_form'])){
		
		if ( ! isset( $_POST['wcod_order_details_field'] ) 
			|| ! wp_verify_nonce( $_POST['wcod_order_details_field'], 'wcod_order_details_action' ) 
		) {
		   print 'Sorry, your nonce did not verify.';
		   exit;
		} else {
		   //pree($_POST);
		   if(isset($_POST['wcod_restrict'])){
				$wcod_restrict = sanitize_wcod_data($_POST['wcod_restrict']);
				update_option('wcod_restrict', $wcod_restrict);
				//wcod_pree($wcod_restrict);exit;
		   }
		   
		}		
		
	}
	
	if(isset($_POST['display_form'])){
		
		if ( ! isset( $_POST['wcod_display_details_field'] ) 
			|| ! wp_verify_nonce( $_POST['wcod_display_details_field'], 'wcod_display_details_action' ) 
		) {
		   print 'Sorry, your nonce did not verify.';
		   exit;
		} else {
		   //pree($_POST);
		   if(isset($_POST['wcod_display'])){
				$wcod_restrict = sanitize_wcod_data($_POST['wcod_display']);
				update_option('wcod_display', $wcod_restrict);
				//wcod_pree($wcod_restrict);exit;
		   }
		   
		}		
		
	}	
	
	
	if(isset($_POST['styles_form'])){
		
		if ( ! isset( $_POST['wcod_styles_form_field'] ) 
			|| ! wp_verify_nonce( $_POST['wcod_styles_form_field'], 'wcod_styles_form_action' ) 
		) {
		   print 'Sorry, your nonce did not verify.';
		   exit;
		} else {
		   //pree($_POST);
		   if(isset($_POST['wcod_styles_scripts'])){
				$wcod_styles_scripts = sanitize_wcod_data($_POST['wcod_styles_scripts']);
				update_option('wcod_styles_scripts', $wcod_styles_scripts);
				//wcod_pree($wcod_restrict);exit;
		   }
		   
		}		
		
	}	
	
	
	if(isset($_POST['themes_form'])){
		
		if ( ! isset( $_POST['wcod_themes_form_field'] ) 
			|| ! wp_verify_nonce( $_POST['wcod_themes_form_field'], 'wcod_themes_form_action' ) 
		) {
		   print 'Sorry, your nonce did not verify.';
		   exit;
		} else {
			
			$wcod_theme_scripts = (isset($_POST['wcod_theme_scripts'])?sanitize_wcod_data($_POST['wcod_theme_scripts']):'');
			update_option('wcod_theme_scripts', $wcod_theme_scripts);
		   
		}		
		
	}			
	
	$wcod_restrict = get_option('wcod_restrict', array());
	$wcod_restrict = (is_array($wcod_restrict)?$wcod_restrict:array());

	$wcod_display = get_option('wcod_display', array());
	$wcod_display = (is_array($wcod_display)?$wcod_display:array());
	
	$wcod_ss = get_option('wcod_styles_scripts', array());
	$wcod_ss = (is_array($wcod_ss)?$wcod_ss:array());	

	
?>
<div class="container-fluid wcod_wrapper_div pt-4">

		<div class="row mb-4">
        	<div class="icon32" id="icon-options-general"><br></div><h4><?php echo $wcod_data['Name']; ?> <?php echo '('.$wcod_data['Version'].($wcod_pro?') Pro':')'); ?></h4> 
            <?php if(!$wcod_pro): ?>
            <a href="<?php echo $wcod_premium_link; ?>" target="_blank" class="btn-sm btn-info go_premium_link"><?php _e('Go Premium', 'wc-order-details'); ?></a>
            <?php endif; ?>
        </div>


        <h2 class="nav-tab-wrapper">
            <a class="nav-tab nav-tab-active"><?php _e("Settings","wc-order-details"); ?> <i class="fas fa-cogs"></i></a>            
            <a class="nav-tab" data-tab="help" data-type="free" style="float:right"><i class="far fa-question-circle"></i>&nbsp;<?php _e("Help", 'wc-order-details'); ?></a>
        </h2>
        
        <div class="nav-tab-content mt-2">
        
		<div class="row">
			<div class="col-md-6 pl-0">
            
<?php 
					if($successfully_created):
					
?>
<div class="alert alert-success fade in alert-dismissible show" style="margin-top:18px;">
 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true" style="font-size:20px">×</span>
  </button>    <strong><?php echo __('Template file created', 'wc-order-details').'!</strong> '.__('Now you can', 'wc-order-details').' <a href="post-new.php?post_type=page" target="_blank">'.__('create a page', 'wc-order-details').'</a> '.__('with page template', 'wc-order-details'); ?> "WC Order Details".
</div>
<?php						
					
					endif;
?>
					
				<form>                            
                  <h6><?php _e('Shortcodes', 'wc-order-details'); ?>:</h6>
                    <div class="wcod_shortcodes">
                    	<ul>
                        	<li>[<strong>WC-ORDER-DETAILS</strong>]</li>
                            <li>[<strong>WC-ORDER-DETAILS</strong> login-required="<strong class="wcod-green">yes</strong>" logout-link="<strong class="wcod-red">no</strong>" home-link="<strong>no</strong>"] <a style="float:right" title="<?php _e('Click here for a screenshot', 'wc-order-details'); ?>" href="https://ps.w.org/wc-order-details/assets/screenshot-8.png" target="_blank"><i class="fas fa-question wcod-red"></i></a></li>
                        </ul>
                    </div>
                    <h6><?php _e('How it works?', 'wc-order-details'); ?></h6>
                    <div class="wcod_hiw">
                    	<ol>
                        	<li><a href="post-new.php?post_type=page" target="_blank"><?php _e('Create a page', 'wc-order-details') ;?></a></li>
                            <li><?php _e('Name it anything but select page template from page Attributes as', 'wc-order-details') ;?> "WC Order Details" <?php if($wcod_template_exists): ?><span title="<?php echo $file.' '.__('template file created', 'wc-order-details'); ?>" class="wcod-template-file"></span><?php endif; ?></li>
                            <li><?php echo __('Create a file in your theme directory as', 'wc-order-details').' "wcod-template.php" '.__('and paste the following stuff in it', 'wc-order-details'); ?>:<br /><br /><br />
                            
                            <b>
                            &lt;?php<br />
                              /**<br />
                              * <?php _e('Template Name', 'wc-order-details') ;?>: WC Order Details<br />
                              */<br />
                              wp_head();<br />
                              echo do_shortcode('[WC-ORDER-DETAILS]');<br />
                            wp_footer();</b><br /><br /><br />

                            
                            </li>
                            <?php if(!$wcod_template_exists): ?>
                            <li><?php echo __('Alternatively', 'wc-order-details').', <a href="admin.php?page=wcod_settings&wcod-template=create">'.__('click here', 'wc-order-details').'</a> '.__('and this file will be created in your theme directory automatically.', 'wc-order-details'); ?>                            
                            </li>
                            <?php endif; ?>
                        </ol>
                    </div>
                    
                    
                    
                    
				</form>        
			</div>
            <div class="col-md-3">
                <form method="post">
                	<?php wp_nonce_field( 'wcod_display_details_action', 'wcod_display_details_field' ); ?>

            		<h6><?php _e('Tabs Settings', 'wc-order-details'); ?>:</h6>
                    <div class="wcod-display-restrictions">
                    	
                       <strong><?php _e('Select tabs to appear/disappear', 'wc-order-details'); ?>:</strong>
                    
                    	<select name="wcod_display[]" multiple="multiple">
                        	<option value="-" <?php selected(in_array('-', $wcod_display)); ?>><?php _e('None', 'wc-order-details'); ?></option>
                            <?php if(!empty($tabs)): foreach($tabs as $tab=>$label): ?>
                            <option <?php selected(in_array($tab, $wcod_display) || empty($wcod_display)); ?> value="<?php echo $tab; ?>"><?php echo $label; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                         <input type="submit" name="display_form" class="btn btn-primary btn-sm mt-3" value="<?php _e('Update', 'wc-order-details'); ?>" />
                    </div>
                   
                </form>
                
                
                <form method="post">
                	<?php wp_nonce_field( 'wcod_styles_form_action', 'wcod_styles_form_field' ); ?>

            		<h6><?php _e('Styles & Scripts', 'wc-order-details'); ?>:</h6>
                    <div class="wcod-display-restrictions">
                    	
                       <strong><?php _e('Select pages to use load plugin styles and scripts', 'wc-order-details'); ?>:</strong>
                    	<?php //wcod_pree($pages_list); ?>
                    	<select name="wcod_styles_scripts[]" multiple="multiple">
                        	<option value="-" <?php selected(in_array('-', $wcod_ss)); ?>><?php _e('None', 'wc-order-details'); ?></option>
                            <?php if(!empty($pages_list)): foreach($pages_list as $list_item): ?>
                            <option <?php selected(in_array($list_item->ID, $wcod_ss) || empty($wcod_ss)); ?> value="<?php echo $list_item->ID; ?>"><?php echo $list_item->post_title; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        
                        <input type="submit" name="styles_form" class="btn btn-primary btn-sm mt-3" value="<?php _e('Update', 'wc-order-details'); ?>" />
                    </div>  
                   
                </form>
            </div>

            <div class="col-md-3">
                <form method="post">
                	<?php wp_nonce_field( 'wcod_order_details_action', 'wcod_order_details_field' ); ?>

            		<h6><?php _e('Allow Users by Role', 'wc-order-details'); ?>:</h6>
                    <div class="wcod-restrictions">
                    	<select name="wcod_restrict[]" multiple="multiple">
                        	<option value=""><?php _e('None', 'wc-order-details'); ?></option>
                            <?php if(!empty($roles)): foreach($roles as $role=>$label): ?>
                            <option <?php selected(in_array($role, $wcod_restrict)); ?> value="<?php echo $role; ?>"><?php echo $label['name']; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                         <input type="submit" name="submit_form" class="btn btn-primary btn-sm mt-3" value="<?php _e('Update', 'wc-order-details'); ?>" />
                    </div>
                   
                </form>
                
                <form method="post">
                	<?php wp_nonce_field( 'wcod_themes_form_action', 'wcod_themes_form_field' ); ?>

            		<h6><?php _e('Various Options', 'wc-order-details'); ?>:</h6>
                    <div class="wcod-display-restrictions">
                    	
                      
                        <label for="wcod_theme_scripts">
	                        <input type="checkbox" id="wcod_theme_scripts" name="wcod_theme_scripts" value="disable" <?php checked(get_option('wcod_theme_scripts', '')=='disable'); ?> />
                            <?php _e('Disable all theme styles', 'wc-order-details'); ?>
                        </label><br />
                        <input type="submit" name="themes_form" class="btn btn-primary btn-sm mt-3" value="<?php _e('Update', 'wc-order-details'); ?>" />
                    </div>  
                   
                </form>
            </div>
            
		</div>
        
        </div>

		<div class="nav-tab-content container-fluid hide mt-2" data-content="help">
			
                <div class="row mt-3">
            
                <ul class="position-relative">
                    <li><a class="btn btn-sm btn-info" href="https://wordpress.org/support/plugin/wc-order-details/" target="_blank"><?php _e('Open a Ticket on Support Forums', 'wc-order-details'); ?></a></li>
                    <li><a class="btn btn-sm btn-warning" href="http://demo.androidbubble.com/contact/" target="_blank"><?php _e('Contact Developer', 'wc-order-details'); ?></a><i class="fas fa-headset"></i></li>
                    <li><iframe width="560" height="315" src="https://www.youtube.com/embed/TQ3gEr1kpEo" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></li>
                </ul>                
    
                </div>
        </div>
		
</div>