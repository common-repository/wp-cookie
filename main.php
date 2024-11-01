<?php
/*
Plugin Name: WP Cookie
Plugin URI: https://aazztech.com/product/wp-cookie-pro
Description: This plugin allows you to inform users that your site uses cookies.
Version: 1.0.0
Author: AazzTech
Author URI: https://aazztech.com
License: GPLv2 or later
*/

if ( !defined( 'ABSPATH' ) ) die( 'Direct browsing is not allow');

if ( !class_exists('Adl_cookie') ){

	class Adl_cookie{

		//save option for general settings
		private $options;

		//save option for style settings
		private $option_style;

		// message
		private $message;

		// accept text
		private $accept_text;

		//decline text
		private $decline_text;

		// more info text
		private $more_info_text;

		// more info url
		private $more_info_url;

		// postion select
		private $position;

		//Theme options
		private $theme_options;

		//background color
		private $background;

		//text color
		private $text_color;

		//Button background color
		private $button_background;

		//Button text color
		private $button_text;

		//Button border color
		private $button_border;

		public function __construct(){

			// Add the page to the admin menu
			add_action('admin_menu',array($this,'adl_admin_menu'));

			//Register page option
			add_action('admin_init',array($this,'adl_page_option'));

			//css for color picker
			wp_enqueue_style('wp-color-picker');
			//Register css & js  
			add_action('wp_enqueue_scripts',array($this,'adl_css_js'));

			//Register css & js for admin
			add_action('admin_enqueue_scripts',array($this,'adl_admin_css_js'));

			//Add js code for cookie
			add_action('wp_footer',array($this,'adl_footer_js'));

			//add style settings
			add_action( 'admin_init', array($this,'adl_style_section') );

			//All values
			$this->adl_all_values();
		}



		
		// Function that have all values
		public function adl_all_values(){

			$this->options  		  = get_option('adl_cookie_general');

			$this->option_style 	  = get_option('adl_cookie_style_settings');

			$this->message 		  	  =  (!empty($this->options['message'])) ? $this->options['message'] : 'We use cookies to ensure that we give you the best experience on our website.';

			$this->accept_text        =  (!empty($this->options['accept_text'])) ? $this->options['accept_text'] :'Got it!';

			$this->decline_text       =  (!empty($this->options['decline_text'])) ? $this->options['decline_text'] :'Decline';

			$this->more_info_text     =  (!empty($this->options['more_info'])) ? $this->options['more_info'] :'Learn more';

			$this->more_info_url      = (!empty($this->options['more_info_url'])) ? $this->options['more_info_url'] : '';

			$this->position           =  (!empty($this->options['position'])) ? $this->options['position'] :'top';

			$this->background 		  = (!empty($this->option_style['background'])) ? $this->option_style['background'] : '#252e39';	

			$this->text_color 		  = (!empty($this->option_style['text_color'])) ? $this->option_style['text_color'] : '#ffffff';	

			$this->theme_options 	  = (!empty($this->option_style['theme_options'])) ? $this->option_style['theme_options'] : 'classic';

			$this->button_background  = (!empty($this->option_style['button_background'])) ? $this->option_style['button_background'] : '#14a7d0';

			$this->button_text 		  = (!empty($this->option_style['button_text'])) ? $this->option_style['button_text'] : '#ffffff';

			$this->button_border      = (!empty($this->option_style['button_border'])) ? $this->option_style['button_border'] : '#14a7d0';
		}

		// Function that will add the options page under Setting Menu.
		public function adl_admin_menu(){

			add_options_page(
				'WP Cookie',
				'WP Cookie',
				'manage_options',
				'wpcookie',
				array($this,'adl_display_page'));
		}

		//Function that will add css & js files
		public function adl_css_js(){

			wp_register_script('cookie-js',PLUGINS_URL('js/cookie.js',__FILE__),array('jquery'),'',false);
			wp_register_style('cookie-style',PLUGINS_URL('css/style.css',__FILE__));

			wp_enqueue_script('cookie-js');
			wp_enqueue_style('cookie-style');
		}

		//Function that will add admin css & js files
		public function adl_admin_css_js(){
			wp_enqueue_script( 'adl_color_js', PLUGINS_URL( 'js/color.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), '', true  );
		}

		
    	//Function that will check if value is a valid HEX color.
		public function check_color( $value ) { 
		     
		    if ( preg_match( '/^#[a-f0-9]{6}$/i', $value ) ) { // if user insert a HEX color with #     
		        return true;
		    }
		     
		    return false;
		}

		

		// Function that will display options page
		public function adl_display_page(){

			

			?>
			<div class="wrap">
				<h3>Cookie</h3>
				<h2 class="nav-tab-wrapper">
	                <?php 
		                $page = (isset($_GET['tab'])) ? $_GET['tab'] : '';

	                ?>
	                <a href="?page=wpcookie&tab=general-settings" class="nav-tab <?php if($page != 'style-settings'){ echo 'nav-tab-active';}?>">General Settings</a>
	                <a href="?page=wpcookie&tab=style-settings" class="nav-tab <?php if($page == 'style-settings'){ echo 'nav-tab-active';}?>">Style settings</a>
	            </h2>

				<form method="post" action="options.php" novalidate="novalidate">

					<?php 

					if(isset($_GET['tab'])){
						if($_GET['tab'] == 'general-settings'){
							settings_fields('adl_option_general');
							do_settings_sections('wpcookie');
						}else{
							settings_fields('adl_option_style');
							do_settings_sections('adl-cookie');
						}
					}else{
						settings_fields('adl_option_general');
							do_settings_sections('wpcookie');
					}
						
						
					submit_button();
					?>

				</form>	

				
			</div>		

			<?php
		}

		//Function for style settings
		public function adl_style_section(){
			
			register_setting('adl_option_style','adl_cookie_style_settings');
			
			//Section for Cookie style
			add_settings_section(
				'cookie-style',
				'Cookie style',
				array($this,'adl_cookie_style'),
				'adl-cookie');

			//Add theme options
			add_settings_field('theme-options',
				'Theme options',
				array($this,'adl_theme_options'),
				'adl-cookie',
				'cookie-style');

			//Add background color
			add_settings_field('background-color',
				'Background color',
				array($this,'adl_background_color'),
				'adl-cookie',
				'cookie-style');

			//Add text color
			add_settings_field('text-color',
				'Text color',
				array($this,'adl_text_color'),
				'adl-cookie',
				'cookie-style');

			//Add button background color
			add_settings_field('button-background-color',
				'Button background',
				array($this,'adl_button_background'),
				'adl-cookie',
				'cookie-style');

			//Add button text color
			add_settings_field('button-text-color',
				'Button text color',
				array($this,'adl_button_text'),
				'adl-cookie',
				'cookie-style');

			//Add button border color
			add_settings_field('button-border-color',
				'Button border color',
				array($this,'adl_button_border'),
				'adl-cookie',
				'cookie-style');

			
		}

		//Function of page option
		public function adl_page_option(){

					//Register setting
					register_setting('adl_option_general','adl_cookie_general');
			
					//Section for content cookie
					add_settings_section(
						'cookie-content',
						'Cookie content',
						array($this,'adl_cookie_content'),
						'wpcookie');
					//Add message field
					add_settings_field(
						'message-area',
						'<label for="adl_cookie_general[message]">Message<label>',
						array($this,'adl_message'),
						'wpcookie',
						'cookie-content');

					//Add button text
					add_settings_field('button-area',
						'<label for="adl_cookie_general[accept_text]">Accept text<label>',
						array($this,'adl_accept_text'),
						'wpcookie',
						'cookie-content');

					//Add decline text
					add_settings_field('decline-area',
						'<label for="adl_cookie_general[decline_text]">Decline text<label>',
						array($this,'adl_decline_text'),
						'wpcookie',
						'cookie-content');

					//Add more info text
					add_settings_field('more-info-text',
						'<label for="adl_cookie_general[more_info]">More info text<label>',
						array($this,'adl_more_info_text'),
						'wpcookie',
						'cookie-content');

					//Add more info url
					add_settings_field('more-info-url',
						'<label for="adl_cookie_general[more_info_url]">More info url<label>',
						array($this,'adl_more_info_url'),
						'wpcookie',
						'cookie-content');

					//Add position
					add_settings_field('position',
						'Position',
						array($this,'adl_position'),
						'wpcookie',
						'cookie-content');


				}


		//function of section
		public function adl_cookie_content(){}

		//Function of cookie style
		public function adl_cookie_style(){}

		//Function of message
		public function adl_message(){

			?>

			<textarea name="adl_cookie_general[message]" cols="50" rows="5" id="adl_cookie_general[message]"><?php echo esc_attr($this->message); ?></textarea>
			<p class="description">Enter the cookie message</p>

			<?php

		}

		//Function of Button text
		public function adl_accept_text(){

			
			?>
			<input type="text" name="adl_cookie_general[accept_text]" value="<?php echo esc_attr($this->accept_text);?>" id="adl_cookie_general[accept_text]"/>
			<p class="description">The default text to dismiss the notification</p>
			<?php

		}

		//Function of Button text
		public function adl_decline_text(){

			
			?>
			<input type="text" name="adl_cookie_general[decline_text]" value="<?php echo esc_attr($this->decline_text);?>" id="adl_cookie_general[decline_text]"/>
			<p class="description">The default text to decline the notification</p>
			<?php

		}

		//Function of More info text
		public function adl_more_info_text(){

			
			?>
			<input type="text" name="adl_cookie_general[more_info]" value="<?php echo esc_attr($this->more_info_text);?>" id="adl_cookie_general[more_info]"/>
			<p class="description">The default text to use at link to provide more information</p>
			<?php

		}

		//Function of More info url
		public function adl_more_info_url(){
			
			?>
			<input type="text" name="adl_cookie_general[more_info_url]" value="<?php echo esc_url($this->more_info_url);?>" id="adl_cookie_general[more_info_url]"/>
			<p class="description"> You can add url </p>
			<?php
		}

		//Function of position
		public function adl_position(){
			
			?>
			<input type="radio" name="adl_cookie_general[position]" value="top" <?php if($this->position == 'top'){ echo "checked='checked'";}?>/> Top

			<input type="radio" name="adl_cookie_general[position]" value="bottom" <?php if($this->position == 'bottom'){ echo "checked='checked'";}?>/> bottom</br/>

			<p class="description">Select location for your cookie notice</p>
			<?php

		}

		//Function for theme options
		public function adl_theme_options(){
			?>
			<select name="adl_cookie_style_settings[theme_options]">
			    <option value="block" <?php if($this->theme_options == 'block'){ echo 'selected';}?>>Block</option>
			    <option value="classic" <?php if($this->theme_options == 'classic'){ echo 'selected';}?>>Classic</option>
			</select>
			<p class="description">Theme options for cookie</p>
			<?php
		}

		//Function of background color
		public function adl_background_color(){
			?>
			<input type="text" name="adl_cookie_style_settings[background]" value="<?php echo esc_attr($this->background);?>" class="cpa-color-picker"/>
			<p class="description">The background color for the notification</p>
			<?php
		}

		//Function of text color
		public function adl_text_color(){
			?>
			<input type="text" name="adl_cookie_style_settings[text_color]" value="<?php echo esc_attr($this->text_color );?>" class="cpa-color-picker"/>
			<p class="description">The text color of the notification</p>
			<?php
		}

		//Function for button background color
		public function adl_button_background(){
			?>
			<input type="text" name="adl_cookie_style_settings[button_background]" value="<?php echo esc_attr($this->button_background );?>" class="cpa-color-picker"/>
			<p class="description">Color of button background</p>
			<?php
		}

		//Function for button text color
		public function adl_button_text(){
			?>
			<input type="text" name="adl_cookie_style_settings[button_text]" value="<?php echo esc_attr($this->button_text );?>" class="cpa-color-picker"/>
			<p class="description">Color of button text</p>
			<?php
		}

		//Function for button border color
		public function adl_button_border(){
			?>
			<input type="text" name="adl_cookie_style_settings[button_border]" value="<?php echo esc_attr($this->button_border );?>" class="cpa-color-picker"/>
			<p class="description">Color of button border</p>
			<?php
		}

		//Function for footer js
		public function adl_footer_js(){
			?>
			<script>
			jQuery(document).ready(function($) {
			window.addEventListener("load", function(){
			window.cookieconsent.initialise({
			  "palette": {
				"popup": {
				  "background": "<?php echo $this->background; ?>",
				  "text": "<?php echo $this->text_color; ?>"
				},
				"button": {
				  "background": "<?php echo $this->button_background; ?>",
				  "text": "<?php echo $this->button_text; ?>",
				  "border": "<?php echo $this->button_border; ?>"
				}
			  },
			  "theme": "<?php echo $this->theme_options; ?>",
			  "position": "<?php echo $this->position; ?>",
			  "type": "opt-out",
			  "content": {
				"message": "<?php echo $this->message; ?>",
				"deny":"<?php echo $this->decline_text;?>",
				"dismiss": "<?php echo $this->accept_text; ?>",
				"link": "<?php echo $this->more_info_text; ?>",
				"href": "<?php echo $this->more_info_url; ?>"
			  }
			})});

			}(jQuery));



			</script>


			<?php
		}



	}// end class!!

	new Adl_cookie;

} //end if of class