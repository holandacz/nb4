<?php
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('style');
$specialtemplates = array('products');

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');

$this_script = 'server_info';

// ########################## SUPERADMIN CHECK ############################
if (!in_array($vbulletin->userinfo['userid'], preg_split('#\s*,\s*#s', $vbulletin->config['SpecialUsers']['superadministrators'], -1, PREG_SPLIT_NO_EMPTY)) ) {
	rpm_print_stop_back("You don't have permission to access this page.");
}

if ( $_REQUEST['do'] == 'phpinfo' ) { phpinfo(); exit;}

print_cp_header();





/////////////////////// front page
if ( empty($_REQUEST['do']) ) {
	if ($_REQUEST['show'] == 'time' || empty($_REQUEST['show'])) {
	print_form_header('', '');
	print_table_header('Server Time &nbsp;<span class="normal">exec(date)</span>');
	exec('date', $date);
	print_description_row('<div align="center"><br />'.$date[0].'<br /><br /></div>');
	print_table_footer();
	}
	
	if ($_REQUEST['show'] == 'uptime' || empty($_REQUEST['show'])) {
	print_form_header('', '');
	print_table_header('Uptime &nbsp;<span class="normal">exec(uptime)</span>');
	exec('uptime', $uptime);
	print_description_row('<div align="center"><br />'.$uptime[0].'<br /><br /></div>');
	print_table_footer();
	}
	
	if ($_REQUEST['show'] == 'serverload' || empty($_REQUEST['show'])) {
	print_form_header('', '');
	$loadavg = @file_get_contents("/proc/loadavg");
	$method = '';
	if ($loadavg) {
		$regs = explode(" ",$loadavg);
		$serverload='Server Loads: <b>' . $regs[0] .'</b> ' . $regs[1] . ' : ' . $regs[2];
		$method = 'file_get_contents("/proc/loadavg")';
    } elseif ( $stats = @exec('uptime') ) {
        preg_match('/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/',$stats,$regs);
        $serverload = 'Server Loads: <b>' . $regs[0] .'</b> ' . $regs[1] . ' : ' . $regs[2];
		$method = 'exec(uptime)';
	} else {
		$serverload = 'failed';
	}
	print_table_header('Server Load &nbsp;<span class="normal">'.$method.'</span>');
	print_description_row('<div align="center"><br />'.$serverload.'<br /><br /></div>');
	print_table_footer();
	}
	
	if ($_REQUEST['show'] == 'memoryusage' || empty($_REQUEST['show'])) {
	print_form_header('', '');
	print_table_header('Memory Usage &nbsp;<span class="normal">exec(free -m)</span>');
	exec('free -m', $mem);
	print_description_row('<pre>'.implode('<br />', $mem).'</pre>');
	print_table_footer();
	}
	
	if ($_REQUEST['show'] == 'freediskspace' || empty($_REQUEST['show'])) {
	print_form_header('', '');
	print_table_header('Free Disk Space &nbsp;<span class="normal">exec(df -h)</span>');
	exec('df -h', $df);
	print_description_row('<pre>'.implode('<br />', $df).'</pre>');
	print_table_footer();
	}
	
	if (empty($_REQUEST['show'])) {
	print_form_header('', '');
	print_description_row('<div align="center"><a href="'.$this_script.'.php?do=phpinfo">Show php info with phpinfo()</a></div>');
	print_table_footer();
	}
	
	if ($_REQUEST['show'] == 'mysqlstats' || empty($_REQUEST['show'])) {
	print_form_header('', '');
	print_table_header('MySQL Stats &nbsp;<span class="normal">mysql_stat()</span>');
	print_description_row(si_mysqlstats());
	print_table_footer();
	}
	
	if ($_REQUEST['show'] == 'mysqlstatus' || empty($_REQUEST['show'])) {
	print_form_header('', '');
	print_table_header('MySQL Status &nbsp;<span class="normal">query: SHOW STATUS</span>');
	print_description_row(si_mysqlstatus());
	print_table_footer();
	}
	
	if ($_REQUEST['show'] == 'mysqlvars' || empty($_REQUEST['show'])) {
	print_form_header('', '');
	print_table_header('MySQL Vars &nbsp;<span class="normal">query: SHOW VARIABLES</span>');
	print_description_row(si_mysqlvars());
	print_table_footer();
	}
}





///////////////////////////////////////////////////////////////////////////////////
/////////// functions

function si_mysqlstats() {
	$s = '';
	$status = explode('  ', mysql_stat());
	while ( list($k, $v) = each($status) ) {
		$s .= $v . '<br />';
	}
	return $s;
}

function si_mysqlstatus() {
	global $db;
	$s = '';
	$result = $db->query_read("SHOW STATUS");
	if (!$result) {
		return ( 'Invalid query: SHOW STATUS' );
	}
	while ($row = $db->fetch_array( $result )) {
		$s .= $row['Variable_name'] . ': ' . $row['Value'] . '<br />';
	}
	return $s;
}

function si_mysqlvars() {
	global $db;
	$s = '';
	$result = $db->query_read("SHOW VARIABLES");
	if (!$result) {
		return ( 'Invalid query: SHOW VARIABLES' );
	}
	while ($row = $db->fetch_array( $result )) {
		$s .= $row['Variable_name'] . ': ' . $row['Value'] . '<br />';
	}
	return $s;
}

print_cp_footer();
?>