<?php
/**
 * RequestFixture
 *
 */
class RequestFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'request_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'request_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 45, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'bool_annulled' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'client_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'subtotal_price' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '10,2', 'unsigned' => false),
		'comment' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'request_date' => '2018-04-21 07:57:26',
			'request_code' => 'Lorem ipsum dolor sit amet',
			'bool_annulled' => 1,
			'client_id' => 1,
			'subtotal_price' => '',
			'comment' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'created' => '2018-04-21 07:57:26',
			'modified' => '2018-04-21 07:57:26'
		),
	);

}
