<?php 
	class WarehouseFilterHelper extends AppHelper {
		var $helpers = ['Form','Html']; // include the html helper
		
		function displayWarehouseFilter($warehouses,$userRoleId,$value,$params=[]){
      $inputOptions=[
        'label'=>'Bodega',
        'value'=>$value,
      ];
      switch (count($warehouses) ){
        case 0:
          $inputOptions['type']='hidden';
          break;  
        case 1:
          break;  
        default:
          $inputOptions['empty']=[0=>'-- Seleccione Bodega --'];
      }
      $inputOptions=array_merge($inputOptions,$params);
      $warehouseFilterInput=$this->Form->input('warehouse_id',$inputOptions);
			return $warehouseFilterInput;
		}
	}
?>