<?php

defined( 'ABSPATH' ) or exit;
class llama_redirect_404_page
{
    private function stats_sql_query()
    {
        global  $wpdb ;
        $query = "SELECT * FROM `" . $wpdb->prefix . "llama_404_log` ORDER BY `date_created` DESC";
        return $query;
    }
    
    public static function show_page()
    {
        global  $llama_redirect_debugger ;
        $fx = llama_redirect_PREFIX;
        $elements = new llama_redirect_elements();
        $database = new llama_redirect_database();
        $interface = new llama_redirect_interface();
        $llama_redirect_404_page = new llama_redirect_404_page();
        //handle [clear_log]:
        
        if ( isset( $_POST["clear_404_log_nonce"] ) and wp_verify_nonce( $_POST['clear_404_log_nonce'], 'clear_404_log' ) ) {
            $database = new llama_redirect_database();
            
            if ( $database->truncate( 'llama_404_log' ) ) {
                echo  $elements->notice( 'success', __( '404 log cleared!', 'llama-redirect' ) ) ;
            } else {
                echo  $elements->notice( 'error', __( 'Log not cleared, an error occured!', 'llama-redirect' ) ) ;
            }
        
        }
        
        //read log records:
        $_404_stats = $database->read_table( 'llama_404_log', array(), $llama_redirect_404_page->stats_sql_query() );
        $clear_log_button = $llama_redirect_404_page->clear_log_button();
        //SHOW NAV and TITLE:
        echo  $elements->nav( $fx ) ;
        echo  $elements->title( $fx, __( '404 log', 'llama-redirect' ), $clear_log_button ) ;
        //SHOW PAGE:
        echo  '<div class="' . $fx . '-container">
			<div class="' . $fx . '-card">
				<div class="' . $fx . '-card-content">
					<table class="striped js-init-data-table" id="llama_redirect_stats_table">
						<thead>
							<tr>
								<th>' . __( 'URL', 'llama-redirect' ) . '</th>
								<th>' . __( 'Date', 'llama-redirect' ) . '</th>
							</tr>
						</thead>
						<tbody>' ;
        if ( !empty($_404_stats) and is_array( $_404_stats ) ) {
            foreach ( $_404_stats as $stats_idx => $stats_data ) {
                $extra_html = '';
                echo  '<tr>
									<td>' . $stats_data['page_url'] . '</td>
									<td>' . $interface->convert_date_to_user_format( $stats_data['date_created'] )->date_time . $extra_html . '</td>
								</tr>' ;
            }
        }
        echo  '
						</tbody>
					</table>
				</div>
			</div>
		</div>' ;
        $llama_redirect_debugger->page_loaded( 'llama_redirect_404_page' );
    }
    
    public function clear_log_button()
    {
        $html = '
		<form action="" method="post">';
        #add nonce field:
        $html .= wp_nonce_field( 'clear_404_log', 'clear_404_log_nonce' );
        #add button:
        $html .= '
			<button type="submit" class="button button-link-delete">
				<span>' . __( 'Clear log', 'llama-redirect' ) . '</span>
			</button>
		</form>';
        return $html;
    }

}