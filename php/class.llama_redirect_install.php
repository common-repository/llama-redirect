<?php

defined( 'ABSPATH' ) or exit;
class llama_redirect_install
{
    protected static  $instance ;
    public function __construct()
    {
    }
    
    public static function init()
    {
        is_null( self::$instance ) and self::$instance = new self();
        return self::$instance;
    }
    
    public function create_llama_redirect_databases()
    {
        //1) settings table:
        $this->llama_redirect_settings();
        //2) stats table:
        $this->llama_redirect_stats();
        //3) 404 log:
        $this->llama_404_log();
    }
    
    public function llama_redirect_settings()
    {
        global  $wpdb ;
        $charset_collate = $wpdb->get_charset_collate();
        $redirect_settings_table_name = $wpdb->prefix . "llama_redirect_settings";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$redirect_settings_table_name}'" ) != $redirect_settings_table_name ) {
            //simple table:
            $sql = "CREATE TABLE {$redirect_settings_table_name} (\r\n\t\t\t\t\t`redirect_id` mediumint(9) NOT NULL AUTO_INCREMENT,\r\n\t\t\t\t\t`link_from` VARCHAR(500) NULL,\r\n\t\t\t\t\t`link_to` VARCHAR(500) NULL,\r\n\t\t\t\t\t`red_code` INT(11) NOT NULL DEFAULT '301',\r\n\t\t\t\t\t`track` TINYINT(1) NOT NULL DEFAULT '0',\r\n\t\t\t\t\t`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\r\n\t\t\t\t\tPRIMARY KEY  (redirect_id)\r\n\t\t\t\t) {$charset_collate};";
            dbDelta( $sql );
            //add unique:
            dbDelta( "ALTER TABLE {$redirect_settings_table_name} ADD UNIQUE KEY `link_from` (`link_from`)" );
        } else {
        }
        
        flush_rewrite_rules();
    }
    
    public function llama_redirect_stats()
    {
        global  $wpdb ;
        $charset_collate = $wpdb->get_charset_collate();
        $redirect_stats_table_name = $wpdb->prefix . "llama_redirect_stats";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$redirect_stats_table_name}'" ) != $redirect_stats_table_name ) {
            $sql = "CREATE TABLE {$redirect_stats_table_name} (\r\n\t\t\t`stats_id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,\r\n\t\t\t`redirect_id` INT NOT NULL,\r\n\t\t\t`client_ip` TEXT NULL,\r\n\t\t\t`client_browser` TEXT NULL,\r\n\t\t\t`client_os` TEXT NULL,\r\n\t\t\t`client_hash` TEXT NULL,\r\n\t\t\t`action_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\r\n\t\t\tPRIMARY KEY  (stats_id)\r\n\t\t  \t) {$charset_collate};";
            dbDelta( $sql );
        }
        
        flush_rewrite_rules();
    }
    
    public function llama_404_log()
    {
        global  $wpdb ;
        $charset_collate = $wpdb->get_charset_collate();
        $redirect_404_log_table_name = $wpdb->prefix . "llama_404_log";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$redirect_404_log_table_name}'" ) != $redirect_404_log_table_name ) {
            $sql = "CREATE TABLE {$redirect_404_log_table_name} (\r\n\t\t\t`log_id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,\r\n\t\t\t`page_url` VARCHAR(255) NULL,\r\n\t\t\t`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\r\n\t\t\tPRIMARY KEY  (log_id)\r\n\t\t  \t) {$charset_collate};";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
        }
        
        flush_rewrite_rules();
    }
    
    public static function activate_llama_redirect()
    {
        $llama_redirect_install = new llama_redirect_install();
        $llama_redirect_install->create_llama_redirect_databases();
    }
    
    public static function deactivate_llama_redirect()
    {
        /**
         * Tables will not be removed, to allow access to LOG
         */
        //0) init:
        $llama_redirect = new llama_redirect();
        $config = $llama_redirect->config();
        //1) remove options:
        if ( isset( $config->settings_options ) and !empty($config->settings_options) ) {
            foreach ( $config->settings_options as $setting ) {
                delete_option( $setting['id'] );
            }
        }
    }

}
// Should not be included in class, because is called from Freemius
function llama_redirect_fs_uninstall_cleanup()
{
    if ( !current_user_can( 'activate_plugins' ) ) {
        return;
    }
    //0) init:
    global  $wpdb ;
    $llama_redirect = new llama_redirect();
    $config = $llama_redirect->config();
    //1) remove databases:
    #settings
    $redirect_settings_table_name = $wpdb->prefix . "llama_redirect_settings";
    $wpdb->query( "DROP TABLE IF EXISTS " . $redirect_settings_table_name );
    #stats
    $redirect_stats_table_name = $wpdb->prefix . "llama_redirect_stats";
    $wpdb->query( "DROP TABLE IF EXISTS " . $redirect_stats_table_name );
    #404_log
    $redirect_404_log_table_name = $wpdb->prefix . "llama_404_log";
    $wpdb->query( "DROP TABLE IF EXISTS " . $redirect_404_log_table_name );
    //2) remove options:
    if ( isset( $config->settings_options ) and !empty($config->settings_options) ) {
        foreach ( $config->settings_options as $setting ) {
            delete_option( $setting['id'] );
        }
    }
    //3) remove assistant options:
    if ( isset( $config->ml_options ) and !empty($config->ml_options) ) {
        foreach ( $config->ml_options as $option ) {
            delete_option( $option['id'] );
        }
    }
}
