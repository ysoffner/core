<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files\BackgroundJob;

use OC\BackgroundJob\TimedJob;
use OC\Lock\Persistent\LockManager;
use OC\Lock\Persistent\LockMapper;

/**
 * Clean up persistent file locks that are expired
 */
class CleanupPersistentFileLocks extends TimedJob {

	/**
	 * Default interval in minutes
	 *
	 * @var int $defaultIntervalMin
	 **/
	protected $defaultIntervalMin = 30;

	/**
	 * sets the correct interval for this timed job
	 */
	public function __construct() {
		$this->interval = $this->defaultIntervalMin * 60;
	}

	/**
	 * Makes the background job do its work
	 *
	 * @param array $argument unused argument
	 * @throws \OCP\AppFramework\QueryException
	 */
	public function run($argument) {
		/** @var LockMapper $lockManager */
		$lockManager = \OC::$server->query(LockMapper::class);
		$lockManager->cleanup();
	}
}
