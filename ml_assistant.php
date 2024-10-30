<?php

defined( 'ABSPATH' ) or exit;
/*
	TODO:
		1) change request URL structure in ML
		2) test connection
		3) clear ML database
*/
/**
 * Handle External scheduled call from API
 * In this case traditional WP Cron is not suitable, because it requires visitors to access a website to work
 * This is incoming request receiving script
*/
// 1) Call API sync only if user access_token is the same like is received from API:

if ( isset(
    $user_access_token,
    $received_access_token,
    $_POST['function'],
    $_POST['request_id']
) and $user_access_token == $received_access_token ) {
    // 2) Define the function, which API is requesting in this call:
    $api_function = sanitize_text_field( $_POST['function'] );
    $request_id = sanitize_text_field( $_POST['request_id'] );
    // Check if Assistant is enabled:
    
    if ( !get_option( $fx . '_ml_assistant_enabled' ) ) {
        status_header( 202 );
        // Accepted
        echo  'ml_assistant_not_enabled' ;
        die;
    }
    
    // 3.1) Function is synchronization -> send compressed data back to API:
    if ( in_array( $api_function, array( 'sync', 'first_sync', 'manual_sync' ) ) ) {
        // Exec sync:
        
        if ( llama_assistant_sync::sync_request( $request_id, $api_function ) ) {
            status_header( 200 );
            // OK
            echo  'ok' ;
            die;
        } else {
            status_header( 409 );
            // Conflict
            echo  'error' ;
            die;
        }
    
    }
}

status_header( 403 );
// Forbidden