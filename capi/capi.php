<?php
require_once plugin_dir_path( __FILE__ ) . 'includes/capi-class.php';
//error_log('capi');
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.synergyinnovativesystems.com
 * @since             1.0.1
 * @package           Capi
 *
 * @wordpress-plugin
 * Plugin Name:       Capi
 * Plugin URI:        http://www.synergyinnovativesystems.com/capi/
 * Description:       This plugin integrates Wordpress with Command and CEO
 * Version:           1.0.1
 * Author:            Synergy Innovative Systems
 * Author URI:        http://www.synergyinnovativesystems.com/
 * License:           Private
 * License URI:       http://www.synergyinnovativesystems.com/licenses/capi.txt
 * Text Domain:       capi
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-capi-activator.php
 */
function activate_capi() {
//error_log("activate");

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-capi-activator.php';
	Capi_Activator::activate();

	
	
/*	
	add_action('after_setup_theme', 'remove_admin_bar');
error_log("activate");
	function remove_admin_bar() {
error_log("remove");
//		if (!current_user_can('administrator') && !is_admin()) {
		if (!current_user_can('administrator')) {
		  show_admin_bar(false);
		}
	}
*/	



	
}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-capi-deactivator.php
 */
function deactivate_capi() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-capi-deactivator.php';
	Capi_Deactivator::deactivate();
}

//error_log("before register");
	register_activation_hook( __FILE__, 'activate_capi' );
	register_deactivation_hook( __FILE__, 'deactivate_capi' );
//error_log("after register");

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-capi.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_capi() {
//error_log('capi run');
	$plugin = new Capi();
	$plugin->run();

}

run_capi();


// add short codes

function capi_wc_volunteer_opportunity($atts = [], $content = null, $tag = '') {
	$capi=new capi_class;
	$opportunities=array();
	$volunteer_group_name=$atts["volunteer_group_name"];
	$evoid=$atts["evoid"];
	$output=$capi->capi_process_api("module=event&operation=wc_volunteer_opportunity&evoid=$evoid&volunteer_group_name=$volunteer_group_name");
	$opportunities=json_decode($output,true);
//	$opportunities=$output_array["opportunities"];

	$output= "<div class='capi_wc_volunteer_opportunity'>";
//	$output.=print_r($opportunities,true);
	$output.="<table class='capi_wc_volunteer_opportunity_table'>\n";
	 $output.="<tr>";
	 $output.="<td>Date</td>\n";
	 $output.="<td>Hours</td>\n";
	 $output.="<td>Job Description</td>\n";
	 $output.="<td>Requirements</td>\n";
	 $output.="<td>Volunteers</td>\n";
	 $output.="</tr>\n";
	foreach ($opportunities as $opportunity) {
	 $output.="<tr>";
	 $output.="<td>".$opportunity["evd_date"]."</td>\n";
	 $output.="<td>".$opportunity["start_time"]." - ".$opportunity["end_time"]."</td>\n";
	 $output.="<td><b>".$opportunity["volunteer_opportunity_name"]."</b> ".$opportunity["job_description"]." Total needed: ".$opportunity["quantity"]."</td>\n";
	 $output.="<td>".$opportunity["requirements"]."</td>\n";
	 $output.="<td></td>\n";
	 $output.="</tr>\n";
	 
	}
	$output.="</div>";
	return $output;
}	

function capi_member_zone() {

	$capi=new capi_class;

	$output="";
	$output.="<div class='capi_member_zone'>\n";
	$output.="<div class='capi_member_zone_inner'>\n";
	$output.="<div class='capi_member_zone_inner_inner'>\n";
	if (!$capi->capi_check_profile()) {
		$output.=capi_member_zone_login();
	} else {
		$output.=capi_member_zone_home();
//		$output.="Logged in.\n";
	}
	$output.="</div><!-- capi_member_zone_inner_inner -->\n";
	$output.="</div><!-- capi_member_zone_inner -->\n";
	$output.="</div><!-- capi_member_zone-->\n";
	
	return $output;
}	

