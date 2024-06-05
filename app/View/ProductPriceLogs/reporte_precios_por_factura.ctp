<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script-->
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
  $('body').on('change','#ProductPriceLogClientId',function(e){	 
    $('#changeDate').trigger('click');
  });

  $('body').on('change','.price',function(){	
    var previousPrice=parseFloat($(this).closest('td').find('span.previousprice').text());
    var newPrice=parseFloat($(this).val());
    if (isNaN(newPrice)){
      $(this).val(0);
      newPrice=0;
    }
    if (Math.abs(previousPrice-newPrice) > 0.001){
      $(this).css('background-color','#8aeecf');
    }
    else {
      $(this).css('background-color','#ffffff');
    }
    
	});	
   
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
	function roundToThree(num) {    
		return +(Math.round(num + "e+3")  + "e-3");
	}
	$('#content').keypress(function(e) {
		if(e.which == 13) { // Checks for the enter key
			e.preventDefault(); // Stops IE from triggering the button to be clicked
		}
	});
	
	$('div.decimal input').click(function(){
		if ($(this).val()=="0"){
			$(this).val("");
		}
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
    $('select.fixed option:not(:selected)').attr('disabled', true);
    <?php if ($boolSaved){ ?>
      alert('Se registraron los precios de venta')
    <?php } ?>
	});
</script>

