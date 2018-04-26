<?php
namespace shellpress\v1_2_1\src\Handlers\External;

use shellpress\v1_2_1\lib\KLogger\KLogger;
use shellpress\v1_2_1\lib\Psr\Log\LogLevel;
use shellpress\v1_2_1\src\Handlers\IHandler;

class LogHandler extends IHandler {

	/** @var KLogger */
	protected $kLogger;

	/**
	 * Called on handler construction.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		$this->kLogger = new KLogger( dirname( $this->shell()->getMainPluginFile() ) . '/log', 'debug',
			array(
				'Y-m-d G:i:s.u',
				'log_' . date( 'd-m-Y' ) . '.log',
				false,
				false,
				true
			)
		);

	}

    /**
     * Returns HTML string log of current day.
     *
     * @deprecated
     *
     * @param bool $isAscending - if true, reverse rows
     * @param null $filePath
     *
     * @return bool|string - returns formatted log string or false when error
     */
    public function getPreformattedLog( $isAscending = true, $filePath = null ) {

        if( $filePath === null ){

            $filePath = $this->kLogger->getLogFilePath();

        }

        $fileContents = file_get_contents( $filePath );

        if( $fileContents ){

            $rows = explode( PHP_EOL, $fileContents );  //  explode file to rows by \n ( new line )

            if( $isAscending ){

                krsort( $rows );    //  reverse array keys

            }

            return sprintf( '<pre>%1$s</pre>', implode( PHP_EOL, $rows ) );   //  glue all parts together with \n ( new line )

        } else {

            return false;

        }

    }

	/**
	 * Sets minimal log level for saving messages in file.
	 *
	 * @param string $logLevel
	 *
	 * @return void
	 */
    public function setLogLevel( $logLevel ) {
    	$this->kLogger->setLogLevelThreshold( $logLevel );
    }

	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function emergency( $message, array $context = array() ) {
		$this->kLogger->log( LogLevel::EMERGENCY, $message, $context );
	}

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function alert( $message, array $context = array() )  {
		$this->kLogger->log( LogLevel::ALERT, $message, $context );
	}

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function critical( $message, array $context = array() ) {
		$this->kLogger->log( LogLevel::CRITICAL, $message, $context );
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function error( $message, array $context = array() ) {
		$this->kLogger->log( LogLevel::ERROR, $message, $context );
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function warning( $message, array $context = array() ) {
		$this->kLogger->log( LogLevel::WARNING, $message, $context );
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function notice( $message, array $context = array() ) {
		$this->kLogger->log( LogLevel::NOTICE, $message, $context );
	}

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function info( $message, array $context = array() ) {
		$this->kLogger->log( LogLevel::INFO, $message, $context );
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function debug( $message, array $context = array() ) {
		$this->kLogger->log( LogLevel::DEBUG, $message, $context );
	}

}