function capi_ad_row() {

	$output="";
	$output.="<div class='capi_ad_row'>\n";
	$output.="<div class='capi_ad_row_inner'>\n";
	$output.="<div class='capi_ad_row_col capi_ad_row_col1'>\n";
	$output.="<img src='/wp-content/uploads/BMWx3.jpg'>";
	$output.="</div><!-- capi_ad_row_col1 -->\n";
	$output.="<div class='capi_ad_row_col capi_ad_row_col2'>\n";
	$output.="<img src='/wp-content/uploads/19pass.jpg'>";
	$output.="</div><!-- capi_ad_row_col2 -->\n";
	$output.="<div class='capi_ad_row_col capi_ad_row_col3'>\n";
	$output.="<img src='/wp-content/uploads/BMWx3.jpg'>";
	$output.="</div><!-- capi_ad_row_col3 -->\n";
	$output.="</div><!-- capi_ad_row_inner -->\n";
	$output.="</div><!-- capi_ad_row-->\n";
	
	return $output;

}

function capi_member_zone_home() {
	$output="";
	if (!isset($_SESSION)) session_start();
	$output.="<div class='capi_member_zone_login'>\n";
	$output.="<center>";
	$output.="<div class='capi_home_welome'>Welcome ".$_SESSION['first_name'].' '.$_SESSION['last_name']."</span>";
	$output.="</center>";
//	$output.="<div class='go-to-member-zone'>";
//	$output.="<div class='go-to-member-zone'>";
/*
	$output.='
	<ul><font color="#ffffff">
	<li>Access Member Discounts</li>
	<li>Post Scores & View USGA Handicap</li>
	<li>Find Member Events, Edit Profile</li><br>
	</ul>
	<center><div id="button-linklg"><a href="/member-zone" class="button-linklg">&nbsp;GO TO MEMBER ZONE&nbsp;</a> </div></center>
	';
	
//	$output.="<a href='http://www.ghin.com/scorePosting.aspx' target='_blank'>Post Scores to GHIN</a>&nbsp;&nbsp;";
	$output.="</div>";
	$output.="<div class='post-a-score'>";
	$output.='<center><div id="button-linklg"><a href="http://www.ghin.com/lookup.aspx" target="_blank" class="button-linklg">&nbsp;POST A SCORE&nbsp;</a> </div></center>';
	$output.="</div>";

	$output.="<div class='club-champions'>";
	$output.='<center><br><br><div id="button-linklg"><a href="/merchandise" class="button-linklg">&nbsp;BUY CGA MERCHANDISE&nbsp;</a> </div></center>';
*/
	$output.="</div>";
	return $output;
}

