<?php
namespace shellpress\v1_0_8\src\Handlers;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-08-21
 * Time: 18:33
 */

class OptionsHandler {

    /** @var string */
    protected $optionsKey;

    /** @var array */
    protected $optionsData;

    /**
     * OptionsHandler constructor.
     *
     * @param $optionsKey
     */
    public function __construct( $optionsKey ) {

        $this->optionsKey = $optionsKey;

        $this->load();  //  Load options from database

    }

    /**
     * Loads saved options from WP database.
     *
     * @return void
     */
    public function load() {

        $options = get_option( $this->optionsKey, array() );

        $this->optionsData = $options;

    }

    /**
     * Gets option from array of WP options.
     *
     * @param string $arrayPath     Next array keys separated by `/`
     * @param mixed $defaultValue   Default value, if this key is not set
     *
     * @return mixed
     */
    public function get( $arrayPath = '', $defaultValue = null ) {

        if( empty( $arrayPath ) ){

            return $this->optionsData;

        } else {

            $pathPieces = explode('/', $arrayPath);

            $option = $this->optionsData;

            foreach ($pathPieces as $pathNode) {

                if (isset($option[$pathNode])) {

                    $option = &$option[$pathNode];

                } else {

                    return $defaultValue;

                }

            }

            return $option;

        }

    }

    /**
     * Updates WP database option with given value.
     *
     * @param mixed $data
     *
     * @return bool
     */
    public function update( $data ) {

        return update_option( $this->getOptionsKey(), $data );

    }

    /**
     * Gets options key.
     *
     * @return string
     */
    public function getOptionsKey() {

        return $this->optionsKey;

    }

}