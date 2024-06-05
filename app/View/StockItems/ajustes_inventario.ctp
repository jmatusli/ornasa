<script>
  /*
    $('body').on('click','.stockQuantity',function(){
      $(this).attr('readonly',false);
    });
    $('body').on('blur','.stockQuantity',function(){
      $(this).attr('readonly',true);
    });
    $('body').on('keypress','.stockQuantity',function(e){
      if(e.which == 13) { // Checks for the enter key
        $(this).trigger('blur');
      }
    });
  */
  
  $('body').on('click','.changeQuantity',function(){
		var thisRow=$(this).closest('tr');
		var previousQuantity=parseInt(thisRow.find('td.stockQuantities  input.previousStockQuantity').val());
    var previousQuantityInputId=thisRow.find('td.stockQuantities  input.previousStockQuantity').attr('id');
    var updatedQuantity=parseInt(thisRow.find('td.stockQuantities div input.updatedStockQuantity').val());
    var productName=thisRow.find('td.stockQuantities input.productName').val();
    //alert("Previous quantity was " + previousQuantity + " and updated quantity is " + updatedQuantity);
    //if (updatedQuantity>previousQuantity){
    //  alert ("No puedes cambiar de una cantidad menor " + previousQuantity + " a una cantidad mayor " + updatedQuantity + " para el producto " + productName);
    //}
    //else {    
      if (updatedQuantity != previousQuantity && updatedQuantity >= 0) {
        var adjustmentDate=$('#ReportInventorydateYear').val() + '-' + $('#ReportInventorydateMonth').val() + '-' + $('#ReportInventorydateDay').val();
      
        var productId=thisRow.find('td.stockQuantities input.productId').val();
        var productPackagingUnit=parseInt(thisRow.find('td.remaining input.packagingUnit').val());
        var remainingInputId=thisRow.find('td.remaining div input.remainingStockQuantity').attr('id');
        var warehouseId=$('#ReportWarehouseId').val();
        
        $.ajax({
          url: '<?php echo $this->Html->url('/'); ?>stockMovements/registeradjustmentmovement/',
          data:{"adjustmentDate":adjustmentDate,"productId":productId,"previousQuantity":previousQuantity,"updatedQuantity":updatedQuantity,"warehouseId":warehouseId},
          cache: false,
          type: 'POST',
          success: function (updatedStockQuantity) {
            if (updatedStockQuantity<0){
              alert("Occurrió un problema, no se registró el ajuste");
            }
            else {
              // update the hidden  previous quantity
              $('#'+previousQuantityInputId).val(updatedStockQuantity);
              // recalculate remaining
              $('#'+remainingInputId).attr('readonly',false);
              var newRemaining = recalculateRemaining(parseInt(updatedStockQuantity), productPackagingUnit)
              $('#'+remainingInputId).val(newRemaining);
              $('#'+remainingInputId).attr('readonly',true);
              alert("Se registró el ajuste");
            }
          },
          error: function(e){
            console.log(e);
            alert(e.responseText);
          }
        });
      }
    //}
	});
         
  function recalculateRemaining(stockQuantity, productPackagingUnit){
    var result="" + stockQuantity;
    if (productPackagingUnit!=0){
      var numberPackagingUnits=Math.floor(stockQuantity/productPackagingUnit);
      var leftovers=stockQuantity-numberPackagingUnits*productPackagingUnit;
      result += " (" + numberPackagingUnits + " unidades de empaque";
      if (leftovers >0){
        result += " y " + leftovers + " unidades que sobran)";
      }
      else {
        result +=")";
      }      
    }
    return result
  } 
  
  $('body').on('click','.changeQuantities',function(){
    var thisRow=$(this).closest('tr');
		
    var rawMaterialId=thisRow.find('td.rawmaterial input.rawMaterialId').val();
    var productId=thisRow.find('td.product input.productId').val();
    var fullProductName=thisRow.find('span.fullproductname').html();
    
    var previousQuantityA=parseInt(thisRow.find('td.remainingA input.previousStockQuantityA').val());
    var previousQuantityB=parseInt(thisRow.find('td.remainingB input.previousStockQuantityB').val());
    var previousQuantityC=parseInt(thisRow.find('td.remainingC input.previousStockQuantityC').val());
    
    var previousQuantityCombined = previousQuantityA + previousQuantityB + previousQuantityC;
    
    $('#changeStockQuantityRawMaterialId').val(rawMaterialId);
    $('#changeStockQuantityProductId').val(productId);
    $('#changeStockQuantityProductName').val(fullProductName);
    
    $('#changeStockQuantityOriginalQuantityA').val(previousQuantityA);
    $('#changeStockQuantityUpdatedQuantityA').val(previousQuantityA);
    $('#changeStockQuantityOriginalQuantityB').val(previousQuantityB);
    $('#changeStockQuantityUpdatedQuantityB').val(previousQuantityB);
    $('#changeStockQuantityOriginalQuantityC').val(previousQuantityC);
    $('#changeStockQuantityUpdatedQuantityC').val(previousQuantityC);
    
    $('#changeStockQuantityOriginalQuantityCombined').val(previousQuantityCombined);
    $('#changeStockQuantityUpdatedQuantityCombined').val(previousQuantityCombined);
    
    $('#changeBottleStockQuantities').modal('show');	
	});  
  
  $('body').on('change','#changeStockQuantityUpdatedQuantityA',function(){
    recalculateUpdatedTotal();
  });
  $('body').on('change','#changeStockQuantityUpdatedQuantityB',function(){
    recalculateUpdatedTotal();
  });
  $('body').on('change','#changeStockQuantityUpdatedQuantityC',function(){
    recalculateUpdatedTotal();
  });
  function recalculateUpdatedTotal(){
    var updatedQuantityA= parseInt($('#changeStockQuantityUpdatedQuantityA').val());
    var updatedQuantityB= parseInt($('#changeStockQuantityUpdatedQuantityB').val());
    var updatedQuantityC= parseInt($('#changeStockQuantityUpdatedQuantityC').val());
    $('#changeStockQuantityUpdatedQuantityCombined').val(updatedQuantityA + updatedQuantityB + updatedQuantityC);  
  }
  
  $('body').on('click','#saveChangeStockQuantities',function(){
    var productId=$('#changeStockQuantityProductId').val();
    var rawMaterialId=$('#changeStockQuantityRawMaterialId').val();
    var thisRow=$('tr#'+productId+'_'+rawMaterialId);
    
    var adjustmentDate=$('#ReportInventorydateYear').val() + '-' + $('#ReportInventorydateMonth').val() + '-' + $('#ReportInventorydateDay').val();
    var originalQuantityA=$('#changeStockQuantityOriginalQuantityA').val();
    var updatedQuantityA= $('#changeStockQuantityUpdatedQuantityA').val();
    var originalQuantityB=$('#changeStockQuantityOriginalQuantityB').val();
    var updatedQuantityB= $('#changeStockQuantityUpdatedQuantityB').val();
    var originalQuantityC=$('#changeStockQuantityOriginalQuantityC').val();
    var updatedQuantityC= $('#changeStockQuantityUpdatedQuantityC').val();
    var originalQuantityCombined=$('#changeStockQuantityOriginalQuantityCombined').val();
    var updatedQuantityCombined= $('#changeStockQuantityUpdatedQuantityCombined').val();
    
    //if (updatedQuantityCombined>originalQuantityCombined){
    //  alert ("La cantidad nueva de A+B+C " + updatedQuantityCombined + " supera la cantidad original " + originalQuantityCombined + ".  Por favor corregir esto para poder guardarla nueva cantidad.");
    //}
    //else {
      if (updatedQuantityA >= 0 && updatedQuantityB >= 0 && updatedQuantityC >= 0 &&  (originalQuantityA !== updatedQuantityA || originalQuantityB !== updatedQuantityB || originalQuantityC !== updatedQuantityC)) {
        var warehouseId=$('#ReportWarehouseId').val();
        
        var productPackagingUnit=thisRow.find('td.product input.packagingUnit').val();
    
        var previousQuantityAInputId=thisRow.find('td.remainingA input.previousStockQuantityA').attr('id');
        var remainingAInputId=thisRow.find('td.remainingA input.remainingStockQuantityA').attr('id');
        var previousQuantityBInputId=thisRow.find('td.remainingB input.previousStockQuantityB').attr('id');
        var remainingBInputId=thisRow.find('td.remainingB input.remainingStockQuantityB').attr('id');
        var previousQuantityCInputId=thisRow.find('td.remainingC input.previousStockQuantityC').attr('id');
        var remainingCInputId=thisRow.find('td.remainingC input.remainingStockQuantityC').attr('id');
        
        $.ajax({
          url: '<?php echo $this->Html->url('/'); ?>stockMovements/registerbottleadjustmentmovements/',
          data:{"adjustmentDate":adjustmentDate,"productId":productId,"rawMaterialId":rawMaterialId,"originalQuantityA":originalQuantityA,"updatedQuantityA":updatedQuantityA,"originalQuantityB":originalQuantityB,"updatedQuantityB":updatedQuantityB,"originalQuantityC":originalQuantityC,"updatedQuantityC":updatedQuantityC,"originalQuantityCombined":originalQuantityCombined,"updatedQuantityCombined":updatedQuantityCombined,"warehouseId":warehouseId},
          dataType:'json',
          cache: false,
          type: 'POST',
          success: function (updatedStockQuantityData) {
            if (updatedStockQuantityData.adjustmentSuccess==-1){
              alert("Occurrió un problema, no se registró el ajuste.  " + updatedStockQuantityData.errorMessage);
            }
            else {
              // update the hidden  previous quantity
              $('#'+previousQuantityAInputId).val(parseInt(updatedStockQuantityData.qtyA));
              // recalculate remaining
              $('#'+remainingAInputId).attr('readonly',false);
              var newRemainingA = recalculateRemaining(parseInt(updatedStockQuantityData.qtyA), productPackagingUnit)
              $('#'+remainingAInputId).val(newRemainingA);
              $('#'+remainingAInputId).attr('readonly',true);
              $('#'+previousQuantityBInputId).val(parseInt(updatedStockQuantityData.qtyB));
              $('#'+remainingBInputId).attr('readonly',false);
              var newRemainingB = recalculateRemaining(parseInt(updatedStockQuantityData.qtyB), productPackagingUnit)
              $('#'+remainingBInputId).val(newRemainingB);
              $('#'+remainingBInputId).attr('readonly',true);
              $('#'+previousQuantityCInputId).val(parseInt(updatedStockQuantityData.qtyC));
              $('#'+remainingCInputId).attr('readonly',false);
              var newRemainingC = recalculateRemaining(parseInt(updatedStockQuantityData.qtyC), productPackagingUnit)
              $('#'+remainingCInputId).val(newRemainingC);
              $('#'+remainingCInputId).attr('readonly',true);
              thisRow.find('td.totalCombined').text(parseInt(updatedStockQuantityData.qtyCombined));
              alert("Se registraron los ajustes");
            }
          },
          error: function(e){
            console.log(e);
            alert(e.responseText);
          }
        });
      }
    //}
		$('#changeBottleStockQuantities').modal('hide');		
	});
  
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
		
    $('.editedStockQuantity').addClass('hidden');
  });

