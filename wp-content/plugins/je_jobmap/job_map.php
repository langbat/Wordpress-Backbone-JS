<?php
/*
  Plugin Name: PA JobMap
  Plugin URI: www.praktischArzt.de
  Description: Kartenmodul für praktischArzt
  Version: 1.0
  Author: Engine Themes team
  Author URI: www.enginethemes.com
  License: GPL2
 */

/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
require_once dirname(__FILE__) . '/update.php';

class JobMap_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     * */
    function JobMap_Widget() {
        $widget_ops = array('classname' => 'je_job_map', 'description' => 'This widget works in all sidebars but its display suits best on top or bottom sidebar.');
        $this->WP_Widget('je_job_map', 'JE JobMap', $widget_ops);
    }

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme
     * @param array  An array of settings for this widget instance
     * @return void Echoes it's output
     * */
    function widget($args, $instance) {

        wp_enqueue_script('gmap');
        wp_enqueue_script('marker_cluster', plugin_dir_url(__FILE__) . '/js/marker-cluster.js', array('gmap'), '1.0');

        wp_enqueue_script('je_jobmap', plugin_dir_url(__FILE__) . '/js/je_jobmap.js', array('gmap', 'marker_cluster'), '1.1');
        if (is_singular('job')) {
            $instance['is_single_job'] = 1;
        }
        wp_localize_script('je_jobmap', 'je_jobmap', $instance);

        extract($args, EXTR_SKIP);
        wp_parse_args($instance, array('height' => '300', 'width' => '420'));
        echo $before_widget;
        if ($instance['title'] != '') {
            echo $before_title;
            echo $instance['title']; // Can set this with a widget option, or omit altogether
            echo $after_title;
        }
        ?>



        <div class="jobmap"> 
            <?php /*
              <div id="zoom-slider">
              <!-- decrease zoom level control -->
              <a id="zoom-control-minus" href="#"> <span class="icon" data-icon="_"></span> </a>
              <!-- zoom slider control -->
              <div id="zoom-range">  <div id="zoom-path"></div>  </div>
              <!-- increase zoom level control -->
              <a id="zoom-control-plus" href="#"> <span class="icon" data-icon="&"></span> </a>
              </div>
             */ ?>


            <div id="je_jobmap" class="je_jobmap" style="width : 100%; height : <?php echo $instance['height']; ?>px">
                <?php /* this is where the map is drawn */ ?>            
            </div>


            <form style="" id="jobsearch" onkeypress="return event.keyCode != 13;" >
                <div class="header-filter jobmap_search">
                    <?php /*
                      <input id="" name="<!-- <!-- id_base --> -->" type="hidden" value="<?php echo $this->id_base ?>">

                      <input size="3" id="<?php echo $this->get_field_id('width') ?>" name="<?php echo $this->get_field_name('width'); ?>" type="hidden" value="<?php echo $instance['width'] ?>">
                      <input size="3" id="<?php echo $this->get_field_id('height') ?>" name="<?php echo $this->get_field_name('height'); ?>" type="hidden" value="<?php echo $instance['height'] ?>">
                      <input class="widefat" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title'); ?>" type="hidden" value="<?php echo $instance['title'] ?>">

                      <input class="lat" id="<?php echo $this->get_field_id('lat'); ?>" name="<?php echo $this->get_field_name('lat'); ?>" type="hidden" value="<?php echo $instance['lat']; ?>" />
                      <input class="lng"  id="<?php echo $this->get_field_id('lng'); ?>" name="<?php echo $this->get_field_name('lng'); ?>" type="hidden" value="<?php echo $instance['lng']; ?>" />
                     */ ?>

                    <div id="row-search1" class="row-search">

                        <div class="form-item">
                            <!-- <label for="job_category">Fachrichtung wählen</label> -->
                            <?php $job_cats = get_terms('job_category', array('orderby' => 'id', 'order' => 'ASC', 'hide_empty' => false, 'pad_counts' => true)); ?>
                            <div class="select-style select_category btn-background border-radius">
                                <?php je_job_cat_select('job_category', 'Fachrichtung', array('id' => 'job_category', 'tabindex' => '2')); ?>
                            </div>
                        </div>

                        <div class="form-item">
                            <!-- <label for="job_location">Ort auswählen</label> -->
                            <div class=" location input-style border-radius">
                            <!-- <input type="text" name="<?php //echo $this->get_field_name('center');    ?>" id="<?php //cho $this->get_field_id('center')    ?>" class="search-box job-searchbox jobmap_location input-search-box border-radius jobmap_location center" placeholder="Stadt eingeben" value="<?php //echo $instance['center']    ?>"> -->
                                <input type="text" name="location" id="location" class="border-radius jobmap_location center" placeholder="Ort / Postleitzahl" value="" tabindex="4" >
                                <span class="icon" data-icon="@"></span>
                            </div>
                        </div>

                        <div class="form-item">
                            <!-- <label for="job_province">Bundesland wählen</label> -->
                            <div class="select-style select_province btn-background border-radius">
                                <select name="search_province" id="search_province" tabindex="1">
                                    <option value=""> Bundesland </option>
                                    <?php
                                    $province_CField = get_page_by_path('bundesland', 'OBJECT', 'je_field');
                                    $province_CField_ID_array = JEP_Field::get_options($province_CField->ID);

                                    foreach ($province_CField_ID_array as $field) {
                                        ?>

                                        <option value="<?php echo $field->ID ?>" data="<?php echo $field->name ?>" >  <?php echo $field->name; ?></option>

                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div id="btn-search" class="form-item">
                            <div class="btn-select">
                                <button class="bg-btn-hyperlink border-radius" tabindex="5">Suchen</button>
                            </div>
                        </div>

                    </div>

                    <br class="clear">

                    <div id="row-search2" class="row-search">

                        <div class="form-item">
                            <!-- <label for="job_types">Position wählen</label> -->
                            <div class="select-style select_jobtype btn-background border-radius ">
                                <?php et_job_type_select('job_type', 'Art der Stelle', array('id' => 'job_types', 'tabindex' => '3')); ?>
                            </div>
                        </div>

                        <div class="form-item">
                            <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
                            <script>
                $(function() {
                    $("#search-slider").slider({
                        range: "min",
                        min: 50,
                        max: 200,
                        step: 50,
                        slide: function(event, ui) {
                            var r = ui.value;
                            if (r == 50)
                                $("input#radius").val(10);
                            else if (r == 100)
                                $("input#radius").val(50);
                            else if (r == 150)
                                $("input#radius").val(100);
                            else if (r == 200)
                                $("input#radius").val(200);
                            else
                                $("input#radius").val(10);
                        }
                    });
                });
                            </script>
                            <div id="search-slider"></div>
                            <input id="radius" type="hidden" name="radius" value="10">
                            <div class="radius-val">
                                <span>10km</span>
                                <span>50km</span>
                                <span>100km</span>
                                <span>200km</span>
                            </div>
                        </div>

                        <div class="form-item">
                            <div class="checkbox">
                                <div class="hospital">
                                    <input type="checkbox" name="search_hospitals" class="search_hospitals" value="1" checked="checked">Kliniken
                                </div>
                                <div class="ambulance">
                                    <input type="checkbox" name="search_ambulances" class="search_ambulance" value="1" checked="checked">Praxen
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>


            <?php
            if (current_user_can('manage_options') && !is_singular('job')) {
                #$ajax_nonce = wp_create_nonce("save-sidebar-widgets");
            }
            ?>


        </div>

        <?php
        $this->template();
        //
        // Widget display logic goes here
        //

        echo $after_widget;
    }

    function template() {
        $template = '<div class="jobmap-content"> <img src="<%= logo %>" /> <p> <a href="<%= permalink %>" > <%= post_title %> </a> </p> <p> ' . __("Location", ET_DOMAIN) . ': <%= location %> </p></div>';
        echo '<script type="text/template" id="je_jobmap_template">' . apply_filters('je_jobmap_template', $template) . '</script>';
    }

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     * */
    function update($new_instance, $old_instance) {

        // update logic goes here
        $updated_instance = $new_instance;
        return $updated_instance;
    }

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     * */
    function form($instance) {
        $instance = wp_parse_args((array) $instance, array(
            'title' => __("Job Map", ET_DOMAIN),
            'width' => '970',
            'height' => '600',
            'zoom' => '12',
            'center' => 'Berlin',
            'lat' => '',
            'lng' => ''
        ));
        extract($instance);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', ET_DOMAIN); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <p> 
            <label for="<?php echo $this->get_field_id('center'); ?>"><?php _e('Map Center:', ET_DOMAIN); ?></label> 
            <input class="widefat"  id="<?php echo $this->get_field_id('center'); ?>" name="<?php echo $this->get_field_name('center'); ?>" type="text" value="<?php echo $center; ?>" /> 
        </p>
        <p> 
            <label for="<?php echo $this->get_field_id('zoom'); ?>"><?php _e('Default Zoom:', ET_DOMAIN); ?></label> 
            <input class="widefat"  id="<?php echo $this->get_field_id('zoom'); ?>" name="<?php echo $this->get_field_name('zoom'); ?>" type="text" value="<?php echo $zoom; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:', ET_DOMAIN); ?></label> 
            <input size='3'  id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" />px

            <label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height:', ET_DOMAIN); ?></label> 
            <input size='3'  id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>" />px
        </p>
        <input size='3'  id="<?php echo $this->get_field_id('lat'); ?>" name="<?php echo $this->get_field_name('lat'); ?>" type="hidden" value="<?php echo $lat; ?>" />
        <input size='3'  id="<?php echo $this->get_field_id('lng'); ?>" name="<?php echo $this->get_field_name('lng'); ?>" type="hidden" value="<?php echo $lng; ?>" />
        <?php
        // display field names here using:
        // $this->get_field_id( 'option_name' ) - the CSS ID
        // $this->get_field_name( 'option_name' ) - the HTML name
        // $instance['option_name'] - the option value
    }

}

