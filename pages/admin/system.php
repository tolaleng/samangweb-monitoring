<?php
if(!$login->Access()) {
	echo "<div class='alert alert-danger'>{$lang->_('NO_PERMISSIONS')}</div>";
}else{
	$mysql_version = $db->query("SHOW VARIABLES LIKE 'version';");
	$mysql_version = $mysql_version->fetch_assoc();
?>
<div class="row marketing">
	<div class="col-md-5" style="margin-top: -30px;">
		<h3><?php echo $lang->_('SYSTEM_INFO'); ?></h3>
		<table style="width: 400px;" class="table table-striped">
			<tr>
				<th>Monitor <?php echo strtolower($lang->_('VERSION')); ?></th>
				<td><?php echo $config['version']; ?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('SERVER'); ?> IP</th>
				<td><?php echo ((DEMO == 1) ? "127.0.0.1" : $_SERVER['SERVER_ADDR']); ?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('SERVER'); ?> Software</th>
				<td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
			</tr>
			<tr>
				<th>PHP <?php echo $lang->_('VERSION'); ?></th>
				<td><?php echo phpversion(); ?> (<a href="index.php?phpinfo" target="_blank">PHPInfo</a>)</td>
			</tr>
			<tr>
				<th>MySQL <?php echo $lang->_('VERSION'); ?></th>
				<td><?php echo $mysql_version['Value']; ?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('LAST_CRON_RUNTIME'); ?></th>
				<td><?php echo date($config['date_format'] . " " . $config['time_format'], strtotime($config['last_cron'])); ?></td>
			</tr>
			<tr>
				<th>CodeCanyon.net</th>
				<td><a href="http://codecanyon.net/item/advanced-website-uptime-monitor/11809914" target="_blank">&raquo; CodeCanyon.net page</a></td>
			</tr>
		</table>
	</div>
	<div class="col-md-7" style="margin-top: -30px;">
		<h3><?php echo $lang->_('SYSTEM_CHECK'); ?></h3>
		<?php echo $system->DoCheck(); ?><br />
	</div>
</div>
<?php } ?>