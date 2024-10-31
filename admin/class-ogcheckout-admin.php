<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.oneglobal.com
 * @since      1.0.0
 *
 * @package    Ogcheckout
 * @subpackage Ogcheckout/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ogcheckout
 * @subpackage Ogcheckout/admin
 * @author     Oneglobal <info@oneglobal.com>
 */
class Ogcheckout_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ogcheckout_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ogcheckout_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ogcheckout-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ogcheckout_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ogcheckout_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ogcheckout-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the Gateway Class.
	 *
	 * @since    1.0.0
	 */
	public function add_gatewayclass() {
		$gateways[] = 'WC_Ogcheckout_Gateway'; // your class name is here
		return $gateways;
	}

	
	/**
	 * Register the Gateway Menu.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Og Checkout Payment Methods', 'Og Checkout Payment Methods' ),
			'Og Checkout',
			'manage_options',
			'ogcheckout_payment_gateway',
			array( $this, 'settings' ),
			'dashicons-admin-generic',
			13
		);
	}

	/**
	 * Register the Gateway Settings Panel.
	 *
	 * @since    1.0.0
	 */
	public function settings() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/ogcheckout-admin-display.php';
	}

	/**
	 * Save Submission of Payment Data.
	 *
	 * @since    1.0.0
	 */
	public function save_ogcheckout_form() {

		// Nonce verification.
		$ogcheckout_setting_nonce = ! empty( $_POST['ogcheckout_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['ogcheckout_nonce'] ) ) : '';

		if ( empty( $ogcheckout_setting_nonce ) || ! wp_verify_nonce( $ogcheckout_setting_nonce, 'ogcheckout_setting_nonce' ) ) {

			esc_html_e( 'Sorry, due to some security issue, your settings could not be saved. Please Reload the screen.', 'ogcheckout' );
			wp_die();
		}

		$customize_payment = ! empty( $_POST['customize_payment'] ) ?  $this->sanitize_recursive( wp_unslash( $_POST['customize_payment'] ) ) : array();

		$data = json_encode( $customize_payment, true );

		if( ! empty( $data ) ) {

			$all_methods = get_option( 'customize_payment_data' );
			if ( ! empty( $all_methods ) ) {
				update_option( 'customize_payment_data', $data );
			} else {
				add_option( 'customize_payment_data', $data );
			}
		}

		$url = get_site_url() . '/wp-admin/admin.php?page=ogcheckout_payment_gateway';
		wp_redirect( $url );
		exit;
	}

	/**
	 * Sanitization for associative array.
	 *
	 * @since    1.0.0
	 * @param    array $s    array to sanitize
	 */
	function sanitize_recursive($s) {
		if ( is_array( $s ) ) {
		  return(array_map( array( $this, 'sanitize_recursive' ), $s));
		} else {
		  return htmlspecialchars($s);
		}
	  }

}
