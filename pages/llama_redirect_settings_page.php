<?php
defined( 'ABSPATH' ) or exit;

class llama_redirect_settings_page {
	public static function show_page() {
		global $llama_redirect_debugger;
		$llama_redirect = new llama_redirect;
		$fx = llama_redirect_PREFIX;
		$config = $llama_redirect->config();
		$elements = new llama_redirect_elements;
		
		echo $elements->nav($fx);
		echo $elements->title( $fx , __( 'Settings', 'llama-redirect' ) );
		
		$setting_names = array();
		if (isset($config->settings_options) and !empty($config->settings_options)) {
			foreach($config->settings_options as $setting) {
				$setting_names[] = $setting['id'];
			}
		}

		//Show form:
		echo 
		'<div class="'.$fx.'-container">
			<div class="'.$fx.'-card">
				<div class="'.$fx.'-card-content">';
					//form
					echo '
					<form method="post" action="options.php">
						'.wp_nonce_field('update-options').'
						'.settings_fields( $fx.'_settings' ).'
							
						<input type="hidden" name="action" value="update" />
						<input type="hidden" name="page_options" value="'.implode(',',$setting_names).'" />
							
						<table class="form-table">';
							if (isset($config->settings_options) and !empty($config->settings_options)) {
								foreach($config->settings_options as $setting) {
									$setting_value = get_option($setting['id']);
									//checkbox:
									if ($setting['input_type'] == 'checkbox') {
										if ($setting_value == '1') {
											$setting['attr']['checked'] = 'checked';	
										}
										else {
											unset($setting['attr']['checked']);
										}
									}
									//other fields:
									else {
										$setting['value'] = $setting_value;	
									}
									echo $elements->table_row($setting, NULL, NULL, array('tooltip' => $fx.'-right') );
								}
							}
						echo '
						</table>
						<p class="submit">
							<input type="submit" class="button-primary" value="'.__( 'Save Changes' , 'llama-redirect' ).'" />
						</p>
					</form>';
				//close divs:
				echo '
				</div>
			</div>
		</div>';
		$llama_redirect_debugger->page_loaded('llama_redirect_settings_page');
	}
}