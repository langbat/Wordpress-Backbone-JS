<?php
/*
 * This Theme is a child-theme of enginetheme
 *
 *
 *   Installation-notes:
 *   - install pomo-patch on WP-update (causes less memory consumption on language translation)
 *   - deactivate include payment.php in parent-functions.php (customized)
 *   - delete customization.css from parent-jobengine/css-directory  (not used)
 *   - deactivate customization-functionality in in jobengine/includes/customizer.php  (~ line 56 : // add_action('init', 'et_customizer_init');)
 *   - deactivate in parent-functions: require_once TEMPLATEPATH . '/includes/ajax_mobile.php';
 *   - deactivate in parent-functions: require_once TEMPLATEPATH . '/mobile/functions.php';

 */


/* --- snip -----  */



// error_reporting(E_ALL);

define('CHILDTHEMEPATH', get_stylesheet_directory());
/*
 *   Use Customized child-files instead of parent-theme-files
 *
 *   note: don't forget to deactive functions in partent-theme on updating
 */




// require_once CHILDTHEMEPATH . '/includes/index.php';
// require_once CHILDTHEMEPATH . '/includes/exception.php';
// require_once CHILDTHEMEPATH . '/includes/company.php';
// require_once CHILDTHEMEPATH . '/includes/job.php';
require_once CHILDTHEMEPATH . '/includes/payment.php';
// require_once CHILDTHEMEPATH . '/includes/languages.php';
// require_once CHILDTHEMEPATH . '/includes/application.php';
require_once CHILDTHEMEPATH . '/includes/template.php';
// require_once CHILDTHEMEPATH . '/includes/ajax_mobile.php';
// require_once CHILDTHEMEPATH . '/includes/schedule.php';
// require_once CHILDTHEMEPATH . '/includes/importer.php';
// require_once CHILDTHEMEPATH . '/includes/customizer.php';
// require_once CHILDTHEMEPATH . '/includes/widgets.php';
// require_once CHILDTHEMEPATH . '/includes/update.php';
// require_once CHILDTHEMEPATH . '/admin/index.php';
// require_once CHILDTHEMEPATH . '/admin/overview.php';
// require_once CHILDTHEMEPATH . '/admin/settings.php';
// require_once CHILDTHEMEPATH . '/admin/companies.php';
// require_once CHILDTHEMEPATH . '/admin/payments.php';
// require_once CHILDTHEMEPATH . '/admin/wizard.php';
// require_once CHILDTHEMEPATH . '/mobile/functions.php';
// include jobmap
// require_once CHILDTHEMEPATH . '/includes/job_map.php';








add_filter('et_registered_styles', 'je_child_register_styles', 20);

function je_child_register_styles($styles) {
    $styles['child_style'] = array(
        'src' => get_bloginfo('stylesheet_directory') . '/css/style.css',
        'deps' => array('stylesheet', 'custom', 'customization')
    );
    return $styles;
}

function filter_image_sizes($sizes) {
    unset($sizes['small_thumb']);
    return $sizes;
}

add_filter('intermediate_image_sizes_advanced', 'filter_image_sizes');

function pa_theme_setup() {
    // register new thumbnail image size
    add_image_size('box-header-image', 360, 220, true); //(cropped)
    add_image_size('small_thumb', 60, 60, true);
}

add_action('after_setup_theme', 'pa_theme_setup');


/*
 * Sonderzeichen im Benutzernamen möglich machen
 */

// apply_filters('sanitize_user', $username, $raw_username, $strict);
// function my_child_theme_remove_scripts() {
//     // deregister style
// }
// add_action( 'wp_enqueue_scripts', 'my_child_theme_remove_scripts', 11 );



function my_child_theme_load_scripts() {

    wp_dequeue_style('bootstrap');
    wp_enqueue_style('bootstrap', get_stylesheet_directory_uri() . '/css/bootstrap-responsive.css');

    // overwrite custom.css from parent-theme
    wp_dequeue_style('custom');
    wp_enqueue_style('custom', get_stylesheet_directory_uri() . '/css/custom.css');



    wp_deregister_script('front');
    wp_enqueue_script(
            'front', //handle
            get_stylesheet_directory_uri() . '/js/front.js', array('jquery', 'et-underscore', 'et-backbone', 'job_engine'), '1.0', true
    );
    wp_deregister_script('bootstrap-js');
    wp_enqueue_script(
            'bootstrap-js', //handle
            get_stylesheet_directory_uri() . '/js/bootstrap.js', array('jquery', 'et-underscore', 'et-backbone', 'job_engine', 'front'), '1.0', true
    );
    wp_deregister_script('index');
    wp_enqueue_script(
            'index', //handle
            get_stylesheet_directory_uri() . '/js/index.js', array('jquery', 'et-underscore', 'et-backbone', 'job_engine', 'front'), '1.0', true
    );
}

