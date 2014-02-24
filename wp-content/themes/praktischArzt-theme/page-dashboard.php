<?php
/**
 * Template Name: Benutzer-Dashboard
 */
if (isset($_GET['applicant_id']) && $_GET['applicant_id'] != '') {
    $attachment = get_children(array(
        'post_type' => 'attachment',
        'post_parent' => $_GET['applicant_id'],
        'posts_per_page' => -1
    ));

    $zipname = get_template_directory() . '/file.zip';
    $zip = new ZipArchive();
    $zip->open($zipname, ZipArchive::CREATE);

    foreach ($attachment as $key => $att) {
        $file = get_attached_file($att->ID);
        $arr = explode('/', $file);
        $name = array_pop($arr);
        $zip->addFile($file, $name);
    }

    $zip->close();


    header("Cache-Control: public");
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");

    // header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename=filename.zip');
    header('Content-Length: ' . filesize($zipname));

    ob_start();
    readfile($zipname);
    ob_end_flush();
    unlink($zipname);
}


global $current_user, $user_ID;

$job_opt = new ET_JobOptions ();
$widgets = $job_opt->get_dashboard_sidebar();

$arrAuthors = array();

$company        = et_create_companies_response($user_ID);
$company_logo   = $company['user_logo'];

// add this company data to the array to pass to js
if(!isset($arrAuthors[$company['id']])){
    $arrAuthors[$company['id']] = array(
        'display_name'  => $company['display_name'],
        'user_url'      => $company['user_url'],
        'user_logo'     => $company_logo
    );
}

get_header();
?>

