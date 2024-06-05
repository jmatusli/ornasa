<?php
App::uses('ProductCategoriesController', 'Controller');

/**
 * ProductCategoriesController Test Case
 *
 */
class ProductCategoriesControllerTest extends ControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.product_category',
		'app.product_type',
		'app.product',
		'app.production_run',
		'app.machine',
		'app.operator',
		'app.shift',
		'app.production_movement',
		'app.stock_item',
		'app.production_result_code',
		'app.stock_movement',
		'app.order',
		'app.third_party',
		'app.stock_movement_type'
	);

/**
 * testIndex method
 *
 * @return void
 */
	public function testIndex() {
		$this->markTestIncomplete('testIndex not implemented.');
	}

/**
 * testView method
 *
 * @return void
 */
	public function testView() {
		$this->markTestIncomplete('testView not implemented.');
	}

/**
 * testAdd method
 *
 * @return void
 */
	public function testAdd() {
		$this->markTestIncomplete('testAdd not implemented.');
	}

/**
 * testEdit method
 *
 * @return void
 */
	public function testEdit() {
		$this->markTestIncomplete('testEdit not implemented.');
	}

/**
 * testDelete method
 *
 * @return void
 */
	public function testDelete() {
		$this->markTestIncomplete('testDelete not implemented.');
	}

}
