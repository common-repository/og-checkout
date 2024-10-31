<?php
/**
 * The admin-specific settings include of the gateway.
 *
 * @link       www.oneglobal.com
 * @since      1.0.0
 *
 * @package    Ogcheckout
 * @subpackage Ogcheckout/admin
 */

/**
 * Initialize the gateway class.
 *
 * @since    1.0.0
 */
function ogcheckout_init_gateway_class() {

	/**
	 * The admin-specific settings include of the gateway.
	 *
	 * @package    Ogcheckout
	 * @subpackage Ogcheckout/admin
	 * @author     Oneglobal <info@oneglobal.com>
	 */
	class WC_Ogcheckout_Gateway extends WC_Payment_Gateway {

		/**
		 * Class constructor, more about it in Step 3
		 */
		public function __construct() {

			$this->id = 'ogcheckout'; // payment gateway plugin ID.
			$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name.
			$this->has_fields = true; // in case you need a custom credit card form.
			$this->method_title = 'Ogcheckout';
			$this->method_description = 'Description of Ogcheckout payment gateway'; // will be displayed on the options page.

			// Gateways can support subscriptions, refunds, saved payment methods.
			// but in this tutorial we begin with simple payments.
			$this->supports = array(
				'products',
			);

			// Method with all the options fields.
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();
			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled = $this->get_option( 'enabled' );
			$this->testmode = 'yes' === $this->get_option( 'testmode' );
			$this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
			$this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );

			// This action hook saves the settings.
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// We need custom JavaScript to obtain a token.
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		}

		/**
		 * Plugin options, we deal with it in Step 3 too
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'       => 'Enable/Disable',
					'label'       => 'Enable Ogchekout Gateway',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				),
				'title' => array(
					'title'       => 'Title',
					'type'        => 'text',
					'description' => 'This controls the title which the user sees during checkout.',
					'default'     => 'Credit Card',
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => 'Description',
					'type'        => 'textarea',
					'description' => 'This controls the description which the user sees during checkout.',
					'default'     => 'Pay with your credit card via our super-cool payment gateway.',
				),

				'merchantcode' => array(
					'title'       => 'Merchant Name',
					'type'        => 'text',
					'required'    => true,
					'default'     => '',
					'desc_tip'    => true,
				),
				'authkey' => array(
					'title'       => 'Auth Key',
					'type'        => 'text',
					'default'     => '',
					'desc_tip'    => true,
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
					'desc_tip'    => true,
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
					'class'       => array( 'input-select' ),
					'options'     => array(
						'customize' => 'Customize Payment Form',
						'ogpayment' => 'Ogpayment Payment Form',
					),
					'required'    => true,
				),
			);
		}
	}
}

