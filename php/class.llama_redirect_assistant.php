<?php

defined( 'ABSPATH' ) or exit;
class llama_redirect_assistant
{
    public  $llama_ml_assistant_url = 'https://assistant.llamasapps.com' ;
    public  $rows_max_count = 1000 ;
    public static function virtual_page()
    {
        // Catch request to the virtual page:
        
        if ( isset( $_GET['llama_assistant_call'], $_GET['llama_assistant_plugin'] ) and sanitize_text_field( $_GET['llama_assistant_plugin'] ) == 'llama-redirect' ) {
            // Restrict direct access -> access_token:
            
            if ( !(isset( $_POST['access_token'] ) and !empty($_POST['access_token'])) ) {
                http_response_code( 403 );
                // Forbidden
                die;
            }
            
            // Restrict direct access -> request_id:
            
            if ( !(isset( $_POST['request_id'] ) and !empty($_POST['request_id'])) ) {
                http_response_code( 401 );
                // Unauthorized
                die;
            }
            
            // Restrict direct access -> function
            
            if ( !(isset( $_POST['function'] ) and !empty($_POST['function'])) ) {
                http_response_code( 405 );
                // Method Not Allowed
                die;
            }
            
            // Compare access_token from reqest and users access_token:
            $fx = llama_redirect_PREFIX;
            $user_access_token = get_option( $fx . '_ml_assistant_access_token' );
            $received_access_token = sanitize_text_field( $_POST['access_token'] );
            $ml_assistant_file = llama_redirect_PLUGIN_DIR . 'ml_assistant.php';
            
            if ( file_exists( $ml_assistant_file ) ) {
                include $ml_assistant_file;
            } else {
                http_response_code( 404 );
                // Not found
                die;
            }
            
            die;
        }
    
    }
    
    public static function api_call( $action = array(), $callback_function = '', $sync_data = NULL )
    {
        $llama_redirect_assistant = new llama_redirect_assistant();
        $request = array(
            'status' => 0,
            'data'   => array(),
        );
        // Handle php calls (form submit)
        
        if ( !empty($action) ) {
            $request = $llama_redirect_assistant->request( $action, $sync_data );
            // Execute Callback function if request is successful and callback function exists
            if ( isset( $request['status'] ) and $request['status'] and !empty($callback_function) ) {
                if ( method_exists( $llama_redirect_assistant, $callback_function ) ) {
                    return $llama_redirect_assistant->{$callback_function}( $request );
                }
            }
        }
        
        return $request;
    }
    
    public function status()
    {
        $fx = llama_redirect_PREFIX;
        //1) if ML Assistant enabled:
        $ml_assistant_enabled = get_option( $fx . '_ml_assistant_enabled' );
        $ml_assistant_access_token = get_option( $fx . '_ml_assistant_access_token' );
        if ( $ml_assistant_enabled == '1' and !empty($ml_assistant_access_token) ) {
            return 'working';
        }
        return 'new';
    }
    
    public function plugin_api_request_receiving_file_exists()
    {
        $file_location = plugin_dir_path( __FILE__ ) . '/../ml_assistant.php';
        // Check file location
        if ( file_exists( $file_location ) ) {
            return true;
        }
        return false;
    }
    
    public function compress_data( $data )
    {
        return gzdeflate( json_encode( $data ), 9 );
    }
    
