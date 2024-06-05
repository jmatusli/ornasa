<?php
App::uses('AppController', 'Controller');
/**
 * AccountingRegisterTypes Controller
 *
 * @property AccountingRegisterType $AccountingRegisterType
 * @property PaginatorComponent $Paginator
 */
class AccountingRegisterTypesController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->AccountingRegisterType->recursive = 0;
		$this->set('accountingRegisterTypes', $this->Paginator->paginate());
		
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

	public function view($id = null) {
		if (!$this->AccountingRegisterType->exists($id)) {
			throw new NotFoundException(__('Invalid accounting register type'));
		}
		$options = array(
			'conditions' => array(
				'AccountingRegisterType.id' => $id,
			),
		);
		$this->set('accountingRegisterType', $this->AccountingRegisterType->find('first', $options));
		
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->AccountingRegisterType->create();
			if ($this->AccountingRegisterType->save($this->request->data)) {
				$this->recordUserAction($this->AccountingRegisterType->id,null,null);
				$this->Session->setFlash(__('The accounting register type has been saved.'), 'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The accounting register type could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

	public function edit($id = null) {
		if (!$this->AccountingRegisterType->exists($id)) {
			throw new NotFoundException(__('Invalid accounting register type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->AccountingRegisterType->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The accounting register type has been saved.'), 'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The accounting register type could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('AccountingRegisterType.' . $this->AccountingRegisterType->primaryKey => $id));
			$this->request->data = $this->AccountingRegisterType->find('first', $options);
		}
		
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->AccountingRegisterType->id = $id;
		if (!$this->AccountingRegisterType->exists()) {
			throw new NotFoundException(__('Invalid accounting register type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->AccountingRegisterType->delete()) {
			$this->Session->setFlash(__('The accounting register type has been deleted.'));
		} else {
			$this->Session->setFlash(__('The accounting register type could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
