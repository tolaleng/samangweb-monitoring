<?php
$check_user = $db->query("SELECT * FROM users WHERE id='{$login->id}'");
$row = $check_user->fetch_assoc();
$email = $row['email'];
$chart1 = $row['chart_1'];
$chart2 = $row['chart_2'];
$theme = $row['theme'];
$language = $row['language'];
if(isset($_POST['submit'])) {
	$email = $_POST['email'];
	$chart1 = $_POST['chart1'];
	$chart2 = $_POST['chart2'];
	$theme = $_POST['theme'];
	$language = $_POST['language'];
	try {
		$login->EditUser($login->id, $email, $_POST['password'], $_POST['r_password'], $chart1, $chart2, $theme, $language, $row['admin'], $row['max_servers']);
		echo "<div class='alert alert-success'>{$lang->_('USER_ACCOUNT_SAVED')}</div>";
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
				$sql = $db->query("SELECT id, display_name, state FROM servers WHERE member_id='{$login->id}' AND disabled='0' AND deleted='0'");
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
				$sql = $db->query("SELECT id, display_name, state FROM servers WHERE member_id='{$login->id}' AND disabled='0' AND deleted='0'");
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
	
	<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('SAVE'); ?></button>
	<br />
	<br />
</form>