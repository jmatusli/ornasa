<?php
App::uses('PagePermission', 'Model');

/**
 * PagePermission Test Case
 *
 */
class PagePermissionTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.page_permission'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->PagePermission = ClassRegistry::init('PagePermission');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->PagePermission);

		parent::tearDown();
	}

}
