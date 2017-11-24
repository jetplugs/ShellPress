<?php
namespace shellpress\v1_1_2\src;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-11-24
 * Time: 22:45
 */

use shellpress\v1_1_2\lib\Psr4Autoloader\Psr4AutoloaderClass;
use shellpress\v1_1_2\src\Handlers\LogHandler;
use shellpress\v1_1_2\src\Handlers\OptionsHandler;
use shellpress\v1_1_2\src\Handlers\UtilityHandler;

if( class_exists( 'shellpress\v1_1_2\src\Shell' ) ) return;

class Shell {

    /** @var array */
    protected $initArgs;

    /** @var OptionsHandler */
    public $options;

    /** @var UtilityHandler */
    public $utility;

    /** @var Psr4AutoloaderClass */
    public $autoloading;

    /** @var LogHandler */
    public $log;

    public function __construct( $initArgs ) {

        $this->initArgs = $initArgs;

        //  -----------------------------------
        //  Initialize components
        //  -----------------------------------

        $this->initAutoloadingHandler();
        $this->initOptionsHandler();
        $this->initLogHandler();
        $this->initUtilityHandler();

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
    public function getPrefix( $stringToPrefix = null ) {

        if( $stringToPrefix === null ){

            return $this->initArgs['app']['pluginPrefix'];

        } else {

            return $this->initArgs['app']['pluginPrefix'] . $stringToPrefix;

        }

    }

    /**
     * Prepands given string with plugin directory url.
     * Example usage: getUrl( '/assets/style.css' );
     *
     * @param string $relativePath
     *
     * @return string - URL
     */
    public function getUrl( $relativePath = null ) {

        $delimeter = 'wp-content';
        $pluginDir = dirname( $this->getMainPluginFile() );

        $pathParts = explode( $delimeter , $pluginDir, 2 );     //  slice path by delimeter string

        $wpContentDirUrl = content_url();                       //  `wp-content` directory url

        $url = $wpContentDirUrl . $pathParts[1];                //  sum of wp-content url + relative path to plugin dir
        $url = rtrim( $url, '/' );                              //  remove trailing slash

        if( $relativePath === null ){

            return $url;

        } else {

            $relativePath = ltrim( $relativePath, '/' );

            return $url . '/' . $relativePath;

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
    public function getPath( $relativePath = null ) {

        $path = dirname( $this->getMainPluginFile() );  // plugin directory path

        if( $relativePath === null ){

            return $path;

        } else {

            $relativePath = ltrim( $relativePath, '/' );

            return $path . '/' . $relativePath;

        }

    }

    /**
     * It gets main plugin file path.
     *
     * @return string - full path to main plugin file (__FILE__)
     */
    public function getMainPluginFile() {

        return $this->initArgs['app']['mainPluginFile'];

    }

    /**
     * Gets version of instance.
     *
     * @return string
     */
    public function getPluginVersion() {

        return $this->initArgs['app']['pluginVersion'];

    }

    /**
     * Gets full version of instance.
     * It's like this: `prefix`_`version`.
     *
     * @return string
     */
    public function getFullPluginVersion() {

        return $this->getPrefix() . '_' . $this->getPluginVersion();

    }

    /**
     * Checks if application is used inside a plugin.
     * It returns false, if directory is not equal ../wp-content/plugins
     *
     * @return bool
     */
    public function isInsidePlugin() {

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
    private function initAutoloadingHandler() {

        if( ! class_exists( 'shellpress\v1_1_2\lib\Psr4Autoloader\Psr4AutoloaderClass' ) ){
            require( dirname( __DIR__ ) . '/lib/Psr4Autoloader/Psr4AutoloaderClass.php' );
        }

        $this->autoloading = new Psr4AutoloaderClass();
        $this->autoloading->register();
        $this->autoloading->addNamespace( 'shellpress\v1_1_2', dirname( __DIR__ ) );

    }

    /**
     * Initialize Logging handler.
     */
    private function initLogHandler() {

        $logHandlerArgs = $this->initArgs['log'];

        $this->log = new LogHandler(
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
    private function initOptionsHandler() {

        $this->options = new OptionsHandler( $this );
        $this->options->setOptionsKey( $this->initArgs['options']['options'] );
        $this->options->setDefaultOptions( $this->initArgs['options']['default'] );
        $this->options->load();

    }

    /**
     * Initialize UtilityHandler.
     */
    private function initUtilityHandler() {

        $this->utility = new UtilityHandler( $this );

    }

}