<?php
$headers = array();
$headers[] = "Keep-Alive: 300";
$headers[] = "Connection: Keep-Alive";
$headers[] = "User-Agent: Advanced Website Uptime Monitor Version " . $config['version'];

$ch = curl_init("http://linuxtender.com?v={$config['version']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_REFERER, "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
$output = curl_exec($ch);
curl_close($ch);

$ch = curl_init("http://linuxtender.com?version={$config['version']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_REFERER, "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
$change_log = curl_exec($ch);
curl_close($ch);

$details = json_decode($output);
?>
<div class="row marketing">
<h3><?php echo $lang->_('VERSION_INFORMATION'); ?></h3>
<table style="width: 400px;" class="table table-striped">
	<tr>
		<th><?php echo $lang->_('CURRENT_VERSION'); ?></th>
		<td><?php echo $config['version']; ?></td>
	</tr>
	<tr>
		<th><?php echo $lang->_('NEWEST_VERSION'); ?></th>
		<td><?php echo $details->version; ?></td>
	</tr>
	<tr>
		<th><?php echo $lang->_('VERSION_RELEASE'); ?></th>
		<td><?php echo $details->release_date; ?></td>
	</tr>
	<tr>
		<th><?php echo $lang->_('NEW_VERSION'); ?></th>
		<td><?php echo (($details->new_version == "no") ? "<div class='label label-success'>{$lang->_('NO')}</div>" : "<div class='label label-danger'>{$lang->_('YES')}</div>"); ?></td>
	</tr>
	<tr>
		<th>CodeCanyon.net</th>
		<td><a href="<?php echo $details->codecanyon; ?>" target="_blank">&raquo; Linux Tender page</a></td>
	</tr>
</table>

<h3><?php echo $lang->_('VERSION_CHANGELOG'); ?></h3>
<?php echo $change_log; ?>
</div>
