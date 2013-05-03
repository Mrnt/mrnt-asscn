<?php
/**
 * Screen for displaying current roles, adding/editing/deleting them
 *
 * @author Maurent Software ${date.year}
 */

class MrntAsscnConfig {

	var $page, $screen_title, $url, $table, $role, $maxranking;
	
	function __construct(){
		global $wpdb;

		$this->url = 'users.php?page=mrnt_asscn_roles';
		$this->table = $wpdb->prefix . 'mrnt_asscn_roles';
		$this->role = array_fill_keys($wpdb->get_col( "DESC " . $this->table, 0 ), '');
		$this->maxranking = $wpdb->get_row( "SELECT ranking, COUNT(ranking) AS count FROM " . $this->table." GROUP BY ranking ORDER BY ranking DESC" );
		add_action('admin_menu', array($this, 'add_menu_entry'));
	}

	function preload() {
		global $pagenow;
		
		$this->url = $pagenow . '?page=mrnt_asscn_roles';
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
		
		if ($action == 'create' || $action == 'save' || $action == 'delete') {
			$this->$action();
		}
	}
	
	function index() {
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

		$this->_header();
		
		if (method_exists($this, $action)) {
			$this->$action();
		}
		
		$this->_footer();
	}
	
	/**
	 * Add a menu entry under Users unless Paid Memberships Pro is present, in which case we will hook in there.
	 */
	function add_menu_entry() {
		if (defined('PMPRO_DIR')) {
			$this->page = add_submenu_page('pmpro-membershiplevels', 'Member Roles', 'Member Roles', 'activate_plugins', 'mrnt_asscn_roles', array($this, 'index'));
		} else {
			$this->page = add_submenu_page('users.php', 'Association Roles', 'Association Roles', 'activate_plugins', 'mrnt_asscn_roles', array($this, 'index'));
		}
		add_action('load-'.$this->page, array($this, 'preload'));
		add_action($this->page, array($this, 'index'));
	}
	
	/**
	 * Form to add new role
	 */
	function add() {
		$data = $this->role;
		?>
		<h3>Add New Role.</h3>
		<p>Create a brand new organizational role and add it to the site. After you have created the role, you
		may want to <a href="">click here</a> to assign users to the new role.</p>
		<form action="" method="post" name="editrole" id="editrole" class="add:mrnt_assoc_role: validate">
			<input name="action" type="hidden" value="create">
			<?php wp_nonce_field( 'create-role', '_wpnonce_create-role' ); ?>
			<?php
			$this->_display_form($data);
			?>
			<p class="submit">
				<input type="submit" name="createrole" id="createrolesub" class="button-primary" value="Add New Role ">
				<a href="<?php echo $this->url?>" class="button"><?php _e('Cancel'); ?></a>
			</p>
		</form>

		<div style="margin: 25px 0 25px 0; height: 1px; line-height: 1px; background: #CCCCCC;"></div>
		<?php
	}

	/**
	 * Create the newly added role
	 */
	function create() {
		global $wpdb;

		check_admin_referer( 'create-role', '_wpnonce_create-role' );
		if ( ! current_user_can('promote_user') ) {
			wp_die(__('Cheatin&#8217; uh?'));
		}

		if ($this->_validate(true)) {
			$wpdb->insert( $this->table, $this->role );
			wp_redirect( $this->url );
			die();
		} else {
			$_REQUEST['action'] = 'add';
		}
	}
	
	/**
	 * Form to edit existing role
	 */
	function edit() {
		global $wpdb;
		
		$id = isset($_GET['id']) ? $_GET['id'] : '';
		if (!isset($_REQUEST['title'])) {
			$role = array_merge($this->role, $wpdb->get_row("SELECT * FROM $this->table WHERE id=$id", ARRAY_A));
		} else {
			$role = $this->role;
		}
		?>
		<h3>Edit Role</h3>
		<form action="" method="post" name="editrole" id="editrole" class="add:mrnt_assoc_role: validate">
			<input name="action" type="hidden" value="save">
			<?php wp_nonce_field( 'edit-role', '_wpnonce_edit-role' ); ?>
			<?php
			$this->_display_form($role);
			?>
			<p class="submit">
				<input type="submit" name="saverole" id="editrolesub" class="button-primary" value="Save Role ">
				<a href="<?php echo $this->url?>" class="button"><?php _e('Cancel'); ?></a>
			</p>
		</form>

		<div style="margin: 25px 0 25px 0; height: 1px; line-height: 1px; background: #CCCCCC;"></div>
		<?php
	}
	
