<?php
namespace shellpress\v1_3_8;

use shellpress\v1_3_8\src\Shell;

if( ! class_exists( 'shellpress\v1_3_8\ShellPress', false ) ){

    /**
     * Core class of plugin. To use it, simple extend it.
     */
    abstract class ShellPress {

        /** @var static */
        protected static $_instances = array();

        /** @var Shell */
        private $_shell;

        /**
         * Private forbidden constructor.
         */
        private final function __construct() {

        }

        /**
         * Gets singleton instance.
         *
         * @deprecated
         *
         * @return static
         */
        public final static function getInstance() {

            return static::i();

        }

        /**
         * Alias for getInstance();
         *
         * @return static
         */
        public final static function i() {

            $calledClass = get_called_class();

            if( ! isset( static::$_instances[ $calledClass ] ) ){

                wp_die( sprintf( 'You need to call %1$s::initShellPress().', $calledClass ) );

            }

            return static::$_instances[ $calledClass ];

        }

        /**
         * Gets Shell object.
         *
         * @deprecated
         *
         * @return Shell
         */
        public final static function shell() {

            return static::s();

        }

        /**
         * Alias for shell();
         *
         * @return shell
         */
        public final static function s() {

            return static::i()->_shell;

        }

        /**
         * Call this method as soon as possible!
         *
         * @param string $mainPluginFile    - absolute path to main plugin file (__FILE__).
         * @param string $pluginPrefix      - will be used to prefix everything in plugin
         * @param string $pluginVersion     - set your plugin version. It will be used in scripts suffixing etc.
         */
        public static function initShellPress( $mainPluginFile, $pluginPrefix, $pluginVersion ) {

	        static::$_instances[ get_called_class() ] = $instance = new static();

            $instance->_shell = new Shell( $mainPluginFile, $pluginPrefix, $pluginVersion );
            $instance->_shell->init( $instance );

            //  ----------------------------------------
            //  Everything is ready. Call onSetUp()
            //  ----------------------------------------

            static::i()->onSetUp();

        }

        //  ================================================================================
        //  METHOD STUBS
        //  ================================================================================

        /**
         * Called automatically after core is ready.
         *
         * @return void
         */
        protected abstract function onSetUp();

    }

}
