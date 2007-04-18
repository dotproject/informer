<?php /* informer $Id: vw_monthly.php 2007/04/11 14:07 weboholic */
	global $INFORMER_CONFIG;
	$perms =& $AppUI->acl();
	
	$m = $AppUI->checkFileName( dPgetParam( $_GET,'m',getReadableModule() ) );
	$denyEdit = getDenyEdit( $m );
	if( $denyEdit ) $AppUI->redirect( 'm=public&a=access_denied' );

	//grab hours per day from config
	$show_possible_hours_worked = $INFORMER_CONFIG['show_possible_hours_worked'];
	if( $show_possible_hours_worked ) $opp_hours = ( $dPconfig['daily_working_hours'] ) ? $dPconfig['daily_working_hours'] : 8;
	
	//get date format
	$df = $AppUI->getPref('SHDATEFORMAT');
	$user_id = $AppUI->user_id;

	$AppUI->savePlace();

	//roll back to the first day of that month, regardless of the current day and/or what day was specified
	if( isset( $_GET['start_date'] ) ) $AppUI->setState( 'InformerStartDateMonthly',$_GET['start_date'] );
	$start_day = new CDate( $AppUI->getState( 'InformerStartDateMonthly' ) ? $AppUI->getState( 'InformerStartDateMonthly' ) : NULL );
	$date = $start_day->format( '%Y-%m-01' ) .' 00:00:00';
	$start_day->setDate( $date,DATE_FORMAT_ISO );

	//last day of that week, add 6 days
	$end_day = new CDate ();
	$end_day->copy( $start_day );
	$end_day->addMonths(1);

	//date of the first day of the previous week.
	$prev_date = new CDate ();
	$prev_date->copy( $start_day );
	$prev_date->addMonths(-1);

	//date of the first day of the next week.
	$next_date = new CDate ();
	$next_date->copy( $start_day );
	$next_date->addMonths(1);
	
	$q = new DBQuery();
	$q->addTable( 	'projects','p' );
	$q->addQuery( 	'p.project_id,p.project_short_name,t.task_name,t.task_id,tl.task_log_id,tl.task_log_name,tl.task_log_description,tl.task_log_hours,DAYOFMONTH(tl.task_log_date) AS day' );
	$q->addJoin( 	'tasks','t','p.project_id = t.task_project' );
	$q->addJoin( 	'task_log','tl','t.task_id = tl.task_log_task' );
	$q->addWhere( 	'
						tl.task_log_creator = '. $user_id .' AND 
						tl.task_log_date >= "'. $start_day->format( FMT_DATETIME_MYSQL ) .'" AND  
						tl.task_log_date <= "'. $end_day->format( FMT_DATETIME_MYSQL ) .'" 
					' );
	$q->addOrder(	'tl.task_log_date' );
	$res = $q->loadList();
	
	$data = array();
	for( $i = 1;$i < $start_day->getDaysInMonth() + 1;$i++ ) $data[$i] = array();
	foreach( $res as $row ) $data[$row['day']][] = $row;
	
	$weekdate = new CDate();
	$weekdate->copy( $start_day );
//echo '<pre>'; print_r($data);echo '</pre>';	
?>
<script language="javascript" type="text/javascript">
	function delIt2(id) {
		if (confirm( "Are you sure you want to delete this Task Log?" )) {
			document.frmDelete2.task_log_id.value = id;
			document.frmDelete2.submit();
		}
	}
</script>
<form name="frmDelete2" action="./index.php?m=tasks" method="post">
	<input type="hidden" name="dosql" value="do_updatetask">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_log_id" value="0" />

</form>
<table align="center" width="100%" class="motitle" border="0" cellspacing="1" cellpadding="2">
	<tbody>
		<tr>
			<td align="left">
				<a href="?m=informer&start_date=<?php echo urlencode( $prev_date->getDate() ); ?>"><img src="./images/prev.gif" width="16" height="16" alt="<?php echo $AppUI->_( 'previous' );?>" border="0"></a>
			</td>
			<td align="center" width="100%">
				<span style="font-size: 12pt;"><?php echo $start_day->format( '%B / %Y' );?></span>
			</td>
			<td align="right">
				<a href="?m=informer&start_date=<?php echo urlencode( $next_date->getDate() ); ?>"><img src="./images/next.gif" width="16" height="16" alt="<?php echo $AppUI->_( 'next' );?>" border="0"></a>
			</td>
		</tr>
	</tbody>
</table>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr><td>&nbsp;</td></tr>
</table>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
	<tbody>
		<tr>
			<th align="center" width="13%"><strong><?php echo $AppUI->_('Project Name'); ?></strong></th>
			<th align="center" width="18%"><strong><?php echo $AppUI->_('Task Name'); ?></strong></th>
			<th align="center" width="18%"><strong><?php echo $AppUI->_('Task Log Summary'); ?></strong></th>
			<th align="center" width="44%"><strong><?php echo $AppUI->_('Log Entry'); ?></strong></th>
			<th align="center" width="7%"><strong><?php echo $AppUI->_('Hours'); ?></strong></th>
		</tr>
<?php
	(float) $weeksum = (float) $month_hours = 0;
	foreach( $data as $day => $ddata ) {
		if( $weekdate->isWorkingDay() ) {
			$month_hours += $day_hours;
			$day_hours = $opp_hours;
		} else {
			$day_hours = 0;
		}
		?>
		<tr>
			<td colspan="5" align="left" style="background-color:#D7EAFF;">
			<?php
				echo $weekdate->format( $df ) .' '.'<b>'. $weekdate->getDayName( false ) .'</b>';
			?>
			</td>
		</tr>
		<?php
		$pp = $tp = false;
		$op = $ot = '';
		(float) $daysum = 0;
		foreach( $ddata as $row ) {
			$cE = $perms->checkModuleItem( 'task_log','edit',$row['task_id'] );
			$cD = $perms->checkModuleItem( 'task_log','delete',$row['task_id'] );
			if( $cE ) $act = "[<a href=\"./index.php?m=tasks&a=view&tab=1&task_id=".$row['task_id']."&task_log_id=".$row['task_log_id']."\">edit</a>]";
			else $act = '';
			if( $cD ) $act .= " [<a href=\"javascript:delIt2(".$row['task_log_id'].");\">delete</a>]";
			if( $act != '' ) $act .= '<br />';

			if( $pp && $op == $row['project_short_name'] ) $row['project_short_name'] = '';
			else { $op = $row['project_short_name']; $ot = ''; }
			if( $tp && $ot == $row['task_name'] ) $row['task_name'] = '';
			else $ot = $row['task_name'];
			
			
			echo "\t<tr>\n";
				echo "\t\t<td align=\"left\" valign=\"top\"><a href=\"./index.php?m=projects&a=view&project_id=". $row['project_id'] ."\">". $row['project_short_name'] ."</a></td>\n";
				echo "\t\t<td align=\"left\" valign=\"top\"><a href=\"./index.php?m=tasks&a=view&task_id=". $row['task_id'] ."\">". $row['task_name'] ."</a></td>\n";
				echo "\t\t<td align=\"left\" valign=\"top\">". $act . $row['task_log_name'] ."</td>\n";
				echo "\t\t<td style=\"text-align: justify;\" valign=\"top\">". nl2br( $row['task_log_description'] ) ."</td>\n";
				echo "\t\t<td align=\"right\" valign=\"top\">";
					echo printSum( $row['task_log_hours'] );
					if( $show_possible_hours_worked ) echo ' / '. printSum( $day_hours );
				echo "</td>\n";
			echo "\t</tr>\n";
			
			$pp = $tp = true;
			(float) $daysum = (float) $daysum + (float) $row['task_log_hours'];
		}
		echo "\t<tr>\n";
			echo "\t\t<td colspan=\"4\" align=\"right\"><strong>Day &sum;</strong></td>\n";
			echo "\t\t<td align=\"right\" valign=\"top\">";
				echo printSum( (float) $daysum );
				if( $show_possible_hours_worked ) echo ' / '. printSum( $day_hours );
			echo "</td>\n";
		echo "\t</tr>\n";
		
		(float) $weeksum = (float) $weeksum + (float) $daysum;
		$weekdate->addDays(1);
	}
	echo "\t<tr>\n";
		echo "\t\t<td colspan=\"4\" align=\"right\" style=\"background-color:#D7EAFF;\"><strong>Month &sum;</strong></td>\n";
		echo "\t\t<td align=\"right\" style=\"background-color:#D7EAFF;\">";
			echo printsum( (float) $weeksum );
			if( $show_possible_hours_worked ) echo ' / '. printSum( $month_hours );
		echo "</td>\n";
	echo "\t</tr>\n";
?>
	</tbody>
</table>

<?php
function printSum( $float ) {
	$float = str_replace( '.',',',(string) $float );
	$pos = strpos( $float,',' );
	if( $pos === false ) return $float .',00';
	if( strlen( substr( $float,$pos + 1 ) ) == 1 ) return $float.'0';
	return $float;
}
?>