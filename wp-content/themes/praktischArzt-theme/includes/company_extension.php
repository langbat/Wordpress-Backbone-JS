<?php

/**
 * extend init
 *  
 */
class PA_Company_extension extends ET_Base {

    static $fields = array(
        'et_profession_title',
        'et_location',
        'description',
        'et_avatar',
        'first_name',
        'et_privacy',
        'et_accessible_companies');

    /**
     * Initialize function
     */
    function __construct() {
        /* register all customized functions here */

        // $this->add_action( 'init', 'add_role');
        // $this->add_action( 'et_after_register', 'after_register_user');
        // $this->add_filter( 'et_user_response', 'user_response' ,10 , 2);
        // $this->add_filter('user_contactmethods', 'modify_contact_methods');
        // $this->add_filter('je_filter_header_account_link', 'je_filter_header_account_link');
    }

    /**
     * 
     */
    public function modify_contact_methods($fields) {

        // Add new fields
        $profile_fields['twitter'] = 'Twitter Username';
        $profile_fields['facebook'] = 'Facebook URL';
        $profile_fields['gplus'] = 'Google+ URL';
        $profile_fields['linkedin'] = 'linkedin URL';

        return $profile_fields;
    }

    /**
     * Insert new job seeker
     * @param $args array contains user data
     */
    static public function insert($args, $wp_error = false) {
        try {
            if (empty($args['user_login']))
                throw new Exception(__('Missing username', ET_DOMAIN));

            $args = wp_parse_args($args, array('role' => self::ROLE));
            $args = apply_filters('insert_job_seeker', $args);

            $fields = array();
            foreach (self::$fields as $meta_key) {
                if (isset($args[$meta_key])) {
                    $fields[$meta_key] = $args[$meta_key];
                    unset($args[$meta_key]);
                }
            }

            $result = wp_insert_user($args, $wp_error);

            if (!$result || is_wp_error($result))
                return $wp_error;

            // insert user meta
            foreach ($fields as $key => $value) {
                update_user_meta($result, $key, $value);
            }

            // call action
            do_action('insert_job_seeker', $result);

            // insert new user
            return $result;
        } catch (Exception $e) {
            if ($wp_error)
                return new WP_Error('add_job_seeker_username', __('Missing User Name', ET_DOMAIN));
            else
                return false;
        }
    }

    /**
     * update job seeker
     */
    static public function update($args, $wp_error = false) {
        try {
            if (empty($args['ID']))
                throw new Exception(__('Missing ID', ET_DOMAIN));

            $args = wp_parse_args($args, array('role' => self::ROLE));
            $args = apply_filters('insert_job_seeker', $args);

            // Filter all meta data to another array
            $fields = array();
            foreach (self::$fields as $meta_key) {
                if (isset($args[$meta_key])) {
                    $fields[$meta_key] = $args[$meta_key];
                    unset($args[$meta_key]);
                }
            }

            // update user data
            $result = wp_update_user($args, $wp_error);

            if (!$result || is_wp_error($result))
                return $wp_error;

            // Update user meta data
            foreach ($fields as $key => $value) {
                update_user_meta($result, $key, $value);
            }

            // UPDATE RESUMES
            // There are some meta data that should be cloned to resume			
            $resume_metas = array(
                'et_profession_title' => isset($fields['et_profession_title']) ? $fields['et_profession_title'] : false,
                'et_url' => isset($args['user_url']) ? $args['user_url'] : false,
                'et_location' => isset($fields['et_location']) ? $fields['et_location'] : false,
                'et_privacy' => isset($fields['et_privacy']) ? $fields['et_privacy'] : false
            );
            // get all resume belonged to user
            $resumes = get_posts(array(
                'post_type' => 'resume',
                'post_status' => 'any',
                'numberposts' => -1,
                'author' => $args['ID']
            ));
            // update resume meta data
            foreach ($resumes as $resume) {
                $args_privacy = array('ID' => $resume->ID);

                if (isset($args['display_name'])) {
                    $args_privacy['post_title'] = $args['display_name'];
                }

                if (isset($fields['et_privacy'])) {
                    $args_privacy['et_privacy'] = $fields['et_privacy'];
                }

                if (isset($args['display_name']) || isset($fields['et_privacy']))
                    JE_Resume::update($args_privacy);


                foreach ($resume_metas as $key => $value) {
                    if (isset($fields[$key]) || $value)
                        update_post_meta($resume->ID, $key, $value);
                }
            }
            // finish update resume meta
            // call action for further needs
            do_action('update_job_seeker', $result);

            // return result
            return $result;
        } catch (Exception $e) {
            if ($wp_error)
                return new WP_Error('update_job_seeker', $e->getMessage());
            else
                return false;
        }
    }

    /**
     * Convert from user to job seeker object
     */
    static public function convert_from_user($user) {
        foreach (self::$fields as $field) {
            $user->$field = get_user_meta($user->ID, $field, true);
        }
        unset($user->data->user_pass);
        // bonus contact methods
        switch ($user->et_privacy) {
            case 'confidential':
                $user = self::confidential_user($user);
                break;

            default:
                $user = self::public_user($user);
                break;
        }
        $user = apply_filters('convert_from_user', $user);
        return $user->data;
    }

