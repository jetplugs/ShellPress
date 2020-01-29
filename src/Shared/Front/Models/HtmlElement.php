<?php
namespace shellpress\v1_3_82\src\Shared\Front\Models;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-10-16
 * Time: 22:58
 */

class HtmlElement {

    /** @var string */
    protected $tag;

    /** @var bool */
    protected $isContainer;

    /** @var array */
    protected $attributes = array();

    /** @var array */
    protected $content = array();

    /**
     * HtmlElement constructor.
     *
     * @param string $tag
     * @param bool $isContainer
     */
    public function __construct( $tag, $isContainer = true ) {

        $this->tag          = (string) $tag;
        $this->isContainer  = (bool) $isContainer;

    }

    /**
     * @return string
     */
    public function __toString() {

        return $this->getDisplay();

    }

    /**
     * @param $tag
     * @param bool $isContainer
     *
     * @return HtmlElement
     */
    public static function create( $tag, $isContainer = true ) {

        return new self( $tag, $isContainer );

    }

    /**
     * @return string
     */
    public function getTag() {

        return $this->tag;

    }

    /**
     * @return array
     */
    public function getAttributes() {

        return $this->attributes;

    }

    /**
     * @return string
     */
    public function getAttributesAsString() {

        $attrStrings = array();

        foreach( $this->getAttributes() as $attrName => $values ){

            $attrStrings[] = sprintf( '%1$s="%2$s"', $attrName, implode( ' ', (array) $values ) );

        }

        $html = implode( ' ', $attrStrings );

        return $html;

    }

    /**
     * @param array $attributes
     *
     * @return self
     */
    public function setAttributes( $attributes ) {

        $this->attributes = $attributes;

        return $this;

    }

    /**
     * @return bool
     */
    public function isContainer() {

        return $this->isContainer;

    }

    /**
     * @param string $attrName      Attribute key.
     * @param string|array $value   Space separated string or array of values.
     *
     * @return self
     */
    public function addAttribute( $attrName, $value ) {

        if( ! isset( $this->attributes[ $attrName ] ) ){
            $this->attributes[ $attrName ] = array();
        }

        if( ! is_array( $value ) ){
            $value = explode( ' ', $value );
        }

        $value = array_unique( array_merge( $this->attributes[ $attrName ], $value ) );                     //  Merge two arrays and unique them
        $value = array_filter( $value,  function( $value ){ return empty( $value ) ? false : true;  } );    //  Remove empty values

        $this->attributes[ $attrName ] = $value;

        return $this;

    }

    /**
     * @param array $attributes
     *
     * @return self
     */
    public function addAttributes( $attributes ) {

        foreach( $attributes as $attrName => $value ){

            $this->addAttribute( $attrName, $value );

        }

        return $this;

    }

    /**
     * @return array
     */
    public function getContent() {

        return (array) $this->content;

    }

    /**
     * @param HtmlElement[]|HtmlElement|string[]|string $content
     *
     * @return self
     */
    public function setContent( $content ) {

        $this->content = is_array( $content ) ? $content : array( $content );

        return $this;

    }

    /**
     * @param HtmlElement|string $content
     *
     * @deprecated use: addContent()
     *
     * @return self
     */
    public function append( $content ) {

        return $this->addContent( $content );

    }

	/**
	 * @param HtmlElement|string $content
	 *
	 * @return self
	 */
	public function addContent( $content ) {

		$this->content[] = $content;

		return $this;

	}

    /**
     * @param HtmlElement|string $content
     *
     * @return string
     */
    protected function parseContentToString( $content ) {

        if( $content instanceof HtmlElement ){

            return $content->getDisplay();

        } elseif( ! is_object( $content ) ) {

            return (string) $content;

        } else {

            return '';

        }

    }

    /**
     * @param string $attrName
     * @param array $values
     *
     * @return string
     */
    protected function parseAttributeToString( $attrName, $values ) {

        return sprintf( '%1$s="%2$s"', $attrName, implode( ' ', $values ) );

    }

    /**
     * @return string
     */
    public function getDisplay() {

	    //  Nested content

	    $content        = (array) $this->getContent();
	    $contentAsHtml  = '';

	    foreach( $content as $elem ){   /** @var $elem HtmlElement|string */

		    if( is_object( $elem ) && method_exists( $elem, 'getDisplay' ) ){
			    $contentAsHtml .= $elem->getDisplay() . PHP_EOL;
		    } else {
			    $contentAsHtml .= $elem . PHP_EOL;
		    }

	    }

	    //  Segments

	    $firstSegment   = $this->getTag() . ' ' . $this->getAttributesAsString();
	    $secondSegment  = $this->getTag();

	    //  Returned string

        if( $this->isContainer() ){

            return sprintf( '<%1$s>%2$s</%3$s>', $firstSegment, $contentAsHtml, $secondSegment );

        } else {

            //  For debugging purposes, content is not ommited, even if it's a mistake.

            return sprintf( '<%1$s>%2$s', $firstSegment, $contentAsHtml );

        }

    }

}