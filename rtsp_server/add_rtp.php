<?php

	require_once( 'vlc_server_lib.php' );
	error_reporting( E_ALL ^ E_NOTICE );
	
	//stream_set_blocking( STDIN, 0 );
	
	$rtp_h = new rtp_header();
	$rtp_h->M = 1;
	$rtp_h->PT = 7;
	$rtp_h->SSRC = 820116;
	
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
			if( strlen($frame)<=4 )
				continue;
		}

		$rtp_data = add_rtp_header( $frame, $rtp_h );
		socket_sendto( $socket, $rtp_data, strlen($rtp_data), 0, '127.0.0.1', 8090 );
	}
	
	return;
?>