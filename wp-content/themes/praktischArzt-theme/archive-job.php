<?php
$general_opts = new ET_GeneralOptions();
$arrAuthors = array();

get_header();
?>
<style>
    #latest_jobs_container{
        max-width: 100%;
    }
    .header-filter > div{
        width: 100%;
    }
    .header-filter > div > div{
        margin: 16px 26px 0px 0px;
    }
    /*  .header-filter > div > div:first-child{
          margin: 16px 0px 0px 0px;
      }
      .header-filter > div > div:last-child{
          margin: 16px 0px 0 20px;
      }*/
    #header-filter{
        margin-bottom: 20px;
    } 
</style>
<div class="row-fluid" id="body_container">
    <div class="clearfix content-block" id="wrapper">
        <div class="full-column main-center clearfix " id="job_list_container" > 

            <div class=" clearfix header-filter" id="header-filter">
                <div class="main-center f-left-all"> 
                    <input type="hidden" name="action_filter" id="action_filter" value="archive_job" />
                    <div class="form-item">
                        <?php $job_cats = get_terms('job_category', array('orderby' => 'id', 'order' => 'ASC', 'hide_empty' => false, 'pad_counts' => true)); ?>
                        <div class="select-style  border-radius" style="border: 1px solid #ccc;">
                            <?php je_job_cat_select('job_category', 'Fachrichtung wählen', array('id' => 'job_category', 'class' => 'job-searchbox select_category')); ?>
                        </div>
                    </div>

                    <div class="location">
                        <input type="text" name="job_location" id="job_location" class="search-box job-searchbox input-search-box border-radius" placeholder="<?php _e('Enter a location', ET_DOMAIN) ?> ..." value="<?php echo get_query_var('location') ?>" />
                        <span class="icon" data-icon="@"></span>
                    </div>

                    <!-- <div class="keyword">
                        <input type="text" name="s" class="search-box job-searchbox input-search-box border-radius" placeholder="<?php //_e('Enter a keyword', ET_DOMAIN)            ?> ..." value="<?php //echo get_query_var('s')            ?>" />
                        <span class="icon" data-icon="s"></span>
                    </div> -->

                    <div class="form-item">
                        <div class="select-style border-radius" >

                            <?php et_job_type_select('job_types', 'Position wählen', array('class' => 'job-searchbox select_jobtype', 'id' => 'job_types', 'tabindex' => '4')); ?>
                        </div>
                    </div>

                    <div class="form-item">
                        <div class="checkbox">
                            <div class="hospital">
                                <input type="checkbox" name="search_hospitals" id="search_hospitals" class="search_hospitals" value="1" <?php echo isset($_GET['search_hospitals']) ? 'checked="checked"' : '' ?> />Klinik
                            </div>
                            <div class="ambulance">
                                <input type="checkbox" name="search_ambulances" id="search_ambulances" class="search_ambulance" value="1" <?php echo isset($_GET['search_ambulances']) ? 'checked="checked"' : '' ?> />Praxis
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="radius" id="radius" value="<?php echo get_query_var('radius') ?>"/>
                    <!--
                    <div class="btn-select">
                        <button class="bg-btn-action border-radius" tabindex="3" type="submit" id="submit_login">filtern </button>
                    </div>
                    -->
                    <div class="clearfix"></div>
                </div>
            </div>



            <?php
            // initial status
            $list_status = get_query_var('status');
            $list_status = (empty($list_status)) ? 'publish' : $list_status;

            if ('publish' == $list_status) {
                $list_title = '';
                $list_status = 'publish';
            } else {
                $list_title = et_get_job_status_labels(explode(',', $list_status));
                $list_status = 'other';
            }
            ?>

            <!-- latest job -->
            <div class="row-fluid">
                <div class="main-column">
                    <div id="latest_jobs_container">
                        <h3 class="main-title"><?php echo $list_title; ?></h3>
                        <ul class="list-jobs lastest-jobs job-account-list">

                            <?php
                            if (isset($_REQUEST['job_category']) && !$_REQUEST['job_category'])
                                unset($_REQUEST['job_category']);
                            if (isset($_REQUEST['job_type']) && !$_REQUEST['job_type'])
                                unset($_REQUEST['job_type']);

                            if (get_query_var('job_category') && get_query_var('job_category')) {
                                $_REQUEST['job_category'] = get_query_var('job_category');
                            }
                            if (get_query_var('job_type') && get_query_var('job_type')) {
                                $_REQUEST['job_type'] = get_query_var('job_type');
                            }

                            $job_query = et_prepare_job_query();
                            $latest_jobs = array();
//                            echo $job_query->request;
                            while ($job_query->have_posts()) {
                                $job_query->the_post();

                                global $job;
                                $job = et_create_jobs_response($post);
                                $latest_jobs[] = $job;

                                // use job-template to display jobs
                                load_template(apply_filters('et_template_job', dirname(__FILE__) . '/template-job.php'), false);
                            }

                            // if no jobs found, display a message
                            if (!$job_query->have_posts()) {
                                ?>
                                <li class="no-job-found"> Leider konnten keine Stellen zur aktuellen Suche gefunden werden. Versuchen Sie es mit anderen Suchkriterien erneut.</li>
                                <?php
                            }
                            ?>
                        </ul>

                        <script type="application/json" id="jobs_list_data"> <?php echo json_encode($latest_jobs); ?> </script>

                        <div class="button-more" <?php
                        if ($wp_query->max_num_pages <= 1) {
                            echo 'style="display:none"';
                        }
                        ?>>
                            <button class="btn-background border-radius"><?php _e('Load More Jobs', ET_DOMAIN) ?></button>
                        </div>
                        <?php
                        //echo '<pre>';
                        //var_dump($latest_jobs);
                        ?>
                        <script type="application/json" id="latest_jobs_data">
                            <?php
                            echo json_encode(array(
                                'status' => $list_status,
                                'jobs' => $latest_jobs
                            ));
                            ?>
                        </script>
                    </div> 
                </div>
            </div>

        </div>
    </div>
</div>
<!--<script type="text/javascript">
    (function($) {
        jQuery(document).ready(function() {
            var job_types = $('#header-filter select.select_jobtype').val();
            var job_category = $('#header-filter select.select_category').val();
            var job_location = $('#job_location').val();

            $.ajax({
                type: "POST",
                dataType: 'json',
                url: et_globals.ajaxURL,
                contentType: 'application/x-www-form-urlencoded;charset=UTF-8',
                data: {action: 'et_fetch_jobs', method: 'read', et_act: 'filter_search', job_category: job_category, job_types: job_types, location: job_location}
            }).done(function(data) {
                $("#load_conditions_by_job_type").html(data);
            });
        });
    })(jQuery);
</script> -->

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