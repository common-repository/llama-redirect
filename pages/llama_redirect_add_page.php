<?php

defined( 'ABSPATH' ) or exit;
class llama_redirect_add_page
{
    function __construct()
    {
    }
    
    public function url_to_regex_pattern( $url )
    {
        $parse_url = parse_url( $url );
        
        if ( isset( $parse_url['host'] ) ) {
            $pattern = addslashes( $parse_url['host'] );
            return '.*' . $pattern . '.*';
        }
        
        return false;
    }
    
    public function input_fields()
    {
        $fx = llama_redirect_PREFIX;
        $llama_visitor_details = new llama_visitor_details();
        $fields = array();
        //--options
        $fields[] = array(
            'id'    => '_options',
            'label' => __( 'MAIN SETTINGS', 'llama-redirect' ),
        );
        //1) server code:
        $fields[] = array(
            'id'                 => 'red_code',
            'label'              => __( 'Response code', 'llama-redirect' ),
            'helper'             => __( 'Specify server response code which will be executed', 'llama-redirect' ),
            'input_type'         => 'select',
            'attr'               => array(
            'required' => 'required',
        ),
            '_acceptable_values' => array(
            301,
            302,
            300,
            303,
            304,
            305,
            307,
            308
        ),
            'values'             => array( array(
            'group_title' => __( 'Most popular', 'llama-redirect' ),
            'list'        => array( array(
            'value'  => 301,
            'title'  => __( '301 Moved Permanently', 'llama-redirect' ),
            'helper' => __( 'This and all future requests should be directed to the given URI', 'llama-redirect' ),
        ), array(
            'value'  => 302,
            'title'  => __( '302 Found (Previously "Moved temporarily")', 'llama-redirect' ),
            'helper' => __( 'Tells the client to look at (browse to) another url. 302 has been superseded by 303 and 307. This is an example of industry practice contradicting the standard. The HTTP/1.0 specification (RFC 1945) required the client to perform a temporary redirect (the original describing phrase was "Moved Temporarily"), but popular browsers implemented 302 with the functionality of a 303 See Other. Therefore, HTTP/1.1 added status codes 303 and 307 to distinguish between the two behaviours. However, some Web applications and frameworks use the 302 status code as if it were the 303.', 'llama-redirect' ),
        ) ),
        ), array(
            'group_title' => __( 'Others', 'llama-redirect' ),
            'list'        => array(
            array(
            'value'  => 300,
            'title'  => __( '300 Multiple Choices', 'llama-redirect' ),
            'helper' => __( 'Indicates multiple options for the resource from which the client may choose (via agent-driven content negotiation). For example, this code could be used to present multiple video format options, to list files with different filename extensions, or to suggest word-sense disambiguation.', 'llama-redirect' ),
        ),
            array(
            'value'  => 303,
            'title'  => __( '303 See Other', 'llama-redirect' ),
            'helper' => __( 'The response to the request can be found under another URI using the GET method. When received in response to a POST (or PUT/DELETE), the client should presume that the server has received the data and should issue a new GET request to the given URI.', 'llama-redirect' ),
        ),
            array(
            'value'  => 304,
            'title'  => __( '304 Not Modified', 'llama-redirect' ),
            'helper' => __( 'Indicates that the resource has not been modified since the version specified by the request headers If-Modified-Since or If-None-Match. In such case, there is no need to retransmit the resource since the client still has a previously-downloaded copy.', 'llama-redirect' ),
        ),
            array(
            'value'  => 305,
            'title'  => __( '305 Use Proxy', 'llama-redirect' ),
            'helper' => __( 'The requested resource is available only through a proxy, the address for which is provided in the response. Many HTTP clients (such as Mozilla[27] and Internet Explorer) do not correctly handle responses with this status code, primarily for security reasons', 'llama-redirect' ),
        ),
            array(
            'value'  => 307,
            'title'  => __( '307 Temporary Redirect', 'llama-redirect' ),
            'helper' => __( 'In this case, the request should be repeated with another URI; however, future requests should still use the original URI. In contrast to how 302 was historically implemented, the request method is not allowed to be changed when reissuing the original request. For example, a POST request should be repeated using another POST request.', 'llama-redirect' ),
        ),
            array(
            'value'  => 308,
            'title'  => __( '308 Permanent Redirect', 'llama-redirect' ),
            'helper' => __( 'The request and all future requests should be repeated using another URI. 307 and 308 parallel the behaviors of 302 and 301, but do not allow the HTTP method to change. So, for example, submitting a form to a permanently redirected resource may continue smoothly.', 'llama-redirect' ),
        )
        ),
        ) ),
        );
        //link from
        $fields[] = array(
            'id'         => 'link_from',
            'label'      => __( 'Link from', 'llama-redirect' ),
            'helper'     => __( 'Type URL to catch redirects from. Example: /redirect-from-this-link/', 'llama-redirect' ),
            'input_type' => 'url',
            'attr'       => array(
            'required'    => 'required',
            'pattern'     => $this->url_to_regex_pattern( get_home_url() ),
            'placeholder' => get_home_url(),
        ),
        );
        //link to
        $fields[] = array(
            'id'         => 'link_to',
            'label'      => __( 'Link target', 'llama-redirect' ),
            'helper'     => sprintf( __( 'Type full URL to target redirect visitor. Example: %s/some/other/link', 'llama-redirect' ), get_site_url() ),
            'input_type' => 'url',
            'attr'       => array(
            'required' => 'required',
        ),
        );
        //track
        $fields[] = array(
            'id'                 => 'track',
            'label'              => __( 'Track link?', 'llama-redirect' ),
            'helper'             => __( 'If checked, clicks will be recorded into Statistics tab', 'llama-redirect' ),
            'input_type'         => 'select',
            '_acceptable_values' => array( 1, 0 ),
            'values'             => array( array(
            'group_title' => __( 'Tracking enabled?', 'llama-redirect' ),
            'list'        => array( array(
            'value' => 1,
            'title' => __( 'Yes', 'llama-redirect' ),
        ), array(
            'value' => 0,
            'title' => __( 'No', 'llama-redirect' ),
        ) ),
        ) ),
        );
        //--options
        $fields[] = array(
            'id'    => '_options',
            'label' => __( 'REDIRECTION RULES', 'llama-redirect' ),
        );
        //browser
        $browser_selection_list = array();
        $visitor_browser_list = $llama_visitor_details->visitor_browser( 'return_dic' );
        if ( !empty($visitor_browser_list) ) {
            foreach ( $visitor_browser_list as $browser_name => $browser_regex ) {
                $browser_selection_list[] = array(
                    'value' => $browser_name,
                    'title' => $browser_name,
                );
            }
        }
        $fields[] = array(
            'id'                 => 'browser',
            'label'              => __( 'Browser', 'llama-redirect' ),
            'helper'             => __( 'Use this redirect rule only if clients browser is in this list', 'llama-redirect' ),
            'input_type'         => 'select',
            'attr'               => array(
            'multiple' => 'multiple',
        ),
            '_acceptable_values' => array_keys( $visitor_browser_list ),
            'values'             => array( array(
            'group_title' => __( 'Browser list', 'llama-redirect' ),
            'list'        => $browser_selection_list,
        ) ),
        );
        //OS
        $os_selection_list = array();
        $visitor_os_list = $llama_visitor_details->visitor_os( 'return_dic' );
        if ( !empty($visitor_os_list) ) {
            foreach ( $visitor_os_list as $os_name => $os_regex ) {
                $os_selection_list[] = array(
                    'value' => $os_name,
                    'title' => $os_name,
                );
            }
        }
        $fields[] = array(
            'id'                 => 'os',
            'label'              => __( 'Operating System', 'llama-redirect' ),
            'helper'             => __( 'Use this redirect rule only if clients OS is in this list', 'llama-redirect' ),
            'input_type'         => 'select',
            'attr'               => array(
            'multiple' => 'multiple',
        ),
            '_acceptable_values' => array_keys( $visitor_os_list ),
            'values'             => array( array(
            'group_title' => __( 'OS list', 'llama-redirect' ),
            'list'        => $os_selection_list,
        ) ),
        );
        //referer_domain
        $fields[] = array(
            'id'         => 'referer_domain',
            'label'      => __( 'Referer domain', 'llama-redirect' ),
            'helper'     => __( 'Type domain name which indicates the last page the visitor was on (the one where visitor clicked the link)', 'llama-redirect' ),
            'input_type' => 'text',
        );
        //[pro] user role
        $wp_role_selection_list = array();
        $visitor_wp_role_list = $llama_visitor_details->visitor_wp_role( 'return_dic' );
        if ( !empty($visitor_wp_role_list) ) {
            foreach ( $visitor_wp_role_list as $wp_role_name => $wp_role_title ) {
                $wp_role_selection_list[] = array(
                    'value' => $wp_role_name,
                    'title' => $wp_role_title,
                );
            }
        }
        //--delimiter
        $fields[] = array(
            'id'    => '_delimiter',
            'label' => '<hr>',
        );
        //--options
        $fields[] = array(
            'id'    => '_options',
            'label' => __( 'WORDPRESS USERS ROLE', 'llama-redirect' ),
        );
        $fields[] = array(
            '_mode'              => 'pro',
            'id'                 => 'wp_role',
            'label'              => __( 'WP user role', 'llama-redirect' ),
            'helper'             => __( 'Use this redirect rule only if a WordPress user role is in this list', 'llama-redirect' ),
            'input_type'         => 'select',
            'attr'               => array(
            'multiple' => 'multiple',
        ),
            '_acceptable_values' => array_keys( $visitor_wp_role_list ),
            'values'             => array( array(
            'group_title' => __( 'WP user roles', 'llama-redirect' ),
            'list'        => $wp_role_selection_list,
        ) ),
        );
        //--options
        $fields[] = array(
            'id'    => '_options',
            'label' => __( 'DATE-TIME INTERVALS', 'llama-redirect' ),
        );
        //[pro] time from + time_to
        $fields[] = array( array(
            '_mode'      => 'pro',
            'id'         => 'time_from',
            'label'      => __( 'Time from, Time to', 'llama-redirect' ),
            'helper'     => __( 'Time of the day when this redirect rule is active from - to.', 'llama-redirect' ) . ' ' . sprintf( __( 'Time now: %s', 'llama-redirect' ), date( 'H:i:s' ) ),
            'input_type' => 'text',
            'attr'       => array(
            'data-type' => 'time',
            'data-def'  => '00:00:00',
        ),
        ), array(
            '_mode'      => 'pro',
            'id'         => 'time_to',
            'label'      => '',
            'helper'     => '',
            'input_type' => 'text',
            'attr'       => array(
            'data-type' => 'time',
            'data-def'  => '23:59:59',
        ),
        ) );
        //[pro] day of the week
        $fields[] = array( array(
            '_mode'              => 'pro',
            'id'                 => 'day',
            'label'              => __( 'Day of the week', 'llama-redirect' ),
            'helper'             => __( 'Use this redirect rule only if Day of the week is in this list', 'llama-redirect' ),
            'input_type'         => 'select',
            'attr'               => array(
            'multiple' => 'multiple',
        ),
            '_acceptable_values' => range( 1, 7 ),
            'values'             => array( array(
            'group_title' => __( 'Day list', 'llama-redirect' ),
            'list'        => array(
            array(
            'value' => 1,
            'title' => __( 'Monday', 'llama-redirect' ),
        ),
            array(
            'value' => 2,
            'title' => __( 'Tuesday', 'llama-redirect' ),
        ),
            array(
            'value' => 3,
            'title' => __( 'Wednesday', 'llama-redirect' ),
        ),
            array(
            'value' => 4,
            'title' => __( 'Thursday', 'llama-redirect' ),
        ),
            array(
            'value' => 5,
            'title' => __( 'Friday', 'llama-redirect' ),
        ),
            array(
            'value' => 6,
            'title' => __( 'Saturday', 'llama-redirect' ),
        ),
            array(
            'value' => 7,
            'title' => __( 'Sunday', 'llama-redirect' ),
        )
        ),
        ) ),
        ) );
        //[pro] month
        $month_list = array();
        for ( $i = 1 ;  $i <= 12 ;  $i++ ) {
            $month_list[] = array(
                'value' => $i,
                'title' => date( 'F', mktime(
                0,
                0,
                0,
                $i,
                10
            ) ),
            );
        }
        $fields[] = array(
            '_mode'              => 'pro',
            'id'                 => 'month',
            'label'              => __( 'Month', 'llama-redirect' ),
            'helper'             => __( 'Use this redirect rule only if Month now is in this list', 'llama-redirect' ),
            'input_type'         => 'select',
            'attr'               => array(
            'multiple' => 'multiple',
        ),
            '_acceptable_values' => range( 1, 12 ),
            'values'             => array( array(
            'group_title' => __( 'Month list', 'llama-redirect' ),
            'list'        => $month_list,
        ) ),
        );
        //[pro] year
        $year_list = array();
        for ( $i = 0 ;  $i <= 5 ;  $i++ ) {
            $year_list[] = array(
                'value' => date( 'Y', strtotime( '+' . $i . ' years', strtotime( "now" ) ) ),
                'title' => date( 'Y', strtotime( '+' . $i . ' years', strtotime( "now" ) ) ),
            );
        }
        $fields[] = array(
            '_mode'              => 'pro',
            'id'                 => 'year',
            'label'              => __( 'Year', 'llama-redirect' ),
            'helper'             => __( 'Use this redirect rule only if Year now is in this list', 'llama-redirect' ),
            'input_type'         => 'select',
            'attr'               => array(
            'multiple' => 'multiple',
        ),
            '_accepatble_values' => range( date( 'Y' ), date( 'Y', strtotime( '+5 years', strtotime( "now" ) ) ) ),
            'values'             => array( array(
            'group_title' => __( 'Year list', 'llama-redirect' ),
            'list'        => $year_list,
        ) ),
        );
        //--options
        $fields[] = array(
            'id'    => '_options',
            'label' => __( 'GET PARAMETERS', 'llama-redirect' ),
        );
        //[pro] get
        $fields[] = array( array(
            '_mode'      => 'pro',
            'id'         => 'get_key',
            'label'      => __( 'GET parameter', 'llama-redirect' ),
            'helper'     => __( 'Use this redirect rule only if URL matches this GET attribute', 'llama-redirect' ),
            'input_type' => 'text',
        ), array(
            '_mode'      => 'pro',
            'id'         => 'get_val',
            'label'      => '',
            'helper'     => '',
            'input_type' => 'text',
        ) );
        //--options
        $fields[] = array(
            'id'    => '_options',
            'label' => __( 'IP RANGES', 'llama-redirect' ),
        );
        //[pro] ip_ranges
        $llama_visitor_details = new llama_visitor_details();
        $fields[] = array( array(
            '_mode'      => 'pro',
            'id'         => 'ip_from',
            'label'      => __( 'IP range', 'llama-redirect' ),
            'helper'     => __( 'Use this redirect rule only if visitors IP is in this IP range.', 'llama-redirect' ) . ' ' . sprintf( __( 'Your ip: %s', 'llama-redirect' ), $llama_visitor_details->visitor_data()->ip ),
            'input_type' => 'text',
        ), array(
            '_mode'      => 'pro',
            'id'         => 'ip_to',
            'label'      => '',
            'helper'     => '',
            'input_type' => 'text',
        ) );
        //is_active:
        $fields[] = array(
            'id'                 => 'is_active',
            'label'              => __( 'Is active?', 'llama-redirect' ),
            'helper'             => __( 'If checked, Redirect rule is active', 'llama-redirect' ),
            'input_type'         => 'select',
            '_acceptable_values' => array( 1, 0 ),
            'values'             => array( array(
            'group_title' => __( 'Redirect rule enabled?', 'llama-redirect' ),
            'list'        => array( array(
            'value' => 1,
            'title' => __( 'Yes', 'llama-redirect' ),
        ), array(
            'value' => 0,
            'title' => __( 'No', 'llama-redirect' ),
        ) ),
        ) ),
        );
        return $fields;
    }
    