	/**
	 * Save changes to edited role
	 */
	function save() {
		global $wpdb;

		check_admin_referer( 'edit-role', '_wpnonce_edit-role' );
		if ($this->_validate()) {
			$wpdb->update( $this->table, $this->role, array('id'=>$this->role['id']) );
			wp_redirect( $this->url );
			die();
		} else {
			$_REQUEST['action'] = 'edit';
		}
	}
	
	/**
	 * Deleting a role
	 */
	function delete() {
		global $wpdb;

		if (isset($_REQUEST['id']) && $id = (int)$_REQUEST['id']) {
			$wpdb->query( "DELETE FROM {$this->table} WHERE id=$id");
			wp_redirect( $this->url );
			die();
		}
	}

	/**
	 * Validate input for new role or edits to existing role
	 */
	function _validate($create = false) {
		$this->role = shortcode_atts($this->role, $_REQUEST);
		foreach($this->role as $key => &$val) {
			$val = trim($val);
		}
		if ($this->role['title']) {
			return true;
		}
		return false;
	}
	
	/**
	 * Output page header
	 */
	function _header() {
		?>
		<div class="wrap">
			
			<div id="icon-users" class="icon32"><br/></div>
			<h2><?php echo get_admin_page_title();?><a href="<?php echo $this->url.'&action=add'; ?>" class="add-new-h2">Add New</a></h2>
			<p>Use this page to manage functional roles for your association or organization.</p>
			<p>Note these roles are separate from user roles which allow you to control what kind of access users have to the site.
			This page allows you to set up roles or positions for members within your organization such as
			'President', 'Secretary', 'Treasurer', etc and assign them some kind of hierachy -
			e.g. 'President' would typically be the most senior position, with 'Secretary' being slightly lower.</p>
			<p>You can then use shortcodes on your pages to list out members who hold office in the organization 
			grouped by role (e.g. 'Finance Committee').</p>

		<?php
	}
	
	/**
	 * Output page footer including table of existing roles
	 */
	function _footer() {
		//Create an instance of our role table class...
		$roleTable = new MrntAsscnRoleTable();
		//Fetch, prepare, sort, and filter our data...
		$roleTable->prepare_items();
		?>
			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form id="movies-filter" method="get">
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<!-- Now we can render the completed list table -->
				<?php $roleTable->display(); ?>
			</form>
			
		</div>
		<?php
	}
	
	/**
	 * Common form for adding/editing role
	 */
	function _display_form($role) {
		extract($role);
		if ($this->maxranking) {
			$maxranking = $this->maxranking->ranking + ($this->maxranking->ranking==$ranking && $this->maxranking->count>1 ? 1 : 0);  
		} else {
			$maxranking = 1;
		}
		?>
		<table class="form-table">
			<tr class="form-required">
				<th scope="row"><label for="user_login">Role Title <span class="description">(required)</span></label></th>
				<td><input name="title" type="text" id="title" value="<?php echo $title; ?>" aria-required="true" class="regular-text"></td>
			</tr>
			<tr class="form-field">
				<th scope="row"><label for="subrole">Sub-Role <span class="description">(optional)</span></label></th>
				<td><input name="subrole" type="text" id="subrole" value="<?php echo $subrole;?>"></td>
			</tr>
			<tr class="form-field">
				<th scope="row"><label for="description">Description <span class="description"></span></label></th>
				<td><input name="description" type="text" id="description" value="<?php echo $description;?>" aria-required="true"></td>
			</tr>
			<tr class="form-field">
				<th scope="row"><label for="role">Seniority</label></th>
				<td><select name="ranking" id="ranking">
				<?php for($i = 1; $i <= $maxranking; $i++):?>
					<option <?php echo ($ranking==$i?'selected="selected"':'')?>><?php echo $i;?></option>
				<?php endfor;?>
				</select>
				</td>
			</tr>
		</table>
		<?php
	}
		
}

