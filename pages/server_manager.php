<div class="row marketing">
	<?php
	if(isset($_GET['a'])) {
		if($_GET['a'] == "add") {
			$sql = $db->query("SELECT * FROM servers WHERE member_id='{$login->id}'");
			$user = $db->query("SELECT * FROM users WHERE id='{$login->id}'")->fetch_assoc();
			if($user['max_servers'] != "0" && $sql->num_rows >= $user['max_servers']) {
				echo "<div class='alert alert-danger'>{$lang->_('LIMIT_REACHED')}</div>";   
			} else {
			$url = "";
			$email = "";
			$disp = "";
			$state = "0";
			$widget = "0";
			$desktop = "1";
			$pushbullet = "0";
			$timeout = "0";
			$check_time = "1";
			if(isset($_POST['submit'])) {
				$url = $_POST['website'];
				$email = $_POST['email'];
				$disp = $_POST['disp_name'];
				$state = (($_POST['state'] == 1) ? "1" : "0");
				$widget = (($_POST['widget'] == 1) ? "1" : "0");
				$desktop = (($_POST['desktop'] == 1) ? "1" : "0");
				$pushbullet = $_POST['pushbullet'];
				$timeout = $_POST['timeout'];
				$check_time = (($config['custom_server_interval'] == 1) ? $_POST['check_time'] : '1');
				try {
					$server->Add($_POST['disp_name'], $_POST['website'], $_POST['email'], $state, $widget, $desktop, $pushbullet, $timeout, $check_time);
					echo "<div class='alert alert-success'>{$lang->_('SERVER_ADDED')}</div>";
					echo $function->Redirect("index.php?p=server_manager");
				}
				catch(ServerError $e) {
					echo "<div class='alert alert-danger'>{$e->getMessage()}</div>"; 
				}
			}
	?>
	<form method="POST" style="margin-top: -30px;">
		<div class="col-md-6">
			<h3><?php echo $lang->_('SERVER_SETTINGS'); ?></h3>
			<div class="form-group">
				<label for="disp_name"><?php echo $lang->_('DISPLAY_NAME'); ?></label>
				<input type="text" class="form-control" id="disp_name" name="disp_name" placeholder="Google" value="<?php echo $disp; ?>" required>
			</div>
			<div class="form-group">
				<label for="website"><?php echo $lang->_('WEBSITE_TO_CHECK'); ?></label>
				<input type="url" class="form-control" id="website" name="website" placeholder="http://google.com" value="<?php echo $url; ?>" required>
			</div>
			<?php if($config['custom_server_interval'] == 1) { ?>
			<div class="form-group">
				<label for="check"><?php echo $lang->_('CHECK_WEBSITE_EVERY'); ?></label>
				<div class="input-group">
					<input type="number" class="form-control" id="check" name="check_time" value="<?php echo $check_time; ?>" min="1" max="60" required>
					<div class="input-group-addon"><?php echo $lang->_('MINUTES'); ?></div>
				</div>
			</div>
			<?php } ?>

			<div class="form-group">
				<label for="timeout"><?php echo $lang->_('ADMIN_TIMEOUT'); ?></label>
				<div class="input-group">
					<input type="number" min="0" max="120" class="form-control" id="timeout" name="timeout" value="<?php echo $timeout; ?>" required>
					<div class="input-group-addon"><?php echo $lang->_('SECONDS'); ?></div>
				</div>
				<p class="help-block">0 = <?php echo $lang->_('DEFAULT'); ?></p>
			</div>

			<div class="form-group">
				<label for="state"><?php echo $lang->_('DISABLED'); ?></label>
				<select name="state" id="state" class="form-control">
					<?php
						echo '<option '.(($state == "1") ? "selected" : "").' value="1">'.$lang->_('YES').'</option>';
						echo '<option '.(($state == "0") ? "selected" : "").' value="0">'.$lang->_('NO').'</option>';
					?>
				</select>
			</div>
		</div>
		<div class="col-md-6">
			<h3><?php echo $lang->_('NOTIFICATIONS'); ?></h3>
			<div class="form-group">
				<label for="email"><?php echo $lang->_('EMAIL'); ?></label>
				<input type="email" class="form-control" id="email" name="email" placeholder="info@example.com (<?php echo $lang->_('EMAIL_LEAVE_BLANK'); ?>)" value="<?php echo $email; ?>">
			</div>
			<div class="form-group">
				<label for="pushbullet">Pushbullet</label>
				<select class="form-control" name="pushbullet">
					<?php
						echo "<option value='0' ".(($pushbullet == 0) ? "selected" : "").">{$lang->_('DISABLED')}</option>";
						$sql = $db->query("SELECT * FROM pushbullet ".((!$login->Access()) ? "WHERE member_id='{$login->id}'" : ""));
						while($row = $sql->fetch_assoc()) {
							echo "<option value='{$row['id']}' ".(($pushbullet == $row['id']) ? "selected" : "").">".$row['email']."</option>";
						}
					?>
				</select>
			</div>
			<div class="form-group">
				<label for="widget"><?php echo $lang->_('EXTERNAL_WIDGET'); ?> <a href="index.php?p=widget">(<?php echo strtolower($lang->_('HELP')); ?>)</a></label>
				<select name="widget" id="widget" class="form-control">
					<?php
						echo '<option '.(($widget == "1") ? "selected" : "").' value="1">'.$lang->_('YES').'</option>';
						echo '<option '.(($widget == "0") ? "selected" : "").' value="0">'.$lang->_('NO').'</option>';
					?>
				</select>
			</div>
			
			<div class="form-group">
				<label for="desktop"><?php echo $lang->_('DESKTOP_NOTIFICATIONS'); ?></label>
				<select name="desktop" id="desktop" class="form-control">
					<?php
						echo '<option '.(($desktop == "1") ? "selected" : "").' value="1">'.$lang->_('YES').'</option>';
						echo '<option '.(($desktop == "0") ? "selected" : "").' value="0">'.$lang->_('NO').'</option>';
					?>
				</select>
			</div>
		</div>
		
		
		<div class="row">
			<div class="col-md-12">
				<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('ADD'); ?></button>
			</div>
		</div>
	</form>
	<?php
			}
		} else if($_GET['a'] == "import") {
			if(!is_writeable("_tmp")) {
				echo "<div class='alert alert-danger'>{$lang->_('IMPORT_EXPORT_DISABLED')}</div>";
			} else {
				$size = $function->FileMaxSize(0);
				$size_bytes = $function->FileMaxSize(1);
				$format = 1;
				if(isset($_POST['submit']) && !empty($_FILES['importFile']['name'])) {
					$ext = pathinfo($_FILES['importFile']['name'], PATHINFO_EXTENSION);
					if($ext != "csv") {
						echo "<div class='alert alert-danger'>{$lang->_('IMPORT_FILE_HELP')}</div>";
						
					} else if($_FILES['importFile']['size'] > $size_bytes) {
						echo "<div class='alert alert-danger'>{$lang->_('IMPORT_FILE_SIZE', array("%size%" => $size))}</div>";
						
					} else {
						$filename = dirname(__FILE__) . "/../_tmp/import_servers_{$login->username}_" . time() . ".csv";
						if (move_uploaded_file($_FILES["importFile"]["tmp_name"], $filename)) {
							$csv = array_map('str_getcsv', file($filename));
							
							echo "<h3>{$lang->_('IMPORT_OUTPUT')}</h3>";
							foreach($csv as $server) {
								$sql = $db->query("SELECT * FROM servers WHERE member_id='{$login->id}'");
								$user = $db->query("SELECT * FROM users WHERE id='{$login->id}'")->fetch_assoc();
								if($user['max_servers'] != "0" && $sql->num_rows >= $user['max_servers']) {
									echo "<div class='alert alert-danger'>{$lang->_('LIMIT_REACHED')}</div>";   
									break;
								} else {
									$error_message = "";
									$server_url = "";
									$display_name = "";
									$email = "";
									$disabled = 0;
									$desktop_notif = 1;
									$widget = 0;
									$timeout = 0;
									
									if(isset($server[0])) {
										$server_url = $db->real_escape_string(html($server[0]));
										if(strpos($server_url, "http://") === false && strpos($server_url, "https://") === false) {
											$server_url = "http://{$server_url}";
										}
										
										if (!filter_var($server_url, FILTER_VALIDATE_URL) || strpos($server_url, ".") === false) {
											$error_message .= "&bull; {$lang->_('SERVER_URL_INVALID')}<br />";
										}
										
										$check_exists = $db->query("SELECT * FROM servers WHERE member_id='{$login->id}' AND server_url='{$server_url}'");
										
										if($check_exists->num_rows != 0) {
											$error_message .= "&bull; {$lang->_('DOMAIN_EXISTS')}<br />";
										}
									}
									
									if(isset($server[1])) {
										$display_name = $db->real_escape_string(html($server[1]));
										if($display_name == "") {
											$display_name = $server_url;	
										}
									} else {
										$display_name = $server_url;
									}
									
									if(isset($server[2])) {
										$email = $db->real_escape_string(html($server[2]));
										if($email == "-") {
											$email = "";	
										} else if(!$function->ValidateEmail($email)) {
											$error_message .= "&bull; {$lang->_('SIGNUP_EMAIL_INVALID')}<br />";
										}
									}
									
									if(isset($server[3])) {
										if($server[3] == 0) { 
											$disabled = 0;
										} else {
											$disabled = 1;
										}
									}
									
									if(isset($server[4])) {
										if($server[4] == 0) { 
											$desktop_notif = 0;
										} else {
											$desktop_notif = 1;
										}
									}
									
									if(isset($server[5])) {
										if($server[5] == 0) { 
											$widget = 0;
										} else {
											$widget = 1;
										}
									}
									
									
									if(isset($server[6])) {
										if($server[6] == 0) { 
											$timeout = 0;
										} else {
											$timeout = 1;
										}
									}
									
									if($error_message == "") {
										$db->query("INSERT INTO servers (member_id, server_url, display_name, email_to, disabled, desktop_notif, widget, timeout) VALUES ('{$login->id}', '{$server_url}', '{$display_name}', '{$email}', '{$disabled}', '{$desktop_notif}', '{$widget}', '{$timeout}')") or die($db->error);
										echo "<div class='alert alert-success'><b>{$server_url}:</b><br /> Imported.</div>";
									} else {
										echo "<div class='alert alert-danger'><b>{$server_url}:</b><br /> {$error_message}</div>";
									}
								}
							}
							unlink($filename);
						} else {
							echo "Sorry, there was an error uploading your file.";
						}
					}
				}
	?>
		<h2 style="margin-top: -10px;"><?php echo $lang->_("IMPORT_NAME"); ?></h2>
		<form method="POST" enctype="multipart/form-data">
			<?php echo $lang->_('IMPORT_INFO'); ?><br /><br />
			
			<div class="form-group">
				<label for="file"><?php echo $lang->_("IMPORT_FILE"); ?> (<?php echo $size; ?>)</label>
				<input type="file" id="file" name="importFile" accept=".csv">
				<p class="help-block"><?php echo $lang->_("IMPORT_FILE_HELP"); ?></p>
			</div><br />
			
			<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('IMPORT_NAME'); ?></button>
			<button type="button" class="btn btn-danger"  onclick="location.href='index.php?p=server_manager'"><?php echo $lang->_('CANCEL'); ?></button>
		</form>
	<?php
			}
		} else if($_GET['a'] == "export") {
			if(!is_writeable("_tmp")) {
				echo "<div class='alert alert-danger'>{$lang->_('IMPORT_EXPORT_DISABLED')}</div>";
			} else {
				$format = 2;
				if(isset($_POST['submit']) && isset($_POST['format'])) {
					$format = $_POST['format'];
					$sql = $db->query("SELECT * FROM servers WHERE deleted='0' AND member_id='{$login->id}'");

					$list = array();
					$i=0;
					while($row = $sql->fetch_assoc()) {
						if($_POST['format'] == 1) {
							$list[$i][] = $row['server_url'];
							$list[$i][] = $row['display_name'];
							$list[$i][] = (($row['email_to'] == "") ? "-" : $row['email_to']);
							$list[$i][] = $row['disabled'];
							$list[$i][] = $row['desktop_notif'];
							$list[$i][] = $row['widget'];
							$list[$i][] = $row['timeout'];
						} else if($_POST['format'] == 2) {
							$list[$i][] = $row['server_url'];
							$list[$i][] = $row['display_name'];
						} else if($_POST['format'] == 3) {
							$list[$i][] = $row['server_url'];
						}
						
						$i++;
					}
					
					$filename = "export_servers_{$login->username}_" . time() . ".csv";
					$fp = fopen(dirname(__FILE__) . "/../_tmp/" . $filename, "w");

					foreach ($list as $fields) {
						fputcsv($fp, $fields);
					}

					fclose($fp);
					
					$download_url = "index.php?download_export={$filename}";
					echo "<div class='alert alert-success'>{$lang->_('EXPORT_SUCCESSFULLY', array("%download_url%" => $download_url))}</div>";
				}
	?>
		<h2 style="margin-top: -10px;"><?php echo $lang->_("EXPORT_NAME"); ?></h2>
		<form method="POST">
			<?php echo $lang->_('EXPORT_INFO'); ?><br /><br />
			
			<div class="row">
				<div class="col-sm-5">
					<b><?php echo $lang->_('EXPORT_FORMAT'); ?></b><br />
					<select class="form-control" name="format">
						<option value="1" <?php if($format == 1) { echo "selected"; } ?>><?php echo $lang->_('EXPORT_FORMAT_1'); ?></option>
						<option value="2" <?php if($format == 2) { echo "selected"; } ?>><?php echo $lang->_('EXPORT_FORMAT_2'); ?></option>
						<option value="3" <?php if($format == 3) { echo "selected"; } ?>><?php echo $lang->_('EXPORT_FORMAT_3'); ?></option>
					</select>
				</div>
			</div><br />
			
			<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('EXPORT_NAME'); ?></button>
			<button type="button" class="btn btn-danger"  onclick="location.href='index.php?p=server_manager'"><?php echo $lang->_('CANCEL'); ?></button>
		</form>
	<?php
			}
		} else {
			echo "<div class='alert alert-danger'>This action was not found.</div>";   
		}
	} else if(isset($_GET['edit'])) {
			$id = $db->real_escape_string($_GET['edit']);
			$sql = $db->query("SELECT * FROM servers WHERE id='{$id}' AND deleted='0' ".((!$login->Access()) ? "AND member_id='{$login->id}'" : ""));
			if($sql->num_rows == 0) {
				echo "<div class='alert alert-danger'>{$lang->_('SERVER_NOT_FOUND')}</div>";
			} else {
				 $row = $sql->fetch_assoc();
				$url = $row['server_url'];
				$email = $row['email_to'];
				$disp = $row['display_name'];
				$widget = (($row['widget'] == "0") ? "0" : "1");
				$state = (($row['disabled'] == "0") ? "0" : "1");
				$desktop = (($row['desktop_notif'] == "0") ? "0" : "1");
				$pushbullet = $row['pushbullet'];
				$owner = $row['member_id'];
				$check_time = $row['check_time'];
				$timeout = $row['timeout'];
				if(isset($_POST['submit'])) {
					$url = $_POST['website'];
					$email = $_POST['email'];
					$disp = $_POST['disp_name'];
					$widget = (($_POST['widget'] == 1) ? "1" : "0");
					$state = (($_POST['state'] == 1) ? "1" : "0");
					$desktop = (($_POST['desktop'] == 1) ? "1" : "0");
					$pushbullet = $_POST['pushbullet'];
					$check_time = (($config['custom_server_interval'] == 1) ? $_POST['check_time'] : $check_time);
					if(isset($_POST['owner'])) { $owner = $_POST['owner']; }
					$timeout = $_POST['timeout'];
					
					try {
						$server->Edit($id, $_POST['disp_name'], $_POST['website'], $_POST['email'], $state, $widget, $desktop, $pushbullet, $owner, $timeout, $check_time);
						echo "<div class='alert alert-success'>{$lang->_('SERVER_EDITED')}</div>";
						echo $function->Redirect("index.php?p=server_manager");
					}
					catch(ServerError $e) {
						echo "<div class='alert alert-danger'>{$e->getMessage()}</div>"; 
					}
				}
	?>
		<form method="POST" style="margin-top: -30px;">
			<div class="col-md-6">
				<h3><?php echo $lang->_('SERVER_SETTINGS'); ?></h3>
				<div class="form-group">
					<label for="disp_name"><?php echo $lang->_('DISPLAY_NAME'); ?></label>
					<input type="text" class="form-control" id="disp_name" name="disp_name" placeholder="Google" value="<?php echo $disp; ?>" required>
				</div>
				<div class="form-group">
					<label for="website"><?php echo $lang->_('WEBSITE_TO_CHECK'); ?></label>
					<input type="url" class="form-control" id="website" name="website" placeholder="http://google.com" value="<?php echo $url; ?>" required>
				</div>
				<?php 
					if($login->Access()) {
				?>
					<div class="form-group">
						<label for="owner"><?php echo $lang->_('OWNER'); ?></label>
						<select class="form-control" name="owner">
							<?php
								$sql = $db->query("SELECT * FROM users");
								while($row = $sql->fetch_assoc()) {
									echo "<option value='{$row['id']}' ".(($owner == $row['id']) ? "selected" : "").">{$row['username']} ({$row['email']})</option>";
								}
							?>
						</select>
					</div>
				<?php } ?>

				<?php if($config['custom_server_interval'] == 1) { ?>
				<div class="form-group">
					<label for="check"><?php echo $lang->_('CHECK_WEBSITE_EVERY'); ?></label>
					<div class="input-group">
						<input type="number" class="form-control" id="check" name="check_time" value="<?php echo $check_time; ?>" min="1" max="60" required>
						<div class="input-group-addon"><?php echo $lang->_('MINUTES'); ?></div>
					</div>
				</div>
				<?php } ?>

				<div class="form-group">
					<label for="timeout"><?php echo $lang->_('ADMIN_TIMEOUT'); ?></label>
					<div class="input-group">
						<input type="number" min="0" max="120" class="form-control" id="timeout" name="timeout" value="<?php echo $timeout; ?>" required>
						<div class="input-group-addon"><?php echo $lang->_('SECONDS'); ?></div>
					</div>
					<p class="help-block">0 = <?php echo $lang->_('DEFAULT'); ?></p>
				</div>
				
				<div class="form-group">
					<label for="state"><?php echo $lang->_('DISABLED'); ?></label>
					<select name="state" id="state" class="form-control">
						<?php
							echo '<option '.(($state == "1") ? "selected" : "").' value="1">'.$lang->_('YES').'</option>';
							echo '<option '.(($state == "0") ? "selected" : "").' value="0">'.$lang->_('NO').'</option>';
						?>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<h3><?php echo $lang->_('NOTIFICATIONS'); ?></h3>
				<div class="form-group">
					<label for="email"><?php echo $lang->_('EMAIL'); ?></label>
					<input type="email" class="form-control" id="email" name="email" placeholder="info@example.com (<?php echo $lang->_('EMAIL_LEAVE_BLANK'); ?>)" value="<?php echo $email; ?>">
				</div>
				<div class="form-group">
					<label for="pushbullet">Pushbullet</label>
					<select class="form-control" name="pushbullet">
						<?php
							echo "<option value='0' ".(($pushbullet == 0) ? "selected" : "").">{$lang->_('DISABLED')}</option>";
							$sql = $db->query("SELECT * FROM pushbullet ".((!$login->Access()) ? "WHERE member_id='{$login->id}'" : ""));
							while($row = $sql->fetch_assoc()) {
								echo "<option value='{$row['id']}' ".(($pushbullet == $row['id']) ? "selected" : "").">".$row['email']."</option>";
							}
						?>
					</select>
				</div>
				<div class="form-group">
					<label for="widget"><?php echo $lang->_('EXTERNAL_WIDGET'); ?> <a href="index.php?p=widget">(<?php echo strtolower($lang->_('HELP')); ?>)</a></label>
					<select name="widget" id="widget" class="form-control">
						<?php
							echo '<option '.(($widget == "1") ? "selected" : "").' value="1">'.$lang->_('YES').'</option>';
							echo '<option '.(($widget == "0") ? "selected" : "").' value="0">'.$lang->_('NO').'</option>';
						?>
					</select>
				</div>
				
				<div class="form-group">
					<label for="desktop"><?php echo $lang->_('DESKTOP_NOTIFICATIONS'); ?></label>
					<select name="desktop" id="desktop" class="form-control">
						<?php
							echo '<option '.(($desktop == "1") ? "selected" : "").' value="1">'.$lang->_('YES').'</option>';
							echo '<option '.(($desktop == "0") ? "selected" : "").' value="0">'.$lang->_('NO').'</option>';
						?>
					</select>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-12">
					<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('SAVE'); ?></button>
				</div>
			</div>
		</form>
	<?php
			}
	} else if(isset($_GET['delete'])) {
			$id = $db->real_escape_string($_GET['delete']);
			$sql = $db->query("SELECT * FROM servers WHERE id='{$id}' AND deleted='0' ".((!$login->Access()) ? "AND member_id='{$login->id}'" : ""));
			if($sql->num_rows == 0) {
				echo "<div class='alert alert-danger'>This server was not found.</div>";
			} else {
				$row = $sql->fetch_assoc();
				if(isset($_POST['submit'])) {
					if(DEMO == 0) { 
						$db->query("UPDATE servers SET deleted = '1' WHERE id='{$id}'");
						
						$user = $db->query("SELECT * FROM users WHERE id='{$row['member_id']}'")->fetch_assoc();
						if($user['chart_1'] == $row['id']) {$db->query("UPDATE users SET chart_1 = '0' AND id='{$row['member_id']}'"); }
						if($user['chart_2'] == $row['id']) {$db->query("UPDATE users SET chart_2 = '0' AND id='{$row['member_id']}'"); }
					}
					echo $function->Redirect("index.php?p=server_manager");
					echo "<div class='alert alert-success'>{$lang->_('SERVER_DELETED')}</div>";
				}
	?>
		<h2 style="margin-top: -10px;"><?php echo $lang->_("DELETE"); ?></h2>
		<form method="POST">
			<?php echo $lang->_('SERVER_DELETE', array("%server_id%" => $id)); ?><br /><br />
			<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('YES'); ?></button>
			<button type="button" class="btn btn-danger"  onclick="location.href='index.php?p=server_manager'"><?php echo $lang->_('NO'); ?></button>
		</form>
	<?php
			}
	} else {
		$sql = $db->query("SELECT * FROM servers ".((!$login->Access()) ? "WHERE member_id='{$login->id}'" : ""));
		$servers = $db->query("SELECT * FROM servers WHERE member_id='{$login->id}'");
		$user = $db->query("SELECT * FROM users WHERE id='{$login->id}'")->fetch_assoc();
		
		//SELECT * FROM calendar WHERE start_date <= NOW() AND end_date >= NOW()
		//SELECT * FROM calendar WHERE end_date <= NOW()
	?>
		<b><?php echo $lang->_('LIMIT'); ?>:</b> <?php echo (($user['max_servers'] == "0") ? $lang->_('UNLIMITED') : (($servers->num_rows >= $user['max_servers']) ? "<font color='red'>{$servers->num_rows}/{$user['max_servers']}</font>" : "<font color='green'>{$servers->num_rows}/{$user['max_servers']}</font>")); ?>
		<p class="pull-right">
			<button type="button" class="btn btn-info" onclick="location.href='index.php?p=calendar'"><i class="fa fa-calendar"></i> <?php echo $lang->_('CALENDAR'); ?></button>
			<button type="button" class="btn btn-success" onclick="location.href='index.php?p=server_manager&a=export'"><i class="fa fa-upload"></i> <?php echo $lang->_('EXPORT_NAME'); ?></button>
			<button type="button" class="btn btn-success" onclick="location.href='index.php?p=server_manager&a=import'"><i class="fa fa-download"></i> <?php echo $lang->_('IMPORT_NAME'); ?></button>
			<button type="button" class="btn btn-primary" onclick="location.href='index.php?p=server_manager&a=add'"><i class="fa fa-plus"></i> <?php echo $lang->_('ADD'); ?></button>
		</p>
		<div style="clear:both;"></div>
		<script type="text/javascript">
		function LoadServers() {
			$( "#LoadServers" ).load( "ajax/Servers.php" );
		}

		$( window ).load(function() {
			LoadServers();
			setInterval(function() {
				LoadServers();
			}, 50000);
		});
		</script>

		<div id="LoadServers"><div class="alert alert-info"><?php echo $lang->_('LOADING_SERVERS'); ?></div></div>
	<?php } ?>
</div>