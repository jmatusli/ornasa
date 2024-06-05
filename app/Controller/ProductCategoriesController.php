<?php
App::uses('AppController', 'Controller');
/**
 * ProductCategories Controller
 *
 * @property ProductCategory $ProductCategory
 * @property PaginatorComponent $Paginator
 */
class ProductCategoriesController extends AppController {

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
		$this->ProductCategory->recursive = 0;
		$this->set('productCategories', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->ProductCategory->exists($id)) {
			throw new NotFoundException(__('Invalid product category'));
		}
		$options = array('conditions' => array('ProductCategory.' . $this->ProductCategory->primaryKey => $id));
		$this->set('productCategory', $this->ProductCategory->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->ProductCategory->create();
			if ($this->ProductCategory->save($this->request->data)) {
				$this->recordUserAction($this->ProductCategory->id,"add",null);
				$this->Session->setFlash(__('The product category has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The product category could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
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
		if (!$this->ProductCategory->exists($id)) {
			throw new NotFoundException(__('Invalid product category'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProductCategory->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The product category has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The product category could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('ProductCategory.' . $this->ProductCategory->primaryKey => $id));
			$this->request->data = $this->ProductCategory->find('first', $options);
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
		$this->ProductCategory->id = $id;
		if (!$this->ProductCategory->exists()) {
			throw new NotFoundException(__('Invalid product category'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProductCategory->delete()) {
			$this->Session->setFlash(__('The product category has been deleted.'));
		} else {
			$this->Session->setFlash(__('The product category could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
