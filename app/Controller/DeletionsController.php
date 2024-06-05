<?php
App::uses('AppController', 'Controller');
/**
 * Deletions Controller
 *
 * @property Deletion $Deletion
 * @property PaginatorComponent $Paginator
 */
class DeletionsController extends AppController {

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
		$this->Deletion->recursive = 0;
		$this->set('deletions', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Deletion->exists($id)) {
			throw new NotFoundException(__('Invalid deletion'));
		}
		$options = array('conditions' => array('Deletion.' . $this->Deletion->primaryKey => $id));
		$this->set('deletion', $this->Deletion->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Deletion->create();
			if ($this->Deletion->save($this->request->data)) {
				$this->Session->setFlash(__('The deletion has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The deletion could not be saved. Please, try again.'));
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
		if (!$this->Deletion->exists($id)) {
			throw new NotFoundException(__('Invalid deletion'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Deletion->save($this->request->data)) {
				$this->Session->setFlash(__('The deletion has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The deletion could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Deletion.' . $this->Deletion->primaryKey => $id));
			$this->request->data = $this->Deletion->find('first', $options);
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
		$this->Deletion->id = $id;
		if (!$this->Deletion->exists()) {
			throw new NotFoundException(__('Invalid deletion'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Deletion->delete()) {
			$this->Session->setFlash(__('The deletion has been deleted.'));
		} else {
			$this->Session->setFlash(__('The deletion could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