class JE_JobMap {

    function __construct() {
        add_action('widgets_init', array($this, 'register_widget'));

        if (is_active_widget(false, false, 'je_job_map', true)) {
            add_action('wp_head', array($this, 'front_end_css'));
            //  add_action('wp_footer', array($this, 'template')) ;

            add_action('wp_ajax_je_jobmap_fetch_jobs', array($this, 'fetch_jobs'));
            add_action('wp_ajax_nopriv_je_jobmap_fetch_jobs', array($this, 'fetch_jobs'));

            add_action('wp_ajax_je_jobmap_fetch_jobs_insingle', array($this, 'single_fetch_jobs'));
            add_action('wp_ajax_nopriv_je_jobmap_fetch_jobs_insingle', array($this, 'single_fetch_jobs'));

            add_action('wp_ajax_je_jobmap_filter', array($this, 'filter_map'));
            add_action('wp_ajax_nopriv_je_jobmap_filter', array($this, 'filter_map'));

            // add_action( 'wp_footer', array($this, 'map_modal_template'));
        }
    }

    function map_modal_template() {
        ?>
        <div class="modal-job" id="modal_job_map" style="" > 
            <!--             <div class="header-filter">
                            <div class="main-center f-left-all">
                                <div class="form-item">
            <?php
            $job_cats = get_terms('job_category', array('orderby' => 'id', 'order' => 'ASC', 'hide_empty' => false, 'pad_counts' => true));
            ?>
                                    <div class="select-style btn-background border-radius">
            <?php
            je_job_cat_select('job_category', __("Select Category", ET_DOMAIN), array('id' => ''));
            ?>
                                        
                                    </div>
                                </div>
                                <div class="location">
                                    <input type="text" name="job_location" class="search-box job-searchbox input-search-box border-radius" placeholder="Enter a location ..." value="">
                                    <span class="icon" data-icon="@"></span>
                                </div>
                            </div>
                        </div> -->
            <div  id="modal_map_inner" style="" ></div>
            <div class="modal-close"></div>
        </div>
        <?php
    }

