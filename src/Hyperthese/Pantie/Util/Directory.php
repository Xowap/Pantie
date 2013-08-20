<?php


namespace Hyperthese\Pantie\Util;


use InvalidArgumentException;

class Directory {
	public static function removeDirectory($path) {
		if (! is_dir($path)) {
			throw new InvalidArgumentException("$path must be a directory");
		}

		if (substr($path, strlen($path) - 1, 1) != '/') {
			$path .= '/';
		}

		foreach (glob($path . '*', GLOB_MARK) as $file) {
			if (is_dir($file)) {
				self::removeDirectory($file);
			} else {
				unlink($file);
			}
		}

		rmdir($path);
	}

	public static function createTemporaryDirectory() {
		$temporaryFile = tempnam(sys_get_temp_dir(), 'pantie');

		if (file_exists($temporaryFile)) {
			unlink($temporaryFile);
		}

		mkdir($temporaryFile);

		return $temporaryFile;
	}

	/**
	 * join
	 *
	 * Takes path components as array or variadic arguments, and join them with
	 * the directory separator.
	 *
	 * Note: the latest component starting with a directory separator is considered as the absolute root for the
	 * path. This will then discard any previous component.
	 * TODO: make this work for Windows and its shitty X: drives letters. Screw Microsoft.
	 *
	 * Example:
	 * Directory::join("/some/path/to/somewhere/", "another/dir", "my_file.jpg");
	 * // returns "/some/path/to/somewhere/another/dir/my_file.jpg"
	 *
	 * Directory::join(array("/foo", "bar"));
	 * // returns "/foo/bar"
	 *
	 * @param mixed $path,... unlimited will either join all the arguments altogether, either join $path if it is an
	 * array
	 * @static
	 * @access public
	 * @return string
	 */
	static function join($path = null) {
		if($path === null or is_string($path)) {
			$path = func_get_args();
		}

		$cleanPath = array();
		foreach($path as $part) {
			if($part !== null) {
				$cleanPart = rtrim($part, DIRECTORY_SEPARATOR);
				if($cleanPart[0] === DIRECTORY_SEPARATOR) {
					$cleanPath = array();
				}
				$cleanPath[] = $cleanPart;
			}
		}

		return implode(DIRECTORY_SEPARATOR, $cleanPath);
	}
}