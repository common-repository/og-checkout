<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.oneglobal.com
 * @since             1.0.0
 * @package           Ogcheckout
 *
 * @wordpress-plugin
 * Plugin Name:       Og Checkout
 * Plugin URI:        www.oneglobal.com
 * Description:       Og Checkout makes it easy for your customers to pay online.
 * Version:           1.0.0
 * Author:            Oneglobal
 * Author URI:        www.oneglobal.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ogcheckout
 * Domain Path:       
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'OGCHECKOUT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ogcheckout-activator.php
 */
function activate_ogcheckout() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ogcheckout-activator.php';
	Ogcheckout_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ogcheckout-deactivator.php
 */
function deactivate_ogcheckout() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ogcheckout-deactivator.php';
	Ogcheckout_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ogcheckout' );
register_deactivation_hook( __FILE__, 'deactivate_ogcheckout' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ogcheckout.php';

add_filter('plugin_action_links', 'ogcheckout_plugin_action_links', 10, 2);

/**
 * Adding custom links to plugin list.
 *
 * @since    1.0.0
 * @param    array 	$links   Array of existing settings link.
 * @param    string $file    String of native file.
 */
function ogcheckout_plugin_action_links($links, $file) {
     static $this_plugin;
     if (!$this_plugin) { $this_plugin = plugin_basename(__FILE__);}
     if ($file == $this_plugin) {
                $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wc-settings&tab=checkout&section=ogcheckout">Settings</a>';
                array_unshift($links, $settings_link);
      }
            return $links;
}

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway.
 */
add_filter( 'woocommerce_payment_gateways', 'ogchekout_add_gateway_class' );

/**
 * Adding our Gateway to Woocommerce.
 *
 * @since    1.0.0
 * @param    array 	$gateways   Array of existing gateways.
 */
function ogchekout_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Ocheckout_Gateway'; // Your class name is here.
	return $gateways;
}
 


