<?php


namespace Hyperthese\Pantie\Ticker;


class TimeoutTickerTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @var TimeoutTicker
	 */
	private $ticker;

	public function setUp() {
		$this->ticker = new TimeoutTicker();
	}

	public function testNotify() {
		$this->ticker->notify();
	}
}
