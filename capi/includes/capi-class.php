<?php

/*
	Copyright 2023, Cynergy Networks, LLC

*/

class Capi_class
{

	private	$capi_url;
	private	$capi_un;
	private	$capi_pw;
	private	$wiwid;
	private	$capi_login_destination;
	private	$capi_update_profile_message;
	private	$capi_register_profile_fields;

	public function __construct()
	{
		$this->capi_url = get_option("capi_url");
		$this->capi_un = get_option("capi_un");
		$this->capi_pw = get_option("capi_pw");
		$this->wiwid = get_option("capi_wiwid");

		$this->capi_update_profile_message = get_option("capi_update_profile_message", "This is the information in your GHIN profile.  You can access it either at GHIN.com or here at the CGA website.  If you update your profile here, it will automatically update in GHIN and vice versa.");
		$this->capi_update_profile_fields = get_option("capi_update_profile_fields", "");
		$this->capi_register_profile_fields = get_option("capi_register_profile_fields", "");

		$this->capi_login_destination = get_option("capi_login_destination");
	}  // end construct()

	public function capi_test()
	{
		return "test";
	}

	public function capi_process_api($queryin = "", $ajax = 0, $check_profile = 1, $ignore_request = 0)
	{

		// ignore_request should have something in $queryin and what is in $_REQUEST will be not be sent to command

		if (!isset($_SESSION)) session_start();
		$page_display = 0;		// parameter passed in if need to display output in a drupal page

		//  global $user;  drupal

		$operation = "";
		$pdf = 0;
		$csv = 0;

		$wiwid = $this->wiwid;

		if (!$ignore_request)
			foreach ($_REQUEST as $key => $value)
				${$key} = $value;


		if ($queryin == "delete_cid") {
			unset($_SESSION['cid']);
			return "cid deleted";
		}

		if ($queryin == "show_cid") {
			return "cid: " . $_SESSION['cid'];
		}



		if ($operation == "local_login") {
			$my_return = $this->capi_local_login($_REQUEST);
			print json_encode($my_return);
			exit;
		} else if ($operation == "local_changepassword") {

			//error_log("change password wiu_user_name:$wiu_user_name, wiu_password:$wiu_password, wiu_repeatpassword:$wiu_repeatpassword");

			$my_return = $this->capi_local_changepassword($_REQUEST);
			print json_encode($my_return);
			exit(0);
		} else if ($operation == "deletewebsiteuser") {
			//error_log("deleting website user");
			$my_return = $this->capi_deletewebsiteuser($_REQUEST);

			print json_encode($my_return);
			exit(0);
		} else if ($operation == "addwebsiteuser") {

			$my_return = $this->capi_addwebsiteuser($_REQUEST);

			print json_encode($my_return);
			exit(0);
		} else if ($operation == "updatewebsiteusergroup") {
			$my_return = $this->capi_updatewebsiteusergroup($_REQUEST);

			print json_encode($my_return);
			exit(0);
		} else if ($operation == "updatewebsiteuser") {
			$my_return = $this->capi_updatewebsiteuser($_REQUEST);

			print json_encode($my_return);
			exit(0);
		} else if ($operation == "updatecontactallcomplete") {
			$my_return = capi_updatecontactallcomplete();

			if ($queryin > "") {
				// internal call, just return
				return $my_return;
			} else {
				print json_encode($my_return);
				exit(0);
			}
		} else {

			//	error_log("calling commandx:$operation");	
			//	error_log("queryin: $queryin");	

			// get user cid to pass into capi for permissions on C side.
			$user_cid = 0;
			if ($check_profile) {
				$user_cid = $this->capi_check_profile(true);
				//error_log("checked_profile user_cid: $user_cid");
			}
			//error_log("after checked profile user_cid: $user_cid");

			//	error_log("user_cid:$user_cid");

			// this is an operation to pass to C	


			if ($queryin > "") $query = $this->urlencodeall($queryin) . "&";
			else $query = "";

			// CAPI Login Information

			$query .= "wiwid=" . urlencode($wiwid) . "&";
			$query .= "user_cid=" . urlencode($user_cid) . "&";

			$query .= "un=" . urlencode($this->capi_un) . "&";
			$query .= "pw=" . urlencode($this->capi_pw) . "&";

			//				$query .= "un=demo.clubsite.synergyinnovativesystems.com&";
			//			  $query .= "pw=demo1&";

			//		  $query .= "capi_external_key_field=" . urlencode(variable_get('capi_external_key_field', "")) . "&";
			//		  $query .= "capi_external_key=" . urlencode($user->uid) . "&";
			$query .= "ip_address=" . urlencode($_SERVER['REMOTE_ADDR']) . "&";
			//error_log("admin_key: ".$_SESSION['admin_key']);
			if (isset($_SESSION['admin_key'])) {
				$query .= "admin_key=" . urlencode($_SESSION['admin_key']) . "&";
			}

			//	error_log($query);

			if (!$ignore_request)
				foreach ($_REQUEST as $key => $value) {

					//error_log($key."=".$value);

					if (is_array($value)) {
						foreach ($value as $value2) {
							${$key}[] = $value2;
							$query .= $key . "[]=" . urlencode($value2) . "&";
						}
					} else {

						${$key} = $value;
						$query .= $key . "=" . urlencode($value) . "&";
					}
				} // end foreach

			$query .= "&page=api";
			$output = "";
			//		  $output= get_option('capi_url', "")."/?page=api&".$query."<br>";

			//error_log("calling capi:".$this->capi_url."/?page=api&".$query);

			$ch = curl_init();

			//		 	  		curl_setopt($ch, CURLOPT_URL, $this->capi_url."/?page=api&".$query);
			curl_setopt($ch, CURLOPT_URL, $this->capi_url);
			//		 	  		curl_setopt($ch, CURLOPT_URL, "http://dev-cogolf.commandsystem.org/command/?page=api&".$query);


			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
			curl_setopt($ch, CURLOPT_TIMEOUT, 500);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			//						curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
			curl_setopt($ch, CURLOPT_SSLVERSION, 1); // problem with constant being available

			curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
			curl_setopt($ch, CURLOPT_POST, 6);

			//error_log($this->capi_url."?$query");						

			if (!($data = curl_exec($ch))) {
				$output .= "Curl Error<br>";
				$output .= "Error: " . curl_error($ch) . "<br>";
				$output .= "Error number: " . curl_errno($ch) . "<br>";
				//							$output.="Data:".$data.":".$this->capi_url."?$query";
				error_log("curl error. $data ");
			} else {
				error_log("err:" . curl_error($ch));
				error_log("err:" . curl_errno($ch));

				curl_close($ch);
				unset($ch);
				$output .= $data;
				//error_log("curl successful. $data");						
			}

			// if this is a pdf, want to put out appropriate headers and exit before drupal fills the page.


			if ($pdf) {
				$name = "tee_card_gift_certificate.pdf";
				header('Content-Type: application/pdf');
				//	header('Content-Length: '.strlen( $output ));
				header('Content-disposition: inline; filename="' . $name . '"');
				header('Cache-Control: public, must-revalidate, max-age=0');
				header('Pragma: public');
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
				print $output;
				exit(0);
			}

			if ($csv) {
				header("Content-type: text/csv; charset=UTF-8");
				header("Content-Disposition: attachment; filename=file.csv");
				header("Pragma: no-cache");
				header("Expires: 0");
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
				print $output;
				exit(0);
			}

			// if this is an ajax call, want to exit before drupal fills the page.


			if ($ajax) {
				error_log("ajax");
				// check for xml 

				if (strpos($output, 'result>') > 0) {
					// parse and return json encoded
					$output_array = $this->capi_parse_xml2($output);
					print json_encode($output_array["result"]);
				} else
					print $output;

				exit(0);
			}
			//error_log("not ajax");

			// check if need drupal page displayed

			if ($page_display == 1) {
				//						drupal_set_title("");
				//error_log("just returning output");
				return $output;
			}

			// if $queryin>"" then capi_process is being called by an internal function
			// and wants a return.  Otherwise, it is being called by jquery and should just
			// print output.

			//error_log("len queryin: ".strlen($queryin));
			//error_log($output);
			//error_log("end of output");


			if ($queryin > "") {
				//error_log("just returning output");
				return $output;
			} else {
				print $output;
				exit;
			}
		}
	}	// end capi_process_api

	public
	/****************************************************************************/

	function capi_parse_xml2($contents, $get_attributes = 1, $priority = 'tag')
	{
		//error_log("contents: $contents");
		return json_decode(str_replace('{}', '""', json_encode(simplexml_load_string("<x>$contents</x>"))), TRUE);

		if (!function_exists('xml_parser_create')) {
			return array();
		}
		$parser = xml_parser_create('');

		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);
		if (!$xml_values)
			return; //Hmm...
		$xml_array = array();
		$parents = array();
		$opened_tags = array();
		$arr = array();
		$current = &$xml_array;
		$repeated_tag_index = array();
		foreach ($xml_values as $data) {
			unset($attributes, $value);
			extract($data);
			//        $result = array (); // pn change
			$result = '';
			$attributes_data = array();
			if (isset($value)) {
				if ($priority == 'tag')
					$result = $value;
				else
					$result['value'] = $value;
			}
			if (isset($attributes) and $get_attributes) {
				foreach ($attributes as $attr => $val) {
					if ($priority == 'tag')
						$attributes_data[$attr] = $val;
					else
						$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}
			if ($type == "open") {
				$parent[$level - 1] = &$current;
				if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
					$current[$tag] = $result;
					if ($attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					$current = &$current[$tag];
				} else {
					if (isset($current[$tag][0])) {
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						$repeated_tag_index[$tag . '_' . $level]++;
					} else {
						$current[$tag] = array(
							$current[$tag],
							$result
						);
						$repeated_tag_index[$tag . '_' . $level] = 2;
						if (isset($current[$tag . '_attr'])) {
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset($current[$tag . '_attr']);
						}
					}
					$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
					$current = &$current[$tag][$last_item_index];
				}
			} elseif ($type == "complete") {
				if (!isset($current[$tag])) {
					$current[$tag] = $result;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
				} else {
					if (isset($current[$tag][0]) and is_array($current[$tag])) {
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						if ($priority == 'tag' and $get_attributes and $attributes_data) {
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag . '_' . $level]++;
					} else {
						$current[$tag] = array(
							$current[$tag],
							$result
						);
						$repeated_tag_index[$tag . '_' . $level] = 1;
						if ($priority == 'tag' and $get_attributes) {
							if (isset($current[$tag . '_attr'])) {
								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset($current[$tag . '_attr']);
							}
							if ($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
					}
				}
			} elseif ($type == 'close') {
				$current = &$parent[$level - 1];
			}
		}
		return ($xml_array);
	}  // end capi_parse_xml2


	public	function capi_login()
	{
		/*	
		$user_id = 2;
		$user = get_user_by( 'id', $user_id ); 
		if( $user ) {
		    wp_set_current_user( $user_id, $user->user_login );
		    wp_set_auth_cookie( $user_id );
		    do_action( 'wp_login', $user->user_login );
		}
	*/

		$output = "";


		$wiu_user_name = "";
		$wiu_password = "";
		$wiwid = $this->wiwid;
		$login_link = 0;
		$login_link_fail_destination = "";
		$capi_login_destination = $this->capi_login_destination;

		foreach ($_REQUEST as $key => $value) ${$key} = $value;

		if ($capi_login_destination == "") {
			//		  $capi_login_destination=variable_get('capi_login_destination', "");
		}
		//	$user_cid=capi_check_profile(true);
		$user_cid = 0;
		if ($user_cid > 0) {
			// already logged in, just go to destination
			//			drupal_goto($capi_login_destination);
		}

		//		drupal_set_title("");	
		//error_log("capi_login");
		$query = "module=website_integration&operation=login&wiwid=$wiwid&capi_login_destination=$capi_login_destination";
		/*	
		if ($login_link) {
			$query.="&login_link=1&login_link_fail_destination=$login_link_fail_destination";
		}
	*/


		if ($wiu_user_name > "" && $wiu_password > "")
			$query .= "&wiu_user_name=$wiu_user_name&wiu_password=$wiu_password";

		$output .= "<div id='capi_login_outer' style='width:300px;margin-left:auto;margin-right:auto;'>";
		$output .= "<div id='capi_login_title'>Log In</div>";

		$result = $this->capi_process_api($query);

		/*
	error_log("login query: $query");	
		$result_decode=json_decode($result);
		if (!is_null($result_decode)) {
		error_log("result_decode:".$result_decode->login_link_fail_destination);
			drupal_goto($result_decode->login_link_fail_destination);
			exit;
		}
	*/

		$output .= $result;

		$output .= "</div>";

		return $output;
	}

	function capi_local_login($req)
	{

		if (!isset($_SESSION)) {
			error_log("capi_local_login session started");
			session_start();
		} else {
			error_log("capi_local_login session not started");
		}

		$cid = 0;
		foreach ($req as $key => $value)
			${$key} = $value;

		$success = 0;

		if (strpos($wiu_user_name, "|") > 0) {

			$admin_flag = true;

			// this is an admin login.

			$wiu_user_name_array = explode("|", $wiu_user_name);

			$admin_user_name = $wiu_user_name_array[1];
			$wiu_user_name = $wiu_user_name_array[0];
		}

		// user not found
		//			$user->uid=0;
		$message = "Failed local login.";
		$success = 0;

		$user = get_user_by('login', $wiu_user_name);
		$user_id = $user->ID;
		//error_log("user_id:$user_id");
		if ($user) {
			wp_set_current_user($user_id, $user->user_login);
			wp_set_auth_cookie($user_id);
			do_action('wp_login', $user->user_login, $user);
			$success = true;
			$_SESSION["capi_uid"] = $user_id;
			$_SESSION["cid"] = $cid;
			$_SESSION["first_name"] = $first_name;
			$_SESSION["last_name"] = $last_name;
			$_SESSION["club_member_number"] = $club_member_number;
			$_SESSION["handicap_number"] = $handicap_number;
			$_SESSION["handicap_index"] = $handicap_index;

			if (current_user_can('administrator') || current_user_can('editor')) {
				$_SESSION["admin_key"] = "xdkejjdkseleiii";;
				//error_log("admin logged in");
			}

			//error_log("club_member_number: $club_member_number");

			$message = "Local login success. $first_name $last_name $club_member_number";
			//error_log("session cid:".$_SESSION["cid"]);
		}




		$my_return = array(
			"message" => $message,
			"success" => $success,
			"uid" => $user_id,
			"cid" => $_SESSION["cid"],
		);


		return $my_return;
	} // end capi_local_login

	function capi_logout()
	{
		if (!isset($_SESSION))
			session_start();

		unset($_SESSION['cid']);
		unset($_SESSION['capi_uid']);
		unset($_SESSION['admin_key']);
		unset($_SESSION['first_name']);
		unset($_SESSION['last_name']);
		unset($_SESSION['club_member_number']);
		unset($_SESSION['handicap_number']);
		unset($_SESSION['handicap_index']);

		wp_logout();
		wp_redirect(home_url());
		exit();
	}

	function capi_tournament_schedule()
	{

		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}


		$wiwid = $this->wiwid;
		$sort = "d";
		$today = date("Y-m-d", time());
		$year_ago = date("Y-m-d", strtotime(date("Y-m-d") . " -1 year"));

		if (current_user_can('administrator') || current_user_can('editor')) {
			$start_date = $year_ago;
		} else {
			$start_date = $today;
		}



		//		$query="module=event&operation=get_event_occurrences&sort=$sort&event_status=1&event_tentative=0&start_date=$start_date&filter_wiwid=$wiwid";
		// remove start date

		$query = "module=event&operation=get_event_occurrences&sort=$sort&event_status=1&event_tentative=0&filter_wiwid=$wiwid";

		$result = $this->capi_parse_xml2($this->capi_process_api($query));
		$events = $result["result"]["events"];
		if (isset($events["event"][0])) $events = $events["event"];
		$output = "<h1>Events</h1>";

		if (current_user_can('administrator') || current_user_can('editor')) {
			$output .= "<div class='capi_admin_div'>";
			$output .= "<div class='capi_add_tournament_link capi_button_link'><a href='" . get_site_url() . "/capi/add_tournament'>Add Event</a></div>";
			$output .= "</div>";
		}
		if (is_array($events)) {
			$output .= "<table>";
			$odd = true;
			foreach ($events as $event) {
				if (($event["show_on_schedule_start_date"] <= $today || $event["show_on_schedule_start_date"] == "") && ($event["show_on_schedule_end_date"] >= $today || $event["show_on_schedule_end_date"] == "")) {
					$output .= "<tr ";
					if ($odd) $output .= "class='odd'";
					$odd = !$odd;
					$output .= ">";
					$output .= "<td>";
					$output .= stripcslashes($event["event_occurrence_name"]);
					$output .= "</td>";
					$output .= "<td>";
					$output .= stripcslashes($event["event_location"]);
					$output .= "</td>";
					$output .= "<td>";
					$output .= date("m/d/Y", strtotime($event["event_start_date"]));
					if ($event["event_start_date"] <> $event["event_end_date"])
						$output .= " - " . date("m/d/Y", strtotime($event["event_end_date"]));
					$output .= "</td>";
					$output .= "<td>";
					$output .= "<a href='" . get_site_url() . "/capi/tournament_info?evoid=" . $event["evoid"] . "'>Info/Register</a>";
					$output .= "</td>";
					$output .= "</tr>";
				}
			}
			$output .= "</table>";
		} else {
			$output .= "No events.";
		}

		if (current_user_can('administrator') || current_user_can('editor')) {
			$query = "module=event&operation=get_event_occurrences&sort=$sort&event_status=0&event_tentative=0&start_date=$start_date&filter_wiwid=$wiwid";
			$result = $this->capi_parse_xml2($this->capi_process_api($query));
			$events = $result["result"]["events"];
			if (isset($events["event"][0])) $events = $events["event"];
			$output .= "<br><br><h1>Inactive</h1>";
			if (is_array($events)) {
				$output .= "<table>";
				$odd = true;
				foreach ($events as $event) {
					if (($event["show_on_schedule_start_date"] <= $today || $event["show_on_schedule_start_date"] == "") &&
						($event["show_on_schedule_end_date"] >= $today || $event["show_on_schedule_end_date"] == "") &&
						$event["event_status"] == 0
					) {
						$output .= "<tr ";
						if ($odd) $output .= "class='odd'";
						$odd = !$odd;
						$output .= ">";
						$output .= "<td>";
						$output .= stripcslashes($event["event_occurrence_name"]);
						$output .= "</td>";
						$output .= "<td>";
						$output .= date("m/d/Y", strtotime($event["event_start_date"]));
						if ($event["event_start_date"] <> $event["event_end_date"])
							$output .= " - " . date("m/d/Y", strtotime($event["event_end_date"]));
						$output .= "</td>";
						$output .= "<td>";
						$output .= "<a href='" . get_site_url() . "/capi/tournament_info?evoid=" . $event["evoid"] . "'>Info</a>";
						$output .= "</td>";
						$output .= "</tr>";
					}
				}
				$output .= "</table>";
			} else {
				$output .= "No events.";
			}
		}

		//$output.= print_r($result,true);
		return $output;
	}

	function capi_edit_tournament()
	{
		if (!current_user_can('administrator') && !current_user_can('editor')) {
			return "Administrators only.";
		}

		$evoid = 0;
		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}

		$query = "module=event&operation=get_edit_event_form&evoid=$evoid";

		$result = "";

		$result .= "<div class='capi_admin_div'>";
		if ($evoid > 0)
			$result .= "<h1>Edit Event</h1>";
		else
			$result .= "<h1>Add Event</h1>";
		$result .= "</div'>";
		$result .= "
			<div class='capi_registrations_link capi_button_link'><a href='" . get_site_url() . "/capi/tournament_schedule/'>Schedule</a></div>
			<div style='clear:both;margin-bottom:5px;'></div>
		";

		$result .= stripslashes($this->capi_process_api($query));

		return $result;
	} // end capi_edit_event