function capi_member_zone_login() {
	$output="";
	$output.="<div class='capi_member_zone_login'>\n";
	$output.="<center>";
	$output.="<div class='capi_member_zone_login_label'>\n";
	$output.="Access the Member Zone or Post a Score";
	$output.="</div>\n";
	$output.="<div class='capi_member_zone_form'>\n";
	$output.="<form id='wi_login_form' name='wi_login_form'>



<input type='hidden' id='operation' name='operation' value='login'>
<input type='hidden' name='module' value='website_integration'>
<input type='hidden' name='page' value='api'>
<input type='hidden' id='cid' name='cid' value=''>
<input type='hidden' id='first_name' name='first_name' value=''>
<input type='hidden' id='last_name' name='last_name' value=''>
<input type='hidden' id='handicap_number' name='handicap_number' value=''>
<input type='hidden' id='handicap_index' name='handicap_index' value=''>
<input type='hidden' id='admin_key' name='admin_key' value=''>
<input type='hidden' id='wiuid' name='wiuid' value=''>
<input type='hidden' id='ajax' name='ajax' value='1'>
<input type='hidden' name='wiwid' value='2'>
<input type='hidden' name='page_displayed' value='1'>
<input type='hidden' id='wiu_must_change' name='must_change' value='0'>
<input type='hidden' id='local_login_success' name='local_login_success' value='0'>
<input type='hidden' id='capi_login_destination' name='capi_login_destination' value='/member-zone'>
<input type='hidden' id='login_link_fail_destination' name='login_link_fail_destination' value=''>

		
	<div class='capi_member_zone_login_user'>
	<input type='text' id='wiu_user_name' name='wiu_user_name' value='' placeholder='GHIN'>
	</div>
	<div class='capi_member_zone_login_password'>
	<input type='password' id='wiu_password' name='wiu_password' value='' placeholder='PASSWORD'>
	</div>
	
	<div class='capi_member_zone_login_submit'>
	<input type='submit' id='login' class='login' name='login' value='Login'/>
	</div>
	
	</form>
	";
	$output.="</div><!-- capi_member_zone_form-->\n";
	$output.="</center>";
	$output.="</div><!-- capi_member_zone_login-->\n";

$output.="

			 <script type='text/javascript' src='/capi/jquery.min.js'></script>

			 <script type='text/javascript' src='/capi/jquery-ui.min.js'></script>

			 <script type='text/javascript' src='/capi/jquery.mask.min.js'></script>

			 
			 <script>
				cjQuery = jQuery.noConflict( true );

				
	//			alert(jQuery.fn.jquery);
				

(function($){

//    $(document).ready(function(){
	      $('#wi_login_form').submit(function(event) {
					event.preventDefault();
					login();
				});
				
		    $('#wiu_user_name').keypress(function(e) {
		        if ( e.keyCode == 13 ) {  // detect the enter key
//		            $('#login').click();
		        }
		    });
				
		    $('#wiu_password').keypress(function(e) {
		        if ( e.keyCode == 13 ) {  // detect the enter key
//		            $('#login').click();
		        }
		    });

//			}); // end ready
				
//	      $('#login').bind('click', login);

	 
	        function login() {

	          var formData = $('#wi_login_form').serializeArray();

	          $('#wi_login_message_div').html('Logging in');
	          $.ajax({
											
            url: '/capi/process',
	            type: 'POST',
							data: formData,
	            dataType: 'json',

	            success:  function(data){
									if (data['success']) {
					
		                $('#wi_login_message_div').html(data['message']);
		                $('#cid').val(data['cid']);
		                $('#first_name').val(data['first_name']);
		                $('#last_name').val(data['last_name']);
		                $('#handicap_number').val(data['handicap_number']);
		                $('#handicap_index').val(data['handicap_index']);
		                $('#wiuid').val(data['wiuid']);
		                $('#admin_key').val(data['admin_key']);
								
		                $('#wiu_must_change').val(data['wiu_must_change']);
										local_login();
									} else {
										alert(data['message']);
										if ($('#login_link_fail_destination').val()>'') {
											window.location.href=$('#login_link_fail_destination').val();
										}
									}
	            },
	            error: function( jqXHR, textStatus, errorThrown){
//alert(data);
//alert(jqXHR.responseText);
//alert(textStatus);
	               console.log('failure1a: ' + textStatus + ' - ' + errorThrown);
	                alert('failure1a: ' + textStatus + ' - ' + errorThrown + ' - ' + jqXHR.responseText);
	            }   
	          });        
	        
	       } // end login
				 
				 function local_login() {
				 
console.log('local login');

            $('#operation').val('local_login');
 					  $('#local_login_success').val(0);

	          var formData = $('#wi_login_form').serializeArray();

	          $('#wi_login_message_div').html('Local logging in.');
	          $.ajax({
											
            url: '/capi/process',
	            type: 'POST',
							data: formData,
	            dataType: 'json',
	            success:  function(data){
//alert(data);							
	                $('#wi_login_message_div').html(data['message']);

									if (data['success']) {
										// direct to member home page
						
										if ($('#wiu_must_change').val()==1) {
											alert('You must change your password.');
											window.location.href='/capi/changepassword?message=You%20must%20change%20password';
										} else	{
											window.location.href=$('#capi_login_destination').val();
										}
									}
									
	            },
	            error: function( jqXHR, textStatus, errorThrown){
								console.log($('#local_login_success').val());
	               console.log('failure2a: ' + textStatus + ' - ' + errorThrown + ' - ' +  jqXHR.responseText);
	                alert('failure2a: ' + textStatus + ' - ' + errorThrown + ' - ' +  jqXHR.responseText + ' - s' + $('#local_login_success').val());
	            }   
	          });

           $('#operation').val('login');
				 	
				 } // end local_login
				 
				 
				 

})(cjQuery)		
</script>
";




	return $output;
}

function capi_process_api($atts = [], $content = null, $tag = '') {
	
	$capi=new capi_class;
	return $capi->capi_process_api($atts[$query]);
}	


function capi_get_my_tournaments() {
	return "<div class='capi_my_tournaments'>In Progress. My Tournaments.</div>";
}	

