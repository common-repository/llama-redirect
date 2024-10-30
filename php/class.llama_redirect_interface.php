<?php
defined( 'ABSPATH' ) or exit;


class llama_redirect_interface {
	
	private static $APP_CLASS = 'llama_redirect';
	
	public static function init_menu() {
		$llama_plugin = new self::$APP_CLASS;
		$init_menu_config = $llama_plugin->config()->menu;
		if (function_exists('add_menu_page') and function_exists('add_submenu_page') and isset($init_menu_config['pages']) and !empty($init_menu_config['pages']) and isset($init_menu_config['global']['icon'])) {
			foreach($init_menu_config['pages'] as $page_idx => $page_data) {
				if (isset($page_data['sub']) and !empty($page_data['sub']))	{
					//add parent:
					if (isset($page_data['title']) and isset($page_data['capability']) and isset($page_data['menu_slug'])) {
						add_menu_page(
							$page_data['title'],
							((isset($page_data['menu_title'])) ? $page_data['menu_title'] : $page_data['title']),
							$page_data['capability'],
							$page_data['menu_slug'],
							array( str_replace( '-', '_', $page_data['menu_slug'].'_index_page' ), 'show_page' ),
							$init_menu_config['global']['icon']
						);
					}
					//add sub:
					foreach($page_data['sub'] as $sub_idx => $sub_data) {
						if (isset($page_data['menu_slug']) and isset($sub_data['title']) and isset($sub_data['capability']) and isset($page_data['menu_slug']) and isset($sub_data['menu_slug'])) {
							add_submenu_page(
								$page_data['menu_slug'],
								$sub_data['title'],
								((isset($sub_data['menu_title'])) ? $sub_data['menu_title'] : $sub_data['title']),
								$sub_data['capability'],
								$page_data['menu_slug'].$sub_data['menu_slug'],
								array( str_replace( '-', '_', $page_data['menu_slug'].$sub_data['menu_slug'].'_page' ), 'show_page' )
							);
						}
					}
				}
				else {
					//add parent only:
					if (isset($page_data['title']) and isset($page_data['capability']) and isset($page_data['menu_slug']) and isset($init_menu_config['global']['icon'])) {
						add_menu_page(
							$page_data['title'],
							((isset($page_data['menu_title'])) ? $page_data['menu_title'] : $page_data['title']),
							$page_data['capability'],
							$page_data['menu_slug'],
							array( str_replace( '-', '_', $page_data['menu_slug'].'_index_page' ), 'show_page' ),
							$init_menu_config['global']['icon']
						);
					}
				}
			}
		}
	}
	public static function render_post_box_buttons($wp_post_obj,$button_block_id) {
		if (isset($button_block_id['args'])) {
			$llama_plugin = new self::$APP_CLASS;
			if (!isset($llama_plugin->config()->{$button_block_id['args']})) {
				return false;	
			}
			$quick_buttons = $llama_plugin->config()->{$button_block_id['args']};
			$html = '';
			if (!empty($quick_buttons)) {
				foreach($quick_buttons as $btn_idx => $btn_data) {
					$html = '<a ';	
					if (isset($btn_data['attr']) and !empty($btn_data['attr'])) {
						foreach($btn_data['attr'] as $attr_key => $attr_val) {
							$html .= ' '.$attr_key.'="'.esc_attr($attr_val).'" ';	
						}
					}
					$html .= '>'.((isset($btn_data['icon'])) ? $btn_data['icon'].' ' : '').$btn_data['text'].'</a>';
				}
			}
			echo $html;
		}
	}
	public static function post_quick_boxes() {
		$llama_plugin = new self::$APP_CLASS;
		if (!isset($llama_plugin->config()->post_quick_boxes)) {
			return false;	
		}
		$quick_buttons = $llama_plugin->config()->post_quick_boxes;
		
		if (!empty($quick_buttons)) {
			foreach($quick_buttons as $block_idx => $block_data) {
				add_meta_box(
					$block_data['id'],
					$block_data['title'],
					array('llama_redirect_interface', 'render_post_box_buttons'),
					'post',
					'side',
					'default',
					$block_data['content']
				);
			}
		}
	}
	public static function load_scripts_admin() {
		$llama_redirect_interface = new llama_redirect_interface;
		$llama_redirect_interface->load_scripts('admin');
	}
	public static function load_scripts_wp() {
		$llama_redirect_interface = new llama_redirect_interface;
		$llama_redirect_interface->load_scripts('wp');
	}
	