add_action('wp_enqueue_scripts', 'my_child_theme_load_scripts');



/*
 * remove wp-admin-css from frontend if no-admin
 */

function hide_admin_bar_from_front_end() {
    if (is_blog_admin()) {
        return true;
    }
    remove_action('wp_head', '_admin_bar_bump_cb');
    return false;
}

add_filter('show_admin_bar', 'hide_admin_bar_from_front_end');

// add_filter('show_admin_bar', '__return_false');




function remove_unused_sidebars() {
    // unregister_sidebar( 'sidebar-main' );
    unregister_sidebar('sidebar-home-top');
    unregister_sidebar('sidebar-home-bottom');
    unregister_sidebar('sidebar-companies');
    unregister_sidebar('sidebar-blog');
    unregister_sidebar('sidebar-company');
    unregister_sidebar('sidebar-job-detail');
    unregister_sidebar('sidebar-resume');
}

add_action('widgets_init', 'remove_unused_sidebars', 11);


/*
 * remove mobile functionality
 */

function je_remove_template_redirect_hook() {
    remove_action('template_redirect', 'hook_template_redirect');
}

add_action('init', 'je_remove_template_redirect_hook');

function custom_editor_styles() {
    //editor styles
    add_editor_style('css/editor-style.css');
}

add_action('init', 'custom_editor_styles');







/*
 *  remove Customization function
 *  add_action(‘init’, ‘et_customizer_init’);
 *  stored in includes > customzer.php, you may change at line #56 :
 */

function remove_customizer() {

    remove_action('init', 'et_customizer_init');
}

add_action('init', 'remove_customizer');

function remove_et_bodyclasses() {

    remove_filter('body_class', 'et_layout_classes');
}

// add_action( 'body_class', 'remove_et_bodyclasses' );




function pa_register_sidebar() {

    //register footer sidebar
    register_sidebar(array(
        'name' => 'Seiten in Fußleiste ',
        'id' => "footer-pages",
        'description' => 'Hier können die Seiten in der Fußleiste verwaltet werden',
        'class' => '',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => "</div>",
        'before_title' => '<h2 class="widgettitle">',
        'after_title' => "</h2>"
            )
    );

    //register homepage silder sidebar
    register_sidebar(
            array(
                'name' => __('Homepage Slider', ET_DOMAIN),
                'id' => 'homepage-slider',
                'description' => __('Widgets for displaying sliders here.', ET_DOMAIN),
                'class' => '',
                'before_widget' => '',
                'after_widget' => "</div>",
                'before_title' => '<h2 class="widgettitle">',
                'after_title' => "</h2>"
            )
    );
}

add_action('widgets_init', 'pa_register_sidebar');

//
// superscript-®
function enable_more_buttons($buttons) {
    $buttons[] = 'sub';
    $buttons[] = 'sup';
    return $buttons;
}

add_filter("mce_buttons_2", "enable_more_buttons");

// replace ® with sup-®
function r_replace($content) {
    $content = str_replace("®", "<sup>&reg;</sup>", $content);

    return $content;
}

add_filter("sup_r", "r_replace", 99);
add_filter('the_content', 'r_replace', 99);
add_filter('the_title', 'r_replace', 99);





/*
 * disbale file-editor
 */
define('DISALLOW_FILE_EDIT', true);


/*
 * remove unwanted dashboard areas
 */
add_action('wp_dashboard_setup', 'wpc_dashboard_widgets');

function wpc_dashboard_widgets() {
    global $wp_meta_boxes;
    // unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);  // Last comments
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);   // Incoming links
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);          // Plugins
    // Remove right now
    // unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}

/*
 * Disable RSS feeds
 */

function disable_feed() {
    wp_die(__('Our RSS feed is disabled. Please <a href="/">visit our homepage</a>.'));
}

add_action('do_feed', 'disable_feed', 1);
add_action('do_feed_rdf', 'disable_feed', 1);
add_action('do_feed_rss', 'disable_feed', 1);
add_action('do_feed_rss2', 'disable_feed', 1);
add_action('do_feed_atom', 'disable_feed', 1);

function removeHeadLinks() {
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'rsd_link');
}

add_action('init', 'removeHeadLinks');



