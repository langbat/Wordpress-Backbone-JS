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
                        <li><a href="<?php echo et_get_page_link('profile'); ?>"><?php _e('COMPANY PROFILE', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('password'); ?>"><?php _e('PASSWORD', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('verbandverwaltung'); ?>"  class="active"><?php _e("Verbände", ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('zertifikat'); ?>" ><?php _e("praktischArzt-Zertifikat", ET_DOMAIN); ?></a></li>
                        <?php do_action('je_after_company_info_tab') ?>
                    </ul>        
                </div>
            </div>

            <div class="main-column">
                <div class="form-account prime-form">
                    <div style="height: 130px;" class="form-item">
                        <div class="row-fluid">
                            <div class="span3">
                                <b><?php _e("Verbandzuweisung", ET_DOMAIN); ?></b>
                            </div>
                            <div class="span4">
                                <form id="verband" action="">
                                    <?php
                                    getVerband($current_user->ID,'company');
                                    ?>
                                </form>
                                <!-- Save company verband -->
                                <script type="text/javascript">
                                (function($) {
                                    jQuery(document).ready(function() {
                                        $("#submit_verband").click(function() {
                                            var verbands = Array();
                                            var user_id =<?=$current_user->ID?>;
                                            for (i = 1; i <= 3; i++) {
                                                verband = $('form#verband select#verband_'+i).val();
                                                if(verband != 'null')
                                                verbands = $.merge(verbands, Array(verband));
                                            }

                                            $.ajax({
                                                type: "POST",
                                                dataType: "json",
                                                url: et_globals.ajaxURL + "?action=et_company_sync&method=update_verband",
                                                data: "verbands="+verbands+"&user_id="+user_id,
                                            }).done(function(data) {
                                                if (data.success) {
                                                    var msg = data.msg;
                                                    var type = 'success';
                                                    location.reload(4000);
                                                } else {
                                                    var msg = data.msg;
                                                    var type = 'error';
                                                }
                                                $('div.notification').remove();
                                                $('body').prepend('<div class="notification autohide '+ type +'-bg">'+ msg +'<div class="main-center">' +'</div></div>');
                                                $('div.notification').hide()
                                                        .fadeIn('fast')
                                                        .delay(1000)
                                                        .fadeOut(3000, function() {
                                                    jQuery(this).remove();
                                                });
                                            });
                                        });
                                    });
                                })(jQuery);
                                </script>
                            </div>
                        </div>
                        <div class="row-fluid">
                            <div class="span3">
                                <span class="note" style="font-size: 13px;"><?php _e("Sind Sie Mitglied bei einem mit uns kooperierenden Verband ?", ET_DOMAIN); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="line-hr"></div>
                    <div class="form-item">
                        <input id="submit_verband" class="bg-btn-action border-radius" type="submit" value="<?php _e('SAVE CHANGE', ET_DOMAIN); ?>" />
                    </div> 
                </div>  
            </div>      <!-- end .main-column -->


            <div class="second-column widget-area padding-top30" id="static-text-sidebar">
                <div id="sidebar" class="user-dashboard-sidebar">


                </div>
            </div>   <!-- end .second-column -->    

        </div>
    </div>
</div>


<?php get_footer(); ?>