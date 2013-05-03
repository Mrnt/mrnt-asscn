<?php
/**
 * Helper functions for admin messages, etc
 * 
 * @author Maurent Software ${date.year}
 */

function mrnt_asscn_log_message($msg) {
	$notices= get_option(MRNT_ASSCN_ADMIN_NOTICE, array());
	$notices[]= $msg;
	update_option(MRNT_ASSCN_ADMIN_NOTICE, $notices);
}

function mrnt_asscn_display_admin_notices() {
	if ($notices= get_option(MRNT_ASSCN_ADMIN_NOTICE)) {
		echo '<div class="updated"><p><strong>Maurent Association:</strong></p>';
		foreach ($notices as $notice) {
			echo '<p>'.$notice.'</p>';
		}
		echo '</div>';
		delete_option(MRNT_ASSCN_ADMIN_NOTICE);
	}	
}