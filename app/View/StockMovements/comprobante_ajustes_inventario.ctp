<script>
  function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span.amountright").each(function(){
			$(this).number(true,2);
			$(this).parent().find('span.currency').text("C$");
		});
	}
	
  $(document).ready(function(){
    formatNumbers();
		formatCurrencies();
    
    $('select.fixed option:not(:selected)').attr('disabled', true);
  });
  
  $('body').on('click','#mesprevio',function(e){	 
    var thisMonth = parseInt($('#ReportStartDateMonth').val());
    var previousMonth= (thisMonth-1)%12;
    var previousYear=parseInt($('#ReportStartDateYear').val());
    if (previousMonth==0){
      previousMonth=12;
      previousYear-=1;
    }
    if (previousMonth<10){
      previousMonth="0"+previousMonth;
    }
    $('#ReportStartDateDay').val('1');
    $('#ReportStartDateMonth').val(previousMonth);
    $('#ReportStartDateYear').val(previousYear);
    var daysInPreviousMonth=daysInMonth(previousMonth,previousYear);
    $('#ReportInventorydateDay').val(daysInPreviousMonth);
    $('#ReportInventorydateMonth').val(previousMonth);
    $('#ReportInventorydateYear').val(previousYear);
    
    $('#ReportComprobanteAjustesInventarioForm').trigger('submit');
  });
  
  $('body').on('click','#messiguiente',function(e){	 
    var thisMonth = parseInt($('#ReportStartDateMonth').val());
    var nextMonth= (thisMonth+1)%12;
    var nextYear=parseInt($('#ReportStartDateYear').val());
    if (nextMonth==0){
      nextMonth=12;
    }
    if (nextMonth==1){
      nextYear+=1;
    }
    if (nextMonth<10){
      nextMonth="0"+nextMonth;
    }
    $('#ReportStartDateDay').val('1');
    $('#ReportStartDateMonth').val(nextMonth);
    $('#ReportStartDateYear').val(nextYear);
    var daysInNextMonth=daysInMonth(nextMonth,nextYear);
    $('#ReportInventorydateDay').val(daysInNextMonth);
    $('#ReportInventorydateMonth').val(nextMonth);
    $('#ReportInventorydateYear').val(nextYear);
    
    $('#ReportComprobanteAjustesInventarioForm').trigger('submit');
  });

