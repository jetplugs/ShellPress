<?php
namespace shellpress\v1_3_76\demo\src\Components;

use shellpress\v1_3_76\src\Shared\Components\IUniversalFrontComponentEDDLicenser;

/**
 * @author jakubkuranda@gmail.com
 * Date: 16.09.2019
 * Time: 11:34
 */
class EddLicenseManagerExample extends IUniversalFrontComponentEDDLicenser {

	/**
	 * This method should be used to set up configuration.
	 *
	 * @return void
	 */
	public function onSetUpLicenser() {

		$this->setApiUrl( 'https://easydigitaldownloads.com' );
		$this->setProductId( '5' );

	}

}