<div class="row marketing">
<?php
if(!$login->Access()) {
	echo "<div class='alert alert-danger'>{$lang->_('NO_PERMISSIONS')}</div>";
}elseif(isset($_GET['add'])) {
	$username = "";
	$email = "";
	$theme = "light";
	$servers = $config['default_max_servers'];
	$admin = "";
	if(isset($_POST['submit'])) {
		$username = $_POST['username'];
		$email = $_POST['email'];
		$theme = $_POST['theme'];
		$servers = $_POST['servers'];
		if(isset($_POST['admin'])) { $admin = "1"; }else{ $admin = "0"; }

		try {
			$login->AddUser($username, $email, $_POST['password'], $_POST['r_password'], $theme, $servers, $admin);
			echo "<div class='alert alert-success'>{$lang->_('USER_ADDED')}<br /><b>{$lang->_('USERNAME')}:</b> ".html($_POST['username'])."<br /><b>{$lang->_('PASSWORD')}:</b> ".html($_POST['password'])."</div>";
		}
		catch(LoginError $e) {
			echo "<div class='alert alert-danger'>{$e->getMessage()}</div>";
		}
	}
?>
<form method="POST">
	<div class="form-group">
		<label for="username"><?php echo $lang->_('USERNAME'); ?></label>
		<input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>" required>
	</div>
	
	<div class="form-group">
		<label for="email"><?php echo $lang->_('EMAIL'); ?></label>
		<input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
	</div>
	
	<div class="form-group">
		<label for="password"><?php echo $lang->_('PASSWORD'); ?></label>
		<input type="password" class="form-control" id="password" name="password" placeholder="" required>
	</div>
	
	<div class="form-group">
		<label for="r_password"><?php echo $lang->_('REPEATPASSWORD'); ?></label>
		<input type="password" class="form-control" id="r_password" name="r_password" placeholder="" required>
	</div>
	
	<div class="form-group">
		<label for="theme"><?php echo $lang->_('THEME'); ?></label>
		<select name="theme" id="theme" class="form-control">
			<option value="light" <?php if($theme == "light") { echo "selected"; } ?>><?php echo $lang->_('THEME_LIGHT'); ?></option>
			<option value="dark" <?php if($theme == "dark") { echo "selected"; } ?>><?php echo $lang->_('THEME_DARK'); ?></option>
		</select>
	</div>
	
	<div class="form-group">
		<label for="servers"><?php echo $lang->_('MAXIMUM_SERVERS'); ?></label>
		<input type="number" min="0" max="50" class="form-control" id="servers" name="servers" value="<?php echo $servers; ?>" required>
		<p class="help-block">0 = <?php echo $lang->_('UNLIMITED'); ?></p>
	</div>
	
	<div class="checkbox">
		<label>
			<input type="checkbox" name="admin" value="1" <?php if($admin == "1") { echo "checked"; } ?>> <?php echo $lang->_('ADMIN'); ?>
		</label>
	</div>
	<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('SAVE'); ?></button>
</form>
<?php
}elseif(isset($_GET['edit'])) {
	$id = $db->real_escape_string($_GET['edit']);
	$check_user = $db->query("SELECT * FROM users WHERE id='{$id}'");
	$row = $check_user->fetch_assoc();
	if($check_user->num_rows == 0){
		echo "<div class='alert alert-danger'>{$lang->_('USER_NOT_FOUND')}</div>";	
	}else{
		$email = $row['email'];
		$chart1 = $row['chart_1'];
		$chart2 = $row['chart_2'];
		$servers = $row['max_servers'];
		$admin = $row['admin'];
		$theme = $row['theme'];
		$language = $row['language'];
		if(isset($_POST['submit'])) {
			$email = $_POST['email'];
			$chart1 = $_POST['chart1'];
			$chart2 = $_POST['chart2'];
			$servers = $_POST['servers'];
			$theme = $_POST['theme'];
			$language = $_POST['language'];
			if(isset($_POST['admin'])) { $admin = "1"; }else{ $admin = "0"; }
			
			try {
				$new_password = "";
				$login->EditUser($id, $email, $_POST['password'], $_POST['r_password'], $chart1, $chart2, $theme, $language, $admin, $servers);
				if(!empty($_POST['password'])) {
					$new_password = "<br /><b>{$lang->_('NEW_PASSWORD')}:</b> ".$_POST['password'];
				}
				echo "<div class='alert alert-success'>{$lang->_('USER_EDITED')}<br />{$new_password}</div>";
			}
			catch(LoginError $e) {
				echo "<div class='alert alert-danger'>{$e->getMessage()}</div>";
			}
		}
?>
<form method="POST">
	<div class="form-group">
		<label for="username"><?php echo $lang->_('USERNAME'); ?></label>
		<input type="text" class="form-control" id="username" value="<?php echo $row['username']; ?>" readonly>
	</div>
	
	<div class="form-group">
		<label for="email"><?php echo $lang->_('EMAIL'); ?></label>
		<input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
	</div>

	<div class="form-group">
		<label for="chart1"><?php echo $lang->_('DASHBOARDCHART'); ?> 1</label>
		<select name="chart1" id="chart1" class="form-control">
			<?php
				echo '<option '.(($chart1 == "0") ? "selected" : "").' value="0">'.$lang->_('DISABLED').'</option>';
				$sql = $db->query("SELECT id, display_name, state FROM servers WHERE member_id='{$row['id']}' AND disabled='0' AND deleted='0'");
				while($row = $sql->fetch_assoc()) {
					echo '<option '.(($chart1 == $row['id']) ? "selected" : "").' value="'.$row['id'].'">'.$row['display_name'].' ('.$row['state'].')</option>';
				}
			?>
		</select>
	</div>
	
	<div class="form-group">
		<label for="chart2"><?php echo $lang->_('DASHBOARDCHART'); ?> 2</label>
		<select name="chart2" id="chart2" class="form-control">
			<?php
				echo '<option '.(($chart2 == "0") ? "selected" : "").' value="0">'.$lang->_('DISABLED').'</option>';
				$sql = $db->query("SELECT id, display_name, state FROM servers WHERE member_id='{$row['id']}' AND disabled='0' AND deleted='0'");
				while($row = $sql->fetch_assoc()) {
					echo '<option '.(($chart2 == $row['id']) ? "selected" : "").' value="'.$row['id'].'">'.$row['display_name'].' ('.$row['state'].')</option>';
				}
			?>
		</select>
	</div>
	
	<div class="form-group">
		<label for="password"><?php echo $lang->_('PASSWORD'); ?></label>
		<input type="password" class="form-control" id="password" name="password" placeholder="<?php echo $lang->_('PASSWORD_LEAVE_BLANK'); ?>">
	</div>
	
	<div class="form-group">
		<label for="r_password"><?php echo $lang->_('REPEATPASSWORD'); ?></label>
		<input type="password" class="form-control" id="r_password" name="r_password" placeholder="<?php echo $lang->_('PASSWORD_LEAVE_BLANK'); ?>">
	</div>

	<div class="form-group">
		<label for="language"><?php echo $lang->_('LANGUAGE'); ?></label>
		<select name="language" id="language" class="form-control">
			<?php
				echo '<option '.(($language == "") ? "selected" : "").' value="default">'.$lang->_('LANGUAGE_SYSTEM_DEFAULT').'</option>';
				foreach (glob("includes/languages/*.txt") as $filename) {
					$file = file_get_contents($filename);
					preg_match('/## Language name: ([A-Za-z0-9]+)/', $file, $name);
					
					$code = str_replace(array("includes/languages/", ".txt"), "", $filename);
					echo '<option '.(($language == $code) ? "selected" : "").' value="'.$code.'">'.$name[1].' ('.$code.')</option>';
					
				}
			?>
		</select>
	</div>

	<div class="form-group">
		<label for="theme"><?php echo $lang->_('THEME'); ?></label>
		<select name="theme" id="theme" class="form-control">
			<option value="light" <?php if($theme == "light") { echo "selected"; } ?>><?php echo $lang->_('THEME_LIGHT'); ?></option>
			<option value="dark" <?php if($theme == "dark") { echo "selected"; } ?>><?php echo $lang->_('THEME_DARK'); ?></option>
		</select>
	</div>
	
	<div class="form-group">
		<label for="servers"><?php echo $lang->_('MAXIMUM_SERVERS'); ?></label>
		<input type="number" min="0" max="50" class="form-control" id="servers" name="servers" value="<?php echo $servers; ?>" required>
		<p class="help-block">0 = <?php echo $lang->_('UNLIMITED'); ?></p>
	</div>
	
	<div class="checkbox">
		<label>
			<input type="checkbox" name="admin" value="1" <?php if($admin == "1") { echo "checked"; } ?>> <?php echo $lang->_('ADMIN'); ?>
		</label>
	</div>
	<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('SAVE'); ?></button>
</form>
<?php
	}
}elseif(isset($_GET['delete'])) {
		$id = $db->real_escape_string($_GET['delete']);
		$sql = $db->query("SELECT * FROM users WHERE id='{$id}'");
		if($sql->num_rows == 0) {
			echo "<div class='alert alert-danger'>{$lang->_('USER_NOT_FOUND')}</div>";
		}else{
			$row = $sql->fetch_assoc();
			if(isset($_POST['submit'])) {
				if(DEMO == 0) {
					$db->query("DELETE FROM users WHERE id='{$id}'");
					$db->query("DELETE FROM pushbullet WHERE member_id='{$id}'");
					$db->query("DELETE FROM sessions WHERE member_id='{$id}'");
					$db->query("UPDATE servers SET deleted='1' WHERE member_id='{$id}'");
				}
				echo "<div class='alert alert-success'>{$lang->_('USER_DELETED')}</div>";
				echo $function->Redirect("index.php?p=admin/users");
			}
?>
		<form method="POST">
			<?php echo $lang->_('USER_DELETE_CONFIRM', array("%username%" => $row['username'])); ?>
			<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('YES'); ?></button>
			<button type="button" class="btn btn-danger"  onclick="location.href='index.php?p=admin/users'"><?php echo $lang->_('NO'); ?></button>
		</form>
<?php
	}
}else{
	$sql = $db->query("SELECT * FROM users");
?>
		<p class="pull-right">
			<button type="button" class="btn btn-primary" onclick="location.href='index.php?p=admin/users&add'"><i class="fa fa-plus"></i> <?php echo $lang->_('ADD'); ?></button>
		</p>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>ID</th>
					<th><?php echo $lang->_('USERNAME'); ?></th>
					<th><?php echo $lang->_('EMAIL'); ?></th>
					<th><?php echo $lang->_('LIMIT'); ?></th>
					<th><?php echo $lang->_('LAST_SIGNIN'); ?></th>
					<th><?php echo $lang->_('ADMIN'); ?></th>
					<th><?php echo $lang->_('OPTIONS'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php 
					while($row = $sql->fetch_assoc()) {
						$servers = $db->query("SELECT * FROM servers WHERE member_id='{$row['id']}'")->num_rows;
				?>
					<tr>
						<td><?php echo $row['id']; ?></td>
						<td><?php echo $row['username']; ?></td>
						<td><?php echo $row['email']; ?></td>
						<td><?php echo (($row['max_servers'] == "0") ? "&infin;" : (($servers >= $row['max_servers']) ? "<font color='red'>{$servers}/{$row['max_servers']}</font>" : "<font color='green'>{$servers}/{$row['max_servers']}</font>")); ?></td>
						<td><?php echo (($row['last_signin'] == "0000-00-00 00:00:00") ? $lang->_('NEVER') : date($config['date_format'] . " " . $config['time_format'], strtotime($row['last_signin']))); ?></td>
						<td><?php echo (($row['admin'] == 1) ? "Yes" : "No"); ?></td>
						<td>
							<span style="cursor: pointer;" onclick="location.href='index.php?p=admin/users&edit=<?php echo $row['id']; ?>'" class="label label-info" <?php echo $function->Tooltip($lang->_('EDIT')); ?>><span class="fa fa-pencil"></span></span>
							<span style="cursor: pointer;" onclick="location.href='index.php?p=admin/users&delete=<?php echo $row['id']; ?>'" class="label label-danger" <?php echo $function->Tooltip($lang->_('DELETE')); ?>><span class="fa fa-trash"></span></span>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
<?php } ?>
</div>