<?php

	require_once( 'rtp_lib.php' );
	error_reporting( E_ALL ^ E_NOTICE );

	$rtp_h = new rtp_header();
	$rtp_h->M = 1;
	$rtp_h->PT = 33;
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
			$len = socket_recvfrom( $socket, $frame, 1024*2, 0, $f_ip, $f_port );
			echo $len."\r\n";
			//echo "-- ".ord($frame[$len-1])."   ".ord($frame[$len-2])."\r\n";
			$rtp_h2 = new rtp_header();
			$rtp_head = substr( $frame, 0, 12 );
			decode_rtp_header( $rtp_head, $rtp_h2 );
/*
			echo "V-P-X-CC - $rtp_h2->V $rtp_h2->P $rtp_h2->X $rtp_h2->CC\r\n";
			echo "M-PT - $rtp_h2->M $rtp_h2->PT\r\n";
			echo "SN - $rtp_h2->SN\r\n";
			echo "TS - $rtp_h2->TS\r\n";
			echo "SSRC - $rtp_h2->SSRC\r\n";

*/
			$nal_h = substr( $frame, 12, 1 );
			$type = ord($nal_h) & 0x1f;
			if( $type!=7 ) {
				echo "nalu-type - $type   ".ord($nal_h)."\r\n";
				echo "\r\n";
			}
			//echo "$frame\r\n\r\n";
			$i++;
			//if( strlen($frame)<=4 )
			//	continue;
		}
/*
		if( $i>4)
			socket_sendto( $socket, $frame, strlen($frame)-rand(0,0), 0, '127.0.0.1', 8090 );
*/
		//$rtp_data = add_rtp_header( $frame, $rtp_h );
		//socket_sendto( $socket, $rtp_data, strlen($rtp_data), 0, '127.0.0.1', 8090 );
	}
	
	return;
?>