<?php
namespace shellpress\v1_0_8\src\Handlers;

/**
 * @author jakubkuranda@gmail.com
 * Date: 08.09.2017
 * Time: 14:41
 */

class UtilityHandler {

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

    /**
     * Generates CSV formatted string from array.
     *
     * @return string|false
     */
    function arrayToCSV( $array, $hasHeaderRow = true, $colSep = ",", $rowSep = "\n", $qut = '"') {

        $output = '';

        if ( ! is_array( $array ) or ! is_array( $array[0] ) ) return false;

        //  Header row.
        if ( $hasHeaderRow ){

            foreach( $array[0] as $key => $val ){

                //  Escaping quotes.
                $key = str_replace( $qut, "$qut$qut", $key );
                $output .= "$colSep$qut$key$qut";

            }

            $output = substr( $output, 1 ) . $rowSep;
        }

        //  Data rows.
        foreach($array as $key => $val){

            $tmp = '';

            foreach($val as $cell_key => $cell_val){

                //  Escaping quotes.
                $cell_val = str_replace($qut, "$qut$qut", $cell_val);
                $tmp .= "$colSep$qut$cell_val$qut";

            }

            $output .= substr( $tmp, 1 ) . $rowSep;

        }

        return $output;
    }

}