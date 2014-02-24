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
        <meta name="description" content="<?php echo get_bloginfo('description') ?>" />

        <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,400,300,700' rel='stylesheet' type='text/css'>

        <title><?php
            /*
             * Print the <title> tag based on what is being viewed.
             */
            global $page, $paged, $current_user, $user_ID;

            wp_title('|', true, 'right');

            // Add the blog name.
            // bloginfo('name');

            // Add the blog description for the home/front page.
            $site_description = get_bloginfo('description', 'display');
            // if ($site_description && ( is_home() || is_front_page() ))
            //     echo " | $site_description";

            // Add a page number if necessary:
            if ($paged >= 2 || $page >= 2)
                echo ' | ' . sprintf(__('Page %s', ET_DOMAIN), max($paged, $page));
            if (is_singular() && get_option('thread_comments'))
                wp_enqueue_script('comment-reply');
            ?></title>

        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
        <?php
        $general_opts = new ET_GeneralOptions();
        $favicon = $general_opts->get_favicon();
        if ($favicon) {
            ?>
                                                            <!-- <link rel="shortcut icon" href="<?php echo $favicon[0]; ?>"/> -->
        <?php } ?>


        <!-- enqueue json library for ie 7 or below -->
        <!--[if LTE IE 7]>
        <?php wp_enqueue_script('et_json') ?>
        <![endif]-->


        <?php wp_head(); ?>


        <!--[if IE]>
          <link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url') ?>/css/custom-ie.css" charset="utf-8" /> 
        <![endif]-->

        <!--[if lte IE 8]> 
          <link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url') ?>/css/custom-ie8.css" charset="utf-8" /> 
          <script src="<?php bloginfo('template_url') ?>/js/cufon-yui.js" type="text/javascript"></script>
          <script src="<?php bloginfo('template_url') ?>/js/Pictos_RIP_400.font.js" type="text/javascript"></script>
        <![endif]-->
    </head>


    <body <?php body_class() ?>> 
    <div id="container">
        <div class="row-fluid">
            <header>

                <div class="main-header bg-main-header" id="header_top">

                    <div class="row-fluid">
                        <div class="main-center mar-gin-header-second"> 
                            <!-- left content on header -->
                            <div class="f-left f-left-all"> 
                                <?php
                                $general_opts = new ET_GeneralOptions();
                                $website_logo = $general_opts->get_website_logo();
                                ?>
                                <!-- fix logo middle -->


                                <table class="fix-logo">
                                    <tr>
                                        <td>
                                            <a href="<?php echo home_url() ?>" class="logo"><img src="<?php echo $website_logo[0]; ?>" alt="<?php echo $general_opts->get_site_title(); ?>" /></a> 
                                        </td>
                                    <tr>
                                </table>


                            </div>

                            <!-- right content on header -->
                            <div class="header-technical f-right f-left-all">
                                <div class="category">  <?php #je_header_menu ();            ?>  

                                    <?php
                                    $menu = wp_nav_menu(array(
                                        'items_wrap' => '<ul class="menu-header-top">%3$s</ul>',
                                        'theme_location' => 'et_top',
                                        'echo' => false
                                    ));
                                    if (has_nav_menu('et_top') && $menu != '') {
                                        echo $menu;
                                    }
                                    ?>
                                </div>

                                <div class="ver-line"></div>

                                <?php
                                // post a job
                                $roles = $current_user->roles;
                                $user_role = array_pop($roles);

                                if (function_exists('et_is_resume_menu') && et_is_resume_menu() && !is_user_logged_in()) {
                                    ?>

                                    <div class="post-job ver-line">
                                        <a href="<?php echo et_get_page_link(array('page_type' => 'jobseeker-signup', 'post_title' => 'Create a Resume')); ?>" class="bg-btn-action btn-header border-radius" title="<?php _e('Create a Resume', ET_DOMAIN) ?>">
                                            <?php _e('CREATE A RESUME', ET_DOMAIN); ?><span class="icon f-right" data-icon="W"></span>
                                        </a>
                                    </div>
                                    <div ></div>


                                    <?php
                                } else {
                                    if ($user_role == 'company' || !is_user_logged_in() || current_user_can('manage_options')) {
                                        ?>
                                        <div class="post-job">
                                            <a href="<?php echo et_get_page_link('post-a-job') ?> " class=" btn-header border-radius current_page_item" title="Stelle schalten">Stelle schalten </a>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                            </div>

                            <?php
                            if (et_is_logged_in()) { // insert current user data here for js
                                $role = $current_user->roles;
                                $user_role = array_pop($role);
                                if ($user_role == 'company' || $user_role == 'administrator')
                                    $user_data = et_create_companies_response($current_user);
                                else
                                    $user_data = et_create_user_response($current_user);
                                ?>
                                <script type="application/json" id="current_user_data">    <?php echo json_encode($user_data); ?>   </script>
                            <?php } ?>

                            <div class="clear"></div>
                        </div>
                        <div class="clearfix"></div>
                    </div> <!-- end #main center -->

                    <div class="social-media-icons border-radius">
                        <a href="https://www.facebook.com/PraktischArzt" class="social_fb" target="_blank"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon-social-facebook.png'; ?>" alt="" /></a> 
                        <a href="https://twitter.com/praktischArzt" class="social_twitter" target="_blank"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon-social-twitter.png'; ?>" alt="" /></a> 
                        <a href="https://www.linkedIn.com" class="social_linkedIn" target="_blank"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon-social-linkedIn.png'; ?>" alt="" /></a> 
                        <a href="https://www.xing.com/companies/praktischarztonlineug" class="social_xing" target="_blank"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon-social-xing.png'; ?>" alt="" /></a> 
                    </div>

                    <?php
                    //include 2nd header 
                    include_once CHILDTHEMEPATH . '/template-2ndheader.php';
                    ?>
                    <div class="clearfix"></div>
                </div> <!-- end #main-header --> 

            </header>

        </div>


