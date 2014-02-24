<?php

function slug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', "-", $str);
    return $str;
}

function findJobs($db_typo3) {
    return @mysql_query('SELECT * FROM user_famulus_advertise WHERE deleted = 0 AND hidden=0', $db_typo3);
}

function checkJob($titleJob, $db_wp) {
        
    $query_wp = @mysql_query('SELECT * FROM dev03posts WHERE post_title like "' . $titleJob . '" ', $db_wp);
    $result = @mysql_fetch_object($query_wp);
    if ($result) {
        return $result->ID;
    } else {
        return true;
    }
}

function findHospital($id, $db_typo3, $db_wp) {

    $query_old = @mysql_query('SELECT * FROM user_famulus_hospital WHERE uid=' . $id, $db_typo3);

    $hospital_new = array();
    while ($hospital_old = @mysql_fetch_object($query_old)) {
        $query_wp = @mysql_query('SELECT * FROM dev03users WHERE user_email like "' . $hospital_old->email . '" ', $db_wp);
        $user = @mysql_fetch_object($query_wp);

        if ($user) {
            $hospital_new['exist']['hospital'] = $hospital_old;
            $hospital_new['exist']['wordpress'] = $user;
            //echo 'Exist:' . $hospital_old->name;
            //echo '<br/>';
        } else {
            //var_dump($hospital_old);
            $hospital_new['new'] = $hospital_old;
            //echo 'New:' . $hospital_old->name;
            //echo '<br/>';
        }
    }
    return $hospital_new;
}

function findContact($id, $db_typo3) {

    $query_old = @mysql_query('SELECT * FROM user_famulus_contact WHERE uid=' . $id, $db_typo3);
    $contact = @mysql_fetch_object($query_old);

    return $contact;
}

function findCategories($old_job_id, $db_typo3, $db_wp) {
    
    global $db_read, $db_write;
    global $count_cat;
    
    $result = @mysql_query('SELECT uid_foreign FROM user_famulus_advertise_medical_speciality_mm WHERE uid_local=' . $old_job_id, $db_typo3);
    $ids = array();

    while ($id = @mysql_fetch_object($result)) {
        $ids[] = $id->uid_foreign;
        $count_cat ++;
    }
    $result = @mysql_query('SELECT * FROM user_famulus_medical_speciality WHERE uid IN (' . implode(',', $ids) . ')', $db_typo3);

    $cat_new = array();
    while ($cate_old = @mysql_fetch_object($result)) {
        $query_wp = @mysql_query('SELECT * FROM dev03terms WHERE name = "' . $cate_old->title . '"', $db_wp);
        $res = @mysql_fetch_object($query_wp);
        if (!$res){
            wp_insert_term(
              $cate_old->title, // the term 
              'job_category', // the taxonomy
              array(
                'description'=> '',
                'slug' => sanitize_title($cate_old->title),
                'parent'=> 0
              )
            );
            $query_wp = @mysql_query('SELECT * FROM dev03terms WHERE name = "' . $cate_old->title . '"', $db_wp);
            $res = @mysql_fetch_object($query_wp);
        }
        
        $cat_new['exist']['hospital'] = $cate_old;
        $cat_new['exist']['wordpress'] = $res;
        $cat_new['exist']['id'][] = $res->term_id; //term_id
        $cat_new['exist']['slug'][] = $res->slug;
    }
    return $cat_new;
}

function findCatName($id, $query_wp){
    $result = @mysql_query('SELECT * FROM dev03terms WHERE term_id = '.$id, $query_wp);
    return @mysql_fetch_object($result); 
}
function findConditions($conditions_str, $db_typo3) {

    $conditions_array = array(
        1 => 978,
        2 => 982,
        3 => 979,
        4 => 1078,
        5 => 981,
        6 => 980,
        7 => 1077
    );
    
    $conditions = array();
    if ($conditions_str != ''){
        $tmp = explode(',', $conditions_str);
        foreach ($tmp as $id){
            $conditions[] = $conditions_array[$id];                    
        }
    }

    return $conditions;
}

function findJobtype($jobtype, $db_typo3, $db_wp) {

    $type_new = array();
    if ($jobtype != "") {
        $type = @mysql_query('SELECT * FROM user_famulus_jobtyp WHERE uid IN (' . $jobtype . ')', $db_typo3);
        while ($types = @mysql_fetch_object($type)) {
            $query_wp = @mysql_query('SELECT * FROM dev03terms WHERE name like "' . $types->title . '"', $db_wp);
            $res = @mysql_fetch_object($query_wp);

            if ($res) {
                $type_new['exist']['hospital'] = $types;
                $type_new['exist']['wordpress'] = $res;
                $type_new['exist']['id'][] = $res->term_id; //term_id
                $type_new['exist']['slug'][] = $res->slug;
            } else {
                //IF NOT FOUND => INSERT NEW job_type TO WORDPRESS
//                $term_job_type = wp_insert_term(
//                        $types->title, // the term 
//                        'job_type', // the taxonomy
//                        array(
//                    'description' => $types->title,
//                    'slug' => 'glasgow'
//                        )
//                );

                $type_new['new'] = $types;
                $type_new['exist'] = NULL;
            }
        }
    } else {
        $type_new['exist'] = NULL;
    }

    return $type_new;
}

function findTypeSlug($id, $db_wp){
    $array = array(
        1 => 6,  //Famulatur => Famulatur
        2 => 8, //Medizinstudentenjob => StudentenJob
        3 => 5, //Assistenzarztstelle => Assistenzarzt
        4 => 7 //PJ-Stelle
    );    
    $query_wp = @mysql_query('SELECT * FROM dev03terms WHERE term_id = "' . $array[$id] . '"', $db_wp);
    return @mysql_fetch_object($query_wp);
}

function findProvince($id, $db_wp, $db_typo3){
    $query_wp = @mysql_query('SELECT zn_name_local FROM static_country_zones WHERE uid="' . $id . '"', $db_typo3);
    $province_name = @mysql_fetch_object($query_wp);
    $province_name = $province_name->zn_name_local;
    
    global $province_CField, $province_CField_ID_array;
    
    if (!$province_CField){
        $province_CField = get_page_by_path('bundesland', 'OBJECT', 'je_field');
        $province_CField_ID_array = JEP_Field::get_options($province_CField->ID);    
    }
    
    foreach ($province_CField_ID_array as $i=>$data){
        if ($data->name == $province_name) 
            return $data->ID;
    }
    return 0;
}