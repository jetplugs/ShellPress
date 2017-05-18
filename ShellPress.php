<?php
namespace shellpress_1_0_0;

use Psr4AutoloaderClass;
use Katzgrau\KLogger\Logger;




/**
 * Core class of plugin.
 * To use it, simple extend it.
 */
class ShellPress {

	protected $options;
	protected $views;
	protected $autoloader;
	protected $log;


    /**
     * @param array $args
     */

	function init( Array $args ) {

        /**
         * First of all - include and register autoloader
         */
        if( ! class_exists( 'Psr4AutoloaderClass' ) ){

            require( __DIR__ . '/lib/external/Psr4AutoloaderClass.php' );

        }

        $this->autoloader = new Psr4AutoloaderClass();
        $this->autoloader->register();
        $this->autoloader->addNamespace( 'shellpress_1_0_0', __DIR__ );


	    $this->options = new Options( $this );
	    $this->options->init( array() );

	    $this->autoloader->addNamespace( 'Katzgrau\KLogger', __DIR__ . '/lib/external/KLogger' );
	    $this->autoloader->addNamespace( 'Psr\Log', __DIR__ . '/lib/external/Psr/Log' );
	    $this->log = new Logger( __DIR__, \Psr\Log\LogLevel::DEBUG );


		
	}

}
