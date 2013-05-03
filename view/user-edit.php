<?php 
if (!defined('PLUGINDIR')) die;

/**
 * View for adding our fields to the edit current user screen.
 * Uses javascript to try to insert fields in existing sections.  
 *
 * @author Maurent Software
 */

	$lastsection = '';	
	foreach($this->fieldsObject->fields as $field => $params) {
		if (!isset($params['adminonly']) || current_user_can('edit_users')) {
			if ($user) {
				switch($params['type']) {
					case 'checkboxes':
					case 'member_role':
						$val = get_user_meta( $user->ID, $field, false );
						break;
					default:
						$val = get_user_meta( $user->ID, $field, true );
						$val = esc_attr( $val );
				}
			} else {
				$val = '';
			}
			if ($params['type'] == 'checkboxes') {
				$val = (array)$val;
			}
			// set up JavaScript to move the field if necessary
			if ($params['section'] == 'name' || $params['section'] == 'contact' || $params['section'] == 'about') {
				if (isset($params['insert']) && $params['insert']) {
					if ($params['insert']['action']=='end') {
						$this->js .= "jQuery('input#$field').closest('tr').remove().first().appendTo('table:has(#{$params['insert']['field']})');\n";
					} else {
						$this->js .= "jQuery('#{$params['insert']['field']}').closest('tr').".($params['insert']['action']=='after'?'after':'before')."(jQuery('#$field').closest('tr'));\n";
					}
				}
			}
			?>
	
			<?php if ($params['section'] != $lastsection):?>
				<?php if ($lastsection):?>
				</table>
				<?php endif;?>
				<h3><?php echo $this->fieldsObject->sections[$params['section']];?></h3>
				<table class="form-table">
			<?php $lastsection = $params['section']; endif;?>		
	
			<?php if ($params['type'] == 'country'):?>
			<tr>
				<th><label for="<?php echo $field;?>"><?php echo $params['prompt'];?> <span class="description"><?php echo $params['description'];?></span></label></th>
				<td>
					<?php mrnt_asscn_list_countries($field, $val);?>
				</td>
			</tr>

			<?php elseif ($params['type'] == 'member_role'):?>
				<?php if ( current_user_can( 'edit_users' ) ):?>
					<?php $member_roles = mrnt_asscn_get_roles();?>
					<?php $lastrole = '';?>
					<?php if(!count($member_roles)):?>
						<tr><th colspan="2">No member roles have been defined yet.</th></tr>
					<?php else: foreach($member_roles as $member_role): ?>
						<tr><th><?php echo ($lastrole != $member_role['title']?$member_role['title']:'')?></th><td><input type="checkbox" name="mrnt_asscn_roles[<?php echo $member_role['id']; ?>]" <?php echo (in_array($member_role['id'],$val)?'checked="checked"':'') ?>> <?php echo $member_role['subrole'];?></td></tr>
						<?php $lastrole = $member_role['title'];?>
					<?php endforeach; endif; ?>
				<?php endif;?>

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
	}
?>
</table>
<script type="text/javascript">
jQuery(function($){
<?php echo $this->js;?>
	$('h3').each(function(){
		var next = jQuery(this).next();
		if (next.is('table') && !next.find('tr').length) {
			$(this).remove();
			next.remove();
		}
	});
	var select = $('#display_name');

	if ( select.length ) {
		function prep_display(select, inputs) {
			var dub = [];
			if ($('#last_name').val()) {
				inputs['display_greetlastname'] = $('#mrnt_asscn_greeting').val() + ' ' + $('#last_name').val();
				if ($('#first_name').val()) {
					inputs['display_greetfirstlast'] = $('#mrnt_asscn_greeting').val() + ' ' + $('#first_name').val() + ' ' + $('#last_name').val();
					inputs['display_greetlastfirst'] = $('#mrnt_asscn_greeting').val() + ' ' + $('#last_name').val() + ' ' + $('#first_name').val();
				}
				if ($('#mrnt_asscn_title').val()) {
					inputs['display_greetlastnametitle'] = inputs['display_greetlastname'] + ', ' + $('#mrnt_asscn_title').val();
					if ($('#first_name').val()) {
						inputs['display_greetfirstlasttitle'] = inputs['display_greetfirstlast'] + ', ' + $('#mrnt_asscn_title').val();
						inputs['display_greetlastfirsttitle'] = inputs['display_greetlastfirst'] + ', ' + $('#mrnt_asscn_title').val();
						inputs['display_firstlasttitle'] = $('#first_name').val() + ' ' + $('#last_name').val() + ', ' + $('#mrnt_asscn_title').val();
						inputs['display_lastfirsttitle'] = $('#last_name').val() + ' ' + $('#first_name').val() + ', ' + $('#mrnt_asscn_title').val();
						inputs['display_firstlasttitle'] = $('#first_name').val() + ' ' + $('#last_name').val() + ', ' + $('#mrnt_asscn_title').val();
						inputs['display_lastfirsttitle'] = $('#last_name').val() + ' ' + $('#first_name').val() + ', ' + $('#mrnt_asscn_title').val();
					}
				}
			}
						
			$.each( $('option', select), function( i, el ){
				dub.push( el.value );
			});
			
			$.each(inputs, function( id, value ) {
				if ( ! value )
					return;

				var val = value.replace(/<\/?[a-z][^>]*>/gi, '');

				if ( inputs[id].length && $.inArray( val, dub ) == -1 ) {
					dub.push(val);
					$('<option />', {
						'text': val
					}).appendTo( select );
				}
			});
		}

		var inputs = {};
		prep_display(select, inputs);

		$('#mrnt_asscn_greeting, #mrnt_asscn_title, #first_name, #last_name, #nickname').bind( 'blur.user_profile', function() {
			var dub = [],
				inputs = {
					display_nickname  : $('#nickname').val() || '',
					display_username  : $('#user_login').val() || '',
					display_firstname : $('#first_name').val() || '',
					display_lastname  : $('#last_name').val() || '',
					display_greetlastname  : ($('#mrnt_asscn_greeting').val() && $('#last_name').val() ? $('#mrnt_asscn_greeting').val() + ' ' + $('#last_name').val() : '')
			};

			if ( inputs.display_firstname && inputs.display_lastname ) {
				inputs['display_firstlast'] = inputs.display_firstname + ' ' + inputs.display_lastname;
				inputs['display_lastfirst'] = inputs.display_lastname + ' ' + inputs.display_firstname;
			}

			prep_display(select, inputs);
		});
	}
	
});
</script>
