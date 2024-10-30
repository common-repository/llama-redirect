<?php

if ( !function_exists( 'llama_redirect_fs' ) ) {
    // Create a helper function for easy SDK access.
    function llama_redirect_fs()
    {
        global  $llama_redirect_fs ;
        
        if ( !isset( $llama_redirect_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_2876_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_2876_MULTISITE', true );
            }
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $llama_redirect_fs = fs_dynamic_init( array(
                'id'             => '2876',
                'slug'           => 'llama-redirect',
                'type'           => 'plugin',
                'public_key'     => 'pk_8559142e9bef671bdc4688cab168a',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 7,
                'is_require_payment' => false,
            ),
                'menu'           => array(
                'slug'       => 'llama-redirect',
                'first-path' => 'admin.php?page=llama-redirect',
                'support'    => false,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $llama_redirect_fs;
    }
    
    // Init Freemius.
    llama_redirect_fs();
    // Signal that SDK was initiated.
    do_action( 'llama_redirect_fs_loaded' );
}
