<?php
namespace shellpress\v1_0_0\src\Pages;


use shellpress\v1_0_0\src\Component;

class PagesHandler extends Component {

    /**
     * @var Page[] - Array of Page objects. They are used further by 'prepare' method.
     */
    protected $pages = array();

    /**
     * @var string - it will be used for prefixing page slugs etc.
     */
    protected $prefix;

    /**
     * @param string $prefix
     */
    public function init( $prefix ) {

        $this->prefix = $prefix;

    }

    /**
     * @param array $args - a set of arguments you can pass for object creation
     */
    public function addPage( $name ,$args ) {

        $page_args = array(
            'prefix'    =>  $this->prefix
        );

        $page_args = array_merge_recursive( $page_args, $args );    //  safe merging

        $this->pages[] = new Page();

    }

}