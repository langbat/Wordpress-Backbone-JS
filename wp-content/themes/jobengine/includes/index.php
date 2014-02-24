<?php

require_once dirname(__FILE__) . '/core/bootstrap.php';
require_once dirname(__FILE__) . '/options/class-ops-general.php';
require_once dirname(__FILE__) . '/options/general.php';
require_once dirname(__FILE__) . '/payment/payment-visitor.php';
require_once dirname(__FILE__) . '/makePOT/makepot.php';

function et_filter_authentication_placeholder($content, $user_id) {
    $user = new WP_User($user_id);

    $content = str_ireplace('[user_login]', $user->user_login, $content);
    $content = str_ireplace('[user_name]', $user->user_login, $content);
    $content = str_ireplace('[user_nicename]', ucfirst($user->user_nicename), $content);
    $content = str_ireplace('[user_email]', $user->user_email, $content);
    $content = str_ireplace('[display_name]', ucfirst($user->display_name), $content);
    $content = str_ireplace('[company]', ucfirst($user->display_name), $content);
    $content = str_ireplace('[dashboard]', et_get_page_link('dashboard'), $content);

    return $content;
}

function et_filter_job_placeholder($content, $job_id) {

    $job = get_post($job_id);
    $terms = wp_get_post_terms($job_id, 'job_type');

    $kinilik = get_post_meta($job_id, 'cfield-984');
    
    
    $content = str_ireplace('[job_type]', $terms[0]->name, $content);

    $content = str_ireplace('[job_title]', $job->post_title, $content);
    
    $content = str_ireplace('[job_excerpt]', $job->post_excerpt, $content);
    $content = str_ireplace('[job_link]', get_permalink($job_id), $content);
    $content = str_ireplace('[dashboard]', et_get_page_link('dashboard'), $content);
    $content = apply_filters('et_filter_job_email', $content, $job_id);


    return $content;
}

/*
 * filter breadcrum add post type job
 */
add_filter('et_breadcrumbs', 'et_job_breadcrums', 10, 2);

function et_job_breadcrums($breadcrumb, $arg) {
    global $post;

    extract($arg);
    if (is_single() && get_post_type() == 'job') {

        $job_cat = et_get_the_job_category($post->ID);
        if (!empty($job_cat)) {

            $job_cat = $job_cat[0];
            $breadcrumb .= et_get_job_category_parents($job_cat, TRUE, ' ' . $delimiter . ' ');
        }

        if ($showCurrent == 1)
            $breadcrumb .= $before . get_the_title() . $after;
    }

    if (is_attachment()) {
        $parent = get_post($post->post_parent);
        if (get_post_type($parent) == "job") {
            $job_cat = et_get_the_job_category($parent->ID);

            if (!empty($job_cat)) {
                $job_cat = $job_cat[0];
                $breadcrumb .= et_get_job_category_parents($job_cat, TRUE, ' ' . $delimiter . ' ');
            }
            $breadcrumb .= $before . get_the_title($parent->ID) . $after;
        }
        if ($showCurrent == 1)
            $breadcrumb .= $before . get_the_title() . $after;
    }

    if (is_author()) {
        $homeLink = get_bloginfo('url');
        $breadcrumb = '';
        $breadcrumb .= '<a class="home" href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';
        global $author;
        $userdata = get_userdata($author);
        $breadcrumb .= $before . sprintf(__('Jobs posted by  %s ', ET_DOMAIN), $userdata->display_name) . $after;
    }

    if (!$showCurrent)
        $breadcrumb = trim($breadcrumb, ' ' . $delimiter . ' ');

    return $breadcrumb;
}

// filter job how to apply content
add_filter('et_job_apply_content', 'et_filter_job_content');
// filter job content
add_filter('et_job_content', 'et_filter_job_content');

function et_filter_job_content($content) {

    //$content	=	str_replace('</ul>', '</ol>', str_replace('<ul>', '<ol>', $content));
    $content = str_replace(array('</h1>', '</h2>', '</h3>', '</h4>', '</h5>'), '</h6>', str_replace(array('<h1>', '<h2>', '<h3>', '<h4>', '<h5>'), '<h6>', $content));

    $pattern = "/<[^\/>]*>(&nbsp;)*([\s]?)*<\/[^>]*>/";  //use this pattern to remove any empty tag '<a target="_blank" rel="nofollow" href="$1">$3</a>'

    $content = preg_replace($pattern, '', $content);


    $link_pattern = "/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/";

    //$content	  =  preg_replace_callback($link_pattern, 'et_preg_replace_callback' , $content);

    $content = str_replace('<a', '<a target="_blank" rel="nofollow"', $content);

    //$content	=	preg_replace("/([http|https]:\/\/)?([a-zA-Z0-9\-.]+\.[a-zA-Z0-9\-]+([\/]([a-zA-Z0-9_\/\-.?&%=+])*)*)/", '<a rel="nofollow" target="__blank" href="http://$2">$2</a>', $content);  

    $content = strip_tags($content, '<p><a><ul><ol><li><h6><span><b><em><strong><br>');

    return $content;
}

/**
 * 	A callback that will be called and passed an array of matched elements in the subject string. The callback should return the replacement string.

 * 	You'll often need the callback function for a preg_replace_callback() in just one place. In this case you can use an anonymous function (since PHP 5.3.0) 
  or create_function() to declare an anonymous function as callback within the call to preg_replace_callback().
  By doing it this way you have all information for the call in one place and do not clutter the function namespace with a callback function's name not used anywhere else.
 */
