<?php
/**
 * InvoiceFixture
 *
 */
class InvoiceFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'order_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'invoice_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 25, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'invoice_date' => array('type' => 'date', 'null' => false, 'default' => null),
		'bool_annulled' => array('type' => 'boolean', 'null' => false, 'default' => null),
		'client_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'currency_id' => array('type' => 'integer', 'null' => false, 'default' => '1', 'unsigned' => false),
		'bool_credit' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'due_date' => array('type' => 'date', 'null' => true, 'default' => null),
		'cashbox_accounting_code_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'bool_retention' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'retention_amount' => array('type' => 'decimal', 'null' => false, 'default' => '0.00', 'length' => '10,2', 'unsigned' => false),
		'retention_number' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 40, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'bool_IVA' => array('type' => 'boolean', 'null' => false, 'default' => null),
		'subtotal_price' => array('type' => 'decimal', 'null' => false, 'default' => '0.00', 'length' => '10,2', 'unsigned' => false),
		'iva_price' => array('type' => 'decimal', 'null' => false, 'default' => '0.00', 'length' => '10,2', 'unsigned' => false),
		'total_price' => array('type' => 'decimal', 'null' => false, 'default' => '0.00', 'length' => '10,2', 'unsigned' => false),
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
			'order_id' => 1,
			'invoice_code' => 'Lorem ipsum dolor sit a',
			'invoice_date' => '2015-05-25',
			'bool_annulled' => 1,
			'client_id' => 1,
			'currency_id' => 1,
			'bool_credit' => 1,
			'due_date' => '2015-05-25',
			'cashbox_accounting_code_id' => 1,
			'bool_retention' => 1,
			'retention_amount' => '',
			'retention_number' => 'Lorem ipsum dolor sit amet',
			'bool_IVA' => 1,
			'subtotal_price' => '',
			'iva_price' => '',
			'total_price' => '',
			'created' => '2015-05-25 19:09:50',
			'modified' => '2015-05-25 19:09:50'
		),
	);

}
