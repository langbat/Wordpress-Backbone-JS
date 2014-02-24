<?php

require_once 'et-payment.php';
require_once 'et-2CO.php';
require_once 'et-google-checkout.php';
require_once 'et-cash.php';
require_once 'et-order.php';

require_once 'gc_xmlbuilder.php';
require_once 'et-authorize.php';
require_once 'Paypal_class.php';

abstract class ET_PaymentVisitor {

    protected $_order;
    protected $_settings;
    protected $_payment_type;

    function __construct(ET_Order $order) {

        $this->_order = clone $order;
    }

    function set_order($order = array()) {
        $this->_order = $order;
    }

    function add_return_param() {
        global $wp_rewrite;

        $link = et_get_page_link('process-payment', array('paymentType' => $this->_payment_type));
        foreach ($this->_settings as $key => $value)
            $this->_settings[$key] = $link;

        return $this->_settings;
    }

    function set_settings($args = array()) {
        $default_settings = array(
            'return' => 'http://localhost',
            'cancel' => 'http://localhost',
            'continue_shop' => 'http://localhost'
        );

        $settings = wp_parse_args($args, $default_settings);
        $this->_settings = $settings;
        $this->add_return_param();
    }

    abstract function setup_checkout(ET_Order $order);

    abstract function do_checkout(ET_Order $order);
}

class ET_InvalidVisitor extends ET_PaymentVisitor {

    function setup_checkout(ET_Order $order) {
        return array('ACK' => false, 'msg' => __("Invalid payment gateway", ET_DOMAIN));
    }

    function do_checkout(ET_Order $order) {
        return array('ACK' => false, 'msg' => __("Invalid payment gateway", ET_DOMAIN));
    }

}

class ET_PaypalVisitor extends ET_PaymentVisitor {

    protected $_payment_type = 'paypal';

    function setup_checkout(ET_Order $order) {
//        echo '<pre>';
//        var_dump($order);
//        exit;
        $mail_method = $order->_send_mail_method;
        do_action('et_email_send_admin', $mail_method);

        //$order				=	clone	$this->_order;
        $order = $order->generate_data_to_pay();

        $settings = $this->_settings;
        $payment = new ET_Paypal($settings, 1);

//        echo '<pre>';
//        var_dump($settings);
//        exit;
        $url = $settings['return'];
        $cancel_url = $settings['cancel'];

        $currencyCodeType = isset($order['currencyCodeType']) ? $order['currencyCodeType'] : '';

        $pro = isset($order['products']) ? $order['products'] : array();
        $products = "";
        $itemamt = 0.00;
        $i = 1;

        /* general product string add to paypal url
          if( !empty ($pro ) ) {
          foreach ($pro as $key => $value) {
          $products	.=	"&item_name_$i=".$value['NAME'];
          $products	.=	"&amount_$i=".$value['AMT'];
          //$products	.=	"&item_number_$i=".$value['QTY'];
          $itemamt	+=	doubleval($value['AMT']*$value['QTY']);
          $i++;
          }
          } */
        /*
        //// item order
        $products .= "&item_name_1=Bestellnumber " . $order['ID'];
        $products .= "&amount_1=" . $order['total'];
        //// item vat
        $products .= "&item_name_2=MWSt (19%)";
        $products .= "&amount_2=" . number_format($order['total'] * 0.19, 2, '.', '');

        $products .= '&lc=DE&image_url=' . get_bloginfo('url') . '/wp-content/uploads/logo-paypal.png&upload=1';

        $total = ''; // '&amount='.($order['total']*1.19);


        $returnURL = '&return=' . urlencode($url) . '&notify_url=' . urlencode($url);
        $cancelURL = '&cancel_return=' . urlencode("$cancel_url");
        $currency = '&currency_code=' . $currencyCodeType;
        $nvpstr = $returnURL . $cancelURL . $products . $total . $currency;        
        return array('url' => $payPalURL, 'ACK' => true, 'extend' => false);
        */
        $paypal = new Paypal_class(et_get_payment_test_mode());
        
        $paypal->returnUrl = $url;
        $paypal->cancelUrl = $cancel_url;
        $paypal->currency = $currencyCodeType;
        
        $amt = number_format($order['total']*1.19, 2, '.', '');
        $params = array(
            'L_PAYMENTREQUEST_0_NAME0'=>urlencode("Bestellnumber " . $order['ID']),
            'L_PAYMENTREQUEST_0_AMT0'=>$order['total'],
            
            'L_PAYMENTREQUEST_0_NAME1'=>urlencode('MWSt (19%)'),
            'L_PAYMENTREQUEST_0_AMT1'=>number_format($order['total'] * 0.19, 2, '.', ''),
            
            
            'PAYMENTREQUEST_0_AMT'=>$amt,
            'PAYMENTREQUEST_0_CURRENCYCODE'=>$currencyCodeType,
            'PAYMENTREQUEST_0_PAYMENTACTION'=>'Sale',
            'LOCALECODE' => 'DE'
        );
        
        $result = $paypal->SetExpressCheckout($params);
		if(!$paypal->isCallSucceeded($result)){ 
			die ($result['L_LONGMESSAGE0']);
			
		}else { 
			// send user to paypal 
			$token = urldecode($result["TOKEN"]); 
			
			$payPalURL = $paypal->paypalUrl.$token; 
			
            return array('url' => $payPalURL, 'ACK' => true, 'extend' => false);
		}

    }
    