function capi_get_upcoming_tournaments($atts = [], $content = null, $tag = '') {
	
	return "<div class='capi_upcoming_tournaments'>In Progress. Upcoming Tournaments.".print_r($atts,true)."</div>";
}	

function capi_tournament_schedule() {
	$capi=new capi_class;
	return "<div class='capi_tournament_schedule'>".$capi->capi_tournament_schedule()."</div>";
}	

function capi_get_join_us() {
	return "<div class='capi_join_us'>In Progress. Join Us.</div>";
}	

function capi_get_member_benefits_footer() {
	return "<div class='capi_member_benefits_footer'>In Progress. Member Benefits.</div>";
}	

function capi_get_copyright_footer() {
	$output="";
	$output.="<div class='capi_copyright_footer'>";
	$output.="<img src='".get_theme_root_uri()."/clubsitepro/images/footer_logo.png'>";
	$output.="<div style='clear:both'></div>";
	$output.="Your club benefits include membership to the Washington State Golf Association and all its benefits. WA Golf is an authorized provider and administrator of the USGA GHIN System.<br><br>";
	$output.='&copy; '.date("Y").' <a href="http://wagolf.org" target="_blank">Washington State Golf Association</a><br><br>Powered by <a href="clubsitepro.com" target="_blank">ClubSitePro</a> from <a href="http://synergyinnovativesystems.com" target="_blank">Synergy Innovative Systems</a>';
	$output.="</div>";
	
	return $output;
}	


function capi_get_capi_staff_directory() {
	$capi=new capi_class;
	$output=$capi->capi_process_api("operation=getcommittee&committee_name=cga staff");
	$output_array=$capi->capi_parse_xml2($output);
	$directory=$output_array["result"]["individual"];
//	$directory=print_r($output_array,false);
	$output="<div class='capi_staff_directory'>";
		foreach($directory as $entry) {
			$output.="<div class='capi_staff_directory_entry'>";

				$output.="<div class='capi_staff_headshot'>";
				if (isset($entry["headshot"]))
					$output.="<img class='capi_staf_headshot_img' src='".$entry["headshot"]."'>";
//				$output.=print_r($entry,false);
				$output.="</div>";

				$output.="<div id='capi_staff_name_".$entry['cid']."' class='capi_staff_name'>";
				$output.=$entry["first_name"]." ".$entry["last_name"];
				$output.="</div>";

				$output.="<div class='capi_staff_title'>";
				if (isset($entry["committee_field_values"]["cga_title"]))
					$output.=$entry["committee_field_values"]["cga_title"];
				else
					$output.="&nbsp;";
				$output.="</div>";

				$output.="<div class='capi_staff_email'>";
				if (isset($entry["email"]))
					$output.=$entry["email"];
				else
					$output.="&nbsp;";
				$output.="</div>";

				$output.="<div class='capi_staff_phone'>";
				if ($entry["number"]>"")
					$output.=$entry["number"];
				else
					$output.="&nbsp;";
				if ($entry["extension"]>"")
					$output.=" ext. ".$entry["extension"];
				$output.="</div>";

				$output.="<div class='capi_staff_read_bio'>";
					$output.="<button class='capi_staff_read_bio_button' data-cid='".$entry['cid']."'>Read Bio</button>";
				$output.="</div>";

				$output.="<div id='bio_".$entry["cid"]."' style='display:none' class='capi_staff_bio'>";
				if (isset($entry["bio"]))
					$output.=$entry["bio"];
				$output.="</div>";

			$output.="</div>";
			
		}
	$output.="</div>";

	$output.='
		<script>
    jQuery(document).ready(function(){
    
			jQuery(".capi_staff_read_bio_button").bind("click",capi_staff_read_bio_button_click);
			jQuery("#capi_modal_close_button").bind("click",capi_modal_close_button_click);
		 
    }); // end document ready
		
		function capi_staff_read_bio_button_click() {
		//alert(jQuery(this).attr("data-cid"));
		  cid=jQuery(this).data("cid");
			jQuery("#capi_overlay").show();
			jQuery("#capi_modal_body").html(jQuery("#bio_"+cid).html());
			jQuery("#capi_modal_header_name").html(jQuery("#capi_staff_name_"+cid).html());
			
		}
		function capi_modal_close_button_click() {
			jQuery("#capi_overlay").hide();
		}		
		</script>
		<div id="capi_overlay">
			<div id="capi_modal">
				<div id="capi_modal_header">
				<div id="capi_modal_header_name">
				
				</div>
				<div id="capi_modal_close">
				<button id="capi_modal_close_button">x</button>
				</div>
				</div>
				<div style="clear:both"></div>
				<div id="capi_modal_body">
				
				</div>
			
			</div>
		</div>
	';

	return $output;
	
}	

