<?php 
$url = "http://{$_SERVER['SERVER_NAME']}".str_replace("index.php?p=widget", "widget.php?id=", $_SERVER['REQUEST_URI']);
?>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/styles/default.min.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/styles/androidstudio.min.css">
<script src="https://highlightjs.org/static/highlight.pack.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<div class="row marketing" style="margin-top: -30px;">
	<h2><?php echo $lang->_('WIDGET_INFORMATION_TITLE'); ?></h2>
	<?php echo $lang->_('WIDGET_INFORMATION_TEXT'); ?>
	
	<b><?php echo $lang->_('WIDGET_URL'); ?>:</b> <?php echo "{$url}[server_id]"; ?><br /><br />
	
	<b>PHP Code:</b><br />
	<pre><code class="php hljs">&lt;?php
	$server = "ID"; // <?php echo $lang->_('REPLACE_TEXT'); ?>
	echo file_get_contents("<?php echo $url; ?>" . $server);
?&gt;</code></pre><br />
	
	<b>Javascript Code (requires jquery):</b><br />
	<pre><code class="xml hljs">&lt;script type="text/javascript"&gt;
$( window ).load(function() {
	var server_id = 1; // <?php echo $lang->_('REPLACE_TEXT'); ?>
	$( "#ServerStats" ).load( "<?php echo $url; ?>" + server_id);
});
&lt;/script&gt;
&lt;div id=&quot;ServerStats&quot;&gt;&lt;/div&gt;</code></pre><br />

	<b>iFrame:</b><br />
	<pre><code class="xml hljs">&lt;iframe src="<?php echo $url; ?>[server_id]" style="border: 0; width:400px; height: 120px">&lt;/iframe&gt;</code></pre>
	
	<h2><?php echo $lang->_('SERVER_WITH_WIDGETS'); ?></h2>
	<?php
	$sql = $db->query("SELECT * FROM servers WHERE ".((!$login->Access()) ? "member_id='{$login->id}' AND " : "")." widget='1' ORDER BY display_name ASC");
	?>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>id</th>
				<th><?php echo $lang->_('SERVER'); ?></th>
				<th><?php echo $lang->_('WIDGET_URL'); ?></th>
				<th><?php echo $lang->_('OWNER'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
				while($row = $sql->fetch_assoc()) {
					$user = $db->query("SELECT * FROM users WHERE id='{$row['member_id']}'")->fetch_assoc();
			?>
				<tr>
					<td><?php echo $row['id']; ?></td>
					<td><a href="index.php?p=dashboard&s=<?php echo $row['id']; ?>"><?php echo $row['display_name']; ?></a></td>
					<td><a href="<?php echo "{$url}".$row['id']; ?>" target="_blank">Open</a></td>
					<td><?php echo "{$user['username']} ({$user['email']})"; ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>