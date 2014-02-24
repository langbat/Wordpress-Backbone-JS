<?php

class JEP_Fields_Admin extends JEP_Fields_Init {

    CONST ACTION_ADMIN_INIT = 'admin_init';
    const ACTION_AJAX_DEL_FIELD = 'wp_ajax_et_delete_field';
    const ACTION_AJAX_SORT_FIELDS = 'wp_ajax_et_sort_fields';
    const ACTION_SAVE_JOB = 'je_save_job';
    const ACTION_META_BOX = 'et_job_meta_box';
    const ACTION_SAVE_POST = 'save_post';

    public function __construct() {
        parent::__construct();

        $this->add_action('et_admin_menu', 'add_admin_menu');

        $this->add_action('et_admin_enqueue_styles-et-job-fields', 'on_print_styles');
        $this->add_action('et_admin_enqueue_scripts-et-job-fields', 'on_print_scripts');

        $this->add_action(self::ACTION_ADMIN_INIT, 'on_handle_post');
        $this->add_action(self::ACTION_AJAX_DEL_FIELD, 'on_delete_field');
        $this->add_action(self::ACTION_AJAX_SORT_FIELDS, 'on_sort_fields');

        $this->add_action(self::ACTION_SAVE_JOB, 'on_save_job');

        $this->add_action(self::ACTION_META_BOX, 'job_meta_box');
        $this->add_action(self::ACTION_SAVE_POST, 'on_save_post');
    }

    public function on_print_styles() {
        $this->add_style('job-fields-style', JEP_FIELD_URL . '/css/admin.css');
        $this->add_existed_style('admin_styles');
    }

