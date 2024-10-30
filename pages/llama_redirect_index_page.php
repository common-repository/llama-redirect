<?php

defined( 'ABSPATH' ) or exit;
class llama_redirect_index_page
{
    function __construct()
    {
        //init ajax update
        add_action( 'wp_ajax_js_llama_redirect_update', array( $this, 'js_llama_redirect_update' ) );
        add_action( 'wp_ajax_nopriv_js_llama_redirect_update', array( $this, 'js_llama_redirect_update' ) );
        //init ajax delete
        add_action( 'wp_ajax_js_llama_redirect_delete', array( $this, 'js_llama_redirect_delete' ) );
        add_action( 'wp_ajax_nopriv_js_llama_redirect_delete', array( $this, 'js_llama_redirect_delete' ) );
    }
    
    public function js_llama_redirect_delete()
    {
        
        if ( $this->delete_redirect_by_id() ) {
            echo  json_encode( array(
                'status' => 1,
                'toast'  => __( 'Removed', 'llama-redirect' ) . '!',
            ) ) ;
        } else {
            echo  json_encode( array(
                'status' => 0,
                'toast'  => __( 'Error', 'llama-redirect' ) . '!',
            ) ) ;
        }
        
        wp_die();
    }
    
    private function delete_redirect_by_id()
    {
        //1) verify data
        if ( isset( $_POST['nonce'] ) and isset( $_POST['id'] ) and wp_verify_nonce( $_POST['nonce'], 'remove_url_' . $_POST['id'] ) ) {
            
            if ( isset( $_POST['_val'] ) and !empty($_POST['_val']) and isset( $_POST['_key'] ) and !empty($_POST['_key']) ) {
                $database = new llama_redirect_database();
                $status = $database->delete( 'llama_redirect_settings', array(
                    '_key' => sanitize_text_field( $_POST['_key'] ),
                    '_val' => sanitize_text_field( $_POST['_val'] ),
                ) );
                if ( $status ) {
                    $status = $database->delete( 'llama_redirect_stats', array(
                        '_key' => 'redirect_id',
                        '_val' => sanitize_text_field( $_POST['_val'] ),
                    ) );
                }
                return $status;
            }
        
        }
        return false;
    }
    
    public function js_llama_redirect_update()
    {
        
        if ( $this->update_redirect_by_id() ) {
            echo  json_encode( array(
                'status' => 1,
                'toast'  => __( 'Updated', 'llama-redirect' ) . '!',
            ) ) ;
        } else {
            echo  json_encode( array(
                'status' => 0,
                'toast'  => __( 'Update error', 'llama-redirect' ) . '!',
            ) ) ;
        }
        
        wp_die();
    }
    
    private function update_redirect_by_id()
    {
        //1) verify data:
        $verified_fields = array();
        $verified_update = array();
        $verify_total = 4;
        
        if ( isset( $_POST['nonce'] ) and isset( $_POST['id'] ) and wp_verify_nonce( $_POST['nonce'], 'update_url_' . $_POST['id'] ) ) {
            //1.1) link_from
            if ( isset( $_POST['link_from'] ) and !empty($_POST['link_from']) ) {
                $verified_fields['link_from'] = esc_url_raw( $_POST['link_from'] );
            }
            //1.2) link_to
            if ( isset( $_POST['link_to'] ) and !empty($_POST['link_to']) ) {
                $verified_fields['link_to'] = esc_url_raw( $_POST['link_to'] );
            }
            //1.3) red_code
            if ( isset( $_POST['red_code'] ) and !empty($_POST['red_code']) ) {
                $verified_fields['red_code'] = sanitize_text_field( $_POST['red_code'] );
            }
            //1.4) track
            if ( isset( $_POST['track'] ) ) {
                $verified_fields['track'] = sanitize_text_field( $_POST['track'] );
            }
            //1.5) id [= _key + _val]
            
            if ( isset( $_POST['_val'] ) and !empty($_POST['_val']) and isset( $_POST['_key'] ) and !empty($_POST['_val']) ) {
                $verified_update['_key'] = sanitize_text_field( $_POST['_key'] );
                $verified_update['_val'] = sanitize_text_field( $_POST['_val'] );
            }
            
            
            if ( count( $verified_fields ) == $verify_total and count( $verified_update ) == 2 ) {
                $database = new llama_redirect_database();
                $status = $database->update( 'llama_redirect_settings', $verified_fields, $verified_update );
                return $status;
            }
        
        }
        
        return false;
    }
    
    private function stats_count_by_redirect_id( $redirect_id )
    {
        global  $wpdb ;
        $table = $wpdb->prefix . "llama_redirect_stats";
        $query = $wpdb->prepare( "SELECT COUNT(*) AS 'total' FROM {$table} WHERE `redirect_id` = %d", array( $redirect_id ) );
        $count = $wpdb->get_col( $query );
        if ( isset( $count[0] ) ) {
            return intval( $count[0] );
        }
        return 0;
    }
    
