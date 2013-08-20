<?php


namespace Hyperthese\Pantie\Ticker;


class TimeoutTickerTest extends \PHPUnit_Framework_TestCase {
	const TEST_PERIOD = 100;

	/**
	 * @var TimeoutTicker
	 */
	private $ticker;

	public function setUp() {
		$this->ticker = new TimeoutTicker(self::TEST_PERIOD);
	}

	public function testNotify() {
		$this->ticker->notify();
	}

	public function testWait() {
		$start = microtime(true);
		$deadline = $start + (self::TEST_PERIOD * 1.5 / 1000);

		$this->ticker->wait($deadline);
		$stop1 = microtime(true);

		$this->ticker->wait($deadline);
		$stop2 = microtime(true);

		// Assert that the waiting time was the TEST_PERIOD with a 5% precision
		$this->assertEquals(self::TEST_PERIOD, ($stop1 - $start) * 1000, '', self::TEST_PERIOD * 0.05);
		$this->assertEquals(self::TEST_PERIOD / 2, ($stop2 - $stop1) * 1000, '', self::TEST_PERIOD * 0.025);
	}
}
