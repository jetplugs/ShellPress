<?php
namespace shellpress_1_0_0;




/**
 * Options handler
 */
class Options {

	protected $app;




	function _construct( ShellPress $app ) {

		$this->app = $app;

		return $this;

	}

	function setup( $args ) {

        // TODO

	}

    /**
     * @param $path - mixed, array or string
     * @param $var - string of end array segment
     */

	function get( $path, $var = null ) {

        if( is_array( $path ) ){

            // TODO

        } else if( is_string( $path ) ){

            // TODO

        }

	}

}
