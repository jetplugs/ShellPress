<?php
namespace shellpress\v1_0_8\src\Handlers;

/**
 * @author jakubkuranda@gmail.com
 * Date: 08.09.2017
 * Time: 14:41
 */

class HelpersHandler {

    /**
     * Merge two arrays without structure changing and overriding values
     *
     * @param array $array1     base array
     * @param array $array2     additional array with more keys/values
     *
     * @return array - merged array
     */
    function arrayMergeRecursiveDistinctSafe( array &$array1, array &$array2 ){

        $merged = $array1;

        foreach( $array2 as $key => &$value ){

            // If both values are arrays
            if ( is_array( $value ) && isset( $merged[$key] ) && is_array( $merged[$key] ) ){

                $merged[$key] = $this->arrayMergeRecursiveDistinctSafe( $merged[$key], $value );

            } else if( ! isset( $merged[$key] ) ) {

                $merged[$key] = $value;

            }

        }

        return $merged;
    }

}