</script>
<div class="stockItems inventory">
<?php
  echo "<h2>Comprobante de Ajustes</h2>";
	echo $this->Form->create('Report'); 
	//echo 'startDate is '.$startDate.'<br/>';
	echo "<fieldset>"; 
    echo  $this->Form->input('Report.start_date',['type'=>'date','label'=>'Inicio Ajustes','dateFormat'=>'DMY','default'=>$startDate,'minYear'=>($userrole!=ROLE_SALES?2013:date('Y')-1),'maxYear'=>date('Y')]);
		echo  $this->Form->input('Report.inventorydate',['type'=>'date','label'=>__('Inventory Date'),'dateFormat'=>'DMY','default'=>$inventoryDate,'minYear'=>($userrole!=ROLE_SALES?2013:date('Y')-1),'maxYear'=>date('Y')]);
		echo  $this->Form->input('Report.warehouse_id',['label'=>__('Warehouse'),'default'=>$warehouseId,'empty'=>['0'=>'-- Todas Bodegas --']]);
    //echo $this->Form->input('Report.product_type_id',['label'=>__('Tipo de Producto'),'default'=>$productTypeId,'empty'=>['0'=>'Todos Tipos de Producto']]);
    
	echo  "</fieldset>";
  echo '<button id="mesprevio" type="button" class="monthswitcher">Mes Previo</button>';
  echo '<button id="messiguiente" type="button" >Mes Siguiente</button>';
  echo $this->Form->end(__('Refresh')); 
  
  if ($userrole!=ROLE_SALES){
    echo "<br/>";
    echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarComprobanteAjustes',$inventoryDate], ['class' => 'btn btn-primary']); 
    //echo $this->Html->link(__('Hoja de Inventario'), ['action' => 'verPdfHojaInventario','ext'=>'pdf',$inventoryDate,$warehouseId,$filename],['class' => 'btn btn-primary','target'=>'blank']); 
	}
  
  $excelOutput='';
  
  if(!empty($adjustedProducts)){
    foreach ($adjustedProducts as $adjustedProductDisplayId => $adjustedProductData){
      echo '<h2> Ajustes para '.$adjustedProductData['name'].'</h2>';
      if (empty($adjustedProductData['Product'])){
        echo '<h3>No hay productos en inventario de este tipo</h3';
      }
      else {
        $productTableHead='';
        $productTableHead.='<thead>';
          $productTableHead.='<tr>';
            if (in_array($adjustedProductDisplayId,['BOTTLES_A','BOTTLES_B'])){
              $productTableHead.='<th>Preforma</th>';
            }
            $productTableHead.='<th>'.__('Producto').'</th>';
            $productTableHead.='<th class="centered"># Inc</th>';
            $productTableHead.='<th class="centered"># Desc</th>';
            $productTableHead.='<th class="centered"># Neto</th>';
            $productTableHead.='<th class="centered">C$ Inc</th>';
            $productTableHead.='<th class="centered">C$ Desc</th>';
            $productTableHead.='<th class="centered">C$ Neto</th>';
            $productTableHead.='<th class="centered">Inventario final</th>';
          $productTableHead.='</tr>';
        $productTableHead.='</thead>';
        $adjustmentTotals=[
          'quantity_up'=>0,
          'value_up'=>0,
          'quantity_down'=>0,
          'value_down'=>0,
        ];  
        $tableRows="";
        foreach ($adjustedProductData['Product'] as $product){
          $adjustmentTotals['quantity_up']+=$product['adjusted']['quantity_up'];
          $adjustmentTotals['value_up']+=$product['adjusted']['value_up'];
          $adjustmentTotals['quantity_down']+=$product['adjusted']['quantity_down'];
          $adjustmentTotals['value_down']+=$product['adjusted']['value_down'];
            
          if ($product['quantity']!="" || $product['adjusted']['quantity_up'] > 0 || $product['adjusted']['quantity_down'] > 0){
            $tableRows.= "<tr>";
            if (in_array($adjustedProductDisplayId,['BOTTLES_A','BOTTLES_B'])){
              $tableRows.='<td>'.$product['raw_material_name'].'</td>';
            }
              $tableRows.='<td>'.$product['product_name'].'</td>';
              $tableRows.='<td class="centered">'.$product['adjusted']['quantity_up'].'</td>';
              $tableRows.='<td class="centered">'.$product['adjusted']['quantity_down'].'</td>';
              $tableRows.='<td class="centered">'.($product['adjusted']['quantity_up']-$product['adjusted']['quantity_down']).'</td>';
              $tableRows.='<td class="centered currency"><span class="currency"></span><span class="amountright">>'.$product['adjusted']['value_up'].'</span></td>';
              $tableRows.='<td class="centered currency"><span class="currency"></span><span class="amountright">>'.$product['adjusted']['value_down'].'</span></td>';
              $tableRows.='<td class="centered currency"><span class="currency"></span><span class="amountright">>'.($product['adjusted']['value_up']-$product['adjusted']['value_down']).'</span></td>';
              $tableRows.='<td class="centered">'.$product['quantity'].'</td>';                
            $tableRows.= "</tr>";
          }
        }  
        $totalRow='';
        $totalRow.= '<tr class="totalrow">';
          $totalRow.= '<td>Total</td>';
          if (in_array($adjustedProductDisplayId,['BOTTLES_A','BOTTLES_B'])){
            $totalRow.='<td></td>';
          }
          $totalRow.='<td class="centered">'.$adjustmentTotals['quantity_up'].'</td>';
          $totalRow.='<td class="centered">'.$adjustmentTotals['quantity_down'].'</td>';
          $totalRow.='<td class="centered">'.($adjustmentTotals['quantity_up']-$adjustmentTotals['quantity_down']).'</td>';
          $totalRow.='<td class="centered currency"><span class="currency"></span><span class="amountright">'.$adjustmentTotals['value_up'].'</span></td>';
          $totalRow.='<td class="centered currency"><span class="currency"></span><span class="amountright">'.$adjustmentTotals['value_down'].'</span></td>';
          $totalRow.='<td class="centered currency"><span class="currency"></span><span class="amountright">'.($adjustmentTotals['value_up']-$adjustmentTotals['value_down']).'</span></td>';
          $totalRow.='<td></td>';
        $totalRow.= '</tr>';
        $productTableBody= '</tbody>'.$totalRow.$tableRows.$totalRow.'</tbody>';
        $productTable='<table id="'.$adjustedProductData['name'].'">'.$productTableHead.$productTableBody.'</table>';
        $excelOutput.=$productTable;
        echo $productTable;
      }
    }
    $_SESSION['comprobanteAjustes'] = $excelOutput;
  }
    
?>

</div>