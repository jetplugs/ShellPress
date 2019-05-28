<?php
namespace shellpress\v1_3_72\src\Components\External;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-11-26
 * Time: 02:29
 */

use shellpress\v1_3_72\src\Shared\Components\IComponent;

class EventHandler extends IComponent {

	/** @var array */
	private $callablesOnUpdate = array();

	/**
	 * Called on handler construction.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		if( ! is_admin() ) return;  //  Bail early because updates run only in admin area.

		//  ----------------------------------------
		//  Run App update callbacks.
		//  ----------------------------------------

		add_action( 'shutdown', array( $this, '_a_updatingCallback' ) );

	}

	/**
	 * This method should not be really trusted.
	 * If ShellPress is inside plugin, it will call plugin activation hook.
	 * Currently not working with themes.
	 *
	 * @param $callable
	 */
    public function addOnActivate( $callable ) {

	    register_activation_hook( $this->s()->getMainPluginFile(), $callable );

    }

	/**
	 * This method should not be really trusted.
	 * If ShellPress is inside plugin, it will call plugin deactivation hook.
	 * Currently not working with themes.
	 *
	 * @param $callable
	 */
    public function addOnDeactivate( $callable ) {

	    register_deactivation_hook( $this->s()->getMainPluginFile(), $callable );

    }

	/**
	 * This method is preferred over addOnActivate() method.
	 * It compares versions of config and call given method when current version is greater than saved one.
	 * It should be called on admin_init to save performance.
	 *
	 * @param $callable
	 */
    public function addOnUpdate( $callable ) {

    	$this->callablesOnUpdate[] = $callable;

    }

    //  ================================================================================
    //  Actions
    //  ================================================================================

	/**
	 * Called on shutdown.
	 */
    public function _a_updatingCallback() {

	    $currentVersion = $this::s()->getPluginVersion();
	    $savedVersion   = $this::s()->options->get( '_shell/pluginVersion', '0' );

	    if( $currentVersion != $savedVersion ){

		    if( ! empty( $this->callablesOnUpdate ) && version_compare( $currentVersion, $savedVersion, '>' ) ) {

			    foreach( $this->callablesOnUpdate as $callable ){
				    call_user_func( $callable );
			    }

		    }

		    $this::s()->options->set( '_shell/pluginVersion', $currentVersion );
		    $this::s()->options->flush();

	    }

    }

}