	function capi_event_registration_form()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$evoid = 0;
		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}

		$query = "module=event&operation=get_event_regististration_form&evoid=$evoid&success_url=" . get_site_url() . "/capi/tournament_schedule";

		$result = ($this->capi_process_api($query));

		return $result;

		//		$result=$this->capi_parse_xml2($this->capi_process_api($query));
		//return print_r($result,true);
		//		$result=$result["result"];
		/*		
		if ($result['success'])
			return "Registration Complete. <a href='/capi/tournament_schedule'>Click here to return to the schedule</a>.";
		else
			return "Error. ".$result['message'];
*/
	} // end capi_register

	function capi_event_withdrawal_form()
	{

		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$evoid = 0;
		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}

		$query = "module=event&operation=get_event_withdrawal_form&evoid=$evoid&success_url=" . get_site_url() . "/capi/tournament_schedule";

		$result = ($this->capi_process_api($query));

		return $result;

		//		$result=$this->capi_parse_xml2($this->capi_process_api($query));
		//return print_r($result,true);
		//		$result=$result["result"];
		/*		
		if ($result['success'])
			return "Registration Complete. <a href='/capi/tournament_schedule'>Click here to return to the schedule</a>.";
		else
			return "Error. ".$result['message'];
*/
	} // end capi_register

	function capi_edit_event_registration_form()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$evoid = 0;
		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}

		$query = "module=event&operation=get_edit_event_form&evoid=$evoid&success_url=" . get_site_url() . "/capi/tournament_schedule";

		$result = ($this->capi_process_api($query));

		return $result;

		//		$result=$this->capi_parse_xml2($this->capi_process_api($query));
		//return print_r($result,true);
		//		$result=$result["result"];
		/*		
		if ($result['success'])
			return "Registration Complete. <a href='/capi/tournament_schedule'>Click here to return to the schedule</a>.";
		else
			return "Error. ".$result['message'];
*/
	} // end capi_edit_event_registration_form

	function capi_my_registrations()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$status_array = array(
			0 => "Cancelled",
			1 => "Registered",
			2 => "Wait listed"
		);

		$evoid = 0;
		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}

		$query = "module=event&operation=get_my_registrations&evoid=$evoid";

		$query_result = ($this->capi_process_api($query));
		$query_result = $this->capi_parse_xml2($query_result);
		//		$result=$result['result'];
		//		$events=$result['events'];

		//		return "<textarea>".print_r($result,true)."</textarea>";

		//return print_r($result,true);
		$result = $query_result["result"];

		if (!$result['success'])
			return "Error. " . $result['message'];

		if (isset($result['events']['event'][0]))
			$result['events'] = $result['events']['event'];

		$output = "";
		if (isset($result['events'])) {
			if (is_array($result['events']))
				foreach ($result['events'] as $event) {
					$output .= "<tr>";

					$output .= "<td>" . stripcslashes($event['event_occurrence_name']) . "</td>";
					$output .= "<td>" . date("m/d/Y", strtotime($event['event_start_date'])) . "</td>";
					$output .= "<td>" . $status_array[$event['registered_status']] . "</td>";
					$output .= "</tr>";
				}
		}
		if ($output > "") {
			$output = "<table>$output</table>";
		} else {
			$output .= "None yet.";
		}
		return $output;
	} // end capi_my_registrations


	function capi_pay_dues_form($atts = [], $content = null, $tag = '')
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$asid = 1;
		$dues = 0;
		$transaction_fee = 0;
		$year = 0;
		$prodid = 0;
		$late_fee = 0;
		$late_date = "";

		extract($atts);

		$today = date("Y-m-d");

		if ($late_date > "") {
			$late_date = date("Y-m-d", strtotime($late_date));
			if ($today > $late_date) {
				$dues += $late_fee;
			}
		}

		//		$query="asid=1&dues=125&transaction_fee=4&year=2022&prodid=1&module=online_membership&operation=om_pay_dues_form";
		$query = "asid=$asid&dues=$dues&transaction_fee=$transaction_fee&year=$year&prodid=$prodid&module=online_membership&operation=om_pay_dues_form";

		$query_result = ($this->capi_process_api($query));

		$result = "";
		//		$result="<h2>".$capi_result->dms_type_name."</h2>";


		$query_result = json_decode($query_result);

		$message = $query_result->message;

		return $message;
	} // end capi_dms_get_recent_document_list

	function capi_ftef()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$query = "module=ftef&operation=ftef_entry_form";

		$query_result = ($this->capi_process_api($query));

		$result = "";


		$query_result = json_decode($query_result);

		$message = $query_result->message;

		return $message;
	} // end capi_dms_get_recent_document_list

	function capi_ftef_email_instructions()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$query = "module=ftef&operation=ftef_email_instructions";

		$query_result = ($this->capi_process_api($query));

		$result = "";


		$query_result = json_decode($query_result);

		$message = $query_result->message;

		return $message;
	} // end capi_dms_get_recent_document_list



	function capi_ftef_print_entry_form()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$query = "module=ftef&operation=ftef_print_entry_form";

		$query_result = ($this->capi_process_api($query));


		$query_result = json_decode($query_result);

		$message = $query_result->message;

		return $message;
	} // end capi_dms_get_recent_document_list


	function capi_dms_get_recent_document_list()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$query = "module=dms&operation=get_recent_document_list&quantity=10";

		$query_result = ($this->capi_process_api($query));

		$result = "";
		//		$result="<h2>".$capi_result->dms_type_name."</h2>";


		$query_result = json_decode($query_result);

		$documents = $query_result->documents;
		$result .= "<table class='capi_dms_table'>";

		foreach ($documents as $document) {

			$result .= "<tr>";
			$result .= "<td><a href='" . get_site_url() . "/capi/get_document?dmsdid=" . $document->dmsdid . "'>" . $document->dms_date . "</a></td>";
			$result .= "<td>" . $document->dms_type_name . "</td>";

			$result .= "</tr>";
		}
		$result .= "</table>";

		return $result;
	} // end capi_dms_get_recent_document_list


	function capi_update_profile()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$edit_cid = 0;

		foreach ($_REQUEST as $key => $value)
			${$key} = $value;


		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		//		$cid=$_SESSION["cid"];
		$cid = $this->capi_check_profile();

		if ($edit_cid > 0) $cid = $edit_cid;

		$fields_array = $this->capi_get_fields();
		$this->capi_fill_fields_array($fields_array, $cid);

		$output = "<h2>Update Profile</h2>";
		$output .= $this->capi_update_profile_message;
		$output .= "<br><br>";


		$form = "basic_bio_form";
		$output .= '    
		  
				<div id="message_div"></div>
				<div id="form_div">
		  
		    <form id="' . $form . '" name="' . $form . '" class="search">
		      
		     ';
		//      $output.= capi_watc_theme_input($form,"","pmcpid","hidden",$my_pm_contact_process_bll->get_field("pmcpid"));

		$output .= $this->capi_theme_input($form, array("field" => "cid", "input_type" => "hidden", "type" => "s", "default" => $cid));
		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 1));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "updatecontactall"));

		if ($this->capi_update_profile_fields == "") {

			$output .= $this->capi_theme_input($form, $fields_array['first_name']);
			$output .= $this->capi_theme_input($form, $fields_array['last_name']);
			$output .= $this->capi_theme_input($form, $fields_array['email-home']);
			$output .= $this->capi_theme_input($form, $fields_array['number-home']);
			$output .= $this->capi_theme_input($form, $fields_array['number-cell']);

			$output .= $this->capi_theme_input($form, $fields_array['street1-home']);
			$output .= $this->capi_theme_input($form, $fields_array['street2-home']);
			$output .= $this->capi_theme_input($form, $fields_array['city-home']);
			$output .= $this->capi_theme_input($form, $fields_array['state-home']);
			$output .= $this->capi_theme_input($form, $fields_array['zip-home']);
			$output .= $this->capi_theme_input($form, $fields_array['birth_date']);
			$output .= "<br><br>Children<br><br>";

			$max_children = 6;

			for ($i = 1; $i <= $max_children; $i++) {
				$output .= $this->capi_theme_input($form, $fields_array["chcid-$i"]);
				$output .= $this->capi_theme_input($form, $fields_array["child_first_name-$i"]);
				$output .= $this->capi_theme_input($form, $fields_array["child_birth_date-$i"]);
				$output .= "<br>";
			}
		} else {
			// use profile fields defined
			$capi_update_profile_fields_array = explode(",", $this->capi_update_profile_fields);
			foreach ($capi_update_profile_fields_array as $capi_update_profile_field) {
				$capi_update_profile_field = trim(strtolower($capi_update_profile_field));
				if ($capi_update_profile_field == "children") {
					$output .= "<br><br>Children<br><br>";

					$max_children = 6;

					for ($i = 1; $i <= $max_children; $i++) {
						$output .= $this->capi_theme_input($form, $fields_array["chcid-$i"]);
						$output .= $this->capi_theme_input($form, $fields_array["child_first_name-$i"]);
						//						      $output.= $this->capi_theme_input($form,$fields_array["child_birth_date-$i"]);
						$output .= "<br>";
					}
				} else {
					$output .= $this->capi_theme_input($form, $fields_array[$capi_update_profile_field]);
				}
			}
		}
		/*
					if ($this->capi_register_profile_fields=="") {
		
			      $output.= $this->capi_theme_input($form,$fields_array['first_name']);
			      $output.= $this->capi_theme_input($form,$fields_array['last_name']);
			      $output.= $this->capi_theme_input($form,$fields_array['email-home']);
			      $output.= $this->capi_theme_input($form,$fields_array['number-home']);
			      $output.= $this->capi_theme_input($form,$fields_array['number-cell']);
			
			      $output.= $this->capi_theme_input($form,$fields_array['street1-home']);
			      $output.= $this->capi_theme_input($form,$fields_array['street2-home']);
			      $output.= $this->capi_theme_input($form,$fields_array['city-home']);
			      $output.= $this->capi_theme_input($form,$fields_array['state-home']);
			      $output.= $this->capi_theme_input($form,$fields_array['zip-home']);
			      $output.= $this->capi_theme_input($form,$fields_array['birth_date']);
					} else {
						// use profile fields defined
						$capi_register_profile_fields_array=explode(",",$this->capi_register_profile_fields);
						foreach ($capi_register_profile_fields_array as $capi_register_profile_field) {
							$capi_register_profile_field=trim(strtolower($capi_register_profile_field));
			      	$output.= $this->capi_theme_input($form,$fields_array[$capi_register_profile_field]);
						
						}
					}
*/
		$output .= '<div style="height:40px">&nbsp;</div><center><p>
		      <input type=button id=save_button name=save_button value="Save">
					</p>
					
					</center>
		    </form>
				
				</div> <!-- form_div -->
				';



		$output .= '	
	 <script type="text/javascript" src="/capi/jquery.mask.min.js"></script>
		<script>
  
    jQuery(document).ready(function(){
    
		';

		$output .= "
	      jQuery('#save_button').bind('click', capi_save_button_click);
				
        jQuery('#" . $form . "').submit(function(e){
            e.preventDefault();
        });

	 ";



		$output .= ' 
	 
				jQuery("#' . $form . '_number-home").mask("999-999-9999");	 
	    });  // end document ready
       </script>
      ';

		$output .= '
		

		<script>

			
			
			function capi_save_button_click() {
		
				// validate data
				
				validate_array=new Array;

				if (typeof validate_form_' . $form . '  === "function") {

					validate_array=validate_form_' . $form . '();
					
					if (!validate_array["success"]) {
						alert("Validation Errors.");
						jQuery("#message_div").html(validate_array["message"]);
						jQuery("html,body").scrollTop(0);
						return;
					}
				}
				
				';
		$output .= "
//        jQuery('#" . $form . "_operation').val('updatecontactallcomplete');
//				alert(jQuery('#basic_bio_form_birth_date-day').val());
//				alert(jQuery('#basic_bio_form_birth_date').val());
        var formData = jQuery('#" . $form . "').serializeArray();

        jQuery('#message_div').html('Saving');
        jQuery.ajax({
									
        url: '";

		$output .= get_site_url() . "/capi/process";

		$output .= "',
          type: 'POST',
					data: formData,
         dataType: 'json',
          success:  function(data){
						if (data['success']) {
              jQuery('#message_div').html(data['message']);
							alert('Save Complete');
//              jQuery('#form_div').html('Save Complete.');//  <a href=/capi/member_home>Return to the Dashboard</a>');
							location.reload();
						} else {
							alert('Error. '+data['messsage']);
              jQuery('#message_div').html(data['message']);
						}
          },
          error: function( jqXHR, textStatus, errorThrown){
             console.log(jqXHR.responseText);
              alert('failure: ' + textStatus + ' - ' + errorThrown);
          }   
        });        
      ";
		$output .= '
	
			}


		</script>

		';

		//				$output.=capi_watc_input_form_javascript($form);

		/*		
				$output.='
				<script>
		
				'.capi_watc_validate_form_basic_bio_form().'
		
				</script>';
*/



		return $output;

		//		$result="<textarea>".print_r($fields_array,false)."</textarea>";

		//		$query="module=event&operation=get_my_registrations&evoid=$evoid";

		//		$result=$this->capi_parse_xml2($this->capi_process_api($query));
		//		$result="<textarea>".print_r($this->capi_process_api($query),false)."</textarea>";

		return $result;

		//return print_r($result,true);
		$result = $result["result"];

		if ($result['success'])
			return "Registration Complete. <a href='" . get_site_url() . "/capi/tournament_schedule'>Click here to return to the schedule</a>.";
		else
			return "Error. " . $result['message'];
	} // end capi_update_profile


	function capi_change_password()
	{

		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		//		$cid=$_SESSION["cid"];
		$cid = $this->capi_check_profile();

		$output = "";


		$output = "";

		$wiu_user_name = "";
		$wiu_password = "";
		$wiu_repeatpassword = "";
		$current_user = wp_get_current_user();
		$wiu_user_name = $current_user->user_login;
		$wiwid = $this->wiwid;

		foreach ($_REQUEST as $key => $value) ${$key} = $value;

		$query = "module=website_integration&operation=changepassword&wiwid=$wiwid&wiu_user_name=$wiu_user_name&capi_login_destination=" . $this->capi_login_destination;

		if ($wiu_password > "" && $wiu_repeatpassword > "")
			$query .= "&wiu_password=$wiu_password&wiu_repeatpassword=$wiu_repeatpassword";

		$output .= $this->capi_process_api($query);
		$output .= "</div>";



		return $output;
	} // end capi_change_password

	function capi_update_profile_submit()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}

		$query = "module=event&operation=get_my_registrations&evoid=$evoid";

		//		$result=$this->capi_parse_xml2($this->capi_process_api($query));
		$result = "<textarea>" . print_r($this->capi_process_api($query), false) . "</textarea>";

		return $result;

		//return print_r($result,true);
		$result = $result["result"];

		if ($result['success'])
			return "Registration Complete. <a href='" . get_site_url() . "/capi/tournament_schedule'>Click here to return to the schedule</a>.";
		else
			return "Error. " . $result['message'];
	} // end capi_update_profile_submit

	function capi_email_registrants()
	{
		return "Email Registrants";
	}

	function capi_registrations()
	{

		$evoid = 0;
		$registered_status_array = array(
			0 => "Cancelled",
			1 => "Registered",
			2 => "Wait Listed"
		);

		foreach ($_REQUEST as $key => $value) ${$key} = $value;

		if ($evoid == 0) {
			return "Invalid event.";
		}

		$output = "<h2>Registrants</h2>";

		if (current_user_can('administrator') || current_user_can('editor')) {
			$output .= "<a class='capi_button_link' href='" . get_site_url() . "/capi/registrations_download/?evoid=$evoid' target='_blank'>Download</a><br>";
		}

		$query = "module=event&operation=get_event_registrants&evoid=$evoid";


		$result = $this->capi_process_api($query);
		$result = $this->capi_parse_xml2($result);
		$result = $result["result"];

		if (isset($result['registrants']['registrant'][0]))
			$result['registrants'] = $result['registrants']['registrant'];
		//$output.=print_r($result,true);
		//$output.="end of results";
		//return $output;
		$output .= "<table>\n";
		foreach ($result['registrants'] as $registrant) {
			$output .= "<tr>\n";
			$output .= "<td>\n";
			$output .= $registrant['first_name'] . " " . $registrant['last_name'];
			$output .= "</td>\n";

			if (isset($registrant["partners"])) {
				if (isset($registrant["partners"]["partner"][0]))
					$registrant["partners"] = $registrant["partners"]["partner"];
				if (is_array($registrant["partners"]))
					foreach ($registrant["partners"] as $partner) {
						$output .= "<td>\n";
						$output .= $partner['first_name'] . " " . $partner['last_name'];
						$output .= "</td>\n";
					}
			}

			// look for registration_type
			$registration_type = "";
			if (isset($registrant['form_fields'])) {

				$form_fields = $registrant['form_fields'];

				if (is_array($form_fields)) {
					foreach ($form_fields as $form_field) {
						if (isset($form_field['eff_name'])) {
							if ($form_field['eff_name'] == 'registration_type') {
								$registration_type = $form_field['erfv_value'];
							}
						}
					}
				}
			}


			$output .= "<td>\n";
			$output .= $registered_status_array[$registrant['registered_status']];
			$output .= "</td>\n";

			if ($registration_type > "") {
				$output .= "<td>\n";
				$output .= $registration_type;
				$output .= "</td>\n";
			}

			if (current_user_can('administrator') || current_user_can('editor')) {
				$output .= "<td>\n";
				if ($registrant['checked_in']) {
					$output .= '
							<input type="button" id="check_in-' . $registrant['cecid'] . '" name="check_in" class="check_in_button" data-cecid="' . $registrant['cecid'] . '" value="Check Out" />
				   ';
				} else {
					$output .= '
							<input type="button" id="check_in-' . $registrant['cecid'] . '" name="check_in" class="check_in_button" data-cecid="' . $registrant['cecid'] . '" value="Check In" />
				   ';
				}
				$output .= "</td>\n";
			}


			$output .= "</tr>\n";
		}
		$output .= "</table>\n";
		$name = "check_in_form";
		$output .= "<script>\n";
		$output .= "
	    jQuery('.check_in_button').bind('click', check_in);
	  ";
		$output .= "
	        function check_in() {
					
//						alert('here');

						var \$this = jQuery(this);
						
						cecid = \$this.data('cecid');
						button_text=jQuery('#check_in-'+cecid).val();
						
//						alert('#check_in-'+cecid);

						jQuery('#check_in-'+cecid).hide();

						

						";


		$output .= "					
	

						
	          jQuery.ajax({
						";

		$output .= '					
					data:{cecid:cecid,button_text:button_text},
					url:"';

		$output .= get_site_url() . '/capi/do_check_in/",';

		$output .= "
	            type: 'GET',
		         dataType: 'json',
	            success:  function(data){
//                  alert(data['checked_in']);
								if (data['success']) {
									if (data['checked_in']) {
										jQuery('#check_in-'+cecid).prop('value','Check Out');
									} else {
										jQuery('#check_in-'+cecid).prop('value','Check In');
									}
									jQuery('#check_in-'+cecid).show();
								} else {
									alert(data['message']);
								}

	            },
	            error: function( jqXHR, textStatus, errorThrown){
	               console.log(jqXHR.responseText);
	                alert('failure: ' + textStatus + ' - ' + errorThrown + ' - ' + console.log(jqXHR.responseText));
	            }   
	          });
						";



		$output .= "      
	        
	       }
		
		";

		$output .= "</script>\n";

		//		$output.="<textarea>$result</textarea>";
		return $output;
	}

	function capi_do_check_in()
	{


		$cecid = 0;
		$button_text = "none";
		$success = 1;
		$message = "";
		$checked_in = 0;

		foreach ($_REQUEST as $key => $value)
			${$key} = $value;

		if (!current_user_can('administrator') && !current_user_can('editor')) {
			$message = "Security Violation";
			$success = 0;
		}

		if ($button_text != "Check In" && $button_text != "Check Out") {
			$message = "Error. Invalid button. $button_text";
			$success = 0;
		} else if ($success) {

			if ($button_text == "Check In") $checked_in = 0;
			else $checked_in = 1;

			$query = "module=event&operation=do_check_in&cecid=$cecid&checked_in=$checked_in&ajax=1";

			$result = $this->capi_process_api($query);
			$result = json_decode($result, true);

			$success = $result['success'];
			$checked_in = $result['checked_in'];
			$message = $result['message'];

			//			$message=print_r($result,true);

		}

		print json_encode(array(
			'success' => $success,
			'message' => $message,
			'checked_in' => $checked_in,
		));
		exit;
	}
	function capi_registrations_download()
	{

		$file_name = "registrants.csv";
		header('Content-Type: application/csv');
		header("Content-Disposition: attachment; filename=$file_name");
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');


		$evoid = 0;

		foreach ($_REQUEST as $key => $value) ${$key} = $value;

		if ($evoid == 0) {
			print "Invalid event.";
			exit();
		}


		if (!current_user_can('administrator') && !current_user_can('editor')) {
			print "Security Violation";
			exit();
		}

		$query = "module=event&operation=get_event_registrants&evoid=$evoid";


		$result = $this->capi_process_api($query);
		$result = $this->capi_parse_xml2($result);
		$result = $result["result"];
		//print_r($result);

		if (isset($result['partner_count']))
			$partner_count = $result['partner_count'];
		else
			$partner_count = 0;


		if (isset($result['registrants']['registrant'][0]))
			$result['registrants'] = $result['registrants']['registrant'];

		if (isset($result['registrants'][0]['form_fields']))
			$form_fields = $result['registrants'][0]['form_fields'];
		else if (isset($result['registrants']['registrant']['form_fields']))
			$form_fields = $result['registrants']['registrant']['form_fields'];
		else
			$form_fields = array();

		if (isset($form_fields['form_field'][0]))
			$form_fields = $form_fields['form_field'];



		//print_r($form_fields);

		$status_array = array(
			"0" => "Cancelled",
			"1" => "Active",
			"2" => "Wait Listed",
		);

		print 'first_name';
		print ",";
		print 'last_name';
		print ",";
		print 'email';
		print ",";
		print 'handicap_number';
		print ",";
		print 'club_name';
		print ",";
		print 'club_member_number';
		print ",";
		print 'registered_status';
		print ",";
		print 'registered_date';
		print ",";
		print 'registered_time';
		print ",";
		print 'checked_in';

		// look through other fields

		if (is_array($form_fields)) {
			foreach ($form_fields as $form_field) {
				print ",";
				print $form_field['eff_name'];
			}
		}

		// loop through partners

		for ($i = 1; $i <= $partner_count; $i++) {
			print ",";
			print "partner_" . $i . "_first_name";
			print ",";
			print "partner_" . $i . "_last_name";
			print ",";
			print "partner_" . $i . "_email";
			print ",";
			print "partner_" . $i . "_handicap_number";
			print ",";
			print "partner_" . $i . "_club_name";
			print ",";
			print "partner_" . $i . "_club_member_number";
		}



		print "\n";

		foreach ($result['registrants'] as $registrant) {
			print $registrant['first_name'];
			print ",";
			print $registrant['last_name'];
			print ",";
			print $registrant['email'];
			print ",";
			print $registrant['handicap_number'];
			print ",";
			print $registrant['club_name'];
			print ",";
			print $registrant['club_member_number'];
			print ",";
			print $status_array[$registrant['registered_status']];
			print ",";
			print $registrant['registered_date'];
			print ",";
			print $registrant['registered_time'];
			print ",";
			print $registrant['checked_in'];

			$registrant_form_fields = $registrant['form_fields'];
			if (isset($registrant_form_fields['form_field'][0]))
				$registrant_form_fields = $registrant_form_fields['form_field'];

			if (is_array($form_fields)) {
				foreach ($form_fields as $form_field) {
					print ",";
					foreach ($registrant_form_fields as $registrant_form_field) {
						if (isset($registrant_form_field['eff_name'])) {
							if ($registrant_form_field['eff_name'] == $form_field['eff_name'])
								print $registrant_form_field['erfv_value'];
						} else {
							print_r($registrant_form_field);
						}
					}
				}
			}

			if (isset($registrant["partners"])) {
				if (isset($registrant["partners"]["partner"][0]))
					$registrant["partners"] = $registrant["partners"]["partner"];
				if (is_array($registrant["partners"]))
					foreach ($registrant["partners"] as $partner) {
						print ",";
						print $partner['first_name'];
						print ",";
						print $partner['last_name'];
						print ",";
						print $partner['email'];
						print ",";
						print $partner['handicap_number'];
						print ",";
						print $partner['club_name'];
						print ",";
						print $partner['club_member_number'];
					}
			}



			print "\n";
		}
		exit();
	}

	function capi_registrations_download_xml()
	{

		//			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//			header('Content-Type: text/xml');
		//	header('Content-Length: '.strlen( $output ));
		//			header('Content-disposition: inline; filename="registrants.xml"');
		header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition: attachment;filename=\"registrants.xml\"");
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		$output = "";
		print "<?xml version=\"1.0\"?>\n
<?mso-application progid=\"Excel.Sheet\"?>\n
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"\n
 xmlns:o=\"urn:schemas-microsoft-com:office:office\"\n
 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"\n
 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"\n
 xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n
 <DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\">\n
  <Author>Command</Author>\n
  <LastAuthor>Command</LastAuthor>\n
  <Created>2018-02-20T14:38:42Z</Created>\n
  <LastSaved>2018-02-20T14:44:31Z</LastSaved>\n
  <Company>Command</Company>\n
  <Version>14.00</Version>\n
 </DocumentProperties>\n
 <OfficeDocumentSettings xmlns=\"urn:schemas-microsoft-com:office:office\">\n
  <AllowPNG/>\n
 </OfficeDocumentSettings>\n
 <ExcelWorkbook xmlns=\"urn:schemas-microsoft-com:office:excel\">\n
  <WindowHeight>6675</WindowHeight>\n
  <WindowWidth>20595</WindowWidth>\n
  <WindowTopX>600</WindowTopX>\n
  <WindowTopY>600</WindowTopY>\n
  <ProtectStructure>False</ProtectStructure>\n
  <ProtectWindows>False</ProtectWindows>\n
 </ExcelWorkbook>\n
 <Styles>\n
  <Style ss:ID=\"Default\" ss:Name=\"Normal\">\n
   <Alignment ss:Vertical=\"Bottom\"/>\n
   <Borders/>\n
   <Font ss:FontName=\"Calibri\" x:Family=\"Swiss\" ss:Size=\"11\" ss:Color=\"#000000\"/>\n
   <Interior/>\n
   <NumberFormat/>\n
   <Protection/>\n
  </Style>\n
 </Styles>\n
 <Worksheet ss:Name=\"Command\">
  <Table>
