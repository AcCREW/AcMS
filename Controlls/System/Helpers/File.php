<?php
/**
 * Read File
 *
 * Opens the file specfied in the path and returns it as a string.
 *
 * @access	public
 * @param	string	path to file
 * @return	string
 */
if (!function_exists('read_file')) {
	function read_file($sFile) {
		if (!file_exists($sFile)) {
			return false;
		}

		if (function_exists('file_get_contents')) {
			return file_get_contents($sFile);
		}

		if (!$fp = @fopen($sFile, FOPEN_READ)) {
			return false;
		}

		flock($fp, LOCK_SH);

		$vData = '';
		if (filesize($sFile) > 0) {
			$vData =& fread($fp, filesize($sFile));
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		return $vData;
	}
}

// ------------------------------------------------------------------------

/**
 * Tests for file writability
 *
 * is_writable() returns TRUE on Windows servers when you really can't write to
 * the file, based on the read-only attribute.  is_writable() is also unreliable
 * on Unix servers if safe_mode is on.
 *
 * @access	private
 * @return	void
 */
if (!function_exists('is_really_writable')) {
	function is_really_writable($sFile) {
		// If we're on a Unix server with safe_mode off we call is_writable
		if (DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == false) {
			return is_writable($sFile);
		}

		// For windows servers and safe_mode "on" installations we'll actually
		// write a file then read it.  Bah...
		if (is_dir($sFile)) {
			$sFile = rtrim($sFile, '/').'/'.md5(mt_rand(1,100).mt_rand(1,100));

			if (($fp = @fopen($sFile, FOPEN_WRITE_CREATE)) === false) {
				return false;
			}

			fclose($fp);
			@chmod($sFile, DIR_WRITE_MODE);
			@unlink($sFile);
			return true;
		} elseif ( ! is_file($sFile) OR ($fp = @fopen($sFile, FOPEN_WRITE_CREATE)) === false) {
			return false;
		}

		fclose($fp);
		return TRUE;
	}
}

// ------------------------------------------------------------------------

/**
 * Delete Files
 *
 * Deletes all files contained in the supplied directory path.
 * Files must be writable or owned by the system in order to be deleted.
 * If the second parameter is set to TRUE, any directories contained
 * within the supplied base directory will be nuked as well.
 *
 * @access	public
 * @param	string	path to file
 * @param	bool	whether to delete any directories found in the path
 * @return	bool
 */
if ( ! function_exists('delete_files')) {
	function delete_files($sPath, $DelDir = false, $nLevel = 0) {
		// Trim the trailing slash
		$sPath = rtrim($sPath, '/');

		if (!$CurrentDir = @opendir($sPath)) {
			return false;
		}

		while (false !== ($sFilename = @readdir($CurrentDir))) {
			if ($sFilename != "." && $sFilename != "..") {
				if (is_dir($sPath.'/'.$sFilename)) {
					// Ignore empty folders
					if (substr($sFilename, 0, 1) != '.') {
						delete_files($sPath.'/'.$sFilename, $DelDir, $nLevel + 1);
					}
				} else {
					unlink($sPath.'/'.$sFilename);
				}
			}
		}
		@closedir($CurrentDir);

		if ($DelDir == true && $nLevel > 0) {
			return @rmdir($sPath);
		}

		return true;
	}
}

// ------------------------------------------------------------------------

/**
 * Write File
 *
 * Writes data to the file specified in the path.
 * Creates a new file if non-existent.
 *
 * @access	public
 * @param	string	path to file
 * @param	string	file data
 * @return	bool
 */
if ( ! function_exists('write_file')) {
	function write_file($sPath, $vData, $sMode = 'wb') {
		if (!$fp = @fopen($sPath, $sMode)) {
			return false;
		}

		flock($fp, LOCK_EX);
		fwrite($fp, $vData);
		flock($fp, LOCK_UN);
		fclose($fp);

		return true;
	}
}