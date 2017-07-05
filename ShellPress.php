<?php
namespace shellpress\v1_0_2;

use shellpress\v1_0_2\lib\Psr4Autoloader\Psr4AutoloaderClass;
use shellpress\v1_0_2\src\Logger;
use shellpress\v1_0_2\src\Options;


/**
 * Core class of plugin.
 * To use it, simple extend it.
 *
 * Changelog
 * ----------------------------------
 * v1_0_2:
 * + Refactored to static
 *
 * v1_0_1:
 *
 */
class ShellPress {

    /**
     * @var Psr4AutoloaderClass
     */
	static public $autoloader;

    /**
     * @var Logger
     */
	static public $log;

    /**
     * @var string
     */
    static protected $mainPluginFile;

    /**
     * @var string
     */
    static private $pluginPrefix;

    /**
     * @var array
     */
    static private $initArgs;


    /**
     * You should call this method just after
     * object creation. __construct method is a
     * good place to do that.
     *
     * @param string $mainPluginFile - absolute path to main plugin file (__FILE__).
     * @param string $pluginPrefix - will be used to prefix everything in plugin
     * @param array|null $initArgs - additional components arguments
     */

	public function initShellPress( $mainPluginFile, $pluginPrefix, $initArgs = array() ) {

	    self::$mainPluginFile = $mainPluginFile;
	    self::$pluginPrefix = $pluginPrefix;

	    //  ----------------------------------------
	    //  Prepare safe arguments
	    //  ----------------------------------------

		$defaultInitArgs = array(
			'options'	=>	array(
			    'args'          => array(
                    'namespace'		=>	self::getPrefix()
                )
            ),
            'logger'    =>  array(
                'directory'         =>  self::getPath( '/log' ),
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

		self::$initArgs = array_merge_recursive( $defaultInitArgs, $initArgs );   // merge default init arguments with specified by developer

        //  -----------------------------------
        //  Initialize helpers
        //  -----------------------------------

        self::_initAutoloader();
        self::_initLogger();

	}

	//  ================================================================================
	//  GETTERS
	//  ================================================================================

    /**
     * Simple function to get prefix or
     * prefixing given string.
     *
     * @param string $stringToPrefix
     * @return string
     */
	static public function getPrefix( $stringToPrefix = null ) {

        if( $stringToPrefix === null ){

            return self::$pluginPrefix;

        } else {

            return self::$pluginPrefix . $stringToPrefix;

        }

    }

    /**
     * Prefixes given string with plugin directory url.
     * Example usage: self::url( '/assets/style.css' );
     *
     * @param string $relativePath
     *
     * @return string - URL
     */
    static public function getUrl($relativePath = null ) {

        $url = plugin_dir_url( self::getMainPluginFile() );     //  plugin directory url with trailing slash
        $url = rtrim( $url, DIRECTORY_SEPARATOR );  //  remove trailing slash

        if( $relativePath === null ){

            return $url;

        } else {

            return $url . $relativePath;

        }

    }

    /**
     * Prefixes given string with current template directory url.
     * Example usage: self::url( '/assets/style.css' );
     *
     * @param null $relative_path
     *
     * @return string
     */
    static public function themeUrl( $relative_path = null ) {

        $url = get_stylesheet_directory_uri();      //  current template directory without trailing slash

        if( $relative_path ){

            return $url . $relative_path;

        } else {

            return $url;

        }

    }

    /**
     * Prefixes given string with plugin directory path.
     * Example usage: self::path( '/dir/another/file.php' );
     *
     * @param string $relativePath
     * @return string - absolute path
     */
    public function getPath( $relativePath = null ) {

        $path = dirname( self::getMainPluginFile() );  // plugin directory path

        if( $relativePath === null ){

            return $path;

        } else {

            return $path . $relativePath;

        }

    }

    /**
     * It gets main plugin file path.
     * @see initShellPress()
     *
     * @return string - full path to main plugin file (__FILE__)
     */
    public function getMainPluginFile() {

        return self::$mainPluginFile;

    }

    //  ================================================================================
    //  INITIALIZATION
    //  ================================================================================

    /**
     * Initialize PSR4 Autoloader.
     * This should be called as an action.
     */
	public function _initAutoloader() {

        if( ! class_exists( 'shellpress\v1_0_0\lib\Psr4Autoloader\Psr4AutoloaderClass' ) ){

            require( dirname( __FILE__ ) . '/lib/Psr4Autoloader/Psr4AutoloaderClass.php' );

        }

        self::$autoloader = new Psr4AutoloaderClass();
        self::$autoloader->register();
        self::$autoloader->addNamespace( 'shellpress\v1_0_2', __DIR__ );

    }

    /**
     * Initialize Logging handler.
     * This should be called as an action.
     */
    public function _initLogger() {

        $loggerArgs = self::$initArgs['logger'];
        
        self::$log = new Logger(
            $loggerArgs['directory'],
            $loggerArgs['logLevel'],
            $loggerArgs['args']
        );

    }

}
