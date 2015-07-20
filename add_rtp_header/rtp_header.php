<?php

	require_once( 'rtp_lib.php' );
	error_reporting( E_ALL ^ E_NOTICE );

	while(1) {
		$mid = fread( STDIN, 1024*21 );
		echo "mid -- $mid\r\n";
		$len = strlen( $mid );
		if( $len<=0 )
			break;
		
		echo $mid[$len-1]."----------len: $len\r\n";	

		if( $mid[$len-1]==='h' ) {
			echo "$len ------- ".$mid[$len-1]."\r\n";
		}
	}
	return;
	
	$fid = fopen( 'rtp_header.txt', 'r' );
	while( !feof($fid) ) {
        $buf = fgets( $fid, 4096 );
		if( strlen($buf)>0 ) {
			$buf = rtrim( $buf, " \r\n" );
			$hex_array = explode( " ", $buf );

			$buf = '';
			foreach( $hex_array as $v )
				$buf .= chr( hexdec($v) );
			
			$rtp_h = new rtp_header();
			decode_rtp_header( $buf, $rtp_h );
			echo "V-P-X-CC - $rtp_h->V $rtp_h->P $rtp_h->X $rtp_h->CC\r\n";
			echo "M-PT - $rtp_h->M $rtp_h->PT\r\n";
			echo "SN - $rtp_h->SN\r\n";
			echo "TS - $rtp_h->TS\r\n";
			echo "SSRC - $rtp_h->SSRC\r\n";
			echo "\r\n";
			
			$encode_h = encode_rtp_header( $rtp_h );
		}
    }
	
    fclose( $fid );
	
?>