function capi_member_zone_main() {

	$capi=new capi_class;
	
		$output="";

		if (!$capi->capi_check_profile()) {
		 return "You must be logged in to see this page.  <a href='".get_site_url()."/capi/login'>Click Here to Log In</a>";
		}
		if (current_user_can('administrator') || current_user_can('editor')) {
//			$output.="<div class='capi_add_tournament_link'><a href='".get_site_url()."/capi/admin'>Admin</a><br><br></div>";
		}

		if (!isset($_SESSION)) session_start();

//		$cid=$_SESSION["cid"];
		$cid=$capi->capi_check_profile();//xxx

		$fields_array=$capi->capi_get_fields();

		$capi->capi_fill_fields_array($fields_array,$cid);
/*
		$query="module=clubsite_pro&operation=get_quick_links";
		$result=$capi->capi_parse_xml2($capi->capi_process_api($query));
		$quick_links=urldecode($result["result"]["csp_quick_links"]);

		$query="module=clubsite_pro&operation=get_association_news";
		$result=$capi->capi_parse_xml2($capi->capi_process_api($query));
		$association_news=urldecode($result["result"]["csp_association_news"]);
*/
		$output.="<div class='capi_member_zone_main'>";

		$output.="<div class='capi_member_zone_main_header'><h2>".
			$fields_array['first_name']["default"]." ".$fields_array['last_name']["default"]."
		 - GHIN #: ".$fields_array['handicap_number']["default"]."
		- USGA Handicap Index: ".$fields_array['handicap_index']["default"];

		$output.="</div> <!--capi_member_zone_main_header -->";

//		$output.="<div class='capi_home_box capi_my_events'><h2>Quick Links</h2>$quick_links</div>";

		
		$output.="
			<div class='capi_home_box_link'><a href='http://www.ghin.com/scorePosting.aspx' target='_blank'>Post Scores</a></div>
			<div class='capi_home_box_link'><a href='http://www.ghin.com/lookup.aspx' target='_blank'>Handicap Lookup</a></div>
			<div class='capi_home_box_link'><a href='".get_site_url()."/capi/member_discounts'>Member Discounts</a></div>
			<div class='capi_home_box_link'><a href='".get_site_url()."/hole-in-one-form'>Hole-In-One</a></div>
			<div class='capi_home_box_link'><a href='".get_site_url()."/capi/update_profile'>Update Profile</a></div>
			<div class='capi_home_box_link'><a href='".get_site_url()."/capi/change_password'>Change Password</a></div>
			";
		
		$output.="</div> <!--capi_member_zone_main -->";
		
		return $output;

}

function capi_get_first_name() {
	if (!isset($_SESSION)) session_start();
	
	return $_SESSION['first_name'];
}

function capi_get_last_name() {
	if (!isset($_SESSION)) session_start();
	return $_SESSION['last_name'];
}

function capi_get_handicap_number() {
	if (!isset($_SESSION)) session_start();
	return $_SESSION['handicap_number'];
}
function capi_get_handicap_index() {
	$capi=new capi_class;
	if (!isset($_SESSION)) session_start();
	$result=json_decode($capi->capi_process_api("operation=handicap_display&handicap_number=".$_SESSION['handicap_number']),true);
	return $result['handicap_index'];
}

function capi_get_login_page() {
	$capi=new capi_class;
	return $capi->capi_login();
}

function capi_get_join_box_content() {
	$capi=new capi_class;
	return $capi->capi_get_join_box_content();
}

function capi_get_member_benefits_box_content() {
	$capi=new capi_class;
	return $capi->capi_get_member_benefits_box_content();
}

function capi_get_contact_box_content() {
	$capi=new capi_class;
	return $capi->capi_get_contact_box_content();
}

