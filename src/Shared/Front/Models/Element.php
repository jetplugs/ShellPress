<?php
namespace shellpress\v1_0_8\src\Shared\Front\Models;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-10-16
 * Time: 22:58
 */

class Element {

    /** @var string */
    protected $tag;

    /** @var array */
    protected $attributes = array();

    public function __construct( $tag ) {

        $this->tag = $tag;

    }

    public function getAttributes() {

        return $this->attributes;

    }

    public function setAttributes( $attributes ) {

        $this->attributes = $attributes;

    }

    /**
     * @param string $attrName      Attribute key.
     * @param string|array $value   Space separated string or array of values.
     */
    public function addAttribute( $attrName, $value ) {

        if( ! isset( $this->attributes[ $attrName ] ) ){
            $this->attributes[ $attrName ] = array();
        }

        if( ! is_array( $value ) ){
            $value = explode( ' ', $value );
        }

        $this->attributes[ $attrName ] = array_unique( array_merge( $this->attributes[ $attrName ], $value ) );

    }

    /**
     * @param array $attributes
     */
    public function addAttributes( $attributes ) {

        foreach( $attributes as $attrName => $value ){

            $this->addAttribute( $attrName, $value );

        }

    }

    /**
     * @return string
     */
    public function getDisplay() {

        $html = '';

        //  TODO

        return $html;

    }

}