<?php
	require_once( 'rtp_lib.php' );
	error_reporting( E_ALL ^ E_NOTICE );
	
	$filename = '../matlab_get_image/out.h264';
	$fid = fopen( $filename, 'r' );
	$contents = fread( $fid, filesize($filename) );
	fclose( $fid );
		
	$index_4_len = 0;
	$index_4 = array();
	$off = 0;
	while( 1 ) {
		$mid = strpos( $contents, "\x00\x00\x00\x01", $off );
		if( $mid===False )
			break;
		else {
			$index_4[] = $mid;
			$index_4_len++;
/*
			if( $index_4_len>1 ) {
				$nal_size = $index_4[$index_4_len-1] - $index_4[$index_4_len-2] - 4;
				echo "nal_size -- $nal_size\r\n";
			}
*/

/*
			echo "$off --".dechex(ord($contents[$mid])).' '.dechex(ord($contents[$mid+1]))
				      .' '.dechex(ord($contents[$mid+2])).' '.dechex(ord($contents[$mid+3]))."\r\n";
*/
			$off = $mid + 4;	

		}
	}
	
	echo "totle frames:   ".count($index_4)."\r\n";
	
	$rtp_h = new rtp_header();
	$rtp_h->M = 1;
	$rtp_h->PT = 7;
	$rtp_h->SSRC = 820116;
	
	$i = 1;
	$socket = start_udp_server( 0 );
	while( $i<$index_4_len ) {
		$r = array( $socket );
		$w = NULL;
		$e = NULL;
		
		$start = $index_4[$i-1];
		$end = $index_4[$i];
		
		$frame = substr( $contents, $start, $end-$start );
		$rtp_data = add_rtp_header( $frame, $rtp_h );
		echo "rtp_data size: ".strlen($rtp_data)."\r\n";
		socket_sendto( $socket, $rtp_data, strlen($rtp_data), 0, '127.0.0.1', 8090 );
		
		$i++;
	}
	
	return;
	
?>