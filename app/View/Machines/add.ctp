<div class="machines form">
<?php 
	echo $this->Form->create('Machine'); 
	echo "<div class='col-md-6'>";
		echo "<fieldset>";
			echo "<legend>".__('Add Machine')."</legend>";
			echo $this->Form->input('name');
			echo $this->Form->input('description');
      echo $this->Form->input('plant_id',['default'=>(count($plants) == 1?array_keys($plants)[0]:0),'empty'=>[0=>'-- Planta --']]);
			echo $this->Form->input('bool_active',array('checked'=>true));
		echo "</fieldset>"; 
	echo "</div>"; 
	echo "<div id='ProductList' style='width:45%;float:left;clear:none;' class='col-md-6'>"; 
		echo "<h3>Productos que se pueden producir con esta máquina</h3>";
		echo "<div class='col-md-6' style='float:left;clear:none;'> ";
		for ($p=0;$p<ceil(count($products)/2);$p++){
			echo $this->Form->input('Product.'.$p.'.product_id',array('type'=>'checkbox','checked'=>true,'label'=>$products[$p]['Product']['name'],'div'=>array('class'=>'checkboxleft')));
		}
		echo "</div>";
		echo "<div class='col-md-6' style='float:left;clear:none;'>";
		for ($p=ceil(count($products)/2);$p<count($products);$p++){		
			echo $this->Form->input('Product.'.$p.'.product_id',array('type'=>'checkbox','checked'=>true,'label'=>$products[$p]['Product']['name'],'div'=>array('class'=>'checkboxleft')));
		}
		echo "</div>";
	echo "</div>"; 
	echo $this->Form->end(__('Submit')); 
?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Machines'), array('action' => 'index'))."</li>";
		echo "<br/>";
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))." </li>";
		}
		
	echo "</ul>";
?>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>