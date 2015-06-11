<?php

	set_time_limit( 0 );
	ob_implicit_flush();
	
	$port = 8090;
	
START:
	$sock = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
	if( $sock===false ) {
		echo "socket_create() failed:reason:" . socket_strerror( socket_last_error() ) . "\n";
		exit;
	}

	// socket_set_nonblock ( $sock );
		
	socket_set_option( $sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>6, "usec"=>0 ) );

	$ok = socket_bind( $sock, '0.0.0.0', $port );
	if( $ok===false ) {
		echo "false  \r\n";
		echo "socket_bind() failed:reason:" . socket_strerror( socket_last_error( $socket ) )."\r\n";
		exit;
	}

	echo "The rtsp server is running!\n";
	
	socket_listen( $sock );      						 // 监听端口
	
	while( 1 ) {
		$conn = socket_accept( $sock );  
		if( $conn ) {
			
			$data_recv = '';
			while( 1 ) {			// void gen TIME_WAIT
				$res = '';
				$res = socket_read( $conn, 128 );		
				
				if( $res=='' | $res==false ) {
					socket_close( $conn );
					break;
				}
				else {
					$data_recv .= $res;	
					echo "$res";
				}
			}
			
			//echo "$data_recv\r\n";
		}
	}
	
	socket_close( $sock );
?>