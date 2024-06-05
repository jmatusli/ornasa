<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
  
  $('body').on('change','#MachinePlantId',function(){	
		$('#plantSelection').trigger('click')
	});
</script>
<div class="machines form">
<?php 
	echo $this->Form->create('Machine',['style'=>'margin-right:0;width:100%;']); 
  echo '<div class="container-fluid">';
    echo '<div class="row">';
      echo '<div class="col-sm-6">';
        echo "<fieldset>";
          echo "<legend>".__('Edit Machine')."</legend>";

          echo $this->Form->input('id',['hidden'=>true]);
          echo $this->Form->input('name',['style'=>'width:225px']);
          echo $this->Form->input('description',['style'=>'width:225px']);
          echo $this->Form->input('plant_id',['empty'=>[0=>'-- Planta --']]);
          echo $this->Form->Submit('Cambiar planta',['id'=>'plantSelection','name'=>'plantSelection']);
          echo $this->Form->input('bool_active',['div'=>['class'=>'div checkboxleft']]);
          echo $this->Form->Submit(__('Save')); 
        
      echo "</div>";
      echo "<div id='ProductList' style='float:left;clear:none;' class='col-sm-6'>"; 
        echo "<h3>Productos que se pueden producir con esta máquina</h3>";
        echo '<div class="row">';
          echo "<div class='col-md-6' style='float:left;clear:none;'> ";
          for ($p=0;$p<ceil(count($products)/2);$p++){
            $productChecked=false;
            //pr($products[$p]);
            if (!empty($products[$p]['MachineProduct'])&& $products[$p]['MachineProduct'][0]['bool_assigned']){
              $productChecked=true;
            }
            echo $this->Form->input('Product.'.$p.'.product_id',[
              'type'=>'hidden',
              'value'=>$products[$p]['Product']['id'],
            ]);
            echo $this->Form->input('Product.'.$p.'.bool_product',[
              'type'=>'checkbox',
              'checked'=>$productChecked,
              'label'=>$products[$p]['Product']['name'],
              'div'=>['class'=>'checkboxleft'],
              'productid'=>$products[$p]['Product']['id']
            ]);
          }
          echo "</div>";
          echo "<div class='col-md-6' style='float:left;clear:none;'>";
          for ($p=ceil(count($products)/2);$p<count($products);$p++){
            $productChecked=false;
            if (!empty($products[$p]['MachineProduct']) && $products[$p]['MachineProduct'][0]['bool_assigned']){
              $productChecked=true;
            }
            echo $this->Form->input('Product.'.$p.'.product_id',[
              'type'=>'hidden',
              'value'=>$products[$p]['Product']['id'],
            ]);
            echo $this->Form->input('Product.'.$p.'.bool_product',[
              'type'=>'checkbox',
              'checked'=>$productChecked,
              'label'=>$products[$p]['Product']['name'],
              'div'=>['class'=>'checkboxleft'],
              'productid'=>$products[$p]['Product']['id']
            ]);
          }
          echo '</div>';
        echo '</div>';  
      echo '</div>';
    echo '</div>';  
	echo "</div>"; 
  echo "</fieldset>";
	echo $this->Form->end(); 
?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission){ 
			//echo "<li>".$this->Form->postLink(__('Delete Machine'), ['action' => 'delete', $this->Form->value('Machine.id')], array(), __('Are you sure you want to delete # %s?', $this->Form->value('Machine.id')))."</li>";
			//echo "<br/>";
		} 
		echo "<li>".$this->Html->link(__('List Machines'), ['action' => 'resumen'])."</li>";
		echo "<br/>";
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), ['controller' => 'productionRuns', 'action' => 'resumen'])." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), ['controller' => 'productionRuns', 'action' => 'crear'])." </li>";
		}
		
	echo "</ul>";
?>	
</div>