<?php
/**
 * Template Name: Stelle anlegen
 */
wp_deregister_script('post_job');
wp_enqueue_script(
        'post_job', get_stylesheet_directory_uri() . '/js/post_job.js', array('jquery', 'et-underscore', 'et-backbone', 'job_engine', 'front')
);



// get helper file
require_once 'jobhandler_helper.php';


// get acf-fields
acf_form_head();




global $current_user, $wp_rewrite;
$general_opt = new ET_GeneralOptions();

$job = get_query_var('job_id');



if (!!$job) {
    $job = get_post($job);

    if (!isset($job->ID) || !isset($current_user->ID) || $job->post_author != $current_user->ID) {

        // not the job author -> redirect to this page without var
        wp_redirect(et_get_page_link('post-a-job'));
        exit;
    }

    $job = et_create_jobs_response($job);
}

$job_opt = new ET_JobOptions ();
$contact_widget = $job_opt->get_post_job_sidebar();

if (isset($current_user->ID)) {
    $recent_location = et_get_user_field($current_user->ID, 'recent_job_location');

    $full_location = isset($recent_location['full_location']) ? $recent_location['full_location'] : '';
    $location = isset($recent_location['location']) ? $recent_location['location'] : '';
    $location_lat = isset($recent_location['location_lat']) ? $recent_location['location_lat'] : '';
    $location_lng = isset($recent_location['location_lng']) ? $recent_location['location_lng'] : '';
    $company = et_create_companies_response($current_user->ID);
    $apply_method = $company['apply_method'];
    $apply_email = $company['apply_email'];
    $applicant_detail = $company['applicant_detail'];
} else {
    $apply_method = 'isapplywithprofile';
    $apply_email = '';
    $applicant_detail = '';
    $full_location = '';
    $location = '';
    $location_lat = '';
    $location_lng = '';
}




get_header();
?>
<script type="text/javascript">
    (function($) {
        jQuery(document).ready(function() {
            $('#form-item-1096').after('<div class="job_fields"><div class="form-item" id="load_conditions_by_job_type"></div></div>');

            $("select#job_types").change(function() {
                var job_types = $('select#job_types').val();
                if (job_types != 'assistenzarzt') {
                    $('#form-item-1096').hide();
                }
                else {
                    $('#form-item-1096').show();
                }

                $.ajax({
                    type: "POST",
                    dataType: 'html',
                    url: et_globals.ajaxURL,
                    data: {action: 'et_conditions_sync', job_types: job_types}
                }).done(function(data) {
                    $("#load_conditions_by_job_type").html(data);
                });
            });

            $("select#job_types").change();
        });
    })(jQuery);
</script> 

<div class="row-fluid">
    <div class="content-block" id="wrapper">
        <div class="heading"> 

            <h1 class="title"><span class="icon" data-icon="W"></span>
                <?php
                if (!$job) {
                    _e('Post a Job', ET_DOMAIN);
                } else {
                    _e('Renew this Job', ET_DOMAIN);
                }
                ?> 
            </h1>

            <?php if (is_user_logged_in()) { ?> <h2>für Klinik/Praxis: <?php echo $current_user->display_name; ?></h2> <?php } ?>

        </div>

        <div class="main-center margin-top25 clearfix">
            <?php
            $main_colum = 'full-column';
            if (!empty($contact_widget) || current_user_can('manage_options'))
                $main_colum = 'main-column';
            ?>
            <div id="post_job"><!--class="<?php //echo $main_colum                                                            ?>" -->

                <?php if (!!$job) { // add the existed job data here for js ?>
                    <script type="application/json" id="job_data">   <?php echo json_encode($job); ?>  </script>
                <?php } ?>


                <div class="post-a-job">
                    <!-- STEP 1 -->
                    <?php $steps = array('1', '2', '3', '4', '5'); ?>

                    <div class="step current <?php // if(!!$job) echo 'completed';                                                                    ?>" id='step_package'>

                        <div class="toggle-title f-left-all bg-toggle-active <?php if (!!$job) echo 'toggle-complete'; ?>">
                            <div class="icon-border"><?php echo array_shift($steps) ?></div>
                            <span class="icon" data-icon="2"></span>
                            <span>Wählen Sie Ihre Anzeige</span>
                        </div>

                        <div class="toggle-content clearfix">

                            <div class="form-item">
                                <div class="label">
                                    <h6 class="">Für welche Position soll diese Stelle geschaltet werden?</h6>
                                </div>

                                <div class="select-style  border-radius">
                                    <?php et_job_type_select('job_types'); ?>
                                </div>
                            </div>


                            <?php
                            global $current_user;
                            $plans = et_get_payment_plans();
                            do_action('je_before_job_package_list');

