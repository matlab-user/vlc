<?php

	$fid = fopen( 'x264_rtp_out', 'w+' );
	
	$sum = 0;
	
	$socket = start_udp_server( 8090 );
	while( 1 ) {	
		$r = array( $socket );
		$w = NULL;
		$e = NULL;
		$num = socket_select( $r, $w, $e, 24 );
		if( $num<=0 )
			break;
		
		foreach( $r as $v ) {
			socket_recvfrom( $socket, $buf, 1024*2, 0, $f_ip, $f_port );
			//fwrite( $fid, $buf );
			echo strlen($buf)."\r\n";
		}
		
		$sum += strlen( $buf );
	}
	
	echo "summ-bytes:  $sum\r\n";
	fclose( $fid );
	socket_close( $socket );
//--------------------------------------------------------------------
	function start_udp_server( $port ) {
		
		$socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
		if( $socket===false ) {
			echo "socket_create() failed:reason:" . socket_strerror( socket_last_error() ) . "\n";
			return FALSE;
		}
			
		socket_set_option( $socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>6, "usec"=>0 ) );

		$ok = socket_bind( $socket, '0.0.0.0', $port );
		if( $ok===false ) {
			echo "false  \r\n";
			echo "socket_bind() failed:reason:" . socket_strerror( socket_last_error( $socket ) )."\r\n";
			return FALSE;
		}
		
		return $socket;
	}

?>