	public function load_scripts($mode) {
		$llama_plugin = new self::$APP_CLASS;
		//load css [admin]
		$css_route = 'css_'.$mode;
		if (isset($llama_plugin->config()->$css_route)) {
			$load_css = $llama_plugin->config()->$css_route;
			if (!empty($load_css)) {
				foreach($load_css as $script_data) {
					if (isset($script_data['id']) and !empty($script_data['id'])) {
						wp_register_style( $script_data['id'], llama_redirect_PLUGIN_URL . 'css/'.$script_data['id'].'.css', false, llama_redirect_PLUGIN_VER );
						wp_enqueue_style( $script_data['id'] );
					}
				}
			}
		}
		//load js [admin]
		$js_route = 'js_'.$mode;
		if (isset($llama_plugin->config()->$js_route)) {
			$load_js = $llama_plugin->config()->$js_route;
			if (!empty($load_js)) {
				foreach($load_js as $script_data) {
					if (isset($script_data['id']) and !empty($script_data['id'])) {
						wp_enqueue_script($script_data['id'], llama_redirect_PLUGIN_URL. 'js/'.$script_data['id'].'.js', array ( 'jquery' ), llama_redirect_PLUGIN_VER, true);	
						wp_localize_script($script_data['id'],'ajax_object',array('ajax_url' => admin_url( 'admin-ajax.php' )));
					}
				}
			}
		}
	}
	public static function plugin_action_links($links) {
		$llama_plugin = new self::$APP_CLASS;
		$add_plugin_links = $llama_plugin->config()->plugin_links;
		$new_links = array();
		if (!empty($add_plugin_links)) {
			foreach($add_plugin_links as $link_data) {
				$new_links[] = '<a href="' . esc_url( admin_url( '/admin.php?page=' . $link_data['url'] ) ) . '">' . $link_data['title'] . '</a>';
			}
		}
		$links = array_merge($new_links,$links);
		return $links;
	}
	public static function web_admin_links() {
		global $wp_admin_bar;
		$llama_plugin = new self::$APP_CLASS;
		if (isset($llama_plugin->config()->web_admin_links)) {
			$web_admin_links = $llama_plugin->config()->web_admin_links;
			if (!empty($web_admin_links)) {
				foreach($web_admin_links as $link_data) {
					//1) add parent:
					$parent = array(
						'id' => $link_data['id'],
						'title'  => $link_data['icon'].$link_data['title'],
						'href'   => $link_data['url'],
						'meta'   => array(),
					);
					//2) add meta:
					if (isset($link_data['meta']) and !empty($link_data['meta'])) {
						foreach($link_data['meta'] as $meta_key => $meta_value) {
							$parent['meta'][$meta_key] = $meta_value;
						}
					}
					//3) show button:
					if (isset($link_data['_view']) and is_array($link_data['_view'])) {
						//3.1) show in both:
						if (in_array('admin',$link_data['_view']) and in_array('web',$link_data['_view'])) {
							$wp_admin_bar->add_menu($parent);
						}
						//3.2) show in web only
						else if (in_array('web',$link_data['_view'])) {
							if ( !is_admin() ) {
								$wp_admin_bar->add_menu($parent);
							}
						}
						//3.3) show in admin only
						else if (in_array('admin',$link_data['_view'])) {
							if ( is_admin() ) {
								$wp_admin_bar->add_menu($parent);
							}
						}
					}
				}
			}
		}
	}
	public static function llama_redirect_plugin_textdomain() {
		load_plugin_textdomain( 'llama-redirect', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );	
	}
	public function convert_date_to_user_format($date) {
		$date_object = new stdClass();
		//1) convert date:
		$date_object->date = date(get_option('date_format'),strtotime($date));
		//2) convert time:
		$date_object->time = date(get_option('time_format'),strtotime($date));
		//3) convert date_time:
		$date_object->date_time = date(get_option('date_format').' '.get_option('time_format'),strtotime($date));
		return $date_object;
	}
}