/*
 *   add navigation menu in admin area
 */

function add_navi_menu() {
    add_menu_page('Navgation verwalten', 'Navigation', 'administrator', 'nav-menus.php', '', get_bloginfo('stylesheet_directory') . '/img/admin_navigation_icon.png', 7);
}

add_action('admin_menu', 'add_navi_menu');



/*
 *  add support for Login via E-Mail-adress
 *
 */

function login_with_email_address($username) {
    $user = get_user_by('email', $username);
    if (!empty($user->user_login))
        $username = $user->user_login;
    return $username;
}

add_action('wp_authenticate', 'login_with_email_address');

function change_username_wps_text($text) {
    if (in_array($GLOBALS['pagenow'], array('wp-login.php'))) {
        if ($text == 'Benutzername') {
            $text = 'Benutzername / E-Mailadresse';
        }
    }
    return $text;
}

add_filter('gettext', 'change_username_wps_text');




/*
 * add user nickname to registration form
 */

// add_action('register_form','myplugin_add_registration_fields');
function myplugin_add_registration_fields() {

    //Get and set any values already sent
    $user_nickname = ( isset($_POST['nickname']) ) ? $_POST['nickname'] : '';
    ?>

    <p>
        <label for="nickname">Anzeigename<br />
            <input type="text" name="nickname" id="nickname" value="<?php echo esc_attr(stripslashes($nickname)); ?>" class="regular-text">
        </label>
    </p>

    <?php
}

// add_action('user_register', 'myplugin_registration_save');
function myplugin_registration_save($user_id) {

    if (isset($_POST['nickname']))
        update_user_meta($user_id, 'nickname', $_POST['nickname']);
}

function frontpage_partner_output() {
    ?>
    <?php
    $partners = getAllPartner();
    $num = (sizeof($partners) >=4) ? 4 : sizeof($partners);
    if(!!$partners) {

    ?>
    <script type="text/javascript" language="javascript">
    $(document).ready(function() {
        $("#frontpage-partner").show();
        $(".slide_parter").jCarouselLite({
            vertical: false,
            hoverPause:false,
            btnNext: ".jcarousellite_next",
            btnPrev: ".jcarousellite_back",
            visible: <?php echo $num ;?>,
            auto:3000,
            speed:1000
        });
    });
    </script>
    <div class="row-fluid clear" id="frontpage-partner" style="display: none;">
        <div id="partner-logo" class="span10">
            <div class="row-fluid">
                <div class="slide_parter">
                    <ul class="row-fluid">
                    <?php
                        $logo = '';
                        foreach ($partners as $partner) {
                            $partner_info = getPartnerInfo($partner->ID);
                            $url = $partner_info['url'];

                            $logo = '<li id="'.$partner_info['ID'] .'">';
                            if($url != '' && filter_var($url, FILTER_VALIDATE_URL)){
                                $logo .= '<a href="' . $url . '" target="_blank">';
                            }
                            $logo .= '<img src="' . $partner_info['logo'] . '" class="img-rounded" alt="" /></a></li>';

                            echo $logo;
                        }
                    ?>
                    </ul>
                </div>

                <div class="jcarousellite_back"></div>
                <div class="jcarousellite_next"></div>
            </div>
        </div>
    </div>
    <?php
    }
}

