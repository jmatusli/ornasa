<script>
  $('body').on('change','.powerselector',function(e){
		if ($(this).is(':checked')){
			$(this).closest('fieldset').find('td.selector input').prop('checked',true);
      $(this).closest('fieldset').find('input.powerselector').prop('checked',true);
      //$(this).closest('fieldset').find('tr').removeClass('noprint');
		}
		else {
			$(this).closest('fieldset').find('td.selector input').prop('checked',false);
			$(this).closest('fieldset').find('input.powerselector').prop('checked',false);
      //$(this).closest('fieldset').find('tr').addClass('noprint');
		}
    //triggerAllSelectors();
	});
</script>
<div class="stockItems view report">

<?php
	echo "<h1>Desactivar lotes</h1>";
  
  echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->Form->input('Report.limit_creation_date',[
        'type'=>'date',
        'label'=>'Creación Lote antes de',
        'dateFormat'=>'DMY',
        'selected'=>$limitCreationDate,
        'minYear'=>2014,
        'maxYear'=>date('Y'),
        'div'=>['class'=>'input date label50'],
      ]);
			echo $this->Form->input('Report.limit_depletion_date',[
        'type'=>'date',
        'label'=>'Lote llegó a cero antes de',
        'dateFormat'=>'DMY',
        'selected'=>$limitDepletionDate,
        'minYear'=>2014,
        'maxYear'=>date('Y'),
        'div'=>['class'=>'input date label50'],
      ]);
			echo $this->Form->input('Report.stock_item_log_quantity',[
        'type'=>'number',
        'label'=>'Mostrar últimos x logs de lote',
        'value'=>$stockItemLogQuantity,
        'div'=>['class'=>'input number label50'],
      ]);
			//echo $this->Form->input('Report.product_category_id',array('label'=>__('Categoría de Producto'),'default'=>$productCategoryId));
		
		
	echo $this->Form->Submit('Establecer condiciones',['name'=>'setConditions','style'=>'min-width:250px;']);
  
  if (empty($activeStockItems)){
    echo '<h2>No hay lotes que corresponden con las condiciones especificadas</h2>';
  }
  else {
    $stockItemCount=0;
  
    $stockItemTableHeader='';
    $stockItemTableHeader.='<thead>';
      $stockItemTableHeader.='<tr>';
        $stockItemTableHeader.='<th style="min-width:35px;"></th>';
        $stockItemTableHeader.='<th style="min-width:125px;">Fecha creación lote</th>';
        $stockItemTableHeader.='<th style="min-width:125px;">Fecha lote agotado</th>';
        $stockItemTableHeader.='<th>Producto</th>';
        $stockItemTableHeader.='<th>Logs</th>';
        $stockItemTableHeader.='<th>Bodega</th>';
      $stockItemTableHeader.='</tr>';
    $stockItemTableHeader.='</thead>';
    
    $stockItemTableRows="";
    $totalSelected=0;
    foreach ($activeStockItems as $stockItem){
      if ($stockItem['StockItemLog'][0]['product_quantity'] <= 0){
        //if the last stockitemlog has a positive quantity the stockitem has been depleted very recently
        
        $stockItemCount++;
        
        $stockItemCreationDateTime=new Datetime($stockItem['StockItem']['stockitem_creation_date']);
        $stockItemDepletionDateTime=new Datetime($stockItem['StockItemLog'][0]['stockitem_date']);   
        
        $stockItemTableRow='';
        if ($stockItem['StockItem']['remaining_quantity'] == 0){
          $stockItemTableRow.='<tr>';
        }
        else {
          $stockItemTableRow.='<tr style="background-color:#ff7f7f;">';
        }
          $stockItemTableRow.='<td class="selector">';
            $stockItemTableRow.=$this->Form->input('StockItem.'.$stockItem['StockItem']['id'].'.selector',[
              'checked'=>false,
              'label'=>false,
              'style'=>'width:100%',
            ]);
            $stockItemTableRow.=$this->Form->input('StockItem.'.$stockItem['StockItem']['id'].'.stockitem_depletion_date',[
              'type'=>'hidden',
              'value'=>$stockItem['StockItemLog'][0]['stockitem_date'],
            ]);
          $stockItemTableRow.='</td>';
          $stockItemTableRow.='<td>'.$stockItemCreationDateTime->format('d-m-Y').'</td>';
          $stockItemTableRow.='<td>'.$this->Html->link($stockItemDepletionDateTime->format('d-m-Y').' '.$stockItem['StockItem']['id'],['action'=>'view',$stockItem['StockItem']['id']]).'</td>';
          $stockItemTableRow.='<td>'.$stockItem['Product']['name'].(empty($stockItem['RawMaterial']['id'])?'':(' '.$stockItem['RawMaterial']['name'])).(empty($stockItem['ProductionResultCode'])?'':(' '.$stockItem['ProductionResultCode']['code'])).'</td>';
          $stockItemTableRow.='<td>';
          foreach ($stockItem['StockItemLog'] as $stockItemLog){
            //pr($stockItemLog);
            $stockItemLogDateTime = new DateTime($stockItemLog['stockitem_date']); 
            $stockItemTableRow.=$stockItemLogDateTime->format('d-m-Y H:i:s').' ';
            $stockItemTableRow.='# '.$stockItemLog['product_quantity'].' ';
            if (!empty($stockItemLog['StockMovement'])){
              if (!empty($stockItemLog['StockMovement']['Order'])){
                $stockItemTableRow.=$this->Html->link($stockItemLog['StockMovement']['Order']['order_code'],['controller'=>'orders','action'=>($stockItemLog['StockMovement']['bool_input']?'verEntrada':($stockItem['ProductionResultCode']['id'] > PRODUCTION_RESULT_CODE_A?'verRemision':'verVenta')),$stockItemLog['StockMovement']['Order']['id']]).' ';  
              }
              else {
                //pr($stockItemLog['StockMovement']);
                $stockItemTableRow.=$stockItemLog['StockMovement']['id'].' ';
              }
            }
            elseif (!empty($stockItemLog['ProductionMovement'])){
              if (!empty($stockItemLog['ProductionMovement']['ProductionRun'])){
                $stockItemTableRow.=$this->Html->link($stockItemLog['ProductionMovement']['ProductionRun']['production_run_code'],['controller'=>'productionRuns','action'=>'detalle',$stockItemLog['ProductionMovement']['ProductionRun']['id']]).' ';    
              }
              else {
                //pr($stockItemLog['ProductionMovement']);
                $stockItemTableRow.=$stockItemLog['ProductionMovement']['id'].' ';
              }
            }
            else {
              //pr($stockItemLog);
            }
            if ($stockItemLog['id'] > $stockItem['StockItemLog'][count($stockItem['StockItemLog'])-1]['id']){
              $stockItemTableRow.='<br/>';
            }
          }  
          $stockItemTableRow.='</td>';
          $stockItemTableRow.='<td>'.$stockItem['Warehouse']['name'].'</td>';
          
        $stockItemTableRow.='</tr>';
        $stockItemTableRows.=$stockItemTableRow;
      }
    }
    $stockItemTableBody='<tbody>'.$stockItemTableRows.'</tbody>';
  
    if ($stockItemCount ==0){
      echo '<h2>Hay lotes que se agotaron, pero después de la fecha de agotado, lo que significa que los procesos involucrados aun podrían estar editados</h2>';
    }  
    else {
      echo "<h2>Lotes activos agotados</h2>";
      echo $this->Form->input('powerselector1',['class'=>'powerselector','checked'=>false,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar lotes','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ],'div'=>['class'=>'input checkbox noprint']]);
    
      echo $this->Form->Submit('Desactivar lotes',['name'=>'deactivateStockItems','style'=>'min-width:250px;']);
    
      echo "<table id='pagosADepositar'>".$stockItemTableHeader.$stockItemTableBody."</table>";
      echo $this->Form->input('powerselector2',['class'=>'powerselector','checked'=>false,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar lotes','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ],'div'=>['class'=>'input checkbox noprint']]);
      
      echo $this->Form->Submit('Desactivar lotes',['name'=>'deactivateStockItems','style'=>'min-width:250px;']);
    }
  }
  
  
    echo "</fieldset>";
	echo $this->Form->End();
	
?>

