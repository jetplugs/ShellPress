<?php
namespace shellpress\v1_2_2\src\Components\External;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-08-21
 * Time: 18:33
 */

use shellpress\v1_2_2\src\Shared\Components\IComponent;

class OptionsHandler extends IComponent {

    /** @var string */
    protected $optionsKey = '';

    /** @var array */
    protected $optionsData = array();

    /** @var array */
    protected $defaultData = array();

    /** @var bool */
    protected $areOptionsLoaded = false;

	/**
	 * Called on handler construction.
	 *
	 * @return void
	 */
	protected function onSetUp() {
		// TODO: Implement onSetUp() method.
	}

    /**
     * Loads saved options from WP database.
     *
     * @return void
     */
    public function load() {

        $options = get_option( $this->getOptionsKey(), array() );

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

    	//  Make sure options are loaded
	    if( ! $this->areOptionsLoaded() ){
	    	$this->load();
		    $this->areOptionsLoaded( true );
	    }

        if( empty( $arrayPath ) ){

            return $this->optionsData;

        } else {

            $keys = explode( '/', $arrayPath );

            return $this->s()->utility->getValueByKeysPath( $this->optionsData, $keys, $defaultValue );

        }

    }

    /**
     * Sets value in options array by given path.
     *
     * @param string $arrayPath - Next array keys separated by `/`
     * @param mixed $value
     */
    public function set( $arrayPath = '', $value ) {

        if( empty( $arrayPath ) ){

            $this->optionsData = $value;

        } else {

            $keys               = explode( '/', $arrayPath );
            $this->optionsData  = $this->s()->utility->setValueByKeysPath( $this->optionsData, $keys, $value );

        }

    }

    /**
     * Saves current options to database.
     *
     * @return bool
     */
    public function flush() {

        return update_option( $this->getOptionsKey(), $this->optionsData );

    }

    /**
     * Sets options key.
     *
     * @param string $key
     */
    public function setOptionsKey( $key ) {

        $this->optionsKey = $key;

    }

    /**
     * Gets options key.
     *
     * @return string
     */
    public function getOptionsKey() {

        return $this->optionsKey ? $this->optionsKey : $this->s()->getPrefix();

    }

    /**
     * Gets default options.
     *
     * @return array
     */
    public function getDefaultOptions() {

        return $this->defaultData;

    }

    /**
     * Sets default options.
     *
     * @param $options
     */
    public function setDefaultOptions( $options ) {

        $this->defaultData = $options;

    }

    /**
     * Checks current saved options and fills them with defaults.
     * If some key already exists, it will not be updated.
     *
     * @param array $options - If not set, they will be filled with default options.
     *
     * @return void
     */
    public function fillDifferencies( $options = array() ) {

        $currentOptions =   $this->get( '', array() );
        $defaultOptions =   $options ? $options : $this->getDefaultOptions();

        $updateOptions =    $this->s()->utility->arrayMergeRecursiveDistinctSafe( $currentOptions, $defaultOptions );

        $this->set( '', $updateOptions );

    }

    /**
     * Replaces all options with defaults.
     *
     * @return void
     */
    public function restoreDefaults() {

        $this->set( '', $this->getDefaultOptions() );

    }

	/**
	 * Setter/ Getter for loading flag.
	 *
	 * @param bool $bool
	 *
	 * @return bool
	 */
    protected function areOptionsLoaded( $bool = null ) {

    	if( $bool === null ){
    		return (bool) $this->areOptionsLoaded;
	    } else {
    		return $this->areOptionsLoaded = (bool) $bool;
	    }

    }

}