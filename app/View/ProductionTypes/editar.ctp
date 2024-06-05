<div class="productionTypes form fullwidth">
<?php	
  echo $this->Form->create('ProductionType'); 
	echo '<fieldset>';
  echo '<legend>'.__('Edit Production Type')	.'</legend>';
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('short_description');
		echo $this->Form->input('long_description');
		echo $this->Form->input('list_order');
		echo $this->Form->input('hex_color');
    
     
        echo '<h3>'.__('Plants').'</h3>';
        foreach ($plants as $plantId=>$plantName){
          echo $this->Form->input('Plant.'.$plantId.'.bool_assigned',[
            'label'=>$plantName,
            'type'=>'checkbox',
            'checked'=>in_array($plantId,array_keys($productionTypePlants)),
            'div'=>['class'=>'div input checkboxleftbig'],
          ]);
        }
     
	echo '</fieldset>';
  echo $this->Form->Submit(__('Submit'));
  echo $this->Form->end();
?>
</div>

<div class="actions">
<?php	
/*
  echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Form->postLink(__('Delete'), ['action' => 'delete', $this->Form->value('ProductionType.id')], [], __('Are you sure you want to delete # %s?', $this->Form->value('ProductionType.id'))).'</li>';
		echo '<li>'.$this->Html->link(__('List Production Types'), ['action' => 'resumen']).'</li>';
	echo '</ul>';
*/
?>
</div>
