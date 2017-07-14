<?php
namespace shellpress\v1_0_4;

use shellpress\v1_0_4\lib\Psr4Autoloader\Psr4AutoloaderClass;
use shellpress\v1_0_4\src\Logger;


/**
 * Core class of plugin.
 * To use it, simple extend it.
 *
 * Changelog
 * ----------------------------------
 * v1_0_4:
 * + Every class property has been moved to single array
 *
 * v1_0_3:
 * + Requirement checker
 * + Changed properties visibility
 *
 * v1_0_2:
 * + Refactored to static
 *
 * v1_0_1:
 *
 */
class ShellPress {

    /**
     * You need to redefine it in your static class!!
     * @var array
     */
    protected static $sp;   //  <-- Copy this line!!!

    /**
     * Call this method as soon as possible!
     *
     * @param string $mainPluginFile    - absolute path to main plugin file (__FILE__).
     * @param string $pluginPrefix      - will be used to prefix everything in plugin
     * @param string $pluginVersion     - set your plugin version. It will be used in scripts suffixing etc.
     * @param array|null $initArgs      - additional components arguments
     */

	public static function initShellPress( $mainPluginFile, $pluginPrefix, $pluginVersion, $initArgs = array() ) {

	    //  ----------------------------------------
	    //  Prepare safe arguments
	    //  ----------------------------------------

		$defaultInitArgs = array(
		    'app'           =>  array(
		        'mainPluginFile'        =>  $mainPluginFile,
                'pluginPrefix'          =>  $pluginPrefix,
                'pluginVersion'         =>  $pluginVersion
            ),
			'options'	    =>	array(
			    'object'        =>  null,
			    'args'          =>  array(
                    'namespace'		=>	$pluginPrefix
                )
            ),
            'autoloader'    =>  array(
                'object'    =>  null
            ),
            'logger'        =>  array(
                'object'            =>  null,
                'directory'         =>  dirname( $mainPluginFile ) . '/log',
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

		static::$sp = array_merge_recursive( $defaultInitArgs, $initArgs );   // merge default init arguments with specified by developer

        //  -----------------------------------
        //  Initialize components
        //  -----------------------------------

        static::_initAutoloader();
        static::_initLogger();

	}

	//  ================================================================================
	//  GETTERS
	//  ================================================================================

    /**
     * Simple function to get prefix or
     * to prepand given string with prefix.
     *
     * @param string $stringToPrefix
     * @return string
     */
	public static function getPrefix( $stringToPrefix = null ) {

        if( $stringToPrefix === null ){

            return static::$sp['app']['pluginPrefix'];

        } else {

            return static::$sp['app']['pluginPrefix'] . $stringToPrefix;

        }

    }

    /**
     * Prepands given string with plugin directory url.
     * Example usage: static::getUrl( '/assets/style.css' );
     *
     * @param string $relativePath
     *
     * @return string - URL
     */
    public static function getUrl( $relativePath = null ) {

        $delimeter = 'wp-content';
        $pluginDir = dirname( static::getMainPluginFile() );

        $pathParts = explode( $delimeter , $pluginDir, 2 );     //  slice path by delimeter string

        $wpContentDirUrl = content_url();                       //  `wp-content` directory url

        $url = $wpContentDirUrl . $pathParts[1];                //  sum of wp-content url + relative path to plugin dir
        $url = rtrim( $url, DIRECTORY_SEPARATOR );              //  remove trailing slash

        if( $relativePath === null ){

            return $url;

        } else {

            return $url . $relativePath;

        }

    }

    /**
     * Prefixes given string with plugin directory path.
     * Example usage: static::path( '/dir/another/file.php' );
     *
     * @param string $relativePath
     * @return string - absolute path
     */
    public static function getPath( $relativePath = null ) {

        $path = dirname( static::getMainPluginFile() );  // plugin directory path

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
    public static function getMainPluginFile() {

        return static::$sp['app']['mainPluginFile'];

    }

    /**
     * Gets version of instance.
     *
     * @return string
     */
    public static function getPluginVersion() {

        return static::$sp['app']['pluginVersion'];

    }

    //  ================================================================================
    //  INITIALIZATION
    //  ================================================================================

    /**
     * Initialize PSR4 Autoloader.
     * This should be called as an action.
     */
	private static function _initAutoloader() {

        if( ! class_exists( 'shellpress\v1_0_4\lib\Psr4Autoloader\Psr4AutoloaderClass' ) ){

            require( dirname( __FILE__ ) . '/lib/Psr4Autoloader/Psr4AutoloaderClass.php' );

        }

        $autoloaderArgs = & static::$sp['autoloader'];    //  reference

        $autoloaderArgs['object'] = new Psr4AutoloaderClass();
        $autoloaderArgs['object']->register();
        $autoloaderArgs['object']->addNamespace( 'shellpress\v1_0_4', __DIR__ );

    }

    /**
     * Initialize Logging handler.
     * This should be called as an action.
     */
    private static function _initLogger() {

        $loggerArgs = & static::$sp['logger'];  //  reference
        
        static::$sp['object'] = new Logger(
            $loggerArgs['directory'],
            $loggerArgs['logLevel'],
            $loggerArgs['args']
        );

    }

    //  ================================================================================
    //  COMPONONETS
    //  ================================================================================

    /**
     * Gets logger object.
     *
     * @return Logger
     */
    public static function logger() {

        return static::$sp['logger']['object'];

    }

    /**
     * Gets autoloader object.
     *
     * @return Psr4AutoloaderClass
     */
    public static function autoloader() {

        return static::$sp['autoloader']['object'];

    }

}