    public function convert_values( $values )
    {
        $form_fields = $this->input_fields();
        if ( !empty($values) and !empty($form_fields) ) {
            foreach ( $values as $key => $val ) {
                //1) find this param in form_fields:
                foreach ( $form_fields as $form_idx => $form_val ) {
                    //1.1) simple field
                    
                    if ( isset( $form_val['id'] ) ) {
                        //2) field found -> define the input_type
                        if ( $form_val['id'] == $key ) {
                            //3) convert select to array
                            if ( $form_val['input_type'] == 'select' ) {
                                $values[$key] = explode( ',', $val );
                            }
                        }
                    } else {
                        if ( !empty($form_val) ) {
                            foreach ( $form_val as $one_form_val ) {
                                //2) field found -> define the input_type
                                if ( $one_form_val['id'] == $key ) {
                                    //3) convert select to array:
                                    if ( $one_form_val['input_type'] == 'select' ) {
                                        $values[$key] = explode( ',', $val );
                                    }
                                }
                            }
                        }
                    }
                
                }
            }
        }
        return $values;
    }
    
    public function verify_form( $post_data, $form_fields )
    {
        $verified_fields = array();
        $verify_total = 4;
        //1) red_code
        if ( isset( $post_data['red_code'] ) and !empty($post_data['red_code']) ) {
            $verified_fields['red_code'] = sanitize_text_field( $post_data['red_code'] );
        }
        //2) link_from
        if ( isset( $post_data['link_from'] ) and !empty($post_data['link_from']) ) {
            //link from contains current site url:
            
            if ( strpos( $post_data['link_from'], get_site_url() ) !== false ) {
                //link from does not contain admin url:
                $admin_path = str_replace( site_url(), '', admin_url() );
                if ( strpos( $post_data['link_from'], $admin_path ) === false ) {
                    $verified_fields['link_from'] = esc_url_raw( $post_data['link_from'] );
                }
            }
        
        }
        //3) link_to
        if ( isset( $post_data['link_to'] ) and !empty($post_data['link_to']) ) {
            if ( $post_data['link_to'] != $post_data['link_from'] ) {
                $verified_fields['link_to'] = esc_url_raw( $post_data['link_to'] );
            }
        }
        //4) track (optional)
        if ( isset( $post_data['track'] ) ) {
            $verified_fields['track'] = sanitize_text_field( $post_data['track'] );
        }
        //5) browser:
        if ( isset( $post_data['browser'] ) ) {
            $verified_fields['browser'] = sanitize_text_field( implode( ',', $post_data['browser'] ) );
        }
        //6) os:
        if ( isset( $post_data['os'] ) ) {
            $verified_fields['os'] = sanitize_text_field( implode( ',', $post_data['os'] ) );
        }
        //7) referer domain:
        if ( isset( $post_data['referer_domain'] ) ) {
            $verified_fields['referer_domain'] = sanitize_text_field( $post_data['referer_domain'] );
        }
        //8) is_active:
        if ( isset( $post_data['is_active'] ) ) {
            $verified_fields['is_active'] = sanitize_text_field( $post_data['is_active'] );
        }
        //RETURN:
        if ( count( $verified_fields ) >= $verify_total ) {
            return $verified_fields;
        }
        return false;
    }
    
