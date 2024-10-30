<?php
defined( 'ABSPATH' ) or exit;

class llama_redirect_stats_page {
	
	private function stats_sql_query($use_redirects_id = 0) {
		global $wpdb;
		$query = "SELECT * FROM `".$wpdb->prefix."llama_redirect_stats` 
		JOIN `".$wpdb->prefix."llama_redirect_settings` ON `".$wpdb->prefix."llama_redirect_stats`.`redirect_id` = `".$wpdb->prefix."llama_redirect_settings`.`redirect_id`";
		if ($use_redirects_id) {
			$query .= " WHERE `".$wpdb->prefix."llama_redirect_settings`.`redirect_id` = '".$use_redirects_id."' ";	
		}
		$query .= "
		ORDER BY stats_id";	
		return $query;
	}
	
	public static function show_page() {
		global $llama_redirect_debugger;
		$fx = llama_redirect_PREFIX;
		$elements = new llama_redirect_elements;
		$database = new llama_redirect_database;
		$llama_redirect_stats_page = new llama_redirect_stats_page;
		$interface = new llama_redirect_interface;
		$title = __( 'Statistics', 'llama-redirect' );
		$clear_log_button = $llama_redirect_stats_page->clear_log_button();
		if (isset($_GET['redirect_id']) and !empty($_GET['redirect_id'])) {
			$title .= ' ( ID : '.sanitize_text_field($_GET['redirect_id']).' )';	
			$clear_log_button = $llama_redirect_stats_page->clear_log_button(sanitize_text_field($_GET['redirect_id']));
		}
		
		//handle [clear_log_all]:
		if ( isset( $_POST["clear_log_all_nonce"] ) and wp_verify_nonce( $_POST['clear_log_all_nonce'], 'clear_log_all') ) {
			if ( $database->truncate( 'llama_redirect_stats' ) ) {
				echo $elements->notice( 'success', __('Log cleared!', 'llama-redirect' ) );
			}
			else {
				echo $elements->notice( 'error', __('Log not cleared, an error occured!', 'llama-redirect' ) );
			}
		}
		//handle [clear_log one item]:
		if (isset($_GET['redirect_id']) and !empty($_GET['redirect_id'])) {
			if ( isset( $_POST['clear_log_' . $_GET['redirect_id'] . '_nonce'] ) && wp_verify_nonce( $_POST['clear_log_' . $_GET['redirect_id'] . '_nonce'], 'clear_log_' . $_GET['redirect_id']) ) {
				if ( $database->delete( 'llama_redirect_stats', array( '_key' => 'redirect_id', '_val' => sanitize_text_field($_GET['redirect_id']) ) ) ) {
					echo $elements->notice( 'success', __('Log cleared!', 'llama-redirect' ) );
				}
				else {
					echo $elements->notice( 'error', __('Log not cleared, an error occured!', 'llama-redirect' ) );
				}
			}	
		}
		
		// Get stats records from database:
		if (isset($_GET['redirect_id']) and !empty($_GET['redirect_id'])) {
			$redirect_stats = $database->read_table('llama_redirect_stats',array(),$llama_redirect_stats_page->stats_sql_query(sanitize_text_field($_GET['redirect_id'])));
		}
		else {
			$redirect_stats = $database->read_table('llama_redirect_stats',array(),$llama_redirect_stats_page->stats_sql_query());
		}

		echo $elements->nav( $fx );
		echo $elements->title( $fx, $title, $clear_log_button );
		
		//show selected redrect settings:
		if (isset($_GET['redirect_id']) and !empty($_GET['redirect_id'])) {
			echo $llama_redirect_stats_page->selected_redirect_settings(sanitize_text_field($_GET['redirect_id']));
		}
		
		echo 
		'<div class="'.$fx.'-container">
			<div class="'.$fx.'-card">
				<div class="'.$fx.'-card-content">
					<table class="striped js-init-data-table" id="llama_redirect_stats_table">
						<thead>
							<tr>
								<th>' . __( 'Link from', 'llama-redirect' ) . '</th>
								<th>' . __( 'Link to', 'llama-redirect' ) . '</th>
								<th>' . __( 'Code', 'llama-redirect' ) . '</th>
								<th>' . __( 'IP', 'llama-redirect' ) . '</th>
								<th>' . __( 'Browser', 'llama-redirect' ) . '</th>
								<th>' . __( 'OS', 'llama-redirect' ) . '</th>
								<th>' . __( 'Date', 'llama-redirect' ) . '</th>
							</tr>
						</thead>
						<tbody>';
						
						if (!empty($redirect_stats) and is_array($redirect_stats)) {
							foreach($redirect_stats as $stats_idx => $stats_data) {
								echo 
								'<tr>
									<td>' . $stats_data['link_from'] . '</td>
									<td>' . $stats_data['link_to'] . '</td>
									<td>' . $stats_data['red_code'] . '</td>
									<td>' . $stats_data['client_ip'] . '</td>
									<td>' . $stats_data['client_browser'] . '</td>
									<td>' . $stats_data['client_os'] . '</td>
									<td>' . $interface->convert_date_to_user_format( $stats_data['action_date'] )->date_time . '</td>
								</tr>';
							}
						}
						
						echo '
						</tbody>
					</table>
				</div>
			</div>
		</div>';
		$llama_redirect_debugger->page_loaded('llama_redirect_stats_page');
	}
	public function clear_log_button($redirect_id = '') {
		$html = '
		<form action="" method="post">';
			#add nonce field:
			if (!empty($redirect_id)) {
				$html .= wp_nonce_field( 'clear_log_' . $redirect_id, 'clear_log_' . $redirect_id . '_nonce' );
			}
			else {
				$html .= wp_nonce_field( 'clear_log_all', 'clear_log_all_nonce' );
			}
			#add button:
			$html .= '
			<button type="submit" class="button button-link-delete">';
				if (!empty($redirect_id)) {
					$html .= '<span>' . __( 'Clear log for this redirect rule', 'llama-redirect' ) . '</span>';	
				}
				else {
					$html .= '<span>' . __( 'Clear log', 'llama-redirect' ) . '</span>';	
				}
			$html .= '
			</button>
		</form>';			
		return $html;
	}
	private function selected_redirect_settings($redirect_id) {
		//init:
		$fx = llama_redirect_PREFIX;
		$database = new llama_redirect_database;
		$selected_redirect_data = $database->read_row('llama_redirect_settings',array('_key' => 'redirect_id', '_val' => $redirect_id));
		$html = '';
		if (!empty($selected_redirect_data)) {
			$html .=
			'<div class="'.$fx.'-container">
				<div class="'.$fx.'-card">
					<div class="'.$fx.'-card-content">
						<div class="'.$fx.'-row">
							<div class="'.$fx.'-col '.$fx.'-s6">
								<table>
									<tbody>
										<tr>
											<th>' . __( 'Link from', 'llama-redirect' ) . '</th>
											<td>'.$selected_redirect_data['link_from'].'</td>
										</tr>
										<tr>
											<th>' . __( 'Link to', 'llama-redirect' ) . '</th>
											<td>'.$selected_redirect_data['link_to'].'</td>
										</tr>
										<tr>
											<th>' . __( 'Response code', 'llama-redirect' ) . '</th>
											<td>'.$selected_redirect_data['red_code'].'</td>
										</tr>
										<tr>
											<th>' . __( 'Edit', 'llama-redirect' ). '</th>
											<td>
												<a href="'.esc_url(admin_url('/admin.php?page=llama-redirect_add&edit=' . $redirect_id)).'" class="button button-primary">
													<span>' . __( 'Edit', 'llama-redirect' ) . '</span>
												</a>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="'.$fx.'-mb-1"></div>';
		}		
		return $html;
	}
}