<div class="productpricelogs form fullwidth">
<?php 
  $reportDateTime=new DateTime();
	echo $this->Form->create('ProductPriceLog');
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Reporte Precios por Factura')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-xs-12'>";	
					echo "<div class='col-sm-9 col-lg-6'>";	
            echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
            echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
            echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
            //echo $this->Form->input('currency_id',['label'=>'Precios','default'=>CURRENCY_CS,'class'=>'fixed']);
            //echo $this->Form->input('product_category_id',['label'=>__('Categoría de Producto'),'value'=>$productCategoryId,'empty'=>[0=>'--TODAS CATEGORÍAS DE PRODUCTO--']]);
            //echo  $this->Form->input('existence_option_id',['label'=>__('Existencia'),'default'=>$existenceOptionId]);
            //echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
            echo $this->Form->input('client_id',['label'=>'Cliente','default'=>$clientId,'empty'=>[0=>" -- Todos Clientes -- "]]);
            
            echo $this->Form->Submit(__('Cambiar Selección'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
            echo "<br/>";
            echo  "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
            
            echo  "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
            echo "<br/>";
            echo "<br/>";
            $fileName=($reportDateTime->format('Ymd')).'_Reporte_Precios_por_Factura.xlsx';
            echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReportePreciosPorFactura',$fileName], ['class' => 'btn btn-primary']); 
          echo "</div>";
        echo "</div>";
      echo "</div>"; 
      echo "<div class='row'>";
        //echo $this->Form->Submit(__('Grabar Precios de Venta'),['id'=>'savePrices2','name'=>'savePrices2','style'=>'width:300px;']);

        $excelOutput='';
        
        $startDateTime=new DateTime($startDate);
        $endDateTime=new DateTime($endDate);
        
        foreach ($invoiceProductPriceArray['ProductType'] as $productTypeId=>$productTypeData){
          if (!empty($productTypeData['ProductTypeInfo']['Product'])){ 
            //pr($productTypeData['Product']);
            echo "<div class='col-xs-12' style='padding:5px;'>";
              echo "<h3>Precios antes de IVA para línea de producto ".$productTypeData['ProductTypeInfo']['name']." en facturas entre ".$startDateTime->format('d-m-Y')." y ".$endDateTime->format('d-m-Y')."</h3>";
              
              $priceTableHead="<thead>";
              if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                $priceTableHead.="<tr>";
                  $priceTableHead.="<th style='width:100px;'></th>";
                  $priceTableHead.="<th style='width:100px;'></th>";
                  if ($clientId == 0){
                    $priceTableHead.="<th style='width:100px;'></th>";
                  }
                  foreach ($productTypeData['ProductTypeInfo']['Product'] as $productId =>$productData){
                    $priceTableHead.='<th colspan="'.count($productData['RawMaterial']).'" style="text-align:center">'.($this->Html->link($productData['name'],['action'=>'registrarPreciosProducto',$productId])).'</th>';
                  }
                $priceTableHead.="</tr>";
                $priceTableHead.="<tr>";
                  $priceTableHead.="<th style='width:100px;'>Fecha</th>";
                  $priceTableHead.="<th style='width:100px;'>Factura</th>";
                  if ($clientId == 0){
                    $priceTableHead.="<th style='width:100px;'>Cliente</th>";
                  }
                  foreach ($productTypeData['ProductTypeInfo']['Product'] as $productId =>$productData){
                    foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                      $priceTableHead.='<th style="text-align:center">'.$rawMaterialData['abbreviation'].'</th>';
                    }
                  }
                $priceTableHead.="</tr>";
              }
              else {
                $priceTableHead.="<tr>";
                  $priceTableHead.="<th style='width:100px;'>Cliente</th>";
                  foreach ($productTypeData['ProductTypeInfo']['Product'] as $productId =>$productData){
                      $priceTableHead.='<th style="text-align:center">'.$productData['name'].'</th>';
                    }
               
                $priceTableHead.="</tr>";
              }  
              $priceTableHead.="</thead>";
              
              $priceTableBodyRows="";
              //echo "client count is ".count($productTypeData['Client'])."<br/>";
              foreach ($productTypeData['Invoice'] as $invoiceId=>$invoiceData){
                //pr($invoiceData);
                $invoiceDateTime=new DateTime($invoiceData['Invoice']['order_date']);
                //echo "invoice id is ".$invoiceId."<br/>";
                $priceTableBodyRows.='<tr>';
                  $priceTableBodyRows.='<td>'.$invoiceDateTime->format('d-m-Y').'</td>';
                  if ($boolVerVenta){
                    $priceTableBodyRows.='<td>'.$this->Html->Link($invoiceData['Invoice']['order_code'],['controller'=>'orders','action'=>'verVenta',$invoiceData['Invoice']['id']]).'</td>';
                  }
                  else {
                    $priceTableBodyRows.='<td>'.$invoiceData['Invoice']['order_code'].'</td>';
                  }
                  if ($clientId == 0){
                    $priceTableBodyRows.='<td>'.$clients[$invoiceData['Invoice']['third_party_id']].'</td>';
                  }
                  if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                    foreach ($productTypeData['ProductTypeInfo']['Product'] as $productId =>$productData){
                      //pr($productData);
                      foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                        //echo "rawMaterialId is ".$rawMaterialId."<br/>";
                
                        $defaultValue=0;
                        if (array_key_exists($productId,$invoiceData['Product'])
                          && array_key_exists($rawMaterialId,$invoiceData['Product'][$productId]['RawMaterial'])
                        ){
                          $defaultValue=round($invoiceData['Product'][$productId]['RawMaterial'][$rawMaterialId]['invoicePrice'],2);
                          
                        }
                        $priceTableBodyRows.='<td class="centered">'.($defaultValue == 0 ?'-':('<span class="amount">'.$defaultValue.'</span>')).'</td>';
                      }
                    }
                  }
                  else {
                    foreach ($productTypeData['ProductTypeInfo']['Product'] as $productId =>$productData){
                      //pr($productData);
                     
                      $defaultValue=0;
                      if (array_key_exists($productId,$invoiceData['Product'])){
                        $defaultValue=round($clientData['Product'][$productId]['price'],2);
                        
                      }
                      
                      $priceTableBodyRows.='<td class="centered">'.($defaultValue == 0 ?'-':('<span class-"amount">'.$defaultValue.'</span>')).'</td>';
                    }
                    
                  }
                  $priceTableBodyRows.='</tr>';
              }  
                
              $priceTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$priceTableBodyRows."</tbody>";  
              
              
            $priceTable="<table id='precios_por_factura_".$productTypeData['ProductTypeInfo']['name']."'>".$priceTableHead.$priceTableBody."</table>";
             echo $priceTable;
             echo "</div>";  
             
             $excelTable="<table id='precios_".$productTypeData['ProductTypeInfo']['name']."'>".$priceTableHead.$priceTableBody."</table>";
             $excelOutput.= $excelTable;
          }
        }
      
        $_SESSION['reportePreciosPorFactura'] = $excelOutput;
      
        //echo $this->Form->Submit(__('Grabar Precios de Venta'),['id'=>'savePrices','name'=>'savePrices','style'=>'width:300px;','div'=>['class'=>'submit','style'=>'clear:left']]);
        
      echo "</div>";        
      echo $this->Form->end();
    echo "</div>";
      
    
  echo "</div>";
echo "</fieldset>";
?>
</div>