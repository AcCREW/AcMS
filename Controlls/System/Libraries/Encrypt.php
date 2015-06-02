<?php
class CEncrypt {
    
	public static $ECRYPTION_KEY	= '';
	public static $HASH_TYPE 	= 'sha1';
	private static $_MCryptExists  = false;
	private static $_MCryptCipher;
	private static $_MCryptMode;

	/**
	 * Constructor
	 *
	 * Simply determines whether the mcrypt library exists.
	 *
	 */
	public static function _Initialize() {
		self::$_MCryptExists = (!function_exists('mcrypt_encrypt')) ? false : true;
        if(($sHashType = Application::GetConfig('HASH_TYPE')) !== false) {
            self::$HASH_TYPE = $sHashType;
        }
        if(($sEncryptionKey = Application::GetConfig('ECRYPTION_KEY')) !== false) {
            self::$ECRYPTION_KEY = $sEncryptionKey;
        }
		log_message('debug', "Encrypt Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the encryption key
	 *
	 * Returns it as MD5 in order to have an exact-length 128 bit key.
	 * Mcrypt is sensitive to keys that are not the correct length
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function GetKey($sKey = '') {
		if ($sKey == '') {
			if (self::$ECRYPTION_KEY != '') {
				return self::$ECRYPTION_KEY;
			}

			$sKey = Application::GetConfig('ENCRYPTION_KEY');

			if ($sKey == false) {
				show_error('In order to use the encryption class requires that you set an encryption key in your config file.');
			}
		}

		return md5($sKey);
	}

	// --------------------------------------------------------------------

	/**
	 * Set the encryption key
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public static function SetKey($sKey = '') {
		self::$ECRYPTION_KEY = $sKey;
	}

	// --------------------------------------------------------------------

	/**
	 * Encode
	 *
	 * Encodes the message string using bitwise XOR encoding.
	 * The key is combined with a random hash, and then it
	 * too gets converted using XOR. The whole thing is then run
	 * through mcrypt (if supported) using the randomized key.
	 * The end result is a double-encrypted message string
	 * that is randomized with each call to this function,
	 * even if the supplied message and key are the same.
	 *
	 * @access	public
	 * @param	string	the string to encode
	 * @param	string	the key
	 * @return	string
	 */
	public static function Encode($sString, $sKey = '') {
		$sKey = self::GetKey($sKey);

		if (self::$_MCryptExists === true) {
			$sEnc = self::MCryptEncode($sString, $sKey);
		} else {
			$sEnc = self::_XOREncode($sString, $sKey);
		}

		return base64_encode($sEnc);
	}

	// --------------------------------------------------------------------

	/**
	 * Decode
	 *
	 * Reverses the above process
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public static function Decode($sString, $sKey = '') {
		$sKey = self::GetKey($sKey);

		if (preg_match('/[^a-zA-Z0-9\/\+=]/', $sString)) {
			return false;
		}

		$sDec = base64_decode($sString);

		if (self::$_MCryptExists === true) {
			if (($sDec = self::MCryptDecode($sDec, $sKey)) === false) {
				return false;
			}
		} else {
			$sDec = self::_XORDecode($sDec, $sKey);
		}

		return $sDec;
	}

	// --------------------------------------------------------------------

	/**
	 * Encode from Legacy
	 *
	 * Takes an encoded string from the original Encryption class algorithms and
	 * returns a newly encoded string using the improved method added in 2.0.0
	 * This allows for backwards compatibility and a method to transition to the
	 * new encryption algorithms.
	 *
	 * For more details, see http://codeigniter.com/user_guide/installation/upgrade_200.html#encryption
	 *
	 * @access	public
	 * @param	string
	 * @param	int		(mcrypt mode constant)
	 * @param	string
	 * @return	string
	 */
	public static function EncodeFromLegacy($sString, $sLegacyMode = MCRYPT_MODE_ECB, $sKey = '') {
		if (self::$_MCryptExists === false) {
			log_message('error', 'Encoding from legacy is available only when Mcrypt is in use.');
			return false;
		}

		// decode it first
		// set mode temporarily to what it was when string was encoded with the legacy
		// algorithm - typically MCRYPT_MODE_ECB
		$sCurrentMode = self::_GetMode();
		self::SetMode($sLegacyMode);

		$sKey = self::GetKey($sKey);

		if (preg_match('/[^a-zA-Z0-9\/\+=]/', $sString)) {
			return false;
		}

		$sDec = base64_decode($sString);

		if (($sDec = self::MCryptDecode($sDec, $sKey)) === false) {
			return false;
		}

		$sDec = self::_XORDecode($sDec, $sKey);

		// set the mcrypt mode back to what it should be, typically MCRYPT_MODE_CBC
		self::SetMode($sCurrentMode);

		// and re-encode
		return base64_encode(self::MCryptEncode($sDec, $sKey));
	}

	// --------------------------------------------------------------------

	/**
	 * XOR Encode
	 *
	 * Takes a plain-text string and key as input and generates an
	 * encoded bit-string using XOR
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public static function _XOREncode($sString, $sKey) {
		$sRand = '';
		while (strlen($sRand) < 32) {
			$sRand .= mt_rand(0, mt_getrandmax());
		}

		$sRand = self::Hash($sRand);

		$sEnc = '';
		for ($i = 0; $i < strlen($sString); $i++) {
			$sEnc .= substr($sRand, ($i % strlen($sRand)), 1).(substr($sRand, ($i % strlen($sRand)), 1) ^ substr($sString, $i, 1));
		}

		return self::_XORMerge($sEnc, $sKey);
	}

	// --------------------------------------------------------------------

	/**
	 * XOR Decode
	 *
	 * Takes an encoded string and key as input and generates the
	 * plain-text original message
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public static function _XORDecode($sString, $sKey) {
		$sString = self::_XORMerge($sString, $sKey);

		$sDec = '';
		for ($i = 0; $i < strlen($sString); $i++) {
			$sDec .= (substr($sString, $i++, 1) ^ substr($sString, $i, 1));
		}

		return $sDec;
	}

	// --------------------------------------------------------------------

	/**
	 * XOR key + string Combiner
	 *
	 * Takes a string and key as input and computes the difference using XOR
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public static function _XORMerge($sString, $sKey) {
		$hash = self::Hash($sKey);
		$sStr = '';
		for ($i = 0; $i < strlen($sString); $i++) {
			$sStr .= substr($sString, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}

		return $sStr;
	}

	// --------------------------------------------------------------------

	/**
	 * Encrypt using Mcrypt
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public static function MCryptEncode($vData, $sKey) {
		$nInitSize = mcrypt_get_iv_size(self::_GetCipher(), self::_GetMode());
		$nInitVect = mcrypt_create_iv($nInitSize, MCRYPT_RAND);
		return self::_AddCipherNoise($nInitVect.mcrypt_encrypt(self::_GetCipher(), $sKey, $vData, self::_GetMode(), $nInitVect), $sKey);
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt using Mcrypt
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public static function MCryptDecode($vData, $sKey) {
		$vData = self::_RemoveCipherNoise($vData, $sKey);
		$nInitSize = mcrypt_get_iv_size(self::_GetCipher(), self::_GetMode());

		if ($nInitSize > strlen($vData)) {
			return false;
		}

		$nInitVect = substr($vData, 0, $nInitSize);
		$vData = substr($vData, $nInitSize);
		return rtrim(mcrypt_decrypt(self::_GetCipher(), $sKey, $vData, self::_GetMode(), $nInitVect), "\0");
	}

	// --------------------------------------------------------------------

	/**
	 * Adds permuted noise to the IV + encrypted data to protect
	 * against Man-in-the-middle attacks on CBC mode ciphers
	 * http://www.ciphersbyritter.com/GLOSSARY.HTM#IV
	 *
	 * Function description
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public static function _AddCipherNoise($vData, $sKey) {
		$sKeyHash = self::Hash($sKey);
		$sKeylen = strlen($sKeyHash);
		$sStr = '';

		for ($i = 0, $j = 0, $nLen = strlen($vData); $i < $nLen; ++$i, ++$j) {
			if ($j >= $sKeylen) {
				$j = 0;
			}

			$sStr .= chr((ord($vData[$i]) + ord($sKeyHash[$j])) % 256);
		}

		return $sStr;
	}

	// --------------------------------------------------------------------

	/**
	 * Removes permuted noise from the IV + encrypted data, reversing
	 * _add_cipher_noise()
	 *
	 * Function description
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function _RemoveCipherNoise($vData, $sKey) {
		$sKeyHash = self::Hash($sKey);
		$sKeylen = strlen($sKeyHash);
		$sStr = '';

		for ($i = 0, $j = 0, $nLen = strlen($vData); $i < $nLen; ++$i, ++$j) {
			if ($j >= $sKeylen) {
				$j = 0;
			}

			$sTemp = ord($vData[$i]) - ord($sKeyHash[$j]);

			if ($sTemp < 0) {
				$sTemp = $sTemp + 256;
			}

			$sStr .= chr($sTemp);
		}

		return $sStr;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Mcrypt Cipher
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public static function SetCipher($sCipher) {
		self::$_MCryptCipher = $sCipher;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Mcrypt Mode
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public static function SetMode($sMode) {
		self::$_MCryptMode = $sMode;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Mcrypt cipher Value
	 *
	 * @access	private
	 * @return	string
	 */
	public static function _GetCipher() {
		if (self::$_MCryptCipher == '') {
			self::$_MCryptCipher = MCRYPT_RIJNDAEL_256;
		}

		return self::$_MCryptCipher;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Mcrypt Mode Value
	 *
	 * @access	private
	 * @return	string
	 */
	public static function _GetMode() {
		if (self::$_MCryptMode == '') {
			self::$_MCryptMode = MCRYPT_MODE_CBC;
		}

		return self::$_MCryptMode;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Hash type
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public static function SetHash($sType = 'sha1') {
		self::$HASH_TYPE = ($sType != 'sha1' && $sType != 'md5') ? 'sha1' : $sType;
	}

	// --------------------------------------------------------------------

	/**
	 * Hash encode a string
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function Hash($sStr) {
		return (self::$HASH_TYPE == 'sha1') ? self::SHA1($sStr) : md5($sStr);
	}

	// --------------------------------------------------------------------

	/**
	 * Generate an SHA1 Hash
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function SHA1($sStr) {
		if (!function_exists('sha1')) {
			if (!function_exists('mhash')) {
				return CSHA1::Generate($sStr);
			} else {
				return bin2hex(mhash(MHASH_SHA1, $sStr));
			}
		} else {
			return sha1($sStr);
		}
	}

}

// END CI_Encrypt class

/* End of file Encrypt.php */
/* Location: ./system/libraries/Encrypt.php */