<?php
App::uses('AppController', 'Controller');
/**
 * CashReceiptTypes Controller
 *
 * @property CashReceiptType $CashReceiptType
 * @property PaginatorComponent $Paginator
 */
class CashReceiptTypesController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->CashReceiptType->recursive = 0;
		$this->set('cashReceiptTypes', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->CashReceiptType->exists($id)) {
			throw new NotFoundException(__('Invalid cash receipt type'));
		}
		$options = array('conditions' => array('CashReceiptType.' . $this->CashReceiptType->primaryKey => $id));
		$this->set('cashReceiptType', $this->CashReceiptType->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->CashReceiptType->create();
			if ($this->CashReceiptType->save($this->request->data)) {
				$this->recordUserAction($this->CashReceiptType->id,null,null);
				$this->Session->setFlash(__('The cash receipt type has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The cash receipt type could not be saved. Please, try again.'));
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->CashReceiptType->exists($id)) {
			throw new NotFoundException(__('Invalid cash receipt type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->CashReceiptType->save($this->request->data)) {
				$this->recordUserAction($this->CashReceipt->id,null,null);
				$this->Session->setFlash(__('The cash receipt type has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The cash receipt type could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('CashReceiptType.' . $this->CashReceiptType->primaryKey => $id));
			$this->request->data = $this->CashReceiptType->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->CashReceiptType->id = $id;
		if (!$this->CashReceiptType->exists()) {
			throw new NotFoundException(__('Invalid cash receipt type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->CashReceiptType->delete()) {
			$this->Session->setFlash(__('The cash receipt type has been deleted.'));
		} else {
			$this->Session->setFlash(__('The cash receipt type could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
