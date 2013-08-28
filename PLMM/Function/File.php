<?php
defined('PHPHP') OR exit(0);

function makedir( $dir, $mode = 0777){	
	if ( ! file_exists($dir) ) {
		makedir(dirname($dir), $mode);
		@mkdir($dir, $mode);
		@chmod($dir, $mode);
	}
	return;
}

function cleardir ($dir) {
	if (!$dh = @opendir ($dir)) {
		return false;
	}
	
	while (false !== ($tmp = readdir ($dh))) {
	
		if ($tmp != '.' && $tmp != '..') {
		
			$file = $dir . '/' . $tmp;
			
			if (is_file ($file)) {
				@unlink ($file);
			} elseif (is_dir ($file)) {
				cleardir ($file );
			}
		}
	}
	@closedir($dh);
	return;
}

function removedir($dir) {
	if (!$dh = @opendir ($dir)) {
		return false;
	}
	
	while (false !== ($tmp = readdir ($dh))) {
	
		if ($tmp != '.' && $tmp != '..') {
		
			$file = $dir . '/' . $tmp;
			
			if (is_file ($file)) {
				@unlink ($file);
			} elseif (is_dir ($file)) {
				removedir ($file );
			}
		}
	}
	@closedir($dh);
	@rmdir ($dir);
	return;
}

function tmpdir()
{
    if (strpos(PHP_OS, 'win', 3)!==false) {
        if (isset($_ENV['TEMP'])) {
            return $_ENV['TEMP'];
        }
        if (isset($_ENV['TMP'])) {
            return $_ENV['TMP'];
        }
        if (isset($_ENV['windir'])) {
            return $_ENV['windir'] . '\\temp';
        }
        if (isset($_ENV['SystemRoot'])) {
            return $_ENV['SystemRoot'] . '\\temp';
        }
        if (isset($_SERVER['TEMP'])) {
            return $_SERVER['TEMP'];
        }
        if (isset($_SERVER['TMP'])) {
            return $_SERVER['TMP'];
        }
        if (isset($_SERVER['windir'])) {
            return $_SERVER['windir'] . '\\temp';
        }
        if (isset($_SERVER['SystemRoot'])) {
            return $_SERVER['SystemRoot'] . '\\temp';
        }
        return '\temp';
    }
    if (isset($_ENV['TMPDIR'])) {
        return $_ENV['TMPDIR'];
    }
    if (isset($_SERVER['TMPDIR'])) {
        return $_SERVER['TMPDIR'];
    }
    return '/tmp';
}
?>