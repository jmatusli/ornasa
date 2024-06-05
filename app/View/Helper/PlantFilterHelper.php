<?php 
	class PlantFilterHelper extends AppHelper {
		var $helpers = ['Form','Html']; // include the html helper
		
		function displayPlantFilter($plants,$userRoleId,$default){
      $plantFilterInput='';
      switch (count($plants) ){
        case 0:
          $plantFilterInput=$this->Form->input('plant_id',['label'=>'Planta','value'=>$default,'type'=>'hidden']);
          break;  
        case 1:
          $plantFilterInput=$this->Form->input('plant_id',['label'=>'Planta','value'=>$default]);
          break;  
        default:
          $plantFilterInput=$this->Form->input('plant_id',['label'=>'Planta','value'=>$default,'empty'=>[0=>'-- Seleccione Planta --']]);
      }
			return $plantFilterInput;
		}
	}
?>