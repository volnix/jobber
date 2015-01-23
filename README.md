# Jobber

"Jobber" is a super-slim library for printing output from a CLI job.  It supports command-line colors through the use of [kevinlebrun/colors.php](https://packagist.org/packages/kevinlebrun/colors.php), a well-revered CLI color library.

It was built with simplicity in mind, as printing job output should be the least of your worries when building CLI-based scripts.

## Example output from Jobber

```
***************************************************
2015-01-23 15:52:35 - Starting test_name

INFO: 2015-01-23 15:52:35 - foo
WARNING: 2015-01-23 15:52:35 - bar
SUCCESS: 2015-01-23 15:52:35 - baz
FATAL: 2015-01-23 15:52:35 - qux

2015-01-23 15:52:35 - Execution Time: 0.0 seconds / Peak memory usage: 3.22 Mb
***************************************************
```

## Installation

Command line:

```bash
composer require volnix/jobber:1.*
```

composer.json:

```json
{
    "name": "your/application",
    "require": {
        "volnix/jobber": "1.*"
    }
}
```

## Usage

Typically the printer (`Volnix/Jobber/Printer`) will be started, then stopped.  The start method prints out the job name and and some asterisks to fence off this job's output.  The stop method prints memory usage, runtime, and more fences.

```php
use Volnix/Jobber/Printer;

Printer::start('my_job_name');
Printer::info('Something happened, but it is not super important.');
Printer::stop();
```

Jobber also supports getting the output out of the printer in plain-text.  This is especially useful for logging job output somewhere.

```php
Printer::start('my_job_name');
Printer::info('Something happened, but it is not super important.');
Printer::stop();

$my_logger->info(Printer::getOutput());
```

All message types:

- Info (Printer::info())
- Warning (Printer::warning())
- Success (Printer::success())
- Fatal (Printer::fatal())
	- **Note:** The fatal call actually calls Printer::stop() since this is reserved for fatal errors that should halt all progress.

If you desire to toggle verbosity on your job, this is supported.  This will only disable info messages, while still allowing others to come through.

```php
// turn off verbosity, disabling info messages
Printer::setVerbosity(false);
```

> **Note:** info messages will still be returned when calling Printer::getOutput()

If you desire to run multiple job "sessions" in one command, you can reset the printer.

```php
// do job 1
Printer::start('job_number_1');
// do something in your code...
Printer::success('Something good happened.');
Printer::stop();

// reset the printer
Printer::reset();

// start job 2
Printer::start('job_number_2');
// ...
```