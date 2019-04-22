<?php
$server_id = "";
if(isset($_GET['s'])) {
	$server_id = "?s=".$db->real_escape_string($_GET['s']);
}
?>
<script type="text/javascript">
function Reload() {
	$( "#Loading" ).load( "ajax/Dashboard.php<?php echo $server_id; ?>" );
}

function ReloadDashboard() {
	$('#Loading').load('ajax/Dashboard.php<?php echo $server_id; ?>');
}

$( window ).load(function() {
	ReloadDashboard();
	
	setInterval(function() {
		ReloadDashboard();
	}, 60000);
});
</script>

<div id="Loading"><div class="alert alert-info"><?php echo $lang->_('LOADING_DASHBOARD'); ?></div></div>
