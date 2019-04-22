<div class="row marketing">
	<?php
	if(isset($_GET['add'])) {
		if(@$_SESSION['pb_step'] == '1' || isset($_POST['step1'])) {
			$token = $db->real_escape_string(html($_SESSION['pb_token']));
			$pushbullet = new Pushbullet($token);
			if($pushbullet->Valid == true) {
				if(isset($_POST['cancel'])) {
					unset($_SESSION['pb_step']);
					unset($_SESSION['pb_token']);
					echo "<div class='alert alert-danger'>Job canceled.</div>";
					echo $function->Redirect("index.php?p=pushbullet");
					
				} else {
					unset($_SESSION['pb_step']);
					unset($_SESSION['pb_token']);

					if(DEMO == 0) { $db->query("INSERT INTO pushbullet (member_id, access_token, email) VALUES ('{$login->id}', '{$token}', '{$pushbullet->Email}')"); }
					echo $function->Redirect("index.php?p=pushbullet");
	?>
		<h3><?php echo $lang->_('ACCESS_TOKEN'); ?> <?php echo html($pushbullet->Email); ?></h3>
		
		<div class='alert alert-success'><?php echo $lang->_('PUSHBULLET_ADDED'); ?></div>
	<?php
				}
			} else {
				unset($_SESSION['pb_step']);
				unset($_SESSION['pb_token']);
				echo "<div class='alert alert-danger'>{$lang->_('ACCESS_TOKEN_INVALID')}</div>";
			}
		} else {
			$valid = false;
			if(isset($_POST['access_token'])) {
				$token = $db->real_escape_string(html($_POST['access_token']));
				$num = $db->query("SELECT * FROM pushbullet WHERE access_token='{$token}' ".((!$login->Access()) ? "AND member_id='{$login->id}'" : ""));
				
				if($num->num_rows != 0) {
					echo "<div class='alert alert-danger'>{$lang->_('TOKEN_EXIST')}</div>";		
				} else {
					$pushbullet = new Pushbullet($_POST['access_token']);
					if($pushbullet->Valid == true) {
						$valid = true;
						$_SESSION['pb_step'] = "1";
						$_SESSION['pb_token'] = $_POST['access_token'];
						echo "<div class='alert alert-success'>{$lang->_('ACCESS_TOKEN_VALID', array("%email%" => html($pushbullet->Email)))}</div>";
					} else {
						echo "<div class='alert alert-danger'>{$lang->_('ACCESS_TOKEN_INVALID')}</div>";
					}
				}
			}
	?>
		<form method="post">
			<?php if($valid == false) { ?>
				<a href="https://www.pushbullet.com/#settings/account" target="_blank"><?php echo $lang->_('GET_TOKEN'); ?></a><br /><br />
				<div class="form-group">
					<label for="access_token">Pushbullet <?php echo $lang->_('ACCESS_TOKEN'); ?>:</label>
					<input type="text" class="form-control" id="access_token" name="access_token" placeholder="o.9YPrUpBNERqfykMa5vuNmCA1yZlGQrSI" required>
				</div>
				<button type="submit" class="btn btn-success" name="check"><?php echo $lang->_('CHECK'); ?></button>
			<?php } else { ?>
				<button type="submit" class="btn btn-danger" name="cancel"><?php echo $lang->_('CANCEL'); ?></button>
				<button type="submit" class="btn btn-success" name="step1"><?php echo $lang->_('ADD'); ?></button>
			<?php } ?>
		</form>
	<?php
		}
	} else if(isset($_GET['delete'])) {
			$id = $db->real_escape_string($_GET['delete']);
			$sql = $db->query("SELECT * FROM pushbullet WHERE id='{$id}' ".((!$login->Access()) ? "AND member_id='{$login->id}'" : ""));
			if($sql->num_rows == 0){ 
				echo "<div class='alert alert-danger'>{$lang->_('PUSHBULLET_NOT_FOUND')}</div>";
			} else {
				if(isset($_POST['submit'])) {
					if(DEMO == 0) { 
						$db->query("DELETE FROM pushbullet WHERE id='{$id}'");
						$db->query("UPDATE servers SET pushbullet='0' WHERE pushbullet='{$id}'");
					}
					echo $function->Redirect("index.php?p=pushbullet");
					echo "<div class='alert alert-success'>{$lang->_('PUSHBULLET_DELETED')}</div>";
				} else {
	?>
		<form method="POST">
			<?php echo $lang->_('PUSHBULLET_DELETE_CONFIRM'); ?>
			<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('YES'); ?></button>
			<button type="button" class="btn btn-danger"  onclick="location.href='index.php?p=pushbullet'"><?php echo $lang->_('NO'); ?></button>
		</form>
	<?php
				}
			}
	} else {
		$sql = $db->query("SELECT * FROM pushbullet ".((!$login->Access()) ? "WHERE member_id='{$login->id}'" : ""));
	?>
		<div class="row">
			<div class="col-sm-12 col-md-6">
				<h2 style="margin-top: -15px;">Pushbullet</h2>
			</div>

			<div class="col-sm-12 col-md-6">
				<button type="button" class="btn btn-primary pull-right" onclick="location.href='index.php?p=pushbullet&add'"><i class="fa fa-plus"></i> <?php echo $lang->_('ADD'); ?></button>
			</div>
		</div>

		<table class="table table-striped">
			<thead>
				<tr>
					<th><?php echo $lang->_('EMAIL'); ?></th>
					<th><?php echo $lang->_('ACCESS_TOKEN'); ?></th>
					<th><?php echo $lang->_('OPTIONS'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php 
					while($row = $sql->fetch_assoc()) {
				?>
					<tr>
						<td><?php echo $row['email']; ?></td>
						<td><?php echo $row['access_token']; ?></td>
						<td>
							<?php 
								if($login->Access()) {
									$user = $db->query("SELECT * FROM users WHERE id='{$row['member_id']}'")->fetch_assoc();
							?>
								<span class="label label-default" <?php echo $function->Tooltip("{$user['username']} ({$user['email']})"); ?>><span class="fa fa-user"></span></span>
							<?php } ?>
							<span style="cursor: pointer;" onclick="location.href='index.php?p=pushbullet&delete=<?php echo $row['id']; ?>'" class="label label-danger" <?php echo $function->Tooltip($lang->_('DELETE')); ?>><span class="fa fa-trash"></span></span>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } ?>
</div>