<?php
/**
 * Class to define the fields we are adding to the user edit screen.
 *
 * @author Maurent Software ${date.year}
 */

class MrntUserFields {
	
	var $sections = array();
	var $fields = array();
	
	function __construct() {
		
		$this->sections = array(
				'name'		=> 'Name',
				'contact'	=> 'Contact Information',
				'address'	=> 'Address',
				'about'		=> 'About the user',
				'roles'		=> (defined('PMPRO_DIR') ? 'Member Roles' : 'Association Roles'),
		);
		
		$this->fields = array(
				// contact fields
				'mrnt_asscn_greeting'	=> array(
						'type'			=> 'select',
						'prompt'		=> 'Greeting',
						'description'	=> '',
						'label'			=> '',
						'options'		=> array(
								'Mr.',
								'Mrs.',
								'Ms.',
								'Dr.',
								),
						'required'		=> false,
						'section'		=> 'name',
						'insert'		=> array(
								'action'	=> 'before',
								'field'		=> 'first_name'
								)),
				'mrnt_asscn_mid_name'	=> array(
						'type'			=> 'string',
						'prompt'		=> 'Middle Name or Initial',
						'description'	=> '',
						'label'			=> '',
						'required'		=> false,
						'section'		=> 'name',
						'insert'		=> array(
								'action'	=> 'after',
								'field'		=> 'first_name'
								)),
				'mrnt_asscn_title'		=> array(
						'type'			=> 'string',
						'prompt'		=> 'Title',
						'description'	=> '(Esq., PhD, etc)',
						'label'			=> '',
						'required'		=> false,
						'section'		=> 'name',
						'insert'		=> array(
								'action'	=> 'after',
								'field'		=> 'last_name'
								)),
				'mrnt_asscn_email_2'	=> array(
						'type'			=> 'email',
						'prompt'		=> 'Additional E-mail Address',
						'description'	=> '(optional)',
						'label'			=> 'Email',
						'required'		=> false,
						'section'		=> 'contact',
						'insert'	=> array(
								'action'	=> 'after',
								'field'		=> 'email'
								)),
				'mrnt_asscn_phone_1'	=> array(
						'type'			=> 'string',
						'prompt'		=> 'Phone',
						'description'	=> '',
						'label'			=> 'Phone',
						'required'		=> false,
						'section'		=> 'contact',
						),
				'mrnt_asscn_phone_2'	=> array(
						'type'			=> 'string',
						'prompt'		=> 'Additional Phone',
						'description'	=> '(optional)',
						'label'			=> 'Phone',
						'required'		=> false,
						'section'		=> 'contact',
						),
				'mrnt_asscn_fax'	=> array(
						'type'			=> 'string',
						'prompt'		=> 'Fax',
						'description'	=> '',
						'label'			=> 'Fax',
						'required'		=> false,
						'section'		=> 'contact',
						),
				// address
				'mrnt_asscn_company'	=> array(
						'type'			=> 'string',
						'prompt'		=> 'Company',
						'description'	=> '(If applicable)',
						'label'			=> '',
						'required'		=> false,
						'section'		=> 'address',
						),
				'mrnt_asscn_address_1'	=> array(
						'type'			=> 'string',
						'prompt'		=> 'Street Address',
						'description'	=> '',
						'label'			=> '',
						'required'		=> false,
						'section'		=> 'address',
						),
				'mrnt_asscn_address_2'	=> array(
						'type'			=> 'string',
						'prompt'		=> '',
						'description'	=> '',
						'label'			=> '',
						'required'		=> false,
						'section'		=> 'address',
						),
				'mrnt_asscn_address_3'	=> array(
						'type'			=> 'string',
						'prompt'		=> '',
						'description'	=> '',
						'label'			=> '',
						'required'		=> false,
						'section'		=> 'address',
						),
				'mrnt_asscn_city'		=> array(
						'type'			=> 'string',
						'prompt'		=> 'City',
						'description'	=> '',
						'label'			=> '',
						'required'		=> false,
						'section'		=> 'address',
						),
				'mrnt_asscn_zip'		=> array(
						'type'			=> 'string',
						'prompt'		=> 'Zip/Postal Code',
						'description'	=> '',
						'label'			=> '',
						'required'		=> false,
						'section'		=> 'address',
						),
				'mrnt_asscn_state'		=> array(
						'type'			=> 'string',
						'prompt'		=> 'State/Province',
						'description'	=> '',
						'label'			=> '',
						'required'		=> false,
						'section'		=> 'address',
						),
				'mrnt_asscn_country'	=> array(
						'type'			=> 'country',
						'prompt'		=> 'Country',
						'description'	=> '',
						'label'			=> '',
						'required'		=> false,
						'section'		=> 'address',
						),
				// member related stuff
				'mrnt_asscn_date_joined'=> array(
						'type'			=> 'string',
						'prompt'		=> 'Date Joined',
						'description'	=> '(Year the member signed up)',
						'label'			=> 'Date Joined',
						'required'		=> false,
						'section'		=> 'about',
						'insert'		=> array(
								'action'	=> 'end',
								'field'		=> 'pass1'
						),
						'adminonly'		=> true,
						),
				'mrnt_asscn_roles'	=> array(
						'type'			=> 'member_role',
						'prompt'		=> '',
						'description'	=> '',
						'label'			=> '',
						'required'		=> false,
						'section'		=> 'roles',
						),
				);
		
	}
	
	function get_default_user_meta() {
		$defs = array();
		foreach($this->fields as $key=>$params) {
			$defs[$key] = array('');
		
		}
		return $defs;
	}
}