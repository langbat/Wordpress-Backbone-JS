<?php
/**
 * This Page display the registration Form for new users.
 *
 */
get_header();
?>

<div class="wrapper content-container" id="body_container">

    <div class="heading">
        <div class="main-center">
            <h1 class="title">Registrierung </h1>	
        </div>
    </div>		
    <div class="account-title">
    </div>

    <div class="main-center margin-top25" >

        <div class="main-column" style="width: 850px;">

            <?php if (!et_is_logged_in()) { ?>
                <div class="step" id='step_auth' style="padding:20px;">


                    <div class="toggle-title f-left-all">


                        <h3> Konto erstellen: </h3>
                    </div>


                    <div class="toggle-content login clearfix" >

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
                                            <input class="bg-default-input is_user_name"  name="reg_user_name" id="reg_user_name" type="text"/>
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
                                            <input class="bg-default-input is_email"  name="reg_email" id="reg_email" type="email"/>
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
                                            <input class="bg-default-input is_pass"  name="reg_pass" id="reg_pass" type="password" />
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
                                            <input class="bg-default-input" name="reg_pass_again" id="reg_pass_again" type="password" />
                                        </div>
                                    </div>
                                    <div class="form-item no-border-bottom clearfix">
                                        <div class="label">&nbsp;</div>
                                        <div class="btn-select">
                                            <button class="bg-btn-action border-radius" type="submit" id="submit_register"><?php _e('CONTINUE', ET_DOMAIN); ?></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <br>

                <div class="step" id="login_box" style="padding:20px;">

                    <h3>Haben Sie bereits ein Konto?  </h3>

                    <div class="form">
                        <form id="login" novalidate="novalidate" autocomplete="on">
                            <div class="form-item">
                                <div class="label">
                                    <h6 class=""><?php _e('USERNAME or EMAIL ADDRESS', ET_DOMAIN); ?></h6>
                                    <?php _e('Please enter your username or email', ET_DOMAIN); ?>
                                </div>
                                <div>
                                    <input class="bg-default-input is_email is_user_name"  name="log_email" id="log_email" type="text" />
                                </div>
                            </div>
                            <div class="form-item">
                                <div class="label">
                                    <h6 class=""><?php _e('PASSWORD', ET_DOMAIN); ?></h6>
                                    <?php _e('Enter your password', ET_DOMAIN); ?>
                                </div>
                                <div>
                                    <input class="bg-default-input is_pass" name="log_pass" id="log_pass" type="password" />
                                </div>
                            </div>
                            <div class="form-item no-border-bottom clearfix">
                                <div class="label">&nbsp;</div>
                                <div class="btn-select">
                                    <button class="bg-btn-action border-radius" type="submit" id="submit_login"><?php _e('LOGIN', ET_DOMAIN); ?></button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>

            <?php } else { ?>				


                <div class="header-second">
                    <div class="main-center breadcrumb">	
                        <h2>Sie sind bereits eingeloggt.</h2>

                        <a href="<?php echo get_permalink($dash); ?>/"> Hier geht es Zu Ihrer Stellenverwaltung</a>  

                        <span> Sie sind eingeloggt als: <b> <?php echo $current_user->display_name; ?></b></span> 

                        <span class="logout f-right"> 
                            <a href="<?php echo wp_logout_url(home_url()) ?>" title="ausloggen"> ausloggen <span class="icon" data-icon="Q"></span></a>
                        </span>

                    </div>

                </div>

            <?php } ?>




        </div>


    </div>
</div>
<script type="text/javascript">
<?php
/**
 * Render javascript objects for companies model
 */
if ($companies_count > 0) {
    echo 'var companies = [';
    $models = array();
    foreach ($alphabet_list as $letter => $companies) {
        foreach ((array) $companies as $company) {
            $models[] = "{'id' : " . $company->ID . ",'display_name':'" . $company->display_name . "','post_url': '" . get_author_posts_url($company->ID) . "'}";
        }
    }
    echo implode(',', $models);
    echo '];';
}
?>
</script>
<?php get_footer(); ?>