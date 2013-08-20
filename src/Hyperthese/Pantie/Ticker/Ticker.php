<?php


namespace Hyperthese\Pantie\Ticker;

/**
 * Class Ticker
 *
 * The Ticker is used for IPC, in order to wake a process from another process. Or supposedly so.
 *
 * @package Hyperthese\Pantie\Ticker
 */
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