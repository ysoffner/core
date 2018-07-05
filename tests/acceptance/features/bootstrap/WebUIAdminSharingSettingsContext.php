<?php

/**
 * ownCloud
 *
 * @author Paurakh Sharma Humagain <paurakh@jankaritech.com>
 * @copyright Copyright (c) 2018 Paurakh Sharma Humagain paurakh@jankaritech.com
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License,
 * as published by the Free Software Foundation;
 * either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */


use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use Page\AdminSharingSettingsPage;
use Page\LoginPage;

/**
 * WebUI AdminSharingSettings context.
 */
class WebUIAdminSharingSettingsContext extends RawMinkContext implements Context {
	private $adminSharingSettingsPage;
	private $loginPage;

	/**
	 *
	 * @var WebUIGeneralContext
	 */
	private $webUIGeneralContext;

	/**
	 * WebUIAdminSharingSettingsContext constructor.
	 *
	 * @param AdminSharingSettingsPage $adminSharingSettingsPage
	 * @param LoginPage $loginPage
	 */
	public function __construct(
		AdminSharingSettingsPage $adminSharingSettingsPage,
		LoginPage $loginPage
	) {
			$this->adminSharingSettingsPage = $adminSharingSettingsPage;
	}

	/**
	 * @When the admin browses to the admin sharing settings page
	 * @Given the admin has browsed to the admin sharing settings page
	 *
	 * @return void
	 */
	public function theAdminBrowsesToTheAdminSharingSettingsPage() {
		$this->webUIGeneralContext->adminLogsInUsingTheWebUI();
		$this->adminSharingSettingsPage->open();
	}

	/**
	 * @When the admin disables the share API using the webUI
	 * 
	 * @return void
	 */
	public function theAdminDisablesTheShareApiUsingTheWebUI() {
		$this->adminSharingSettingsPage->disableShareApi();
		$this->adminSharingSettingsPage->waitForAjaxCallsToStartAndFinish(
			$this->getSession()
		);
	}

	/**
	 * @When the admin disables share via link using the webUI
	 * 
	 * @return void
	 */
	public function theAdminDisablesShareViaLinkUsingTheWebui() {
		$this->adminSharingSettingsPage->disablePublicShare();
		$this->adminSharingSettingsPage->waitForAjaxCallsToStartAndFinish(
			$this->getSession()
		);
	}

	/**
	 * @When the admin disables public uploads using the webUI
	 * 
	 * @return void
	 */
	public function theAdminDisablesPublicUploadsUsingTheWebui() {
		$this->adminSharingSettingsPage->disablePublicUpload();
		$this->adminSharingSettingsPage->waitForAjaxCallsToStartAndFinish(
			$this->getSession()
		);
	}

	/**
	 * This will run before EVERY scenario.
	 * It will set the properties for this object.
	 *
	 * @BeforeScenario @webUI
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 */
	public function before(BeforeScenarioScope $scope) {
		// Get the environment
		$environment = $scope->getEnvironment();
		// Get all the contexts you need in this context
		$this->webUIGeneralContext = $environment->getContext('WebUIGeneralContext');
	}
}