function tripple_boxes_output() {
    global $post;
    $tripple_boxes_img_size = "box-header-image";
    unset($image);

    wp_reset_query();
    ?>
    <div class="tripple-boxes-wrapper row-fluid clear"><!-- class = tripple-boxes-wrapper-->

        <?php
        if (get_field('box-text-1')) {

            $image = get_field('box-image-1');
            ?>

            <div class="tripple-boxes span4" id="fp-box-1"><!-- class = tripple-boxes-->
                <div class="box-image">
                    <?php if (get_field('box-link-1')) { ?>
                        <a href="<?php the_field('box-link-1'); ?>"  class="box-link" >
                            <?php
                            if ($image) {
                                echo '<img src="' . $image['sizes']['box-header-image'] . '" alt="" />';
                            }
                            ?>
                            <div class="box-headline">
                                <?php
                                if (get_field('box-headline-1')) {
                                    echo '<h3>' . get_field('box-headline-1') . '</h3>';
                                }
                                ?>
                            </div>

                        </a><?php } ?>
                    <div class="clearfix"></div>
                </div>



                <div class="box-text">
                    <?php echo get_field('box-text-1'); ?>
                    <div class="clearfix"></div>
                </div>
            </div>
        <?php } ?>



        <?php
        if (get_field('box-text-2')) {

            $image = get_field('box-image-2');
            ?>

            <div class="tripple-boxes span4" id="fp-box-2">
                <div class="box-image">
                    <?php if (get_field('box-link-2')) { ?>
                        <a href="<?php the_field('box-link-2'); ?>" class="box-link" >
                            <?php
                            if ($image) {
                                echo '<img src="' . $image['sizes']['box-header-image'] . '" alt="" />';
                            }
                            ?>

                            <div class="box-headline">
                                <?php
                                if (get_field('box-headline-2')) {
                                    echo '<h3>' . get_field('box-headline-2') . '</h3>';
                                }
                                ?>
                            </div>
                        </a>
                    <?php } ?>
                    <div class="clearfix"></div>
                </div>

                <div class="box-text">
                    <?php echo get_field('box-text-2'); ?>
                    <div class="clearfix"></div>
                </div>
            </div>
        <?php } ?>




        <?php
        if (is_front_page()) { // tripple box on frontpage
            if (get_field('box-text-3')) {

                $image = get_field('box-image-3');
                ?>

                <div class="tripple-boxes span4" id="fp-box-3">
                    <div class="box-image">
                        <?php if (get_field('box-link-3')) { ?>
                            <a href="<?php the_field('box-link-3'); ?>" class="box-link" >
                                <?php
                                if ($image) {
                                    echo '<img src="' . $image['sizes']['box-header-image'] . '" alt="" />';
                                }
                                ?>
                                <div class="box-headline">
                                    <h3>Die neuesten Blogbeiträge</h3>
                                </div>

                            </a>
                        <?php } ?>
                        <div class="clearfix"></div>
                    </div>

                    <div class="box-text">
                        <?php
                        $args = array('numberposts' => '3');
                        $recent_posts = wp_get_recent_posts($args);
                        foreach ($recent_posts as $recent) {
                            echo '<span><a href="' . get_permalink($recent["ID"]) . '" title="' . esc_attr($recent["post_title"]) . '" >' . $recent["post_title"] . '</a> </span><br> <hr>';
                        }
                        ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <?php
            }
        } else {   // tripple boxes on regular pages
            if (get_field('box-text-3')) {

                $image = get_field('box-image-3');
                ?>

                <div class="tripple-boxes span4" id="fp-box-3">
                    <div class="box-image">
                        <?php if (get_field('box-link-3')) { ?>

                            <a href="<?php the_field('box-link-3'); ?>" class="box-link" >
                                <?php
                                if ($image) {
                                    echo '<img src="' . $image['sizes']['box-header-image'] . '" alt="" />';
                                }
                                ?>
                                <div class="box-headline">
                                    <?php
                                    if (get_field('box-headline-3')) {
                                        echo '<h3>' . get_field('box-headline-3') . '</h3>';
                                    }
                                    ?>
                                </div>

                            </a>
                        <?php } ?>
                        <div class="clearfix"></div>
                    </div>

                    <div class="box-text">
                        <?php echo get_field('box-text-3'); ?>
                        <div class="clearfix"></div>
                    </div>
                </div><?php
            }
        }
        ?>


        <script style="text/javascript">
            $(document).ready(function() {

            var biggestHeight = '0';
            // Loop through elements children to find & set the biggest height
            $(".tripple-boxes").each(function() {

            // If this elements height is bigger than the biggestHeight
            if ($(this).outerHeight() > biggestHeight) {
            // Set the biggestHeight to this Height
            biggestHeight = $(this).outerHeight();
            }
            });

            // Set the container height
            // $(".tripple-boxes").outerHeight(biggestHeight);
            $(".tripple-boxes").css("min-height", biggestHeight);
            });
        </script>
        <div class="clearfix"></div>
    </div> <!-- end .tripple-boxes-wrapper  -->

    <?php
}

//end function  tripple_box_output

function display_quickinfo() {
    ?>

    <div class="quickinfo-wrap clear">
        <?php
        if (get_field('quickinfo-facts'))
            echo '<div class="quickinfo-facts span4"><span class="quickinfo-headline">Quick-Info</span>' . get_field('quickinfo-facts') . '</div>';

        // video
        $video_url = get_field('video_url');
        $video = get_field('quickinfo-video');
        //update:  get youtube-video-id from url and paste it into embed link

        if (!!$video_url) {
            ?>
            <div class="span8">
                <iframe width="100%" height="417" src="http://<?php echo $video_url; ?>" frameborder="0" allowfullscreen></iframe>
            </div>
            <?php
        } elseif ($video) {
            ?>

            <div class="span8">
                <video class="video-player"  controls name="media">
                    <source src="<?php echo $video['url'] ?>" type="video/mp4">
                </video>
            </div>

        <?php } ?>

    </div>


    <?php
}