    public static function show_page()
    {
        global  $llama_redirect_debugger ;
        $fx = llama_redirect_PREFIX;
        $elements = new llama_redirect_elements();
        $database = new llama_redirect_database();
        $llama_redirect_index_page = new llama_redirect_index_page();
        $interface = new llama_redirect_interface();
        $my_links = $database->read_table( 'llama_redirect_settings' );
        //add link button:
        $add_link_button = '<a href="' . esc_url( admin_url( '/admin.php?page=llama-redirect_add' ) ) . '" class="button button-primary button-large">
				<span>' . __( 'Add', 'llama-redirect' ) . '</span>
			</a>';
        //SHOW PAGE:
        echo  $elements->nav( $fx ) ;
        echo  $elements->title( $fx, __( 'My links', 'llama-redirect' ), $add_link_button ) ;
        echo  '<div class="' . $fx . '-container">
			<div class="' . $fx . '-card">' ;
        //SHOW STATS:
        if ( count( $my_links ) > 0 ) {
            echo  '
					<div class="' . $fx . '-card-content">
						<div class="' . $fx . '-right-align">
							<p class="' . $fx . '-filter-container">
								<label class="screen-reader-text" for="quiz_search">' . __( 'Search', 'llama-redirect' ) . '</label>
								<input type="search" id="llama_redirect_filter" name="llama_redirect_filter" value=""/>
								<a href="#" class="button js-llama_redirect_filter">' . __( 'Filter', 'llama-redirect' ) . '</a>
							</p>
							<div>' . sprintf( __( '%s links total', 'llama-redirect' ), count( $my_links ) ) . '</div>
						</div>
					</div>' ;
        }
        echo  '
				<div class="' . $fx . '-card-content ' . $fx . '-no-padding-top">
					<table class="striped">
						<thead>
							<tr>
								<th>' . __( 'Link From', 'llama-redirect' ) . '</th>
								<th>' . __( 'Link Target', 'llama-redirect' ) . '</th>
								<th>' . __( 'Code', 'llama-redirect' ) . '</th>
								<th>' . __( 'Tracking', 'llama-redirect' ) . '</th>
								<th>' . __( 'Active', 'llama-redirect' ) . '</th>
								<th></th>
							</tr>
						</thead>
						<tbody>' ;
        
        if ( !empty($my_links) and is_array( $my_links ) ) {
            foreach ( $my_links as $my_link_idx => $my_link_data ) {
                echo  '<tr data-id="' . esc_attr( $my_link_data['redirect_id'] ) . '" data-filter="' . esc_attr( strtolower( $my_link_data['link_from'] ) . ' ' . strtolower( $my_link_data['link_to'] ) ) . '">
										<td>' . $my_link_data['link_from'] . '</td>
										<td>' . $my_link_data['link_to'] . '</td>
										<td>' . $my_link_data['red_code'] . '</td>
										<td>' . (( $my_link_data['track'] ? __( 'Yes', 'llama-redirect' ) : __( 'No', 'llama-redirect' ) )) . '</td>
										<td>' . (( $my_link_data['is_active'] ? __( 'Yes', 'llama-redirect' ) : __( 'No', 'llama-redirect' ) )) . '</td>
										<td>' ;
                //edit link button
                echo  '
											<a
												href="' . esc_url( admin_url( '/admin.php?page=llama-redirect_add&edit=' . $my_link_data['redirect_id'] ) ) . '"
												class="button button-primary">
													<span>' . __( 'Edit', 'llama-redirect' ) . '</span>
											</a>' ;
                //delete link button
                echo  '
											<a 
												href="#!"
												class="button button-link-delete js-llama_redirect_delete"
												data-id="' . esc_attr( $my_link_data['redirect_id'] ) . '"
												data-nonce="' . esc_attr( wp_create_nonce( 'remove_url_' . $my_link_data['redirect_id'] ) ) . '">
													<span>' . __( 'Delete', 'llama-redirect' ) . '</span>
											</a>' ;
                //stats button
                $this_link_stats_count = $llama_redirect_index_page->stats_count_by_redirect_id( $my_link_data['redirect_id'] );
                echo  '
											<a
												href="' . esc_url( '?page=llama-redirect_stats&redirect_id=' . $my_link_data['redirect_id'] ) . '"
												class="button ' . $fx . '-btn-dashicon" data-dashicon="dashicons-chart-bar">
													<span>' . sprintf( __( 'Stats (%s)', 'llama-redirect' ), $this_link_stats_count ) . '</span>
											</a>' ;
                echo  '
										</td>
									</tr>' ;
            }
        } else {
            echo  '<tr>
									<td colspan="5">
										<div class="' . $fx . '-center">
											<h3 class="' . $fx . '-card-title">' . __( 'You dont have any redirects now', 'llama-redirect' ) . '</h3>
											<a href="' . esc_url( admin_url( '/admin.php?page=llama-redirect_add' ) ) . '" class="' . $fx . '-card-title">' . __( 'Click here to add new', 'llama-redirect' ) . '</a>
										</div>
									</td>
								</tr>' ;
        }
        
        echo  '
						</tbody>
						<tfoot>
							<tr>
								<td colspan="5"></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>' ;
        $llama_redirect_debugger->page_loaded( 'llama_redirect_index_page' );
    }

}
$llama_redirect_index_page = new llama_redirect_index_page();