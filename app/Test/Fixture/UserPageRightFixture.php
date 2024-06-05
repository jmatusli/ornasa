<?php
/**
 * UserPageRightFixture
 *
 */
class UserPageRightFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'permission_datetime' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'page_right_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'role_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'bool_allowed' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'controller' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 20, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'action' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 30, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'permission_datetime' => '2020-10-14 12:02:10',
			'page_right_id' => 1,
			'role_id' => 1,
			'user_id' => 1,
			'bool_allowed' => 1,
			'controller' => 'Lorem ipsum dolor ',
			'action' => 'Lorem ipsum dolor sit amet',
			'created' => '2020-10-14 12:02:10',
			'modified' => '2020-10-14 12:02:10'
		),
	);

}
