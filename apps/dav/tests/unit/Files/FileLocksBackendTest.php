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

namespace OCA\DAV\Tests\Unit\Files;

use OC\Lock\Persistent\Lock;
use OCA\DAV\Connector\Sabre\File;
use OCA\DAV\Files\FileLocksBackend;
use OCP\Files\FileInfo;
use OCP\Files\Storage\IPersistentLockingStorage;
use OCP\Files\Storage\IStorage;
use OCP\Lock\Persistent\ILock;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\IFile;
use Sabre\DAV\Locks\LockInfo;
use Sabre\DAV\Tree;
use Test\TestCase;

class FileLocksBackendTest extends TestCase {

	/** @var FileLocksBackend */
	private $plugin;
	/** @var Tree | \PHPUnit_Framework_MockObject_MockObject */
	private $tree;
	/** @var IPersistentLockingStorage | IStorage | \PHPUnit_Framework_MockObject_MockObject */
	private $storageOfFileToBeLocked;

	public function setUp() {
		parent::setUp();

		$this->storageOfFileToBeLocked = $this->createMock([IPersistentLockingStorage::class, IStorage::class]);

		$this->tree = $this->createMock(Tree::class);
		$this->tree->method('getNodeForPath')->willReturnCallback(function ($uri) {
			if ($uri === 'unknown-file.txt') {
				throw new NotFound();
			}
			if ($uri === 'not-a-owncloud-file.txt') {
				return $this->createMock(IFile::class);
			}
			if ($uri === 'not-on-locking-storage.txt') {
				$storage = $this->createMock(IStorage::class);
				$storage->method('instanceOfStorage')->willReturn(false);
				$fileInfo = $this->createMock(FileInfo::class);
				$fileInfo->method('getStorage')->willReturn($storage);
				$file = $this->createMock(File::class);
				$file->method('getFileInfo')->willReturn($fileInfo);
				return $file;
			}
			if ($uri === 'locked-file.txt') {
				$storage = $this->createMock([IPersistentLockingStorage::class, IStorage::class]);
				$storage->method('instanceOfStorage')->willReturn(true);
				$storage->method('getLocks')->willReturnCallback(function () {
					$lock = new Lock();
					$lock->setToken('123-456-7890');
					$lock->setScope(ILock::LOCK_SCOPE_EXCLUSIVE);
					$lock->setDepth(0);
					$lock->setGlobalFileName('locked-file.txt');
					$lock->setGlobalUserId('alice');
					$lock->setOwner('Alice Wonder');
					$lock->setTimeout(1234);
					$lock->setCreatedAt(164419200);
					return [
						$lock
					];
				});
				$fileInfo = $this->createMock(FileInfo::class);
				$fileInfo->method('getStorage')->willReturn($storage);
				$fileInfo->method('getInternalPath')->willReturn('locked-file.txt');
				$file = $this->createMock(File::class);
				$file->method('getFileInfo')->willReturn($fileInfo);
				return $file;
			}
			if ($uri === 'file-to-be-locked.txt') {
				$this->storageOfFileToBeLocked->method('instanceOfStorage')->willReturn(true);
				$this->storageOfFileToBeLocked->method('getLocks')->willReturn([]);
				$fileInfo = $this->createMock(FileInfo::class);
				$fileInfo->method('getStorage')->willReturn($this->storageOfFileToBeLocked);
				$fileInfo->method('getInternalPath')->willReturn('locked-file.txt');
				$file = $this->createMock(File::class);
				$file->method('getFileInfo')->willReturn($fileInfo);
				return $file;
			}
			return $this->createMock(File::class);
		});

		$this->plugin = new FileLocksBackend($this->tree, false);
	}

	public function testGetLocks() {
		$locks = $this->plugin->getLocks('unknown-file.txt', true);
		$this->assertEmpty($locks);
		$locks = $this->plugin->getLocks('not-a-owncloud-file.txt', true);
		$this->assertEmpty($locks);
		$locks = $this->plugin->getLocks('not-on-locking-storage.txt', true);
		$this->assertEmpty($locks);
		$locks = $this->plugin->getLocks('locked-file.txt', true);
		$lockInfo = new LockInfo();
		$lockInfo->token = '123-456-7890';
		$lockInfo->scope = LockInfo::EXCLUSIVE;
		$lockInfo->uri = 'files/alice/locked-file.txt';
		$lockInfo->owner = 'Alice Wonder';
		$lockInfo->timeout = 1234;
		$lockInfo->created = 164419200;
		$this->assertEquals([
			$lockInfo
		], $locks);
	}

	public function testLock() {
		$lockInfo = new LockInfo();
		$lockInfo->token = '123-456-7890';
		$lockInfo->scope = LockInfo::SHARED;
		$lockInfo->owner = 'Alice Wonder';
		$lockInfo->timeout = 1234;
		$lockInfo->created = 164419200;

		$this->assertFalse($this->plugin->lock('unknown-file.txt', $lockInfo));
		$this->assertFalse($this->plugin->lock('not-a-owncloud-file.txt', $lockInfo));
		$this->assertFalse($this->plugin->lock('not-on-locking-storage.txt', $lockInfo));

		$this->storageOfFileToBeLocked
			->expects($this->once())
			->method('lockNodePersistent')
			->with('locked-file.txt', [
				'token' => '123-456-7890',
				'scope' => ILock::LOCK_SCOPE_SHARED,
				'depth' => 0,
				'owner' => 'Alice Wonder'
			])
			->willReturn(true);
		$this->assertTrue($this->plugin->lock('file-to-be-locked.txt', $lockInfo));
	}

	public function testUnlock() {
		$lockInfo = new LockInfo();
		$lockInfo->token = '123-456-7890';
		$lockInfo->scope = LockInfo::SHARED;
		$lockInfo->owner = 'Alice Wonder';
		$lockInfo->timeout = 1234;
		$lockInfo->created = 164419200;

		$this->assertFalse($this->plugin->unlock('unknown-file.txt', $lockInfo));
		$this->assertFalse($this->plugin->unlock('not-a-owncloud-file.txt', $lockInfo));
		$this->assertFalse($this->plugin->unlock('not-on-locking-storage.txt', $lockInfo));

		$this->storageOfFileToBeLocked
			->expects($this->once())
			->method('unlockNodePersistent')
			->with('locked-file.txt', [
				'token' => '123-456-7890'
			])
			->willReturn(true);
		$this->assertTrue($this->plugin->unlock('file-to-be-locked.txt', $lockInfo));
	}
}
