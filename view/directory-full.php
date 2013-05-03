<?php
if (!defined('PLUGINDIR')) die;
 
/**
 * View for adding the our user fields to the new user screen
 *
 * @author Maurent Software
 */
?>
<?php $defmeta = $fieldsObject->get_default_user_meta();?>
<table>
<?php foreach($users as $user): ?>
	<?php $meta = get_user_meta($user->ID, null, false)+$defmeta;?>
	<tr class="mrnt_asscn_member">
		<td class="mrnt_asscn_name">
			<span class="mrnt_asscn_greet"><?php echo $meta['mrnt_asscn_greeting'][0];?></span>
			<span class="mrnt_asscn_first"><?php echo $meta['first_name'][0];?></span>
			<span class="mrnt_asscn_last"><?php echo $meta['last_name'][0];?></span>
		</td>
		<td class="mrnt_asscn_address">
			<?php echo $meta['mrnt_asscn_company'][0].($meta['mrnt_asscn_company'][0]?'<br />':'')?>
			<?php echo $meta['mrnt_asscn_address_1'][0].($meta['mrnt_asscn_address_1'][0]?'<br />':'')?>
			<?php echo $meta['mrnt_asscn_address_2'][0].($meta['mrnt_asscn_address_2'][0]?'<br />':'')?>
			<?php echo $meta['mrnt_asscn_address_3'][0].($meta['mrnt_asscn_address_3'][0]?'<br />':'')?>
			<?php echo $meta['mrnt_asscn_city'][0].($meta['mrnt_asscn_city'][0]?' ':'').$meta['mrnt_asscn_state'][0].($meta['mrnt_asscn_state'][0]?' ':'').$meta['mrnt_asscn_zip'][0].($meta['mrnt_asscn_zip'][0]?' ':'').($meta['mrnt_asscn_city'][0]||$meta['mrnt_asscn_state'][0]||$meta['mrnt_asscn_zip'][0]?'<br />':'')?>
			<?php echo $meta['mrnt_asscn_country'][0]?>
		</td>
		<td class="mrnt_asscn_contact">
			<?php echo $user->user_email?><br />
			<?php echo ($meta['mrnt_asscn_email_2'][0]?$meta['mrnt_asscn_email_2'][0].'<br />':'')?>
			<?php echo ($meta['mrnt_asscn_phone_1'][0]||$meta['mrnt_asscn_phone_2'][0]?'<label>Phone:</label>':'')?>
			<?php echo ($meta['mrnt_asscn_phone_1'][0]?'<span>'.$meta['mrnt_asscn_phone_1'][0].'</span>':'')?>
			<?php echo ($meta['mrnt_asscn_phone_2'][0]?'<span>'.$meta['mrnt_asscn_phone_2'][0].'</span>':'')?>
			<?php echo ($meta['mrnt_asscn_fax'][0]?'<label>Fax:</label><span>'.$meta['mrnt_asscn_fax'][0].'</span>':'')?>
			<?php echo ($user->user_url?$user->user_url:'')?>
			</td>
	</tr>		
<?php endforeach;?>
</table>
