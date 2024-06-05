<?php
App::uses('AppController', 'Controller');
/**
 * AccountingRegisterInvoices Controller
 *
 * @property AccountingRegisterInvoice $AccountingRegisterInvoice
 * @property PaginatorComponent $Paginator
 */
class AccountingRegisterInvoicesController extends AppController {

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
		$this->AccountingRegisterInvoice->recursive = 0;
		$this->set('accountingRegisterInvoices', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->AccountingRegisterInvoice->exists($id)) {
			throw new NotFoundException(__('Invalid accounting register invoice'));
		}
		$options = array('conditions' => array('AccountingRegisterInvoice.' . $this->AccountingRegisterInvoice->primaryKey => $id));
		$this->set('accountingRegisterInvoice', $this->AccountingRegisterInvoice->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->AccountingRegisterInvoice->create();
			if ($this->AccountingRegisterInvoice->save($this->request->data)) {
				$this->Session->setFlash(__('The accounting register invoice has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The accounting register invoice could not be saved. Please, try again.'));
			}
		}
		$accountingRegisters = $this->AccountingRegisterInvoice->AccountingRegister->find('list');
		$invoices = $this->AccountingRegisterInvoice->Invoice->find('list');
		$this->set(compact('accountingRegisters', 'invoices'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->AccountingRegisterInvoice->exists($id)) {
			throw new NotFoundException(__('Invalid accounting register invoice'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->AccountingRegisterInvoice->save($this->request->data)) {
				$this->Session->setFlash(__('The accounting register invoice has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The accounting register invoice could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('AccountingRegisterInvoice.' . $this->AccountingRegisterInvoice->primaryKey => $id));
			$this->request->data = $this->AccountingRegisterInvoice->find('first', $options);
		}
		$accountingRegisters = $this->AccountingRegisterInvoice->AccountingRegister->find('list');
		$invoices = $this->AccountingRegisterInvoice->Invoice->find('list');
		$this->set(compact('accountingRegisters', 'invoices'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->AccountingRegisterInvoice->id = $id;
		if (!$this->AccountingRegisterInvoice->exists()) {
			throw new NotFoundException(__('Invalid accounting register invoice'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->AccountingRegisterInvoice->delete()) {
			$this->Session->setFlash(__('The accounting register invoice has been deleted.'));
		} else {
			$this->Session->setFlash(__('The accounting register invoice could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
