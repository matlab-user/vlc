<?php

	class dev_info {
		public $ip = '';			// 设备控制地址, 默认为 UDP  
		public $port = 0;			// 设备控制端口
		public $at = 0;				// 最后一次收到信息的时间，UTC时间
		public $server_id = '';		// 开启的服务 ID	
		public $rtsp_url = '';			// 反馈给 viewer 的 rtsp 地址
		public $recver_port = 0;	// 接收设备数据的UDP端口
	}


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
	
	function get_dev_id( $recv_str ) {
		$h = substr( $recv_str, 0, 2 );
		if( $h!=='ID' && $h!=='ON' && $h!=='QY' )
			return '';
		
		$t = substr($recv_str, -1 );
		if( $t!==';' )
			return '';
		
		$id = ltrim( $recv_str, 'ID;ONQY' );
		
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
		
	function get_valid_tcp_port() {
		$delim = ' ';
		$out_res = array();
		
		for( $i=0; $i<10; $i++ ) {
			$port = mt_rand( 1024, pow(2,16)-1 );
			exec( "netstat -atn | grep $port", $out_res );
			
			if( count($out_res)==0 )
				return $port;
			
			$sig = 0;
			foreach( $out_res as $v ) {
				strtok( $v, $delim );
				strtok( $delim );
				strtok( $delim );
				$s1 = strtok( $delim );

				$out_res_2 = array();
				exec( "echo $s1 | grep -w $port", $out_res_2 );
				if( count($out_res_2)!=0 ) {
					$sig = 1;
					break;
				}
			}
			
			if( $sig==0 )
				return $port;
		}
		
		return -1;
	}
	
	// 根据源 ip、port，获得 viewer ip port
	function get_view_addr( $ip, $port, &$v_ip, &$v_port ) {
		global $dev_info_array;
		
		foreach( $dev_info_array as $k => $v ) {
			if( $v->ip==$ip && $v->port==$port ) {
				$v_ip = $v->v_ip;
				$v_port = $v->v_port;
				break;
			}
		}
	} 
	
	function decode_id_port( $str, &$id, &$port ) {
		
		$info = explode( "-", $str );
		if( count($info)!=3 )
			return;
		
		$id = $info[1];
		$port = $info[2];
	}
	
?>