    function do_checkout(ET_Order $order) {

        $order_pay = clone $order;


        $payment = new ET_Paypal();

        $order = $order_pay->generate_data_to_pay();
        
        $paypal = new Paypal_class(et_get_payment_test_mode());
        $token = trim($_GET['token']);		
		$result = $paypal->GetExpressCheckoutDetails($token);
        
        $result['TOKEN'] = $token; 
		$result['ORDERTOTAL'] = number_format($order['total']*1.19, 2, '.', '');
        
        if($paypal->isCallSucceeded($result)){ 			
			$paymentResult = $paypal->DoExpressCheckoutPayment($result);
            
	       	if($paypal->isCallSucceeded($paymentResult)){
                $message = "Sie haben eine Zahlungsaufforderung mit allen Details per E-Mail bekommen.  Sobald Ihre Zahlung bei uns eingegangen ist, wird Ihr Stellenangebot freigeschaltet.";

                do_action('et_cash_checkout', compact('message', 'order'));
                //do_action('et_invoice_payment', $order);    
    
                $order_pay->set_payment_code($paymentResult['TRANSACTIONID']);
                $order_pay->set_payer_id($result['EMAIL']);
                
                $session = et_read_session();
                
                if (isset($session['is_payment_zertifikat']) && $session['is_payment_zertifikat']){
                    global $user_ID;
                    setUserZerfifikat($user_ID);
                }
                // check $mc_gross, $mc_currency and receiver email are match with paid order and setting
                if ($order_pay->get_status() == 'pending') {
                    //if ($paymentResult['PAYMENTSTATUS'] == 'Completed')
                        //$order_pay->set_status('publish');
                    //else
                        //$order_pay->set_status('pending');
                        
                    $order_pay->set_status('publish');
                    
                    $order_pay->update_order();
                    
                    return array(
                        'ACK' => true,
                        'payment' => 'simplePaypal',
                        'payment_status' => $paymentResult['PAYMENTSTATUS']
                    );
                } else {
                    return array(
                        'ACK' => false,
                        'payment' => 'simplePaypal',
                        'msg' => $paymentResult['PENDINGREASON'],
                    );
                }
			}
            else{
                
            }
		}
    }

}

class ET_2COVisitor extends ET_PaymentVisitor {

    protected $_payment_type = '2checkout';

