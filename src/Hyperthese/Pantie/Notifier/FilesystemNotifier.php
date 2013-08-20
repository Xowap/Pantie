<?php


namespace Hyperthese\Pantie\Notifier;


use Hyperthese\Pantie\Ticker\Ticker;

/**
 * Class FilesystemNotifier
 *
 * A notifier that gets blob notifications from the file system.
 *
 * @package Hyperthese\Pantie\Notifier
 */
final class FilesystemNotifier implements Notifier {
	/**
	 * @var string Path to the folder containing the blob queue
	 */
	private $path;

	/**
	 * @var \Hyperthese\Pantie\Ticker\Ticker
	 */
	private $ticker;

	/**
	 * @var array List of callbacks to be triggered when a blob arrives
	 */
	private $callbacks = array();

	/**
	 * @param string $path   Path to the folder containing the blob queue. It will be created as needed when waiting,
	 *                       and will fail silently on sending if the path does not exist.
	 * @param Ticker $ticker A ticker instance responsible for waking up the process when some data appeared
	 */
	public function __construct($path, Ticker $ticker) {
		$this->path = $path;
		$this->ticker = $ticker;
	}

	/**
	 * Provide a callback to be called when a new blob arrives. Usually, blobs will arrive one at a time, resulting in a
	 * single call to the callback. However an unlimited number of those could appear at once, and thus the callback
	 * could be called
	 *
	 * @param callable $callback
	 */
	public function onBlob($callback) {
		$this->callbacks[] = $callback;
	}

	/**
	 * Checks that the working folder exists
	 *
	 * @throws NotifierInternalException
	 */
	private function sanityCheck() {
		if(!file_exists($this->path)) {
			if(!@mkdir($this->path, true)) {
				throw new NotifierInternalException("The \"pending\" ({$this->path}) directory could not be created");
			}
		}

		if(!is_dir($this->path)) {
			throw new NotifierInternalException("The \"pending\" ({$this->path}) directory is not actually a directory");
		}
	}

	/**
	 * Forwards $blob to all the callbacks.
	 *
	 * @param $blob
	 */
	protected function triggerCallbacks($blob) {
		foreach ($this->callbacks as $callback) {
			call_user_func($callback, $blob);
		}
	}

	private static function makeBlobName() {
		$microtime = microtime();
		$microtimePattern = "!0\.(\d+) (\d+)!";
		preg_match($microtimePattern, $microtime, $matches);

		$uniqueId = sha1(uniqid($microtime));

		return "{$matches['2']}{$matches['1']}_$uniqueId.blob";
	}

	/**
	 * Actually sends a blob to the queue.
	 *
	 * @param string $content blob content to be sent
	 */
	public function sendBlob($content) {
		$this->sanityCheck();

		$blobName = implode(DIRECTORY_SEPARATOR, array($this->path, $this->makeBlobName()));
		$fh = fopen($blobName, "w");
		fwrite($fh, $content);
		fclose($fh);

		$this->ticker->notify();
	}

	/**
	 * Calling this method will result in entering the waiting loop for new messages. When a new message arrives, the
	 * callbacks provided through onBlob() are called, and then wait() returns.
	 *
	 * If results are already present, the trigger is released right away, and wait() returns immediately after.
	 *
	 * @param int $timeout Time to wait at most
	 */
	public function wait($timeout) {
		$blobPattern = implode(DIRECTORY_SEPARATOR, array($this->path, "*.blob"));
		$foundBlobs = false;
		$deadline = microtime(true) + $timeout;

		$this->sanityCheck();

		while (!$foundBlobs and (microtime(true) < $deadline)) {
			$blobList = glob($blobPattern);

			foreach ($blobList as $blobPath) {
				$foundBlobs = true;

				$blobContent = file_get_contents($blobPath);
				$this->triggerCallbacks($blobContent);

				try {
					@unlink($blobPath);
				} catch(\Exception $ignored) {
					// Just to be safe
				}
			}

			$this->ticker->wait($deadline);
		}
	}
}