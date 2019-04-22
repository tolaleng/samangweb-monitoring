<div class="row marketing">
<?php
$blacklist = array("504", "503", "0");
if(!$login->Access()) {
	echo "<div class='alert alert-danger'>{$lang->_('NO_PERMISSIONS')}</div>";
}else{
	if(isset($_GET['add'])) {
		$code = "";
		if(isset($_POST['submit']) && isset($_POST['code'])) {
			$id = $db->real_escape_string(html($_POST['code']));
			$code = $id;
			$sql = $db->query("SELECT * FROM response_codes WHERE code='{$id}'");
			
			if(empty($_POST['code'])) {
				echo "<div class='alert alert-danger'>{$lang->_('FIELDSREQUIRED')}</div>";
			} else if(preg_match("/^[0-9]+$/", $_POST['code']) == 0) {
				echo "<div class='alert alert-danger'>{$lang->_('RESPONSE_CODE_ADD_ERROR_INTEGER')}</div>";
			} else if(strlen($_POST['code']) != 3) {
				echo "<div class='alert alert-danger'>{$lang->_('RESPONSE_CODE_ADD_ERROR_NUMBERS')}</div>";
			} else if(in_array($_POST['code'], $blacklist)) {
				echo "<div class='alert alert-danger'>{$lang->_('RESPONSE_CODE_ADD_ERROR_BLACKLIST')}</div>";
			} else if($sql->num_rows != 0) {
				echo "<div class='alert alert-danger'>{$lang->_('RESPONSE_CODE_ADD_ERROR_EXIST')}</div>";
			} else {
				if(DEMO == 0) { 
					$db->query("INSERT INTO response_codes (code) VALUES ('{$id}')");
					$db->query("UPDATE server_stats SET state='active' WHERE response_code='{$id}'");
				}
				echo "<div class='alert alert-success'>{$lang->_('RESPONSE_CODE_ADDED')}</div>";
				echo $function->Redirect("index.php?p=admin/response_code");
			}
		}
?>
<div class="alert alert-info"><?php echo $lang->_('RESPONSE_CODE_NOTE'); ?></div>
<form method="POST">
	<div class="form-group">
		<label for="code"><?php echo $lang->_('RESPONSE_CODE'); ?>:</label>
		<input type="text" class="form-control" id="code" name="code" value="<?php echo $code; ?>" maxlength="3" required>
	</div>
	<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('SAVE'); ?></button>
</form>
<?php		
	} else if(isset($_GET['delete'])) {
			$id = $db->real_escape_string($_GET['delete']);
			$sql = $db->query("SELECT * FROM response_codes WHERE code='{$id}'");
			if($sql->num_rows == 0) {
				echo "<div class='alert alert-danger'>{$lang->_('RESPONSE_CODE_ADD_ERROR_NOT_FOUND')}</div>";
			}else{
				$row = $sql->fetch_assoc();
				if(isset($_POST['submit'])) {
					if(DEMO == 0) { 
						$db->query("UPDATE server_stats SET state='down' WHERE response_code='{$row['code']}'");
						$db->query("DELETE FROM response_codes WHERE code='{$id}'");
					}
					echo $function->Redirect("index.php?p=admin/response_code", 3);
					echo "<div class='alert alert-success'>{$lang->_('RESPONSE_CODE_DELETED', array("%id%" => $id))}</div>";
				}
	?>
		<form method="POST">
			<?php echo $lang->_('RESPONSE_CODE_DELETE', array("%id%" => $row['code'])); ?>
			<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('YES'); ?></button>
			<button type="button" class="btn btn-danger"  onclick="location.href='index.php?p=admin/response_code'"><?php echo $lang->_('NO'); ?></button>
		</form>
	<?php
			}
	}else{
?>
<p class="pull-right"><button type="button" class="btn btn-primary" onclick="location.href='index.php?p=admin/response_code&add'"><i class="fa fa-plus"></i> <?php echo $lang->_('ADD'); ?></button></p>
<?php echo $lang->_('RESPONSE_CODE_INFORMATION'); ?><br /><br />
<table class="table table-striped">
	<thead>
		<tr>
			<th><?php echo $lang->_('RESPONSE_CODE'); ?></th>
			<th><?php echo $lang->_('INFORMATION'); ?></th>
			<th><?php echo $lang->_('OPTIONS'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			$sql = $db->query("SELECT * FROM response_codes ORDER BY code ASC");
			while($row = $sql->fetch_assoc()) {
		?>
			<tr>
				<td><?php echo $row['code']; ?></td>
				<td><?php echo $server->ResponseName($row['code']); ?></td>
				<td><span style="cursor: pointer;" onclick="location.href='index.php?p=admin/response_code&delete=<?php echo $row['code']; ?>'" class="label label-danger" <?php echo $function->Tooltip($lang->_('DELETE')); ?>><span class="fa fa-trash"></span></span></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php
	}
}
?>
</div>