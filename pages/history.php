<?php
if(isset($_GET['s'])) {
	$id = $db->real_escape_string($_GET['s']);
	$sql = $db->query("SELECT * FROM servers WHERE ".((!$login->Access()) ? "member_id='{$login->id}' AND " : "")." id='{$id}'");
	$row = $sql->fetch_assoc();
	
	if($sql->num_rows == 0) {
		echo '<div class="alert alert-danger">'.$lang->_('SERVER_NOT_FOUND').'</div>';
	} else {
		if(isset($_GET['date'])) {
			$date = $db->real_escape_string($_GET['date']);
			$got_history = $db->query("SELECT * FROM history WHERE server_id='{$id}' AND date='{$date}' ORDER BY date DESC LIMIT 1");
			if($got_history->num_rows == 0) { 
				$got_history = $db->query("SELECT * FROM history WHERE server_id='{$id}' ORDER BY date DESC LIMIT 1");
				$history = $got_history->fetch_assoc();
				$text = $lang->_('YESTERDAY');
			} else {
				$history = $got_history->fetch_assoc();
				$text = date('d-m-Y', strtotime($date));
			}
		} else {
			$got_history = $db->query("SELECT * FROM history WHERE server_id='{$id}' ORDER BY date DESC LIMIT 1");
			$history = $got_history->fetch_assoc();
			$text = date('d-m-Y', strtotime($history['date']));
		}
		if($got_history->num_rows == 0 ) {
			echo '<div class="alert alert-danger">'.$lang->_('HISTORY_NO_DATA').'</div>';
		} else {
?>
<script type="text/javascript">
$(function () {
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
			text: '<?php echo $lang->_('SUCCEEDED_FAILED_REQUESTS'); ?> <?php echo $text; ?>'
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
				y: <?php echo $history['request_succeed']; ?>
			}, {
				name: '<?php echo $lang->_('REQUESTS_FAILED'); ?>',
				y: <?php echo $history['request_failed']; ?>
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
			text: '<?php echo $lang->_('AVERAGE_LOADTIME_HISTORY'); ?>'
		},
		xAxis: {
			categories: [<?php

				$sql = $db->query("SELECT date FROM history WHERE server_id='{$id}' ORDER BY id ASC");
				while($row_LoadTimes = $sql->fetch_assoc()) {
					echo "'".date($config['date_format'], strtotime($row_LoadTimes['date']))."',";
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
					enabled: true,
					symbol: 'circle',
					lineWidth: 1,
					lineColor: '#666666'
				}
			}
		},
		series: [{
			name: '<?php echo $lang->_('AVERAGE_LOADTIME'); ?>',
			data: [<?php
					$sql = $db->query("SELECT load_average FROM history WHERE server_id='{$id}' ORDER BY id ASC");
					while($row_LoadTimes = $sql->fetch_assoc()) {
						echo $row_LoadTimes['load_average'].",";
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
	<div class="col-lg-6" style="margin-top: -30px;">
		<h3><?php echo $lang->_('STATISTICS'); ?> <?php echo $text; ?></h3>
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
				<th><?php echo $lang->_('UPTIME'); ?></th>
				<td><?php echo $function->Uptime($history['request_succeed'], $history['request_failed']); ?>%</td>
			</tr>
			<tr>
				<th><?php echo $lang->_('AVERAGE_LOADTIME'); ?></th>
				<td><?php echo $history['load_average']; ?> <?php echo $lang->_('SECONDS'); ?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('REQUESTS_SUCCEED'); ?></th>
				<td><?php echo $history['request_succeed']; ?></td>
			</tr>
			<tr>
				<th><?php echo $lang->_('REQUESTS_FAILED'); ?></th>
				<td><?php echo $history['request_failed']; ?></td>
			</tr>
		</table>
	</div>
	<div class="col-lg-6" style="margin-top: -30px;">
		<?php $json = json_decode($history['response_codes']); ?>
		<h3><?php echo $lang->_('RESPONSE_CODE'); ?> <?php echo $text; ?></h3>
		<table class="table table-striped">
			<thead>
				<tr>
					<th><?php echo $lang->_('GIVEN'); ?></th>
					<th><?php echo $lang->_('RESPONSE_CODE'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
				foreach($json as $data) {
					if($data->state == "active") {
						$color = "success";
					} else if($data->state == "down") {
						$color = "danger";
					} else {
						$color = "warning";
					}
			?>
				<tr>
					<td><?php echo $data->given; ?>x</td>
					<td><div class="label label-<?php echo $color; ?>"><?php echo $data->code; ?> <span style="margin-left: 3px;" class="fa fa-question-circle" <?php echo (($data->code != "0") ? $function->Tooltip($server->ResponseName($data->code)) : ""); ?>></span></div></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<div id="piechart" style="width: 100%; height: 300px;"></div>
	</div>
</div>
<div class="row marketing">
	<div class="col-lg-12">
		<h3><?php echo $lang->_('HISTORY'); ?></h3>
		<div id="curve_chart" style="width: 100%; height: 300px;"></div>
		<table class="table table-striped table-condensed" style="margin: 0px;">
			<tr>
				<th width="20%"><?php echo $lang->_('DATE'); ?></th>
				<th width="20%"><?php echo $lang->_('UPTIME'); ?></th>
				<th width="20%"><?php echo $lang->_('LOAD_TIME'); ?></th>
				<th width="40%"><?php echo $lang->_('SUCCEEDED_FAILED_REQUESTS'); ?></th>
				<th width="20%"></th>
			</tr>
		</table>
		<div style="height: 500px; overflow-y: scroll;">
			<table class="table table-striped table-condensed">
				<?php
					$sql = $db->query("SELECT * FROM history WHERE server_id='{$id}' ORDER BY id DESC");
					$i=0;
					while($row = $sql->fetch_assoc()) {
				?>
				<tr>
					<td width="20%"><?php echo date($config['date_format'], strtotime($row['date'])); ?></td>
					<td width="20%"><?php echo $function->Uptime($row['request_succeed'], $row['request_failed']); ?>%</td>
					<td width="20%"><?php echo $row['load_average']; ?></td>
					<td width="40%"><?php echo $row['request_succeed']." / ".$row['request_failed']; ?></td>
					<?php if($i == 0) { ?>
						<td width="20%"><span style="cursor: pointer;" onclick="location.href='index.php?p=history&s=<?php echo $id; ?>'" class="label label-success" <?php echo $function->Tooltip($lang->_('SHOW_THIS_INFORMATION')); ?>><span class="fa fa-eye"></span></span></td>
					<?php } else { ?>
						<td width="20%"><span style="cursor: pointer;" onclick="location.href='index.php?p=history&s=<?php echo $id; ?>&date=<?php echo $row['date']; ?>'" class="label label-success" <?php echo $function->Tooltip($lang->_('SHOW_THIS_INFORMATION')); ?>><span class="fa fa-eye"></span></span></td>
					<?php } ?>
				</tr>
				<?php
					$i++;
					}
				?>
			</table>
			
			
		</div>
	</div>
</div>
<?php	
		}
	}
} else {
$sql = $db->query("SELECT * FROM servers ".((!$login->Access()) ? "WHERE member_id='{$login->id}'" : "")." ORDER BY display_name ASC");
?>
<div class="row marketing">
	<h2 style="margin-top: -10px;"><?php echo $lang->_("HISTORY"); ?></h2>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?php echo $lang->_('SERVER'); ?></th>
				<th width="20%"><?php echo $lang->_('LAST_UPTIME'); ?></th>
				<th width="20%"><?php echo $lang->_('LAST_AVERAGE_LOADTIME'); ?></th>
				<th width="20%"><?php echo $lang->_('SUCCEEDED_FAILED_REQUESTS'); ?></th>
				<th width="20%"><?php echo $lang->_('HISTORY_FROM_DAY'); ?></th>
				<th><?php echo $lang->_('OPTIONS'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
				while($row = $sql->fetch_assoc()) {
					$got_history = $db->query("SELECT * FROM history WHERE server_id='{$row['id']}' ORDER BY date DESC LIMIT 1");
					$history = $got_history->fetch_assoc();
					if($got_history->num_rows != 0) {
						$uptime = $function->Uptime($history['request_succeed'], $history['request_failed']);
			?>
				<tr>
					<td><a href="index.php?p=history&s=<?php echo $row['id']; ?>"><?php echo $row['display_name']; ?></a></td>
					<td>
						<div class="progress" <?php echo $function->Tooltip($lang->_('UPTIME') . ": ".$uptime."%"); ?>>
							<div class="progress-bar active progress-bar-<?php echo $server->Uptime($uptime); ?> progress-bar-striped" role="progressbar" aria-valuenow="<?php echo $uptime; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $uptime; ?>%"></div>
						</div>
					</td>
					<td>
						<div class="progress" <?php echo $function->Tooltip($history['load_average']." ".$lang->_('SECONDS')); ?>>
							<div class="progress-bar progress-bar-<?php echo $server->ProgressColor($function->Procent($config['timeout'], $history['load_average'])); ?> progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo $function->Procent($config['timeout'], $history['load_average']); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $function->Procent($config['timeout'], $history['load_average']); ?>%"></div>
						</div>
					</td>
					<td><?php echo $history['request_succeed']." / ".$history['request_failed']; ?></td>
					<td><?php echo date($config['date_format'], strtotime($history['date'])); ?></td>
					<td>
						<?php if($row['deleted'] == "0") { ?>
							<?php 
								if($login->Access()) {
									$user = $db->query("SELECT * FROM users WHERE id='{$row['member_id']}'")->fetch_assoc();
							?>
								<span class="label label-default" <?php echo $function->Tooltip("{$user['username']} ({$user['email']})"); ?>><span class="fa fa-user"></span></span>
							<?php } ?>
							<span style="cursor: pointer;" onclick="location.href='index.php?p=history&s=<?php echo $row['id']; ?>'" class="label label-success" <?php echo $function->Tooltip($lang->_('STATISTICS')); ?>><span class="fa fa-line-chart"></span></span>
						<?php }else{ echo $lang->_('NOT_AVAILABLE'); } ?>
					</td>
				</tr>
			<?php } } ?>
		</tbody>
	</table>
</div>
<?php } ?>