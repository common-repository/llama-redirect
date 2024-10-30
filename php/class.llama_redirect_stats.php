<?php
defined( 'ABSPATH' ) or exit;

class llama_redirect_debugger {
	function __construct() {
		$this->_started = microtime(1);		
		$this->_mysql = array();
	}
	public function _mysql_finished($command,$exec_time) {
		//record:
		if (!isset( $this->_mysql[$command] )) {
			$this->_mysql[$command] = array('count' => 0, 'load_time' => array());	
		}
		$this->_mysql[$command]['count'] += 1;
		$this->_mysql[$command]['load_time'][] = $exec_time;
	}
	public function page_loaded($page_id) {
		if (llama_redirect_DEBUGGER) {
			$fx = llama_redirect_PREFIX;
			//record stats:
			$page_load_time = round( microtime(1) - $this->_started , 2 ); //sec
			$sys_getloadavg = sys_getloadavg();
			//show stats:
			$stats = array(
				'page_load_time' => $page_load_time,
				'mysql' => $this->mysql_to_avg($this->_mysql),
				'ram_usage' => $this->ram_usage(), //mb
				'cpu_usage' => $sys_getloadavg[0],
			);
			echo '<div class="'.$fx.'-container"><pre><code>'.print_r($stats,1).'</code></pre></div>';
		}
	}
	private static function mysql_to_avg($mysql) {
		$stats = array();
		if (!empty($mysql)) {
			foreach($mysql as $command => $command_stats) {
				$stats[$command] = array(
					'count' => $command_stats['count'],
					'load_time' => ((!empty($command_stats)) ? array_sum($command_stats['load_time']) / count($command_stats) : 0)
				);	
			}
		}
		return $stats;
	}
	private static function ram_usage() {
		$free = shell_exec('free');
		$free = (string)trim($free);
		$free_arr = explode("\n", $free);
		$mem = explode(" ", $free_arr[1]);
		$mem = array_filter($mem, function($value) { return ($value !== NULL && $value !== false && $value !== ''); }); // removes nulls from array
		$mem = array_merge($mem);
		$memtotal = round($mem[1] / 1000000,2);
		$memused = round($mem[2] / 1000000,2);
		$memfree = round($mem[3] / 1000000,2);
		$membuffer = round($mem[5] / 1000000,2);
		$memcached = round($mem[6] / 1000000,2);
		$memusage = round((($memused - $memcached - $membuffer) / ($memtotal * 100)),2);
		return abs($memusage);	
	}
}
$llama_redirect_debugger = new llama_redirect_debugger();