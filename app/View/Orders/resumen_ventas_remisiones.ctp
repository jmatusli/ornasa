<div class="orders index sales fullwidth">
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
  
  function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
    formatPercentages();
	});
</script>
<?php 
  
  $salesTableHeader="<thead>";
		$salesTableHeader.="<tr>";
			$salesTableHeader.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$salesTableHeader.="<th>".$this->Paginator->sort('order_code','Orden')."</th>";
			$salesTableHeader.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
			$salesTableHeader.="<th class='centered'>".$this->Paginator->sort('# Envase A')."</th>";
			$salesTableHeader.="<th class='centered'>".$this->Paginator->sort('# Tapones')."</th>";
      $salesTableHeader.="<th class='centered'>".$this->Paginator->sort('# Servicios')."</th>";
      //$salesTableHeader.="<th class='centered'>".$this->Paginator->sort('# Suministros')."</th>";
      $salesTableHeader.="<th class='centered'>".$this->Paginator->sort('# Importados')."</th>";
      $salesTableHeader.="<th class='centered'>".$this->Paginator->sort('# Ingroup')."</th>";
      //$salesTableHeader.="<th class='centered'>".$this->Paginator->sort('# Locales')."</th>";
      if (!empty($salesOtherProductTypes)){
        foreach ($salesOtherProductTypes as $productTypeId=>$productTypeName){
          $salesTableHeader.="<th class='centered'># ".$productTypeName."</th>";
        }
      }
      switch ($paymentOptionId){
        case INVOICES_ALL:
          if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
            $salesTableHeader.="<th class='centered'>Costo Total</th>";
          }
          $salesTableHeader.="<th class='centered'>Precio Total</th>";
          break;
        case INVOICES_CASH:
          if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
            $salesTableHeader.="<th class='centered'>Costo Efectivo</th>";
          }
          $salesTableHeader.="<th class='centered'>Precio Efectivo</th>";
          break;
        case INVOICES_CREDIT:
          if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
            $salesTableHeader.="<th class='centered'>Costo Crédito</th>";
          }
          $salesTableHeader.="<th class='centered'>Precio Crédito</th>";
          break;
      }
      $salesTableHeader.="<th class='centered'>".__('Invoice Price')." C$</th>";
      $salesTableHeader.="<th class='centered'>".__('Invoice Price')." US$</th>";      
      if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
        switch ($paymentOptionId){
          case INVOICES_ALL:
            $salesTableHeader.="<th class='centered'>Margen</th>"; 
            break;
          case INVOICES_CASH:
            $salesTableHeader.="<th class='centered'>Margen Efectivo</th>"; 
            break;
          case INVOICES_CREDIT:
            $salesTableHeader.="<th class='centered'>Margen Crédito</th>"; 
            break;
        }
             
      }
      $excelSalesTableHeader=$salesTableHeader;
    $excelSalesTableHeader.="</tr>";
	$excelSalesTableHeader.="</thead>";  
      //$salesTableHeader.="<th class='actions'>".__('Actions')."</th>";
		$salesTableHeader.="</tr>";
	$salesTableHeader.="</thead>";
	
	    	
	$excelSalesTableRows=$salesTableRows="";
  
  $totalPriceProductsCash=0;
  $totalPriceProductsCredit=0;
	$totalPriceProducts=0;
	$totalInvoicePriceCS=0; 
	$totalInvoicePriceUSD=0; 
  $totalCostCash=0;
  $totalCostCredit=0;
	$totalCost=0;
  
  $totalPriceProduced=0;
  $totalPriceCap=0;
  $totalPriceService=0;
  //$totalPriceConsumible=0;
  $totalPriceImport=0;
  $totalPriceInjection=0;
  $totalPriceLocal=0;
  $totalPriceOthers=[];
  
  $totalCostProduced=0;
  $totalCostCap=0;
  $totalCostService=0;
  //$totalCostConsumible=0;
  $totalCostImport=0;
  $totalCostInjection=0;
  $totalCostLocal=0;
  $totalCostOthers=[];
  
	$totalProduced=0;
	$totalCap=0;
  $totalService=0;
  //$totalConsumible=0;
  $totalImport=0;
  $totalInjection=0;
  $totalLocal=0;
  $totalOthers=[];
	
	foreach ($sales as $sale){
		//pr($sale);
    
    if ($sale['Invoice']['bool_credit']){
      $totalPriceProductsCredit+=$sale['Order']['total_price'];
      $totalCostCredit+=$sale['Order']['total_cost'];
    }
    else {
      $totalPriceProductsCash+=$sale['Order']['total_price'];
      $totalCostCash+=$sale['Order']['total_cost'];
    }
    
    $totalPriceProduced+=$sale['Order']['price_produced'];
    $totalPriceCap+=$sale['Order']['price_cap'];
    $totalPriceService+=$sale['Order']['price_service'];
    //$totalPriceConsumible+=$sale['Order']['price_consumible'];
    $totalPriceImport+=$sale['Order']['price_import'];
    $totalPriceInjection+=$sale['Order']['price_injection'];
    //$totalPriceLocal+=$sale['Order']['price_local'];
    
    if (!empty($sale['Order']['price_others'])){
      foreach ($sale['Order']['price_others'] as $productTypeId=>$priceOther){
        if (!array_key_exists($productTypeId,$totalPriceOthers)){
          $totalPriceOthers[$productTypeId]=0;
        }
        $totalPriceOthers[$productTypeId]+=$priceOther;
      }
      //pr($totalPriceOthers);
    }
    
    $totalCostProduced+=$sale['Order']['cost_produced'];
    $totalCostCap+=$sale['Order']['cost_cap'];
    $totalCostService+=$sale['Order']['cost_service'];
    //$totalCostConsumible+=$sale['Order']['cost_consumible'];
    $totalCostImport+=$sale['Order']['cost_import'];
    $totalCostInjection+=$sale['Order']['cost_injection'];
    //$totalCostLocal+=$sale['Order']['cost_local'];
    
    if (!empty($sale['Order']['cost_others'])){
      foreach ($sale['Order']['cost_others'] as $productTypeId=>$costOther){
        if (!array_key_exists($productTypeId,$totalCostOthers)){
          $totalCostOthers[$productTypeId]=0;
        }
        $totalCostOthers[$productTypeId]+=$costOther;
      }
      //pr($totalCostOthers);
    }
    
    $totalPriceProducts+=$sale['Order']['total_price'];
    $totalCost+=$sale['Order']['total_cost'];
    
    //if ($sale['Order']['id'] == 10598){
    //  //pr($sale);
    //  //echo "paymentOptionId is ".$paymentOptionId."<br/>";
    //} 
    
    if (
      (
        ($sale['Invoice']['bool_credit'] && $paymentOptionId !=INVOICES_CASH) 
        || 
        (!$sale['Invoice']['bool_credit'] && $paymentOptionId !=INVOICES_CREDIT)
      )
      && 
      (
        $saleTypeOptionId == SALE_TYPE_ALL
        || $saleTypeOptionId == SALE_TYPE_BOTTLE && $sale['Order']['cost_produced'] > 0
        || $saleTypeOptionId == SALE_TYPE_CAP && $sale['Order']['cost_cap'] > 0
        || $saleTypeOptionId == SALE_TYPE_SERVICE && $sale['Order']['price_service'] > 0
        // || $saleTypeOptionId == SALE_TYPE_CONSUMIBLE && $sale['Order']['cost_consumible'] > 0
        || $saleTypeOptionId == SALE_TYPE_IMPORT && $sale['Order']['price_import'] > 0
        || $saleTypeOptionId == SALE_TYPE_INJECTION && $sale['Order']['price_injection'] > 0
        // || $saleTypeOptionId == SALE_TYPE_LOCAL && $sale['Order']['price_local'] > 0
      )
      && $sale['Order']['warehouse_id'] == $warehouseId 
      
    ){
      
      if (!empty($sale['Invoice']['Currency'])){
        if ($sale['Invoice']['Currency']['id'] == CURRENCY_CS){
          $totalInvoicePriceCS+=$sale['Invoice']['total_price']; 
        }
        elseif ($sale['Invoice']['Currency']['id'] == CURRENCY_USD){
          $totalInvoicePriceUSD+=$sale['Invoice']['total_price']; 
        }
      }

      $totalProduced+=$sale['Order']['quantity_produced'];
      $totalCap+=$sale['Order']['quantity_cap'];
      $totalService+=$sale['Order']['quantity_service'];
      //$totalConsumible+=$sale['Order']['quantity_consumible'];
      $totalImport+=$sale['Order']['quantity_import'];
      $totalInjection+=$sale['Order']['quantity_injection'];
      //$totalLocal+=$sale['Order']['quantity_local'];
      if (!empty($sale['Order']['quantity_others'])){
        foreach ($sale['Order']['quantity_others'] as $productTypeId=>$quantityOther){
          if (!array_key_exists($productTypeId,$totalOthers)){
            $totalOthers[$productTypeId]=0;
          }
          $totalOthers[$productTypeId]+=$quantityOther;
        }
        //pr($totalOthers);
      }
      
      $orderDateTime=new DateTime($sale['Order']['order_date']);
      $invoiceCode=$sale['Order']['order_code'].(($sale['Invoice']['bool_annulled']==1)?" (Anulado)":"");
      
      $excelSalesTableRow=$salesTableRow="";
      
      if ($sale['Invoice']['bool_annulled']==1){
        $salesTableRow.="<tr".($sale['Invoice']['bool_credit']?"":" style='color:#f00;'")." class='italic'>";		
      }
      else {
        $salesTableRow.="<tr".($sale['Invoice']['bool_credit']?"":" style='color:#f00;'").">";		
      }
        $salesTableRow.="<td>".$orderDateTime->format('d-m-Y')."</td>";
        $salesTableRow.="<td>".($bool_sale_view_permission?$this->Html->link($invoiceCode, ['action' => 'verVenta', $sale['Order']['id']]):$invoiceCode)."</td>";
        
        $salesTableRow.="<td>".($sale['ThirdParty']['bool_generic'] || $userRoleId == ROLE_SALES || $userRoleId == ROLE_FACTURACION?$sale['ThirdParty']['company_name']:$this->Html->link($sale['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verCliente', $sale['ThirdParty']['id']]))."</td>";
        $salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_produced']."</span></td>";
        $salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_cap']."</span></td>";
        $salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_service']."</span></td>";
        //$salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_consumible']."</span></td>";
        $salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_import']."</span></td>";
        $salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_injection']."</span></td>";
        //$salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_local']."</span></td>";
        if (!empty($salesOtherProductTypes)){
          foreach ($salesOtherProductTypes as $productTypeId=>$productTypeName){
            $typeQuantityOther=0;
            if (!empty($sale['Order']['quantity_others'])){
              foreach ($sale['Order']['quantity_others'] as $quantityProductTypeId=>$quantityOther){
                if ($quantityProductTypeId == $productTypeId){
                  $typeQuantityOther =$quantityOther;
                }
              }
            }
            $salesTableRow.="<td class='centered'><span class='amountright'>".$typeQuantityOther."</span></td>";
          }  
        } 
        
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          $salesTableRow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Order']['total_cost']."</span></td>";
        }
        $salesTableRow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Order']['total_price']."</span></td>";
        if (!empty($sale['Invoice']['Currency'])){
          if ($sale['Invoice']['Currency']['id']==CURRENCY_CS){
            $salesTableRow.="<td class='centered  CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Invoice']['total_price']."</span></td>";
            $salesTableRow.="<td class='centered'>-</td>";
          }
          elseif ($sale['Invoice']['Currency']['id']==CURRENCY_USD){
            $salesTableRow.="<td class='centered'>-</td>";
            $salesTableRow.="<td class='centered USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$sale['Invoice']['total_price']."</span></td>";
          }
        }
        else {
          $salesTableRow.="<td class='centered'>-</td>";
          $salesTableRow.="<td class='centered'>-</td>";
        }
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          $salesTableRow.="<td class='centered percentage'><span>".(100*($sale['Order']['total_price'] == 0? "-":($sale['Order']['total_price']-$sale['Order']['total_cost'])/$sale['Order']['total_price']))."</span></td>";
        }
        /*
        $salesTableRow.="<td class='actions'>";
          $orderCode=str_replace(' ','',$sale['Order']['order_code']);
          $orderCode=str_replace('/','',$orderCode);
          $filename='Venta_Factura_'.$orderCode;
          if ($bool_sale_edit_permission){
            $salesTableRow.=$this->Html->link(__('Edit'), array('action' => 'editarVenta', $sale['Order']['id'])); 
            //$salesTableBodyWithActions.=$this->Form->postLink(__('Delete'), array('action' => 'delete', $sale['Order']['id']), array(), __('Are you sure you want to delete exit # %s?', $sale['Order']['order_code']));
            //$salesTableBodyWithActions.=$this->Form->postLink(__('Anular'), array('action' => 'anularVenta', $sale['Order']['id']), array(), __('Seguro que quiere anular la venta # %s?', $sale['Order']['order_code']));
          }
          if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
            $salesTableRow.=$this->Html->link(__('Pdf'), array('action' => 'verPdfVenta','ext'=>'pdf',$sale['Order']['id'],$filename));	
          }
          $salesTableRow.=$this->Html->link(__('Imprimir'), array('action' => 'imprimirVenta', $sale['Order']['id'])); 
        $salesTableRow.="</td>";
        */
      $salesTableRow.="</tr>";
      $salesTableRows.=$salesTableRow;

      
      $excelSalesTableRow.="<tr".($sale['Invoice']['bool_credit']?"":" style='background-color:#f00;'").">";
        $excelSalesTableRow.="<td>".$orderDateTime->format('d-m-Y')."</td>";
        $excelSalesTableRow.="<td>".$invoiceCode."</td>";
        $excelSalesTableRow.="<td>".$sale['ThirdParty']['company_name']."</td>";        
        $excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_produced'],4)."</td>";
        $excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_cap'],4)."</td>";
        $excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_service'],4)."</td>";
        //$excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_consumible'],4)."</td>";
        $excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_import'],4)."</td>";
        $excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_injection'],4)."</td>";
        //$excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_local'],4)."</td>";
        
        if (!empty($salesOtherProductTypes)){
          foreach ($salesOtherProductTypes as $productTypeId=>$productTypeName){
            $typeQuantityOther=0;
            if (!empty($sale['Order']['quantity_others'])){
              foreach ($sale['Order']['quantity_others'] as $quantityProductTypeId=>$quantityOther){
                if ($quantityProductTypeId == $productTypeId){
                  $typeQuantityOther =$quantityOther;
                }
              }
            }
            $excelSalesTableRow.="<td class='centered'>".round($typeQuantityOther,4)."</td>";
          }  
        }    
            
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          $excelSalesTableRow.="<td class='centered'>".round($sale['Order']['total_cost'],2)."</td>";
        }
        $excelSalesTableRow.="<td class='centered'>".round($sale['Order']['total_price'],2)."</td>";
        if (!empty($sale['Invoice']['Currency'])){
          if ($sale['Invoice']['Currency']['id']==CURRENCY_CS){
            $excelSalesTableRow.="<td class='centered'>".round($sale['Invoice']['total_price'],2)."</td>";
            $excelSalesTableRow.="<td class='centered'>-</td>";
          }
          elseif ($sale['Invoice']['Currency']['id']==CURRENCY_USD){
            $excelSalesTableRow.="<td class='centered'>-</td>";
            $excelSalesTableRow.="<td class='centered'>".round($sale['Invoice']['total_price'],2)."</td>";
          }
        }
        else {
          $excelSalesTableRow.="<td class='centered'>-</td>";
          $excelSalesTableRow.="<td class='centered'>-</td>";
        }
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          if ($sale['Order']['total_price'] == 0){
            $excelSalesTableRow.="<td class='centered percentage'>0</td>";
          }
          else {
            $excelSalesTableRow.="<td class='centered percentage'>".round(100*($sale['Order']['total_price']-$sale['Order']['total_cost'])/$sale['Order']['total_price'],2)."</td>";
          }
        }
      $excelSalesTableRow.="</tr>";  
      $excelSalesTableRows.=$excelSalesTableRow;
      
    }
	}
	//echo "total price USD is ".$totalpriceUSD."<br/>";
	
	$totalRow="<tr class='totalrow'>";
		$totalRow.="<td>Total C$</td>";
		$totalRow.="<td></td>";
		$totalRow.="<td></td>";
		$totalRow.="<td class='centered number'><span class='amountright'>".$totalProduced."</span></td>";
		$totalRow.="<td class='centered number'><span class='amountright'>".$totalCap."&nbsp;</span></td>";
    $totalRow.="<td class='centered number'><span class='amountright'>".$totalService."&nbsp;</span></td>";
    //$totalRow.="<td class='centered number'><span class='amountright'>".$totalConsumible."&nbsp;</span></td>";
    $totalRow.="<td class='centered number'><span class='amountright'>".$totalImport."&nbsp;</span></td>";
    $totalRow.="<td class='centered number'><span class='amountright'>".$totalInjection."&nbsp;</span></td>";
    //$totalRow.="<td class='centered number'><span class='amountright'>".$totalLocal."&nbsp;</span></td>";
    if (!empty($salesOtherProductTypes)){
      foreach ($salesOtherProductTypes as $productTypeId=>$productTypeName){
        $typeTotalQuantityOther=0;
        if (!empty($totalOthers)){
          foreach ($totalOthers as $totalQuantityProductTypeId=>$totalQuantityOther){
            if ($totalQuantityProductTypeId == $productTypeId){
              $typeTotalQuantityOther =$totalQuantityOther;
            }
          }
        }
        $totalRow.="<td class='centered number'><span class='amountright'>".$typeTotalQuantityOther."&nbsp;</span></td>";
      }  
    } 
    
    switch ($paymentOptionId){
      case INVOICES_ALL:
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCost."</span></td>";
        }
        $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProducts."</span></td>";
        break;
      case INVOICES_CASH:
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostCash."</span></td>";
        }
        $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProductsCash."</span></td>";
        break;
      case INVOICES_CREDIT:
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostCredit."</span></td>";
        }
        $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProductsCredit."</span></td>";
        break;
    }
    
    $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalInvoicePriceCS."</span></td>";
    $totalRow.="<td class='centered USDcurrency'><span class='currency'></span><span class='amountright'>".$totalInvoicePriceUSD."</span></td>";
    
    if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
      switch ($paymentOptionId){
        case INVOICES_ALL:
          $totalRow.="<td class='centered percentage'><span>".(100*($totalPriceProducts == 0? "-":($totalPriceProducts-$totalCost)/$totalPriceProducts))."</span></td>";
          break;
        case INVOICES_CASH:
          $totalRow.="<td class='centered percentage'><span>".(100*($totalPriceProductsCash == 0? "-":($totalPriceProductsCash-$totalCostCash)/$totalPriceProductsCash))."</span></td>";
          break;
        case INVOICES_CREDIT:
          $totalRow.="<td class='centered percentage'><span>".(100*($totalPriceProductsCredit == 0? "-":($totalPriceProductsCredit-$totalCostCredit)/$totalPriceProductsCredit))."</span></td>";
          break;
      }
    }
    //$totalrow.="<td></td>";
	$totalRow.="</tr>";
	
	
	$totalRowForExcel="<tr class='totalrow'>";
    $totalRowForExcel.="<td>Total C$</td>";
    $totalRowForExcel.="<td></td>";
    $totalRowForExcel.="<td></td>";
    
    $totalRowForExcel.="<td class='centered'>".round($totalProduced,2)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalCap,2)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalService,2)."</td>";
    //$totalRowForExcel.="<td class='centered'>".round($totalConsumible,2)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalImport,2)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalInjection,2)."</td>";
    //$totalRowForExcel.="<td class='centered'>".round($totalLocal,2)."</td>";
    foreach ($totalOthers as $totalOther){
      $totalRowForExcel.="<td class='centered'>".round($totalOther,2)."</td>";
    }
    switch ($paymentOptionId){
      case INVOICES_ALL:
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          $totalRowForExcel.="<td class='centered'>".round($totalCost,2)."</td>";
        }
        $totalRowForExcel.="<td class='centered'>".round($totalPriceProducts,2)."</td>";
        break;
      case INVOICES_CASH:
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          $totalRowForExcel.="<td class='centered'>".round($totalCostCash,2)."</td>";
        }
        $totalRowForExcel.="<td class='centered'>".round($totalPriceProductsCash,2)."</td>";
        break;
      case INVOICES_CREDIT:
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          $totalRowForExcel.="<td class='centered'>".round($totalCostCredit,2)."</td>";
        }
        $totalRowForExcel.="<td class='centered'>".round($totalPriceProductsCredit,2)."</td>";
        break;
    }
    $totalRowForExcel.="<td class='centered'>".round($totalInvoicePriceCS,2)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalInvoicePriceUSD,2)."</td>";
    if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
      switch ($paymentOptionId){
        case INVOICES_ALL:
          if ($totalPriceProducts == 0){
            $totalRowForExcel.="<td class='centered percentage'>0</td>";
          }
          else {
            $totalRowForExcel.="<td class='centered percentage'>".round(100*($totalPriceProducts-$totalCost)/$totalPriceProducts,2)."</td>";
          }
          break;
        case INVOICES_CASH:
          if ($totalPriceProductsCash == 0){
            $totalRowForExcel.="<td class='centered percentage'>0</td>";
          }
          else {
            $totalRowForExcel.="<td class='centered percentage'>".round(100*($totalPriceProductsCash-$totalCostCash)/$totalPriceProductsCash,2)."</td>";
          }
          break;
        case INVOICES_CREDIT:
          if ($totalPriceProductsCredit == 0){
            $totalRowForExcel.="<td class='centered percentage'>0</td>";
          }
          else {
            $totalRowForExcel.="<td class='centered percentage'>".round(100*($totalPriceProductsCredit-$totalCostCredit)/$totalPriceProductsCredit,2)."</td>";
          }break;
      }
    }
    if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
      
      
    }
	$totalRowForExcel.="</tr>";
	
	
	$excelSalesTableBody='<tbody>'.$totalRowForExcel.$excelSalesTableRows.$totalRowForExcel."</tbody>";
	$salesTableBody='<tbody>'.$totalRow.$salesTableRows.$totalRow."</tbody>";
	
	$salesTableWithActions="<table cellpadding='0' cellspacing='0' id='ventas'>".$salesTableHeader.$salesTableBody."</table>";
	$salestable="<table cellpadding='0' cellspacing='0' id='ventas'>".$excelSalesTableHeader.$excelSalesTableBody."</table>";
	
	$remissionstableheader="<thead>";
		$remissionstableheader.="<tr>";
			$remissionstableheader.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$remissionstableheader.="<th>".$this->Paginator->sort('order_code','Orden')."</th>";
			$remissionstableheader.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
			$remissionstableheader.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase B')."</th>";
			$remissionstableheader.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase C')."</th>";
      if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
        $remissionstableheader.="<th class='centered'>".$this->Paginator->sort('Costo Total')."</th>";
      }
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
      if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
        $remissionsTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('Costo Total')."</th>";
      }  
      $remissionsTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('total_price')."</th>";
      $remissionsTableHeaderWithActions.="<th class='centered'>".__('Remission Price')." C$</th>";
      $remissionsTableHeaderWithActions.="<th class='centered'>".__('Remission Price')." US$</th>";
			$remissionsTableHeaderWithActions.="<th class='actions'>".__('Actions')."</th>";
		$remissionsTableHeaderWithActions.="</tr>";
	$remissionsTableHeaderWithActions.="</thead>";
	
	$remissionsTableBodyWithActions=$remissionstablebody="<tbody>";
	
	$totalPriceProductsRemissions=0; 
	$totalRemissionPriceCS=0; 
	$totalRemissionPriceUSD=0; 
	$totalCostRemissions=0;
	$totalProduced=0;
	$totalCap=0;
		
	foreach ($remissions as $sale){
    if ($sale['Order']['warehouse_id'] == $warehouseId){
      $totalPriceProductsRemissions+=$sale['Order']['total_price'];
      //pr($sale);
      if (!empty($sale['CashReceipt']['Currency'])){
        if ($sale['CashReceipt']['Currency']['id']==CURRENCY_CS){
          $totalRemissionPriceCS+=$sale['CashReceipt']['amount']; 
        }
        elseif ($sale['CashReceipt']['Currency']['id']==CURRENCY_USD){
          $totalRemissionPriceUSD+=$sale['CashReceipt']['amount']; 
        }
      }
      if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
        $totalCostRemissions+=$sale['Order']['total_cost'];
      }
      $totalProduced+=$sale['Order']['quantity_produced_B'];
      $totalCap+=$sale['Order']['quantity_produced_C'];

      $orderDateTime=new DateTime($sale['Order']['order_date']);
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
        $remissionstablebody.="<td>".$orderDateTime->format('d-m-Y')."</td>";
        $remissionstablebody.="<td>".$invoiceCode."</td>";
        $remissionstablebody.="<td>".$sale['ThirdParty']['company_name']."</td>";
        $remissionstablebody.="<td class='centered'>".round($sale['Order']['quantity_produced_B'],4)."</td>";
        $remissionstablebody.="<td class='centered'>".round($sale['Order']['quantity_produced_C'],4)."</td>";
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          $remissionstablebody.="<td class='centered'>".round($sale['Order']['total_cost'],4)."</td>";
        }
        $remissionstablebody.="<td class='centered'>".round($sale['Order']['total_price'],4)."</td>";
        if (!empty($sale['CashReceipt']['Currency'])){
          if ($sale['CashReceipt']['Currency']['id'] == CURRENCY_CS){
            $remissionstablebody.="<td class='centered'>".round($sale['CashReceipt']['amount'],4)."</td>";
            $remissionstablebody.="<td class='centered'>0</td>";
          }
          elseif ($sale['CashReceipt']['Currency']['id'] == CURRENCY_USD){
            $remissionstablebody.="<td class='centered'>0</td>";
            $remissionstablebody.="<td class='centered USDcurrency'>".round($sale['CashReceipt']['amount'],4)."</td>";
          }
        }
        else {
          $remissionstablebody.="<td class='centered'>0</td>";
          $remissionstablebody.="<td class='centered'>0</td>";
        }
      $remissionstablebody.="</tr>";
      
      if ($sale['CashReceipt']['bool_annulled']==1){
        $remissionsTableBodyWithActions.="<tr class='italic'>";		
      }
      else {
        $remissionsTableBodyWithActions.="<tr>";		
      }
      $remissionsTableBodyWithActions.="<td>".$orderDateTime->format('d-m-Y')."</td>";
      $remissionsTableBodyWithActions.="<td>".($bool_remission_view_permission?$this->Html->link($invoiceCode, ['action' => 'verRemision', $sale['Order']['id']]):$invoiceCode)."</td>";
      $remissionsTableBodyWithActions.="<td>".($sale['ThirdParty']['bool_generic'] || $userRoleId == ROLE_SALES || $userRoleId == ROLE_FACTURACION?$sale['ThirdParty']['company_name']:$this->Html->link($sale['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verCliente', $sale['ThirdParty']['id']]))."</td>";
      $remissionsTableBodyWithActions.="<td class='centered number'>".$sale['Order']['quantity_produced_B']."</td>";
      $remissionsTableBodyWithActions.="<td class='centered number'>".$sale['Order']['quantity_produced_C']."</td>";
      if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
        $remissionsTableBodyWithActions.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Order']['total_cost']."</span></td>";
      }
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
        //if ($userRoleId==ROLE_ADMIN) { 
        if ($bool_remission_edit_permission) { 
          $remissionsTableBodyWithActions.=$this->Html->link(__('Edit'), array('action' => 'editarRemision', $sale['Order']['id'])); 
          //$remissionsTableBodyWithActions.=$this->Form->postLink(__('Delete'), array('action' => 'delete', $sale['Order']['id']), array(), __('Are you sure you want to delete exit # %s?', $sale['Order']['order_code']));
          //$remissionsTableBodyWithActions.=$this->Form->postLink(__('Anular'), array('action' => 'anularRemision', $sale['Order']['id']), array(), __('Seguro que quiere anular la remisión # %s?', $sale['Order']['order_code']));
        } 
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          $remissionsTableBodyWithActions.=$this->Html->link(__('Pdf'), array('action' => 'verPdfRemision','ext'=>'pdf',$sale['Order']['id'],$filename));				
        }
      $remissionsTableBodyWithActions.="</td>";
      $remissionsTableBodyWithActions.="</tr>";
    }
	}
	$totalrow="<tr class='totalrow'>";
		$totalrow.="<td>Total C$</td>";
		$totalrow.="<td></td>";
		$totalrow.="<td></td>";
		$totalrow.="<td class='centered number'>".$totalProduced."</td>";
		$totalrow.="<td class='centered number'>".$totalCap."</td>";
    if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
      $totalrow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostRemissions."</span></td>";
    }  
    $totalrow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceProductsRemissions."</span></td>";
    $totalrow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalRemissionPriceCS."</span></td>";
    $totalrow.="<td class='centered USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$totalRemissionPriceUSD."</span></td>";
    $totalrow.="<td></td>";
	$totalrow.="</tr>";
	
	
	$totalRowForExcel="<tr class='totalrow'>";
		$totalRowForExcel.="<td>Total C$</td>";
		$totalRowForExcel.="<td></td>";
		$totalRowForExcel.="<td></td>";
		$totalRowForExcel.="<td class='centered'>".round($totalProduced,4)."</td>";
		$totalRowForExcel.="<td class='centered'>".round($totalCap,4)."</td>";
    if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
      $totalRowForExcel.="<td class='centered'>".round($totalCostRemissions,4)."</td>";
    }
    $totalRowForExcel.="<td class='centered'>".round($totalPriceProductsRemissions,4)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalRemissionPriceCS,4)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalRemissionPriceCS,4)."</td>";
	$totalRowForExcel.="</tr>";

	
	$remissionstablebody=$totalRowForExcel.$remissionstablebody.$totalRowForExcel."</tbody>";
	$remissionsTableBodyWithActions=$totalrow.$remissionsTableBodyWithActions.$totalrow."</tbody>";
	
	$remissionsTableWithActions="<table cellpadding='0' cellspacing='0' id='remisiones'>".$remissionsTableHeaderWithActions.$remissionsTableBodyWithActions."</table>";
	$remissionstable="<table cellpadding='0' cellspacing='0' id='remisiones'>".$remissionstableheader.$remissionstablebody."</table>";


	echo "<h2>Ventas y Remisiones</h2>";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";	
			echo "<div class='col-md-5'>";	
        echo $this->Form->create('Report'); 
        echo "<fieldset>"; 
          echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>($userRoleId == ROLE_ADMIN || $canSeeUtilityTables?2014:date('Y')-1),'maxYear'=>date('Y')]);
          echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>($userRoleId == ROLE_ADMIN || $canSeeUtilityTables?2014:date('Y')-1),'maxYear'=>date('Y')]);
          echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
          echo $this->Form->input('Report.payment_option_id',array('label'=>__('Visualizar Contado/Crédito'),'default'=>$paymentOptionId));
          echo $this->Form->input('Report.client_type_id',array('default'=>$clientTypeId,'empty'=>[0=>'-- Todos tipos de cliente --']));
          echo $this->Form->input('Report.zone_id',array('default'=>$zoneId,'empty'=>[0=>'-- Todas zonas --']));
          if ($userRoleId == ROLE_ADMIN  || $canSeeUtilityTables){
            echo $this->Form->input('Report.sale_type_option_id',[
              'label'=>__('Producto Vendido'),
              'value'=>$saleTypeOptionId,
              'style'=>'background-color:'.($saleTypeOptionId>0?'yellow':'none'),
            ]);
          }  
          else {
            echo $this->Form->input('Report.sale_type_option_id',['default'=>$saleTypeOptionId,'type'=>'hidden']);
          }
          if ($userRoleId == ROLE_ADMIN  || $canSeeAllUsers){
            echo $this->Form->input('Report.user_id',[
              'label'=>'Vendedor',
              'value'=>$userId,
              'empty'=>[0=>'-- Todos Vendedores --'],
            ]);
          }
          else {
            echo $this->Form->input('Report.user_id',[
              'default'=>$userId,
              'type'=>'hidden',
            ]);
          }
          

        echo "</fieldset>"; 
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>"; 
          echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>"; 
        }
        echo $this->Form->Submit(__('Refresh')); 
	
        if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
          echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarResumenVentasRemisiones'), array( 'class' => 'btn btn-primary')); 
        }
        
      echo "</div>";
			echo "<div class='col-md-5'>";	
				
				$totalProductionRuns=0;
				$totalAcceptableRuns=0;
        
        if ($warehouseId > 0 && ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables)){
          $utilityTable="";
          $utilitySummaryTableHeader="<thead>";
            $utilitySummaryTableHeader.="<tr>";
              $utilitySummaryTableHeader.="<th>Tipo de Ventas</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Precio Producto Ventas</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Costo Producto Ventas</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Utilidad</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Utilidad %</th>";
            $utilitySummaryTableHeader.="</tr>";
          $utilitySummaryTableHeader.="</thead>";
          
					$utilityTableRows="";
          $utilityTableRows.="<tr>";
            $utilityTableRows.="<td>Ventas Contado</td>";  
            
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProductsCash."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostCash."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceProductsCash-$totalCostCash)."</span></td>";  
            if (!empty($totalPriceProductsCash)){
              $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceProductsCash-$totalCostCash)/$totalPriceProductsCash)."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
            }              
          $utilityTableRows.="</tr>";
          $utilityTableRows.="<tr>";
            $utilityTableRows.="<td>Ventas Crédito</td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProductsCredit."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostCredit."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceProductsCredit-$totalCostCredit)."</span></td>";  
            if (!empty($totalPriceProductsCredit)){
              $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceProductsCredit-$totalCostCredit)/$totalPriceProductsCredit)."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
            }              
          $utilityTableRows.="</tr>";
          
          $utilityTableTotalRow="";
          $utilityTableTotalRow.="<tr class='totalrow'>";
            $utilityTableTotalRow.="<td>Todas Ventas</td>";  
            $utilityTableTotalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProducts."</span></td>";  
            $utilityTableTotalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCost."</span></td>";  
            $utilityTableTotalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceProducts-$totalCost)."</span></td>"; 
            if (!empty($totalPriceProducts)){
              $utilityTableTotalRow.="<td class='centered percentage'><span>".(100*($totalPriceProducts-$totalCost)/$totalPriceProducts)."</span></td>";
            }
            else {
              $utilityTableTotalRow.="<td class='centered percentage'><span>0</span></td>";
            }
            $utilityTableTotalRow.="</tr>";
          
					$utilityTableBody="<tbody>".$utilityTableTotalRow.$utilityTableRows.$utilityTableTotalRow."</tbody>";
				$utilityTable.="<table id='utilidad_efectivo_credito'>".$utilitySummaryTableHeader.$utilityTableBody."</table>";
        
        echo "<h2>Utilidad de Ventas (todas bodegas)</h2>";
				echo $utilityTable;
        $excelUtilityCashCredit=$utilityTable;
        
        $utilityTable="";
        $utilitySummaryTableHeader="<thead>";
            $utilitySummaryTableHeader.="<tr>";
              $utilitySummaryTableHeader.='<th style="min-width:190px">Tipo de Producto</th>';
              $utilitySummaryTableHeader.="<th class='centered'>Precio Producto Ventas</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Costo Producto Ventas</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Utilidad</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Utilidad %</th>";
            $utilitySummaryTableHeader.="</tr>";
          $utilitySummaryTableHeader.="</thead>";
          
					$utilityTableRows="";
          $utilityTableRows.="<tr>";
            $utilityTableRows.='<td>Ventas<span class="hiddenOnScreen"> Botellas</span>'.$this->Form->Submit('Botellas',[
              'id'=>'onlyBottle',
              'name'=>'onlyBottle',
              'class'=>'linkStyledText', 
              'div'=>false,
            ]).'</td>';  
            
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProduced."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostProduced."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceProduced-$totalCostProduced)."</span></td>";  
            if (!empty($totalPriceProduced)){
              $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceProduced-$totalCostProduced)/$totalPriceProduced)."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
            }              
          $utilityTableRows.="</tr>";
          $utilityTableRows.="<tr>";
            $utilityTableRows.='<td>Ventas<span class="hiddenOnScreen"> Tapones</span>'.$this->Form->Submit('Tapones',[
              'id'=>'onlyCap',
              'name'=>'onlyCap',
              'class'=>'linkStyledText', 
              'div'=>false,
            ]).'</td>';  
            
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceCap."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostCap."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceCap-$totalCostCap)."</span></td>";  
            if (!empty($totalPriceCap)){
              $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceCap-$totalCostCap)/$totalPriceCap)."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
            }              
          $utilityTableRows.="</tr>";
          $utilityTableRows.="<tr>";
            $utilityTableRows.='<td>Ventas<span class="hiddenOnScreen"> Servicios</span>'.$this->Form->Submit('Servicios',[
              'id'=>'onlyService',
              'name'=>'onlyService',
              'class'=>'linkStyledText', 
              'div'=>false,
            ]).'</td>';  
            
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceService."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostService."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceService-$totalCostService)."</span></td>";  
            if (!empty($totalPriceService)){
              $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceService-$totalCostService)/$totalPriceService)."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
            }              
          $utilityTableRows.="</tr>";
        /*  
          $utilityTableRows.="<tr>";
            $utilityTableRows.='<td>Ventas<span class="hiddenOnScreen"> Producto Consumible</span>'.$this->Form->Submit('Producto Consumible',[
              'id'=>'onlyConsumible',
              'name'=>'onlyConsumible',
              'class'=>'linkStyledText', 
              'div'=>false,
            ]).'</td>';  
            
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceConsumible."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostConsumible."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceConsumible-$totalCostConsumible)."</span></td>";  
            if (!empty($totalPriceConsumible)){
              $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceConsumible-$totalCostConsumible)/$totalPriceConsumible)."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
            }              
          $utilityTableRows.="</tr>";
        */  
          
          $utilityTableRows.="<tr>";
            $utilityTableRows.='<td>Ventas<span class="hiddenOnScreen"> Importados</span>'.$this->Form->Submit('Importados',[
              'id'=>'onlyImport',
              'name'=>'onlyImport',
              'class'=>'linkStyledText', 
              'div'=>false,
            ]).'</td>';  
            
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceImport."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostImport."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceImport-$totalCostImport)."</span></td>";  
            if (!empty($totalPriceImport)){
              $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceImport-$totalCostImport)/$totalPriceImport)."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
            }              
          $utilityTableRows.="</tr>";
          
          $utilityTableRows.="<tr>";
            $utilityTableRows.='<td>Ventas<span class="hiddenOnScreen"> Ingroup</span>'.$this->Form->Submit('Ingroup',[
              'id'=>'onlyInjection',
              'name'=>'onlyInjection',
              'class'=>'linkStyledText', 
              'div'=>false,
            ]).'</td>';  
            
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceInjection."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostInjection."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceInjection-$totalCostInjection)."</span></td>";  
            if (!empty($totalPriceInjection)){
              $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceInjection-$totalCostInjection)/$totalPriceInjection)."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
            }              
          $utilityTableRows.="</tr>";
        /*
          $utilityTableRows.="<tr>";
            $utilityTableRows.='<td>Ventas<span class="hiddenOnScreen"> Locales</span>'.$this->Form->Submit('Locales',[
              'id'=>'onlyLocal',
              'name'=>'onlyLocal',
              'class'=>'linkStyledText', 
              'div'=>false,
            ]).'</td>';  
            
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceLocal."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostLocal."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceLocal-$totalCostLocal)."</span></td>";  
            if (!empty($totalPriceLocal)){
              $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceLocal-$totalCostLocal)/$totalPriceLocal)."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
            }              
          $utilityTableRows.="</tr>";
        */
          if (!empty($salesOtherProductTypes)){
            
            foreach ($salesOtherProductTypes as $productTypeId=>$productTypeName){
              $utilityTableRows.="<tr>";
                $utilityTableRows.="<td>Ventas Producto ".$productTypeName."</td>";  
                
                $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceOthers[$productTypeId]."</span></td>"; 
                $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostOthers[$productTypeId]."</span></td>";  
                $utilityTableRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceOthers[$productTypeId]-$totalCostOthers[$productTypeId])."</span></td>";  
                if (!empty($totalPriceOthers[$productTypeId])){
                  $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceOthers[$productTypeId]-$totalCostOthers[$productTypeId])/$totalPriceOthers[$productTypeId])."</span></td>";
                }
                else {
                  $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
                }              
              $utilityTableRows.="</tr>";
            }  
          }
          
          $utilityTableTotalRow="";
          $utilityTableTotalRow.="<tr class='totalrow'>";
            $utilityTableTotalRow.="<td>Todas Ventas</td>";  
            $utilityTableTotalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProducts."</span></td>";  
            $utilityTableTotalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCost."</span></td>";  
            $utilityTableTotalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceProducts-$totalCost)."</span></td>"; 
            if (!empty($totalPriceProducts)){
              $utilityTableTotalRow.="<td class='centered percentage'><span>".(100*($totalPriceProducts-$totalCost)/$totalPriceProducts)."</span></td>";
            }
            else {
              $utilityTableTotalRow.="<td class='centered percentage'><span>0</span></td>";
            }
            $utilityTableTotalRow.="</tr>";
          
					$utilityTableBody="<tbody>".$utilityTableTotalRow.$utilityTableRows.$utilityTableTotalRow."</tbody>";
				$utilityTable.="<table id='utilidad_por_tipo'>".$utilitySummaryTableHeader.$utilityTableBody."</table>";
        
        echo "<h2>Utilidad de Ventas por Tipo de Producto  (todas bodegas)</h2>";
				echo $utilityTable;
        $excelUtilityPerType=$utilityTable;
        
      }
			echo "</div>";
			echo "<div class='col-md-2'>";	
				echo "<div class='actions fullwidth' style=''>";	
        if ($userRoleId != ROLE_SALES){
					echo "<h3>".__('Actions')."</h3>";
					echo "<ul>";
            if ($bool_sale_add_permission) {
              echo "<li>".$this->Html->link(__('New Sale'), ['action' => 'crearVenta'])."</li>";
              echo "<br/>";
            }
            if ($bool_remission_add_permission) {
              echo "<li>".$this->Html->link(__('New Remission'), ['action' => 'crearRemision'])."</li>";
              echo "<br/>";
            }
            if ($bool_client_index_permission){
              echo "<li>".$this->Html->link(__('List Clients'), ['controller' => 'third_parties', 'action' => 'resumenClientes'])."</li>";
            }
            if ($bool_client_add_permission) {
              echo "<li>".$this->Html->link(__('New Client'), ['controller' => 'third_parties', 'action' => 'crearCliente'])."</li>";
            }
          echo "</ul>";
        }
				echo "</div>";
			echo "</div>";
		echo "</div>";
	echo "</div>";			        
?>
</div>
<div class='related'>
<?php
  $excelOutput="";
  if ($warehouseId == 0){
    echo "<h2>Seleccione una bodega para ver datos</h2>";
  }
  else {
    echo "<h3>Ventas</h3>";
    echo "<p class='comment'>Facturas de contado aparecen <span style='color:#f00;'>en rojo</span></p>";
    echo $salesTableWithActions;
    
    echo "<h3>Remisiones</h3>";
    echo $remissionsTableWithActions;
    
    $excelOutput=$salestable.$remissionstable;
    if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
      $excelOutput.=$excelUtilityCashCredit;
      $excelOutput.=$excelUtilityPerType;
    }
    
  }
  $_SESSION['resumenVentasRemisiones'] = $excelOutput;
  echo $this->Form->End(); 
	
?>
</div>