function PA_Company_Count($args = '') {
    if ($args == '') {
        $args = array(
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => "</aside>",
            'before_title' => '<div class="widget-title">',
            'after_title' => '</div>',
        );
    }

    extract($args);
    $before_widget = str_replace('widget ', 'widget bg-grey-widget companies-statis ', $before_widget);
    echo $before_widget;

    global $wpdb;
    $count = et_get_job_count();
    $companies = et_get_active_companies();  //  oder: et_get_companies_in_alphabet()
    $companies_count = count($companies);

    if (!empty($companies)):
        ?>

        <div class="frontpage_jobs">  <h2>Interessante Stellen</h2>
            <ul>

                <?php foreach ($companies as $key => $company) { ?>

                    <li>
                        <a class="company-item" href="<?php echo get_author_posts_url($company->ID, $company->user_login) ?>" title="<?php echo $company->display_name ?>">
                            <?php echo $company->display_name ?>
                        </a>
                    </li>

                    <?php
                }
            endif;
            echo '</ul>';


            echo $after_widget;
        }

        /*
         * frontpage-login-box
         *
         */

        function pa_do_loginbox() {
            ?>

            <?php if (!is_user_logged_in() || is_page_template('page-post-a-job.php') || is_page_template('page-upgrade-account.php')) : ?>

                <div class="toggle-content frontpage_login login clearfix modal-job modal-login" id="modal_login"  style="display: block">
                    <div class="form">
                        <form id="login" class="modal-form" novalidate="novalidate" autocomplete="on">

                            <div class="form-item">
                                <div> <span class="btn-blue"> Arbeitgeber-Login </span> </div>
                            </div>

                            <div class="form-item">
                                <div class="fld-wrap" id="fld_login_email">
                                  <!-- <input class="bg-default-input is_email is_user_name" tabindex="1" name="log_email" id="log_email" type="text" plaeholder="<?php _e('USERNAME or EMAIL ADDRESS', ET_DOMAIN); ?>" /> -->
                                    <input name="log_email" class="bg-default-input is_email is_user_name not_empty"  id="log_email" type="text" />
                                    <?php do_action('je_linkedin_button') ?>
                                </div>
                            </div>

                            <div class="form-item">
                                <div>
                                    <input class="bg-default-input is_pass" name="log_pass" id="log_pass" type="password" placeholder="<?php _e('PASSWORD', ET_DOMAIN); ?>"  />
                                </div>
                            </div>

                            <div class="form-item no-border-bottom clearfix">

                                <div class="btn-select button">
                                    <button class="bg-btn-action border-radius" type="submit" id="submit_login"><?php _e('LOGIN', ET_DOMAIN); ?></button>
                                    <button class="bg-btn-action border-radius" id="submit_login1" style="display: none;" ></button>

                                </div>
                                <div class="user-link">
                                    <a href="#" class="forgot-pass-link"><?php _e('FORGOT PASSWORD', ET_DOMAIN) ?></a>
                                     | <a href="#" class="register-link"><?php _e('REGISTER ACCOUNT', ET_DOMAIN); ?></a>
                                </div>
                            </div>

                        </form></div>
                </div>
                <?php
                    et_template_modal_register();
                    et_template_modal_forgot_pass();
                ?>

                <?php
            endif;  //end if user logged in
        }

        function pa_register($args) {

            $pa_args = array(
                'user_adress' => isset($_POST['user_adress']) ? $_POST['user_adress'] : 'noPost',
                'user_phone' => isset($_POST['user_phone']) ? $_POST['user_phone'] : 'noPost',
            );

            $args = array_merge($args, $pa_args);
        }

        add_action('je_before_user_register', 'pa_register');

        function pa_company_response($company_response) {
            global $user;

            if (is_numeric($user))
                $user = get_userdata((int) $user);

            if (empty($user->ID))
                return;


            $company_phone = trim(et_get_user_field($user->ID, 'company_phone'));
            $company_adress = trim(et_get_user_field($user->ID, 'company_adress'));

            $pa_company_response = array(
                'company_adress' => $company_adress,
                'company_phone' => $company_phone
            );


            $company_response = array_merge($company_response, $pa_company_response);
            return $company_response;
        }

        add_filter('', 'pa_company_response');

        function PA_Company_Profile($args = '', $instance = array('title' => '')) {
            if ($args == '') {
                $args = array(
                    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                    'after_widget' => "</aside>",
                    'before_title' => '<div class="widget-title">',
                    'after_title' => '</div>',
                );
            }

            $author_id = '';
            if (is_single()) {
                global $post;
                $author_id = $post->post_author;
            }

            if (get_query_var('author')) {
                $author_id = get_query_var('author');
            }

            extract($args);
            if ($author_id) {
                $company = et_create_companies_response($author_id);

                $company_logo = $company['user_logo'];

                $before_widget = str_replace('widget ', 'widget company-profile bg-grey-widget margin-top15 ', $before_widget);
                echo $before_widget;

                if ($instance['title'])
                    echo $before_title . $instance['title'] . $after_title
                    ?>
                <div class="thumbs">
                    <?php
                    if (!empty($company_logo['attach_id']) && file_exists(get_attached_file($company_logo['attach_id'])) == true) {
                        ?>
                        <a id="job_author_thumb" data="<?php echo $company['ID']; ?>" href="<?php echo $company['post_url']; ?>"
                           title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" class="thumb">
                            <img src="<?php echo ( isset($company_logo['large']) && !empty($company_logo['large']) ) ? $company_logo['large'][0] : $company_logo['small_thumb'][0]; ?>" id="company_logo_thumb" data="<?php echo $company_logo['attach_id']; ?>" />
                        </a>
                        <?php
                    } else {
                        ?>
                        <a id="job_author_thumb" data=""
                           title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" class="thumb">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/default_logo.jpg" id="company_logo_thumb" data="" />
                        </a>
                        <?php
                    }
                    ?>
                </div>

                <?php
                if (is_single()) {
                    $value = get_post_meta($post->ID, 'cfield-992', true);
                    echo!empty($value) ? '<div class="title company_contact">Ansprechpartner: ' . $value . '</div>' : '';
                }
                ?>

                <?php
                if (is_single()) {
                    $value = get_post_meta($post->ID, 'cfield-993', true);
                    echo!empty($value) ? '<div class="title company_telefon">Telefon: ' . $value . '</div>' : '';
                }
                ?>

                <?php if (!empty($company['display_name'])) : ?>
                    <div class="title company_name">
                        <a  id="job_author_name" class="name job_author_link" href="<?php echo get_author_posts_url($company['ID']) ?>"
                            title="<?php printf(__('View jobs posted by %s', ET_DOMAIN), $company['display_name']) ?>">
                                <?php echo $company['display_name'] ?>
                        </a>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="companyid" value="<?php echo $company['ID'] ?>" />

                <?php if (!empty($company['user_url'])) : ?>
                    <div class="info icon-default">
                        <?php
                        if (preg_match("/^(http:\/\/)/", $company['user_url']))
                            echo '<a id="job_author_url" target="_blank" rel="nofollow" href="' . $company['user_url'] . '">' . 'Homepage' . '</a>';
                        else
                            echo $company['user_url'];
                        ?>
                        <span class="icon" data-icon="A"></span>

                    </div>
                    <?php
                endif;

                if (!empty($company['description'])) :
                    ?>
                    <div class="info company-description">
                        <div class="content">
                            <?php echo $company['description']; ?>
                        </div>
                    </div>
                    <?php
                endif;


                echo $after_widget;
            } else {
                if (current_user_can('manage_options')) {
                    echo $before_widget;
                    _e("<strong>Admin notice:</strong> JE Company Profile widget should only be dragged to the company sidebar or job detail sidebar to function properly.", ET_DOMAIN);
                    echo $after_widget;
                }
            }
        }

        /** ngocanh
         * @return Array IDs of post
         */
        function findPostIdsInRadius($distance, $post_code) {
            global $wpdb;

            $radius = 6371.009;

            $address = $post_code . ', Germany';
            $url = 'http://maps.googleapis.com/maps/api/geocode/xml?address=' . $address . '&sensor=false';
            $parsedXML = simplexml_load_file($url);

            if ($parsedXML->status != "OK") {
                return array(0);
            }

            $lat = $parsedXML->result->geometry->location->lat;
            $lng = $parsedXML->result->geometry->location->lng;

            $maxLat = str_replace(',', '.', (float) $lat + rad2deg($distance / $radius));
            $minLat = str_replace(',', '.', (float) $lat - rad2deg($distance / $radius));

            // longitude boundaries (longitude gets smaller when latitude increases)
            $maxLng = str_replace(',', '.', (float) $lng + rad2deg($distance / $radius / cos(deg2rad((float) $lat))));
            $minLng = str_replace(',', '.', (float) $lng - rad2deg($distance / $radius / cos(deg2rad((float) $lat))));

            $sql = "SELECT *
FROM (
    SELECT post_id, SUM(lng) lng, SUM(lat) lat FROM (
    	SELECT post_id, meta_value lng, 0 lat FROM dev03postmeta WHERE meta_key = 'et_location_lng'
    	UNION
    	SELECT post_id, 0, meta_value  FROM dev03postmeta WHERE meta_key = 'et_location_lat'
    ) p
    GROUP BY post_id
) address
WHERE lat >= $minLat AND lat <= $maxLat AND lng >= $minLng AND lng <= $maxLng";
            $ids = array(0);
            $result = $wpdb->get_results($sql);
            foreach ($result as $id) {
                $ids[] = $id->post_id;
            }
            //find by location
            $sql = "SELECT post_id, meta_value FROM dev03postmeta WHERE meta_value = '%$post_code%' AND meta_key = 'et_plz'";
            $result = $wpdb->get_results($sql);
            foreach ($result as $id) {
                if (!in_array($id->post_id))
                    $ids[] = $id->post_id;
            }

            return $ids;
        }

        function resetRadiusSearch(&$args) {
            if (isset($args['location']) && $args['location']) {
                $radius = 50;
                if (isset($_SESSION['radius']))
                    $radius = $_SESSION['radius'];
                if (isset($_GET['radius'])) {
                    $radius = $_GET['radius'];
                    $_SESSION['radius'] = $radius;
                }

                $args['post__in'] = findPostIdsInRadius($radius, $args['location']);
                unset($args['location']);
            }
        }

        function getConditionFiledId($type) {
            $field_id = '';
            switch ($type) {
                case 'famulatur':
                case 'praktisches-jahr':
                case 'praxisfamulatur':
                    $field_id = 975; //975
                    break;
                case 'studentenjob':
                    $field_id = 1659; //1659 1656
                    break;
            }

            return $field_id;
        }

        function register_session() {
            if (!session_id())
                session_start();
        }

        add_action('init', 'register_session');

        /**
         * dev: thang.le
         */
        function formatDateDE($date) {
            return date_i18n('j F Y', strtotime($date));
        }

        function getJobPackageType($pid) {
            $basis = array(12, 1073, 1072);
            $top = array(13, 15, 16, 17, 18, 19);
            $premium = array(20, 21, 22);
            $pack = array();

            if (in_array($pid, $top)) {
                $pack['pack'] = 'top';
                $pack['datasort'] = 1;
            } elseif (in_array($pid, $premium)) {
                $pack['pack'] = 'premium';
                $pack['datasort'] = 1;
            } else {
                $pack['pack'] = 'basis';
                $pack['datasort'] = 0;
            }

            //var_dump($featured); // $pack;
            return $pack;
        }

        /**
         * dev: thang.le
         */
