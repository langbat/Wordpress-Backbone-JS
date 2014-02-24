<?php
// this file displays jobs in  latest-post-box  on frontpage

global $post, $job;
//full_location
$job_cat = array();
if (isset($job['categories'][0])) {
    foreach ($job['categories'] as $cat) {
        $job_cat[] = '<a href="' . $cat['url'] . '"> ' . $cat['name'] . ' </a>';
    }
}

$job_cat = implode(', ', $job_cat);

$job_type = isset($job['job_types'][0]) ? $job['job_types'][0] : '';
// $featured = $job['featured'] == 1 ? 'featured' : '';
// $dataSort = $job['featured'] == 1 ? 1 : 0;

$company = et_create_companies_response($job['author_id']);
$company_logo = $company['user_logo'];
$info_job = getJobPackageType($job['job_package']);

// add this company data to the array to pass to js
if (!isset($arrAuthors[$company['id']])) {
    $arrAuthors[$company['id']] = array(
        'display_name' => $company['display_name'],
        'user_url' => $company['user_url'],
        'user_logo' => $company_logo
    );
}
?>

<div class="box-job <?php echo $info_job['pack']; ?>" data-sort="<?php echo $info_job['datasort']; ?>">
    <li class="job-item clearfix clear <?php echo $info_job['pack']; ?>">
        <div class="row-fluid">
            <div class="span2 logo">
                <div class="thumb" style="vertical-align: middle;">
                    <?php
                    if (!empty($company_logo['attach_id']) && file_exists(get_attached_file($company_logo['attach_id'])) == true) {
                        ?>
                        <a id="job_author_thumb" data="<?php echo $company['ID']; ?>" href="<?php echo $company['post_url']; ?>" 
                           title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" class="thumb">
                            <img src="<?php echo ( isset($company_logo['large']) && !empty($company_logo['large']) ) ? $company_logo['large'][0] : $company_logo['small_thumb'][0]; ?>" id="company_logo_thumb" data="<?php echo $company_logo['attach_id']; ?>" />
                        </a>
                        <?php
                    } else {
                        ?>
                        <a id="job_author_thumb" data="" 
                           title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" class="thumb">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/default_logo.jpg" id="company_logo_thumb" data="" />
                        </a>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="span10 job-info">
                <div class="row-fluid">
                    <a class="title-link title"  href="<?php the_permalink() ?>" title="<?php printf(__('View more details of %s', ET_DOMAIN), get_the_title()) ?>">
                        <h2><?php the_title(); ?></h2>
                    </a>
                </div>
                <div class="row-fluid">
                    <div class="span4 desc">
                        <div class="cat company_name">
                            <a data="<?php echo $company['ID']; ?>" href="<?php echo $company['post_url']; ?>" title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), 'praktischArzt') ?>">
                                <?php echo $company['display_name'] ?>
                            </a>
                        </div>

                        <?php if ($job['full_location'] != '') { ?>
                            <div >
                                <span class="icon location" data-icon="@"></span><span class="nline"><?php echo $job['full_location'] ?></span>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="span8">
                        <div class="span5 desc">
                            <?php if ($job_cat) { ?>
                                <div >
                                    <span class="nline"><?php echo $job_cat; ?></span>
                                </div>
                            <?php } ?> 

                            <div class="company_label clear">
                                <?php
                                //get company Zertifikat logo
                                echo getZertifikat($job['author_id']);

                                //get job verband logo
                                echo getJobVerbandLogo($job['ID']);
                                ?>
                            </div>
                        </div>
                        <div class="span7">
                            <div class="span6">
                                <div class="desc">  
                                    <?php if ($job_type != '') { ?>
                                        <div class="job-type <?php echo 'color-' . $job_type['color'] ?>">
                                            <span class="flag"></span>
                                            <a href="<?php echo $job_type['url']; ?>" title="<?php printf(__('View all posted jobs in %s', ET_DOMAIN), $job_type['name']); ?>">
                                                <?php echo $job_type['name'] ?>
                                            </a>
                                        </div>
                                    <?php } ?>


                                    <?php if ($job['date'] != '') { ?>
                                        <div class="">
                                            <span class="icon" data-icon="t"></span><span class="job-date"><?php echo formatDateDE($job['date']); ?></span>
                                        </div>
                                    <?php } ?>


                                    <div class="is_klinik">
                                        <?php
                                        // $fields = $job['fields'];
                                        $isClinic_CField = get_page_by_path('klinikum-ambulanz', 'OBJECT', 'je_field'); //ID of custom field is_klinik
                                        $fields = JEP_Field::get_all_fields();
                                        foreach ($fields as $field) {
                                            $label = $field->name;
                                            $value = get_post_meta($job['ID'], 'cfield-' . $field->ID, true);

                                            if ((!!$value ) && ( $field->type == 'select' ) && ($field->ID == $isClinic_CField->ID )) :    // get Klinikum-ambulance only

                                                $options = JEP_Field::get_options($field->ID);

                                                foreach ($options as $option) {
                                                    if ($option->ID == $value) {
                                                        echo '<span class="icon" data-icon="H"></span><span>' . $option->name . '</span>';
                                                        break;
                                                    }
                                                }


                                            endif;
                                        }
                                        ?>
                                    </div><!-- is klinik -->

                                </div>
                            </div>
                            <div class="span6">
                                <div class="btn-select f-right">
                                    <a class="title-link"  href="<?php the_permalink() ?>" title="<?php printf(__('View more details of %s', ET_DOMAIN), get_the_title()) ?>">
                                        <button class="bg-btn-hyperlink border-radius">   Stelle ansehen   </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div> 
        <br clas="clear"> 
    </li>
</div>



