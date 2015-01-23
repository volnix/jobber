<?php
/**
 * User: nvolgas
 * Date: 1/23/15
 * Time: 7:47 AM
 */

namespace Volnix\Jobber;

use Colors\Color;

class Printer {

	/** @var bool $verbose Whether verbosity is turned on or not */
	private static $verbose = true;

	/** @var string $output A string containing all the output from the job */
	private static $output  = "";

	/** @var string $start The starting time of the job */
	private static $start   = "";

	/** @var string $end The ending time of the job */
	private static $end     = "";

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
		print $message;
	}

	/**
	 * Stop the job and print some stats about its execution.
	 *
	 * @return void
	 */
	public static function stop()
	{
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
	 * @return void
	 */
	public static function warning($message)
	{
		$output = sprintf('WARNING: %s - %s', (new \DateTime)->format('Y-m-d H:i:s'), $message);
		$color = new Color;
		echo $color($output)->bg('yellow')->black() . PHP_EOL;

		self::$output .= $output . PHP_EOL;
	}

	/**
	 * Print a success message.
	 *
	 * @param $message
	 * @return void
	 */
	public static function success($message)
	{
		$output = sprintf('SUCCESS: %s - %s', (new \DateTime)->format('Y-m-d H:i:s'), $message);
		$color = new Color;
		echo $color($output)->bg('green')->bold()->black() . PHP_EOL;

		self::$output .= $output . PHP_EOL;
	}

	/**
	 * Print a an informational message.  Will not print to CLI if verbosity is disabled.
	 *
	 * @param $message
	 * @return void
	 */
	public static function info($message)
	{
		$output = sprintf('INFO: %s - %s', (new \DateTime)->format('Y-m-d H:i:s'), $message);

		if (static::$verbose === true) {
			$color = new Color;
			echo $color($output)->white() . PHP_EOL;
		}

		self::$output .= $output . PHP_EOL;
	}

	/**
	 * Print a fatal message and call Printer::stop().
	 *
	 * @param $message
	 * @return void
	 */
	public static function fatal($message)
	{
		$output = sprintf('FATAL: %s - %s', (new \DateTime)->format('Y-m-d H:i:s'), $message);

		$color = new Color;
		echo $color($output)->bg('red')->bold()->white() . PHP_EOL;
		self::$output .= $output . PHP_EOL;

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
		return (memory_get_peak_usage() / 1024 / 1024);
	}

	/**
	 * Get peak memory usage in Kb
	 *
	 * @return float
	 */
	public static function getMemoryUsageInKb()
	{
		return (memory_get_peak_usage() / 1024);
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
	}

} 