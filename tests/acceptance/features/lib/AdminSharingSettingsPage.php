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
namespace Page;

use Behat\Mink\Session;

/**
 * Admin Sharing Settings page.
 */
class AdminSharingSettingsPage extends OwncloudPage {
	
	/**
	 *
	 * @var string $path
	 */
	protected $path = '/index.php/settings/admin?sectionid=sharing';
	
	protected $shareApiCheckboxXpath = '//label[@for="shareAPIEnabled"]';
	protected $publicShareCheckboxXpath = '//label[@for="allowLinks"]';
	protected $publicUploadCheckboxXpath = '//label[@for="allowPublicUpload"]';


	/**
	 * Disable apps to use the Share API
	 * 
	 * @return void
	 */
	public function disableShareApi() {
		$shareApiCheckbox = $this->find("xpath", $this->shareApiCheckboxXpath);
		$shareApiCheckbox->click();
	}

	/**
	 * Disable users to share via link
	 * 
	 * @return void
	 */
	public function disablePublicShare() {
		$shareApiCheckbox = $this->find("xpath", $this->publicShareCheckboxXpath);
		$shareApiCheckbox->click();
	}

	/**
	 * Disable public uploads
	 *
	 * @return void
	 */
	public function disablePublicUpload() {
		$shareApiCheckbox = $this->find("xpath", $this->publicUploadCheckboxXpath);
		$shareApiCheckbox->click();
	}

	/**
	 * waits till at least one Ajax call is active and
	 * then waits till all outstanding ajax calls finish
	 *
	 * @param Session $session
	 * @param int $timeout_msec
	 *
	 * @return void
	 */
	public function waitForAjaxCallsToStartAndFinish(
		Session $session,
		$timeout_msec = STANDARDUIWAITTIMEOUTMILLISEC
	) {
			$start = \microtime(true);
			$this->waitForAjaxCallsToStart($session);
			$end = \microtime(true);
			$timeout_msec = $timeout_msec - (($end - $start) * 1000);
			$timeout_msec = \max($timeout_msec, MINIMUMUIWAITTIMEOUTMILLISEC);
			$this->waitForOutstandingAjaxCalls($session, $timeout_msec);
	}
}
