<?php
	require_once( 'rtp_lib.php' );
	error_reporting( E_ALL ^ E_NOTICE );
	
	$filename = '../matlab_get_image/out.h264';
	$fid = fopen( $filename, 'r' );
	$contents = fread( $fid, filesize($filename) );
	fclose( $fid );
	
	$filename = 'w.sdp';
	$fid = fopen( $filename, 'r' );
	$sdp_contents = fread( $fid, filesize($filename) );
	fclose( $fid );
	echo "$sdp_contents\r\n";
	
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
	$rtp_h->M = 0;
	$rtp_h->PT = 96;
	$rtp_h->SSRC = 820116;
	$rtp_h->TS = pow(2,33) - 1; 
	
	$time_inc = 90000 / 30;				// RTP 计算时间的方法，以时钟脉冲为单位计算；
	$max_int_4 = pow(2,33) - 1;
	
	$i = 1;
	$socket = start_udp_server( 0 );
//	socket_sendto( $socket, $sdp_contents, strlen($sdp_contents), 0, '127.0.0.1', 8090 );
//	sleep(1);
//	$len = socket_recvfrom( $socket, $frame, 1024*2, 0, $f_ip, $f_port );
//	echo $len."\r\n";

for( $j=0; $j<pow(2,10); $j++ ) {	
	while( $i<$index_4_len ) {

		$start = $index_4[$i-1];
		$end = $index_4[$i];
		
		$frame = substr( $contents, $start, $end-$start );
		$rtp_data = add_rtp_header( $frame, $rtp_h );
		//echo "rtp_data size: ".strlen($rtp_data)."\r\n";
		socket_sendto( $socket, $rtp_data, strlen($rtp_data), 0, '127.0.0.1', 8090 );
		
		$i++;
		$rtp_h->TS += $time_inc;
		if( $rtp_h->TS>= $max_int_4 )
			$rtp_h->TS %= $max_int_4;
		
		usleep( 33000 );
	}
	echo "TS - $rtp_h->TS\t\tSN - $rtp_h->SN\r\n";
	$i = 1;
}	
	return;
	
?>