function et_preg_replace_callback($match) {
    $match = $match[0];
    preg_match("/target=([^\>]*)\"/", $match, $mat);

    $match = preg_replace("/target=([^\>]*)\"/", '', $match);
    $match = preg_replace("/rel=([^\>]*)\"/", '', $match);

    $match = str_replace('<a', '<a target="_blank" rel="nofollow"', $match);

    return $match;
}

add_action('et_insert_job', 'et_update_admin_new_feeds');
add_action('et_update_job', 'et_update_admin_new_feeds');

function et_update_admin_new_feeds($job_id) {
    global $user_ID;
    $users = get_users(array('role' => 'administrator'));

    foreach ($users as $user) {
        if ($user->ID == $user_ID)
            break;

        $feeds = et_get_user_new_feeds($user->ID);
        if (!in_array($job_id, $feeds)) {
            $feeds[] = $job_id;
        }
        et_update_user_new_feeds($user->ID, $feeds);
    }
}

/**
 * Retrieve term parents with separator.
 *
 * @param int $id term ID.
 * @param string $taxonomy Taxonomy.
 * @param bool $link Optional, default is false. Whether to format with link.
 * @param string $separator Optional, default is '/'. How to separate terms.
 * @param bool $nicename Optional, default is false. Whether to use nice name for display.
 * @param array $visited Optional. Already linked to terms to prevent duplicates.
 * @return string
 */
function et_get_term_parents($id, $taxonomy = 'category', $link = false, $separator = '/', $nicename = false, $visited = array()) {
    $chain = '';

    $parent = &get_term($id, $taxonomy);

    if (is_wp_error($parent))
        return $parent;

    if ($nicename)
        $name = $parent->slug;
    else
        $name = $parent->name;

    if ($parent->parent && ( $parent->parent != $parent->term_id ) && !in_array($parent->parent, $visited)) {
        $visited[] = $parent->parent;
        $chain .= et_get_term_parent($parent->parent, $taxonomy, $link, $separator, $nicename, $visited);
    }

    if ($link)
        $chain .= '<a href="' . esc_url(get_term_link($parent, $taxonomy)) . '" title="' . esc_attr(sprintf(__("View all posts in %s", ET_DOMAIN), $parent->name)) . '">' . $name . '</a>' . $separator;
    else
        $chain .= $name . $separator;
    return $chain;
}

/*
 * filter nav menu object to add class current item to menu
 */
add_filter('wp_nav_menu_objects', 'et_filter_nav_menu_objects');

function et_filter_nav_menu_objects($sort_items) {

    if (is_singular('post')) {
        global $post;
        foreach ($sort_items as $value) {
            if ($value->object == 'post' && $value->object_id == $post->ID) {
                return $sort_items;
            }
        }
        foreach ($sort_items as $value) {
            if (in_array('current-post-ancestor', $value->classes)) {
                $value->classes[] = 'current-menu-item';
                return $sort_items;
            }
        }
        return $sort_items;
    }

    if (is_category()) {
        foreach ($sort_items as $value) {
            if (in_array('current-category-ancestor', $value->classes) && !in_array('current-category-parent', $value->classes)) {
                $value->classes[] = 'current-menu-item';
                return $sort_items;
            }
        }
    }
    return $sort_items;
}

/**
 * filter comment default fields trigger by hook comment default field call in initialize theme
 * @param array $fields
 * @author dakachi
 */
function et_comment_default_fields($fields) {

    $fields['author'] = '<div class="form-item ">
							<label>' . __("Your Name", ET_DOMAIN) . '</label>
							<div class="input290">
								<input type="text" id="author" name="author" value=""  class="text" title="Your Name"/>
								<span class="require">*</span>
							</div>
						</div>';
    $fields['email'] = '<div class="form-item ">
							<label>' . __("Your Email", ET_DOMAIN) . '</label>
							<div class="input290">
								<input type="text" id="email" name="email" value="" class="text" title="Your Email"/>
								<span class="require">*</span>
							</div>
						</div>';
    $fields['url'] = '<div class="form-item">
							<label>' . __("Your Website", ET_DOMAIN) . '</label>
							<div class="input">
								<input type="text" name="url" value="" class="text" title="Your Website"/>
							</div>
						</div>';

    return $fields;
}

add_filter('comment_form_default_fields', 'et_comment_default_fields');

/*
 * load more post action 
 */
add_action('wp_ajax_et-load-more-post', 'et_load_more_post');
add_action('wp_ajax_nopriv_et-load-more-post', 'et_load_more_post');