function capi_course_search_form() {
	$capi=new capi_class;
	return $capi->capi_course_search_form();
}

function capi_edit_contact_box_editor() {
	$my_content="";
	wp_nonce_field('nonce_action', 'nonce_field');
	$content = get_option('my_content');
	wp_editor( $content, 'settings_wpeditor' );
}

function capi_get_registration_form() {
	$capi=new capi_class;
	return $capi->capi_register();
}

function capi_my_registrations() {
	$capi=new capi_class;
	return $capi->capi_my_registrations();
}

function capi_pay_dues_form($atts = [], $content = null, $tag = '') {
	$capi=new capi_class;
	return $capi->capi_pay_dues_form($atts,$content,$tag);
}

function capi_ftef() {

	$capi=new capi_class;
	return $capi->capi_ftef();
}



function capi_ftef_email_instructions() {

	$capi=new capi_class;
	return $capi->capi_ftef_email_instructions();
}

function capi_ftef_print_entry_form() {

	$capi=new capi_class;
	return $capi->capi_ftef_print_entry_form();
}

function capi_dms_get_recent_document_list() {
	$capi=new capi_class;
	return $capi->capi_dms_get_recent_document_list();
}

function capi_load_custom_wp_tiny_mce() {
error_log("in capi_load_custom_wp_tiny_mce");

	$editor_id = 'contact_box';
	$uploaded_csv = get_post_meta( $post->ID, 'custom_editor_box', true);
	wp_editor( $uploaded_csv, $editor_id );
	
}

function capi_volunteer_opportunity($atts = [], $content = null, $tag = '') {
	$capi=new capi_class;
	return $capi->capi_volunteer_opportunity($atts,$content,$tag);
}


	
add_shortcode('capi_my_tournaments', 'capi_get_my_tournaments');
add_shortcode('capi_upcoming_tournaments', 'capi_get_upcoming_tournaments');
add_shortcode('capi_tournament_schedule', 'capi_tournament_schedule');
add_shortcode('capi_join_us', 'capi_get_join_us');
add_shortcode('capi_member_benefits_footer', 'capi_get_member_benefits_footer');
add_shortcode('capi_contact_us_footer', 'capi_get_contact_us_footer');
add_shortcode('capi_copyright_footer', 'capi_get_copyright_footer');
add_shortcode('capi_staff_directory', 'capi_get_capi_staff_directory');
add_shortcode('capi_course_search_form', 'capi_course_search_form');
add_shortcode('capi_member_zone', 'capi_member_zone');
add_shortcode('capi_ad_row', 'capi_ad_row');
add_shortcode('capi_member_zone_main', 'capi_member_zone_main');
add_shortcode('capi_get_first_name', 'capi_get_first_name');
add_shortcode('capi_get_last_name', 'capi_get_last_name');
add_shortcode('capi_get_handicap_number', 'capi_get_handicap_number');
add_shortcode('capi_get_handicap_index', 'capi_get_handicap_index');
add_shortcode('capi_get_login_page', 'capi_get_login_page');
add_shortcode('capi_edit_contact_box_editor', 'capi_edit_contact_box_editor'); // not used
add_shortcode('capi_get_contact_box_content', 'capi_get_contact_box_content');
add_shortcode('capi_get_join_box_content', 'capi_get_join_box_content');
add_shortcode('capi_get_member_benefits_box_content', 'capi_get_member_benefits_box_content');
add_shortcode('capi_get_registration_form', 'capi_get_registration_form');
add_shortcode('capi_my_registrations', 'capi_my_registrations');
add_shortcode('capi_dms_get_recent_document_list', 'capi_dms_get_recent_document_list');
add_shortcode('capi_pay_dues_form', 'capi_pay_dues_form');
add_shortcode('capi_volunteer_opportunity', 'capi_volunteer_opportunity');
add_shortcode('capi_ftef', 'capi_ftef');
add_shortcode('capi_ftef_print_entry_form', 'capi_ftef_print_entry_form');
add_shortcode('capi_ftef_email_instructions', 'capi_ftef_email_instructions');
add_shortcode('capi_wc_volunteer_opportunity', 'capi_wc_volunteer_opportunity');
add_shortcode('capi_process_api', 'capi_process_api');
