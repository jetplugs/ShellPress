<?php
namespace shellpress\v1_3_85\demo;

/**
 * Date: 15.01.2019
 * Time: 21:40
 */

use shellpress\v1_3_85\demo\src\Components\AdminPageExample;
use shellpress\v1_3_85\demo\src\Components\EddLicenseManagerExample;
use shellpress\v1_3_85\demo\src\Components\EddLicenseManagerExample2;
use shellpress\v1_3_85\demo\src\Components\UniversalFrontExample;
use shellpress\v1_3_85\ShellPress;

class Demo extends ShellPress {

	/** @var UniversalFrontExample */
	public $universalFrontExample;

	/** @var AdminPageExample */
	public $adminPageExample;

	/** @var EddLicenseManagerExample */
	public $eddLicenseManagerExample;

	/** @var EddLicenseManagerExample2 */
	public $eddLicenseManagerExample2;

	/**
	 * Called automatically after core is ready.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		$this->universalFrontExample = new UniversalFrontExample( $this );
		$this->adminPageExample = new AdminPageExample( $this );
		$this->eddLicenseManagerExample = new EddLicenseManagerExample( $this );
		$this->eddLicenseManagerExample2 = new EddLicenseManagerExample2( $this );

	}

}