<div class="row-fluid" id="body_container">
    <div class="clearfix content-block" id="wrapper">
        <div class="heading">

            <!-- <div class="technical logout f-right">
                    <a href="<?php echo wp_logout_url(home_url()) ?>"><?php _e('LOGOUT', ET_DOMAIN); ?> <span class="icon" data-icon="Q"></span></a>
            </div> -->
            <h1 class="title"><?php _e("ACCOUNT", ET_DOMAIN); ?></h1>       

        </div>

        <div id="page_company_profile" class="wrapper account-jobs account-step">
            <?php if ($current_user->role)  ?>
            <div class="account-title">
                <div class="row-fluid">
                    <ul class="account-menu">
                        <?php do_action('je_before_company_info_tab') ?>
                        <li><a href="<?php echo et_get_page_link('dashboard'); ?>" class="active"><?php _e('YOUR JOBS', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('profile'); ?>"><?php _e('COMPANY PROFILE', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('password'); ?>"><?php _e('PASSWORD', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('verbandverwaltung'); ?>" ><?php _e('Verbände', ET_DOMAIN); ?></a></li>
                        <li><a href="<?php echo et_get_page_link('zertifikat'); ?>"><?php _e('praktischArzt-Zertifikat', ET_DOMAIN); ?></a></li>
                        <?php do_action('je_after_company_info_tab') ?>
                    </ul>        
                </div>
            </div>

            <div class="row-fluid">
                <?php
                $main_colum = 'full-column';

                /**
                 * Display payment plans that company have left
                 */
                $purchase_plans = et_get_purchased_quantity($current_user->ID);

                $plans = et_get_payment_plans();

                $purchase_count = 0;
                foreach ($purchase_plans as $id => $quantity) {
                    if (isset($plans[$id]))
                        $purchase_count += $quantity;
                }
                $resume_view_duration = 0;
                if (function_exists('je_get_resume_view_duration')) {
                    $resume_view_duration = je_get_resume_view_duration($user_ID);
                    if ($resume_view_duration > time())
                        $resume_view_duration = $resume_view_duration - time();
                }

                if (!empty($widgets) || current_user_can('manage_options') || $purchase_count) {
                    $main_colum = 'main-column';
                }
                ?>

                <?php
                $statuses = array(
                    'archive' =>
                    array(
                        'title' => __('ARCHIVED', ET_DOMAIN),
                        'class' => 'expired'
                    ),
                    'draft' =>
                    array(
                        'title' => __('DRAFT', ET_DOMAIN),
                        'class' => 'pending'
                    ),
                    'pending' =>
                    array(
                        'title' => __('PENDING', ET_DOMAIN),
                        'class' => 'pending'
                    ),
                    'publish' =>
                    array(
                        'title' => __('ACTIVE', ET_DOMAIN),
                        'class' => 'active'
                    ),
                    'reject' =>
                    array(
                        'title' => __('REJECTED', ET_DOMAIN),
                        'class' => 'pending'
                    ),
                );
                $queries = array('reject', 'pending', 'draft', 'publish', 'archive');
                ?>
                <div class="<?php echo $main_colum ?> account-content">
                    <ul class="job-account-list  account-job-applicant clearfix">
                        <?php
                        global $current_user;
                        $arrJobs = array();

                        foreach ($queries as $status) :
                            $query = new WP_Query(array(
                                'author' => $current_user->ID,
                                'post_type' => 'job',
                                'post_status' => $status,
                                'posts_per_page' => -1
                            ));
                            if ($query->have_posts()) :
                                ?>
                                <?php
                                while ($query->have_posts()) : $query->the_post();
                                    $job = et_create_jobs_response($post);
                                    $arrJobs[] = $job;
                                    $i = 0;

                                    $application = get_children(array('post_parent' => $post->ID, 'post_type' => 'application'));
                                    ?>
                                    <li class="acc-job-item job-item-<?php echo $status ?>">
                                        <div class="row-fluid">
                                            <div class="span5">
                                                <div class="title"> 
                                                    <a href="<?php the_permalink(); ?>" title="<?php the_title() ?>"><?php the_title() ?></a> 

                                                    <?php
                                                    $num_of_applier = count($application);

                                                    if ($num_of_applier == 1) {
                                                        ?>
                                                        <span class="applier-more"><?php printf(__('( %s Bewerber )', ET_DOMAIN), count($application)) ?></span>
                                                        <?php
                                                    } else {
                                                        if ($num_of_applier > 1) {
                                                            ?>
                                                            <span class="applier-more"><?php printf(__('( %s Bewerber )', ET_DOMAIN), count($application)) ?></span>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="span4">
                                                <span class="date">
                                                    <?php echo get_the_date() ?>

                                                    <?php
                                                    $et_expired_date = get_post_meta($post->ID, 'et_expired_date');

                                                    if (is_array($et_expired_date) && isset($et_expired_date[0]) && $et_expired_date[0]) {
                                                        echo '. Endzeit: ' . formatDateDE($et_expired_date[0]);
                                                    }
                                                    if (!is_array($et_expired_date) && $et_expired_date) {
                                                        echo '. Endzeit: ' . formatDateDE($et_expired_date);
                                                    }
                                                    ?>    
                                                </span>
                                            </div>
                                            <div class="span3">
                                                <div class="span6">
                                                    <div class="job-status apps color-<?php echo $statuses[$post->post_status]['class'] ?>" data="<?php echo $post->post_status ?>">
                                                        <?php
                                                        if ($i == 0) {
                                                            echo $statuses[$post->post_status]['title'];
                                                        } else {
                                                            _e('UNPAID', ET_DOMAIN);
                                                            $i = 0;
                                                        }
                                                        ?> <span>&bull;</span> 
                                                    </div>
                                                </div>
                                                <div class="span6">
                                                    <div class="control" data="<?php echo $post->ID ?>">
                                                        <!-- pending job -->
                                                        <?php if ($post->post_status != 'archive' && $post->post_status != 'draft') { ?>

                                                            <?php if ($job['job_paid']) { //paid or free   ?> 
                                                                <a href="#" class="control-action action-edit tooltip" title="<?php _e('Edit', ET_DOMAIN); ?>"><span class="icon" data-icon="p"></span></a>
                                                                <?php
                                                            } else {
                                                                $i = 1; // unpaid 
                                                                ?>
                                                                <a href="<?php echo et_get_page_link('post-a-job', array('job_id' => $post->ID)) ?>" class="control-action action-repost tooltip" title="<?php _e('Choose another payment method', ET_DOMAIN); ?>"><span class="icon" data-icon="1"></span></a>
                                                            <?php } ?>
                                                            <a href="#" class="control-action action-postview tooltip" title="<?php echo et_post_views($post->ID) ?>"><span class="icon" data-icon="E"></span></a>
                                                            <?php if (!in_array($post->post_status, array('pending', 'reject'))) { // if the job is pending, prevent the user to archive it    ?>
                                                                <a href="#" class="control-action action-archive tooltip" title="<?php _e('Archive', ET_DOMAIN); ?>"><span class="icon" data-icon="#"></span></a>
                                                            <?php } ?>

                                                            <!-- pending job -->

                                                        <?php } else if ($post->post_status == 'draft') { ?>
                                                            <!-- draft job -->
                                                            <a href="<?php echo et_get_page_link('post-a-job', array('job_id' => $post->ID)) ?>" class="control-action action-repost tooltip" title="<?php _e('Edit', ET_DOMAIN); ?>"><span class="icon" data-icon="p"></span></a>
                                                            <a href="#" class="control-action action-remove tooltip color-pending" title="<?php _e('Permanently delete', ET_DOMAIN); ?>"><span class="icon" data-icon="*"></span></a>
                                                            <!-- draft job -->
                                                            <!-- archived job -->
                                                        <?php } else { ?>
                                                            <a href="<?php echo et_get_page_link('post-a-job', array('job_id' => $post->ID)) ?>" class="control-action action-repost tooltip" title="<?php _e('Renew', ET_DOMAIN); ?>"><span class="icon" data-icon="1"></span></a>
                                                            <a href="#" class="control-action action-remove tooltip color-pending" title="<?php _e('Permanently delete', ET_DOMAIN); ?>"><span class="icon" data-icon="*"></span></a>
                                                        <?php } ?>
                                                        <!-- archived job -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if (!empty($application)) { ?>
                                            <ul class="list-applier-more">
                                                <?php
                                                foreach ($application as $key => $applicant) {
                                                    $emp_email = get_post_meta($applicant->ID, 'et_emp_email', true);
                                                    $jobseeker = get_user_by('email', $emp_email);
                                                    if ($jobseeker) {
                                                        $post = get_posts(array('post_author' => $jobseeker->ID, 'post_type' => 'resume', 'post_status' => 'publish'));
                                                        if (!$post)
                                                            continue;
                                                        ?>  <li>
                                                            <a href="<?php echo get_permalink($post[0]->ID) ?>"><span class="text"><?php echo $jobseeker->display_name ?></span> 
                                                                <?php echo date('jS-M', strtotime($applicant->post_date)) ?> / <?php echo $jobseeker->user_email ?></a>                                         
                                                        </li>

                                                        <?php
                                                    }else {
                                                        $attachment = get_children(array(
                                                            'post_type' => 'attachment',
                                                            'post_parent' => $applicant->ID,
                                                            'posts_per_page' => -1
                                                        ));
                                                        ?>
                                                        <li><a href="#"><span class="text"><?php echo get_post_meta($applicant->ID, 'et_emp_name', true); ?></span> 
                                                                <?php echo date('jS-M', strtotime($applicant->post_date)) ?> / <?php echo get_post_meta($applicant->ID, 'et_emp_email', true); ?></a>
                                                            <?php if (!empty($attachment)) { ?>
                                                                <div class="input-file clearfix et_uploader applier-file">
                                                                    <a title="<?php _e('Download', ET_DOMAIN) ?>" href="?applicant_id=<?php echo $applicant->ID; ?>">
                                                                        <span class="btn-background border-radius button" style="z-index: 0;">  
                                                                            <span  class="icon-file"></span>
                                                                        </span>
                                                                    </a>
                                                                </div>      
                                                            <?php } ?>
                                                        </li>
                                                        <?php
                                                    }
                                                }
                                                ?>          
                                            </ul>
                                        <?php } ?>
                                    </li>
                                <?php endwhile; ?>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </ul>
                </div>

                <?php
                /**
                  if (!empty($widgets) || current_user_can('manage_options') || $purchase_count) {
                  ?>
                  <div class="second-column widget-area padding-top30" id="static-text-sidebar">
                  <div id="sidebar" class="user-dashboard-sidebar">
                  <?php
                  if ($purchase_count || $resume_view_duration > 0) {
                  ?>
                  <div class="widget bg-grey-widget companies-statis">
                  <?php
                  foreach ($purchase_plans as $id => $quantity) {
                  if ($quantity <= 0 || !isset($plans[$id]))
                  continue;
                  echo '<div>';
                  if ($quantity >= 1 && isset($plans[$id]))
                  printf(__('You have %d jobs in <span class="impress">%s</span> ', ET_DOMAIN), $quantity, $plans[$id]['title']);
                  echo '</div>';
                  }

                  if ($resume_view_duration) {
                  echo '<div>';
                  $time = round($resume_view_duration / ( 24 * 60 * 60 ));
                  if ($time > 1) {
                  printf(__("You have %s days remaining to view resume details.", ET_DOMAIN), $time);
                  } else {
                  printf(__("You have %s day remaining to view resume details.", ET_DOMAIN), $time);
                  }

                  echo '</div>';
                  }
                  ?>

                  </div>
                  <?php
                  }
                  foreach ($widgets as $key => $value) {
                  ?>
                  <div class="widget widget-contact bg-grey-widget" id="<?php echo $key ?>">
                  <div class="view">
                  <?php echo $value ?>
                  </div>
                  <?php if (current_user_can('manage_options')) { ?>
                  <div class="btn-widget edit-remove">
                  <a href="#" class="bg-btn-action border-radius edit"><span class="icon" data-icon='p'></span></a>
                  <a href="#" class="bg-btn-action border-radius remove"><span class="icon" data-icon='#'></span></a>
                  </div>
                  <?php } ?>
                  </div>
                  <?php } ?>
                  </div>
                  <?php if (current_user_can('manage_options')) { ?>
                  <div class="widget widget-contact bg-grey-widget" id="widget-contact">
                  <a href="#" class="add-more"><?php _e('Add a text widget +', ET_DOMAIN) ?> </a>
                  </div>
                  <?php } ?>
                  </div>
                  <?php }
                 * */
                ?>

                <script type="application/json" id="job_list_data">
<?php echo json_encode($arrJobs); ?>
                </script>

                <script type="text/template" id="job_item_template">


                    <% if ( id !== 0 ) { %>
                    <div class="control f-right" data="<%= id %>">
                    <% if ( status !== "archive" ) { %>
                    <a href="#" class="action-edit tooltip" title="<?php _e('Edit', ET_DOMAIN); ?>"><span class="icon" data-icon="p"></span></a>
                    <a href="#" class="control-action action-postview tooltip" title="<?php echo et_post_views($post->ID) ?>"><span class="icon" data-icon="E"></span></a>
                    <% if ( status === 'publish' ) { %>
                    <a href="#" class="action-archive tooltip" title="<?php _e('Archive', ET_DOMAIN); ?>"><span class="icon" data-icon="#"></span></a>
                    <% } %>

                    <% } else { %>
                    <a href="<%= renew_url %>" class="action-repost tooltip" title="<?php _e('Renew', ET_DOMAIN); ?>"><span class="icon" data-icon="1"></span></a>
                    <a href="#" class="control-action action-remove tooltip color-pending" title="<?php _e('Permanently delete', ET_DOMAIN); ?>">
                    <span class="icon" data-icon="*"></span>
                    </a>
                    <% } %>
                    </div>
                    <% } %>
                    <div class="job-status apps f-right color-<%= status %>"><%= dashboardStatus %> <span>&bull;</span> </div>
                    <div class="title"><a href="<%= permalink %>"><%= title %></a> <span class="date"><%= date %></span></div>
                </script>
            </div>
        </div>

        <!-- this script passes the companies data for js usage -->
        <script type="application/json" id="companies_data">
            <?php echo json_encode($arrAuthors);?>
        </script>

        <script type="text/javascript">
            (function($) {
            jQuery(document).ready(function() {
            // $("a.action-edit").click(function() {
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

            $("#page_company_profile .title a").click(function(){
            var link = $(this).attr('href'); 
            window.location = link;
            });
            });
            })(jQuery);
        </script> 
    </div>
</div>
<?php get_footer(); ?>