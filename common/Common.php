<?php

function autoLoad($currPath) {
	if (is_dir($currPath)) {
		$handler = opendir ($currPath);
		while (($filename = readdir( $handler )) !== false) {
			if ($filename != "." && $filename != ".." && $filename[0] != '.') {		
				if(is_file($currPath . '/' . $filename)) {		
					require_once $currPath . '/' . $filename;
				}
				if(is_dir($currPath . '/' . $filename)) {		
					autoLoad($currPath . '/' . $filename);
				}
			}
		}
		closedir($handler);
	}
}

autoLoad(dirname(__FILE__));
