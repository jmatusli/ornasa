<div class="incidences view">
<?php 
	echo "<h2".($incidence['CreatingUser']['bool_active']?"":" class='italic'").">".__('Incidence')." ".$incidence['Incidence']['name'].($incidence['CreatingUser']['bool_active']?"":" (desactivado)")."</h2>";
	echo "<dl>";
		echo "<dt>".__('Name')."</dt>";
		echo "<dd>".h($incidence['Incidence']['name'])."</dd>";
    echo "<dt>".__('List Order')."</dt>";
		echo "<dd>".h($incidence['Incidence']['list_order'])."</dd>";
		echo "<dt>".__('Creating User')."</dt>";
		echo "<dd>".$incidence['CreatingUser']['username']."</dd>";
	echo "</dl>";
  echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
    if ($bool_edit_permission){
      echo "<li>".$this->Html->link(__('Edit Incidence'), array('action' => 'editarIncidencia', $incidence['Incidence']['id']))."</li>";
     echo "<br>";
    }
		if ($bool_delete_permission){
      echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $incidence['Incidence']['id']), array(), __('Está seguro que quiere eliminar la incidencia %s?', $incidence['Incidence']['name']))."</li>";
      echo "<br>";
    }
		echo "<li>".$this->Html->link(__('List Incidences'), array('action' => 'resumenIncidencias'))."</li>";
		echo "<li>".$this->Html->link(__('New Incidence'), array('action' => 'crearIncidencia'))."</li>";
		
	echo "</ul>";
?> 
</div>
<div class="related">
<?php
  if (!empty($incidence['ProductionRuns'])){
    $tableContents=$this->ProductionRunDisplay->productionRunTableContents($incidence['ProductionRuns'], false,$userrole);
    $table="<table id='incidencia_".$incidence['Incidence']['name']."'>".$tableContents."</table>";
    echo "<h3>Ordenes de Producción para incidencia ".$incidence['Incidence']['name']."</h3>";
    echo $table;
  }
?>
</div>

