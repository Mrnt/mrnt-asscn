<?php
/**
 * General helper functions
 *  
 * @author Maurent Software ${date.year}
 */

/**
 * Fetch an array of country names
 */
function mrnt_asscn_get_countries() {
	global $wpdb;

	return $wpdb->get_col("SELECT name FROM " . $wpdb->prefix . "mrnt_asscn_country");
}

/**
 * Make drop list of country names with supplied value selected.
 * Allows current value to be shown even if it's not in our country list.
 */
function mrnt_asscn_list_countries($field, $curr = null) {
	$countries = mrnt_asscn_get_countries();
	if ($curr && !in_array(strtolower($curr), array_map('strtolower', $countries))) {
		$curr = ucwords(strtolower($curr));
		array_unshift($countries, $curr);
		if (class_exists('en_US')) {
			$collate = new Collator();
			$collate->sort($countries);
		}
	}
	?>	
	<select name="<?php echo $field;?>" id="<?php echo $field;?>">
		<?php foreach($countries as $country):?>
		<option <?php echo ($country==$curr ? 'selected="selected"' : '')?>><?php echo $country?></option>
		<?php endforeach;?>
	</select>
	<?php
}

/**
 * Fetch an array of member roles
 */
function mrnt_asscn_get_roles() {
	global $wpdb;

	return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mrnt_asscn_roles ORDER BY ranking", ARRAY_A);
}