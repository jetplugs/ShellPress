<?php
namespace shellpress\v1_0_5\src\AdminPageFramework\CustomFields\RadioReveal;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-08-05
 * Time: 01:51
 */

use MailBoo_AdminPageFramework_FieldType_radio;
use MailBoo_AdminPageFramework_Input_radio;

class FieldType_radioreveal extends MailBoo_AdminPageFramework_FieldType_radio {

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
     */
    public function getField( $aField ) {

        $wholeFieldHtml = parent::getField( $aField );

        return $wholeFieldHtml . PHP_EOL . $this->_getRevealScript( $aField['input_id'], $aField['reveal'] );
    }

    /**
     * Gets whole javascript definition.
     *
     * @param string $sInputID
     *
     * @return string
     */
    protected function _getRevealScript( $sInputID, $aReveal ) {

        $reveal = json_encode( $aReveal );

        $script = "
            jQuery( document ).ready( function( $ ){
            
                var reveal = JSON.parse( '{$reveal}' );
                
                $( '#field-{$sInputID}' ).ShellPress_RadioReveal( reveal );
                
            });
        ";

        return "<script>{$script}</script>";

    }

    public function getEnqueuingScripts() {

        $scripts = array(
            array(
                'src'           =>  __DIR__ . '/RadioReveal.js',
                'version'       =>  'ShellPress_v1_0_5',
                'dependancies'  =>  array( 'jquery' )
            )
        );

        return $scripts;

    }

}