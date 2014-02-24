<?php

class JEP_Fields_Init extends JEP_Fields_Base {

    const FILTER_REPONSE_JOB = 'et_jobs_ajax_response';

    public function __construct() {
        parent::__construct();
        $this->add_filter(self::FILTER_REPONSE_JOB, 'update_response_job');
    }

    /**
     * 
     */
    public function update_response_job($job) {
        $fields = JEP_Field::get_all_fields();
        $return = array();
        foreach ($fields as $field) {
            switch ($field->type) {
                case 'text':
                case 'select':
                case 'checkbox':
                default:
                    $return[] = array(
                        'ID' => $field->ID,
                        'type' => $field->type,
                        'value' => get_post_meta($job['ID'], 'cfield-' . $field->ID, true)
                    );
                    break;

                case 'date':
                    $time = get_post_meta($job['ID'], 'cfield-' . $field->ID, true);
                    if ($time === '' || strtotime($time) === 0)
                        $time = '';
                    else
                        $time = date(get_option('date_format'), strtotime($time));
                    $return[] = array(
                        'ID' => $field->ID,
                        'type' => $field->type,
                        'value' => $time
                    );
                    break;
            }
        }
        $job['fields'] = $return;
        return $job;
    }

}