function et_load_more_post() {

    header('HTTP/1.0 200 OK');
    header('Content-type: application/json');

    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $template = isset($_POST['template']) ? $_POST['template'] : 'category';

    if ($template == 'date') {
        $query = new WP_Query($_POST['template_value'] . '&post_status=publish&paged=' . $page);
    } else {
        $term = get_term_children($_POST['template_value'], 'category');
        $term[] = $_POST['template_value'];
        $term = implode($term, ',');
        $args = array(
            'post_status' => 'publish',
            'post_type' => 'post',
            'paged' => $page,
            'cat' => $term
        );
        $query = new WP_Query($args);
    }

    $data = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {

            $query->the_post();
            global $post;
            $date = get_the_date('d S M Y');
            $date_arr = explode(' ', $date);

            $cat = wp_get_post_categories($post->ID);

            $cat = get_category($cat[0]);

            $data .= '
				<li>
					<div class="thumbnail font-quicksand">
					<div class="img-thumb">
						<a href="' . get_author_posts_url($post->post_author) . '">
							' . get_avatar($post->post_author) . '
						</a>
					</div>
					<div class="author">
						<a  title="' . sprintf(__("View all posts by %s ", ET_DOMAIN), get_the_author()) . '" 
							href="' . get_author_posts_url($post->post_author) . '">
							' . get_the_author() . '
						</a>
						</div>
					<div class="join-date">' . $date_arr[2] . ' ' . $date_arr[0] . '<sup>' . strtoupper($date_arr[1]) . '</sup>, ' . $date_arr[3] . '</div>
				</div>
        		<div class="content">
	          		<div class="header font-quicksand">
	           			<a href="' . get_category_link($cat) . '">
							' . $cat->name . '
	           			</a> 
	           			<a href="' . get_permalink() . '" class="comment">
	           				<span class="icon" data-icon="q"> ' . get_comments_number() . ' </span>
	           			</a>
	          		</div>
          			<h2 class="title">
           	 			<a href="' . get_permalink() . '" title="' . get_the_title() . '" >' . get_the_title() . '</a>
          			</h2>
          			<div class="description">
          				
							' . get_the_excerpt() . '
						
          			</div>
          			<div class="footer font-quicksand">
                		<a href="' . get_permalink() . '" title="' . sprintf(__("View post %s", ET_DOMAIN), get_the_title()) . '">
		      	          	' . __("READ MORE", ET_DOMAIN) . ' <span class="icon" data-icon="]"></span>
		      	        </a>
             		</div>
        		</div>
				</li>';
        }
        echo json_encode(array(
            'data' => $data,
            'success' => true,
            'msg' => '',
            'total' => $query->max_num_pages
        ));
    } else {
        echo json_encode(array(
            'data' => $data,
            'success' => false,
            'msg' => __('There is no posts yet.', ET_DOMAIN)
        ));
    }
    exit;
}

add_action('wp_ajax_et-save-job-static-text', 'et_save_job_static_text');

function et_save_job_static_text() {

    header('HTTP/1.0 200 OK');
    header('Content-type: application/json');

    $sidebar = trim($_POST['sidebar']);
    $id = trim($_POST['id']);
    $data = stripcslashes($_POST['html']);
    $data = str_ireplace('&lt;', '<', $data);
    $data = str_ireplace('&gt;', '>', $data);
    //$data		=	nl2br($data);

    $job_opt = new ET_JobOptions();
    $response = array(
        'success' => true,
        'msg' => __('Error!', ET_DOMAIN),
        'sidebar' => $sidebar,
        'id' => $id
    );
    switch ($sidebar) {
        case 'post-job-sidebar':

            $widget = $job_opt->get_post_job_sidebar();

            if (isset($widget[$id])) {
                $widget[$id] = $data;
            } else {
                $id = $job_opt->generate_post_job_widget_id();
                $widget[$id] = $data;
            };
            $response['id'] = $id;
            $response['msg'] = $data;

            $job_opt->set_post_job_sidebar($widget);

            break;
        case 'user-dashboard-sidebar':
            $widget = $job_opt->get_dashboard_sidebar();

            if (isset($widget[$id])) {
                $widget[$id] = $data;
            } else {
                $id = $job_opt->generate_dashboard_widget_id();
                $widget[$id] = $data;
            };

            $response['id'] = $id;
            $response['msg'] = $data;
            $job_opt->set_dashboard_sidebar($widget);
            break;
        case 'upgrade-account-sidebar':
            $widget = $job_opt->get_upgrade_account_sidebar();

            if (isset($widget[$id])) {
                $widget[$id] = $data;
            } else {
                $id = $job_opt->generate_dashboard_widget_id();
                $widget[$id] = $data;
            };

            $response['id'] = $id;
            $response['msg'] = $data;
            $job_opt->set_upgrade_account_sidebar($widget);
            break;

        default:
            $response['success'] = false;
            break;
    }
    echo json_encode($response);
    exit;
}

add_action('wp_ajax_et-remove-job-static-text', 'et_remove_job_static_text');

function et_remove_job_static_text() {

    header('HTTP/1.0 200 OK');
    header('Content-type: application/json');

    $sidebar = trim($_POST['sidebar']);
    $id = trim($_POST['id']);

    $response = array(
        'success' => true,
        'msg' => __('Error!', ET_DOMAIN),
        'sidebar' => $sidebar,
        'id' => $id
    );

    $job_opt = new ET_JobOptions();

    switch ($sidebar) {
        case 'post-job-sidebar':

            $widget = $job_opt->get_post_job_sidebar();

            if (isset($widget[$id])) {
                unset($widget[$id]);
            } else {
                $response['success'] = false;
            }

            $job_opt->set_post_job_sidebar($widget);

            break;
        case 'user-dashboard-sidebar':
            $widget = $job_opt->get_dashboard_sidebar();

            if (isset($widget[$id])) {
                unset($widget[$id]);
            } else {
                $response['success'] = false;
            }

            $job_opt->set_dashboard_sidebar($widget);
            break;
        case 'upgrade-account-sidebar':
            $widget = $job_opt->get_upgrade_account_sidebar();

            if (isset($widget[$id])) {
                unset($widget[$id]);
            } else {
                $response['success'] = false;
            }

            $job_opt->set_upgrade_account_sidebar($widget);
            break;

        default:
            $response['success'] = false;
            break;
    }

    echo json_encode($response);

    exit;
}

//////////////////////////////////////////////////////
/* 		authentication mail filter				 */
//////////////////////////////////////////////////////

add_action('et_after_register', 'et_user_register_mail', 10, 2);

function et_user_register_mail($user_id, $role) {
    if ($role != 'company')
        return;
    $user = new WP_User($user_id);

    $user_email = $user->user_email;
    $mail_opt = new ET_JobEngineMailTemplate();
    $register_mail = $mail_opt->get_register_mail();

    $register_mail = et_filter_authentication_placeholder($register_mail, $user_id);
    $subject = sprintf(__("Sie haben sich erfolgreich bei %s registriert.", ET_DOMAIN), get_option('blogdescription'));
    $headers = '';

    $register_mail = et_get_mail_header() . $register_mail . et_get_mail_footer();

    wp_mail($user_email, $subject, $register_mail);
    
    $admin_email = get_option('admin_email');
    wp_mail($admin_email, $subject, $register_mail);
}

