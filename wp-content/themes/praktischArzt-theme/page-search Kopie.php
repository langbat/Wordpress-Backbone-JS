<?php
$general_opts = new ET_GeneralOptions();
$arrAuthors = array();

get_header();
?>

<div class="wrapper searchpage clear clearfix content-container ">
    <?php
    global $et_global;
    $company_id = get_query_var('author');
    $company = et_create_companies_response($company_id);
    $company_logo = $company['user_logo'];

    // $search_jobtype   = $_REQUEST['job_type'] != '0' ? $_REQUEST['job_type'] : ''; 
    // $search_category   = $_REQUEST['job_category'] != '0' ? $_REQUEST['job_category'] : '';   //ToDo: make this an array if multiple categories
    // $jobname = ($job_types)    ? get_term_by('slug', $job_type, 'job_type')          : ''; 
    // $catname = ($cat_category)? get_term_by('slug', $cat_category, 'job_category')  : ''; 


    $q = array('');
    // search_jobtype

    if (isset($_REQUEST['job_type']) && (!empty($_REQUEST['job_type']))) {
        $search_jobtype = array(
            'taxonomy' => 'job_type',
            'field' => 'slug',
            'terms' => $_REQUEST['job_type']
        );
    }
    else
        $search_jobtype = null;


    // search_category
    // if  ( isset($_REQUEST['job_category']) && ( !empty($_REQUEST['job_category'])  ) ) {
    //  $search_category = array(  
    //             'taxonomy'=> 'job_category',
    //             'field'   => 'slug',
    //             'terms'   =>  $_REQUEST['job_category']
    //           );
    //  } else $search_category= ''; 
    /*
      if  ( isset($_REQUEST['job_category']) && ( !empty( $_REQUEST['job_category'] )) ) {
      // $search_category = 'category_name='.$_REQUEST['job_category'];
      $search_category = '';
      }
      else $search_category = '';
     */



    /*
      Klinikum/Ambulanz:
      <select name="cfield[875]">
      <option value="876">Klinikum</option>
      <option value="877">Ambulanz / Praxis</option>
      </select>
     */
    // var_dump( $a = JEP_Field::get_job_fields(800) );  // $a[0]->value : 877
    // var_dump( JEP_Field::get_options(875) );   
    //
    // search clinic AND OR ambulance
    //
    $isClinic_CField = get_page_by_path('klinikum-ambulanz', 'OBJECT', 'je_field');
    $a = JEP_Field::get_options($isClinic_CField->ID);

    $isClinic_ID = $a[0]->ID; // is option of 875
    $isAmbulance_ID = $a[1]->ID; // is option of 875

    $is_clinic = (isset($_REQUEST['search_hospitals']) == '1') ? $a[0]->ID : 0;
    $is_ambulance = (isset($_REQUEST['search_ambulances']) == '1') ? $a[1]->ID : 0;

    $search_clinics = array(
        'key' => 'cfield-' . $isClinic_CField->ID, //taxonomy-meta: fieldID where selection is stored
        'value' => array($is_clinic, $is_ambulance), //IDs of clinic and ambulance as meta-values
        'compare' => 'IN'
    );


    //
    // search_province
    // $b = get_term_by( $field, $value, $taxonomy, $output, $filter ) 
    $province_CField = get_page_by_path('bundesland', 'OBJECT', 'je_field');
    $province_CField_ID_array = JEP_Field::get_options($province_CField->ID); // $province_CField_ID_array = array( 887, 888, 889, 890, 891, 892, 893, 894, 895, 896, 897, 898, 899, 900, 901 );

    if (isset($_REQUEST['search_province']) && (!empty($_REQUEST['search_province']) )) {
        $search_province = array(
            'key' => 'cfield-' . $province_CField->ID,
            'value' => $_REQUEST['search_province']
        );
    }
    else
        $search_province = '';
    ?>


    <div class="heading">
    </div>

    <div class="full-column main-center clearfix width100 margin-top-30" id="job_list_container" style="width:100%;"> 
        <?php /*
          <div class="header-filter result-filter" id="header-filter">
          <div class="main-center f-left-all">

          <div class="form-item">
          <label for="job_province">Bundesland wählen</label>
          <div class="select-style select_province btn-background border-radius">

          <select name="search_province" id="search_province" class="resultlist_province" tabindex="1">
          <?php
          $province_CField_IDs = array();
          foreach ($province_CField_ID_array as $field) {
          $province_CField_IDs[] = $field->ID;
          ?>
          <option value="<?php echo $field->ID ?>" data="<?php echo $field->name ?>" >  <?php echo $field->name; ?></option>
          <?php } ?>
          </select>

          </div>
          </div>

          <div class="form-item">
          <label for="job_category">Fachrichtung wählen</label>
          <?php $job_cats   =   get_terms('job_category', array('orderby' => 'id', 'order' => 'ASC','hide_empty' => false ,'pad_counts' => true));  ?>
          <div class="select-style  border-radius" style="border: 1px solid #ccc;">
          <?php  je_job_cat_select ('job_category', 'Fachrichtung wählen', array('id' => 'job_category', 'class'=> 'job-searchbox select_category resultlist_category','tabindex'  =>  '2'));  ?>
          </div>
          </div>

          <div class="form-item">
          <label for="job_types">Position wählen</label>
          <div class="select-style border-radius" >
          <?php et_job_type_select('job_types', 'Position wählen', array('class' => 'job-searchbox select_jobtype resultlist_jobtypes', 'id'  => 'job_types','tabindex'  =>  '3' )); ?>
          </div>
          </div>

          <div class="location">
          <label for="job_location">Ort auswählen</label>
          <div>
          <input type="text" name="job_location" class="search-box job-searchbox input-search-box border-radius resultlist_location" placeholder="<?php _e('Enter a location', ET_DOMAIN) ?> ..." value="<?php echo get_query_var( 'location' ) ?>" tabindex="4"  />
          <span class="icon" data-icon="@"></span>
          </div>
          </div>

          <br class="clear">

          <div class="hospital ">
          <label ><input type="checkbox" name="search_hospitals" class="search_hospitals" value="" checked="checked"> Suche nach Kliniken</label>
          </div>

          <div class="ambulance ">
          <label><input type="checkbox" name="search_ambulances" class="search_ambulance" checked="checked"> Suche nach Praxen</label>
          </div>
         */ ?>

        <?php /*
          <div class="btn-select">
          <button class="bg-btn-action border-radius" tabindex="5" type="submit" id="submit_login">filtern </button>
          </div>
         */ ?>
        <br class="clear">
    </div>
