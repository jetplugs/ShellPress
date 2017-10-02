<?php
namespace shellpress\v1_0_7\src\Handlers;

use shellpress\v1_0_7\lib\KLogger\KLogger;
use shellpress\v1_0_7\lib\Psr\Log\LogLevel;

class Logger extends KLogger {

    /**
     * Logger constructor.
     * Call parent constructor too.
     *
     * @param string $logDirectory
     * @param string $logLevelThreshold
     * @param array $options
     */
    function __construct( $logDirectory, $logLevelThreshold = LogLevel::DEBUG, array $options = array() ) {

        parent::__construct( $logDirectory, $logLevelThreshold, $options );

    }

    /**
     * Returns HTML string log of current day.
     *
     * @param bool $isAscending - if true, reverse rows
     * @param null $filePath
     *
     * @return bool|string - returns formatted log string or false when error
     */
    public function getPreformattedLog( $isAscending = true, $filePath = null ) {

        if( $filePath === null ){

            $filePath = $this->getLogFilePath();

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

}