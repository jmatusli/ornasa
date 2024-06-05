<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');
class ProductNaturesController extends AppController {

  public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel');

  public function resumen() {
		$this->ProductNature->recursive = -1;
		
		$productNatureCount=	$this->ProductNature->find('count', [
			'fields'=>['ProductNature.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [
			],
			'contain'=>[				
			],
			'limit'=>($productNatureCount!=0?$productNatureCount:1),
      'order'=>'ProductNature.list_order',
		] ;

		$productNatures = $this->Paginator->paginate('ProductNature');
		$this->set(compact('productNatures'));
	}

  public function guardarResumenNaturalezas($fileName) {
		$exportData=$_SESSION['resumenNaturalezas'];
		$this->set(compact('exportData','fileName'));
	}

	public function detalle($id = null) {
		if (!$this->ProductNature->exists($id)) {
			throw new NotFoundException(__('Invalid product nature'));
		}
		
    $options = [
      'conditions' => [
        'ProductNature.id' => $id,
      ],
    ];
		$this->set('productNature', $this->ProductNature->find('first', $options));
	}

	public function crear() {
		if ($this->request->is('post')) {
			$this->ProductNature->create();
			if ($this->ProductNature->save($this->request->data)) {
				$this->Session->setFlash(__('The product nature has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen'] );
			} 
      else {
        pr($e);
				$this->Session->setFlash(__('The product nature could not be saved. Please, try again.'), 'default',['class' => 'error-message'] );
			}
		}
	}


	public function editar($id = null) {
		if (!$this->ProductNature->exists($id)) {
			throw new NotFoundException(__('Invalid product nature'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this->ProductNature->save($this->request->data)) {
				$this->Session->setFlash(__('The product nature has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(array('action' => 'resumen'));
			} 
      else {
				$this->Session->setFlash(__('The product nature could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} else {
			$options = array('conditions' => array('ProductNature.id' => $id));
			$this->request->data = $this->ProductNature->find('first', $options);
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
		$this->ProductNature->id = $id;
		if (!$this->ProductNature->exists()) {
			throw new NotFoundException(__('Invalid product nature'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProductNature->delete()) {
			$this->Session->setFlash(__('The product nature has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The product nature could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
