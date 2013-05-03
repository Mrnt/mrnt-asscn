<?php 
/**
 * Class for installing and uninstalling this plugin
 * On install sets up tables, on uninstall cleans up all the data it created including options.
 * 
 * @author Maurent Software ${date.year}
 */

class MrntAsscnInstall {

	var $db_version = MRNT_ASSCN_DB_VERSNUM;
	var $messages = '';
	
	function __construct() {
	}

	function activate() {
		global $wpdb;

		// trap any errors in the error log
		ob_start();
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		
		// create the roles table
		$table_name = $wpdb->prefix . 'mrnt_asscn_roles';
		// NB need two spaces between KEY and name of field for dbDelta
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			ranking mediumint(9) NOT NULL,
			title tinytext NOT NULL,
			subrole tinytext NOT NULL,
			description tinytext NOT NULL,
			UNIQUE KEY  id (id)
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
		@dbDelta($sql);
		if (!$wpdb->get_var( "SELECT COUNT(*) FROM $table_name" )) {
			// add some starter values
			$wpdb->insert( $table_name, array( 'ranking' => 1, 'title' => 'President' ) );
			$wpdb->insert( $table_name, array( 'ranking' => 2, 'title' => 'Secretary' ) );
		}
		
		// create data tables
		$sql = file_get_contents(MRNT_ASSCN_PATH . '/data/install.mysql');
		$sql = str_replace('mrnt_asscn', $wpdb->prefix.'mrnt_asscn', $sql);
		$this->_batchquery($sql);
		$sql = file_get_contents(MRNT_ASSCN_PATH . '/data/import.mysql');
		$sql = str_replace('mrnt_asscn', $wpdb->prefix.'mrnt_asscn', $sql);
		$this->_batchquery($sql);
		
		add_option(MRNT_ASSCN_DB_VERSION, $this->db_version);

		$message = ob_get_flush();
		mrnt_asscn_log_message($message);
		
		if (defined('PMPRO_DIR')) {
			mrnt_asscn_log_message('You will find an option to add and configure members roles in the menu under "Memberships"->"Member Roles".');
		} else {
			mrnt_asscn_log_message('You will find an option to add and configure members roles in the menu under "Users"->"Member Roles".');
		}
	}

	function uninstall() {
		global $wpdb;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		// drop roles table
		$table = $wpdb->prefix . 'mrnt_asscn_roles';
		$wpdb->query("DROP TABLE IF EXISTS $table");

		// cleanup user meta
		$table = $wpdb->prefix . 'usermeta';
		$wpdb->query("DELETE FROM $table WHERE meta_key LIKE 'mrnt_asscn_%'");

		// remove data tables
		$sql = file_get_contents(MRNT_ASSCN_PATH . '/data/uninstall.mysql');
		$sql = str_replace('mrnt_asscn', $wpdb->prefix.'mrnt_asscn', $sql);
		$this->_batchquery($sql);

		//Delete any options that stored also?
		delete_option(MRNT_ASSCN_DB_VERSION);
		delete_option(MRNT_ASSCN_ADMIN_NOTICE);
	}

	function _batchquery($sql) {
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		$mysqli->set_charset('utf8');
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			die;
		}
		if ($mysqli->multi_query($sql)) {
			$i = 0;
			do {
				$i++;
			} while ($mysqli->next_result());
		}
		if ($mysqli->errno) {
			echo "Batch execution prematurely ended on statement $i.\n";
			var_dump($statements[$i], $mysqli->error);
		}
	}
}