add_filter('et_retrieve_password_message', 'et_filter_retrieve_password_message', 10, 3);

function et_filter_retrieve_password_message($message, $active_key, $user_data) {

    $user_login = $user_data->user_login;
    $mail_opt = new ET_JobEngineMailTemplate();
    $forgot_message = $mail_opt->get_forgot_pass_mail();

    $activate_url = apply_filters('et_reset_password_link', network_site_url("wp-login.php?action=rp&key=$active_key&login=" . rawurlencode($user_login), 'login'), $active_key, $user_login);

    $forgot_message = et_filter_authentication_placeholder($forgot_message, $user_data->ID);
    $forgot_message = str_ireplace('[activate_url]', $activate_url, $forgot_message);

    return $forgot_message;
}

add_action('et_password_reset', 'et_password_reset_mail', 10, 2);

function et_password_reset_mail($user, $new_pass) {
    $mail_opt = new ET_JobEngineMailTemplate();
    $new_pass_msg = $mail_opt->get_reset_pass_mail();

    $new_pass_msg = et_filter_authentication_placeholder($new_pass_msg, $user->ID);
    //$new_pass_msg 	=	str_ireplace('[new_pass]', $new_pass, $new_pass_msg);
    //$new_pass_msg 	=	str_ireplace('[user_login]', $user->user_login, $new_pass_msg);

    $subject = apply_filters('et_reset_pass_mail_subject', __('Password updated successfully!', ET_DOMAIN));

    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= "From: " . get_option('blogdescription') . " < " . get_option('admin_email') . "> \r\n";

    $new_pass_msg = et_get_mail_header() . $new_pass_msg . et_get_mail_footer();
    wp_mail($user->user_email, $subject, $new_pass_msg, $headers);
}

/**
 * send apply email
 */
function je_application_mail($job, $application, $attachs, $apply_info = array()) {
    // verify if sending mail is allowed
    if (!et_get_auto_email('apply')) {
        $res = array(
            'success' => true,
            'msg' => __('<span><strong>Congratulations!</strong></span><br /><span class="msg">Your application has been sent. Good luck!</span>', ET_DOMAIN)
        );
    }

    // this array will hold the file paths of the attachments for wp_mail
    extract($apply_info);
    $attachments = array();
    // make this application the post_parent of all attachments
    foreach ($attachs as $att) {
        $att = et_update_post(array('ID' => $att, 'post_parent' => $application));
        if ($att) {
            $attachments[] = get_attached_file($att);
        }
    }
    $job_id = $job->ID;
    // get commpany detail to send mail
    $company = et_create_companies_response($job->post_author);
    $company_email = et_get_post_field($job_id, 'apply_email');
    $company_email = ($company_email != '') ? $company_email : $company['apply_email'];
    $company_name = $company['display_name'];
    $blog_name = get_option('blogdescription');

    // application mail subject and content
    $subject = sprintf(__("Application for %s you posted on %s", ET_DOMAIN), $job->post_title, $blog_name);
    $message = $apply_note;
    $seeker = array('email' => $email, 'name' => $emp_name, 'job' => $job_id);
    // filter mail content and title
    $subject = apply_filters('et_job_apply_email_title', $subject, $job_id);
    $message = apply_filters('et_job_apply_email_content', $message, $seeker);

    // mail header
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= "From: " . $blog_name . " < " . get_option('admin_email') . "> \r\n";
    $employer_headers = $headers . " Reply-To: " . $email . "\r\n";

    $employee_subject = sprintf(__("Application for %s you sent through %s", ET_DOMAIN), $job->post_title, $blog_name);

    $employee_message = sprintf(__("<p>Dear %s,</p> <p>You have sent your application successfully for this job: %s. Here is the email which was sent to the employer.</p>", ET_DOMAIN), ucfirst($emp_name), $job->post_title);
    $employee_message = $employee_message . '<br/>' . $message;


    $message = et_get_mail_header() . $message . et_get_mail_footer();
    $employee_message = et_get_mail_header() . $employee_message . et_get_mail_footer();

    $admin_email = get_option('admin_email');

    // send mail, if successful, response to user
    if (wp_mail($company_email, $subject, $message, $employer_headers, $attachments) &&
            wp_mail($email, $employee_subject, $employee_message, $headers, $attachments)
    ) {
        wp_mail($admin_email, $subject, $message, $employer_headers, $attachments);

        $res = array(
            'success' => true,
            'msg' => __('<span><strong>Congratulations!</strong></span><br /><span class="msg">Your application has been sent. Good luck!</span>', ET_DOMAIN)
        );
        return $res;
    } else {
        return false;
    }
}

/**
 * send remind job mail to job seeker
 */
function je_remind_job_mail($email, $subject, $message, $headers = '') {
    // verify if sending mail is allowed
    if (!et_get_auto_email('remind'))
        return true;
    return wp_mail($email, $subject, $message, $headers);
}

function je_reject_job_mail($to, $subject, $message, $header) {
    // verify if sending mail is allowed
    if (!et_get_auto_email('reject'))
        return true;
    return wp_mail($to, $subject, $message, $header);
}

//////////////////////////////////////////////////////
/* 		job mail filter				 */
//////////////////////////////////////////////////////
add_action('et_change_job_status', 'et_change_job_status_mail', 10, 2);

