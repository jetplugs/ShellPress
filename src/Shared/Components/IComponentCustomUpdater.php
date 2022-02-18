<?php
namespace shellpress\v1_3_9\src\Shared\Components;

/**
 * Date: 13.05.2018
 * Time: 21:02
 */

/**
 * Class CustomUpdater
 *
 * @deprecated
 * @package shellpress\v1_3_9\src\Shared\Components
 */
abstract class IComponentCustomUpdater extends IComponent {

    /** @var string */
    protected $serverUrl;

    /** @var array */
    protected $requestBodyArgs;

    /** @var string */
    protected $appDirBasename;

    /**
     * Registers update_plugins transient filter.
     *
     * @param string $serverUrl       - URL to server which we will ask for updates.
     * @param array  $requestBodyArgs - POST arguments passed when making request for updates.
     *
     * @return void
     */
    public function setUpdateSource( $serverUrl, $requestBodyArgs = array() ) {

    }

    /**
     * Hides package information from update_plugins transient.
     *
     * @param string $info
     *
     * @return void
     */
    public function disableUpdatePackage() {

    }

}