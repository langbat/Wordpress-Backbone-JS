<?php
/**
 * Template Name: Unternehmensprofil
 */
global $current_user;

$dash = get_page(6);

//get acf plugin for frontend-output
acf_form_head();


get_header();
?>
<div class="row-fluid" id="body_container">
    <div class="content-block" id="wrapper">
        <div class="heading">

            <h1 class="title"><?php _e("ACCOUNT", ET_DOMAIN); ?></h1>	 
        </div>

        <div id="page_company_profile" class="wrapper account-jobs account-step">
            <div class="account-title">
                <div class="main-center clearfix">
                    <ul class="account-menu">
                        <?php do_action('je_before_company_info_tab') ?>
                        <li><a href="<?php echo et_get_page_link('dashboard'); ?>"><?php _e('YOUR JOBS', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('profile'); ?>" class="active"><?php _e('COMPANY PROFILE', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('password'); ?>"><?php _e('PASSWORD', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('verbandverwaltung'); ?>" ><?php _e('Verbände', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('zertifikat'); ?>"><?php _e('praktischArzt-Zertifikat', ET_DOMAIN); ?></a></li>
                        <?php do_action('je_after_company_info_tab') ?>
                    </ul>        
                </div>
            </div>

            <div class="main-column account-content">
                <div class="form-account prime-form">
                    <form id="profile" action="">
                        <?php
                        global $current_user, $wp_query;
                        $cur_user = et_create_companies_response($current_user);
                        $user_logo = (!empty($cur_user['user_logo']) ) ? $cur_user['user_logo']['thumbnail'][0] : '';

                        $character = 499 - strlen($current_user->description);
                        ?>
                        <input type="hidden" name="id" value="<?php echo $current_user->ID ?>">
                        <?php
                        et_the_form(apply_filters('company_profile_fields', array(
                            'display_name' => array(
                                'name' => 'display_name',
                                'title' => __('Company Name', ET_DOMAIN),
                                'input_class' => 'not_empty',
                                'input_id' => 'display_name',
                                'type' => 'text',
                                'value' => $current_user->display_name
                            ),
                            'email' => array(
                                'name' => 'user_email',
                                'title' => __('Company Email', ET_DOMAIN),
                                'input_class' => 'is_email',
                                'input_id' => 'user_email',
                                'type' => 'text',
                                'value' => $current_user->user_email
                            ),
                            'logo' => array(
                                'id' => 'user_logo_container',
                                'name' => 'user_logo',
                                'input_id' => 'user_logo',
                                'title' => __('Company Logo', ET_DOMAIN),
                                'value' => $user_logo,
                                'type' => 'image',
                            ),
                            'user_url' => array(
                                'name' => 'user_url',
                                'title' => __('Company Website', ET_DOMAIN),
                                'type' => 'text',
                                'input_id' => 'user_url',
                                'value' => $current_user->user_url
                            ),
                            'bio' => array(
                                'name' => 'description',
                                'title' => ($character > 1) ? sprintf(__("Company Information (%s characters left)", ET_DOMAIN), '<span id="chacracter">' . $character . '</span>') : sprintf(__("Company Information (%s character left)", ET_DOMAIN), '<span id="chacracter">' . $character . '</span>'),
                                'type' => 'textarea',
                                'input_id' => 'description',
                                'value' => $current_user->description,
                                'class' => 'company-bio'
                            )
                                        )
                        ));
                        ?>
                    </form>

                    <script type="application/json" id="profile_data">
<?php echo json_encode($cur_user); ?>
                    </script>

                    <div class="line-hr"></div>
                    <div class="form-item">
                        <input id="submit_profile" class="bg-btn-action border-radius" type="submit" value="<?php _e('SAVE CHANGE', ET_DOMAIN); ?>" />
                    </div> 
                    <input type="hidden" id="0_char" value='<?php printf(__("Company Information (%s character left)", ET_DOMAIN), '<span id="chacracter">0</span>'); ?>' />
                    <input type="hidden" id="1_char" value='<?php printf(__("Company Information (%s character left)", ET_DOMAIN), '<span id="chacracter">1</span>'); ?>' />
                    <input type="hidden" id="n_char" value='<?php printf(__("Company Information (%s characters left)", ET_DOMAIN), '<span id="chacracter"></span>'); ?>' />
                </div>  
            </div>		<!-- end .main-column -->


            <div class="second-column widget-area padding-top30" id="static-text-sidebar">
                <div id="sidebar" class="user-dashboard-sidebar">


                </div>
            </div>   <!-- end .second-column -->    

        </div>
    </div>
</div>


<?php get_footer(); ?>