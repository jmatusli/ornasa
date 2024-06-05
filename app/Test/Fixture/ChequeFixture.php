<?php
/**
 * ChequeFixture
 *
 */
class ChequeFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'cheque_date' => array('type' => 'date', 'null' => false, 'default' => null),
		'cheque_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'receiver_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'amount' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '10,2', 'unsigned' => false),
		'currency_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'concept' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'bank_accounting_code_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'accounting_register_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'purchase_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'cheque_type_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
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
			'cheque_date' => '2015-06-07',
			'cheque_code' => 'Lorem ipsum dolor sit amet',
			'receiver_name' => 'Lorem ipsum dolor sit amet',
			'amount' => '',
			'currency_id' => 1,
			'concept' => 'Lorem ipsum dolor sit amet',
			'bank_accounting_code_id' => 1,
			'accounting_register_id' => 1,
			'purchase_id' => 1,
			'cheque_type_id' => 1,
			'created' => '2015-06-07 19:07:54',
			'modified' => '2015-06-07 19:07:54'
		),
	);

}
