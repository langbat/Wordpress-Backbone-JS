<?php
global $et_global, $post, $user_ID;
$imgUrl = $et_global['imgUrl'];
$jsUrl = $et_global['jsUrl'];
$job = $post;

// $cat		=	wp_get_post_categories($post->ID);
// $cat		=	get_category($cat[0]); 

get_header();
?>
<div class="row-fluid" id="body_container"> 
    <div class="clearfix content-block" id="wrapper">
        <div id="single-job">
            <div class="row-fluid">
                <style type="text/css">
                    .plupload	 {
                        width: 200px !important;
                        height: 100px !important;
                    }
                </style>
                <?php
                if (have_posts()) {
                    the_post();
                    $job_data = et_create_jobs_response($post);
                    $job_cats = $job_data['categories'];
                    $job_types = $job_data['job_types'];

                    $job_location = $job_data['location'];
                    $job_full_location = $job_data['full_location'];

                    $company = et_create_companies_response($post->post_author);
                    $company_logo = $company['user_logo'];

                    $expire = $job_data['expired_date'];

                    if (current_user_can('edit_others_posts')) {
                        ?>
                        <div class="heading-message message" <?php
                        if ($post->post_status == 'publish') {
                            echo 'style ="display:none;"';
                        }
                        ?>>
                            <div class="main-center">
                                <div class="text">
                                    <?php
                                    $statuses = array(
                                        'draft' => __('NOT READY', ET_DOMAIN),
                                        'pending' => __('PENDING', ET_DOMAIN),
                                        'archive' => __('ARCHIVED', ET_DOMAIN),
                                        'reject' => __('REJECTED', ET_DOMAIN),
                                        'publish' => __('ACTIVE', ET_DOMAIN)
                                    );
                                    if ($post->post_status == 'pending')
                                        _e("THIS JOB IS PENDING. YOU CAN APPROVE OR REJECT IT.", ET_DOMAIN);
                                    else
                                        printf(__("THIS JOB IS %s.", ET_DOMAIN), $statuses[$post->post_status]);
                                    ?>
                                </div>
                                <div class="arrow"></div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="heading">

                        <div class="main-center">
                            <?php if (current_user_can('edit_others_posts') || $user_ID == $job->post_author) { ?>
                                <div class="technical  f-right job-controls">

                                    <!-- admin action -->
                                    <?php if (current_user_can('edit_others_posts')) { ?>
                                        <div class="f-right" id="adminAction" <?php
                                        if ($post->post_status == 'publish') {
                                            echo 'style ="display:none;"';
                                        }
                                        ?> >
                                            <a href="#" class="color-active" id="approveJob">
                                                <span data-icon="3" class="icon"></span>
                                                <?php _e("APPROVE", ET_DOMAIN); ?>
                                            </a>
                                            <a rel="modal-box" href="#modal_reject_job" class="color-pending">
                                                <span data-icon="*" class="icon"></span><?php _e("REJECT", ET_DOMAIN); ?>
                                            </a>
                                        </div>
                                    <?php } ?>
                                    <!-- admin action 

                                    <a id="edit-single-job" href="#" class="color-edit">-->
                                    <a rel="modal-box" href="#modal_edit_job" class="color-edit">
                                        <span data-icon="p" class="icon"></span><?php _e("EDIT THIS JOB", ET_DOMAIN); ?>
                                    </a>          
                                </div>
                            <?php } ?>

                            <h1 data="<?php echo $job->ID; ?>" class="title job-title" id="job_title"><?php the_title() ?>
                                <?php
                                    /**
                                    if ($job_data['post_views'] > 0) { ?>  
                                        <span class="vcount">(<?php printf(_n('1 Aufrufe', '%s Aufrufe', $job_data['post_views'], ET_DOMAIN), $job_data['post_views']); ?>)</span>
                                    <?php }
                                    **/
                                ?>
                            </h1>
                        </div>
                    </div>

                    <div class="heading-info clearfix mapoff">
                        <div class="main-center">
                            <div class="info f-left f-left-all">

                                <div class="company job-info"> 

                                    <!-- Job author, type, location, posted date -->
                                    <div class="company-name">
                                        <a  href="<?php echo get_author_posts_url($company['ID']) ?>" data="<?php echo $company['ID']; ?>"
                                            title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" class="name job_author_link" id="job_author_name">
                                                <?php echo $company['display_name'] ?>
                                        </a>
                                    </div>

                                    <div class="text-left">
                                        <?php
                                        $value = get_post_meta($job->ID, 'cfield-984', true);
                                        $options = JEP_Field::get_options(984);
                                        foreach ($options as $option) {

                                            if ($option->ID == $value) {
                                                echo $option->name;
                                                break;
                                            }
                                        }
                                        ?>
                                    </div>


                                    <!-- job type -->
                                    <div id="job_type" class="job-type">
                                        <?php
                                        if (!empty($job_types)) {
                                            foreach ($job_types as $job_type) {
                                                ?>
                                                <input class="job-type-slug" type="hidden" value="<?php echo $job_type['slug']; ?>"/>
                                                <a class="<?php echo 'color-' . $job_type['color']; ?>" href="<?php echo $job_type['url'] ?>" title="<?php printf(__('View posted jobs in %s ', ET_DOMAIN), $job_type['name']) ?>">
                                                    <span class="flag"></span>
                                                    <?php echo $job_type['name'] ?>
                                                </a>
                                                <?php
                                                break;  // first jobtype only (?)
                                            }
                                        }
                                        ?>
                                    </div>

                                    <!-- job location -->
                                    <div class="job-local">
                                        <div class="delimiter text-left">
                                            <?php
                                            $value = get_post_meta($job->ID, 'cfield-1036', true);
                                            $options = JEP_Field::get_options(1036);
                                            foreach ($options as $option) {

                                                if ($option->ID == $value) {
                                                    echo $option->name;
                                                    break;
                                                }
                                            }
                                            ?>
                                        </div>
                                        <?php if ($job_full_location != '') { ?>
                                            <span class="icon location" data-icon="@"></span>
                                            <?php
                                            $tooltip = '';
                                            if ($job_full_location != __('Anywhere', ET_DOMAIN) && $job_data['location_lat'] != '' && $job_data['location_lng'] != '') {
                                                $tooltip = __('Karte &raquo', ET_DOMAIN);
                                            }
                                            ?>
                                            <div class="f-left" id="job_location">
                                                <?php echo $job_full_location ?>
                                            </div>
                                            <div title="<?php echo $tooltip ?>" class="job-location f-left" id='view-map'><?php echo $tooltip ?></div>
                                            <input type="hidden" name="jobFullLocation" value="<?php echo $job_full_location ?>" >
                                            <input type="hidden" name="jobLocLat" value="<?php echo $job_data['location_lat'] ?>" >
                                            <input type="hidden" name="jobLocLng" value="<?php echo $job_data['location_lng'] ?>" >
                                        <?php } ?>
                                    </div> 

                                </div>

                                <div class="job_categories clear"> <span>Fachgebiet:</span>
                                    <?php
                                    $i = 0;
                                    if (!empty($job_cats)) {
                                        foreach ($job_cats as $i => $cat) {   // get_category_link($cat) .'">' . $cat->name
                                            if ($i != 0)
                                                echo ', ';
                                            ?>
                                            <a href="<?php echo $cat['url']; ?>"> <?php echo $cat['name']; ?> </a> 
                                            <?php
                                            $i++;
                                        }
                                    }
                                    ?>
                                </div>					

                            </div>

                            <!-- job map -->
                            <div class="clear"></div>
                            <div id="jmap" class="<?php if ($job_location == __('Anywhere', ET_DOMAIN)) echo 'überregional '; ?>heading-map hide">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span8">
                        <div class="main-column">

                            <div class="job-detail tinymce-style">
                                <?php if ($condition_field_id = getConditionFiledId($job_types[0]['slug'])): ?>
                                    <?php
                                    $value = get_post_meta($job->ID, 'cfield-' . $condition_field_id, true);

                                    if (isset($value[$condition_field_id]) && count($value[$condition_field_id])) {
                                        $value = $value[$condition_field_id];
                                        $options = JEP_Field::get_options($condition_field_id);

                                        echo '<h3>Konditionen / Vergütung:</h3><ul class="condition-list">';
                                        foreach ($options as $option) {
                                            if (in_array($option->ID, $value)) {
                                                echo '<li class="checked">' . $option->name . '</li>';
                                            }
                                        }
                                        echo '</ul>';
                                    }
                                    ?>
                                <?php endif; ?>

                                <?php do_action('je_before_job_description', $job); ?>
                                <div class="description" id="job_description"> <h3>Stellenbeschreibung</h3>

                                    <?php
                                    // job description 
                                    the_content();
                                    ?>
                                </div>

                                <?php
                                // action for plugin job fields
                                do_action('je_single_job_fields', $job);

                                do_action('je_after_job_description', $job);
                                ?>
                            </div>

                            <?php
                            /*
                             *  apply template 		 //include(locate_template('template-apply.php'));
                             */
                            get_template_part('template-apply');
                            ?>				

                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="span4">
                        <div class="second-column widget-area" id="sidebar-job-detail">

                            <!-- post date -->
                            <span class="icon date" data-icon="\"></span>
                                  <div class="date">		
                                  <?php
                                  the_date();
                                  // echo formatDateDE($job_data['date']);
                                   ?>


                    </div>				


                    <?php
                    //do company profile sidebar
                    PA_Company_Profile();
                    ?>

                    <div class="company_label clear">
                        <?php
                        //get company Zertifikat logo
                        echo getZertifikat($job_data['author_id']);

                        //get job verband logo
                        echo getJobVerbandLogo($job_data['ID']);
                        ?>
                    </div>
                    <br>


                    <!-- social share -->
                    <div class="sharing">
                        <?php
                        $api = get_option('et_addthis_api', '');
                        if ($api)
                            $api = '#pubid=' . $api;
                        ?>

                        <!-- AddThis Button BEGIN -->
                        <div class="addthis_toolbox addthis_default_style ">
                            <ul>
                                <li><a id="button_facebook_share" class="at300b sharing-btn"><img src="<?php bloginfo('template_directory') ?>/img/share-fb.png" width="40" height="40" border="0" alt="Share to Facebook" /></a></li>
                                <li><a id="button_twitter_share" class="at300b sharing-btn"><img src="<?php bloginfo('template_directory') ?>/img/share-twitter.png" width="40" height="40" border="0" alt="Share to Facebook" /></a></li>
                                <li><a id="button_google_share" class="at300b sharing-btn"><img src="<?php bloginfo('template_directory') ?>/img/share-gplus.png" width="40" height="40" border="0" alt="" /></a></li>
                                <li><a id="button_linkedin_share"  class="at300b sharing-btn"><img src="<?php bloginfo('template_directory') ?>/img/share-in.png" width="40" height="40" border="0" alt="" /></a></li>
                            </ul>
                        </div>
                        <script type="text/javascript">
                            var addthis_config = {data_track_addressbar: false};
                            (function($) {
                                jQuery(document).ready(function() {
                                    $(".addthis_toolbox a#button_facebook_share").click(function() {
                                        window.open(
                                                'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(location.href),
                                                'facebook-share-dialog',
                                                'width=626,height=436,top=100,left=400');
                                        return false;
                                    });
                                    $('.addthis_toolbox a#button_linkedin_share').click(function() {
                                        window.open(
                                                'http://www.linkedin.com/shareArticle?mini=true&url=' + location.href + '&title=<?php echo the_title(); ?>', 'Linkedin-share-dialog',
                                                'width=800,height=436,top=100,left=400');
                                        return false;
                                    });
                                    $('.addthis_toolbox a#button_google_share').click(function() {
                                        window.open(
                                                'https://plus.google.com/share?url=' + encodeURIComponent(location.href),
                                                'google-plus-share-dialog',
                                                'width=626,height=436,top=100,left=400');
                                        return false;
                                    });
                                    $('.addthis_toolbox a#button_twitter_share').click(function() {
                                        window.open(
                                                'http://twitter.com/share?text=' + encodeURIComponent(location.href),
                                                'google-plus-share-dialog',
                                                'width=626,height=436,top=100,left=400');
                                        return false;
                                    });

                                });
                            })(jQuery);
                        </script>
                        <script type="text/javascript">
                            (function($) {
                                jQuery(document).ready(function() {
                                    // $("a#edit-single-job").click(function() {
                                    //     $("#modal_edit_job").show();
                                    //     $("#lean_overlay").show();
                                    // });
                                    // $("#modal_edit_job .modal-close ,#lean_overlay").click(function() {
                                    //     $("#modal_edit_job").hide();
                                    //     $("#lean_overlay").hide();
                                    //     $("#modal_edit_job").css("display", "none");
                                    // });

                                    $("select#job_types").change(function() {
                                        var job_types = $('#job-details select#job_types').val();
                                        var job_id = $('#job-details input#id').val();
                                        $.ajax({
                                            type: "POST",
                                            dataType: 'html',
                                            url: et_globals.ajaxURL,
                                            data: {action: 'et_conditions_sync', job_types: job_types, job_id: job_id}
                                        }).done(function(data) {
                                            $("#load_conditions_by_job_type").html(data);
                                        });
                                    });
                                    
                                    $("div#submit-form").click(function() {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: et_globals.ajaxURL + "?action=et_job_sync&method=edit_job",
                                            data: $('#modal_edit_job #job_form').serialize()
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
                        <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js<?php echo $api ?>"></script>
                        <!-- AddThis Button END -->
                    </div>
                    <!-- end social share -->


                </div> <!-- end 2nd column -->
                <div class="clearfix"></div>
            </div>

            <div class="clearfix"></div>
            <!-- inject job data here for bootstrapping model -->
            <script type="application/json" id="job_data">  <?php echo json_encode($job_data); ?> </script>
            <script type="application/json" id="company_data"> <?php echo json_encode($company); ?> </script>

            <script type="text/template" id="apply_button">
                <button title="<?php __('Apply for this job', ET_DOMAIN); ?>" class="bg-btn-action border-radius btn-default btn-apply applyJob" id="apply2">
                <?php __("APPLY FOR THIS JOB", ET_DOMAIN); ?>
                <span class="icon" data-icon="R"></span>
                </button> 
            </script>
            <script type="text/template" id="how_to_apply_button">
                <button title="<?php __('HOW TO APPLY', ET_DOMAIN); ?>" class="bg-btn-action border-radius btn-default btn-apply applyJob" id="apply3">
                <?php __("HOW TO APPLY", ET_DOMAIN); ?>
                <span class="icon" data-icon="O"></span>
                </button> 
            </script>
            <script type="text/template" id="apply_detail">
                <h5><?php __("HOW TO APPLY FOR THIS JOB", ET_DOMAIN); ?></h5>
                <div class="description"><%=applicant_detail%></div>
                <a href="#" class="back-step icon" data-icon="D"></a>
            </script>
            <div class="clearfix"></div>
        </div>

    <?php } ?>
</div>
</div>
<div class="clearfix"></div>
</div>  
<?php
get_footer();