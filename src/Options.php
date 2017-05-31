<?php
namespace shellpress\v1_0_0\src;




/**
 * Options handler
 */
class Options extends Component {

    protected $namespace;   //TODO

    /**
     * Initialize options handler
     * @param array $args
     */
	function init( $args ) {

        // TODO

	}

    /**
     * @param mixed $path - array or string
     * @param (optional) string $var - string of end array segment
     */

	function get( $path, $var = null ) {

	    $option = get_option( $this->namespace );

	    if( is_string( $var ) ){        // if isset var

            if( is_array( $path ) ){

                foreach( $path as $node ){



                }

            } else if( is_string( $path ) ){

                // TODO

            }

        } else {                        // if ! isset var

            if( is_array( $path ) ){

                // TODO

            } else if( is_string( $path ) ){

                // TODO

            }

        }

	}

}