    function setup_checkout(ET_Order $order) {
        $_2co_payment = new ET_2CO ();
        $order_pay = clone $order;

        $order = $order_pay->generate_data_to_pay();
        extract($order);

        $pro_str = "";

        if (!empty($products)) {
            foreach ($products as $key => $value) {
                $k = $key;
                $pro_str .= "&c_prod_$k=" . urlencode($value['ID']);
                $pro_str .= "&c_name_$k=" . urldecode($value['NAME']);
                $pro_str .= "&c_description_$k=" . urldecode($value['L_DESC']);

                //$pro_str	.=	"&c_price_$k=".urlencode($value['AMT']);
            }
            $pro_str .= "&cart_order_id=" . $order_name;
        }
        $payer = new WP_User($order['payer']);

        $first_name = get_user_meta($payer->ID, 'first_name', true);
        $last_name = get_user_meta($payer->ID, 'last_name', true);

        $buyer = array(
            'name' => $payer->display_name,
            'street_address' => '',
            'city' => '',
            'state' => '',
            'country' => '', // use country postal code
            'email' => $payer->user_email,
            'phone' => ''
        );

        extract($buyer);
        /*
         * generate buyer info to send to 2checkout
         */
        $buyer_str = '';
        $buyer_str .= '&card_holder_name=' . urlencode($name);
        $buyer_str .= '&street_address=' . urlencode($street_address);
        $buyer_str .= '&city=' . urlencode($city);
        $buyer_str .= '&country=' . urlencode($country);
        $buyer_str .= '&email=' . urlencode($email);
        $buyer_str .= '&phone=' . urlencode($phone);

        /*
         * ship detail
         */
        $shipping = array(
            'ship_name' => 'Dakachi orime',
            'ship_street_address' => '52 Hoa Hong',
            'ship_city' => 'Ho Chi Minh',
            'ship_state' => '',
            'ship_zip' => '',
            'ship_country' => 'VNM'
        );

        $shipping = array();
        $ship_str = '';
        extract($shipping);
        if (!empty($shipping)) {

            $ship_str .= '&ship_name=' . urlencode($ship_name);
            $ship_str .= '&ship_street_address=' . urlencode($ship_street_address);
            $ship_str .= '&ship_city=' . urlencode($ship_city);
            $ship_str .= '&ship_state=' . urlencode($ship_state);
            $ship_str .= '&ship_zip=' . urlencode($ship_zip);
            $ship_str .= '&ship_country=' . urlencode($ship_country);
        }
        if ($total == '' || $ID == '') {
            return array(
                'ACK' => false,
                'error_msg' => 'Invalid argument cart order id',
            );
        }
        $return = "&x_receipt_link_url=" . $this->_settings['return'];

        $nvpstr = "&total=" . urlencode($total) . "&cart_order_id=" . urlencode($ID) . "&id_type=1" . $buyer_str . $ship_str . $pro_str . $return;

        $location = $_2co_payment->set_checkout($nvpstr, 'SetExpressCheckout');

        return array(
            'ACK' => true,
            'url' => $location['url'],
            'extend' => false
        );
    }

    function do_checkout(ET_Order $order) {
        $order_pay = clone $order;
        $order_pay->set_payment_code($_REQUEST['order_number']);
        $order = $order_pay->generate_data_to_pay();



        if ($order['payment_code'] == '') {
            return false;
        }


        /* The MD5 hash is provided to help you verify the authenticity of a sale. 
         * This is especially useful for vendors that sell downloadable products, or e-goods, 
         * as it can be used to verify whether sale actually came from 2Checkout and was a legitimate live sale. 
         * We intentionally break the hash code for demo orders so that you can compare the hash we provide with 
         * what it should be to determine whether or not to provide the customer with your goods.
         */
        $payment = new ET_2CO ();
        $md5_key = ( $payment->md5($order['payment_code'], $order['total']));

        if ($md5_key == $_REQUEST['key']) {
            // do something update order
            $order_pay->set_status('publish');
            $order_pay->update_order();

            return array(
                'ACK' => true,
                'payment' => '2checkout'
            );
        } else {
            if ($payment->get_mode() == 'Y') {
                $order_pay->set_status('publish');
                $order_pay->update_order();
                return array(
                    'ACK' => false,
                    'test_mode' => true,
                    'payment' => '2checkout',
                    'reponse' => array(
                        'S_MESSAGE' => __("MD5 False", ET_DOMAIN),
                        'L_MESSAAGE' => __("In  Demo mode the 2Checkout order number is changed to '1' which will cause the hash to fail this. This is intentional so that merchants selling downloadable products and services can compare the hash to determine whether to provide a product to a customer", ET_DOMAIN)
                    ),
                    'payment_status' => 'Completed'
                );
            } else {
                return array(
                    'ACK' => false,
                    'payment' => '2checkout',
                    'reponse' => array(
                        'S_MESSAGE' => __("MD5 False", ET_DOMAIN),
                        'L_MESSAAGE' => __("Md5 check sum not match!", ET_DOMAIN)
                    ),
                    'payment_status' => 'error'
                );
            }
        }
    }

}

