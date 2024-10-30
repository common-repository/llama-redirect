<?php
defined( 'ABSPATH' ) or exit;

class llama_redirect_help_page {
	public static function show_page() {
		global $llama_redirect_debugger;
		$fx = llama_redirect_PREFIX;
		$elements = new llama_redirect_elements;
		$llama_redirect_help_page = new llama_redirect_help_page;

		//SHOW NAV and TITLE:
		echo $elements->nav($fx);
		echo $elements->title($fx, __( 'Help', 'llama-redirect' ));

		//SHOW PAGE:
		$html =  
		'<div class="'.$fx.'-container">';
			
			//URL to online sandboxes for testing:
			$html .= '
			<div class="'.$fx.'-card">
				<div class="'.$fx.'-card-content">
					<h3><a href="' . esc_url( 'https://llamasapps.com/go/wildcard-tester-20' ) . '" target="_blank">' . __( 'Wildcard Tester Online', 'llama-redirect' ) . '</a></h3>
					<h3><a href="' . esc_url( 'https://llamasapps.com/go/ip-range-tester-21') . '" target="_blank">' . __( 'IP Range Tester Online', 'llama-redirect') . '</a></h3>
				</div>
			</div>';
			
			//should be the URL to official documentation accrding to plugins version - to have information up to date
			$html .= '
			<div class="'.$fx.'-card">
				<div class="'.$fx.'-card-content">
					<embed src="//static.llamasapps.com/plugin_files/llama_redirect-' . llama_redirect_PLUGIN_VER . '-plugin_docs.pdf" type="application/pdf" width="100%" height="700px" />
				</div>
			</div>';
			
		//CLOSE PAGE:
		$html .= '
		</div>';
		
		echo $html;
		$llama_redirect_debugger->page_loaded('llama_redirect_help_page');
	}
}