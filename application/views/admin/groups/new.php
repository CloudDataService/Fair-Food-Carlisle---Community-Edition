<h2>New Group</h2>
<p>Create a new group and set the buying advocate. This will send an e-mail asking them to enter in the details and start promoting Fair Food Carlisle in their group.</p>


<?php echo form_open(current_url(), array('id' => 'new_group_form')); ?>

	<table class="form">
		<tr>
			<td colspan="2">About the buying advocate (person)</td>
		</tr>
		<tr>
			<th><label for="u_title">Title</label></th>
			<td>
				<select name="u_title" id="u_title" class="other">
					<option value="">-- Please select --</option>
					<?php 
					foreach($this->config->config['titles'] as $title)
					{
						echo '<option value="' . $title . '" ' . ($title == @$user['u_title'] ? 'selected="selected"' : '') . '>' . $title . '</option>';
					} 
					?>                
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="u_fname">First Name</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="u_fname" id="u_fname" maxlength="30" />
			</td>
		</tr>
		<tr>
			<th><label for="u_sname">Last Name</label> </th>
			<td>
				<input type="text" name="u_sname" id="u_sname" maxlength="30" />
			</td>
		</tr>
		<tr>
			<th><label for="u_email">E-mail Address</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="u_email" id="u_email" maxlength="80" />
			</td>
		</tr>
		
		
		<tr>
			<td colspan="2" style="border-top: dashed 1px #000;">About the buying group</td>
		</tr>
	
		
		<tr>
			<th><label for="bg_name">Buying Group Name</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="bg_name" id="bg_name" maxlength="80" />
			</td>
		</tr>
		<tr>
			<th><label for="bg_deliveryday">Delivery Day of Week</label></th>
			<td>
				<select name="bg_deliveryday" id="bg_deliveryday" class="other">
					<option value="Tuesday">Tuesday</option>             
				</select>
			</td>
		</tr>
		
				
		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="btn" value="Create Group" /></td>
		</tr>
		
	</table>

<?php echo form_close(); ?>