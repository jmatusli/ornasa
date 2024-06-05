<?php
App::uses('AppModel', 'Model');


class PurchaseOrderInvoice extends AppModel {

	public $displayField = 'invoice_code';


	public $belongsTo = [
    'Entry' => [
			'className' => 'Order',
			'foreignKey' => 'entry_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'PaymentUser' => [
			'className' => 'User',
			'foreignKey' => 'payment_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'PurchaseOrder' => [
			'className' => 'PurchaseOrder',
			'foreignKey' => 'purchase_order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
