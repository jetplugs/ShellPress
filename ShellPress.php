<?php
namespace shellpress\v1_0_7;

use shellpress\v1_0_7\lib\Psr4Autoloader\Psr4AutoloaderClass;
use shellpress\v1_0_7\src\Factory\Factory;
use shellpress\v1_0_7\src\Helpers;
use shellpress\v1_0_7\src\Logger;
use shellpress\v1_0_7\src\Options;


/**
 * Core class of plugin. To use it, simple extend it.
 * **Please remember to define `protected static $sp;` property in new class.**
 */
abstract class ShellPress {

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
	    //  Prepare arguments
	    //  ----------------------------------------

		$defaultInitArgs = array(
		    'app'           =>  array(
		        'mainPluginFile'        =>  $mainPluginFile,
                'pluginPrefix'          =>  $pluginPrefix,
                'pluginVersion'         =>  $pluginVersion
            ),
			'options'	    =>	array(
			    'object'        =>  null,
			    'optionsKey'    =>  $pluginPrefix
            ),
            'factory'       =>  array(
                'object'        =>  null
            ),
            'autoloader'    =>  array(
                'object'    =>  null
            ),
            'logger'        =>  array(
                'object'            =>  null,
                'directory'         =>  dirname( $mainPluginFile ) . '/log',
                'logLevel'          =>  'debug',
                'dateFormat'        =>  'Y-m-d G:i:s.u',
                'filename'          =>  'log_' . date( 'd-m-Y' ) . '.log',
                'flushFrequency'    =>  false,
                'logFormat'         =>  false,
                'appendContext'     =>  true
            ),
            'helpers'       =>  array(
                'object'            =>  null
            )
		);

		static::$sp = array_replace_recursive( $defaultInitArgs, $initArgs );   // replace default init arguments with specified by developer

        //  -----------------------------------
        //  Initialize components
        //  -----------------------------------

        static::_initAutoloader();
        static::_initOptions();
        static::_initLogger();
        static::_initHelpers();

        //  ----------------------------------------
        //  Calling hooks
        //  ----------------------------------------

        register_activation_hook( static::getMainPluginFile(),      array( get_called_class(), 'onActivation' ) );
        register_deactivation_hook( static::getMainPluginFile(),    array( get_called_class(), 'onDeactivation' ) );

        add_action( 'init',                                         array( get_called_class(), 'onInit' ) );

        //  ----------------------------------------
        //  Everything is ready. Call onSetUp()
        //  ----------------------------------------

        static::onSetUp();

	}

	//  ================================================================================
	//  METHOD STUBS
	//  ================================================================================

    /**
     * Called automaticly after core is ready.
     *
     * @return void
     */
    public static function onSetUp() {}

    /**
     * Called automaticly on init hook.
     *
     * @return void
     */
	public static function onInit() {}

    /**
     * Called automaticly on plugin activation.
     *
     * @return void
     */
	public static function onActivation() {}

    /**
     * Called automaticly on plugin deactivation.
     *
     * @return void
     */
	public static function onDeactivation() {}

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
     * Prefixes given string with directory path.
     * Your path must have slash on start.
     * Example usage: getPath( '/dir/another/file.php' );
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
     *
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
     */
	private static function _initAutoloader() {

        if( ! class_exists( 'shellpress\v1_0_7\lib\Psr4Autoloader\Psr4AutoloaderClass' ) ){

            require( dirname( __FILE__ ) . '/lib/Psr4Autoloader/Psr4AutoloaderClass.php' );

        }

        $autoloaderArgs = & static::$sp['autoloader'];    //  reference

        $autoloaderArgs['object'] = new Psr4AutoloaderClass();
        $autoloaderArgs['object']->register();
        $autoloaderArgs['object']->addNamespace( 'shellpress\v1_0_7', __DIR__ );

    }

    /**
     * Initialize Logging handler.
     */
    private static function _initLogger() {

        $loggerArgs = & static::$sp['logger'];      //  reference
        
        $loggerArgs['object'] = new Logger(
            $loggerArgs['directory'],
            $loggerArgs['logLevel'],
            array(
                $loggerArgs['dateFormat'],
                $loggerArgs['filename'],
                $loggerArgs['flushFrequency'],
                $loggerArgs['logFormat'],
                $loggerArgs['appendContext']
            )
        );

    }

    /**
     * Initialize options handler.
     */
    private static function _initOptions() {

        $optionsArgs = & static::$sp['options'];    //  reference

        $optionsArgs['object'] = new Options( $optionsArgs['optionsKey'] );

    }


    /**
     * Initialize Factory.
     */
    private static function _initFactory() {

        $factoryArgs = & static::$sp['factory'];    //  reference

        $factoryArgs['object'] = new Factory();

    }

    /**
     * Initialize Helpers.
     */
    private static function _initHelpers() {

        $helpersArgs = & static::$sp['helpers'];    //  reference

        $helpersArgs['object'] = new Helpers();

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

    /**
     * Gets factory object.
     *
     * @return Factory
     */
    public static function factory() {

        return static::$sp['factory']['object'];

    }

    /**
     * Gets options object.
     *
     * @return Options
     */
    public static function options() {

        return static::$sp['options']['object'];

    }

    /**
     * Gets helpers object.
     *
     * @return Helpers
     */
    public static function helpers() {

        return static::$sp['helpers']['object'];

    }

}