class ET_GoogleVisitor extends ET_PaymentVisitor {

    protected $_payment_type = 'google_checkout';

    function setup_checkout(ET_Order $order) {

        $order = clone $order;
        $order = $order->generate_data_to_pay();
        $setting = $this->_settings;

        $payment = new ET_GoogleCheckout ();

        $currencyCodeType = isset($order['currencyCodeType']) ? $order['currencyCodeType'] : '';

        $pro = isset($order['products']) ? $order['products'] : array();
        $products = "";
        $itemamt = 0.00;
        $i = 0;

        if (isset($order['total_before_discount']) && $order['total_before_discount'] > $order['total']) {

            $pro[] = array(
                'NAME' => __("Discount", ET_DOMAIN),
                'L_DESC' => __("You have use a coupon code to get discount", ET_DOMAIN),
                'AMT' => $order['total'] - $order['total_before_discount'],
                'QTY' => 1
            );
        }
        // set digital url 
        $key = $payment->get_digital_key($order['ID']);



        $url = $setting['return'];

        $returnURL = ($url . '&token=' . $key);


        // general product string add to paypal url
        if (!empty($pro)) {
            $xml_data = new gc_XmlBuilder();

            $xml_data->Push('checkout-shopping-cart', array('xmlns' => GOOGLE_SCHEMA_URL));
            $xml_data->Push('shopping-cart');

            $xml_data->Push('items');
            foreach ($pro as $key => $value) {
                $xml_data->Push('item');

                $xml_data->Element('item-name', $value['NAME']);
                $xml_data->Element('item-description', $value['L_DESC']);
                $xml_data->Element('unit-price', $value['AMT'], array('currency' => $order['currencyCodeType']));
                $xml_data->Element('quantity', $value['QTY']);

                $xml_data->Push('digital-content');
                // digital url
                $xml_data->Element('url', $returnURL);
                // description when order success
                $xml_data->element('description', substr('You have completed order!', 0, 40));
                $xml_data->Pop('digital-content');

                $xml_data->Pop('item');
            }

            $xml_data->Pop('items');
        }

        $xml_data->Pop('shopping-cart');

        $xml_data->Push('checkout-flow-support');
        $xml_data->Push('merchant-checkout-flow-support');

        $xml_data->Element('continue-shopping-url', $setting['continue_shop']);

        $xml_data->Pop('merchant-checkout-flow-support');
        $xml_data->Pop('checkout-flow-support');

        $xml_data->Pop('checkout-shopping-cart');

        $nvpstr = $xml_data->GetXML();

        $data = $payment->set_checkout($nvpstr);

        return array(
            'ACK' => true,
            'url' => $data['url'],
            'extend' => $data
        );
    }

    function do_checkout(ET_Order $order) {
        $order_pay = clone $order;
        $order = $order_pay->generate_data_to_pay();

        $payment = new ET_GoogleCheckout ();

        if (isset($_REQUEST['token'])) {
            if ($payment->get_digital_key($order['ID']) == $_REQUEST['token']) {
                $order_pay->set_status('publish');
                $order_pay->update_order();

                return array(
                    'ACK' => true,
                    'payment' => 'google_checkout',
                    'payment_status' => 'Completed'
                );
            } else {
                return array(
                    'ACK' => false,
                    'payment' => 'google_checkout',
                    'S_MESSAGE' => __("Fraudulent or error!", ET_DOMAIN),
                    'payment_status' => 'error'
                );
            }
        }
        return array(
            'ACK' => false,
            'payment' => 'google_checkout',
            'payment_status' => 'error'
        );
    }

}
class ET_DebitVisitor extends ET_CashVisitor {
    protected $_payment_type = 'debit';
}

class ET_CashVisitor extends ET_PaymentVisitor {

    protected $_payment_type = 'cash';

    function setup_checkout(ET_Order $order) {
        $mail_method = $order->_send_mail_method;
        do_action('et_email_send_admin', $mail_method);

        $cash = new ET_Cash($this->_settings);
        $returnURL = $cash->set_checkout();


        return array(
            'success' => true,
            'ACK' => true,
            'url' => $returnURL
        );
    }

