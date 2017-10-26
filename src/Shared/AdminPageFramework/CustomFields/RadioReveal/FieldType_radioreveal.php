<?php
namespace shellpress\v1_0_8\src\Shared\AdminPageFramework\CustomFields\RadioReveal;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-08-05
 * Time: 01:51
 */

use SP_v1_0_8_AdminPageFramework_FieldType_radio;

class FieldType_radioreveal extends SP_v1_0_8_AdminPageFramework_FieldType_radio {

    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'radioreveal' );

    /**
     * Defines the default key-values of this field type.
     */
    protected $aDefaultKeys = array(
        'label'         =>  array(),
        'attributes'    =>  array(),
        'reveals'       =>  array()
    );

    /**
     * Gets modified field HTML.
     * It adds new script at the end of whole string.
     *
     * @param array $fieldArray
     *
     * @return string
     *
     */
    public function getField( $fieldArray ) {

        $wholeFieldHtml = parent::getField( $fieldArray );

        return $wholeFieldHtml . PHP_EOL . $this->_getRevealScript( $fieldArray['input_id'], $fieldArray['reveal'] );

    }

    /**
     * Gets whole javascript definition.
     *
     * @param string $inputId
     *
     * @return string
     */
    protected function _getRevealScript( $inputId, $revealSelectors ) {

        $jsonRevealSelectors = wp_json_encode( $revealSelectors );

        $script = "
            jQuery( document ).ready( function( $ ){
            
                var revealSelectors = JSON.parse( '{$jsonRevealSelectors}' );
                
                $( '#field-{$inputId}' ).ShellPress_RadioReveal( revealSelectors );
                
            });
        ";

        return "<script>{$script}</script>";

    }

    /**
     * Enqueue scripts.
     *
     * @return array
     */
    public function getEnqueuingScripts() {

        $scripts = array(
            array(
                'src'           => __DIR__ . '/RadioReveal.js',
                'version'       =>  'SP_v1_0_8',
                'dependancies'  =>  array( 'jquery' )
            )
        );

        return $scripts;

    }

}