    public function collect_plugin_data( $mode )
    {
        global  $wpdb ;
        $llama_redirect_database = new llama_redirect_database();
        $table = '';
        $id_column = '';
        $table_rows = array();
        $args = array();
        // Prepare SQL request for "404_log" table
        
        if ( $mode == '404_log' ) {
            $id_column = 'log_id';
            $table = $wpdb->prefix . 'llama_404_log';
            $query = "SELECT * FROM `" . $table . "` ORDER BY `" . $id_column . "` DESC LIMIT %d";
            $args = array( $this->rows_max_count );
        } else {
            
            if ( $mode == 'redirect_settings' ) {
                $id_column = 'redirect_id';
                $table = $wpdb->prefix . 'llama_redirect_settings';
                $query = "SELECT * FROM `" . $table . "` ORDER BY `" . $id_column . "` DESC LIMIT %d";
                $args = array( $this->rows_max_count );
            } else {
                
                if ( $mode == 'redirect_stats' ) {
                    $id_column = 'stats_id';
                    $table = $wpdb->prefix . 'llama_redirect_stats';
                    $query = "SELECT * FROM `" . $table . "` ORDER BY `" . $id_column . "` DESC LIMIT %d";
                    $args = array( $this->rows_max_count );
                }
            
            }
        
        }
        
        // If mode is defined -> do the SQL request
        
        if ( !empty($query) ) {
            $table_rows = $llama_redirect_database->read_table( $table, NULL, $wpdb->prepare( $query, $args ) );
            $table_rows = array_slice( $table_rows, 0, $this->rows_max_count );
        }
        
        return $table_rows;
    }
    
    public function request( $action_path = array(), $sync_data = NULL )
    {
        // Init
        $fx = llama_redirect_PREFIX;
        $llama_redirect = new llama_redirect();
        $data = array();
        // Verify data:
        if ( empty($action_path) ) {
            return false;
        }
        // Prepare API CALL:
        $api_call_url = $this->llama_ml_assistant_url . '/' . implode( '/', $action_path ) . '/';
        // Add default params as data to API CALL:
        // Define css styling to use for display:
        $data['plugin_css_prefix'] = llama_redirect_PREFIX;
        // Define language to display content:
        $data['lang'] = get_user_locale();
        // Define date_format to show in user locale:
        $data['date_format'] = get_option( 'date_format' );
        // Define time_format to show in user locale:
        $data['time_format'] = get_option( 'time_format' );
        // Define domain to check if registration is already enabled:
        $data['domain'] = get_site_url();
        // Define domain ssl to check the callback url structure:
        $data['is_ssl'] = is_ssl();
        // Define plugin_wp_id to get suggestions for:
        $data['plugin_key'] = 'llama-redirect';
        // Define license key to avoid violence:
        $data['license_key'] = $llama_redirect->_key();
        // Define access_token:
        $data['access_token'] = get_option( $fx . '_ml_assistant_access_token' );
        // Define admin_url to manage URLS targets in chat.
        $data['admin_url'] = get_admin_url();
        // Define the public location of ml_assistant.php for future incoming HTTP requests
        $data['ml_api_file_location'] = '/?' . http_build_query( array(
            'llama_assistant_call'   => 1,
            'llama_assistant_plugin' => 'llama-redirect',
        ) );
        // Add user First name:
        $wordpress_user_data = get_userdata( get_current_user_id() );
        $data['first_name'] = ( isset( $wordpress_user_data->first_name ) ? $wordpress_user_data->first_name : '' );
        $data['last_name'] = ( isset( $wordpress_user_data->last_name ) ? $wordpress_user_data->last_name : '' );
        // Add user avatar to display in chat
        $data['user_avatar'] = get_avatar_url( get_current_user_id() );
        // Add compressed statistical data if exists ( from sync_request() )
        if ( !empty($sync_data) ) {
            $data['sync'] = $sync_data;
        }
        // DO API CALL:
        $post = wp_remote_post( $api_call_url, array(
            'method'      => "POST",
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(
            'ML_ASSIST_VER' => 1,
        ),
            'body'        => $data,
            'cookies'     => array(),
        ) );
        //has error:
        
        if ( is_wp_error( $post ) ) {
            return array(
                'status' => 0,
                'data'   => array(
                'error' => $post->get_error_message(),
            ),
            );
        } else {
            
            if ( isset( $post['body'] ) and $json = json_decode( $post['body'], 1 ) ) {
                return $json;
            } else {
                echo  $post['body'] ;
                return $post['body'];
            }
        
        }
        
        return false;
    }
    
