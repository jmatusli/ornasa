<?php
App::uses('AppController', 'Controller');
/**
 * SalesOrderProducts Controller
 *
 * @property SalesOrderProduct $SalesOrderProduct
 * @property PaginatorComponent $Paginator
 */
class SalesOrderProductsController extends AppController {


	public $components = array('Paginator');

	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->Auth->allow('getprocessproductsfordepartment','getsalesorderproductinfo');		
	}

	public function getprocessproducts() {
		$this->layout = "ajax";
		
		$this->loadModel('Department');
		$this->loadModel('ProductionOrder');
		$this->loadModel('ProductionOrderProduct');
		$this->loadModel('SalesOrderProduct');
		$this->loadModel('ProductionProcessProduct');
		
		$productionProcessId=trim($_POST['production_process_id']);
		
		$qualifiedProductionOrderIds=$this->ProductionOrder->find('list',array(
			'fields'=>array('ProductionOrder.id'),
			'conditions'=>array(
				'ProductionOrder.bool_annulled'=>false,
			),
		));
		
		$qualifiedSalesOrderProductIds=$this->ProductionOrderProduct->find('list',array(
			'fields'=>array('ProductionOrderProduct.sales_order_product_id'),
			'conditions'=>array(
				'ProductionOrderProduct.production_order_id'=>$qualifiedProductionOrderIds,
			),
		));
		
		$salesOrderProductsAlreadyInProductionProcess=$this->ProductionProcessProduct->find('list',array(
			'fields'=>array('ProductionProcessProduct.sales_order_product_id'),
			'conditions'=>array(
				'ProductionProcessProduct.production_process_id'=>$productionProcessId,
			)
		));
		
		$salesOrderProductsForDepartment=$this->SalesOrderProduct->find('all',array(
			'fields'=>array(
				'SalesOrderProduct.id',
				'SalesOrderProduct.product_quantity',
			),
			'conditions'=>array(
				'OR'=>array(
					array(
						'SalesOrderProduct.id'=>$qualifiedSalesOrderProductIds,
						'SalesOrderProduct.sales_order_product_status_id'=>PRODUCT_STATUS_AWAITING_PRODUCTION,
					),
					array(
						'SalesOrderProduct.id'=>$salesOrderProductsAlreadyInProductionProcess,
					),
				),
			),
			'contain'=>array(
				'Product'=>array(
					'fields'=>array('Product.name'),
				),
				'SalesOrder'=>array(
					'ProductionOrder'=>array(
						'fields'=>array('ProductionOrder.production_order_code'),
						'conditions'=>array(
							'ProductionOrder.id'=>$qualifiedProductionOrderIds,
						),
					),
				),
			),
			'order'=>'Product.name',
		));
		//pr($salesOrderProductsForDepartment);
		$this->set(compact('salesOrderProductsForDepartment'));
	}
	
	public function getprocessproductsfordepartment() {
		// REQUIRES UPDATE
		$this->layout = "ajax";
		
		$this->loadModel('Department');
		$this->loadModel('ProductionOrder');
		$this->loadModel('ProductionOrderProduct');
		$this->loadModel('ProductionOrderProductDepartment');
		$this->loadModel('SalesOrderProduct');
		$this->loadModel('ProductionProcess');
		$this->loadModel('ProductionProcessProduct');
		
		$departmentId=trim($_POST['department_id']);
		$productionProcessId=trim($_POST['production_process_id']);
		
		if (!$departmentId){
			throw new NotFoundException(__('Departamento no presente'));
		}
		
		$pendingSalesOrderProducts=$this->SalesOrderProduct->find('all',array(
			'conditions'=>array(
				'SalesOrderProduct.sales_order_product_status_id'=>PRODUCT_STATUS_AWAITING_PRODUCTION,
			),
			'contain'=>array(
				'Product',
				'ProductionOrderProduct'=>array(
					'ProductionOrderProductDepartment',	
				),
				'ProductionProcessProduct'=>array(
					'ProductionProcess',	
				),
				'SalesOrder'=>array(
					'ProductionOrder',
				),
			),
			'order'=>'Product.name',
		));
		//pr($pendingSalesOrderProducts);
		
		$salesOrderProductsForDepartment=array();
		for ($i=0;$i<count($pendingSalesOrderProducts);$i++){
			//pr($pendingSalesOrderProducts[$i]['ProductionOrderProduct']);
			// CHECK FIRST IF THE PRODUCT NEEDS TO BE PROCESSED IN THE DEPARTMENT
			$boolDepartmentOK=false;
			$productionOrderProductQuantity=0;
			//ASSUMED: PRODUCTION ORDERS PRESENT, IF NOT, THERE IS NO WAY THAT PRODUCTS REACH THIS STATUS
			foreach ($pendingSalesOrderProducts[$i]['ProductionOrderProduct'] as $productionOrderProduct){
				//ASSUMED: PRODUCTION ORDER PRODUCT DEPARTMENT PRESENT, IF NOT, IT COULD NOT HAVE BEEN SAVED
				foreach ($productionOrderProduct['ProductionOrderProductDepartment'] as $productDepartment){
					if ($productDepartment['department_id']==$departmentId){
						$boolDepartmentOK=true;
					}
				}
				if ($boolDepartmentOK){
					$productionOrderProductQuantity+=$productionOrderProduct['product_quantity'];
				}
			}
			if ($boolDepartmentOK){
				$boolProcessProductForCurrentProcess=false;
				//pr($pendingSalesOrderProducts[$i]['ProductionProcessProduct']);
				// CHECK HOW MANY HAVE BEEN PROCESSED BY THIS DEPARTMENT
				$productionProcessProductDepartmentQuantity=0;
				if (!empty($pendingSalesOrderProducts[$i]['ProductionProcessProduct'])){	
					foreach ($pendingSalesOrderProducts[$i]['ProductionProcessProduct'] as $productionProcessProduct){
						if ($productionProcessProduct['ProductionProcess']['id']==$productionProcessId){
							$boolProcessProductForCurrentProcess=true;
						}
						else if ($productionProcessProduct['ProductionProcess']['department_id']==$departmentId){
							$productionProcessProductDepartmentQuantity+=$productionProcessProduct['product_quantity'];
						}
					}
				}
				//echo "orderquantity is ".$productionOrderProductQuantity." and processquantity is ".$productionProcessProductDepartmentQuantity."<br/>";
				if ($boolProcessProductForCurrentProcess){
					$salesOrderProductsForDepartment[]=$pendingSalesOrderProducts[$i];
				}
				elseif ($productionProcessProductDepartmentQuantity<$productionOrderProductQuantity){
					$salesOrderProductsForDepartment[]=$pendingSalesOrderProducts[$i];
				}
			}
		}
		//pr($salesOrderProductsForDepartment);
		$this->set(compact('salesOrderProductsForDepartment'));
	}

	public function getsalesorderproductinfo(){
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
		$salesOrderProductId=trim($_POST['salesorderproductid']);
		
		$this->SalesOrderProduct->recursive=-1;
		$salesOrderProduct=$this->SalesOrderProduct->find('first',array(
			'fields'=>array(
				'SalesOrderProduct.id',
				'SalesOrderProduct.product_description',
				'SalesOrderProduct.product_quantity',
			),
			'conditions'=>array(
				'SalesOrderProduct.id'=>$salesOrderProductId,
			),
			'contain'=>array(
				'Product',
				'SalesOrder',
				'ProductionOrderProduct'=>array(
					'ProductionOrder',
					'ProductionOrderProductOperationLocation',
				),
			),
		));
		$operationlocations=array();
		if (!empty($salesOrderProduct)){
			if (!empty($salesOrderProduct['ProductionOrderProduct'])){
				for ($pop=0;$pop<count($salesOrderProduct['ProductionOrderProduct']);$pop++){
					if (!empty($salesOrderProduct['ProductionOrderProduct'][$pop]['ProductionOrderProductOperationLocation'])){
						foreach ($salesOrderProduct['ProductionOrderProduct'][$pop]['ProductionOrderProductOperationLocation'] as $productlocation){
							$operationlocations[]=$productlocation['operation_location_id'];
						}
						$salesOrderProduct['ProductionOrderProduct'][$pop]['operationlocations']=$operationlocations;
					}
					else {
						$salesOrderProduct['ProductionOrderProduct'][$pop]['operationlocations']=array(0=>0);
					}
				}
			}
		}
		//pr($salesOrderProduct);
		return json_encode($salesOrderProduct);
	}
	
	public function getproductionordersforpurchaseorderproduct() {
		$this->layout = "ajax";
		
		$purchaseorderproductid=trim($_POST['purchaseorderproductid']);
		if (!$purchaseorderproductid){
			throw new NotFoundException(__('Producto no presente'));
		}
		
		$productionOrderIds=$this->PurchaseOrderProduct->find('list',array(
			'fields'=>array('PurchaseOrderProduct.production_order_id'),
			'conditions'=>array(
				'PurchaseOrderProduct.id'=>$purchaseorderproductid,
			),
		));
		
		$this->loadModel('ProductionOrder');
		$productionOrdersForPurchaseOrderProduct=$this->ProductionOrder->find('all',array(
			'fields'=>array(
				'ProductionOrder.id','ProductionOrder.production_order_code',
			),
			'conditions'=>array(
				'ProductionOrder.id'=>$productionOrderIds,
			),
			'order'=>'ProductionOrder.production_order_code',
		));
		//pr($purchaseOrderProductsForDepartment);
		$this->set(compact('purchaseOrderProductsForDepartment'));
	}
	
	// MOVED TO MODEL
	/*
	public function splitSalesOrderProduct($sales_order_product_id, $new_sales_order_product_status_id,$new_product_quantity){
		$this->autoRender = false;
		
		$originalSalesOrderProduct=$this->SalesOrderProduct->find('first',array(
			'conditions'=>array(
				'SalesOrderProduct.id'=>$sales_order_product_id,
			),
		));
		
		$productUnitPrice=$originalSalesOrderProduct['SalesOrderProduct']['product_unit_price'];
		$originalProductQuantity=$originalSalesOrderProduct['SalesOrderProduct']['product_quantity'];
		echo "before starting the splitting";
		$datasource=$this->SalesOrderProduct->getDataSource();
		$datasource->begin();
		try {
			echo "datasource started";
			// FIRST UPDATE THE PRODUCT WITH THE NEW STATUS AND THE QUANTITY
			$this->SalesOrderProduct->id=$sales_order_product_id;
			$salesOrderProductArray=array();
			$salesOrderProductArray['SalesOrderProduct']['product_quantity']=$new_product_quantity;
			$salesOrderProductArray['SalesOrderProduct']['product_total_price']=$new_product_quantity*$productUnitPrice;
			$salesOrderProductArray['SalesOrderProduct']['sales_order_product_status_id']=$new_sales_order_product_status_id;
			if (!$this->SalesOrderProduct->save($salesOrderProductArray)){
				pr($this->validateErrors($this->SalesOrderProduct));
				echo "Problema separando los productos entregados de los pendientes en la orden de venta";
				throw new Exception();
			}
			
			// THEN CREATE A NEW SALES ORDER PRODUCT
			$this->SalesOrderProduct->create();
			$salesOrderProductArray=array();
			$salesOrderProductArray['SalesOrderProduct']['sales_order_id']=$originalSalesOrderProduct['SalesOrderProduct']['sales_order_id'];
			$salesOrderProductArray['SalesOrderProduct']['product_id']=$originalSalesOrderProduct['SalesOrderProduct']['product_id'];
			$salesOrderProductArray['SalesOrderProduct']['product_description']=$originalSalesOrderProduct['SalesOrderProduct']['product_description'];
			$salesOrderProductArray['SalesOrderProduct']['product_unit_price']=$originalSalesOrderProduct['SalesOrderProduct']['product_unit_price'];
			$salesOrderProductArray['SalesOrderProduct']['product_quantity']=$originalSalesOrderProduct['SalesOrderProduct']['product_quantity']-$new_product_quantity;
			$salesOrderProductArray['SalesOrderProduct']['product_total_price']=($originalSalesOrderProduct['SalesOrderProduct']['product_quantity']-$new_product_quantity)*$productUnitPrice;
			$salesOrderProductArray['SalesOrderProduct']['currency_id']=$originalSalesOrderProduct['SalesOrderProduct']['currency_id'];
			$salesOrderProductArray['SalesOrderProduct']['bool_iva']=$originalSalesOrderProduct['SalesOrderProduct']['bool_iva'];
			$salesOrderProductArray['SalesOrderProduct']['sales_order_product_status_id']=$originalSalesOrderProduct['SalesOrderProduct']['sales_order_product_status_id'];
			$salesOrderProductArray['SalesOrderProduct']['bool_no_production']=$originalSalesOrderProduct['SalesOrderProduct']['bool_no_production'];
			if (!$this->SalesOrderProduct->save($salesOrderProductArray)){
				pr($this->validateErrors($this->SalesOrderProduct));
				echo "Problema separando los productos entregados de los pendientes en la orden de venta";
				throw new Exception();
			}
			
			// WHAT IS MISSING IS THE CONTINUING SPLITTING OF CORRESPONDING PRODUCTION ORDER PRODUCTS, PURCHASE ORDER PRODUCTS, PRODUCTION PROCESS PRODUCTS
		
			$datasource->commit();
			//$this->recordUserAction($this->Invoice->id,null,null);
			//$this->recordUserActivity($this->Session->read('User.username'),"Se registró la factura número ".$this->request->data['Invoice']['invoice_code']);
			
			return true;
		} 
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('No se podía dividir el producto de la orden de venta.'), 'default',array('class' => 'error-message'));
			return false;
		}
	}
	*/
	
	public function index() {
		$this->SalesOrderProduct->recursive = -1;
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		
		$salesOrderProductCount=	$this->SalesOrderProduct->find('count', array(
			'fields'=>array('SalesOrderProduct.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($salesOrderProductCount!=0?$salesOrderProductCount:1),
		);

		$salesOrderProducts = $this->Paginator->paginate('SalesOrderProduct');
		$this->set(compact('salesOrderProducts'));
	}

	public function view($id = null) {
		if (!$this->SalesOrderProduct->exists($id)) {
			throw new NotFoundException(__('Invalid sales order product'));
		}
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		$options = array('conditions' => array('SalesOrderProduct.' . $this->SalesOrderProduct->primaryKey => $id));
		$this->set('salesOrderProduct', $this->SalesOrderProduct->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->SalesOrderProduct->create();
			if ($this->SalesOrderProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The sales order product has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The sales order product could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		$salesOrders = $this->SalesOrderProduct->SalesOrder->find('list');
		$products = $this->SalesOrderProduct->Product->find('list');
		$salesOrderProductStatuses = $this->SalesOrderProduct->SalesOrderProductStatus->find('list');
		$this->set(compact('salesOrders', 'products', 'salesOrderProductStatuses'));
	}

	public function edit($id = null) {
		if (!$this->SalesOrderProduct->exists($id)) {
			throw new NotFoundException(__('Invalid sales order product'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->SalesOrderProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The sales order product has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The sales order product could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('SalesOrderProduct.' . $this->SalesOrderProduct->primaryKey => $id));
			$this->request->data = $this->SalesOrderProduct->find('first', $options);
		}
		$salesOrders = $this->SalesOrderProduct->SalesOrder->find('list');
		$products = $this->SalesOrderProduct->Product->find('list');
		$salesOrderProductStatuses = $this->SalesOrderProduct->SalesOrderProductStatus->find('list');
		$this->set(compact('salesOrders', 'products', 'salesOrderProductStatuses'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->SalesOrderProduct->id = $id;
		if (!$this->SalesOrderProduct->exists()) {
			throw new NotFoundException(__('Invalid sales order product'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->SalesOrderProduct->delete()) {
			$this->Session->setFlash(__('The sales order product has been deleted.'));
		} else {
			$this->Session->setFlash(__('The sales order product could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
