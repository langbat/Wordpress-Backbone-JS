<?php
// $imgUrl  = $et_global['imgUrl'];
// $jsUrl = $et_global['jsUrl'];
$general_opts = new ET_GeneralOptions();
$arrAuthors = array();
if (isset($_SESSION['search_province']))
    unset($_SESSION['search_province']);
get_header();
?>

<?php
?>


<div class="row-fluid" id="body_container">

    <div class="clearfix " id="wrapper">

        <?php
// Loginbox output
        pa_do_loginbox();
        ?> 

        <!-- image slideshow on frontpage -->
        <?php if (is_active_sidebar('homepage-slider')) dynamic_sidebar('homepage-slider'); ?>

        <!-- frontpage-partner -->
        <?php frontpage_partner_output(); ?>

        <div class="clearfix"></div>

        <?php
        // jobmap output
        if (is_active_sidebar('sidebar-main')) :
            ?>
            <div id="frontpage-sidebar" class="">
                <div id="btn_interesting_jobs"></div>
                <script type="text/javascript">
                    $(document).ready(function() {
                        $('#btn_interesting_jobs').show()
                                .click(function(event) {
                            if ($('body').hasClass('logged-in')) {
                                $('html,body').animate({scrollTop: $('#latest_jobs_container').offset().top - 220}, 'slow');
                            } else {
                                $('html,body').animate({scrollTop: $('#latest_jobs_container').offset().top - 170}, 'slow');
                            }
                        });
                    });
                </script>
                <?php dynamic_sidebar('sidebar-main'); ?>
                <div class="clearfix"></div>
            </div>
        <?php endif; ?> 

        <?php
        /*
         * display latest jobs
         *
         */
        $args = array(
            'post_type' => 'job',
            'post_status' => array('publish'),
            'posts_per_page' => 6,
//            'meta_query' => array(
//                array('key' => 'et_job_paid',
//                    'value' => 1
//                ),
//            ),
            'orderby' => 'post_date',
            'meta_key' => 'et_featured',
            'order' => 'DESC'
        );

        //customize query by filter
        add_filter('posts_orderby', 'et_filter_orderby');
        $job_query = new WP_Query($args);

        remove_filter('posts_orderby', 'et_filter_orderby');


        // initial status
        $list_status = get_query_var('status');
        $list_status = (empty($list_status)) ? 'publish' : $list_status;
        if ('publish' == $list_status) {
            $list_title = __('LATEST JOBS', ET_DOMAIN);
            $list_status = 'publish';
        } else {
            $list_title = et_get_job_status_labels(explode(',', $list_status));
            $list_status = 'other';
        }
        ?>

        <div class="content-block" id="latest_jobs_container">
            <h3 class="impress"> Interessante Stellen </h3>
            <ul class="list-jobs lastest-jobs job-account-list">
                <?php
                $all_jobs = array();
//                echo $job_query->request;
                while ($job_query->have_posts()) {
                    global $job;

                    $job_query->the_post();
                    $job = et_create_jobs_response($post);
                    $all_jobs[] = $job;

                    // load template file 
                    load_template(apply_filters('et_template_job', dirname(__FILE__) . '/template-job.php'), false);
                }
// if no jobs found, display a message
                if (!have_posts()) {
                    ?>
                    <li class="no-job-found"><?php _e('Oops! Sorry, no jobs found', ET_DOMAIN) ?></li>
                    <?php
                }
                ?>
            </ul>
            <!-- passing latest-job-data-for js usage -->
            <script type="application/json" id="latest_jobs_data">
<?php
echo json_encode(array(
    'status' => $list_status,
    'jobs' => $all_jobs
));
?>
            </script>
            <div class="clearfix"></div>
        </div>

        <?php /*
          <!-- this script passes the companies data for js usage -->
          <script type="application/json" id="companies_data">  <?php echo json_encode($arrAuthors);?>  </script>
         */ ?>    

        <?php
        //
        // end latest jobs 
        //
    ?>


        <div class="row-fluid"> <!-- end .wrapper --> 

            <div class="content-block">
                <!-- box modul -->
                <?php if (function_exists('tripple_boxes_output')) tripple_boxes_output(); ?>


                <?php if (is_active_sidebar('sidebar-home-bottom')) { ?>
                    <div class="main-center clearfix padding-top30">
                        <div class="sidebar-home-bottom <?php if (current_user_can('manage_options')) echo 'sortable' ?>" id="sidebar-home-bottom" >
                            <?php dynamic_sidebar('sidebar-home-bottom'); ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                <?php } ?>
                <div class="clearfix"></div>
            </div>
        </div> 

    </div>
    <div class="clearfix"></div>
</div>
<!--<script type="text/javascript">
    (function($) {
    jQuery(document).ready(function() {
    var list = $('#latest_jobs_container .list-jobs');
    var listItems = list.find('div.box-job').sort(function(a, b) {
    return $(b).attr('data-sort') - $(a).attr('data-sort');
    });
    list.find('div.box-job').remove();
    list.append(listItems);
    });
    })(jQuery);
</script>-->
<?php get_footer(); ?>