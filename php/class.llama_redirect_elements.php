<?php
defined( 'ABSPATH' ) or exit;


class llama_redirect_elements {
	
	private static $APP_CLASS = 'llama_redirect';
	
	public function nav($fx) {
		
		$llama_plugin = new self::$APP_CLASS;
		$llama_assistant = new llama_redirect_assistant;
		$menu = $llama_plugin->config()->menu;
		
		$html = '
		<div class="'.$fx.'-nav" data-page="'.((isset($_GET['page'])) ? sanitize_text_field($_GET['page']) : '').'">
			<div class="'.$fx.'-container">
				<div class="'.$fx.'-row">
					<div class="'.$fx.'-col '.$fx.'-s12 '.$fx.'-center">
						<div class="'.$fx.'-logo-wrapper">
							<img src="' . llama_redirect_PLUGIN_URL . 'assets/icon-128x128.png' . '" id="llama_redirect_logo"/>
							'.((defined('llama_redirect_TITLE')) ? '<span>'.llama_redirect_TITLE.'</span>' : '').'
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="'.$fx.'-sub-nav">
			<div class="'.$fx.'-container">
				<div class="'.$fx.'-row">
					<div class="'.$fx.'-col '.$fx.'-s12 '.$fx.'-center">
						<div class="nav-tab-wrapper '.$fx.'-tabs">';
						if (isset($menu['pages']) and !empty($menu['pages'])) {
							foreach($menu['pages'] as $page_idx => $page_data) {
								
								//show parent:
								$is_active = ((isset($_GET['page']) and sanitize_text_field($_GET['page']) == $page_data['menu_slug']) ? true : false);
								$html .= '<a class="nav-tab '.(($is_active) ? 'nav-tab-active' : '').'" href="'.esc_url(admin_url('/admin.php?page='.$page_data['menu_slug'])).'">'.$page_data['title'].'</a>';		
								
								//show sub:
								if (isset($page_data['sub']) and !empty($page_data['sub'])) {
									foreach($page_data['sub'] as $sub_idx => $sub_data) {
										
										$is_active = ((isset($_GET['page']) and sanitize_text_field($_GET['page']) == $page_data['menu_slug'].$sub_data['menu_slug']) ? true : false);
										
										//assistant:
										if ($sub_data['menu_slug'] == '_assistant') {
											$html .= 
											'<a
												class="nav-tab '.(($is_active) ? 'nav-tab-active' : '').'"
												href="'.esc_url(admin_url('/admin.php?page='.$page_data['menu_slug'].$sub_data['menu_slug'])).'">
													<span>'.$sub_data['title'].'</span>';
													#<span class="'.$fx.'-badge">' . $llama_assistant->badge_notifications() . '</span>
											$html .= '
											</a>';	
										}
								
										//other sections:
										else {
											$html .= 
											'<a
												class="nav-tab '.(($is_active) ? 'nav-tab-active' : '').'"
												href="'.esc_url(admin_url('/admin.php?page='.$page_data['menu_slug'].$sub_data['menu_slug'])).'">
													<span>'.$sub_data['title'].'</span>
											</a>';	
										}
									}
								}
							}
						}
				$html .= '
						</div>
					</div>
				</div>
			</div>
		</div>
		';
		$html .= wp_get_nav_menu_items('llama-redirect');
		return $html;	
	}
	public function title( $fx, $title = '', $html_after = '', $html_before = '', $h1_class = '') {
		$html = 
		'<div class="'.$fx.'-container '.$fx.'-page-title">
			<div class="'.$fx.'-row">
				<div class="'.$fx.'-col '.$fx.'-s12 '.$fx.'-center">
					<h1 '.((!empty($h1_class)) ? 'class="'.$h1_class.'"' : '').'>'.$html_before.$title.$html_after.'</h1>
				</div>
			</div>
		</div>';
		return $html;
	}
	public function create_input_select($fx,$field_data,$values = array()) {
		$html = '';
		$is_multiple = ((isset($field_data['attr']['multiple'])) ? true : false);
		
		if (isset($field_data['values']) and !empty($field_data['values'])) {
			if (isset($field_data['values'][0]['group_title'])) {
				$html = '<select name="'.$field_data['id'] . (($is_multiple) ? '[]' : '').'" id="'.$field_data['id'].'"';
				if (isset($field_data['attr']) and !empty($field_data['attr'])) {
					foreach($field_data['attr'] as $attr_key => $attr_value) {
						if ($attr_value) {
							$html .= ' '.$attr_key.'="'.esc_attr($attr_value).'" ';	
						}
					}
				}
				if (isset($field_data['_mode']) and $field_data['_mode'] == 'pro' and !(  llama_redirect_fs()->is__premium_only() ) ) {
					$html .= ' disabled="disabled" ';	
				}
				
				$html .= '>';
				foreach($field_data['values'] as $group) {
					$html .= '<optgroup label="'.$group['group_title'].'">';
					if (isset($group['list']) and !empty($group['list'])) {
						foreach($group['list'] as $option) {
							$html .= 
							'<option
								value="'.esc_attr($option['value']).'" '
								. ((isset($option['helper'])) ? 'data-helper="'.esc_attr($option['helper']).'"' : '')
								. ((isset($values[$field_data['id']]) and in_array($option['value'],$values[$field_data['id']])) ? 'selected="selected"' : '')
								. '>'
								. esc_attr($option['title']) . 
							'</option>';
						}
					}
					$html .= '</optgroup>';
				}
				$html .= '</select>';
				$html .= '<div class="'.$fx.'-select-helper" data-for="'.$field_data['id'].'"></div>';
			}
		}
		return $html;
	}
	public function create_input_simple($fx,$field_data) {
		$html = 
		'<input 
			type="'.((isset($field_data['input_type'])) ? $field_data['input_type'] : 'text').'"
			id="'.$field_data['id'].'"
			value="'.((isset($field_data['value'])) ? $field_data['value'] : '').'"
			name="'.$field_data['id'].'"';
			if (isset($field_data['attr']) and !empty($field_data['attr'])) {
				foreach($field_data['attr'] as $attr_key => $attr_value) {
					if ($attr_value) {
						$html .= ' '.$attr_key.'="'.esc_attr($attr_value).'" ';	
					}
				}
			}
			if (isset($field_data['_mode']) and $field_data['_mode'] == 'pro' and  !( llama_redirect_fs()->is__premium_only() ) ) {
				$html .= ' disabled="disabled" ';	
			}
		$html .= '
		/>';
		if (isset($field_data['append_html'])) {
			$html .= $field_data['append_html'];
		}
		
		return $html;
	}
	public function table_row($field_data,$pre_defined_url = '',$values = array(), $css_rules = array()) {
		$fx = llama_redirect_PREFIX;
		//one input field in one row only:
		if (isset($field_data['id'])) {
			
			$tooltip_class = ((isset($css_rules['tooltip'])) ? $css_rules['tooltip'] : '');
			
			$html = '
			<tr valign="top">
				<th scope="row" width="25%" align="left">
					<label for="'.$field_data['id'].'">'.esc_html( ((isset($field_data['label'])) ? $field_data['label'] : '') ).'</label>
					<span class="'.$fx.'-tooltip '.$fx.'-tooltip-top '.$tooltip_class.'">
						<span class="dashicons dashicons-editor-help"></span>
						<span class="'.$fx.'-tooltiptext">'.esc_html( ((isset($field_data['helper'])) ? $field_data['helper'] : '') ).'</span>
					</span>
				</th>
				<td width="75%">
					<div>';
					if ($field_data['input_type'] == 'select') {
						$html .= $this->create_input_select($fx,$field_data,$values);
					}
					else {
						if ($field_data['id'] == 'link_from') {
							$field_data['value'] = $pre_defined_url;
						}
						if (isset($values[$field_data['id']])) {
							$field_data['value'] = $values[$field_data['id']];
						}
						$html .= $this->create_input_simple($fx,$field_data);
					}
				$html .= '
					</div>
				</td>
			</tr>';	
		}
		//multiple input fields in one row
		else {
			$html = '
			<tr valign="top">
				<th scope="row" width="25%" align="left">
					<label for="'.$field_data[0]['id'].'">'.esc_html($field_data[0]['label']).'</label>
					<span class="'.$fx.'-tooltip '.$fx.'-tooltip-top">
						<span class="dashicons dashicons-editor-help"></span>
						<span class="'.$fx.'-tooltiptext">'.esc_html($field_data[0]['helper']).'</span>
					</span>
				</th>
				<td width="75%">
					<table width="100%">
						<tr>';
						foreach($field_data as $one_field_data) {
							$html .= '<td><div>';
							if ($one_field_data['input_type'] == 'select') {
								$html .= $this->create_input_select($fx,$one_field_data,$values);
							}
							else {
								if ($one_field_data['id'] == 'link_from') {
									$one_field_data['value'] = $pre_defined_url;
								}
								if (isset($values[$one_field_data['id']])) {
									$one_field_data['value'] = $values[$one_field_data['id']];
								}
								$html .= $this->create_input_simple($fx,$one_field_data,$values);
							}
							$html .= '</div></td>';
						}
						
				$html .= '
						</tr>
					</table>
				</td>
			</tr>';	
		}
		//return result:
		return $html;
	}
	public function table_delimiter($title,$cols) {
		$html = '<tr>';	
		$html .= '<th colspan="'.$cols.'">'.$title.'</th>';
		$html .= '</tr>';
		return $html;
	}
	public function table_title($title,$cols) {
		$html = '<tr>';
		for ($i = 1; $i <= $cols; $i++) {
			$html .= '<th>'.(($cols == 2 and $i == 2) ? $title : '').'</th>';
		}
		$html .= '</tr>';
		return $html;
	}
	public function notice($type,$text,$dismissible = true) {
		$html = 
		'<div class="notice notice-'.$type.' '.(($dismissible) ? 'is-dismissible' : '').'"> 
			<p><strong>'.$text.'</strong></p>';
			if ($dismissible) {
				$html .= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__( 'Dismiss this notice', 'llama-redirect' ).'</span></button>';
			}
		$html .= '
		</div>';
		return $html;
	}
}