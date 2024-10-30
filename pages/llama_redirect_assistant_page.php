<?php
defined( 'ABSPATH' ) or exit;


class llama_redirect_assistant_page {
	
	function __construct() {
		//init ajax [get rules] -> assistant class -> api call
		add_action('wp_ajax_js_llama_redirect_ml_get_rules',array('llama_redirect_assistant','api_call'));
		add_action('wp_ajax_nopriv_js_llama_redirect_ml_get_rules',array('llama_redirect_assistant','api_call'));
		//init ajax [get log] -> assistant class -> api call
		add_action('wp_ajax_js_llama_redirect_ml_get_log',array('llama_redirect_assistant','api_call'));
		add_action('wp_ajax_nopriv_js_llama_redirect_ml_get_log',array('llama_redirect_assistant','api_call'));
		//add notice, that it is external page:
		add_action( 'admin_notices', array('llama_redirect_assistant_page', 'llama_redirect_assistant_notice') );
		
		// Remove other notices:
		remove_action( 'admin_notices', 'update_nag', 3 );
	}

	public static function llama_redirect_assistant_notice() {
		$fx = llama_redirect_PREFIX;
		
		if (isset($_GET['page']) and sanitize_text_field($_GET['page']) == 'llama-redirect_assistant') {
			echo 
			'<div class="notice '.$fx.'-ml-notice">
				<p>
					<i class="dashicons dashicons-lock"></i>
					<span>' . __( 'Secure HTTPS Llama Machine Learning Assistant page, running from an external domain - llamasapps.com [EU]', 'llama-redirect' ) . '</span>
				</p>
			</div>';
		}
	}
	