add_action( 'plugins_loaded', 'ogcheckout_init_gateway_classes' );

	/**
	 * Adding our Gateway Class to Woocommerce.
	 *
	 * @since    1.0.0
	 */
	function ogcheckout_init_gateway_classes(){
	
	/**
	 * The Gateway class for Ogcheckout.
	 *
	 * @package    Ogcheckout
	 * @author     Oneglobal <info@oneglobal.com>
	 */
	class WC_Ocheckout_Gateway extends WC_Payment_Gateway {

			/**
			 * Class constructor, more about it in Step 3
			 */
			public function __construct() {

				$this->id = 'ogcheckout';
				$this->image_baseurl = plugin_dir_url( __FILE__ ) . 'resources/images/';
				$this->icon = $this->image_baseurl . 'ogcheckout.png';
				$this->has_fields = true; 
				$this->method_title = 'Og Checkout';
				$this->method_description = 'Og Checkout makes it easy for your customers to pay online.'; // will be displayed on the options page.

				$this->supports = array(
					'products',
					'default_credit_card_form'
				);

				// Method with all the options fields.
				$this->init_form_fields();

				// Load the settings.
				$this->init_settings();
				$this->title = 'Og Checkout';
				$this->description = 'You will be redirect to Og Checkout secure page';
				$this->enabled = $this->get_option( 'enabled' );
				// This action hook saves the settings.
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				add_action( 'woocommerce_api_' . $this->id , array( $this, 'ogcheckoutCallback' ) );

				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_ogcheckout_scripts' ) );
			}

			/**
			 * Plugin Scripts added.
			 */
			public function enqueue_ogcheckout_scripts() {
				
				wp_register_script( 'ogcheckout_scripts', plugin_dir_url( __FILE__ ) . 'admin/js/ogcheckout-admin.js' , array( 'jquery', 'jquery-payment' ), '1.0.0', true );
				wp_enqueue_script( 'ogcheckout_scripts' );
			}

			/**
			 * Plugin options, we deal with it in Step 3 too.
			 */
			public function init_form_fields(){
				$this->form_fields = array(
					'enabled' => array(
						'title'       => 'Enable/Disable',
						'label'       => 'Enable Og Chekout Gateway',
						'type'        => 'checkbox',
						'description' => '',
						'default'     => 'no'
					),
					'merchantcode' => array(
						'title'       => 'Merchant Name',
						'type'        => 'text',
						'default'     => '',
						'desc_tip'    => true,
					),		
					'authkey' => array(
						'title'       => 'Auth Key',
						'type'        => 'text',
						'default'     => '',
						'desc_tip'    => true
					),	
					'secretkey' => array(
						'title'       => 'Secret Key',
						'type'        => 'text',
						'default'     => '',
						'desc_tip'    => true,
					),
					'endpoint' => array(
						'title'       => 'Endpoint Url',
						'type'        => 'text',
						'default'     => '',
						'desc_tip'    => true
					),
					'language' => array(
						'title'       => 'Language',
						'type'        => 'text',
						'default'     => 'en',
						'desc_tip'    => true,
					),
					'tunnel' => array(
						'title'       => 'Tunnel',
						'type'        => 'text',
						'default'     => '',
						'desc_tip'    => true,
					),		
					'paymentmethodmode' => array(
						'title'       => 'Payment Method',
						'label'       => 'Payment Method',
						'type'        => 'select',
						'placeholder' => 'Select Payment Mode',
						'class'       => array('input-select'),
						'options'     => array(''=> 'Select Payment Method Mode','customize'=>'Customize Your Payment Form','ogpayment' => 'Og Checkout Payment Form'),
						'required'    => true,
					),
					'ogpaymentmethod' => array(
						'title'       => 'Payment Method',
						'type'        => 'text',
						'description' => __('Refer to Documentation'),
						'default' => '',
						'desc_tip' => true,
					),
					'ogpaymentcurrency' => array(
						'title'       => 'Payment Currency',
						'type'        => 'text',
						'description' => __('Leave this field blank when you use "all" parameter in Payment Method'),
						'default' => '',
						'desc_tip' => true,
					),				
					
				);		
			}
	
		/**
		 * Add Payment fields.
		 */
		public function payment_fields() {
		
			// I will echo() the form, but you can close PHP tags and print it directly in HTML.
			echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
			echo '<div class="ogdesc">You will be redirect to Og Checkout Secure page</div>';
			if( 'ogcheckout' === esc_attr( $this->id ) ){
				
				$all_gateways = WC()->payment_gateways->payment_gateways();
				$allowed_gateways = $all_gateways['ogcheckout'];
				$allowed_gateways = $allowed_gateways->settings;
			
				if( 'customize' == $allowed_gateways['paymentmethodmode'] ){
					echo '<div  class="customize_payment_box" style="padding:10px 0;">';	
					echo '<input type="hidden" name="payment_method_mode" value="custompaymentmethod" />';
					$all_methods = json_decode(get_option( 'customize_payment_data' ),true);
					$i=0;
					foreach($all_methods as $method){ if( 0 == $i ){ $chk="checked";}else{$chk="";}?>
					<div class="radio radio_payment">
					<label>
					<input type="radio" name="ogcheckout-methods" value="<?php echo esc_html( $method['code'] ); ?>" class="ogcheckout_methods" <?=$chk; ?> /><?php echo esc_html( $method['name'] ); ?>           </label>
					<span><img src="<?php echo esc_url( $this->image_baseurl ) . esc_html( $method['code'] ); ?>.png" width="50px"></span></div>
					<?php $i++;}
					echo '</div>';
				}else{
					echo '<input type="hidden" name="payment_method_mode" value="ogpaymentmethod" />';
				}
			}
		
			echo '<div class="clear"></div></fieldset>';
		
		}

		/**
		 * Callback for Process Payment.
		 *
		 * @since 1.0.0
		 * @param string $orderid  Order Id.
		 */
		public function process_payment( $order_id ) {
			
			$payment_method_mode = ! empty( $_POST['payment_method_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method_mode'] ) ) : '';
			$order = wc_get_order( $order_id );

			try {

				//code...
				if( ! empty( $payment_method_mode ) ) {
				
					global $woocommerce;
					$all_gateways = WC()->payment_gateways->payment_gateways();
					$allowed_gateways = $all_gateways['ogcheckout'];
					$settings = $allowed_gateways->settings;
					$merchantcode = $settings['merchantcode'];
					$secretkey = $settings['secretkey'];
					$authkey = $settings['authkey'];
					$endpoint = $settings['endpoint'];
					$mode = $settings['paymentmethodmode'];
					$ogpaymentmethod = $settings['ogpaymentmethod'];
					$ogpaymentcurrency = $settings['ogpaymentcurrency'];
					if(empty($settings['language'])){
						$language = 'en';
					}else{
						$language = $settings['language'];
					}
	
					if( 'ogpaymentmethod' == $payment_method_mode && 'ogpayment' == $mode ){
						$paymentMethodCode = $ogpaymentmethod;
						if(!empty($ogpaymentcurrency)){
							$paymentMethodCurrency = $ogpaymentcurrency;
						}else{
							$paymentMethodCurrency = get_woocommerce_currency();
						}
						$response = $this->getRedirectUrl($paymentMethodCode,$paymentMethodCurrency,$order_id);
					}
	
					if( 'custompaymentmethod' == $payment_method_mode && 'customize' == $mode ){
						$payment_channels = json_decode(get_option( 'customize_payment_data' ),true);
						$paymentCode = $payment_method_mode;
						foreach($payment_channels as $key=>$channel){
							if($channel['code']==$paymentCode){
								$paymentMethodCurrency = $channel['currency'];
							}
						}

						if(!empty($ogpaymentcurrency)){
							$paymentMethodCurrency = $ogpaymentcurrency;
						}else{
							$paymentMethodCurrency = get_woocommerce_currency();
						}

						$paymentMethodCode = ! empty( $_POST['ogcheckout-methods'] ) ? sanitize_text_field( wp_unslash( $_POST['ogcheckout-methods'] ) ) : '';
						$response = $this->getRedirectUrl($paymentMethodCode,$paymentMethodCurrency,$order_id);
					}	
	
					if(isset($response['success'])){
					return array(
						'result'    => 'success',
						'redirect'  => $response['url']
					);
					}else{
						wc_add_notice( $response['errorMessgae'], 'error' );
						$order->add_order_note( 'Error: '. $response['errorMessgae'] );			
						return array(
						'result'    => 'error',
							'redirect' => $this->get_return_url( $order )
						); 
						
					}
				}
			
			} catch (\Throwable $e) {
				throw new Exception( $e->getMessage(), 1);
			}
		}

		/**
		 * Callback for Redirect Url.
		 *
		 * @since 1.0.0
		 * @param string $paymentMethodCode Payment method code.
		 * @param string $paymentMethodCurrency  Payment method currency.
		 * @param string $orderid  Order Id.
		 */
		public function getRedirectUrl($paymentMethodCode,$paymentMethodCurrency,$order_id){
			global $woocommerce;

			$all_gateways = WC()->payment_gateways->payment_gateways();
			$allowed_gateways = $all_gateways['ogcheckout'];
			$settings = $allowed_gateways->settings;

			$merchantID = $settings['merchantcode'];
			$secretkey = $settings['secretkey'];
			$authKey = $settings['authkey'];
			$endpoint = $settings['endpoint'];
			$mode = $settings['paymentmethodmode'];
			$ogpaymentmethod = $settings['ogpaymentmethod'];
			$ogpaymentcurrency = $settings['ogpaymentcurrency'];
			$currency = get_woocommerce_currency();
			$callback = WC()-> api_request_url( 'ogcheckout' );
			if(empty($settings['language'])){
				$language = 'en';
			}else{
				$language = $settings['language'];
			}

			if($mode=='customize'){
				if($paymentMethodCurrency==$currency){
					$doConvert = "N";
					$sourceCurrency = "";
				}else{
					$doConvert = "Y";
					$sourceCurrency = $currency;		   
				}	       
			}

			if($mode=='ogpayment'){

				if(strcasecmp($ogpaymentmethod,"all")==0){
					$doConvert = "N";
					$sourceCurrency = "";	
					$paymentMethodCurrency = $currency;
				}else{
					if($paymentMethodCurrency==$currency){
						$doConvert = "N";
						$sourceCurrency = "";
					}else{
						$doConvert = "Y";
						$sourceCurrency = $currency;		   
					}	           
				}

			}


			$items = $woocommerce->cart->get_cart();
			$description = array();

			foreach ( $items as $item => $values ) {
				$_product =  wc_get_product( $values[ 'data' ]->get_id()); 
				$description[] = $_product->get_title();
			}

			$description = implode( ' | ',$description );

			$order = wc_get_order( $order_id );

			$order_data = $order->get_data();

			$ref=$this->generateRefrenceId($order_id); // set up a blank string.

			$timestamp = date("y/m/d H:m:s t");
			$tunnel = '';
			$userReference = $this->generateUserRefrenceId($order_data['billing']['phone']);
			$amount = $order_data['total'];

			$datatocomputeHash = (float)$amount.$authKey.$paymentMethodCurrency.$merchantID.$paymentMethodCode.(int)$ref.$sourceCurrency.$timestamp.$tunnel.(int)$userReference;

			$hash = strtoupper(hash_hmac("sha256", $datatocomputeHash,$secretkey));	

					$data = array(
						'merchantCode' => $merchantID,
						'authKey' => $authKey,
						'currency' => $paymentMethodCurrency,
						'pc'=> $paymentMethodCode,
						'tunnel'=> $tunnel,
						'amount'=> (float)$amount,
						'doConvert'=> $doConvert,
						'sourceCurrency'=> $sourceCurrency,
						'description'=> $description,
						'referenceID'=> (int)$ref,
						'timeStamp'=> $timestamp,
						'language'=> $language,
						'callbackURL'=> $callback,
						'hash'=> $hash,
						'userReference'=> (int)$userReference,
						'billingDetails'=> array(
							'fName'=> $order_data['billing']['first_name'],
							'lName'=> $order_data['billing']['last_name'],
							'mobile'=> $order_data['billing']['phone'],
							'email'=> $order_data['billing']['email'],
							'city'=> $order_data['billing']['city'],
							'pincode'=> $order_data['billing']['postcode'],
							'state'=> $order_data['billing']['state'],
							'address1'=> $order_data['billing']['address_1'],
							'address2'=> $order_data['billing']['address_1']
						),
				);	
				
				$request = json_encode($data,true);	
				if (!$endpoint) {
					$curl = 'https://ogcheckoutstage.oneglobal.com/OgPay/V1/api/GenToken/Validate';
				} else {
					$curl = $endpoint;
				}
				$headers = array(
					'Content-Type'  => 'application/json',
					'User-Agent'    => 'Your App Name (www.yourapp.com)',
					'Authorization' => 'Basic xxxxxx',
				);
				
				$respon = wp_remote_post($curl, array(
						'method' => 'POST',
						'timeout' => 45,
						'redirection' => 5,
						'httpversion' => '1.0',
						'sslverify' => false,
						'blocking' => true,
						'headers' => $headers,
						'body' => $request,
						'cookies' => array()
					)
				);					

					$returnParam = array();
					
					if ( !is_wp_error( $respon ) ) {
						if(isset($respon['body'])){
							$response = json_decode($respon['body'],true);
								if($response['errorCode']=='0'){
									$trackid = get_post_meta($order_id, 'track_id');
									if($trackid){
										update_post_meta($order_id, 'track_id',$ref);
									}else{
										add_post_meta($order_id, 'track_id',$ref);
									}					
									$returnParam['success'] = 'Y';
									$returnParam['url'] = $response['result']['redirectURL'];  	
								}else{
									$returnParam['error'] = 'Y';
									$returnParam['errorMessgae'] = $response['errorMessgae'];              
								}
						}

							
						
					}else{
						$returnParam['error'] = 'Y';
						$returnParam['errorMessgae'] = $response->get_error_message();  
					}
				

					return $returnParam;			

		}

		/**
		 * Callback for Reference Id.
		 *
		 * @since 1.0.0
		 * @param string $orderid  Order Id.
		 */
		public function generateRefrenceId($orderid){

			$digits_needed = 15;
			
			$orderid = time().$orderid;
			
			$length = strlen((int)$orderid);
			
			if($length<$digits_needed){
			
				$required = $digits_needed-$length;
				
				$id='';
				for ($i = 1; $i <= $required; $i++) {
					$id .= 1;
				}
				
				$refrenceId = $id.$orderid;
			}else{
				$refrenceId = $id.$orderid;
			}
			
			return (int)$refrenceId;
		}

		/**
		 * Callback for UserId Reference.
		 *
		 * @since 1.0.0
		 * @param string $uref   UserId Reference.
		 */
		public function generateUserRefrenceId($uref=""){

			$digits_needed = 10;
			$uref = preg_replace("/[^0-9]/", "", $uref);
			$length = strlen($uref);
			$id='';
			if($length<$digits_needed){
			
				$required = $digits_needed-$length;
				
				
				for ($i = 1; $i <= $required; $i++) {
					$id .= 1;
				}
				
				$refrenceId = $id.$uref;
			}else{
				$refrenceId = $id.$uref;
			}
			
			return (int)$refrenceId;
		}	

		/**
		 * Callback for Order.
		 *
		 * @since 1.0.0
		 * @param object $order             Main order.
		 */
		public function ogcheckoutCallback($order) { 
			global $woocommerce, $post;

			if (! empty( $_GET['trackid'] ) ) {
				$parts =  empty( $_SERVER['REQUEST_URI']  ) ? parse_url( $_SERVER['REQUEST_URI'] ) : '';
				$query =array();
				
				parse_str( $parts['query'], $query );
				foreach ( $query as $key => $value ) {
					$response[$key] = $value;
				}

			} else {
				$response = 0;
			}
			
			if( ! empty( $response ) ){

				$trackid = ! empty( $_GET['trackid'] ) ? sanitize_text_field( wp_unslash( $_GET['trackid'] ) ) : '';
				$all_gateways = WC()->payment_gateways->payment_gateways();
				$allowed_gateways = ! empty( $all_gateways['ogcheckout'] ) ? $all_gateways['ogcheckout'] : '';
				$settings = $allowed_gateways->settings;

				$merchantID = ! empty( $settings['merchantcode'] ) ? $settings['merchantcode'] : '';
				$secretkey = ! empty( $settings['secretkey'] ) ? $settings['secretkey'] : '';
				$authKey = ! empty( $settings['authkey'] ) ? $settings['authkey'] : '';
				$endpoint = ! empty( $settings['endpoint'] ) ? $settings['endpoint'] : '';
				$mode = ! empty( $settings['paymentmethodmode'] ) ? $settings['paymentmethodmode'] : '';
				$ogpaymentmethod = ! empty( $settings['ogpaymentmethod'] ) ? $settings['ogpaymentmethod'] : '';
				$ogpaymentcurrency = ! empty( $settings['ogpaymentcurrency'] ) ? $settings['ogpaymentcurrency'] : '';
				$currency = get_woocommerce_currency();
				$order_id = $this->get_order_id( 'track_id', $trackid );
				
				$order = wc_get_order($order_id);		
				$order_info = $order->get_data();

				if ( ! empty( $order_info ) ) {

					//Validate response data.
					$secretkey = ! empty( $settings['secretkey'] ) ? $settings['secretkey'] : '';
					$hash = ! empty( $response['Hash'] ) ? $response['Hash'] : '';
					$outParams = "trackid=".$trackid."&result=".$response['result']."&refid=".$response['refid'];
					$outHash = strtoupper(hash_hmac("sha256", $outParams,$secretkey));

					if( $hash==$outHash ){
					
						if ( ! empty( $response['result'] ) ) {
						
							switch( $response['result'] ) {
								case 'CAPTURED':

									$order->update_status( 'processing' ); // Processing.
									
									// this is important part for empty cart.
									$woocommerce->cart->empty_cart();
									// Redirect to thank you page.
									$url = $this->get_return_url( $order );						
									break;
								case 'NOT CAPTURED':
									$order->update_status( 'cancelled' ); // Missing.
									$url = wc_get_checkout_url();
									break;
								case 'DECLINED':
									$order->update_status( 'cancelled' ); // Denied.
									$url = wc_get_checkout_url();
									break;
								case 'REJECTED':
									$order->update_status( 'cancelled' ); // Denied.
									$url = wc_get_checkout_url();
									break;
								case 'BLOCKED':
									$order->update_status( 'cancelled' ); // Denied.
									$url = wc_get_checkout_url();
									break;
							}
						
						} else {
							$order->update_status( 'pending' );
							$url = wc_get_checkout_url();
						}
						
						$note = "Track Id: ".$trackid."\r\n Payment Status: ".$response['result'];
						$order->add_order_note($note);	
						wp_redirect( $url );
						exit;

					} else{
						$url = wc_get_checkout_url();
						wp_redirect( esc_url( $url ) );
						exit;				
					}
				}
			}		
		}	

		/**
		 * Get Order Id from meta query
		 *
		 * @since 1.0.0
		 * @param int $key             Main order id
		 * @param int $value               Is Upsell transaction
		 */
		public function get_order_id($key, $value) {
			global $wpdb;
			$meta = $wpdb->get_results("SELECT * FROM ".$wpdb->postmeta." WHERE meta_key='".$wpdb->escape($key)."' AND meta_value='".$wpdb->escape($value)."'");
			if ( !empty($meta) && is_array($meta) && ! empty( $meta[0] ) ) {
					$meta = ! empty( $meta[0] ) ? $meta[0] : array();
				}
			if (is_object($meta)) {
					return $meta->post_id;
				}
			else {
					return false;
				}
			}
			
		}
	}

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_ogcheckout() {

		$plugin = new Ogcheckout();
		$plugin->run();

	}
	run_ogcheckout();