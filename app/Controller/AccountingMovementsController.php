<?php
App::uses('AppController', 'Controller');
/**
 * AccountingMovements Controller
 *
 * @property AccountingMovement $AccountingMovement
 * @property PaginatorComponent $Paginator
 */
class AccountingMovementsController extends AppController {

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
		$this->AccountingMovement->recursive = 0;
		$this->set('accountingMovements', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->AccountingMovement->exists($id)) {
			throw new NotFoundException(__('Invalid accounting movement'));
		}
		$options = array('conditions' => array('AccountingMovement.' . $this->AccountingMovement->primaryKey => $id));
		$this->set('accountingMovement', $this->AccountingMovement->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->AccountingMovement->create();
			if ($this->AccountingMovement->save($this->request->data)) {
				$this->Session->setFlash(__('The accounting movement has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The accounting movement could not be saved. Please, try again.'));
			}
		}
		$accountingRegisters = $this->AccountingMovement->AccountingRegister->find('list');
		$accountingCodes = $this->AccountingMovement->AccountingCode->find('list');
		$currencies = $this->AccountingMovement->Currency->find('list');
		$this->set(compact('accountingRegisters', 'accountingCodes', 'currencies'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->AccountingMovement->exists($id)) {
			throw new NotFoundException(__('Invalid accounting movement'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->AccountingMovement->save($this->request->data)) {
				$this->Session->setFlash(__('The accounting movement has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The accounting movement could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('AccountingMovement.' . $this->AccountingMovement->primaryKey => $id));
			$this->request->data = $this->AccountingMovement->find('first', $options);
		}
		$accountingRegisters = $this->AccountingMovement->AccountingRegister->find('list');
		$accountingCodes = $this->AccountingMovement->AccountingCode->find('list');
		$currencies = $this->AccountingMovement->Currency->find('list');
		$this->set(compact('accountingRegisters', 'accountingCodes', 'currencies','id'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->AccountingMovement->id = $id;
		if (!$this->AccountingMovement->exists()) {
			throw new NotFoundException(__('Invalid accounting movement'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->AccountingMovement->delete()) {
			$this->Session->setFlash(__('The accounting movement has been deleted.'));
		} else {
			$this->Session->setFlash(__('The accounting movement could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
