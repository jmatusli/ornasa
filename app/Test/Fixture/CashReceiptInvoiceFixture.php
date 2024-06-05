<?php
/**
 * CashReceiptInvoiceFixture
 *
 */
class CashReceiptInvoiceFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'cash_receipt_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'invoice_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
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
			'cash_receipt_id' => 1,
			'invoice_id' => 1,
			'created' => '2015-05-29 21:03:02',
			'modified' => '2015-05-29 21:03:02'
		),
	);

}