    private function enable_api_callback( $return_data = array() )
    {
        $status = 0;
        $fx = llama_redirect_PREFIX;
        $request_status = ( (isset( $return_data['status'] ) and $return_data['status']) ? true : false );
        $access_token = ( (isset( $return_data['data']['access_token'] ) and !empty($return_data['data']['access_token'])) ? true : false );
        
        if ( $request_status and $access_token ) {
            //1) enable ML Assistant as option:
            update_option( $fx . '_ml_assistant_enabled', 1 );
            //2) set ML Assistant access_token:
            update_option( $fx . '_ml_assistant_access_token', sanitize_text_field( $return_data['data']['access_token'] ) );
            $status = 1;
        }
        
        return array(
            'status' => $status,
        );
    }
    
    public function update_redirect_by_id( $data_list )
    {
        $llama_redirect_database = new llama_redirect_database();
        $report = array();
        // Loop each task:
        if ( !empty($data_list) ) {
            foreach ( $data_list as $one_update ) {
                // Verify task:
                
                if ( isset( $one_update['redirect_id'], $one_update['key'], $one_update['value'] ) ) {
                    // Prepare call to database class:
                    $database_table = 'llama_redirect_settings';
                    $database_values = array(
                        $one_update['key'] => $one_update['value'],
                    );
                    $database_where = array(
                        '_key' => 'redirect_id',
                        '_val' => $one_update['redirect_id'],
                    );
                    // Make call to database to update:
                    
                    if ( $llama_redirect_database->update( $database_table, $database_values, $database_where ) ) {
                        $report[] = array(
                            'redirect_id' => $one_update['redirect_id'],
                            'status'      => 'update:ok',
                        );
                    } else {
                        $report[] = array(
                            'redirect_id' => $one_update['redirect_id'],
                            'status'      => 'update:fail',
                        );
                    }
                
                }
            
            }
        }
        // Return report:
        return $report;
    }
    
    public function insert_redirect( $data_list )
    {
        $llama_redirect_database = new llama_redirect_database();
        $report = array();
        // Loop each task:
        if ( !empty($data_list) ) {
            foreach ( $data_list as $one_insert ) {
                // Verify task:
                
                if ( isset( $one_insert['link_from'], $one_insert['link_to'], $one_insert['red_code'] ) ) {
                    //Prepare call to database class:
                    $database_table = 'llama_redirect_settings';
                    $database_values = $one_insert;
                    // Make call to database to insert:
                    
                    if ( $new_redirect_id = $llama_redirect_database->insert( $database_table, $database_values ) ) {
                        $report[] = array(
                            'new_redirect_id' => $new_redirect_id,
                            'status'          => 'insert:ok',
                        );
                    } else {
                        $report[] = array(
                            'status' => 'insert:fail',
                        );
                    }
                
                }
            
            }
        }
        // Return report:
        return $report;
    }
    
    public function delete_redirect_by_id( $data_list )
    {
        $llama_redirect_database = new llama_redirect_database();
        $report = array();
        // Loop each task:
        if ( !empty($data_list) ) {
            foreach ( $data_list as $one_delete ) {
                // Verify task:
                
                if ( isset( $one_delete['redirect_id'] ) ) {
                    $database_table = 'llama_redirect_settings';
                    $database_values = array(
                        '_key' => 'redirect_id',
                        '_val' => $one_delete['redirect_id'],
                    );
                    // Make call to database to delete:
                    
                    if ( $llama_redirect_database->delete( $database_table, $database_values ) ) {
                        $report[] = array(
                            'redirect_id' => $one_delete['redirect_id'],
                            'status'      => 'delete:ok',
                        );
                    } else {
                        $report[] = array(
                            'redirect_id' => $one_delete['redirect_id'],
                            'status'      => 'delete:fail',
                        );
                    }
                
                }
            
            }
        }
        // Return report:
        return $report;
    }

}