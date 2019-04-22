<?php
if(phpversion() < "5.3") {
	echo "<div class='alert alert-danger'>{$lang->_('CALENDAR_DISABLED')}</div>";
} else {
	$calendar = new Calendar();
?>
<div class="row marketing">
	<?php
	if(isset($_GET['add'])) {
		$server = "";
		$information = "";
		$start_date = "";
		$end_date = "";
		
		if(isset($_POST['save'])) {
			$server = $_POST['server'];
			$information = $_POST['information'];
			$start_date = $_POST['start_date'];
			$end_date = $_POST['end_date'];
			
			try {
				$calendar->Add($server, $information, $start_date, $end_date);
				echo "<div class='alert alert-success'>{$lang->_('CALENDAR_ADDED')}</div>" . $function->Redirect("index.php?p=calendar");
			}
			catch(Exception $e) {
				echo "<div class='alert alert-danger'>{$e->getMessage()}</div>"; 
			}
		}
	?>
		<h2 style="margin-top: -10px;"><?php echo $lang->_("ADD"); ?></h2>
		<form method="post">
				<div class="form-group">
					<label><?php echo $lang->_('SERVER'); ?>:</label>
					<select class="form-control" name="server">
						<?php
							$sql = $db->query("SELECT * FROM servers WHERE member_id='{$login->id}'");
							while($row = $sql->fetch_assoc()) {
								echo "<option value='{$row['id']}' ".(($server == $row['id']) ? "selected" : "").">{$row['display_name']} ({$row['server_url']})</option>";
							}
						?>
					</select>
				</div>
				<div class="form-group">
					<label><?php echo $lang->_('INFORMATION'); ?>:</label>
					<input type="text" class="form-control" name="information" placeholder="<?php echo $lang->_("CALENDAR_INFORMATION_PLACEHOLDER"); ?>" value="<?php echo $information; ?>" required>
				</div>
				<div class="form-group">
					<label><?php echo $lang->_('CALENDAR_START_DATE'); ?>:</label>
					<input type="text" class="form-control" name="start_date" id="start_date" value="<?php echo $start_date; ?>" required>
				</div>
				<div class="form-group">
					<label><?php echo $lang->_('CALENDAR_END_DATE'); ?>:</label>
					<input type="text" class="form-control" name="end_date" id="end_date" value="<?php echo $end_date; ?>" required>
				</div>
				<button type="submit" class="btn btn-success" name="save"><?php echo $lang->_('SAVE'); ?></button>
		</form>
	<?php
	} else if(isset($_GET['edit'])) {
		$id = $db->real_escape_string($_GET['edit']);
		
		$sql = $db->query("SELECT * FROM calendar WHERE id='{$id}' AND member_id='{$login->id}'");
		$row = $sql->fetch_assoc();
		
		if($sql->num_rows == 0){ 
			echo "<div class='alert alert-danger'>{$lang->_('CALENDAR_NOT_FOUND')}</div>";
		} else {
			$server = $row['server_id'];
			$information = $row['information'];
			$start_date = date("Y/m/d H:i", strtotime($row['start_date']));
			$end_date = date("Y/m/d H:i", strtotime($row['end_date']));
			
			if(isset($_POST['save'])) {
				$server = $_POST['server'];
				$information = $_POST['information'];
				$start_date = $_POST['start_date'];
				$end_date = $_POST['end_date'];
				
				try {
					$calendar->Edit($id, $server, $information, $start_date, $end_date);
					echo "<div class='alert alert-success'>{$lang->_('CALENDAR_EDITED')}</div>" . $function->Redirect("index.php?p=calendar");
				}
				catch(Exception $e) {
					echo "<div class='alert alert-danger'>{$e->getMessage()}</div>"; 
				}
			}
	?>
		<h2 style="margin-top: -10px;"><?php echo $lang->_("EDIT"); ?></h2>
		<form method="post">
				<div class="form-group">
					<label><?php echo $lang->_('SERVER'); ?>:</label>
					<select class="form-control" name="server">
						<?php
							$sql = $db->query("SELECT * FROM servers WHERE member_id='{$login->id}'");
							while($row = $sql->fetch_assoc()) {
								echo "<option value='{$row['id']}' ".(($server == $row['id']) ? "selected" : "").">{$row['display_name']} ({$row['server_url']})</option>";
							}
						?>
					</select>
				</div>
				<div class="form-group">
					<label><?php echo $lang->_('INFORMATION'); ?>:</label>
					<input type="text" class="form-control" name="information" placeholder="<?php echo $lang->_("CALENDAR_INFORMATION_PLACEHOLDER"); ?>" value="<?php echo $information; ?>" required>
				</div>
				<div class="form-group">
					<label><?php echo $lang->_('CALENDAR_START_DATE'); ?>:</label>
					<input type="text" class="form-control" name="start_date" id="start_date" value="<?php echo $start_date; ?>" required>
				</div>
				<div class="form-group">
					<label><?php echo $lang->_('CALENDAR_END_DATE'); ?>:</label>
					<input type="text" class="form-control" name="end_date" id="end_date" value="<?php echo $end_date; ?>" required>
				</div>
				<button type="submit" class="btn btn-success" name="save"><?php echo $lang->_('SAVE'); ?></button>
		</form>
	<?php
		}
	} else if(isset($_GET['delete'])) {
			$id = $db->real_escape_string($_GET['delete']);
			$sql = $db->query("SELECT * FROM calendar WHERE id='{$id}' AND member_id='{$login->id}'");

			if($sql->num_rows == 0){ 
				echo "<div class='alert alert-danger'>{$lang->_('CALENDAR_NOT_FOUND')}</div>";
			} else {
				if(isset($_POST['submit'])) {
					if(DEMO == 0) { 
						$db->query("DELETE FROM calendar WHERE id='{$id}'");
					}
					echo $function->Redirect("index.php?p=calendar");
					echo "<div class='alert alert-success'>{$lang->_('CALENDAR_DELETED')}</div>";
				} else {
	?>
		<h2 style="margin-top: -10px;"><?php echo $lang->_("DELETE"); ?></h2>
		<form method="POST">
			<?php echo $lang->_('CALENDAR_DELETE_CONFIRM'); ?><br /><br />
			<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('YES'); ?></button>
			<button type="button" class="btn btn-danger"  onclick="location.href='index.php?p=calendar'"><?php echo $lang->_('NO'); ?></button>
		</form>
	<?php
				}
			}
	} else {
		$sql = $db->query("SELECT * FROM calendar WHERE member_id='{$login->id}' ORDER BY server_id ASC, member_id ASC");
	?>
		<div class="row">
			<div class="col-sm-12 col-md-6">
				<h2 style="margin-top: -15px;"><?php echo $lang->_("CALENDAR"); ?></h2>
			</div>

			<div class="col-sm-12 col-md-6">
				<button type="button" class="btn btn-primary pull-right" onclick="location.href='index.php?p=calendar&add'"><i class="fa fa-plus"></i> <?php echo $lang->_('ADD'); ?></button>
			</div>
		</div>
		
		<?php echo $lang->_("CALENDAR_INFORMATION"); ?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th><?php echo $lang->_("SERVER"); ?></th>
					<th><?php echo $lang->_("INFORMATION"); ?></th>
					<th><?php echo $lang->_("CALENDAR_START_DATE"); ?></th>
					<th><?php echo $lang->_("CALENDAR_END_DATE"); ?></th>
					<th><?php echo $lang->_("STATE"); ?></th>
					<th><?php echo $lang->_("CALENDAR_DATE_ADDED"); ?></th>
					<th><?php echo $lang->_('OPTIONS'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php 
					while($row = $sql->fetch_assoc()) {
						$server = $db->query("SELECT * FROM servers WHERE id='{$row['server_id']}'")->fetch_assoc();
						
						if($row['state'] == "0") {
							$state = "<div class='label label-danger'>{$lang->_('CALENDAR_STATE_WAIT')}</div>";
						} else if($row['state'] == "1") {
							$state = "<div class='label label-warning'>{$lang->_('CALENDAR_STATE_PROGRESS')}</div>";
						} else if($row['state'] == "2") {
							$state = "<div class='label label-success'>{$lang->_('CALENDAR_STATE_END')}</div>";
						} 
				?>
					<tr>
						<td><a href="index.php?p=dashboard&s=<?php echo $server['id']; ?>"><?php echo $server['display_name']; ?></a></td>
						<td><?php echo $row['information']; ?></td>
						<td><?php echo date($config['date_format'] . " " . $config['time_format'], strtotime($row['start_date'])); ?></td>
						<td><?php echo date($config['date_format'] . " " . $config['time_format'], strtotime($row['end_date'])); ?></td>
						<td><?php echo $state; ?></td>
						<td><?php echo date($config['date_format'] . " " . $config['time_format'], strtotime($row['date'])); ?></td>
						<td>
							<span style="cursor: pointer;" onclick="location.href='index.php?p=calendar&edit=<?php echo $row['id']; ?>'" class="label label-success" <?php echo $function->Tooltip($lang->_('EDIT')); ?>><span class="fa fa-pencil"></span></span>
							<span style="cursor: pointer;" onclick="location.href='index.php?p=calendar&delete=<?php echo $row['id']; ?>'" class="label label-danger" <?php echo $function->Tooltip($lang->_('DELETE')); ?>><span class="fa fa-trash"></span></span>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } ?>
</div>
<?php } ?>