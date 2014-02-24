<?php
/**
 * Template Name: Zahlungsprozess
 */
global $wp_rewrite;

$payment_return = array('ACK' => false);
$payment_type = get_query_var('paymentType');

// if( !session_id() ) { session_start(); }
$session = et_read_session();

//   $session = array(2) { 
//		["order_id"]=> int(637) 
//	 	["job_id"]=> string(3) "636" 
//	 }

if ($payment_type == 'free'){
    do_action('et_email_send_admin', array());
}

if ($payment_type) {
    if (isset($session['order_id']))
        $order = new ET_JobOrder($session['order_id']);
    else
        $order = new ET_NOPAYOrder();

    $visitor = JE_Payment_Factory::createPaymentVisitor(strtoupper($payment_type), $order);
    $payment_return = $order->accept($visitor);
    //$payment_type			=	$_REQUEST['paymentType'];

    $payment_return = apply_filters('je_payment_process', $payment_return, $order, $payment_type);
    do_action('je_payment_process_action', $payment_return, $order, $payment_type);
}

// echo 'order : <br><pre>';
// var_dump($order);
// echo '</pre>';
$job_id = $session['job_id'];

function isPaypalReturnSuccess(){
    if (!isset($_GET['token'])){
        return true;
    }
    else if (isset($_GET['PayerID'])){
        return true;
    }
    
    return false;
}
?>

<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
    <!--<![endif]-->
    <head>
        <meta charset="<?php bloginfo('charset'); ?>" />
        <!-- Use the .htaccess and remove these lines to avoid edge case issues.
                                 More info: h5bp.com/i/378 -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <!-- <meta name="viewport" content="width=device-width" /> -->
        <meta name="description" content="<?php echo bloginfo('description') ?>" />
        <meta name="keywords" content="Job, Jobs, company, employer, employee" />
        <title><?php
            /*
             * Print the <title> tag based on what is being viewed.
             */
            global $page, $paged, $user_ID;

            wp_title('|', true, 'right');

// Add the blog name.
            bloginfo('name');

// Add the blog description for the home/front page.
            $site_description = get_bloginfo('description', 'display');
            if ($site_description && ( is_home() || is_front_page() ))
                echo " | $site_description";

