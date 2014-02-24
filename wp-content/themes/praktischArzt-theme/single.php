<?php
get_header();
if (have_posts()) {
    global $post;
    the_post();
    $date = get_the_date('d S M Y');
    $date_arr = explode(' ', $date);

    $cat = wp_get_post_categories($post->ID);
    if (isset($cat[0]))
        $cat = get_category($cat[0]);
    ?>
    <div class="row-fluid" id="body_container">
        <div class="clearfix content-block" id="wrapper"> 

            <div class="heading"> 
                <h1 class="title job-title" id="job_title"><?php _e("OUR BLOG", ET_DOMAIN); ?></h1> 
            </div> 
            <div class="row-fluid">  
                <div class="span9">  
                    <div class="content single-entry">
                        <div class="header">Kategorien:
                            <?php if (isset($cat->name)) { ?>
                                <a href="<?php echo get_category_link($cat) ?>"> <?php echo $cat->name ?> </a> 
                            <?php } ?>
                        </div>

                        <h2 class="title">  <a href="<?php the_permalink() ?>" title="<?php the_title() ?>" ><?php the_title() ?></a> </h2>

                        <div class="description tinymce-style">
                            <?php the_content('') ?>
                        </div>
                    </div>
                    <hr>
                    <div class="comments">
                        <h3 class="title"><?php comments_number('bisher kein Kommentar zu diesem Artikel', '1 Kommentar zu diesem Artikel', '% Kommentare zu diesem Artikel'); ?> </h3>
                        <?php comments_template('', true) ?>
                    </div>     
                </div>
                <div class="span3">
                    <div class="textright">
                        <div class="author">  geschrieben von: <?php the_author() ?>  </div>
                        <div class="join-date">am <?php the_date(); ?></div>
                        <?php et_follow_us() ?>
                    </div> 
                </div> 

                <?php if (is_active_sidebar('sidebar-blog')) { ?>
                    <div id="sidebar-blog" class="second-column f-right widget-area <?php if (current_user_can('manage_options')) echo 'sortable' ?>">
                        <?php dynamic_sidebar('sidebar-blog'); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php
}
get_footer();