// get job or company verband info
        function getVerbandInfo($id, $type, $isNewJob = false) {
            //get company verband
            $verband_ids = array();
            if ($type == 'company') {
                $verband_ids = get_user_meta($id, 'verbandverwaltung', true);
                //get job verband
            } elseif ($type == 'job') {
                if ($isNewJob == true) {
                    $verband_ids = get_user_meta($id, 'verbandverwaltung', true);
                } else {
                    $verband_ids = get_post_meta($id, 'verbandverwaltung', true);
                }
            } else {
                return false;
            }
            if ((is_array($verband_ids) && $verband_ids[0] != 'null' && $verband_ids[0] != null)) {
                $verbands = array();
                foreach ($verband_ids as $verband_id) {
                    $post_verband = get_post($verband_id);

                    $verband_info = get_post_meta($verband_id);
                    $img = get_post_meta($verband_info['union_logo'][0], '_wp_attached_file', true);
                    if ($img != null) {
                        $verband_logo = home_url() . '/wp-content/uploads/' . $img;
                    } else {
                        $verband_logo = '';
                    }
                    $verband_decription = '';
                    if (!!$verband_info && isset($verband_info['union_decription'])) {
                        $verband_decription = $verband_info['union_decription'][0];
                    }

                    $verband = array(
                        'ID' => $verband_id,
                        'title' => $post_verband->post_title,
                        'logo' => $verband_logo,
                        'decription' => $verband_decription,
                            // 'Post' => $post_verband,
                            // 'Verband' => $verband_info,
                    );
                    $verbands = array_merge($verbands, array($verband));
                }
            }
            if (isset($verbands))
                return $verbands;
            else
                return false;
        }