function et_change_job_status_mail($job_id, $status) {
    $job_title = get_the_title($job_id);
    $mail_opt = new ET_JobEngineMailTemplate();
    // verify if sending mail is allowed
    if (!et_get_auto_email('approve') && $status == 'publish')
        return false;
    else if (!et_get_auto_email('archive') && $status == 'archive')
        return false;

    switch ($status) {
        case 'publish':
            $subject = apply_filters('et_publish_job_mail_title', sprintf(__('Your job %s posted in %s has been approved!', ET_DOMAIN), $job_title, get_option('blogdescription'))
            );
            $message = et_filter_job_placeholder($mail_opt->get_approve_mail(), $job_id);
            break;

        case 'archive' :
            $subject = apply_filters('et_archive_job_mail_title', sprintf(__('Your job %s posted in %s has been archived!', ET_DOMAIN), $job_title, get_option('blogdescription'))
            );
            $message = et_filter_job_placeholder($mail_opt->get_archive_mail(), $job_id);
            break;

        default:
            return false;
            break;
    }

    $job = get_post($job_id);

    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= "From: " . get_option('blogdescription') . " < " . get_option('admin_email') . "> \r\n";

    $message = et_filter_authentication_placeholder($message, $job->post_author);
    $message = et_get_mail_header() . $message . et_get_mail_footer();

    wp_mail(get_the_author_meta('email', $job->post_author), $subject, $message, $headers);

    //ngocanh: send also to admin if a job approved    
    if ($status == 'publish') {
        wp_mail(get_option('admin_email'), $subject, $message, $headers);
    }
}

/*
 * reject job mail body filter
 */
add_filter('et_reject_mail_body', 'et_filter_reject_mail_body', 10, 2);

function et_filter_reject_mail_body($reason, $job_id) {
    $job = get_post($job_id);
    $mail_opt = new ET_JobEngineMailTemplate();
    $message = $mail_opt->get_reject_mail();

    $message = et_filter_authentication_placeholder($message, $job->post_author);
    $message = et_filter_job_placeholder($message, $job_id);
    $message = str_ireplace('[reason]', $reason, $message);

    return $message;
    //wp_mail(get_the_author_meta('email', $job->post_author), $subject, $message, $headers);
}

add_filter('et_job_apply_email_content', 'et_filter_job_apply_email_content', 10, 2);

function et_filter_job_apply_email_content($note, $seeker) {
    $job_id = $seeker['job'];
    $job = get_post($job_id);
    $mail_opt = new ET_JobEngineMailTemplate();
    $message = $mail_opt->get_apply_mail();

    $message = et_filter_job_placeholder($message, $job_id);
    $message = et_filter_authentication_placeholder($message, $job->post_author);

    $message = str_ireplace('[seeker_note]', $note, $message);
    $message = str_ireplace('[seeker_name]', $seeker['name'], $message);
    $message = str_ireplace('[seeker_mail]', $seeker['email'], $message);

    return $message;
}

add_filter('et_share_job_message', 'et_filter_share_job_message', 10, 3);

function et_filter_share_job_message($remind_note, $job_id, $seeker) {
    $job = get_post($job_id);
    $mail_opt = new ET_JobEngineMailTemplate();
    $message = $mail_opt->get_remind_mail();

    $message = et_filter_job_placeholder($message, $job_id);
    $message = et_filter_authentication_placeholder($message, $job->post_author);

    $message = str_ireplace('[remind_note]', $remind_note, $message);
    $message = str_ireplace('[seeker_email]', $seeker, $message);

    return $message;
}

add_filter('wp_mail', 'et_filter_wp_mail');

function et_filter_wp_mail($compact) {

    if ($compact['headers'] == '') {
        $compact['headers'] = 'MIME-Version: 1.0' . "\r\n";
        $compact['headers'] .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $compact['headers'] .= "From: " . get_option('blogdescription') . " < " . get_option('admin_email') . "> \r\n";
    }



    $compact['message'] = str_ireplace('[site_url]', get_bloginfo('url'), $compact['message']);
    $compact['message'] = str_ireplace('[blogname]', get_bloginfo('name'), $compact['message']);
    $compact['message'] = str_ireplace('[admin_email]', get_option('admin_email'), $compact['message']);

    $compact['message'] = html_entity_decode($compact['message'], ENT_QUOTES, 'UTF-8');
    $compact['subject'] = html_entity_decode($compact['subject'], ENT_QUOTES, 'UTF-8');

    //$compact['message']		= 	et_get_mail_header().$compact['message'].et_get_mail_footer();

    return $compact;
}

function et_get_mail_header() {
    $opt = new ET_GeneralOptions ();
    $size = apply_filters('je_mail_logo_size', array(120, 50));
    $logo_url = $opt->get_website_logo($size);


    $customize = $opt->get_customization();

    $mail_header = '<html> 
					</head>
					<body style="font-family: Arial, sans-serif;font-size: 0.9em;margin: 0;	padding: 0;	color: #222222;">
					<div style="margin: 0px auto; width:600px; border: 1px solid ' . $customize['background'] . '">
						<table width="100%" cellspacing="0" cellpadding="0">
						<tr style="background: ' . $customize['header'] . '; height: 63px; vertical-align: middle;">
							<td style="padding: 10px 5px 10px 20px; width: 20%;">
								<img style="max-height: 40px" src="' . $logo_url[0] . '" alt="' . get_option('blogdescription') . '">
							</td>
							<td style="padding: 10px 20px 10px 5px">
								<span style="text-shadow: 0 0 1px #151515; color: #b0b0b0;">' . get_option('blogdescription') . '</span>
							</td>
						</tr>
						<tr><td colspan="2" style="height: 5px; background-color: ' . $customize['background'] . ';"></td></tr>
						<tr>
							<td colspan="2" style="background: #ffffff; color: #222222; line-height: 18px; padding: 10px 20px;">';
    return apply_filters('et_get_mail_header', $mail_header);
}

