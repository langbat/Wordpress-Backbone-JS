<!DOCTYPE html>
<html>
<head>
	<title><?php echo bloginfo('name'). __(' - Mobile version',ET_DOMAIN); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<?php
		global $et_global , $current_user;

		// mobile icon for Apple devices
		$general_opts	= new ET_GeneralOptions();
		$mobile_icon	= $general_opts->get_mobile_icon();
		if ($mobile_icon){ ?>
			<link rel="apple-touch-icon" href="<?php echo $mobile_icon[0];?>"/>
		<?php 
		}
		else{ ?>
			<!-- Standard iPhone --> 
			<link rel="apple-touch-icon" sizes="57x57" href="<?php echo $et_global['imgUrl'];?>apple-touch-icon-57x57.png" />
			<!-- Retina iPhone --> 
			<link rel="apple-touch-icon" sizes="114x114" href="<?php echo $et_global['imgUrl'];?>apple-touch-icon-114x114.png" />
			<!-- Standard iPad --> 
			<link rel="apple-touch-icon" sizes="72x72" href="<?php echo $et_global['imgUrl'];?>apple-touch-icon-72x72.png" />
			<!-- Retina iPad --> 
			<link rel="apple-touch-icon" sizes="144x144" href="<?php echo $et_global['imgUrl'];?>apple-touch-icon-144x144.png" />
		<?php
		}
	?>
	
	<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/reset.css">
	<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/themes/engine-themes.min.css">
	<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/jquery.mobile.structure-1.3.1.min.css">
	<link rel="stylesheet" href="<?php bloginfo('template_url')?>/css/fonts/font-face.css">
	<!-- <link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/style.css"> -->
	<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/jquery.mobile-1.3.1.min.css" />
	<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/jquery.style.css">
	<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/custom.css">
	<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/job-label.css">

	<script type="text/javascript">
		var et_globals = {
			"ajaxURL"    	: "<?php echo admin_url('admin-ajax.php');?>",
			"homeURL"    	: "<?php echo home_url();?>",
			"imgURL"    	: "<?php echo TEMPLATEURL . '/img';?>",
			"jsURL"     	: "<?php echo TEMPLATEURL . '/js';?>",
			"dashboardURL"  : "<?php echo et_get_page_link('dashboard');?>",
			"logoutURL"    	: "<?php echo wp_logout_url( home_url() );?>",
			"routerRootCompanies" : "<?php echo et_get_page_link('companies');?>"
		};
	</script>
	<script src="<?php bloginfo('template_url')?>/mobile/js/jquery-1.9.1.min.js"></script>
	<script src="<?php bloginfo('template_url')?>/mobile/js/jquery.mobile-1.3.1.min.js"></script>
	<script type="text/javascript" src="<?php echo FRAMEWORK_URL . '/js/lib/underscore-min.js'?>"></script>
	<script type="text/javascript" src="<?php bloginfo('template_url')?>/mobile/js/script.js"></script>
	<script type="text/javascript" src="<?php bloginfo('template_url')?>/mobile/js/mobile_script.js"></script>
	
	<script type="text/javascript" src="<?php bloginfo('template_url')?>/resumes/js/mobile.js"></script>
	<script type="text/javascript" src="https://simplemodal.googlecode.com/files/jquery.simplemodal-1.4.4.js"></script>
	<!-- GOOGLE ANALYTICS -->
	<?php echo $general_opts->get_google_analytics(); ?>
	<!-- GOOGLE ANALYTICS -->	
	<?php do_action('et_mobile_head') ?>
</head>
<body class="body-mobile">
	<div data-role="page" id="main_page_load" <?php body_class( 'mobile page-resume ui-page-active' ) ?>>
	<div data-role="header" class="header-bar">
		<?php 
		if (!is_home() || !empty($_SERVER['QUERY_STRING']) ) {	?>
			<a href="#" data-rel="back" rel="external" data-ajax="false" class="ui-btn-s btn_back" data-role="none">
				<span class="arrow-left"></span><?php _e('Back',ET_DOMAIN); ?>
			</a>
		<?php	}	?>
		<h1><a href="<?php echo home_url(); ?>" rel="external" data-ajax="false" ><?php echo bloginfo('name');?></a></h1>
		<?php
			if ( !is_user_logged_in() ) { 
				echo '<a href="'.et_get_page_link('login').'" class="ui-btn-s icon ui-btn-right" data-role="none" data-icon="y"></a>';
			}
			else{
				$role	=	$current_user->roles;
				$role	=	array_pop($role);
				if($role == 'company' || current_user_can( 'manage_options' )) {
					echo '<a href="'.et_get_page_link('dashboard').'" class="ui-btn-s icon ui-btn-right" data-role="none" data-icon="y"></a>';
				} else {
					echo '<a href="'.et_get_page_link('jobseeker-account').'" class="ui-btn-s icon ui-btn-right" data-role="none" data-icon="y"></a>';
				}
			}
		?>
	</div><!-- /header -->

		<?php if ( (is_home() || is_archive() ) && function_exists('et_is_resume_menu') ){ ?>
		<div data-role="navbar" class="job-navbar">
		    <ul>
		        <li>
		        	<a href="<?php echo home_url( ) ?>" class="font-quicksand  <?php if (is_home() || (is_post_type_archive('job')  )) echo ' ui-btn-active ui-state-persist' ?>">
		        	<?php _e('JOBS', ET_DOMAIN) ?>
			        </a>
			    </li>
		        <li>
		        	<a href="<?php echo get_post_type_archive_link( 'resume' ) ?>" class="font-quicksand <?php if (is_post_type_archive('resume') ) echo ' ui-btn-active ui-state-persist' ?>">
		        		<?php _e('RESUMES', ET_DOMAIN) ?>
		        	</a>
		        </li>
		    </ul>
	    </div><!-- /navbar --> 
	    <?php } ?>
