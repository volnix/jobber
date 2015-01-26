<?php
/**
 * User: nvolgas
 * Date: 1/23/15
 * Time: 8:19 AM
 */

namespace Volnix\Jobber\Tests;


use Volnix\Jobber\Printer;

class PrinterTest extends \PHPUnit_Framework_TestCase {

	public function testStartStop()
	{
		ob_start();
		Printer::start('test_name');
		Printer::stop();
		$output = ob_get_clean();

		$this->assertNotEmpty($output);
		$this->assertRegExp('/Starting test_name/', $output);
		$this->assertRegExp('/Execution Time\: [0-9]+\.[0-9]+ seconds/', $output);
		$this->assertRegExp('/Peak memory usage\: [0-9]+\.[0-9]+ Mb/', $output);

		$output = Printer::getOutput();
		$this->assertNotEmpty($output);
		$this->assertRegExp('/Starting test_name/', $output);
		$this->assertRegExp('/Execution Time\: [0-9]+\.[0-9]+ seconds/', $output);
		$this->assertRegExp('/Peak memory usage\: [0-9]+\.[0-9]+ Mb/', $output);
	}

	public function testStartStopReset()
	{
		ob_start();
		Printer::start('test_name');
		Printer::stop();

		$this->assertNotEmpty(ob_get_clean());
		$this->assertNotEmpty(Printer::getOutput());

		Printer::reset();
		$this->assertEmpty(Printer::getOutput());
		$this->assertEquals(0, Printer::getExecutionTime());
	}

	public function testStringMessages()
	{
		ob_start();
		Printer::start('test_name');
		Printer::info('foo');
		Printer::warning('bar');
		Printer::success('baz');
		Printer::error('qux');
		$output = ob_get_clean();

		$this->assertRegExp('/INFO\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- foo/', $output);
		$this->assertRegExp('/WARNING\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- bar/', $output);
		$this->assertRegExp('/SUCCESS\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- baz/', $output);
		$this->assertRegExp('/ERROR\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- qux/', $output);

		$this->assertRegExp('/INFO\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- foo/', Printer::getOutput());
		$this->assertRegExp('/WARNING\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- bar/', Printer::getOutput());
		$this->assertRegExp('/SUCCESS\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- baz/', Printer::getOutput());
		$this->assertRegExp('/ERROR\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- qux/', Printer::getOutput());
	}

	public function testArrayMessages()
	{
		ob_start();
		Printer::start('test_name');
		Printer::info(['foo', 'bar']);
		Printer::warning(['foo', 'bar']);
		Printer::success(['foo', 'bar']);
		Printer::error(['foo', 'bar']);
		$output = ob_get_clean();

		$this->assertRegExp('/INFO\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- foo/', $output);
		$this->assertRegExp('/INFO\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- bar/', $output);
		$this->assertRegExp('/WARNING\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- foo/', $output);
		$this->assertRegExp('/WARNING\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- bar/', $output);
		$this->assertRegExp('/SUCCESS\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- foo/', $output);
		$this->assertRegExp('/SUCCESS\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- bar/', $output);
		$this->assertRegExp('/ERROR\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- foo/', $output);
		$this->assertRegExp('/ERROR\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- bar/', $output);
	}

	public function testVerbosityOnOff()
	{
		ob_start();
		Printer::start('test_name');
		Printer::setVerbosity(false);
		Printer::info('foo');
		$output = ob_get_clean();

		$this->assertNotRegExp('/INFO\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- foo/', $output);
		$this->assertRegExp('/INFO\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- foo/', Printer::getOutput());
	}

	public function testFatalError()
	{
		ob_start();
		Printer::start('test_name');
		Printer::fatal('error_text');
		$output = ob_get_clean();

		$this->assertNotEmpty($output);
		$this->assertRegExp('/Execution Time\: [0-9]+\.[0-9]+ seconds/', $output);
		$this->assertRegExp('/Peak memory usage\: [0-9]+\.[0-9]+ Mb/', $output);
		$this->assertRegExp('/ERROR\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- error_text/', $output);

		$output = Printer::getOutput();
		$this->assertNotEmpty($output);
		$this->assertRegExp('/Execution Time\: [0-9]+\.[0-9]+ seconds/', $output);
		$this->assertRegExp('/Peak memory usage\: [0-9]+\.[0-9]+ Mb/', $output);
		$this->assertRegExp('/ERROR\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- error_text/', Printer::getOutput());
	}

	public function testMemoryFunctions()
	{
		$this->assertInternalType('float', Printer::getMemoryUsageInMb());
		$this->assertInternalType('float', Printer::getMemoryUsageInKb());
	}

	public function testStopTextOnlyPrintsOnce()
	{
		ob_start();
		Printer::start('test_name');
		Printer::stop();
		$output = ob_get_clean();

		$this->assertRegExp('/Execution Time\: [0-9]+\.[0-9]+ seconds/', $output);
		$this->assertRegExp('/Peak memory usage\: [0-9]+\.[0-9]+ Mb/', $output);

		ob_start();
		Printer::stop();
		$output2 = ob_get_clean();

		$this->assertNotRegExp('/Execution Time\: [0-9]+\.[0-9]+ seconds/', $output2);
		$this->assertNotRegExp('/Peak memory usage\: [0-9]+\.[0-9]+ Mb/', $output2);

		ob_start();
		Printer::start('test_name');
		Printer::fatal('foo');
		$output = ob_get_clean();

		$this->assertRegExp('/Execution Time\: [0-9]+\.[0-9]+ seconds/', $output);
		$this->assertRegExp('/Peak memory usage\: [0-9]+\.[0-9]+ Mb/', $output);
		$this->assertRegExp('/ERROR\: [0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \- foo/', Printer::getOutput());

		ob_start();
		Printer::stop();
		$output2 = ob_get_clean();

		$this->assertNotRegExp('/Execution Time\: [0-9]+\.[0-9]+ seconds/', $output2);
		$this->assertNotRegExp('/Peak memory usage\: [0-9]+\.[0-9]+ Mb/', $output2);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBadMessageThrowsException()
	{
		ob_start();
		Printer::start('test');

		$junk = new \stdClass;
		Printer::info($junk);
		ob_clean();
	}
}
 