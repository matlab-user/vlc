<?php

	error_reporting( E_ALL ^ E_NOTICE );
	
	while(1) {
		$out_str = '';
		//$loop = rand(1024*9,1024*20);
		$loop = 1024*20;
		for( $i=0; $i<$loop; $i++ )
			$out_str .= 'w';
		
		$out_str .= 'h';
		
		fwrite( STDOUT, $out_str );
		
		$delay = rand(1,4);
		sleep( 1 );
		break;
	}
?>