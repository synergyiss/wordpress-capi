<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Capi
 * @subpackage Capi/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Capi
 * @subpackage Capi/admin
 * @author     Paul Niebuhr <paul@synergyinnovativesystems.com
 */
class Capi_Admin {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		// Tracks new sections for whitelist_custom_options_page()
		$this->page_sections = array();
		// Must run after wp's `option_update_filter()`, so priority > 10
//		add_action( 'whitelist_options', array( $this, 'display_plugin_setup_page' ),11 );

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/capi-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/capi-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	
	public function add_plugin_admin_menu() {
	
	    /*
	     * Add a settings page for this plugin to the Settings menu.
	     *
	     * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
	     *
	     *        Administration Menus: http://codex.wordpress.org/Administration_Menus
	     *
	     */
//	    add_options_page( 'CAPI integration settings for Command and CEO.', 'CAPI', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page'));
	}
	
	 /**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	
	public function add_action_links( $links ) {
	    /*
	    *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
	    */
	   $settings_link = array(
	    '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
	   );
	   return array_merge(  $settings_link, $links );
	
	}
	
	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	
	public function display_plugin_setup_page() {
	
	    include_once( 'partials/capi-admin-display.php' );
	}	

	public function validate($input) {
	error_log("25");

error_log("def".print_r($input,true).".ghi");

		return $input;
		
	    // All checkboxes inputs        
	    $valid = array();
	
	    //Cleanup
	    $valid['capi_url'] = $input['capi_url'];
	    $valid['capi_un'] = $input['capi_un'];
error_log("abc");
error_log("def".print_r($input,true).".ghi");
//return array("capi_url"=>"url","capi_un"=>'un');
	    return $valid;
	 }
	 
	  public function create_plugin_settings_page() {
/*
			add_option('capi_url','');
	     register_setting('capi', 'capi_url', array($this, 'validate'));
			add_option('capi_un','');
	     register_setting('capi', 'capi_un', array($this, 'validate'));
*/
//	     register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
//			 add_settings_section( 'capi_section', 'CAPI Section', array( $this, 'section_callback' ), 'capi_fields' );

//	     register_setting('capi-group', 'capi-settings');

			// Add the menu item and page
			$page_title = 'CAPI Integration Settings Page';
			$menu_title = 'CAPI';
			$capability = 'manage_options';
			$slug = 'capi_fields';
			$callback = array( $this, 'display_plugin_setup_page' );
			$icon = 'dashicons-admin-plugins';
			$position = 100;
		
			add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
			

		}
	
		public function setup_sections() {
				add_settings_section( 'capi_section', 'Capi Section', array( $this, 'section_callback' ), 'capi_fields' );
		}	
	
		public function section_callback( $arguments ) {
		switch( $arguments['id'] ){
				case 'capi_section':
					echo 'This is the first description here!';
					break;
			}
		}
		
		public function setup_fields() {
//error_log('url:'.get_option('capi_url', ""));
			$fields = array(
					array(
						'uid' => 'capi_url',
						'label' => 'CAPI Url',
						'section' => 'capi_section',
						'type' => 'text',
						'options' => false,
						'placeholder' => '',
						'helper' => 'URL to capi',
						'supplemental' => 'Put in the URL to the CAPI Call',
						'default' => ''
					),
					array(
						'uid' => 'capi_un',
						'label' => 'CAPI User Name',
						'section' => 'capi_section',
						'type' => 'text',
						'options' => false,
						'placeholder' => '',
						'helper' => '',
						'supplemental' => '',
						'default' => ''
					),
					array(
						'uid' => 'capi_pw',
						'label' => 'CAPI Password',
						'section' => 'capi_section',
						'type' => 'text',
						'options' => false,
						'placeholder' => '',
						'helper' => '',
						'supplemental' => '',
						'default' => ''
					),
					array(
						'uid' => 'capi_un',
						'label' => 'CAPI User Name',
						'section' => 'capi_section',
						'type' => 'text',
						'options' => false,
						'placeholder' => '',
						'helper' => '',
						'supplemental' => '',
						'default' => ''
					),
					array(
						'uid' => 'capi_member_number_field',
						'label' => 'Member Number Field',
						'section' => 'capi_section',
						'type' => 'text',
						'options' => false,
						'placeholder' => '',
						'helper' => '',
						'supplemental' => '',
						'default' => ''
					),
					array(
						'uid' => 'capi_club_number',
						'label' => 'Club Number',
						'section' => 'capi_section',
						'type' => 'text',
						'options' => false,
						'placeholder' => '',
						'helper' => '',
						'supplemental' => 'CAPI Club Number of primary membership club.',
						'default' => ''
					),
					array(
						'uid' => 'capi_login_destination',
						'label' => 'Login Destination',
						'section' => 'capi_section',
						'type' => 'text',
						'options' => false,
						'placeholder' => '',
						'helper' => '',
						'supplemental' => '',
						'default' => ''
					),
					array(
						'uid' => 'capi_wiwid',
						'label' => 'CAPI Website ID',
						'section' => 'capi_section',
						'type' => 'text',
						'options' => false,
						'placeholder' => '',
						'helper' => '',
						'supplemental' => 'Website id from backend website integration web table.',
						'default' => ''
					),
					array(
						'uid' => 'capi_update_profile_message',
						'label' => 'CAPI Update Profile Message',
						'section' => 'capi_section',
						'type' => 'text',
						'options' => false,
						'placeholder' => '',
						'helper' => '',
						'supplemental' => 'Message shown to user when they update their profile.',
						'default' => ''
					),
					array(
						'uid' => 'capi_update_profile_fields',
						'label' => 'CAPI Update Profile Fields',
						'section' => 'capi_section',
						'type' => 'textarea',
						'options' => false,
						'placeholder' => '',
						'helper' => '',
						'supplemental' => 'List of fields shown to a user when they update their profile.',
						'default' => ''
					),
					array(
						'uid' => 'capi_register_profile_fields',
						'label' => 'CAPI Register Profile Fields',
						'section' => 'capi_section',
						'type' => 'textarea',
						'options' => false,
						'placeholder' => '',
						'helper' => '',
						'supplemental' => 'List of fields shown to a user when they register for the site.',
						'default' => ''
					),
				);
				foreach( $fields as $field ){
					add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'capi_fields', $field['section'], $field );
					register_setting( 'capi_fields', $field['uid'] );
				}		
		}
		
		public function field_callback( $arguments ) {
			    $value = get_option( $arguments['uid'] ); // Get the current value, if there is one
			    if( ! $value ) { // If no value exists
			        $value = $arguments['default']; // Set to our default
			    }
			
				// Check which type of field we want
				switch( $arguments['type'] ){
					case 'text': // If it is a text field
						printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
						break;
					case 'textarea': // If it is a textarea
						printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
						break;
					case 'select': // If it is a select dropdown
						if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
							$options_markup = '';
							foreach( $arguments['options'] as $key => $label ){
								$options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value, $key, false ), $label );
							}
							printf( '<select name="%1$s" id="%1$s">%2$s</select>', $arguments['uid'], $options_markup );
						}
						break;
				}			
				// If there is help text
			    if( $helper = $arguments['helper'] ){
			        printf( '<span class="helper"> %s</span>', $helper ); // Show it
			    }
			
				// If there is supplemental text
			    if( $supplimental = $arguments['supplemental'] ){
			        printf( '<p class="description">%s</p>', $supplimental ); // Show it
			    }
			
		}
}
