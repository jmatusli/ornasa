<?php
App::uses('AppController', 'Controller');

class PurchaseOrderProductsController extends AppController {

	public $components = array('Paginator');

	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->Auth->allow('getpurchaseorderproductsfordepartment','getproductdescriptionforpurchaseorderproduct','getproductionordersforpurchaseorderproduct');		
	}
	
	public function index() {
		$this->PurchaseOrderProduct->recursive = -1;
		
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
		
		$purchaseOrderProductCount=	$this->PurchaseOrderProduct->find('count', array(
			'fields'=>array('PurchaseOrderProduct.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($purchaseOrderProductCount!=0?$purchaseOrderProductCount:1),
		);

		$purchaseOrderProducts = $this->Paginator->paginate('PurchaseOrderProduct');
		$this->set(compact('purchaseOrderProducts'));
	}

	public function view($id = null) {
		if (!$this->PurchaseOrderProduct->exists($id)) {
			throw new NotFoundException(__('Invalid purchase order product'));
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
		$options = array('conditions' => array('PurchaseOrderProduct.' . $this->PurchaseOrderProduct->primaryKey => $id));
		$this->set('purchaseOrderProduct', $this->PurchaseOrderProduct->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->PurchaseOrderProduct->create();
			if ($this->PurchaseOrderProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The purchase order product has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The purchase order product could not be saved. Please, try again.'));
			}
		}
		$purchaseOrders = $this->PurchaseOrderProduct->PurchaseOrder->find('list');
		$products = $this->PurchaseOrderProduct->Product->find('list');
		$this->set(compact('purchaseOrders', 'products'));
	}

	public function edit($id = null) {
		if (!$this->PurchaseOrderProduct->exists($id)) {
			throw new NotFoundException(__('Invalid purchase order product'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->PurchaseOrderProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The purchase order product has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The purchase order product could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('PurchaseOrderProduct.' . $this->PurchaseOrderProduct->primaryKey => $id));
			$this->request->data = $this->PurchaseOrderProduct->find('first', $options);
		}
		$purchaseOrders = $this->PurchaseOrderProduct->PurchaseOrder->find('list');
		$products = $this->PurchaseOrderProduct->Product->find('list');
		$this->set(compact('purchaseOrders', 'products'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->PurchaseOrderProduct->id = $id;
		if (!$this->PurchaseOrderProduct->exists()) {
			throw new NotFoundException(__('Invalid purchase order product'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->PurchaseOrderProduct->delete()) {
			$this->Session->setFlash(__('The purchase order product has been deleted.'));
		} else {
			$this->Session->setFlash(__('The purchase order product could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}

	public function getpendingproductsfordepartment() {
		$this->layout = "ajax";
		
		$this->loadModel('Department');
		$departmentId=trim($_POST['department_id']);
		if (!$departmentId){
			throw new NotFoundException(__('Departamento no presente'));
		}
		
		$purchaseOrderProductsForDepartment=$this->PurchaseOrderProduct->find('all',array(
			'fields'=>array(
				'PurchaseOrderProduct.id','PurchaseOrderProduct.product_quantity',
				'Product.name','ProductionOrder.production_order_code',
			),
			'conditions'=>array(
				'PurchaseOrderProduct.department_id'=>$departmentId,
				'PurchaseOrderProduct.bool_processed'=>'0',
			),
			'order'=>'Product.name',
		));
		//pr($purchaseOrderProductsForDepartment);
		$this->set(compact('purchaseOrderProductsForDepartment'));
	}

	public function getproductdescriptionforpurchaseorderproduct(){
		$this->layout = "ajax";
		$this->autoRender = false;
		
		$purchaseorderproductid=trim($_POST['purchaseorderproductid']);
		
		$this->PurchaseOrderProduct->recursive=-1;
		$purchaseOrderProduct=$this->PurchaseOrderProduct->find('first',array(
			'conditions'=>array(
				'PurchaseOrderProduct.id'=>$purchaseorderproductid,
			),
		));

		if (!empty($purchaseOrderProduct)){
			return $purchaseOrderProduct['PurchaseOrderProduct']['product_description'];
		}
		else {
			return "";
		}
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


}
