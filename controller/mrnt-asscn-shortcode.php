<?php
/**
 * Class to handle shortcodes.
 * We can list all members, all members for a range of member roles or all members for a particular role.
 *
 * @author Maurent Software ${date.year}
 */

class MrntAsscnShortcode {

	var $fieldsObject = null;
	var $fields = null;

	function __construct() {
		add_shortcode('mrnt_asscn', array($this, 'do_shortcode'));
		add_action('wp_enqueue_scripts', array($this, 'styling'));
	}

	/**
	 * Add in our styling for displaying lists of users
	 */
	function styling() {
		wp_enqueue_style( 'mrnt-asscn', MRNT_ASSCN_URI . '/css/mrnt-asscn.css', array(), filemtime(MRNT_ASSCN_PATH . '/css/mrnt-asscn.css') );
	}

	/**
	 * Handle the shortcode
	 * @param unknown $atts
	 */
	function do_shortcode($atts) {
		if (!$this->fields) {
			include_once(MRNT_ASSCN_PATH . '/model/mrnt-user-fields.php');
			$this->fieldsObject = new MrntUserFields();
		}

		$atts = shortcode_atts( array(
				'roles'			=> '',
				'members'		=> '',
		), $atts );

		if ($atts['roles']!='') {
			return $this->roles($atts['roles']);
		}
		if ($atts['members']!='') {
			return $this->members($atts['members']);
		}
	}

	/**
	 * Get formatted list of users that have the given role(s)
	 * @param string $roles	: Optional comma separated list of roles (as ID's or titles). If empty then display all roles.
	 * @return string		: html formatted list of members grouped by role
	 */
	function roles($roles = 'all') {
		global $wpdb;

		$return = '';

		$roles = array_map('trim',explode(',', $roles));
		$member_roles = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mrnt_asscn_roles ORDER BY ranking DESC, subrole ASC");
		$lastrole = '';
		foreach($member_roles as $member_role) {
			if ($roles[0]=='all' || in_array($member_role->title, $roles) || in_array($member_role->id, $roles)) {
				$users = get_users(array(
						'meta_key'		=> 'mrnt_asscn_roles',
						'meta_value'	=> $member_role->id,
						'orderby'		=> 'display_name'
						));
				if ($users) {
					if (count($roles)) {
						if ($lastrole != $member_role->title) {
							$return .= '<h3>'.$member_role->title.'</h3>';
						}
						if ($member_role->subrole) {
							$return .= '<h4>'.$member_role->subrole.'</h4>';
						}
						$return .= $this->_format_members($users);
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Get formatted list of users sorted by name.
	 * If PaidMembershipsPro is present we fetch a list of its members grouped by membership level instead
	 * @param string $membership	:	optional comma separated list of membership types as defined in PaidMembershipPro
	 * 									if empty then display all memberships
	 * @return string
	 */
	function members($memberships = 'all') {
		$return = '';

		// PMPro Support - print member list by level
		if (defined('PMPRO_DIR')) {
			global $wpdb;

			$memberships = array_map('trim',explode(',', $memberships));
			$member_levels = $wpdb->get_results("SELECT id, name FROM $wpdb->pmpro_membership_levels ORDER BY name");
			foreach($member_levels as $member_level) {
				if (in_array($member_level->name, $memberships) || $memberships[0] == 'all') {
					$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, u.user_url, UNIX_TIMESTAMP(u.user_registered) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership FROM $wpdb->users u 
					LEFT JOIN $wpdb->pmpro_memberships_users mu ON u.ID = mu.user_id 
					LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id
					LEFT JOIN $wpdb->usermeta mt ON u.ID = mt.user_id
					WHERE mu.membership_id > 0  AND mu.status = 'active' AND mu.membership_id = '" . $member_level->id . "' AND mt.meta_key = 'last_name' 
					GROUP BY u.ID ORDER BY mt.meta_value ASC";
					$users = $wpdb->get_results($sqlQuery);
					if ($users) {
						$return .= '<h3>'.$member_level->name.'</h3>';
						$return .= $this->_format_members($users);
					}
				}
			}
		} else {
			$users = get_users(array(
					'orderby'		=> 'display_name'
					));
			if ($users) {
				usort($users, array($this, '_sort_lastname'));
				$return .= $this->_format_members($users);
			}
		}
		return $return;
	}

	function _sort_firstname($a, $b) {
		return strcasecmp($a->first_name, $b->first_name);
	}

	function _sort_lastname($a, $b) {
		if (!$result = strcasecmp($a->last_name, $b->last_name)) {
			$result = strcasecmp($a->first_name, $b->first_name);
		}
		return $result;
	}

	function _format_members($users) {
		$fieldsObject = $this->fieldsObject;
		@ob_flush();
		ob_start();
		include(MRNT_ASSCN_PATH.'/view/directory-full.php');
		$op = ob_get_clean();
		return $op;
	}

}