</script>
<div class="stockItems inventory">
<?php
  echo "<h2>Ajustes de Inventario</h2>";
	echo $this->Form->create('Report'); 
	
	echo "<fieldset>"; 
		//echo  $this->Form->input('Report.inventorydate',['type'=>'date','label'=>__('Inventory Date'),'dateFormat'=>'DMY','default'=>$inventoryDate,'minYear'=>($userrole!=ROLE_SALES?2013:date('Y')-1),'maxYear'=>date('Y'),'class'=>'fixed']);
    echo  $this->Form->input('Report.inventorydate',['type'=>'date','label'=>__('Inventory Date'),'dateFormat'=>'DMY','default'=>$inventoryDate,'minYear'=>($userrole!=ROLE_SALES?2013:date('Y')-1),'maxYear'=>date('Y')]);
		echo  $this->Form->input('Report.warehouse_id',['label'=>__('Warehouse'),'default'=>$warehouseId,'empty'=>['0'=>'Todas Bodegas']]);
    echo $this->Form->input('Report.product_type_id',['label'=>__('Tipo de Producto'),'default'=>$productTypeId,'empty'=>['0'=>'Todos Tipos de Producto']]);
    
    // 20190423 ONLY SHOW PRODUCTS IN STOCK, THERE CAN BE NO ADJUSTMENTS FOR PRODUCT NOT IN STOCK 
    //echo  $this->Form->input('Report.display_option_id',['label'=>__('Mostrar'),'default'=>$displayOptionId]);
	echo  "</fieldset>";
  echo $this->Form->end(__('Refresh')); 
  /*
  if ($userrole!=ROLE_SALES){
    echo "<br/>";
    echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteInventario'], ['class' => 'btn btn-primary']); 
    echo $this->Html->link(__('Hoja de Inventario'), ['action' => 'verPdfHojaInventario','ext'=>'pdf',$inventoryDate,$warehouseId,$filename],['class' => 'btn btn-primary','target'=>'blank']); 
	}
  */
  if(!empty($bottles)){
    echo "<h2>Botellas</h2>";
	
    $finishedMaterialTable= "<table id='botellas' cellpadding='0' cellspacing='0'>";
      $finishedMaterialTable.= "<thead>";
        $finishedMaterialTable.= "<tr>";
          $finishedMaterialTable.= "<th style='width:10%'>".__('Preforma')."</th>";
          $finishedMaterialTable.= "<th  style='width:10%'>".__('Producto')."</th>";
          //if($userrole!=ROLE_FOREMAN  && $userrole!=ROLE_SALES) {
          //	$finishedMaterialTable.= "<th class='centered'>".__('Average Unit Price')."</th>";
          //}
          $finishedMaterialTable.= "<th class='centered'>".__('Cant. A')."</th>";
          $finishedMaterialTable.= "<th class='centered'>".__('Cant. B')."</th>";
          $finishedMaterialTable.= "<th class='centered'>".__('Cant. C')."</th>";
          //if($userrole!=ROLE_FOREMAN && $userrole!=ROLE_SALES) {
          //	$finishedMaterialTable.= "<th class='centered'>".__('Valor A')."</th>";
          //	$finishedMaterialTable.= "<th class='centered'>".__('Valor B')."</th>";
          //	$finishedMaterialTable.= "<th class='centered'>".__('Valor C')."</th>";
          //}
          $finishedMaterialTable.= "<th class='centered'>".__('Remaining')."</th>";
          //if($userrole!=ROLE_FOREMAN  && $userrole!=ROLE_SALES) {
          //	$finishedMaterialTable.= "<th class='centered'>".__('Total Value')."</th>";
          //  $finishedMaterialTable.=  "<th></th>";
          //}
        $finishedMaterialTable.= "</tr>";
      $finishedMaterialTable.= "</thead>";
      $finishedMaterialTable.="<tbody>";

      //$valuebotellasA=0;
      $quantitybotellasA=0; 
      //$valuebotellasB=0;
      $quantitybotellasB=0; 
      //$valuebotellasC=0;
      $quantitybotellasC=0; 
      //$valuebotellas=0;
      $quantitybotellas=0; 
      
      $tableRows="";
      foreach ($bottles as $stockItem){
        $average="";
        $remainingA=0;
        $remainingB=0;
        $remainingC=0;
        //$totalvalueA="";
        //$totalvalueB="";
        //$totalvalueC="";
        //$totalvalue=0;
        $packagingunit=$stockItem['Product']['packaging_unit'];
        if ($stockItem['0']['Remaining_A']!=""){
          $remainingA= number_format($stockItem['0']['Remaining_A'],0,".",","); 
          // if there are products and the value of packaging unit is not 0, show the number of packages
          if ($packagingunit!=0 && $stockItem['0']['Remaining_A']!=0){
            $numberpackagingunitsA=floor($stockItem['0']['Remaining_A']/$packagingunit);
            $leftoversA=$stockItem['0']['Remaining_A']-$numberpackagingunitsA*$packagingunit;
            $remainingA .= " (".$numberpackagingunitsA." ".__("emps");
            if ($leftoversA >0){
              $remainingA.= " + ".$leftoversA.")";
            }
            else {
              $remainingA.=")";
            }
          }
          //$totalvalueA=$stockItem['0']['Saldo_A'];
          //$valuebotellasA+=$stockItem['0']['Saldo_A'];
          $quantitybotellasA+=$stockItem['0']['Remaining_A'];
        }
        else {
          $remainingA= "0";
          $totalvalueA="0";
        }
        if ($stockItem['0']['Remaining_B']!=""){
          $remainingB= number_format($stockItem['0']['Remaining_B'],0,".",","); 
          // if there are products and the value of packaging unit is not 0, show the number of packages
          if ($packagingunit!=0 && $stockItem['0']['Remaining_B']!=0){
            $numberpackagingunitsB=floor($stockItem['0']['Remaining_B']/$packagingunit);
            $leftoversB=$stockItem['0']['Remaining_B']-$numberpackagingunitsB*$packagingunit;
            $remainingB .= " (".number_format($numberpackagingunitsB,0,".",",")." ".__("emps");
            if ($leftoversB >0){
              $remainingB.= " + ".number_format($leftoversB,0,".",",").")";
            }
            else {
              $remainingB.=")";
            }
          }
          //$totalvalueB=$stockItem['0']['Saldo_B'];
          //$valuebotellasB+=$stockItem['0']['Saldo_B'];
          $quantitybotellasB+=$stockItem['0']['Remaining_B'];
        }
        else {
          $remainingB= "0";
          //$totalvalueB="0";
        }
        if ($stockItem['0']['Remaining_C']!=""){
          $remainingC= number_format($stockItem['0']['Remaining_C'],0,".",","); 
          // if there are products and the value of packaging unit is not 0, show the number of packages
          if ($packagingunit!=0 && $remainingC!=0){
            $numberpackagingunitsC=floor($stockItem['0']['Remaining_C']/$packagingunit);
            $leftoversC=$stockItem['0']['Remaining_C']-$numberpackagingunitsC*$packagingunit;
            $remainingC .= " (".number_format($numberpackagingunitsC,0,".",",")." ".__("emps");
            if ($leftoversC >0){
              $remainingC.= " + ".number_format($leftoversC,0,".",",").")";
            }
            else {
              $remainingC.=")";
            }
          }
          //$totalvalueC=$stockItem['0']['Saldo_C'];
          //$valuebotellasC+=$stockItem['0']['Saldo_C'];
          $quantitybotellasC+=$stockItem['0']['Remaining_C'];
        }
        else {
          $remainingC= "0";
          //$totalvalueC="0";
        }
        $remainingTotal=$stockItem['0']['Remaining_A']+$stockItem['0']['Remaining_B']+$stockItem['0']['Remaining_C'];

        //$totalvalue=$totalvalueA+$totalvalueB+$totalvalueC;
        
        //$valuebotellas+=$totalvalue;
        
        //$average=$remainingTotal>0?($totalvalue/$remainingTotal):0;
        $quantitybotellas+=$remainingTotal;
        
        if ($displayOptionId!=DISPLAY_STOCK || $remainingTotal > 0){
          $tableRows.="<tr id='".$stockItem['Product']['id']."_".$stockItem['RawMaterial']['id']."'>";
            $tableRows.="<td class='rawmaterial'>";
              $tableRows.="<span class='fullproductname' hidden>".str_replace("PREFORMA ","",$stockItem['RawMaterial']['name'])." ".$stockItem['Product']['name']."</span>";
              $tableRows.=  $this->Form->input('StockQuantity.rawMaterialId.'.$stockItem['RawMaterial']['id'],['label'=>false,'default'=>$stockItem['RawMaterial']['id'],'class'=>'rawMaterialId','type'=>'hidden']);
              $tableRows.=substr(str_replace("PREFORMA ","",$stockItem['RawMaterial']['name']),0,12).(strlen($stockItem['RawMaterial']['name'])>21?"...":"");
            $tableRows.="</td>";
            
            $tableRows.="<td class='product'>";
              $tableRows.=  $this->Form->input('StockQuantity.productId.'.$stockItem['Product']['id'],['label'=>false,'default'=>$stockItem['Product']['id'],'class'=>'productId','type'=>'hidden']);
              $tableRows.=  $this->Form->input('StockQuantity.name.'.$stockItem['Product']['id'],['label'=>false,'default'=>$stockItem['Product']['name'],'class'=>'productName','type'=>'hidden']);
              $tableRows.=  $this->Form->input('StockQuantity.packagingunit.'.$stockItem['Product']['id'],['label'=>false,'default'=>$stockItem['Product']['packaging_unit'],'class'=>'packagingUnit','type'=>'hidden']);
              if($userrole!=ROLE_FOREMAN  && $userrole!=ROLE_SALES) {
                $tableRows.=$this->Html->link($stockItem['Product']['name'],['controller' => 'products', 'action' => 'verReporteProducto', $stockItem['Product']['id']])."</td>";
              }
              else {
                $tableRows.=$stockItem['Product']['name'];
              }
            $tableRows.="</td>";
            //if($userrole!=ROLE_FOREMAN  && $userrole!=ROLE_SALES) {
            //  $tableRows.="<td class='centered currency averageCost'><span class='currency'></span><span class='amountright'>".$average."</span></td>";
            //}
            $tableRows.="<td class='centered remainingA'>";
              $tableRows.=  $this->Form->input('StockQuantity.previousA.'.$stockItem['Product']['id'],['label'=>false,'default'=>$stockItem['0']['Remaining_A'],'class'=>'previousStockQuantityA','type'=>'hidden']);
              
              $tableRows.= $this->Form->input('StockQuantity.remainingA.'.$stockItem['Product']['id'],['label'=>false,'default'=>$remainingA,'class'=>'remainingStockQuantityA','readonly'=>true,'style'=>'font-size:14px;width:100%;text-align:left;']);
            $tableRows.="</td>";
            $tableRows.="<td class='centered remainingB'>";
              $tableRows.=  $this->Form->input('StockQuantity.previousB.'.$stockItem['Product']['id'],['label'=>false,'default'=>$stockItem['0']['Remaining_B'],'class'=>'previousStockQuantityB','type'=>'hidden']);
              $tableRows.= $this->Form->input('StockQuantity.remainingB.'.$stockItem['Product']['id'],['label'=>false,'default'=>$remainingB,'class'=>'remainingStockQuantityB','readonly'=>true,'style'=>'font-size:14px;width:100%;text-align:left;']);
            $tableRows.="</td>";
            $tableRows.="<td class='centered remainingC'>";
              $tableRows.=  $this->Form->input('StockQuantity.previousC.'.$stockItem['Product']['id'],['label'=>false,'default'=>$stockItem['0']['Remaining_C'],'class'=>'previousStockQuantityC','type'=>'hidden']);
              $tableRows.= $this->Form->input('StockQuantity.remainingC.'.$stockItem['Product']['id'],['label'=>false,'default'=>$remainingC,'class'=>'remainingStockQuantityC','readonly'=>true,'style'=>'font-size:14px;width:100%;text-align:left;']);
            $tableRows.="</td>";
            //if($userrole!=ROLE_FOREMAN  && $userrole!=ROLE_SALES) {
            //  $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalueA."</span></td>";
            //  $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalueB."</span></td>";
            //  $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalueC."</span></td>";
            //}
            $tableRows.="<td class='totalcolumn centered number totalCombined'>".$remainingTotal."</td>";
            //if($userrole!=ROLE_FOREMAN && $userrole!=ROLE_SALES) {
            //  $tableRows.="<td class='totalcolumn centered currency'><span class='currency'></span><span class='amountright'>".$totalvalue."</span></td>";		
              
            //}
            $tableRows.= "<td class='centered currency'><button class='changeQuantities' type='button'>Cambiar Cantidades</button></td>";  
          $tableRows.="</tr>";
        }
      }
        $totalRow="";
        $totalRow.="<tr class='totalrow'>";
          $totalRow.="<td>Total</td>";
          $totalRow.="<td></td>";
          //if($quantitybotellas>0){
          //	$avg=$valuebotellas/$quantitybotellas;
          //}
          //else {
          //	$avg=0;
          //}
          //if($userrole!=ROLE_FOREMAN && $userrole!=ROLE_SALES) {				
          //	$totalRow.="<td class='centered currency averageCost'><span class='currency'></span><span class='amountright'>".$avg."</span></td>";
          //}
          $totalRow.="<td class='centered number'>".$quantitybotellasA."</td>";
          $totalRow.="<td class='centered number'>".$quantitybotellasB."</td>";
          $totalRow.="<td class='centered number'>".$quantitybotellasC."</td>";
          //if($userrole!=ROLE_FOREMAN && $userrole!=ROLE_SALES) {
          //	$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellasA."</span></td>";
          //	$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellasB."</span></td>";
          //	$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellasC."</span></td>";
          //}
          $totalRow.="<td class='centered number'>".$quantitybotellas."</td>";
          //if($userrole!=ROLE_FOREMAN && $userrole!=ROLE_SALES) {
          //	$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellas."</span></td>";
          //  
          //}
          $totalRow.="<td></td>";
        $totalRow.="</tr>";
        $finishedMaterialTable.=$totalRow.$tableRows.$totalRow;
      $finishedMaterialTable.="</tbody>";
    $finishedMaterialTable.="</table>";
    echo $finishedMaterialTable;
    
    echo "<div id='changeBottleStockQuantities' class='modal fade'>";
      echo "<div class='modal-dialog'>";
        echo "<div class='modal-content' style='width:800px!important;'>";
          //echo $this->Form->create('StockQuantity', array('enctype' => 'multipart/form-data')); 
          echo "<div class='modal-header'>";
            //echo "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>";
            echo "<h4 class='modal-title'>Cambiar cantidades de botella</h4>";
          echo "</div>";
          
          echo "<div class='modal-body'>";
            echo $this->Form->create('BottleStockQuantity'); 
              echo "<fieldset>";
                //echo $this->Form->input('warehouse_id',['id'=>'changeStockQuantityWarehouseId','type'=>'hidden']);
                
                echo $this->Form->input('product_id',['id'=>'changeStockQuantityProductId','type'=>'hidden']);
                echo $this->Form->input('raw_material_id',['id'=>'changeStockQuantityRawMaterialId','type'=>'hidden']);
                
                echo $this->Form->input('product_name',['id'=>'changeStockQuantityProductName','label'=>'Nombre Producto','readonly'=>true]);
                
                echo "<div class='container-fluid'>";
                  echo "<div class='row'>";
                    echo "<div class='col-sm-6'>";
                      echo $this->Form->input('original_quantity_A',['id'=>'changeStockQuantityOriginalQuantityA','label'=>'# Original A','type'=>'number','style'=>'text-align:right;','readonly'=>true]);
                      echo $this->Form->input('original_quantity_B',['id'=>'changeStockQuantityOriginalQuantityB','label'=>'# Original B','type'=>'number','style'=>'text-align:right;','readonly'=>true]);
                      echo $this->Form->input('original_quantity_C',['id'=>'changeStockQuantityOriginalQuantityC','label'=>'# Original C','type'=>'number','style'=>'text-align:right;','readonly'=>true]);
                      echo $this->Form->input('original_quantity_combined',['id'=>'changeStockQuantityOriginalQuantityCombined','label'=>'# Original Combinada','type'=>'number','style'=>'text-align:right;','readonly'=>true]);
                    echo  "</div>";
                    echo "<div class='col-sm-6'>";
                      echo $this->Form->input('updated_quantity_A',['id'=>'changeStockQuantityUpdatedQuantityA','label'=>'# Nuevo A','type'=>'number','style'=>'text-align:right;']);
                      echo $this->Form->input('updated_quantity_B',['id'=>'changeStockQuantityUpdatedQuantityB','label'=>'# Nuevo B','type'=>'number','style'=>'text-align:right;']);
                      echo $this->Form->input('updated_quantity_C',['id'=>'changeStockQuantityUpdatedQuantityC','label'=>'# Nuevo C','type'=>'number','style'=>'text-align:right;']);
                      echo $this->Form->input('updated_quantity_combined',['id'=>'changeStockQuantityUpdatedQuantityCombined','label'=>'# Nuevo Combinada','type'=>'number','style'=>'text-align:right;']);
                    echo  "</div>";
                  echo  "</div>";
                echo  "</div>";
                //echo "<p class='comment'>La cantidad combinada de A,B y C no debe superar la cantidad original presente.</p>";
                
              echo "</fieldset>";
            echo $this->Form->end(); 	
          echo "</div>";
          echo "<div class='modal-footer'>";
            echo "<button type='button' class='btn btn-default' data-dismiss='modal'>Cerrar</button>";
            echo "<button type='button' class='btn btn-primary' id='saveChangeStockQuantities'>".__('Cambiar cantidad en bodega')."</button>";
          echo "</div>";
          
        echo "</div>";
      echo "</div>";
    echo "</div>";
  }
  
  if(!empty($allInventoryProductTypes)){
    foreach ($allInventoryProductTypes as $productType){
      if ($productType['ProductType']['id']!= PRODUCT_TYPE_BOTTLE){
        echo "<h2>".$productType['ProductType']['name']."</h2>";
        $productTable="<table id='".substr($productType['ProductType']['name'],0,20)."' cellpadding='0' cellspacing='0'>";
          $productTable.="<thead>";
            $productTable.="<tr>";
              $productTable.="<th>".__('Producto')."</th>";
             //if($userrole!=ROLE_FOREMAN) {
              //  $productTable.="<th class='centered' style='width:5%'>".__('Average Unit Price')."</th>";
              //}
              $productTable.="<th class='centered' style='width:30%'>".__('Remaining')."</th>";
              $productTable.="<th class='centered' style='width:12%'>Nueva Cantidad</th>";
              //if($userrole!=ROLE_FOREMAN) {
              //  $productTable.="<th class='centered' style='width:8%'>".__('Total Value')."</th>";
              //}
              $productTable.="<th class='actions'></th>";
            $productTable.="</tr>";
          $productTable.="</thead>";
          $productTable.="<tbody>";
        
          //$valuepreformas=0;
          $quantitypreformas=0; 
          $tableRows="";
          foreach ($productType['products'] as $stockItem){
            $remaining="";
            //$average="";
            //$totalvalue="";
            if ($stockItem['0']['Remaining']!=""){
              $remaining= number_format($stockItem['0']['Remaining'],0,".",","); 
              $packagingunit=$stockItem['Product']['packaging_unit'];
              if ($packagingunit!=0){
                $numberpackagingunits=floor($stockItem['0']['Remaining']/$packagingunit);
                $leftovers=$stockItem['0']['Remaining']-$numberpackagingunits*$packagingunit;
                $remaining .= " (".number_format($numberpackagingunits,0,".",",")." ".__("packaging units");
                if ($leftovers >0){
                  $remaining.= " ".__("and")." ".number_format($leftovers,0,".",",")." ".__("leftover units").")";
                }
                else {
                  $remaining.=")";
                }
              }
              //$average=$stockItem['0']['Remaining']>0?$stockItem['0']['Saldo']/$stockItem['0']['Remaining']:0;
              //$totalvalue=$stockItem['0']['Saldo'];
              //$valuepreformas+=$stockItem['0']['Saldo'];
              $quantitypreformas+=$stockItem['0']['Remaining'];
            }
            else {
              $remaining= "0";
              //$average="0";
              //$totalvalue="0";
            }
            $tableRows.= "<tr>";
            if ($displayOptionId!=DISPLAY_STOCK || $remaining!="0"){
              if($userrole!=ROLE_FOREMAN) {
                switch ($productType['ProductType']['id']){
                  case PRODUCT_TYPE_PREFORMA:
                    $tableRows.= "<td>".$this->Html->link($stockItem['Product']['name'], ['controller' => 'stockItems', 'action' => 'verReporteProducto', $stockItem['Product']['id']])."</td>";
                    break;
                  case PRODUCT_TYPE_CAP:
                    $tableRows.="<td>".$this->Html->link($stockItem['Product']['name'], ['controller' => 'stockMovements', 'action' => 'verReporteCompraVenta', $stockItem['Product']['id']])."</td>";
                    break;
                  default:
                    $tableRows.="<td>".$this->Html->link($stockItem['Product']['name'], ['controller' => 'stockMovements', 'action' => 'verKardex', $stockItem['Product']['id']])."</td>";
                }
                //$tableRows.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$average."</span></td>";
              }
              else {
                $tableRows.= "<td>".$stockItem['Product']['name']."</td>";
              }
              
              $tableRows.="<td class='centered remaining'>";
              $tableRows.=  $this->Form->input('StockQuantity.packagingunit.'.$stockItem['Product']['id'],['label'=>false,'default'=>$stockItem['Product']['packaging_unit'],'class'=>'packagingUnit','type'=>'hidden']);
              $tableRows.= $this->Form->input('StockQuantity.remaining.'.$stockItem['Product']['id'],['label'=>false,'default'=>$remaining,'class'=>'remainingStockQuantity','readonly'=>true,'style'=>'font-size:14px;width:100%;text-align:left;']);
              $tableRows.="</td>";
              
              $tableRows.= "<td class='centered stockQuantities'>";
                $tableRows.=  $this->Form->input('StockQuantity.productId.'.$stockItem['Product']['id'],['label'=>false,'default'=>$stockItem['Product']['id'],'class'=>'productId','type'=>'hidden']);
                $tableRows.=  $this->Form->input('StockQuantity.name.'.$stockItem['Product']['id'],['label'=>false,'default'=>$stockItem['Product']['name'],'class'=>'productName','type'=>'hidden']);
                $tableRows.=  $this->Form->input('StockQuantity.previous.'.$stockItem['Product']['id'],['label'=>false,'default'=>$stockItem['0']['Remaining'],'class'=>'previousStockQuantity','type'=>'hidden']);
                $tableRows.=  $this->Form->input('StockQuantity.updated.'.$stockItem['Product']['id'],['label'=>false,'default'=>$stockItem['0']['Remaining'],'class'=>'updatedStockQuantity','style'=>'font-size:16px;width:100%;text-align:right;']);
              $tableRows.="</td>";
              //if($userrole!=ROLE_FOREMAN) {
              //  $tableRows.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalue."</span></td>";
              //}
              $tableRows.= "<td class='centered currency'><button class='changeQuantity' type='button'>Cambiar Cantidad</button></td>";
            }
            $tableRows.= "</tr>";
          }
          $totalRow="";
          $totalRow.= "<tr class='totalrow'>";
            $totalRow.= "<td>Total</td>";
            //if($quantitypreformas>0){
            //  $avg=$valuepreformas/$quantitypreformas;
            //}
            //else {
            //  $avg=0;
            //}
            //if($userrole!=ROLE_FOREMAN) {
            //  $totalRow.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$avg."</span></td>";
            //}
            $totalRow.= "<td class='centered number'>".$quantitypreformas."</td>";
            $totalRow.= "<td class='centered'></td>";
            //if($userrole!=ROLE_FOREMAN) {
            //  $totalRow.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuepreformas."</span></td>";
            //}  
            $totalRow.= "<td></td>";
            
          $totalRow.= "</tr>";
          $productTable.=$totalRow.$tableRows.$totalRow;
          $productTable.= "</tbody>";
        $productTable.= "</table>";
        echo $productTable;
      }
    }
  }  
?>

</div>