    function fetch_jobs() {

        $jobs = query_posts(array(
            'post_type' => 'job',
            'post_status' => 'publish',
            'showposts' => '1000',
            'meta_query' => array(
                array(
                    'key' => 'et_location_lat',
                    'value' => '',
                    'compare' => '!=',
                )
            )
        ));

        $job_data = array();
        $job_data['count'] = count($jobs);
        $job_data['data'] = array();
        //var_dump($job_data);
        foreach ($jobs as $job) {
            $data = array();
            $data['post_title'] = $job->post_title;
            $data['post_excerpt'] = empty($job->post_excerpt) ? apply_filters('the_excerpt', $job->post_content) : $job->post_excerpt;
            $logo = et_get_company_logo($job->post_author);
            //var_dump($logo);
            $data['logo'] = $logo['thumbnail'][0];
            $data['lat'] = get_post_meta($job->ID, 'et_location_lat', true);
            $data['lng'] = get_post_meta($job->ID, 'et_location_lng', true);
            $data['location'] = get_post_meta($job->ID, 'et_full_location', true);
            $data['permalink'] = get_permalink($job->ID);
            array_push($job_data['data'], $data);
        }

        header('HTTP/1.0 200 OK');
        header('Content-type: application/json');

        echo json_encode($job_data);
        exit;
    }

