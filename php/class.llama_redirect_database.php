<?php
defined( 'ABSPATH' ) or exit;


class llama_redirect_database
{

	public function insert($table,$values)
	{
		global $wpdb;
		global $llama_redirect_debugger;
		$_started = microtime(1);
		$status = false;
		
		if (!empty($table) and !empty($values))
		{
			//1) prepare columns to insert:
			$mysql_columns = array();
			foreach($values as $value_key => $value_val)
			{
				if (!is_numeric($value_val))
				{
					$mysql_columns[$value_key] = sanitize_text_field($value_val);
				}
				else
				{
					$mysql_columns[$value_key] = floatval($value_val);
				}
			}
			//2) insert
			if (!empty($mysql_columns))
			{
				$insert = $wpdb->insert($wpdb->prefix.$table,$mysql_columns,$this->mysql_values_replace($mysql_columns));
				if ($insert)
				{
					$status = $insert;	
				}
				//2.1) handle error
				
				echo '<h1>'.$wpdb->last_error.'</h1>';
				
				$this->handle_error($wpdb->last_error,$table);
			}
		}
		$llama_redirect_debugger->_mysql_finished('insert',(microtime(1) - $_started));
		return $status;
	}
	
	public function update($table,$values,$update_kv)
	{
		global $wpdb;
		global $llama_redirect_debugger;
		$_started = microtime(1);
		$status = false;
		
		if (!empty($table) and !empty($values) and isset($update_kv['_key']) and isset($update_kv['_val']))
		{
			//1) prepare colums to update:
			$mysql_columns = array();
			foreach($values as $value_key => $value_val)
			{
				if (!is_numeric($value_val))
				{
					$mysql_columns[$value_key] = sanitize_text_field($value_val);
				}
				else
				{
					$mysql_columns[$value_key] = floatval($value_val);	
				}
			}
			//2) update:
			if (!empty($mysql_columns))
			{
				$update = $wpdb->update(
					$wpdb->prefix.$table,
					$mysql_columns,
					array(
						$update_kv['_key'] => $update_kv['_val']
					),
					$this->mysql_values_replace($mysql_columns),
					array('%d')
				);
				if (!($update === false))
				{
					$status = true;
				}
				//2.1) handle error
				$this->handle_error($wpdb->last_error,$table);
			}
		}
		$llama_redirect_debugger->_mysql_finished('update',(microtime(1) - $_started));
		return $status;
	}
	
	public function delete($table,$delete_kv)
	{
		global $wpdb;
		global $llama_redirect_debugger;
		$_started = microtime(1);
		$status = false;
		
		if (!empty($table) and isset($delete_kv['_key']) and isset($delete_kv['_val']))
		{
			//delete
			$delete = $wpdb->delete(
				$wpdb->prefix.$table,
				array(
					sanitize_text_field($delete_kv['_key']) => sanitize_text_field($delete_kv['_val'])
				),
				array('%d')
			);
			if (!($delete === false))
			{
				$status = true;	
			}
			//handle error
			$this->handle_error($wpdb->last_error,$table);
		}
		$llama_redirect_debugger->_mysql_finished('delete',(microtime(1) - $_started));
		return $status;
	}
	
	public function mysql_values_replace($cols_vals)
	{
		$replace = array();
		if (!empty($cols_vals))
		{
			foreach($cols_vals as $col => $val) {
				if (is_numeric($val))
				{
					$replace[] = '%d';
				}
				else {
					$replace[] = '%s';	
				}
			}
		}
		return $replace;
	}
	
	public function read_row($table,$filter = array())
	{
		//0) init
		global $wpdb;
		global $llama_redirect_debugger;
		$_started = microtime(1);
		$query = "SELECT * FROM `".$wpdb->prefix.$table."`";
		if (isset($filter['_key']) and isset($filter['_val']))
		{
			$query .= " WHERE `".sanitize_text_field($filter['_key'])."` = '".sanitize_text_field($filter['_val'])."'";	
		}
		//2) run
		$rows = $wpdb->get_row($query,ARRAY_A);
		$llama_redirect_debugger->_mysql_finished('read_row',(microtime(1) - $_started));
		//2.1) handle error
		$this->handle_error($wpdb->last_error,$table);
		
		return $rows;
	}
	
	public function read_table($table,$filter = array(),$use_query = '',$limit = '')
	{
		//0) init
		global $wpdb;
		global $llama_redirect_debugger;
		$_started = microtime(1);
		//1) build query
		if (empty($use_query))
		{
			$query = "SELECT * FROM `".$wpdb->prefix.$table."`" . ((!empty($limit)) ? " LIMIT ".$limit : "");
			if (isset($filter['_key']) and isset($filter['_val']))
			{
				$query .= " WHERE `".sanitize_text_field($filter['_key'])."` = '".sanitize_text_field($filter['_val'])."'";	
			}
		}
		else {
			$query = $use_query;	
		}
		//2) run
		$results = $wpdb->get_results($query,ARRAY_A);
		//2.1) handle error
		$this->handle_error($wpdb->last_error,$table);
		
		$llama_redirect_debugger->_mysql_finished('read_row',(microtime(1) - $_started));
		return $results;
	}
	
	public function general_query($query, $args = array())
	{
		global $wpdb;
		//1) Execute query
		if (!empty($query))
		{
			if (!empty($args))
			{
				$query = $wpdb->prepare($query,$args);
			}
			if ( $wpdb->query( $query ) )
			{
				return true;
			}
		}
		return false;
	}
	
	public function truncate($table)
	{
		global $wpdb;
		global $llama_redirect_debugger;
		$_started = microtime(1);
		$query = "TRUNCATE `".$wpdb->prefix.$table."`";	
		$status = $wpdb->query($query);
		
		// handle error
		$this->handle_error($wpdb->last_error,$table);
		
		$llama_redirect_debugger->_mysql_finished('truncate',(microtime(1) - $_started));
		return $status;
	}
	
	public function get_columns($table)
	{
		global $wpdb;
		global $llama_redirect_debugger;
		$_started = microtime(1);
		$prefixed_table = $wpdb->prefix . $table;
		$columns = $wpdb->get_col("DESC {$prefixed_table}", 0);
		
		// handle error
		$this->handle_error($wpdb->last_error,$table);
		
		$llama_redirect_debugger->_mysql_finished('get_columns',(microtime(1) - $_started));
		return $columns;
	}
	
	public function handle_error($error,$table)
	{
		$error = strtolower($error);
		// if table not exists -> create new table:
		if (strpos($error,'table') !==false and strpos($error,'doesn') !==false and strpos($error,'exist') !==false)
		{
			$llama_redirect_install = new llama_redirect_install;
			if (method_exists($llama_redirect_install,$table))
			{
				$llama_redirect_install->$table();
			}
		}
	}
	
}