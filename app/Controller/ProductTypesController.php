<?php
App::uses('AppController', 'Controller');

class ProductTypesController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->ProductType->recursive = -1;
		$productTypeCount=	$this->ProductType->find('count', array(
			'fields'=>array('ProductType.id'),
		));
		
		$this->Paginator->settings = array(
			'contain'=>array(
				'ProductCategory',
				'AccountingCode',
			),
			'order' => array('ProductCategory.name'=>'ASC','ProductType.name'=> 'ASC'),
			'limit'=>$productTypeCount,
		);
		$productTypes = $this->Paginator->paginate('ProductType');
		$this->set(compact('productTypes'));
		
		$aco_name="Products/index";		
		$bool_product_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_index_permission'));
		$aco_name="Products/add";		
		$bool_product_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_add_permission'));
	}

	public function view($id = null) {
		if (!$this->ProductType->exists($id)) {
			throw new NotFoundException(__('Invalid product type'));
		}
		$options = array('conditions' => array('ProductType.' . $this->ProductType->primaryKey => $id));
		$this->set('productType', $this->ProductType->find('first', $options));
		
		$aco_name="Products/index";		
		$bool_product_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_index_permission'));
		$aco_name="Products/add";		
		$bool_product_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_add_permission'));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->ProductType->create();
			if ($this->ProductType->save($this->request->data)) {
				$this->recordUserAction($this->ProductType->id,null,null);
				$this->Session->setFlash(__('The product type has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The product type could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		$productCategories = $this->ProductType->ProductCategory->find('list');
		$this->set(compact('productCategories'));
		
		$this->loadModel('AccountingCode');
		$inventoryAccountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INVENTORY);
		$accountingCodes=$this->AccountingCode->find('list',array(
			'conditions'=>array(
				'AccountingCode.lft >'=>$inventoryAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$inventoryAccountingCode['AccountingCode']['rght'],
				'AccountingCode.bool_main'=>true,
			),
		));
		$this->set(compact('accountingCodes'));
		
		$aco_name="Products/index";		
		$bool_product_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_index_permission'));
		$aco_name="Products/add";		
		$bool_product_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_add_permission'));
	}

	public function edit($id = null) {
		if (!$this->ProductType->exists($id)) {
			throw new NotFoundException(__('Invalid product type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProductType->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The product type has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The product type could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('ProductType.' . $this->ProductType->primaryKey => $id));
			$this->request->data = $this->ProductType->find('first', $options);
		}
		$productCategories = $this->ProductType->ProductCategory->find('list');
		$this->set(compact('productCategories'));
		
		$this->loadModel('AccountingCode');
		$inventoryAccountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INVENTORY);
		$accountingCodes=$this->AccountingCode->find('list',array(
			'conditions'=>array(
				'AccountingCode.lft >'=>$inventoryAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$inventoryAccountingCode['AccountingCode']['rght'],
				'AccountingCode.bool_main'=>true,
			),
		));
		$this->set(compact('accountingCodes'));
		
		$aco_name="Products/index";		
		$bool_product_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_index_permission'));
		$aco_name="Products/add";		
		$bool_product_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ProductType->id = $id;
		if (!$this->ProductType->exists()) {
			throw new NotFoundException(__('Invalid product type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProductType->delete()) {
			$this->Session->setFlash(__('The product type has been deleted.'));
		} else {
			$this->Session->setFlash(__('The product type could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
