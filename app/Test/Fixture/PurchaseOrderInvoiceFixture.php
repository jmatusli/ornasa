<?php
/**
 * PurchaseOrderInvoiceFixture
 *
 */
class PurchaseOrderInvoiceFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'purchase_order_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'entry_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'invoice_code' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'bool_iva' => array('type' => 'boolean', 'null' => false, 'default' => null),
		'invoice_subtotal' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '10,2', 'unsigned' => false),
		'invoice_iva' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '10,2', 'unsigned' => false),
		'invoice_total' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '10,2', 'unsigned' => false),
		'remarca' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 150, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
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
			'purchase_order_id' => 1,
			'entry_id' => 1,
			'invoice_code' => 1,
			'bool_iva' => 1,
			'invoice_subtotal' => '',
			'invoice_iva' => '',
			'invoice_total' => '',
			'remarca' => 'Lorem ipsum dolor sit amet',
			'created' => '2020-06-01 18:36:39',
			'modified' => '2020-06-01 18:36:39'
		),
	);

}