	public static function show_page() {
		$llama_redirect_assistant = new llama_redirect_assistant;
		$fx = llama_redirect_PREFIX;
		$elements = new llama_redirect_elements;
		$llama_redirect_assistant_page = new llama_redirect_assistant_page;
		
		$form_submit_status = $llama_redirect_assistant_page->handle_form_submit();
		
		//SHOW NAV and TITLE:
		echo $elements->nav($fx);
		echo $elements->title($fx, __( 'Llama Machine Learning Assistant', 'llama-redirect' ), NULL, NULL, 'centered');
		
		$assistant_mode = $llama_redirect_assistant->status();

		//SHOW PAGE:
		$html =  
		'<div class="'.$fx.'-container">';
			if ( ! $form_submit_status ) {
				$html .= 
				'<div class="'.$fx.'-card">
					<div class="'.$fx.'-card-content">
						<div class="'.$fx.'-center">' . $llama_redirect_assistant_page->error_card() . '</div>
					</div>
				</div>';
			}
			else if ( $llama_redirect_assistant->plugin_api_request_receiving_file_exists() ) {
				if ( $assistant_mode == 'new') {
					$html .=
					'<div class="'.$fx.'-card">
						<div class="'.$fx.'-card-content">
							<div class="'.$fx.'-center">' . $llama_redirect_assistant_page->welcome_card() . '</div>
						</div>
					</div>';
				}
				else {
					$html .= $llama_redirect_assistant_page->settings_form();
					$html .= $llama_redirect_assistant_page->chatbot();
				}
			}
			else {
				$html .= '
				<div class="'.$fx.'-card">
					<div class="'.$fx.'-card-content">
						<div class="'.$fx.'-center">' . $llama_redirect_assistant_page->plugin_api_file_not_found_card() . '</div>
					</div>
				</div>';	
			}

		//CLOSE PAGE:
		$html .= '
		</div>';
		echo $html;
	}
	private function handle_form_submit() {
		$fx = llama_redirect_PREFIX;
		$llama_redirect_assistant = new llama_redirect_assistant;
		$ml_assistant_option_name = $fx.'_ml_assistant_enabled';
		$ml_assistant_auto_option_name = $fx.'_ml_assistant_auto_apply_enabled';
		
		// Form submit to start using the Llama Assistant
		if ( isset( $_POST["start_ml_assistant_nonce"] ) and wp_verify_nonce( $_POST['start_ml_assistant_nonce'], 'start_ml_assistant') ) {
			// API call:
			if ($api_call = $llama_redirect_assistant->api_call(array('start', 'llama_redirect'),'enable_api_callback')) {
				if (isset($api_call['status']) and $api_call['status']) {
					// Action was successfully executed
					return true;	
				}
			}
			// Some error occured
			return false;
		}
		// Form submit to enable / disable Llama Assistant:
		else if ( isset( $_POST["ml_assistant_enabled_nonce"] ) and wp_verify_nonce( $_POST["ml_assistant_enabled_nonce"], 'ml_assistant_enabled' ) ) {
			
			if ( isset( $_POST[$ml_assistant_option_name] ) ) {
				
				//Update WP option:
				update_option( $ml_assistant_option_name, sanitize_text_field( $_POST[$ml_assistant_option_name] ) );
				
				// If is disable -> disable "auto_apply" too:
				if ( sanitize_text_field($_POST[$ml_assistant_option_name]) != '1' ) {
					update_option( $ml_assistant_auto_option_name, 0 );
				}
				
				
				// Send API call -> assistant disabled:
				$api_call = $llama_redirect_assistant->api_call( array( 'turn_off', get_option( $fx.'_ml_assistant_access_token' ) ) );

			}
			
		}
		// Form submit to change Auto Apply function
		else if ( isset( $_POST["ml_assistant_auto_apply_nonce"] ) and wp_verify_nonce( $_POST['ml_assistant_auto_apply_nonce'], 'ml_assistant_auto_apply' ) ) {
			
			if ( isset( $_POST[$ml_assistant_auto_option_name] ) ) {
			
				// Update WP option:
				update_option( $ml_assistant_auto_option_name, sanitize_text_field( $_POST[$ml_assistant_auto_option_name] ) );
				
				// Send API call -> auto enabled:
				if ( sanitize_text_field($_POST[$ml_assistant_auto_option_name]) == '1' ) {
					$api_call = $llama_redirect_assistant->api_call( array( 'auto_on', get_option( $fx.'_ml_assistant_access_token' ) ) );
				}
				// Send API call -> auto disabled:
				else {
					$api_call = $llama_redirect_assistant->api_call( array( 'auto_off', get_option( $fx.'_ml_assistant_access_token' ) ) );
				}

			}
		}
		// Return TRUE, because no action was set
		return true;
	}
	private static function plugin_api_file_not_found_card() {
		$fx = llama_redirect_PREFIX;
		$llama_redirect_assistant = new llama_redirect_assistant;
		$html = '
		<h1>' . __( 'Ops' , 'llama-redirect' ) . '</h1>
		<h2>' . __( 'We tried to find API requests receiver file, but it not exists', 'llama-redirect' ) . '</h2>
		<code class="'.$fx.'-inline-block '.$fx.'-left-align">
			<p>' . sprintf( __( 'We tried to find: %s file', 'llama-redirect'), '<u>' . plugins_url() . $llama_redirect_assistant->api_receiver_file_path . '</u>' ) . '</p>
			<p>' . __( 'But it not exists or not working properly', 'llama-redirect' ) . '</p>
			<p>' . sprintf( __( 'Please try to reinstall the plugin or contact us using %s', 'llama-redirect'), '<a href="'.esc_url(admin_url('/admin.php?page=llama-redirect-contact')).'">' . __( 'this form', 'llama-redirect') . '</a>') . '</p>
		</code>';
		return $html;
	}
	private function error_card() {
		$html = '
		<h1>' . __( 'Ops', 'llama-redirect' ) . '</h1>
		<h2>' . __( 'An error occured', 'llama-redirect' ) . '</h2>
		<p>' . sprintf( __( 'Please try last action again or contact us using %s', 'llama-redirect' ), '<a href="'.esc_url(admin_url('/admin.php?page=llama-redirect-contact')).'">' . __( 'this form', 'llama-redirect' ) . '</a>' ) . '</p>';
		return $html;
	}	
	private function settings_form() {
		$fx = llama_redirect_PREFIX;
		$llama_redirect = new llama_redirect;
		$elements = new llama_redirect_elements;
		$config = $llama_redirect->config();
		$html = '';

		//prepare settings names into array
		$setting_names = array();
		if (isset($config->ml_options) and !empty($config->ml_options)) {
			foreach($config->ml_options as $setting) {
				$setting_names[] = $setting['id'];
			}
		}
		
		$label_close_settings = __( 'Close settings', 'llama-redirect' );
		$label_open_settings = __( 'Open settings' , 'llama-redirect' );
		
		//create form:
		$html .= 
		'<div class="'.$fx.'-ml-chat-container">
			<div class="'.$fx.'-row '.$fx.'-mb-1">
				<div class="'.$fx.'-col '.$fx.'-s12">
					<a
						class="button-secondary js-'.$fx.'-toggle-settings-button is_close"
						onclick="llama_redirect.assistant_toggle_settings(this)"
						data-is_open="' . esc_attr( $label_close_settings ) . '"
						data-is_close="' . esc_attr( $label_open_settings ) . '"></a>
				</div>
			</div>
		</div>
		<div id="'.$fx.'ml_settings" class="'.$fx.'-row" style="display:none">
			<div class="'.$fx.'-col '.$fx.'-s12">';
				
				// Show table with settings
				$html .= '
				<table class="'.$fx.'-bordered '.$fx.'-left-align">
					<thead>
						<tr>
							<th width="25%">' . __( 'Setting' , 'llama-redirect' ) . '</th>
							<th width="10%"></th>
							<th width="45%">' . __( 'Description', 'llama-redirect') . '</th>
							<th width="25%">' . __( 'Current status', 'llama-redirect') . '</th>
						</tr>
					</thead>
					<tbody>';
						//1) ML Assistant:
						$ml_assistant_option_name = $fx.'_ml_assistant_enabled';
						$ml_assistant_enabled = ((get_option( $ml_assistant_option_name )) ? true : false);
						$html .= 
						'<tr>
							<th>
								<div class="'.$fx.'-circle '.(($ml_assistant_enabled) ? 'green' : 'grey').'"></div>
								<span>' . __( 'Llama Assistant' , 'llama-redirect') . '</span>
							</th>
							<td>
								<form action="" method="post">';
									#add nonce field:
									$html .= wp_nonce_field( 'ml_assistant_enabled', 'ml_assistant_enabled_nonce' );
									
									// If enabled -> show button to turn off:
									if ($ml_assistant_enabled) {
										$html .= 
										'<button class="button button-link-delete" type="submit" name="'.$ml_assistant_option_name.'" value="0">
											<span>' . __( 'Turn Off', 'llama-redirect' ) . '</span>
										</button>';
									}
									else {
										$html .= 
										'<button class="button button-primary" type="submit" name="'.$ml_assistant_option_name.'" value="1">
											<span>' . __( 'Turn On', 'llama-redirect' ) . '</span>
										</button>';	
									}
								$html .= '
								</form>
							</td>
							<td>
								<span>' . __( 'Machine Learning Assistant sends personalized suggestions, based on your statistical data, tracks errors and conflicts. Keeps everything to be in working order.', 'llama-redirect') . '</span>
							</td>
							<td>';
								if ($ml_assistant_enabled) {
									$html .= '<span>' . __( 'Syncronization is active', 'llama-redirect' ) . '</span>';
								}
								else {
									$html .= '<span>' . __( 'Syncronization is off', 'llama-redirect' ) . '</span>';
								}
							$html .= '
							</td>
						</tr>';
						
						//2) ML Assistant Auto Apply function:
						$ml_assistant_auto_option_name = $fx.'_ml_assistant_auto_apply_enabled';
						$ml_assistant_auto_enabled = ((get_option($ml_assistant_auto_option_name)) ? true : false);
						$html .= 
						'<tr>
							<th>
								<div class="'.$fx.'-circle '.(($ml_assistant_auto_enabled) ? 'green' : 'grey').'" ></div>
								<span>' . __( 'Llama Assistant Auto Apply', 'llama-redirect' ) . '</span>
							</th>
							<td>
								<form action="" method="post">';
									#add nonce field:
									$html .= wp_nonce_field( 'ml_assistant_auto_apply', 'ml_assistant_auto_apply_nonce' );
									
									// If enabled -> show button to turn off:
									if ($ml_assistant_auto_enabled) {
										$html .= 
										'<button class="button button-link-delete" type="submit" name="'.$ml_assistant_auto_option_name.'" value="0">
											<span>' . __( 'Turn Off', 'llama-redirect' ) . '</span>
										</button>';
									}
									else {
										$html .= 
										'<button class="button button-primary" type="submit" name="'.$ml_assistant_auto_option_name.'" value="1">
											<span>' . __( 'Turn On', 'llama-redirect' ) . '</span>
										</button>';	
									}
								$html .= '
								</form>
							</td>
							<td>
								<span>' . __( 'Allows to automatically apply a suggestion, make changes in settings of this plugin. For example, when you get a suggestion to change a function or create a Redirect Rule, now you have to do it manually. If enabeld, Llama Assistant will do this action for you.', 'llama-redirect' ) . '</span>
							</td>
							<td>';
								if ($ml_assistant_auto_enabled) {
									$html .= '<span>' . __( 'Llama Assistant has permissions to change your settings', 'llama-redirect' ) . '</span>';
								}
								else {
									$html .= '<span>' . __( 'Access to settings is restricted', 'llama-redirect' ) . '</span>';
								}
							$html .= '
							</td>
						</tr>';
						
					$html .= '
					</tbody>
				</table>';

			$html .= '
			</div>
		</div>';
		return $html;
	}
	public function chatbot() {
		$llama_redirect_assistant = new llama_redirect_assistant;
		$fx = llama_redirect_PREFIX;
		$html = '';
		
		$iframe_url = $llama_redirect_assistant->llama_ml_assistant_url.'/chatbot/llama_redirect/'.get_option( $fx.'_ml_assistant_access_token' ).'/';
		
		$html .= 
		'<div id="llama_redirect_ml_assistant" data-src="'.$iframe_url.'">';
			//add preloader
			$html .= '<div class="'.$fx.'-pong-loader ml-preloader"></div>';
		$html .= '
		</div>';
		
		return $html;
	}
	public function welcome_card() {
		$fx = llama_redirect_PREFIX;
		$llama_redirect_assistant = new llama_redirect_assistant;
		$llama_redirect = new llama_redirect;
		$license_key = $llama_redirect->_key();
		$html = '';
		
		if (empty($license_key)) {
			$html = '
			<div class="'.$fx.'-center">
				<h1>' . __('There is a problem with your license key', 'llama-redirect' ) . '</h1>
				<p>' . __('Please check that you have a valid license key and try again', 'llama-redirect' ) . '</p>
			</div>';
		}
		else {
			$iframe_url_params = array(
				'lang' => get_bloginfo( 'language' ),
				'date_format' => get_option('date_format')
			);
			$iframe_url = $llama_redirect_assistant->llama_ml_assistant_url.'/start/llama_redirect/'.base64_encode( $llama_redirect->_key() ).'/?'.http_build_query($iframe_url_params);
			
			$html .= 
			'<div id="llama_redirect_ml_assistant" data-src="'.$iframe_url.'">';
				//add preloader:
				$html .= '<div class="'.$fx.'-pong-loader ml-preloader"></div>';
				//show confirmation button:
				$html .= '
				<form action="" method="post">';
					#add nonce field:
					$html .= wp_nonce_field( 'start_ml_assistant', 'start_ml_assistant_nonce' );
					#add table contents:
					$html .= '
					<input type="submit" name="submit-form" id="submit-form" class="button button-primary button-hero" value="' . esc_attr( __( 'Confirm & Enable Llama Assistant', 'llama-redirect' ) ) . '">
				</form>';
			$html .= '
			</div>';
		}
		return $html;
	}
}
$llama_redirect_assistant_page = new llama_redirect_assistant_page();