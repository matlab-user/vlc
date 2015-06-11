<?php
	// 返回新建立的 UDP 端口
	
	require_once( 'vlc_server_lib.php' );
	error_reporting( E_ALL ^ E_NOTICE );
	
	$server_ip = '127.0.0.1';
	$server_port = 8090;
	
	$descriptorspec = array(
	   0 => array("pipe", "r"),
	   1 => array("pipe", "w"),
	   2 => array("file", "/tmp/error-output.txt", "a")
	);
	
	$longopts  = array(
		"id:",			// 程序的 id 值				
	);
	
	$options = getopt( '', $longopts );
	$ID = $options['id'];
	
	if( $ID=='' ) {
		echo "ID is missing. quit!\r\n";
		exit();
	}	
		
	$l_ip = '';
	$l_port = 0;
	
	$rtsp_port = get_valid_tcp_port();
//	$com = "vlc -vvv - --sout '#rtp{mux=ts,sdp=rtsp://$server_ip:$rtsp_port/wdh}' vlc://quit";
$com = "vlc -vvv - vlc://quit";
	$proc = proc_open( $com, $descriptorspec, $pipes );
	if( is_resource($proc) ) {	
		fclose( $pipes[1] );	
		report_port( 'TP', $ID, "rtsp://$server_ip:$rtsp_port/wdh" );
	}
	else {
		echo "proc_open failed. quit!\r\n";
		exit();
	}
	
	$socket = start_udp_server( 0 );
	if( $socket===FALSE ) {
		echo time()."\tsend udp port failed!\r\n";
		return;	
	}
	echo "The video recver --$ID-- is running!\n";
	
	socket_getsockname( $socket, $l_ip, $l_port );		// 获取新建 udp 的 ip、port
	report_port( 'UP', $ID, $l_port );
	
	while( true ) {
		
		$r = array( $socket );
		$w = NULL;
		$e = NULL;
		
		$num = socket_select( $r, $w, $e, 6 );
		if( $num===false || $num<=0 ) {
			fclose( $pipes[0] );
			proc_close( $proc );
			break;
		}
		
		if( $num>0 ) {
			$len = socket_recvfrom( $socket, $buf, 1024*2, 0, $f_ip, $f_port );
			if( $len>0 ) {	
				if( ord($buf)==128 ) {
					fwrite( $pipes[0], $buf, $len );
					//socket_sendto( $socket, $buf, $len, 0, $v_ip, $v_port );	
				}
			}
		}
	}
	
	socket_close( $socket );
	
//-------------------------------------------------------------------------------------	
	function report_port( $header, $id, $port ) {
		global $server_ip, $server_port;
		if( $header!=='UP' && $header!=='TP' )
			return False;
			
		if( ($sock=socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))<0 ) { 
			echo "failed to create socket: ".socket_strerror($sock)."\n"; 
			return False; 
		} 
		
		socket_set_option( $sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>10, "usec"=>0 ) );
		socket_set_option( $sock, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>3, "usec"=>0 ) );
		socket_set_option( $sock, SOL_SOCKET, SO_REUSEADDR, 1 );
		
		$msg = "$header-$id-$port";
		$len = strlen( $msg );
		socket_sendto( $sock, $msg, $len, 0, $server_ip, $server_port );
		
		socket_close( $sock );
	}

	
?>