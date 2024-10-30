<?php
defined( 'ABSPATH' ) or exit;


class llama_redirect {
	
	public function init() {
		$this->add_hooks();
		$this->activate_listeners();
	}
	public function config() {
		$fx = llama_redirect_PREFIX;
		$config = new stdClass();
		$config->menu = array(
			'global' => array(
				'icon' => 'dashicons-admin-links',
			),
			'pages' => array(
				array(
					'title' => __( 'URL redirects', 'llama-redirect' ),
					'capability' => 'moderate_comments',
					'menu_slug' => 'llama-redirect',
					'sub' => array(
						array(
							'title' => __( 'Llama Assistant', 'llama-redirect' ),
							'menu_title' => __( 'Llama Assistant', 'llama-redirect' ) . ' <span class="awaiting-mod">new</span>',
							'capability' => 'manage_options',
							'menu_slug' => '_assistant'
						),
						array(
							'title' => __( 'Settings', 'llama-redirect' ),
							'capability' => 'administrator',
							'menu_slug' => '_settings'
						),
						array(
							'title' => __( 'Add URL', 'llama-redirect' ),
							'capability' => 'manage_options',
							'menu_slug' => '_add'
						),
						array(
							'title' => __( 'Statistics', 'llama-redirect' ),
							'capability' => 'manage_options',
							'menu_slug' => '_stats'
						),
						array(
							'title' => __( '404s', 'llama-redirect' ),
							'capability' => 'manage_options',
							'menu_slug' => '_404'
						),
						array(
							'title' => __( 'Help', 'llama-redirect' ),
							'capability' => 'manage_options',
							'menu_slug' => '_help'
						)
					)
				)
			)
		);
		$config->css_admin = array(
			array(
				'id' => 'datatables.min',
			),
			array(
				'id' => 'llama_redirect_admin_css',
			),
			array(
				'id' => 'jquery.timepicker.min'
			)
		);
		$config->css_wp = array(
			array(
				'id' => 'llama_redirect_admin_css'
			)
		);
		$config->js_admin = array(
			array(
				'id' => 'datatables.min'
			),
			array(
				'id' => 'admin_redirect_admin_js'
			),
			array(
				'id' => 'jquery.timepicker.min'
			),
			array(
				'id' => 'jquery.ba-postmessage.min'
			)
		);
		if ( get_option( $fx.'_show_button_in_post_page' ) == '1' ) {
			$config->post_quick_boxes = array(
				array(
					'id' => 'llama_redirect_quick_button',
					'title' => __( 'URL redirect it!', 'llama-redirect' ),
					'content' => 'post_quick_button_redirect_it',
				)
			);
			$config->post_quick_button_redirect_it = array(
				array(
					'attr' => array(
						'href' => esc_url(admin_url('/admin.php?page=llama-redirect_add&post='.((isset($_GET['post'])) ? sanitize_text_field($_GET['post']) : 0))),
						'class' => 'button button-small button-primary',
						'target' => '_blank'
					),
					'text' => __( 'Add link', 'llama-redirect' )
				)
			);
		}
		$config->plugin_links = array(
			array(
				'title' => __( 'My redirects', 'llama-redirect' ),
				'url' => 'llama-redirect',
			),
			array(
				'title' => __( 'Add link', 'llama-redirect' ),
				'url' => 'llama-redirect_add',
			),
			array(
				'title' => __( 'Help', 'llama-redirect' ),
				'url' => 'llama-redirect_help'
			)
		);
		if ( get_option( $fx.'_show_button_in_web' ) == '1' ) {
			$config->web_admin_links = array(
				array(
					'id' => $fx.'_web_admin',
					'title' => __( 'URL redirect it!', 'llama-redirect' ),
					'icon' => '<span class="dashicons dashicons-admin-links wp-media-buttons-icon" style="font:normal 14px/1 dashicons;position:relative;top:9px;margin-right:5px;"></span>',
					'url' => esc_url(admin_url('/admin.php?page=llama-redirect_add&link_from='.add_query_arg(NULL,NULL))),
					'meta' => array(
						'target' => '_blank',
						'class' => $fx.'-main-icon-pseudo',
					),
					'_view' => array('web')
				)
			);
		}
		$config->settings_options = array(
			array(
				'id' => $fx.'_def_track',
				'label' => __( 'Default tracking enabled?', 'llama-redirect' ),
				'helper' => __( 'Check this if you want enable tracking by default. You can always uncheck this in a specific redirect rule', 'llama-redirect' ),
				'input_type' => 'checkbox',
				'value' => 1,
				'append_html' => '<span>' . __( 'Yes', 'llama-redirect' ) . '</span>',
				'args' => array(
					'default' => 1,
				)
			),
			array(
				'id' => $fx.'_show_button_in_post_page',
				'label' => __( 'Show button in Post page?', 'llama-redirect' ),
				'helper' => __( 'Check this if you want to show [URL Redirect It] button in a post editing page', 'llama-redirect' ),
				'input_type' => 'checkbox',
				'value' => 1,
				'append_html' => '<span>' . __( 'Yes', 'llama-redirect' ) . '</span>',
				'args' => array(
					'default' => 1,
				)
			),
			array(
				'id' => $fx.'_show_button_in_web',
				'label' => __( 'Show [Quick Add] ?', 'llama-redirect' ),
				'helper' => __( 'Check this if you want to show [Quick Add] button in Admins top nav panel', 'llama-redirect' ),
				'input_type' => 'checkbox',
				'value' => 1,
				'append_html' => '<span>' . __( 'Yes', 'llama-redirect' ) . '</span>',
				'args' => array(
					'default' => 1,
				)
			),
			array(
				'id' => $fx.'_force_to_https',
				'label' => __( 'Enable force HTTPS', 'llama-redirect' ),
				'helper' => __( 'Check this if you want to enable force HTTPS redirects on the any website page', 'llama-redirect' ),
				'input_type' => 'checkbox',
				'value' => 1,
				'append_html' => '<span>' . __( 'Yes', 'llama-redirect' ) . '</span>',
				'args' => array(
					'default' => 0,
				)
			),
			array(
				'id' => $fx.'_enable_404_log',
				'label' => __( 'Enable 404 LOG', 'llama-redirect' ),
				'helper' => __( 'Check this if you want to enable 404 (Not Found) error logging', 'llama-redirect' ),
				'input_type' => 'checkbox',
				'value' => 1,
				'append_html' => '<span>' . __( 'Yes', 'llama-redirect' ) . '</span>',
				'args' => array(
					'default' => 1,
				)
			)
		);
		$config->ml_options = array(
			array(
				'id' => $fx.'_ml_assistant_enabled',
				'label' => __( 'Llama Assistant enabled', 'llama-redirect' ),
				'helper' => __( 'Llama Machine Learning Assistant is enabled and working for this plugin', 'llama-redirect' ),
				'input_type' => 'checkbox',
				'value' => 0,
			),
			array(
				'id' => $fx.'_ml_assistant_access_token',
				'label' => __( 'Llama Assistant access token', 'llama-redirect' ),
				'helper' => __( 'Your Access Token, issued by Llama ML Assistant service API', 'llama-redirect' ),
				'input_type' => 'text',
				'attr' => array(
					//should be disabled, because is set automatically using form submition
					'disabled' => 'disabled'
				),
				'value' => '',
			),
			array(
				'id' => $fx.'_ml_assistant_auto_apply_enabled',
				'label' => __( 'Llama Assistant Auto Apply enabled', 'llama-redirect' ),
				'helper' => __( 'Allow ML Assistant to make changes in this plugin settings', 'llama-redirect' ),
				'input_type' => 'checkbox',
				'value' => 0,
			)
		);
		return $config;
	}
	public function add_hooks() {
		add_action( 'admin_menu', array( 'llama_redirect_interface', 'init_menu' ) );
		add_action( 'admin_init', array( 'llama_redirect', 'add_options' ) );
		add_action( 'admin_init', array( 'llama_redirect', 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( 'llama_redirect_interface', 'load_scripts_admin' ) );
		add_action( 'wp_enqueue_scripts', array( 'llama_redirect_interface', 'load_scripts_wp' ) );
		add_action( 'add_meta_boxes', array( 'llama_redirect_interface', 'post_quick_boxes' ), 10, 2 );
		add_action( 'plugin_action_links_' . llama_redirect_plugin_basename, array( 'llama_redirect_interface', 'plugin_action_links' ) );
		add_action( 'admin_bar_menu', array( 'llama_redirect_interface', 'web_admin_links' ), 100 );
		add_action( 'init', array( 'llama_redirect_interface', 'llama_redirect_plugin_textdomain' ) );
		
		// Add virtual page for Llama Assistant:
		add_filter( 'init', array('llama_redirect_assistant', 'virtual_page') );
	}
	public static function add_options() {
		$llama_redirect = new llama_redirect;
		$fx = llama_redirect_PREFIX;
		$config = $llama_redirect->config();
		if (isset($config->settings_options) and !empty($config->settings_options)) {
			foreach($config->settings_options as $setting) {
				add_option( $setting['id'], $setting['value'] );
			}
		}
		if (isset($config->ml_options) and !empty($config->ml_options)) {
			foreach($config->ml_options as $ml_setting) {
				add_option( $ml_setting['id'], $ml_setting['value'] );
			}
		}
	}
	public static function register_settings() {
		$llama_redirect = new llama_redirect;
		$fx = llama_redirect_PREFIX;
		$config = $llama_redirect->config();
		if (isset($config->settings_options) and !empty($config->settings_options)) {
			foreach($config->settings_options as $setting) {
				register_setting( $fx.'_settings', $setting['id'], $setting['args']);
			}
		}
	}
	private function activate_listeners() {
		add_action('init', array('llama_redirect_function','llama_redirect_action'), 1, 0);
		add_action('template_redirect', array('llama_redirect_function','llama_redirect_404'), 1, 0);
	}
	public function _key() {
		global $llama_redirect_fs;
		$k = '';
		$l = llama_redirect_fs()->_get_license();
		if ( is_object( $l ) and isset( $l->secret_key )) {
			$k = $l->secret_key;
		}
		if (empty($k)) {
			try {
				$temp = new ReflectionClass(get_class($llama_redirect_fs));
				$secret = $temp->getProperty('_site');
				$secret->setAccessible(true);
				$var = $secret->getValue($llama_redirect_fs);
				$k = ((isset($var->public_key)) ? $var->public_key : false);
			}
			catch (Exception $e) {}
		}
		return $k;
	}
}