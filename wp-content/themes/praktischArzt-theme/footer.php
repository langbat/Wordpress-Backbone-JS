<?php
if (is_home() || is_singular('job') || is_page_template('page-dashboard.php') || is_author() || is_post_type_archive('job') ||
        is_tax('job_type') || is_tax('job_category') || is_search() || apply_filters('je_footer_can_print_modal_template', false)) {

    global $post, $user_ID;

    if (current_user_can('edit_others_posts') || is_page_template('page-dashboard.php') || ( is_singular('job') && $post->post_author == $user_ID )) {
        //$job_categories = et_get_job_categories ();
        je_modal_edit_job_template(et_create_jobs_response($post));
    }

    // display modal reject job when logging in as administrators
    if (current_user_can('edit_others_posts')) {
        echo et_template_modal_reject();
    }
    ?>


    <!-- provide template for rendering in js. used mostly in homepage & company page -->
    <script type="text/template" id="job_list_item">  <?php //echo et_template_frontend_job()   ?> 

    </script>
    <!-- end template of job list item -->



    <?php
}

if (is_page_template('page-upgrade-account.php') || is_page_template('page-dashboard.php')) {
    echo '<div style="display:none" >';
    wp_editor($applicant_detail, 'call-to-add-tinymce', je_editor_settings());
    echo '</div>';
}

if (!is_user_logged_in() || is_page_template('page-post-a-job.php') || is_page_template('page-upgrade-account.php')) {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('li a[title="Stellenverwaltung"]').attr('href', '')
            .click(function(event) {
                event.preventDefault();
                $('div.notification').remove();
                $('body').prepend('<div class="notification autohide error-bg">Bitte loggen Sie sich ein<div class="main-center"></div></div>');
                $('div.notification').hide()
                        .fadeIn('fast')
                        .delay(1000)
                        .fadeOut(3000, function() {
                    jQuery(this).remove();
                });
            });
        });
    </script>
    <div class="modal-job modal-login" id="modal_login" >
        <div class="edit-job-inner">
            <div class="title"><?php _e('LOGIN', ET_DOMAIN); ?></div>
            <form class="modal-form" id="login" novalidate="novalidate" autocomplete="on">
                <div class="content">

                    <div class="form-item">
                        <div class="label">
                            <h6><?php _e('Username or email address', ET_DOMAIN); ?></h6>
                        </div>
                        <div class="fld-wrap" id="fld_login_email">
                            <input name="log_email" class="bg-default-input is_email is_user_name not_empty" id="log_email" type="text" />
                            <?php do_action('je_linkedin_button') ?>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label">
                            <h6><?php _e('Password', ET_DOMAIN); ?></h6>
                        </div>
                        <div class="fld-wrap" id="fld_login_password">
                            <input name="log_pass" class="bg-default-input is_pass not_empty" id="log_pass" type="password" />
                        </div>
                    </div>

                </div>
                <div class="footer">
                    <div class="button">  
                        <input type="submit" class="bg-btn-action border-radius" value="<?php _e('Login', ET_DOMAIN); ?>" id="submit_login">
                        <span class="arr">&raquo;</span>
                    </div>
                    <a href="#" class="forgot-pass-link"><?php _e('FORGOT PASSWORD', ET_DOMAIN) ?></a>
                </div>
            </form>


            <div class="form-item">
                <div class="label">
                    <h6><?php _e('Username', ET_DOMAIN); ?></h6>
                </div>
                <div class="fld-wrap" id="">
                    <input name="register_user_name" class="bg-default-input is_user_name not_empty required" id="register_user_name" type="text" />

                </div>
            </div>
            <div class="form-item">
                <div class="label">
                    <h6><?php _e('Email', ET_DOMAIN); ?></h6>
                </div>
                <div class="fld-wrap" id="">
                    <input name="register_email" class="bg-default-input is_email is_user_name not_empty required" id="register_email" type="text" />

                </div>
            </div>
            <div class="form-item">
                <div class="label">
                    <h6><?php _e('Password', ET_DOMAIN); ?></h6>
                </div>
                <div class="fld-wrap" id="">
                    <input name="register_pass" class="bg-default-input is_pass not_empty required" id="register_pass" type="password" />
                </div>
            </div>

            <div class="form-item">
                <div class="label">
                    <h6><?php _e('Retype Password', ET_DOMAIN); ?></h6>
                </div>
                <div class="fld-wrap" id="">
                    <input name="re_register_pass" class="bg-default-input is_pass not_empty required" id="re_register_pass" type="password" />
                </div>
            </div>

            <div class="form-item" id="term-of-use">

                <div class="fld-wrap" id="">
                    <input name="register_term" class="bg-default-input is_pass not_empty" id="term_of" type="checkbox" />
                    <label for="term_of"><?php printf(__("I agree with <a href='%s' > Terms of use </a>", ET_DOMAIN), et_get_page_link('terms-of-use')); ?> </label>
                </div>
            </div>

        </div>
        <div class="footer font-quicksand">
            <div class="button">  
                <input type="submit" class="bg-btn-action border-radius" value="<?php _e('Register', ET_DOMAIN); ?>" id="submit_register">
                <span class="arr">&raquo;</span>
            </div>
        </div>
    </form>
    </div> <!-- .edit-job-inner -->
    <div class="modal-close"></div>
    </div><!-- .modal-job -->
    <?php
    et_template_modal_register();
    et_template_modal_forgot_pass();
}



$general_opt = new ET_GeneralOptions();
$copyright = $general_opt->get_copyright();
$has_footer_nav = false;

if (has_nav_menu('et_footer')) {
    $has_footer_nav = true;
}
?>
</div>
<footer class="bg-footer"> <div class="footer_color"></div>
    <div class="main-center">
        <div class="f-left f-left-all">

            <div class="copyright"><?php echo $copyright; ?></div>	

            <?php
            if (has_nav_menu('et_footer')) {
                wp_nav_menu(array(
                    'theme_location' => 'et_footer',
                    'container' => 'ul',
                    'menu_class' => 'menu-bottom'
                ));
            }

            do_action('je_footer_bar');
            ?>


            <?php if (is_active_sidebar('footer-pages')) { ?>
                <div id="footer-pages-sidebar" class="widget-area">   
                    <?php dynamic_sidebar('footer-pages'); ?>   
                </div>
            <?php } ?>

        </div>



        <div class="f-right f-left-all">
                <!-- <div class="follow"><?php _e("Follow Us", ET_DOMAIN); ?></div> -->
            <?php et_follow_us() ?>
        </div>

        <div class="clear"></div>
    </div>
</footer>



<!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<!--[if lte IE 8]>
        <script type="text/javascript"> 
                Cufon.replace('.icon'); // Works without a selector engine
                Cufon.replace('.icon:before'); // Works without a selector engine
                
                jQuery(".icon").each( function(){
                        var cthis = jQuery(this);
                        cthis.append( cthis.attr("data-icon") );
                });			

                Cufon.now(); 
        </script> 
<![endif]-->

<?php wp_footer(); ?>
</body>
</html>