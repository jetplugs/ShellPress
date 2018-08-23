<?php
namespace shellpress\v1_2_8\src;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-11-24
 * Time: 22:45
 */

use shellpress\v1_2_8\lib\Psr4Autoloader\Psr4AutoloaderClass;
use shellpress\v1_2_8\ShellPress;
use shellpress\v1_2_8\src\Components\External\AutoloadingHandler;
use shellpress\v1_2_8\src\Components\External\EventHandler;
use shellpress\v1_2_8\src\Components\External\MustacheHandler;
use shellpress\v1_2_8\src\Components\External\UpdateHandler;
use shellpress\v1_2_8\src\Components\Internal\DebugHandler;
use shellpress\v1_2_8\src\Components\Internal\ExtractorHandler;
use shellpress\v1_2_8\src\Components\External\LogHandler;
use shellpress\v1_2_8\src\Components\External\MessagesHandler;
use shellpress\v1_2_8\src\Components\External\OptionsHandler;
use shellpress\v1_2_8\src\Components\External\UtilityHandler;

if( ! class_exists( 'shellpress\v1_2_8\src\Shell', false ) ) {

    class Shell {

    	/** @var bool */
    	protected $isInitialized = false;

    	//  ---

        /** @var string */
        protected $mainPluginFile;

        /** @var string */
        protected $pluginPrefix;

        /** @var string */
        protected $pluginVersion;

        //  ---

        /** @var OptionsHandler */
        public $options;

        /** @var UtilityHandler */
        public $utility;

        /** @var Psr4AutoloaderClass */
        public $autoloading;

        /** @var LogHandler */
        public $log;

        /** @var EventHandler */
        public $event;

        /** @var MessagesHandler */
        public $messages;

        /** @var UpdateHandler */
        public $update;

        /** @var MustacheHandler */
        public $mustache;

        /** @var ExtractorHandler */
        protected $extractor;

        /** @var DebugHandler */
        protected $debug;

        /**
         * Shell constructor.
         *
         * @param string        $mainPluginFile
         * @param string        $pluginPrefix
         * @param string        $pluginVersion
         * @param ShellPress    $shellPress
         */
        public function __construct( $mainPluginFile, $pluginPrefix, $pluginVersion ) {

            $this->mainPluginFile = $mainPluginFile;
            $this->pluginPrefix   = $pluginPrefix;
            $this->pluginVersion  = $pluginVersion;

        }

	    /**
	     * Initializes built in components.
	     * Called on ShellPress::initShellPress();
	     *
	     * @param ShellPress $shellPress
	     *
	     * @return void
	     */
        public function init( &$shellPress ) {

        	if( $this->isInitialized ) return;

	        //  ----------------------------------------
	        //  Before auto loading
	        //  ----------------------------------------

	        if( ! class_exists( 'shellpress\v1_2_8\src\Shared\Components\IComponent', false ) )
		        require( __DIR__ . '/Shared/Components/IComponent.php' );
	        if( ! class_exists( 'shellpress\v1_2_8\src\Components\External\AutoloadingHandler', false ) )
		        require( __DIR__ . '/Components/External/AutoloadingHandler.php' );

	        //  -----------------------------------
	        //  Initialize handlers
	        //  -----------------------------------

	        $this->autoloading  = new AutoloadingHandler( $shellPress );
	        $this->utility      = new UtilityHandler( $shellPress );
	        $this->options      = new OptionsHandler( $shellPress );
	        $this->log          = new LogHandler( $shellPress );
	        $this->messages     = new MessagesHandler( $shellPress );
	        $this->event        = new EventHandler( $shellPress );
	        $this->update       = new UpdateHandler( $shellPress );
	        $this->mustache     = new MustacheHandler( $shellPress );
	        $this->extractor    = new ExtractorHandler( $shellPress );
	        $this->debug        = new DebugHandler( $shellPress );

        }

        //  ================================================================================
        //  GETTERS
        //  ================================================================================

        /**
         * Simple function to get prefix or
         * to prepend given string with prefix.
         *
         * @param string $stringToPrefix
         *
         * @return string
         */
        public function getPrefix( $stringToPrefix = '' ) {

	        return $this->pluginPrefix . $stringToPrefix;

        }

        /**
         * Prepends given string with plugin or theme directory url.
         * Example usage: getUrl( 'assets/style.css' );
         *
         * @param string $relativePath
         *
         * @return string - URL
         */
        public function getUrl( $relativePath = '' ) {

	        //  ----------------------------------------
	        //  Prepare url
	        //  ----------------------------------------

            if( $this->isInsidePlugin() ){
            	$containerUrl = plugin_dir_url( $this->getMainPluginFile() );
            } else {
	        	$containerUrl = get_template_directory_uri();
            }

            $containerUrl = rtrim( $containerUrl, '/' );  //  Always remove trailing slash.

            //  ----------------------------------------
            //  Result
            //  ----------------------------------------

            return $relativePath ? $containerUrl . '/' . ltrim( $relativePath, '/' ) : $containerUrl;

        }

        /**
         * Prefixes given string with directory path.
         * Your path must have slash on start.
         * Example usage: getPath( '/dir/another/file.php' );
         *
         * @param string $relativePath
         *
         * @return string - absolute path
         */
        public function getPath( $relativePath = null ) {

            $path = dirname( $this->getMainPluginFile() );  // plugin directory path

            if ( $relativePath === null ) {

                return $path;

            } else {

                $relativePath = ltrim( $relativePath, '/' );

                return $path . '/' . $relativePath;

            }

        }

        /**
         * Requires file by given relative path.
         * If class name is given as a second parameter, it will check, if class already exists.
         *
         * @param string      $path      - Relative file path
         * @param string|null $className - Class name to check against.
         *
         * @return void
         */
        public function requireFile( $path, $className = null ) {

            if ( $className && class_exists( $className, false ) ) {

                return; //  End method. Do not load file.

            }

            require( $this->getPath( $path ) );

        }

        /**
         * It gets main plugin file path.
         *
         * @return string - full path to main plugin file (__FILE__)
         */
        public function getMainPluginFile() {

            return $this->mainPluginFile;

        }

        /**
         * Returns absolute directory path of currently used ShellPress directory.
         *
         * @return string
         */
        public function getShellPressDir() {

            return dirname( __DIR__ );

        }

        /**
         * Gets version of instance.
         *
         * @return string
         */
        public function getPluginVersion() {

            return $this->pluginVersion;

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
         * If app is created inside plugin, it will return plugin basename ( directory/pluginname ).
         * If app is created inside theme, it will return theme directory name.
         *
         * @since 1.2.1
         *
         * @return string
         */
        public function getPluginBasename() {

        	if( $this->isInsidePlugin() && function_exists( 'plugin_basename' ) ){
        		return plugin_basename( $this->getMainPluginFile() );
	        } else {
        		return basename( dirname( $this->getMainPluginFile() ) );
	        }

        }

        /**
         * Checks if application is used inside a plugin.
         *
         * @return bool
         */
        public function isInsidePlugin() {

        	if( defined( 'WP_PLUGIN_DIR' ) ){

        		if( strpos( __DIR__, WP_PLUGIN_DIR ) !== false ){
        			return true;
		        } else {
        			return false;
		        }

	        } else {

        		return false;

	        }

        }

        /**
         * Checks if application is used inside a theme.
         *
         * @return bool
         */
        public function isInsideTheme() {

            if ( strpos( __DIR__, get_theme_root() ) !== false ) {
                return true;
            } else {
                return false;
            }

        }

    }

}