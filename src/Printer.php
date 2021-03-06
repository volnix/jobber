<?php
/**
 * User: nvolgas
 * Date: 1/23/15
 * Time: 7:47 AM
 */

namespace Volnix\Jobber;

use Colors\Color;

class Printer {

	const TYPE_ERROR    = 'ERROR';

	const TYPE_WARNING  = 'WARNING';

	const TYPE_SUCCESS  = 'SUCCESS';

	const TYPE_INFO     = 'INFO';

	/** @var bool $verbose Whether verbosity is turned on or not */
	protected static $verbose   = true;

	/** @var string $output A string containing all the output from the job */
	protected static $output    = "";

	/** @var string $start The starting time of the job */
	protected static $start     = "";

	/** @var string $end The ending time of the job */
	protected static $end       = "";

	/** @var bool $stopped Whether or not the job has been stopped */
	protected static $stopped   = true;

	/**
	 * Start the job.  Optional $job_name argument for the name of this job.
	 *
	 * @param string $job_name
	 * @return void
	 */
	public static function start($job_name = "")
	{
		static::$start = microtime();
		$message = sprintf("%s***************************************************%s%s - Starting %s%s%s",
			PHP_EOL,
			PHP_EOL,
			(new \DateTime)->format('Y-m-d H:i:s'),
			$job_name,
			PHP_EOL, PHP_EOL
		);

		static::$output .= $message;
		static::$stopped = false;
		print $message;
	}

	/**
	 * Stop the job and print some stats about its execution.
	 *
	 * @return void
	 */
	public static function stop()
	{
		// only print this stuff out if the job is in a running state
		if ( ! static::$stopped ) {

			static::$end = microtime();
			$message = sprintf("%s%s - Execution Time: %.1f seconds / Peak memory usage: %.2f Mb%s***************************************************%s",
				PHP_EOL,
				(new \DateTime)->format('Y-m-d H:i:s'),
				self::getExecutionTime(),
				self::getMemoryUsageInMb(),
				PHP_EOL,
				PHP_EOL
			);

			static::$output .= $message;
			static::$stopped = true;

		} else {
			$message = "";
		}

		print $message;
	}

	/**
	 * Set the verbosity of our printing (this will exclude info messages if verbsosity is set to FALSE).
	 *
	 * @param bool $verbosity
	 */
	public static function setVerbosity($verbosity = false)
	{
		self::$verbose = (bool) $verbosity;
	}


	/**
	 * Get all the output from the job in plain-text (useful for logging).
	 *
	 * @return string
	 */
	public static function getOutput()
	{
		return static::$output;
	}

	/**
	 * Print a warning message.
	 *
	 * @param $message
	 * @param $replacements
	 * @return void
	 */
	public static function warning($message, array $replacements = [])
	{
		$output = self::buildMessage($message, $replacements, self::TYPE_WARNING);

		$color = new Color;
		echo $color($output)->bg('yellow')->black() . PHP_EOL;

		self::$output .= $output . PHP_EOL;
	}

	/**
	 * Print a success message.
	 *
	 * @param string|array $message
	 * @param array $replacements
	 * @return void
	 */
	public static function success($message, array $replacements = [])
	{
		$output = self::buildMessage($message, $replacements, self::TYPE_SUCCESS);

		$color = new Color;
		echo $color($output)->bg('green')->bold()->black() . PHP_EOL;

		self::$output .= $output . PHP_EOL;
	}

	/**
	 * Print an informational message.  Will not print to CLI if verbosity is disabled.
	 *
	 * @param string|array $message
	 * @param array $replacements
	 * @return void
	 */
	public static function info($message, array $replacements = [])
	{
		$output = self::buildMessage($message, $replacements, self::TYPE_INFO);

		if (static::$verbose === true) {
			$color = new Color;
			echo $color($output)->white() . PHP_EOL;
		}

		self::$output .= $output . PHP_EOL;
	}

	/**
	 * Print an error message.
	 *
	 * @param string|array $message
	 * @param array $replacements
	 * @return void
	 */
	public static function error($message, array $replacements = [])
	{
		$output = self::buildMessage($message, $replacements, self::TYPE_ERROR);

		$color = new Color;
		echo $color($output)->bg('red')->bold()->white() . PHP_EOL;

		self::$output .= $output . PHP_EOL;
	}

	/**
	 * Print a fatal message and call Printer::stop().
	 *
	 * @param string|array $message
	 * @param array $replacements
	 * @return void
	 */
	public static function fatal($message, array $replacements = [])
	{
		// print an error
		self::error($message, $replacements);

		// call stop since this is a dying message
		self::stop();
	}

	/**
	 * Get peak memeory usage in Mb
	 *
	 * @return float
	 */
	public static function getMemoryUsageInMb()
	{
		return (float) (memory_get_peak_usage() / 1024 / 1024);
	}

	/**
	 * Get peak memory usage in Kb
	 *
	 * @return float
	 */
	public static function getMemoryUsageInKb()
	{
		return (float) (memory_get_peak_usage() / 1024);
	}

	/**
	 * Get the execution time of the job, using the current time if the stop function hasn't been called
	 *
	 * @return float
	 */
	public static function getExecutionTime()
	{
		// if we haven't started the job yet, just bail out with 0
		if (empty(static::$start)) {
			return 0.0;
		}

		// if we haven't flagged an end to the job yet, then use the current time
		$end = !empty(static::$end) ? static::$end : microtime();

		list($start_usec, $start_sec) = explode(" ", static::$start);
		list($end_usec, $end_sec) = explode(" ", $end);
		$diff_sec= intval($end_sec) - intval($start_sec);
		$diff_usec= floatval($end_usec) - floatval($start_usec);
		return floatval( $diff_sec ) + $diff_usec;
	}

	/**
	 * Reset the job in preparation for starting over.
	 *
	 * @return void
	 */
	public static function reset()
	{
		static::$output     = "";
		static::$start      = "";
		static::$end        = "";
		static::$stopped    = true;
	}

	/**
	 * Build our message with the input provided.
	 *
	 * @param string|array $message
	 * @param array $replacements
	 * @param string $type
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	private static function buildMessage($message, array $replacements = [], $type = "")
	{
		// validate our message first
		if (!is_scalar($message) && !is_array($message)) {
			throw new \InvalidArgumentException("Invalid message type.  Message must be string or array.");
		}

		$messages = [];

		if (is_array($message)) {

			// iterate, building each message
			foreach ($message as $msg) {
				$messages[] = sprintf('%s: %s - %s', $type, (new \DateTime)->format('Y-m-d H:i:s'), $msg);
			}

		} elseif (count($replacements) > 0) {
			$parsed_message = vsprintf($message, $replacements);
			$messages[] = sprintf('%s: %s - %s', $type, (new \DateTime)->format('Y-m-d H:i:s'), $parsed_message);
		} else {
			$messages[] = sprintf('%s: %s - %s', $type, (new \DateTime)->format('Y-m-d H:i:s'), $message);
		}

		return implode(PHP_EOL, $messages);
	}


} 