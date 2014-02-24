<?php 

/**
 * Handle mobile here
 */
add_filter('template_include', 'et_template_mobile');
function et_template_mobile($template){
	global $user_ID, $wp_query, $wp_rewrite;
	$new_template = $template;

	// no need to redirect when in admin
	if ( is_admin() ) return $template;

	/***
	  * Detect mobile and redirect to the correlative layout file
	  */ 
	global $isMobile;
	$detector = new ET_MobileDetect();
	$isMobile = apply_filters( 'et_is_mobile', ($detector->isMobile()) ? true : false );

	if ( $isMobile  ){
		$filename 		= basename($template);
		
		$child_path		= get_stylesheet_directory() . '/mobile' . '/' . $filename;
		$parent_path 	= get_template_directory() . '/mobile' . '/' . $filename;
		
		if ( file_exists($child_path) ){
			$new_template = $child_path;
		} else if ( file_exists( $parent_path )){
			$new_template = $parent_path;
		} else {
			$new_template = get_template_directory() . '/mobile/unsupported.php';
		}

		// some special page which are existed in main template
		if(!in_array($filename, array('header-mobile.php' , 'footer-mobile.php')) ) {
			if (is_page_template('page-login.php')){
				$new_template = get_template_directory() . '/mobile/page-login.php';
			} else if (is_page_template('page-register.php')){
				$new_template = get_template_directory() . '/mobile/page-register.php';
			}
		}
	}

	return $new_template;
}

// add_filter('post_type_archive_title', 'et_custom_title');
// function et_custom_title($title){
// 	if ( is_archive('resume') )
// 		$title = __('Resumes', ET_DOMAIN);
// 	return $title;
// }

/**
 * Mobile template for job items
 */
function et_template_mobile_job(){
	$variables = array();
	$template = <<<TEMPLATE
	<li data-icon="false" class="list-item">
		<span class="arrow-right"></span>
		<a href="<%= permalink %>" data-transition="slide">
			<p class="name">
				<%= title %>
			</p>
			<p class="list-function">
				<span class="postions"><%= author %></span>
				<% if ( job_types.length > 0 ) { %>
					<span class="type-job color-<% if (typeof job_types[0].color != 'undefined') { %><%=job_types[0].color%> <% } %>">
						<span class="flags flag<% if (typeof job_types[0].color != 'undefined') { %><%=job_types[0].color%> <% } %>"></span>
						<% _.each(job_types, function(type) { %>
							<%= type.name %>
						<% }); %>
					</span>
				<% } %>
				<% if ( location != '' ) { %>
					<span class="locations"><span class="icon" data-icon="@"> </span><%= location %></span>
				<% } %>
			</p>
		</a>
		<div class="mblDomButtonGrayArrow arrow">
			<div></div>
		</div>
	</li>
TEMPLATE;

	$template = apply_filters('et_mobile_job_template', $template);
	return $template;
}


/**
 * Mobile template for resume items
 */
function et_template_mobile_resume(){
	$variables = array();
	$template = <<<TEMPLATE
	<li class="resume-item" data-icon="false" class="clearfix"><span class="arrow-right"></span>
	<a href="<%= permalink %>" data-transition="slide">
		<span class="thumb-img">
			<img src="<%= jobseeker_data.et_avatar.thumbnail[0] %>">
		</span>
		<span class="intro-text">
		<span class="fix-middle">
			<h1><%= post_title %></h1>
			<p class="positions"><%= et_profession_title %></p>
			<p class="locations"><span class="icon-locations"></span><%= et_location %></p>
		</span>
		</span>
	</a>
</li>
TEMPLATE;

	$template = apply_filters('et_mobile_job_template', $template);
	return $template;
}

/**
 * 
 */
function et_mobile_resume_taxo_values($cat, $key = 'name'){
	return $cat->$key;
}


add_filter ('option_page_on_front', 'filter_on_front_page') ;
function filter_on_front_page ($page_on_front) {
	global $isMobile;
	$detector = new ET_MobileDetect();
	$isMobile = apply_filters( 'et_is_mobile', ($detector->isMobile()) ? true : false );

	if ( $isMobile && $page_on_front ){ 
		return '';
	} 
	return $page_on_front;
}
?>