</div>



<?php
/*
 * displays the search results of the frontpage-job-search 
 *
 */
wp_reset_query();

$args = array(
    'post_type' => 'job',
    'post_status' => array('publish'),
    'category_name' => $search_category,
    'posts_per_page' => 10,
    'meta_query' => array(
        'relation' => 'AND',
        array('key' => 'et_job_paid',
            'value' => 1
        )
    // array( 'key' => 'et_featured',  //display featured only
    //        'value' => 1
    //     )
    ),
    'tax_query' => array(
        'relation' => 'AND',
    ),
    'orderby' => 'meta_value',
    'meta_key' => 'et_featured',
    'order' => 'ASC'
);

// add search_bundesland
if ($search_jobtype) {
    $args['tax_query'][] = $search_jobtype;
}

// add search_bundesland
if ($search_province) {
    $args['meta_query'][] = $search_province;
}

// add search_clinics

$args['meta_query'][] = $search_clinics;



//customize query by filter
add_filter('posts_orderby', 'et_filter_orderby');

// $job_query = get_posts( $args  );   // get posts instead of wp_query
$job_query = new WP_Query($args);

remove_filter('posts_orderby', 'et_filter_orderby');



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
<div id="latest_jobs_container">
    <h3 class="main-title"><?php echo $list_title; ?></h3>
    <ul class="list-jobs lastest-jobs job-account-list">

        <?php
        $latest_jobs = array();
        echo $job_query->request;
        while ($job_query->have_posts()) {
            $job_query->the_post();

            // while ($job_query->have_posts()) { $job_query->the_post ();

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

    <script type="application/json" id="latest_jobs_data">
        <?php
        echo json_encode(array(
            'status' => $list_status,
            'jobs' => $latest_jobs
        ));
        ?>
    </script>
</div>
<!-- end latest jobs -->


<?php /*
  <!-- this script passes the companies data for js usage -->
  <script type="application/json" id="companies_data"> <?php echo json_encode($arrAuthors);?> </script>
 */ ?>

</div>
</div>


<?php /*

  <script type="application/json" id="author_data">
  <?php echo json_encode(array(
  'display_name'  => $company['display_name'],
  'user_url'    => $company['user_url'],
  'user_logo'   => $company_logo
  )
  ); ?>
  </script>
 */ ?>


<?php get_footer(); ?>