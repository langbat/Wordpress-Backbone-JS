<?php

class JEP_Field extends JEP_Fields_Base{

    const POST_FIELD        = 'je_field';
    const POST_OPTION       = 'je_field_option';
    const TAX_OPTION        = 'je_field_option';

    const META_TYPE         = 'je_field_type';
    const META_REQUIRED     = 'je_field_required';

    public function __construct(){
        $this->add_action('init', 'init');
    }

    public function init(){
        register_post_type( 'je_field', array(
            'labels' => array(
                'name'          => 'Custom Fields',
                'singular_name' => 'Custom Field',
                'plural_name'   => 'Custom Fields',
                'add_new'       => 'Add new',
                'add_new_item'  => 'Add new field',
                'edit_item'     => 'Edit field'
                ),
            'public'            => false,
            'publicly_queryable' => true,
            'show_ui'           => false, 
            'show_in_menu'      => false, 
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'field' ),
            'capability_type'   => 'post',
            'has_archive'       => true, 
            'hierarchical'      => false,
            'menu_position'     => null,
            'supports'          => array( 'title', 'editor', 'author' )
        ) );

        register_post_type( 'je_field_option', array(
            'labels' => array(
                'name'          => 'Custom field options',
                'singular_name' => 'Custom field option',
                'plural_name'   => 'Custom field options',
                'add_new'       => 'Add new',
                'add_new_item'  => 'Add new field',
                'edit_item'     => 'Edit field'
                ),
            'public'            => false,
            'publicly_queryable' => true,
            'show_ui'           => false, 
            'show_in_menu'      => false, 
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'field_option' ),
            'capability_type'   => 'post',
            'has_archive'       => true, 
            'hierarchical'      => false,
            'menu_position'     => null,
            'supports'          => array( 'title', 'editor', 'author' )
        ) );

        // register options
        register_taxonomy( 'je_field_option', array('job', 'je_field'), array(
            'hierarchical'            => false,
            'labels'                  => array(
                'name'                         => _x( 'Field Options', 'taxonomy general name' ),
                'singular_name'                => _x( 'Field Option', 'taxonomy singular name' ),
                'search_items'                 => __( 'Search Field Optionss' ),
                'popular_items'                => __( 'Popular Field Options' ),
                'all_items'                    => __( 'All Field Options' ),
                'parent_item'                  => null,
                'parent_item_colon'            => null,
                'edit_item'                    => __( 'Edit Field Option' ), 
                'update_item'                  => __( 'Update Field Option' ),
                'add_new_item'                 => __( 'Add New Field Option' ),
                'new_item_name'                => __( 'New Field Option Name' ),
                'separate_items_with_commas'   => __( 'Separate field options with commas' ),
                'add_or_remove_items'          => __( 'Add or remove field options' ),
                'choose_from_most_used'        => __( 'Choose from the most used field options' ),
                'not_found'                    => __( 'No field options found.' ),
                'menu_name'                    => __( 'Field options' )
                ),
            'show_ui'                 => false,
            'show_admin_column'       => false,
            //'update_count_callback'   => '_update_post_term_count',
            'query_var'               => true,
            'rewrite'                 => array( 'slug' => 'field_option' )
        ) );
    }

    static public function get_job_fields($post_id){
        $fields = self::get_all_fields();
        foreach ($fields as $field) {
            $field->value = get_post_meta($post_id, 'cfield-' . $field->ID, true );
        }
        return $fields;
    }


    // used to save and update job field and options
    static public function update_job_fields($post_id, array $args){  //note: array $args = (array)$_POST['cfield']; contains new customfield-value (or array of values if checkbox)
        $fields = self::get_all_fields();       // contains array of ALL customfields->ID


        foreach ($fields as $field) {


            if ( is_array($args)  )  { 
                    //keep calm
            }   
            elseif ( !isset( $args[$field->ID] )  ) continue; 

            switch ($field->type) {
                default:
                case 'text':  
                case 'select':
                    update_post_meta( $post_id, 'cfield-' . $field->ID, $args[$field->ID] );
                    break;
                case 'checkbox':          
                    // in this case $args contains array of new selected-checkbox-option-IDs
                    update_post_meta( $post_id, 'cfield-' . $field->ID, $args );                                                         
                    break;
                case 'date':
                    $time   =   strtotime( $args[$field->ID] );
                    if($args[$field->ID] === '' || $time === 0 )  
                        delete_post_meta( $post_id, 'cfield-' . $field->ID ) ;
                    else
                        update_post_meta( $post_id, 'cfield-' . $field->ID, date('Y-m-d h:i:s', strtotime( $args[$field->ID] ) ));
                    break;
            }
        }
    }

    static public function get_all_fields(){
        $fields = get_posts(array(
            'post_type' => self::POST_FIELD,
            'orderby'   => 'menu_order ID',
            'order'     => 'ASC',
            'numberposts' => -1
        ));
        $return = array();
        foreach ($fields as $field) {
            $return[] = self::get_field($field->ID);
        }
        return $return;
    }

    static public function get_options($id){
        $options = get_posts(array(
            'post_parent'   => $id,
            'post_type'     => self::TAX_OPTION,
            'orderby'       => 'ID',
            'order'         => 'ASC',
            'numberposts'   => -1
            ));
        $return = array();
        foreach ((array)$options as $option) {
            $return[] = (object)array(
                'ID'    =>  $option->ID,
                'name'  =>  $option->post_title,
                'desc'  =>  $option->post_content
                );
        }
        return $return;
    }

    static public function get_field_option($option_id){
        $option = get_post($option_id);
        if (!empty($option)){
            return (object)array(
                'ID'    => $option_id,
                'name'  => $option->post_title
            );
        }
        else 
            return false;
    }

    static public function sort_fields($sortData){
        global $wpdb;

        $sql = "UPDATE {$wpdb->posts} SET menu_order = CASE ID ";
        $sql_arr = array();
        foreach ($sortData as $key => $id) {
            $sql .= " WHEN {$id} THEN {$key} ";
        }
        $sql .= " END ";
        return $wpdb->query($sql);
    }


    // return single field as object
    static public function get_field($id, $object = OBJECT){
        $field  = get_post( $id );
        
        if (!$field) return false;

        $return = (object)array();
        $return->ID          = $field->ID;
        $return->name        = $field->post_title;
        $return->desc        = $field->post_content;
        $return->type        = get_post_meta( $id, self::META_TYPE, true );
        $return->required    = get_post_meta( $id, self::META_REQUIRED, true );
        $return->options     = array();
        $return->raw         = $field;

        return $return;   
    }

    // create a single field in custom-fields-admin-area
    static public function insert_field($args, $wp_error = false){
        //registers fields to and asigns them to post


        $args = wp_parse_args( $args, array(
            'name'      => '',
            'desc'      => '',
            'type'      => 'text',
            'required'  => false
        ) );

        $result = wp_insert_post(array(
            'post_type'     => 'je_field',
            'post_content'  => $args['desc'],
            'post_title'    => $args['name'],
            'post_status'   => 'publish'
        ));

        if ($result){
            update_post_meta( $result, self::META_TYPE, $args['type'] );
            update_post_meta( $result, self::META_REQUIRED, $args['required'] ? 1 : 0 );

            if ( $args['type'] == 'checkbox' && !empty($args['options'])){
                 
                // update values to field
                 self::update_field_option($result, $args['options']);
            }

            if ( $args['type'] == 'select' && !empty($args['options'])){
                self::update_field_option($result, $args['options']);
                //wp_set_object_terms( $result, $terms, self::TAX_OPTION );
            }
        }

        // return result
        return $result;
    }

    // delete a single field in custom-fields-admin-area
    static public function delete_field($id){
        // delete field options first
        $options = get_posts( array(
            'post_parent'   => $id, 
            'post_status'   => array('publish', 'trash', 'draft'),
            'post_type'     => self::POST_OPTION
            ) );

        if ( false !== wp_delete_post($id) ){
            foreach ($options as $option => $value) {
                wp_delete_post($option);
            }
            return true;
        } else {
            return false;
        }
    }

    // update a single field in custom-fields-admin-area
    static public function update_field($id, $args){
        $params = array(
            'ID'            => $args['ID'],
            'post_title'    => $args['name'],
            'post_content'  => $args['desc']
            );
        $result = wp_update_post($params);

        if ( isset($args['type']) )
            update_post_meta( $id, self::META_TYPE, $args['type'] );

        if ( isset($args['required'])  )
            if ($args['required'])
                update_post_meta( $id, self::META_REQUIRED, 1 );
            else 
                update_post_meta( $id, self::META_REQUIRED, 0 );

        if ( $args['type'] == 'checkbox' && !empty($args['options'])){
            self::update_field_option($params['ID'], $args['options']);
        }

        if ( $args['type'] == 'select' && !empty($args['options'])){
            self::update_field_option($params['ID'], $args['options']);
        }

        return $result;
    }


    // handle options in custom-field-admin-area
    static protected function update_field_option($id, $options){
        $oldOptions = self::get_options($id);        

        foreach ((array)$options as $option) {
            // if id exist, update new one
            if (is_array($option) && !empty($option['id']) && !empty($option['name'])){
                self::update_option($option['id'], $option['name']);

                // find and remove update fields in old options
                foreach ($oldOptions as $key => $opt) {
                    if ($opt->ID == $option['id'])
                        unset($oldOptions[$key]);
                }
            }
            else if (is_string($option) ){ // unless, add new
                self::add_option($id, $option);
            }
        }

        // delete removed options
        foreach ($oldOptions as $opt) {
            wp_delete_post( $opt->ID, true );
        }
    }


    //adds option to custom-field-admin area
    static protected function add_option($field_id, $name, $order = null){
        if (empty($name)) return false;
        return wp_insert_post(array(
            'post_title'    => $name,
            'post_parent'   => $field_id,
            'post_type'     => self::TAX_OPTION,
            'post_status'   => 'publish',
            'menu_order'    => $order ? $order : $field_id 
        ));
    }

    // update options in custom-field-admin-area
    static protected function update_option($option_id, $name, $order = null){
        return wp_update_post(array(
            'ID'            => $option_id,
            'post_title'    => $name
        ));  
    }
}