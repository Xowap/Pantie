<?php


namespace Hyperthese\Pantie\Ticker;


final class TimeoutTicker implements Ticker {
	/**
	 * Default poll period, in milliseconds
	 */
	const DEFAULT_POLL_MILLISEC_PERIOD = 100;

	private $millisecWaitingTime;

	public function __construct($millisecWaitingTime = self::DEFAULT_POLL_MILLISEC_PERIOD) {
		$this->millisecWaitingTime = $millisecWaitingTime;
	}

	/**
	 * Waits until something might have happened in watched path
	 *
	 * @param int $deadline Unix timestamp not to run after
	 */
	public function wait($deadline) {
		$usualSleepTime = $this->millisecWaitingTime * 1000;
		$timeToDeadline = ($deadline - time()) * 1000 * 1000;

		if ($timeToDeadline > 0) {
			usleep(min($usualSleepTime, $timeToDeadline));
		}
	}

	/**
	 * Notifies the waiters of the path that something happened
	 */
	public function notify() {
		// This method does actually nothing
	}
}