    public static function show_page()
    {
        global  $llama_redirect_debugger ;
        $elements = new llama_redirect_elements();
        $llama_redirect_add_page = new llama_redirect_add_page();
        $fx = llama_redirect_PREFIX;
        $form_fields = $llama_redirect_add_page->input_fields();
        $is_edit = 0;
        $show_section = 'form';
        $values = array();
        //If from "post.php" => pre-define post url:
        $pre_defined_url = '';
        if ( isset( $_GET['post'] ) and !empty($_GET['post']) ) {
            $pre_defined_url = get_permalink( intval( $_GET['post'] ) );
        }
        if ( isset( $_GET['link_from'] ) and !empty($_GET['link_from']) ) {
            $pre_defined_url = esc_url_raw( get_site_url() . $_GET['link_from'] );
        }
        //If from "edit" mode:
        if ( isset( $_GET['edit'] ) and !empty($_GET['edit']) ) {
            $is_edit = sanitize_text_field( $_GET['edit'] );
        }
        //add or update hadnler:
        
        if ( isset( $_POST["add_new_url_nonce"] ) && wp_verify_nonce( $_POST['add_new_url_nonce'], 'add_new_url' ) ) {
            $status = false;
            $verified_fields = $llama_redirect_add_page->verify_form( $_POST, $form_fields );
            
            if ( $verified_fields ) {
                $database = new llama_redirect_database();
                
                if ( $is_edit ) {
                    $status = $database->update( 'llama_redirect_settings', $verified_fields, array(
                        '_key' => 'redirect_id',
                        '_val' => $is_edit,
                    ) );
                } else {
                    $status = $database->insert( 'llama_redirect_settings', $verified_fields );
                }
            
            }
            
            //edit mode -> show notification:
            
            if ( $is_edit ) {
                
                if ( $status ) {
                    echo  $elements->notice( 'success', __( 'URL settings updated!', 'llama-redirect' ) ) ;
                } else {
                    echo  $elements->notice( 'error', __( 'Changes not saved, an error occured!', 'llama-redirect' ) ) ;
                }
            
            } else {
                
                if ( $status ) {
                    $show_section = 'add_success';
                } else {
                    $show_section = 'add_failure';
                }
            
            }
        
        }
        
        //If from "edit" => set values by id:
        
        if ( $is_edit ) {
            $database = new llama_redirect_database();
            $values = $database->read_row( 'llama_redirect_settings', array(
                '_key' => 'redirect_id',
                '_val' => sanitize_text_field( $_GET['edit'] ),
            ) );
            $values = $llama_redirect_add_page->convert_values( $values );
        }
        
        //SHOW NAV AND TITLES:
        echo  $elements->nav( $fx ) ;
        
        if ( $is_edit ) {
            echo  $elements->title( $fx, __( 'Edit URL', 'llama-redirect' ) ) ;
        } else {
            echo  $elements->title( $fx, __( 'Add URL', 'llama-redirect' ) ) ;
        }
        
        //SHOE PAGE:
        
        if ( $show_section == 'form' ) {
            echo  '<div class="' . $fx . '-container">
				<form action="" method="post" id="llama_redirect_add_page">' ;
            #add nonce field:
            echo  wp_nonce_field( 'add_new_url', 'add_new_url_nonce' ) ;
            #html -> card:
            echo  '
					<div class="' . $fx . '-card">
						<div class="' . $fx . '-card-content">
							<table>' ;
            //add fields:
            if ( !empty($form_fields) ) {
                foreach ( $form_fields as $field_data ) {
                    
                    if ( isset( $field_data['id'] ) and $field_data['id'] == '_options' ) {
                        echo  $elements->table_title( $field_data['label'], 2 ) ;
                    } else {
                        
                        if ( isset( $field_data['id'] ) and $field_data['id'] == '_delimiter' ) {
                            echo  $elements->table_delimiter( $field_data['label'], 2 ) ;
                        } else {
                            //set default tracking value according to the setting option:
                            if ( isset( $field_data['id'] ) and $field_data['id'] == 'track' ) {
                                $values = $llama_redirect_add_page->set_default_tracking_value( $field_data, $values );
                            }
                            echo  $elements->table_row( $field_data, $pre_defined_url, $values ) ;
                        }
                    
                    }
                
                }
            }
            echo  '</table>
						</div>
						<div class="' . $fx . '-card-action">
							<table>
								<tr>
									<th scope="row" width="25%"></th>
									<td width="75%">' ;
            
            if ( $is_edit ) {
                echo  '<input type="hidden" name="redirect_id" value="' . $is_edit . '"/>' ;
                submit_button(
                    __( 'Save changes', 'llama-redirect' ),
                    'primary',
                    'submit-form',
                    false
                );
            } else {
                submit_button(
                    __( 'Add', 'llama-redirect' ),
                    'primary',
                    'submit-form',
                    false
                );
            }
            
            echo  '</td>
								</tr>
							</table>
						</div>
					</div>
				</form>
			</div>' ;
        } else {
            
            if ( $show_section == 'add_success' ) {
                echo  '<div class="' . $fx . '-container">
				<div class="' . $fx . '-card">
					<div class="' . $fx . '-content ' . $fx . '-center">
						<div class="' . $fx . '-row">
							<div class="' . $fx . '-col ' . $fx . '-s12">
								<span class="dashicons dashicons-yes ' . $fx . '-icon-success"></span>
							</div>
						</div>
						<div class="' . $fx . '-row">
							<div class="' . $fx . '-col ' . $fx . '-s12">
								<h4 class="' . $fx . '-card-title">' . __( 'Url successfuly added!', 'llama-redirect' ) . '</h4>
							</div>
						</div>
					</div>
					<div class="' . $fx . '-card-action">
						<div class="' . $fx . '-row">
							<div class="' . $fx . '-col ' . $fx . '-s6 ' . $fx . '-right-align">
								<a href="' . esc_url( admin_url( '/admin.php?page=llama-redirect_add' ) ) . '" class="' . $fx . '-btn ' . $fx . '-btn-large ' . $fx . '-green">' . __( 'Add more', 'llama-redirect' ) . '</a>
							</div>
							<div class="' . $fx . '-col ' . $fx . '-s6 ' . $fx . '-left-align">
								<a href="' . esc_url( admin_url( '/admin.php?page=llama-redirect' ) ) . '" class="' . $fx . '-btn ' . $fx . '-btn-large ' . $fx . '-blue">' . __( 'Go to list', 'llama-redirect' ) . '</a>
							</div>
						</div>
					</div>
				</div>
			</div>' ;
            } else {
                if ( $show_section == 'add_failure' ) {
                    echo  '<div class="' . $fx . '-container">
				<div class="' . $fx . '-card">
					<div class="' . $fx . '-content ' . $fx . '-center">
						<div class="' . $fx . '-row">
							<div class="' . $fx . '-col ' . $fx . '-s12">
								<span class="dashicons dashicons-no-alt ' . $fx . '-icon-error"></span>
							</div>
						</div>
						<div class="' . $fx . '-row">
							<div class="' . $fx . '-col ' . $fx . '-s12">
								<h4 class="' . $fx . '-card-title">' . __( 'An error occured!', 'llama-redirect' ) . '</h4>
							</div>
						</div>
						<div class="' . $fx . '-row">
							<div class="' . $fx . '-col ' . $fx . '-s12">
								<div class="' . $fx . '-inline-block ' . $fx . '-left-align">
									<p>' . __( 'Please be sure that URLs are:', 'llama-redirect' ) . '</p>
									<ul class="' . $fx . '-browser-default">
										<li>' . __( 'Both links are typed correctly', 'llama-redirect' ) . '</li>
										<li>' . __( 'There is no current redirect using this link', 'llama-redirect' ) . '</li>
										<li>' . sprintf( __( 'Link from is not from your admin panel (does not contain "%s")', 'llama-redirect' ), str_replace( site_url(), '', admin_url() ) ) . '</li>
										<li>' . __( 'Link from and Link to are not equal (can not redirect to the same page)', 'llama-redirect' ) . '</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<div class="' . $fx . '-card-action ' . $fx . '-center">
						<div class="' . $fx . '-row">
							<div class="' . $fx . '-col ' . $fx . '-s12">
								<a href="' . esc_url( admin_url( '/admin.php?page=llama-redirect_add' ) ) . '" class="' . $fx . '-btn ' . $fx . '-btn-large ' . $fx . '-green">' . __( 'Try again', 'llama-redirect' ) . '</a>
							</div>
						</div>
					</div>
				</div>
			</div>' ;
                }
            }
        
        }
        
        $llama_redirect_debugger->page_loaded( 'llama_redirect_add_page' );
    }
    
    public function set_default_tracking_value( $field_data, $values )
    {
        $fx = llama_redirect_PREFIX;
        
        if ( $field_data['id'] == 'track' ) {
            $default_value = get_option( $fx . '_def_track' );
            
            if ( $default_value == '1' ) {
                $values['track'] = array( '1' );
            } else {
                $values['track'] = array( '0' );
            }
        
        }
        
        return $values;
    }

}