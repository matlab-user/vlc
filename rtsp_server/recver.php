<?php
	// --v_ip	   	数据转发向此 ip
	// --v_port 	数据转发向此 port
	// 返回新建立的 UDP 端口
	
	require_once( 'vlc_server_lib.php' );
	error_reporting( E_ALL ^ E_NOTICE );
	
	$server_ip = '127.0.0.1';
	$server_port = 8090;
		
	$longopts  = array(
		"v_ip:",    	// Required value
		"v_port:",   	// Required value
		"id:",			// 程序的 id 值				
	);
	
	$options = getopt( '', $longopts );	
	$v_ip = $options['v_ip'];
	$v_port = intval( $options['v_port'] );
	$ID = $options['id'];
	
	if( $v_ip=='' || $v_port<=0 || $ID=='' ) {
		echo "quit";
		exit();
	}	
		
	$l_ip = '';
	$l_port = 0;
	
	$socket = start_udp_server( 0 );
	if( $socket===FALSE ) {
		echo time()."\tsend udp port failed!\r\n";
		return;	
	}
	echo "The video recver --$ID-- is running!\n";
	
	socket_getsockname( $socket, $l_ip, $l_port );		// 获取新建 udp 的 ip、port
	report_port( $ID, $l_port );
	
	while( true ) {
		
		$r = array( $socket );
		$w = NULL;
		$e = NULL;
		
		$num = socket_select( $r, $w, $e, 6 );
		if( $num===false || $num<=0 )
			break;
		
// proc_open 启动 cvlc rtsp 服务器进程

		if( $num>0 ) {
			$len = socket_recvfrom( $socket, $buf, 1024*2, 0, $f_ip, $f_port );
			if( $len>0 ) {
				if( ord($buf)==128 )
					socket_sendto( $socket, $buf, $len, 0, $v_ip, $v_port );				
			}
		}
	}
	
	socket_close( $socket );
	
//-------------------------------------------------------------------------------------	
	function report_port( $id, $port ) {
		global $server_ip, $server_port;
		
		if( ($sock=socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))<0 ) { 
			echo "failed to create socket: ".socket_strerror($sock)."\n"; 
			exit(); 
		} 
		
		socket_set_option( $sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>10, "usec"=>0 ) );
		socket_set_option( $sock, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>3, "usec"=>0 ) );
		socket_set_option( $sock, SOL_SOCKET, SO_REUSEADDR, 1 );
		
		$msg = "TP-$id-$port";
		$len = strlen( $msg );
		socket_sendto( $sock, $msg, $len, 0, $server_ip, $server_port );
		
		socket_close( $sock );
	}

	
?>