/**
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 *	Object to display/manage organizational roles as a table
 */
class MrntAsscnRoleTable extends WP_List_Table {
	
	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	function __construct(){
		global $status, $page;
				
		//Set parent defaults
		parent::__construct( array(
			'singular'	=> 'role',		//singular name of the listed records
			'plural'	=> 'roles',		//plural name of the listed records
			'ajax'		=> false		//does this table support ajax?
		) );
	}
	
	/** ************************************************************************
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title()
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as
	 * possible.
	 *
	 * Since we have defined a column_title() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 *
	 * For more detailed insight into how columns are handled, take a look at
	 * WP_List_Table::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 * @param array $column_name The name/slug of the column to be processed
	 * @return string Text or HTML to be placed inside the column <td>
	 **************************************************************************/
	function column_default($item, $column_name){
		switch($column_name){
			case 'ranking':
			case 'description':
			case 'subrole':
				return $item[$column_name];
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}
		
	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_title($item){
		
		//Build row actions
		$actions = array(
			'edit'	  => sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>',$_REQUEST['page'],'edit',$item['id']),
			'delete'	=> sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
		);
		
		//Return the title contents
		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			/*$1%s*/ $item['title'],
			/*$2%s*/ $item['id'],
			/*$3%s*/ $this->row_actions($actions)
		);
	}
	
	/** ************************************************************************
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item['id']				//The value of the checkbox should be the record's id
		);
	}
	
	/** ************************************************************************
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_columns(){
		$columns = array(
			'cb'			=> '<input type="checkbox" />', //Render a checkbox instead of text
			'title'			=> 'Title',
			'subrole'		=> 'Sub-Role',
			'description'	=> 'Description',
			'ranking'		=> 'Seniority',
		);
		return $columns;
	}
	
	/** ************************************************************************
	 * Register our list of sortable columns.
	 * This should return an array where the key is the column that needs to be
	 * sortable, and the value is db column to sort by. Often, the key and value
	 * will be the same, but this is not always the case (as the value is a
	 * column name from the database, not the list table).
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 **************************************************************************/
	function get_sortable_columns() {
		$sortable_columns = array(
			'title'			=> array('title',false),	 //true means it's already sorted
			'subrole'		=> array('subrole',false),
			'description'	=> array('description',false),
			'ranking'		=> array('ranking',false),
		);
		return $sortable_columns;
	}
	
	/** ************************************************************************
	 * Provide list of bulk actions we support.
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_bulk_actions() {
		$actions = array(
			'delete'	=> 'Delete'
		);
		return $actions;
	}
	
	/** ************************************************************************
	 * Handle our bulk actions.
	 *
	 * @see $this->prepare_items()
	 **************************************************************************/
	function process_bulk_action() {
		
		//Detect when a bulk action is being triggered...
		if( 'delete'===$this->current_action() ) {
			global $wpdb;

			foreach($_GET['role'] as $id) {
				$wpdb->query($wpdb->prepare( "DELETE FROM {$wpdb->prefix}mrnt_asscn_roles WHERE id=%d", $id));
			}
		}
		
	}
	
	/** ************************************************************************
	 * Prepare data for display.
	 * At a minimum, we should set $this->items and $this->set_pagination_args(),
	 * although the following properties and methods are frequently interacted with here...
	 *
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	function prepare_items() {
		global $wpdb; //This is used only if making any database queries

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 5;
		
		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();
		
		/**
		 * Fetch data
		 */
		$table = $wpdb->prefix . 'mrnt_asscn_roles';
		$data = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);
		
		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 */
		function usort_reorder($a,$b){
			$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ranking'; //If no sort, default to title
			$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
			$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
			return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
		}
		usort($data, 'usort_reorder');
		
		/**
		 * REQUIRED for pagination.
		 */
		$current_page = $this->get_pagenum();
		
		/**
		 * REQUIRED for pagination.
		 */
		$total_items = count($data);
		
		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
		
		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;
		
		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items'	=> $total_items,				//WE have to calculate the total number of items
			'per_page'		=> $per_page,					//WE have to determine how many items to show on a page
			'total_pages'	=> ceil($total_items/$per_page) //WE have to calculate the total number of pages
		) );
	}
}
