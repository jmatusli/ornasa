<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller','PHPExcel');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');
/**
 * StockMovements Controller
 *
 * @property StockMovement $StockMovement
 * @property PaginatorComponent $Paginator
 */
class StockMovementsController extends AppController {
	
	public $components = ['Paginator','RequestHandler'];
	public $helpers = ['PhpExcel']; 

  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('registeradjustmentmovement','registerbottleadjustmentmovements','sortByMovementDate','guardarReporteVentaProductoPorCliente');		
	}
  
  public function registeradjustmentmovement() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
    $this->loadModel('Product');
    $this->loadModel('StockItem');
    $this->loadModel('StockItemLog');
    
    $adjustmentDate=trim($_POST['adjustmentDate'])." 00:00:00";
    
    $productId=trim($_POST['productId']);
    $previousQuantity=trim($_POST['previousQuantity']);
    $updatedQuantity=trim($_POST['updatedQuantity']);
    $warehouseId=trim($_POST['warehouseId']);
    
    $this->Product->recursive=-1;
    $this->StockItem->recursive=-1;
    
    $linkedProduct=$this->Product->find('first',[
      'conditions'=>['Product.id'=>$productId,],
    ]);
    $productName=$linkedProduct['Product']['name'];
		$productCategoryId = $this->Product->getProductCategoryId($productId);
    $productTypeId = $this->Product->getProductTypeId($productId);
    
    //if (($productCategoryId !== CATEGORY_PRODUCED && $updatedQuantity>$previousQuantity) || $updatedQuantity < 0){
    if ($updatedQuantity < 0 ||  $adjustmentDate > date('Y-m-d 23:59:59')){
      return -1;
    }
    
    $productionResultCodeId =0;
    $rawMaterialId=0;
    //if ($productCategoryId == CATEGORY_PRODUCED){
      // $productionResultCodeId = $product['production_result_code_id'];
      // $rawMaterialId = $product['raw_material_id'];
    // }
    if ($productTypeId == PRODUCT_TYPE_INJECTION_OUTPUT){
      $productionResultCodeId = PRODUCTION_RESULT_CODE_A;
    }
    
    $userName=$this->Session->read('User.username');
    $adjustmentCode=$this->StockMovement->getAdjustmentCode($userName);
    
		$datasource=$this->StockMovement->getDataSource();
    $datasource->begin();
    $errorMessage='';
    try {
      //$currentDateTime=new DateTime();
      //$adjustmentDate = $currentDateTime->format('Y-m-d H:i:s');
      $productQuantity = $previousQuantity - $updatedQuantity;
      if ($productQuantity<0){
        $currentStock=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($productId,$adjustmentDate,$warehouseId,$rawMaterialId,$productionResultCodeId);
        if ($currentStock['quantity'] > 0){
          $averageCost=$currentStock['value']/$currentStock['quantity'];
        }
        else {            
          //pr($currentStock);
          $adjustmentSuccess=-1;
          $errorMessage.="No se podía establecer un costo como ninguno de estos materiales era presente en bodega, ni su preforma";
          echo "problema buscando el lote existente del producto";
          throw new Exception();
        }
          
        $stockItemData=[
          'name'=>"Lote creado por ajuste hacia arriba",
          'description'=>"Lote creado por ajuste hacia arriba",
          'stockitem_creation_date'=>$adjustmentDate,
          'product_id'=>$productId,
          'product_unit_price'=>$averageCost,
          'original_quantity'=>abs($productQuantity),
          'remaining_quantity'=>abs($productQuantity),
          'production_result_code_id'=>$productionResultCodeId,
          //'raw_material_id'=>$rawMaterialId,
          'warehouse_id'=>$warehouseId,
        ];
        $this->StockItem->create();
        if (!$this->StockItem->save($stockItemData)) {
          pr($this->validateErrors($this->StockItem));
          $adjustmentSuccess=-1;
          $errorMessage+="No se podía guardar el lote";
          throw new Exception();
        }
        $stockItemId=$this->StockItem->id;
        
        $stockMovementData=[
          'movement_date'=>$adjustmentDate,
          'bool_input'=>true,
          'name'=>$adjustmentDate.$productName,
          'description'=>"ajuste hacia arriba",
          'order_id'=>-1234,
          'stockitem_id'=>$stockItemId,
          'product_id'=>$productId,
          'product_quantity'=>abs($productQuantity),
          'product_unit_price'=>$averageCost,
          'product_total_price'=>$averageCost*abs($productQuantity),
          'production_result_code_id'=>$productionResultCodeId,
          'bool_adjustment'=>true,
          'adjustment_code'=>$adjustmentCode,
        ];
        //pr($stockMovementData);
        $this->StockMovement->create();
        if (!$this->StockMovement->save($stockMovementData)) {
          pr($this->validateErrors($this->StockMovement));
          $adjustmentSuccess=-1;
          $errorMessage+="No se podía registrar la adición de materiales";
          throw new Exception();
        }
        $stockMovementId=$this->StockMovement->id;
                
        $stockItemLogData=[
          'stockitem_id'=>$stockItemId,
          'stock_movement_id'=>$stockMovementId,
          'stockitem_date'=>$adjustmentDate,
          'product_id'=>$productId,
          'product_unit_price'=>$averageCost,
          'product_quantity'=>abs($productQuantity),
          'production_result_code_id'=>$productionResultCodeId,
          'warehouse_id'=>$warehouseId,
        ];
        $this->StockItemLog->create();
        if (!$this->StockItemLog->save($stockItemLogData)) {
          pr($this->validateErrors($this->StockItemLog));
          $adjustmentSuccess=-1;
          $errorMessage+="No se podía guardar el historial de lote";
          throw new Exception();
        }
        
        $this->recordUserActivity($this->Session->read('User.username'),"Movimiento de ajuste");
      }
      else {
        if ($productCategoryId === CATEGORY_PRODUCED){
          $usedMaterials= $this->StockItem->getFinishedMaterialsForSale($product_id,$production_result_code_id,$product_quantity,$raw_material_id,$saleDateAsString,$warehouseId);		
        }
        else {
          $usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$adjustmentDate,$warehouseId);		
        }  
                
        for ($k=0;$k<count($usedMaterials);$k++){
          $materialUsed=$usedMaterials[$k];
          $stockItemId=$materialUsed['id'];
          $quantityPresent=$materialUsed['quantity_present'];
          $quantityUsed=$materialUsed['quantity_used'];
          $quantityRemaining=$materialUsed['quantity_remaining'];
          if (!$this->StockItem->exists($stockItemId)) {
            throw new NotFoundException(__('Invalid StockItem'));
          }
          $linkedStockItem=$this->StockItem->find('first',[
            'conditions'=>['StockItem.id'=>$stockItemId,],
          ]);
          $productUnitPrice=$linkedStockItem['StockItem']['product_unit_price'];
          $message="Se ajustó lote ".$productName." (Cantidad:".$quantityUsed.") el ".$adjustmentDate;
          
          $StockItemData=[];
          $StockItemData['id']=$stockItemId;
          $StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
          $StockItemData['remaining_quantity']=$quantityRemaining;
          if (!$this->StockItem->save($StockItemData)) {
            echo "problema al guardar el lote";
            pr($this->validateErrors($this->StockItem));
            throw new Exception();
          }
          
          $message="Se ajustó ".$productName." (Cantidad:".$quantityUsed.", de un total de ".$productQuantity.") para ajuste de ".$adjustmentDate;
          
          $stockMovementData=[];
          $stockMovementData['movement_date']=$adjustmentDate;
          $stockMovementData['bool_input']=false;
          
          $stockMovementData['name']=$adjustmentDate.$productName;
          $stockMovementData['description']=$message;
          $stockMovementData['order_id']=-1234;
          $stockMovementData['stockitem_id']=$stockItemId;
          $stockMovementData['product_id']=$productId;
          $stockMovementData['product_quantity']=$quantityUsed;
          $stockMovementData['product_unit_price']=$productUnitPrice;
          $stockMovementData['product_total_price']=$productUnitPrice*$quantityUsed;
          $stockMovementData['production_result_code_id']=$productionResultCodeId;
          $stockMovementData['bool_adjustment']=true;
          $stockMovementData['adjustment_code']=$adjustmentCode;
          $this->StockMovement->create();
          if (!$this->StockMovement->save($stockMovementData)) {
            echo "problema al guardar el movimiento de lote";
            pr($this->validateErrors($this->StockMovement));
            throw new Exception();
          }
        
          $this->recreateStockItemLogs($stockItemId);
          
          $this->recordUserActivity($this->Session->read('User.username'),"Se registró un movimiento de ajuste de inventario de producto ".$productName." de cantidad inicial ".$previousQuantity." a cantidad nueva ".$updatedQuantity);
        }
      }
             
      $datasource->commit();
    
      //$this->recordUserAction($this->StockMovement->id,"registerAdjustment",null);
      //$this->Session->setFlash(__('Se guardó el ajuste  de inventario de producto '.$productName.' a cantidad '.$updatedQuantity),'default',['class' => 'success']);
                
      return $updatedQuantity;
    } 
    catch(Exception $e){
      $datasource->rollback();
      //pr($e);
      //$this->Session->setFlash(__('No se podía realizar el ajuste de inventario.'), 'default',['class' => 'error-message']);
      return -1;
    }
    
    return 1000;
  }
  
  public function registerbottleadjustmentmovements() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
    $this->loadModel('Product');
    $this->loadModel('StockItem');
    $this->loadModel('StockItemLog');
    
    $this->loadModel('Warehouse');
    $this->loadModel('PlantProductionResultCode');
    
    $this->StockItem->recursive=-1;
    
    $adjustmentSuccess=1;
    $errorMessage="";
    $qtyA=0;
    $qtyB=0;
    $qtyC=0;
    
    $adjustmentDate=trim($_POST['adjustmentDate'])." 00:00:00";
    
    $productId=trim($_POST['productId']);
    $rawMaterialId=trim($_POST['rawMaterialId']);
    
    $originalQuantityA=trim($_POST['originalQuantityA']);
    $updatedQuantityA=trim($_POST['updatedQuantityA']);
    $originalQuantityB=trim($_POST['originalQuantityB']);
    $updatedQuantityB=trim($_POST['updatedQuantityB']);
    $originalQuantityC=trim($_POST['originalQuantityC']);
    $updatedQuantityC=trim($_POST['updatedQuantityC']);
    
    $originalQuantityCombined=trim($_POST['originalQuantityCombined']);
    $updatedQuantityCombined=trim($_POST['updatedQuantityCombined']);
    
    $warehouseId=trim($_POST['warehouseId']);
    
    $plantId=$this->Warehouse->getPlantId($warehouseId);
    $productionResultCodes=$this->PlantProductionResultCode->getProductionResultCodesForPlant($plantId);
    
    $productName=$this->Product->getProductName($productId);
    $rawMaterialName=$this->Product->getProductName($rawMaterialId);
    $productCategoryId = $this->Product->getProductCategoryId($productId);
    
    //if ($updatedQuantityCombined > $originalQuantityCombined){
    //  $adjustmentSuccess=-1;
    //  $errorMessage="La cantidad actu de A+B+C ".$updatedQuantityCombined." supera la cantidad original ".$updatedQuantityCombined.".   Por favor corregir esto.";
    //}
    //elseif ($updatedQuantityA < 0){
    
    if ($adjustmentDate > date('Y-m-d 23:59:59')){
      $adjustmentSuccess=-1;
      $errorMessage="La fecha del ajuste no puede estar en el futuro!  No se guardó el ajuste.";
    }
    elseif ($updatedQuantityA < 0){  
      $adjustmentSuccess=-1;
      $errorMessage="La cantidad nueva de A ".$updatedQuantityA." es negativa.   Por favor corregir esto.";
    }
    elseif ($updatedQuantityB < 0){
      $adjustmentSuccess=-1;
      $errorMessage="La cantidad nueva de B ".$updatedQuantityB." es negativa.   Por favor corregir esto.";
    }
    elseif ($updatedQuantityC < 0){
      $adjustmentSuccess=-1;
      $errorMessage="La cantidad nueva de A ".$updatedQuantityC." es negativa.   Por favor corregir esto.";
    }
    else {
      $datasource=$this->StockMovement->getDataSource();
      $datasource->begin();
      try {
        //$currentDateTime=new DateTime();
        //$adjustmentDate = $currentDateTime->format('Y-m-d H:i:s');
        
        $productQuantityA = $originalQuantityA - $updatedQuantityA;
        $productQuantityB= $originalQuantityB - $updatedQuantityB;
        $productQuantityC = $originalQuantityC - $updatedQuantityC;
        /*
        $productQuantities=[];
        foreach ($productionResultCodes as $productionResultCodeId => $productionResultCodeName){
          $productQuantities[$productionResultCodeId]=0;
        }
        */
        $usedMaterialsA=[];
        $usedMaterialsB=[];
        $usedMaterialsC=[];
        $boolReducedA=true;
        $boolReducedB=true;
        $boolReducedC=true;
        $boolChangedA=false;
        $boolChangedB=false;
        $boolChangedC=false;
        if ($originalQuantityA > $updatedQuantityA){
          $usedMaterialsA= $this->StockItem->getFinishedMaterialsForSale($productId,PRODUCTION_RESULT_CODE_A,$productQuantityA,$rawMaterialId,$adjustmentDate,$warehouseId);		
        }
        elseif ($originalQuantityA < $updatedQuantityA){
          $boolReducedA=false;
          $boolChangedA=true;
        }
        if ($originalQuantityB > $updatedQuantityB){
          $usedMaterialsB= $this->StockItem->getFinishedMaterialsForSale($productId,PRODUCTION_RESULT_CODE_B,$productQuantityB,$rawMaterialId,$adjustmentDate,$warehouseId);		
        }
        elseif ($originalQuantityB < $updatedQuantityB){
          $boolReducedB=false;
          $boolChangedB=true;
        }
        if ($originalQuantityC > $updatedQuantityC){
          $usedMaterialsC= $this->StockItem->getFinishedMaterialsForSale($productId,PRODUCTION_RESULT_CODE_C,$productQuantityC,$rawMaterialId,$adjustmentDate,$warehouseId);		
        }
        elseif ($originalQuantityC < $updatedQuantityC){
          $boolReducedC=false;
          $boolChangedC=true;
        }
        $userName=$this->Session->read('User.username');
        $adjustmentCode=$this->StockMovement->getAdjustmentCode($userName);
        
        if (!$boolReducedA || !$boolReducedB || !$boolReducedC){
          // at least one of the materials has gone up 
          /*
          // STEP 1 combine all materials that were taken out of stock
          $allUsedMaterials=[];
          if (!empty($usedMaterialsA)){
            foreach ($usedMaterialsA as $usedMaterialA){
              $allUsedMaterials[]=$usedMaterialA;
            }
          }
          if (!empty($usedMaterialsB)){
            foreach ($usedMaterialsB as $usedMaterialB){
              $allUsedMaterials[]=$usedMaterialB;
            }
          }
          if (!empty($usedMaterialsC)){
            foreach ($usedMaterialsC as $usedMaterialC){
              $allUsedMaterials[]=$usedMaterialC;
            }
          }
          //pr($allUsedMaterials);
          // STEP 2 calculate average price of materials taken out of stock
          $totalQuantityUsed=0;
          $totalPriceUsed=0;
          foreach ($allUsedMaterials as $usedMaterial){
            $totalQuantityUsed+=$usedMaterial['quantity_used'];
            $totalPriceUsed+=($usedMaterial['quantity_used']*$usedMaterial['unit_price']);
          }
          $averageCost=$totalPriceUsed/$totalQuantityUsed;
          
          // STEP 3 register stockitems and input movements using averageCost
          
          */
          $currentStockA=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($productId,$adjustmentDate,$warehouseId,$rawMaterialId,PRODUCTION_RESULT_CODE_A);
          $currentStockB=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($productId,$adjustmentDate,$warehouseId,$rawMaterialId,PRODUCTION_RESULT_CODE_B);
          $currentStockC=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($productId,$adjustmentDate,$warehouseId,$rawMaterialId,PRODUCTION_RESULT_CODE_C);
          if ($currentStockA['quantity']+$currentStockB['quantity']+ $currentStockC['quantity'] > 0){
            $averageCost=($currentStockA['value']+$currentStockB['value']+ $currentStockC['value'])/($currentStockA['quantity']+$currentStockB['quantity']+ $currentStockC['quantity']);
          }
          else {
            $currentStockRawMaterial=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($rawMaterialId,$adjustmentDate,$warehouseId,0,0);
            if ($currentStockRawMaterial['quantity'] > 0){
              $averageCost=$currentStockRawMaterial['value']/$currentStockRawMaterial['quantity'];
            }
            else {            
              $adjustmentSuccess=-1;
              $errorMessage+="No se podía establecer un costo como ninguno de estos materiales era presente en bodega, ni su preforma";
              throw new Exception();
            }
          }  
          if (!$boolReducedA){
            $stockItemData=[
              'name'=>"Lote creado por ajuste hacia arriba",
            	'description'=>"Lote creado por ajuste hacia arriba",
            	'stockitem_creation_date'=>$adjustmentDate,
            	'product_id'=>$productId,
            	'product_unit_price'=>$averageCost,
            	'original_quantity'=>abs($productQuantityA),
            	'remaining_quantity'=>abs($productQuantityA),
            	'production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
            	'raw_material_id'=>$rawMaterialId,
            	'warehouse_id'=>$warehouseId,
            ];
            $this->StockItem->create();
            if (!$this->StockItem->save($stockItemData)) {
              pr($this->validateErrors($this->StockItem));
              $adjustmentSuccess=-1;
              $errorMessage+="No se podía guardar el lote de materiales de calidad A";
              throw new Exception();
            }
            $stockItemId=$this->StockItem->id;
            
            $stockMovementData=[
              'movement_date'=>$adjustmentDate,
              'bool_input'=>true,
              'name'=>$adjustmentDate.$productName,
              'description'=>"ajuste hacia arriba",
              'order_id'=>-1234,
              'stockitem_id'=>$stockItemId,
              'product_id'=>$productId,
              'product_quantity'=>abs($productQuantityA),
              'product_unit_price'=>$averageCost,
              'product_total_price'=>$averageCost*abs($productQuantityA),
              'production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
              'bool_adjustment'=>true,
              'adjustment_code'=>$adjustmentCode,
            ];
            //pr($stockMovementData);
            $this->StockMovement->create();
            if (!$this->StockMovement->save($stockMovementData)) {
              pr($this->validateErrors($this->StockMovement));
              $adjustmentSuccess=-1;
              $errorMessage+="No se podía registrar la adición de materiales de calidad A";
              throw new Exception();
            }
            $stockMovementId=$this->StockMovement->id;
                
            $stockItemLogData=[
            	'stockitem_id'=>$stockItemId,
            	'stock_movement_id'=>$stockMovementId,
            	'stockitem_date'=>$adjustmentDate,
            	'product_id'=>$productId,
            	'product_unit_price'=>$averageCost,
            	'product_quantity'=>abs($productQuantityA),
            ];
            $this->StockItemLog->create();
            if (!$this->StockItemLog->save($stockItemLogData)) {
              pr($this->validateErrors($this->StockItemLog));
              $adjustmentSuccess=-1;
              $errorMessage+="No se podía guardar el historial de lote de materiales de calidad A";
              throw new Exception();
            }
            
            $this->recordUserActivity($this->Session->read('User.username'),"Movimiento de ajuste de calidad A");
          }
          if (!$boolReducedB){
            $stockItemData=[
              'name'=>"Lote creado por ajuste hacia arriba",
            	'description'=>"Lote creado por ajuste hacia arriba",
            	'stockitem_creation_date'=>$adjustmentDate,
            	'product_id'=>$productId,
            	'product_unit_price'=>$averageCost,
            	'original_quantity'=>abs($productQuantityB),
            	'remaining_quantity'=>abs($productQuantityB),
            	'production_result_code_id'=>PRODUCTION_RESULT_CODE_B,
            	'raw_material_id'=>$rawMaterialId,
            	'warehouse_id'=>$warehouseId,
            ];
            $this->StockItem->create();
            if (!$this->StockItem->save($stockItemData)) {
              pr($this->validateErrors($this->StockItem));
              $adjustmentSuccess=-1;
              $errorMessage+="No se podía guardar el lote de materiales de calidad B";
              throw new Exception();
            }
            $stockItemId=$this->StockItem->id;
            
            $stockMovementData=[
              'movement_date'=>$adjustmentDate,
              'bool_input'=>true,
              'name'=>$adjustmentDate.$productName,
              'description'=>"ajuste hacia arriba",
              'order_id'=>-1234,
              'stockitem_id'=>$stockItemId,
              'product_id'=>$productId,
              'product_quantity'=>abs($productQuantityB),
              'product_unit_price'=>$averageCost,
              'product_total_price'=>$averageCost*abs($productQuantityB),
              'production_result_code_id'=>PRODUCTION_RESULT_CODE_B,
              'bool_adjustment'=>true,
              'adjustment_code'=>$adjustmentCode,
            ];
            $this->StockMovement->create();
            if (!$this->StockMovement->save($stockMovementData)) {
              pr($this->validateErrors($this->StockMovement));
              $adjustmentSuccess=-1;
              $errorMessage+="No se podía registrar la adición de materiales de calidad B";
              throw new Exception();
            }
            $stockMovementId=$this->StockMovement->id;
                
            $stockItemLogData=[
            	'stockitem_id'=>$stockItemId,
            	'stock_movement_id'=>$stockMovementId,
            	'stockitem_date'=>$adjustmentDate,
            	'product_id'=>$productId,
            	'product_unit_price'=>$averageCost,
            	'product_quantity'=>abs($productQuantityB),
            ];
            $this->StockItemLog->create();
            if (!$this->StockItemLog->save($stockItemLogData)) {
              pr($this->validateErrors($this->StockItemLog));
              $adjustmentSuccess=-1;
              $errorMessage+="No se podía guardar el historial de lote de materiales de calidad B";
              throw new Exception();
            }
            
            $this->recordUserActivity($this->Session->read('User.username'),"Movimiento de ajuste de calidad B");
          }
          if (!$boolReducedC){
            $stockItemData=[
              'name'=>"Lote creado por ajuste hacia arriba",
            	'description'=>"Lote creado por ajuste hacia arriba",
            	'stockitem_creation_date'=>$adjustmentDate,
            	'product_id'=>$productId,
            	'product_unit_price'=>$averageCost,
            	'original_quantity'=>abs($productQuantityC),
            	'remaining_quantity'=>abs($productQuantityC),
            	'production_result_code_id'=>PRODUCTION_RESULT_CODE_C,
            	'raw_material_id'=>$rawMaterialId,
            	'warehouse_id'=>$warehouseId,
            ];
            $this->StockItem->create();
            if (!$this->StockItem->save($stockItemData)) {
              pr($this->validateErrors($this->StockItem));
              $adjustmentSuccess=-1;
              $errorMessage+="No se podía guardar el lote de materiales de calidad C";
              throw new Exception();
            }
            $stockItemId=$this->StockItem->id;
            
            $stockMovementData=[
              'movement_date'=>$adjustmentDate,
              'bool_input'=>true,
              'name'=>$adjustmentDate.$productName,
              'description'=>"ajuste hacia arriba",
              'order_id'=>-1234,
              'stockitem_id'=>$stockItemId,
              'product_id'=>$productId,
              'product_quantity'=>abs($productQuantityC),
              'product_unit_price'=>$averageCost,
              'product_total_price'=>$averageCost*abs($productQuantityC),
              'production_result_code_id'=>PRODUCTION_RESULT_CODE_C,
              'bool_adjustment'=>true,
              'adjustment_code'=>$adjustmentCode,
            ];
            $this->StockMovement->create();
            if (!$this->StockMovement->save($stockMovementData)) {
              pr($this->validateErrors($this->StockMovement));
              $adjustmentSuccess=-1;
              $errorMessage+="No se podía registrar la adición de materiales de calidad C";
              throw new Exception();
            }
            $stockMovementId=$this->StockMovement->id;
                
            $stockItemLogData=[
            	'stockitem_id'=>$stockItemId,
            	'stock_movement_id'=>$stockMovementId,
            	'stockitem_date'=>$adjustmentDate,
            	'product_id'=>$productId,
            	'product_unit_price'=>$averageCost,
            	'product_quantity'=>abs($productQuantityC),
            ];
            $this->StockItemLog->create();
            if (!$this->StockItemLog->save($stockItemLogData)) {
              pr($this->validateErrors($this->StockItemLog));
              $adjustmentSuccess=-1;
              $errorMessage+="No se podía guardar el historial de lote de materiales de calidad C";
              throw new Exception();
            }
            
            $this->recordUserActivity($this->Session->read('User.username'),"Movimiento de ajuste de calidad C");
          }
        }
        if (!empty($usedMaterialsA)){
          for ($k=0;$k<count($usedMaterialsA);$k++){
            $materialUsed=$usedMaterialsA[$k];
            $stockItemId=$materialUsed['id'];
            $quantityPresent=$materialUsed['quantity_present'];
            $quantityUsed=$materialUsed['quantity_used'];
            $quantityRemaining=$materialUsed['quantity_remaining'];
            if (!$this->StockItem->exists($stockItemId)) {
              throw new NotFoundException(__('Invalid StockItem'));
            }
            $linkedStockItem=$this->StockItem->find('first',[
              'conditions'=>['StockItem.id'=>$stockItemId,],
            ]);
            $productUnitPrice=$linkedStockItem['StockItem']['product_unit_price'];
            $message="Se ajustó lote ".$productName." (Cantidad:".$quantityUsed.") el ".$adjustmentDate;
            
            $stockItemData=[];
            $stockItemData['id']=$stockItemId;
            $stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
            $stockItemData['remaining_quantity']=$quantityRemaining;
            if (!$this->StockItem->save($stockItemData)) {
              pr($this->validateErrors($this->StockItem));
              $adjustmentSuccess=-1;
              $errorMessage+="Problema al guardar la actualización del lote de calidad A";
              throw new Exception();
            }
            $message="Se ajustó ".$productName." (Cantidad:".$quantityUsed.", de un total de ".$productQuantityA.") para ajuste de ".$adjustmentDate;
            
            $stockMovementData=[];
            $stockMovementData['movement_date']=$adjustmentDate;
            $stockMovementData['bool_input']=false;
            $stockMovementData['name']=$adjustmentDate.$productName;
            $stockMovementData['description']=$message;
            $stockMovementData['order_id']=-1234;
            $stockMovementData['stockitem_id']=$stockItemId;
            $stockMovementData['product_id']=$productId;
            $stockMovementData['product_quantity']=$quantityUsed;
            $stockMovementData['product_unit_price']=$productUnitPrice;
            $stockMovementData['product_total_price']=$productUnitPrice*$quantityUsed;
            $stockMovementData['production_result_code_id']=PRODUCTION_RESULT_CODE_A;
            $stockMovementData['bool_adjustment']=true;
            $stockMovementData['adjustment_code']=$adjustmentCode;
            
            $this->StockMovement->create();
            if (!$this->StockMovement->save($stockMovementData)) {
              pr($this->validateErrors($this->StockMovement));
              $adjustmentSuccess=-1;
              $errorMessage+="Problema al guardar el movimiento  de ajuste de calidad A";
              throw new Exception();
            }
          
            $this->recreateStockItemLogs($stockItemId);
            
            $this->recordUserActivity($this->Session->read('User.username'),"Se registró un movimiento de ajuste de inventario de producto ".$productName." ".$rawMaterialName." calidad ".PRODUCTION_RESULT_CODE_A." de cantidad inicial ".$originalQuantityA." a cantidad nueva ".$updatedQuantityA);
          }
        }
        if (!empty($usedMaterialsB)){
          for ($k=0;$k<count($usedMaterialsB);$k++){
            $materialUsed=$usedMaterialsB[$k];
            $stockItemId=$materialUsed['id'];
            $quantityPresent=$materialUsed['quantity_present'];
            $quantityUsed=$materialUsed['quantity_used'];
            $quantityRemaining=$materialUsed['quantity_remaining'];
            if (!$this->StockItem->exists($stockItemId)) {
              throw new NotFoundException(__('Invalid StockItem'));
            }
            $linkedStockItem=$this->StockItem->find('first',[
              'conditions'=>['StockItem.id'=>$stockItemId,],
            ]);
            $productUnitPrice=$linkedStockItem['StockItem']['product_unit_price'];
            $message="Se ajustó lote ".$productName." (Cantidad:".$quantityUsed.") el ".$adjustmentDate;
            
            $stockItemData=[];
            $stockItemData['id']=$stockItemId;
            $stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
            $stockItemData['remaining_quantity']=$quantityRemaining;
            if (!$this->StockItem->save($stockItemData)) {
              pr($this->validateErrors($this->StockItem));
              $adjustmentSuccess=-1;
              $errorMessage+="Problema al guardar la actualización del lote de calidad B";
              throw new Exception();
            }
            $message="Se ajustó ".$productName." (Cantidad:".$quantityUsed.", de un total de ".$productQuantityB.") para ajuste de ".$adjustmentDate;
            
            $stockMovementData=[];
            $stockMovementData['movement_date']=$adjustmentDate;
            $stockMovementData['bool_input']=false;
            $stockMovementData['name']=$adjustmentDate.$productName;
            $stockMovementData['description']=$message;
            $stockMovementData['order_id']=-1234;
            $stockMovementData['stockitem_id']=$stockItemId;
            $stockMovementData['product_id']=$productId;
            $stockMovementData['product_quantity']=$quantityUsed;
            $stockMovementData['product_unit_price']=$productUnitPrice;
            $stockMovementData['product_total_price']=$productUnitPrice*$quantityUsed;
            $stockMovementData['production_result_code_id']=PRODUCTION_RESULT_CODE_B;
            $stockMovementData['bool_adjustment']=true;
            $stockMovementData['adjustment_code']=$adjustmentCode;
            
            $this->StockMovement->create();
            if (!$this->StockMovement->save($stockMovementData)) {
              pr($this->validateErrors($this->StockMovement));
              $adjustmentSuccess=-1;
              $errorMessage+="Problema al guardar el movimiento  de ajuste de calidad B";
              throw new Exception();
            }
          
            $this->recreateStockItemLogs($stockItemId);
            
            $this->recordUserActivity($this->Session->read('User.username'),"Se registró un movimiento de ajuste de inventario de producto ".$productName." ".$rawMaterialName." calidad ".PRODUCTION_RESULT_CODE_B." de cantidad inicial ".$originalQuantityB." a cantidad nueva ".$updatedQuantityB);
          }
        }
        if (!empty($usedMaterialsC)){
          for ($k=0;$k<count($usedMaterialsC);$k++){
            $materialUsed=$usedMaterialsC[$k];
            $stockItemId=$materialUsed['id'];
            $quantityPresent=$materialUsed['quantity_present'];
            $quantityUsed=$materialUsed['quantity_used'];
            $quantityRemaining=$materialUsed['quantity_remaining'];
            if (!$this->StockItem->exists($stockItemId)) {
              throw new NotFoundException(__('Invalid StockItem'));
            }
            $linkedStockItem=$this->StockItem->find('first',[
              'conditions'=>['StockItem.id'=>$stockItemId,],
            ]);
            $productUnitPrice=$linkedStockItem['StockItem']['product_unit_price'];
            $message="Se ajustó lote ".$productName." (Cantidad:".$quantityUsed.") el ".$adjustmentDate;
            
            $stockItemData=[];
            $stockItemData['id']=$stockItemId;
            $stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
            $stockItemData['remaining_quantity']=$quantityRemaining;
            if (!$this->StockItem->save($stockItemData)) {
              pr($this->validateErrors($this->StockItem));
              $adjustmentSuccess=-1;
              $errorMessage+="Problema al guardar la actualización del lote de calidad C";
              throw new Exception();
            }
            $message="Se ajustó ".$productName." (Cantidad:".$quantityUsed.", de un total de ".$productQuantityC.") para ajuste de ".$adjustmentDate;
            
            $stockMovementData=[];
            $stockMovementData['movement_date']=$adjustmentDate;
            $stockMovementData['bool_input']=false;
            $stockMovementData['name']=$adjustmentDate.$productName;
            $stockMovementData['description']=$message;
            $stockMovementData['order_id']=-1234;
            $stockMovementData['stockitem_id']=$stockItemId;
            $stockMovementData['product_id']=$productId;
            $stockMovementData['product_quantity']=$quantityUsed;
            $stockMovementData['product_unit_price']=$productUnitPrice;
            $stockMovementData['product_total_price']=$productUnitPrice*$quantityUsed;
            $stockMovementData['production_result_code_id']=PRODUCTION_RESULT_CODE_C;
            $stockMovementData['bool_adjustment']=true;
            $stockMovementData['adjustment_code']=$adjustmentCode;
            
            $this->StockMovement->create();
            if (!$this->StockMovement->save($stockMovementData)) {
              pr($this->validateErrors($this->StockMovement));
              $adjustmentSuccess=-1;
              $errorMessage+="Problema al guardar el movimiento  de ajuste de calidad C";
              throw new Exception();
            }
          
            $this->recreateStockItemLogs($stockItemId);
            
            $this->recordUserActivity($this->Session->read('User.username'),"Se registró un movimiento de ajuste de inventario de producto ".$productName." ".$rawMaterialName." calidad ".PRODUCTION_RESULT_CODE_C." de cantidad inicial ".$originalQuantityC." a cantidad nueva ".$updatedQuantityC);
          }
        }
        
        $datasource->commit();
      
        //$this->recordUserAction($this->StockMovement->id,"registerAdjustment",null);
        //$this->Session->setFlash('Se guardó el ajuste  de inventario de producto '.$productName.' '.$rawMaterialName.' a cantidades A: '.$updatedQuantityA.', B: '.$updatedQuantityB.', C: '.$updatedQuantityC.'.','default',['class' => 'success']);
                  
        $qtyA=$updatedQuantityA;
        $qtyB=$updatedQuantityB;
        $qtyC=$updatedQuantityC;
      } 
      catch(Exception $e){
        $datasource->rollback();
        //pr($e);
        //$this->Session->setFlash(__('No se podía realizar el ajuste de inventario.'), 'default',['class' => 'error-message']);
        $adjustmentSuccess= -1;
      }
    }
    
    $result=[
      'adjustmentSuccess'=>$adjustmentSuccess,
      'errorMessage'=>$errorMessage,
      'qtyA'=>$qtyA,
      'qtyB'=>$qtyB,
      'qtyC'=>$qtyC,
      'qtyCombined'=>($qtyA+$qtyB+$qtyC),
    ];
    return json_encode($result);
  }
  
  public function resumenAjustesInventario(){
    $this->StockMovement->recursive = -1;
		
		//$currencyId=CURRENCY_USD;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			//$userId=$this->request->data['Report']['user_id'];
			//$currencyId=$this->request->data['Report']['currency_id'];
		}
		
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
			//if ($this->Session->check('currencyId')){
			//	$currencyId=$_SESSION['currencyId'];
			//}
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		//$_SESSION['currencyId']=$currencyId;
		//$_SESSION['userId']=$userId;
		
		$this->set(compact('startDate','endDate'));
		//$this->set(compact('currencyId'));
		
		$conditions=[
			'StockMovement.movement_date >='=>$startDate,
			'StockMovement.movement_date <'=>$endDatePlusOne,
      'StockMovement.bool_adjustment'=>true,
		];
		
		$adjustmentCount=	$this->StockMovement->find('count', [
			'fields'=>['StockMovement.id'],
			'conditions' => $conditions,
		]);
		
		$this->Paginator->settings = [
			'conditions' => $conditions,
			'contain'=>[				
				'Product',
        'ProductionResultCode',
        'StockItem'=>[
          'RawMaterial',
        ],
			],
      'order'=>'movement_date DESC,adjustment_code DESC',
			'limit'=>($adjustmentCount!=0?$adjustmentCount:1),
		];

		$adjustmentMovements = $this->Paginator->paginate('StockMovement');
		$this->set(compact('adjustmentMovements'));
		
		//$this->loadModel('Currency');
		//$currencies=$this->Currency->find('list');
		//$this->set(compact('currencies'));
		
    //$aco_name="PurchaseOrders/editar";		
		//$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		//$this->set(compact('bool_edit_permission'));
  }
  
  public function guardarResumenAjustesInventario(){
    $exportData=$_SESSION['ajustesInventario'];
		$this->set(compact('exportData'));
  }
  
  public function eliminarAjuste($adjustmentCode = null) {
		if (empty($adjustmentCode)) {
			throw new NotFoundException(__('No hay código de ajuste'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		
		$adjustmentMovements=$this->StockMovement->find('all', [
			'conditions' => [
				'StockMovement.adjustment_code' => $adjustmentCode,
			],
			'contain'=>[
        'StockItem'
      ],
		]);
		$flashMessage="";
		
    $datasource=$this->StockMovement->getDataSource();
    $datasource->begin();
    try {
      //delete all stockMovements, stockItems and stockItemLogs
      foreach ($adjustmentMovements as $stockMovement){
        //pr($stockMovement);
        if (!empty($stockMovement['StockItem'])){
          $stockItem['StockItem']=$stockMovement['StockItem'];
          if ($stockMovement['StockMovement']['bool_input']){
            $stockItem['StockItem']['remaining_quantity']-=$stockMovement['StockMovement']['product_quantity'];
            $stockItem['StockItem']['original_quantity']=0;
          }
          else {
            $stockItem['StockItem']['remaining_quantity']+=$stockMovement['StockMovement']['product_quantity'];
          }
          $stockItem['StockItem']['description'].="|ajuste eliminado ".$adjustmentCode;
          if (!$this->StockItem->save($stockItem)) {
            echo "problema eliminando el estado de lote";
            pr($this->validateErrors($this->StockItem));
            throw new Exception();
          }
        }
        if (!$this->StockMovement->delete($stockMovement['StockMovement']['id'])) {
          echo "Problema al eliminar el movimiento de ajuste";
          pr($this->validateErrors($this->StockMovement));
          throw new Exception();
        }
      }
      foreach ($adjustmentMovements as $stockMovement){
        if (!empty($stockMovement['StockItem']['id'])){
           $this->recreateStockItemLogs($stockMovement['StockItem']['id']);
        }				
			}
			
      $datasource->commit();
    /*
      $this->loadModel('Deletion');
      $this->Deletion->create();
      $deletionArray=[];
      $deletionArray['Deletion']['user_id']=$this->Auth->User('id');
      $deletionArray['Deletion']['reference_id']=$entry['Order']['id'];
      $deletionArray['Deletion']['reference']=$entry['Order']['order_code'];
      $deletionArray['Deletion']['type']='Order';
      $this->Deletion->save($deletionArray);
    */			
      $this->recordUserActivity($this->Session->read('User.username'),"Se eliminó el ajuste número ".$adjustmentCode);
          
      $this->Session->setFlash('Se eliminó el ajuste #'.$adjustmentCode,'default',['class' => 'success']);				
    }
    catch(Exception $e){
      $datasource->rollback();
      pr($e);
      $this->Session->setFlash('No se podía eliminar el ajuste #'.$adjustmentCode, 'default',['class' => 'error-message']);
    }
    return $this->redirect(['action' => 'resumenAjustesInventario']);
	}

  public function resumenTransferencias(){
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$this->set(compact('startDate','endDate'));
		
		$conditions=[
			'StockMovement.movement_date >='=>$startDate,
			'StockMovement.movement_date <'=>$endDatePlusOne,
      'StockMovement.bool_transfer'=>true,
		];
		
		$transferCount=	$this->StockMovement->find('count', [
			'fields'=>['StockMovement.id'],
			'conditions' => $conditions,
		]);
		
		$this->Paginator->settings = [
      'conditions' => $conditions,
			'contain'=>[				
				'Product',
        'ProductionResultCode',
        'StockItem'=>[
          'RawMaterial',
          'Warehouse',
        ],
			],
      'order'=>'movement_date DESC,transfer_code DESC',
			'limit'=>($transferCount!=0?$transferCount:1),
		];

		$transferMovements = $this->Paginator->paginate('StockMovement');
    //pr($transferMovements);
		$this->set(compact('transferMovements'));
  }
  
  public function guardarResumenTransferencias(){
    $exportData=$_SESSION['transferencias'];
		$this->set(compact('exportData'));
  }
  
  public function detalleTransferencia($transferCode=null){
    
    $transferMovements = $this->StockMovement->find('all',[
      'conditions' => [
        'StockMovement.transfer_code'=>$transferCode,
      ],
			'contain'=>[				
				'Product',
        'ProductionResultCode',
        'StockItem'=>[
          'RawMaterial',
          'Warehouse',
        ],
			],
      'order'=>'movement_date DESC',
		]);
    //pr($transferMovements);
		$this->set(compact('transferCode','transferMovements'));
  }
  
  public function pdfTransferencia($transferCode=null){
    $transferMovements = $this->StockMovement->find('all',[
      'conditions' => [
        'StockMovement.transfer_code'=>$transferCode,
      ],
			'contain'=>[				
				'Product',
        'ProductionResultCode',
        'StockItem'=>[
          'RawMaterial',
          'Warehouse',
        ],
			],
      'order'=>'movement_date DESC',
		]);
    //pr($transferMovements);
		$this->set(compact('transferMovements'));
  }
  
  public function transferirLote() {
		$this->loadModel('ProductCategory');
    $this->loadModel('ProductType');
		$this->loadModel('Product');
		$this->loadModel('ProductionResultCode');
		
    $this->loadModel('StockItem');
		
    $this->loadModel('Warehouse');
    $this->loadModel('PlantProductType');
		$this->loadModel('WarehouseProduct');
    
		$originWarehouseId=WAREHOUSE_DEFAULT;
		$targetWarehouseId=WAREHOUSE_FINISHED;
		
		$productId=0;
		
		if ($this->request->is('post')) {
			$originWarehouseId=$this->request->data['Report']['origin_warehouse_id'];
			
			$inventoryDateArray=$this->request->data['Report']['movement_date'];
			$inventoryDateString=$inventoryDateArray['year'].'-'.$inventoryDateArray['month'].'-'.$inventoryDateArray['day'];
			
			$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
			$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDateString."+1 days"));
    }
    else if (!empty($_SESSION['inventoryDate'])){
			$inventoryDate=$_SESSION['inventoryDate'];
		}
		else {
			$inventoryDate = date("Y-m-d",strtotime(date("Y-m-d")));
		}
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		$_SESSION['inventoryDate']=$inventoryDate;
    //pr($inventoryDate);
		
		$this->set(compact('originWarehouseId','targetWarehouseId'));
		$this->set(compact('productId'));
		$this->set(compact('inventoryDate'));
    
    $originPlantId=$this->Warehouse->getPlantId($originWarehouseId);
    
    $products=[];
    $productRemainingQuantities=[];
    
    if ($originPlantId == PLANT_SANDINO){
      $finishedProducts=$this->StockItem->getInventoryItems(PRODUCT_TYPE_BOTTLE,$inventoryDate,$originWarehouseId,false);
      //pr($finishedProducts);
      foreach ($finishedProducts as $finishedProduct){
        if ($finishedProduct[0]['Remaining']>0){
          $product=$finishedProduct['Product'];
          $rawMaterial=$finishedProduct['RawMaterial'];
          if ($finishedProduct[0]['Remaining_A']>0){
            $index=$product['id']."|".$rawMaterial['id']."|".PRODUCTION_RESULT_CODE_A;
            $products[$index]=$product['name']." ".$rawMaterial['name']." A (".$finishedProduct[0]['Remaining_A'].")";
            $productRemainingQuantities[$index]=$finishedProduct[0]['Remaining_A'];
          }
          if ($finishedProduct[0]['Remaining_B']>0){
            $index=$product['id']."|".$rawMaterial['id']."|".PRODUCTION_RESULT_CODE_B;
            $products[$index]=$product['name']." ".$rawMaterial['name']." B (".$finishedProduct[0]['Remaining_B'].")";
            $productRemainingQuantities[$index]=$finishedProduct[0]['Remaining_B'];
          }
          if ($finishedProduct[0]['Remaining_C']>0){
            $index=$product['id']."|".$rawMaterial['id']."|".PRODUCTION_RESULT_CODE_C;
            $products[$index]=$product['name']." ".$rawMaterial['name']." C (".$finishedProduct[0]['Remaining_C'].")";
            $productRemainingQuantities[$index]=$finishedProduct[0]['Remaining_C'];
          }
        }
      }
      //pr($products);
    }
		
    $plantProductTypeIds=$this->PlantProductType->getProductTypeIdsForPlant($originPlantId);
    $productCategoryIds=$this->ProductCategory->find('list',[
      'fields'=>'ProductCategory.id',
      'conditions'=>['ProductCategory.id !=' => CATEGORY_RAW],
    ]);
    $productTypeIds=$this->ProductType->find('list',[
      'fields'=>'ProductType.id',
      'conditions'=>[
        'ProductType.id !=' => PRODUCT_TYPE_BOTTLE,
        'ProductType.id' => $plantProductTypeIds,
        'ProductType.product_category_id' => $productCategoryIds,
      ],
    ]);
    foreach ($productTypeIds as $productTypeId){
      $productTypeProducts=$this->StockItem->getInventoryItems($productTypeId,$inventoryDate,$originWarehouseId,false);
      //pr($finishedProducts);
      foreach ($productTypeProducts as $productTypeProduct){
        if ($productTypeProduct[0]['Remaining']>0){
          $product=$productTypeProduct['Product'];
          //pr($product);
          $products[$product['id']]=$product['name']. " (".$productTypeProduct[0]['Remaining'].")";
          $productRemainingQuantities[$product['id']]=$productTypeProduct[0]['Remaining'];
        }
      }
    }
    $this->set(compact('products','productRemainingQuantities'));
    
    $productWarehouses=[];
    foreach (array_keys($products) as $productId){
      $productWarehouses[$productId]=$this->WarehouseProduct->getWarehouseIdsForProduct($productId);
    }
    $this->set(compact('productWarehouses'));
    //pr($productWarehouses);
    
		$warehouses = $this->Warehouse->find('list');
		$this->set(compact('warehouses'));
    
		if ($this->request->is('post') && !empty($this->request->data['submit'])) {	
      $targetWarehouseId=$this->request->data['Report']['target_warehouse_id'];
			$productId=$this->request->data['Report']['product_id'];
      
      $transferDateTimeString=$inventoryDateString." 00:00:00";
      $userName=$this->Session->read('User.username');
      
      if (strpos($productId,"|") >0){
        $firstSeparator=strpos($productId,"|");
        $secondSeparator=strpos($productId,"|",($firstSeparator+1));
        
        $finishedProductId=substr($productId,0,$firstSeparator);
        $rawMaterialId=substr($productId,($firstSeparator+1),($secondSeparator-$firstSeparator-1));
        $productionResultCodeId=substr($productId,$secondSeparator+1);
        $transferQuantity=$this->request->data['Report']['transfer_quantity'];
        
        // get the lists for reading out the names
        $allPreformas=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_PREFORMA)));
        $allBottles=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_BOTTLE)));
        $productionResultCodes=$this->ProductionResultCode->find('list');			
        $allWarehouses=$this->Warehouse->find('list');							
        
        if ($transferQuantity>0 && $finishedProductId>0 && $rawMaterialId>0 && $productionResultCodeId>0){
          $quantityInStock=0;
        
          // now get the corresponding stock items
          $this->StockItem->recursive=-1;
          $relevantStockItems=$this->StockItem->find('all',array(
            'conditions'=>array(
              'StockItem.product_id'=>$finishedProductId,
              'StockItem.raw_material_id'=>$rawMaterialId,
              'StockItem.remaining_quantity >'=>0,
              'StockItem.production_result_code_id'=>$productionResultCodeId,
              'StockItem.stockitem_creation_date <='=>$inventoryDate,
              'StockItem.warehouse_id'=>$originWarehouseId,
            ),
          ));
          
          foreach ($relevantStockItems as $stockItem){
            // WE WILL NOT LOOK AT THE QUANTITY OF STOCK IN TIME, JUST AT CURRENT QUANTITY
            // INSTEAD WE ADD THE CURRENT REMAINING QUANTITY
            $quantityInStock+=$stockItem['StockItem']['remaining_quantity'];
          }
          
          if ($quantityInStock<$transferQuantity){
            $this->Session->setFlash('Intento de transferir '.$transferQuantity." ".$allPreformas[$rawMaterialId]." ".$allBottles[$finishedProductId]." (".$productionResultCodes[$productionResultCodeId].") pero en bodega únicamente hay ".$quantityInStock, 'default',array('class' => 'error-message'));
          }
          else {
            // transfer!
            $currentdate= new DateTime();
            $stockItemsForTransfer=$this->StockItem->getFinishedMaterialsForSale($finishedProductId,$productionResultCodeId,$transferQuantity,$rawMaterialId,$inventoryDatePlusOne,$originWarehouseId,"DESC");
            $quantityAvailableForTransfer=0;
            if (count($stockItemsForTransfer)){
              foreach ($stockItemsForTransfer as $stockItem){
                $quantityAvailableForTransfer+=$stockItem['quantity_present'];
              }
            }
            if ($transferQuantity>$quantityAvailableForTransfer){
              $this->Session->setFlash('Los lotes presentes en el momento de transferencia ya salieron de bodega', 'default',array('class' => 'error-message'));
            }
            else {
              $newlyCreatedStockItems=array();
              
              $transferCode="TRANS_000001";
              $lastTransfer=$this->StockMovement->find('first',array(
                'fields'=>array('StockMovement.transfer_code'),
                'conditions'=>array(
                  'bool_transfer'=>true,
                ),
                'order'=>array('StockMovement.transfer_code' => 'desc'),
              ));
              if (!empty($lastTransfer)){
                $transferNumber=substr($lastTransfer['StockMovement']['transfer_code'],6,6)+1;
                $transferCode="TRANS_".str_pad($transferNumber,6,"0",STR_PAD_LEFT)."_".$this->Session->read('User.username');
              }
            
              //pr($stockItemsForTransfer);
              $datasource=$this->StockItem->getDataSource();
              $datasource->begin();
              try{
                foreach ($stockItemsForTransfer as $transferStockItem){
                  if (!$this->StockItem->exists($stockitem_id)) {
                    throw new NotFoundException(__('Invalid StockItem'));
                  }
                  $stockItemId=$transferStockItem['id'];
                  $quantity_present=$transferStockItem['quantity_present'];
                  $quantity_used=$transferStockItem['quantity_used'];
                  $quantity_remaining=$transferStockItem['quantity_remaining'];
                  $unit_price=$transferStockItem['unit_price'];
                  
                  $linkedStockItem=$this->StockItem->getStockItemById($stockItemId);
                  $message="Transferred ".$quantity_used." of ".$allPreformas[$rawMaterialId]." ".$allBottles[$finishedProductId]." (".$productionResultCodes[$productionResultCodeId].") from ".$allWarehouses[$originWarehouseId]." to ".$allWarehouses[$targetWarehouseId];
                  
                  // STEP 1: EDIT THE STOCKITEM OF ORIGIN
                  $stockItemData=[
                    'id'=>$stockItemId,
                    'description'=>$linkedStockItem['StockItem']['description']."|".$message,
                    'remaining_quantity'=>$quantity_remaining,
                  ];
                  $this->StockItem->id=$stockItemId;
                  if (!$this->StockItem->save($stockItemData)) {
                    echo "problema al editor el lote de origen";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  // STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
                  $stockMovementData=[
                    'movement_date'=>$transferDateTimeString,
                    'bool_input'=>false,
                    'name'=>$message,
                    'description'=>$this->request->data['StockMovement']['description'],
                    'order_id'=>0,
                    'stockitem_id'=>$stockItemId,
                    'product_id'=>$finishedProductId,
                    'product_quantity'=>$quantity_used,
                    'product_unit_price'=>$unit_price,
                    'product_total_price'=>$unit_price*$quantity_used,
                    'production_result_code_id'=>$productionResultCodeId,
                    'bool_reclassification'=>false,
                    'bool_transfer'=>true,
                    'transfer_code'=>$transferCode,
                  ];
                  $this->StockMovement->create();
                  if (!$this->StockMovement->save($stockMovementData)) {
                    echo "problema al guardar el movimiento de lote";
                    pr($this->validateErrors($this->StockMovement));
                    throw new Exception();
                  }
                  
                  // STEP 3: SAVE THE TARGET STOCKITEM
                  $stockItemData=[
                    'name'=>$message,
                    'description'=>$message,
                    'stockitem_creation_date'=>$transferDateTimeString,
                    'product_id'=>$finishedProductId,
                    'product_unit_price'=>$unit_price,
                    'original_quantity'=>$quantity_used,
                    'remaining_quantity'=>$quantity_used,
                    'production_result_code_id'=>$productionResultCodeId,
                    'raw_material_id'=>$rawMaterialId,
                    'warehouse_id'=>$targetWarehouseId,
                  ];    
                  $this->StockItem->create();
                  if (!$this->StockItem->save($stockItemData)) {
                    echo "problema al guardar el lote de destino";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  // STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
                  $newStockItemId=$this->StockItem->id;
                  $newlyCreatedStockItems[]=$newStockItemId;
                  
                  $originStockMovementId=$this->StockMovement->id;
                  
                  $stockMovementData=[
                    'movement_date'=>$transferDateTimeString,
                    'bool_input'=>true,
                    'name'=>$message,
                    'description'=>$this->request->data['StockMovement']['description'],
                    'order_id'=>0,
                    'stockitem_id'=>$newStockItemId,
                    'product_id'=>$finishedProductId,
                    'product_quantity'=>$quantity_used,
                    'product_unit_price'=>$unit_price,
                    'product_total_price'=>$unit_price*$quantity_used,
                    'production_result_code_id'=>$productionResultCodeId,
                    'bool_reclassification'=>false,
                    'reclassification_code'=>"",
                    'origin_stock_movement_id'=>$originStockMovementId,
                    'bool_transfer'=>true,
                    'transfer_code'=>$transferCode,
                  ];
                  $this->StockMovement->create();
                  if (!$this->StockMovement->save($stockMovementData)) {
                    echo "problema al guardar el movimiento de lote";
                    pr($this->validateErrors($this->StockMovement));
                    throw new Exception();
                  }
                  // STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
                  $this->recordUserActivity($this->Session->read('User.username'),$message);
                }
                $datasource->commit();
                
                foreach ($stockItemsForTransfer as $transferStockItem){
                  $this->recreateStockItemLogs($transferStockItem['id']);
                }
                for ($i=0;$i<count($newlyCreatedStockItems);$i++){
                  $this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
                }
                
                $this->Session->setFlash(__('Transferencia exitosa'),'default',['class' => 'success']);
                return $this->redirect(['action' => 'transferirLote']);
              }
              catch(Exception $e){
                $datasource->rollback();
                pr($e);
                $this->Session->setFlash(__('Transferencia falló'), 'default',['class' => 'error-message'], 'default',['class' => 'error-message']);
              }
            }
          }
        }
        else {
          $warning="";
          if($transferQuantity==0){
            $warning.="Cantidad de productos debe estar positiva!<br/>";
          }
          if($finishedProductId==0){
            $warning.="Seleccione una botella!<br/>";
          }
          if($rawMaterialId==0){
            $warning.="Seleccione una preforma!<br/>";
          }
          if($productionResultCodeId==0){
            $warning.="Seleccione la calidad original!<br/>";
          }
          $this->Session->setFlash($warning, 'default',array('class' => 'error-message'));
        }
      }
      else {
        $transferQuantity=$this->request->data['Report']['transfer_quantity'];        
        if ($transferQuantity > 0 && $productId > 0){
          $quantityInStock=0;
        
          // now get the corresponding stock items
          $this->StockItem->recursive=-1;
          $relevantStockItems=$this->StockItem->find('all',[
            'conditions'=>[
              'StockItem.product_id'=>$productId,
              'StockItem.remaining_quantity >'=>0,
              'StockItem.stockitem_creation_date <='=>$inventoryDate,
              'StockItem.warehouse_id'=>$originWarehouseId,
            ],
          ]);
          foreach ($relevantStockItems as $stockItem){
            $quantityInStock+=$stockItem['StockItem']['remaining_quantity'];
          }
          
          if ($quantityInStock<$transferQuantity){
            $this->Session->setFlash('Intento de transferir '.$transferQuantity." ".$products[$productId]." pero en bodega únicamente hay ".$quantityInStock, 'default',['class' => 'error-message']);
          }
          else {
            // transfer!
            $currentDateTime= new DateTime();
            $stockItemsForTransfer=$this->StockItem->getOtherMaterialsForSale($productId,$transferQuantity,$inventoryDatePlusOne,$originWarehouseId,"DESC");
            $quantityAvailableForTransfer=0;
            if (count($stockItemsForTransfer)){
              foreach ($stockItemsForTransfer as $stockItem){
                $quantityAvailableForTransfer+=$stockItem['quantity_present'];
              }
            }
            if ($transferQuantity>$quantityAvailableForTransfer){
              $this->Session->setFlash('Los lotes presentes en el momento de transferencia ya salieron de bodega', 'default',['class' => 'error-message']);
            }
            else {
              $newlyCreatedStockItems=[];
              
              $transferCode=$this->StockMovement->getTransferCode($userName);
            
              //pr($stockItemsForTransfer);
              $datasource=$this->StockItem->getDataSource();
              $datasource->begin();
              try{
                foreach ($stockItemsForTransfer as $transferStockItem){
                  $stockItemId=$transferStockItem['id'];
                  $quantityPresent=$transferStockItem['quantity_present'];
                  $quantityUsed=$transferStockItem['quantity_used'];
                  $quantityRemaining=$transferStockItem['quantity_remaining'];
                  $unitPrice=$transferStockItem['unit_price'];
                  if (!$this->StockItem->exists($stockItemId)) {
                    throw new NotFoundException(__('Invalid StockItem'));
                  }
                  $linkedStockItem=$this->StockItem->getStockItemById($stockItemId);
                  $message="Transferred ".$quantityUsed." of ".$products[$productId]." from ".$warehouses[$originWarehouseId]." to ".$warehouses[$targetWarehouseId];
                  
                  // STEP 1: EDIT THE STOCKITEM OF ORIGIN
                  $stockItemData=[
                    'id'=>$stockItemId,
                    'description'=>$linkedStockItem['StockItem']['description']."|".$message,
                    'remaining_quantity'=>$quantityRemaining,
                  ];
                  $this->StockItem->id=$stockItemId;
                  if (!$this->StockItem->save($stockItemData)) {
                    echo "problema al editor el lote de origen";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  // STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
                  $stockMovementData=[
                    'movement_date'=>$transferDateTimeString,
                    'bool_input'=>false,
                    'name'=>$message,
                    'description'=>$this->request->data['StockMovement']['description'],
                    'order_id'=>0,
                    'stockitem_id'=>$stockItemId,
                    'product_id'=>$productId,
                    'product_quantity'=>$quantityUsed,
                    'product_unit_price'=>$unitPrice,
                    'product_total_price'=>$unitPrice*$quantityUsed,
                    'bool_reclassification'=>false,
                    'bool_transfer'=>true,
                    'transfer_code'=>$transferCode,
                  ];
                  
                  $this->StockMovement->create();
                  if (!$this->StockMovement->save($stockMovementData)) {
                    echo "problema al guardar el movimiento de lote";
                    pr($this->validateErrors($this->StockMovement));
                    throw new Exception();
                  }
                  
                  // STEP 3: SAVE THE TARGET STOCKITEM
                  $stockItemData=[
                    'name'=>$message,
                    'description'=>$message,
                    'stockitem_creation_date'=>$transferDateTimeString,
                    'product_id'=>$productId,
                    'product_unit_price'=>$unitPrice,
                    'original_quantity'=>$quantityUsed,
                    'remaining_quantity'=>$quantityUsed,
                    'warehouse_id'=>$targetWarehouseId,
                  ];
                  $this->StockItem->create();
                  if (!$this->StockItem->save($stockItemData)) {
                    echo "problema al guardar el lote de destino";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  // STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
                  $newStockitemId=$this->StockItem->id;
                  $newlyCreatedStockItems[]=$newStockitemId;
                  
                  $originStockMovementId=$this->StockMovement->id;
                  
                  $stockMovementData=[
                    'movement_date'=>$transferDateTimeString,
                    'bool_input'=>true,
                    'name'=>$message,
                    'description'=>$this->request->data['StockMovement']['description'],
                    'order_id'=>0,
                    'stockitem_id'=>$newStockitemId,
                    'product_id'=>$productId,
                    'product_quantity'=>$quantityUsed,
                    'product_unit_price'=>$unitPrice,
                    'product_total_price'=>$unitPrice*$quantityUsed,
                    'bool_reclassification'=>false,
                    'reclassification_code'=>"",
                    'origin_stock_movement_id'=>$originStockMovementId,
                    'bool_transfer'=>true,
                    'transfer_code'=>$transferCode,
                  ];
                  $this->StockMovement->create();
                  if (!$this->StockMovement->save($stockMovementData)) {
                    echo "problema al guardar el movimiento de lote";
                    pr($this->validateErrors($this->StockMovement));
                    throw new Exception();
                  }
                      
                  // STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
                  $this->recordUserActivity($this->Session->read('User.username'),$message);
                }
                $datasource->commit();
                
                foreach ($stockItemsForTransfer as $transferStockItem){
                  $this->recreateStockItemLogs($transferStockItem['id']);
                }
                for ($i=0;$i<count($newlyCreatedStockItems);$i++){
                  $this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
                }
                
                $this->Session->setFlash(__('Transferencia exitosa'),'default',['class' => 'success']);
                return $this->redirect(['action' => 'resumenTransferencias']);
              }
              catch(Exception $e){
                $datasource->rollback();
                pr($e);
                $this->Session->setFlash(__('Transferencia falló'), 'default',['class' => 'error-message']);
              }
            }
          }
        }
        else {
          $warning="";
          if($transferQuantity==0){
            $warning.="Cantidad de productos debe estar positiva!<br/>";
          }
          if($productId==0){
            $warning.="Seleccione un producto!<br/>";
          }
          $this->Session->setFlash($warning, 'default',['class' => 'error-message']);
        }
      }  
		}
	}
	
  public function eliminarTransferencia($transferCode = null) {
		if (empty($transferCode)) {
			throw new NotFoundException(__('No hay código de tranferencia'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		
		$transferMovements=$this->StockMovement->find('all', [
			'conditions' => [
				'StockMovement.transfer_code' => $transferCode,
			],
			'contain'=>[
        'StockItem'=>[
          'StockMovement'=>[
            'conditions'=>['StockMovement.transfer_code !=' => $transferCode],
          ],
          'ProductionMovement',
          'Warehouse',
        ]
      ],
		]);
		$flashMessage="";
    $boolDeletionAllowed=true;
    foreach ($transferMovements as $stockMovement){
      if ($stockMovement['StockMovement']['bool_input']){
        if (!empty($stockMovement['StockItem']['StockMovement'])){
          $boolDeletionAllowed=false;
          $flashMessage.="No se puede eliminar la transferencia porque los productos transferidos ya se ocuparon en stockmovements ";
          foreach ($stockMovement['StockItem']['StockMovement'] as $usedStockMovement){
             $flashMessage.=$usedStockMovement['id'].","; 
          }
        }
        if (!empty($stockMovement['StockItem']['ProductionMovement'])){
          $boolDeletionAllowed=false;
          $flashMessage.="No se puede eliminar la transferencia porque los productos transferidos ya se ocuparon en productionmovements ";
          foreach ($stockMovement['StockItem']['ProductionMovement'] as $usedProductionMovement){
             $flashMessage.=$usedProductionMovement['id'].","; 
          }
        }
      }
    }
    if ($boolDeletionAllowed){    
      $datasource=$this->StockMovement->getDataSource();
      $datasource->begin();
      try {
        //delete all stockMovements, stockItems and stockItemLogs
        foreach ($transferMovements as $stockMovement){
          //pr($stockMovement);
          if (!empty($stockMovement['StockItem'])){
            $stockItem['StockItem']=$stockMovement['StockItem'];
            if ($stockMovement['StockMovement']['bool_input']){
              $stockItem['StockItem']['remaining_quantity']-=$stockMovement['StockMovement']['product_quantity'];
              $stockItem['StockItem']['original_quantity']=0;
            }
            else {
              $stockItem['StockItem']['remaining_quantity']+=$stockMovement['StockMovement']['product_quantity'];
            }
            $stockItem['StockItem']['description'].="| transferencia eliminado ".$transferCode;
            if (!$this->StockItem->save($stockItem)) {
              echo "problema eliminando el estado de lote";
              pr($this->validateErrors($this->StockItem));
              throw new Exception();
            }
          }
          if (!$this->StockMovement->delete($stockMovement['StockMovement']['id'])) {
            echo "Problema al eliminar el movimiento de transferencia";
            pr($this->validateErrors($this->StockMovement));
            throw new Exception();
          }
        }
        foreach ($transferMovements as $stockMovement){
          if (!empty($stockMovement['StockItem']['id'])){
            if (!$stockMovement['StockMovement']['bool_input']){
             $this->recreateStockItemLogs($stockMovement['StockItem']['id']);
            }
            else {
              if (!$this->StockItem->delete($stockMovement['StockItem']['id'])) {
              echo "problema eliminando el lote";
              pr($this->validateErrors($this->StockItem));
              throw new Exception();
            }
            }
          }				
        }
        
        $datasource->commit();
      /*
        $this->loadModel('Deletion');
        $this->Deletion->create();
        $deletionArray=[];
        $deletionArray['Deletion']['user_id']=$this->Auth->User('id');
        $deletionArray['Deletion']['reference_id']=$entry['Order']['id'];
        $deletionArray['Deletion']['reference']=$entry['Order']['order_code'];
        $deletionArray['Deletion']['type']='Order';
        $this->Deletion->save($deletionArray);
      */			
        $this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la transferencia número ".$transferCode);
            
        $this->Session->setFlash('Se eliminó la transferencia #'.$transferCode,'default',['class' => 'success']);				
        return $this->redirect(['action' => 'resumenTransferencias']);
      }
      catch(Exception $e){
        $datasource->rollback();
        pr($e);
        $this->Session->setFlash('No se podía eliminar la transferencia #'.$transferCode, 'default',['class' => 'error-message']);
        return $this->redirect(['action' => 'detalleTransferencia', $transferCode]);
      }
    }
    else {
      $this->Session->setFlash($flashMessage.'No se eliminó la transferencia #'.$transferCode, 'default',['class' => 'error-message']);
      return $this->redirect(['action' => 'detalleTransferencia', $transferCode]);
    }    
	}
	
	public function verReporteCompraVenta($id=0){
		if (!$this->StockMovement->Product->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}
	
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->StockMovement->Product->recursive=1;
		$allOtherMaterials=$this->StockMovement->Product->find('all',array('conditions'=>array('ProductType.product_category_id'=>CATEGORY_OTHER,'Product.id'=>$id)));
		$materialPosition=[];
		$positionCounter=0;
		
		foreach ($allOtherMaterials as $tapon){
			$materialPosition[$tapon['Product']['id']]['Entry']=$positionCounter;
			$positionCounter++;
			$materialPosition[$tapon['Product']['id']]['Exit']=$positionCounter;
			$positionCounter++;
			$materialPosition[$tapon['Product']['id']]['Reclassified']=$positionCounter;
			$positionCounter++;
			$materialPosition[$tapon['Product']['id']]['Saldo']=$positionCounter;
			$positionCounter++;
		}
		//pr($materialPosition);
		$originalInventory=[];
		for ($i=0;$i<4*count($allOtherMaterials);$i++){
			$originalInventory[$i]=0;
		}
		$this->StockMovement->StockItem->StockItemLog->recursive=0;
		foreach ($allOtherMaterials as $tapon){
			$this->StockMovement->StockItem->recursive=-1;
			$allStockItemsForProduct = $this->StockMovement->StockItem->find('all', array(
				'conditions' => array(
					'StockItem.product_id'=> $tapon['Product']['id'],
				),
			));
			//pr($allStockItemsForProduct);
			$productInitialStock=0;
			foreach ($allStockItemsForProduct as $stockItemForProduct){
				$stockItemId=$stockItemForProduct['StockItem']['id'];
				$this->StockMovement->StockItem->StockItemLog->recursive=-1;
				$initialStockItemLogForStockItem=$this->StockMovement->StockItem->StockItemLog->find('first',array(
					'conditions' => array(
						'StockItemLog.stockitem_id ='=> $stockItemId,
						'StockItemLog.stockitem_date <'=>$startDate
					),
					'order'=>'StockItemLog.id DESC'
				));
				if (!empty($initialStockItemLogForStockItem)){
					if ($initialStockItemLogForStockItem['StockItemLog']['product_quantity']>0){
						//pr($initialStockItemLogForStockItem);
					}
					$productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
				}
			}
			$originalInventory[$materialPosition[$tapon['Product']['id']]['Saldo']]=$productInitialStock;
		}
		//pr($originalInventory);
		
		$otherMaterialIds=[];
		foreach ($allOtherMaterials as $tapon){
			$otherMaterials[]=$tapon['Product']['id'];
		}
		$this->StockMovement->recursive=2;
		$allStockMovements=$this->StockMovement->find('all',
			array(
				'conditions'=>array(
					'Product.id'=>$otherMaterials, // IN statement
					'StockMovement.product_quantity >'=>0,
					'movement_date >='=>$startDate,
					'movement_date <'=>$endDatePlusOne,
				),
				'order'=>'movement_date ASC'
			)
		);
		//pr($allStockMovements);
		$resultMatrix=[];
		$currentInventory=$originalInventory;
		$rowCounter=0;
		foreach($allStockMovements as $otherStockMovement){
			$resultMatrix[$rowCounter]['date']=$otherStockMovement['Order']['order_date'];
			if (!empty($otherStockMovement['Order']['ThirdParty']['company_name'])){
				$resultMatrix[$rowCounter]['providerclient']=$otherStockMovement['Order']['ThirdParty']['company_name'];
				$resultMatrix[$rowCounter]['providerid']=$otherStockMovement['Order']['ThirdParty']['id'];
				$resultMatrix[$rowCounter]['providerbool']=$otherStockMovement['Order']['ThirdParty']['bool_provider'];
        $resultMatrix[$rowCounter]['issale']=!empty($otherStockMovement['Order']['Invoice']['id']);
			}
			else {
				$resultMatrix[$rowCounter]['providerclient']="-";
				$resultMatrix[$rowCounter]['providerid']=0;
				$resultMatrix[$rowCounter]['providerbool']=0;
        $resultMatrix[$rowCounter]['issale']=0;
			}
			if (!empty($otherStockMovement['Order']['order_code'])){
				$resultMatrix[$rowCounter]['invoicecode']=$otherStockMovement['Order']['order_code'];
				$resultMatrix[$rowCounter]['invoiceid']=$otherStockMovement['Order']['id'];
				if ($otherStockMovement['Order']['stock_movement_type_id']==MOVEMENT_PURCHASE){
					$resultMatrix[$rowCounter]['entrybool']=1;
				}
				else {
					$resultMatrix[$rowCounter]['entrybool']=0;
				}
			}
			else {
				$resultMatrix[$rowCounter]['invoicecode']="-";
				$resultMatrix[$rowCounter]['invoiceid']=0;
				$resultMatrix[$rowCounter]['entrybool']=0;
			}
			$productid=$otherStockMovement['StockMovement']['product_id'];
			$boolinput=$otherStockMovement['StockMovement']['bool_input'];
			$boolreclassified=$otherStockMovement['StockMovement']['bool_reclassification'];
			$saldoRef=0;
			if (!$boolreclassified){
				if ($boolinput){
					for ($i=0;$i<4*count($allOtherMaterials);$i++){					
						if ($i==$materialPosition[$productid]['Entry']){
							$resultMatrix[$rowCounter][$i]=$otherStockMovement['StockMovement']['product_quantity'];
							$saldoRef=$i+3;
						}
						else {
							$resultMatrix[$rowCounter][$i]="-";
						}
					}
					$currentInventory[$saldoRef-3]+=$otherStockMovement['StockMovement']['product_quantity'];
					$currentInventory[$saldoRef]+=$otherStockMovement['StockMovement']['product_quantity'];
					$resultMatrix[$rowCounter][$saldoRef]=$currentInventory[$saldoRef];
				}
				else {
					for ($i=0;$i<4*count($allOtherMaterials);$i++){					
						if ($i==$materialPosition[$productid]['Exit']){
							$resultMatrix[$rowCounter][$i]=$otherStockMovement['StockMovement']['product_quantity'];
							$saldoRef=$i+2;
						}
						else {
							$resultMatrix[$rowCounter][$i]="-";
						}
					}
					$currentInventory[$saldoRef-2]-=$otherStockMovement['StockMovement']['product_quantity'];
					$currentInventory[$saldoRef]-=$otherStockMovement['StockMovement']['product_quantity'];
					$resultMatrix[$rowCounter][$saldoRef]=$currentInventory[$saldoRef];
				}
			}
			else {
				if ($boolinput){
					for ($i=0;$i<4*count($allOtherMaterials);$i++){					
						if ($i==$materialPosition[$productid]['Reclassified']){
							$resultMatrix[$rowCounter][$i]=$otherStockMovement['StockMovement']['product_quantity'];
							$saldoRef=$i+1;
						}
						else {
							$resultMatrix[$rowCounter][$i]="-";
						}
					}
					$currentInventory[$saldoRef-1]+=$otherStockMovement['StockMovement']['product_quantity'];
					$currentInventory[$saldoRef]+=$otherStockMovement['StockMovement']['product_quantity'];
					$resultMatrix[$rowCounter][$saldoRef]=$currentInventory[$saldoRef];
				}
				else {
					for ($i=0;$i<4*count($allOtherMaterials);$i++){					
						if ($i==$materialPosition[$productid]['Reclassified']){
							$resultMatrix[$rowCounter][$i]=$otherStockMovement['StockMovement']['product_quantity'];
							$saldoRef=$i+1;
						}
						else {
							$resultMatrix[$rowCounter][$i]="-";
						}
					}
					$currentInventory[$saldoRef-1]-=$otherStockMovement['StockMovement']['product_quantity'];
					$currentInventory[$saldoRef]-=$otherStockMovement['StockMovement']['product_quantity'];
					$resultMatrix[$rowCounter][$saldoRef]=$currentInventory[$saldoRef];
				}
			}
			$rowCounter++;
		}
		//pr($resultMatrix);
		//pr($currentInventory);
			
		$this->set(compact('originalInventory','resultMatrix','currentInventory','startDate','endDate','allOtherMaterials','id'));

	}
	
	public function guardarReporteCompraVenta(){
		$exportData=$_SESSION['reporteCompraVenta'];
		$this->set(compact('exportData'));
	}
  
  public function verKardex($productId=0,$rawMaterialId=0){
		if (!$this->StockMovement->Product->exists($productId)) {
			throw new NotFoundException(__('Invalid product'));
		}
    
    $this->loadModel('ProductionMovement');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    $this->loadModel('StockItem');
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
	
    $warehouseId=0;
  
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $warehouseId=$this->request->data['Report']['warehouse_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
    $this->set(compact('startDate','endDate'));
		
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if (count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
    elseif (count($warehouses) > 1 && $warehouseId == 0){
      if (array_key_exists(WAREHOUSE_DEFAULT,$warehouses)){
        $warehouseId = WAREHOUSE_DEFAULT;
      }
      else {
        $warehouseId=0;
      }
    }
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
    //echo 'warehouse id is '.$warehouseId.'<br/>';
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    $plantId=0;
    if ($warehouseId > 0){
      $plantId=$this->Warehouse->getPlantId($warehouseId);
    }
    $this->set(compact('plantId'));
    //echo 'plant id is '.$plantId.'<br/>';
		
		$product=$this->StockMovement->Product->getProductById($productId);
    $this->set(compact('product','productId'));
    $rawMaterial=$this->StockMovement->Product->getProductById($rawMaterialId);
    $this->set(compact('rawMaterial','rawMaterialId'));
    
	$saldos=$this->StockItem->getSaldo($productId,$warehouseId,$startDate,$rawMaterialId);
 

   /* $stockItemConditions=[
      'StockItem.product_id'=> $productId,
      'StockItem.warehouse_id'=> $warehouseId,
    ];
    if ($rawMaterialId > 0){
      $stockItemConditions['StockItem.raw_material_id']=$rawMaterialId;
    }
    $allStockItemsForProduct = $this->StockMovement->StockItem->find('all', [
      'conditions' => $stockItemConditions,
      'recursive'=>-1,
    ]);
    //pr($allStockItemsForProduct);
    $productInitialStock=[
      'total'=>0,
    ];*/
    $productionResultCodeIds=[
      'total'=>0,
    ];
	$codes=$this->StockItem->getResultCodes($productId);
	foreach($codes as $codeIds)
	{
	    $productionResultCodeIds[$codeIds['stock_movements']['production_result_code_id']]=0;	
	}
    /*foreach ($allStockItemsForProduct as $stockItemForProduct){
      $stockItemProductionResultCodeId=$stockItemForProduct['StockItem']['production_result_code_id'];
      if (!array_key_exists($stockItemProductionResultCodeId,$productInitialStock)){
        $productInitialStock[$stockItemProductionResultCodeId]=0;
        $productionResultCodeIds[$stockItemProductionResultCodeId]=0;
      }
      $stockItemId=$stockItemForProduct['StockItem']['id'];
      $this->StockMovement->StockItem->StockItemLog->recursive=-1;
      $initialStockItemLogForStockItem=$this->StockMovement->StockItem->StockItemLog->find('first',[
        'conditions' => [
          'StockItemLog.stockitem_id'=> $stockItemId,
          'StockItemLog.stockitem_date <'=>$startDate
        ],
        'recursive'=>-1,
        'order'=>'StockItemLog.id DESC'
      ]);
      if (!empty($initialStockItemLogForStockItem)){
        $productInitialStock[$stockItemProductionResultCodeId]+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
        $productInitialStock['total']+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
      }
    }*/
    $originalInventory=[
      'entry'=>$productionResultCodeIds,
      'exit'=>$productionResultCodeIds,
      'adjustment'=>$productionResultCodeIds,
      'saldo'=>$saldos,
    ];
    $currentInventory=$originalInventory;
		//pr($originalInventory);
    $stockMovementConditions=[
      'StockMovement.product_id'=>$productId, 
      'StockMovement.product_quantity >'=>0,
      'StockMovement.movement_date >='=>$startDate,
      'StockMovement.movement_date <'=>$endDatePlusOne,
      'StockItem.warehouse_id'=>$warehouseId,
    ];
    if ($rawMaterialId > 0){
      $stockMovementConditions['StockItem.raw_material_id']=$rawMaterialId;
    }
    $allStockMovements=$this->StockMovement->find('all',[
      'conditions'=>$stockMovementConditions,
      'contain'=>[
        'Order'=>[
          'ThirdParty',
          'Invoice',
        ],
        'StockItem',
      ],
      'order'=>'movement_date ASC',
    ]);
		//pr($allStockMovements);
		$resultMatrix=[];
		
		$rowCounter=0;
		foreach($allStockMovements as $stockMovement){
      $productionResultCodeId=$stockMovement['StockItem']['production_result_code_id'];
      
      $boolInput=$stockMovement['StockMovement']['bool_input'];
			if ($rowCounter == 0 || !array_key_exists('invoicecode',$resultMatrix[$rowCounter-1]) || (array_key_exists('order_code',$stockMovement['Order']) && $resultMatrix[$rowCounter-1]['invoicecode'] != $stockMovement['Order']['order_code'])){    
        $resultMatrix[$rowCounter]['date']=$stockMovement['StockMovement']['movement_date'];
        if (!empty($stockMovement['Order']['ThirdParty']['company_name'])){
          $resultMatrix[$rowCounter]['providerclient']=$stockMovement['Order']['ThirdParty']['company_name'];
          $resultMatrix[$rowCounter]['providerid']=$stockMovement['Order']['ThirdParty']['id'];
          $resultMatrix[$rowCounter]['providerbool']=$stockMovement['Order']['ThirdParty']['bool_provider'];
          $resultMatrix[$rowCounter]['issale']=!empty($stockMovement['Order']['Invoice']['id']);
        }
        else {
          $resultMatrix[$rowCounter]['providerclient']="-";
          $resultMatrix[$rowCounter]['providerid']=0;
          $resultMatrix[$rowCounter]['providerbool']=0;
          $resultMatrix[$rowCounter]['issale']=0;
        }
        if (!empty($stockMovement['Order']['order_code'])){
          $resultMatrix[$rowCounter]['invoicecode']=$stockMovement['Order']['order_code'];
          $resultMatrix[$rowCounter]['invoiceid']=$stockMovement['Order']['id'];
          if ($stockMovement['Order']['stock_movement_type_id'] == MOVEMENT_PURCHASE){
            $resultMatrix[$rowCounter]['entrybool']=1;
            $resultMatrix[$rowCounter]['type']="Entrada";
          }
          else {
            $resultMatrix[$rowCounter]['entrybool']=0;
            $resultMatrix[$rowCounter]['type']="Factura";
          }
        }
        else {
          $resultMatrix[$rowCounter]['invoicecode']="-";
          $resultMatrix[$rowCounter]['invoiceid']=0;
          $resultMatrix[$rowCounter]['entrybool']=0;
          $resultMatrix[$rowCounter]['type']='-';
        }
        $productid=$stockMovement['StockMovement']['product_id'];
        $boolReclassified=$stockMovement['StockMovement']['bool_reclassification'] || $stockMovement['StockMovement']['bool_transfer'] || $stockMovement['StockMovement']['bool_adjustment'];
        if (!$boolReclassified){
          if ($boolInput){
            $resultMatrix[$rowCounter]['entry'][$productionResultCodeId]=$stockMovement['StockMovement']['product_quantity'];
            $resultMatrix[$rowCounter]['entry']['total']=$stockMovement['StockMovement']['product_quantity'];
            $resultMatrix[$rowCounter]['exit']['total']=0;
            $resultMatrix[$rowCounter]['adjustment']['total']=0;
            
            $currentInventory['entry'][$productionResultCodeId]+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['entry']['total']+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo'][$productionResultCodeId]+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo']['total']+=$stockMovement['StockMovement']['product_quantity'];
          }
          else {
            $resultMatrix[$rowCounter]['entry']['total']=0;
            $resultMatrix[$rowCounter]['exit'][$productionResultCodeId]=$stockMovement['StockMovement']['product_quantity'];
            $resultMatrix[$rowCounter]['exit']['total']=$stockMovement['StockMovement']['product_quantity'];
            $resultMatrix[$rowCounter]['adjustment']['total']=0;
			
		    $currentInventory['exit'][$productionResultCodeId]+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['exit']['total']+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo'][$productionResultCodeId]-=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo']['total']-=$stockMovement['StockMovement']['product_quantity'];
          }
        }
        else {
          if ($stockMovement['StockMovement']['bool_reclassification']){
            $resultMatrix[$rowCounter]['type']='Reclasificación';
            $resultMatrix[$rowCounter]['reclassificationCode']=$stockMovement['StockMovement']['reclassification_code'];          
          }
          elseif ($stockMovement['StockMovement']['bool_transfer']){
            $resultMatrix[$rowCounter]['type']='Transferencia';
            $resultMatrix[$rowCounter]['transferCode']=$stockMovement['StockMovement']['transfer_code'];
          } 
          elseif ($stockMovement['StockMovement']['bool_adjustment']){
            $resultMatrix[$rowCounter]['type']='Ajuste';
            $resultMatrix[$rowCounter]['adjustmentCode']=$stockMovement['StockMovement']['adjustment_code'];
            
          }
          //$resultMatrix[$rowCounter]['type']='ajuste';
          
          $resultMatrix[$rowCounter]['entry']['total']=0;
          $resultMatrix[$rowCounter]['exit']['total']=0;
          $resultMatrix[$rowCounter]['adjustment']['total']=$stockMovement['StockMovement']['product_quantity'];
          $resultMatrix[$rowCounter]['adjustment']['total']=$stockMovement['StockMovement']['product_quantity'];
          
          if ($boolInput){
            $resultMatrix[$rowCounter]['bool_input']=true;
            $currentInventory['adjustment'][$productionResultCodeId]+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['adjustment']['total']+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo'][$productionResultCodeId]+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo']['total']+=$stockMovement['StockMovement']['product_quantity'];
          }
          else {
            $resultMatrix[$rowCounter]['bool_input']=false;
            $resultMatrix[$rowCounter]['incoming']="1";
            $currentInventory['adjustment'][$productionResultCodeId]-=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['adjustment']['total']-=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo'][$productionResultCodeId]-=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo']['total']-=$stockMovement['StockMovement']['product_quantity'];
          }
        }
        $resultMatrix[$rowCounter]['saldo']=$currentInventory['saldo'];
      }
      elseif ($rowCounter > 0) {
        $rowCounter--;
        $productid=$stockMovement['StockMovement']['product_id'];
        $boolReclassified=$stockMovement['StockMovement']['bool_reclassification'] || $stockMovement['StockMovement']['bool_transfer'] || $stockMovement['StockMovement']['bool_adjustment'];
        if (!$boolReclassified){
          if ($boolInput){
            if (!array_key_exists($productionResultCodeId,$resultMatrix[$rowCounter]['entry'])){
              $resultMatrix[$rowCounter]['entry'][$productionResultCodeId]=0;
            }  
            $resultMatrix[$rowCounter]['entry'][$productionResultCodeId]+=$stockMovement['StockMovement']['product_quantity'];
            $resultMatrix[$rowCounter]['entry']['total']+=$stockMovement['StockMovement']['product_quantity'];
            
            $currentInventory['entry'][$productionResultCodeId]+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['entry']['total']+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo'][$productionResultCodeId]+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo']['total']+=$stockMovement['StockMovement']['product_quantity'];
          }
          else {
            if (!array_key_exists($productionResultCodeId,$resultMatrix[$rowCounter]['exit'])){
              $resultMatrix[$rowCounter]['exit'][$productionResultCodeId]=0;
            }  
            $resultMatrix[$rowCounter]['exit'][$productionResultCodeId]+=$stockMovement['StockMovement']['product_quantity'];
            $resultMatrix[$rowCounter]['exit']['total']+=$stockMovement['StockMovement']['product_quantity'];
      
            $currentInventory['exit'][$productionResultCodeId]+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['exit']['total']+=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo'][$productionResultCodeId]-=$stockMovement['StockMovement']['product_quantity'];
            $currentInventory['saldo']['total']-=$stockMovement['StockMovement']['product_quantity'];
          }
        }
      }
			$rowCounter++;
		}
    //pr($resultMatrix);
    $productionMovementConditions=[
      'ProductionMovement.product_id'=>$productId, // IN statement
      'ProductionMovement.product_quantity >'=>0,
      'ProductionMovement.movement_date >='=>$startDate,
      'ProductionMovement.movement_date <'=>$endDatePlusOne,
      'StockItem.warehouse_id'=>$warehouseId,
    ];
    if ($rawMaterialId > 0){
      $productionMovementConditions['StockItem.raw_material_id']=$rawMaterialId;
    }
    $allProductionMovements=$this->ProductionMovement->find('all',[
      'conditions'=>$productionMovementConditions,
      'contain'=>[
        'ProductionRun',
        'StockItem',
      ],
      'order'=>'movement_date ASC'
    ]);
		$rowCounter=count($resultMatrix);
		 //	pr($originalInventory);
			//pr($allStockMovements);
			//pr($allProductionMovements);
		//exit; 
    foreach($allProductionMovements as $productionMovement){
      //pr($productionMovement);
      $productionResultCodeId=$productionMovement['StockItem']['production_result_code_id'];
      $boolInput=$productionMovement['ProductionMovement']['bool_input'];
      if ($rowCounter ==0 || !array_key_exists('productionRunCode',$resultMatrix[$rowCounter-1]) || $resultMatrix[$rowCounter-1]['productionRunCode'] != $productionMovement['ProductionRun']['production_run_code']){        
        $resultMatrix[$rowCounter]['date']=$productionMovement['ProductionRun']['production_run_date'];
        $resultMatrix[$rowCounter]['providerclient']="-";
        $resultMatrix[$rowCounter]['providerid']=0;
        $resultMatrix[$rowCounter]['providerbool']=0;
        $resultMatrix[$rowCounter]['issale']=0;
        if (!empty($productionMovement['ProductionRun']['production_run_code'])){
          $resultMatrix[$rowCounter]['productionRunCode']=$productionMovement['ProductionRun']['production_run_code'];
          $resultMatrix[$rowCounter]['productionRunId']=$productionMovement['ProductionRun']['id'];
          $resultMatrix[$rowCounter]['type']="Orden de Producción";
        }
        else {
          $resultMatrix[$rowCounter]['productionRunCode']="-";
          $resultMatrix[$rowCounter]['productionRunId']=0;
          $resultMatrix[$rowCounter]['type']="-";
        }
        $resultMatrix[$rowCounter]['adjustment']['total']=0;
        if (!$boolInput){
          $resultMatrix[$rowCounter]['entry'][$productionResultCodeId]=$productionMovement['ProductionMovement']['product_quantity'];
          $resultMatrix[$rowCounter]['entry']['total']=$productionMovement['ProductionMovement']['product_quantity'];
          $resultMatrix[$rowCounter]['exit']['total']="-";
          
          $currentInventory['entry'][$productionResultCodeId]+=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['entry']['total']+=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['saldo'][$productionResultCodeId]+=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['saldo']['total']+=$productionMovement['ProductionMovement']['product_quantity'];
        }
        else {
          $resultMatrix[$rowCounter]['entry']['total']=0;
          $resultMatrix[$rowCounter]['exit'][$productionResultCodeId]=$productionMovement['ProductionMovement']['product_quantity'];
          $resultMatrix[$rowCounter]['exit']['total']=$productionMovement['ProductionMovement']['product_quantity'];
          
          $currentInventory['exit'][$productionResultCodeId]+=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['exit']['total']+=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['saldo'][$productionResultCodeId]-=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['saldo']['total']-=$productionMovement['ProductionMovement']['product_quantity'];
        }
        $resultMatrix[$rowCounter]['saldo']=$currentInventory['saldo'];
      }
      else {
        $rowCounter--;
        if (!$boolInput){
          if (!array_key_exists($productionResultCodeId,$resultMatrix[$rowCounter]['entry'])){
            $resultMatrix[$rowCounter]['entry'][$productionResultCodeId]=0;
          }
          $resultMatrix[$rowCounter]['entry'][$productionResultCodeId]+=$productionMovement['ProductionMovement']['product_quantity'];
          $resultMatrix[$rowCounter]['entry']['total']+=$productionMovement['ProductionMovement']['product_quantity'];
          
          $currentInventory['entry'][$productionResultCodeId]+=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['entry']['total']+=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['saldo'][$productionResultCodeId]+=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['saldo']['total']+=$productionMovement['ProductionMovement']['product_quantity'];
        }
        else {
          if (!array_key_exists($productionResultCodeId,$resultMatrix[$rowCounter]['exit'])){
            $resultMatrix[$rowCounter]['exit'][$productionResultCodeId]=0;
          }
          $resultMatrix[$rowCounter]['exit'][$productionResultCodeId]-=$productionMovement['ProductionMovement']['product_quantity'];
          $resultMatrix[$rowCounter]['exit']['total']-=$productionMovement['ProductionMovement']['product_quantity'];
          
          $currentInventory['exit'][$productionResultCodeId]+=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['exit']['total']+=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['saldo'][$productionResultCodeId]-=$productionMovement['ProductionMovement']['product_quantity'];
          $currentInventory['saldo']['total']-=$productionMovement['ProductionMovement']['product_quantity'];
        }
        $resultMatrix[$rowCounter]['saldo']=$currentInventory['saldo'];
      }
			$rowCounter++;
		}
		//pr($resultMatrix);	
    usort($resultMatrix,[$this,'sortByMovementDate']);
    //pr($resultMatrix);
		$this->set(compact('originalInventory','resultMatrix','currentInventory','startDate','endDate','product','productId'));
    
    $this->loadModel('ProductionResultCode');
    $productionResultCodes=$this->ProductionResultCode->getProductionResultCodeList();
    $this->set(compact('productionResultCodes'));
	}
	
	public function guardarKardex($productName=""){
		$exportData=$_SESSION['kardex'];
		$this->set(compact('exportData','productName'));
	}
	
  public function sortByMovementDate($rowA,$rowB){
    if($rowA['date'] != $rowB['date']){ 		
			return ($rowA['date'] < $rowB['date']) ? -1 : 1;
		}
		else {
      if (array_key_exists(
      'productionRunCode',$rowA) && array_key_exists(
      'productionRunCode',$rowB)){
        return ($rowA['productionRunCode'] < $rowB['productionRunCode']) ? -1 : 1;
      }
		}
	}
	
	public function verReporteVentaProductoPorCliente(){
		$this->loadModel('Order');
		$this->loadModel('Product');
		$this->loadModel('StockItem');
		$this->loadModel('ThirdParty');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('User');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $warehouseId=0;
    $userId=0;
    
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $userId=$this->request->data['Report']['user_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
    $this->set(compact('userId'));
    $users=$this->User->getActiveUserList();
    $this->set(compact('users'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if (count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
    elseif (count($warehouses) > 1 && $warehouseId == 0){
      if (!empty($_SESSION['warehouseId'])){
        $warehouseId = $_SESSION['warehouseId'];
      }
      elseif (array_key_exists(WAREHOUSE_DEFAULT,$warehouses)){
        $warehouseId = WAREHOUSE_DEFAULT;
      }
      else {
        $warehouseId=0;
      }
    }
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
		
    $saleConditions=[
      'Order.stock_movement_type_id'=> MOVEMENT_SALE,
      'Order.order_date >='=> $startDate,
      'Order.order_date <'=> $endDatePlusOne,
      'Order.warehouse_id'=> $warehouseId,
    ];
    if ($userId > 0){
      $saleConditions['Order.user_id']=$userId;
    }
    $salesIdsForPeriod=$this->Order->find('list',[
      'fields'=>['Order.id'],
      'conditions'=>$saleConditions,
    ]);
    
		$movementConditions=[
			'StockMovement.movement_date >='=>$startDate,
			'StockMovement.movement_date <'=>$endDatePlusOne,
			'StockMovement.product_quantity >'=>0,
			'StockMovement.production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
			'StockMovement.bool_input'=>false,
			'StockMovement.bool_reclassification'=>false,
      'StockMovement.bool_adjustment'=>false,
      'StockMovement.order_id'=>$salesIdsForPeriod,
		];
    
    $movementsForPeriod=$this->StockMovement->find('all',[
			'fields'=>[
				'StockMovement.product_id',
				'StockMovement.order_id',
				'StockMovement.stockitem_id',
			],
			'conditions'=>$movementConditions,
			'contain'=>[
				'Order'=>[
					'fields'=>['Order.id','Order.third_party_id'],
				],
			],
		]);
		$soldProductIds=[];
    $orderIds=[];
		$buyingClientIds=[];
		$stockItemIds=[];
		
		foreach($movementsForPeriod as $movement){
			//pr($movement);
			$soldProductIds[]=$movement['StockMovement']['product_id'];
			$orderIds[]=$movement['Order']['id'];
      $buyingClientIds[]=$movement['Order']['third_party_id'];
			$stockItemIds[]=$movement['StockMovement']['stockitem_id'];
		}
		
		$soldProductIds=array_unique($soldProductIds);
		$this->Product->recursive=-1;
		$soldProducts=$this->Product->find('all',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>[
				'Product.id'=>$soldProductIds,
			],
			'order'=>'Product.name',
		]);
		
    $orderIds=array_unique($orderIds);
    //pr($orderIds);
		$buyingClientIds=array_unique($buyingClientIds);
    /*
		$buyingClientList=$this->ThirdParty->find('list',[
			'conditions'=>[
				'ThirdParty.id'=>$buyingClientIds,
			],
			'order'=>'ThirdParty.company_name',
		]);
    */
    $buyingClients=$this->ThirdParty->find('all',[
			'conditions'=>[
				'ThirdParty.id'=>$buyingClientIds,
			],
      'contain'=>[
        'Order'=>[
          'fields'=>'Order.id',
          'conditions'=>[
            'Order.id'=>$orderIds,
          ],
        ]
      ],
			'order'=>'ThirdParty.company_name',
		]);
    for ($c=0;$c<count($buyingClients);$c++){
      $clientOrderIds=[];
      foreach ($buyingClients[$c]['Order'] as $order){
        $clientOrderIds[]=$order['id'];
      }
      $buyingClients[$c]['orderIds']=array_unique( $clientOrderIds);
    }
		//pr($buyingClients);
		$stockItemIds=array_unique($stockItemIds);
		$stockItemList=$this->StockItem->find('list',[
			'conditions'=>[
				'StockItem.id'=>$stockItemIds,
			],
		]);
		
		//pr($soldProducts);
		//pr($buyingClientList);
		/*
		for ($p=0;$p<count($soldProducts);$p++){
			//echo "soldProduct is ".$soldProducts[$p]['Product']['name']."<br/>";
			$movementConditionsForPeriodAndProduct=$movementConditions;
			$movementConditionsForPeriodAndProduct[]=array('StockMovement.product_id'=>$soldProducts[$p]['Product']['id']);
			$movementsForPeriodAndProduct=$this->StockMovement->find('all',array(
				'fields'=>array(
					'StockMovement.stockitem_id',				
				),
				'conditions'=>$movementConditionsForPeriodAndProduct,
				'contain'=>array(
					'StockItem'=>array(
						'fields'=>array('StockItem.raw_material_id'),
					),
				),
			));
			//pr($movementsForPeriodAndProduct);
			$rawMaterialIds=[];
			foreach($movementsForPeriodAndProduct as $movement){
				//pr($movement);
				$rawMaterialIds[]=$movement['StockItem']['raw_material_id'];
			}
			$rawMaterialIds=array_unique($rawMaterialIds);	
			// now retrieve what we need for the rawmaterials
			$rawMaterials=$this->Product->find('all',array(
				'fields'=>array('Product.id','Product.name'),
				'conditions'=>array(
					'Product.id'=>$rawMaterialIds,
				),
				'order'=>'Product.name',
			));
			//pr($rawMaterials);
			//echo "count of raw materials is ".count($rawMaterials)."<br/>";
			if ($soldProducts[$p]['Product']['id']==12){
				//echo "product material is ".$soldProducts[$p]['Product']['id']."<br/>";
			}
			for ($r=0;$r<count($rawMaterials);$r++){
				if ($soldProducts[$p]['Product']['id']==12){
					//echo "raw material is ".$rawMaterials[$r]['Product']['id']."<br/>";
				}
				//echo "r is ".$r."<br/>";
				//pr($rawMaterials[$r]);
				$rawMaterialStockItemIds=$this->StockItem->find('list',array(
					'fields'=>array('StockItem.id'),
					'conditions'=>array(
						'StockItem.id'=>$stockItemIds,
						'StockItem.raw_material_id'=>$rawMaterials[$r]['Product']['id'],
						'StockItem.product_id'=>$soldProducts[$p]['Product']['id'],
					),
				));
				$movementConditionsForPeriodAndProductAndRawMaterial=$movementConditionsForPeriodAndProduct;
				$movementConditionsForPeriodAndProductAndRawMaterial[]=array('StockMovement.stockitem_id'=>$rawMaterialStockItemIds);
				$stockItemIdsForRawMaterialAndFinishedProduct=$this->StockMovement->find('list',array(
					'fields'=>array(
						'StockMovement.stockitem_id',		
					),
					'conditions'=>$movementConditionsForPeriodAndProductAndRawMaterial,
				));
				if ($soldProducts[$p]['Product']['id']==12){
					//echo "movement conditions for CL 365ML and preforma 21 <br/>";
					//pr($movementConditionsForPeriodAndProductAndRawMaterial);
					//echo "stock item ids for CL 365ML and preforma 21 <br/>";
					//pr($stockItemIdsForRawMaterialAndFinishedProduct);
				}
				$clientProductArray=[];
				
				foreach ($buyingClientList as $clientId=>$clientName){
					$orderConditionsForClient=array(
						'Order.order_date >='=>$startDate,
						'Order.order_date <'=>$endDatePlusOne,
						'Order.bool_annulled'=>false,
						'Order.third_party_id'=>$clientId,
						'Order.stock_movement_type_id'=>MOVEMENT_SALE,
					);
					$orderIdsForClient=$this->Order->find('list',array(
						'fields'=>array('Order.id'),
						'conditions'=>$orderConditionsForClient,
					));
					
					$movementConditionsForPeriodAndProductAndRawMaterialAndClient=$movementConditionsForPeriodAndProductAndRawMaterial;
					$movementConditionsForPeriodAndProductAndRawMaterialAndClient[]=array('StockMovement.order_id'=>$orderIdsForClient);
					//echo "showing the ocnditions for period and product and raw material and client";
					//pr($movementConditionsForPeriodAndProductAndRawMaterialAndClient);
					$this->StockMovement->virtualFields['product_total'] = 0;
					$quantityPurchasedForClient = $this->StockMovement->find('all', array(
						'fields' => array('SUM(StockMovement.product_quantity) AS StockMovement__product_total'),
						'conditions'=>$movementConditionsForPeriodAndProductAndRawMaterialAndClient,
					));
					//pr($quantityPurchasedForClient);
					
					$clientData=[];
					$clientData['client_id']=$clientId;
					$clientData['client_name']=$clientName;
					if (!empty($quantityPurchasedForClient['0']['StockMovement']['product_total'])){
						$clientData['product_quantity']=$quantityPurchasedForClient['0']['StockMovement']['product_total'];
					}
					else {
						$clientData['product_quantity']=0;
					}
					
					$clientProductArray[]=$clientData;
				}
				$rawMaterials[$r]['Clients']=$clientProductArray;
			}
			//pr($rawMaterials);
			$soldProducts[$p]['RawMaterials']=$rawMaterials;
		}
		*/
    //pr($soldProducts);
    
    for ($p=0;$p<count($soldProducts);$p++){
			$movementConditionsForPeriodAndProduct=$movementConditions;
			$movementConditionsForPeriodAndProduct[]=['StockMovement.product_id'=>$soldProducts[$p]['Product']['id']];
			$movementsForPeriodAndProduct=$this->StockMovement->find('all',[
				'fields'=>['StockMovement.stockitem_id',				],
				'conditions'=>$movementConditionsForPeriodAndProduct,
				'contain'=>[
					'StockItem'=>['fields'=>['StockItem.raw_material_id'],],
				],
			]);
			$rawMaterialIds=[];
			foreach($movementsForPeriodAndProduct as $movement){
				$rawMaterialIds[]=$movement['StockItem']['raw_material_id'];
			}
			$rawMaterialIds=array_unique($rawMaterialIds);	
			$rawMaterials=$this->Product->find('all',[
				'fields'=>['Product.id','Product.name'],
				'conditions'=>['Product.id'=>$rawMaterialIds,],
				'order'=>'Product.name',
			]);
			
			$soldProducts[$p]['RawMaterials']=$rawMaterials;
    }
    //pr($soldProducts);
    //pr ($buyingClients);
    
    for ($c=0;$c<count($buyingClients);$c++){
      $orderIdsForClient=$buyingClients[$c]['orderIds'];
      
      foreach ($soldProducts as $product){
        $movementConditionsForPeriodAndProduct=$movementConditions;
        $movementConditionsForPeriodAndProduct[]=['StockMovement.product_id'=>$product['Product']['id']];
        
        foreach ($product['RawMaterials'] as $rawMaterial){
          $rawMaterialStockItemIds=$this->StockItem->find('list',[
            'fields'=>['StockItem.id'],
            'conditions'=>[
              'StockItem.id'=>$stockItemIds,
              'StockItem.raw_material_id'=>$rawMaterial['Product']['id'],
              'StockItem.product_id'=>$product['Product']['id'],
            ],
          ]);
          $movementConditionsForPeriodAndProductAndRawMaterial=$movementConditionsForPeriodAndProduct;
          $movementConditionsForPeriodAndProductAndRawMaterial[]=['StockMovement.stockitem_id'=>$rawMaterialStockItemIds];
          $movementConditionsForPeriodAndProductAndRawMaterialAndClient=$movementConditionsForPeriodAndProductAndRawMaterial;
					$movementConditionsForPeriodAndProductAndRawMaterialAndClient[]=['StockMovement.order_id'=>$orderIdsForClient];
          $this->StockMovement->virtualFields['product_total'] = 0;
          $quantityPurchasedForClient = $this->StockMovement->find('all', [
            'fields' => ['SUM(StockMovement.product_quantity) AS StockMovement__product_total'],
            'conditions'=>$movementConditionsForPeriodAndProductAndRawMaterialAndClient,
          ]);
          if (!empty($quantityPurchasedForClient['0']['StockMovement']['product_total'])){
            $buyingClients[$c]['quantities'][]=['product_quantity'=>$quantityPurchasedForClient['0']['StockMovement']['product_total']];
          }
          else {
            $buyingClients[$c]['quantities'][]=['product_quantity'=>0];
          }
        }
      }
    }
		//pr($buyingClients);
		$this->set(compact('soldProducts','buyingClients','startDate','endDate'));

	}
	
	public function guardarReporteVentaProductoPorCliente(){
		$exportData=$_SESSION['reporteVentaProductoPorCliente'];
		$this->set(compact('exportData'));
	}
  
  public function reporteEstimacionesComprasPorCliente(){
		$this->loadModel('Order');
		$this->loadModel('Product');
		$this->loadModel('StockItem');
		$this->loadModel('ThirdParty');
		
    $this->loadModel('User');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    define('NUMBER_OF_MONTHS',3);
		$this->set(compact('NUMBER_OF_MONTHS'));
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDateEstimations']) && !empty($_SESSION['endDateEstimations'])){
			$startDate=$_SESSION['startDateEstimations'];
			$endDate=$_SESSION['endDateEstimations'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$firstDayThisMonth = date("Y-m-01");
      $startDate= date( "Y-m-d", strtotime( date("Y-m-01")."-".NUMBER_OF_MONTHS." months" ) );
			$endDate=date( "Y-m-d", strtotime( date("Y-m-01")."-1 days" ) );
      $endDatePlusOne= date("Y-m-01");
		}
		
		$_SESSION['startDateEstimations']=$startDate;
		$_SESSION['endDateEstimations']=$endDate;
		
		$movementConditions=[
			'StockMovement.movement_date >='=>$startDate,
			'StockMovement.movement_date <'=>$endDatePlusOne,
			'StockMovement.product_quantity >'=>0,
			'StockMovement.production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
			'StockMovement.bool_input'=>false,
			'StockMovement.bool_reclassification'=>false,
      'StockMovement.bool_adjustment'=>false,
		];
		$movementsForPeriod=$this->StockMovement->find('all',[
			'fields'=>[
				'StockMovement.product_id',
				'StockMovement.order_id',
				'StockMovement.stockitem_id',
			],
			'conditions'=>$movementConditions,
			'contain'=>[
				'Order'=>[
					'fields'=>['Order.id','Order.third_party_id'],
				],
			],
		]);
		$soldProductIds=[];
    $orderIds=[];
		$buyingClientIds=[];
		$stockItemIds=[];
		
		foreach($movementsForPeriod as $movement){
			//pr($movement);
			$soldProductIds[]=$movement['StockMovement']['product_id'];
			$orderIds[]=$movement['Order']['id'];
      $buyingClientIds[]=$movement['Order']['third_party_id'];
			$stockItemIds[]=$movement['StockMovement']['stockitem_id'];
		}
		
		$soldProductIds=array_unique($soldProductIds);
		$this->Product->recursive=-1;
		$soldProducts=$this->Product->find('all',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>[
				'Product.id'=>$soldProductIds,
			],
			'order'=>'Product.name',
		]);
		
    $orderIds=array_unique($orderIds);
    //pr($orderIds);
		$buyingClientIds=array_unique($buyingClientIds);
    $buyingClients=$this->ThirdParty->find('all',[
			'conditions'=>[
				'ThirdParty.id'=>$buyingClientIds,
			],
      'contain'=>[
        'Order'=>[
          'fields'=>'Order.id',
          'conditions'=>[
            'Order.id'=>$orderIds,
          ],
        ]
      ],
			'order'=>'ThirdParty.company_name',
		]);
    for ($c=0;$c<count($buyingClients);$c++){
      $clientOrderIds=[];
      foreach ($buyingClients[$c]['Order'] as $order){
        $clientOrderIds[]=$order['id'];
      }
      $buyingClients[$c]['orderIds']=array_unique( $clientOrderIds);
    }
		//pr($buyingClients);
    $buyingClientList=$this->ThirdParty->find('list',[
			'conditions'=>[
				'ThirdParty.id'=>$buyingClientIds,
			],
			'order'=>'ThirdParty.company_name',
		]);
    
		$stockItemIds=array_unique($stockItemIds);
		$stockItemList=$this->StockItem->find('list',[
			'conditions'=>[
				'StockItem.id'=>$stockItemIds,
			],
		]);
		
		//pr($soldProducts);
		//pr($buyingClientList);
		    
    for ($p=0;$p<count($soldProducts);$p++){
      $quantitiesInventory=$this->StockItem->getInventoryTotalPerProduct($soldProducts[$p]['Product']['id'],date('Y-m-d'),1,true);
      //pr($quantitiesInventory);
      
			$movementConditionsForPeriodAndProduct=$movementConditions;
			$movementConditionsForPeriodAndProduct[]=['StockMovement.product_id'=>$soldProducts[$p]['Product']['id']];
			$movementsForPeriodAndProduct=$this->StockMovement->find('all',[
				'fields'=>['StockMovement.stockitem_id',				],
				'conditions'=>$movementConditionsForPeriodAndProduct,
				'contain'=>[
					'StockItem'=>['fields'=>['StockItem.raw_material_id'],],
				],
			]);
			$rawMaterialIds=[];
			foreach($movementsForPeriodAndProduct as $movement){
				$rawMaterialIds[]=$movement['StockItem']['raw_material_id'];
			}
			$rawMaterialIds=array_unique($rawMaterialIds);	
			$rawMaterials=$this->Product->find('all',[
				'fields'=>['Product.id','Product.name'],
				'conditions'=>['Product.id'=>$rawMaterialIds,],
				'order'=>'Product.name',
			]);
      for ($r=0;$r<count($rawMaterials);$r++){
        foreach ($quantitiesInventory as $inventory){
          if ($inventory['RawMaterial']['id']==$rawMaterials[$r]['Product']['id']){
            $rawMaterials[$r]['Product']['quantity_inventory']=$inventory[0]['Remaining_A'];
          }
        }  
      }
      $soldProducts[$p]['RawMaterials']=$rawMaterials;
    }
    //pr($soldProducts);
    //pr ($buyingClients);
    
    for ($c=0;$c<count($buyingClients);$c++){
      $orderIdsForClient=$buyingClients[$c]['orderIds'];
      
      foreach ($soldProducts as $product){
        $movementConditionsForPeriodAndProduct=$movementConditions;
        $movementConditionsForPeriodAndProduct[]=['StockMovement.product_id'=>$product['Product']['id']];
        
        foreach ($product['RawMaterials'] as $rawMaterial){
          $rawMaterialStockItemIds=$this->StockItem->find('list',[
            'fields'=>['StockItem.id'],
            'conditions'=>[
              'StockItem.id'=>$stockItemIds,
              'StockItem.raw_material_id'=>$rawMaterial['Product']['id'],
              'StockItem.product_id'=>$product['Product']['id'],
            ],
          ]);
          $movementConditionsForPeriodAndProductAndRawMaterial=$movementConditionsForPeriodAndProduct;
          $movementConditionsForPeriodAndProductAndRawMaterial[]=['StockMovement.stockitem_id'=>$rawMaterialStockItemIds];
          $movementConditionsForPeriodAndProductAndRawMaterialAndClient=$movementConditionsForPeriodAndProductAndRawMaterial;
					$movementConditionsForPeriodAndProductAndRawMaterialAndClient[]=['StockMovement.order_id'=>$orderIdsForClient];
          $this->StockMovement->virtualFields['product_total'] = 0;
          $quantityPurchasedForClient = $this->StockMovement->find('all', [
            'fields' => ['SUM(StockMovement.product_quantity) AS StockMovement__product_total'],
            'conditions'=>$movementConditionsForPeriodAndProductAndRawMaterialAndClient,
          ]);
          if (!empty($quantityPurchasedForClient['0']['StockMovement']['product_total'])){
            $buyingClients[$c]['quantities'][]=[
              'product_quantity_average'=>ceil($quantityPurchasedForClient['0']['StockMovement']['product_total']/NUMBER_OF_MONTHS),
              'product_quantity_total'=>$quantityPurchasedForClient['0']['StockMovement']['product_total']
            ];
          }
          else {
            $buyingClients[$c]['quantities'][]=[
              'product_quantity_average'=>0,
              'product_quantity_total'=>0,
            ];
          }
        }
      }
    }
		//pr($buyingClients);
		$this->set(compact('soldProducts','buyingClients','buyingClientList','startDate','endDate'));

	}
	
	public function guardarEstimacionesComprasPorCliente(){
		$exportData=$_SESSION['reporteEstimacionesComprasPorCliente'];
		$this->set(compact('exportData'));
	}
  
    public function index() {
		$this->StockMovement->recursive = 0;
		$this->set('stockMovements', $this->Paginator->paginate());
	}

	public function view($id = null) {
		if (!$this->StockMovement->exists($id)) {
			throw new NotFoundException(__('Invalid stock movement'));
		}
		$options = array('conditions' => array('StockMovement.' . $this->StockMovement->primaryKey => $id));
		$this->set('stockMovement', $this->StockMovement->find('first', $options));
	}
	
	public function viewStockentry($id = null) {
		if (!$this->StockMovement->exists($id)) {
			throw new NotFoundException(__('Invalid stock movement'));
		}
		$options = array('conditions' => array('StockMovement.' . $this->StockMovement->primaryKey => $id));
		$this->set('stockMovement', $this->StockMovement->find('first', $options));
	}
	
	public function viewStockremoval($id = null) {
		if (!$this->StockMovement->exists($id)) {
			throw new NotFoundException(__('Invalid stock movement'));
		}
		$options = array('conditions' => array('StockMovement.' . $this->StockMovement->primaryKey => $id));
		$this->set('stockMovement', $this->StockMovement->find('first', $options));
	}

	public function add($orderid=null) {
		if ($this->request->is('post')) {
			$this->StockMovement->create();
			if ($this->StockMovement->save($this->request->data)) {
				$this->Session->setFlash(__('The stock movement has been saved.'),'default',['class' => 'success']);
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The stock movement could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		}
		$stockItems = $this->StockMovement->StockItem->find('list');
		$orders = $this->StockMovement->Order->find('list');
		$products = $this->StockMovement->Product->find('list');
		$this->set(compact('stockItems', 'orders', 'products'));
	}
	
	public function addStockentry($orderid=null) {
		if ($this->request->is('post')) {
			//var_dump($this->request->data);
			
			$this->StockMovement->create();
			
			$this->request->data['StockMovement']['order_id']=$orderid;
			$quantity=$this->request->data['StockMovement']['product_quantity'];
			$price=$this->request->data['StockMovement']['product_price'];
			$lotname=$this->request->data['StockMovement']['name'];
			
			$linkedOrder=$this->StockMovement->Order->read(null,$orderid);
			$invoicecode=$linkedOrder['Order']['invoice_code'];
			
			$linkedProduct=$this->StockMovement->Product->read(null,$this->request->data['StockMovement']['product_id']);
			$productname=$linkedProduct['Product']['name'];
			$message="Added product ".$productname." (Quantity:".$quantity.",Unit Price:".$price.") to Purchase ".$invoicecode;
			
			$this->request->data['StockMovement']['description']=$message;
			$this->request->data['StockMovement']['order_id']=$orderid;
			$this->request->data['StockMovement']['product_type_quantity']=$producttypeid;
			$this->request->data['StockMovement']['product_type_quantity']=$producttypequantity;
			$this->request->data['StockMovement']['product_type_unit_price']=$producttypeunitprice;
			if ($this->StockMovement->save($this->request->data)) {
				$stockmovementid=$this->StockMovement->id;
				for ($i=1;$i<=$quantity;$i++){
					$StockItemData[$i]['name']=$lotname."_".$i;
					$StockItemData[$i]['stock_movement_id']=$stockmovementid;
					$StockItemData[$i]['product_type_id']=$producttypeid;
					$StockItemData[$i]['unit_price']=$producttypeunitprice;
					$StockItemData[$i]['original_quantity']=$producttypequantity;
					$StockItemData[$i]['remaining_quantity']=$producttypequantity;
				}
				if ($this->StockMovement->StockItem->saveAll($StockItemData)){
					$this->Session->setFlash(__('The stock movement has been saved.'),'default',['class' => 'success']);
					$this->recordUserActivity($this->Session->read('User.username'),$message);
					return $this->redirect(array('controller'=>'orders','action' => 'viewPurchase',$orderid));
				}
			} else {
				$this->Session->setFlash(__('The stock movement could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		}
		
		$stockItems = $this->StockMovement->StockItem->find('list');
		$this->StockMovement->Order->recursive=1;
		$orders = $this->StockMovement->Order->find('list');
		$products = $this->StockMovement->Product->find('list');
		$this->set(compact('stockItems', 'orders', 'products'));
	}
	
	public function addStockremoval($orderid=null) {
		$this->loadModel('StockItem');
		if ($this->request->is('post')) {
			// var_dump($this->request->data);
			
			
			$this->StockMovement->create();
			$this->request->data['StockMovement']['order_id']=$orderid;
			$producttypeid=$this->request->data['StockMovement']['product_type_id'];
			$producttypequantity=$this->request->data['StockMovement']['product_type_quantity'];
			$quantitysold=$producttypequantity;
			$producttypeunitprice=$this->request->data['StockMovement']['product_type_unit_price'];
			$movementdate=$this->request->data['StockMovement']['movement_date'];
			$resultcodeid=$this->request->data['StockMovement']['production_result_code_id'];
			
			$linkedOrder=$this->StockMovement->Order->read(null,$orderid);
			$invoicecode=$linkedOrder['Order']['invoice_code'];
			
			$linkedProduct=$this->StockMovement->ProductType->read(null,$producttypeid);
			$producttypename=$linkedProduct['ProductType']['name'];
			
			$message="Removed from stock producttype ".$producttypename." (Quantity:".$producttypequantity.",Unit Price:".$producttypeunitprice.") to Sale ".$invoicecode;
			
			$this->request->data['StockMovement']['description']=$message;
			$this->request->data['StockMovement']['order_id']=$orderid;
			//$this->request->data['StockMovement']['product_type_id']=$producttypeid;
			//$this->request->data['StockMovement']['product_type_quantity']=$producttypequantity;
			//$this->request->data['StockMovement']['product_type_unit_price']=$producttypeunitprice;
			if ($this->StockMovement->save($this->request->data)) {
				$stockmovementid=$this->StockMovement->id;
				$soldFinishedMaterials= $this->StockItem->getFinishedMaterialsForSale($producttypeid,$resultcodeid,$quantitysold,$movementdate,$originWarehouseId);
				
				$i=0;
				pr($soldFinishedMaterials);
				foreach ($soldFinishedMaterials as $soldFinishedMaterial){
					$stockItemId= $soldFinishedMaterial['id'];
					$finishedname= $soldFinishedMaterial['name'];
				
					$finishedunitprice=$soldFinishedMaterial['unit_price'];
					$unitprice=$finishedunitprice;
					$quantitypresent=$soldFinishedMaterial['quantity_present'];
					$quantityused= $soldFinishedMaterial['quantity_used'];
					$quantityremaining=$soldFinishedMaterial['quantity_remaining'];
					
					$message = "Used quantity ".$quantityused." of finished product ".$finishedname." in sale ".$invoicecode." (prior to sale: ".$quantitypresent."|remaining: ".$quantityremaining.")";
					$stockItemId=$soldFinishedMaterial['id'];
					
					$StockItemData['StockItem']['id']=$stockItemId;
					$StockItemData['StockItem']['remaining_quantity']=$quantityremaining;
					$this->StockItem->save($StockItemData);
					/*
					$finishedStockMovement['StockMovement']['name']=$invoicecode."_VENTA_".$i;
					$finishedStockMovement['StockMovement']['description']=$message;
					$finishedStockMovement['StockMovement']['bool_input']=true;
					$finishedStockMovement['StockMovement']['stock_item_id']=$stockItemId;
					$finishedStockMovement['StockMovement']['production_run_id']=$productionrunid;
					$finishedStockMovement['StockMovement']['product_type_id']=$producttypeid;
					$finishedStockMovement['StockMovement']['product_type_quantity']=$quantityused;
					$finishedStockMovement['StockMovement']['product_type_unit_price']=$finishedunitprice;
					$this->StockMovement->save($StockItemData);
					*/
					$this->recordUserActivity($this->Session->read('User.username'),$message);
					$i++;
				}
				$this->Session->setFlash(__('The stock movement has been saved.'),'default',['class' => 'success']);
				$this->recordUserActivity($this->Session->read('User.username'),$message);
				return $this->redirect(array('controller'=>'orders','action' => 'verVenta',$orderid));
			}
			else {
				$this->Session->setFlash(__('The stock movement could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		}
		
		$stockItems = $this->StockMovement->StockItem->find('list');
		$finishedMaterialsInventory = $this->StockMovement->StockItem->getInventoryTotals(false);
		
		$this->set(compact('rawMaterialsInventory','usedRawMaterials'));
		
		$this->StockMovement->Order->recursive=1;
		$orders = $this->StockMovement->Order->find('list');
		$productionResultCodes = $this->StockMovement->ProductionResultCode->find('list');
		$productTypes = $this->StockMovement->ProductType->find('list',array('conditions' => array('ProductType.bool_raw' => false)));
		$this->set(compact('stockItems', 'orders', 'productTypes','productionResultCodes','finishedMaterialsInventory'));
	}

	public function edit($id = null) {
		if (!$this->StockMovement->exists($id)) {
			throw new NotFoundException(__('Invalid stock movement'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->StockMovement->save($this->request->data)) {
				$this->Session->setFlash(__('The stock movement has been saved.'),'default',['class' => 'success']);
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The stock movement could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} else {
			$options = array('conditions' => array('StockMovement.' . $this->StockMovement->primaryKey => $id));
			$this->request->data = $this->StockMovement->find('first', $options);
		}
		$stockItems = $this->StockMovement->StockItem->find('list');
		$orders = $this->StockMovement->Order->find('list');
		$products = $this->StockMovement->Product->find('list');
		$this->set(compact('stockItems', 'orders', 'products'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->StockMovement->id = $id;
		if (!$this->StockMovement->exists()) {
			throw new NotFoundException(__('Invalid stock movement'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->StockMovement->delete()) {
			$this->Session->setFlash(__('The stock movement has been deleted.'));
		} else {
			$this->Session->setFlash(__('The stock movement could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
	

}
