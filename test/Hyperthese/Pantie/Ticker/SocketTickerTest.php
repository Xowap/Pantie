<?php


namespace Hyperthese\Pantie\Ticker;


use Hyperthese\Pantie\Util\Directory;

class SocketTickerTest extends \PHPUnit_Framework_TestCase {
	const WAIT_TIMEOUT = 100;

	/** @var string */
	private $temporaryDirectory;
	/** @var SocketTicker */
	private $ticker;

	public function setUp() {
		$this->temporaryDirectory = Directory::createTemporaryDirectory();
		$this->ticker = new SocketTicker($this->temporaryDirectory);
	}

	public function tearDown() {
		//Directory::removeDirectory($this->temporaryDirectory);
	}

	public function testWaitSuccess() {
		$pid = pcntl_fork();

		if ($pid === -1) {
			throw new \Exception("Unable to fork!");
		} elseif ($pid) {
			$start = microtime(true);
			$this->ticker->wait(microtime(true) + self::WAIT_TIMEOUT / 1000);
			$stop = microtime(true);

			$this->assertEquals(self::WAIT_TIMEOUT / 2, ($stop - $start) * 1000, null, self::WAIT_TIMEOUT * 0.20);

			pcntl_wait($status);
		} else {
			usleep(self::WAIT_TIMEOUT * 1000 / 2);
			$this->ticker->notify();
			exit();
		}
	}

	public function testWaitTimeout() {
		$start = microtime(true);
		$this->ticker->wait(microtime(true) + self::WAIT_TIMEOUT / 1000);
		$stop = microtime(true);

		$this->assertEquals(self::WAIT_TIMEOUT, ($stop - $start) * 1000, null, self::WAIT_TIMEOUT * 0.20);
	}
}
