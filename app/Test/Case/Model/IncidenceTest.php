<?php
App::uses('Incidence', 'Model');

/**
 * Incidence Test Case
 *
 */
class IncidenceTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.incidence',
		'app.creating_user'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Incidence = ClassRegistry::init('Incidence');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Incidence);

		parent::tearDown();
	}

}
