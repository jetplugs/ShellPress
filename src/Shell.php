<?php
namespace shellpress\v1_2_0\src;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-11-24
 * Time: 22:45
 */

use shellpress\v1_2_0\lib\Psr4Autoloader\Psr4AutoloaderClass;
use shellpress\v1_2_0\src\Handlers\External\AutoloadingHandler;
use shellpress\v1_2_0\src\Handlers\External\CustomizerHandler;
use shellpress\v1_2_0\src\Handlers\External\EventHandler;
use shellpress\v1_2_0\src\Handlers\Internal\ExtractorHandler;
use shellpress\v1_2_0\src\Handlers\External\LogHandler;
use shellpress\v1_2_0\src\Handlers\External\MessagesHandler;
use shellpress\v1_2_0\src\Handlers\External\OptionsHandler;
use shellpress\v1_2_0\src\Handlers\External\UtilityHandler;

if( ! class_exists( 'shellpress\v1_2_0\src\Shell', false ) ) {

    class Shell {

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

        /** @var ExtractorHandler */
        protected $extractor;

        /** @var CustomizerHandler */
        public $customizer;

        /**
         * Shell constructor.
         *
         * @param string $mainPluginFile
         * @param string $pluginPrefix
         * @param string $pluginVersion
         */
        public function __construct( $mainPluginFile, $pluginPrefix, $pluginVersion ) {

            $this->mainPluginFile = $mainPluginFile;
            $this->pluginPrefix   = $pluginPrefix;
            $this->pluginVersion  = $pluginVersion;

            //  ----------------------------------------
            //  Before auto loading
            //  ----------------------------------------

            if( ! class_exists( 'shellpress\v1_2_0\src\Handlers\IHandler', false ) )
            	require( __DIR__ . '/Handlers/IHandler.php' );
            if( ! class_exists( 'shellpress\v1_2_0\src\Handlers\External\AutoloadingHandler', false ) )
            	require( __DIR__ . '/Handlers/External/AutoloadingHandler.php' );

            //  -----------------------------------
            //  Initialize handlers
            //  -----------------------------------

            $this->autoloading  = new AutoloadingHandler( $this );
            $this->utility      = new UtilityHandler( $this );
            $this->options      = new OptionsHandler( $this );
            $this->log          = new LogHandler( $this );
            $this->messages     = new MessagesHandler( $this );
            $this->event        = new EventHandler( $this );
            $this->extractor    = new ExtractorHandler( $this );
            $this->customizer   = new CustomizerHandler( $this);

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
        public function getPrefix( $stringToPrefix = null ) {

            if ( $stringToPrefix === null ) {

                return $this->pluginPrefix;

            } else {

                return $this->pluginPrefix . $stringToPrefix;

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

            $pathParts = explode( $delimeter, $pluginDir, 2 );     //  slice path by delimeter string

            $wpContentDirUrl = content_url();                       //  `wp-content` directory url

            $url = $wpContentDirUrl . $pathParts[ 1 ];                //  sum of wp-content url + relative path to plugin dir
            $url = rtrim( $url, '/' );                              //  remove trailing slash

            if ( $relativePath === null ) {

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
         * Checks if application is used inside a plugin.
         * It returns false, if directory is not equal ../wp-content/plugins
         *
         * @return bool
         */
        public function isInsidePlugin() {

            if ( strpos( __DIR__, 'wp-content/plugins' ) !== false ) {
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
        public function isInsideTheme() {

            if ( strpos( __DIR__, 'wp-content/themes' ) !== false ) {
                return true;
            } else {
                return false;
            }

        }

    }

}