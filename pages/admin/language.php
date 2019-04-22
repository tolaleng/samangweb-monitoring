<div class="row marketing">
<?php
if(!$login->Access()) {
	echo "<div class='alert alert-danger'>{$lang->_('NO_PERMISSIONS')}</div>";
}else if(isset($_GET['edit'])) {
	$path = "includes/languages/{$_GET['edit']}.txt";
	if(!file_exists($path)) {
		echo "<div class='alert alert-danger'>{$lang->_('LANGUAGE_NOT_FOUND')}</div>";
	} else {
		if(!is_writable($path)) {
			echo "<div class='alert alert-danger'>{$lang->_('LANGUAGE_NOT_WRITEABLE', array('%file%' => $path))}</div>";
		} else if(isset($_POST['save'])) {
			if(DEMO == 0) {
				$lang->setLang($_GET['edit']);
				$file = file_get_contents($path);
				
				preg_match('/## Language name: ([A-Za-z0-9]+)/', $file, $name);
				preg_match('/## Created By: ([A-Za-z0-9\-]+)/', $file, $creator);
				
				foreach($_POST as $key => $value) {
					$postdata = array("save", "Lang_name", "Lang_creator");
					
					if(!in_array($key, $postdata)) {
						$value = str_ireplace(array("\n", "\r"), "", $value);
						$value = str_ireplace('&quot;', '"', $value);
						$file = str_replace("{$key}={$lang->_($key)}", "{$key}={$value}", $file);
						$file_replacer = str_replace("\n{$key}={$lang->_($key)}", "\n{$key}={$value}", $file);
					}
				}
				$file = str_ireplace("## Language name: {$name[1]}", "## Language name: {$_POST['Lang_name']}", $file);
				$file = str_ireplace("## Created By: {$creator[1]}", "## Created By: {$_POST['Lang_creator']}", $file);
				
				
				file_put_contents($path, $file);
				$lang->setLang($login->lang);
			}
			echo "<div class='alert alert-success'>{$lang->_('LANGUAGE_FILE_EDITED')}</div>";
		}
?>
	<h3 style="margin-top: -20px;"><?php echo $lang->_('LANGUAGE_EDIT_FILE'); ?>: <?php echo $_GET['edit']; ?></h3>
	<form method="post">
		<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
		<?php
		$file = file_get_contents($path);

		preg_match_all('/<begin name=\"([A-Za-z0-9\ &]+)\">(.*?)<end>/s', $file, $output);
		preg_match('/## Language name: ([A-Za-z0-9]+)/', $file, $name);
		preg_match('/## Monitor Version: ([0-9\.]+)/', $file, $monitor_version);
		preg_match('/## Created By: ([A-Za-z0-9\-]+)/', $file, $creator);

		
		echo '<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="heading">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse" aria-expanded="true" aria-controls="collapse">
						1. Language pack settings
						</a>
					</h4>
					</div>
					<div id="collapse" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading">
					<div class="panel-body">
						<b>'.$lang->_('LANGUAGE_NAME').'</b><br />
						<input type="text" class="form-control" name="Lang_name" value="'.$name[1].'"><br />
						<b>'.$lang->_('LANGUAGE_MONITOR_VERSION').'</b><br />
						<input type="text" class="form-control" disabled readonly value="'.$monitor_version[1].'"><br />
						<b>'.$lang->_('LANGUAGE_CREATED_BY').'</b><br />
						<input type="text" class="form-control" name="Lang_creator" value="'.$creator[1].'"><br />
					</div></div></div>';
		
		$i=0;
		foreach($output[1] as $names) {
			echo '<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="heading'.$i.'">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$i.'" aria-expanded="false" aria-controls="collapse'.$i.'">
						'.($i + 2).'. '.$names.'
						</a>
					</h4>
					</div>
					<div id="collapse'.$i.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading'.$i.'">
					<div class="panel-body">';
			
			preg_match_all("/([A-Z0-9_]+)=(.*)/", $output[2][$i], $strings);
			$n=0;
			foreach($strings[1] as $string) {
				$textareas = array('WIDGET_INFORMATION_TEXT', 'CRON_UNKNOWN_ERROR_MSG', 'CRON_ONLINE_MSG', 'CRON_OFFLINE_INVALID_RESPONSE_CODE_MSG', 'WEBSITE_NOTIFICATION_MSG', 'RESET_PASSOWRD_EMAIL', 'RESET_PASSOWRD_EMAIL_MESSAGE', 'ACCOUNT_ACTIVATE_EMAIL_MESSAGE');
				echo "<b>{$string}</b><br />";
				if(in_array($string, $textareas)) {
					$strings[2][$n] = str_ireplace(array("<br />", "<br>", "<br/>"), "<br />\r", $strings[2][$n]);
					echo "<textarea class=\"form-control\" rows=\"8\" name=\"{$string}\">{$strings[2][$n]}</textarea><br />";
				} else {
					$strings[2][$n] = str_ireplace('"', '&quot;', $strings[2][$n]);
					echo "<input type=\"text\" class=\"form-control\" name=\"{$string}\" value=\"{$strings[2][$n]}\"><br />";
				}
				$n++;
			}
			$i++;
			echo '</div></div></div>';
		}
		?>
		</div>
		<input type="submit" class="btn btn-success" name="save" value="<?php echo $lang->_('SAVE'); ?>">
	</form>
<?php
	}
} else if(isset($_GET['update'])) {
	$path = "includes/languages/{$_GET['update']}.txt";
	if(!file_exists($path)) {
		echo "<div class='alert alert-danger'>{$lang->_('LANGUAGE_NOT_FOUND')}</div>";
	} else {
		if(!is_writable($path)) {
			echo "<div class='alert alert-danger'>{$lang->_('LANGUAGE_NOT_WRITEABLE', array('%file%' => $path))}</div>";
		} else {
			if(DEMO == 0) {
				$lang->setLang($_GET['update']);
				$default_file = file_get_contents("includes/languages/en.txt");
				$file_new = file_get_contents($path);
				
				preg_match_all('/<begin name=\"([A-Za-z0-9\ &]+)\">(.*?)<end>/s', $default_file, $output);
				preg_match('/## Language name: ([A-Za-z0-9]+)/', $default_file, $default_name);
				preg_match('/## Monitor Version: ([0-9\.]+)/', $default_file, $default_monitor_version);
				preg_match('/## Created By: ([A-Za-z0-9\-]+)/', $default_file, $default_creator);
				
				preg_match('/## Language name: ([A-Za-z0-9]+)/', $file_new, $name);
				preg_match('/## Created By: ([A-Za-z0-9\-]+)/', $file_new, $creator);
		
				$i=0;
				foreach($output[1] as $names) {
					preg_match_all("/([A-Z0-9_]+)=(.*)/", $output[2][$i], $strings);
					$n=0;
					foreach($strings[1] as $string) {
						$default_file = str_replace("\n{$string}={$strings[2][$n]}", "\n{$string}={$lang->_($string)}", $default_file);
						$n++;
					}
					$i++;
				}
				$default_file = str_ireplace("## Language name: {$default_name[1]}", "## Language name: {$name[1]}", $default_file);
				$default_file = str_ireplace("## Monitor Version: {$default_monitor_version[1]}", "## Monitor Version: {$config['version']}", $default_file);
				$default_file = str_ireplace("## Created By: {$default_creator[1]}", "## Created By: {$creator[1]}", $default_file);
				file_put_contents($path, $default_file);
				$lang->setLang($login->lang);
			}
			echo "<div class='alert alert-success'>{$lang->_('LANGUAGE_FILE_UPDATED')}</div>" . $function->Redirect("index.php?p=admin/language");
		}
	}
} else if(isset($_GET['duplicate'])) {
	$path = "includes/languages/";
	$filename = "{$path}{$_GET['duplicate']}.txt";
	if(!file_exists($path)) {
		echo "<div class='alert alert-danger'>{$lang->_('LANGUAGE_NOT_FOUND')}</div>";
	} else {
		if(!is_writable($path)) {
			echo "<div class='alert alert-danger'>{$lang->_('LANGUAGE_FOLDER_NOT_WRITEABLE', array('%folder%' => $path))}</div>";
		} else {
			$file = file_get_contents($filename);

			preg_match('/## Language name: ([A-Za-z0-9]+)/', $file, $name);
			preg_match('/## Monitor Version: ([0-9\.]+)/', $file, $monitor_version);
			preg_match('/## Created By: ([A-Za-z0-9\-]+)/', $file, $creator);
			
			if(str_replace(".", "", $config['version']) > str_replace(".", "", $monitor_version[1])) {
				echo "<div class='alert alert-danger'>{$lang->_('LANGUAGE_UPDATE_NEEDED_ERROR')}</div>";
			} else {
				if(isset($_POST['save'])) {
					if(file_exists($path.$_POST['Lang_Code'].".txt")) {
						echo "<div class='alert alert-danger'>{$lang->_('LANGUAGE_FOUND')}</div>";
					} else {
						if(DEMO == 0) {
							$file = str_ireplace("## Language name: {$name[1]}", "## Language name: {$_POST['Lang_Name']}", $file);
							$file = str_ireplace("## Created By: {$creator[1]}", "## Created By: {$_POST['Lang_Creator']}", $file);
							file_put_contents($path.$_POST['Lang_Code'].".txt", $file);
							echo $function->Redirect("index.php?p=admin/language&edit=".$_POST['Lang_Code']);
						}
						echo "<div class='alert alert-success'>{$lang->_('LANGUAGE_DUPLICATED')}</div>";
					}
				}
?>
	<form method="post">
		<b><?php echo $lang->_('LANG_CODE'); ?></b><br />
		<input type="text" class="form-control" name="Lang_Code" value="<?php echo $_GET['duplicate']; ?>"><br />
		
		<b><?php echo $lang->_('LANGUAGE_NAME'); ?></b><br />
		<input type="text" class="form-control" name="Lang_Name" value="<?php echo $name[1]; ?>"><br />

		<b><?php echo $lang->_('LANGUAGE_CREATED_BY'); ?></b><br />
		<input type="text" class="form-control" name="Lang_Creator" value="<?php echo $creator[1]; ?>"><br />
		<input type="submit" class="btn btn-success" name="save" value="<?php echo $lang->_('DUPLICATE'); ?>">
	</form>
<?php
			}
		}
	}
} else if(isset($_GET['delete'])) {
	$path = "includes/languages/";
	$filename = "{$path}{$_GET['delete']}.txt";
	if(!file_exists($path)) {
		echo "<div class='alert alert-danger'>{$lang->_('LANGUAGE_NOT_FOUND')}</div>";
	} else {
		if(!is_writable($path)) {
			echo "<div class='alert alert-danger'>{$lang->_('LANGUAGE_FOLDER_NOT_WRITEABLE', array('%folder%' => $path))}</div>";
		} else if($_GET['delete'] == "en") {
			echo "<div class='alert alert-danger'>{$lang->_('DEFAULT_LANGUAGE_DELETE')}</div>";			
		} else {
			if(isset($_POST['submit'])) {
				if(DEMO == 0) {
					if(@unlink($filename)) {
						$id = $db->real_escape_string($_GET['delete']);
						$db->query("UPDATE config SET default_language='en' WHERE default_language='{$id}'");
						$db->query("UPDATE users SET language='' WHERE language='{$id}'");
						echo "<div class='alert alert-success'>{$lang->_('LANGUAGE_DELETED')}</div>";
						echo $function->Redirect("index.php?p=admin/language");
					} else {
						echo "<div class='alert alert-danger'>Something went wrong!</div>";
					}
				} else {
					echo "<div class='alert alert-success'>{$lang->_('LANGUAGE_DELETED')}</div>";
					echo $function->Redirect("index.php?p=admin/language");
				}
			}
?>
	<form method="POST">
		<?php echo $lang->_('LANGUAGE_DELETE', array("%lang%" => $_GET['delete'])); ?><br /><br />
		<button type="submit" class="btn btn-success" name="submit"><?php echo $lang->_('YES'); ?></button>
		<button type="button" class="btn btn-danger"  onclick="location.href='index.php?p=admin/language'"><?php echo $lang->_('NO'); ?></button>
	</form>
<?php
		}
	}
} else {
?>
<h3 style="margin-top: -20px;"><?php echo $lang->_('LANGUAGE_MANAGER'); ?></h3>
<table class="table table-striped">
	<thead>
		<tr>
			<th><?php echo $lang->_('LANG_CODE'); ?></th>
			<th><?php echo $lang->_('LANGUAGE_NAME'); ?></th>
			<th><?php echo $lang->_('LANGUAGE_MONITOR_VERSION'); ?></th>
			<th><?php echo $lang->_('LANGUAGE_CREATED_BY'); ?></th>
			<th><?php echo $lang->_('LANGUAGE_UPDATED_DATE'); ?></th>
			<th></th>
			<th><?php echo $lang->_('OPTIONS'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php 
			foreach (glob("includes/languages/*.txt") as $filename) {
				$file = file_get_contents($filename);

				preg_match('/## Language name: ([A-Za-z0-9]+)/', $file, $name);
				preg_match('/## Monitor Version: ([0-9\.]+)/', $file, $monitor_version);
				preg_match('/## Created By: ([A-Za-z0-9\-]+)/', $file, $creator);
				
				$code = str_replace(array("includes/languages/", ".txt"), "", $filename);
		?>
			<tr>
				<td><?php echo $code; ?></td>
				<td><?php echo $name[1]; ?></td>
				<td><?php echo $monitor_version[1]; ?></td>
				<td><?php echo $creator[1]; ?></td>
				<td><?php echo date($config['date_format'] . " " . $config['time_format'], filemtime($filename)); ?></td>
				<td><?php echo ((str_replace(".", "", $config['version']) > str_replace(".", "", $monitor_version[1])) ? "<div class='label label-danger'>{$lang->_('LANGUAGE_UPDATE_NEEDED')}</div>" : "<div class='label label-success'>{$lang->_('LANGUAGE_UPDATED')}</div>"); ?></td>
				<td>
					<?php if(str_replace(".", "", $config['version']) > str_replace(".", "", $monitor_version[1])) { ?>
						<span style="cursor: pointer;" onclick="location.href='index.php?p=admin/language&update=<?php echo $code; ?>'" class="label label-success" <?php echo $function->Tooltip($lang->_('UPDATE')); ?>><span class="fa fa-pencil-square-o"></span></span>					
					<?php } ?>
					<span style="cursor: pointer;" onclick="location.href='index.php?p=admin/language&edit=<?php echo $code; ?>'" class="label label-info" <?php echo $function->Tooltip($lang->_('EDIT')); ?>><span class="fa fa-pencil"></span></span>
					<span style="cursor: pointer;" onclick="location.href='index.php?p=admin/language&duplicate=<?php echo $code; ?>'" class="label label-primary" <?php echo $function->Tooltip($lang->_('DUPLICATE')); ?>><span class="fa fa-files-o"></span></span>
					<span style="cursor: pointer;" onclick="window.open('includes/languages/<?php echo $code; ?>.txt', '_blank');" class="label label-warning" <?php echo $function->Tooltip($lang->_('DOWNLOAD')); ?>><span class="fa fa-download"></span></span>
					<?php if($code != "en") { ?>
						<span style="cursor: pointer;" onclick="location.href='index.php?p=admin/language&delete=<?php echo $code; ?>'" class="label label-danger" <?php echo $function->Tooltip($lang->_('DELETE')); ?>><span class="fa fa-trash"></span></span>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php } ?>
</div>
	