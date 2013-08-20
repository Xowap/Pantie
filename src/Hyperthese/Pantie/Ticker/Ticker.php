<?php


namespace Hyperthese\Pantie\Ticker;


interface Ticker {
	/**
	 * Waits until something might have happened in watched path
	 *
	 * @param int $deadline Unix timestamp not to run after
	 */
	public function wait($deadline);

	/**
	 * Notifies the waiters of the path that something happened
	 */
	public function notify();
}