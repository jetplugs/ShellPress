<?php
namespace shellpress\v1_0_0\src\Pages;


use shellpress\v1_0_0\ShellPress;
use shellpress\v1_0_0\src\Component;

class PagesHandler extends Component {

    /**
     * @var array - parameters for each page. They are used further by 'prepare' method.
     */
    protected $pages = array();

    /**
     * PagesHandler constructor.
     * @param ShellPress $app
     */
    function __construct( $app ){

        parent::_construct( $app );

    }

    /**
     * @param string $prefix
     */
    public function init( $args ) {

        /**
         * PREFIX/factory/adminpage/handler
         * @returns PagesHandler
         */
        do_action( $this->app->prefix( '/factory/adminpage/handler' ), $this );

        /**
         * PREFIX/factory/adminpage/list
         * Apply filter for direct pages array modification
         */
        $this->pages = apply_filters( $this->app->prefix( '/factory/adminpage/list' ), $this->pages );

    }

    /**
     * @param string $slug
     * @param array $pageArgs
     */
    public function addPage( $slug, $pageArgs ) {

        $default_pageArgs = array(
            'pageTitle'     =>  'Page Title',
            'menuTitle'     =>  'Menu Title',
            'capability'    =>  'manage_options',
            'slug'          =>  $slug,
            'parent'        =>  null,
            'icon'          =>  'dashicons-admin-plugins',
            'order'         =>  10,
            'callable'      =>  null
        );

        $pageArgs = array_merge( $default_pageArgs, $pageArgs );    //  safe merging

        $this->pages[$slug] = $pageArgs;

    }

    /**
     * @param string $slug
     */
    public function removePage( $slug ) {

        unset( $this->pages[$slug] );

    }

    /**
     * The most important method.
     * Call it to execute code for admin pages creation.
     */
    public function flushPages() {

        foreach( $this->pages as $page ){

            if( $page['parent'] === null ){     //  root element

                add_menu_page(
                    $page['pageTitle'],
                    $page['menuTitle'],
                    $page['capability'],
                    $page['slug'],
                    $page['callable'],
                    $page['icon'],
                    $page['order']
                );

            } else {                            //  child element

                add_submenu_page(
                    $page['parent'],
                    $page['pageTitle'],
                    $page['menuTitle'],
                    $page['capability'],
                    $page['slug'],
                    $page['callable']
                );

            }

        }

    }



}