<?php get_header(); ?>

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
                <div class="row-fluid">

                    <?php if (function_exists('display_quickinfo')) display_quickinfo(); ?> 

                    <?php the_content() ?>

                </div>  

                <!-- box modul -->
                <?php if (function_exists('tripple_boxes_output')) tripple_boxes_output(); ?>
                <div class="bottom-text row-fluid">
                    <div style="padding-left: 12px;" class="row-fluid">

                        <div class="span3 newsletter-btn"><a href="<?php bloginfo('url') ?>/?page_id=527/">Newsletter</a></div>
                        <?php // change ?page_id=527 to newsletter    ?>
                        <div class="span9">
                            <span class="newsletter-text"><a href="<?php bloginfo('url') ?>/?page_id=527/">  <B>Melde Dich für unseren Newsletter an und erhalte regelmäßig interessante Stellen, Informationen und lesenswerte Blogartikel direkt in Dein Postfach!</B>       </a></span>
                        </div>

                    </div>
                    <div class="row-fluid">
                        <p align=center><BR />Solltest Du Fragen oder Anregungen haben, kontaktiere uns bitte über <a href="mailto:kontakt@praktischarzt.de">kontakt@praktischArzt.de</a></p>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php
}

get_footer();
?>