<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>
		";

		$evoid = 0;

		foreach ($_REQUEST as $key => $value) ${$key} = $value;

		if ($evoid == 0) {
			print "Invalid event.";
			exit();
		}

		if (!current_user_can('administrator') && !current_user_can('editor')) {
			print "Security Violation";
			exit();
		}

		$query = "module=event&operation=get_event_registrants&evoid=$evoid";


		$result = $this->capi_process_api($query);
		$result = $this->capi_parse_xml2($result);
		$result = $result["result"];

		if (isset($result['registrants']['registrant'][0]))
			$result['registrants'] = $result['registrants']['registrant'];


		print "<Row>\n";
		print $this->cell('first_name');
		print $this->cell('last_name');
		print $this->cell('email');
		print $this->cell('handicap_number');
		print $this->cell('club_member_number');
		print $this->cell('street1');
		print $this->cell('street2');
		print $this->cell('city');
		print $this->cell('state');
		print $this->cell('zip');
		print $this->cell('registered_status');
		print "</Row>\n";

		foreach ($result['registrants'] as $registrant) {
			print "<Row>\n";
			print $this->cell($registrant['first_name']);
			print $this->cell($registrant['last_name']);
			print $this->cell($registrant['email']);
			print $this->cell($registrant['handicap_number']);
			print $this->cell($registrant['club_name']);
			print $this->cell($registrant['club_member_number']);
			print $this->cell($registrant['street1']);
			print $this->cell($registrant['street2']);
			print $this->cell($registrant['city']);
			print $this->cell($registrant['state']);
			print $this->cell($registrant['zip']);
			print $this->cell($registrant['registered_status']);
			print "</Row>\n";
		}
		print "</Table>\n
		</Worksheet>\n
		</Workbook>\n
		";
		exit();
	}


	function capi_manage_membership()
	{
		return "Manage Membership";
	}

	function capi_manage_payment_methods()
	{
		$query = "module=ecommerce&operation=getpaymentinstrument_form";
		$result = "<h2>Manage Payment Method</h2>";

		$result .= $this->capi_process_api($query);
		return $result;
	}

	function capi_edit_contact_box()
	{
		if (!current_user_can('administrator') && !current_user_can('editor')) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		$data = ($this->capi_process_api("operation=get_contact_box_content&module=website_integration", 0, 0));
		$data = str_replace("<result>", "", $data);
		$data = str_replace("</result>", "", $data);
		//			$result=$this->capi_parse_xml2($data);
		//			$result=$result['result'];


		$output = "<h2>Edit Contact Box</h2>";
		//	$output.="result".print_r($result,true);


		$form = "edit_contact_box_form";
		$output .= '    
			  
					<div id="message_div"></div>
					<div id="form_div">
			  
			    <form id="' . $form . '" name="' . $form . '" class="search">
			      
			     ';
		//      $output.= capi_watc_theme_input($form,"","pmcpid","hidden",$my_pm_contact_process_bll->get_field("pmcpid"));

		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 1));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "update_contact_box_content"));
		$output .= $this->capi_theme_input($form, array("field" => "module", "input_type" => "hidden", "type" => "s", "default" => "website_integration"));

		//						$output.="[capi_edit_contact_box_editor]";

		$fieldspec = array(
			'label' => 'Contact Box',
			'field' => 'contact_box',
			'class' => '',
			'type' => 's',
			'input_type' => 'textarea',
			'length' => 1500,
			'default' => $data,
			'mandatory' => true,
			'classes' => '',
			'options' => null,
			'help_text' => ''
		);
		$output .= $this->capi_theme_input($form, $fieldspec);

		$output .= '<div style="height:40px">&nbsp;</div><center><p>
			      <input type=button id=save_button name=save_button value="Save">
						</p>
						
						</center>
			    </form>
					
					</div> <!-- form_div -->
					';

		$output .= '	
		<script>
  
    jQuery(document).ready(function(){
    
		';

		$output .= "
	      jQuery('#save_button').bind('click', capi_save_button_click);
				
        jQuery('#" . $form . "').submit(function(e){
            e.preventDefault();
        });

	 ";



		$output .= ' 
	 
	 
	    });  // end document ready
       </script>
      ';

		$output .= '
		

		<script>

			
			
			function capi_save_button_click() {
			
				// validate data
				
				validate_array=new Array;

				if (typeof validate_form_' . $form . '  === "function") {

					validate_array=validate_form_' . $form . '();
					
					if (!validate_array["success"]) {
						alert("Validation Errors.");
						jQuery("#message_div").html(validate_array["message"]);
						jQuery("html,body").scrollTop(0);
						return;
					}
				}
				
				';
		$output .= "
//        jQuery('#" . $form . "_operation').val('updatecontactallcomplete');
//				alert(jQuery('#basic_bio_form_birth_date-day').val());
//				alert(jQuery('#basic_bio_form_birth_date').val());
        var formData = jQuery('#" . $form . "').serializeArray();

        jQuery('#message_div').html('Saving');
        jQuery.ajax({
									
        url: '";

		$output .= get_site_url() . "/capi/process";

		$output .= "',
          type: 'POST',
					data: formData,
         dataType: 'json',
          success:  function(data){
						if (data['success']) {
              jQuery('#message_div').html(data['message']);
              jQuery('#form_div').html('Save Complete.');//  <a href=/capi/member_home>Return to the Dashboard</a>');
						} else {
							alert('Error. '+data['messsage']);
              jQuery('#message_div').html(data['message']);
						}
          },
          error: function( jqXHR, textStatus, errorThrown){
             console.log(jqXHR.responseText);
              alert('failure: ' + textStatus + ' - ' + errorThrown);
          }   
        });        
      ";
		$output .= '
	
			}


		</script>

		';

		//			add_action("the_post","capi_load_custom_wp_tiny_mce");
		error_log("capi_load_custom_wp_tiny_mce action added");

		return $output;
	}


	function capi_get_contact_box_content()
	{

		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		$data = ($this->capi_process_api("operation=get_contact_box_content&module=website_integration", 0, 0));
		//			$result=$this->capi_parse_xml2($data);
		//			$result=$result['result'];

		$data = str_replace("<result>", "", $data);
		$data = str_replace("</result>", "", $data);
		return $data;
	}

	function get_member_directory()
	{
		$admin_key = "";
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		if (!current_user_can('administrator') && !current_user_can('editor')) {
			$admin_key = "xdkejjdkseleiii";
		}

		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		$data = ($this->capi_process_api("operation=searchmemberdirectory&module=member_directory&admin_key=$admin_key&show_member_detail=admin_only", 0, 0));
		$result = $this->capi_parse_xml2($data);
		$result = $result['result'];
	}

	function capi_edit_join_box()
	{
		if (!current_user_can('administrator') && !current_user_can('editor')) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		$data = ($this->capi_process_api("operation=get_join_box_content&module=website_integration", 0, 0));
		$data = str_replace("<result>", "", $data);
		$data = str_replace("</result>", "", $data);
		//			$result=$this->capi_parse_xml2($data);
		//			$result=$result['result'];


		$output = "<h2>Edit join Box</h2>";
		//	$output.="result".print_r($result,true);


		$form = "edit_join_box_form";
		$output .= '    
			  
					<div id="message_div"></div>
					<div id="form_div">
			  
			    <form id="' . $form . '" name="' . $form . '" class="search">
			      
			     ';
		//      $output.= capi_watc_theme_input($form,"","pmcpid","hidden",$my_pm_join_process_bll->get_field("pmcpid"));

		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 1));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "update_join_box_content"));
		$output .= $this->capi_theme_input($form, array("field" => "module", "input_type" => "hidden", "type" => "s", "default" => "website_integration"));

		//						$output.="[capi_edit_join_box_editor]";

		$fieldspec = array(
			'label' => 'join Box',
			'field' => 'join_box',
			'class' => '',
			'type' => 's',
			'input_type' => 'textarea',
			'length' => 1500,
			'default' => $data,
			'mandatory' => true,
			'classes' => '',
			'options' => null,
			'help_text' => ''
		);
		$output .= $this->capi_theme_input($form, $fieldspec);

		$output .= '<div style="height:40px">&nbsp;</div><center><p>
			      <input type=button id=save_button name=save_button value="Save">
						</p>
						
						</center>
			    </form>
					
					</div> <!-- form_div -->
					';

		$output .= '	
		<script>
  
    jQuery(document).ready(function(){
    
		';

		$output .= "
	      jQuery('#save_button').bind('click', capi_save_button_click);
				
        jQuery('#" . $form . "').submit(function(e){
            e.preventDefault();
        });

	 ";



		$output .= ' 
	 
	 
	    });  // end document ready
       </script>
      ';

		$output .= '
		

		<script>

			
			
			function capi_save_button_click() {
			
				// validate data
				
				validate_array=new Array;

				if (typeof validate_form_' . $form . '  === "function") {

					validate_array=validate_form_' . $form . '();
					
					if (!validate_array["success"]) {
						alert("Validation Errors.");
						jQuery("#message_div").html(validate_array["message"]);
						jQuery("html,body").scrollTop(0);
						return;
					}
				}
				
				';
		$output .= "
//        jQuery('#" . $form . "_operation').val('updatejoinallcomplete');
//				alert(jQuery('#basic_bio_form_birth_date-day').val());
//				alert(jQuery('#basic_bio_form_birth_date').val());
        var formData = jQuery('#" . $form . "').serializeArray();

        jQuery('#message_div').html('Saving');
        jQuery.ajax({
									
        url: '";

		$output .= get_site_url() . "/capi/process";

		$output .= "',
          type: 'POST',
					data: formData,
         dataType: 'json',
          success:  function(data){
						if (data['success']) {
              jQuery('#message_div').html(data['message']);
              jQuery('#form_div').html('Save Complete.');//  <a href=/capi/member_home>Return to the Dashboard</a>');
						} else {
							alert('Error. '+data['messsage']);
              jQuery('#message_div').html(data['message']);
						}
          },
          error: function( jqXHR, textStatus, errorThrown){
             console.log(jqXHR.responseText);
              alert('failure: ' + textStatus + ' - ' + errorThrown);
          }   
        });        
      ";
		$output .= '
	
			}


		</script>

		';


		return $output;

		//		$result="<textarea>".print_r($fields_array,false)."</textarea>";

		//		$query="module=event&operation=get_my_registrations&evoid=$evoid";

		//		$result=$this->capi_parse_xml2($this->capi_process_api($query));
		//		$result="<textarea>".print_r($this->capi_process_api($query),false)."</textarea>";


		return $result;
	}

	function capi_get_join_box_content()
	{

		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		$data = ($this->capi_process_api("operation=get_join_box_content&module=website_integration", 0, 0));
		$data = str_replace("<result>", "", $data);
		$data = str_replace("</result>", "", $data);
		//			$result=$this->capi_parse_xml2($data);
		//			$result=$result['result'];
		$output = trim($data);
		if ($output == "") {
			// get available memberships
			$data = ($this->capi_process_api("operation=get_available_club_membership_links&module=online_membership", 0, 0));
			$result = $this->capi_parse_xml2($data);
			$result = $result['result'];
			if (!$result['success']) {
				$output = "Error. " . $result['message'];
			} else {
				$memberships = $result["memberships"];
				if (isset($memberships["membership"][0])) $memberships = $memberships["membership"];
				if (sizeof($memberships) > 0) {
					foreach ($memberships as $membership) {
						$output .= "<div class='capi-membership-p'><a href='" . $membership['join_link'] . "' target='_blank'>" . $membership['om_name'] . "</a></div>";
					}
				} else {
					$output = "Please contact the club.";
				}
			} // end else result success
		} // end if output==""
		//$output=$data;			
		//$output.=print_r($memberships,true);
		return $output;
	}


	function capi_member_benefits()
	{
		$output = "<h2>Member Benefits</h2>";
		$data = ($this->capi_process_api("operation=get_member_benefits_box_content&module=website_integration", 0, 0));
		$data = str_replace("<result>", "", $data);
		$data = str_replace("</result>", "", $data);
		$output .= $data;
		return $output;
	}

	function capi_contact_us()
	{
		$output = "<h2>Contact Us</h2>";

		$data = ($this->capi_process_api("operation=get_contact_box_content&module=website_integration", 0, 0));
		$data = str_replace("<result>", "", $data);
		$data = str_replace("</result>", "", $data);
		$output .= $data;

		$output .= $this->capi_contact_us_form();

		return $output;
	}

	function capi_edit_member_benefits_box()
	{
		if (!current_user_can('administrator') && !current_user_can('editor')) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		$data = ($this->capi_process_api("operation=get_member_benefits_box_content&module=website_integration", 0, 0));
		$data = str_replace("<result>", "", $data);
		$data = str_replace("</result>", "", $data);
		//			$result=$this->capi_parse_xml2($data);
		//			$result=$result['result'];


		$output = "<h2>Edit Member Benefits Box</h2>";
		//	$output.="result".print_r($result,true);


		$form = "edit_member_benefits_box_form";
		$output .= '    
			  
					<div id="message_div"></div>
					<div id="form_div">
			  
			    <form id="' . $form . '" name="' . $form . '" class="search">
			      
			     ';
		//      $output.= capi_watc_theme_input($form,"","pmcpid","hidden",$my_pm_member_benefits_process_bll->get_field("pmcpid"));

		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 1));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "update_member_benefits_box_content"));
		$output .= $this->capi_theme_input($form, array("field" => "module", "input_type" => "hidden", "type" => "s", "default" => "website_integration"));

		//						$output.="[capi_edit_member_benefits_box_editor]";

		$fieldspec = array(
			'label' => 'member_benefits Box',
			'field' => 'member_benefits_box',
			'class' => '',
			'type' => 's',
			'input_type' => 'textarea',
			'length' => 1500,
			'default' => $data,
			'mandatory' => true,
			'classes' => '',
			'options' => null,
			'help_text' => ''
		);
		$output .= $this->capi_theme_input($form, $fieldspec);

		$output .= '<div style="height:40px">&nbsp;</div><center><p>
			      <input type=button id=save_button name=save_button value="Save">
						</p>
						
						</center>
			    </form>
					
					</div> <!-- form_div -->
					';

		$output .= '	
		<script>
  
    jQuery(document).ready(function(){
    
		';

		$output .= "
	      jQuery('#save_button').bind('click', capi_save_button_click);
				
        jQuery('#" . $form . "').submit(function(e){
            e.preventDefault();
        });

	 ";



		$output .= ' 
	 
	 
	    });  // end document ready
       </script>
      ';

		$output .= '
		

		<script>

			
			
			function capi_save_button_click() {
			
				// validate data
				
				validate_array=new Array;

				if (typeof validate_form_' . $form . '  === "function") {

					validate_array=validate_form_' . $form . '();
					
					if (!validate_array["success"]) {
						alert("Validation Errors.");
						jQuery("#message_div").html(validate_array["message"]);
						jQuery("html,body").scrollTop(0);
						return;
					}
				}
				
				';
		$output .= "
//        jQuery('#" . $form . "_operation').val('updatemember_benefitsallcomplete');
//				alert(jQuery('#basic_bio_form_birth_date-day').val());
//				alert(jQuery('#basic_bio_form_birth_date').val());
        var formData = jQuery('#" . $form . "').serializeArray();

        jQuery('#message_div').html('Saving');
        jQuery.ajax({
									
        url: '";

		$output .= get_site_url() . "/capi/process";

		$output .= "',
          type: 'POST',
					data: formData,
         dataType: 'json',
          success:  function(data){
						if (data['success']) {
              jQuery('#message_div').html(data['message']);
              jQuery('#form_div').html('Save Complete.');//  <a href=/capi/member_home>Return to the Dashboard</a>');
						} else {
							alert('Error. '+data['messsage']);
              jQuery('#message_div').html(data['message']);
						}
          },
          error: function( jqXHR, textStatus, errorThrown){
             console.log(jqXHR.responseText);
              alert('failure: ' + textStatus + ' - ' + errorThrown);
          }   
        });        
      ";
		$output .= '
	
			}


		</script>

		';


		return $output;

		//		$result="<textarea>".print_r($fields_array,false)."</textarea>";

		//		$query="module=event&operation=get_my_registrations&evoid=$evoid";

		//		$result=$this->capi_parse_xml2($this->capi_process_api($query));
		//		$result="<textarea>".print_r($this->capi_process_api($query),false)."</textarea>";


		return $result;
	}


	function capi_get_member_benefits_box_content()
	{

		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		$data = ($this->capi_process_api("operation=get_member_benefits_box_content&module=website_integration", 0, 0));
		$data = str_replace("<result>", "", $data);
		$data = str_replace("</result>", "", $data);
		//return "<textarea>$data</textarea>";
		//			$result=$this->capi_parse_xml2($data);
		//			$result=$result['result'];
		//			return $result['member_benefits_box'];
		return $data;
	}

	function capi_register()
	{

		if (!isset($_SESSION))
			session_start();

		$data = ($this->capi_process_api("operation=registration_form&module=website_integration&page_display=0", 0, 0));
		$data = str_replace("<result>", "", $data);
		$data = str_replace("</result>", "", $data);
		//return "<textarea>$data</textarea>";
		//			$result=$this->capi_parse_xml2($data);
		//			$result=$result['result'];
		//			return $result['member_benefits_box'];
		return $data;
	}


	function capi_admin()
	{
		//return phpInfo();
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}
		if (!current_user_can('administrator') && !current_user_can('editor')) {
			return "You must be admin in to see this page.";
		}

		$result = "<h2>ClubsitePro Administration</h2>";
		$result .= "<div class='capi_admin_div'>";
		/*
		$result.="<div class='capi_button_link'>";
		$result.="<a class='capi_button_link' href='".get_site_url()."/capi/mass_subscribe'>Mass Subscribe</a>";
		$result.="</div>";
*/
		$result .= "<div class='capi_button_link'>";
		$result .= "<a class='capi_button_link' href='" . get_site_url() . "/capi/send_email'>Broadcast Email</a>";
		$result .= "</div>";
		$result .= "<div class='capi_button_link'>";
		$result .= "<a class='capi_button_link' href='" . get_site_url() . "/capi/list_emails'>Email History</a>";
		$result .= "</div>";
		//		$result.="<div class='capi_button_link'>";
		//		$result.="<a class='capi_button_link' href='".get_site_url()."/capi/send_text_message'>Broadcast Text</a>";
		//		$result.="</div>";
		$result .= "<div class='capi_button_link'>";
		$result .= "<a class='capi_button_link' href='" . get_site_url() . "/capi/manage_site_subscriptions'>Broadcast Groups</a>";
		$result .= "</div>";
		$result .= "<div class='capi_button_link'>";
		$result .= "<a class='capi_button_link' href='" . get_site_url() . "/capi/manage_commerce'>Manage Ecommerce Gateway</a>";
		$result .= "</div>";
		/*
		$result.="<div class='capi_button_link'>";
		$result.="<a class='capi_button_link' href='".get_site_url()."/capi/edit_contact_box'>Edit Contact Box</a>";
		$result.="</div>";
		$result.="<div class='capi_button_link'>";
		$result.="<a class='capi_button_link' href='".get_site_url()."/capi/edit_join_box'>Edit Join Box</a>";
		$result.="</div>";
		$result.="<div class='capi_button_link'>";
		$result.="<a class='capi_button_link' href='".get_site_url()."/capi/edit_member_benefits_box'>Edit Member Benefits Box</a>";
		$result.="</div>";
	*/
		$result .= "<div class='capi_button_link'>";
		$result .= "<a class='capi_button_link' href='https://clubsitepro.com/videos/' target='_blank'>Get Help</a>";
		$result .= "</div>";
		$result .= "</div>";
		return $result;
		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		$query = "module=member_directory&operation=getmemberdirectory&mdid=1";

		$result = ($this->capi_process_api($query));
		return $result;
		return print_r($result, true);
		$result = $result["result"];

		if ($result['success'])
			return "Registration Complete. <a href='" . get_site_url() . "/capi/tournament_schedule'>Click here to return to the schedule</a>.";
		else
			return "Error. " . $result['message'];
	} // end capi_admin


	function capi_email_preferences()
	{

		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$edit_cid = 0;

		foreach ($_REQUEST as $key => $value)
			${$key} = $value;


		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		//		$cid=$_SESSION["cid"];
		$cid = $this->capi_check_profile();

		if ($edit_cid > 0) $cid = $edit_cid;

		if ($cid == 0) {
			return "Login Expired";
		}

		$query = "operation=get_subcription_list&delivery_type=0";

		$result = "";

		$result .= ($this->capi_process_api($query));

		$result = $this->capi_parse_xml2($this->capi_process_api($query));
		$subscriptions = $result["result"]["subscriptions"];
		if (isset($subscriptions["subscription"][0])) $subscriptions = $subscriptions["subscription"];

		// get subscriptions for this user.

		$query = "operation=get_contact_subcription&delivery_type=0";
		$result = "";
		$result .= ($this->capi_process_api($query));

		$result = $this->capi_parse_xml2($this->capi_process_api($query));
		$contact_subscriptions = $result["result"]["subscriptions"];
		if (isset($contact_subscriptions["subscription"][0])) $contact_subscriptions = $contact_subscriptions["subscription"];

		// put in an sid array
		$sid = array();
		foreach ($contact_subscriptions as $subscription) {
			if ($subscription['subscription_type'] > 0 && $subscription['subscription_type'] < 4) {
				$sid[$subscription['sid']] = 1;
			}
		}



		$output = "<h2>Email Preferences</h2>";
		$output .= "<br><br>";
		//$output.="<br>subs:".print_r($contact_subscriptions,true)."<br>sid:".print_r($sid,true)."<br>";
		$form = "basic_bio_form";
		$output .= '    
		  
				<div id="message_div"></div>
				<div id="form_div">
		  
		    <form id="' . $form . '" name="' . $form . '" class="search">
		      
		     ';
		//      $output.= capi_watc_theme_input($form,"","pmcpid","hidden",$my_pm_contact_process_bll->get_field("pmcpid"));

		$output .= $this->capi_theme_input($form, array("field" => "cid", "input_type" => "hidden", "type" => "s", "default" => $cid));
		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 1));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "save_site_contact_subscription"));
		$sid_list = "";
		foreach ($subscriptions as $subscription) {

			$sid_list .= "," . $subscription['sid'];
			if (isset($sid[$subscription['sid']])) $default = 1;
			else $default = 0;
			$output .= $this->capi_theme_input($form, array(
				'label' => $subscription['subscription_name'],
				'field' => 'sid-' . $subscription['sid'],
				'input_type' => 'checkbox',
				'default' => $default,
			));
		}
		$sid_list = ltrim($sid_list, ",");

		$output .= "<input type='hidden' name='sid_list' value='$sid_list'>";
		$output .= '<div style="height:40px">&nbsp;</div><center><p>
		      <input type=button id=save_button name=save_button value="Save">
					</p>
					
					</center>
		    </form>
				
				</div> <!-- form_div -->
				';



		$output .= '	
	 <script type="text/javascript" src="/capi/jquery.mask.min.js"></script>
		<script>
  
    jQuery(document).ready(function(){
    
		';

		$output .= "
	      jQuery('#save_button').bind('click', capi_save_button_click);
				
        jQuery('#" . $form . "').submit(function(e){
            e.preventDefault();
        });

	 ";



		$output .= ' 
	 
				jQuery("#' . $form . '_number-home").mask("999-999-9999");	 
	    });  // end document ready
       </script>
      ';

		$output .= '
		

		<script>

			
			
			function capi_save_button_click() {
		
				
				';
		$output .= "
//        jQuery('#" . $form . "_operation').val('updatecontactallcomplete');
//				alert(jQuery('#basic_bio_form_birth_date-day').val());
//				alert(jQuery('#basic_bio_form_birth_date').val());
        var formData = jQuery('#" . $form . "').serializeArray();

        jQuery('#message_div').html('Saving');
        jQuery.ajax({
									
        url: '";

		$output .= get_site_url() . "/capi/process";

		$output .= "',
          type: 'POST',
					data: formData,
         dataType: 'json',
          success:  function(data){
						if (data['success']) {
              jQuery('#message_div').html(data['message']);
//							alert('Save Complete');
//              jQuery('#form_div').html('Save Complete.');//  <a href=/capi/member_home>Return to the Dashboard</a>');
//							location.reload();
						} else {
							alert('Error. '+data['messsage']);
              jQuery('#message_div').html(data['message']);
						}
          },
          error: function( jqXHR, textStatus, errorThrown){
             console.log(jqXHR.responseText);
              alert('failure: ' + textStatus + ' - ' + errorThrown);
          }   
        });        
      ";
		$output .= '
	
			}


		</script>

		';

		//				$output.=capi_watc_input_form_javascript($form);

		/*		
				$output.='
				<script>
		
				'.capi_watc_validate_form_basic_bio_form().'
		
				</script>';
*/

		return $output;
	} // end capi_email_preferences

	function capi_text_preferences()
	{

		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}


		$edit_cid = 0;

		foreach ($_REQUEST as $key => $value)
			${$key} = $value;


		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		//		$cid=$_SESSION["cid"];
		$cid = $this->capi_check_profile();

		if ($edit_cid > 0) $cid = $edit_cid;

		if ($cid == 0) {
			return "Login Expired";
		}

		if ($edit_cid > 0) $cid = $edit_cid;

		$fields_array = $this->capi_get_fields();
		$this->capi_fill_fields_array($fields_array, $cid);

		$query = "operation=get_subcription_list&delivery_type=1";

		$result = "";

		$result .= ($this->capi_process_api($query));

		$result = $this->capi_parse_xml2($this->capi_process_api($query));
		$subscriptions = $result["result"]["subscriptions"];
		if (isset($subscriptions["subscription"][0])) $subscriptions = $subscriptions["subscription"];

		// get subscriptions for this user.

		$query = "operation=get_contact_subcription&delivery_type=0";
		$result = "";
		$result .= ($this->capi_process_api($query));

		$result = $this->capi_parse_xml2($this->capi_process_api($query));
		$contact_subscriptions = $result["result"]["subscriptions"];
		if (isset($contact_subscriptions["subscription"][0])) $contact_subscriptions = $contact_subscriptions["subscription"];

		// put in an sid array
		$sid = array();
		foreach ($contact_subscriptions as $subscription) {
			if ($subscription['subscription_type'] > 0 && $subscription['subscription_type'] < 4) {
				$sid[$subscription['sid']] = 1;
			}
		}



		$output = "<h2>Text Message Preferences</h2>";
		$output .= "<br><br>";
		//$output.="<br>subs:".print_r($contact_subscriptions,true)."<br>sid:".print_r($sid,true)."<br>";
		$form = "basic_bio_form";
		$output .= '    
		  
				<div id="message_div"></div>
				<div id="form_div">
		  
		    <form id="' . $form . '" name="' . $form . '" class="search">
		      
		     ';
		//      $output.= capi_watc_theme_input($form,"","pmcpid","hidden",$my_pm_contact_process_bll->get_field("pmcpid"));

		$output .= $this->capi_theme_input($form, array("field" => "cid", "input_type" => "hidden", "type" => "s", "default" => $cid));
		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 1));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "save_site_contact_subscription"));
		$output .= $this->capi_theme_input($form, $fields_array['number-cell']);
		$output .= $this->capi_theme_input($form, $fields_array['carrier-cell']);

		$sid_list = "";
		foreach ($subscriptions as $subscription) {

			$sid_list .= "," . $subscription['sid'];
			if (isset($sid[$subscription['sid']])) $default = 1;
			else $default = 0;
			$output .= $this->capi_theme_input($form, array(
				'label' => $subscription['subscription_name'],
				'field' => 'sid-' . $subscription['sid'],
				'input_type' => 'checkbox',
				'default' => $default,
			));
		}
		$sid_list = ltrim($sid_list, ",");

		$output .= "<input type='hidden' name='sid_list' value='$sid_list'>";
		$output .= '<div style="height:40px">&nbsp;</div><center><p>
		      <input type=button id=save_button name=save_button value="Save">
					</p>
					
					</center>
		    </form>
				
				</div> <!-- form_div -->
				';



		$output .= '	
	 <script type="text/javascript" src="/capi/jquery.mask.min.js"></script>
		<script>
  
    jQuery(document).ready(function(){
    
		';

		$output .= "
	      jQuery('#save_button').bind('click', capi_save_button_click);
				
        jQuery('#" . $form . "').submit(function(e){
            e.preventDefault();
        });

	 ";



		$output .= ' 
	 
				jQuery("#' . $form . '_number-cell").mask("999-999-9999");	 
	    });  // end document ready
       </script>
      ';

		$output .= '
		

		<script>

			
			
			function capi_save_button_click() {
		
				
				';
		$output .= "
//        jQuery('#" . $form . "_operation').val('updatecontactallcomplete');
//				alert(jQuery('#basic_bio_form_birth_date-day').val());
//				alert(jQuery('#basic_bio_form_birth_date').val());
        var formData = jQuery('#" . $form . "').serializeArray();

        jQuery('#message_div').html('Saving');
        jQuery.ajax({
									
        url: '";

		$output .= get_site_url() . "/capi/process";

		$output .= "',
          type: 'POST',
					data: formData,
         dataType: 'json',
          success:  function(data){
						if (data['success']) {
              jQuery('#message_div').html(data['message']);
//							alert('Save Complete');
//              jQuery('#form_div').html('Save Complete.');//  <a href=/capi/member_home>Return to the Dashboard</a>');
//							location.reload();
						} else {
							alert('Error. '+data['messsage']);
              jQuery('#message_div').html(data['message']);
						}
          },
          error: function( jqXHR, textStatus, errorThrown){
             console.log(jqXHR.responseText);
              alert('failure: ' + textStatus + ' - ' + errorThrown);
          }   
        });        
      ";
		$output .= '
	
			}


		</script>

		';

		//				$output.=capi_watc_input_form_javascript($form);

		/*		
				$output.='
				<script>
		
				'.capi_watc_validate_form_basic_bio_form().'
		
				</script>';
*/

		return $output;
	} // end capi_text_preferences


	function capi_post_score()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}
		$result = "<h2>Post Score</h2>";

		$result .= '<script type="text/javascript" src="/capi-ghin.php"></script ><br />';

		//		$result.='<script type="text/javascript" src="https://widgets.ghin.com/LaunchWidget.js?widget=ScorePosting&width=800&height=100%&showheader=0&clubno=03&state=CO"></script><br />';
		//		$result.='<script type="text/javascript" src="https://widgets.ghin.com/LaunchWidget.js?widget=HandicapLookupEntry&small=0&css=mga01&showheader=1&showheadertext=1&showfootertext=1"></script>';

		//		$result.="<script>\n".( file_get_contents('https://widgets.ghin.com/LaunchWidget.js?widget=ScorePosting&width=800&height=100%&showheader=0&clubno=03&state=CO'))."\n</script>";
		//		$result.='<textarea><script>'.file_get_contents('https://widgets.ghin.com/LaunchWidget.js?widget=ScorePosting&width=800&height=100%&showheader=0&clubno=03&state=CO')."</script></textarea>";


		return $result;

		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		$query = "module=member_directory&operation=getmemberdirectory&mdid=1";

		$result = ($this->capi_process_api($query));
		return $result;
		return print_r($result, true);
		$result = $result["result"];

		if ($result['success'])
			return "Registration Complete. <a href='" . get_site_url() . "/capi/tournament_schedule'>Click here to return to the schedule</a>.";
		else
			return "Error. " . $result['message'];
	} // end 

	function capi_member_directory()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}
		if (current_user_can('administrator') || current_user_can('editor')) {
			$admin_key = "xdkejjdkseleiii";
		} else {
			$admin_key = "";
		}


		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		$query = "module=member_directory&operation=getmemberdirectory&mdid=1&admin_key=$admin_key&show_member_detail=admin_only";
		$result = ($this->capi_process_api($query));
		return $result;



		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		if (!current_user_can('administrator') && !current_user_can('editor')) {
			$admin_key = "xdkejjdkseleiii";
		}

		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		$data = ($this->capi_process_api("operation=searchmemberdirectory&module=member_directory&admin_key=$admin_key&show_member_detail=admin_only", 0, 0));



		//return print_r($result,true);
		$result = $result["result"];

		if ($result['success'])
			return "Registration Complete. <a href='" . get_site_url() . "/capi/tournament_schedule'>Click here to return to the schedule</a>.";
		else
			return "Error. " . $result['message'];
	} // end capi_member_directory

	function capi_registration_form()
	{
		if (is_user_logged_in()) {
			return "You are already logged in.";
		}

		$evoid = 0;
		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}

		$query = "operation=registration_form&module=website_integration&page_display=0";


		$result = ($this->capi_process_api($query));

		return $result;
		$result = $result["result"];

		if ($result['success'])
			return "Registration Complete. <a href='" . get_site_url() . "/capi/tournament_schedule'>Click here to return to the schedule</a>.";
		else
			return "Error. " . $result['message'];
	} // end capi_registration_form


	function capi_tournament_info()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$evoid = 0;
		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}
		if ($evoid == 0) {
			return "Invalid event.";
		}

		$query = "module=event&operation=get_event_occurrence&evoid=$evoid";

		$result = $this->capi_parse_xml2($this->capi_process_api($query));
		$event = $result["result"];

		$urls = $event['urls'];
		if (isset($urls['url'][0])) $urls = $urls['url'];

		//		return print_r($event,true);

		$output = "<h1>" . stripslashes($event['event_occurrence_name']) . "</h1>";


		if (current_user_can('administrator') || current_user_can('editor')) {
			/*
			$output.="
			<div class='capi_admin_div'>
			<div class='capi_add_tournament_link capi_button_link'><a href='".get_site_url()."/capi/edit_tournament?evoid=$evoid'>Edit Event</a></div>
			<div class='capi_add_tournament_link capi_button_link'><a href='".get_site_url()."/capi/edit_tournament_fields?evoid=$evoid'>Edit Custom Fields</a></div>
			<div class='capi_registrations_link capi_button_link'><a href='".get_site_url()."/capi/registrations?evoid=$evoid'>Registrations</a></div>
			<div class='capi_registrations_link capi_button_link'><a href='".get_site_url()."/capi/email_registrants?evoid=$evoid'>Email Registrants</a></div>
			</div><div style='clear:both;margin-bottom:5px;'></div>
			";
		}
*/
			$output .= "
			<div class='capi_admin_div'>
			<div class='capi_add_tournament_link capi_button_link'><a href='" . get_site_url() . "/capi/edit_tournament?evoid=$evoid'>Edit Event</a></div>
			<div class='capi_add_tournament_link capi_button_link'><a href='" . get_site_url() . "/capi/edit_tournament_fields?evoid=$evoid'>Custom Fields</a></div>
			</div><div style='clear:both;margin-bottom:5px;'></div>
			";
		}

		$output .= "
			<div class='capi_registrations_link capi_button_link'><a href='" . get_site_url() . "/capi/tournament_schedule/'>Schedule</a></div>
			<div style='clear:both;margin-bottom:5px;'></div>
		";

		if ($event['show_registrants'] || current_user_can('administrator') || current_user_can('editor')) {
			$output .= "
				<div class='capi_registrations_link capi_button_link'><a href='" . get_site_url() . "/capi/registrations?evoid=$evoid'>Registrations</a></div>
				<div style='clear:both;margin-bottom:5px;'></div>
			";
		}


		$output .= $event["event_location"] . "<br>";
		if ($event["event_street1"] > "") {
			$output .= $event["event_street1"] . "<br>";
		}
		if ($event["event_street2"] > "") {
			$output .= $event["event_street2"] . "<br>";
		}

		if ($event["event_city"] > "") {
			$output .= $event["event_city"] . ", " . $event["event_state"] . " " . $event["event_zip"] . "<br>";
		}

		$output .= date("m/d/Y", strtotime($event["event_start_date"]));
		if ($event["event_start_date"] <> $event["event_end_date"])
			$output .= " - " . date("m/d/Y", strtotime($event["event_end_date"]));
		$output .= "<br><br>";

		if ($event["registration_start_date"] > "") {
			$output .= "Registration Window: ";
			$output .= date("m/d/Y", strtotime($event["registration_start_date"]));
			if ($event["registration_start_time"] > "00:00:00")
				$output .= " " . date("h:i a", strtotime($event["registration_start_time"]));

			$output .= " - " . date("m/d/Y", strtotime($event["registration_end_date"]));

			if ($event["registration_end_time"] < "23:59:00")
				$output .= " " . date("h:i a", strtotime($event["registration_end_time"]));
			$output .= "<br><br>";
		}

		if ($event["withdrawal_end_date"] > "") {
			$output .= "Withdraw Until: ";
			$output .= date("m/d/Y", strtotime($event["withdrawal_end_date"]));
			if ($event["withdrawal_end_time"] > "00:00:00")
				$output .= " " . date("h:i a", strtotime($event["withdrawal_end_time"]));

			$output .= "<br><br>";
		}

		if ($event["event_occurrence_description"] > "") {
			$description = str_replace('\"', '"', html_entity_decode($event["event_occurrence_description"]));
			$output .= str_replace("\n", "<br>", $description);
			$output .= "<br><br>";
		}
		if ($event["event_occurrence_note"] > "") {
			$output .= str_replace("\n", "<br>", html_entity_decode($event["event_occurrence_note"]));
			$output .= "<br><br>";
		}
		if (is_array($urls)) {
			foreach ($urls as $url) {
				$output .= '<a href="' . $url['eu_url'] . '" target="_blank">' . $url['event_url_type_name'] . "</a><br>";
			}
		}

		//			$output.=print_r($event,true);

		// see if already registered for event

		$query = "module=event&operation=registration_status&evoid=$evoid";

		$result = $this->capi_parse_xml2(($this->capi_process_api($query)));
		//			$output.="<textarea>".print_r($result,true)."</textarea><br>";
		$result = $result['result'];
		$registered_status = $result['registered_status'];
		if ($registered_status == -1) {
			// make sure within registration window
			//		 date_default_timezone_set ( "America/New_York" );
			$now = date("Y-m-d-H-i-s", time());
			date_default_timezone_set(ini_get('date.timezone'));
			$now = time();
			//				$output.=" now: $now ".$event["registration_end_date"]."-".$event["registration_end_time"];
			if ($now >= strtotime($event["registration_start_date"] . " " . $event["registration_start_time"]) && $now <= strtotime($event["registration_end_date"] . " " . $event["registration_end_time"])) {
				$output .= "<a class='capi_button_link' href='" . get_site_url() . "/capi/event/registration_form?evoid=" . $evoid . "'>Register</a>";
			} else {
				// registrations closed
				//					$output.="Registrations not open. ".ini_get('date.timezone')." ".date_default_timezone_get (  )." ".$now." ".date("Y-m-d-H-i-s",time())." ".strtotime($event["registration_end_date"]." ".$event["registration_end_time"]);
				$output .= "Registrations not open.";
			}
		} else if ($registered_status == 0) {
			$output .= "Registration cancelled/withdrawn.  Contact administrator to register.";
		} else if ($registered_status == 1) {
			$output .= "Already registered.";
			// make sure within registration window
			//		 date_default_timezone_set ( "America/New_York" );
			$now = date("Y-m-d-H-i-s", time());
			date_default_timezone_set(ini_get('date.timezone'));
			$now = time();
			//				$output.=" now: $now ".$event["registration_end_date"]."-".$event["registration_end_time"];
			if ($now <= strtotime($event["withdrawal_end_date"] . " " . $event["withdrawal_end_time"])) {
				$output .= " - <a class='capi_button_link' href='" . get_site_url() . "/capi/event/withdrawal_form?evoid=" . $evoid . "'>Withdraw</a>";
			}
		} else if ($registered_status == 2) {
			$output .= "Wait listed.";
		}

		return $output;
	} // end capi_tournament_info


	//***

	function capi_tournament_registrants()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$evoid = 0;
		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}
		if ($evoid == 0) {
			return "Invalid event.";
		}
		$output = "<h1>Registrants</h1>";

		$query = "module=event&operation=get_event_registrants&evoid=$evoid";

		$my_return = $this->capi_parse_xml2($this->capi_process_api($query));


		$result = $my_return["result"];

		//$output.="<textarea>".print_r($result,true)."</textarea>";

		$registrants = $result["registrants"];

		$output .= "
			<div class='capi_registrations_link capi_button_link'><a href='" . get_site_url() . "/capi/tournament_info/?evoid=$evoid'>Info</a></div>
			<div style='clear:both;margin-bottom:5px;'></div>
		";


		if (isset($registrants["registrant"][0])) $registrants = $registrants["registrant"];

		$output .= "<table>";
		foreach ($registrants as $registrant) {
			$output .= "<td>";
			$output .= $registrant["last_name"] . ", " . $registrant["first_name"];
			$output .= "</td>";
			if (isset($registrant["partner_last_name"])) {
				$output .= "<td>";
				$output .= $registrant["partner_last_name"] . ", " . $registrant["partner_first_name"];
				$output .= "</td>";
			}
			$output .= "<td>";
			if ($registrant["registered_status"] == 0)
				$output .= " - Cancelled/Withdrawn";
			if ($registrant["registered_status"] == 2)
				$output .= " - Waitlisted";
			$output .= "</td>";
			$output .= "</tr>";
		}
		$output .= "</table>";

		//$output.= "<textarea>".print_r($my_return,true)."</textarea>";
		return $output;
		return "good";
	} // end capi_tournament_registrants



	function capi_check_profile($in_capi_process_api = false)
	{


		if (!isset($_SESSION))
			session_start();

		$_SESSION['LAST_ACTIVITY'] = time(); // this is to keep session alive

		//error_log("check_profile:".print_r($_SESSION,true));	

		//	error_log("_SESSION['cid']:".$_SESSION['cid']);
		//	error_log("_SESSION['capi_uid']:".$_SESSION['capi_uid']);

		$user_id = get_current_user_id();


		if ($user_id == 0) return 0;

		if (!isset($_SESSION['cid'])) {
			// session expired. look up cid
			//error_log("session expired, look up cid ".$user_id);
			$cid = $this->capi_process_api("operation=lookupcid&module=website_integration&external_uid=" . $user_id, 0, 0, 1);

			if ($cid > 0) {
				$_SESSION['cid'] = $cid;
				$_SESSION['capi_uid'] = $user_id;
			}
		} else {
			$cid = $_SESSION['cid'];
		}
		//error_log("cid: $cid");	
		return $cid;

		/*
	  if (isset($_SESSION['cid']) && isset($_SESSION['capi_uid'])) {
			if ($_SESSION['capi_uid']==$user_id)
			  return $_SESSION['cid'];
			else {
			 unset($_SESSION['cid']);
			 unset($_SESSION['capi_uid']);
			}
		}  else {
			/// logged into wp, but not ceo.  get the login
			// todo, create relogin
			if (!$in_capi_process_api) {


$query="module=website_integration&operation=lookupcid&external_uid=$user_id";
error_log ("lookupcid: ".$this->capi_process_api($query,0,0));

				$query="module=website_integration&operation=lookupcid&external_uid=$user_id";
				$cid=$this->capi_process_api($query,0,0);
				$_SESSION['cid']=$cid;
				$_SESSION['capi_uid']=$user_id;
				return ($cid);
				
			}
							
		}
*/

		// no session variables set yet

		return 0;

		$_SESSION['capi_uid'] = $user_id;

		$individual = $this->capi_parse_individual_xml($this->capi_process_api_c("operation=getindividual"));
		$cid = 0;

		//	error_log("check profile.  success: ".$individual[0]["success"]);	
		//	error_log("check profile.  individual: ".print_r($individual,true));	

		if ($individual[0]["success"] == "true") {
			$cid = $individual[0]["cid"];
		}

		$_SESSION['cid'] = $cid;

		return $cid;

		return 0; // should not get here for wordpress	
		//	if ($user->uid==1) return 0;

		$my_profile = user_load($user->uid);


		//	error_log("in check profile.");	

		$individual = capi_parse_individual_xml(capi_process_api_c("operation=getindividual"));
		$cid = 0;

		//	error_log("check profile.  success: ".$individual[0]["success"]);	
		//	error_log("check profile.  individual: ".print_r($individual,true));	

		if ($individual[0]["success"] != "true") {
			// create contact 
			$query = "";
			$query .= "first_name=" . capi_get_profile_field($my_profile, "first_name") . "&";
			$query .= "middle_name=" . capi_get_profile_field($my_profile, "middle_name") . "&";
			$query .= "last_name=" . capi_get_profile_field($my_profile, "last_name") . "&";
			$query .= "suffix_name=" . capi_get_profile_field($my_profile, "suffix_name") . "&";
			$query .= "email=" . $my_profile->mail . "&";
			$query .= "capi_member_number=" . capi_get_profile_field($my_profile, variable_get('capi_member_number_field', "")) . "&";
			$query .= "capi_member_number=" . urlencode($my_profile->{variable_get('capi_member_number_field', "")}) . "&";
			$query .= "capi_club_number=" . urlencode(variable_get('capi_club_number', "")) . "&";

			$cid = capi_process_api_c("operation=createindividual&" . $query);
		} else {

			$cid = $individual[0]["cid"];
		}

		$_SESSION['cid'] = $cid;
		$_SESSION['capi_uid'] = $user->uid;


		error_log("cid: $cid");
		return $cid;
	}

	/**************************************************************************
		
			function: capi_process_api_c
			
			This function processes only the raw call to ceo/command
			
			
			only looks at queryin, not request
		
	 ***************************************************************************/
	// not working
	function capi_process_api_c($queryin = "")
	{


		$operation = "";

		$wiwid = $this->wiwid;

		//		error_log("process apix operation: $operation");

		//		error_log("queryin: $queryin");	


		if ($queryin > "") $query = $this->urlencodeall($queryin) . "&";
		else $query = "";

		// CAPI Login Information

		$query .= "wiwid=" . urlencode($wiwid) . "&";
		//			$query .= "user_cid=" . urlencode($user_cid) . "&";
		$query .= "un=demo.clubsite.synergyinnovativesystems.com&";
		$query .= "pw=demo1&";
		$query .= "capi_external_key_field=" . "&";
		$query .= "capi_external_key=" . urlencode($user->uid) . "&";

		//				  $query .= "capi_external_key_field=" . urlencode(variable_get('capi_external_key_field', "")) . "&";
		//				  $query .= "capi_external_key=" . urlencode($user->uid) . "&";

		if (isset($_SESSION['admin_key'])) {
			$query .= "admin_key=" . urlencode($_SESSION['admin_key']) . "&";
		}

		$output = "";
		//  $output= variable_get('capi_url', "")."/?page=api&".$query."<br>";

		//		error_log("calling capi:".variable_get('capi_url', "")."/?page=api&".$query);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, variable_get('capi_url', "") . "/?page=api&" . $query);


		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		curl_setopt($ch, CURLOPT_POST, 6);

		if (!($data = curl_exec($ch))) {
			$output .= "Curl Error";
			$output .= curl_error($ch);
			$output .= "Data:" . $data . ":";
			error_log("curl error. $data");
		} else {
			curl_close($ch);
			unset($ch);
			$output .= $data;
			//error_log("curl successful. $data");						
		}

		return $output;
	} // end capi_process_api_c

	function capi_parse_individual_xml($data)
	{

		$p = xml_parser_create();
		xml_parse_into_struct($p, $data, $vals, $index);
		xml_parser_free($p);

		//      print $vals[1]["tag"] . " tag 1<br>";
		//      print $vals[2]["tag"] . " tag 2<br>";
		//      print $vals[3]["tag"] . " tag 3<br>";
		//      var_dump($vals);
		//      return "success";

		$count = 0;
		$x = "";
		for ($i = 0; $i < sizeof($vals); $i++) {
			//        $x.=$i .  $vals[$i]["tag"] . "<br>";
			switch (strtolower($vals[$i]["tag"])) {

				case "result":
					if ($vals[$i]["type"] == "close")
						$count++;
					break;
				default:
					if (isset($vals[$i]["value"]))
						$individual[$count][strtolower($vals[$i]["tag"])] = $vals[$i]["value"];
					break;
			}
		}

		/*
				        
				         $x.="$count individuals found.<br><table>";
				        dump($individual);
				        for ($i=0;$i<$count;$i++) {
				          $x .= "<tr>";
				          $x .= "<td>" . $individual[$i]["cid"] . "</td>";
				          $x .= "<td>" . $individual[$i]["firstname"] . "</td>";
				          $x .= "<td>" . $individual[$i]["middlename"] . "</td>";
				          $x .= "<td>" . $individual[$i]["lastname"] . "</td>";
				          $x .= "<td>" . $individual[$i]["clubname"] . "</td>";
				          $x .= "<td>" . $individual[$i]["email"] . "</td>";
				          $x .= "<td>" . $individual[$i]["value"] . "</td>";
				          $x .= "<td>" . $individual[$i]["revdate"] . "</td>";
				          $x .= "</tr>\n";
				        }         
				        $x .= "</table>";
				        return $x;
				        
				        */

		return $individual;
	} // end capi_parse_individual_xml

	function urlencodeall($s)
	{

		$a = explode("&", $s);
		$b = "";
		foreach ($a as $k) {
			if (strlen($k) > 0) {
				$l = explode("=", $k);
				$b .= $l[0] . "=" . urlencode($l[1]) . "&";
			}
		}
		error_log("in: $s out: $b");
		return $b;
	}

	/**************************************************************************
	
		function: capi_addwebsiteuser
		
		returns:
		array(
					 "message"=>"",
	         "success"=>true or false
	         "uid"=>Uid of new user
					);
	
	 ***************************************************************************/

	function capi_addwebsiteuser($queryin = "")
	{

		foreach ($_REQUEST as $key => $value)

			${$key} = $value;


		//error_log("addwebsiteuser:".print_r($queryin,true));
		//print_r($_REQUEST);

		// check user name and password
		$capi_un = $this->capi_un;
		$capi_pw = $this->capi_pw;

		if (!($un == $capi_un && $pw == $capi_pw)) {
			$my_return = array(
				"message" => "validation error",
				"success" => false,
			);
			error_log("capi api validation error: $un:$pw");
			return ($my_return);
		}

		// make sure user doesn't exist


		$wpmu_id = 0;  // multisite user id
		if ($my_user = get_user_by('login', $wiu_user_name)) {

			if (is_multisite()) {
				$is_user = is_user_member_of_blog($my_user->ID, get_current_blog_id());
				$wpmu_id = $my_user->ID;
			} else {
				$is_user = true;
			}
			if ($is_user) {
				$my_return = array(
					"message" => "User exists: $wiu_user_name",
					"success" => false,
				);
				error_log("User exists error: $wiu_user_name");
				return ($my_return);
			}
		}

		if ($my_user = get_user_by('email', $email)) {
			if (is_multisite()) {
				$is_user = is_user_member_of_blog($my_user->ID, get_current_blog_id());
			} else {
				$is_user = true;
			}
			if ($is_user) {
				$my_return = array(
					"message" => "User email exists error: $email",
					"success" => false,
				);
				error_log("User email exists error: $email");
				return ($my_return);
			}
		}

		$user_array = array(
			'user_login' => $wiu_user_name,
			'user_pass' => $wiu_password,
			'user_email' => $email,
			'first_name' => $first_name,
			'last_name' => $last_name,
		);

		//			$user_id=wp_create_user( $wiu_user_name, $wiu_password, $email );

		if ($wpmu_id > 0) {

			$my_return = array(
				"message" => "",
				"success" => true,
				"uid" => $wpmu_id,
			);
		} else {

			$user_id = wp_insert_user($user_array);

			$my_return = array(
				"message" => "",
				"success" => true,
				"uid" => $user_id,
			);
		}


		return $my_return;
	}  // end capi_addwebsiteuser


	/**************************************************************************
	
		function: capi_updatewebsiteuser
		
		Parameters:
		
		external_uid - uid to be changed
				
		returns:
		array(
					 "message"=>"",
	         "success"=>true or false
	         "uid"=>Uid of new user
					);
	
	 ***************************************************************************/

	function capi_updatewebsiteuser($queryin = "")
	{

		$uid = 0;
		$group_names = "";

		foreach ($_REQUEST as $key => $value)

			${$key} = $value;

		$uid = $external_uid;

		//error_log("capi_updatewebsiteusergroup:".print_r($queryin,true));
		//print_r($_REQUEST);

		// check user name and password
		$capi_un = $this->capi_un;
		$capi_pw = $this->capi_pw;

		if (!($un == $capi_un && $pw == $capi_pw)) {
			$my_return = array(
				"message" => "validation error",
				"success" => false,
			);
			error_log("capi api validation error: $un:$pw");
			return ($my_return);
		}

		// make sure user exists

		if (!$my_user = get_user_by('id', $uid)) {
			$my_return = array(
				"message" => "User does not exist: $uid",
				"success" => false,
			);
			error_log("User does not exist error: $uid");
			return ($my_return);
		}

		// look for changes other than password

		if ($my_user->user_login != $wiu_user_name) {

			// if user name is different, make sure no duplicate user names

			if ($my_user = get_user_by('login', $wiu_user_name)) {
				$my_return = array(
					"message" => "User exists: $wiu_user_name",
					"success" => false,
				);
				error_log("User exists error: $wiu_user_name");
				return ($my_return);
			}
		}

		// if email address is different, make sure no duplicate emails

		if ($my_user->email != $email) {
			if ($my_user = get_user_by('email', $email)) {
				$my_return = array(
					"message" => "User email exists error: $email",
					"success" => false,
				);
				error_log("User email exists error: $email");
				return ($my_return);
			}
		}

		$user_array = array(
			'ID' => $uid,
			'user_login' => $wiu_user_name,
			'user_email' => $email,
			'first_name' => $first_name,
			'last_name' => $last_name,
		);

		$success = wp_update_user($user_array);

		//error_log("change: ".print_r($success));			

		$my_return = array(
			"message" => $message . "caps: " . print_r($success, true) . ": ",
			"success" => false,
		);

		return $my_return;
	}  // end capi_updatewebsiteusergroup


	/**************************************************************************
	
		function: capi_updatewebsiteusergroup
		
		Parameters:
		
		uid - uid to be updated
				
		returns:
		array(
					 "message"=>"",
	         "success"=>true or false
	         "uid"=>Uid of new user
					);
	
	 ***************************************************************************/

	function capi_updatewebsiteusergroup($queryin = "")
	{

		$uid = 0;
		$group_names = "";

		foreach ($_REQUEST as $key => $value)

			${$key} = $value;

		//error_log("capi_updatewebsiteusergroup:".print_r($queryin,true));
		//print_r($_REQUEST);

		// check user name and password
		$capi_un = $this->capi_un;
		$capi_pw = $this->capi_pw;

		if (!($un == $capi_un && $pw == $capi_pw)) {
			$my_return = array(
				"message" => "validation error",
				"success" => false,
			);
			error_log("capi api validation error: $un:$pw");
			return ($my_return);
		}

		// make sure user exists

		if (!$my_user = get_user_by('id', $uid)) {
			$my_return = array(
				"message" => "User does not exist: $uid",
				"success" => false,
			);
			error_log("User does not exist error: $uid");
			return ($my_return);
		}

		if ($group_names == "") {
			$my_return = array(
				"message" => "Error. No groups specified.",
				"success" => false,
			);
			error_log("Error. No groups specified.");
			return ($my_return);
		}

		$roles_array = explode(",", $group_names);
		$my_user = new WP_User($uid);
		$my_user->set_role("");
		//			wp_update_user( array( 'ID' => $uid, 'role' => 'Subscriber' ) );

		foreach ($roles_array as $role) {
			if (is_multisite()) {
				//error_log("multi: ".get_current_blog_id()." -$role");
				add_user_to_blog(get_current_blog_id(), $my_user->ID, $role);
				//					add_user_to_blog( get_current_blog_id(), $my_user->ID, "administrator" );
			} else {
				$my_user->add_role($role);
			}
		}


		$my_return = array(
			"message" => $message . "caps: " . print_r($my_user, true) . ": ",
			"success" => true,
		);


		return $my_return;
	}  // end capi_updatewebsiteusergroup

	/**************************************************************************
	
		function: capi_deletewebsiteuser
		
		parameters:
		
			external_uid
			wiu_user_name
		
		returns:
		array(
					 "message"=>"",
	         "success"=>true or false
					);
	
	 ***************************************************************************/

	function capi_deletewebsiteuser($queryin = "")
	{


		$external_uid = 0;
		$wiu_user_name = 0;

		foreach ($_REQUEST as $key => $value)

			${$key} = $value;


		error_log("deletewebsiteuser:" . print_r($queryin, true));
		//print_r($_REQUEST);

		// check user name and password
		$capi_un = $this->capi_un;
		$capi_pw = $this->capi_pw;

		if (!($un == $capi_un && $pw == $capi_pw)) {
			$my_return = array(
				"message" => "validation error",
				"success" => false,
			);
			error_log("capi api validation error: $un:$pw");
			return ($my_return);
		}

		// make sure not user 1

		if ($external_uid == 1) {
			$my_return = array(
				"message" => "Can't delete user 1",
				"success" => false,
			);
			error_log("trying to delete user 1");
			return ($my_return);
		}

		// make sure user exists

		if (!$my_user = get_user_by('id', $external_uid)) {
			$my_return = array(
				"message" => "User does not exist: $external_uid",
				"success" => false,
			);
			error_log("User does not exist error: $external_uid");
			return ($my_return);
		}

		if (is_multisite()) {
			require_once(ABSPATH . 'wp-admin/includes/ms.php');
			remove_user_from_blog($external_uid, get_current_blog_id(), 1);
		} else {
			require_once(ABSPATH . 'wp-admin/includes/user.php');
			$success = wp_delete_user($external_uid, 1);
		}

		$my_return = array(
			"message" => "",
			"success" => $success,
		);


		return $my_return;
	}  // end capi_deletewebsiteuser


	function capi_member_home()
	{
		$output = "";

		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}
		if (current_user_can('administrator') || current_user_can('editor')) {
			$output .= "<div class='capi_admin_div'><a class='capi_button_link' href='" . get_site_url() . "/capi/admin'>Admin</a><br><br></div>";
		}

		if (!isset($_SESSION)) session_start();

		//		$cid=$_SESSION["cid"];
		$cid = $this->capi_check_profile();
		$fields_array = $this->capi_get_fields();

		$this->capi_fill_fields_array($fields_array, $cid);
		//yyyy		

		$query = "module=clubsite_pro&operation=get_quick_links";
		$result = $this->capi_parse_xml2($this->capi_process_api($query));
		$quick_links = urldecode($result["result"]["csp_quick_links"]);

		$query = "module=clubsite_pro&operation=get_association_news";
		$result = $this->capi_parse_xml2($this->capi_process_api($query));
		$association_news = urldecode($result["result"]["csp_association_news"]);

		$output .= "<div class='capi_home_box capi_my_events'><h2>" .
			$fields_array['first_name']["default"] . " " . $fields_array['last_name']["default"] . "</h2>
		GHIN #: " . $fields_array['handicap_number']["default"] . "<br>
		USGA Handicap Index: " . $fields_array['handicap_index']["default"] .
			"<br>
		<a href='" . get_site_url() . "/capi/update_profile'>Update Profile</a><br>
		<a href='http://www.ghin.com/scorePosting.aspx' target='_blank'>Post a Score<a><br>
		<a href='" . get_site_url() . "/capi/member_directory'>Member Directory</a><br>
		<a href='" . get_site_url() . "/capi/email_preferences'>Email Preferences</a><br>
		<a href='" . get_site_url() . "/capi/text_preferences'>Text Message Preferences</a><br>
		<a href='" . get_site_url() . "/capi/change_password'>Change Password</a><br>
		<a href='" . get_site_url() . "/capi/logout'>Log Out</a><br>
