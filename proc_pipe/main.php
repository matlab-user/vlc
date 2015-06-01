<?php
	
//	echo floatval(time()+0.1234)."\r\n";
//	return;
	
	$descriptorspec = array(
	   0 => array("pipe", "r"),  // 标准输入，子进程从此管道中读取数据
	   1 => array("pipe", "w"),  // 标准输出，子进程向此管道中写入数据
	   2 => array("file", "/tmp/error-output.txt", "a") // 标准错误，写入到一个文件
	);

	$proc = proc_open( 'php t3.php', $descriptorspec, $pipes );
	if( is_resource($proc) ) {
		echo "s1\r\n";
		fwrite( $pipes[0], 'wang dehui' );
		fclose( $pipes[0] );	

		echo stream_get_contents( $pipes[1] );
		fclose( $pipes[1] );

		proc_close( $proc );
		
		echo "s2\r\n";
	}
	
?>