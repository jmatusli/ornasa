<?php
App::uses('AppModel', 'Model');
/**
 * QuotationProduct Model
 *
 * @property Quotation $Quotation
 * @property Product $Product
 * @property Currency $Currency
 */
class QuotationProduct extends AppModel {
/*
	public function getDroppedPercentageForQuotationProduct($quotationProduct_id){
		$quotationProduct=$this->read(null,$quotationProduct_id);
		$quotation=$this->Quotation->read(null,$quotationProduct['QuotationProduct']['quotation_id']);
	
		if ($quotation['Quotation']['bool_rejected']){
			return 100;
		}
		else {
			$invoiceModel=ClassRegistry::init('Invoice');
			$invoiceModel->recursive=-1;		
			$invoices=$invoiceModel->find('all',array(
				'conditions'=>array(
					'Invoice.quotation_id'=>$quotation['Quotation']['id'],
				),
			));
			if (!empty($invoices)){
				$invoiceProductTotal=0;
				foreach ($invoices as $invoice){
					$invoiceProductModel=ClassRegistry::init('InvoiceProduct');
					$invoiceProductModel->recursive=-1;		
					$invoiceProducts=$invoiceProductModel->find('all',array(
						'conditions'=>array(
							'InvoiceProduct.product_id'=>$quotationProduct['QuotationProduct']['product_id'],
							'InvoiceProduct.invoice_id'=>$invoice['Invoice']['id'],
						),
					));
					if (!empty($invoiceProducts)){
						foreach ($invoiceProducts as $invoiceProduct){
							$invoiceProductTotal+=$invoiceProduct['InvoiceProduct']['product_total_price'];
						}
					}
				}
				return round(100-(100*$invoiceProductTotal/$quotationProduct['QuotationProduct']['product_total_price']),2);
			}
			else {
				$salesOrderModel=ClassRegistry::init('SalesOrder');
				$salesOrderModel->recursive=-1;		
				$salesOrders=$salesOrderModel->find('all',array(
					'conditions'=>array(
						'SalesOrder.quotation_id'=>$quotation['Quotation']['id'],
					),
				));
				if (!empty($salesOrders)){
					$salesOrderProductTotal=0;
					foreach ($salesOrders as $salesOrder){
						//pr($salesOrder);
						$salesOrderProductModel=ClassRegistry::init('SalesOrderProduct');
						$salesOrderProductModel->recursive=-1;		
						//echo "sales order id is ".$salesOrder['SalesOrder']['id']."<br/>";
						//echo "product id is ".$quotationProduct['QuotationProduct']['product_id']."<br/>";
						$salesOrderProducts=$salesOrderProductModel->find('all',array(
							'conditions'=>array(
								'SalesOrderProduct.product_id'=>$quotationProduct['QuotationProduct']['product_id'],
								'SalesOrderProduct.sales_order_id'=>$salesOrder['SalesOrder']['id'],
							),
						));
						if (!empty($salesOrderProducts)){
							foreach ($salesOrderProducts as $salesOrderProduct){
								$salesOrderProductTotal+=$salesOrderProduct['SalesOrderProduct']['product_total_price'];
							}
						}
					}
					return round(100-(100*$salesOrderProductTotal/$quotationProduct['QuotationProduct']['product_total_price']),2);
				}
				else {
					$dueDate= new DateTime($quotation['Quotation']['due_date']);
					$nowDate= new DateTime();
					$daysLate=$nowDate->diff($dueDate);
					if ((int)$daysLate->format("%r%a")<0){
						return 100;
					}
					else {
						return 0;
					}
				}
			} 
		}
	}
	public function getSoldPercentageForQuotationProduct($quotationProduct_id){
		$quotationProduct=$this->read(null,$quotationProduct_id);
		$quotation=$this->Quotation->read(null,$quotationProduct['QuotationProduct']['quotation_id']);
	
		$invoiceModel=ClassRegistry::init('Invoice');
		$invoiceModel->recursive=-1;		
		$invoices=$invoiceModel->find('all',array(
			'conditions'=>array(
				'Invoice.quotation_id'=>$quotation['Quotation']['id'],
			),
		));
		if (!empty($invoices)){
			$invoiceProductTotal=0;
			foreach ($invoices as $invoice){
				$invoiceProductModel=ClassRegistry::init('InvoiceProduct');
				$invoiceProductModel->recursive=-1;		
				$invoiceProducts=$invoiceProductModel->find('all',array(
					'conditions'=>array(
						'InvoiceProduct.product_id'=>$quotationProduct['QuotationProduct']['product_id'],
						'InvoiceProduct.invoice_id'=>$invoice['Invoice']['id'],
					),
				));
				if (!empty($invoiceProducts)){
					foreach ($invoiceProducts as $invoiceProduct){
						$invoiceProductTotal+=$invoiceProduct['InvoiceProduct']['product_total_price'];
					}
				}
			}
			return round(100*$invoiceProductTotal/$quotationProduct['QuotationProduct']['product_total_price'],2);
		}
		else {
			$salesOrderModel=ClassRegistry::init('SalesOrder');
			$salesOrderModel->recursive=-1;		
			$salesOrders=$salesOrderModel->find('all',array(
				'conditions'=>array(
					'SalesOrder.quotation_id'=>$quotation['Quotation']['id'],
				),
			));
			if (!empty($salesOrders)){
				$salesOrderProductTotal=0;
				foreach ($salesOrders as $salesOrder){
					$salesOrderProductModel=ClassRegistry::init('SalesOrderProduct');
					$salesOrderProductModel->recursive=-1;		
					$salesOrderProducts=$salesOrderProductModel->find('all',array(
						'conditions'=>array(
							'SalesOrderProduct.product_id'=>$quotationProduct['QuotationProduct']['product_id'],
							'SalesOrderProduct.sales_order_id'=>$salesOrder['SalesOrder']['id'],
						),
					));
					if (!empty($salesOrderProducts)){
						foreach ($salesOrderProducts as $salesOrderProduct){
							$salesOrderProductTotal+=$salesOrderProduct['SalesOrderProduct']['product_total_price'];
						}
					}
				}
				return round(100*$salesOrderProductTotal/$quotationProduct['QuotationProduct']['product_total_price'],2);
			}
			else {
				$dueDate= new DateTime($quotation['Quotation']['due_date']);
				$nowDate= new DateTime();
				$daysLate=$nowDate->diff($dueDate);
				return 0;
				
			}
		} 
	}
*/
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'quotation_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'product_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'currency_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

	public $belongsTo = array(
		'Quotation' => array(
			'className' => 'Quotation',
			'foreignKey' => 'quotation_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Product' => array(
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
    'RawMaterial' => [
			'className' => 'Product',
			'foreignKey' => 'raw_material_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Currency' => array(
			'className' => 'Currency',
			'foreignKey' => 'currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