function et_get_mail_footer() {

    $info = apply_filters('et_mail_footer_contact_info', get_option('blogdescription') . ' <br>
					' . get_option('admin_email') . ' <br>'
    );
    $opt = new ET_GeneralOptions ();
    $customize = $opt->get_customization();

    $mail_footer = '</td>
					</tr>
					<tr>
						<td colspan="2" style="background: ' . $customize['background'] . '; padding: 10px 20px; color: #666;">
							<table width="100%" cellspacing="0" cellpadding="0">
								<tr>
									<td style="vertical-align: top; text-align: left; width: 50%;">' . $opt->get_copyright() . '</td>
									<td style="text-align: right; width: 50%;">' . $info . '</td>
								</tr>
							</table>
						</td>						
					</tr>
					</table>				
				</div>			
				</body>
				</html>';
    return apply_filters('et_get_mail_footer', $mail_footer);
}

add_filter('et_retrieve_password_message', 'je_retrieve_password_message');

function je_retrieve_password_message($message) {
    return et_get_mail_header() . $message . et_get_mail_footer();
}

add_action('et_cash_checkout', 'je_email_cash_message');

function je_email_cash_message($data) {
    global $current_user, $user_ID;
    $auto_email = et_get_auto_emails();
    
    $cash_message = $data['message'];
    $order = $data['order'];
    
     
    if ($auto_email['cash_notice']) {
        // get cash notification mail template and filter placeholder
        $mail_opt = new ET_JobEngineMailTemplate();
        $message = $mail_opt->get_cash_notification_mail();
        $message = str_ireplace('[cash_message]', $cash_message, $message);
        $message = et_filter_authentication_placeholder($message, $user_ID);
        
        $session = et_read_session();
        if ($order['payment'] == 'cash'){
            $message = str_replace('[type]', 'Überweisung', $message);
        }
        else{
            $message = str_replace('[type]', 'Lastschrift', $message);
        }


        if (isset($order['ID']) && !$session['is_payment_zertifikat']){
            $attachments = array(je_email_invoice($order));
        }
        else{
            $attachments = array(je_email_invoice($order, 1));
        }
        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= 'BCC: '. get_option('admin_email') . "\r\n";
        $headers .= 'FROM: praktischArzt <'. get_option('admin_email') . "> \r\n";
        $headers .= "Content-type: text/html; charset=".get_bloginfo('charset')."" . "\r\n";
        
        wp_mail($current_user->data->user_email, 
            __("Zahlungsaufforderung", ET_DOMAIN), et_get_mail_header() . $message . et_get_mail_footer(), 
            $headers,
            $attachments
        );            
        //send other emails
        //je_send_post_job_mail($order);        
    }
}
function je_send_post_job_mail(){
    global $current_user, $user_ID;
    
    $session = et_read_session();
    $job_form_data = isset($session['job_form_data'])?$session['job_form_data']:'';
    
    if (!isset($session['order_id']))
        $template_file = 'basic_1.htm';
    else if ($job_form_data['packageID'] == 12)  //free
        $template_file = 'basic_2.htm';
    else if ($job_form_data['facebook'] == 1 || $job_form_data['blog'] == 1 || $job_form_data['newsletter'] == 1)
        $template_file = 'paid_2.htm';
    else
        $template_file = 'paid_1.htm';
    
    $gebucht = array();
    if (is_array($job_form_data) && $job_form_data['facebook'] == 1){
        $gebucht[] = 'Facebook-Annonce';
    }
    if (is_array($job_form_data) && $job_form_data['blog'] == 1){
        $gebucht[] = 'Blog-Annonce';
    }
    if (is_array($job_form_data) && $job_form_data['newsletter'] == 1){
        $gebucht[] = 'Newsletter-Annonce';
    }
    $path = wp_upload_dir(); $path = $path['basedir'];
    
    return str_replace(array('[display_name]', '[slogan]', '[gebucht]'), 
        array($current_user->data->user_login, get_the_title($session['job_id']), implode(', ', $gebucht)), file_get_contents($path.'/invoices/'.$template_file));
}



function je_edit_job_mail($job_id){
    global $current_user, $user_ID;
    
    $template_file = 'edit.htm';
    $path = wp_upload_dir(); $path = $path['basedir'];
    
    $message = str_replace(array('[display_name]', '[slogan]'), 
        array($current_user->data->user_login, get_the_title($job_id)), file_get_contents($path.'/invoices/'.$template_file));
        
    wp_mail($current_user->data->user_email, 
            __("Ihre Stelleanzeige auf praktischArzt wurde erfolgreich editiert", ET_DOMAIN), et_get_mail_header() . $message . et_get_mail_footer()
        );
    
}

add_action('et_invoice_payment', 'je_email_invoice');

