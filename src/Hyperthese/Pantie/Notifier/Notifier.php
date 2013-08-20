<?php


namespace Hyperthese\Pantie\Notifier;

/**
 * Class Notifier
 *
 * The Notifier interface handles the "real-time" sending and receiving of blobs
 *
 * @package Hyperthese\Pantie\Notifier
 */
interface Notifier {
	/**
	 * Provide a callback to be called when a new blob arrives. Usually, blobs will arrive one at a time, resulting in a
	 * single call to the callback. However an unlimited number of those could appear at once, and thus the callback
	 * could be called.
	 *
	 * The callback receive one argument: $callback($blob) where $blob is the blob as a string.
	 *
	 * @param callable $callback
	 */
	public function onBlob($callback);

	/**
	 * Actually sends a blob to the queue.
	 *
	 * @param string $content blob content to be sent
	 */
	public function sendBlob($content);

	/**
	 * Calling this method will result in entering the waiting loop for new messages. When a new message arrives, the
	 * callbacks provided through onBlob() are called, and then wait() returns.
	 *
	 * If results are already present, the trigger is released right away, and wait() returns immediately after.
	 *
	 * @param int $timeout Time to wait at most
	 */
	public function wait($timeout);
}