<?php
namespace shellpress\v1_0_0\src\Pages;


use shellpress\v1_0_0\src\Component;

class PagesHandler extends Component {

    /**
     * @var array - parameters for each page. They are used further by 'prepare' method.
     */
    protected $pages = array();

    /**
     * @param string $prefix
     */
    public function init( $args ) {



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

        $pageArgs = array_merge_recursive( $default_pageArgs, $pageArgs );    //  safe merging

        $this->pages[$slug] = $pageArgs;

    }

    /**
     * @param string $slug
     */
    public function removePage( $slug ) {

        unset( $this->pages[$slug] );

    }

    public function flushPages() {

        //TODO
        wp_die( var_dump( $this->app ) );

        //  leave hook for plugins
        $this->pages = apply_filters( $this->app->prefix( '/pages/list' ), $this->pages );

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