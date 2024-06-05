<script>
	$('body').on('change','.assignment',function(){
		$(this).closest('tr').find('.changed').val(1);
	});
</script>
<div class="clients asociarplantasclientes fullwidth" style="overflow-x:auto">
<?php 
	echo $this->Form->create('PlantThirdParty');
	echo "<fieldset>";
		echo $this->Form->input('plant_id',['label'=>'Plant','default'=>$selectedPlantId,'empty'=>[0=>'-- Planta --']]);
		echo $this->Form->input('third_party_id',['label'=>'Cliente','default'=>$selectedThirdPartyId,'empty'=>[0=>'-- Cliente --']]);
		echo $this->Form->Submit(__('Actualizar'),['id'=>'refresh','name'=>'refresh']);
		echo "<legend>".__('Asociar Clientes con Plantas')."</legend>";
		echo $this->Form->Submit(__('Guardar'),['id'=>'submit','name'=>'submit']);	
    echo "<br/>";
    echo $this->Html->link(__('Guardar como Excel'),['action' => 'guardarAsociacionesPlantasClientes'],['class' => 'btn btn-primary']); 
		
    $excelOutput='';
    
    $tableHead="";
    $tableHead.="<thead>";
      $tableHead.="<tr>";
        $tableHead.="<th>".__('Clientes')."</th>";
        foreach ($selectedPlants as $plantId=>$plantData){
          $tableHead.="<th>".$this->Html->link($plantData,['controller'=>'plants','action'=>'detalle',$plantId])."</th>";
        }
      $tableHead.="</tr>";
    $tableHead.="</thead>";
    $excelHead="";
    $excelHead.="<thead>";
      $excelHead.="<tr>";
        $excelHead.='<th>Cliente</th>';
        foreach ($selectedPlants as $plantId=>$plantValue){
          $excelHead.="<th>".$plantValue."</th>";
        }
      $excelHead.="</tr>";
    $excelHead.="</thead>";
    
    $excelBody=$tableBody='';
    
    $tableBody.="<tbody>";
    for ($tp=0;$tp<count($selectedThirdParties);$tp++){
      //pr($selectedThirdParties][$tp]);
      $tableBody.="<tr>";
        $tableBody.="<td>";
          $tableBody.=$this->Html->link($selectedThirdParties[$tp]['ThirdParty']['company_name'],['controller'=>'thirdParties','action'=>'view',$selectedThirdParties[$tp]['ThirdParty']['id']]);
          $tableBody.=$this->Form->input('ThirdParty.'.$selectedThirdParties[$tp]['ThirdParty']['id'].'.bool_changed',['type'=>'hidden','label'=>false,'value'=>0,'class'=>'changed']);
        $tableBody.="</td>";
        if (empty($selectedThirdParties[$tp]['Plant'])){
        foreach ($selectedPlants as $plantId=>$plantValue){
            $tableBody.="<td>";
              $tableBody.=$this->Form->input('ThirdParty.'.$selectedThirdParties[$tp]['ThirdParty']['id'].'.Plant.'.$plantId.'.bool_assigned',[
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
              $tableBody.=$this->Form->input('ThirdParty.'.$selectedThirdParties[$tp]['ThirdParty']['id'].'.Plant.'.$plantId.'.bool_assigned',[
                'type'=>'checkbox',
                'label'=>false,
                'checked'=>$selectedThirdParties[$tp]['Plant'][$plantId],
                'class'=>'assignment',
              ]);
            $tableBody.="</td>";
          }
        }
      $tableBody.="</tr>";			
    }
    $tableBody.="</tbody>";
    
    $excelBody="<tbody>";
    for ($tp=0;$tp<count($selectedThirdParties);$tp++){
      //pr($selectedThirdParties[$tp]);
      $excelBody.="<tr>";
        $excelBody.="<td>";
          $excelBody.=$selectedThirdParties[$tp]['ThirdParty']['company_name'];
        $excelBody.="</td>";
        if (empty($selectedThirdParties[$tp]['Plant'])){
          foreach ($selectedPlants as $plantId=>$plantValue){
            $excelBody.="<td>0</td>";
          }
        }
        else {
          foreach ($selectedPlants as $plantId=>$plantValue){
            $excelBody.="<td>".($selectedThirdParties[$tp]['Plant'][$plantId]?"1":"0")."</td>";
          }
        }
      $excelBody.="</tr>";			
    }
    $excelBody.="</tbody>";
  
    $table="<table cellpadding='0' cellspacing='0'>".$tableHead.$tableBody."</table>";
    echo $table;
    $excelOutput.="<table id='plantas_clientes'>".$excelHead.$excelBody."</table>";
  
  $_SESSION['resumenAsociacionesPlantasClientes'] = $excelOutput;
   
	echo "</fieldset>";
	echo $this->Form->Submit(__('Guardar'));
	echo $this->Form->End();

?>
</div>
