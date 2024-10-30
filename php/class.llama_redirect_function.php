<?php

defined( 'ABSPATH' ) or exit;
class llama_redirect_function
{
    public static function llama_redirect_action()
    {
        //prevent from admin pages:
        if ( is_admin() ) {
            return;
        }
        //1) init:
        $llama_redirect_function = new llama_redirect_function();
        $current_url = home_url() . add_query_arg( NULL, NULL );
        $redirect_rule = $llama_redirect_function->redirect_rule( $current_url );
        
        if ( $redirect_rule and $llama_redirect_function->redirect_match( $redirect_rule, $current_url ) ) {
            //3) add to stats
            if ( $redirect_rule['track'] ) {
                $llama_redirect_function->track_redirect( $redirect_rule['redirect_id'] );
            }
            //4) redirect:
            wp_redirect( $redirect_rule['link_to'], intval( $redirect_rule['red_code'] ) );
            exit;
        }
    
    }
    
    public function path_only( $url )
    {
        $path = array();
        $parse_url = parse_url( $url );
        
        if ( isset( $parse_url['scheme'] ) ) {
            $path[] = $parse_url['scheme'] . '://';
        } else {
            $path[] = 'http://';
        }
        
        if ( isset( $parse_url['host'] ) ) {
            $path[] = $parse_url['host'];
        }
        if ( isset( $parse_url['path'] ) ) {
            $path[] = $parse_url['path'];
        }
        return implode( '', $path );
    }
    
    public function redirect_match( $redirect, $current_url )
    {
        //0) init:
        $go = true;
        $llama_visitor_details = new llama_visitor_details();
        $visitor_data = $llama_visitor_details->visitor_data();
        //1) browser
        if ( isset( $redirect['browser'] ) and !empty($redirect['browser']) ) {
            if ( !in_array( $visitor_data->browser, $redirect['browser'] ) ) {
                return false;
            }
        }
        //2) os:
        if ( isset( $redirect['os'] ) and !empty($redirect['os']) ) {
            if ( !in_array( $visitor_data->os, $redirect['os'] ) ) {
                return false;
            }
        }
        //3) referer
        if ( isset( $redirect['referer_domain'] ) and !empty($redirect['referer_domain']) ) {
            if ( !(isset( $_SERVER['HTTP_REFERER'] ) and $_SERVER['HTTP_REFERER'] == $redirect['referer_domain']) ) {
                return false;
            }
        }
        return $go;
    }
    
    public static function llama_redirect_404()
    {
        //0) init:
        global  $wp ;
        $fx = llama_redirect_PREFIX;
        $llama_redirect_function = new llama_redirect_function();
        $current_url = home_url( add_query_arg( array(), $wp->request ) );
        //1) if 404 and setting enabled -> logging
        if ( is_404() and get_option( $fx . '_enable_404_log' ) == '1' ) {
            //log:
            $llama_redirect_function->track_404( $current_url );
        }
    }
    
    private function redirect_rule( $current_url )
    {
        global  $wpdb ;
        $current_url = $this->path_only( $current_url );
        $table = $wpdb->prefix . 'llama_redirect_settings';
        $query = $wpdb->prepare( "SELECT * FROM {$table} WHERE `link_from` = %s AND `is_active` = %d", array( $current_url, 1 ) );
        $rule = $wpdb->get_row( $query, ARRAY_A );
        //convert lists to arrays:
        if ( isset( $rule['browser'] ) ) {
            $rule['browser'] = array_filter( explode( ',', $rule['browser'] ) );
        }
        if ( isset( $rule['os'] ) ) {
            $rule['os'] = array_filter( explode( ',', $rule['os'] ) );
        }
        if ( isset( $rule['wp_role'] ) ) {
            $rule['wp_role'] = array_filter( explode( ',', $rule['wp_role'] ) );
        }
        if ( isset( $rule['day'] ) ) {
            $rule['day'] = array_filter( explode( ',', $rule['day'] ) );
        }
        if ( isset( $rule['month'] ) ) {
            $rule['month'] = array_filter( explode( ',', $rule['month'] ) );
        }
        if ( isset( $rule['year'] ) ) {
            $rule['year'] = array_filter( explode( ',', $rule['year'] ) );
        }
        //return:
        return $rule;
    }
    
    private function track_redirect( $redirect_id )
    {
        global  $wpdb ;
        $llama_visitor_details = new llama_visitor_details();
        $llama_redirect_database = new llama_redirect_database();
        $visitor_data = $llama_visitor_details->visitor_data();
        $stats = array(
            'redirect_id'    => $redirect_id,
            'client_ip'      => $visitor_data->ip,
            'client_browser' => $visitor_data->browser,
            'client_os'      => $visitor_data->os,
            'client_hash'    => $visitor_data->user_agent_hash,
        );
        $llama_redirect_database->insert( 'llama_redirect_stats', $stats );
    }
    
    private function track_404( $current_url )
    {
        global  $wpdb ;
        $llama_redirect_database = new llama_redirect_database();
        $stats = array(
            'page_url' => $current_url,
        );
        $llama_redirect_database->insert( 'llama_404_log', $stats );
    }
    
    public function get_key_val_match( $url, $get_key = '', $get_val = '' )
    {
        $match = false;
        $parse_url = parse_url( $url );
        
        if ( isset( $parse_url['query'] ) ) {
            parse_str( $parse_url['query'], $kv );
            //both:
            
            if ( !empty($get_key) and !empty($get_val) ) {
                if ( isset( $kv[$get_key] ) and $kv[$get_key] == $get_val ) {
                    $match = true;
                }
            } else {
                
                if ( !empty($get_val) and !empty($kv) ) {
                    foreach ( $kv as $k => $v ) {
                        if ( $v == $get_val ) {
                            $match = true;
                        }
                    }
                } else {
                    if ( !empty($get_key) ) {
                        if ( isset( $kv[$get_key] ) ) {
                            $match = true;
                        }
                    }
                }
            
            }
        
        }
        
        return $match;
    }
    
    public function is_ip_in_range( $ip_from, $ip_to, $visitor_ip )
    {
        $min = ip2long( $ip_from );
        $max = ip2long( $ip_to );
        $visitor = ip2long( $visitor_ip );
        return $visitor >= $min and $visitor <= $max;
    }

}