// Display plans matching job_types(AA, FPJS)
                            if (!empty($plans)) {
                                ?>
                                <?php
                                // select matching plan (??)
                                $plan = $plans[$jobhandler['basis']['reduced']['weeks4']];

                                // foreach ($plans as $plan) :
                                $sel = ( isset($job['job_package']) && $job['job_package'] == $plan['ID']) ? 'selected' : '';

                                $featured_text = $plan['featured'] ? __('featured', ET_DOMAIN) : __('normal', ET_DOMAIN);
                                $plan['quantity'] = isset($plan['quantity']) ? $plan['quantity'] : 1;

                                $purchase_plans = !empty($current_user->ID) ? et_get_purchased_quantity($current_user->ID) : array();
                                ?>


                                <?php if (!!$jobhandler) { // add the existed job data here for js ?>
                                    <script type="application/json" id="jobhandler">   <?php echo json_encode($jobhandler); ?>  </script>
                                <?php } ?>

                                <div class="clear row-fluid">
                                    <div class="clearfix span4 payment_plan basis">
                                        <h2 class="title"> Basis-Anzeige </h2>
                                        <!-- <span class="plan_teaser">Nutzen Sie unsere kostenlose Basis-Anzeigen und besetzen Sie unkompliziert Ihre Stellen</span> -->

                                        <span class="plan_teaser">

                                            <ul>
                                                <li class="checked">Anzeige in den Suchergebnissen</li>
                                                <li class="checked">Bewerbung direkt an Sie</li>
                                            </ul>

                                        </span>



                                        <span class="pricetag" id="price_basis">kostenlos</span> 

                                        <div class="btn-select"> 
                                            <button class="bg-btn-hyperlink border-radius select_plan" data-package="<?php echo $plan['ID']; ?>" data-basis="basis" data-job="0"   data-price="<?php echo $plan['price']; ?>"><?php _e('Select', ET_DOMAIN); ?></button>
                                        </div>
                                        <ul class="clearfix announcement type_regular " id="type_regular_<?php echo $plan['ID']; ?>">
                                            <li>
                                                <input type="checkbox" id="regular_facebook_<?php echo $plan['ID']; ?>" value="facebook" class="price_facebook" data-regular="99" > <label for="regular_facebook_<?php echo $plan['ID']; ?>">Facebook-Announcement <b><span>99.00 €</span></b></label>
                                            </li> 
                                            <li>
                                                <input type="checkbox" id="regular_blog_<?php echo $plan['ID']; ?>" value="blog" class="price_blog" data-regular="179" > <label for="regular_blog_<?php echo $plan['ID']; ?>">Blog-Announcement <b><span>179.00 €</span></b></label>
                                            </li>
                                            <li>
                                                <input type="checkbox" id="regular_newsletter_<?php echo $plan['ID']; ?>" value="newsletter" class="price_newsletter" data-regular="79" > <label for="regular_newsletter_<?php echo $plan['ID']; ?>">Newsletter-Announcement <b><span>79.00 €</span></b></label>
                                            </li>
                                        </ul>
                                        <ul class="clearfix announcement type_reduced" id="type_reduced<?php echo $plan['ID']; ?>">
                                            <li>
                                                <input type="checkbox" id="reduced_facebook_<?php echo $plan['ID']; ?>" value="facebook" class="price_facebook" data-reduced="69"> <label for="reduced_facebook_<?php echo $plan['ID']; ?>">Facebook-Announcement <b><span>69.00 €</span></b></label>
                                            </li> 
                                            <li>
                                                <input type="checkbox" id="reduced_blog_<?php echo $plan['ID']; ?>" value="blog" class="price_blog" data-reduced="179"> <label for="reduced_blog_<?php echo $plan['ID']; ?>">Blog-Announcement <b><span>179.00 €</span></b></label>
                                            </li>
                                            <li>
                                                <input type="checkbox" id="reduced_newsletter_<?php echo $plan['ID']; ?>" value="newsletter" class="price_newsletter" data-reduced="49"> <label for="reduced_newsletter_<?php echo $plan['ID']; ?>">Newsletter-Announcement <b><span>49.00 €</span></b></label>
                                            </li>
                                        </ul>
                                        <div class="clearfix"></div>
                                    </div> 

                                    <div class="clearfix span4 payment_plan top">
                                        <h2 class="title"> Top-Anzeige </h2>
                                        <span class="plan_teaser">

                                            <ul>
                                                <li class="checked">Anzeige in den Suchergebnissen</li>
                                                <li class="checked">Bewerbungen direkt an Sie</li>
                                                <li class="checked">Top Platzierung in den Suchergebnissen</li>
                                            </ul>
                                        </span>



                                        <?php
                                        $plan = $plans[$jobhandler['top']['regular']['weeks4']];
                                        if ($plan['price'] > 0) {
                                            ?>
                                            <span class="pricetag" id="price_top"> <?php echo et_get_price_format($plan['price'], '') ?> </span> 
                                        <?php } ?> 

                                        <div class="btn-select"> 
                                            <button class="bg-btn-hyperlink border-radius select_plan" data-basis="top" data-package="<?php echo $plan['ID']; ?>" data-job="0" data-price="<?php echo $plan['price']; ?>"><?php _e('Select', ET_DOMAIN); ?></button>
                                        </div>
                                        <ul class="clearfix announcement type_regular " id="type_regular_<?php echo $plan['ID']; ?>">
                                            <li>
                                                <input type="checkbox" id="regular_facebook_<?php echo $plan['ID']; ?>" value="facebook" class="price_facebook" data-regular="99" > <label for="regular_facebook_<?php echo $plan['ID']; ?>">Facebook-Announcement <b><span>99.00 €</span></b></label>
                                            </li> 
                                            <li>
                                                <input type="checkbox" id="regular_blog_<?php echo $plan['ID']; ?>" value="blog" class="price_blog" data-regular="179" > <label for="regular_blog_<?php echo $plan['ID']; ?>">Blog-Announcement <b><span>179.00 €</span></b></label>
                                            </li>
                                            <li>
                                                <input type="checkbox" id="regular_newsletter_<?php echo $plan['ID']; ?>" value="newsletter" class="price_newsletter" data-regular="79" > <label for="regular_newsletter_<?php echo $plan['ID']; ?>">Newsletter-Announcement <b><span>79.00 €</span></b></label>
                                            </li>
                                        </ul>
                                        <ul class="clearfix announcement type_reduced" id="type_reduced<?php echo $plan['ID']; ?>">
                                            <li>
                                                <input type="checkbox" id="reduced_facebook_<?php echo $plan['ID']; ?>" value="facebook" class="price_facebook" data-reduced="69"> <label for="reduced_facebook_<?php echo $plan['ID']; ?>">Facebook-Announcement <b><span>69.00 €</span></b></label>
                                            </li> 
                                            <li>
                                                <input type="checkbox" id="reduced_blog_<?php echo $plan['ID']; ?>" value="blog" class="price_blog" data-reduced="179"> <label for="reduced_blog_<?php echo $plan['ID']; ?>">Blog-Announcement <b><span>179.00 €</span></b></label>
                                            </li>
                                            <li>
                                                <input type="checkbox" id="reduced_newsletter_<?php echo $plan['ID']; ?>" value="newsletter" class="price_newsletter" data-reduced="49"> <label for="reduced_newsletter_<?php echo $plan['ID']; ?>">Newsletter-Announcement <b><span>49.00 €</span></b></label>
                                            </li>
                                        </ul>
                                    </div>


                                    <div class="clearfix span4 payment_plan premium" >
                                        <h2 class="title"> Premium-Anzeige </h2>
                                        <!-- <span class="plan_teaser">Präsentieren Sie sich als attraktiver Arbeitgeber direkt auf der Startseite und für jeden sofort sichtbar</span> -->
                                        <span class="plan_teaser">

                                            <ul>
                                                <li class="checked">Anzeige in den Suchergebnissen</li>

                                                <li class="checked"> Bewerbungen direkt an Sie</li>

                                                <li class="checked"> Top Platzierung in den Suchergebnissen</li>

                                                <li class="checked"> Platzierung auf der Startseite – maximale Sichtkontakte</li>
                                            </ul>
                                        </span>


                                        <?php
                                        $plan = $plans[$jobhandler['premium']['regular']['weeks4']];
                                        if ($plan['price'] > 0) {
                                            ?>
                                            <span class="pricetag" id="price_premium"> <?php echo et_get_price_format($plan['price'], '') ?> </span> 
                                        <?php } ?>


                                        <div class="btn-select"> 
                                            <button class="bg-btn-hyperlink border-radius select_plan" data-basis="premium" data-package="<?php echo $plan['ID']; ?>" data-job="0" data-price="<?php echo $plan['price']; ?>"><?php _e('Select', ET_DOMAIN); ?></button>
                                        </div>
                                        <ul class="clearfix announcement type_regular " id="type_regular_<?php echo $plan['ID']; ?>">
                                            <li>
                                                <input type="checkbox" id="regular_facebook_<?php echo $plan['ID']; ?>" value="facebook" class="price_facebook" data-regular="99" > <label for="regular_facebook_<?php echo $plan['ID']; ?>">Facebook-Announcement <b><span>99.00 €</span></b></label>
                                            </li> 
                                            <li>
                                                <input type="checkbox" id="regular_blog_<?php echo $plan['ID']; ?>" value="blog" class="price_blog" data-regular="179" > <label for="regular_blog_<?php echo $plan['ID']; ?>">Blog-Announcement <b><span>179.00 €</span></b></label>
                                            </li>
                                            <li>
                                                <input type="checkbox" id="regular_newsletter_<?php echo $plan['ID']; ?>" value="newsletter" class="price_newsletter" data-regular="79" > <label for="regular_newsletter_<?php echo $plan['ID']; ?>">Newsletter-Announcement <b><span>79.00 €</span></b></label>
                                            </li>
                                        </ul>
                                        <ul class="clearfix announcement type_reduced" id="type_reduced<?php echo $plan['ID']; ?>">
                                            <li>
                                                <input type="checkbox" id="reduced_facebook_<?php echo $plan['ID']; ?>" value="facebook" class="price_facebook" data-reduced="69"> <label for="reduced_facebook_<?php echo $plan['ID']; ?>">Facebook-Announcement <b><span>69.00 €</span></b></label>
                                            </li> 
                                            <li>
                                                <input type="checkbox" id="reduced_blog_<?php echo $plan['ID']; ?>" value="blog" class="price_blog" data-reduced="179"> <label for="reduced_blog_<?php echo $plan['ID']; ?>">Blog-Announcement <b><span>179.00 €</span></b></label>
                                            </li>
                                            <li>
                                                <input type="checkbox" id="reduced_newsletter_<?php echo $plan['ID']; ?>" value="newsletter" class="price_newsletter" data-reduced="49"> <label for="reduced_newsletter_<?php echo $plan['ID']; ?>">Newsletter-Announcement <b><span>49.00 €</span></b></label>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <?php
                            }