function je_email_invoice($order, $type = 0) { // default is post job
    global $current_user, $user_ID;
    
    $auto_email = et_get_auto_emails();
    
    $pdf_content = '';
    
    if ($auto_email['invoice_notice']) {
        // get cash notification mail template and filter placeholder
        $mail_opt = new ET_JobEngineMailTemplate();
        if ($type == 0){
            $message = $mail_opt->get_invoice_mail();
        }
        else{
            $message = $mail_opt->get_invoice_mail_zertifikat();    
        }
        
        if ($type == 0){
            $ID = $order['product_id'];
            $price = $order['products'][$ID]['AMT'];
            $quantity = $order['products'][$ID]['QTY'];
            $tax = $order['products'][$ID]['TAXAMT'];
            $invoice_subject =  $order['products'][$ID]['NAME'];
            $message = str_ireplace('[invoice_subject]', $invoice_subject, $message);                        
        }
        else{
            $ID = $order['ID'];            
            $price = $order['total'];
            $quantity = 1;
            $tax = $order['total']*0.19;
            $message = str_ireplace('[invoice_subject]', 'praktischArzt Zertifikat', $message);   
        }

        $message = str_ireplace('[job_payment_amount]', number_format($price, 2), $message);
        $message = str_ireplace('[total_amount]', number_format($price * $quantity, 2), $message);

        $message = str_ireplace('[job_quantity]', $quantity, $message);
        $message = str_ireplace('[total_bill]', number_format(($price * $quantity) + $tax, 2), $message);
        $message = str_ireplace('[Excl_VAT]', number_format($tax, 2), $message);
        
        
        $session = et_read_session();
        
        $message = str_ireplace('[invoice_number]', $ID, $message);
        
        $message = et_filter_authentication_placeholder($message, $user_ID);

        if ($type == 0 && isset($session['job_id'])) {
            $pdf_content = et_filter_job_placeholder($message, $session['job_id']);
            $job_form_data = $session['job_form_data'];
            
            $gebucht = array();
            if (is_array($job_form_data) && $job_form_data['facebook'] == 1){
                $gebucht[] = 'Facebook-Annonce';
            }
            if (is_array($job_form_data) && $job_form_data['blog'] == 1){
                $gebucht[] = 'Blog-Annonce';
            }
            if (is_array($job_form_data) && $job_form_data['newsletter'] == 1){
                $gebucht[] = 'Newsletter-Annonce';
            }    
            
            $selectedDuration = str_replace('weeks', '', $session['job-content']['selectedDuration']);
            $job_desc =  $order['products'][$ID]['NAME'] .': '.$selectedDuration.' Wochen';
            if (count($gebucht)){
                $job_desc .= ', '.implode(', ', $gebucht);
            }                                            
            $pdf_content = str_ireplace('[job_desc]', $job_desc, $pdf_content);
        }
        else{
            $pdf_content = $message;
                    
        }        
                                
        if ($pdf_content != ''){
            $path = wp_upload_dir(); $path = $path['basedir'];
            $content = file_get_contents($path.'/invoices/mail.htm');
            
            $contact = $current_user->data->display_name;
            
            $et_full_location = get_post_meta($session['job_id'], 'et_full_location');                   
            if (isset($et_full_location[0]))                    {                        
                $contact .= '<br/>'.$et_full_location[0];
            }
              
            $content = str_replace(
                array('{folder}', '{content}', '{contact}', '{date}'), 
                array($path.'/invoices/', $pdf_content, $contact, date('d.m.Y')), $content);
                
            if ($order['payment'] == 'cash'){
                $content = str_replace('[footer_text]', 'Der Rechnungsbetrag ist 14 Tage nach Erhalt der Rechnung auf unten aufgeführtes Konto unter Angabe der Rechnungsnummer zu überweisen.', $content);
            }        
            else if ($order['payment'] == 'debit'){
                $content = str_replace('[footer_text]', 'Der Rechnungsbetrag wird innerhalb der nächsten 14 Tage von Ihrem Konto abgebucht.', $content);
            }
            else{
                $content = str_replace('[footer_text]', 'Der Rechnungsbetrag wird von Ihrem Paypal Konto abgebucht.', $content);
            }
            
            require_once (ABSPATH . WPINC.'/dompdf/dompdf_config.inc.php');
            $dompdf = new DOMPDF();
            $dompdf->load_html($content);
            $dompdf->set_paper('letter', 'portrait');
            $dompdf->render();
            $output = $dompdf->output();
            $filename = getcwd().'/wp-content/uploads/invoices/files/praktischArzt_Bestellung_'.$order['ID'].'.pdf';
            file_put_contents($filename, $output);
            
            return $filename;                    
            
            //wp_mail($current_user->data->user_email, __("Zahlungsvorlage", ET_DOMAIN), et_get_mail_header() . $message . et_get_mail_footer());
        }        
        
        
        
        
    }
    
    
}

add_action('et_email_send_admin', 'je_email_send_admin_post_job');

