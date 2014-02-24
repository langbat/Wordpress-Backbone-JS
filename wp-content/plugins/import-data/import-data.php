<?php
/*
  Plugin Name: Import Data
  Plugin URI: localhost
  Description: Import other Database
  Author: Codex authors
  Author URI: http://toasternet-online.de
 */

// Hook for adding admin menus
add_action('admin_menu', 'mt_add_pages');

// action function for above hook
function mt_add_pages() {
    add_menu_page(__('Import Data', 'menu-import-data'), __('Import Data', 'menu-import-data'), 'manage_options', 'import-data-handle', 'import_data_page');
}


require_once (dirname(__FILE__) . '/functions.php');

// import_data_page() displays the page content for the custom Test Toplevel menu
function import_data_page() {
    $count = 0; global $count_cat;
    
    $current_user = wp_get_current_user();
    echo "<h2>" . __('Import Data', 'menu-import-data') . "</h2>";
    echo "<h2>Username: " . $current_user->user_login . "</h2><br />";
    //SERVER
    require_once (dirname(__FILE__) . '/db.php');
    
    mysql_query("DELETE FROM dev03postmeta WHERE post_id IN (SELECT ID FROM dev03posts WHERE post_type = 'job' AND ID > 1757)", $db_wp);
    mysql_query("DELETE FROM dev03posts WHERE post_type = 'job' AND ID > 1757", $db_wp);
    
    $jobs = findJobs($db_typo3);


    echo '<pre>';
    mysql_num_rows($jobs);
    
    while ($row = @mysql_fetch_object($jobs)) {
        //$checkJob = checkJob($row->header, $db_wp);
        /* if ($checkJob !== true){
            $post_content = $row->conditions_txt?'<b>Konditionen/Vergütung:</b><br />'.$row->conditions_txt.'<br /><b>Sonstige Informationen:</b><br />'.$row->misc:$row->misc;
            
            $my_post = array(
                'post_content' => $post_content,  
                'ID' => $checkJob              
            );
            wp_update_post($my_post);
        }
        else */
        {

            $contact = findContact($row->contact, $db_typo3);

            $categories = findCategories($row->uid, $db_typo3, $db_wp);

            $conditions = findConditions($row->conditions, $db_typo3);

            //$jobtype = findJobtype($row->jobtyp, $db_typo3, $db_wp);


            $post_jobs = findHospital($row->hospital, $db_typo3, $db_wp);
            $posts_insert = array();
            $attach = false;
            $user_id = NULL;
            if (isset($post_jobs['exist'])) {
                $user_id = $post_jobs['exist']['wordpress']->ID;
                $posts_insert = $post_jobs['exist']['hospital'];
            } else {
                $posts_insert = $post_jobs['new'];
                $user_login = ($post_jobs['new']->firstname != "") ? $post_jobs['new']->firstname : $post_jobs['new']->name;
                $user_id = wp_insert_user(array(
                    'ID' => '',
                    'user_pass' => wp_generate_password(),
                    'user_login' => $post_jobs['new']->email,
                    'user_email' => $post_jobs['new']->email,
                    'user_nicename' => $post_jobs['new']->firstname,
                    'user_url' => $post_jobs['new']->www,
                    'first_name' => $post_jobs['new']->firstname,
                    'last_name' => $post_jobs['new']->lastname,
                    'display_name' => $post_jobs['new']->name,
                    'user_registered' => date('Y-m-d H:i:s'),
                    'role' => 'company'
                ));

                add_user_meta($user_id, 'first_name', $post_jobs['new']->firstname);
                add_user_meta($user_id, 'last_name', $post_jobs['new']->lastname);
                add_user_meta($user_id, 'nickname', $post_jobs['new']->name);
                add_user_meta($user_id, 'et_apply_email', $post_jobs['new']->email);
                
                $attach = true;
                echo "USER ID: " . $user_id;
                echo '<br/>';
                echo "COMPANY: " . $post_jobs['new']->name;
                echo '<br/>';
                
            }

            $terms_jobtype_id = array(6);
            $terms_jobtype_slug = array('famulatur');

            
            //$post_category = (isset($categories['exist'])) ? implode(',', array_unique($categories['exist']['id'])) : 'allgemein';
            $terms_id = isset($categories['exist']['id'])?$categories['exist']['id']:array(1); 
            
            $post_content = $row->conditions_txt?'<b>Konditionen/Vergütung:</b><br />'.$row->conditions_txt.'<br /><b>Sonstige Informationen:</b><br />'.$row->misc:$row->misc;

            $terms_slug = (isset($categories['exist']['slug'])) ? array_unique($categories['exist']['slug']) : array('allgemein');
            foreach ($terms_id as $i => $term_id){
                $count ++;
                $term = findCatName($term_id, $db_wp);
                $job_type = findTypeSlug($row->section, $db_wp);
                
                $post_title = ($row->header != "") ? $row->header : $term->name;
                
                $my_post = array(
                    'post_title' => wp_strip_all_tags($post_title),
                    'post_content' => $post_content,
                    'post_status' => 'publish',
                    'post_category' => array($terms_id),
                    'post_type' => 'job',
                    'post_author' => $user_id,
                    'post_date' => date('Y-m-d H:i:s', $posts_insert->crdate),
                    'tax_input' => array(
                        'job_category' => array($term->slug),
                        'job_type' => array($job_type->slug)
                    ),
                );
    
                //Insert the post into the database
                $post_id = wp_insert_post($my_post);
                //SET POST TERM CATEGORY TAXONOMY
                if (is_taxonomy_hierarchical('job_category')) {
                    $terms_slug = array_unique(array_map('intval', $terms_id));
                }
                if (is_taxonomy_hierarchical('job_type')) {
                    $terms_jobtype_slug = array_unique(array_map('intval', $terms_jobtype_id));
                }
                
                $address = array();
                if ($posts_insert->address)
                    $address[] = $posts_insert->address;
                if ($posts_insert->zip)
                    $address[] = $posts_insert->zip;
                if ($posts_insert->city)
                    $address[] = $posts_insert->city;
                $address = implode(', ', $address);
                
                wp_set_post_terms($post_id, array($term->term_id), 'job_category', false);
                wp_set_post_terms($post_id, array($job_type->term_id), 'job_type', false);
                add_post_meta($post_id, 'et_location', $address);
                add_post_meta($post_id, 'et_full_location', $address);
                add_post_meta($post_id, 'et_plz', $posts_insert->zip);
                
                add_post_meta($post_id, 'et_location_lat', $posts_insert->lat);
                add_post_meta($post_id, 'et_location_lng', $posts_insert->lon);
                
                if ($posts_insert->endtime)
                    add_post_meta($post_id, 'et_expired_date', date('Y-m-d H:i:s', $posts_insert->endtime));
                
                //conditions
                add_post_meta($post_id, 'cfield-975', array(975 => $conditions));
                
                //province
                global $province_CField;
                if ($posts_insert->country_zones && $province_id = findProvince($posts_insert->country_zones, $db_wp, $db_typo3)){
                    echo $province_id;
                    add_post_meta($post_id, 'cfield-'.$province_CField->ID, $province_id);
                }
                
                //ADD Klinik
                $value_984 = 985;
                $id_klinik = $posts_insert->klinik;
                $id_arzt = $posts_insert->arzt;
                if ($id_arzt == 1)
                    $value_984 = 986;
                else if ($id_klinik == 1)
                    $value_984 = 985;
                add_post_meta($post_id, 'cfield-984', $value_984);
                //ADD CONTACT NAME
                add_post_meta($post_id, 'cfield-992', $contact->firstname . " " . $contact->lastname);
                //ADD Phone
                add_post_meta($post_id, 'cfield-993', $contact->fon);
                
                if (isset($post_jobs['exist'])) {
                    //ADD LOGO COMPANY
                    $wp_filetype = ($posts_insert->images != "") ? explode('.', $posts_insert->images) : 'jpg';
        
                    $attachment = array(
                        'guid' => home_url() . '/wp-content/uploads/hospital/' . $posts_insert->images,
                        'post_mime_type' => $wp_filetype[1],
                        'post_title' => preg_replace('/\.[^.]+$/', '', $posts_insert->images),
                        'post_content' => '',
                        'post_author' => $user_id,
                        'post_status' => 'inherit'
                    );
                    $attach_id = wp_insert_attachment($attachment, 'hospital/' . $posts_insert->images, $post_id);
                    // you must first include the image.php file
                    // for the function wp_generate_attachment_metadata() to work
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );
                    $attach_data = wp_generate_attachment_metadata($attach_id, 'hospital/' . $posts_insert->images);
                    wp_update_attachment_metadata($attach_id, $attach_data);
        
                    if ($attach)             {
                        if ($posts_insert->images != "") {
                            $value_attach = array(
                                'attach_id' => $attach_id,
                                'small_thumb' => array(home_url() . '/wp-content/uploads/hospital/' . $posts_insert->images),
                                'thumbnail' => array(home_url() . '/wp-content/uploads/hospital/' . $posts_insert->images)
                            );
                            add_user_meta($user_id, 'et_user_logo', $value_attach);
                        }
                    }
                    else             {
                        if ($posts_insert->images != "") {
                            delete_user_meta($user_id, 'et_user_logo');
                            echo $posts_insert->images;
                            
                            $value_attach = array(
                                'attach_id' => $attach_id,
                                'small_thumb' => array(home_url() . '/wp-content/uploads/hospital/' . $posts_insert->images),
                                'thumbnail' => array(home_url() . '/wp-content/uploads/hospital/' . $posts_insert->images)
                            );
                            update_user_meta($user_id, 'et_user_logo', $value_attach);
                        }
                    }
                        
                }
            }    
            
            echo 'Hospital: ' . $row->uid;
            echo '<br/>';
            echo 'Job Category: ' . implode(',', $terms_id);
            echo '<br/>';
            echo 'Job Type: ' . implode(',', $terms_jobtype_id);
            echo '<br/>';
            echo 'Address: ' . $posts_insert->address;
            echo '<br/>';
            echo 'PostID:' . $post_id;
            echo '<br/>';
            echo '====================================================';
            echo '<br/>';
        } 
    }
    
    echo '<h1>Post: '.$count.'</h1>';
    echo '<h1>Cat: '.$count_cat.'</h1>';
    
    mysql_query("update  dev03usermeta set meta_value = replace(meta_value, '.tif', '.jpg') where meta_key = 'et_user_logo'", $db_wp);
    mysql_query("update  dev03usermeta set meta_value = replace(meta_value, '.bmp', '.jpg') where meta_key = 'et_user_logo'", $db_wp);
    mysql_query("update  dev03usermeta set meta_value = replace(meta_value, '.JPG', '.jpg') where meta_key = 'et_user_logo'", $db_wp);
    

}
?>