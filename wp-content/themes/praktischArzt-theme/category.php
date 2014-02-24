<?php
global $wp_query;

get_header();
?>
<div class="row-fluid">
    <div class="learfix content-block" id="wrapper">
        <div class="heading">
            <div class="main-center">
                <h1 class="title job-title" id="job_title"><?php _e("OUR BLOG", ET_DOMAIN); ?></h1>
            </div>
        </div>
        <div class="main-center" >

            <div class="main-column"><!-- id="entry-list"-->
                <div class="row-fluid">
                    <div class="span9">
                        <ul class="entry-blog" >
                            <?php
                            if (have_posts()) {
                                while (have_posts()) {
                                    global $post;

                                    the_post();
                                    $date = get_the_date('d S M Y');
                                    $date_arr = explode(' ', $date);

                                    $cat = wp_get_post_categories($post->ID);

                                    $cat = get_category($cat[0]);
                                    ?>
                                    <li>
                                        <div class="thumbnail f-right">

                                            <div class="author">
                                                <?php the_author() ?>					
                                            </div>
                                            <div class="join-date"><?php echo $date_arr[2] ?> <?php echo $date_arr[0] ?><sup><?php echo strtoupper($date_arr[1]) ?></sup>, <?php echo $date_arr[3] ?></div>
                                        </div>


                                        <div class=" ">
                                            <div class="header">
                                                <a href="<?php echo get_category_link($cat) ?>">
                                                    <?php echo $cat->name ?>
                                                </a> 
                                                <a href="<?php the_permalink() ?>" class="comment">
                                                    <span class="icon" data-icon="q"></span>
                                                    <?php comments_number('0', '1', '%') ?>
                                                </a>
                                            </div>
                                            <h2 class="title">
                                                <a href="<?php the_permalink() ?>" title="<?php the_title() ?>" ><?php the_title() ?></a>
                                            </h2>
                                            <div class="description">

                                                <?php the_excerpt() ?>

                                            </div>
                                            <div class="footer">
                                                <a href="<?php the_permalink() ?>" title="<?php printf(__("View post %s", ET_DOMAIN), get_the_title()) ?>">
                                                    <?php _e("READ MORE", ET_DOMAIN); ?> <span class="icon" data-icon="]"></span>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php if ($wp_query->max_num_pages > 1) { ?>
                                <div class="button-more">
                                    <button class="btn-background border-radius" id="load-more-post"><?php _e("Load More Articles", ET_DOMAIN); ?></button>
                                    <input type="hidden" name="template" id="template" value="<?php echo $wp_query->query_vars['cat'] ?>"/>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <div class="span3">
                        <div class="second-column   f-right">
                            <ul><?php
                                $args = array(
                                    'style' => 'list',
                                    'title_li' => 'Alle Kategorien'
                                );

                                wp_list_categories();
                                ?>
                            </ul>
                        </div>
                    </div>
                </div> 
            </div> 

            <?php if (is_active_sidebar('sidebar-blog')) { ?>
                <div id="sidebar-blog" class="second-column widget-area <?php if (current_user_can('manage_options')) echo 'sortable' ?>">
                    <?php dynamic_sidebar('sidebar-blog'); ?>
                </div>
            <?php } ?>

        </div>
    </div>	
</div> 
<?php
get_footer();