    static public function confidential_user($user) {
        global $current_user;
        /**
         * shouldn't set confidential for owner
         */
        if ($current_user->ID == $user->ID || current_user_can('manage_options'))
            return self::public_user($user);

        $accessible_list = JE_Job_Seeker::get_accessible_list($user->ID);
        if (in_array($current_user->ID, $accessible_list))
            return self::public_user($user);

        $user->display_name = __("Anonymous", ET_DOMAIN);
        $img = et_get_resume_avatar($user->ID, 150);

        // trim content, get image src
        preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $img, $matches);
        $img_url = $matches[1];

        // generate avatars array
        $avatars = array();
        if (is_array($user->et_avatar)) {
            foreach ((array) $user->et_avatar as $key => $value) {
                $avatars[$key][0] = $img_url;
            }
        } else {
            $keys = get_intermediate_image_sizes();
            foreach ($keys as $key) {
                $avatars[$key] = array($img_url);
            }
        }

        $user->et_avatar = $avatars;

        $confidential_data = array('user_email', 'user_nicename');
        foreach ($confidential_data as $value) {
            unset($user->data->$value);
        }

        return apply_filters('je_confidential_user', $user);
    }

    static public function public_user($user) {

        $contacts = array('twitter', 'facebook', 'gplus', 'linkedin');
        foreach ($contacts as $contact) {
            $user->$contact = get_user_meta($user->ID, $contact, true);
        }

        // get properly avatar when user has no avatar
        if (empty($user->et_avatar['thumbnail']) || empty($user->et_avatar['thumbnail'][0])) {
            // get default avatar
            $img = et_get_resume_avatar($user->ID, 150);

            // trim content, get image src
            preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $img, $matches);
            $img_url = $matches[1];

            // generate avatars array
            $avatars = array();
            if (is_array($user->et_avatar)) {
                foreach ((array) $user->et_avatar as $key => $value) {
                    $avatars[$key][0] = $img_url;
                }
            } else {
                $keys = get_intermediate_image_sizes();
                foreach ($keys as $key) {
                    $avatars[$key] = array($img_url);
                }
            }
            $user->et_avatar = $avatars;
        }

        return apply_filters('je_public_user', $user);
    }

    static function set_accessible_list($jobseeker_ID, $access_ID) {
        //delete_user_meta( $jobseeker_ID, 'et_accessible_companies');
        $accessible_companies = get_user_meta($jobseeker_ID, 'et_accessible_companies', true);
        if (!is_array($accessible_companies))
            $accessible_companies = array();
        if (!in_array($access_ID, $accessible_companies)) {
            array_push($accessible_companies, $access_ID);
            update_user_meta($jobseeker_ID, 'et_accessible_companies', $accessible_companies);
        }
    }

    static public function get_accessible_list($jobseeker_ID) {
        $accessible_companies = get_user_meta($jobseeker_ID, 'et_accessible_companies', true);
        if (!is_array($accessible_companies))
            $accessible_companies = array();
        return $accessible_companies;
    }

    /**
     * get a job seeker by given id
     */
    static public function get($ID) {
        $user = get_userdata($ID);
        return self::convert_from_user($user);
    }

    /**
     * get a job seeker by given id
     */
    static public function get_meta($id, $field) {
        if (in_array($field, self::$fields)) {
            return get_user_meta($id, $field, true);
        }
        else
            return false;
    }

    /**
     * get job seekers via function get_users
     */
    static public function get_jobseekers($args) {
        $args = wp_parse_args($args, array('role' => 'jobseeker'));
        $users = get_users($args);
        $jobseekers = array();
        foreach ($users as $user) {
            $jobseekers[] = self::convert_from_user($user);
        }
        return $jobseekers;
    }

    public function after_register_user($user) {
        if (!$user)
            return $user;
        if (isset($_REQUEST['role']) && $_REQUEST['role'] == 'jobseeker') {
            $fields = array();
            if (isset($_REQUEST['et_avatar'])) {
                $all_sizes = get_intermediate_image_sizes();
                foreach ($all_sizes as $size) {
                    $data[$size] = array($_REQUEST['et_avatar'], 300, 200);
                }
                $_REQUEST['et_avatar'] = $data;
            }
            foreach (self::$fields as $meta_key) {
                if (isset($_REQUEST[$meta_key])) {
                    update_user_meta($user, $meta_key, $_REQUEST[$meta_key]);
                }
            }
        }

        return $user;
    }

    public function company_response($response, $user) {
        $roles = $user->roles;
        if (array_pop($roles) == 'jobseeker') {
            $response['role'] = 'jobseeker';
            $resume = JE_Resume::get_resumes(array('author' => $user->ID, 'post_status' => array('pending', 'publish')));
            if (isset($resume[0])) {
                $id = $resume[0]->ID;
            } else {
                $id = JE_Resume::insert(array('post_title' => $user->user_login, 'post_author' => $user->ID));
            }
            // wp_reset_query();
            $response['profile_url'] = get_permalink($id);
            foreach (self::$fields as $meta_key) {
                if (isset($args[$meta_key])) {
                    $response[$meta_key] = get_usermeta($user->ID, $meta_key, true);
                }
            }
        }
        return $response;
    }

    public function je_filter_header_account_link($link) {
        global $current_user;
        $roles = $current_user->roles;
        if (array_pop($roles) == 'jobseeker') {
            $resume = JE_Resume::get_resumes(array('author' => $current_user->ID, 'post_status' => array('pending', 'publish')));
            if (isset($resume[0])) {
                $id = $resume[0]->ID;
            } else {
                $id = JE_Resume::insert(array('post_title' => $current_user->user_login, 'post_author' => $current_user->ID));
            }
            //wp_reset_query();

            return get_permalink($id);
        }
        return $link;
    }

}