function je_email_send_admin_post_job($mail_method = array()) {
    global $current_user, $user_ID;
    //Facebook, Newletter und Blog
    $auto_email = et_get_auto_emails();
    if ($auto_email['mail_admin']) {
        $mail_opt = new ET_JobEngineMailTemplate();

        //if ($mail_method['facebook'] == 1 || $mail_method['newsletter'] == 1 || $mail_method['blog'] == 1) 
        {
            //MESSAGE SEND ADMIN AND USER
            $message_admin = $mail_opt->get_email_send_admin();
            $message_admin = et_filter_authentication_placeholder($message_admin, $user_ID);

            
            $facebook = (isset($mail_method['facebook']) && $mail_method['facebook'] == 1) ? "Facebook<br/>" : "";
            $newletter = (isset($mail_method['newsletter']) && $mail_method['newsletter'] == 1) ? "Newsletter<br/>" : "";
            $blog = (isset($mail_method['blog']) && $mail_method['blog'] == 1) ? "Blog<br/>" : "";

            $mail_message = $facebook . $newletter . $blog;
            $message_admin = str_ireplace('[email_method]', $mail_message, $message_admin);

            $session = et_read_session();
            $admin_email = get_option('admin_email');
                        
            if (isset($session['method']) && $session['method'] == "payment_zertifikat") {
                //$message_user = $mail_opt->get_email_send_user();
                //$message_user = et_filter_authentication_placeholder($message_user, $user_ID);                
                //$message_user = str_ireplace('[email_method]', $mail_message, $message_user);
                
                $path = wp_upload_dir(); $path = $path['basedir'];
                $message_user = str_replace('[display_name]', $current_user->data->user_login, file_get_contents($path.'/invoices/pa.htm'));
                $message_admin = str_ireplace('[job_title]', 'Zertifikat', $message_admin);
                    
                $email_title = 'Vielen Dank für die Buchung des praktischArzt Zertifikat';
            } else if (isset($session['job_id'])) {
                $link_job = get_permalink($session['job_id']);
                $message_admin = et_filter_job_placeholder($message_admin, $session['job_id']);
                $message_admin = str_ireplace('[link_to_job]', $link_job, $message_admin);

                //$message_user = et_filter_job_placeholder($message_user, $session['job_id']);
                //$message_user = str_ireplace('[link_to_job]', $link_job, $message_user);
                
                $email_title =  __("Ihre Stellenanzeige auf praktischArzt wurde erfolgreich angelegt", ET_DOMAIN);
                
                $message_user = je_send_post_job_mail();
            }
			if (isset($_POST['debit']) && is_array($_POST['debit'])){
                $csv_file = je_debit_csv($_POST['debit']);
                //$current_user->data->user_email
				
				//if (!isset($session['job_form_data']))
				wp_mail($admin_email, $email_title, et_get_mail_header() . $message_admin . et_get_mail_footer(), '', array($csv_file));
                wp_mail($current_user->data->user_email, $email_title, et_get_mail_header() . $message_user . et_get_mail_footer());
            }
            else{
                
                
                //$current_user->data->user_email
				//if (!isset($session['job_form_data']))
				wp_mail($admin_email, $email_title, et_get_mail_header() . $message_admin . et_get_mail_footer());
                
                wp_mail($current_user->data->user_email, $email_title, et_get_mail_header() . $message_user . et_get_mail_footer());    
            }
            
        }
    }
}

function je_debit_csv($data){
    global $current_user, $user_ID;
        
    $authorID = isset($data['authorID']) ? $data['authorID'] : $user_ID;
    $jobID = isset($data['jobID']) ? $data['jobID'] : '';
    $price = isset($data['price']) ? $data['price'] : 0;
    $payer = isset($data['payer']) ? $data['payer'] : "";
    $accountNumber = isset($data['accountNumber']) ? $data['accountNumber'] : "";
    $bankNumber = isset($data['bankNumber']) ? $data['bankNumber'] : "";
    $company_location = et_get_user_field($user_ID, 'recent_job_location');
    $order_date = date('Y-m-d');
    $total_price = $price;
    //var_dump($data);
    //exit;
    
    $session = et_read_session();
    $filename = 'Lastschrift-Bestellnumber '.$session['order_id'].".csv";

    $result = "";
    $result .="Kontoinhaber;Rechnungsbetrag;Kontonummer;Bankleitzahl;Verwendungszweck1;Verwendungszweck2";
    $result .="\n";
    $result .=$payer . ";" . $total_price . ";" . $accountNumber . ";" . $bankNumber . ";" . (isset($company_location['full_location'])?$company_location['full_location']:'') . ";" . $order_date;
    $result .="\n";
    
    $filename = ABSPATH ."/wp-content/uploads/files/" . $filename;

    file_put_contents($filename, $result); 
    
    return $filename;
}   


    
add_action('et_email_payment_debit', 'je_email_payment_debit');
    
function je_email_payment_debit($data) {
    global $current_user, $user_ID;
    $mail_opt = new ET_JobEngineMailTemplate();
    
    $message = $mail_opt->get_invoice_mail_debit();
    $message_zertifikat = $mail_opt->get_invoice_mail_debit_zertifikat();
    
    $message = et_filter_authentication_placeholder($message, $user_ID);
    
    
    $message = str_ireplace('[Excl_VAT]', $data['amt'], $message);
    $message = str_ireplace('[total_bill]', $data['total'], $message);
    $message = str_ireplace('[invoice_subject]', $data['subject'], $message);
    $message = str_ireplace('[csv_file]', $data['csv_file'], $message);
    
    $message_zertifikat = et_filter_authentication_placeholder($message_zertifikat, $user_ID);
    $message_zertifikat = str_ireplace('[total_amount]', ($data['price'] * $data['quantity']), $message_zertifikat);
    $message_zertifikat = str_ireplace('[Excl_VAT]', $data['amt'], $message_zertifikat);
    $message_zertifikat = str_ireplace('[total_bill]', $data['total'], $message_zertifikat);
    $message_zertifikat = str_ireplace('[invoice_subject]', $data['subject'], $message_zertifikat);
    $message_zertifikat = str_ireplace('[csv_file]', $data['csv_file'], $message_zertifikat);

    $session = et_read_session();

    if (isset($session['method']) && $session['method'] == "payment_debit_zertifikat") {
        //var_dump($message_zertifikat);exit;
        wp_mail($current_user->data->user_email, __("Zahlungsvorlage", ET_DOMAIN), et_get_mail_header() . $message_zertifikat . et_get_mail_footer());
    } else {
        $message = str_ireplace('[job_payment_amount]', $data['price'], $message);
        $message = str_ireplace('[job_quantity]', $data['quantity'], $message);
        $message = str_ireplace('[total_amount]', ($data['price'] * $data['quantity']), $message);

        $message = et_filter_job_placeholder($message, $session['job_id']);
        //var_dump($message);exit;
        wp_mail($current_user->data->user_email, __("Zahlungsvorlage", ET_DOMAIN), et_get_mail_header() . $message . et_get_mail_footer());
    }
}
