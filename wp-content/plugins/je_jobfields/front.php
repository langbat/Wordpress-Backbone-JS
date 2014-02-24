<?php

class JEP_Fields_Front extends JEP_Fields_Init {

    const ACTION_POST_JOB = 'et_post_job_fields';
    const ACTION_EDIT_JOB = 'et_edit_job_fields';
    const ACTION_PRINT_SCRIPT = 'et_enqueue_scripts';
    const ACTION_PRINT_STYLE = 'wp_print_styles';
    const ACTION_SINGlE_JOB_FIELDS = 'je_single_job_fields';
    const ACTION_SEARCH_JOB_FIELDS = 'pa_search_job_fields';

    public function __construct() {
        parent::__construct();
        $this->add_action(self::ACTION_INIT, 'on_init');
    }

    public function on_init() {

        $this->add_action(self::ACTION_POST_JOB, 'on_post_job_fields');
        $this->add_action(self::ACTION_EDIT_JOB, 'on_edit_job_fields');
        $this->add_action(self::ACTION_PRINT_SCRIPT, 'on_enqueue_scripts', 20);
        $this->add_action(self::ACTION_PRINT_STYLE, 'on_enqueue_styles', 20);
        $this->add_action(self::ACTION_SINGlE_JOB_FIELDS, 'on_single_job_fields');
        $this->add_action(self::ACTION_SEARCH_JOB_FIELDS, 'on_search_job_fields');
    }

    public function on_enqueue_scripts() {
        if (is_page_template('page-post-a-job.php') ||
                is_home() ||
                is_archive('job') ||
                is_tax('job_category') ||
                is_tax('job_type') ||
                is_singular('job') ||
                is_page_template('page-dashboard.php')) {
            $this->add_existed_script('jquery-ui-datepicker');
            $this->add_script('field-post-a-job', JEP_FIELD_URL . '/js/front.js', array('jquery', 'jquery-ui-datepicker'));

            wp_localize_script('field-post-a-job', 'jep_field', array(
                'dateFormat' => $this->convert_php_date_format(get_option('date_format'))
            ));
        }
    }

    private function convert_php_date_format($df) {
        $replace = array(
            'd' => 'dd', // two digi date
            'j' => 'd', // no leading zero date
            'm' => 'mm', // two digi month
            'n' => 'm', // no leading zero month
            'l' => 'DD', // date name long
            'D' => 'D', // date name short
            'F' => 'MM', // month name long
            'M' => 'M', // month name shỏrt
            'Y' => 'yy', // 4 digits year
            'y' => 'y',
        );
        $return = str_replace(array_keys($replace), array_values($replace), $df);
        return $return;
    }

    public function on_enqueue_styles() {

        $this->add_style('job-fields-front-style', JEP_FIELD_URL . '/css/front.css');


        if (is_page_template('page-post-a-job.php') ||
                is_home() ||
                is_archive('job') ||
                is_tax('job_category') ||
                is_tax('job_type') ||
                is_singular('job') ||
                is_page_template('page-dashboard.php')) {
            global $wp_scripts, $post;
            $ui = $wp_scripts->query('jquery-ui-core');
            $url = "http://code.jquery.com/ui/{$ui->ver}/themes/smoothness/jquery-ui.css";
            wp_enqueue_style('jquery-ui-redmond', $url, false, $ui->ver);
        }
    }

    /**
     * Display custom job fields in single job page  (read only)
     */
    public function on_single_job_fields($job) {
        $fields = JEP_Field::get_all_fields();
// echo '<i>'.__METHOD__ . ' in front.php<br></i>' ;




        echo '<div class="job_fields"> ';

        foreach ($fields as $field) {
            $label = $field->name;
            $field_get_value = array(983, 1096);
            if (in_array($field->ID, $field_get_value)) :
                $value = get_post_meta($job->ID, 'cfield-' . $field->ID, true);  // could be either array (for checkboxes ) or string (everything else)

                if (!!$value) :  // prevent displaying nonsense
                    switch ($field->type) {
                        case 'select':
                            $options = JEP_Field::get_options($field->ID);

                            echo '<h4>' . $label . ':</h4>';
                            foreach ($options as $option) {

                                if ($option->ID == $value) {
                                    echo '<p>' . $option->name . '</p>';
                                    break;
                                }
                            }
                            break;

                        case 'checkbox':
                            $options = JEP_Field::get_options($field->ID);

                            $i = 0;
                            echo '<h4>' . $label . '</h4> <ul class="check_list" name="cfield[' . $field->ID . ']">';

                            foreach ($value as $v) {

                                foreach ($options as $option) {

                                    if (is_array($v) && in_array($option->ID, $v)) {
                                        echo '<li class="checked" >' . $option->name . '</li>';
                                    }
                                }
                            }

                            echo '</ul>';
                            break;

                        case 'date':
                            if (strtotime($value) == 0)
                                break;
                            $date = date(get_option('date_format'), strtotime($value));
                            echo '<p><strong>' . $label . '</strong>:  ' . $date . '</p>';
                            break;

                        case 'text':
                        default:
                            echo '<p><strong>' . $label . '</strong>: ' . $value . '</p>';
                            break;
                    }
                endif;
            endif;
        }
        echo '</div>';
    }