// Add a page number if necessary:
            if ($paged >= 2 || $page >= 2)
                echo ' | ' . sprintf(__('Page %s', ET_DOMAIN), max($paged, $page));
            ?></title>
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />


        <!--[if lte IE 8]> <link rel="stylesheet" type="text/css" href="css/lib/ie.css" charset="utf-8" /> <![endif]-->
        <?php wp_head(); ?>

    </head>
    <body class="redirect">
        <?php
        $payment_return = wp_parse_args($payment_return, array('ACK' => false, 'payment_status' => ''));
        extract($payment_return);



        $job = get_post($session['job_id']);
        
        //ngocanh
        //if (isset($ACK) && $ACK || (isset($test_mode) && $test_mode))
        if (isset($job->post_type) && $job->post_type == 'job' && isPaypalReturnSuccess()) 
        {
            if (!isset($session['method']) && $job->post_type == 'job') {
                ?>
                <!-- Prompt IE 6 users to install Chrome Frame. Remove this if you support IE 6.
                   chromium.org/developers/how-tos/chrome-frame-getting-started -->
                <!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->

                <div class="redirect-content" >
                    <div class="main-center">

                        <?php
                        if ($payment_type == 'cash' || $payment_type == 'debit') {
                            printf(__("<p>Your job has been submitted to our website.</p> %s ", ET_DOMAIN), $response['L_MESSAAGE']);
                        }
                        ?>
                        <div class="title"><?php _e("Success, friend", ET_DOMAIN); ?></div>
                        <div class="content">
                            <?php
                            if ($payment_status == 'Pending')
                                _e("Your payment has been sent successfully but is currently set as 'pending' by Paypal. <br/>You will be notified when your job is approved.", ET_DOMAIN)
                                ?>
                            <br/>
                            <?php _e("You are now redirected to your job page… ", ET_DOMAIN); ?> <br/>
                            <?php printf(__('Time left: %s', ET_DOMAIN), '<span class="count_down">10</span>') ?> 
                        </div>
                        <?php echo '<a href="' . get_permalink($session['job_id']) . '" >' . get_the_title($session['job_id']) . '</a>'; ?>
                    </div>
                </div>	
                <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
                <?php
                $job_id = $session['job_id'];
                $jobpackage = et_get_post_field($job_id, 'job_package');


                /**
                 * check jobpackage change or not
                 */
                if ($payment_type != 'usePackage') {
                    je_update_job_old_order($job, $jobpackage);
                }

                //je_process_job ( $payment_type, $job )
                if ($payment_type != 'free' && $payment_type != 'usePackage') {

                    // save the plans to company's storage
                    $count = et_update_company_plans($job->post_author, $jobpackage);

                    /**
                     * notice user have pay or not
                     */
                    if ($payment_type === 'cash' || $payment_type == 'debit')
                        je_update_company_paid_plans($job->post_author, $jobpackage);
                    else
                        je_update_company_paid_plans($job->post_author, $jobpackage, 1);


                    /*
                     * 	Do some magic here to sumbit new total-amount.
                     * 	and more order-details
                     *
                     */



                    /**
                     * update user order
                     */
                    $order_id = $session['order_id'];
                    $o = et_get_post_field($job_id, 'job_order');

                    if (empty($o)) {
                        $o = array();
                    }
                    $o[] = $order_id;

                    et_update_post_field($job_id, 'job_order', $o);

                    update_post_meta($job_id, 'je_job_package_order', $order_id);

                    je_update_current_user_order($job->post_author, $jobpackage, $order_id);


                    /**
                     * check pending option
                     */
                    $job_opts = new ET_JobOptions();
                    //$order_pay	=	$order->generate_data_to_pay();	
                    if (($payment_type != 'cash' && $payment_type != 'debit' && $payment_status != 'Pending') /* || $order_pay['total'] <= 0 */) {
                        et_update_post_field($job_id, 'job_paid', 1);
                        /**
                         * check for option pending job or not
                         */
                        
                        /*
                        approve for Paypal
                        if ($job_opts->use_pending())
                            wp_update_post(array('ID' => $job->ID, 'post_status' => 'pending'));
                        else */  
                        {
                            wp_update_post(array('ID' => $job->ID, 'post_status' => 'publish'));
                            do_action('je_approve_job', $job);
                        }
                    } else {
                        et_update_post_field($job_id, 'job_paid', 0);
                        wp_update_post(array('ID' => $job->ID, 'post_status' => 'pending'));
                    }
                } else {
                    $current_order = je_get_current_user_order($job->post_author);

                    if (isset($current_order[$jobpackage])) {
                        update_post_meta($job_id, 'je_job_package_order', $current_order[$jobpackage]);
                    }
                }

                /**
                 * update company job group by package
                 */
                if (isset($session['method']) && $session['method'] == "payment_zertifikat") {
                    $redirect_link = et_get_page_link('zertifikat');
                } else {
                    $redirect_link = get_permalink($session['job_id']);
                }
                et_update_post_field($job_id, 'post_views', 0);
            } else {
                /**
                 * process payment when pay to upgrade account
                 */
                ?>
                <div class="redirect-content">
                    <div class="main-center">
                        <?php
                        global $user_ID;
                        if (isset($session['method'])) {
                            
                        } else {
                            $plans = et_get_resume_plans();

                            $order_data = $order->get_order_data();

                            $payment_plan_id = $order_data['payment_plan'];

                            $plan = $plans[$payment_plan_id];

                            $duration = $plan['duration'];
                        }

                        if ($payment_type == 'cash' || $payment_type == 'debit') {
                            printf(__('<p> Your account is now pending for upgrade.</p> %s ', ET_DOMAIN), $response['L_MESSAAGE']);
                            if (isset($session['method']) && $session['method'] == "payment_zertifikat") {
                                $redirect_link = et_get_page_link('zertifikat');
                            } else {
                                $redirect_link = get_post_type_archive_link('resume');
                            }
                            if (!isset($session['method'])) {
                                update_user_meta($user_ID, 'je_resume_view_order_data', array('order_id' => $session['order_id'], 'package_id' => $payment_plan_id, 'duration' => $duration));
                                update_user_meta($user_ID, 'je_resume_view_order_status', 'pending');
                            }

                            $msg = __("You are now redirected to the resumes' list … ", ET_DOMAIN);
                        } else {
                            if (isset($session['resume_id'])) {
                                $redirect_link = get_permalink($session['resume_id']);
                                $msg = __("You are now redirected to recent view resume profile … ", ET_DOMAIN);
                            } else {
                                $redirect_link = get_post_type_archive_link('resume');
                                $msg = __("You are now redirected to the resumes' list … ", ET_DOMAIN);
                            }

                            if (isset($session['method']) && $session['method'] == "payment_zertifikat") {
                                $redirect_link = et_get_page_link('zertifikat');
                            }

                            je_update_view_resume_duration($user_ID, $duration);
                        }
                        ?>
                        <div class="title"><?php _e("Success, friend", ET_DOMAIN); ?></div>
                        <div class="content">
                            <?php
                            if ($payment_status == 'Pending') {
                                _e("Your payment has been sent successfully but is currently set as 'pending' by Paypal. <br/>You will be notified when your account is approved.", ET_DOMAIN);
                                $redirect_link = get_post_type_archive_link('resume');
                                update_user_meta($user_ID, 'je_resume_view_order_data', array('order_id' => $session['order_id'], 'package_id' => $payment_plan_id, 'duration' => $duration));
                                update_user_meta($user_ID, 'je_resume_view_order_status', 'pending');
                            }
                            ?>
                            <br/>
                            <?php echo $msg; ?> <br/>
                            <?php printf(__('Time left: %s', ET_DOMAIN), '<span class="count_down">10</span>') ?> 
                        </div>

                        <?php
                        if ($payment_type != 'cash' && isset($session['resume_id']))
                            echo '<a href="' . $redirect_link . '" >' . get_the_title($session['resume_id']) . '</a>';
                        else
                            echo '<a href="' . $redirect_link . '" >' . __("Resumes", ET_DOMAIN) . '</a>';
                        ?>
                    </div>
                </div>
                <?php
            }
        } else {
            if (!isset($session['method']) && ($job->post_type == 'job' || !function_exists('et_is_resume_menu'))) {
                $redirect_link = et_get_page_link('post-a-job', array('job_id' => $session['job_id']));
                
                //Check If payment Paypal or Cash for PA ( Zertifikat) => return url to page  zertifikat
                if (isset($session['method']) && $session['method'] == "payment_zertifikat") {
                    $redirect_link = et_get_page_link('zertifikat');
                } else {
                    //$redirect_link = get_permalink($session['job_id']);
                }
                ?>
                <div class="redirect-content">
                    <div class="main-center">
                    <?php if (!isPaypalReturnSuccess()):?>
                        <div class="title"> <?php _e("Payment failed, friend!", ET_DOMAIN); ?> </div>
                    <?php else:?>
                        <div class="title"><?php _e("Success, friend", ET_DOMAIN); ?></div>
                        
                    <?php endif;?>
                        <div class="content">
                            <?php _e("You are now redirected back to the job posting page…", ET_DOMAIN); ?> <br/>
                            <?php printf(__('Time left: %s', ET_DOMAIN), '<span class="count_down">10</span>') ?> 
                        </div>
                        <?php echo '<a href="' . $redirect_link . '" >' . __('Post a job', ET_DOMAIN) . '</a>'; ?>
                    </div>
                </div>	 	
                <?php
            } else {
                if (isset($session['resume_id'])) {
                    $redirect_link = et_get_page_link('upgrade-account', array('resume' => $session['resume_id']));
                } else {
                    $redirect_link = et_get_page_link('upgrade-account');
                }

                if (isset($session['method']) && $session['method'] == "payment_zertifikat") {
                    $redirect_link = et_get_page_link('zertifikat');
                }
                ?>
                <div class="redirect-content">
                    <div class="main-center">
                        <?php if (!isPaypalReturnSuccess()):?>
                            <div class="title"> <?php _e("Payment failed, friend!", ET_DOMAIN); ?> </div>
                        <?php else:?>
                            <div class="title"><?php _e("Success, friend", ET_DOMAIN); ?></div>
                        <?php endif;?>
                        
                        <div class="content">
                            <?php _e("Sie werden jetzt auf die Seite zum Upgrade Ihres Account weitergeleitet...", ET_DOMAIN); ?> <br/>
                            <?php printf(__('Time left: %s', ET_DOMAIN), '<span class="count_down">10</span>') ?> 
                        </div>
                        <?php echo '<a href="' . $redirect_link . '" >' . __('Upgraden Sie Ihr Account', ET_DOMAIN) . '</a>'; ?>
                    </div>
                </div>	
                <?php
            }
        }


        et_destroy_session();
        ?>
        <script type="text/javascript">

            jQuery(document).ready(function() {
                setTimeout(function() {
                    // temporarily deactivated
                    window.location = '<?php echo $redirect_link ?>';

                }, 10000);
                setInterval(function() {
                    var i = jQuery('.count_down').html();
                    if (i == 0) {
                        jQuery('.count_down').html(10);
                        i = 11;
                    }
                    jQuery('.count_down').html(parseInt(i) - 1);
                }, 1000);
            });
        </script>

    </body>
</html>