    function filter_map() {
        $response = array();
        try {
            global $post, $et_global;

            // refine meta query
            $request = $_REQUEST;
            if (isset($request['meta_query'])) {
                foreach ((array) $request['meta_query'] as $index => $meta) {
                    if (isset($meta['key']))
                        $request['meta_query'][$index]['key'] = $et_global['db_prefix'] . $meta['key'];
                }
            }

            if (!empty($request['status'])) {
                $request['post_status'] = $request['status'];
                unset($request['status']);
            }

            if (isset($request['post_status'])) {
                $arrStatuses = (is_array($request['post_status'])) ? $request['post_status'] : explode(',', $request['post_status']);
            } else {
                $arrStatuses = array('publish');
            }
            $list_title = et_get_job_status_labels($arrStatuses);

            if (!empty($request['job_type']) && is_array($request['job_type']))
                $request['job_type'] = implode(',', $request['job_type']);
            if (!empty($request['job_category']) && is_array($request['job_category']))
                $request['job_category'] = implode(',', $request['job_category']);

            $request['meta_query'] = array(
                array(
                    'key' => 'et_location_lat',
                    'value' => '',
                    'compare' => '!=',
                )
            );
            $args = wp_parse_args($request, array(
                'post_type' => 'job',
                'post_status' => array('publish'),
                'orderby' => 'post_date',
                'order' => 'DESC',
                'showposts' => 1000,
            ));

            //add_filter('posts_orderby','et_filter_orderby');
            //$request    = apply_filters( 'et_fetch_jobs', $request );
            $query = new WP_Query($args);
            // remove_filter('posts_orderby', 'et_filter_orderby');

            $jobs = array();
            $authors = array();

            $response = array();
            if ($query->have_posts()) {
                $job_data = array();
                $job_data['count'] = $query->found_posts;
                $job_data['data'] = array();

                while ($query->have_posts()) {
                    $query->the_post();
                    global $post;
                    $job = $post;

                    $data = array();
                    $data['post_title'] = $job->post_title;
                    $data['post_excerpt'] = empty($job->post_excerpt) ? apply_filters('the_excerpt', $job->post_content) : $job->post_excerpt;
                    $logo = et_get_company_logo($job->post_author);
                    $data['logo'] = $logo['thumbnail'][0];
                    $data['lat'] = get_post_meta($job->ID, 'et_location_lat', true);
                    $data['lng'] = get_post_meta($job->ID, 'et_location_lng', true);
                    $data['location'] = get_post_meta($job->ID, 'et_full_location', true);
                    $data['permalink'] = get_permalink($job->ID);
                    array_push($job_data['data'], $data);
                }

                $response = $job_data;
            }
        } catch (Exception $e) {
            $response = array(
                'status' => false,
                'code' => 400,
                'msg' => __("An error has occurred!", ET_DOMAIN)
            );
        }

        header('HTTP/1.0 200 OK');
        header('Content-type: application/json');

        echo json_encode($response);
        exit;
    }

