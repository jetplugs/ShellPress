<?php
namespace shellpress\v1_1_8\src\Handlers;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-11-26
 * Time: 02:29
 */

class EventHandler extends Handler {

    public function addOnActivate( $callable ) {

        register_activation_hook( $this->shell()->getMainPluginFile(), $callable );

    }

    public function addOnDeactivate( $callable ) {

        register_deactivation_hook( $this->shell()->getMainPluginFile(), $callable );

    }

}