<?php
namespace shellpress\v1_1_2;

use shellpress\v1_1_2\src\Shell;

if( class_exists( 'shellpress\v1_1_2\ShellPress' ) ) return;
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

            static::$_instances[ $calledClass ] = new static();

        }

        return static::$_instances[ $calledClass ];

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
     * Call this method as soon as possible!
     *
     * @param string $mainPluginFile    - absolute path to main plugin file (__FILE__).
     * @param string $pluginPrefix      - will be used to prefix everything in plugin
     * @param string $pluginVersion     - set your plugin version. It will be used in scripts suffixing etc.
     * @param array $initArgs           - additional components arguments
     */
	public static function initShellPress( $mainPluginFile, $pluginPrefix, $pluginVersion, $initArgs = array() ) {

	    //  ----------------------------------------
	    //  Prepare arguments
	    //  ----------------------------------------

		$defaultInitArgs = array(
		    'app'                   =>  array(
		        'mainPluginFile'        =>  $mainPluginFile,
                'pluginPrefix'          =>  $pluginPrefix,
                'pluginVersion'         =>  $pluginVersion
            ),
			'options'	            =>	array(
			    'key'                   =>  $pluginPrefix,
                'default'               =>  array()
            ),
            'log'                       =>  array(
                'directory'             =>  dirname( $mainPluginFile ) . '/log',
                'logLevel'              =>  'debug',
                'dateFormat'            =>  'Y-m-d G:i:s.u',
                'filename'              =>  'log_' . date( 'd-m-Y' ) . '.log',
                'flushFrequency'        =>  false,
                'logFormat'             =>  false,
                'appendContext'         =>  true
            )
		);

        $initArgs = array_replace_recursive( $defaultInitArgs, $initArgs );   // replace default init arguments with specified by developer

        require_once( __DIR__ . '/src/Shell.php' );

        $instance           = static::getInstance();
		$instance->_shell   = new Shell( $initArgs );

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

}
