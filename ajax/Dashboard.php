<?php
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");

require_once("../includes/autoload.php");

$lang->setLang($login->lang);

if(!$login->LoggedIn) {
	die("<div class='alert alert-danger'>{$lang->_('LOGIN_TOKEN_EXPIRED')}</div>");
}
if(isset($_GET['s'])) {
	$id = $db->real_escape_string($_GET['s']);
	$sql = $db->query("SELECT * FROM servers WHERE id='{$id}' AND deleted='0' ".((!$login->Access()) ? "AND member_id='{$login->id}'" : ""));
	$row = $sql->fetch_assoc();
	if($sql->num_rows == 0){
		echo "<div class='alert alert-danger'>{$lang->_('SERVER_NOT_FOUND')}</div>";
	}else{
		$count = $db->query("SELECT id FROM server_stats WHERE server_id='{$id}'");
		$avg = "0";
		$uptime = "0";
		$total_uptime = "0";
		$succeed = "0";
		$failed = "0";
		
		if($row['timeout'] == 0) {
			$timeout = $config['timeout'];
		} else {
			$timeout = $row['timeout'];
		}
		
		if($count->num_rows != "0") {
			$avg_sum = $db->query("SELECT SUM(load_time) AS c FROM server_stats WHERE server_id='{$id}'");
			$avg_sum = $avg_sum->fetch_assoc();
			$avg_num = $db->query("SELECT * FROM server_stats WHERE server_id='{$id}'");
			$avg = $avg_sum['c'] / $avg_num->num_rows;

			$succeed = $db->query("SELECT * FROM server_stats WHERE server_id='{$id}' AND state='active'");
			$succeed = $succeed->num_rows;
			$failed = $db->query("SELECT * FROM server_stats WHERE server_id='{$id}' AND state='down'");
			$failed = $failed->num_rows;
			$uptime = $function->Uptime($succeed, $failed);
			
			$total_uptime = $db->query("SELECT SUM(request_succeed) AS succeed, SUM(request_failed) AS failed FROM history WHERE server_id='{$id}'");
			$total_uptime = $total_uptime->fetch_assoc();
			$total_uptime = $function->Uptime($total_uptime['succeed'] + $succeed, $total_uptime['failed'] + $failed);
		}
?>
<script type="text/javascript">
$(function () {
	$('[data-toggle="tooltip"]').tooltip();
	$('#piechart').highcharts({
		chart: {
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false,
			backgroundColor: "#<?php echo $function->Theme($login->Theme, "ChartBG"); ?>",
			type: 'pie'
		},
		title: {
			style: {
				color: "#<?php echo $function->Theme($login->Theme, "ChartFont"); ?>"
			},
			text: '<?php echo $lang->_('SUCCEEDED_FAILED_REQUESTS_TODAY'); ?>'
		},
		tooltip: {
			pointFormat: '{series.name}: <b>{point.percentage:.0f}%</b>'
		},
		lotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
		series: [{
			name: 'Brands',
			colorByPoint: true,
			data: [{
				name: '<?php echo $lang->_('REQUESTS_SUCCEED'); ?>',
				y: <?php echo $succeed; ?>
			}, {
				name: '<?php echo $lang->_('REQUESTS_FAILED'); ?>',
				y: <?php echo $failed; ?>
			}]
		}],
		credits: {
			enabled: false
		}
	});
	
	$('#curve_chart').highcharts({
		exporting: { enabled: false }, 
		chart: {
			style: {
				color: "#<?php echo $function->Theme($login->Theme, "ChartFont"); ?>"
			},
			backgroundColor: "#<?php echo $function->Theme($login->Theme, "ChartBG"); ?>",
			type: 'area'
		},
		title: {
			style: {
				color: "#<?php echo $function->Theme($login->Theme, "ChartFont"); ?>"
			},
			text: '<?php echo $lang->_('LOAD_TIMES_TODAY'); ?>'
		},
		xAxis: {
			categories: [<?php
				$sql = $db->query("SELECT check_date FROM server_stats WHERE server_id='{$id}' ORDER BY id ASC");
				while($row_LoadTimes = $sql->fetch_assoc()) {
					echo "'".date("H:i", strtotime($row_LoadTimes['check_date']))."',";
				}
			?>],
			tickmarkPlacement: 'on',
			title: {
				style: {
					color: "#<?php echo $function->Theme($login->Theme, "ChartFont"); ?>"
				},
				enabled: false
			},
			labels: {
				style: {
					color: "#<?php echo $function->Theme($login->Theme, "ChartFont"); ?>"
				}
			}
		},
		yAxis: {
			title: {
				style: {
					color: "#<?php echo $function->Theme($login->Theme, "ChartFont"); ?>"
				},
				text: '<?php echo $lang->_('LOAD_TIME_IN_SEC'); ?>'
			},
			labels: {
				style: {
					color: "#<?php echo $function->Theme($login->Theme, "ChartFont"); ?>"
				}
			}
		},
		tooltip: {
			shared: true
		},
		legend: {
			itemStyle: {
				color: "#<?php echo $function->Theme($login->Theme, "ChartFont"); ?>"
			}, 
			itemHoverStyle: {
				color: '#<?php echo $function->Theme($login->Theme, "ChartFont"); ?>'
			},
		},
		plotOptions: {
			area: {
				style: {
					color: "#<?php echo $function->Theme($login->Theme, "ChartFont"); ?>"
				},
				stacking: 'normal',
				lineColor: '#666666',
				lineWidth: 1,
				marker: {
					enabled: false,
					symbol: 'circle',
					lineWidth: 1,
					lineColor: '#666666'
				}
			}
		},
		series: [{
			name: 'Load time',
			data: [<?php
					$sql = $db->query("SELECT load_time FROM server_stats WHERE server_id='{$id}' ORDER BY id ASC");
					while($row_LoadTimes = $sql->fetch_assoc()) {
						echo $row_LoadTimes['load_time'].",";
					}
				?>]
		}],
		credits: {
			enabled: false
		}
	});
});
</script>

<div class="row marketing">
	<div class="col-lg-6">
		<h4><?php echo $lang->_('STATISTICS_TODAY'); ?></h4>
		<table style="width: 400px;" class="table table-striped">
			<?php
				if($login->Access()) {
					$user = $db->query("SELECT * FROM users WHERE id='{$row['member_id']}'")->fetch_assoc();
			?>
				<tr>
					<th><?php echo $lang->_('OWNER'); ?></th>
					<td><a href="index.php?p=users&edit=<?php echo $row['id']; ?>"><?php echo $user['username']; ?> (<?php echo $user['email']; ?>)</a></td>
				</tr>
			<?php } ?>
			<tr>
				<th><?php echo $lang->_('NAME'); ?></th>
				<td><?php echo $row['display_name']; ?></td>
			</tr>
			<tr>
				<th>URL</th>
				<td><a href="<?php echo $row['server_url']; ?>" target="_blank"><?php echo $row['server_url']; ?></a></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('TODAY_UPTIME'); ?></th>
				<td><?php echo $uptime; ?>%</td>
			</tr>
			<tr>
				<th><?php echo $lang->_('TOTAL_UPTIME'); ?></th>
				<td><?php echo $total_uptime; ?>%</td>
			</tr>
			<tr>
				<th><?php echo $lang->_('SERVER_UP_FOR'); ?></th>
				<td><?php
					if($row['state'] == 'active' && $row['back_online'] != "0000-00-00 00:00:00") {
						if(function_exists('date_diff')) {
							$datetime1 = new DateTime($row['back_online']);
							$datetime2 = new DateTime(date("Y-m-d H:i:s"));
							$date = $datetime1->diff($datetime2);
							echo $date->format($lang->_('DATEFORMAT'));
						} else {
							$diff = $function->dateDiff("now", $row['back_online']);
							echo $lang->_('DATEFORMAT', array("%a" => $diff[0], "%h" => $diff[1], "%i" => $diff[2]));
						}
					} else {
						echo $lang->_('UNKNOWN');
					}
				?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('LAST_LOAD_TIME'); ?></th>
				<td><?php echo $row['last_load']; ?> <?php echo $lang->_('SECONDS'); ?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('AVERAGE'); ?></th>
				<td><?php echo round($avg, 3); ?> <?php echo $lang->_('SECONDS'); ?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('REQUESTS_SUCCEED'); ?></th>
				<td><?php echo $succeed; ?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('REQUESTS_FAILED'); ?></th>
				<td><?php echo $failed; ?></td>
			</tr>
		</table>
	</div>
	<div class="col-lg-6">
		<h4><?php echo $lang->_('DOWNTIME'); ?></h4>
		<table style="width: 400px;" class="table table-striped">
			<tr>
				<th><?php echo $lang->_('CURRENT'); ?></th>
				<td><?php if($row['deleted'] == "1") { echo $server->Color("deleted"); }elseif($row['disabled'] == "1") { echo $server->Color("disabled"); }else{ echo $server->Color($row['state']); } ?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('LAST_OFFLINE'); ?></th>
				<td><?php echo (($row['last_down'] == "0000-00-00 00:00:00") ? $lang->_('NEVER') : date($config['date_format'] . " " . $config['time_format'], strtotime($row['last_down']))); ?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('BACK_ONLINE'); ?></th>
				<td><?php echo (($row['back_online'] == "0000-00-00 00:00:00") ? $lang->_('NEVER') : date($config['date_format'] . " " . $config['time_format'], strtotime($row['back_online']))); ?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('TIME_OFFLINE'); ?></th>
				<td>
					<?php 
						if($row['last_down'] != "0000-00-00 00:00:00") {
							if($row['back_online'] == "0000-00-00 00:00:00") { $date = date("Y-m-d H:i:s"); }else{ $date = $row['back_online']; }
							if($row['state'] == 'down') { $date = date("Y-m-d H:i:s"); }
							
							if(function_exists('date_diff')) {
								$datetime1 = new DateTime($row['last_down']);
								$datetime2 = new DateTime($date);
								$date = $datetime1->diff($datetime2);
								echo $date->format($lang->_('DATEFORMAT'));
							} else {
								$diff = $function->dateDiff($date, $row['last_down']);
								echo $lang->_('DATEFORMAT', array("%a" => $diff[0], "%h" => $diff[1], "%i" => $diff[2]));
							}
							
							
						} else {
							echo $lang->_('UNKNOWN');
						}
					?>
				</td>
			</tr>
		</table>
		<br />
		<?php
		$sql = $db->query("SELECT * FROM server_stats WHERE server_id='{$id}' AND state='down' GROUP BY response_code ORDER BY response_code DESC");
		?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th><?php echo $lang->_('GIVEN'); ?></th>
					<th><?php echo $lang->_('RESPONSE_CODE'); ?></th>
					<th><?php echo $lang->_('LOAD_TIME'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
				if($sql->num_rows == 0) {
					echo "<tr><td>{$lang->_('SERVER_NO_DOWNTIME_TODAY')}</td><td></td><td></td></tr>";
				}else{
				while($row = $sql->fetch_assoc()) { 
					$count = $db->query("SELECT * FROM server_stats WHERE server_id='{$id}' AND response_code='{$row['response_code']}'");
					$count = $count->num_rows;
			?>
				<tr>
					<td><?php echo $count; ?>x</td>
					<td><div class="label label-danger"><?php echo $row['response_code']; ?> <span style="margin-left: 3px;" class="fa fa-question-circle" <?php echo $function->Tooltip((($row['response_code'] != "0") ? $server->ResponseName($row['response_code']) : $row['curl_error'])); ?>></span></div></td>
					<td><div class="progress" <?php echo $function->Tooltip($row['load_time']." ".$lang->_('SECONDS')); ?>>
							<div class="progress-bar progress-bar-<?php echo $server->ProgressColor($function->Procent($timeout, $row['load_time'])); ?> progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo $function->Procent($timeout, $row['load_time']); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $function->Procent($timeout, $row['load_time']); ?>%"></div>
						</div>
					</td>
				</tr>
			<?php } } ?>
			</tbody>
		</table>
	</div>
</div>

<div class="row marketing">
	<div class="col-lg-12">
		<h4><?php echo $lang->_('SERVER_EVENTS'); ?></h4>
		<table class="table table-striped table-condensed" style="margin: 0px;">
			<tr>
				<th width="20%"><?php echo $lang->_('STATE'); ?></th>
				<th width="20%"><?php echo $lang->_('RESPONSE_CODE'); ?></th>
				<th width="40%"><?php echo $lang->_('DATE'); ?></th>
			</tr>
		</table>
		<div style="max-height: 500px; overflow-y: scroll;">
			<table class="table table-striped table-condensed">
				<?php
					$sql = $db->query("SELECT * FROM server_events WHERE server_id='{$id}' ORDER BY id DESC");
					while($row = $sql->fetch_assoc()) {
						if($row["state"] == "up") {
							$state = '<div class="label label-success"><i class="fa fa-arrow-up"></i> '.$lang->_('ONLINE').'</div>';
							$color = 'success';
						}else{
							$state = '<div class="label label-danger"><i class="fa fa-arrow-down"></i> '.$lang->_('OFFLINE').'</div>';
							$color = 'danger';
						}
						$server_name = $db->query("SELECT id, display_name FROM servers WHERE id='{$row['server_id']}'");
						$server_name = $server_name->fetch_assoc();
				?>
				<tr class="<?php echo $color; ?>">
					<td width="20%"><?php echo $state; ?></td>
					<td width="20%"><?php echo $row['response_code']; ?> <span class="fa fa-question-circle" <?php echo $function->Tooltip((($row['response_code'] != "0") ? $server->ResponseName($row['response_code']) : $row['curl_error'])); ?>></span></td>
					<td width="40%"><?php echo date($config['date_format'] . " " . $config['time_format'], strtotime($row['date'])); ?></td>
				</tr>
				<?php
					}
				?>
			</table>
		</div>
	</div>
</div>

<div class="row marketing">
	<h4><?php echo $lang->_('RESPONSE_CODES_TODAY'); ?></h4>
	<div class="col-lg-6">
		<div id="piechart" style="width: 100%; height: 300px;"></div>
	</div>
	<div class="col-lg-6">
		<div id="curve_chart" style="width: 100%; height: 300px;"></div>
	</div>
</div>

<div class="row marketing">
	<div class="col-lg-12">
		<table class="table table-striped table-condensed" style="margin: 0px;">
			<tr>
				<th width="20%"><?php echo $lang->_('DATE'); ?></th>
				<th width="20%"><?php echo $lang->_('RESPONSE_CODE'); ?></th>
				<th width="20%"><?php echo $lang->_('LOAD_TIME'); ?></th>
				<th width="40%"><?php echo $lang->_('STATE'); ?></th>
			</tr>
		</table>
		<div style="height: 500px; overflow-y: scroll;">
			<table class="table table-striped table-condensed">
				<?php
					$sql = $db->query("SELECT * FROM server_stats WHERE server_id='{$id}' ORDER BY id DESC");
					while($row = $sql->fetch_assoc()) {
						if($row["state"] == "active") {
							$state = '<div class="label label-success"><i class="fa fa-arrow-up"></i> '.$lang->_('ONLINE').'</div>';
							$color = 'success';
						}else{
							$state = '<div class="label label-danger"><i class="fa fa-arrow-down"></i> '.$lang->_('OFFLINE').'</div>';
							$color = 'danger';
						}
				?>
				<tr class="<?php echo $color; ?>">
					<td width="20%"><?php echo date($config['date_format'] . " " . $config['time_format'], strtotime($row['check_date'])); ?></td>
					<td width="20%"><?php echo $row['response_code']; ?> <span class="fa fa-question-circle" <?php echo $function->Tooltip((($row['response_code'] != "0") ? $server->ResponseName($row['response_code']) : $row['curl_error'])); ?>></span></td>
					<td width="20%"><?php echo $row['load_time']; ?></td>
					<td width="40%"><?php echo $state; ?></td>
				</tr>
				<?php
					}
				?>
			</table>
			
			
		</div>
	</div>
</div>

<?php
	}
}else{
$user = $db->query("SELECT * FROM users WHERE id='{$login->id}'")->fetch_assoc();

$s1_name['display_name'] = "NaN";
$s2_name['display_name'] = "NaN";
$s1_avg = "0";
$s2_avg = "0";
$total_avg = "0";

if($user['chart_1'] != "0") {
	$s1_name = $db->query("SELECT display_name FROM servers WHERE id='".$user['chart_1']."'")->fetch_assoc();
	
	$count_total = $db->query("SELECT * FROM server_stats WHERE server_id='".$user['chart_1']."'");
	$s1_avg = "0";
	if($count_total->num_rows != 0) {
		$s1_avg_sum = $db->query("SELECT SUM(load_time) AS s1_c FROM server_stats WHERE server_id='".$user['chart_1']."'")->fetch_assoc();
		$s1_avg_num = $db->query("SELECT * FROM server_stats WHERE server_id='".$user['chart_1']."'")->num_rows;
		$s1_avg = $s1_avg_sum['s1_c'] / $s1_avg_num;
	}
}

if($user['chart_2'] != "0") {
	$s2_name = $db->query("SELECT display_name FROM servers WHERE id='".$user['chart_2']."'")->fetch_assoc();
	
	$count_total = $db->query("SELECT * FROM server_stats WHERE server_id='".$user['chart_2']."'");
	$s2_avg = "0";
	if($count_total->num_rows != 0) {
		$s2_avg_sum = $db->query("SELECT SUM(load_time) AS s2_c FROM server_stats WHERE server_id='".$user['chart_2']."'")->fetch_assoc();
		$s2_avg_num = $db->query("SELECT * FROM server_stats WHERE server_id='".$user['chart_2']."'")->num_rows;
		$s2_avg = $s2_avg_sum['s2_c'] / $s2_avg_num;
	}
}

$count_total = $db->query("SELECT * FROM server_stats ".((!$login->Access()) ? "WHERE member_id='{$login->id}'" : ""));
if($count_total->num_rows != 0) {
	$total_avg_sum = $db->query("SELECT SUM(load_time) AS total_c FROM server_stats ".((!$login->Access()) ? "WHERE member_id='{$login->id}'" : ""))->fetch_assoc();
	$total_avg_num = $db->query("SELECT * FROM server_stats ".((!$login->Access()) ? "WHERE member_id='{$login->id}'" : ""))->num_rows;
	$total_avg = $total_avg_sum['total_c'] / $total_avg_num;
}
?>
<script type="text/javascript">
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
		$(".dial").knob({
			'min':0,
			'max':<?php echo $config["timeout"] + 1; ?>,
			'readOnly': true,
			'dynamicDraw': true,
			<?php echo $function->Theme($login->Theme, "knob"); ?>
		});
	})
