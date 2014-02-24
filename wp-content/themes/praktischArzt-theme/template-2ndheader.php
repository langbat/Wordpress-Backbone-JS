<?php
global $post, $job, $current_user;

$marginTop = '26px';
$dash = get_page(6);
if (!is_tax() && !is_search() && et_is_logged_in())
    $marginTop = '0'
    ?>

<?php #if( !is_home() && !is_tax() && !is_post_type_archive('job') &&  !is_search()  && et_is_logged_in() ) {  ?>
<div class="clear blue-divider" style="margin-top: <?php echo $marginTop; ?>"></div>  	
<?php
if (!is_tax() && !is_search() && et_is_logged_in()) {
    $marginTop = '0'
    ?>
    <div class="header-second">
        <div class="main-center breadcrumb">
            <?php
            if (!is_page())
                echo et_breadcrumbs(array('showCurrent' => false, 'home' => __('Home', ET_DOMAIN)));
            else
                echo et_breadcrumbs(array('showCurrent' => true, 'home' => __('Home', ET_DOMAIN)));
            ?> 

            | <a href="<?php echo get_permalink($dash); ?>"> Zu Ihrer Stellenverwaltung </a>  
            | <span class="sie-sind"> Sie sind eingeloggt als: <b> <?php echo $current_user->display_name; ?></b></span> 

            <span class="logout f-right"> 
                <a href="<?php echo wp_logout_url(home_url()) ?>" title="ausloggen"> ausloggen <span class="icon" data-icon="Q"></span></a>
            </span>
        </div>
    </div>
<?php } ?> 	





<?php /*
  <div class="header-second">
  <div class="main-center breadcrumb">
  <?php
  if(!is_page () )
  echo et_breadcrumbs( array ('showCurrent' => false, 'home'			=>	__('Home', ET_DOMAIN)));
  else echo et_breadcrumbs(array ('showCurrent' => true, 'home'			=>	__('Home', ET_DOMAIN)));

  $dash = get_page_by_title( 'dashboard' );?>
  <?php if ( et_is_logged_in() ){ ?> | <a href="<?php echo get_permalink($dash); ?>"> Zu Ihrer Stellenverwaltung </a>  |

  <span> Sie sind eingeloggt als: <b> <?php echo $current_user->display_name; ?></b></span>

  <span class="logout f-right">
  <a href="<?php echo wp_logout_url( home_url() ) ?>" title="ausloggen"> ausloggen <span class="icon" data-icon="Q"></span></a>
  </span>
  <?php } ?>

  </div>
  </div>
 */ ?>		