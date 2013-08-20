<?php


namespace Hyperthese\Pantie\Notifier;


use Hyperthese\Pantie\Ticker\Ticker;
use Hyperthese\Pantie\Util\Directory;

class MockTicker implements Ticker {
	private $notifyCount = 0;

	/**
	* Waits until something might have happened in watched path
	*
	* @param int $deadline Unix timestamp not to run after
	*/
	public function wait($deadline) {
		return;
	}

	/**
	 * Notifies the waiters of the path that something happened
	 */
	public function notify() {
		$this->notifyCount += 1;
	}

	/**
	 * @return int
	 */
	public function getNotifyCount() {
		return $this->notifyCount;
	}
}


class FilesystemNotifierTest extends \PHPUnit_Framework_TestCase {
	const TEST_BLOB_CONTENT1 = "coucou";
	const TEST_BLOB_CONTENT2 = "pouet";
	const TEST_WAIT_TIMEOUT = 100;
	const TIMEOUT_PRECISION = 0.2;

	/** @var FilesystemNotifier $notifier */
	private $notifier;
	/** @var string $path */
	private $path;
	/** @var MockTicker $ticker */
	private $ticker;

	public function setUp() {
		$this->ticker = new MockTicker();
		$this->path = Directory::createTemporaryDirectory();
		$this->notifier = new FilesystemNotifier($this->path, $this->ticker);
	}

	public function testSendBlob() {
		$this->notifier->sendBlob(self::TEST_BLOB_CONTENT1);

		$candidates = glob(Directory::join($this->path, "*.blob"));
		$this->assertEquals(1, count($candidates));

		$content = file_get_contents($candidates[0]);
		$this->assertEquals(self::TEST_BLOB_CONTENT1, $content);

		$this->assertEquals(1, $this->ticker->getNotifyCount());
	}

	public function testGetBlob() {
		$blobs = array();
		$this->notifier->sendBlob(self::TEST_BLOB_CONTENT1);
		$this->notifier->sendBlob(self::TEST_BLOB_CONTENT2);

		$this->notifier->onBlob(function ($blob) use (&$blobs) {
			$blobs[] = $blob;
		});

		$this->notifier->wait(self::TEST_WAIT_TIMEOUT / 1000);

		$this->assertEquals(array(self::TEST_BLOB_CONTENT1, self::TEST_BLOB_CONTENT2), $blobs);
	}

	public function testGetBlobTimeout() {
		$start = microtime(true);
		$this->notifier->wait(self::TEST_WAIT_TIMEOUT / 1000);
		$stop = microtime(true);

		$this->assertEquals(self::TEST_WAIT_TIMEOUT, ($stop - $start) * 1000, '', self::TEST_WAIT_TIMEOUT * self::TIMEOUT_PRECISION);
	}

	public function tearDown() {
		Directory::removeDirectory($this->path);
	}
}
