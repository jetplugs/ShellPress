<?php
namespace shellpress\v1_0_0;




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

            require( 'lib/Psr4Autoloader/Psr4AutoloaderClass.php' );

        }

        $this->autoloader = new lib\Psr4Autoloader\Psr4AutoloaderClass();
        $this->autoloader->register();
        $this->autoloader->addNamespace( 'shellpress\v1_0_0', __DIR__ );

        //  ----------------------------------------
        //  Options handler init
        //  ----------------------------------------

	    $this->options = new src\Options( $this );
	    $this->options->init( array(
	    	'namespace'		=>	''
	    ) );

	    //  ----------------------------------------
	    //  Logger handler init
	    //  ----------------------------------------

//	    $this->autoloader->addNamespace( 'Katzgrau\KLogger', __DIR__ . '/lib/External/KLogger');
//	    $this->autoloader->addNamespace( 'Psr\Log', __DIR__ . '/lib/External/Psr/Log');
//	    $this->log = new Logger( __DIR__, \Psr\Log\LogLevel::DEBUG );


		
	}

}
