<?php
namespace shellpress\v1_0_0;

use shellpress\v1_0_0\lib\KLogger\Logger;
use shellpress\v1_0_0\lib\Psr\Log\LogLevel;
use shellpress\v1_0_0\lib\Psr4Autoloader\Psr4AutoloaderClass;


/**
 * Core class of plugin.
 * To use it, simple extend it.
 */
class ShellPress {

	protected $options;
	protected $views;

    /**
     * @var Psr4AutoloaderClass
     */
	protected $autoloader;

    /**
     * @var Logger
     */
	protected $log;


    /**
     * @param array $args
     */

	protected function init( Array $args ) {

		$default_args = array(
			'options'	=>	array(
								'namespace'		=>	'shellpress_app'
							)
		);

	    //  ----------------------------------------
	    //  PSR4 Autloader init
	    //  ----------------------------------------

        if( ! class_exists( 'shellpress\v1_0_0\lib\Psr4Autoloader\Psr4AutoloaderClass' ) ){

            require( dirname( __FILE__ ) . '/lib/Psr4Autoloader/Psr4AutoloaderClass.php' );

        }

        $this->autoloader = new Psr4AutoloaderClass();
        $this->autoloader->register();
        $this->autoloader->addNamespace( 'shellpress\v1_0_0', __DIR__ );

        //  ----------------------------------------
        //  Options handler init
        //  ----------------------------------------

	    $this->options = new src\Options( $this );
	    $this->options->init( array() );

	    //  ----------------------------------------
	    //  Logger handler init
	    //  ----------------------------------------

	    $this->log = new Logger( __DIR__, LogLevel::DEBUG );
		
	}

}
