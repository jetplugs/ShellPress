<?php
namespace shellpress\v1_1_2;

use shellpress\v1_1_2\lib\Psr4Autoloader\Psr4AutoloaderClass;
use shellpress\v1_1_2\src\Handlers\UtilityHandler;
use shellpress\v1_1_2\src\Handlers\LogHandler;
use shellpress\v1_1_2\src\Handlers\OptionsHandler;

/**
 * Core class of plugin. To use it, simple extend it.
 */
abstract class ShellPress {

    /** @var static */
    protected static $_instances = array();

    /** @var array */
    private $_initArgs = array();

    /** @var OptionsHandler */
    private $_optionsHandler;

    /** @var UtilityHandler */
    private $_utilityHandler;

    /** @var Psr4AutoloaderClass */
    private $_autoloadingHandler;

    /** @var LogHandler */
    private $_logHandler;

    /**
     * Private forbidden constructor.
     */
    private final function __construct() {

    }

    /**
     * Gets singleton instance.
     *
     * @return static
     */
    public final static function getInstance() {

        $calledClass = get_called_class();

        if( ! isset( static::$_instances[ $calledClass ] ) ){

            static::$_instances[ $calledClass ] = new static();

        }

        return static::$_instances[ $calledClass ];

    }

    /**
     * Alias for getInstance().
     *
     * @return static
     */
    public final static function i() {

        return static::getInstance();

    }

    /**
     * Call this method as soon as possible!
     *
     * @param string $mainPluginFile    - absolute path to main plugin file (__FILE__).
     * @param string $pluginPrefix      - will be used to prefix everything in plugin
     * @param string $pluginVersion     - set your plugin version. It will be used in scripts suffixing etc.
     * @param array $initArgs           - additional components arguments
     */
	public static function initShellPress( $mainPluginFile, $pluginPrefix, $pluginVersion, $initArgs = array() ) {

	    $instance = static::getInstance();

	    //  ----------------------------------------
	    //  Prepare arguments
	    //  ----------------------------------------

		$defaultInitArgs = array(
		    'app'                   =>  array(
		        'mainPluginFile'        =>  $mainPluginFile,
                'pluginPrefix'          =>  $pluginPrefix,
                'pluginVersion'         =>  $pluginVersion
            ),
			'optionsHandler'	    =>	array(
			    'optionsKey'            =>  $pluginPrefix,
                'defaultOptions'        =>  array()
            ),
            'logHandler'            =>  array(
                'object'                =>  null,
                'directory'             =>  dirname( $mainPluginFile ) . '/log',
                'logLevel'              =>  'debug',
                'dateFormat'            =>  'Y-m-d G:i:s.u',
                'filename'              =>  'log_' . date( 'd-m-Y' ) . '.log',
                'flushFrequency'        =>  false,
                'logFormat'             =>  false,
                'appendContext'         =>  true
            )
		);

		$instance->_initArgs = array_replace_recursive( $defaultInitArgs, $initArgs );   // replace default init arguments with specified by developer

        //  -----------------------------------
        //  Initialize components
        //  -----------------------------------

        $instance->_initAutoloadingHandler();
        $instance->_initOptionsHandler();
        $instance->_initLogHandler();
        $instance->_initHelpers();

        //  ----------------------------------------
        //  Everything is ready. Call onSetUp()
        //  ----------------------------------------

        $instance->onSetUp();

	}

	//  ================================================================================
	//  METHOD STUBS
	//  ================================================================================

    /**
     * Called automaticly after core is ready.
     *
     * @return void
     */
    protected abstract function onSetUp();

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

            return static::getInstance()->_initArgs['app']['pluginPrefix'];

        } else {

            return static::getInstance()->_initArgs['app']['pluginPrefix'] . $stringToPrefix;

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

        return static::getInstance()->_initArgs['app']['mainPluginFile'];

    }

    /**
     * Gets version of instance.
     *
     * @return string
     */
    public static function getPluginVersion() {

        return static::getInstance()->_initArgs['app']['pluginVersion'];

    }

    /**
     * Gets full version of instance.
     * It's like this: `prefix`_`version`.
     *
     * @return string
     */
    public function getFullPluginVersion() {

        return static::getPrefix() . '_' . static::getPluginVersion();

    }

    /**
     * Checks if application is used inside a plugin.
     * It returns false, if directory is not equal ../wp-content/plugins
     *
     * @return bool
     */
    public static function isInsidePlugin() {

        if( strpos( __DIR__, 'wp-content/plugins' ) !== false ){

            return true;

        } else {

            return false;

        }

    }

    /**
     * Checks if application is used inside a theme.
     * It returns false, if directory is not equal ../wp-content/themes
     *
     * @return bool
     */
    public static function isInsideTheme() {

        if( strpos( __DIR__, 'wp-content/themes' ) !== false ){

            return true;

        } else {

            return false;

        }

    }

    //  ================================================================================
    //  INITIALIZATION
    //  ================================================================================

    /**
     * Initialize PSR4 Autoloader.
     */
	private function _initAutoloadingHandler() {

        if( ! class_exists( 'shellpress\v1_1_2\lib\Psr4Autoloader\Psr4AutoloaderClass' ) ){

            require( dirname( __FILE__ ) . '/lib/Psr4Autoloader/Psr4AutoloaderClass.php' );

        }

        $this->_autoloadingHandler = new Psr4AutoloaderClass();
        $this->_autoloadingHandler->register();
        $this->_autoloadingHandler->addNamespace( 'shellpress\v1_1_2', __DIR__ );

    }

    /**
     * Initialize Logging handler.
     */
    private function _initLogHandler() {

        $logHandlerArgs = $this->_initArgs['logHandler'];
        
        $this->_logHandler = new LogHandler(
            $logHandlerArgs['directory'],
            $logHandlerArgs['logLevel'],
            array(
                $logHandlerArgs['dateFormat'],
                $logHandlerArgs['filename'],
                $logHandlerArgs['flushFrequency'],
                $logHandlerArgs['logFormat'],
                $logHandlerArgs['appendContext']
            )
        );

    }

    /**
     * Initialize options handler.
     */
    private function _initOptionsHandler() {

        $this->_optionsHandler = new OptionsHandler( static::getInstance() );
        $this->_optionsHandler->setOptionsKey( $this->_initArgs['optionsHandler']['optionsKey'] );
        $this->_optionsHandler->setDefaultOptions( $this->_initArgs['optionsHandler']['defaultOptions'] );
        $this->_optionsHandler->load();

    }

    /**
     * Initialize HelpersHandler.
     */
    private function _initHelpers() {

        $this->_utilityHandler = new UtilityHandler( static::getInstance() );

    }

    //  ================================================================================
    //  COMPONONETS
    //  ================================================================================

    /**
     * Gets LogHandler object.
     *
     * @return LogHandler
     */
    public static function log() {

        return static::getInstance()->_logHandler;

    }

    /**
     * Gets AutoloadingHandler object.
     *
     * @return Psr4AutoloaderClass
     */
    public static function autoloading() {

        return static::getInstance()->_autoloadingHandler;

    }

    /**
     * Gets OptionsHandler object.
     *
     * @return OptionsHandler
     */
    public static function options() {

        return static::getInstance()->_optionsHandler;

    }

    /**
     * Gets UtilityHandler object.
     *
     * @return UtilityHandler
     */
    public static function utility() {

        return static::getInstance()->_utilityHandler;

    }

}