<!--		<a href='" . get_site_url() . "/capi/manage_payment_methods'>Manage Payment Methods</a> -->
		</div>";

		$output .= "<div class='capi_home_box capi_my_events'><h2>Events</h2>";
		$output .= "<b>My Events</b><br>";
		$output .= $this->capi_my_registrations();
		$output .= "<br><br>";
		$output .= "<a href='" . get_site_url() . "/capi/tournament_schedule'>Schedule</a>";
		$output .= "</div>";


		return $output;
	} // end capi_member_home

	function capi_theme_input($form = '', $field_spec)
	{

		$label = '';
		$field = '';
		$input_type = 'text';
		$default = '';
		$maxlength = '45';
		$options = NULL;
		$mandatory = "";
		$classes = "";
		$class = "";
		$rows = 10;
		$cols = 80;


		foreach ($field_spec as $key => $value) ${$key} = $value;
		//	error_log("field: $field  default: ".print_r($default,true));

		$output = "";
		if ($maxlength > 45) $size = 22.5;
		else if ($type == "date") $size = $maxlength;
		else $size = $maxlength / 2;
		if (isset($options["rows"])) $rows = $options["rows"];
		else $rows = 10;
		if (isset($options["cols"])) $cols = $options["cols"];
		else $cols = 60;

		switch ($input_type) {
			case "hidden":
				$output .= "<input name=\"";

				if ($class > "") $output .= $class . "__";
				$output .= "$field\" id=\"" . $form . "_" . $field . "\" class=\"input " . $classes . "\" type=\"hidden\" value=\"$default\">";
				break;
			case "checkbox":
				if ($default) $checked = "checked";
				else $checked = "";
				if ($label > "")
					$output .= "<div class='input_div capi_checkbox'><input name=\"$field\" id=\"" . $form . "_" . $field . "\" class=\"input " . $classes . "\" type=\"checkbox\"  $checked></div>";
				$output .= "<div class='label_div capi_checkbox'><label class=\"form_label $mandatory\">" . $label . "</label></div>";
				break;
			case "text":
				$output = "<div class=\"form-item-group-major\">";
				if ($label > "")
					$output .= "<div class='label_div'><label class=\"form_label $mandatory\">" . $label . "</label></div>";
				$output .= "<div class='input_div'><input name=\"";
				if ($class > "") $output .= $class . "__";
				$output .= "$field\" id=\"" . $form . "_" . $field . "\" class=\"input " . $classes . "\" type=\"text\" size=\"$size\" maxlength=\"$maxlength\" value=\"" . htmlspecialchars($default) . "\">";
				$output .= "</div></div>";
				break;
			case "tel":
				$output = "<div class=\"form-item-group-major\">";
				if ($label > "")
					$output .= "<div class='label_div'><label class=\"form_label $mandatory\">" . $label . "</label></div>";
				$output .= "<div class='input_div'><input name=\"";
				if ($class > "") $output .= $class . "__";
				$output .= "$field\" id=\"" . $form . "_" . $field . "\" class=\"input " . $classes . "\" type=\"tel\" size=\"$size\" maxlength=\"$maxlength\" value=\"" . htmlspecialchars($default) . "\">";
				$output .= "</div></div>";
				break;
			case "date":
				$output = "<div class=\"form-item-group-major\">";
				if ($label > "")
					$output .= "<div class='label_div'><label class=\"form_label $mandatory\">" . $label . "</label></div>";


				$output .= "<input name=\"";
				if ($class > "") $output .= $class . "__";
				$output .= "$field\" id=\"" . $form . "_" . $field . "\" class=\"input " . $classes . "\" type=\"hidden\" size=\"$size\" maxlength=\"$maxlength\" value=\"" . htmlspecialchars($default) . "\">";

				$d = "";
				$m = "";
				$y = "";
				if (strlen($default) > "") {
					$def = strtotime($default);
					$d = date("d", $def);
					$m = date("m", $def);
					$y = date("Y", $def);
				}

				$d_options = array(0 => "");
				for ($i = 1; $i < 32; $i++) $d_options[$i] = $i;

				$m_options = array(0 => "");
				for ($i = 1; $i < 13; $i++) $m_options[$i] = $i;

				$y_options = array(0 => "");
				for ($i = 1900; $i < 2050; $i++) $y_options[$i] = $i;


				$output .= "<div class='input_div'>";

				// month

				$output .= "<select name=\"";
				if ($class > "") $output .= $class . "__";
				$output .= "$field-month\" id=\"" . $form . "_" . $field . "-month\" class=\"input " . $classes . "\">";
				foreach ($m_options as $key => $value) {
					$output .= "<option value='" . $key . "'";
					if ($m == $key) $output .= " selected";
					$output .= ">" . $value . "</option>";
				}
				$output .= "</select>";

				// day

				$output .= "<select name=\"";
				if ($class > "") $output .= $class . "__";
				$output .= "$field-day\" id=\"" . $form . "_" . $field . "-day\" class=\"input " . $classes . "\">";
				foreach ($d_options as $key => $value) {
					$output .= "<option value='" . $key . "'";
					if ($d == $key) $output .= " selected";
					$output .= ">" . $value . "</option>";
				}
				$output .= "</select>";

				// year

				$output .= "<select name=\"";
				if ($class > "") $output .= $class . "__";
				$output .= "$field-year\" id=\"" . $form . "_" . $field . "-year\" class=\"input " . $classes . "\">";
				foreach ($y_options as $key => $value) {
					$output .= "<option value='" . $key . "'";
					if ($y == $key) $output .= " selected";
					$output .= ">" . $value . "</option>";
				}
				$output .= "</select>";

				$output .= "</div>";




				break;
			case "time":
				$output = "<div class=\"form-item-group-major\"><label class=\"form_label $mandatory\">" . $label . "</label>";
				$output .= "<input name=\"$field\" id=\"" . $form . "_" . $field . "\" class=\"input " . $classes . "\" type=\"text\" size=\"$size\" maxlength=\"$maxlength\" value=\"$default\"></div>";
				break;
			case "textarea":
				$output = "<div class=\"form-item-group-major\">";
				if ($label > "")
					$output .= "<div class='label_div'><label class=\"form_label $mandatory\">" . $label . "</label></div>";
				$output .= "<div class='input_div'><textarea name=\"";
				if ($class > "") $output .= $class . "__";
				$output .= "$field\" rows=\"$rows\" cols=\"$cols\" id=\"" . $form . "_" . $field . "\" class=\"input " . $classes . "\">";
				$output .= htmlspecialchars($default);
				$output .= "</textarea>";
				$output .= "</div></div>";
				break;
				/*		
		  $output="<div class=\"form-item-group-full\"><label class=\"form_label $mandatory\">".$label."</label>";
		  $output.="<textarea name=\"$field\" rows=\"$rows\" cols=\"$cols\" id=\"" . $form . "_" . $field . "\" class=\"input " . $classes . "\">";
		  $output.=htmlspecialchars($default);
		  $output.="</textarea></div>";
		  break;
	*/
			case "display":
				$output = "<label class=\"form_label $mandatory\">" . $label . "</label>";
				$output .= $field;
				break;
			case "select":
				$output = "<div class=\"form-item-group-major\">";
				if ($label > "")
					$output .= "<div class='label_div'><label class=\"form_label $mandatory\">" . $label . "</label></div>";
				$output .= "<div class='input_div'><select name=\"";
				if ($class > "") $output .= $class . "__";
				$output .= "$field\" id=\"" . $form . "_" . $field . "\" class=\"input " . $classes . "\">";
				foreach ($options as $key => $value) {
					$output .= "<option value='" . $key . "'";
					if ($default == $key) $output .= " selected";
					$output .= ">" . $value . "</option>";
				}
				$output .= "</select>";
				$output .= "</div></div>";
				break;
				/*
		  $output="<div class=\"form-item-group-major\"><label class=\"form_label $mandatory\">".$label."</label>";
		  $output.="<select name=\"$field\" id=\"" . $form . "_" . $field . "\" class=\"input\">";
		  foreach ($options as $key => $value) { 
			$output.="<option value='" . $key . "'";
			if ($default==$key) $output.=" selected";
			$output.=">".$value."</option>";
		  }
		  $output.="</select></div>";
	*/
				break;
			case "multiple":
				$output = "";
				$output .= "<div class=\"form-item-group-major\"><label class=\"form_label $mandatory\">" . $label . "</label>";

				$output .= "<select name=\"" . $field . "[]\" multiple id=\"" . $form . "_" . $field . "\" class=\"input\">";
				foreach ($options as $key => $value) {
					$output .= "<option value='" . $key . "'";
					if (in_array($key, $default)) $output .= " selected";
					$output .= ">" . $value . "</option>";
				}
				$output .= "</select>";

				$output .= "</div>";

				break;
			case "datalist":
				$output = "<div class=\"form-item-group-major\"><label class=\"form_label $mandatory\">" . $label . "</label>";
				$output .= "<input name=\"$field\" id=\"" . $form . "_" . $field . "\"  list=\"" . $form . "_" . $field . "_list\" class=\"input " . $classes . "\" type=\"text\" size=\"$size\" maxlength=\"$maxlength\" value=\"" . htmlspecialchars($default) . "\"></div>";
				$output .= "<datalist id=\"" . $form . "_" . $field . "_list\">";
				foreach ($options as $key => $value) {
					$output .= "<option value='" . $key . "'";
					if ($default == $key) $output .= " selected";
					$output .= ">" . $value . "</option>";
				}
				$output .= "</datalist></div>";
				break;
		}

		return $output;
	} // end $this->capi_theme_input


	function capi_get_fields()
	{
		$fields_array = array(
			"prefix_name" =>
			array(
				'label' => 'Prefix Name',
				'field' => 'prefix_name',
				'class' => 'contact_individual',
				'type' => 's',
				'input_type' => 'text',
				'length' => 100,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"suffix_name" =>
			array(
				'label' => 'Suffix Name',
				'field' => 'suffix_name',
				'class' => 'contact_individual',
				'type' => 's',
				'input_type' => 'text',
				'length' => 100,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"last_name" =>
			array(
				'label' => 'Last Name',
				'field' => 'last_name',
				'class' => 'contact_individual',
				'type' => 's',
				'input_type' => 'text',
				'length' => 100,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"middle_name" =>
			array(
				'label' => 'Middle Name',
				'field' => 'middle_name',
				'class' => 'contact_individual',
				'type' => 's',
				'input_type' => 'text',
				'length' => 100,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"first_name" =>
			array(
				'label' => 'First Name',
				'field' => 'first_name',
				'class' => 'contact_individual',
				'type' => 's',
				'input_type' => 'text',
				'length' => 100,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"preferred_last_name" =>
			array(
				'label' => 'Preferred Family Name (as player wishes it to appear for publication)',
				'field' => 'preferred_last_name',
				'class' => 'contact_individual',
				'type' => 's',
				'input_type' => 'text',
				'length' => 100,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"preferred_first_name" =>
			array(
				'label' => 'Preferred Given Name (as player wishes it to appear for publication)',
				'field' => 'preferred_first_name',
				'class' => 'contact_individual',
				'type' => 's',
				'input_type' => 'text',
				'length' => 100,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"wiu_password" =>
			array(
				'label' => 'New Password (only fill in to change)',
				'field' => 'wiu_password',
				'class' => '',
				'type' => 's',
				'input_type' => 'text',
				'length' => 100,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"wiu_repeatpassword" =>
			array(
				'label' => 'Repeat Password (only fill in to change)',
				'field' => 'wiu_repeatpassword',
				'class' => '',
				'type' => 's',
				'input_type' => 'text',
				'length' => 100,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"birth_date" =>
			array(
				'label' => 'Date of Birth (mm-dd-yyyy)',
				'field' => 'birth_date',
				'class' => 'contact_individual',
				'type' => 'd',
				'input_type' => 'date',
				'length' => 20,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"handedness" =>
			array(
				'label' => 'Handedness',
				'field' => 'handedness',
				'class' => 'contact_individual',
				'type' => 's',
				'input_type' => 'select',
				'length' => 1,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => array("" => "", "R" => "Right", "L" => "Left"),
				'help_text' => ''
			),
			"gender" =>
			array(
				'label' => 'Gender',
				'field' => 'gender',
				'class' => 'contact_individual',
				'type' => 's',
				'input_type' => 'select',
				'length' => 1,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => array("" => "", "M" => "Male", "F" => "Female"),
				'help_text' => ''
			),



			"number-home" =>
			array(
				'label' => 'Phone Number',
				'field' => 'number-home',
				'class' => 'contact_phone',
				'type' => 's',
				'input_type' => 'text',
				'length' => 20,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"number-cell" =>
			array(
				'label' => 'Phone Number (Cell)',
				'field' => 'number-cell',
				'class' => 'contact_phone',
				'type' => 's',
				'input_type' => 'text',
				'length' => 20,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"carrier-cell" =>
			array(
				'label' => 'Carrier (Cell)',
				'field' => 'carrier-cell',
				'class' => 'contact_phone',
				'type' => 's',
				'input_type' => 'select',
				'length' => 25,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => array(
					'' => 'Please select.',
					'txt.att.net' => 'AT&T Wireless Text',
					'mms.att.net' => 'AT&T Wireless MMS',
					'myboostmobile.com' => 'Boost Mobile',
					'mobile.celloneusa.com' => 'Cellular One',
					'Cmobile.mycingular.com' => 'Cingular',
					'mymetropcs.com' => 'Metro PCS',
					'messaging.nextel.com' => 'Nextel',
					'messaging.sprintpcs.com' => 'Sprint PCS (messaging)',
					'pm.sprint.com' => 'Sprint PCS (pm)',
					'tmomail.net' => 'T-Mobile',
					'mmst5.tracfone.com' => 'Tracfone',
					'email.uscc.net' => 'US Cellular',
					'sms.mycricket.com' => 'Cricket',
					'vtext.com' => 'Verizon Option 1',
					'vzwpix.com' => 'Verizon Option 2'
				),
				'help_text' => ''
			),

			"email-business" =>
			array(
				'label' => 'Email Address',
				'field' => 'email-business',
				'class' => 'email_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 250,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"handicap_number" =>
			array(
				'label' => 'Handicap Number',
				'field' => 'handicap_number',
				'class' => 'handicap_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 20,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"handicap_index" =>
			array(
				'label' => 'Handicap Index',
				'field' => 'handicap_index',
				'class' => 'handicap_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 10,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"email-home" =>
			array(
				'label' => 'Email Address',
				'field' => 'email-home',
				'class' => 'email_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 250,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"cc_email-home" =>
			array(
				'label' => 'CC Email Address',
				'field' => 'cc_email-home',
				'class' => 'email_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 250,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"street1-home" =>
			array(
				'label' => 'Street Address',
				'field' => 'street1-home',
				'class' => 'address',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"street2-home" =>
			array(
				'label' => '&nbsp;',
				'field' => 'street2-home',
				'class' => 'address',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"city-home" =>
			array(
				'label' => 'City',
				'field' => 'city-home',
				'class' => 'address',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"state-home" =>
			array(
				'label' => 'State',
				'field' => 'state-home',
				'class' => 'address',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"zip-home" =>
			array(
				'label' => 'Zip',
				'field' => 'zip-home',
				'class' => 'address',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"country-home" =>
			array(
				'label' => 'Country',
				'field' => 'country-home',
				'class' => 'address',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"street1-par_play" =>
			array(
				'label' => 'Street Address',
				'field' => 'street1-par_play',
				'class' => 'address',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"street2-par_play" =>
			array(
				'label' => '&nbsp;',
				'field' => 'street2-par_play',
				'class' => 'address',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"city-par_play" =>
			array(
				'label' => 'City',
				'field' => 'city-par_play',
				'class' => 'address',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"state-par_play" =>
			array(
				'label' => 'State',
				'field' => 'state-par_play',
				'class' => 'address',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"zip-par_play" =>
			array(
				'label' => 'Zip',
				'field' => 'zip-par_play',
				'class' => 'address',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
			"url-facebook" =>
			array(
				'label' => 'Facebook',
				'field' => 'url-facebook',
				'class' => 'contact_url',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => false,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"url-twitter" =>
			array(
				'label' => 'Twitter',
				'field' => 'url-twitter',
				'class' => 'contact_url',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => false,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),


			"url-instagram" =>
			array(
				'label' => 'Instagram',
				'field' => 'url-instagram',
				'class' => 'contact_url',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => false,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"text_value-Most_Memorable_Golf_Experience" =>
			array(
				'label' => 'Most Memorable Golf Experience',
				'field' => 'text_value-Most_Memorable_Golf_Experience',
				'class' => 'text_contact',
				'type' => 's',
				'input_type' => 'textarea',
				'length' => 1500,
				'default' => '',
				'mandatory' => false,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"text_value-Parent_Relationship" =>
			array(
				'label' => 'Parent/Legal Guardian Relationship',
				'field' => 'text_value-Parent_Relationship',
				'class' => 'text_contact',
				'type' => 's',
				'input_type' => 'select',
				'length' => 1,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => array("" => "", "P" => "Parent", "G" => "Legal Guardian"),
				'help_text' => ''
			),

			"text_value-Parent_Email" =>
			array(
				'label' => 'Parent/Legal Guardian Email',
				'field' => 'text_value-Parent_Email',
				'class' => 'text_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"text_value-Parent_Street1" =>
			array(
				'label' => 'Parent/Legal Guardian Street 1',
				'field' => 'text_value-Parent_Street1',
				'class' => 'text_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"text_value-Parent_Street2" =>
			array(
				'label' => 'Parent/Legal Guardian Street 2',
				'field' => 'text_value-Parent_Street2',
				'class' => 'text_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => false,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"text_value-Parent_City" =>
			array(
				'label' => 'Parent/Legal Guardian City',
				'field' => 'text_value-Parent_City',
				'class' => 'text_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"text_value-Parent_State" =>
			array(
				'label' => 'Parent/Legal Guardian State/Province',
				'field' => 'text_value-Parent_State',
				'class' => 'text_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"text_value-Parent_Zip" =>
			array(
				'label' => 'Parent/Legal Guardian Zip/Postal',
				'field' => 'text_value-Parent_Zip',
				'class' => 'text_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 15,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"text_value-Parent_Country" =>
			array(
				'label' => 'Parent/Legal Guardian Country',
				'field' => 'text_value-Parent_Country',
				'class' => 'text_contact',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => true,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),

			"url-website" =>
			array(
				'label' => 'Website',
				'field' => 'url-website',
				'class' => 'contact_url',
				'type' => 's',
				'input_type' => 'text',
				'length' => 150,
				'default' => '',
				'mandatory' => false,
				'classes' => '',
				'options' => null,
				'help_text' => ''
			),
		);

		$max_children = 6;

		for ($i = 1; $i <= $max_children; $i++) {

			$fields_array["chcid-$i"] =
				array(
					'label' => "Child ID $i",
					'field' => "chcid-$i",
					'class' => 'children_contact',
					'type' => 's',
					'input_type' => 'hidden',
					'length' => 10,
					'default' => '',
					'mandatory' => false,
					'classes' => '',
					'options' => null,
					'help_text' => ''
				);

			$fields_array["child_first_name-$i"] =
				array(
					'label' => "Child $i First Name",
					'field' => "child_first_name-$i",
					'class' => 'children_contact',
					'type' => 's',
					'input_type' => 'text',
					'length' => 50,
					'default' => '',
					'mandatory' => false,
					'classes' => '',
					'options' => null,
					'help_text' => ''
				);

			$fields_array["child_birth_date-$i"] =
				array(
					'label' => "Child $i Birth Date",
					'field' => "child_birth_date-$i",
					'class' => 'children_contact',
					'type' => 's',
					'input_type' => 'date',
					'length' => 10,
					'default' => '',
					'mandatory' => false,
					'classes' => '',
					'options' => null,
					'help_text' => ''
				);
		}




		return $fields_array;
	} // endi capi_watc_get_fields


	function capi_fill_fields_array(&$fields_array, $cid)
	{

		//error_log("capi_fill_fields_array: cid $cid");

		//	$data=urldecode(capi_process_api("operation=getindividual&cid=$cid",0,0));
		$data = ($this->capi_process_api("operation=getindividual&cid=$cid", 0, 0));
		//error_log("data:$data");
		$individual_array = $this->capi_parse_xml2($data);
		//	print_r($individual_array);
		//error_log("individual_array_children:".print_r($individual_array['result']['children'],true));

		//	$fields_array["last_name"]["default"]=$individual_array["result"]["last_name"];	

		// fill in the default value in our fields_array with values from CAPI to backend.

		foreach ($fields_array as $field) {
			//	error_log("field:".$field["field"]." class:".$field["class"]);
			//	print("field:".$field["field"]." class:".$field["class"])."<br>";
			if (strpos($field["field"], "-") > 0) {
				// field name is in format fieldname-type where type is specific to email, phone, etc.

				// there can be multiple occurrences in the $individual_data array

				$key_array = explode("-", $field["field"]);

				if ($field["class"] == "email_contact") {
					//error_log(print_r($individual_array["result"],true));
					// loop through emails to find the correct type

					$email = null;
					if (isset($individual_array["result"]["email"]["email"][0])) {
						// there are multiple email addresses, find the primary email address
						$primary_email = "";
						foreach ($individual_array["result"]["email"]["email"] as $email) {
							//error_log("email:	".$email["email"]);			
							//error_log("primary_email:	".$email["primary_email"]);			
							if (strtolower($email["primary_email"]) == "1") {
								// this is the primary email address
								$primary_email = $email;
							} // end if
						} // end foreach
					} else {
						// only one email address, must be primary
						$primary_email = $individual_array["result"]["email"]["email"];
					}


					if (is_array($primary_email)) {
						$fields_array[$field["field"]]["default"] = urldecode($primary_email["email"]);
						$fields_array["cc_email-home"]["default"] = urldecode($primary_email["cc_email"]);
					} // end if is_array

				} else if ($field["class"] == "contact_phone") {  // end if field[class]=email

					if (isset($individual_array["result"]["phones"]["phone"][0]))
						$individual_array["result"]["phones"] = $individual_array["result"]["phones"]["phone"];

					// loop through phones to find the correct type

					if (is_array($individual_array["result"]["phones"])) {
						foreach ($individual_array["result"]["phones"] as $phone) {
							if (strtolower($phone["phone_type"]) == $key_array["1"]) {
								$fields_array[$field["field"]]["default"] = $phone[$key_array[0]];
							} // end if
						} // end foreach
					} // end if is_array


				} else if ($field["class"] == "children_contact") {  // end if field[class]=children_contact

					if (isset($individual_array["result"]["children"]["child"][0]))
						$individual_array["result"]["children"] = $individual_array["result"]["children"]["child"];

					// loop through children to find correct one
					//error_log("childrene: ".print_r($individual_array["result"]["children"],true));

					if (is_array($individual_array["result"]["children"])) {
						foreach ($individual_array["result"]["children"] as $child) {
							error_log("match: " . $child["c"] . " - " . $key_array["1"]);
							if ($child["c"] == $key_array["1"]) {
								$fields_array[$field["field"]]["default"] = $child[$key_array[0]];
							} // end if
						} // end foreach
					} // end if is_array


				} else if ($field["class"] == "address") {  // end if field[class]=phone
					//error_log("address");				
					// loop through emails to find the correct type
					if (isset($individual_array["result"]["addresses"]["address"][0]))
						$individual_array["result"]["addresses"] = $individual_array["result"]["addresses"]["address"];

					//error_log(print_r($individual_array["result"]["addresses"],true));				


					if (is_array($individual_array["result"]["addresses"])) {
						foreach ($individual_array["result"]["addresses"] as $address) {
							//error_log("address type:".$address["address_type"]."-".$key_array[0]);				
							if (strtolower($address["address_type"]) == $key_array["1"]) {
								$fields_array[$field["field"]]["default"] = urldecode($address[$key_array[0]]);
							} // end if
						} // end foreach
					} // end if is_array

				} else if ($field["class"] == "contact_url") {  // end if field[class]=address
					// loop through emails to find the correct type


					$urls = null;
					if (isset($individual_array["result"]["urls"]["url"][0])) {
						$urls = $individual_array["result"]["urls"]["url"];
					} else if (is_array($individual_array["result"]["urls"]))
						$urls = $individual_array["result"]["urls"];
					if (!is_null($urls))
						foreach ($urls as $url) {
							if (strtolower($url["url_type"]) == strtolower($key_array["1"])) {
								$fields_array[$field["field"]]["default"] = urldecode($url[$key_array[0]]);
							} // end if
						} // end foreach

					/*
	
					if (isset($individual_array["result"]["urls"]["url"][0])) {
						$urls=$individual_array["result"]["urls"]["url"];
					} else
						$urls=$individual_array["result"]["urls"];
					foreach ($urls as $url) {
						if (strtolower($url["url_type"])==$key_array["1"]) {
							$fields_array[$field["field"]]["default"]=urldecode($url[$key_array[0]]);
						} // end if
					} // end foreach
	*/
				} else if ($field["class"] == "text_contact") {  // end if field[class]=contact_url
					// loop through texts to find the correct type
					$texts = null;
					if (isset($individual_array["result"]["texts"]["text"][0])) {
						$texts = $individual_array["result"]["texts"]["text"];
					} else if (is_array($individual_array["result"]["texts"]))
						$texts = $individual_array["result"]["texts"];
					if (!is_null($texts))
						foreach ($texts as $text) {
							if (strtolower($text["text_type"]) == strtolower($key_array["1"])) {
								$fields_array[$field["field"]]["default"] = urldecode($text[$key_array[0]]);
							} // end if
						} // end foreach

				} else if ($field["class"] == "subscription_contact") {  // end else if field[class]=text_contact
					// loop through texts to find the correct type

					$subscriptions = null;
					if (isset($individual_array["result"]["subscriptions"]["subscription"][0])) {
						$subscriptions = $individual_array["result"]["subscriptions"]["subscription"];
					} else if (is_array($individual_array["result"]["subscriptions"]))
						$subscriptions = $individual_array["result"]["subscriptions"];
					if (!is_null($subscriptions))
						foreach ($subscriptions as $subscription) {
							//	error_log("subscription_contact: ".$subscription["sid"]." key: ".$key_array["1"]." =:".($subscription["sid"]==$key_array["1"]));
							if ($subscription["sid"] == $key_array["1"] && $subscription["subscription_type"] == 1) {
								//	error_log("subscription setting ".$fields_array[$field["field"]]["default"]);
								$fields_array[$field["field"]]["default"] = 1;
							} // end if
						} // end foreach

				}  // end else if field[class]=subscription_contact



			} else if ($field["class"] == "handicap_contact") {  // end if field[class]=address zzz
				// loop through handicaps to get active handicap
				if (is_array($individual_array["result"]["handicaps"])) {
					if (isset($individual_array["result"]["handicaps"]["handicap"][0]))
						$handicaps = $individual_array["result"]["handicaps"]["handicap"];
					else
						$handicaps = $individual_array["result"]["handicaps"];
					foreach ($handicaps as $handicap) {

						if ($handicap['hc_status'] == 'A')
							$fields_array[$field["field"]]["default"] = $handicap[$field["field"]];
					} // end foreach
				} // end if is_array

			} else {
				if (isset($individual_array["result"][$field["field"]])) {
					$fields_array[$field["field"]]["default"] = $individual_array["result"][$field["field"]];
				}
			}
		}
	} // end capi_fill_fields_array

	function capi_send_text_message()
	{

		if (!current_user_can('administrator') && !current_user_can('editor')) {
			$output .= "Security Violation";
			return $output;
		}

		// get subscription list
		$query = "operation=get_subcription_list&delivery_type=1";

		$capi_result = $this->capi_parse_xml2($this->capi_process_api($query));
		$subscriptions = $capi_result["result"]["subscriptions"];
		if (isset($subscriptions["subscription"][0])) $subscriptions = $subscriptions["subscription"];

		$cid = $this->capi_check_profile();
		$fields_array = $this->capi_get_fields();

		$this->capi_fill_fields_array($fields_array, $cid);

		$output = "<h2>Send Text Message</h2>";

		$output .= $this->capi_theme_input($form, array("field" => "cid", "input_type" => "hidden", "type" => "s", "default" => $cid));
		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 1));
		$output .= $this->capi_theme_input($form, array("field" => "module", "input_type" => "hidden", "type" => "s", "default" => "message"));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "broadcast_email"));


		$output .= $this->capi_theme_input($form, array(
			"label" => "Reply To Email",
			"field" => "from_email",
			"input_type" => "text",
			"default" => $fields_array['email-home']["default"],
			"max_length" => 150
		));
		$output .= $this->capi_theme_input($form, array(
			"label" => "From Name",
			"field" => "from_name",
			"input_type" => "text",
			"default" => $fields_array['first_name']["default"] . " " . $fields_array['last_name']["default"],
			"max_length" => 150
		));



		$result .= '
			  <div class="content">
			    <form action="/admin/config/system/sms_alert/send" method="post" id="sms-alert-send-form" accept-charset="UTF-8"><div><div class="form-item form-type-textfield form-item-from">
			  <label for="edit-from">From <span class="form-required" title="This field is required.">*</span></label>
			 <input type="text" id="edit-from" name="from" value="paul@paulcindy.com" size="100" maxlength="200" class="form-text required" />
			<div class="description">Reply To Email Address</div>
			</div>
			<br>
			<div class="form-item form-type-textfield form-item-message">
			  <label for="edit-message">Message <span class="form-required" title="This field is required.">*</span></label>
			 <input type="text" id="edit-message" name="message" value="" size="150" maxlength="150" class="form-text required" />
			<div class="description">Max of 150 characters.</div>
			</div>
			<br>
			<br>Check no boxes below for all recipients.<br><br>			
		';
		foreach ($subscriptions as $subscription) {

			$sid = $subscription['sid'];
			$subscription_name = $subscription['subscription_name'];

			$result .= "
				<div class='capi_subscription'>
				 <input type='checkbox' id='sid_$sid' name='sid_$sid' value='0' class='capi_subscription_checkbox' />
				 <label class='capi_subscription_label' for='sid_$sid'>$subscription_name</label>
				</div>
			";
		}

		$result .= '
			<br>
			
			<input type="submit" id="edit-submit" name="op" value="Send Message" class="form-submit" /></div></form>  </div>
			';
		return $result;
	} // end capi_broadcast_text_message							





	function capi_list_emails()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}
		$wiwid = $this->wiwid;

		if (!current_user_can('administrator') &&  !current_user_can('editor')) {
			return "Security Violation.";
		}



		$query = "module=message&operation=list_emails";

		$result = json_decode($this->capi_process_api($query), true);

		//return "<textarea>".print_r($result,true)."</textarea>";		
		$emails = $result["emails"];
		//		if (isset($emails["email"][0])) $emails=$emails["email"];
		$output = "<h1>Emails</h1>";

		$output .= "<div class='capi_admin_div'><a class='capi_button_link' href='" . get_site_url() . "/capi/admin'>Admin</a></div>";
		if (is_array($emails)) {
			$output .= "<table>";
			$output .= "<tr>";
			$output .= "<th>";
			$output .= "Subject";
			$output .= "</th>";
			$output .= "<th>";
			$output .= "Recipient Count";
			$output .= "</th>";
			$output .= "<th>";
			$output .= "Date Sent";
			$output .= "</th>";
			$output .= "<th>";
			$output .= "Recipient List";
			$output .= "</th>";
			$output .= "</tr>";

			$odd = true;
			foreach ($emails as $email) {

				$output .= "<tr ";
				if ($odd) $output .= "class='odd'";
				$odd = !$odd;
				$output .= ">";
				$output .= "<td>";
				$output .= stripcslashes($email["subject"]);
				$output .= "</td>";
				$output .= "<td>";
				$output .= $email["count"];
				$output .= "</td>";
				$output .= "<td>";
				$output .= date("m/d/Y", strtotime($email["queued"]));
				$output .= "</td>";
				$output .= "<td>";
				$output .= "<a href='" . get_site_url() . "/capi/show_email?subject=" . urlencode($email["subject"]) . "&queued=" . date("Y-m-d", strtotime($email["queued"])) . "'>Show</a>";
				$output .= "</td>";
				$output .= "</tr>";
			}
			$output .= "</table>";
		} else {
			$output .= "No events.";
		}


		//$output.= print_r($result,true);
		return $output;
	} // end capi_list_emails

	function capi_show_email()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}
		$wiwid = $this->wiwid;

		if (!current_user_can('administrator') &&  !current_user_can('editor')) {
			return "Security Violation.";
		}



		$query = "module=message&operation=show_email";

		$result = json_decode($this->capi_process_api($query), true);


		if (!$result['success']) {
			return $result['message'];
		}
		$emails = $result["emails"];
		//		if (isset($emails["email"][0])) $emails=$emails["email"];
		$output = "<h1>Recipients</h1>";
		$output .= "<a class='capi_button_link' href='" . get_site_url() . "/capi/list_emails'>Back to List Emails</a>";
		if (is_array($emails)) {
			$output .= "<table>";
			$odd = true;
			foreach ($emails as $email) {

				$output .= "<tr ";
				if ($odd) $output .= "class='odd'";
				$odd = !$odd;
				$output .= ">";
				$output .= "<td>";
				$output .= $email["last_name"];
				$output .= "</td>";
				$output .= "<td>";
				$output .= $email["first_name"];
				$output .= "</td>";
				$output .= "<td>";
				$output .= $email["email"];
				$output .= "</td>";
				$output .= "</tr>";
			}
			$output .= "</table>";
		} else {
			$output .= "No recipients.";
		}


		//$output.= print_r($result,true);
		return $output;
	} // end capi_show_email



	function capi_send_email()
	{

		// get subscription list
		$query = "operation=get_subcription_list&delivery_type=0";

		$capi_result = $this->capi_parse_xml2($this->capi_process_api($query));
		$subscriptions = $capi_result["result"]["subscriptions"];
		if (isset($subscriptions["subscription"][0])) $subscriptions = $subscriptions["subscription"];


		//		$cid=$_SESSION["cid"];
		$cid = $this->capi_check_profile();
		$fields_array = $this->capi_get_fields();

		$this->capi_fill_fields_array($fields_array, $cid);



		$output = "<h2>Broadcast Email</h2>";
		$result .= '
			<div class="content">
			<form action="/admin/config/system/sms_alert/send" method="post" id="sms-alert-send-form" accept-charset="UTF-8"><div><div class="form-item form-type-textfield form-item-from">
			<label for="edit-from">Reply To Email<span class="form-required" title="This field is required.">*</span></label>
			<input type="text" id="edit-from" name="from" value="' . $fields_array['email']["default"] . '" size="100" maxlength="200" class="form-text required" />
			</div>
			<label for="edit-from">From Name<span class="form-required" title="This field is required.">*</span></label>
			<input type="text" id="edit-from" name="from" value="' . $fields_array['first_name']["default"] . " " . $fields_array['last_name']["default"] . '" size="100" maxlength="200" class="form-text required" />
			</div>

			<div class="form-item form-type-textfield form-item-message">
			<label for="edit-message">Subject <span class="form-required" title="This field is required.">*</span></label>
			<input type="text" id="edit-message" name="message" value="" size="150" maxlength="150" class="form-text required" />
			</div>
			<div class="form-item form-type-textfield form-item-message">
			<label for="edit-message">Body <span class="form-required" title="This field is required.">*</span></label>
			<textarea rows=10 cols=10 id="edit-message" name="message" value="" /></textarea>
			</div>
		';

		$form = "broadcast_email";
		$output .= '    
		<div id="form_div">
  
    <form id="' . $form . '" name="' . $form . '" class="search">
		';


		$output .= $this->capi_theme_input($form, array("field" => "cid", "input_type" => "hidden", "type" => "s", "default" => $cid));
		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 1));
		$output .= $this->capi_theme_input($form, array("field" => "module", "input_type" => "hidden", "type" => "s", "default" => "message"));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "broadcast_email"));


		$output .= $this->capi_theme_input($form, array(
			"label" => "Reply To Email",
			"field" => "from_email",
			"input_type" => "text",
			"default" => $fields_array['email-home']["default"],
			"max_length" => 150
		));
		$output .= $this->capi_theme_input($form, array(
			"label" => "From Name",
			"field" => "from_name",
			"input_type" => "text",
			"default" => $fields_array['first_name']["default"] . " " . $fields_array['last_name']["default"],
			"max_length" => 150
		));

		$output .= $this->capi_theme_input($form, array(
			"label" => "Subject",
			"field" => "subject",
			"input_type" => "text",
			"default" => "",
			"max_length" => 150
		));

		$output .= $this->capi_theme_input($form, array(
			"label" => "Body",
			"field" => "body",
			"input_type" => "textarea",
			"default" => "",
			"max_length" => 15000
		));

		$output .= "<br><br><b>Groups</b><br><br>";
		$sid_list = "";
		foreach ($subscriptions as $subscription) {

			$sid_list .= "," . $subscription['sid'];
			$output .= $this->capi_theme_input($form, array(
				'label' => $subscription['subscription_name'],
				'field' => 'sid-' . $subscription['sid'],
				'input_type' => 'checkbox',
			));
		}
		$sid_list = ltrim($sid_list, ",");

		$output .= "<br><br><b>Send to </b><br><br>";
		$output .= $this->capi_theme_input($form, array(
			'label' => "Active",
			'field' => 'active',
			'input_type' => 'checkbox',
			'default' => 'checked',
		));

		$output .= $this->capi_theme_input($form, array(
			'label' => "Inactive",
			'field' => 'inactive',
			'input_type' => 'checkbox',
		));

		$output .= "<br><br><b>Send to </b><br><br>";

		$output .= $this->capi_theme_input($form, array(
			'label' => "Regular",
			'field' => 'regular',
			'input_type' => 'checkbox',
			'default' => 'checked',
		));

		$output .= $this->capi_theme_input($form, array(
			'label' => "Associate",
			'field' => 'associate',
			'input_type' => 'checkbox',
		));


		$output .= '<div id="message_div"></div>';

		$output .= "<input type='hidden' name='sid_list' value='$sid_list'>";
		$output .= '<div style="height:10px">&nbsp;</div><center><p>
      <input type=button id=send_button name=send_button value="Send">
			</form>
			';


		$output .= '	
	 <script type="text/javascript" src="/capi/jquery.mask.min.js"></script>
		<script>
  
    jQuery(document).ready(function(){
    
		';

		$output .= "
	      jQuery('#send_button').bind('click', capi_send_button_click);
				
        jQuery('#" . $form . "').submit(function(e){
            e.preventDefault();
        });

	 ";



		$output .= ' 
	 
	    });  // end document ready
       </script>
      ';

		$output .= '
		

		<script>

			
			
			function capi_send_button_click() {
		
				
				';
		$output .= "
        var formData = jQuery('#" . $form . "').serializeArray();

        jQuery('#send_button').hide();
        jQuery('#message_div').html('Sending');
        jQuery.ajax({
									
        url: '";

		$output .= get_site_url() . "/capi/process";

		$output .= "',
          type: 'POST',
					data: formData,
	         dataType: 'json',
          success:  function(data){
						if (data['success']) {
              jQuery('#message_div').html(data['message']);
			        jQuery('#send_button').show();
//							alert('Save Complete');
//              jQuery('#form_div').html('Save Complete.');//  <a href=/capi/member_home>Return to the Dashboard</a>');
//							location.reload();
						} else {
							alert('Error. '+data['messsage']);
			        jQuery('#send_button').show();
              jQuery('#message_div').html(data['message']);
						}
          },
          error: function( jqXHR, textStatus, errorThrown){
             console.log(jqXHR.responseText);
              alert('failure: ' + textStatus + ' - ' + errorThrown);
          }   
        });        
      ";
		$output .= '
	
			}


		</script>

		';


		return $output;
	} // end capi_send_email

	function capi_mass_subscribe()
	{

		// get subscription list
		$query = "operation=get_subcription_list&delivery_type=0";

		$capi_result = $this->capi_parse_xml2($this->capi_process_api($query));
		$subscriptions = $capi_result["result"]["subscriptions"];
		if (isset($subscriptions["subscription"][0])) $subscriptions = $subscriptions["subscription"];

		$output = "<h2>Mass Subscribe</h2>";

		$form = "mass_subscribe_form";
		$output .= '    
		  
				<div id="message_div"></div>
				<div id="form_div">
		  
		    <form id="' . $form . '" name="' . $form . '" class="search">
		      
		     ';
		//      $output.= capi_watc_theme_input($form,"","pmcpid","hidden",$my_pm_contact_process_bll->get_field("pmcpid"));

		$output .= $this->capi_theme_input($form, array("field" => "cid", "input_type" => "hidden", "type" => "s", "default" => $cid));
		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 1));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "mass_subscribe"));

		$output .= $this->capi_theme_input($form, array(
			"label" => "Membership Status:", "field" => "status_flag", "input_type" => "select", "type" => "s", "default" => "A",
			"options" => array("A" => "Active", "I" => "Inactive", "B" => "Both")
		));

		$output .= $this->capi_theme_input($form, array(
			"label" => "Golfer Status:", "field" => "member_type_flag", "input_type" => "select", "type" => "s", "default" => "R",
			"options" => array("R" => "Regular", "J" => "Junior", "B" => "Both")
		));

		$output .= $this->capi_theme_input($form, array(
			"label" => "Membership Type:", "field" => "member_type_flag", "input_type" => "select", "type" => "s", "default" => "R",
			"options" => array("R" => "Regular", "A" => "Associate", "B" => "Both")
		));

		$subscription_array = array();

		foreach ($subscriptions as $subscription) {
			$subscription_array[$subscription['sid']] = $subscription['subscription_name'];
		}

		$output .= $this->capi_theme_input($form, array(
			"label" => "Subscribe To:", "field" => "sid", "input_type" => "select", "type" => "s", "default" => "",
			"options" => $subscription_array
		));

		$output .= $this->capi_theme_input($form, array(
			"label" => "Empty List Before Subscribing:", "field" => "clear_first", "input_type" => "select", "type" => "s", "default" => "N",
			"options" => array("N" => "No. Keep All Subscribers", "Y" => "Yes. Empty List Then Subscribe")
		));


		/*
		      $output.= $this->capi_theme_input($form,$fields_array['first_name']);
		      $output.= $this->capi_theme_input($form,$fields_array['last_name']);
		      $output.= $this->capi_theme_input($form,$fields_array['birth_date']);
		      $output.= $this->capi_theme_input($form,$fields_array['email-home']);
		      $output.= $this->capi_theme_input($form,$fields_array['number-home']);
		
		      $output.= $this->capi_theme_input($form,$fields_array['street1-home']);
		      $output.= $this->capi_theme_input($form,$fields_array['street2-home']);
		      $output.= $this->capi_theme_input($form,$fields_array['city-home']);
		      $output.= $this->capi_theme_input($form,$fields_array['state-home']);
		      $output.= $this->capi_theme_input($form,$fields_array['zip-home']);
*/
		$output .= '<div style="height:40px">&nbsp;</div><center><p>
		      <input type=button id=subscribe_button name=subscribe_button value="Subscribe">
					</p>
					
					</center>
		    </form>
				
				</div> <!-- form_div -->
				';
		//
		$output .= '	
		<script>
  
    jQuery(document).ready(function(){
    
		';

		$output .= "
	      jQuery('#subscribe_button').bind('click', capi_subscribe_button_click);
				
        jQuery('#" . $form . "').submit(function(e){
            e.preventDefault();
        });

	 ";



		$output .= ' 
	 
	 
	    });  // end document ready
       </script>
      ';

		$output .= '
		

		<script>

			
			
			function capi_subscribe_button_click() {
		
				';
		$output .= "
        var formData = jQuery('#" . $form . "').serializeArray();

        jQuery('#message_div').html('Saving');
        jQuery.ajax({
									
        url: '";

		$output .= get_site_url() . "/capi/process";

		$output .= "',
          type: 'POST',
					data: formData,
         dataType: 'json',
          success:  function(data){
						if (data['success']) {
              jQuery('#message_div').html(data['message']);
//              jQuery('#form_div').html('Subscribe Complete.');//  <a href=/capi/member_home>Return to the Dashboard</a>');
							alert('Subscribe complete.');
						} else {
							alert('Error. '+data['messsage']);
              jQuery('#message_div').html(data['message']);
						}
          },
          error: function( jqXHR, textStatus, errorThrown){
             console.log(jqXHR.responseText);
              alert('failure: ' + textStatus + ' - ' + errorThrown);
          }   
        });        
      ";
		$output .= '
	
			}


		</script>

		';

		return $output;
	} // end capi_mass_subscribe


	function capi_manage_commerce()
	{

		$result = "<h2>Manage Commerce Gateway</h2>";
		if (!current_user_can('administrator') && !current_user_can('editor')) {
			return "Administrators only.";
		}

		$evoid = 0;
		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}

		$query = "module=ecommerce&operation=get_edit_payment_gateway_form";

		$result = "";

		$result .= ($this->capi_process_api($query));

		return $result;
	} // end capi_manage_commerce							

	function capi_manage_site_subscriptions()
	{

		$result = "<h2>Manage Broadcast Groups</h2>";
		if (!current_user_can('administrator') && !current_user_can('editor')) {
			return "Administrators only.";
		}

		$sid = "";
		$delete = 0;
		$confirm = 0;
		$subscription_name = "";
		$output = "";

		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}

		if ($delete) {

			if ($confirm) {
				// do the delete
				$query = "operation=delete_site_subscription&sid=$sid";

				$result = "";

				$result = json_decode($this->capi_process_api($query), true);

				$output = $result['message'];

				$output .= "<a href='" . get_site_url() . "/capi/manage_site_subscriptions/'>Return</a>";
			} else {
				// create the confirmation screen
				$output .= "<a href='" . get_site_url() . "/capi/manage_site_subscriptions/?delete=1&sid=$sid&confirm=1&subscription_name=$subscription_name'>Confirm Delete $subscription_name</a>";
				$output .= "<br><br>";
				$output .= "<a href='" . get_site_url() . "/capi/manage_site_subscriptions/'>Cancel</a>";
			}
		} else {

			$query = "operation=get_manage_site_subscriptions&sid=$sid";

			$result = "";

			$result .= ($this->capi_process_api($query));

			$output = $result;
		}

		return $output;
	} // end capi_manage_commerce							

	function capi_unsubscribe()
	{

		$result = "<h2>Unsubscribe</h2>";

		$sid = 0;
		$cid = 0;
		$confirmed = 0;
		$param = "";

		foreach ($_REQUEST as $getvar => $getval) {
			${$getvar} = $getval;
		}

		if ($param == "") {
			$output = "Invalid";
		} else {

			$result_array = explode("-", $param);
			$sid = $result_array[0];
			$cid = $result_array[1];
			$email = $result_array[2];

			$query = "operation=unsubscribe&sid=$sid&cid=$cid&email=$email";

			$result = "";

			$result .= ($this->capi_process_api($query));

			$result = $this->capi_parse_xml2($result);
			$result = $result['result'];

			if ($result['success'] != '1') {
				$output = $result['message'];
			} else {
				if ($confirmed) {
					$output = $result['message'];
				} else {
					$subscription_name = $result['subscription_name'];
					$output = "<a href='" . get_site_url() . "/capi/unsubscribe/?param=$sid-$cid-$email&confirmed=1'>Click to Unsubscribe to $subscription_name</a>";
				}
			}

			//			$output=$result['message'];
			//			$output=print_r($result,true);
			///			$output=$query;

		}

		return $output;
	} // end capi_manage_commerce							

	function capi_get_document()
	{


		$query .= "&dmsdid=7&module=dms&operation=get_document";

		$document = json_decode($this->capi_process_api($query));

		$result = "<h2>" . $document->dms_type_name . "</h2>";


		$result .= "Date: " . $document->dms_date;
		$result .= "<br/>";
		if ($document->dmsc_from > "") {
			$result .= "From: " . $document->dmsc_from;
			$result .= "<br/>";
		}
		if ($document->dmsc_to > "") {
			$result .= "To: " . $document->dmsc_to;
			$result .= "<br/>";
		}
		if ($document->dmsc_subject > "") {
			$result .= "Subject: " . $document->dmsc_subject;
			$result .= "<br/>";
		}
		$result .= "<br/>";
		$result .= $document->dms_body;
		$result .= "<br/>";

		return $result;
	} // end capi_get_document							

	function capi_get_document_list()
	{

		$dmstid = 0;

		foreach ($_REQUEST as $key => $value)
			${$key} = $value;


		$query .= "&dmstid=$dmstid&module=dms&operation=get_document_list";

		$capi_result = json_decode($this->capi_process_api($query));
		//$result.= print_r($capi_result,true);		

		$documents = $capi_result->documents;

		$result = "<h2>" . $capi_result->dms_type_name . "</h2>";
		$result .= "<table class='capi_dms_table'>";
		$result .= "<td>Date</td>";
		if ($dmstid == 1) {
			$result .= "<td>From</td>";
			$result .= "<td>Subject</td>";
		} else if ($dmstid == 2) {
			$result .= "<td>Match Number</td>";
			$result .= "<td>Result</td>";
			$result .= "<td>Venue</td>";
		} else if ($dmstid == 3) {
			$result .= "<td>Location</td>";
		}
		foreach ($documents as $document) {

			$result .= "<tr>";
			$result .= "<td><a href='" . get_site_url() . "/capi/get_document?dmsdid=" . $document->dmsdid . "'>" . $document->dms_date . "</a></td>";
			if ($dmstid == 1) {
				$result .= "<td>" . $document->dmsc_from . "</td>";
				$result .= "<td>" . $document->dmsc_subject . "</td>";
			} else if ($dmstid == 2) {
				$result .= "<td>" . $document->dmsmr_match_number . "</td>";
				$result .= "<td>" . $document->dmsmr_result . "</td>";
				$result .= "<td>" . $document->dmsmr_venue . "</td>";
			} else if ($dmstid == 3) {
				$result .= "<td>" . $document->dmsmm_location . "</td>";
			}

			$result .= "</tr>";
		}
		$result .= "</table>";

		return $result;
	} // end capi_get_document_list							


	function capi_edit_tournament_fields()
	{

		$evoid = 0;

		if (!current_user_can('administrator') && !current_user_can('editor')) {
			return "No permission for this page.";
		}

		foreach ($_REQUEST as $key => $value)
			${$key} = $value;

		$result = "<h2>Edit Tournament Fields</h2>";

		$query = "evoid=$evoid&module=event&operation=get_event_custom_field_list_form";

		$result = $this->capi_process_api($query);


		return $result;
	} // end capi_edit_tournament_fields							




	function capi_edit_tournament_field_submit()
	{

		$my_return = array(
			"success" => 0,
			"message" => "None",
		);

		if (!current_user_can('administrator') && !current_user_can('editor')) {
			$my_return['message'] = "No permission for this page.";
			print json_encode($my_return);
			exit;
		}

		$query = "module=event&operation=do_edit_event_custom_field_form_submit";

		foreach ($_REQUEST as $key => $value) {
			${$key} = $value;
			$query .= "&$key=" . urlencode($value);
		}


		$result = $this->capi_process_api($query);


		print $result;
		exit;
	} // end capi_edit_tournament_field_submit

	function capi_delete_tournament_field()
	{

		$my_return = array(
			"success" => 0,
			"message" => "None",
		);

		if (!current_user_can('administrator') && !current_user_can('editor')) {
			$my_return['message'] = "No permission for this page.";
			print json_encode($my_return);
			exit;
		}

		$query = "module=event&operation=do_edit_event_custom_field_delete";

		foreach ($_REQUEST as $key => $value) {
			${$key} = $value;
			$query .= "&$key=" . urlencode($value);
		}


		$result = $this->capi_process_api($query);


		print $result;
		exit;
	} // end capi_delete_tournament_field

	function capi_edit_tournament_field()
	{

		$evoid = 0;

		if (!current_user_can('administrator') && !current_user_can('editor')) {
			return "No permission for this page.";
		}

		foreach ($_REQUEST as $key => $value)
			${$key} = $value;

		$result = "";

		//		$query="evoid=$evoid&module=event&operation=get_edit_event_custom_field_form&efid=$efid&effid=$effid";
		$query = "evoid=$evoid&module=event&operation=get_edit_event_custom_field_form&effid=$effid";

		$result = $this->capi_process_api($query);


		print $result;
		exit;
	} // end capi_edit_tournament_field							

	function capi_course_search_form()
	{
		$output = "";
		/*	
		$output.='
		<style>
		.content-p {
		    width: 90%;
				float: left;
				padding-right:0;
		}
		input[type="text"], input[type="email"] {
		    background: #f1f1f1 none repeat scroll 0 0;
		    border: medium none;
		    color: #131313;
		    margin: 0;
		    padding: 2px;
		    text-align: left;
				width: 90%;
		}
		select {
		    background: #f1f1f1 none repeat scroll 0 0;
		    border: medium none;
		    border-radius: 0;
		    color: #1723a9;
		    display: block;
		    height: 45px;
		    padding: 2px;
		    text-align: left;
		    width: 90%;
		}
		
		#capi_club_search_results {
			padding:10px;
		}
		
		div.capi_club_list {
			width:90%;
		}
		
		div.capi_club_list_row {
			width:100%;
		}
	
		div.capi_club_list_row::after {
			clear: both;
			content: "";
			display: table;
		}
	
		.capi_club_list_row.odd {
			background: #f0f0f0;
		}
		
		div.capi_club_list_club_name {
			float: left;
			width:40%;
			padding: 10px;
			cursor:hand; cursor:pointer;
		}
	
		div.capi_club_list_club_type {
			float: left;
			width:30%;
			padding: 10px;
		}
	
		div.capi_club_list_region {
			float: left;
			width:30%;
			padding: 10px;
		}
	
		</style>
		';
		
		$regionText="&IS_CLUB=TRUE";
*/

		$output .= '<div id=searchForm style="padding-left:10px;background-color:#ffffff;width:90%;max-width:750px;margin-left:auto;margin-right:auto;">

		<form action="" method="get" id="capi_course_directory_search_form">';

		$output .= "<br><div id='capi_course_search'>\n";
		$output .= '<div class="form-item" id="edit-name-wrapper" style="width:90%;">';

		$output .= '<input type="hidden" name="is_primary" value="0">';
		$output .= '<input type="hidden" name="page" value="api">';
		$output .= '<input type="hidden" name="operation" value="searchclub">';

		$output .= '<input type="hidden" name="facility" value="1">';

		$output .= '<label for="edit-name">Course Name: </label><br>';
		$output .= '<input type="text" maxlength="30" name="club_name" id="edit-name" size="30" value="" class="form-text"/>
	<br><br>
	<div class="form-item" id="edit-city-wrapper" style="width:90%;">
	 <label for="edit-city">City: </label><br>
	 <input type="text" maxlength="30" name="city" id="edit-city" size="30" value="" class="form-text"/>
	</div><br>';

		$output .= '<div class="form-item" id="edit-type-wrapper" style="width:90%;">
	 <label for="edit-type">Club Type: </label><br>
	 <input type="checkbox" name="cltid[]" value="9">&nbsp;Public&nbsp;&nbsp;
	 <input type="checkbox" name="cltid[]" value="8">&nbsp;Private&nbsp;&nbsp;
	 <input type="checkbox" name="cltid[]" value="11">&nbsp;Semi-Private&nbsp;&nbsp;
	 <input type="checkbox" name="cltid[]" value="7">&nbsp;Military&nbsp;&nbsp;
	 <input type="checkbox" name="cltid[]" value="10">&nbsp;Resort
	 </div>
  ';

		$output .= '<br><div class="form-item" id="edit-type-wrapper" style="width:90%;">
	 <label for="edit-type">Number of Holes: </label><br>
	 <input type="checkbox" name="number_of_holes[]" value="18H Reg">&nbsp;18&nbsp;&nbsp; 
	 <input type="checkbox" name="number_of_holes[]" value="27H Reg">&nbsp;27&nbsp;&nbsp; 
	 <input type="checkbox" name="number_of_holes[]" value="36H Reg">&nbsp;36&nbsp;&nbsp; 
	 <input type="checkbox" name="number_of_holes[]" value="54H Reg">&nbsp;54&nbsp;&nbsp; 
	 <input type="checkbox" name="number_of_holes[]" value="9H Reg">&nbsp;9&nbsp;&nbsp; 
	 <br>
	 <input type="checkbox" name="number_of_holes[]" value="18H Exe">&nbsp;18&nbsp;Executive&nbsp;&nbsp; 
	 <input type="checkbox" name="number_of_holes[]" value="9H Exe">&nbsp;9&nbsp;Executive&nbsp;&nbsp; 
	 <input type="checkbox" name="number_of_holes[]" value="9H Par 3">&nbsp;9&nbsp;Par&nbsp;3&nbsp;&nbsp;
	 </div>
  ';


		$output .= '
	<div style="clear:both;"> </div>
	
	<div id="capi_find_club">';
		$output .= "<br>";
		$output .= '
	<div id="capi_club_search_button"><button id="capi_club_directory_search">Search</button></div>
	<div id="capi_club_search_back" style="display:none;"><button id="capi_club_directory_back">Back to List</button></div>
	';
		/*
	if ($IS_CLUB) {
	 	$output.= '<input type="hidden" name="IS_CLUB" id="edit-isclub" value="TRUE"  class="form-submit" />';
	} else {
		$output.= '<input type="hidden" name="IS_CLUB" id="edit-isclub" value="FALSE"  class="form-submit" />';
	}
*/

		$output .= '</form></div></div>
	<div id="capi_club_search_results" style="clear:both;"></div>
	<div id="capi_club_search_one_club" style="clear:both;display:none;"></div>
	
	';

		$output .= '';


		$output .= "
			<script>
    jQuery(document).ready(function(){

			jQuery('#capi_club_directory_search').bind('click', capi_club_directory_search_click);
			jQuery('#capi_club_directory_back').bind('click', capi_club_directory_back_click);
               
	    jQuery('#capi_course_directory_search_form').submit(function(e){
	        e.preventDefault();
	    });
		});
			function capi_club_directory_back_click() {
					jQuery('#capi_club_search_results').show();
					jQuery('#capi_club_search_one_club').hide();
					jQuery('#capi_club_search_back').hide();
			}

			function capi_club_directory_search_click() {
				";
		$output .= "			
						jQuery('#capi_club_search_results').html('Searching...');
	                                  
	          var formData = jQuery('#capi_course_directory_search_form').serializeArray();
	          jQuery.ajax({
	            url: '" . get_site_url() . "/capi/course_directory_search/',
	            type: 'POST',
	            data: formData,
		          dataType: 'json',
	            success: function(data){
//alert(data);
								if (data['success']=='1') {
									jQuery('#capi_club_search_results').html(data['output']);
									jQuery('#capi_club_search_results').show();
									jQuery('#capi_club_search_one_club').hide();
									jQuery('#capi_club_search_back').hide();
								} else {
									alert('Error: '+data['message']);
								}
	            },
	            error:function( jqXHR, textStatus, errorThrown){
	            //  console.log('abc');
	               console.log(jqXHR.responseText);
	                alert('failure: ' + textStatus + ' - ' + errorThrown + '-' + jqXHR.responseText);
	            }   // end error  
	          }); // end ajax
			}
			";
		/*	
	$output.="			
	
			function capi_club_directory_parse(data) {
						formData={ 'club_xml': data };
	          jQuery.ajax({
	            url: '/capi/club_directory/search',
	            url: '".get_site_url()."/capi/course_directory_search',
	            type: 'POST',
	            data: formData,
	            dataType: 'json',
	            success: function(data){
							alert(data);
								if (data['success']=='1') {
									jQuery('#capi_club_search_results').html(data['output']);
								} else {
									jQuery('#capi_club_search_results').html(data['message']);
								}
	                
	            },
	            error:function( jqXHR, textStatus, errorThrown){
	            //  console.log('abc');
	               console.log(jqXHR.responseText);
	                alert('failure: ' + textStatus + ' - ' + errorThrown);
	            }   // end error  
	          }); // end ajax
			}
			";
*/

		$output .= "			 
			
			</script>
			";





		return $output;
	}

	function capi_get_texts($texts)
	{
		if (isset($texts['text'][0])) {
			$texts = $texts['text'];
		}
		$texts_array = array();
		foreach ($texts as $text) {
			$texts_array[$text['type']] = urldecode($text['text_value']);
		}
		return $texts_array;
	}

	function capi_get_phones($phones)
	{
		if (isset($phones['phone'][0])) {
			$phones = $phones['phone'];
		}
		$phones_array = array();
		foreach ($phones as $phone) {
			$phones_array[$phone['type']] = urldecode($phone['number']);
			if ($phone['primary']) {
				$phones_array['primary'] = urldecode($phone['number']);
			}
		}
		return $phones_array;
	}

	function capi_get_urls($urls)
	{
		if (isset($urls['url'][0])) {
			$urls = $urls['url'];
		}
		$urls_array = array();
		foreach ($urls as $url) {
			$urls_array[$url['type']] = urldecode($url['url']);
		}
		return $urls_array;
	}

	function capi_get_courses($courses)
	{
		if (isset($courses['course'][0])) {
			$courses = $courses['course'];
		}
		$courses_array = array();
		foreach ($courses as $course) {
			$courses_array[$course['cmcid']]['course_name'] = urldecode($course['course_name']);
			$tees = $course['tees'];
			if (isset($tees['tee'][0])) {
				$tees = $tees['tee'];
			}
			$cm_gender_array = array("0" => "Mixed", "1" => "Male", "2" => "Female");

			$output = "<table class='cm_tee_search_results search_results_table'>";
			$odd = true;
			$output .= "<tr class=\"table_header\">";
			$output .= "<td id=\"date_head\" class=\"table_header_col\">";
			$output .= "Tee Name";
			$output .= "</td>";
			$output .= "<td id=\"amount_col_head\" class=\"table_header_col\">";
			$output .= "Tee Gender";
			$output .= "</td>";
			$output .= "<td>";
			$output .= "Length";
			$output .= "</td>";
			$output .= "<td>";
			$output .= "Rating 18";
			$output .= "</td>";
			$output .= "<td>";
			$output .= "Slope 18";
			$output .= "</td>";
			$output .= "<td>";
			$output .= "Rating Front";
			$output .= "</td>";
			$output .= "<td>";
			$output .= "Rating Back";
			$output .= "</td>";
			$output .= "</tr>";

			foreach ($tees as $tee) {
				$output .= "<tr class=\"";
				$output .= $odd ? "odd" : "even";
				$output .= "\">";
				$output .= "<td>";
				$output .= "<a class='cm_tee_name' data-cmtid='" . urldecode($tee['cmtid']) . "'>";
				$output .= urldecode($tee['tee_name']);
				$output .= "</a>";
				$output .= "</td>";
				$output .= "<td>";
				$output .= $cm_gender_array[urldecode($tee['tee_gender'])];
				$output .= "</td>";
				$output .= "<td>";
				$output .= urldecode($tee['tee_length']);
				$output .= "</td>";
				$output .= "<td>";
				$output .= urldecode($tee['rating18']);
				$output .= "</td>";
				$output .= "<td>";
				$output .= urldecode($tee['slope18']);
				$output .= "</td>";
				$output .= "<td>";
				$output .= urldecode($tee['ratingfront9']) . "/" . urldecode($tee['slopefront9']);
				$output .= "</td>";
				$output .= "<td>";
				$output .= urldecode($tee['ratingback9']) . "/" . urldecode($tee['slopeback9']);
				$output .= "</td>";
			}
			$output .= "</table>";
			$courses_array[$course['cmcid']]['tees'] = $output;
		} // end foreach courses

		return $courses_array;
	}

	function capi_get_functions($functions)
	{
		if (isset($functions['fuctions']['function'][0])) {
			$functions = $functions['fuction'];
		}

		$functions_array = array();

		foreach ($functions as $function) {
			$functions_array[$function['function_name']]['name'] = urldecode($function['first_name']) . " " . urldecode($function['last_name']);
			if ($function['suffix'] > "")
				$functions_array[$function['function_name']]['name'] .= ", " . urldecode($function['suffix']);
		}
		return $functions_array;
	}

	function capi_get_addresses($addresses)
	{

		if (isset($addresses['addresses']['address'][0])) {
			$addresses = $addresses['address'];
		}
		$address_array = array();
		foreach ($addresses as $address) {
			if ($address['primary']) {
				$address_array['primary']['street1'] = $address['street1'];
				$address_array['primary']['street2'] = $address['street2'];
				$address_array['primary']['street3'] = $address['street3'];
				$address_array['primary']['city'] = $address['city'];
				$address_array['primary']['state'] = $address['state'];
				$address_array['primary']['zip'] = $address['zip'];

				$address_array[$address['type']]['street1'] = $address['street1'];
				$address_array[$address['type']]['street2'] = $address['street2'];
				$address_array[$address['type']]['street3'] = $address['street3'];
				$address_array[$address['type']]['city'] = $address['city'];
				$address_array[$address['type']]['state'] = $address['state'];
				$address_array[$address['type']]['zip'] = $address['zip'];
			}
		}

		return $output;
	}
	function capi_get_primary_address($addresses)
	{

		if (isset($addresses['address'][0])) {
			$addresses = $addresses['address'];
		}

		foreach ($addresses as $address) {
			if ($address['primary']) {
				$output = $address['street1'];
				$output .= "<br>";
				if ($address['street2'] > "") {
					$output .= $address['street2'];
					$output .= "<br>";
				}
				if ($address['street3'] > "") {
					$output .= $address['street3'];
					$output .= "<br>";
				}
				$output .= $address['city'] . ", " . $address['state'] . " " . $address['zip'];
			}
		}

		return $output;
	}

	function capi_get_course()
	{

		$my_return['success'] = 0;
		$my_return['message'] = 'Whoops!';
		$my_return['output'] = '';

		$club_cid = "";
		$output = "";

		foreach ($_REQUEST as $key => $value)
			${$key} = $value;

		if ($club_cid == "") {
			$my_return['message'] = 'Invalid Club';
			return json_encode($my_return);
		}
		$query = "operation=getclub&club_cid=$club_cid&facility=1";

		$result = $this->capi_parse_xml2($this->capi_process_api($query));
		$result = $result['result'];


		if (!$result['success']) {
			$my_return['success'] = 0;
			$my_return['message'] = $result['message'];
			//$my_return['message']="textarea".print_r($result,false)."</textarea>";
			print json_encode($my_return);
			exit;
		}

		$output .= "";

		$texts = $this->capi_get_texts($result['texts']);
		$phones = $this->capi_get_phones($result['phones']);
		$addresses = $this->capi_get_addresses($result['addresses']);
		$functions = $this->capi_get_functions($result['functions']);
		$courses = $this->capi_get_courses($result['courses']);
		$urls = $this->capi_get_urls($result['urls']);

		$output .= "<div class='capi_directory_club_name'>" . $result['club_name'] . "</div>\n";
		$output .= "<div class='capi_directory_top_left'>\n";
		$output .= "<div class='capi_directory_address'>";

		//$output.=print_r($result['addresses'],true)."abc<br>";

		$output .= $this->capi_get_primary_address($result['addresses']);
		$output .= "</div>\n";
		if (isset($phones['primary'])) {
			$output .= "<b>Phone: </b>" . $phones['primary'];
			$output .= "<br>";
		}
		if (isset($phones['fax'])) {
			$output .= "<b>Fax: </b>" . $phones['fax'];
			$output .= "<br>";
		}

		//			$output.=print_r($urls,true);

		if (isset($urls['Website'])) {
			$output .= '<b>Website: </b><a href="http://' . $urls['Website'] . '" target="_blank">CLICK HERE</a>';
			$output .= "<br>";
		}


		$output .= "<hr>";

		if ($result['region'] > "") {
			$output .= "<b>Status: </b>" . $result['region'];
			$output .= "<br>";
		}

		if ($result['year_founded'] > "") {
			$output .= "<b>Year Opened: </b>" . $result['year_founded'];
			$output .= "<br>";
		}

		if ($result['architect'] > "") {
			$output .= "<b>Architect: </b>" . $result['architect'];
			$output .= "<br>";
		}
		if ($result['club_type'] > "") {
			$output .= "<b>Type: </b>" . $result['club_type'];
			$output .= "<br>";
		}

		if ($result['number_of_holes'] > "") {
			$output .= "<b>Holes: </b>" . $result['number_of_holes'];
			$output .= "<br>";
		}

		if (isset($texts['Fee Range'])) {
			$output .= "<b>Fee Range: </b>" . $texts['Fee Range'];
			$output .= "<br>";
		}

		// I tried to duplicate $club 'region' here - can this set of data include it as well?

		if (isset($texts['Season'])) {
			$output .= "<b>Season: </b>" . $texts['Season'];
			$output .= "<br>";
		}

		$output .= "<hr>";

		if (isset($functions['Head Professional'])) {
			$output .= "<b>Head Professional: </b>" . $functions['Head Professional']['name'];
			$output .= "<br>";
		}
		if (isset($functions['Superintendent'])) {
			$output .= "<b>Superintendent: </b>" . $functions['Superintendent']['name'];
			$output .= "<br>";
		}
		if (isset($functions['General Manager'])) {
			$output .= "<b>General Manager: </b>" . $functions['General Manager']['name'];
			$output .= "<br>";
		}
		if (isset($functions['Director of Golf'])) {
			$output .= "<b>Director of Golf: </b>" . $functions['Director of Golf']['name'];
			$output .= "<br>";
		}

		$output .= "</div><!-- capi_directory_top_left --></div>\n";

		$output .= "<div class='capi_directory_top_right'>\n";

		$coursename = $result['club_name'];
		$coursestreet = $addresses['primary']['street1'];
		$coursecity = $addresses['primary']['city'];
		$coursestate = $addresses['primary']['state'];
		$coursezip = $addresses['primary']['zip'];
		$output .= '
		<!--google map--> 
			<div class="courseDirPostRight">
			    	<div id="courseDirWidgets">
			            <div id="courseDirGoogleMap"><iframe width="300" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=' .
			$coursename . '%20%20' .
			$coursestreet . '%20' .
			$coursecity . '%20%20' .
			$coursezip . '&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe></div>
			        </div>
			</div>
			';

		$output .= "</div><!-- capi_directory_top_right --></div>\n";
		$output .= "<div class='capi_directory_bottom' style='clear:both'>\n";
		$output .= "<hr>";
		if (isset($texts['Directions'])) {
			$output .= "<b>Directions: </b>" . $texts['Directions'];
			$output .= "<br>";
		}

		$output .= "</div> <!-- capi_directory_bottom -->\n";

		if (sizeof($courses) > 0) {
			$output .= "<div class='capi_directory_courses' style='clear:both'>\n";
			$output .= "<hr>";
			$output .= "<b>Course Rating Information</b><br>";
			foreach ($courses as $course) {
				$output .= "<b>" . $course['course_name'] . "</b>";
				$output .= "<br>";
				$output .= $course['tees'];
			}

			$output .= "</div> <!-- capi_directory_courses -->\n";
		}
		//		$output.=print_r($result['courses'],true);


		//		$output.="<br><br>x".print_r($result,true);

		$my_return["output"] = $output;
		$my_return["success"] = 1;
		print json_encode($my_return);

		exit;
	}

	function capi_course_directory_search()
	{

		$my_return['success'] = 0;
		$my_return['message'] = '';
		$my_return['output'] = '';

		$club_name = "";
		$city = "";

		foreach ($_REQUEST as $key => $value)
			${$key} = $value;

		//error_log('requested:'.print_r($_REQUEST,true)." club_name:$club_name");

		$query = "operation=searchclub&facility=1";

		if ($club_name > "")
			$query .= "&club_name=$club_name";

		if ($city > "")
			$query .= "&club_name=$city";

		$result = $this->capi_process_api($query);
		$result = $this->capi_parse_xml2($result);

		$result = $result['result'];


		if (!$result['success']) {
			$my_return['success'] = 0;
			$my_return['message'] = $result['message'];
			//$my_return['message']="textarea".print_r($result,false)."</textarea>";
			print json_encode($my_return);
			exit;
		}

		$club_array = $result['clubs'];

		$output = "";

		$row_count = $result["row_count"];

		$output .= "Found: $row_count<br><br>";

		if ($row_count == 0) {
			$my_return['success'] = 0;
			$my_return['output'] = $output;
			print json_encode($my_return);
			exit(0);
		}

		if ($row_count == 1) {
			$clubs = $club_array;
		} else {
			$clubs = $club_array["club"];
		}
		$output .= "<div class='capi_club_list'>\n";
		//$output.="sql:".$result["sql"];
		$odd = "odd";

		$output .= "<div class='capi_club_list_heading'>";

		$output .= "<div class='capi_club_list_heading_course_name'>";
		$output .= "Course Name";
		$output .= "</div>\n"; // capi_club_list_heading

		$output .= "<div class='capi_club_list_heading_city'>";
		$output .= "City";
		$output .= "</div>\n"; // capi_club_list_heading

		$output .= "<div class='capi_club_list_heading_type'>";
		$output .= "Type";
		$output .= "</div>\n"; // capi_club_list_heading

		$output .= "<div class='capi_club_list_heading_holes'>";
		$output .= "Holes";
		$output .= "</div>\n"; // capi_club_list_heading

		$output .= "</div>\n"; // capi_club_list_heading

		foreach ($clubs as $club) {
			$output .= "<div class='capi_club_list_row $odd'>";

			if ($odd > "") $odd = "";
			else $odd = "odd";
			$output .= "<div id='capi_club_list_club_name_" . $club["cid"] . "' data-cid='" . $club["cid"] . "' class='capi_club_list_club_name'>" . $club["club_name"] . "</div>";
			$output .= "<div class='capi_club_list_city'>" . $club["city"] . "</div>";
			$output .= "<div class='capi_club_list_type'>" . $club["club_type"] . "</div>";
			if ($club["number_of_holes"] == "None") $club["number_of_holes"] = "";
			$output .= "<div class='capi_club_list_holes'>" . $club["number_of_holes"] . "</div>";

			$output .= "</div>\n";
		}
		$output .= "</div>\n";


		$output .= "
			<script>
			jQuery('.capi_club_list_club_name').bind('click', capi_club_list_club_name_click);
	
			";

		$output .= "			
	
			function capi_club_list_club_name_click() {
				jQuery('#capi_club_search_one_club').html('Fetching...');
				jQuery('#capi_club_search_results').hide();
				jQuery('#capi_club_search_one_club').show();
				jQuery('#capi_club_search_back').show();
//				jQuery(window).scrollTop(jQuery('#capi-search-top-left').offset().top);
				cid=this.getAttribute('data-cid');
						formData={ 'club_cid': cid };
	          jQuery.ajax({
	            url: '" . get_site_url() . "/capi/get_course',
	            type: 'POST',
	            data: formData,
	            dataType: 'json',
	            success: function(data){
								if (data['success']=='1') {
									jQuery('#capi_club_search_one_club').html(data['output']);
								} else {
									jQuery('#capi_club_search_one_club').html(data['message']);
								}
	            },
	            error:function( jqXHR, textStatus, errorThrown){
	            //  console.log('abc');
	               console.log(jqXHR.responseText);
	                alert('failure: ' + textStatus + ' - ' + errorThrown);
	            }   // end error  
	          }); // end ajax
			}
			";


		$output .= "			 
			
			</script>
			";





		//	$output.="<br><br>sql: ".$club_array["result"]["sql"];
		//$output.="<br><br>clubarray: ".print_r($club_array,true);
		//	$my_return['message']=print_r($club_array,true);
		//	$my_return['message']=print_r($_REQUEST,true);
		//	$output.="<textarea>".$_REQUEST['club_xml']."</textarea>";

		$my_return['success'] = 1;
		$my_return['output'] = $output;
		print json_encode($my_return);
		exit(0);
	}

	function capi_local_changepassword($req)
	{
		// don't change passwords in wordpress.  causes problems.	
		$my_return = array(
			"message" => $message,
			"success" => 1,
		);

		return $my_return;
		// nothing executed beyond here.
		$wiu_password = "";
		$message = "";
		$success = 0;

		foreach ($req as $key => $value)
			${$key} = $value;

		if ($wiu_password == "") {
			$message = "invalid password";
			$success = 0;
		} else {
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
			wp_set_password($wiu_password, $user_id);
			$message = "Password set.";
			$success = 1;
		}


		$my_return = array(
			"message" => $message,
			"success" => $success,
		);

		return $my_return;
	} // end capi_change_localpassword

	function capi_contact_us_form()
	{

		$form = "contact_us_form";
		$output .= '    
		  
				<div id="message_div"></div>
				<div id="form_div">
		  
		    <form id="' . $form . '" name="' . $form . '" class="search">
		      
		     ';
		//      $output.= capi_watc_theme_input($form,"","pmcpid","hidden",$my_pm_contact_process_bll->get_field("pmcpid"));

		$output .= $this->capi_theme_input($form, array("field" => "cid", "input_type" => "hidden", "type" => "s", "default" => $cid));
		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 1));
		$output .= $this->capi_theme_input($form, array("field" => "module", "input_type" => "hidden", "type" => "s", "default" => "website_integration"));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "submit_contact_us"));
		$fieldspec = array(
			'label' => 'First Name',
			'field' => 'first_name',
			'class' => '',
			'type' => 's',
			'input_type' => 'text',
			'length' => 150,
			'default' => "",
			'mandatory' => true,
			'classes' => '',
			'options' => null,
			'help_text' => ''
		);
		$output .= $this->capi_theme_input($form, $fieldspec);

		$fieldspec = array(
			'label' => 'Last Name',
			'field' => 'last_name',
			'class' => '',
			'type' => 's',
			'input_type' => 'text',
			'length' => 150,
			'default' => "",
			'mandatory' => true,
			'classes' => '',
			'options' => null,
			'help_text' => ''
		);
		$output .= $this->capi_theme_input($form, $fieldspec);

		$fieldspec = array(
			'label' => 'Email',
			'field' => 'email',
			'class' => '',
			'type' => 's',
			'input_type' => 'text',
			'length' => 150,
			'default' => "",
			'mandatory' => true,
			'classes' => '',
			'options' => null,
			'help_text' => ''
		);
		$output .= $this->capi_theme_input($form, $fieldspec);


		$fieldspec = array(
			'label' => 'Message',
			'field' => 'message',
			'class' => '',
			'type' => 's',
			'input_type' => 'textarea',
			'length' => 1500,
			'default' => "",
			'mandatory' => true,
			'classes' => '',
			'options' => null,
			'help_text' => ''
		);
		$output .= $this->capi_theme_input($form, $fieldspec);

		$output .= '<div style="height:40px">&nbsp;</div><center><p>
		      <input type=button id=submit_contact name=submit_contact value="Submit">
					</p>
					
					</center>
		    </form>
				
				</div> <!-- form_div -->
				';


		$output .= '	
		<script>
  
    jQuery(document).ready(function(){
    
		';

		$output .= "
	      jQuery('#submit_contact').bind('click', capi_submit_contact_click);
				
        jQuery('#" . $form . "').submit(function(e){
            e.preventDefault();
        });

	 ";



		$output .= ' 
	 
	 
	    });  // end document ready
       </script>
      ';

		$output .= '
		

		<script>

			
			
			function capi_submit_contact_click() {

				// validate data
				
				validate_array=new Array;

				if (typeof validate_form_' . $form . '  === "function") {

					validate_array=validate_form_' . $form . '();
					
					if (!validate_array["success"]) {
						alert("Validation Errors.");
						jQuery("#message_div").html(validate_array["message"]);
						jQuery("html,body").scrollTop(0);
						return;
					}
				}
				
				';
		$output .= "

        var formData = jQuery('#" . $form . "').serializeArray();

        jQuery('#message_div').html('Saving');
        jQuery.ajax({
									
        url: '";

		$output .= get_site_url() . "/capi/process";

		$output .= "',
          type: 'POST',
					data: formData,
         dataType: 'json',
          success:  function(data){
						if (data['success']) {
              jQuery('#message_div').html(data['message']);
              jQuery('#form_div').html('Message Sent.');//  <a href=/capi/member_home>Return to the Dashboard</a>');
						} else {
							alert('Error. '+data['messsage']);
              jQuery('#message_div').html(data['message']);
						}
          },
          error: function( jqXHR, textStatus, errorThrown){
             console.log(jqXHR.responseText);
              alert('failure: ' + textStatus + ' - ' + errorThrown);
          }   
        });        
      ";
		$output .= '
	
			}


		</script>

		';

		//				$output.=capi_watc_input_form_javascript($form);

		/*		
				$output.='
				<script>
		
				'.capi_watc_validate_form_basic_bio_form().'
		
				</script>';
*/



		return $output;

		//		$result="<textarea>".print_r($fields_array,false)."</textarea>";

		//		$query="module=event&operation=get_my_registrations&evoid=$evoid";

		//		$result=$this->capi_parse_xml2($this->capi_process_api($query));
		//		$result="<textarea>".print_r($this->capi_process_api($query),false)."</textarea>";

		return $result;

		//return print_r($result,true);
		$result = $result["result"];

		if ($result['success'])
			return "Registration Complete. <a href='" . get_site_url() . "/capi/tournament_schedule'>Click here to return to the schedule</a>.";
		else
			return "Error. " . $result['message'];
	} // end capi_contact_us_form

	public function cell($cell)
	{
		return "<Cell><Data ss:Type=\"String\">" . $cell . "</Data></Cell>\n";
	}

	public function capi_resource()
	{
		$operation = $_REQUEST["operation"];
		unset($_REQUEST["operation"]);

		if (!isset($_SESSION))
			session_start();

		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		//		$cid=$_SESSION["cid"];
		$cid = $this->capi_check_profile();

		//		if ($edit_cid>0) $cid=$edit_cid;

		if (!isset($_REQUEST["ajax"])) $_REQUEST["ajax"] = 0;

		$output = "";

		if ($cid == 0) {
			$output = "You must be logged in.";
		} else {
			$query = "module=resource&operation=" . $operation;
			$result = ($this->capi_process_api($query));

			if ($_REQUEST["ajax"]) {
				//				print ($this->capi_process_api($query));
				print $result;
				exit(0);
			} else {
				$output .= "<div id='capi_breadcrumb' class='capi_breadcrumb'><br><br></div>";
				$output .= "<div id='capi' class='capi' style='overflow:auto'>";

				//				$output.=($this->capi_process_api($query));
				$output .= $result;

				$output .= "</div> <!-- end of capi -->";
			}
		}
		return $output;
	}



	//		$query="module=event&operation=get_event_regististration_form&evoid=$evoid&success_url=".get_site_url()."/capi/tournament_schedule";


	public	function capi_league_management_respond()
	{
		$output = "This is an old inquiry email. Please use updated invitation email.";
		return $output;
	}

	function capi_par_play()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		$edit_cid = 0;

		foreach ($_REQUEST as $key => $value)
			${$key} = $value;


		//		foreach($_REQUEST as $getvar => $getval){ ${$getvar} = $getval; } 

		if (!isset($_SESSION))
			session_start();

		//		$cid=$_SESSION["cid"];
		$cid = $this->capi_check_profile();

		if ($edit_cid > 0) $cid = $edit_cid;

		$fields_array = $this->capi_get_fields();
		$this->capi_fill_fields_array($fields_array, $cid);

		$output = "<h2>Par Play Address</h2>";
		$output .= "<b><p style='color:red;'>ONLY FILL OUT THIS FORM IF YOU WANT YOUR PAR PLAY DELIVERED TO AN ALTERNATE ADDRESS!</p></b>";
		$output .= "<br><br>";


		$form = "basic_bio_form";
		$output .= '    
		  
				<div id="message_div"></div>
				<div id="form_div">
		  
		    <form id="' . $form . '" name="' . $form . '" class="search">
		      
		     ';
		//      $output.= capi_watc_theme_input($form,"","pmcpid","hidden",$my_pm_contact_process_bll->get_field("pmcpid"));

		$output .= $this->capi_theme_input($form, array("field" => "cid", "input_type" => "hidden", "type" => "s", "default" => $cid));
		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 1));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "updatecontactall"));
		$output .= $this->capi_theme_input($form, array("field" => "email_notification", "input_type" => "hidden", "type" => "s", "default" => "rebelf@comcast.net"));


		$output .= $this->capi_theme_input($form, $fields_array['street1-par_play']);
		$output .= $this->capi_theme_input($form, $fields_array['street2-par_play']);
		$output .= $this->capi_theme_input($form, $fields_array['city-par_play']);
		$output .= $this->capi_theme_input($form, $fields_array['state-par_play']);
		$output .= $this->capi_theme_input($form, $fields_array['zip-par_play']);
		$output .= '<div style="height:40px">&nbsp;</div><center><p>
		      <input type=button id=save_button name=save_button value="Save">
					</p>
					
					</center>
		    </form>
				
				</div> <!-- form_div -->
				';



		$output .= '	
	 <script type="text/javascript" src="/capi/jquery.mask.min.js"></script>
		<script>
  
    jQuery(document).ready(function(){
    
		';

		$output .= "
	      jQuery('#save_button').bind('click', capi_save_button_click);
				
        jQuery('#" . $form . "').submit(function(e){
            e.preventDefault();
        });

	 ";



		$output .= ' 
	 
				jQuery("#' . $form . '_number-home").mask("999-999-9999");	 
	    });  // end document ready
       </script>
      ';

		$output .= '
		

		<script>

			
			
			function capi_save_button_click() {
		
				// validate data
				
				validate_array=new Array;

				if (typeof validate_form_' . $form . '  === "function") {

					validate_array=validate_form_' . $form . '();
					
					if (!validate_array["success"]) {
						alert("Validation Errors.");
						jQuery("#message_div").html(validate_array["message"]);
						jQuery("html,body").scrollTop(0);
						return;
					}
				}
				
				';
		$output .= "
//        jQuery('#" . $form . "_operation').val('updatecontactallcomplete');
//				alert(jQuery('#basic_bio_form_birth_date-day').val());
//				alert(jQuery('#basic_bio_form_birth_date').val());
        var formData = jQuery('#" . $form . "').serializeArray();

        jQuery('#message_div').html('Saving');
        jQuery.ajax({
									
        url: '";

		$output .= get_site_url() . "/capi/process";

		$output .= "',
          type: 'POST',
					data: formData,
         dataType: 'json',
          success:  function(data){
						if (data['success']) {
              jQuery('#message_div').html(data['message']);
							alert('Save Complete');
//              jQuery('#form_div').html('Save Complete.');//  <a href=/capi/member_home>Return to the Dashboard</a>');
							location.reload();
						} else {
							alert('Error. '+data['messsage']);
              jQuery('#message_div').html(data['message']);
						}
          },
          error: function( jqXHR, textStatus, errorThrown){
             console.log(jqXHR.responseText);
              alert('failure: ' + textStatus + ' - ' + errorThrown);
          }   
        });        
      ";
		$output .= '
	
			}


		</script>

		';

		//				$output.=capi_watc_input_form_javascript($form);

		/*		
				$output.='
				<script>
		
				'.capi_watc_validate_form_basic_bio_form().'
		
				</script>';
*/



		return $output;

		//		$result="<textarea>".print_r($fields_array,false)."</textarea>";

		//		$query="module=event&operation=get_my_registrations&evoid=$evoid";

		//		$result=$this->capi_parse_xml2($this->capi_process_api($query));
		//		$result="<textarea>".print_r($this->capi_process_api($query),false)."</textarea>";

		return $result;

		//return print_r($result,true);
		$result = $result["result"];

		if ($result['success'])
			return "Registration Complete. <a href='" . get_site_url() . "/capi/tournament_schedule'>Click here to return to the schedule</a>.";
		else
			return "Error. " . $result['message'];
	} // end capi_par_play


	function capi_volunteer_opportunity($atts = [], $content = null, $tag = '')
	{

		/*
		if ( !is_user_logged_in() ){
		 return "You must be logged in to see this page.  <a href='".get_site_url()."/capi/login'>Click Here to Log In</a>";
		}
*/

		$volunteer_group_name = "";
		$evoid = 0;
		$show_date = 1;
		$show_time = 1;

		extract($atts);


		if (!isset($_SESSION))
			session_start();

		$cid = $this->capi_check_profile();

		$query = "module=event&operation=wc_volunteer_opportunity&evoid=$evoid&volunteer_group_name=$volunteer_group_name";
		$result = $this->capi_process_api($query);
		$result = json_decode($result, true);
		//return print_r($result["volunteer_opportunities"],true);
		//return print_r($result,true);

		$output = "";
		$output .= "<form name='$volunteer_group_name' action='/capi/wc_mail_team' method=post>\n";

		$output .= '
	<table align="center" border="0" cellpadding="5" cellspacing="0" width="928">
		<tbody>
			<tr bgcolor="#dfd9ba" valign="top">';

		if ($show_date) {
			$output .= '
				<td height="30" width="100">
					<u>Date</u></td>';
		};
		if ($show_time) {
			$output .= '
				<td height="30" width="88">
					<u>Hours</u></td>';
		};
		$output .= '
				<td height="30" width="210">
					<u>Job Description</u></td>
				<td bgcolor="#dfd9ba" height="30" width="194">
					<u>Requirements</u></td>
				<td bgcolor="#dfd9ba" height="30" width="115">
					<u>Volunteer</u></td>
			</tr>';

		$light = true;

		foreach ($result['volunteer_opportunities'] as $volunteer_opportunity) {
			for ($i = -1; $i < sizeof($volunteer_opportunity["event_volunteers"]); $i++) {


				if ($i > -1 || (sizeof($volunteer_opportunity["event_volunteers"]) < $volunteer_opportunity["quantity"])) {
					if ($light) {
						$output .= '<tr bgcolor="#e9e4cf">';
						$light = false;
					} else {
						$output .= '<tr bgcolor="#dfd9ba">';
						$light = true;
					}

					if ($show_date) {
						$output .= "<td>" . date("m-d", strtotime($volunteer_opportunity["evd_date"])) . "</td>";
					};

					if ($show_time) {
						$output .= "<td>" . $volunteer_opportunity["start_time"];
						if ($volunteer_opportunity["start_time"] != $volunteer_opportunity["end_time"]) {
							$output .= " - " . $volunteer_opportunity["end_time"];
						}
						$output .= "</td>";
					};

					$output .= "<td>" . $volunteer_opportunity["job_description"] . "</td>";
					$output .= "<td>" . $volunteer_opportunity["requirements"] . "</td>";

					if ($i > -1) {
						$volunteer = $volunteer_opportunity["event_volunteers"][$i];
						$output .= "<td>";
						//	$output.=print_r($volunteer,true);
						/*	
		           if ($user->uid==1 || in_array('editor', array_values($user->roles)) || in_array('team captain', array_values($user->roles)))
		          {
		             $output.= "<a href='/user/" . $my_node->field_uid[0]["uid"] . "'>";
		             $output.= $my_node->field_volunteer_name[0]["value"];
		             $output.= "</a>";
		          } else
	*/ {
							if (current_user_can('administrator') || current_user_can('editor')) {
								$output .= "<a href='/capi/show_user?cid=" . $volunteer["cid"] . "'>";
								$output .= $volunteer["first_name"] . " " . $volunteer["last_name"];
								$output .= "</a>";
							} else {
								$output .= $volunteer["first_name"] . " " . $volunteer["last_name"];
							}

							if (current_user_can('administrator') || current_user_can('editor')) {
								$output .= "<br><a href='/capi/wc_volunteer_now/?i_agree=on&void=" . $volunteer_opportunity["void"] . "&evvolid=" . $volunteer["evvolid"] .
									"&edit_cid=" . $volunteer["cid"] . "'>";
								$output .= "Edit";
								$output .= "</a>";
								$output .= " - <!--<img src='" . $module_path . "/images/envelope.jpg'>--><input name=cid-" . $volunteer["cid"] . " type=checkbox>";
							}
						}
					} else {
						$output .= "<td bgcolor='#9f0000'>";
						$output .= "<a style='color:white' href=/capi/wc_volunteer_now?show_date=$show_date&show_time=$show_time&i_agree=on&void=" . $volunteer_opportunity["void"] . ">Click here to volunteer<br>" .
							($volunteer_opportunity["quantity"] - sizeof($volunteer_opportunity["event_volunteers"])) . " spaces available</a>";
					}
					/*
		         if ($user->uid==1 || in_array('editor', array_values($user->roles)) || in_array('team captain', array_values($user->roles)))
		         {
		           if ($my_node->field_volunteer_name[0]["value"]!=null)
		           {
		              $output.= "<br><a href=/node/$my_node->nid/edit>Edit</a>";
									$output.=" - <div id='checked_in-".$my_node->nid."' style='display:inline-block;cursor: pointer; cursor: hand;' class='check_in_div' nid='".$my_node->nid."'>";
		              if ($my_node->field_checked_in[0]["value"]=="In") {
		                $output.="Here";
		              } else {
		                $output.= "Check In";
		              }
		              $output.= "</div> - <img src='" . $module_path . "/images/envelope.jpg'><input name=uid-" . $my_node->field_uid[0]["uid"] . " type=checkbox>";
		            }
		            else
		            $output.= "<br><a style='color:white' href=/node/$my_node->nid/edit>Edit</a>";
		           
		         }
	*/
					$output .= "</td>";

					$output .= "</tr>\n";
				} // end if I>-1
			} // end for i
		} // end foreach

		if (current_user_can('administrator') || current_user_can('editor')) {
			$output .= "<tr>\n";
			if ($show_date) {
				$output .= "<td ></td>\n";
			}
			if ($show_time) {
				$output .= "<td ></td>\n";
			}
			$output .= "<td ></td>\n";
			$output .= "<td ></td>\n";
			$output .= "<td align=center><input type=button onclick='return checkall_" . $volunteer_opportunity["void"] . "(this.form);' value='Select Entire Team'><br><input type=submit value='Email Selected'></td>\n";
			$output .= "</tr>\n";
		}

		$output .= '	</tbody>';
		$output .= '</table></form>';
		$output .= '
		<script>
	  function checkall_' . $volunteer_opportunity["void"] . '(form) {
	    for(i=0; i<form.elements.length; i++)
	    {
	    if(form.elements[i].type=="checkbox")
	    {
	      form.elements[i].checked=true;
	    }
	    }     
	    return false;  
	  }
		</script>
	';

		return $output;


		return $result;


		return print_r($result, true);
		return $volunteer_group_name;

		$today = date("Y-m-d");

		if ($late_date > "") {
			$late_date = date("Y-m-d", strtotime($late_date));
			if ($today > $late_date) {
				$dues += $late_fee;
			}
		}

		//		$query="asid=1&dues=125&transaction_fee=4&year=2022&prodid=1&module=online_membership&operation=om_pay_dues_form";
		$query = "asid=$asid&dues=$dues&transaction_fee=$transaction_fee&year=$year&prodid=$prodid&module=online_membership&operation=om_pay_dues_form";

		$query_result = ($this->capi_process_api($query));

		$result = "";
		//		$result="<h2>".$capi_result->dms_type_name."</h2>";


		$query_result = json_decode($query_result);

		$message = $query_result->message;

		return $message;
	} // end capi_dms_get_recent_document_list

	function capi_wc_volunteer_now()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}
		$void = 0;
		$evvolid = 0;
		$vol_cid = 0;
		$edit_cid = 0;
		$show_date = 1;
		$show_time = 1;

		$i_agree = "";
		$output = "";

		extract($_REQUEST);


		if (!isset($_SESSION))
			session_start();

		$cid = $this->capi_check_profile();

		if ($cid == 0) {
			$output .= '
			Registered Volunteers: <a href="/capi/login">Click to here login</a><br />
		';
		} else {

			if ($edit_cid > 0) $cid = $edit_cid;

			$fields_array = $this->capi_get_fields();
			$this->capi_fill_fields_array($fields_array, $cid);

			// get volunteer opportunity information


			$query = "module=event&operation=wc_volunteer_opportunity&void=$void";
			$result = $this->capi_process_api($query);
			//			$output.="result: ".$result."<br>";
			$result = json_decode($result, true);

			$volunteer_opportunity = $result['volunteer_opportunities'][0];
			$current_volunteer_quantity = sizeof($volunteer_opportunity["event_volunteers"]);
			$volunteer_opportunity_name = $volunteer_opportunity["volunteer_opportunity_name"];
			$quantity = $volunteer_opportunity["quantity"];
			$evd_date = date("m-d", strtotime($volunteer_opportunity["evd_date"]));
			$hours = $volunteer_opportunity["start_time"];
			if ($volunteer_opportunity["start_time"] != $volunteer_opportunity["end_time"]) {
				$hours .= " - " . $volunteer_opportunity["end_time"] . "</td>";
			}
			$job_description = $volunteer_opportunity["job_description"];
			$requirements = $volunteer_opportunity["requirements"];


			//			$output.="quantity: $quantity current_volunteer_quantity:$current_volunteer_quantity<br>";

			if ($current_volunteer_quantity < $quantity || $edit_cid > 0) {

				// Check to see if i_agree box is set

				if ($i_agree == "on") {

					$output .= '
			
			You are volunteering for: ' . $volunteer_opportunity_name . '<br>
			
			<table align="center" border="0" cellpadding="5" cellspacing="0" width="928">
				<tbody>
					<tr bgcolor="#dfd9ba" valign="top">
			';
					if ($show_date) $output .= '
						<td height="30" width="100">
							<u>Date</u></td>';
					if ($show_time) $output .= '
						<td height="30" width="88">
							<u>Hours</u></td>';
					$output .= '
						<td height="30" width="210">
							<u>Job Description</u></td>
						<td bgcolor="#dfd9ba" height="30" width="194">
							<u>Requirements</u></td>
					</tr>
			 
			';

					$output .= '<tr bgcolor="#e9e4cf">';
					if ($show_date)
						$output .= "<td>" . $evd_date . "</td>";
					if ($show_time)
						$output .= "<td>" . $hours . "</td>";
					$output .= "<td>" . $job_description . "</td>";
					$output .= "<td>" . $requirements . "</td>";


					$output .= "</tr>\n";
					$output .= "</table>";



					if (current_user_can('administrator') || current_user_can('editor')) {
						// get full user list
						$query = "operation=searchindividual&limit=5000";
						$result = ($this->capi_process_api($query));
						$capi_user_list = $this->capi_parse_xml2($result);
						$capi_user_list = $capi_user_list['result']['individual'];


						//				$output.=print_r($capi_user_list,true);

						$output .= '<form method="post" action="/capi/wc_do_volunteer_now/">';

						$output .= "<select name=cid>\n";
						if ($evvolid > 0) {
							$output .= "<option value=0>Remove Volunteer</option>\n";
						} else {
							$output .= "<option value=0>Select a Volunteer</option>\n";
						}

						foreach ($capi_user_list as $individual) {
							$output .= "<option value=" . $individual['cid'];
							if ($edit_cid == $individual['cid']) $output .= " selected ";
							$output .= ">" . $individual['last_name'] . " " . $individual['first_name'] . "</option>\n";
						}
						$output .= "</select>\n";
						$output .= '
					    <input type=hidden name=void value="' . $void . '">
					    <input type=hidden name=evvolid value="' . $evvolid . '">
					    <input type=submit name=submit value="Save Volunteer">
							</form>
							';
					} else {
						$output .= '
					<!--
					Please, enter the family member name that is volunteering for this opportunity.&nbsp;<strong> One person only, please - no multiple names.</strong><br>
					-->
					<form method="post" action="/capi/wc_do_volunteer_now/">
					' . $fields_array['first_name']['default'] . ' ' . $fields_array['last_name']['default'] . '
					    <input type=hidden name=void value="' . $void . '">
					    <input type=submit name=submit value="Volunteer Now!">
					</form>
					';
					}
				} else {  // if $i_agree

					// $i_agree box not set.  Show the form
					$output .= '
			
			
			<table style="width:400px">
				<tbody>
					<tr>
						<td>I acknowledge this position for which I am volunteering at Westchester&#39;s Christmas Dinner (WCD) is to be staffed by me and me only. I will not bring additional people to the event venue who are not registered volunteers for other volunteer positions.<br />
						<br />
						As I understand the building has capacity limits, I will leave the premises at the conclusion of my scheduled volunteer shift.<br />
						<br />
						I understand that the mission of WCD is to provide a hot meal and gifts to those in need on Christmas Day and I further understand that all of the goods to accomplish this are donated and will be dispersed in accordance with the mission.<br />
						&nbsp;</td>
					</tr>
				</tbody>
			</table>
			  ' . $fields_array['first_name']['default'] . ' ' . $fields_array['last_name']['default'] . '
			  <form action="/capi/wc_volunteer_now/" name="agree_form">
			  
			  <input type="hidden" name="void" value="' . $void . '">
			  
				By checking this box, I agree: <input name="i_agree" type="checkbox" /> 
			  <input type="submit" value="Submit" />
			  </form> 
			';
				}
			} else {
				$output .= "Sorry, someone has volunteered for this opportunity already.";
			}
		} // cid>0

		return $output;
	} // end capi_wc_volunteer_now

	function capi_wc_do_volunteer_now()
	{
		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}
		$void = 0;
		$cid = 0;
		$evvolid = 0;

		$output = "";

		extract($_REQUEST);


		if (!isset($_SESSION))
			session_start();

		if ($cid == 0) {
			$cid = $this->capi_check_profile();
		}

		if ($cid == 0) {
			$output .= '
			Registered Volunteers: <a href="/capi/login">Click to here login</a><br />
		';
		} else {

			// do registration

			//xxx
			$query = "module=event&operation=wc_do_volunteer_now&void=$void&cid=$cid&evvolid=$evvolid";
			$result = $this->capi_process_api($query);
			//			$output.="result: ".$result."<br>";
			//$output.=" $query ";
			$result = json_decode($result, true);
		}
		if ($result['success']) {
			$output .= "Thank you for signing up to volunteer at the Christmas Dinner! If you need to change or cancel your signup, please contact our Volunteer Coordinator, Jane Emmer:  jweilemmer@gmail.com";
		} else {
			$output .= "Error. " . $result['message'];
		}

		return $output;
	}	// end capi_do_volunteer_now

	public function capi_show_user()
	{
		$output = "";

		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		if (!isset($_SESSION))
			session_start();

		if ($cid == 0) {
			$cid = $this->capi_check_profile();
		}

		if ($cid == 0) {
			$output .= "Invalid";
			return $output;
		}


		$cid = 0;

		extract($_REQUEST);


		if (!isset($_SESSION))
			session_start();

		if ($cid == 0) {
			$output .= "Invalid";
			return $output;
		}


		$individual = $this->capi_parse_xml2($this->capi_process_api("operation=getindividual&cid=$cid"));
		$individual = $individual["result"];

		//	error_log("check profile.  success: ".$individual[0]["success"]);	
		//	error_log("check profile.  individual: ".print_r($individual,true));	

		if ($individual["success"] == "true") {
			//			$output.=print_r($individual,true);
			$output .= $individual["first_name"] . " " . $individual["last_name"] . "<br><br>";
			if (isset($individual["addresses"]["address"][0])) $individual["addresses"] = $individual["addresses"]["address"];
			foreach ($individual["addresses"] as $address) {
				$output .= $address["street1"] . "<br>";
				if ($address["street2"] > "") $output .= $address["street2"] . "<br>";
				$output .= $address["city"] . ", " . $address["state"] . ", " . $address["zip"] . "<br><br>";
			}

			if (isset($individual["emails"]["email"][0])) $individual["emails"] = $individual["emails"]["email"];
			foreach ($individual["emails"] as $email) {
				$output .= $email["email"] . "<br>";
			}

			if (isset($individual["phones"]["phone"][0])) $individual["phones"] = $individual["phones"]["phone"];
			foreach ($individual["phones"] as $phone) {
				$output .= $phone["phone_type"] . ": " . $phone["number"] . "<br>";
			}

			$output .= "<br><a href='javascript:history.back();'>Back</a>";

			//			foreach ($individual[0]["email"]

			return $output;
		} // end do_volunteer_now


	} // end capi_show_user


	function capi_wc_mail_team()
	{


		if (!is_user_logged_in()) {
			return "You must be logged in to see this page.  <a href='" . get_site_url() . "/capi/login'>Click Here to Log In</a>";
		}

		if (!current_user_can('administrator') && !current_user_can('editor')) {
			return "Administrators only.";
		}

		$output = "";
		$cids = "";
		$subject = "";
		$from_display = "";
		$reply_to_email = "";
		$subject = "";
		$body = "";

		foreach ($_REQUEST as $key => $value) {
			if (substr($key, 0, 4) == "cid-") {
				if (strlen($cids) > 0) $cids .= ",";
				$cids .= substr($key, 4);
			} else {
				${$key} = $value;
			}
		}

		if ($cids == "") {
			return "Error. No recipients selected.";
		}

		if ($subject . $from_display . $reply_to_email . $subject . $body > "") {

			// something was entered, verify data

			$errors = "";

			if ($from_display == "") {
				$errors .= "From Display Required<br>";
			}
			if ($reply_to_email == "") {
				$errors .= "Reply To Email Required<br>";
			} else {
				// check for valid reply to email address
				if (!filter_var($reply_to_email, FILTER_VALIDATE_EMAIL)) {
					$errors .= "Reply To Email Invalid<br>";
				}
			}
			if ($subject == "") {
				$errors .= "Subject Required<br>";
			}
			if ($body == "") {
				$errors .= "Body Required<br>";
			}
			if ($errors > "")
				$output .= "Error. $errors.<br>";
			else {
				// send email

				$query = "module=$module&operation=$operation&wiwid=$wiwid";
				$query .= "cids=$cids&from_email=$reply_to_email&subject=$subject";
				$query .= "from_name=$from_display&body=$body";
				//return "test3";		
				$result = json_decode($this->capi_process_api($query), true);
				//return "test2";				
				return "Email send process complete. " . $result['message'];
			}
		}



		$output .= "<h2>Send Email</h2><br>";

		$output .= "<form name='mail_team' action='/capi/wc_mail_team' method=post>\n";

		$output .= $this->capi_theme_input($form, array("field" => "ajax", "input_type" => "hidden", "type" => "s", "default" => 0));
		$output .= $this->capi_theme_input($form, array("field" => "module", "input_type" => "hidden", "type" => "s", "default" => "message"));
		$output .= $this->capi_theme_input($form, array("field" => "operation", "input_type" => "hidden", "type" => "s", "default" => "broadcast_email"));

		$output .= "<input type=hidden id=cids name=cids value='$cids'>\n";
		$output .= "From Display<br>";
		$output .= "<input type=text id=from_display name=from_display size=100 value='$from_display'>\n";
		$output .= "Reply to Email<br>";
		$output .= "<input type=text id=reply_to_email name=reply_to_email size=100 value='$reply_to_email'>\n";
		$output .= "Subject<br>";
		$output .= "<input type=text id=subject name=subject size=100 value='$subject'>\n";
		$output .= "Body<br>";
		$output .= "<textarea id=body name=body rows=10 style='width:100%'>$body</textarea>\n";
		$output .= "<input type=submit value='Send Email'>\n";
		$output .= "</form>\n";


		//	$output.= "<br><br>".print_r($_REQUEST,true);

		return $output;
	} // end capi_wc_mail_team

}
