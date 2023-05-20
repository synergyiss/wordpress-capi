<?php

require_once plugin_dir_path( __FILE__ ) . '../includes/capi-class.php';

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Capi
 * @subpackage Capi/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Capi
 * @subpackage Capi/public
 * @author     Your Name <email@example.com>
 */
class Capi_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	private $capi;  // capi class variable

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
//error_log('construct action added');
		add_filter('the_posts',array($this,'capi_page_detect'));
//		add_action('the_posts',array($this,'capi_page_detect'),-100);

		$this->capi=new capi_class;
	
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Capi_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Capi_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/capi-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Capi_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Capi_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/capi-public.js', array( 'jquery' ), $this->version, false );

	}
	
public function capi_page_detect($posts){
	

    global $wp;
    global $wp_query;

//error_log('capi_page_detect');		
//error_log("capi_page_detect");
/*
$page_slug="capi/process";

            if(count($posts) == 0 && (strtolower($wp->request) == $page_slug || $wp->query_vars['page_id'] == $page_slug)){
error_log('abcpage3:'.$wp->request);
                //create a fake post
                $post = new stdClass;
                $post->post_author = 1;
//                $post->post_name = $page_slug;
//                $post->guid = get_bloginfo('wpurl' . '/' . $page_slug);
                $post->post_title = 'page title';
                //put your custom content here
                $post->post_content = "Fake Content";
                //just needs to be a number - negatives are fine
//                $post->ID = -42;
  //              $post->post_status = 'static';
  //              $post->comment_status = 'closed';
  //              $post->ping_status = 'closed';
  //              $post->comment_count = 0;
                //dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
//                $post->post_date = current_time('mysql');
//                $post->post_date_gmt = current_time('mysql',1);

//                $post = (object) array_merge((array) $post, (array) $this->args);

//error_log(print_r($post,true));

                $posts = NULL;
                $posts[] = $post;

//                $wp_query->is_page = true;
//                $wp_query->is_singular = true;
//                $wp_query->is_home = false;
//                $wp_query->is_archive = false;
//                $wp_query->is_category = false;
//                unset($wp_query->query["error"]);
//                $wp_query->query_vars["error"]="";
//                $wp_query->is_404 = false;
            return $posts;

            }
//error_log(print_r($wp,true));
*/
//		error_log("count posts: ".count($posts));
//		error_log("is main query: ".$wp_query->is_main_query());
		if (!$wp_query->is_main_query()) return $posts; // this is not the main query.  Avoid being called multiple times.
/*		
error_log('requested1:'.print_r($_REQUEST,true));
error_log('wp:'.print_r($wp,true));
error_log('wp->request:'.print_r($wp,true));
error_log("here!");

*/

        $capi_league_management = "league_management/respond"; 

        $capi_url[] = "capi/abc"; 


        $capi_url[] = "capi/show_cid"; 
        $capi_url[] = "capi/delete_cid"; 

        $capi_url[] = "capi/get_document_list"; 
        $capi_url[] = "capi/get_document"; 

        $capi_url[] = "capi/process"; 
        $capi_url[] = "capi/login"; 
        $capi_url[] = "capi/logout"; 
        $capi_url[] = "capi/member_home"; 

        $capi_url[] = "capi/tournament_schedule"; 
        $capi_url[] = "capi/tournament_info"; 
        $capi_url[] = "capi/tournament_registrants"; 
        $capi_url[] = "capi/add_tournament"; 
        $capi_url[] = "capi/edit_tournament"; 

				
        $capi_url[] = "capi/event/registration_form"; 
        $capi_url[] = "capi/event/registration_form_submit"; 

        $capi_url[] = "capi/event/withdrawal_form"; 
        $capi_url[] = "capi/event/withdrawal_form_submit"; 

        $capi_url[] = "capi/do_check_in"; 

        $capi_url[] = "capi/resource"; 

        $capi_url[] = "capi/member_directory"; 
        $capi_url[] = "capi/email_preferences"; 
        $capi_url[] = "capi/text_preferences"; 
        $capi_url[] = "capi/send_text_message"; 
        $capi_url[] = "capi/send_email"; 
        $capi_url[] = "capi/list_emails"; 
        $capi_url[] = "capi/show_email"; 
        $capi_url[] = "capi/mass_subscribe"; 
        $capi_url[] = "capi/unsubscribe"; 
        $capi_url[] = "capi/manage_commerce"; 
        $capi_url[] = "capi/manage_site_subscriptions"; 
        $capi_url[] = "capi/post_score"; 
        $capi_url[] = "capi/registration_form"; // register for the website
        $capi_url[] = "capi/my_registrations"; 
        $capi_url[] = "capi/update_profile"; 
        $capi_url[] = "capi/par_play"; 
        $capi_url[] = "capi/capi_update_profile_picture"; 
        $capi_url[] = "capi/capi_update_profile_picture_submit"; 
        $capi_url[] = "capi/change_password"; 
        $capi_url[] = "capi/changepassword"; 
        $capi_url[] = "capi/post_score"; 
        $capi_url[] = "capi/score_history"; 
        $capi_url[] = "capi/email_registrants"; 
        $capi_url[] = "capi/registrations"; 
        $capi_url[] = "capi/registrations_download"; 
        $capi_url[] = "capi/manage_membership"; 
        $capi_url[] = "capi/manage_payment_methods"; 
        $capi_url[] = "capi/edit_contact_box"; 
        $capi_url[] = "capi/edit_join_box"; 
        $capi_url[] = "capi/edit_member_benefits_box"; 
        $capi_url[] = "capi/edit_tournament_fields"; 
        $capi_url[] = "capi/delete_tournament_field"; 
        $capi_url[] = "capi/edit_tournament_field"; 
        $capi_url[] = "capi/edit_tournament_field_submit"; 
        $capi_url[] = "capi/wc_volunteer_now"; 
        $capi_url[] = "capi/wc_do_volunteer_now"; 
        $capi_url[] = "capi/wc_mail_team"; 
        $capi_url[] = "capi/admin"; 
        $capi_url[] = "capi/course_directory_search"; 
        $capi_url[] = "capi/get_course"; 
        $capi_url[] = "capi/register"; 
        $capi_url[] = "capi/show_user"; 
        $capi_url[] = "capi/member_benefits"; 
        $capi_url[] = "capi/contact_us"; 
				

				$wp_request=strtolower($wp->request);

    global $fakepage_ABCD_detect; // used to stop double loading

//error_log('abcpage1a:'.$wp->request." count:".count($posts)." fakepage_ABCD_detect: $fakepage_ABCD_detect");

if (count($posts)>0) {
//	error_log(print_r($posts,true));
}

// count($posts) wasn't helping because for register page one of the other posts was filled in.
//    if (count($posts) == 0 && !$fakepage_ABCD_detect && (in_array($wp_request,$capi_url)) ) {

//    if (!$fakepage_ABCD_detect && (in_array($wp_request,$capi_url) || substr($wp_request,strlen($capi_league_management)==$capi_league_management) )) {
    if (!$fakepage_ABCD_detect && (in_array($wp_request,$capi_url)  )) {

//error_log('abcpage inside:'.$wp->request);

        // stop interferring with other $posts arrays on this page (only works if the sidebar is rendered *after* the main page)
        $fakepage_ABCD_detect = true;
        
		add_action('template_redirect', 'capi_template_redir');
	


				
        // create a fake virtual page
        $post = new stdClass;

				// determine content based on url

				if ($wp_request=='capi/process') {
	        $post->post_content = $this->capi->capi_process_api();
				} else if ($wp_request=='capi/abc') {
				    $post->post_content = "abc";
				} else if ($wp_request=='capi/show_cid') {
				    $post->post_content = $this->capi->capi_process_api("show_cid");
				} else if ($wp_request=='capi/delete_cid') {
				    $post->post_content = $this->capi->capi_process_api("delete_cid");
				} else if ($wp_request=='capi/login') {
				    $post->post_content = $this->capi->capi_login();
				} else if ($wp_request=='capi/logout') {
				    $post->post_content = $this->capi->capi_logout();
				} else if ($wp_request=='capi/tournament_schedule') {
				    $post->post_content = $this->capi->capi_tournament_schedule();
				} else if ($wp_request=='capi/tournament_info') {
				    $post->post_content = $this->capi->capi_tournament_info();
				} else if ($wp_request=='capi/tournament_registrants') {
				    $post->post_content = $this->capi->capi_tournament_registrants();
				} else if ($wp_request=='capi/add_tournament') {
				    $post->post_content = $this->capi->capi_edit_tournament();
				} else if ($wp_request=='capi/edit_tournament') {
				    $post->post_content = $this->capi->capi_edit_tournament();
				} else if ($wp_request=='capi/member_directory') {
				    $post->post_content = $this->capi->capi_member_directory();
				} else if ($wp_request=='capi/registration_form') {
				    $post->post_content = $this->capi->capi_registration_form();
				} else if ($wp_request=='capi/event/registration_form') {
				    $post->post_content = $this->capi->capi_event_registration_form();
				} else if ($wp_request=='capi/event/registration_form_submit') {
				    $post->post_content = $this->capi->capi_event_registration_form_submit();
				} else if ($wp_request=='capi/event/withdrawal_form') {
				    $post->post_content = $this->capi->capi_event_withdrawal_form();
				} else if ($wp_request=='capi/event/withdrawal_form_submit') {
				    $post->post_content = $this->capi->capi_event_withdrawal_form_submit();
				} else if ($wp_request=='capi/do_check_in') {
				    $post->post_content = $this->capi->capi_do_check_in();
				} else if ($wp_request=='capi/my_registrations') {
				    $post->post_content = $this->capi->capi_my_registrations();
				} else if ($wp_request=='capi/update_profile') {
				    $post->post_content = $this->capi->capi_update_profile();
				} else if ($wp_request=='capi/par_play') {
				    $post->post_content = $this->capi->capi_par_play();
				} else if ($wp_request=='capi/update_profile_picture') {
				    $post->post_content = $this->capi->update_profile_picture();
				} else if ($wp_request=='capi/update_profile_picture_submit') {
				    $post->post_content = $this->capi->update_profile_picture_submit();
				} else if ($wp_request=='capi/change_password') {
				    $post->post_content = $this->capi->capi_change_password();
				} else if ($wp_request=='capi/changepassword') {
				    $post->post_content = $this->capi->capi_change_password();
				} else if ($wp_request=='capi/member_home') {
				    $post->post_content = $this->capi->capi_member_home();
				} else if ($wp_request=='capi/post_score') {
				    $post->post_content = $this->capi->capi_post_score();
				} else if ($wp_request=='capi/email_preferences') {
				    $post->post_content = $this->capi->capi_email_preferences();
				} else if ($wp_request=='capi/text_preferences') {
				    $post->post_content = $this->capi->capi_text_preferences();
				} else if ($wp_request=='capi/send_text_message') {
				    $post->post_content = $this->capi->capi_send_text_message();
				} else if ($wp_request=='capi/send_email') {
				    $post->post_content = $this->capi->capi_send_email();
				} else if ($wp_request=='capi/list_emails') {
				    $post->post_content = $this->capi->capi_list_emails();
				} else if ($wp_request=='capi/show_email') {
				    $post->post_content = $this->capi->capi_show_email();
				} else if ($wp_request=='capi/manage_commerce') {
				    $post->post_content = $this->capi->capi_manage_commerce();
				} else if ($wp_request=='capi/manage_site_subscriptions') {
				    $post->post_content = $this->capi->capi_manage_site_subscriptions();
				} else if ($wp_request=='capi/admin') {
				    $post->post_content = $this->capi->capi_admin();
				} else if ($wp_request=='capi/registrations') {
				    $post->post_content = $this->capi->capi_registrations();
				} else if ($wp_request=='capi/registrations_download') {
				    $post->post_content = $this->capi->capi_registrations_download();
				} else if ($wp_request=='capi/email_registrants') {
				    $post->post_content = $this->capi->capi_email_registrants();
				} else if ($wp_request=='capi/email_registrants') {
				    $post->post_content = $this->capi->capi_email_registrants();
				} else if ($wp_request=='capi/wc_volunteer_now') {
				    $post->post_content = $this->capi->capi_wc_volunteer_now();
				} else if ($wp_request=='capi/wc_do_volunteer_now') {
				    $post->post_content = $this->capi->capi_wc_do_volunteer_now();
				} else if ($wp_request=='capi/wc_mail_team') {
				    $post->post_content = $this->capi->capi_wc_mail_team();
				} else if ($wp_request=='capi/manage_payment_methods') {
				    $post->post_content = $this->capi->capi_manage_payment_methods();
				} else if ($wp_request=='capi/edit_contact_box') {
				    $post->post_content = $this->capi->capi_edit_contact_box();
				} else if ($wp_request=='capi/edit_join_box') {
				    $post->post_content = $this->capi->capi_edit_join_box();
				} else if ($wp_request=='capi/edit_member_benefits_box') {
				    $post->post_content = $this->capi->capi_edit_member_benefits_box();
				} else if ($wp_request=='capi/member_benefits') {
				    $post->post_content = $this->capi->capi_member_benefits();
				} else if ($wp_request=='capi/contact_us') {
				    $post->post_content = $this->capi->capi_contact_us();

				} else if ($wp_request=='capi/delete_tournament_field') {
				    $post->post_content = $this->capi->capi_delete_tournament_field();
				} else if ($wp_request=='capi/edit_tournament_fields') {
				    $post->post_content = $this->capi->capi_edit_tournament_fields();
				} else if ($wp_request=='capi/edit_tournament_field') {
				    $post->post_content = $this->capi->capi_edit_tournament_field();
				} else if ($wp_request=='capi/edit_tournament_field_submit') {
				    $post->post_content = $this->capi->capi_edit_tournament_field_submit();
				} else if ($wp_request=='capi/edit_tournament_field_submit') {
				    $post->post_content = $this->capi->capi_edit_tournament_field_submit();
				} else if ($wp_request=='capi/mass_subscribe') {
				    $post->post_content = $this->capi->capi_mass_subscribe();
				} else if ($wp_request=='capi/unsubscribe') {
				    $post->post_content = $this->capi->capi_unsubscribe();
				} else if ($wp_request=='capi/get_document_list') {
				    $post->post_content = $this->capi->capi_get_document_list();
				} else if ($wp_request=='capi/get_document') {
				    $post->post_content = $this->capi->capi_get_document();
				} else if ($wp_request=='capi/resource') {
				    $post->post_content = $this->capi->capi_resource();
				} else if ($wp_request=='capi/course_directory_search') {
				    $post->post_content = $this->capi->capi_course_directory_search();
				} else if ($wp_request=='capi/get_course') {
				    $post->post_content = $this->capi->capi_get_course();
				} else if ($wp_request=='capi/register') {
				    $post->post_content = $this->capi->capi_register();;
				} else if ($wp_request=='capi/show_user') {
				    $post->post_content = $this->capi->capi_show_user();
				} else if (substr($wp_request,strlen($capi_league_management)==$capi_league_management)) {
				    $post->post_content = $this->capi->capi_league_management_respond();;
				}
//error_log('requested2:'.print_r($_REQUEST,true));

				// remove filter that adds <p> in wordpress

				remove_filter('the_content', 'wpautop');

				$post->post_content=str_replace('<script>','<pre class="script"><script>// <![CDATA[',$post->post_content);
				$post->post_content=str_replace('</script>','// ]]></script></pre>',$post->post_content);

				$post->post_content=str_replace('<style>','<pre class="style"><style>',$post->post_content);
				$post->post_content=str_replace('</style>','</style></pre>',$post->post_content);
				


				$post->post_content.="<style>pre.script {
				  visibility: hidden;
				  display: none;
				}
				.sidebar {
				    display: none;
				}
				</style>
				
				";
				
				if (substr($wp_request,0,4)=="capi") {
					$post->post_content.="
					<style>
						.entry-header {
						    display: none;
						}		
						</style>		
					";
				}

        $post->post_author = 1;
        $post->post_name = $wp_request;
        $post->guid = home_url() . '/' . $wp_request;
        $post->post_title = "";
//        $post->ID = -999;		// this caused an error
        $post->ID = 0;
        $post->post_type = 'page';
//        $post->post_status = 'static';
        $post->post_status = 'public';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->comment_count = 0;
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql', 1);
				
//$post->post_content = "Test Content";
//error_log(print_r($wp,true));
	
	      $posts=NULL;

//$post->post_content='wp->request:'.print_r($wp,true);

        $posts[]=$post;
				
 
        // make wpQuery believe this is a real page too
 
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_home = false;
        $wp_query->is_archive = false;
        $wp_query->is_category = false;
        unset($wp_query->query["error"]);
        $wp_query->query_vars["error"]="";
        $wp_query->is_404=false;
   
	  }  // not in array
		
    
//    return array();
    return $posts;
}

								
}


function capi_template_redir()
{
//https://stackoverflow.com/questions/17960649/wordpress-plugin-generating-virtual-pages-and-using-theme-template

    // Display movie template using WordPress' internal precedence
    //  ie: child > parent; page-movie.php > page.php
    //  this call includes the template which outputs the content

error_log('capi_template_redir');

    get_template_part('page', 'capi');

    exit;
}
