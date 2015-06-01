<?php

	class dev_info {
		public $ip = '';			// 设备控制地址, 默认为 UDP  
		public $port = 0;			// 设备控制端口
		public $at = 0;				// 最后一次收到信息的时间，UTC时间
		public $addr = ‘’;			// 开启的服务地址
	}


	function start_udp_server( $port ) {
		
		$socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
		if( $socket===false ) {
			echo "socket_create() failed:reason:" . socket_strerror( socket_last_error() ) . "\n";
			exit;
		}
			
		socket_set_option( $socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>6, "usec"=>0 ) );

		$ok = socket_bind( $socket, '0.0.0.0', $port );
		if( $ok===false ) {
			echo "false  \r\n";
			echo "socket_bind() failed:reason:" . socket_strerror( socket_last_error( $socket ) )."\r\n";
			exit;
		}

		echo "The udp server is running!\n";
		return $socket;
	}
	
	function get_id( $recv_str ) {
		$h = substr( $recv_str, 0, 2 );
		if( $h!=='ID' && $h!=='ON' )
			return '';
		
		$t = substr($recv_str, -1 );
		if( $t!==';' )
			return '';
		
		$id = trim( $recv_str, 'ID;ON' );
		
		return $id;
	} 
	
	// 最后一次访问时间，与当前时间差 >=$t 时，清除此 dev_info
	// 单位 秒
	function clean_dev_info( $t ) {
		global $dev_info_array;
		
		foreach( $dev_info_array as $k => $v ) {
			if( (time()-$v->at)>= $t )
				unset( $dev_info_array[$k] );
		}
	}
?>