<script>
  $('body').on('click','.stockQuantity',function(){
    $(this).attr('readonly',false);
    //var thisRow=$(this).closest('tr');
	});
  
  $('body').on('blur','.stockQuantity',function(){
    $(this).attr('readonly',true);
		//var thisRow=$(this).closest('tr');
	});
  
  $('body').on('keypress','.stockQuantity',function(e){
    if(e.which == 13) { // Checks for the enter key
      $(this).trigger('blur');
    }
	});
  
  $('body').on('click','.stockQuantityModal',function(){
    //var currentStockQuantity=$(this).val();
    var thisRow=$(this).closest('tr');
    var currentAverageCost=thisRow.find('td.averageCost').find('span.amountright').html();
    var fullProductName=thisRow.find('span.fullproductname').html()+" A";
    
    $('#changeStockQuantityProductId').val($(this).val());
    $('#changeStockQuantityRawMaterialId').val($(this).val());
    $('#changeStockQuantityProductQualityId').val($(this).val());
    
    $('#changeStockQuantityProductName').val(fullProductName);
    
    $('#changeStockQuantityOriginalQuantity').val($(this).val());
    $('#changeStockQuantityUpdatedQuantity').val($(this).val());
    
    $('#changeStockQuantityAverageCost').val(currentAverageCost);
    $('#changeStockQuantityUpdatedCost').val(currentAverageCost);
    $('#changeStockQuantity').modal('show');		
	});
  
  $('body').on('click','#saveChangeStockQuantity',function(){
    /*
    var clientid=$('#EditClientId').val();
		var clientemail=$('#EditClientEmail').val();
    var clientphone=$('#EditClientPhone').val();
    var clientaddress=$('#EditClientAddress').val();
    var clientrucnumber=$('#EditClientRucNumber').val();
    $.ajax({
			url: '<?php echo $this->Html->url('/'); ?>thirdParties/saveexistingclient/',
      data:{"clientid":clientid,"clientemail":clientemail,"clientphone":clientphone,"clientaddress":clientaddress,"clientrucnumber":clientrucnumber},
			cache: false,
			type: 'POST',
			success: function (data) {
				if (data=="1"){
					alert("El cliente se guardó.");
				}
        else {
          console.log(data);
          alert(data);
        }
			},
			error: function(e){
				console.log(e);
				alert(e.responseText);
			}
		});
    */
		$('#changeStockQuantity').modal('hide');		
	});

	function daysInMonth(month,year) {
		return new Date(year, month, 0).getDate();
	}
	
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
    $('.editedStockQuantity').addClass('hidden');
		formatNumbers();
		formatCurrencies();
	});
</script>


