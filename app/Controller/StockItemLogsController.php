<?php
App::uses('AppController', 'Controller');
/**
 * StockItemLogs Controller
 *
 * @property StockItemLog $StockItemLog
 * @property PaginatorComponent $Paginator
 */
class StockItemLogsController extends AppController {

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
		$this->StockItemLog->recursive = 0;
		$this->set('stockItemLogs', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->StockItemLog->exists($id)) {
			throw new NotFoundException(__('Invalid stock item log'));
		}
		$options = array('conditions' => array('StockItemLog.' . $this->StockItemLog->primaryKey => $id));
		$this->set('stockItemLog', $this->StockItemLog->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->StockItemLog->create();
			if ($this->StockItemLog->save($this->request->data)) {
				$this->Session->setFlash(__('The stock item log has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The stock item log could not be saved. Please, try again.'));
			}
		}
		$stockItems = $this->StockItemLog->StockItem->find('list');
		$stockMovements = $this->StockItemLog->StockMovement->find('list');
		$productionMovements = $this->StockItemLog->ProductionMovement->find('list');
		$products = $this->StockItemLog->Product->find('list');
		$this->set(compact('stockItems', 'stockMovements', 'productionMovements', 'products'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->StockItemLog->exists($id)) {
			throw new NotFoundException(__('Invalid stock item log'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->StockItemLog->save($this->request->data)) {
				$this->Session->setFlash(__('The stock item log has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The stock item log could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('StockItemLog.' . $this->StockItemLog->primaryKey => $id));
			$this->request->data = $this->StockItemLog->find('first', $options);
		}
		$stockItems = $this->StockItemLog->StockItem->find('list');
		$stockMovements = $this->StockItemLog->StockMovement->find('list');
		$productionMovements = $this->StockItemLog->ProductionMovement->find('list');
		$products = $this->StockItemLog->Product->find('list');
		$this->set(compact('stockItems', 'stockMovements', 'productionMovements', 'products'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->StockItemLog->id = $id;
		if (!$this->StockItemLog->exists()) {
			throw new NotFoundException(__('Invalid stock item log'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->StockItemLog->delete()) {
			$this->Session->setFlash(__('The stock item log has been deleted.'));
		} else {
			$this->Session->setFlash(__('The stock item log could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
