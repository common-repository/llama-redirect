<?php
defined( 'ABSPATH' ) or exit;

class llama_assistant_sync {
	/**
	 * This function prepares user data for Llama Machine Learning Assistant.
	 * Is executed using remote call from API
	 * The main role is to prepare and compress data
	 */
	public static function sync_request($request_id, $synchronization_mode = 'sync') {
		$llama_redirect_assistant = new llama_redirect_assistant;
		$data = array();
		
		/**
		 * Synchronization mode could be:
		 *  "sync" = regular synchronization request
		 *  "first_sync" = first time only
		 *	"manual_sync" = manual synchronization according to manual request from chat
		 */
		if (!in_array($synchronization_mode,array('sync','first_sync','manual_sync'))) {
			return false;
		}
		
		//0) Bind request id to API:
		$data['request_id'] = $request_id;
		
		//1) Plugin list:
		$data['plugins'] = get_option('active_plugins');
		
		//2) System info:
		// The current WordPress version
		$data['sys_wp_version'] = get_bloginfo( 'version' );
		// The current PHP version
		$data['sys_php_version'] = phpversion();
		// The "Encoding for pages and feeds" (set in Settings > Reading)
		$data['sys_charset'] = get_bloginfo( 'charset' );
		// The content-type (default: "text/html"). 
		$data['sys_html_type'] = get_bloginfo( 'html_type' );
		// Language code for the current site
		$data['language'] = get_bloginfo( 'language' );

		//3) Llama plugin data summary:
		$data['llama_plugin'] = array(
			'plugin_id' => 'llama_redirect',
			'plugin_data' => array(
				// 404 log table contents
				'404_log' => $llama_redirect_assistant->collect_plugin_data('404_log'),
				// Redirect_settings table contents
				'redirect_settings' => $llama_redirect_assistant->collect_plugin_data('redirect_settings'),
				// Redirect_stats
				'redirect_stats' => $llama_redirect_assistant->collect_plugin_data('redirect_stats'),
			)
		);
		
		//4) Compress
		$compressed = $llama_redirect_assistant->compress_data($data);
		
		//5) Return the results of API call:
		return $llama_redirect_assistant->api_call(array($synchronization_mode, 'llama_redirect'), NULL, $compressed);
	}
	/**
	 * This function receives action list from Llama Machine Learning Assistant.
	 * Each action is validated and executed.
	 * Here is not required to check access token again, because function is called from ml_assistant.php, which already did this.
	 */
	public static function manage_clients_functions($request_id, $actions ) {
		
		$llama_redirect_assistant = new llama_redirect_assistant;
		$report = array(
			'status' => '',
			'actions' => array()
		);
		$success_exec_count = 0;
		
		if ( !empty( $actions ) ) {
			foreach($actions as $one_action) {
				// Verify action to contain all the required fields:
				if ( isset($one_action['id'], $one_action['request_type'], $one_action['function'], $one_action['data'] ) ) {
					
					$action_function = $one_action['function'];
					$action_id = $one_action['id'];
					$action_data = $one_action['data'];
					
					// If this action is "update":
					if ( $one_action['request_type'] == 'update' and !empty( $action_data ) ) {
						
						// Check again, if all is correct -> if required function for this action exists in this class:
						if ( method_exists( $llama_redirect_assistant, $action_function ) ) {
							// Execute function
							$exec_report = $llama_redirect_assistant->$action_function($action_data);
							// Add to report
							$report['actions'][$action_id] = $exec_report;
							// Increment success_exec_count
							$success_exec_count++;
						}
						else {
							// Report "method_not_exists"
							$report['actions'][$action_id] = array('status' => 'method_not_exists');	
						}
						
					}
					// If this action is "insert"
					else if ( $one_action['request_type'] == 'insert' and !empty( $action_data ) ) {
						
						// Check again, if all is correct -> if required function for this action exists in this class:
						if ( method_exists( $llama_redirect_assistant, $action_function ) ) {
							// Execute function:
							$exec_report = $llama_redirect_assistant->$action_function($action_data);
							// Add to report
							$report['actions'][$action_id] = $exec_report;
							// Increment success_exec_count
							$success_exec_count++;
						}
						else {
							// Report "method_not_exists"
							$report['actions'][$action_id] = array('status' => 'method_not_exists');	
						}

					}
					// If this action is "delete"
					else if ( $one_action['request_type'] == 'delete' and !empty( $action_data ) ) {
						
						// Check again, if all is correct -> if required function for this action exists in this class:
						if ( method_exists( $llama_redirect_assistant, $action_function ) ) {
							// Execute function:
							$exec_report = $llama_redirect_assistant->$action_function($action_data);
							// Add to report:
							$report['actions'][$action_id] = $exec_report;
							// Increment success_exec_count
							$success_exec_count++;
						}
						else {
							// Report "method_not_exists"
							$report['actions'][$action_id] = array('status' => 'method_not_exists');	
						}
						
					}
					// Report "request_unknown"
					else {
						$report['actions'][$action_id] = array('status' => 'request_unknown');
					}
					
				}
			}
			// Report status:
			if ($success_exec_count == count($actions)) {
				$report['status'] = 'ok';
			}
			else {
				$report['status'] = 'semi_ok';
			}
		}
		else {
			// Report "empty_actions"
			$report['status'] = "empty_actions";	
		}
		return $report;
	}
}