    function single_fetch_jobs() {
        $single_job = $_REQUEST['job'];

        $jobs = query_posts(array(
            'post_type' => 'job',
            'post_status' => 'publish',
            'showposts' => '1000',
            'meta_query' => array(
                array(
                    'key' => 'et_location_lat',
                    'value' => '',
                    'compare' => '!=',
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'job_type',
                    'field' => 'slug',
                    'terms' => $single_job['job_types'][0]['slug'],
                ),
                array(
                    'taxonomy' => 'job_category',
                    'field' => 'slug',
                    'terms' => $single_job['categories'][0]['slug'],
                ),
                'relation' => 'OR'
            )
        ));

        $job_data = array();
        $job_data['count'] = count($jobs);
        $job_data['data'] = array();

        foreach ($jobs as $job) {
            $data = array();
            $data['post_title'] = $job->post_title;
            $data['post_excerpt'] = empty($job->post_excerpt) ? apply_filters('the_excerpt', $job->post_content) : $job->post_excerpt;
            $logo = et_get_company_logo($job->post_author);
            $data['logo'] = $logo['thumbnail'][0];
            $data['lat'] = get_post_meta($job->ID, 'et_location_lat', true);
            $data['lng'] = get_post_meta($job->ID, 'et_location_lng', true);
            $data['location'] = get_post_meta($job->ID, 'et_full_location', true);
            $data['permalink'] = get_permalink($job->ID);
            array_push($job_data['data'], $data);
        }

        if ($single_job['location_lat'] != '') {
            $job_data['center'] = array('lat' => $single_job['location_lat'], 'lng' => $single_job['location_lng']);
        }

        header('HTTP/1.0 200 OK');
        header('Content-type: application/json');

        echo json_encode($job_data);
        exit;
    }

    function register_widget() {
        register_widget('JobMap_Widget');
    }

    function front_end_css() {
        
    }

}

add_action('after_setup_theme', 'je_jobmap_init');

function je_jobmap_init() {
    new JE_JobMap ();
}

if (!function_exists('je_job_cat_children_options')) {

    function je_job_cat_children_options($tax, $cats = array(), $parent = false, $level = 0) {
        // re get categories if it empty
        if (empty($cats))
            $cats = array();

        // echo 
        foreach ($cats as $cat) {
            if (($parent == false && !$cat->parent) || $parent == $cat->parent) {
                // seting spacing
                $space = '';
                for ($i = 0; $i < $level; $i++)
                    $space .= '&nbsp;&nbsp;';

                $current = get_query_var($tax);
                $selected = $current == $cat->slug ? 'selected="selected"' : '';
                global $current_filter;
                if (empty($current_filter))
                    $current_filter = array();
                if ($current == $cat->slug)
                    $current_filter[$tax] = $cat->name;

                // display option tag
                echo '<option value="' . $cat->slug . '" ' . $selected . ' rel="' . $cat->name . '">' . $space . $cat->name . '</option>';
                je_job_cat_children_options($tax, $cats, $cat->term_id, $level + 1);
            }
        }
    }

}


if (!function_exists('je_job_cat_select')) {

    function je_job_cat_select($name, $label = 'Select Category', $args = array()) {
        $cats = et_get_job_categories_in_order();
        $args = wp_parse_args($args, array(
            'class' => '',
            'id' => 'filter_cat',
        ));
        ?>
        <select name="<?php echo $name ?>" id="<?php echo $args['id'] ?>" class="<?php echo $args['class'] ?>">
            <option value="0"><?php echo $label ?></option>
            <?php je_job_cat_children_options('job_category', $cats); ?>
        </select>
        <?php
    }

}