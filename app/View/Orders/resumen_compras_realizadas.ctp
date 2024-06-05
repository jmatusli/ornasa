<div class="orders compras_realizadas fullwidth">
<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0,'.',',');
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
	});
</script>
<?php 
  $salestableheader="<thead>";
		$salestableheader.="<tr>";
			$salestableheader.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$salestableheader.="<th>".$this->Paginator->sort('order_code','Orden')."</th>";
			$salestableheader.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
			$salestableheader.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase A')."</th>";
			$salestableheader.="<th class='centered'>".$this->Paginator->sort('Cantidad Tapones')."</th>";
			$salestableheader.="<th class='centered'>".$this->Paginator->sort('total_price')."</th>";
			$salestableheader.="<th class='centered'>".__('Invoice Price')." C$</th>";
			$salestableheader.="<th class='centered'>".__('Invoice Price')." US$</th>";
		$salestableheader.="</tr>";
	$salestableheader.="</thead>";
	
	$salesTableHeaderWithActions="<thead>";
		$salesTableHeaderWithActions.="<tr>";
			$salesTableHeaderWithActions.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$salesTableHeaderWithActions.="<th>".$this->Paginator->sort('order_code')."</th>";
			$salesTableHeaderWithActions.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
			$salesTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase A')."</th>";
			$salesTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('Cantidad Tapones')."</th>";
			$salesTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('total_price')."</th>";
			$salesTableHeaderWithActions.="<th class='centered'>".__('Invoice Price')." C$</th>";
			$salesTableHeaderWithActions.="<th class='centered'>".__('Invoice Price')." US$</th>";
		$salesTableHeaderWithActions.="</tr>";
	$salesTableHeaderWithActions.="</thead>";
	
	$salesTableBodyWithActions=$salestablebody="<tbody>";
  $totalPriceProductsCash=0;
  $totalPriceProductsCredit=0;
	$totalpriceproducts=0;
	$totalinvoicepriceCS=0; 
  $totalinvoicepriceUSD=0; 
	$totalcost=0; 	
  $totalCostCash=0;
  $totalCostCredit=0;
	$totalproduced=0;
	$totalother=0;
	
	foreach ($sales as $sale){
    if(!$sale['Invoice']['bool_annulled']){
      //pr($sale);
      if ($sale['Invoice']['bool_credit']){
        $totalPriceProductsCredit+=$sale['Order']['total_price'];
        $totalCostCredit+=$sale['Order']['total_cost'];
      }
      else {
        $totalPriceProductsCash+=$sale['Order']['total_price'];
        $totalCostCash+=$sale['Order']['total_cost'];
      }
      
      $totalpriceproducts+=$sale['Order']['total_price'];
      $totalcost+=$sale['Order']['total_cost'];
      
      if (($sale['Invoice']['bool_credit'] && $paymentOptionId !=INVOICES_CASH) || (!$sale['Invoice']['bool_credit'] && $paymentOptionId !=INVOICES_CREDIT)){
        if (!empty($sale['Invoice']['Currency'])){
        if ($sale['Invoice']['Currency']['id']==CURRENCY_CS){
          $totalinvoicepriceCS+=$sale['Invoice']['total_price']; 
        }
        elseif ($sale['Invoice']['Currency']['id']==CURRENCY_USD){
          $totalinvoicepriceUSD+=$sale['Invoice']['total_price']; 
        }
      }
      
        $totalproduced+=$sale['Order']['quantity_produced'];
        $totalother+=$sale['Order']['quantity_other'];

        $orderdate=new DateTime($sale['Order']['order_date']);
        $invoiceCode=$sale['Order']['order_code'].(($sale['Invoice']['bool_annulled']==1)?" (Anulado)":"");
          
        // for the excel
        $salestablebody.="<tr".($sale['Invoice']['bool_credit']?"":" style='background-color:#f00;'").">";
          $salestablebody.="<td>".$orderdate->format('d-m-Y')."</td>";
          $salestablebody.="<td>".$invoiceCode."</td>";
          $salestablebody.="<td>".$sale['ThirdParty']['company_name']."</td>";
          $salestablebody.="<td class='centered'>".round($sale['Order']['quantity_produced'],4)."</td>";
          $salestablebody.="<td class='centered'>".round($sale['Order']['quantity_other'],4)."</td>";
          $salestablebody.="<td class='centered'>".round($sale['Order']['total_price'],2)."</td>";
          if (!empty($sale['Invoice']['Currency'])){
            if ($sale['Invoice']['Currency']['id']==CURRENCY_CS){
              $salestablebody.="<td class='centered'>".round($sale['Invoice']['total_price'],2)."</td>";
              $salestablebody.="<td class='centered'>-</td>";
            }
            elseif ($sale['Invoice']['Currency']['id']==CURRENCY_USD){
              $salestablebody.="<td class='centered'>-</td>";
              $salestablebody.="<td class='centered'>".round($sale['Invoice']['total_price'],2)."</td>";
            }
          }
          else {
            $salestablebody.="<td class='centered'>-</td>";
            $salestablebody.="<td class='centered'>-</td>";
          }
        $salestablebody.="</tr>";
        if ($sale['Invoice']['bool_annulled']==1){
          $salesTableBodyWithActions.="<tr".($sale['Invoice']['bool_credit']?"":" style='color:#f00;'")." class='italic'>";		
        }
        else {
          $salesTableBodyWithActions.="<tr".($sale['Invoice']['bool_credit']?"":" style='color:#f00;'").">";		
        }
          $salesTableBodyWithActions.="<td>".$orderdate->format('d-m-Y')."</td>";
          $salesTableBodyWithActions.="<td>".$this->Html->link($invoiceCode, array('action' => 'verVenta', $sale['Order']['id']))."</td>";
          $salesTableBodyWithActions.="<td>".$this->Html->link($sale['ThirdParty']['company_name'], array('controller' => 'third_parties', 'action' => 'verCliente', $sale['ThirdParty']['id']))."</td>";
          $salesTableBodyWithActions.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_produced']."</span></td>";
          $salesTableBodyWithActions.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_other']."</span></td>";
          $salesTableBodyWithActions.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Order']['total_price']."</span></td>";
          if (!empty($sale['Invoice']['Currency'])){
            if ($sale['Invoice']['Currency']['id']==CURRENCY_CS){
              $salesTableBodyWithActions.="<td class='centered  CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Invoice']['total_price']."</span></td>";
              $salesTableBodyWithActions.="<td class='centered'>-</td>";
            }
            elseif ($sale['Invoice']['Currency']['id']==CURRENCY_USD){
              $salesTableBodyWithActions.="<td class='centered'>-</td>";
              $salesTableBodyWithActions.="<td class='centered USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$sale['Invoice']['total_price']."</span></td>";
            }
          }
          else {
            $salesTableBodyWithActions.="<td class='centered'>-</td>";
            $salesTableBodyWithActions.="<td class='centered'>-</td>";
          }
          
        $salesTableBodyWithActions.="</tr>";
      }
    }
	}
	//echo "total price USD is ".$totalpriceUSD."<br/>";
	
  
	
	$totalrow="<tr class='totalrow'>";
		$totalrow.="<td>Total C$</td>";
		$totalrow.="<td></td>";
		$totalrow.="<td></td>";
		$totalrow.="<td class='centered number'><span class='amountright'>".$totalproduced."</span></td>";
		$totalrow.="<td class='centered number'><span class='amountright'>".$totalother."&nbsp;</span></td>";
    switch ($paymentOptionId){
      case INVOICES_ALL:
        $totalrow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalpriceproducts."</span></td>";
        break;
      case INVOICES_CASH:
        $totalrow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProductsCash."</span></td>";
        break;
      case INVOICES_CREDIT:
        $totalrow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProductsCredit."</span></td>";
        break;
    }
		$totalrow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalinvoicepriceCS."</span></td>";
		$totalrow.="<td class='centered USDcurrency'><span class='currency'></span><span class='amountright'>".$totalinvoicepriceUSD."</span></td>";
		
	$totalrow.="</tr>";
	
	
	$totalRowForExcel="<tr class='totalrow'>";
	$totalRowForExcel.="<td>Total C$</td>";
	$totalRowForExcel.="<td></td>";
	$totalRowForExcel.="<td></td>";
	$totalRowForExcel.="<td class='centered'>".round($totalproduced,2)."</td>";
	$totalRowForExcel.="<td class='centered'>".round($totalother,2)."</td>";
	$totalRowForExcel.="<td class='centered'>".round($totalpriceproducts,2)."</td>";
	$totalRowForExcel.="<td class='centered'>".round($totalinvoicepriceCS,2)."</td>";
	$totalRowForExcel.="<td class='centered'>".round($totalinvoicepriceUSD,2)."</td>";
	$totalRowForExcel.="</tr>";
	
	
	$salestablebody=$totalRowForExcel.$salestablebody.$totalRowForExcel."</tbody>";
	$salesTableBodyWithActions=$totalrow.$salesTableBodyWithActions.$totalrow."</tbody>";
	
	$salesTableWithActions="<table cellpadding='0' cellspacing='0' id='ventas'>".$salesTableHeaderWithActions.$salesTableBodyWithActions."</table>";
	$salestable="<table cellpadding='0' cellspacing='0' id='ventas'>".$salestableheader.$salestablebody."</table>";
	
	$remissionstableheader="<thead>";
		$remissionstableheader.="<tr>";
			$remissionstableheader.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$remissionstableheader.="<th>".$this->Paginator->sort('order_code','Orden')."</th>";
			$remissionstableheader.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
			$remissionstableheader.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase B')."</th>";
			$remissionstableheader.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase C')."</th>";
			$remissionstableheader.="<th class='centered'>".$this->Paginator->sort('total_price')."</th>";
			$remissionstableheader.="<th class='centered'>".__('Remission Price')." C$</th>";
			$remissionstableheader.="<th class='centered'>".__('Remission Price')." US$</th>";
		$remissionstableheader.="</tr>";
	$remissionstableheader.="</thead>";
	
	$remissionsTableHeaderWithActions="<thead>";
		$remissionsTableHeaderWithActions.="<tr>";
			$remissionsTableHeaderWithActions.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$remissionsTableHeaderWithActions.="<th>".$this->Paginator->sort('order_code')."</th>";
			$remissionsTableHeaderWithActions.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
			$remissionsTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase B')."</th>";
			$remissionsTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase C')."</th>";
			$remissionsTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('total_price')."</th>";
			$remissionsTableHeaderWithActions.="<th class='centered'>".__('Remission Price')." C$</th>";
			$remissionsTableHeaderWithActions.="<th class='centered'>".__('Remission Price')." US$</th>";
			$remissionsTableHeaderWithActions.="<th class='actions'>".__('Actions')."</th>";
		$remissionsTableHeaderWithActions.="</tr>";
	$remissionsTableHeaderWithActions.="</thead>";
	
	$remissionsTableBodyWithActions=$remissionstablebody="<tbody>";
	
	$totalpriceproductsremissions=0; 
	$totalremissionpriceCS=0; 
	$totalremissionpriceUSD=0; 
	$totalcostremissions=0;
	$totalproduced=0;
	$totalother=0;
		
	foreach ($remissions as $sale){
    if (!$sale['CashReceipt']['bool_annulled']){
      if ($sale['Order']['id']==1794){
        pr($sale);
      }
      $totalpriceproductsremissions+=$sale['Order']['total_price'];
      //pr($sale);
      if (!empty($sale['CashReceipt']['Currency'])){
        if ($sale['CashReceipt']['Currency']['id']==CURRENCY_CS){
          $totalremissionpriceCS+=$sale['CashReceipt']['amount']; 
        }
        elseif ($sale['CashReceipt']['Currency']['id']==CURRENCY_USD){
          $totalremissionpriceUSD+=$sale['CashReceipt']['amount']; 
        }
      }
      $totalproduced+=$sale['Order']['quantity_produced_B'];
      $totalother+=$sale['Order']['quantity_produced_C'];

      $orderdate=new DateTime($sale['Order']['order_date']);
      $orderdate=new DateTime($sale['Order']['order_date']);
      $invoiceCode=$sale['Order']['order_code'];
      if ($sale['CashReceipt']['bool_annulled']==1){
        $invoiceCode.=" (Anulado)";
      }
      
      if ($sale['CashReceipt']['bool_annulled']==1){
        $remissionstablebody.="<tr class='italic'>";		
      }
      else {
        $remissionstablebody.="<tr>";		
      }
        $remissionstablebody.="<td>".$orderdate->format('d-m-Y')."</td>";
        $remissionstablebody.="<td>".$invoiceCode."</td>";
        $remissionstablebody.="<td>".$sale['ThirdParty']['company_name']."</td>";
        $remissionstablebody.="<td class='centered'>".round($sale['Order']['quantity_produced_B'],4)."</td>";
        $remissionstablebody.="<td class='centered'>".round($sale['Order']['quantity_produced_C'],4)."</td>";
        $remissionstablebody.="<td class='centered'>".round($sale['Order']['total_price'],4)."</td>";
        if (!empty($sale['CashReceipt']['Currency'])){
          if ($sale['CashReceipt']['Currency']['id']==CURRENCY_CS){
            $remissionstablebody.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['CashReceipt']['amount']."</span></td>";
            $remissionstablebody.="<td class='centered'>-</td>";
          }
          elseif ($sale['CashReceipt']['Currency']['id']==CURRENCY_USD){
            $remissionstablebody.="<td class='centered'>-</td>";
            $remissionstablebody.="<td class='centered USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$sale['CashReceipt']['amount']."</span></td>";
          }
        }
        else {
          $remissionstablebody.="<td class='centered'>-</td>";
          $remissionstablebody.="<td class='centered'>-</td>";
        }
      $remissionstablebody.="</tr>";
      
      if ($sale['CashReceipt']['bool_annulled']==1){
        $remissionsTableBodyWithActions.="<tr class='italic'>";		
      }
      else {
        $remissionsTableBodyWithActions.="<tr>";		
      }
      $remissionsTableBodyWithActions.="<td>".$orderdate->format('d-m-Y')."</td>";
      $remissionsTableBodyWithActions.="<td>".$this->Html->link($invoiceCode, array('action' => 'verRemision', $sale['Order']['id']))."</td>";
      $remissionsTableBodyWithActions.="<td>".$sale['ThirdParty']['company_name']."</td>";
      $remissionsTableBodyWithActions.="<td class='centered number'>".$sale['Order']['quantity_produced_B']."</td>";
      $remissionsTableBodyWithActions.="<td class='centered number'>".$sale['Order']['quantity_produced_C']."</td>";
      $remissionsTableBodyWithActions.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Order']['total_price']."</span></td>";
      if (!empty($sale['CashReceipt']['Currency'])){
        if ($sale['CashReceipt']['Currency']['id']==CURRENCY_CS){
          $remissionsTableBodyWithActions.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['CashReceipt']['amount']."</span></td>";
          $remissionsTableBodyWithActions.="<td class='centered'>-</td>";
        }
        elseif ($sale['CashReceipt']['Currency']['id']==CURRENCY_USD){
          $remissionsTableBodyWithActions.="<td class='centered'>-</td>";
          $remissionsTableBodyWithActions.="<td class='centered USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$sale['CashReceipt']['amount']."</span></td>";
        }
      }
      else {
        $remissionsTableBodyWithActions.="<td class='centered'>-</td>";
        $remissionsTableBodyWithActions.="<td class='centered'>-</td>";
      }
      $remissionsTableBodyWithActions.="<td class='actions'>";
        $orderCode=str_replace(' ','',$sale['Order']['order_code']);
        $orderCode=str_replace('/','',$orderCode);
        $filename='Remision_'.$orderCode;
        //if ($userrole==ROLE_ADMIN) { 
        if ($bool_remission_edit_permission) { 
          $remissionsTableBodyWithActions.=$this->Html->link(__('Edit'), array('action' => 'editarRemision', $sale['Order']['id'])); 
          //$remissionsTableBodyWithActions.=$this->Form->postLink(__('Delete'), array('action' => 'delete', $sale['Order']['id']), array(), __('Are you sure you want to delete exit # %s?', $sale['Order']['order_code']));
          //$remissionsTableBodyWithActions.=$this->Form->postLink(__('Anular'), array('action' => 'anularRemision', $sale['Order']['id']), array(), __('Seguro que quiere anular la remisión # %s?', $sale['Order']['order_code']));
        } 
        $remissionsTableBodyWithActions.=$this->Html->link(__('Pdf'), array('action' => 'verPdfRemision','ext'=>'pdf',$sale['Order']['id'],$filename));				
      $remissionsTableBodyWithActions.="</td>";
      $remissionsTableBodyWithActions.="</tr>";
    }
	}
	
	$totalrow="<tr class='totalrow'>";
		$totalrow.="<td>Total C$</td>";
		$totalrow.="<td></td>";
		$totalrow.="<td></td>";
		$totalrow.="<td class='centered number'>".$totalproduced."</td>";
		$totalrow.="<td class='centered number'>".$totalother."</td>";
		$totalrow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalpriceproductsremissions."</span></td>";
		$totalrow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalremissionpriceCS."</span></td>";
		$totalrow.="<td class='centered USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$totalremissionpriceUSD."</span></td>";
		$totalrow.="<td></td>";
	$totalrow.="</tr>";
	
	
	$totalRowForExcel="<tr class='totalrow'>";
		$totalRowForExcel.="<td>Total C$</td>";
		$totalRowForExcel.="<td></td>";
		$totalRowForExcel.="<td></td>";
		$totalRowForExcel.="<td class='centered'>".round($totalproduced,4)."</td>";
		$totalRowForExcel.="<td class='centered'>".round($totalother,4)."</td>";
		$totalRowForExcel.="<td class='centered'>".round($totalpriceproductsremissions,4)."</td>";
		$totalRowForExcel.="<td class='centered'>".round($totalremissionpriceCS,4)."</td>";
		$totalRowForExcel.="<td class='centered'>".round($totalremissionpriceCS,4)."</td>";
	$totalRowForExcel.="</tr>";

	$remissionstablebody=$totalRowForExcel.$remissionstablebody.$totalRowForExcel."</tbody>";
	$remissionsTableBodyWithActions=$totalrow.$remissionsTableBodyWithActions.$totalrow."</tbody>";
	
	$remissionsTableWithActions="<table cellpadding='0' cellspacing='0' id='remisiones'>".$remissionsTableHeaderWithActions.$remissionsTableBodyWithActions."</table>";
	$remissionstable="<table cellpadding='0' cellspacing='0' id='remisiones'>".$remissionstableheader.$remissionstablebody."</table>";


	echo "<h2>Reporte de compras realizadas en el último mes</h2>";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";	
			echo "<div class='col-md-5'>";
        if ($roleId==ROLE_ADMIN){      
          echo $this->Form->create('Report'); 
          echo "<fieldset>"; 
            echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
            echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
            echo $this->Form->input('Report.client_id',['default'=>$clientId,'empty'=>[0=>"--Seleccione cliente--"]]);
          echo "</fieldset>"; 
          echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>"; 
          echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>"; 
          echo $this->Form->end(__('Refresh')); 
        }
        if (!empty($sales)||!empty($remissions)){
          echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarResumenVentasRemisiones'), array( 'class' => 'btn btn-primary')); 
        }
      echo "</div>";
      echo "<div class='col-md-5'>";
        if (count($purchaseEstimation['estimatedProducts'])>0){
          echo "<h3>Estimación de compras</h3>";
          echo "<table id='estimatedProducts'>";
            echo "<thead>";
              echo "<tr>";
                echo "<th>".__('Product')."</th>";
                echo "<th>".__('Preforma')."</th>";
                echo "<th>".__('Calidad')."</th>";
                echo "<th>".__('Quantity')."</th>";
                if ($roleId==ROLE_ADMIN){
                  echo "<th>".__('Unit Price')."</th>";
                }
                if ($roleId==ROLE_ADMIN){
                  echo "<th>".__('Total Price')."</th>";
                }
                echo "<th></th>";
              echo "</tr>";
            echo "</thead>";
            echo "<tbody>";            
            for ($i=0;$i<count($purchaseEstimation['estimatedProducts']);$i++) {
              $purchaseEstimationProduct=$purchaseEstimation['estimatedProducts'][$i];
              //pr($purchaseEstimationProduct);
              //echo "raw material id is ".$purchaseEstimationProduct['PurchaseEstimationProduct']['raw_material_id']."<br/>";
              echo "<tr>";
                echo "<td class='productid'>".$purchaseEstimationProduct['PurchaseEstimationProduct']['product_name']."</td>";
                echo "<td class='rawmaterialid'>".$purchaseEstimationProduct['PurchaseEstimationProduct']['raw_material_name']."</td>";
                echo "<td>".$purchaseEstimationProduct['PurchaseEstimationProduct']['production_result_code']."</td>";          
                echo "<td>".$purchaseEstimationProduct['PurchaseEstimationProduct']['product_quantity']."</td>";
                switch ($roleId){
                  case ROLE_ADMIN: 
                    echo "<td  class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$purchaseEstimationProduct['PurchaseEstimationProduct']['product_unit_price']."</span></td>";
                    break;
                  default:
                    //echo "<td  class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$purchaseEstimationProduct['PurchaseEstimationProduct']['product_unit_price']."</span></td>";
                    break;
                }
                switch ($roleId){
                  case ROLE_ADMIN: 
                    echo "<td  class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$purchaseEstimationProduct['PurchaseEstimationProduct']['product_total_price']."</span></td>";
                    break;
                  default:
                    //echo "<td  class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$purchaseEstimationProduct['PurchaseEstimationProduct']['product_total_price']."</span></td>";
                    break;
                }
              echo "</tr>";			
            }
            echo "</tbody>";            
          echo "</table>";  
        }
      echo "</div>";
		echo "</div>";
	echo "</div>";			        
?>
</div>
<div class='related'>
<?php
	echo "<h3>Ventas</h3>";
  if (!empty($sales)){
    echo "<p class='comment'>Facturas de contado aparecen <span style='color:#f00;'>en rojo</span></p>";
	echo $salesTableWithActions;
	}
  else {
    echo "<h3>No había ventas en los últimos 30 días</h3>";
  }
	echo "<h3>Remisiones</h3>";
  if (!empty($remissions)){
    echo $remissionsTableWithActions;
  }
  else {
    echo "<h3>No había remisiones en los últimos 30 días</h3>";
  }
	
	$_SESSION['resumenVentasRemisiones'] = $salestable.$remissionstable;
?>
</div>