<div class="stockItems inventory">
<?php  
  $overviewTableHead='';
  $overviewTableHead.='<thead>';
    $overviewTableHead.='<tr>';
      $overviewTableHead.='<th>Línea de Producto</th>';
      $overviewTableHead.='<th>Unidades</th>';
      $overviewTableHead.='<th>Costo</th>';
    $overviewTableHead.='</tr>';
  $overviewTableHead.='</thead>';
  
  $overviewTableRows='';
  $totalQuantity=0;
  $totalValue=0;
  
  if ($plantId == PLANT_SANDINO){
    $rawMaterialTable="<table id='preformas'>";
      $rawMaterialTable.="<thead>";
        $rawMaterialTable.="<tr>";
          $rawMaterialTable.="<th>".__('Producto')."</th>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $rawMaterialTable.="<th class='centered'>".__('Average Unit Price')."</th>";
          }
          $rawMaterialTable.="<th class='centered'>".__('Remaining')."</th>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $rawMaterialTable.="<th class='centered'>".__('Total Value')."</th>";
          }
        $rawMaterialTable.="</tr>";
      $rawMaterialTable.="</thead>";
      $rawMaterialTable.="<tbody>";
    
      $valuepreformas=0;
      $quantitypreformas=0; 
      $tableRows="";
      //pr($preformas);
      foreach ($preformas as $stockItem){
        $remaining="";
        $average="";
        $totalvalue="";
        if ($stockItem['0']['Remaining']!=""){
          $remaining= number_format($stockItem['0']['Remaining'],0,".",","); 
          $packagingunit=$stockItem['Product']['packaging_unit'];
          // if there are products and the value of packaging unit is not 0, show the number of packages
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
          $average=$stockItem['0']['Remaining']>0?$stockItem['0']['Saldo']/$stockItem['0']['Remaining']:0;
          $totalvalue=$stockItem['0']['Saldo'];
          $valuepreformas+=$stockItem['0']['Saldo'];
          $quantitypreformas+=$stockItem['0']['Remaining'];
        }
        else {
          $remaining= "0";
          $average="0";
          $totalvalue="0";
        }
        
        if ($displayOptionId!=DISPLAY_STOCK || $remaining!="0"){
          $tableRows.= "<tr>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
          
            $tableRows.= "<td>".$this->Html->link($stockItem['Product']['name'], array('controller' => 'stockItems', 'action' => 'verReporteProducto', $stockItem['Product']['id']))."</td>";
            $tableRows.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$average."</span></td>";
          }
          else {
            $tableRows.= "<td>".$stockItem['Product']['name']."</td>";
          }
          
          $tableRows.= "<td class='centered'>".$remaining."</td>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $tableRows.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalue."</span></td>";
          }
          $tableRows.= "</tr>";
        }
      }
      $totalRow="";
      $totalRow.= "<tr class='totalrow'>";
        $totalRow.= "<td>Total</td>";
        if($quantitypreformas>0){
          $avg=$valuepreformas/$quantitypreformas;
        }
        else {
          $avg=0;
        }
        if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
          $totalRow.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$avg."</span></td>";
        }
        $totalRow.= "<td class='centered number'>".$quantitypreformas."</td>";
        if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
          $totalRow.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuepreformas."</span></td>";
        }
      $totalRow.= "</tr>";
      $rawMaterialTable.=$totalRow.$tableRows.$totalRow;
      $rawMaterialTable.= "</tbody>";
    $rawMaterialTable.= "</table>";
    
    $totalQuantity+=$quantitypreformas;
    $totalValue+=$valuepreformas;
    
    $overviewTableRows.='<tr>';
      $overviewTableRows.='<td>Preformas</td>';
      $overviewTableRows.='<td class="centered number">'.$quantitypreformas.'</td>';
      $overviewTableRows.='<td class="centered currency"><span class="currency"></span><span class="amountright">'.$valuepreformas.'</span></td>';
    $overviewTableRows.='</tr>';
          
    $finishedMaterialTable= "<table id='botellas' cellpadding='0' cellspacing='0'>";
      $finishedMaterialTable.= "<thead>";
        $finishedMaterialTable.= "<tr>";
          $finishedMaterialTable.= "<th>".__('Preforma')."</th>";
          $finishedMaterialTable.= "<th>".__('Producto')."</th>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $finishedMaterialTable.= "<th class='centered'>".__('Average Unit Price')."</th>";
          }
          $finishedMaterialTable.= "<th class='centered'>".__('Cant. A')."</th>";
          $finishedMaterialTable.= "<th class='centered'>".__('Cant. B')."</th>";
          $finishedMaterialTable.= "<th class='centered'>".__('Cant. C')."</th>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $finishedMaterialTable.= "<th class='centered'>".__('Valor A')."</th>";
            $finishedMaterialTable.= "<th class='centered'>".__('Valor B')."</th>";
            $finishedMaterialTable.= "<th class='centered'>".__('Valor C')."</th>";
          }
          $finishedMaterialTable.= "<th class='centered'>".__('Remaining')."</th>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $finishedMaterialTable.= "<th class='centered'>".__('Total Value')."</th>";
          }
        $finishedMaterialTable.= "</tr>";
      $finishedMaterialTable.= "</thead>";
      $finishedMaterialTable.="<tbody>";

      $valuebotellasA=0;
      $quantitybotellasA=0; 
      $valuebotellasB=0;
      $quantitybotellasB=0; 
      $valuebotellasC=0;
      $quantitybotellasC=0; 
      $valuebotellas=0;
      $quantitybotellas=0; 
      
      $tableRows="";
      foreach ($productos as $stockItem){
        $average="";
        $remainingA=0;
        $remainingB=0;
        $remainingC=0;
        $totalvalueA="";
        $totalvalueB="";
        $totalvalueC="";
        $totalvalue=0;
        $packagingunit=$stockItem['Product']['packaging_unit'];
        if ($stockItem['0']['Remaining_A']!=""){
          $remainingA= number_format($stockItem['0']['Remaining_A'],0,".",","); 
          // if there are products and the value of packaging unit is not 0, show the number of packages
          if ($packagingunit!=0 && $stockItem['0']['Remaining_A']!=0){
            $numberpackagingunitsA=floor($stockItem['0']['Remaining_A']/$packagingunit);
            $leftoversA=$stockItem['0']['Remaining_A']-$numberpackagingunitsA*$packagingunit;
            $remainingA .= " (".$numberpackagingunitsA." ".__("_emps");
            if ($leftoversA >0){
              $remainingA.= " + ".$leftoversA.")";
            }
            else {
              $remainingA.=")";
            }
          }
          $totalvalueA=$stockItem['0']['Saldo_A'];
          $valuebotellasA+=$stockItem['0']['Saldo_A'];
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
            $remainingB .= " (".number_format($numberpackagingunitsB,0,".",",")." ".__("_emps");
            if ($leftoversB >0){
              $remainingB.= " + ".number_format($leftoversB,0,".",",").")";
            }
            else {
              $remainingB.=")";
            }
          }
          $totalvalueB=$stockItem['0']['Saldo_B'];
          $valuebotellasB+=$stockItem['0']['Saldo_B'];
          $quantitybotellasB+=$stockItem['0']['Remaining_B'];
        }
        else {
          $remainingB= "0";
          $totalvalueB="0";
        }
        if ($stockItem['0']['Remaining_C']!=""){
          $remainingC= number_format($stockItem['0']['Remaining_C'],0,".",","); 
          // if there are products and the value of packaging unit is not 0, show the number of packages
          if ($packagingunit!=0 && $remainingC!=0){
            $numberpackagingunitsC=floor($stockItem['0']['Remaining_C']/$packagingunit);
            $leftoversC=$stockItem['0']['Remaining_C']-$numberpackagingunitsC*$packagingunit;
            $remainingC .= " (".number_format($numberpackagingunitsC,0,".",",")." ".__("_emps");
            if ($leftoversC >0){
              $remainingC.= " + ".number_format($leftoversC,0,".",",").")";
            }
            else {
              $remainingC.=")";
            }
          }
          $totalvalueC=$stockItem['0']['Saldo_C'];
          $valuebotellasC+=$stockItem['0']['Saldo_C'];
          $quantitybotellasC+=$stockItem['0']['Remaining_C'];
        }
        else {
          $remainingC= "0";
          $totalvalueC="0";
        }
        $remainingTotal=$stockItem['0']['Remaining_A']+$stockItem['0']['Remaining_B']+$stockItem['0']['Remaining_C'];

        //echo "remaining A is ".$stockItem['0']['Remaining_A']."<br>";
        //echo "remaining B is ".$stockItem['0']['Remaining_B']."<br>";
        //echo "remaining C is ".$stockItem['0']['Remaining_C']."<br>";
        //echo "remaining total is ".$remainingTotal."<br>";
        $totalvalue=$totalvalueA+$totalvalueB+$totalvalueC;
        
        $valuebotellas+=$totalvalue;
        
        $average=$remainingTotal>0?($totalvalue/$remainingTotal):0;
        $quantitybotellas+=$remainingTotal;
        
        if ($displayOptionId!=DISPLAY_STOCK || $remainingTotal > 0){
          $tableRows.="<tr>";
            $tableRows.="<td><span class='fullproductname' hidden>".$stockItem['RawMaterial']['name']." ".$stockItem['Product']['name']."</span>".$this->Html->link($stockItem['RawMaterial']['name'],['controller' => 'products', 'action' => 'verReporteProducto', $stockItem['Product']['id']])."</td>";
            if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
              $tableRows.="<td>".$this->Html->link($stockItem['Product']['name'],['controller' => 'stockMovements', 'action' => 'verKardex', $stockItem['Product']['id'],$stockItem['RawMaterial']['id']])."</td>";
              $tableRows.="<td class='centered currency averageCost'><span class='currency'></span><span class='amountright'>".$average."</span></td>";
            }
            else {
              $tableRows.="<td>".$stockItem['Product']['name']."</td>";
            }
            //$tableRows.="<td class='centered'>".$this->Form->input('stockItemQuantity',['label'=>false,'default'=>$stockItem['0']['Remaining_A'],'readonly'=>true,'class'=>'stockQuantityModal','style'=>'font-size:16px;'])." ".$remainingA."</td>";
            
            $tableRows.="<td class='centered'>".$remainingA."</td>";
            $tableRows.="<td class='centered'>".$remainingB."</td>";
            $tableRows.="<td class='centered'>".$remainingC."</td>";
            if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
              $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalueA."</span></td>";
              $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalueB."</span></td>";
              $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalueC."</span></td>";
            }
            $tableRows.="<td class='totalcolumn centered number'>".$remainingTotal."</td>";
            if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
              $tableRows.="<td class='totalcolumn centered currency'><span class='currency'></span><span class='amountright'>".$totalvalue."</span></td>";		
            }
          $tableRows.="</tr>";
        }
      }
        $totalRow="";
        $totalRow.="<tr class='totalrow'>";
          $totalRow.="<td>Total</td>";
          $totalRow.="<td></td>";
          if($quantitybotellas>0){
            $avg=$valuebotellas/$quantitybotellas;
          }
          else {
            $avg=0;
          }
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {				
            $totalRow.="<td class='centered currency averageCost'><span class='currency'></span><span class='amountright'>".$avg."</span></td>";
          }
          $totalRow.="<td class='centered number'>".$quantitybotellasA."</td>";
          $totalRow.="<td class='centered number'>".$quantitybotellasB."</td>";
          $totalRow.="<td class='centered number'>".$quantitybotellasC."</td>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellasA."</span></td>";
            $totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellasB."</span></td>";
            $totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellasC."</span></td>";
          }
          $totalRow.="<td class='centered number'>".$quantitybotellas."</td>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellas."</span></td>";
          }
        $totalRow.="</tr>";
        $finishedMaterialTable.=$totalRow.$tableRows.$totalRow;
      $finishedMaterialTable.="</tbody>";
    $finishedMaterialTable.="</table>";
    
    $totalQuantity+=$quantitybotellas;
    $totalValue+=$valuebotellas;
    
    $overviewTableRows.='<tr>';
      $overviewTableRows.='<td>Botellas</td>';
      $overviewTableRows.='<td class="centered number">'.$quantitybotellas.'</td>';
      $overviewTableRows.='<td class="centered currency"><span class="currency"></span><span class="amountright">'.$valuebotellas.'</span></td>';
    $overviewTableRows.='</tr>';
    
    $capTable="<table id='tapones' cellpadding='0' cellspacing='0'>";
      $capTable.="<thead>";
        $capTable.="<tr>";
          $capTable.="<th>".__('Producto')."</th>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $capTable.="<th class='centered'>".__('Average Unit Price')."</th>";
          }
          $capTable.="<th class='centered'>".__('Remaining')."</th>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $capTable.="<th class='centered'>".__('Total Value')."</th>";
          }
        $capTable.="</tr>";
      $capTable.="</thead>";
      $capTable.="<tbody>";

      $valuetapones=0;
      $quantitytapones=0; 
      $tableRows="";
      foreach ($tapones as $stockItem){
        $remaining="";
        $average="";
        $totalvalue="";
        if ($stockItem['0']['Remaining']!=""){
          $remaining= number_format($stockItem['0']['Remaining'],0,".",","); 
          $packagingunit=$stockItem['Product']['packaging_unit'];
          // if there are products and the value of packaging unit is not 0, show the number of packages
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
          $average=$stockItem['0']['Remaining']>0?number_format($stockItem['0']['Saldo']/$stockItem['0']['Remaining'],4,".",","):0;
          $totalvalue=$stockItem['0']['Saldo'];
          $valuetapones+=$stockItem['0']['Saldo'];
          $quantitytapones+=$stockItem['0']['Remaining'];
        }
        else {
          $remaining= "0";
          $average="0";
          $totalvalue="0";
        }
        if ($displayOptionId!=DISPLAY_STOCK || $remaining!="0"){
          $tableRows.="<tr>";
            if($userRoleId == ROLE_ADMIN || $canSeeLink) {
              $tableRows.="<td>".$this->Html->link($stockItem['Product']['name'], ['controller' => 'stockMovements', 'action' => 'verReporteCompraVenta', $stockItem['Product']['id']])."</td>";
            }
            else {
              $tableRows.="<td>".$stockItem['Product']['name']."</td>";
            }
            if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
              $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$average."</span></td>";
            }
            $tableRows.="<td class='centered'>".$remaining."</td>";
            if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
              $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalue."</span></td>";
            }
          $tableRows.="</tr>";
        }
      }
        $totalRow="";
        $totalRow.="<tr class='totalrow'>";
          $totalRow.="<td>Total</td>";
          if($quantitytapones>0){
            $avg=$valuetapones/$quantitytapones;
          }
          else {
            $avg=0;
          }
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$avg."</span></td>";
          }
          $totalRow.="<td class='centered number'>".$quantitytapones."</td>";
          if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            $totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuetapones."</span></td>";
          }
        $totalRow.="</tr>";
        $capTable.=$totalRow.$tableRows.$totalRow;
      $capTable.="</tbody>";
    $capTable.="</table>";
    
    $totalQuantity+=$quantitytapones;
    $totalValue+=$valuetapones;
    
    $overviewTableRows.='<tr>';
      $overviewTableRows.='<td>Tapones</td>';
      $overviewTableRows.='<td class="centered number">'.$quantitytapones.'</td>';
      $overviewTableRows.='<td class="centered currency"><span class="currency"></span><span class="amountright">'.$valuetapones.'</span></td>';
    $overviewTableRows.='</tr>';
  }  
  if ($warehouseId > 0){    
    $otherMaterialTables='';
    $otherMaterialsBlock='';
    if ($plantId == PLANT_SANDINO){
      $otherMaterialsBlock.="<h1>Otros tipos de producto</h1>";
    }
    foreach ($otherProducts as $otherProductCategory){
      if (!empty($otherProductCategory['ProductLines'])){
        $otherMaterialsBlock.="<h2>Líneas de Categoría ".$otherProductCategory['ProductCategory']['name']."</h2>";
        foreach ($otherProductCategory['ProductLines'] as $otherProductType){
          //if ($otherProductType['ProductType']['id'] === PRODUCT_TYPE_INJECTION_GRAIN){ 
          //  pr($otherProductType);
          //}
          $otherMaterialTable="<table id='".substr($otherProductType['ProductType']['name'],0,30)."' >";
            $otherMaterialTable.="<thead>";
              $otherMaterialTable.="<tr>";
                $otherMaterialTable.="<th>".__('Product')."</th>";
                if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
                  $otherMaterialTable.="<th class='centered'>".__('Average Unit Price')."</th>";
                }
                $otherMaterialTable.="<th class='centered'>".__('Remaining')."</th>";
                if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
                  $otherMaterialTable.="<th class='centered'>".__('Total Value')."</th>";
                }
              $otherMaterialTable.="</tr>";
            $otherMaterialTable.="</thead>";
            $otherMaterialTable.="<tbody>";

            $valueProducts=0;
            $quantityProducts=0; 
            $tableRows="";
            foreach ($otherProductType['Products'] as $stockItem){
              $remaining="";
              $average="";
              $totalvalue="";
              if ($stockItem['0']['Remaining']!=""){
                $remaining= number_format($stockItem['0']['Remaining'],0,".",","); 
                $packagingunit=$stockItem['Product']['packaging_unit'];
                // if there are products and the value of packaging unit is not 0, show the number of packages
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
                $average=$stockItem['0']['Remaining']>0?number_format($stockItem['0']['Saldo']/$stockItem['0']['Remaining'],4,".",","):0;
                $totalvalue=$stockItem['0']['Saldo'];
                $valueProducts+=$stockItem['0']['Saldo'];
                $quantityProducts+=$stockItem['0']['Remaining'];
              }
              else {
                $remaining= "0";
                $average="0";
                $totalvalue="0";
              }
              if ($displayOptionId!=DISPLAY_STOCK || $remaining!="0"){
                $tableRows.="<tr>";
                  if($userRoleId == ROLE_ADMIN || $canSeeLink) {
                    $tableRows.="<td>".$this->Html->link($stockItem['Product']['name'], ['controller' => 'stockMovements', 'action' => 'verKardex', $stockItem['Product']['id']])."</td>";
                  }
                  else {
                    $tableRows.="<td>".$stockItem['Product']['name']."</td>";
                  }
                  //$tableRows.="<td>".$this->Html->link($stockItem['Product']['name'], ['controller' => 'stockMovements', 'action' => 'verReporteCompraVenta', $stockItem['Product']['id']])."</td>";
                  //$tableRows.="<td>".$stockItem['Product']['name']."</td>";
                  if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
                    $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$average."</span></td>";
                  }
                  $tableRows.="<td class='centered'>".$remaining."</td>";
                  if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
                    $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalue."</span></td>";
                  }
                $tableRows.="</tr>";
              }
            }
              $totalRow="";
              $totalRow.="<tr class='totalrow'>";
                $totalRow.="<td>Total</td>";
                if($quantityProducts>0){
                  $avg=$valueProducts/$quantityProducts;
                }
                else {
                  $avg=0;
                }
                if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
                  $totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$avg."</span></td>";
                }
                $totalRow.="<td class='centered number'>".$quantityProducts."</td>";
                if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
                  $totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valueProducts."</span></td>";
                }
              $totalRow.="</tr>";
              $otherMaterialTable.=$totalRow.$tableRows.$totalRow;
            $otherMaterialTable.="</tbody>";
          $otherMaterialTable.="</table>";
          
          $totalQuantity+=$quantityProducts;
          $totalValue+=$valueProducts;
          
          $overviewTableRows.='<tr>';
            $overviewTableRows.='<td>'.$otherProductType['ProductType']['name'].'</td>';
            $overviewTableRows.='<td class="centered number">'.$quantityProducts.'</td>';
            $overviewTableRows.='<td class="centered currency"><span class="currency"></span><span class="amountright">'.$valueProducts.'</span></td>';
          $overviewTableRows.='</tr>';
          
          $otherMaterialsBlock.='<div class="row">';
            $otherMaterialsBlock.='<div class="col-sm-4">';
              $otherMaterialsBlock.="<h3>".$otherProductType['ProductType']['name']."</h3>";
            $otherMaterialsBlock.='</div>';
            if ($userRoleId===ROLE_ADMIN){  
              $otherMaterialsBlock.='<div class="col-sm-4">';
                $otherMaterialsBlock.=$this->Html->link(__('Ajustes de Inventario de '.$otherProductType['ProductType']['name']), ['action' => 'ajustesInventario',$warehouseId,$otherProductType['ProductType']['id']],['class' => 'btn btn-primary','target'=>'blank']); 
              $otherMaterialsBlock.='</div>';  
            }
          $otherMaterialsBlock.='</div>';  
      
          $otherMaterialsBlock.=$otherMaterialTable;
          
          $otherMaterialTables.=$otherMaterialTable;
        }
      }
    }
  }  
  
  $overviewTableTotalRow='';
  $overviewTableTotalRow.='<tr class="totalrow">';
    $overviewTableTotalRow.='<td>Total</td>';
    $overviewTableTotalRow.='<td class="centered number">'.$totalQuantity.'</td>';
    $overviewTableTotalRow.='<td class="centered currency"><span class="currency"></span><span class="amountright">'.$totalValue.'</span></td>';
  
  $overviewTableTotalRow.='</tr>';
  $overviewTableBody=$overviewTableTotalRow.$overviewTableRows.$overviewTableTotalRow;
  $overviewTable='<table id="resumen_inventario">'.$overviewTableHead.$overviewTableBody.'</table>';
  
  
  echo "<h1>Inventario</h1>";
  
  echo '<div class="container-fluid">';
    echo '<div class="row">';
      echo '<div class="col-sm-6">';
        echo $this->Form->create('Report'); 
        
        echo "<fieldset>"; 
          echo  $this->Form->input('Report.inventorydate',array('type'=>'date','label'=>__('Inventory Date'),'dateFormat'=>'DMY','default'=>$inventoryDate,'minYear'=>($userRoleId!=ROLE_SALES?2013:date('Y')-1),'maxYear'=>date('Y')));
          echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);

          echo  $this->Form->input('Report.display_option_id',['label'=>__('Mostrar'),'default'=>$displayOptionId]);
        echo  "</fieldset>";
        if($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
          echo  "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
          echo  "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
        }
        echo $this->Form->end(__('Refresh')); 
      echo '</div>';  
      echo '<div class="col-sm-6 overview">';
        if ($warehouseId > 0 && ($userRoleId == ROLE_ADMIN || $canSeeExecutiveSummary)){
          echo $overviewTable;
        }
        else {
          echo '<br/>';
        }
      echo '</div>';  
    echo '</div>';    
  if ($warehouseId > 0 && $userRoleId != ROLE_SALES){
    echo '<div class="row">';
      echo '<div class="col-sm-4">';
        echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteInventario'], ['class' => 'btn btn-primary']); 
      echo '</div>';    
      echo '<div class="col-sm-4">';
        echo $this->Html->link(__('Hoja de Inventario'), ['action' => 'verPdfHojaInventario','ext'=>'pdf',$inventoryDate,$warehouseId,$filename],['class' => 'btn btn-primary','target'=>'blank']); 
      echo '</div>';    
      if ($userRoleId === ROLE_ADMIN){  
        echo '<div class="col-sm-4">';
          echo $this->Html->link(__('Ajustes de Inventario'), ['action' => 'ajustesInventario',$warehouseId],['class' => 'btn btn-primary','target'=>'blank']); 
        echo '</div>';    
      }
      echo '<br/>';
    echo '</div>';    
  }
      
  if ($warehouseId == 0){
    echo '<br/>';
    echo '<h2>Seleccione una bodega para ver datos</h2>';
  } 
  else {
    echo '<br/>';
    if ($plantId == PLANT_SANDINO){
      if (in_array($userRoleId,[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING,ROLE_MANAGER])){
        echo '<div class="row">';
          echo '<div class="col-sm-4">';
            echo "<h2>Preformas</h2>";
          echo '</div>';
          if ($userRoleId===ROLE_ADMIN){  
            echo '<div class="col-sm-4">';
              echo $this->Html->link(__('Ajustes de Inventario de Preformas'), ['action' => 'ajustesInventario',$warehouseId,PRODUCT_TYPE_PREFORMA],['class' => 'btn btn-primary','target'=>'blank']); 
            echo '</div>';  
          }
        echo '</div>';  
        echo $rawMaterialTable;
      }
      echo '<div class="row">';
        echo '<div class="col-sm-4">';
          echo "<h2>Botellas</h2>";
        echo '</div>';
        if ($userRoleId === ROLE_ADMIN){  
          echo '<div class="col-sm-4">';
            echo $this->Html->link(__('Ajustes de Inventario de Botellas'), ['action' => 'ajustesInventario',$warehouseId,PRODUCT_TYPE_BOTTLE],['class' => 'btn btn-primary','target'=>'blank']); 
          echo '</div>';  
        }
      echo '</div>';  
    
      echo $finishedMaterialTable;

      echo '<div class="row">';
        echo '<div class="col-sm-4">';
          echo "<h2>Tapones</h2>";
        echo '</div>';
        if ($userRoleId === ROLE_ADMIN){  
          echo '<div class="col-sm-4">';
            echo $this->Html->link(__('Ajustes de Inventario de Tapones'), ['action' => 'ajustesInventario',$warehouseId,PRODUCT_TYPE_CAP],['class' => 'btn btn-primary','target'=>'blank']); 
          echo '</div>';  
        }
      echo '</div>';  
      echo $capTable;
      switch ($userRoleId){
        case ROLE_SALES:
          $_SESSION['inventoryReport'] = $rawMaterialTable.$finishedMaterialTable.$capTable.$otherMaterialTables;
          break;
        case ROLE_ADMIN:
          $_SESSION['inventoryReport'] = $overviewTable.$rawMaterialTable.$finishedMaterialTable.$capTable.$otherMaterialTables;
          break;
        default:
          $_SESSION['inventoryReport'] = $finishedMaterialTable.$capTable.$otherMaterialTables;
      }  
    }
    else {
      $_SESSION['inventoryReport'] = $otherMaterialTables;
    }
    echo $otherMaterialsBlock;
    
    
  }
  echo '</div>';  
  echo "<div id='changeStockQuantity' class='modal fade'>";
    echo "<div class='modal-dialog'>";
      echo "<div class='modal-content'>";
        //echo $this->Form->create('StockQuantity', array('enctype' => 'multipart/form-data')); 
        echo "<div class='modal-header'>";
          //echo "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>";
          echo "<h4 class='modal-title'>Cambiar cantidad de producto</h4>";
        echo '</div>';
        
        echo "<div class='modal-body'>";
          echo $this->Form->create('StockQuantity'); 
            echo "<fieldset>";
              echo $this->Form->input('id',['id'=>'changeStockQuantityProductId','type'=>'hidden']);
              echo $this->Form->input('id',['id'=>'changeStockQuantityRawMaterialId','type'=>'hidden']);
              echo $this->Form->input('id',['id'=>'changeStockQuantityProductQualityId','type'=>'hidden']);
              
              echo $this->Form->input('product_name',['id'=>'changeStockQuantityProductName','label'=>'Nombre Producto','readonly'=>true]);
              
              echo $this->Form->input('original_quantity',['id'=>'changeStockQuantityOriginalQuantity','label'=>'Cantidad Original','readonly'=>true]);
              echo $this->Form->input('average_cost',['id'=>'changeStockQuantityAverageCost','label'=>'Costo promedio','readonly'=>true]);
              
              echo $this->Form->input('updated_quantity',['id'=>'changeStockQuantityUpdatedQuantity']);
              echo "<p class='comment'>Si la cantidad actualizada de un producto es mayor que la cantidad actual, se debe asignar un precio.  Si la cantidad es menor, el costo registrado no tiene impacto.</p>";
              echo $this->Form->input('updated_cost',['id'=>'changeStockQuantityUpdatedCost','label'=>'Nuevo costo']);
            echo "</fieldset>";
          echo $this->Form->end(); 	
        echo '</div>';
        echo "<div class='modal-footer'>";
          echo "<button type='button' class='btn btn-default' data-dismiss='modal'>Cerrar</button>";
          echo "<button type='button' class='btn btn-primary' id='saveChangeStockQuantity'>".__('Cambiar cantidad en bodega')."</button>";
        echo '</div>';
        
      echo '</div>';
    echo '</div>';
  echo '</div>';
?>

</div>

