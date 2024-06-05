<?php
App::uses('AppController', 'Controller');
/**
 * AccountingRegisterCashReceipts Controller
 *
 * @property AccountingRegisterCashReceipt $AccountingRegisterCashReceipt
 * @property PaginatorComponent $Paginator
 */
class AccountingRegisterCashReceiptsController extends AppController {

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
		$this->AccountingRegisterCashReceipt->recursive = 0;
		$this->set('accountingRegisterCashReceipts', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->AccountingRegisterCashReceipt->exists($id)) {
			throw new NotFoundException(__('Invalid accounting register cash receipt'));
		}
		$options = array('conditions' => array('AccountingRegisterCashReceipt.' . $this->AccountingRegisterCashReceipt->primaryKey => $id));
		$this->set('accountingRegisterCashReceipt', $this->AccountingRegisterCashReceipt->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->AccountingRegisterCashReceipt->create();
			if ($this->AccountingRegisterCashReceipt->save($this->request->data)) {
				$this->Session->setFlash(__('The accounting register cash receipt has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The accounting register cash receipt could not be saved. Please, try again.'));
			}
		}
		$accountingRegisters = $this->AccountingRegisterCashReceipt->AccountingRegister->find('list');
		$cashReceipts = $this->AccountingRegisterCashReceipt->CashReceipt->find('list');
		$this->set(compact('accountingRegisters', 'cashReceipts'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->AccountingRegisterCashReceipt->exists($id)) {
			throw new NotFoundException(__('Invalid accounting register cash receipt'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->AccountingRegisterCashReceipt->save($this->request->data)) {
				$this->Session->setFlash(__('The accounting register cash receipt has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The accounting register cash receipt could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('AccountingRegisterCashReceipt.' . $this->AccountingRegisterCashReceipt->primaryKey => $id));
			$this->request->data = $this->AccountingRegisterCashReceipt->find('first', $options);
		}
		$accountingRegisters = $this->AccountingRegisterCashReceipt->AccountingRegister->find('list');
		$cashReceipts = $this->AccountingRegisterCashReceipt->CashReceipt->find('list');
		$this->set(compact('accountingRegisters', 'cashReceipts'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->AccountingRegisterCashReceipt->id = $id;
		if (!$this->AccountingRegisterCashReceipt->exists()) {
			throw new NotFoundException(__('Invalid accounting register cash receipt'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->AccountingRegisterCashReceipt->delete()) {
			$this->Session->setFlash(__('The accounting register cash receipt has been deleted.'));
		} else {
			$this->Session->setFlash(__('The accounting register cash receipt could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
