<?php


namespace Hyperthese\Pantie\Ticker;


use Hyperthese\Pantie\Util\Directory;

class SocketTicker implements Ticker {
	const MAGIC_WORD = '\o/';

	private $socketPath;
	private $path;

	public function __construct($path) {
		$this->socketPath = Directory::join($path, getmypid() . ".socket");
		$this->path = $path;
	}

	/**
	 * @param $deadline
	 *
	 * @return mixed
	 */
	public static function timeToDeadlineFloat($deadline) {
		$timeToDeadline = $deadline - microtime(true);

		return $timeToDeadline;
	}

	/**
	 * Calculates the number of seconds and microseconds before reaching the given deadline. The seconds and
	 * microseconds are the two values of the returned array.
	 *
	 * @param $deadline
	 * @return array
	 */
	public static function timeToDeadlineArray($deadline) {
		$timeToDeadline = self::timeToDeadlineFloat($deadline);

		$seconds = intval($timeToDeadline);
		$microseconds = ($timeToDeadline - $seconds) * 1000000;

		return array($seconds, $microseconds);
	}

	/**
	 * Sets the timeout of the given socket to expire at the given deadline.
	 *
	 * @param $deadline
	 * @param $socket
	 */
	public function timeoutAtDeadline($deadline, $socket) {
		list($seconds, $microseconds) = $this->timeToDeadlineArray($deadline);
		stream_set_timeout($socket, $seconds, $microseconds);
	}

	/**
	 * Waits until something might have happened in watched path
	 *
	 * @param int $deadline Unix timestamp not to run after
	 *
	 * @throws TickerInternalException
	 */
	public function wait($deadline) {
		$socket = stream_socket_server("unix://{$this->socketPath}", $errorNumber, $errorString);

		if (!$socket) {
			throw new TickerInternalException(
				sprintf("Impossible to create the ticker socket, error %d: %s", $errorNumber, $errorString)
			);
		}

		while (microtime(true) < $deadline) {
			$connection = @stream_socket_accept($socket, $this->timeToDeadlineFloat($deadline));

			if ($connection === false) {
				continue;
			}

			stream_set_blocking($connection, 1);
			$this->timeoutAtDeadline($deadline, $connection);

			$word = fread($connection, strlen(self::MAGIC_WORD));

			if ($word === self::MAGIC_WORD) {
				break;
			}
		}
	}

	/**
	 * Notifies the waiters of the path that something happened
	 */
	public function notify() {
		$socketPattern = Directory::join($this->path, "*.socket");
		foreach (glob($socketPattern) as $socketPath) {
			$socket = fsockopen("unix://$socketPath");

			if (!$socket) {
				continue;
			}

			fwrite($socket, self::MAGIC_WORD);
			fclose($socket);
		}
	}
}
