<?php
App::uses('AppController', 'Controller');
/**
 * CashReceiptInvoices Controller
 *
 * @property CashReceiptInvoice $CashReceiptInvoice
 * @property PaginatorComponent $Paginator
 */
class CashReceiptInvoicesController extends AppController {

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
		$this->CashReceiptInvoice->recursive = 0;
		$this->set('cashReceiptInvoices', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->CashReceiptInvoice->exists($id)) {
			throw new NotFoundException(__('Invalid cash receipt invoice'));
		}
		$options = array('conditions' => array('CashReceiptInvoice.' . $this->CashReceiptInvoice->primaryKey => $id));
		$this->set('cashReceiptInvoice', $this->CashReceiptInvoice->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->CashReceiptInvoice->create();
			if ($this->CashReceiptInvoice->save($this->request->data)) {
				$this->Session->setFlash(__('The cash receipt invoice has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The cash receipt invoice could not be saved. Please, try again.'));
			}
		}
		$cashReceipts = $this->CashReceiptInvoice->CashReceipt->find('list');
		$invoices = $this->CashReceiptInvoice->Invoice->find('list');
		$this->set(compact('cashReceipts', 'invoices'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->CashReceiptInvoice->exists($id)) {
			throw new NotFoundException(__('Invalid cash receipt invoice'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->CashReceiptInvoice->save($this->request->data)) {
				$this->Session->setFlash(__('The cash receipt invoice has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The cash receipt invoice could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('CashReceiptInvoice.' . $this->CashReceiptInvoice->primaryKey => $id));
			$this->request->data = $this->CashReceiptInvoice->find('first', $options);
		}
		$cashReceipts = $this->CashReceiptInvoice->CashReceipt->find('list');
		$invoices = $this->CashReceiptInvoice->Invoice->find('list');
		$this->set(compact('cashReceipts', 'invoices'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->CashReceiptInvoice->id = $id;
		if (!$this->CashReceiptInvoice->exists()) {
			throw new NotFoundException(__('Invalid cash receipt invoice'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->CashReceiptInvoice->delete()) {
			$this->Session->setFlash(__('The cash receipt invoice has been deleted.'));
		} else {
			$this->Session->setFlash(__('The cash receipt invoice could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