    /**
     * Displays  custom fields in post-a-job-page
     */
    public function on_post_job_fields() {
// echo '<i>'.__METHOD__ . ' in front.php<br></i>' ;
        $job_id = get_query_var('job_id');
        $fields = JEP_Field::get_all_fields();

        echo '<div class="job_fields"> ';

        foreach ($fields as $field) {
            $name = 'cfield[' . $field->ID . ']';
            $required = $field->required ? 'input-required required' : '';
            if ($field->type != "checkbox") {
                ?>
                <div class="form-item" id="form-item-<?php echo $field->ID?>">
                    <?php
                    ?>
                    <div class="label">
                        <h6 class=""><?php echo $field->name ?></h6>
                        <?php echo $field->desc ?>
                    </div>
                    <?php
                    if ($job_id) {
                        $value = get_post_meta($job_id, 'cfield-' . $field->ID, true);
                    }
                    else
                        $value = '';

                    switch ($field->type) {
                        case 'select':
                            ?>
                            <div class="select-style btn-background border-radius">
                                <select class="input-field <?php echo $required ?>" name="cfield[<?php echo $field->ID ?>]" >
                                    <?php
                                    $options = JEP_Field::get_options($field->ID);
                                    foreach ($options as $option) {
                                        if ($option->ID == $value)
                                            $checked = 'selected="selected"';
                                        else
                                            $checked = '';
                                        echo '<option ' . $checked . ' value="' . $option->ID . '">' . $option->name . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php
                            break;

                        case 'checkbox':
                            ?>
                            <!--<div class="">
                                <ul class="checkbox <?php echo $required ?>"  >
                            <?php
                            //$options = JEP_Field::get_options($field->ID);
                            //foreach ($options as $option) {
                            //echo '<li><label><input type="checkbox" name="cfield[' . $field->ID . '][]" value="' . $option->ID . '">&nbsp; ' . $option->name . '</label></li>';
                            //}
                            ?>
                                </ul>
                            </div>-->
                            <?php
                            break;

                        case 'text' :
                            ?>
                            <div>
                                <input type="text" class="bg-default-input input-field <?php echo $required ?>" name="cfield[<?php echo $field->ID ?>]" value="<?php echo $value ?>" />
                            </div>
                            <?php
                            break;

                        case 'date' :
                            if ($value != '') {
                                ?>
                                <div class="input-date">
                                    <div class="icon icon-date" data-icon="\"></div>
                                         <input type="text" class="bg-default-input input-field <?php echo $required ?>" name="cfield[<?php echo $field->ID ?>]" value="<?php echo date(get_option('date_format'), strtotime($value)); ?>" />
                                </div>
                                <?php
                            } else {
                                ?>
                                <div class="input-date">
                                    <div class="icon icon-date" data-icon="\"></div>
                                         <input type="text" class="bg-default-input input-field <?php echo $required ?>" name="cfield[<?php echo $field->ID ?>]" value="<?php echo $value; ?>" />
                                </div>
                                <?php
                            }

                            break;

                        default:
                            # code...
                            break;
                    }
                    ?>
                </div>
                <?php
            }
        }
        ?>
        </div>

        <?php
    }

    /**
     * Displays  custom fields in edit-job-page
     */
    public function on_edit_job_fields() {
        $fields = JEP_Field::get_all_fields();
        foreach ($fields as $field) {
            $name = 'cfield[' . $field->ID . ']';
            $required = $field->required ? 'input-required required' : '';
            if ($field->type != "checkbox") {
                ?>
                <div class="form-item">
                    <div class="label">
                        <h6><?php echo $field->name ?></h6>
                    </div>
                    <?php
                    switch ($field->type) {
                        case 'text':
                            ?>
                            <div><input type="text" class="bg-default-input input-field <?php echo $required ?> cfield-<?php echo $field->ID ?>" name="<?php echo $name ?>" id=""></div>
                            <?php
                            break;

                        case 'date':
                            ?>
                            <div class="input-date">
                                <div class="icon icon-date" data-icon="\"></div>
                                     <input type="text" class="bg-default-input input-field cfield-<?php echo $field->ID ?> <?php echo $required ?>" name="<?php echo $name ?>" id="">
                            </div>
                            <?php
                            break;

                        case 'select':
                            $options = JEP_Field::get_options($field->ID);
                            ?>
                            <div class="select-style btn-background border-radius">
                                <select class="input-field <?php echo $required ?> cfield-<?php echo $field->ID ?>" name="<?php echo $name ?>" id="">
                                    <?php
                                    foreach ($options as $option) {
                                        echo '<option value="' . $option->ID . '">' . $option->name . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php
                            break;

                        case 'checkbox':
                            //$options = JEP_Field::get_options($field->ID);
                            //echo '<div class="" ><ul class="check_list  checkbox cfield-' . $field->ID . '" ' . $required . ' id="" >';
                            //foreach ($options as $opt) {
                            //$v = $field->ID; // in this case, $field->value is an array, so use proper value of object
                            //$checked = ( is_array($v) && in_array($opt->ID, $field->value[$v]) ) ? 'checked="checked"' : '';  // mark the checkbox if current option is in database
                            //echo '<li><label><input type="checkbox" name="cfield[' . $field->ID . '][]" value="' . $opt->ID . '" ' . $checked . '>  ' . $opt->name . '</label></li>';
                            //}
                            //echo '</ul></div>';
                            break;

                        default:

                            break;
                    }
                    ?>
                </div>
                    <?php
                }
            }
        }

        /**
         * Display custom job fields in search-results template
         */
        public function on_search_job_fields($field) {
            $fields = JEP_Field::get_all_fields();


            // display klinik/praxis only

            /*
              foreach ($fields as $field) {
              $name 		= 'cfield['.$field->ID.']';
              $required 	= $field->required ? 'input-required required' : '';
              ?>
              <div class="label">  </div>

              <div class="input-date">
              <div class="icon icon-date" data-icon="\"></div>
              <input type="text" class="bg-default-input input-field cfield-<?php echo $field->ID ?> <?php echo $required ?>" name="<?php echo $name ?>" id="">
              </div>
              </div>

              }
             */
        }

    }

// end class
    ?>