//get list all Verband
        function getAllVerband() {
            $query = new WP_Query(array(
                'post_type' => 'union',
                'post_status' => 'publish',
                'orderby' => 'title',
                'order' => 'ASC',
                'posts_per_page' => -1
            ));

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    return $query->posts;
                endwhile;
            endif;
        }

//seclect verband of job or company
        function getVerband($id, $type, $isJob = false) {
            if (is_numeric($id)) {
                $All_Verbands = getAllVerband();
                if ($isJob == true) {
                    $CJ_verbands = getVerbandInfo($id, $type, true);
                } else {
                    $CJ_verbands = getVerbandInfo($id, $type);
                }
                if ($type == 'company') {
                    for ($i = 0; $i < 1; $i++) {
                        ?>
                        <select name="verband_<?php echo $i + 1; ?>" id="verband_<?php echo $i + 1; ?>">
                            <option value="null">- Nein -</option>
                            <?php foreach ($All_Verbands as $verband) { ?>
                                <option <?php echo (isset($CJ_verbands[$i]['ID']) && $CJ_verbands[$i]['ID'] == $verband->ID) ? 'selected="selected"' : '' ?> value="<?php echo $verband->ID; ?>"><?php echo $verband->post_title; ?></option>
                            <?php } ?>
                        </select>
                        <?php
                    }
                } else {
                    for ($i = 0; $i < 1; $i++) {
                        ?>
                        <div class="select-style btn-background border-radius">
                            <select class="input-field" name="verband_<?php echo $i + 1; ?>" id="verband_<?php echo $i + 1; ?>">
                                <option value="null">- Nein -</option>
                                <?php foreach ($All_Verbands as $verband) { ?>
                                    <option <?php echo (isset($CJ_verbands[$i]['ID']) && $CJ_verbands[$i]['ID'] == $verband->ID) ? 'selected="selected"' : '' ?> value="<?php echo $verband->ID; ?>"><?php echo $verband->post_title; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <?php
                    }
                }
            }
        }

//get company Zertifikat logo
        function getZertifikat($id) {
            $logo = '';
            if (is_numeric($id)) {
                $company_zertifikat = get_user_meta($id, 'company_certificate_pa', true);
                if (!!$company_zertifikat) {
                    $logo = '<span><img src="' . get_stylesheet_directory_uri() . '/img/icon-pa-zertifikat.png' . '"></span>';
                }
            }
            return $logo;
        }

//get job verband logo
        function getJobVerbandLogo($id) {
            $logo_box = '';
            if (is_numeric($id)) {
                $verbands = getVerbandInfo($id, 'job');
                if (!!$verbands) {
                    foreach ($verbands as $verband) {
                        $logo = '<span><img src="' . $verband['logo'] . '" title="' . $verband['title'] . '"></span>';
                        $logo_box .= $logo;
                    }
                }
            }
            return $logo_box;
        }

        function setUserZerfifikat($authorID, $verify = 1) {
            if ($verify)
                update_user_meta($authorID, 'company_certificate_pa', 1);
            $user_certificate = get_user_meta($authorID, 'certificate_buy_date', true);
            if ($user_certificate != "") {
                update_user_meta($authorID, 'certificate_buy_date', date('Y-m-d'));
            } else {
                add_user_meta($authorID, 'certificate_buy_date', date('Y-m-d'));
            }
        }

        //get list all Partner
        function getAllPartner() {
            $query = new WP_Query(array(
                'post_type' => 'partnerlogo',
                'post_status' => 'publish',
                'orderby' => 'id',
                'order' => 'ASC',
                'posts_per_page' => -1
            ));

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    return $query->posts;
                endwhile;
            endif;
        }

        function getPartnerInfo($id) {

            $post_partner = get_post($id);
            $partner_info = get_post_meta($id);

            $img = get_post_meta($partner_info['partner_logo'][0], '_wp_attached_file', true);
            if ($img != null) {
                $partner_logo = home_url() . '/wp-content/uploads/' . $img;
            } else {
                $partner_logo = '';
            }
            $partner_description = $partner_info['partner_description'][0];
            $partner_url = $partner_info['partner_url'][0];

            $partner = array(
                'ID' => $post_partner->ID,
                'title' => $post_partner->post_title,
                'logo' => $partner_logo,
                'description' => $partner_description,
                'url' => $partner_url
            );

            return $partner;
        }
