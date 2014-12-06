<?php
/*
	Plugin Name: Paga Woocommerce E-Pay
	Plugin URI: https://www.mypaga.com/
	Description: Paga E-Pay Plugin for Woocommerce
	Version: 1.1.0
	Author: Pagatech Limited
	Author URI: https://www.mypaga.com/
	License:  GPL-2.0+
 	License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! defined( 'ABSPATH' ) )
	exit;

add_action('plugins_loaded', 'woocommerce_paga_init', 0);

function woocommerce_paga_init() {

	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

	/**
 	 * Gateway class
 	 */
	class WC_Tbz_Paga_Gateway extends WC_Payment_Gateway {

		public function __construct(){
			global $woocommerce;

			$this->id 					= 'tbz_paga_gateway';
    		$this->icon 				= apply_filters('woocommerce_paga_icon', plugins_url( 'assets/pay-with-paga.png' , __FILE__ ) );
			$this->has_fields 			= false;
        	$this->method_title     	= 'pay with paga';
        	$this->method_description  	= 'your cash, anytime, anywhere';

			// Load the form fields.
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();

			// Define user set variables
			$this->title 					= $this->get_option( 'title' );
			$this->description 				= $this->get_option( 'description' );
			$this->paga_epay_code	 		= $this->get_option( 'paga_epay_code' );
			$this->testmode					= $this->get_option( 'testmode' );

			//Actions
			add_action('woocommerce_receipt_tbz_paga_gateway', array($this, 'receipt_page'));
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// Payment listener/API hook
			add_action( 'woocommerce_api_wc_tbz_paga_gateway', array( $this, 'check_paga_response' ) );
		}

        /**
         * Admin Panel Options
         **/
        public function admin_options(){
            echo '<h3>Paga Payment Gateway</h3>';
            echo '<p>Paga Payment Gateway allows you to accept payment on your Woocommerce Powered Store Using Paga</p>';
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }


	    /**
	     * Initialise Gateway Settings Form Fields
	    **/

		function init_form_fields(){

			$this->form_fields = array(
				'enabled' => array(
					'title' 			=> 	'Enable/Disable',
					'type' 				=> 	'checkbox',
					'label' 			=>	'Enable Paga Payment Gateway',
					'description' 		=> 	'Enable or disable the gateway.',
            		'desc_tip'      	=> 	true,
					'default' 			=> 	'yes'
				),
				 'title' => array(
					'title' 			=> 	'Title',
						'type' 			=> 	'text',
						'description' 	=> 	'This controls the title which the user sees during checkout.',
            			'desc_tip'      => 	false,
						'default' 		=>  'pay with paga'
					),
				'description' => array(
					'title' 		=> 	'Description',
					'type' 			=> 	'textarea',
					'description' 	=> 	'This controls the description which the user sees during checkout.',
					'default' 		=> 	'your cash, anytime, anywhere'
				),
				'paga_epay_code' => array(
					'title' 		=> 	'Paga Merchant Key',
					'type' 			=> 	'text',
					'description' 	=> 	'Enter your Paga merchant key here' ,
					'default' 		=> 	'',
        			'desc_tip'     		=> 	false
				),
				'return_url' => array(
					'title' 		=> 	'Return URL',
					'type' 			=> 	'text',
					'description' 	=> 	'This URL should be copied and put in the Payment notification URL field under the Merchant Information section in the E-Pay Set-up area under your Paga Merchant account.',
					'desc_tip'      => 	false,
					'disabled'		=>  true,
					'css' 			=> 'min-width: 550px !important; color: #000; background: #fff; color: red',
					'default' 		=>  WC()->api_request_url( 'WC_Tbz_Paga_Gateway' )
				),
				'testing' => array(
					'title'       	=> 'Gateway Testing',
					'type'        	=> 'title',
					'description' 	=> '',
				),
				'testmode' => array(
					'title'       		=> 'Test Mode',
					'type'        		=> 'checkbox',
					'label'       		=> 'Enable Test Mode',
					'default'     		=> 'no',
					'description' 		=> 'Test mode enables you to test payments before going live. <br />If you ready to start receving payment on your site, kindly uncheck this.',
				)
			);
		}



		/**
		 * Get payment args for passing to paga
		**/
		function get_paga_args( $order ) {
			global $woocommerce;

			$order_id 			= $order->id;

			$order_total		= $order->get_total();
			$description       	= "Payment for Order ID: $order_id on ". get_bloginfo('name');
			$return_url 		= WC()->api_request_url( 'WC_Tbz_Paga_Gateway' );
			$email  			= $order->billing_email;
			$phone_number		= $order->billing_phone;

			if ( 'yes' == $this->testmode ) {
        		$test = "true";
			} else {
				$test = "false";
			}

			// paga Args
			$paga_args = array(
				'description'		=> $description,
				'subtotal' 			=> $order_total,
				'invoice'			=> $order_id,
				'email'				=> $email,
				'return_url'		=> $return_url,
				'test' 				=> $test
			);

			$paga_args = apply_filters( 'woocommerce_paga_args', $paga_args );
			return $paga_args;
		}


	    /**
		 * Generate the Paga Payment button link
	    **/
	    function generate_paga_form( $order_id ) {
			global $woocommerce;

			$order = new WC_Order( $order_id );

			$paga_args = $this->get_paga_args( $order );

			$paga_epay_code = $this->paga_epay_code;

			$paga_args_array = array();

			foreach ($paga_args as $key => $value) {
				$paga_args_array[] = '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" />';
			}

			return '<form action="" method="post" id="paga_payment_form" target="_top">
					' . implode('', $paga_args_array) . '
				</form>
					<!-- begin Paga ePay widget code -->
					<script type="text/javascript" src="https://www.mypaga.com/paga-web/epay/ePay-start.paga?k='.$paga_epay_code.'&e=false&layout=H"> </script>
					<!-- end Paga ePay widget code -->
					<a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__('Cancel order &amp; restore cart', 'woocommerce').'</a>'
				;

		}


	    /**
	     * Process the payment and return the result
	    **/
		function process_payment( $order_id ) {

			$order = new WC_Order( $order_id );
	        return array(
	        	'result' 	=> 'success',
				'redirect'	=> $order->get_checkout_payment_url( true )
	        );

		}


	    /**
	     * Output for the order received page.
	    **/
		function receipt_page( $order ) {
			echo '<p>'.__('Thank you for your order, please click the payment method you want to use below to make payment.', 'woocommerce').'</p>';
			echo $this->generate_paga_form( $order );
		}


		/**
		 * Process Payment!
		**/
		function check_paga_response( $posted ){

			global $woocommerce;

            if( $_POST['merchant_key'] == $this->paga_epay_code ){

				$transaction_id = $_POST['transaction_id'];
				$order_id 		= $_POST['invoice'];
				$order_id 		= (int) $order_id;

                $order 			= new WC_Order($order_id);

		        $order_total	= $order->get_total();

				$amount_paid 	= $_POST['amount'];

	            //after payment hook
                do_action('tbz_paga_woo_after_payment', $_POST, $order );

				// check if the amount paid is equal to the order amount.
				if( $order_total != $amount_paid )
				{

	                //Update the order status
					$order->update_status('on-hold', '');

					//Error Note
					$message = 'Thank you for shopping with us.<br />Your payment transaction was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
					$message_type = 'notice';

					//Add Customer Order Note
                    $order->add_order_note( $message.'<br />Paga Transaction ID: '.$transaction_id, 1 );

                    //Add Admin Order Note
                    $order->add_order_note( 'This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was: &#8358; '.$amount_paid.' while the total order amount is: &#8358; '.$order_total.'<br />Paga Transaction ID: '.$transaction_id );

					// Reduce stock levels
					$order->reduce_order_stock();

					// Empty cart
					WC()->cart->empty_cart();
				}
				else
				{
	                if($order->status == 'processing')
	                {

	                    $order->add_order_note('Payment Via Paga<br />Transaction ID: '.$transaction_id);

	                    //Add customer order note
	 					$order->add_order_note("Payment Received.<br />Your order is currently being processed.<br />We will be shipping your order to you soon.< br />Paga Transaction ID: '.$transaction_id", 1);

						// Reduce stock levels
						$order->reduce_order_stock();

						// Empty cart
						WC()->cart->empty_cart();

						//Transaction Status Message
						$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is currently being processed.';
						$message_type = 'success';
	                }
	                else
	                {

	                	if( $order->has_downloadable_item() ){

							$order->update_status( 'completed', 'Payment received, your order is now complete.' );

		                    $order->add_order_note( 'Payment Via Paga<br />Transaction ID: '.$transaction_id );

		                    //Add customer order note
		 					$order->add_order_note( "Payment Received.<br />Your order is now complete.<br />We will be shipping your order to you soon.", 1 );

							// Reduce stock levels
							$order->reduce_order_stock();

							// Empty cart
							WC()->cart->empty_cart();

							//Transaction Status Message
							$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is now complete.';
							$message_type = 'success';
						}

						else{

							$order->update_status( 'processing', 'Payment received, your order is currently being processed.' );

		                    $order->add_order_note( 'Payment Via Paga<br />Transaction ID: '.$transaction_id );

		                    //Add customer order note
		 					$order->add_order_note( "Payment Received.<br />Your order is currently being processed.<br />We will be shipping your order to you soon.", 1 );

							// Reduce stock levels
							$order->reduce_order_stock();

							// Empty cart
							WC()->cart->empty_cart();

							//Transaction Status Message
							$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is currently being processed.';
							$message_type = 'success';
						}

	                }
                }

                $paga_message = array(
                	'message'		=> $message,
                	'message_type' 	=> $message_type
                );

				if ( version_compare( WOOCOMMERCE_VERSION, "2.2" ) >= 0 ) {
					add_post_meta( $order_id, '_transaction_id', $transaction_id, true );
				}

				update_post_meta( $order_id, '_tbz_paga_message', $paga_message );

                die( 'IPN Processed OK. Payment Successfully' );
            }

            else{

				if( isset( $_POST['status'] ) )
				{

					$transaction_status = $_POST['status'];
					$transaction_id 	= $_POST['transaction_id'];
					$order_id 			= $_POST['invoice'];
					$order_id 			= (int) $order_id;

			        $order 				= new WC_Order($order_id);

					$paga_message 		= get_post_meta( $order_id, '_tbz_paga_message', true );

					if( ! empty( $paga_message) ){

						$message 		= $paga_message['message'];
						$message_type 	= $paga_message['message_type'];
						delete_post_meta( $order_id, '_tbz_paga_message' );
						wc_add_notice( $message, $message_type );

		            	$redirect_url 	= esc_url( $this->get_return_url( $order ) );
			            wp_redirect( $redirect_url );
			            exit;
					}

					else{

						$transaction_message = $this->get_transaction_message( $transaction_status );

						if( $transaction_status == 'SUCCESS' )
						{

					        $order_total	= $order->get_total();

							$amount_paid 	= $_POST['total'];

				            //after payment hook
			                do_action('tbz_paga_woo_after_payment', $_POST, $order );

							// check if the amount paid is equal to the order amount.
							if( $order_total != $amount_paid )
							{

				                //Update the order status
								$order->update_status('on-hold', '');

								//Error Note
								$message = 'Thank you for shopping with us.<br />Your payment transaction was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
								$message_type = 'notice';

								//Add Customer Order Note
			                    $order->add_order_note( $message.'<br />Paga Transaction ID: '.$transaction_id, 1 );

			                    //Add Admin Order Note
			                    $order->add_order_note( 'This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was: &#8358; '.$amount_paid.' while the total order amount is: &#8358; '.$order_total.'<br />Paga Transaction ID: '.$transaction_id );

								// Reduce stock levels
								$order->reduce_order_stock();

								// Empty cart
								WC()->cart->empty_cart();
							}
							else
							{
				                if($order->status == 'processing')
				                {

				                    $order->add_order_note( 'Payment Via Paga<br />Transaction ID: '.$transaction_id );

				                    //Add customer order note
				 					$order->add_order_note( "Payment Received.<br />Your order is currently being processed.<br />We will be shipping your order to you soon.< br />Paga Transaction ID: '.$transaction_id", 1) ;

									// Reduce stock levels
									$order->reduce_order_stock();

									// Empty cart
									WC()->cart->empty_cart();

									//Transaction Status Message
									$message = 'Thank you for shopping with us.<br />'.$transaction_message.'<br />Your order is currently being processed.';
									$message_type = 'success';
				                }
				                else
				                {

				                	if( $order->has_downloadable_item() ){

										$order->update_status( 'completed', 'Payment received, your order is now complete.');

					                    $order->add_order_note( 'Payment Via Paga<br />Transaction ID: '.$transaction_id );

					                    //Add customer order note
					 					$order->add_order_note( "Payment Received.<br />Your order is now complete.<br />We will be shipping your order to you soon.", 1 );

										// Reduce stock levels
										$order->reduce_order_stock();

										// Empty cart
										WC()->cart->empty_cart();

										//Transaction Status Message
										$message = 'Thank you for shopping with us.<br />'.$transaction_message.'<br />Your order is now complete.';
										$message_type = 'success';
									}

									else{

										$order->update_status( 'processing', 'Payment received, your order is currently being processed.' );

					                    $order->add_order_note( 'Payment Via Paga<br />Transaction ID: '.$transaction_id );

					                    //Add customer order note
					 					$order->add_order_note( "Payment Received.<br />Your order is currently being processed.<br />We will be shipping your order to you soon.", 1 );

										// Reduce stock levels
										$order->reduce_order_stock();

										// Empty cart
										WC()->cart->empty_cart();

										//Transaction Status Message
										$message = 'Thank you for shopping with us.<br />'.$transaction_message.'<br />Your order is currently being processed.';
										$message_type = 'success';
									}

				                }
			                }

							if ( version_compare( WOOCOMMERCE_VERSION, "2.2" ) >= 0 ) {
								add_post_meta( $order_id, '_transaction_id', $transaction_id, true );
							}
						}

			            else
			            {
							$message =  'Thank you for shopping with us. <br />However, the transaction wasn\'t successful, payment wasn\'t received.<br />'.$transaction_message;
							$message_type 	= 'error';

							//Add Customer Order Note
		                   	$order->add_order_note( $message, 1 );

		                    //Add Admin Order Note
		                  	$order->add_order_note( $message );

			                //Update the order status
							$order->update_status('failed', '');
			            }
					}

					wc_add_notice( $message, $message_type );
				}
				else
				{
					$message =  'Thank you for shopping with us. <br />However, the transaction wasn\'t successful, payment wasn\'t recieved.';
					$message_type 	= 'error';
					wc_add_notice( $message, $message_type );

					$redirect_url = get_permalink( wc_get_page_id('myaccount') );
		            wp_redirect( $redirect_url );
		            exit;
				}

            	$redirect_url = esc_url( $this->get_return_url( $order ) );
	            wp_redirect( $redirect_url );
	            exit;
            }
		}


		/**
	 	* Get Payment Transaction Status
	 	**/
		function get_transaction_message( $transaction_status ){

			switch ($transaction_status) {
				case 'SUCCESS':
					$message = 'Payment Transaction was Successfull';
					break;
				case 'ERROR_TIMEOUT':
					$message = 'Payment Transaction Timeout. Try again later.';
					break;
				case 'ERROR_INSUFFICIENT_BALANCE':
					$message = 'Insuccient balance in your account';
					break;
				case 'ERROR_INVALID_CUSTOMER_ACCOUNT':
					$message = 'Invalid Customer Account';
					break;
				case 'ERROR_CANCELLED':
					$message = 'Transaction was cancelled.';
					break;
				case 'ERROR_BELOW_MINIMUM':
					$message = 'The order amount is below the minimum allowed. <br />Contact the merchant.';
					break;
				case 'ERROR_ABOVE_MAXINUM':
					$message = 'The order amount is above the maximum allowed. <br />Contact the merchant.';
					break;
				case 'ERROR_AUTHENTICATION':
					$message= 'Invalid Login Details';
					break;
				case 'ERROR_UNKNOWN':
					$message = 'Transaction Failed. Kindly Try again';
					break;

				default:
					$message = 'Transaction Failed. Kindly Try again';
					break;
			}
            return $message;
		}
	}

	/**
 	* Add the Gateway to WooCommerce
 	**/
	function woocommerce_add_paga_gateway($methods) {
		$methods[] = 'WC_Tbz_Paga_Gateway';
		return $methods;
	}
	add_filter('woocommerce_payment_gateways', 'woocommerce_add_paga_gateway' );


	/**
	 * only add the naira currency and symbol if WC versions is less than 2.1
	 */
	if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) <= 0 ) {

		/**
		* Add NGN as a currency in WC
		**/
		add_filter( 'woocommerce_currencies', 'tbz_add_my_currency' );

		if( ! function_exists( 'tbz_add_my_currency' )){
			function tbz_add_my_currency( $currencies ) {
			     $currencies['NGN'] = __( 'Naira', 'woocommerce' );
			     return $currencies;
			}
		}

		/**
		* Enable the naira currency symbol in WC
		**/

		add_filter('woocommerce_currency_symbol', 'tbz_add_my_currency_symbol', 10, 2);

		if( ! function_exists( 'tbz_add_my_currency_symbol' ) ){
			function tbz_add_my_currency_symbol( $currency_symbol, $currency ) {
			     switch( $currency ) {
			          case 'NGN': $currency_symbol = '&#8358; '; break;
			     }
			     return $currency_symbol;
			}
		}
	}

	/**
	* Add Settings link to the plugin entry in the plugins menu for WC below 2.1
	**/
	if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) <= 0 ) {

		add_filter('plugin_action_links', 'tbz_paga_plugin_action_links', 10, 2);

		function tbz_paga_plugin_action_links($links, $file) {
		    static $this_plugin;

		    if (!$this_plugin) {
		        $this_plugin = plugin_basename(__FILE__);
		    }

		    if ($file == $this_plugin) {
		        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_Tbz_Paga_Gateway">Settings</a>';
		        array_unshift($links, $settings_link);
		    }
		    return $links;
		}
	}
	/**
	* Add Settings link to the plugin entry in the plugins menu for WC 2.1 and above
	**/
	else{
		add_filter('plugin_action_links', 'tbz_paga_plugin_action_links', 10, 2);

		function tbz_paga_plugin_action_links($links, $file) {
		    static $this_plugin;

		    if (!$this_plugin) {
		        $this_plugin = plugin_basename(__FILE__);
		    }

		    if ($file == $this_plugin) {
		        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wc-settings&tab=checkout&section=wc_tbz_paga_gateway">Settings</a>';
		        array_unshift($links, $settings_link);
		    }
		    return $links;
		}
	}

	/**
 	* Display the testmode notice
 	**/
	function tbz_wc_paga_testmode_notice(){
		$tbz_paga_settings = get_option( 'woocommerce_tbz_paga_gateway_settings' );

		$paga_test_mode = $tbz_paga_settings['testmode'];

		if ( 'yes' == $paga_test_mode ) {

		$settings_link = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wc-settings&tab=checkout&section=wc_tbz_paga_gateway';

	    ?>
		    <div class="update-nag">
		        Paga testmode is still enabled, remember to disable it when you want to start accepting live payment on your site. You can do so <a href="<?php echo $settings_link; ?>">here</a>
		    </div>
	    <?php
		}
	}
	add_action( 'admin_notices', 'tbz_wc_paga_testmode_notice' );
}