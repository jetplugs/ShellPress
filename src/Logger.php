<?php
namespace shellpress\v1_0_0\src;

use shellpress\v1_0_0\lib\KLogger\KLogger;
use shellpress\v1_0_0\lib\Psr\Log\LogLevel;

class Logger extends KLogger {

    /**
     * Logger constructor.
     * Call parent constructor too.
     *
     * @param string $logDirectory
     * @param string $logLevelThreshold
     * @param array $options
     */
    function __construct($logDirectory, $logLevelThreshold = LogLevel::DEBUG, array $options = array()) {

        parent::__construct($logDirectory, $logLevelThreshold, $options);

    }

    public function getLog( $isAscending = false, $filePath = null ) {

        if( $filePath === null ){

            $filePath = $this->getLogFilePath();

        }

        $fileContents = file_get_contents( $filePath );

        return $fileContents;

    }

}