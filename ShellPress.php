<?php
namespace shellpress\v1_2_0;

use shellpress\v1_2_0\src\Shell;

if( class_exists( 'shellpress\v1_2_0\ShellPress' ) ) return;
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
     * @return static
     */
    public final static function getInstance() {

        $calledClass = get_called_class();

        if( ! isset( static::$_instances[ $calledClass ] ) ){

            wp_die( sprintf( 'You need to call %1$s::initShellPress().', $calledClass ) );

        }

        return static::$_instances[ $calledClass ];

    }

    /**
     * Alias for getInstance();
     *
     * @return static
     */
    public final static function i() {

        return static::getInstance();

    }

    /**
     * Gets Shell object.
     *
     * @return Shell
     */
    public final static function shell() {

        return static::getInstance()->_shell;

    }

	/**
	 * Alias for shell();
	 *
	 * @return shell
	 */
	public final static function s() {

		return static::shell();

	}

    /**
     * Call this method as soon as possible!
     *
     * @param string $mainPluginFile    - absolute path to main plugin file (__FILE__).
     * @param string $pluginPrefix      - will be used to prefix everything in plugin
     * @param string $pluginVersion     - set your plugin version. It will be used in scripts suffixing etc.
     */
	public static function initShellPress( $mainPluginFile, $pluginPrefix, $pluginVersion ) {

        require_once( __DIR__ . '/src/Shell.php' );

        $instance           = new static();
		$instance->_shell   = new Shell( $mainPluginFile, $pluginPrefix, $pluginVersion );

		static::$_instances[ get_called_class() ] = $instance;

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
