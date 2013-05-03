<?php 
/**
 * Class for adding fields to the user admin forms
 * 
 * @author Maurent Software ${date.year}
 */

class MrntAsscnUser {
	
	var $fieldsObject = null;
	var $js = '';

	function __construct() {
		include_once(MRNT_ASSCN_PATH . '/model/mrnt-user-fields.php');
		$this->fieldsObject = new MrntUserFields();
	}
	
	/**
	 * Hack our fields into the new user form using js since there are no filters in new-user.php 
	 */
	function add_user() {
		global $pagenow;
		
		// do this only in page user-new.php
		if($pagenow !== 'user-new.php')
			return;
		
		// do this only if you can
		if(!current_user_can('manage_options'))
			return false;

		// get form as a string...
		@ob_flush();
		ob_start();
		include(MRNT_ASSCN_PATH.'/helper/functions.php');
		include(MRNT_ASSCN_PATH.'/view/user-add.php');
		$op = ob_get_clean();
		$op = preg_replace(array("/\n|\r/","/>\s+</","/\s+/","/'/"), array("","><"," ","&apos;"), $op);
		// ...and insert it into the table with JavaScript:
		?>
		<script type='text/javascript'>
		jQuery(function(){
			if (jQuery('#createuser table').length) {
				jQuery('#createuser table').append('<? echo $op;?>');
			}
			<?php echo $this->js;?>
			// remove any sections that are empty after shuffling our fields
			jQuery('h3').each(function(){
				var $parent = jQuery(this).parent().parent();
				if ($parent.next() && $parent.next().find('h3').length) {
					$parent.remove();
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Add address section to the form for editing other users or ourself
	 */
	function edit_user($user = null) {
		include(MRNT_ASSCN_PATH.'/helper/functions.php');
		include(MRNT_ASSCN_PATH.'/view/user-edit.php');
	}

	/**
	 * Add fields to the contact section of the user edit form 
	 */
	function edit_user_contactmethods($methods) {
		return $methods;
	}

	/**
	 * Save edits or new user
	 */
	function validate_user( $errors, $update = null, $user_id = null ) {
		if ( !current_user_can( 'edit_users', $user_id ) )
			return false;

		if (!$_POST['first_name']) {	 
			$errors->add('first_name', "<strong>ERROR</strong>: Please enter a first name.");
		}			
		if (!$_POST['last_name']) {
			$errors->add('last_name', "<strong>ERROR</strong>: Please enter a last name.");
		}
		foreach($this->fieldsObject->fields as $field => $params) {
			if ($params['required'] && !$_POST[$field]) {
				$errors->add($field, "<strong>ERROR</strong>: Please enter a value for '{$params['prompt']}'.");
			} elseif ($params['type'] == 'email' && $_POST[$field] && !is_email($_POST[$field])) {
				$errors->add($field, "<strong>ERROR</strong>: Please enter a valid email address for '{$params['prompt']}'.");
			}
		}
	}
	
	/**
	 * Save edits or new user
	 */
    function save_user( $user_id ) {
        if ( !current_user_can( 'edit_users', $user_id ) )
            return false;

        foreach($this->fieldsObject->fields as $field => $params) {
			switch($params['type']) {
				case 'member_role':
				case 'checkboxes':
					delete_user_meta( $user_id, $field );
					if (isset($_POST[$field]) && count($_POST[$field])) {
						foreach($_POST[$field] as $key => $val) {
							add_user_meta( $user_id, $field, $key);
						}
					}
					break;
				default:
					update_user_meta( $user_id, $field, $_POST[$field]);
			}
        }
    }
}