</script>
<div class="row marketing">
	<div class="col-lg-4">
		<center>
			<h4><?php echo $lang->_('AVERAGE'); ?> (<?php echo $s1_name['display_name']; ?>)</h4>
			<p><input type="text" value="<?php echo round($s1_avg, 2); ?>" data-readOnly="true" data-angleArc="250" data-angleOffset="-125" class="dial"></p>
		</center>
	</div>
	<div class="col-lg-4">
		<center>
			<h4 style="margin-left: 20px;"><?php echo $lang->_('AVERAGE'); ?> (<?php echo $s2_name['display_name']; ?>)</h4>
			<p><input type="text" value="<?php echo round($s2_avg, 2); ?>" data-readOnly="true" data-angleArc="250" data-angleOffset="-125" class="dial"></p>
		</center>
	</div>
	<div class="col-lg-4">
		<center>
			<h4 style="margin-left: 30px;"><?php echo $lang->_('AVERAGE'); ?> (<?php echo $lang->_('ALL_SERVERS'); ?>)</h4>
			<p><input type="text" value="<?php echo round($total_avg, 2); ?>" data-readOnly="true" data-angleArc="250" data-angleOffset="-125" class="dial"></p>
		</center>
	</div>
</div>

<div class="row marketing">
	<div class="col-lg-6">
		<?php
			$sql = $db->query("SELECT * FROM servers WHERE ".((!$login->Access()) ? "member_id='{$login->id}' AND" : "")." state='down'");
		?>
		<h4><?php echo $lang->_('CURRENTLY_DOWN'); ?></h4>
		<?php
		if($sql->num_rows == 0) {
			echo $lang->_('NO_SERVERS_DOWN');
		}else{
		?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th><?php echo $lang->_('SERVER'); ?></th>
					<th><?php echo $lang->_('RESPONSE'); ?></th>
					<th><?php echo $lang->_('LOAD_TIME'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php 
				while($row = $sql->fetch_assoc()) {
				if($row['timeout'] == 0) {
					$timeout = $config['timeout'];
				} else {
					$timeout = $row['timeout'];
				}
			?>
				<tr>
					<td><a href="index.php?p=dashboard&s=<?php echo $row['id']; ?>"><?php echo $row['display_name']; ?></a></td>
					<td><div class="label label-danger"><?php echo $row['response_code']; ?> <span style="margin-left: 3px;" class="fa fa-question-circle" <?php echo $function->Tooltip((($row['response_code'] != "0") ? $server->ResponseName($row['response_code']) : $row['curl_error'])); ?>></span></div></td>
					<td><div class="progress" <?php echo $function->Tooltip($row['last_load']." seconds"); ?>>
							<div class="progress-bar progress-bar-<?php echo $server->ProgressColor($function->Procent($timeout, $row['last_load'])); ?> progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo $function->Procent($timeout, $row['last_load']); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $function->Procent($timeout, $row['last_load']); ?>%"></div>
						</div>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php } ?>
	</div>
	<div class="col-lg-6">
		<?php
			$sql = $db->query("SELECT DISTINCT response_code FROM server_stats ".((!$login->Access()) ? "WHERE member_id='{$login->id}'" : "")." ORDER BY response_code ASC");
		?>
		<h4><?php echo $lang->_('TOTAL_RESPONSE_CODES'); ?></h4>
		<table class="table table-striped">
			<thead>
				<tr>
					<th><?php echo $lang->_('RESPONSE_CODE'); ?></th>
					<th><?php echo $lang->_('TOTAL'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php 
				while($row = $sql->fetch_assoc()) { 
				$count = $db->query("SELECT * FROM server_stats WHERE ".((!$login->Access()) ? "member_id='{$login->id}' AND" : "")." response_code='{$row['response_code']}'");
				$count = $count->num_rows;
			?>
				<tr>
					<td><span <?php echo $function->Tooltip($server->ResponseName($row['response_code'])); ?>><?php echo $row['response_code']; ?></span></td>
					<td><?php echo $count; ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<div class="row marketing">
	<div class="col-lg-12">
		<h4><?php echo $lang->_('LATEST_SERVER_EVENTS'); ?> (<?php echo strtolower($lang->_('ALL_SERVERS')); ?>)</h4>
		<table class="table table-striped table-condensed" style="margin: 0px;">
			<tr>
				<th width="20%"><?php echo $lang->_('STATE'); ?></th>
				<th width="30%"><?php echo $lang->_('SERVER'); ?></th>
				<th width="20%"><?php echo $lang->_('RESPONSE_CODE'); ?></th>
				<th width="40%"><?php echo $lang->_('DATE'); ?></th>
			</tr>
		</table>
		<div style="max-height: 500px; overflow-y: scroll;">
			<table class="table table-striped table-condensed">
				<?php
					$sql = $db->query("SELECT * FROM server_events ".((!$login->Access()) ? "WHERE member_id='{$login->id}'" : "")." ORDER BY id DESC LIMIT 20");
					while($row = $sql->fetch_assoc()) {
						if($row["state"] == "up") {
							$state = '<div class="label label-success"><i class="fa fa-arrow-up"></i> '.$lang->_('ONLINE').'</div>';
							$color = 'success';
						}else{
							$state = '<div class="label label-danger"><i class="fa fa-arrow-down"></i> '.$lang->_('OFFLINE').'</div>';
							$color = 'danger';
						}
						$server_name = $db->query("SELECT id, display_name FROM servers WHERE id='{$row['server_id']}'");
						$server_name = $server_name->fetch_assoc();
				?>
				<tr class="<?php echo $color; ?>">
					<td width="20%"><?php echo $state; ?></td>
					<td width="30%"><a href="index.php?p=dashboard&s=<?php echo $row['server_id']; ?>"><?php echo $server_name['display_name']; ?></a></td>
					<td width="20%"><?php echo $row['response_code']; ?> <span class="fa fa-question-circle" <?php echo $function->Tooltip((($row['response_code'] != "0") ? $server->ResponseName($row['response_code']) : $row['curl_error'])); ?>></span></td>
					<td width="40%"><?php echo date($config['date_format'] . " " . $config['time_format'], strtotime($row['date'])); ?></td>
				</tr>
				<?php
					}
				?>
			</table>
		</div>
	</div>
</div>
<div class="row marketing">
	<div class="col-lg-12">
		<h4><?php echo $lang->_('RESPONSE_CODES_LAST_HOUR'); ?> (<?php echo $lang->_('ALL_SERVERS'); ?>)</h4>
		<table class="table table-striped table-condensed" style="margin: 0px;">
			<tr>
				<th width="30%"><?php echo $lang->_('SERVER'); ?></th>
				<th width="30%"><?php echo $lang->_('DATE'); ?></th>
				<th width="20%"><?php echo $lang->_('RESPONSE_CODE'); ?></th>
				<th width="20%"><?php echo $lang->_('LOAD_TIME'); ?></th>
				<th width="40%"><?php echo $lang->_('STATE'); ?></th>
			</tr>
		</table>
		<div style="max-height: 500px; overflow-y: scroll;">
			<table class="table table-striped table-condensed">
				<?php
					$sql = $db->query("SELECT * FROM server_stats WHERE ".((!$login->Access()) ? "member_id='{$login->id}' AND" : "")." check_date > DATE_SUB(NOW(), INTERVAL 1 HOUR) ORDER BY id DESC");
					while($row = $sql->fetch_assoc()) {
						if($row["state"] == "active") {
							$state = '<div class="label label-success"><i class="fa fa-arrow-up"></i> '.$lang->_('ONLINE').'</div>';
							$color = 'success';
						}else{
							$state = '<div class="label label-danger"><i class="fa fa-arrow-down"></i> '.$lang->_('OFFLINE').'</div>';
							$color = 'danger';
						}
						$server_name = $db->query("SELECT id, display_name FROM servers WHERE id='{$row['server_id']}'");
						$server_name = $server_name->fetch_assoc();
				?>
				<tr class="<?php echo $color; ?>">
					<td width="30%"><a href="index.php?p=dashboard&s=<?php echo $row['server_id']; ?>"><?php echo $server_name['display_name']; ?></a></td>
					<td width="30%"><?php echo date($config['date_format'] . " " . $config['time_format'], strtotime($row['check_date'])); ?></td>
					<td width="20%"><?php echo $row['response_code']; ?> <span class="fa fa-question-circle" <?php echo $function->Tooltip((($row['response_code'] != "0") ? $server->ResponseName($row['response_code']) : $row['curl_error'])); ?>></span></td>
					<td width="20%"><?php echo $row['load_time']; ?></td>
					<td width="40%"><?php echo $state; ?></td>
				</tr>
				<?php
					}
				?>
			</table>
		</div>
	</div>
</div>
<?php
}
$db->close();
?>