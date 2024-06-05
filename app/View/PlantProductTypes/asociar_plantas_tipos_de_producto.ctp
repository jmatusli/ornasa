<script>
	$('body').on('change','.assignment',function(){
		$(this).closest('tr').find('.changed').val(1);
	});
</script>
<div class="clients asociarplantastiposdeproducto fullwidth" style="overflow-x:auto">
<?php 
	echo $this->Form->create('PlantProductType');
	echo "<fieldset>";
		echo $this->Form->input('plant_id',['label'=>'Plant','default'=>$selectedPlantId,'empty'=>[0=>'-- Planta --']]);
		echo $this->Form->input('product_type_id',['default'=>$selectedProductTypeId,'empty'=>[0=>'-- Tipo de Producto --']]);
		echo $this->Form->Submit(__('Actualizar'),['id'=>'refresh','name'=>'refresh']);
		echo "<legend>".__('Asociar Tipos de Producto con Plantas')."</legend>";
		echo $this->Form->Submit(__('Guardar'),['id'=>'submit','name'=>'submit']);	
    echo "<br/>";
    echo $this->Html->link(__('Guardar como Excel'),['action' => 'guardarAsociacionesPlantasTiposDeProducto'],['class' => 'btn btn-primary']); 
		
    $excelOutput='';
    
    $tableHead="";
    $tableHead.="<thead>";
      $tableHead.="<tr>";
        $tableHead.="<th>".__('Production Types')."</th>";
        foreach ($selectedPlants as $plantId=>$plantValue){
          $tableHead.="<th>".$this->Html->link($plantValue,['controller'=>'plants','action'=>'detalle',$plantId])."</th>";
        }
      $tableHead.="</tr>";
    $tableHead.="</thead>";
    $excelHead="";
    $excelHead.="<thead>";
      $excelHead.="<tr>";
        $excelHead.='<th>Tipo de Producto</th>';
        foreach ($selectedPlants as $plantId=>$plantValue){
          $excelHead.="<th>".$plantValue."</th>";
        }
      $excelHead.="</tr>";
    $excelHead.="</thead>";
    
    $tableBody="<tbody>";
    for ($pt=0;$pt<count($selectedProductTypes);$pt++){
      //pr($selectedProductTypes][$pt]);
      $tableBody.="<tr>";
        $tableBody.="<td>";
          $tableBody.=$this->Html->link($selectedProductTypes[$pt]['ProductType']['name'],['controller'=>'productTypes','action'=>'view',$selectedProductTypes[$pt]['ProductType']['id']]);
          $tableBody.=$this->Form->input('ProductType.'.$selectedProductTypes[$pt]['ProductType']['id'].'.bool_changed',['type'=>'hidden','label'=>false,'value'=>0,'class'=>'changed']);
        $tableBody.="</td>";
        if (empty($selectedProductTypes[$pt]['Plant'])){
        foreach ($selectedPlants as $plantId=>$plantValue){
            $tableBody.="<td>";
              $tableBody.=$this->Form->input('ProductType.'.$selectedProductTypes[$pt]['ProductType']['id'].'.Plant.'.$plantId.'.bool_assigned',[
                'type'=>'checkbox',
                'label'=>false,
                'checked'=>false,
                'class'=>'assignment',
              ]);
            $tableBody.="</td>";
          }
        }
        else {
          foreach ($selectedPlants as $plantId=>$plantValue){
            $tableBody.="<td>";
              $tableBody.=$this->Form->input('ProductType.'.$selectedProductTypes[$pt]['ProductType']['id'].'.Plant.'.$plantId.'.bool_assigned',[
                'type'=>'checkbox',
                'label'=>false,
                'checked'=>$selectedProductTypes[$pt]['Plant'][$plantId],
                'class'=>'assignment',
              ]);
            $tableBody.="</td>";
          }
        }
      $tableBody.="</tr>";			
    }
    $tableBody.="</tbody>";
    $excelBody="</tbody>";
    $excelBody="<tbody>";
    for ($pt=0;$pt<count($selectedProductTypes);$pt++){
      //pr($selectedProductTypes[$pt]);
      $excelBody.="<tr>";
        $excelBody.="<td>";
          $excelBody.=$selectedProductTypes[$pt]['ProductType']['name'];
        $excelBody.="</td>";
        if (empty($selectedProductTypes[$pt]['Plant'])){
          foreach ($selectedPlants as $plantId=>$plantValue){
            $excelBody.="<td>0</td>";
          }
        }
        else {
          foreach ($selectedPlants as $plantId=>$plantValue){
            $excelBody.="<td>".($selectedProductTypes[$pt]['Plant'][$plantId]?"1":"0")."</td>";
          }
        }
      $excelBody.="</tr>";			
    }
    $excelBody.="</tbody>";
    $table="<table cellpadding='0' cellspacing='0'>".$tableHead.$tableBody."</table>";
    echo $table;
    $excelOutput.="<table id='tipos_de_producto_plantas'>".$excelHead.$excelBody."</table>";
  
  $_SESSION['resumenAsociacionesPlantasTiposDeProducto'] = $excelOutput;
   
	echo "</fieldset>";
	echo $this->Form->Submit(__('Guardar'));
	echo $this->Form->End();

?>
</div>
