<?php
namespace shellpress\v1_0_0;

use shellpress\v1_0_0\lib\KLogger\Logger;
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
	public $options;

    /**
     * @var
     */
    public $widgets;

    /**
     * @var PagesHandler
     */
    public $pages;

    /**
     * @var Psr4AutoloaderClass
     */
	public $autoloader;

    /**
     * @var Logger
     */
	protected $log;

    /**
     * @var string
     */
    private $mainPluginFile;

    /**
     * @var string
     */
    private $nameSpace;

    /**
     * @var array
     */
    private $initArgs;


    /**
     * You should call this method just after
     * object creation. __construct method is a
     * good place to do that.
     *
     * @param string $mainPluginFile - absolute path to main plugin file (__FILE__).
     * @param string $nameSpace - simple namespace which will be used to prefix everything in plugin
     * @param array $args - additional components arguments
     */

	protected function init( $mainPluginFile, $nameSpace, $initArgs ) {

	    $this->mainPluginFile = $mainPluginFile;
	    $this->nameSpace = $nameSpace;

	    //  ----------------------------------------
	    //  Prepare safe arguments
	    //  ----------------------------------------

		$defaultInitArgs = array(
			'options'	=>	array(
			    'args'          => array(
                    'namespace'		=>	$this->nameSpace
                )
            ),
            'pages'	=>	array(
                'args'          => array(

                )
            ),
            'logger'    =>  array(
                'directory'         =>  $this->path( '/log' ),
                'logLevel'          =>  'debug',
                'args'              =>  array(
                    'dateFormat'        =>  'Y-m-d G:i:s.u',
                    'filename'          =>  'log_' . date( 'd-m-Y' ) . '.log',
                    'flushFrequency'    =>  false,
                    'logFormat'         =>  false,
                    'appendContext'     =>  true
                )
            )
		);

		$this->initArgs = array_merge_recursive( $defaultInitArgs, $initArgs );   // merge default init arguments with specified by developer

        \add_action( 'plugins_loaded',      array( $this, 'sp_initAutoloader' ) );
        \add_action( 'plugins_loaded',      array( $this, 'sp_initOptions' ) );
        \add_action( 'plugins_loaded',      array( $this, 'sp_initLogger' ) );
        \add_action( 'admin_init',          array( $this, 'sp_initPageHandler' ) );

	}

    /**
     * Simple function to get prefix or
     * prefixing given string.
     *
     * @param string $string
     * @return string
     */
	public function prefix( $string = null ) {

        if( $string === null ){

            return $this->nameSpace;

        } else {

            return $this->nameSpace . $string;

        }

    }

    /**
     * Prefixes given string with plugin directory url.
     * Example usage: $this->url( '/assets/style.css' );
     *
     * @param string $relative_path
     * @return string - URL
     */
    public function url( $relative_path = null ) {

        $url = \plugin_dir_url( $this->mainPluginFile );    //  plugin directory url with trailing slash
        $url = rtrim( $url, DIRECTORY_SEPARATOR );  //  remove trailing slash

        if( $relative_path === null ){

            return $url;

        } else {

            return $url . $relative_path;

        }

    }

    /**
     * Prefixes given string with plugin directory path.
     * Example usage: $this->path( '/dir/another/file.php' );
     *
     * @param string $relative_path
     * @return string - absolute path
     */
    public function path( $relative_path = null ) {

        $path = \dirname( $this->mainPluginFile );  // plugin directory path

        if( $relative_path === null ){

            return $path;

        } else {

            return $path . $relative_path;

        }

    }

    /**
     * Initialize PSR4 Autoloader.
     * This should be called as an action.
     */
	public function sp_initAutoloader() {

        if( ! class_exists( 'shellpress\v1_0_0\lib\Psr4Autoloader\Psr4AutoloaderClass' ) ){

            require( dirname( __FILE__ ) . '/lib/Psr4Autoloader/Psr4AutoloaderClass.php' );

        }

        $this->autoloader = new Psr4AutoloaderClass();
        $this->autoloader->register();
        $this->autoloader->addNamespace( 'shellpress\v1_0_0', __DIR__ );

    }

    /**
     * Initialize Options handler.
     * This should be called as an action.
     */
    public function sp_initOptions() {

        $this->options = new Options( $this );
        $this->options->init( $this->initArgs['options']['args'] );

    }

    /**
     * Initialize Logging handler.
     * This should be called as an action.
     */
    public function sp_initLogger() {

        $this->log = new Logger(
            $this->initArgs['logger']['directory'],
            $this->initArgs['logger']['logLevel'],
            $this->initArgs['logger']['args']
        );

    }

    /**
     * Initialize Admin pages handler.
     * This should be called as an action.
     */
    public function sp_initPageHandler() {

        $this->pages = new PagesHandler( $this );
        $this->pages->init( $this->initArgs['pages']['args'] );

    }

}
