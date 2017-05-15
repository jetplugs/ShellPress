<?php
namespace shellpress_1_0_0;




/**
 * Core class of plugin.
 * To use it, simple extend it.
 */
class ShellPress {

	protected $options;
	protected $views;


    /**
     * @param array $args
     */

	function init( Array $args ) {

	    $this->options = new Options( $this );
	    $this->options->init( array() );

		
	}

}
