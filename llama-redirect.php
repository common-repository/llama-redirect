<?php
/*
Plugin Name: SEO URL Redirects for Wordpress plugin - LlamasApps
Plugin URI:  https://llamasapps.com/wordpress-plugins/redirect-plugin/
Description: Manage all URL redirects in your Website in a few clicks. Use custom server response codes, wildcards, Time and Date based redirection. Fight IP referal spam using IP ranges as redirection rules. Enable force HTTPS. Use 404 logging.
Version:     2.0
Author:      LlamasApps
Author URI:  https://llamasapps.com/
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or exit;

//1) DEFINE:
define('llama_redirect_PLUGIN_DIR',plugin_dir_path( __FILE__ ));
define('llama_redirect_PREFIX','llama_redirect_');
define('llama_redirect_PLUGIN_URL',plugin_dir_url( __FILE__ ));
define('llama_redirect_TITLE','LlamasApps &rsaquo; ' . __('URL redirects','llama') );
define('llama_redirect_PLUGIN_VER','2.0');
define('llama_redirect_plugin_basename',plugin_basename( __FILE__ ));
define('llama_redirect_FS_ID','2876');
define('llama_redirect_DEBUGGER',0);

//1.1) LOAD STATS:
require_once(llama_redirect_PLUGIN_DIR.'/php/class.llama_redirect_stats.php');

//2) LOAD FREEMIUS:
require_once(llama_redirect_PLUGIN_DIR.'/freemius.php');

//3) LOAD LIBRARIES:
require_once(llama_redirect_PLUGIN_DIR.'php/class.llama_redirect.php');
require_once(llama_redirect_PLUGIN_DIR.'php/class.llama_redirect_function.php');
//3.1) classes for pages:
require_once(llama_redirect_PLUGIN_DIR.'pages/llama_redirect_index_page.php');
require_once(llama_redirect_PLUGIN_DIR.'pages/llama_redirect_add_page.php');
require_once(llama_redirect_PLUGIN_DIR.'pages/llama_redirect_stats_page.php');
require_once(llama_redirect_PLUGIN_DIR.'pages/llama_redirect_help_page.php');
require_once(llama_redirect_PLUGIN_DIR.'pages/llama_redirect_settings_page.php');
require_once(llama_redirect_PLUGIN_DIR.'pages/llama_redirect_404_page.php');
require_once(llama_redirect_PLUGIN_DIR.'pages/llama_redirect_assistant_page.php');
//3.2) interface loader
require_once(llama_redirect_PLUGIN_DIR.'php/class.llama_redirect_interface.php');
//3.4) database
require_once(llama_redirect_PLUGIN_DIR.'php/class.llama_redirect_database.php');
//3.5) plugin libraries
require_once(llama_redirect_PLUGIN_DIR.'php/class.llama_redirect_visitor_details.php');
require_once(llama_redirect_PLUGIN_DIR.'php/class.llama_redirect_elements.php');
//3.6) plugin installation core
require_once(llama_redirect_PLUGIN_DIR.'php/class.llama_redirect_install.php');
//3.7) assistant
require_once(llama_redirect_PLUGIN_DIR.'php/class.llama_redirect_assistant.php');
require_once(llama_redirect_PLUGIN_DIR.'php/llama_assistant_sync.php');


//4) START APP:
$llama_redirect = new llama_redirect;
$llama_redirect->init();

//5) REGISTER APP:
//5.1) activation:
register_activation_hook( __FILE__, array( 'llama_redirect_install', 'activate_llama_redirect' ) );
//5.2) deactivation:
register_deactivation_hook(__FILE__,array( 'llama_redirect_install', 'deactivate_llama_redirect' ) );
//5.3) uninstall:
llama_redirect_fs()->add_action( 'after_uninstall', 'llama_redirect_fs_uninstall_cleanup' );