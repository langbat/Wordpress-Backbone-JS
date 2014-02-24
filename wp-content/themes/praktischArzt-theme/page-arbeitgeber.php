<?php
get_header();
?>
<?php
if (have_posts()) {
    the_post();
    ?>
    <div class="row-fluid" id="body_container">

        <div class="clearfix content-block" id="wrapper">
            <div class="heading">
                <div class="main-center">
                    <h1 class="title job-title" id="job_title"><?php the_title() ?></h1>
                </div>
            </div>
            <div class="main-center">

                <?php if (function_exists('display_quickinfo')) display_quickinfo(); ?>

                <div class="row-fluid">
                    <div class="tinymce-style">
                        <?php the_content() ?>
                    </div>
                </div>

                <?php
                /*
                  $widgetNL = new WYSIJA_NL_Widget(true);

                  echo '<div id="newsletter_subscription">' . $widgetNL->widget(array('form' => 1, 'form_type' => 'php')) .
                  '<span class="newsletter_desc">Hier können Sie sich für unseren Newsletter eintragen </span>' .
                  '</div>';
                 */
                ?>


                <!-- box modul -->
                <?php tripple_boxes_output(); ?>


                <div class="bottom-text row-fluid">
                    <div class="row-fluid">
                        <div class="span3 newsletter-btn arbeitgeber-footerBtn"> 
                            <a href="<?php echo et_get_page_link('post-a-job') ?> " class="btn-header border-radius current_page_item" title="Stelle schalten">Stelle schalten </a>
                        </div>
                        <div class="span9" style="margin-left: 5px;">
                            <span class="newsletter-text"><a href="<?php echo et_get_page_link('post-a-job') ?>/">  <B>Im Login Bereich für Arbeitgeber finden Sie alle für Sie als Ansprechpartner der Klinik oder Praxis relevanten Informationen.</B> </a></span>
                        </div>

                    </div>

                    <div class="row-fluid">
                        <p align=center><BR /> Sollten Sie Fragen oder Anregungen haben, kontaktieren Sie uns bitte über <a href="mailto:kontakt@praktischarzt.de">kontakt@praktischArzt.de</a></p>
                    </div>
                </div>

            </div><!-- main-center -->
        </div>
    </div>

    <?php
}

get_footer();