//provide Hook
                            do_action('je_after_job_package_list');
                            ?>

                            <script id="package_plans" type="text/data"><?php echo json_encode($plans); ?></script>

                        </div>
                        <div class="clearfix"></div>
                    </div>



                    <?php
                    //
                    //  STEP 2 - duration
                    ?>

                    <div class="step <?php #if(!!$job) echo 'completed';                                                                    ?>" id='step_plan'>
                        <div class="toggle-title f-left-all">
                            <div class="icon-border"><?php echo array_shift($steps) ?></div>
                            <span class="icon" data-icon="2"></span>
                            <span>Bitte wählen sie die Laufzeit des Pakets</span>
                        </div>

                        <div class="toggle-content clearfix" style="display: none">
                            <ul>
                                <li class="clearfix w4">
                                    <div class="label"> <strong> 4 Wochen</strong> <span class="discount"></span> <span class="label discount4weeks pricetag"> </span> </div>
                                    <div class="btn-select f-right weeks4">
                                        <button class="btn-select f-rightbg-btn-hyperlink border-radius select_duration" data-duration="weeks4" data-price="<?php ?>"><?php _e('Select', ET_DOMAIN); ?>
                                        </button>
                                    </div>

                                </li>

                                <li class="clearfix w8">
                                    <div class="label"> <strong>8 Wochen</strong> <span class="discount">inkl. 10% Rabatt </span> <span class="label discount8weeks pricetag"> </span> </div>
                                    <div class="btn-select f-right weeks8">
                                        <button class="btn-select f-rightbg-btn-hyperlink border-radius select_duration" data-duration="weeks8" data-price="<?php ?>"><?php _e('Select', ET_DOMAIN); ?></button>
                                    </div>		

                                </li>
                                <li class="clearfix w12">
                                    <div class="label"> <strong> 12 Wochen</strong> <span class="discount">inkl. 20% Rabatt </span><span class="label discount12weeks pricetag"> </span></div>
                                    <div class="btn-select f-right weeks12">
                                        <button class="btn-select f-rightbg-btn-hyperlink border-radius select_duration" data-duration="weeks12" data-price="<?php ?>"><?php _e('Select', ET_DOMAIN); ?></button>
                                    </div> 
                                </li>
                            </ul>


                        </div>
                    </div>



                    <?php
                    //
                    //  STEP 3 - User authentification
                    ?>

                    <?php if (!et_is_logged_in()) { ?>
                        <div class="step" id='step_auth'>
                            <div class="toggle-title f-left-all">
                                <div class="icon-border"><?php echo array_shift($steps) ?></div>
                                <span class="icon" data-icon="2"></span>
                                <span><?php _e('Login or create an account', ET_DOMAIN); ?></span>
                            </div>
                            <div class="toggle-content login clearfix" style="display: none">
                                <div class="tab-title f-left-all clearfix">
                                    <div class="bg-tab active"><?php _e('Register', ET_DOMAIN); ?></div>
                                    <div class="bg-tab"><span><?php _e('Already have an account?', ET_DOMAIN); ?></span> <?php _e('Login', ET_DOMAIN); ?></div>
                                </div>
                                <div class="tab-content">
                                    <div class="form current">
                                        <form id="register" novalidate="novalidate" autocomplete="on">
                                            <div class="form-item">
                                                <div class="label">
                                                    <label for="reg_email">
                                                        <h6 class=""><?php _e('USER NAME', ET_DOMAIN); ?></h6>
                                                        <?php _e('Please enter your username', ET_DOMAIN); ?>
                                                    </label>
                                                </div>
                                                <div>
                                                    <input class="bg-default-input is_user_name" tabindex="1" name="reg_user_name" id="reg_user_name" type="text"/>
                                                </div>
                                            </div>
                                            <div class="form-item">
                                                <div class="label">
                                                    <label for="reg_email">
                                                        <h6 class=""><?php _e('EMAIL ADDRESS', ET_DOMAIN); ?></h6>
                                                        <?php _e('Please enter your email address', ET_DOMAIN); ?>
                                                    </label>
                                                </div>
                                                <div>
                                                    <input class="bg-default-input is_email" tabindex="1" name="reg_email" id="reg_email" type="email"/>
                                                </div>
                                            </div>
                                            <div class="form-item">
                                                <div class="label">
                                                    <label for="reg_pass">
                                                        <h6 class=""><?php _e('PASSWORD', ET_DOMAIN); ?></h6>
                                                        <?php _e('Enter your password', ET_DOMAIN); ?>
                                                    </label>
                                                </div>
                                                <div>
                                                    <input class="bg-default-input is_pass" tabindex="2" name="reg_pass" id="reg_pass" type="password" />
                                                </div>
                                            </div>
                                            <div class="form-item">
                                                <div class="label">
                                                    <label for="reg_pass_again">
                                                        <h6 class=" repeat_pass "><?php _e('RETYPE YOUR PASSWORD', ET_DOMAIN); ?></h6>
                                                        <?php _e('Retype your password', ET_DOMAIN); ?>
                                                    </label>
                                                </div>
                                                <div>
                                                    <input class="bg-default-input" tabindex="3" name="reg_pass_again" id="reg_pass_again" type="password" />
                                                </div>
                                            </div>
                                            <div class="form-item no-border-bottom clearfix">
                                                <div class="label">&nbsp;</div>
                                                <div class="btn-select">
                                                    <button class="bg-btn-action border-radius" tabindex="4" type="submit" id="submit_register"><?php _e('CONTINUE', ET_DOMAIN); ?></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="form">
                                        <form id="login" novalidate="novalidate" autocomplete="on">
                                            <div class="form-item">
                                                <div class="label">
                                                    <h6 class=""><?php _e('USERNAME or EMAIL ADDRESS', ET_DOMAIN); ?></h6>
                                                    <?php _e('Please enter your username or email', ET_DOMAIN); ?>
                                                </div>
                                                <div>
                                                    <input class="bg-default-input is_email is_user_name" tabindex="1" name="log_email" id="log_email" type="text" />
                                                </div>
                                            </div>
                                            <div class="form-item">
                                                <div class="label">
                                                    <h6 class=""><?php _e('PASSWORD', ET_DOMAIN); ?></h6>
                                                    <?php _e('Enter your password', ET_DOMAIN); ?>
                                                </div>
                                                <div>
                                                    <input class="bg-default-input is_pass" tabindex="2" name="log_pass" id="log_pass" type="password" />
                                                </div>
                                            </div>
                                            <div class="form-item no-border-bottom clearfix">
                                                <div class="label">&nbsp;</div>
                                                <div class="btn-select">
                                                    <button class="bg-btn-action border-radius" tabindex="3" type="submit" id="submit_login"><?php _e('LOGIN', ET_DOMAIN); ?></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>


                    <?php
                    //
                    //  STEP 4 - Company Info
                    ?>

                    <div class="step <?php if (!!$job) echo 'completed'; ?>" id='step_job'>
                        <div class="toggle-title f-left-all <?php if (!!$job) echo 'toggle-complete'; ?>">
                            <div class="icon-border"><?php echo array_shift($steps) ?></div>
                            <span class="icon" data-icon="2"></span>
                            <span><?php _e('Describe your job and your company', ET_DOMAIN); ?></span>
                        </div>

                        <div class="toggle-content login clearfix" style="display: none">
                            <div class="form">
                                <form id="job_form" method="post" enctype="multipart/form-data" novalidate="novalidate" autocomplete="on">
                                    <div id="job_info">
                                        <div class="form-item">
                                            <div class="label">
                                                <h6 class=""><?php _e('JOB TITLE', ET_DOMAIN); ?></h6>
                                                <?php _e('Enter a short title for your job', ET_DOMAIN); ?>
                                            </div>
                                            <div>
                                                <input placeholder="" class="bg-default-input" tabindex="1" name="title" id="title" type="text" value="<?php if (isset($job['title'])) echo $job['title']; ?> " />
                                            </div>
                                        </div>
                                        <div class="form-item">
                                            <div class="label">
                                                <h6 class=""><?php _e('JOB DESCRIPTION', ET_DOMAIN); ?></h6>
                                                <?php _e('Describe your job in a few paragraphs ', ET_DOMAIN); ?>
                                            </div>
                                            <div class="job_description">
                                                <?php
                                                if (isset($job['content']))
                                                    $content = $job['content'];
                                                else
                                                    $content = '';
                                                wp_editor($content, 'content', je_job_editor_settings());
                                                // 
                                                ?>

                                                                                        <!-- <textarea class="bg-default-input tinymce" tabindex="2" name="content" id="content"><?php
                                                if (isset($job['content']))
                                                    echo $job['content'];
                                                else
                                                    echo ' ';
                                                ?></textarea> -->
                                            </div>
                                        </div>
                                        <div class="form-item">
                                            <div class="label">
                                                <h6 class=""><?php _e('JOB LOCATION', ET_DOMAIN); ?></h6>
                                                <?php _e('Enter a city and country or leave it blank', ET_DOMAIN); ?>
                                            </div>
                                            <div>
                                                <div class="address">
                                                    <?php
                                                    if (isset($job['full_location']))
                                                        $full_location = $job['full_location'];
                                                    if (isset($job['location']))
                                                        $location = $job['location'];
                                                    if (isset($job['location_lat']))
                                                        $location_lat = $job['location_lat'];
                                                    if (isset($job['location_lng']))
                                                        $location_lng = $job['location_lng'];
                                                    ?>
                                                    <input class="bg-default-input" name="full_location" tabindex="3" id="full_location" type="text" value="<?php echo $full_location ?>"/>
                                                    <input type="hidden" name="location" id="location" value="<?php echo $location; ?>" />
                                                    <input type="hidden" name="location_lat" id="location_lat" value="<?php echo $location_lat; ?>" />
                                                    <input type="hidden" name="location_lng" id="location_lng" value="<?php echo $location_lng; ?>" />
                                                    <div class="address-note">
                                                        <?php _e('Examples: "Melbourne VIC", "Seattle", "Anywhere"', ET_DOMAIN) ?>
                                                        <!--
                                                        <?php _e('Display location as', ET_DOMAIN) ?>: <span title="<?php _e('Edit location', ET_DOMAIN) ?>" id="add_sample">"<?php echo $location; ?>"</span>
                                                        <input style="display:none" type="text" maxlength="50" id="add_sample_input" value="<?php echo $location; ?>">
                                                        -->
                                                    </div>
                                                </div>
                                                <div class="maps">
                                                    <div class="map-inner" id="map"></div>
                                                </div>
                                            </div>
                                        </div>


                                        <!-- How to apply -->
                                        <div class="form-item">
                                            <div class="label">
                                                <h6 class=""><?php _e('HOW TO APPLY', ET_DOMAIN); ?></h6>
                                                <?php _e('Select how you would want jobseekers to submit their applications', ET_DOMAIN); ?>
                                            </div>
                                            <div class="apply">
                                                <input type="hidden" id="apply_method" value="">
                                                <input type="radio" name="apply_method" id="isapplywithprofile" value="isapplywithprofile" <?php if ($apply_method != 'ishowtoapply') echo 'checked' ?> />
                                                <label class="" for="isapplywithprofile">
                                                    <?php _e("Allow job seekers to submit their cover letter and resume directly", ET_DOMAIN); ?>
                                                </label>
                                                <div class="email_apply">
                                                    <span class=""><?php _e("Send applications to this email address:", ET_DOMAIN); ?></span>&nbsp;
                                                    <input class="bg-default-input application-email" type="text" name="apply_email" id="apply_email" value="<?php
                                                    if (is_user_logged_in()) {
                                                        echo $current_user->user_email;
                                                    }
                                                    ?>"/> </br>
                                                    <span class="example"><?php _e("e.g. 'application@demo.com'", ET_DOMAIN); ?></span>
                                                </div>

                                                <input type="radio" name="apply_method" id="ishowtoapply" value="ishowtoapply" <?php if ($apply_method == 'ishowtoapply') echo 'checked' ?> />
                                                <label class="" for="ishowtoapply" ><?php _e("Job seekers must follow the application steps below", ET_DOMAIN); ?></label>
                                                <div class="applicant_detail">

                                                    <?php wp_editor('', 'applicant_detail', je_editor_settings()) ?>
<!-- <textarea name="applicant_detail" id="applicant_detail"><?php echo $applicant_detail ?></textarea> -->

                                                </div>

                                            </div>
                                        </div>


                                        <!-- Choose Category -->
                                        <div class="form-item">
                                            <div class="label">
                                                <h6 class=""><?php _e('JOB CATEGORY', ET_DOMAIN); ?></h6>
                                                <?php _e('Select a category for your job', ET_DOMAIN); ?>
                                            </div>
                                            <div class="select-style btn-background border-radius">
                                                <?php et_job_cat_select('categories') ?>
                                            </div>
                                        </div>

                                        <!-- Choose Verband -->
                                        <div class="form-item" id="select_verband">
                                            <div class="label">
                                                <h6><?php _e("Verbandzuweisung", ET_DOMAIN); ?></h6>
                                                <?php _e("Sind Sie Mitglied bei einem mit uns kooperierenden Verband ?", ET_DOMAIN); ?>
                                            </div>
                                            <?php
                                            //get All Verband
                                            global $current_user;
                                            getVerband($current_user->ID, 'job', true);
                                            ?>
                                        </div>

                                        <script type="text/javascript">
                                            $(document).ready(function() {
                                                $('#select_verband').insertAfter('#form-item-984');
                                            });
                                        </script>

                                        <?php
//do actions and provide et_post_job_fields-hook
                                        do_action('et_post_job_fields')
                                        ?>
                                    </div>

                                    <div id="company_info">
                                        <div class="form-item">
                                            <div class="label">
                                                <h6 class=""><?php _e('COMPANY NAME', ET_DOMAIN); ?></h6>
                                                <?php _e('Enter your company name', ET_DOMAIN); ?>
                                            </div>
                                            <div>
                                                <input class="bg-default-input" tabindex="6" name="display_name" id="display_name" type="text" value="<?php
                                                if (is_user_logged_in()) {
                                                    echo $current_user->display_name;
                                                }
                                                ?>"/>
                                            </div>
                                        </div>
                                        <div class="form-item">
                                            <div class="label">
                                                <h6 class=""><?php _e('COMPANY WEBSITE', ET_DOMAIN); ?></h6>
                                                <?php _e('Enter your company website', ET_DOMAIN); ?>
                                            </div>
                                            <div>
                                                <input class="bg-default-input" tabindex="7" name="user_url" id="user_url" type="text" value="<?php
                                                if (is_user_logged_in()) {
                                                    echo $current_user->user_url;
                                                }
                                                ?>" />
                                            </div>
                                        </div>
                                        <?php $uploaderID = 'user_logo'; ?>
                                        <div class="form-item" id="<?php echo $uploaderID; ?>_container">
                                            <div class="label">
                                                <h6><?php _e('COMPANY LOGO', ET_DOMAIN); ?></h6>
                                                <?php _e('Upload your company logo', ET_DOMAIN); ?>
                                            </div>
                                            <div>
                                                <span class="company-thumbs" id="<?php echo $uploaderID; ?>_thumbnail">
                                                    <?php
                                                    if (is_user_logged_in()) {
                                                        $user_logo = et_get_company_logo($current_user->ID);
                                                        if (!empty($user_logo)) {
                                                            ?>
                                                            <img src="<?php echo $user_logo['company-logo'][0]; ?>" id="<?php echo $uploaderID; ?>_thumb" data="<?php echo $user_logo['attach_id']; ?>" />
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="input-file clearfix et_uploader">
                                                <span class="btn-background border-radius button" id="<?php echo $uploaderID; ?>_browse_button" tabindex="8" >
                                                    <?php _e('Browse...', ET_DOMAIN); ?>
                                                    <span class="icon" data-icon="o"></span>
                                                </span>
                                                <span class="et_ajaxnonce" id="<?php echo wp_create_nonce($uploaderID . '_et_uploader'); ?>"></span>
                                                <div class="clearfix"></div>
                                                <div class="filelist"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-item clearfix agb_for_free">
                                        <ul>
                                            <li>
                                                <input type="checkbox" id="agb_check_free" name="agb_check_free" required class="">  <label for="agb_check_free">Ich habe die <a href="<?php bloginfo('url'); ?>/agb/" target="about:blank">AGB gelesen</a></label> 
                                            </li>
                                        </ul>
                                    </div>
                                    <?php
//do actions and provide hook
                                    do_action('je_post_job_after_author_info')
                                    ?>

                                    <div class="form-item clearfix">

                                        <div class="label">&nbsp;</div>
                                        <div class="btn-select">
                                            <!--<button class="bg-btn-action border-radius" tabindex="9" type="submit" id="submit_job"><?php _e('CONTINUE', ET_DOMAIN); ?></button>-->
                                            <div class="btn bg-btn-action border-radius save_job" id="save_job"><?php _e('CONTINUE', ET_DOMAIN); ?></div>
                                        </div>									

                                        <div class="btn-cancel">
                                            <a href="<?php echo et_get_page_link('post-a-job') ?>" class="btn-background border-radius button" id="indeed_search">
                                                <?php _e("CANCEL", ET_DOMAIN); ?>
                                            </a>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>



                    <?php
                    $payment_gateways = et_get_enable_gateways();
//
//  STEP 5 -  PAYMENT
                    if (!empty($payment_gateways)) {
                        ?>
                        <div class="step" id='step_payment'>
                            <div class="toggle-title f-left-all">
                                <div class="icon-border"><?php echo array_shift($steps) ?></div>
                                <span class="icon" data-icon="2"></span>
                                <span><?php _e('Send payment and submit your job', ET_DOMAIN); ?></span>
                            </div>

                            <div class="toggle-content payment clearfix" style="display: none" id="payment_form">

                                <div class="label">
                                    <ul>
                                        <li class="checkout_package"><h4>Gewähltes Paket:</h4> 
                                            <span class="entry">  
                                                <?php if (isset($job['title'])) echo $job['title']; ?>
                                            </span></li>
                            <!-- <li class="checkout_addons">Zusatzoptionen:<span class="entry"></span></li> -->
                                        <li class="checkout_duration">Laufzeit: 
                                            <span class="entry"><?php if (isset($job['duration'])) echo $job['duration']; ?></span> Wochen
                                        </li>

                                        <li class="checkout_total"><h3>Gesamtkosten des Pakets:</h3> 
                                            <div>
                                                <label class="price_entry"> Paketpreis  </label>
                                                <span class="price_entry"> <?php if (isset($job['price'])) echo $job['price']; ?></span>
                                            </div>
                                            <div>	
                                                <label class="vat_entry"> zzgl. 19% MwSt.</label>
                                                <span class="vat_entry"> <?php if (isset($job['price'])) echo $job['price']; ?></span>
                                            </div>
                                            <div>
                                                <label class="total_entry"> Zu zahlender Betrag </label>
                                                <span class="total_entry"> <?php if (isset($job['price'])) echo $job['price']; ?></span>
                                            </div>

                                        </li>
                                    </ul>
                                </div>

                                <form method="post" action="" id="checkout_form">
                                    <div class="payment_info"> </div>
                                    <div style="position:absolute; left : -7777px; " >
                                        <input type="submit" id="payment_submit" />
                                    </div>

                                    <ul>
                                        <li>
                                            <input type="checkbox" id="agb_check" name="agb_check" required class="">  <label for="agb_check">Ich habe die <a href="<?php bloginfo('url'); ?>/agb/" target="about:blank">AGB gelesen</a></label> 
                                        </li>
                                        <li style="display: none">
                                            <input type="checkbox" id="post_facebook" name="data[email]" value="" class=""> <label for="post_facebook">Bei Facebook</label>
                                        </li>
                                        <li style="display: none">
                                            <input type="checkbox" id="post_newsletter" name="data[email]" value="" class=""> <label for="post_newsletter">In Newsletter</label>
                                        </li>
                                        <li style="display: none">
                                            <input type="checkbox" id="post_blog" name="data[email]" value="" class=""> <label for="post_blog">In Blog</label>
                                        </li>
                                        <li class="clearfix">
                                            <div class="f-left">
                                                <div class="title">Debit</div> 
                                                <div class="desc"></div> 
                                            </div> 
                                            <div class="f-right">
                                                <div class="btn-select ">
                                                    <div class="btn bg-btn-hyperlink border-radius select_payment_debit" data-price="" data-gateway="debit" > Jetzt kaufen</div>
                                                </div> 

                                                <div id="form_payment_debit">

                                                    <div class="control-group">
                                                        <label class="control-label" for="inputPayer">Zahlungsempfanger</label>
                                                        <input type="text" id="inputPayer" placeholder="Zahlungsempfanger"> 
                                                    </div>
                                                    <div class="control-group">
                                                        <label class="control-label" for="inputAmount">Betrag</label> 
                                                        <input type="text" id="inputAmount" placeholder="Betrag"> 
                                                    </div>
                                                    <div class="control-group">
                                                        <label class="control-label" for="accountNumber">Konto</label> 
                                                        <input type="text" id="accountNumber" name="accountNumber" placeholder="Konto"> 
                                                    </div>
                                                    <div class="control-group">
                                                        <label class="control-label" for="bankNumber">Blz</label> 
                                                        <input type="text" id="bankNumber" name="bankNumber" placeholder="Blz"> 
                                                    </div> 
                                                    <div class="clearfix"></div>
                                                    <div class="btn-select"> 
                                                        <div class="btn bg-btn-hyperlink border-radius payment_debit" data-price="" data-gateway="debit" >Jetzt kaufen</div>
                                                    </div> 
                                                </div>
                                            </div> 
                                        </li>
                                        <?php
                                        $je_default_payment = array('google_checkout', 'paypal', 'cash', '2checkout');

                                        do_action('before_je_payment_button', $payment_gateways);
                                        foreach ($payment_gateways as $key => $payment) {
                                            if (!isset($payment['active']) || $payment['active'] == -1 || !in_array($key, $je_default_payment))
                                                continue;
                                            ?>
                                            <li class="clearfix">
                                                <div class="f-left">
                                                    <div class="title"><?php echo $payment['label'] ?></div>
                                                    <?php if (isset($payment['description'])) { ?>
                                                        <div class="desc"><?php echo $payment['description'] ?></div>
                                                    <?php } ?>
                                                </div>

                                                <div class="btn-select f-right">
                                                    <!--<button class="bg-btn-hyperlink border-radius select_payment" data-price="" data-gateway="<?php echo $key ?>" > Jetzt kaufen</button>-->
                                                    <div class="btn bg-btn-hyperlink border-radius select_payment" data-price="" data-gateway="<?php echo $key ?>" > Jetzt kaufen</div>
                                                </div>

                                            </li>
                                            <?php
                                        }
                                        do_action('after_je_payment_button', $payment_gateways);
                                        ?>
                                    </ul>
                                </form> 
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="clearfix"></div>
                    <?php } ?>
                </div>
                <div class="clearfix"></div>
            </div> 
            <div class="clearfix"></div>
        </div> 
    </div>
</div> 
<style>.bg-footer{position: inherit !important;}</style>
<?php
get_footer();

