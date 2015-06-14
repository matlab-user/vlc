<?php

	require_once( 'rtp_lib.php' );
	error_reporting( E_ALL ^ E_NOTICE );
		
	$rtp_h = new rtp_header();
	$rtp_h->M = 1;
	$rtp_h->PT = 7;
	$rtp_h->SSRC = 820116;
	
	$i = 0;
	$socket = start_udp_server( 8091 );
	while( 1 ) {
		$r = array( $socket );
		$w = NULL;
		$e = NULL;
		$num = socket_select( $r, $w, $e, 20 );
		if( $num===false ) {
			socket_close( $socket );
			break;
		}
		elseif( $num>0 ) {
			socket_recvfrom( $socket, $frame, 1024*2, 0, $f_ip, $f_port );

			//echo "$frame\r\n\r\n";
			$i++;
			//if( strlen($frame)<=4 )
			//	continue;
		}
		if( $i>4)
			socket_sendto( $socket, $frame, strlen($frame)-rand(0,0), 0, '127.0.0.1', 8090 );
		//$rtp_data = add_rtp_header( $frame, $rtp_h );
		//socket_sendto( $socket, $rtp_data, strlen($rtp_data), 0, '127.0.0.1', 8090 );
	}
	
	return;
?>