<?php
namespace shellpress\v1_0_0;

use shellpress\v1_0_0\lib\KLogger\Logger;
use shellpress\v1_0_0\lib\Psr\Log\LogLevel;
use shellpress\v1_0_0\lib\Psr4Autoloader\Psr4AutoloaderClass;
use shellpress\v1_0_0\src\Options;
use shellpress\v1_0_0\src\Pages\PagesHandler;


/**
 * Core class of plugin.
 * To use it, simple extend it.
 */
class ShellPress {

    /**
     * @var Options
     */
	protected $options;

    /**
     * @var
     */
    protected $widgets;

    /**
     * @var PagesHandler
     */
    protected $pages;

    /**
     * @var Psr4AutoloaderClass
     */
	protected $autoloader;

    /**
     * @var Logger
     */
	protected $log;


    /**
     * You should call this method just after
     * object creation. __construct method is a
     * good place to do that.
     *
     * @param array $args
     */

	protected function init( $args ) {

	    //  ----------------------------------------
	    //  Prepare safe arguments
	    //  ----------------------------------------

		$init_args = array(
			'options'	=>	array(
                'namespace'		=>	'shellpress_app'
            ),
            'logger'    =>  array(
                'directory'     =>  __DIR__ . 'log',
                'loglevel'      =>  'DEBUG',
                'args'          =>  array()
            )
		);

		$init_args = array_merge_recursive( $init_args, $args );   // merge init arguments with custom

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
        //  Options handling init
        //  ----------------------------------------

	    $this->options = new src\Options( $this );
	    $this->options->init( $init_args['options'] );

	    //  ----------------------------------------
	    //  Logging handling init
	    //  ----------------------------------------

	    $this->log = new Logger( $init_args['logger']['directory'], LogLevel::DEBUG, $init_args['logger']['args'] );

	    //  ----------------------------------------
	    //  Pages handling init
	    //  ----------------------------------------

        $this->pages = new PagesHandler( $this );

	}

}
