<?php
if (!defined('PLUGINDIR')) die;
 
/**
 * View for adding the our user fields to the new user screen
 *
 * @author Maurent Software
 */

	$lastsection = '';	
	foreach($this->fieldsObject->fields as $field => $params) {
		switch($params['type']) {
			case 'array':
			case 'member_role':
				$val = (array)$val;
				break;
			default:
				$val = '';
		}
		// get js to move this field if necessary
		if ($params['insert']) {
			$this->js .= "jQuery('#{$params['insert']['field']}').parent().parent().".($params['insert']['action']=='after'?'after':'before')."(jQuery('#$field').parent().parent());\n";
		}
		?>

		<?php if ($params['section'] != $lastsection):?>
		<tr>
			<th colspan="2"><h3><?php echo $this->fieldsObject->sections[$params['section']];?></h3></th>
		</tr>
		<?php $lastsection = $params['section']; endif;?>		

		<?php if ($params['type'] == 'country'):?>
		<tr>
			<th><label for="<?php echo $field;?>"><?php echo $params['prompt'];?> <span class="description"><?php echo $params['description'];?></span></label></th>
			<td>
				<?php mrnt_asscn_list_countries($field);?>
			</td>
		</tr>

		<?php elseif ($params['type'] == 'member_role'):?>
			<?php $member_roles = mrnt_asscn_get_roles();?>
			<?php $lastrole = '';?>
			<?php foreach($member_roles as $member_role): ?>
				<tr><th><?php echo ($lastrole != $member_role['title']?$member_role['title']:'')?></th><td><input type="checkbox" name="mrnt_asscn_roles[<?php echo $member_role['id']; ?>]" <?php echo (in_array($member_role['id'],$val)?'checked="checked"':'') ?>> <?php echo $member_role['subrole'];?></td></tr>
				<?php $lastrole = $member_role['title'];?>
			<?php endforeach; ?>

		<?php elseif ($params['type'] == 'select'):?>
		<tr>
			<th><label for="<?php echo $field;?>"><?php echo $params['prompt'];?> <span class="description"><?php echo $params['description'];?></span></label></th>
			<td>
				<select name="<?php echo $field;?>" id="<?php echo $field;?>">
					<?php foreach($params['options'] as $option):?>
					<option <?php echo ($option==$val ? 'selected="selected"' : '')?>><?php echo $option?></option>
					<?php endforeach;?>
				</select>
			</td>
		</tr>

		<?php else:?>
		<tr>
			<th><label for="<?php echo $field;?>"><?php echo $params['prompt'];?> <span class="description"><?php echo $params['description'];?></span></label></th>
			<td>
				<input type="text" name="<?php echo $field;?>" id="<?php echo $field;?>" value="<?php echo $val;?>" class="regular-text" />
				<br />
			</td>
		</tr>

		<?php endif;?>
		<?php
	}
?>
	