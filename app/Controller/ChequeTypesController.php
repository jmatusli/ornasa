<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ChequeTypesController extends AppController {

	public $components = array('Paginator');
	public $helpers = array('PhpExcel'); 

	public function index() {
		$this->ChequeType->recursive = 0;
		$this->set('chequeTypes', $this->Paginator->paginate());
	}

	public function view($id = null) {
		if (!$this->ChequeType->exists($id)) {
			throw new NotFoundException(__('Invalid cheque type'));
		}
		$options = array('conditions' => array('ChequeType.' . $this->ChequeType->primaryKey => $id));
		$this->set('chequeType', $this->ChequeType->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->ChequeType->create();
			if ($this->ChequeType->save($this->request->data)) {
				$this->recordUserAction($this->ChequeType->id,null,null);
				$this->Session->setFlash(__('The cheque type has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The cheque type could not be saved. Please, try again.'), 'default',array('class' => 'error-message')); 
			}
		}
		$accountingCodes = $this->ChequeType->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname'
		));
		$this->set(compact('accountingCodes'));
	}

	public function edit($id = null) {
		if (!$this->ChequeType->exists($id)) {
			throw new NotFoundException(__('Invalid cheque type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ChequeType->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The cheque type has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The cheque type could not be saved. Please, try again.'), 'default',array('class' => 'error-message')); 
			}
		} 
		else {
			$options = array('conditions' => array('ChequeType.' . $this->ChequeType->primaryKey => $id));
			$this->request->data = $this->ChequeType->find('first', $options);
		}
		$accountingCodes = $this->ChequeType->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname'
		));
		$this->set(compact('accountingCodes'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ChequeType->id = $id;
		if (!$this->ChequeType->exists()) {
			throw new NotFoundException(__('Invalid cheque type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ChequeType->delete()) {
			$this->Session->setFlash(__('The cheque type has been deleted.'));
		} else {
			$this->Session->setFlash(__('The cheque type could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
