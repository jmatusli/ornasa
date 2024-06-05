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
<div class="orders index sales fullwidth">
<?php 
  $startDateTime=new DateTime($startDate);
  $endDateTime=new DateTime($endDate);
  
  $salesTableHeader='<thead>';
		$salesTableHeader.='<tr>';
			$salesTableHeader.='<th>Fecha</th>';
			$salesTableHeader.='<th>Cliente</th>';
      $salesTableHeader.='<th>Factura</th>';
			$salesTableHeader.='<th>Orden de Venta</th>';
      $salesTableHeader.='<th>Cotización</th>';
			//$salesTableHeader.='<th class="centered"># Envase A</th>';
			//$salesTableHeader.='<th class="centered"># Tapones</th>';
      //$salesTableHeader.='<th class="centered"># Servicios</th>';
      //$salesTableHeader.='<th class="centered"># Suministros</th>';
      //$salesTableHeader.='<th class="centered"># Importados</th>';
      //$salesTableHeader.='<th class="centered"># Locales</th>';
      //if (!empty($salesOtherProductTypes)){
      //  foreach ($salesOtherProductTypes as $productTypeId=>$productTypeName){
      //    $salesTableHeader.='<th class="centered"># '.$productTypeName.'</th>';
      //  }
      //}
      switch ($paymentOptionId){
        case INVOICES_ALL:
          if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
            $salesTableHeader.='<th class="centered">Costo Total</th>';
          }
          $salesTableHeader.='<th class="centered">Precio Total</th>';
          break;
        case INVOICES_CASH:
          if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
            $salesTableHeader.='<th class="centered">Costo Efectivo</th>';
          }
          $salesTableHeader.='<th class="centered">Precio Efectivo</th>';
          break;
        case INVOICES_CREDIT:
          if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
            $salesTableHeader.='<th class="centered">Costo Crédito</th>';
          }
          $salesTableHeader.='<th class="centered">Precio Crédito</th>';
          break;
      }
      $salesTableHeader.='<th class="centered">Precio C$</th>';
      $salesTableHeader.='<th class="centered">Precio  US$</th>';      
      if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
        switch ($paymentOptionId){
          case INVOICES_ALL:
            $salesTableHeader.='<th class="centered">Margen</th>'; 
            break;
          case INVOICES_CASH:
            $salesTableHeader.='<th class="centered">Margen Efectivo</th>'; 
            break;
          case INVOICES_CREDIT:
            $salesTableHeader.='<th class="centered">Margen Crédito</th>'; 
            break;
        }
      }
      $excelSalesTableHeader=$salesTableHeader;
    $excelSalesTableHeader.='</tr>';
	$excelSalesTableHeader.='</thead>';  
      
		$salesTableHeader.='</tr>';
	$salesTableHeader.='</thead>';
	
	
  
  
  $userTablesContent='';
  $excelUserTablesContent='';
  foreach ($selectedUsers as $selectedUserId=>$selectedUserName){
    $excelSalesTableRows=$salesTableRows="";
    
    $totalPriceProductsCash=0;
    $totalPriceProductsCredit=0;
    $totalPriceProducts=0;
    $totalInvoicePriceCS=0; 
    $totalInvoicePriceUSD=0; 
    $totalCostCash=0;
    $totalCostCredit=0;
    $totalCost=0;
    /*
    $totalPriceProduced=0;
    $totalPriceCap=0;
    $totalPriceService=0;
    //$totalPriceConsumible=0;
    $totalPriceImport=0;
    $totalPriceLocal=0;
    $totalPriceOthers=[];
    
    $totalCostProduced=0;
    $totalCostCap=0;
    $totalCostService=0;
    //$totalCostConsumible=0;
    $totalCostImport=0;
    $totalCostLocal=0;
    $totalCostOthers=[];
    
    $totalProduced=0;
    $totalCap=0;
    $totalService=0;
    //$totalConsumible=0;
    $totalImport=0;
    $totalLocal=0;
    $totalOthers=[];
    */
    
    if (empty($allUserSales[$selectedUserId]['Sales'])){
      //$userTablesContent.='<h2>No hay facturas para vendedor '.$selectedUserName.' para el período '.$startDateTime->format('d-m-Y').' -> '.$endDateTime->format('d-m-Y').'</h2>';
    }
    else {
      $userTablesContent.='<h2>Facturas para vendedor '.$selectedUserName.' para el período '.$startDateTime->format('d-m-Y').' -> '.$endDateTime->format('d-m-Y').'</h2>';
      foreach ($allUserSales[$selectedUserId]['Sales'] as $sale){
        //pr($sale);
      	
        if ($sale['Invoice']['bool_credit']){
          $totalPriceProductsCredit+=$sale['Order']['total_price'];
          $totalCostCredit+=$sale['Order']['total_cost'];
        }
        else {
          $totalPriceProductsCash+=$sale['Order']['total_price'];
          $totalCostCash+=$sale['Order']['total_cost'];
        }
      /*  
        //$totalPriceProduced+=$sale['Order']['price_produced'];
        //$totalPriceCap+=$sale['Order']['price_cap'];
        //$totalPriceService+=$sale['Order']['price_service'];
        ////$totalPriceConsumible+=$sale['Order']['price_consumible'];
        //$totalPriceImport+=$sale['Order']['price_import'];
        //$totalPriceLocal+=$sale['Order']['price_local'];
        
        //if (!empty($sale['Order']['price_others'])){
        //  foreach ($sale['Order']['price_others'] as $productTypeId=>$priceOther){
        //    if (!array_key_exists($productTypeId,$totalPriceOthers)){
        //      $totalPriceOthers[$productTypeId]=0;
        //    }
        //    $totalPriceOthers[$productTypeId]+=$priceOther;
        //  }
        //  //pr($totalPriceOthers);
        //}
        
        //$totalCostProduced+=$sale['Order']['cost_produced'];
        //$totalCostCap+=$sale['Order']['cost_cap'];
        //$totalCostService+=$sale['Order']['cost_service'];
        ////$totalCostConsumible+=$sale['Order']['cost_consumible'];
        //$totalCostImport+=$sale['Order']['cost_import'];
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
      */  
        $totalPriceProducts+=$sale['Order']['total_price'];
        $totalCost+=$sale['Order']['total_cost'];
        //pr($sale['Order']);
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
            || $saleTypeOptionId == SALE_TYPE_LOCAL && $sale['Order']['price_local'] > 0
          )      
        ){
          
          if (!empty($sale['Invoice']['Currency'])){
            if ($sale['Invoice']['Currency']['id']==CURRENCY_CS){
              $totalInvoicePriceCS+=$sale['Invoice']['total_price']; 
            }
            elseif ($sale['Invoice']['Currency']['id']==CURRENCY_USD){
              $totalInvoicePriceUSD+=$sale['Invoice']['total_price']; 
            }
          }
        /*
          $totalProduced+=$sale['Order']['quantity_produced'];
          $totalCap+=$sale['Order']['quantity_cap'];
          $totalService+=$sale['Order']['quantity_service'];
          //$totalConsumible+=$sale['Order']['quantity_consumible'];
          $totalImport+=$sale['Order']['quantity_import'];
          $totalLocal+=$sale['Order']['quantity_local'];
          if (!empty($sale['Order']['quantity_others'])){
            foreach ($sale['Order']['quantity_others'] as $productTypeId=>$quantityOther){
              if (!array_key_exists($productTypeId,$totalOthers)){
                $totalOthers[$productTypeId]=0;
              }
              $totalOthers[$productTypeId]+=$quantityOther;
            }
            //pr($totalOthers);
          }
        */  
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
            $salesTableRow.="<td>".($sale['ThirdParty']['bool_generic'] || $userRoleId == ROLE_SALES || $userRoleId == ROLE_FACTURACION?$sale['ThirdParty']['company_name']:$this->Html->link($sale['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verCliente', $sale['ThirdParty']['id']]))."</td>";
            $salesTableRow.="<td>".($bool_sale_view_permission?$this->Html->link($invoiceCode, ['action' => 'verVenta', $sale['Order']['id']]):$invoiceCode)."</td>";
            $salesTableRow.="<td>".(empty($sale['Invoice']['SalesOrder']['id'])?"-":($bool_sale_view_permission?$this->Html->link($sale['Invoice']['SalesOrder']['sales_order_code'], ['controller'=>'salesOrders','action' => 'detalle', $sale['Invoice']['SalesOrder']['id']]):$sale['Invoice']['SalesOrder']['sales_order_code']))."</td>";
            $salesTableRow.="<td>".((empty($sale['Invoice']['SalesOrder']['id']) || empty($sale['Invoice']['SalesOrder']['Quotation']['id']))?"-":($bool_sale_view_permission?$this->Html->link($sale['Invoice']['SalesOrder']['Quotation']['quotation_code'], ['controller'=>'quotations','action' => 'detalle', $sale['Invoice']['SalesOrder']['Quotation']['id']]):$sale['Invoice']['SalesOrder']['Quotation']['quotation_code']))."</td>";
            
            //$salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_produced']."</span></td>";
            //$salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_cap']."</span></td>";
            //$salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_service']."</span></td>";
            ////$salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_consumible']."</span></td>";
            //$salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_import']."</span></td>";
            //$salesTableRow.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_local']."</span></td>";
            //if (!empty($salesOtherProductTypes)){
            //  foreach ($salesOtherProductTypes as $productTypeId=>$productTypeName){
            //    $typeQuantityOther=0;
            //    if (!empty($sale['Order']['quantity_others'])){
            //      foreach ($sale['Order']['quantity_others'] as $quantityProductTypeId=>$quantityOther){
            //        if ($quantityProductTypeId == $productTypeId){
            //          $typeQuantityOther =$quantityOther;
            //        }
            //      }
            //    }
            //    $salesTableRow.="<td class='centered'><span class='amountright'>".$typeQuantityOther."</span></td>";
            //  }  
            //} 
            
            if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
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
            if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
              $salesTableRow.="<td class='centered percentage'><span>".(100*($sale['Order']['total_price'] == 0? "-":($sale['Order']['total_price']-$sale['Order']['total_cost'])/$sale['Order']['total_price']))."</span></td>";
            }
            
          $salesTableRow.="</tr>";
          $salesTableRows.=$salesTableRow;

          
          $excelSalesTableRow.="<tr".($sale['Invoice']['bool_credit']?"":" style='background-color:#f00;'").">";
            $excelSalesTableRow.="<td>".$orderDateTime->format('d-m-Y')."</td>";
            $excelSalesTableRow.="<td>".$sale['ThirdParty']['company_name']."</td>";        
            $excelSalesTableRow.="<td>".$invoiceCode."</td>";
            $excelSalesTableRow.="<td>".(empty($sale['Invoice']['SalesOrder']['id'])?"-":$sale['Invoice']['SalesOrder']['sales_order_code'])."</td>";
            $excelSalesTableRow.="<td>".((empty($sale['Invoice']['SalesOrder']['id']) || empty($sale['Invoice']['SalesOrder']['Quotation']['id']))?"-":$sale['Invoice']['SalesOrder']['Quotation']['quotation_code'])."</td>";
            
            //$excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_produced'],4)."</td>";
            //$excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_cap'],4)."</td>";
            //$excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_service'],4)."</td>";
            //$excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_import'],4)."</td>";
            //$excelSalesTableRow.="<td class='centered'>".round($sale['Order']['quantity_local'],4)."</td>";
            //if (!empty($salesOtherProductTypes)){
            //  foreach ($salesOtherProductTypes as $productTypeId=>$productTypeName){
            //    $typeQuantityOther=0;
            //    if (!empty($sale['Order']['quantity_others'])){
            //      foreach ($sale['Order']['quantity_others'] as $quantityProductTypeId=>$quantityOther){
            //        if ($quantityProductTypeId == $productTypeId){
            //          $typeQuantityOther =$quantityOther;
            //        }
            //      }
            //    }
            //    $excelSalesTableRow.="<td class='centered'>".round($typeQuantityOther,4)."</td>";
            //  }  
            //}    
                
            if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
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
            if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
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
    }
    
    
    $totalRow="<tr class='totalrow'>";
      $totalRow.="<td>Total C$</td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";
      //$totalRow.="<td class='centered number'><span class='amountright'>".$totalProduced."</span></td>";
      //$totalRow.="<td class='centered number'><span class='amountright'>".$totalCap."&nbsp;</span></td>";
      //$totalRow.="<td class='centered number'><span class='amountright'>".$totalService."&nbsp;</span></td>";
      ////$totalRow.="<td class='centered number'><span class='amountright'>".$totalConsumible."&nbsp;</span></td>";
      //$totalRow.="<td class='centered number'><span class='amountright'>".$totalImport."&nbsp;</span></td>";
      //$totalRow.="<td class='centered number'><span class='amountright'>".$totalLocal."&nbsp;</span></td>";
      //if (!empty($salesOtherProductTypes)){
      //  foreach ($salesOtherProductTypes as $productTypeId=>$productTypeName){
      //    $typeTotalQuantityOther=0;
      //    if (!empty($totalOthers)){
      //      foreach ($totalOthers as $totalQuantityProductTypeId=>$totalQuantityOther){
      //        if ($totalQuantityProductTypeId == $productTypeId){
      //          $typeTotalQuantityOther =$totalQuantityOther;
      //        }
      //      }
      //    }
      //    $totalRow.="<td class='centered number'><span class='amountright'>".$typeTotalQuantityOther."&nbsp;</span></td>";
      //  }  
      //} 
      
      switch ($paymentOptionId){
        case INVOICES_ALL:
          if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
            $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCost."</span></td>";
          }
          $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProducts."</span></td>";
          break;
        case INVOICES_CASH:
          if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
            $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostCash."</span></td>";
          }
          $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProductsCash."</span></td>";
          break;
        case INVOICES_CREDIT:
          if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
            $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostCredit."</span></td>";
          }
          $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProductsCredit."</span></td>";
          break;
      }
      
      $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalInvoicePriceCS."</span></td>";
      $totalRow.="<td class='centered USDcurrency'><span class='currency'></span><span class='amountright'>".$totalInvoicePriceUSD."</span></td>";
      
      if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
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
  /*  
    $totalRowForExcel="<tr class='totalrow'>";
      $totalRowForExcel.="<td>Total C$</td>";
      $totalRowForExcel.="<td></td>";
      $totalRowForExcel.="<td></td>";
      $totalRowForExcel.="<td></td>";
      $totalRowForExcel.="<td></td>";
      
      //$totalRowForExcel.="<td class='centered'>".round($totalProduced,2)."</td>";
      //$totalRowForExcel.="<td class='centered'>".round($totalCap,2)."</td>";
      //$totalRowForExcel.="<td class='centered'>".round($totalService,2)."</td>";
      ////$totalRowForExcel.="<td class='centered'>".round($totalConsumible,2)."</td>";
      //$totalRowForExcel.="<td class='centered'>".round($totalImport,2)."</td>";
      //$totalRowForExcel.="<td class='centered'>".round($totalLocal,2)."</td>";
      //foreach ($totalOthers as $totalOther){
      //  $totalRowForExcel.="<td class='centered'>".round($totalOther,2)."</td>";
      //}
      switch ($paymentOptionId){
        case INVOICES_ALL:
          if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
            $totalRowForExcel.="<td class='centered'>".round($totalCost,2)."</td>";
          }
          $totalRowForExcel.="<td class='centered'>".round($totalPriceProducts,2)."</td>";
          break;
        case INVOICES_CASH:
          if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
            $totalRowForExcel.="<td class='centered'>".round($totalCostCash,2)."</td>";
          }
          $totalRowForExcel.="<td class='centered'>".round($totalPriceProductsCash,2)."</td>";
          break;
        case INVOICES_CREDIT:
          if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
            $totalRowForExcel.="<td class='centered'>".round($totalCostCredit,2)."</td>";
          }
          $totalRowForExcel.="<td class='centered'>".round($totalPriceProductsCredit,2)."</td>";
          break;
      }
      $totalRowForExcel.="<td class='centered'>".round($totalInvoicePriceCS,2)."</td>";
      $totalRowForExcel.="<td class='centered'>".round($totalInvoicePriceUSD,2)."</td>";
      if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
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
      if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
        
        
      }
    $totalRowForExcel.="</tr>";
  */	
    
    if (!empty($salesTableRows)){
      //$excelSalesTableBody='<tbody>'.$totalRowForExcel.$excelSalesTableRows.$totalRowForExcel."</tbody>";
      $excelSalesTableBody='<tbody>'.$excelSalesTableRows."</tbody>";
      $salesTableBody='<tbody>'.$totalRow.$salesTableRows.$totalRow."</tbody>";
      
      $salesTableWithActions="<table cellpadding='0' cellspacing='0' id='ventas'>".$salesTableHeader.$salesTableBody."</table>";
      $salesTable="<table cellpadding='0' cellspacing='0' id='ventas'>".$excelSalesTableHeader.$excelSalesTableBody."</table>";
      $userTablesContent.=$salesTableWithActions;
      $excelUserTablesContent.=$salesTable;
    }
    
    
  }
  
	
	

	echo "<h1>Facturas por Vendedor</h1>";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";	
			echo "<div class='col-sm-6'>";	
        echo $this->Form->create('Report'); 
        echo "<fieldset>"; 
          echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>($userRoleId != ROLE_SALES && $userRoleId != ROLE_FACTURACION?2014:date('Y')-1),'maxYear'=>date('Y')]);
          echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>($userRoleId != ROLE_SALES && $userRoleId != ROLE_FACTURACION?2014:date('Y')-1),'maxYear'=>date('Y')]);
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
          if ($userRoleId == ROLE_ADMIN  || $canSeeAllUsers || $canSeeAllVendors){
            echo $this->Form->input('Report.vendor_user_id',[
              'label'=>'Vendedor',
              'value'=>$vendorUserId,
              'options'=>$users,
              'empty'=>[0=>'-- Todos Vendedores --'],
            ]);
          }
          else {
            echo $this->Form->input('Report.vendor_user_id',[
              'default'=>$vendorUserId,
              'type'=>'hidden',
            ]);
          }
          

        echo "</fieldset>"; 
        if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
          echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>"; 
          echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>"; 
        }
        echo $this->Form->Submit(__('Refresh')); 
	
        if ($userRoleId == ROLE_ADMIN || $canSeeInventoryCost){
          echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarFacturasPorVendedor'), array( 'class' => 'btn btn-primary')); 
        }
        
      echo "</div>";
			echo "<div class='col-sm-6'>";	
      if ($warehouseId > 0 && ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables)){ 
        $utilityColumns='';
        $utilityColumns.='<th class="centered">Precio</th>';
        $utilityColumns.='<th class="centered">Costo</th>';
        $utilityColumns.='<th class="centered">Utilidad</th>';
        $utilityColumns.='<th class="centered">Utilidad %</th>';
      
        $vendorUtilityTableHead='';
        $vendorUtilityTableHead.='<thead>';
          $vendorUtilityTableHead.='<tr>';
            $vendorUtilityTableHead.='<th>Vendedor</th>';
            $vendorUtilityTableHead.=$utilityColumns;
          $vendorUtilityTableHead.='</tr>';
        $vendorUtilityTableHead.='</thead>';
        
        $vendorUtilityTableRows='';
        foreach ($statsByVendor as $currentVendorUserId=>$vendorUtilityData){
          if ($currentVendorUserId != '0' && $vendorUtilityData['price'] > 0){
            $vendorUtilityTableRows.='<tr>';
              $vendorUtilityTableRows.='<td>'.($currentVendorUserId > 0?$users[$currentVendorUserId]:"NA").'</td>';
              $vendorUtilityTableRows.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$vendorUtilityData['price'].'</span></td>';
              $vendorUtilityTableRows.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$vendorUtilityData['cost'].'</span></td>';
              $vendorUtilityTableRows.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.($vendorUtilityData['price']-$vendorUtilityData['cost']).'</span></td>';
              $vendorUtilityTableRows.='<td class="centered percentage"><span>'.($vendorUtilityData['price']>0?(100*($vendorUtilityData['price']-$vendorUtilityData['cost'])/$vendorUtilityData['price']):0).'</span></td>';
            $vendorUtilityTableRows.='</tr>';
          }
        }
        $vendorUtilityTotalRow='';
        $vendorUtilityTotalRow.='<tr class="totalrow">';
          $vendorUtilityTotalRow.='<td>Totales</td>';
          $vendorUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$statsByVendor[0]['price'].'</span></td>';
          $vendorUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$statsByVendor[0]['cost'].'</span></td>';
          $vendorUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.($statsByVendor[0]['price']-$statsByVendor[0]['cost']).'</span></td>';
          $vendorUtilityTotalRow.='<td class="centered percentage"><span>'.($statsByVendor[0]['price']>0?(100*($statsByVendor[0]['price']-$statsByVendor[0]['cost'])/$statsByVendor[0]['price']):0).'</span></td>';
        $vendorUtilityTotalRow.='</tr>';
        
        $vendorUtilityTableBody='<tbody>'.$vendorUtilityTotalRow.$vendorUtilityTableRows.$vendorUtilityTotalRow.'</tbody>';
        $vendorUtilityTable='<table id="utilidad_x_vendedor">'.$vendorUtilityTableHead.$vendorUtilityTableBody.'</table>';
        
        $creditUtilityTableHead='';
        $creditUtilityTableHead.='<thead>';
          $creditUtilityTableHead.='<tr>';
            $creditUtilityTableHead.='<th>Estado Crédito</th>';
            $creditUtilityTableHead.=$utilityColumns;
          $creditUtilityTableHead.='</tr>';
        $creditUtilityTableHead.='</thead>';
        
        $creditUtilityTableRows='';
        foreach ($statsByVendorByCreditStatus as $creditType=>$creditUtilityData){
          if ($creditType != '0'){
            //pr($creditUtilityData);
            $creditVendorUtilityTableRows='';
            foreach ($creditUtilityData as $creditVendorUserId => $creditVendorUtilityData){            
              if ($creditVendorUserId !='0' && $creditVendorUtilityData['price'] > 0){
                $creditVendorUtilityTableRows.='<tr>';
                  $creditVendorUtilityTableRows.='<td>'.($creditVendorUserId > 0 ? $users[$creditVendorUserId]:"NA").'</td>';
                  $creditVendorUtilityTableRows.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$creditVendorUtilityData['price'].'</span></td>';
                  $creditVendorUtilityTableRows.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$creditVendorUtilityData['cost'].'</span></td>';
                  $creditVendorUtilityTableRows.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.($creditVendorUtilityData['price']-$creditVendorUtilityData['cost']).'</span></td>';
                  $creditVendorUtilityTableRows.='<td class="centered percentage"><span>'.($creditVendorUtilityData['price']>0?(100*($creditVendorUtilityData['price']-$creditVendorUtilityData['cost'])/$creditVendorUtilityData['price']):0).'</span></td>';
                $creditVendorUtilityTableRows.='</tr>';
              }
              
            }
            $creditVendorUtilityTotalRow='';
            $creditVendorUtilityTotalRow.='<tr style="background-color:#84c7a8">';
              $creditVendorUtilityTotalRow.='<td>'.($creditType =='cash' ? "Contado":"Crédito").'</td>';
              $creditVendorUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$creditUtilityData[0]['price'].'</span></td>';
              $creditVendorUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$creditUtilityData[0]['cost'].'</span></td>';
              $creditVendorUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.($creditUtilityData[0]['price']-$creditUtilityData[0]['cost']).'</span></td>';
              $creditVendorUtilityTotalRow.='<td class="centered percentage"><span>'.($creditUtilityData[0]['price']>0?(100*($creditUtilityData[0]['price']-$creditUtilityData[0]['cost'])/$creditUtilityData[0]['price']):0).'</span></td>';
            $creditVendorUtilityTotalRow.='</tr>';
            
            $creditUtilityTableRows.=$creditVendorUtilityTotalRow.$creditVendorUtilityTableRows.$creditVendorUtilityTotalRow;
  
          } 
        }
        $creditUtilityTotalRow='';
        $creditUtilityTotalRow.='<tr class="totalrow">';
          $creditUtilityTotalRow.='<td>Totales</td>';
          $creditUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$statsByVendorByCreditStatus[0]['price'].'</span></td>';
          $creditUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$statsByVendorByCreditStatus[0]['cost'].'</span></td>';
          $creditUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.($statsByVendorByCreditStatus[0]['price']-$statsByVendorByCreditStatus[0]['cost']).'</span></td>';
          $creditUtilityTotalRow.='<td class="centered percentage"><span>'.($statsByVendorByCreditStatus[0]['price']>0?(100*($statsByVendorByCreditStatus[0]['price']-$statsByVendorByCreditStatus[0]['cost'])/$statsByVendorByCreditStatus[0]['price']):0).'</span></td>';
        $creditUtilityTotalRow.='</tr>';
        
        $creditUtilityTableBody='<tbody>'.$creditUtilityTotalRow.$creditUtilityTableRows.$creditUtilityTotalRow.'</tbody>';
        $creditUtilityTable='<table id="utilidad_x_credito_x_vendedor">'.$creditUtilityTableHead.$creditUtilityTableBody.'</table>';
        
        $clientTypeUtilityTableHead='';
        $clientTypeUtilityTableHead.='<thead>';
          $clientTypeUtilityTableHead.='<tr>';
            $clientTypeUtilityTableHead.='<th>Vendedor</th>';
            $clientTypeUtilityTableHead.=$utilityColumns;
          $clientTypeUtilityTableHead.='</tr>';
        $clientTypeUtilityTableHead.='</thead>';
        
        $clientTypeUtilityTableRows='';
        foreach ($statsByClientType as $currentClientTypeId=>$clientTypeUtilityData){
          if ($currentClientTypeId != '0' && $clientTypeUtilityData['price'] > 0){
            $clientTypeUtilityTableRows.='<tr>';
              $clientTypeUtilityTableRows.='<td>'.($currentClientTypeId > 0 ? $clientTypes[$currentClientTypeId]:"NA").'</td>';
              $clientTypeUtilityTableRows.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$clientTypeUtilityData['price'].'</span></td>';
              $clientTypeUtilityTableRows.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$clientTypeUtilityData['cost'].'</span></td>';
              $clientTypeUtilityTableRows.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.($clientTypeUtilityData['price']-$clientTypeUtilityData['cost']).'</span></td>';
              $clientTypeUtilityTableRows.='<td class="centered percentage"><span>'.($clientTypeUtilityData['price']>0?(100*($clientTypeUtilityData['price']-$clientTypeUtilityData['cost'])/$clientTypeUtilityData['price']):0).'</span></td>';
            $clientTypeUtilityTableRows.='</tr>';
          }
        }
        
        $clientTypeUtilityTotalRow='';
        $clientTypeUtilityTotalRow.='<tr class="totalrow">';
          $clientTypeUtilityTotalRow.='<td>Totales</td>';
          $clientTypeUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$statsByClientType[0]['price'].'</span></td>';
          $clientTypeUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.$statsByClientType[0]['cost'].'</span></td>';
          $clientTypeUtilityTotalRow.='<td class="centered CScurrency"><span class="currency"></span><span class="amountright">'.($statsByClientType[0]['price']-$statsByClientType[0]['cost']).'</span></td>';
          $clientTypeUtilityTotalRow.='<td class="centered percentage"><span>'.($statsByClientType[0]['price']>0?(100*($statsByClientType[0]['price']-$statsByClientType[0]['cost'])/$statsByClientType[0]['price']):0).'</span></td>';
        $clientTypeUtilityTotalRow.='</tr>';
        
        $clientTypeUtilityTableBody='<tbody>'.$clientTypeUtilityTotalRow.$clientTypeUtilityTableRows.$clientTypeUtilityTotalRow.'</tbody>';
        $clientTypeUtilityTable='<table id="utilidad_x_tipo_cliente">'.$clientTypeUtilityTableHead.$clientTypeUtilityTableBody.'</table>';
        
        echo '<h2>Utilidad de Ventas por Vendedor</h2>';
        echo $vendorUtilityTable;
        
        echo '<h2>Utilidad de Ventas por Crédito vs Contado</h2>';
        echo $creditUtilityTable;
        
        echo '<h2>Utilidad de Ventas por Tipo de Cliente</h2>';
        echo $clientTypeUtilityTable;
      }
			echo '</div>';
		echo '</div>';
	echo '</div>';			        
?>
</div>
<div class='related'>
<?php
  $excelOutput="";
  if ($warehouseId == 0){
    echo "<h2>Seleccione una bodega para ver datos</h2>";
  }
  else {
    echo "<h1>Facturas por vendedor</h1>";
    echo "<p class='comment'>Facturas de contado aparecen <span style='color:#f00;'>en rojo</span></p>";
    echo $userTablesContent;
    
    $excelOutput.=$vendorUtilityTable;
    $excelOutput.=$creditUtilityTable;
    $excelOutput.=$clientTypeUtilityTable;
    $excelOutput.=$excelUserTablesContent;
    //if ($userRoleId == ROLE_ADMIN || $canSeeUtilityTables){
    //  $excelOutput.=$excelUtilityCashCredit;
    //  $excelOutput.=$excelUtilityPerType;
    //}
  }
  $_SESSION['facturasPorVendedor'] = $excelOutput;
  echo $this->Form->End(); 
	
?>
</div>