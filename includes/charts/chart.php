<?php
	include("charts.php");
	
	if(is_numeric($_GET['sent']) && $_GET['sent'] > 0){
		$sent = $_GET['sent'];
	} else {
		$sent = 0;
	}
	if(is_numeric($_GET['failed']) && $_GET['failed'] > 0){
		$failed = $_GET['failed'];
	} else {
		$failed = 0;
	}
	if(is_numeric($_GET['unqread']) && $_GET['unqread'] > 0){
		$unqread = $_GET['unqread'];
	} else {
		$unqread = 0;
	}
	if(is_numeric($_GET['read']) && $_GET['read'] > 0){
		$read = $_GET['read'];
	} else {
		$read = 0;
	}
	if(is_numeric($_GET['links']) && $_GET['links'] > 0){
		$links = $_GET['links'];
	} else {
		$links = 0;
	}
	
	$chart[ 'axis_category' ] = array ( 'size'=>12, 'color'=>"000000", 'alpha'=>50, 'font'=>"arial", 'bold'=>true, 'skip'=>0 ,'orientation'=>"horizontal" ); 
	$chart[ 'axis_ticks' ] = array ( 'value_ticks'=>false, 'category_ticks'=>false );
	$chart[ 'axis_value' ] = array ( 'alpha'=>0 );
	
	$chart[ 'chart_border' ] = array ( 'top_thickness'=>0, 'bottom_thickness'=>0, 'left_thickness'=>0, 'right_thickness'=>0 );
	$chart[ 'chart_data' ] = array ( array ( "", "Campaign"), array ( "Emails Sent", $sent ) , array ( "Emails Failed", $failed ) , array ( "Emails Read (Unique)", $unqread ) , array ( "Emails Read", $read ) , array ( "Links Clicked", $links ) );
	$chart[ 'chart_grid_h' ] = array ( 'thickness'=>0 );
	$chart[ 'chart_pref' ] = array ( 'rotation_x'=>rand(20,40), 'rotation_y'=>rand(20,40) ); 
	$chart[ 'chart_rect' ] = array ( 'x'=>-60, 'y'=>30, 'width'=>480, 'height'=>240, 'positive_alpha'=>0, 'negative_alpha'=>25 );
	$chart[ 'chart_type' ] = "3d column" ;
	$chart[ 'chart_value' ] = array ( 'hide_zero'=>false, 'color'=>"000000", 'alpha'=>80, 'size'=>10, 'position'=>"over", 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'as_percentage'=>false );
	
	$chart[ 'legend_label' ] = array ( 'layout'=>"vertical", 'font'=>"arial", 'bold'=>true, 'size'=>12, 'color'=>"000000", 'alpha'=>50 ); 
	$chart[ 'legend_rect' ] = array ( 'x'=>50, 'y'=>35, 'width'=>100, 'height'=>40, 'margin'=>5, 'fill_color'=>"000066", 'fill_alpha'=>0, 'line_color'=>"000000", 'line_alpha'=>0, 'line_thickness'=>0 ); 
	
	$chart[ 'series_color' ] = array ("E4B244","768bb3" ); 
	$chart[ 'series_gap' ] = array ( 'bar_gap'=>10, 'set_gap'=>20) ; 
	
	SendChartData ( $chart );
?>