    public function on_print_scripts() {

        //$this->add_existed_script('jquery');
        $this->add_existed_script('et_underscore');
        $this->add_existed_script('et_backbone');
        $this->add_existed_script('jquery-ui-sortable');

        if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')) {
            $this->add_script('job-field-script-add', JEP_FIELD_URL . '/js/admin_add.js', array('jquery', 'et_underscore', 'et_backbone'));
            wp_localize_script('job-field-script-add', 'et_fields', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'confirm_del' => __('Are you sure you want to delete this option?', ET_DOMAIN)
            ));
        } else {
            $this->add_script('job-field-script', JEP_FIELD_URL . '/js/admin.js', array('jquery', 'et_underscore', 'et_backbone'));
            wp_localize_script('job-field-script', 'et_fields', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonceDelete' => wp_create_nonce('delete_field'),
                'nonceSort' => wp_create_nonce('sort_field')
            ));
        }
    }

    /**
     * Add menu field
     */
    public function add_admin_menu() {
        et_register_menu_section('pa-job-fields', array(
            'menu_title' => 'PA Zusatzoptionen',
            'page_title' => 'Zusatzoptionen',
            'page_subtitle' => __("Manage your job's additional information", ET_DOMAIN),
            'callback' => array($this, 'menu_view'),
            'slug' => 'et-job-fields',
        ));
    }

    /**
     * handle saving job
     */
    public function on_save_job($job_id) {
        if (empty($_REQUEST['content']['raw']))
            return;

        wp_parse_str($_REQUEST['content']['raw'], $args);

        if (empty($args['cfield']))
            return;
        $fields = $args['cfield'];

        JEP_Field::update_job_fields($job_id, $fields);
    }

    public function on_save_post($post_id) {
        if (!isset($_POST['_additional_nonce']) || !wp_verify_nonce($_POST['_additional_nonce'], 'additional_fields'))
            return;

        if (!current_user_can('manage_options'))
            return;

        $fields = (array) $_POST['cfield'];

        JEP_Field::update_job_fields($post_id, $fields);
    }

    public function job_meta_box($post) {
// echo '<i>'.__METHOD__ . ' in admin.php<br></i>' ;		
        ?>
        <h4><?php _e('Additional information', ET_DOMAIN) ?>:</h4>
        <input type="hidden" name="_additional_nonce" value="<?php echo wp_create_nonce('additional_fields') ?>">
        <?php
        $fields = JEP_Field::get_job_fields($post->ID);
        foreach ($fields as $field) {
            ?>
            <p>
            <h4><strong><?php echo $field->name ?></strong><h4>

                    <?php
                    switch ($field->type) {
                        default:
                        case 'text':
                            echo '<input type="text" name="cfield[' . $field->ID . ']" value="' . $field->value . '">';
                            break;

                        case 'date':
                            if ($field->value === '' || strtotime($field->value) === 0)
                                $value = '';
                            else
                                $value = date(get_option('date_format'), strtotime($field->value));
                            echo '<input type="text" class="datepicker" name="cfield[' . $field->ID . ']" value="' . $value . '">';
                            break;

                        case 'select':
                            $options = JEP_Field::get_options($field->ID);
                            echo '<select name="cfield[' . $field->ID . ']">';

                            foreach ($options as $opt) {
                                $selected = $opt->ID == $field->value ? 'selected="selected"' : '';
                                echo '<option value="' . $opt->ID . '" ' . $selected . '>' . $opt->name . '</option>';
                            }
                            echo '</select>';
                            break;

                        case 'checkbox':
                            $options = JEP_Field::get_options($field->ID);
                            $value = get_post_meta($post->ID, 'cfield-'.$field->ID, true);
                            
                            if (count($value) && isset($value[$field->ID]))
                                $value = $value[$field->ID];
                                
                            echo '<ul class="check_list">';
                            foreach ($options as $opt) {
                                $v = $field->ID; // in this case, $field->value is an array, so use proper value of object

                                $checked = ( is_array($value) && in_array($opt->ID, $value) ) ? 'checked="checked"' : '';  // mark the checkbox if current option is in database

                                echo '<li><label><input type="checkbox" name="cfield[' . $field->ID . '][]" value="' . $opt->ID . '" ' . $checked . '>  ' . $opt->name . '</label></li>';
                            }
                            echo '</ul>';
                            break;
                    }
                    ?>

                    </p>
                    <?php
                }
                ?>
                <?php
            }

            /**
             * Handle post
             */
            public function on_handle_post() {
                $this->msg = isset($_COOKIE['et_field_msg']) ? $_COOKIE['et_field_msg'] : '';
                setcookie("et_field_msg", "", time() - 3600, '/');

                if (isset($_POST['_nonce']) && wp_verify_nonce($_POST['_nonce'], 'add_field')) {
                    $this->on_add_field();
                } else if (isset($_POST['_nonce']) && wp_verify_nonce($_POST['_nonce'], 'edit_field')) {
                    $this->on_edit_field();
                }
            }

            /**
             * Handle post to add field
             */
            protected function on_add_field() {
                // register fields to post
                $args = array(
                    'name' => $_POST['name'],
                    'type' => $_POST['type'],
                    'desc' => $_POST['desc'],
                    'options' => $_POST['options'],
                    'required' => $_POST['required']
                );
                if ($result = JEP_Field::insert_field($args)) {
                    setcookie('et_field_msg', __('Your new field has been inserted to "Post-a-Job"-form', ET_DOMAIN), time() + 3600, '/');
                    wp_redirect(remove_query_arg(array('action', 'id')));
                }
            }

            /**
             * handle post to edit field
             */
            protected function on_edit_field() {
                $args = array(
                    'ID' => $_POST['ID'],
                    'name' => $_POST['name'],
                    'type' => $_POST['type'],
                    'desc' => $_POST['desc'],
                    'options' => $_POST['options'],
                    'required' => $_POST['required']
                );
                if ($result = JEP_Field::update_field($args['ID'], $args)) {
                    setcookie('et_field_msg', __('Field has been updated', ET_DOMAIN), time() + 3600, '/');
                    wp_redirect(remove_query_arg(array('action', 'id')));
                }
            }

            /**
             * Ajax handle: delete field
             */
            public function on_delete_field() {
                $postData = $_POST['content'];
                try {
                    if (isset($postData['_nonce']) && wp_verify_nonce($postData['_nonce'], 'delete_field')) {

                        if (!current_user_can('manage_options'))
                            throw new Exception(__('You dont have permission to perform this action', ET_DOMAIN));

                        if (isset($postData['id'])) {
                            if (JEP_Field::delete_field($postData['id'])) {
                                $response = array('success' => true);
                            } else {
                                throw new Exception(__("Can't delete field", ET_DOMAIN));
                            }
                        } else {
                            throw new Exception(__('Field id doesnt exist', ET_DOMAIN));
                        }
                    }
                } catch (Exception $e) {
                    $response = array(
                        'success' => false,
                        'msg' => $e->getMessage()
                    );
                }

                header('HTTP/1.0 200 OK');
                header('Content-type: application/json');
                echo json_encode($response);
                exit;
            }

            public function on_sort_fields() {
                $postData = $_POST['content'];
                try {
                    if (!current_user_can('manage_options'))
                        throw new Exception(__("You don't have permission to perform this action", ET_DOMAIN));
                    if (isset($postData['data'])) {
                        parse_str($postData['data'], $pos);
                        if (JEP_Field::sort_fields(array_values($pos['item'])))
                            $reponse = array(
                                'success' => true
                            );
                        else
                            throw new Exception(__('Cannot sort', ET_DOMAIN));
                    }
                } catch (Exception $e) {
                    $reponse = array(
                        'success' => false,
                        'msg' => $e->getMessage()
                    );
                }

                header('HTTP/1.0 200 OK');
                header('Content-type: application/json');
                echo json_encode($reponse);
                exit;
            }

            /**
             * LIST VIEW
             */
            public function menu_view($args) {
                if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')) {
                    $this->add_view($args);
                    return;
                }
                ?>
                <div class="et-main-header">
                    <div class="title font-quicksand"><?php echo $args->menu_title ?></div>
                    <div class="desc"><?php // echo $args->page_subtitle   ?></div>
                </div>
                <div class="et-main-main et-main-full no-menu clearfix inner-content" id="job-fields-list">
                    <?php
                    if (!empty($this->msg)) {
                        echo '<div class="updated"><p>' . $this->msg . '</p></div>';
                    }
                    ?>
                    <div class="title font-quicksand">
                        <a href="<?php echo add_query_arg('action', 'add') ?>" class="new-link"><?php _e('New', ET_DOMAIN) ?></a>
                        <?php _e('Custom fields list', ET_DOMAIN) ?>
                    </div>
                    <div class="desc">
                        <ul class="ordered-list sortable" id="lst_fields">
                            <?php
                            $fields = JEP_Field::get_all_fields();
                            foreach ($fields as $field) {
                                ?>
                                <li class="item item-<?php echo $field->ID ?>" id="item_<?php echo $field->ID ?>">
                                    <div class="sort-handle"></div>
                                    <span><strong><?php echo $field->name ?></strong> <?php if ($field->required) echo '(' . __('required', ET_DOMAIN) . ')' ?></span>  
                                    <div class="actions">
                                        <a href="<?php echo add_query_arg(array('action' => 'edit', 'id' => $field->ID)) ?>" title="<?php _e('Edit', ET_DOMAIN) ?>" class="icon act-edit" data-icon="p"></a>
                                        <a href="#" title="<?php _e('Delete', ET_DOMAIN) ?>" data="<?php echo $field->ID ?>" class="icon act-del" data-icon="D"></a>
                                    </div>
                                </li>
                            <?php } ?>
                            <!-- <li class="item" id="" data="">
                                    <div class="sort-handle"></div>
                                    <span><strong>Soft Skill</strong> (required)</span>  
                                    <div class="actions">
                                            <a href="#" title="<?php _e('Edit', ET_DOMAIN) ?>" class="icon act-edit" rel="<?php echo $plan['ID'] ?>" data-icon="p"></a>
                                            <a href="#" title="<?php _e('Delete', ET_DOMAIN) ?>" class="icon act-del" rel="<?php echo $plan['ID'] ?>" data-icon="D"></a>
                                    </div>
                            </li> -->
                        </ul>
                    </div>
                </div>
                <?php
            }

            public function add_view($args) {
                if (isset($_GET['id']))
                    $field = JEP_Field::get_field($_GET['id']);
                else
                    $field = (object) array(
                                'name' => '',
                                'desc' => '',
                                'type' => 'text',
                                'options' => array(),
                                'required' => false
                    );
                ?>
                <div class="et-main-header">
                    <div class="title font-quicksand"><?php echo $args->menu_title ?></div>
                    <div class="desc"><?php _e("Add a new job information field in Post a Job page.", ET_DOMAIN) ?></div>
                </div>
                <div class="et-main-main et-main-full no-menu clearfix inner-content" id="job-fields-add">
                    <div class="title font-quicksand">
                        <a href="<?php echo remove_query_arg(array('action', 'id')) ?>" class="back-link"> <span class="icon" data-icon="["></span>&nbsp;&nbsp;<?php _e('Back to custom fields list', ET_DOMAIN) ?></a>
                        <?php
                        if (isset($_GET['id'])) {
                            _e('Edit a field', ET_DOMAIN);
                        } else {
                            _e('Add a new field', ET_DOMAIN);
                        }
                        ?>
                    </div>
                    <div class="desc">
                        <form action="" method="post" id="fadd" class="et-form">
                            <?php
                            if (isset($field->ID))
                                echo '<input type="hidden" name="ID" value="' . $field->ID . '">';
                            if ($_GET['action'] == 'add')
                                echo '<input type="hidden" name="_nonce" value="' . wp_create_nonce('add_field') . '">';
                            else if ($_GET['action'] == 'edit')
                                echo '<input type="hidden" name="_nonce" value="' . wp_create_nonce('edit_field') . '">';
                            ?>
                            <div class="form-item">
                                <label for="name"><?php _e("Field name", ET_DOMAIN) ?></label>
                                <input type="text" name="name" placeholder="<?php _e('Enter a field name', ET_DOMAIN) ?>" value="<?php echo $field->name ?>">
                            </div>
                            <div class="form-item">
                                <label for="name"><?php _e("Field Description", ET_DOMAIN) ?></label>
                                <textarea name="desc" placeholder="<?php _e("Enter field's description", ET_DOMAIN) ?>" cols="30" rows="10"><?php echo $field->desc ?></textarea>
                            </div>
                            <div class="form-item">
                                <label for="name"><?php _e("Field type", ET_DOMAIN) ?></label>
                                <span class="cb-field">
                                    <label>
                                        <input type="radio" name="type" value="text" <?php echo $field->type == 'text' ? 'checked="checked"' : '' ?>>
                                        <span class="field-type field-text">Text</span>
                                    </label>	
                                </span>
                                <span class="cb-field">
                                    <label>
                                        <input type="radio" name="type" value="select" <?php echo $field->type == 'select' ? 'checked="checked"' : '' ?>>
                                        <span class="field-type field-text">Drop</span>
                                    </label>	
                                </span>
                                <span class="cb-field">
                                    <label>
                                        <input type="radio" name="type" value="date" <?php echo $field->type == 'date' ? 'checked="checked"' : '' ?>>
                                        <span class="field-type field-text">Date</span>
                                    </label>
                                </span>
                                <span class="cb-field">
                                    <label>
                                        <input type="radio" name="type" value="checkbox" <?php echo $field->type == 'radio' ? 'checked="checked"' : '' ?>>
                                        <span class="field-type field-text">Checkbox</span>
                                    </label>
                                </span>					



                            </div>
                            <div class="form-item form-drop" <?php
                            if (( $field->type = 'select' ) ||  ($field->type = 'checkbox')) {
                                
                            } else {
                                echo 'style="display:none"';
                            }
                            ?>>
                                <label for="option"></label>
                                <ul class="form-options">
                                    <?php
                                    $count = 0;
                                    if (($field->type == 'select') || ($field->type == 'checkbox'))
                                        $options = JEP_Field::get_options($field->ID);
                                    else
                                        $options = array();
                                    if (!empty($options)) {
                                        foreach ($options as $option) {
                                            ?>
                                            <li class="form-option">
                                                <input type="hidden" name="options[<?php echo $count ?>][id]" value="<?php echo $option->ID ?>">
                                                <input type="text" name="options[<?php echo $count ?>][name]" value="<?php echo $option->name ?>">
                                                <div class="controls controls-2">
                                                    <a class="button act-open-form del-opt" rel="33" title="<?php _e('Delete this option') ?>">
                                                        <span class="icon" data-icon="*"></span>
                                                    </a>
                                                </div>	
                                            </li>
                                            <?php
                                            $count++;
                                        }
                                    }
                                    ?>
                                    <li class="form-option">
                                        <input type="text" name="options[<?php echo $count ?>]" id="">
                                        <div class="controls controls-2">
                                            <a class="button act-open-form del-opt" rel="33" title="<?php _e('Delete this option') ?>">
                                                <span class="icon" data-icon="*"></span>
                                            </a>
                                        </div>	
                                    </li>
                                </ul>
                                <script type="text/template" id="tl_option">
                                    <li class="form-option">
                                    <input type="text" name="options[<%= id %>]" id="">
                                    <div class="controls controls-2">
                                    <a class="button act-open-form" title="<?php _e('Delete this option') ?>">
                                    <span class="icon" data-icon="*"></span>
                                    </a>
                                    </div>	
                                    </li>
                                </script>
                            </div>
                            <div class="form-item">
                                <label for="required"><?php _e('Required', ET_DOMAIN) ?></label>
                                <input type="hidden" name="required" value="0" id="">
                                <input type="checkbox" name="required" value="1" id="" <?php if ($field->required) echo 'checked="checked"' ?>><?php _e("Check this if field is required", ET_DOMAIN) ?>
                            </div>
                            <input type="submit" class="et-button btn-button load-more" value="<?php _e("Save", ET_DOMAIN) ?>">
                        </form>
                    </div>
                </div>
                <?php
            }

        }

        ;