    function do_checkout(ET_Order $order) {

        $order_pay = clone $order;
        $order = $order_pay->generate_data_to_pay();
//        echo '<pre>';
//        var_dump($order);
//        exit; 
        if (isset($order['ID'])) {

            $cash = new ET_Cash($this->_settings);

            $order_pay->set_status('pending');
            $order_pay->update_order();
            
            $message = $cash->do_checkout($order);
            
            if ($this->_payment_type == 'cash'){
                $message = str_replace('[type]', 'Überweisung', $message);
            }
            else{
                $message = str_replace('[type]', 'Lastschrift', $message);
            }

            //do_action('et_invoice_payment', $order);

            return array(
                'ACK' => true,
                'payment' => 'cash',
                'response' => array(
                    'S_MESSAGE' => $message,
                    'L_MESSAAGE' => $message,
                ),
                'payment_status' => 'Completed'
            );
        } else {
            return array(
                'ACK' => false,
                'payment' => 'cash',
                'response' => array(
                    'S_MESSAGE' => __("Invalid order ID", ET_DOMAIN),
                    'L_MESSAAGE' => __("Invalid order ID", ET_DOMAIN),
                ),
                'payment_status' => 'error'
            );
        }
    }

}

class ET_AuthorizeVisitor extends ET_PaymentVisitor {

    protected $_payment_type = 'authorize';

    function setup_checkout(ET_Order $order) {
        //$order				=	clone	$order ;
        $order = $order->generate_data_to_pay();

        $authorize = new ET_Authorize($this->_settings, 1);

        extract($order);
        $pro_str = "";
        $amount = 0.00;
        $extend = '';
        if (!empty($products))
            foreach ($products as $key => $value) {
                $extend .= $authorize->add_field('x_line_item', $key . '<|>' . $value['NAME'] . '<|>' . $value['L_DESC'] . '<|>' . $value['QTY'] . '<|>' . $value['AMT'] . '<|>0');
                $amount = $amount + $value['QTY'] * $value['AMT'];
            }
        $amount = number_format($amount, 2, '.', '');
        $extend .= $authorize->add_field('x_amount', $amount);
        //$extend .=	$authorize->add_field('x_currency_code', $order['currencyCodeType']);

        $data = $authorize->set_checkout($extend, $amount);
        return array(
            'ACK' => true,
            'url' => $data['url'],
            'extend' => $data
        );
    }

    function do_checkout(ET_Order $order) {

        $md5 = isset($_REQUEST['x_MD5_Hash']) ? $_REQUEST['x_MD5_Hash'] : '';
        $amount = isset($_REQUEST['x_amount']) ? $_REQUEST['x_amount'] : '';
        $trans_id = isset($_REQUEST['x_trans_id']) ? $_REQUEST['x_trans_id'] : '';

        $payment = new ET_Authorize($this->_settings, 1);

        if ($md5 == $payment->generate_hash($amount, $trans_id)) {
            $this->_order->set_status('publish');
            $this->_order->update_order();
            return array(
                'ACK' => true,
                'payment' => 'authorize',
                'payment_status' => 'Completed'
            );
        } else {
            return array(
                'ACK' => false,
                'payment' => 'authorize',
                'payment_status' => 'error'
            );
        }
    }

}

class ET_Payment_Factory {

    function __construct() {
        // dont know what i can do here
    }

    public static function createPaymentVisitor($paymentType, $order) {

        switch ($paymentType) {
            case 'CASH' :
                $class = new ET_CashVisitor($order);
                break;
            case 'DEBIT' :
                $class = new ET_DebitVisitor($order);
                break;
            case 'GOOGLE_CHECKOUT' :
                $class = new ET_GoogleVisitor($order);
                break;
            case 'PAYPAL' :
                $class = new ET_PaypalVisitor($order);
                break;
            case 'AUTHORIZE' :
                $class = new ET_AuthorizeVisitor($order);
                break;
            case '2CHECKOUT' :
                $class = new ET_2COVisitor($order);
                break;
            default : $class = false;
        }

        return apply_filters('et_factory_build_payment_visitor', $class, $paymentType, $order);
    }

}