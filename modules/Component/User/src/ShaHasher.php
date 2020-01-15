<?php

namespace GuoJiangClub\Catering\Component\User;

use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use RuntimeException;

class ShaHasher implements HasherContract
{

	/**
	 * Default crypt cost factor.
	 *
	 * @var int
	 */
	protected $rounds = 10;

	/**
	 * Hash the given value.
	 *
	 * @param string $value
	 * @param array  $options
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	public function make($value, array $options = [])
	{
		$cost = isset($options['rounds']) ? $options['rounds'] : $this->rounds;

		if (settings('password_encryption_type') == 'SHA256') {

			$hash = hash('sha256', $value);

			return $hash;
		} else {
			$hash = password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);

			if ($hash === false) {
				throw new RuntimeException('Bcrypt hashing not supported.');
			}

			return $hash;
		}
	}

	public function check($value, $hashedValue, array $options = [])
	{
		if (substr($hashedValue, 0, 2) == 'U$') {
			// This may be an updated password from user_update_7000(). Such hashes
			// have 'U' added as the first character and need an extra md5().
			$stored_hash = substr($hashedValue, 1);
			$password    = md5($value);
		} else {
			$stored_hash = $hashedValue;
		}

		$type = substr($stored_hash, 0, 3);
		switch ($type) {
			case '$S$':
				// A normal Drupal 7 password using sha512.
				$hash = $this->_password_crypt('sha512', $value, $stored_hash);
				break;
			case '$H$':
				// phpBB3 uses "$H$" for the same thing as "$P$".
			case '$P$':
				// A phpass password generated using md5.  This is an
				// imported password or from an earlier Drupal version.
				$hash = $this->_password_crypt('md5', $value, $stored_hash);
				break;
			default:
				$hash = '';
				break;
		}

		return password_verify($value, $hashedValue)
			|| ($hash && $stored_hash == $hash)
			|| hash('sha256', $value) == $hashedValue;
	}

	/**
	 * Check if the given hash has been hashed using the given options.
	 *
	 * @param string $hashedValue
	 * @param array  $options
	 *
	 * @return bool
	 */
	public function needsRehash($hashedValue, array $options = [])
	{
		$cost = isset($options['rounds']) ? $options['rounds'] : $this->rounds;

		return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, ['cost' => $cost]);
	}

	/**
	 * Set the default password work factor.
	 *
	 * @param int $rounds
	 *
	 * @return $this
	 */
	public function setRounds($rounds)
	{
		$this->rounds = (int) $rounds;

		return $this;
	}

	private function _password_crypt($algo, $password, $setting)
	{
		// Prevent DoS attacks by refusing to hash large passwords.
		if (strlen($password) > 512) {
			return false;
		}
		// The first 12 characters of an existing hash are its setting string.
		$setting = substr($setting, 0, 12);

		if ($setting[0] != '$' || $setting[2] != '$') {
			return false;
		}
		$count_log2 = $this->_password_get_count_log2($setting);
		// Hashes may be imported from elsewhere, so we allow != DRUPAL_HASH_COUNT
		if ($count_log2 < 7 || $count_log2 > 30) {
			return false;
		}
		$salt = substr($setting, 4, 8);
		// Hashes must have an 8 character salt.
		if (strlen($salt) != 8) {
			return false;
		}

		// Convert the base 2 logarithm into an integer.
		$count = 1 << $count_log2;

		// We rely on the hash() function being available in PHP 5.2+.
		$hash = hash($algo, $salt . $password, true);
		do {
			$hash = hash($algo, $hash . $password, true);
		} while (--$count);

		$len    = strlen($hash);
		$output = $setting . $this->_password_base64_encode($hash, $len);
		// _password_base64_encode() of a 16 byte MD5 will always be 22 characters.
		// _password_base64_encode() of a 64 byte sha512 will always be 86 characters.
		$expected = 12 + ceil((8 * $len) / 6);

		return (strlen($output) == $expected) ? substr($output, 0, 55) : false;
	}

	/**
	 * Parse the log2 iteration count from a stored hash or setting string.
	 */
	private function _password_get_count_log2($setting)
	{
		$itoa64 = $this->_password_itoa64();

		return strpos($itoa64, $setting[3]);
	}

	private function _password_itoa64()
	{
		return './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	}

	/**
	 * Encodes bytes into printable base 64 using the *nix standard from crypt().
	 *
	 * @param $input
	 *   The string containing bytes to encode.
	 * @param $count
	 *   The number of characters (bytes) to encode.
	 *
	 * @return
	 *   Encoded string
	 */
	private function _password_base64_encode($input, $count)
	{
		$output = '';
		$i      = 0;
		$itoa64 = $this->_password_itoa64();
		do {
			$value  = ord($input[$i++]);
			$output .= $itoa64[$value & 0x3f];
			if ($i < $count) {
				$value |= ord($input[$i]) << 8;
			}
			$output .= $itoa64[($value >> 6) & 0x3f];
			if ($i++ >= $count) {
				break;
			}
			if ($i < $count) {
				$value |= ord($input[$i]) << 16;
			}
			$output .= $itoa64[($value >> 12) & 0x3f];
			if ($i++ >= $count) {
				break;
			}
			$output .= $itoa64[($value >> 18) & 0x3f];
		} while ($i < $count);

		return $output;
	}
}