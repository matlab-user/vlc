<?php

	$handle =  proc_open( 'netstat -anpc 10 >/dev/null &', array(), $pipes );
	//sleep(1);
	$stat = proc_get_status( $handle );
	echo "'pid'; " . $stat['pid'] . "\n";
	//echo stream_get_contents( $handle )."\r\n";
	//$read = fread( $handle, 2096 );
	//echo $read;
	sleep( 2 );
	echo proc_close( $handle )."\r\n";
		
	$pid = $stat['pid'] + 1;
	$cmd = "kill -9 $pid";
	
	echo $cmd."\r\n";
	
	exec( $cmd );
	
	//proc_close( $handle );
?>