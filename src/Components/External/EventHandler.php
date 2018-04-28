<?php
namespace shellpress\v1_2_1\src\Components\External;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-11-26
 * Time: 02:29
 */

use shellpress\v1_2_1\src\Shared\Components\IComponent;

class EventHandler extends IComponent {

	/**
	 * Called on handler construction.
	 *
	 * @return void
	 */
	protected function onSetUp() {
		// TODO: Implement onSetUp() method.
	}

    public function addOnActivate( $callable ) {

        register_activation_hook( $this->s()->getMainPluginFile(), $callable );

    }

    public function addOnDeactivate( $callable ) {

        register_deactivation_hook( $this->s()->getMainPluginFile(), $callable );

    }

}