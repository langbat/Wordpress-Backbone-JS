<?php
get_header();
?>
<?php
if (have_posts()) {
    the_post();
    ?>
    <div class="row-fluid" id="body_container">
        <div class="content-block" id="wrapper">
            <div class="heading"> 
                <h1 class="title" ><?php the_title() ?></h1> 
            </div>


            <div class="row-fluid">

                <div class="entry-blog">
                    <?php the_content() ?>
                </div>
                <!-- box modul -->
                <?php if (function_exists('tripple_boxes_output')) tripple_boxes_output(); ?>

            </div> 
        </div>
    </div>
    <?php
}

get_footer();