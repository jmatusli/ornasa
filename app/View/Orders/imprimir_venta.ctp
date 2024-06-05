<div class="orders view fullwidth">
<?php
  function monthOfYear($monthString){
    $monthsOfYear=["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
    return $monthsOfYear[(int)$monthString-1];
  }

	//pr($invoice);
	$nowDate=date('Y-m-d');
	$nowDateTime=new DateTime($nowDate);
	$url="img/ornasa_logo.jpg";
	$imageurl=$this->App->assetUrl($url);
  
  $invoiceDateTime=new DateTime($order['Order']['order_date']);
  $invoiceCode=$order['Order']['order_code'];
  
  if ($invoice['Invoice']['bool_credit']){
		$dueDate=new DateTime($invoice['Invoice']['due_date']);
  }
  //pr($order['ThirdParty']);
	$output='';
  $output.='<div class="container-fluid">';
    $output.='<div class="row">';  
      $output.='<div class="col-sm-10">';  
        //$output="<div id='invoicePrint' style='width:215mm;height:215mm;padding:0mm;'>";
        $output.="<div id='invoicePrint' style='width:215mm;height:202mm;padding:0mm;'>";  
          $output.="<table class='noprint' style='height:20mm;font-size:1rem;'>";
            $output.="<tr>";
              $output.="<td class='extraSmall left' style='width:33.33%;'>
                <br/>
                <div class='noprint'>Dirección: Km 10.1 Carretera Nueva León 150 m arriba</div>
                <div class='noprint'>Tel:2299-1123</div>
                <div class='noprint'>administración@ornasa.com<div>
                <div class='noprint'>www.ornasa.com</div>
                &nbsp;
              </td>";
              $output.="<td class='bold' style='width:33.33%;'>
                <img src='".$imageurl."' class='noprint' style='height:20mm;'/>&nbsp;		
                <div class='noprint' style='height:5mm;'>RUC:J0310000103860</div>
              </td>";
              $output.="<td class='bold' style='width:33.33%;'><br/></td>";
            $output.="</tr>";
            
          $output.="</table>";
        
          $output.="<table style='height:6mm;margin-top:3mm;font-size:1rem;'>";
            $output.="<tr>";
              $output.="<td class='bold' style='width:65%;'>
                <div>
                  <span class='left' style='font-size:2rem;'>FACTURA DE ".($invoice['Invoice']['bool_credit']?"CRÉDITO":"CONTADO")."</span>
                </div>
              </td>";
              /*
              $output.='<td class="bold" style="width:25%;">
                <div>
                  <span class="big">FACTURA</span>
                  <span class="right">No</span>
                  <span class="right" style="font-size:1.8rem;">'.$invoiceCode.'</span>
                </div>
              </td>';
              */
              $output.='<td class="bold" style="width:35%;font-size:1.8rem;">
                <div>
                  <span class="big right">FACTURA No '.$invoiceCode.'</span>
                </div>
              </td>';
            $output.="</tr>";
          $output.="</table>";
          $output.="<table  style='height:16mm;'>";
            $output.="<tr>";
            /*
              $output.="<td style='width:60%;font-size:14px;'>
                <div>
                  <span class='left'>Cliente:</span>
                  <span class='left'>".($order['ThirdParty']['id']!=CLIENTS_VARIOUS?$order['ThirdParty']['company_name']:$order['Order']['client_name'])."</span>
                </div>
                <div>
                  <span class='left'>Dirección:</span>
                  <span class='left'>".($order['ThirdParty']['id']!=CLIENTS_VARIOUS?$order['ThirdParty']['address']:$order['Order']['client_address'])."</span>
                </div>
                <div>
                  <span class='left'>Teléfono:</span>
                  <span class='left'>".($order['ThirdParty']['id']!=CLIENTS_VARIOUS?$order['ThirdParty']['phone']:$order['Order']['client_phone'])."</span>
                 </div>
                 <div>
                  <span class='left'>RUC:</span>
                  <span class='left'>".($order['ThirdParty']['id']!=CLIENTS_VARIOUS?$order['ThirdParty']['ruc_number']:$order['Order']['client_ruc'])."</span>
                 </div>
              </td>";
            */
              $output.="<td style='width:60%;font-size:14px;'>
                <div>
                  <span class='left'>Cliente:</span>
                  <span class='left'>".(!empty($order['Order']['client_name']) || $order['ThirdParty']['bool_generic']?$order['Order']['client_name']:$order['ThirdParty']['company_name'])."</span>
                </div>
                <div>
                  <span class='left'>Dirección:</span>
                  <span class='left'>".(!empty($order['Order']['client_address']) || $order['ThirdParty']['bool_generic']?$order['Order']['client_address']:$order['ThirdParty']['address'])."</span>
                </div>
                <div>
                  <span class='left'>Teléfono:</span>
                  <span class='left'>".(!empty($order['Order']['client_phone']) || $order['ThirdParty']['bool_generic']?$order['Order']['client_phone']:$order['ThirdParty']['phone'])."</span>
                 </div>
                 <div>
                  <span class='left'>RUC:</span>
                  <span class='left' style='font-size:1.2em'>".(!empty($order['Order']['client_ruc']) || $order['ThirdParty']['bool_generic']?$order['Order']['client_ruc']:$order['ThirdParty']['ruc_number'])."</span>
                 </div>
              </td>";
              
              $output.="<td style='width:40%;font-size:14px;'>
                <div>
                  <span class='left'>Fecha: </span>
                  <span class='right'>".$invoiceDateTime->format('d')." de ".monthOfYear($invoiceDateTime->format('m')) ." ".$invoiceDateTime->format('Y')."</span>
                </div>
                <div>
                  <span class='left'>Grabado por: </span>
                  <span class='right'>".$order['RecordUser']['first_name']." ".$order['RecordUser']['last_name']."</span>
                </div>
                <div>
                  <span class='left'>Vendedor: </span>
                  <span class='right'>".($order['VendorUser']['first_name']." ".$order['VendorUser']['last_name'])."</span>
                </div>";
                if ($invoice['Invoice']['bool_credit']){
                  $output.="<div>
                      <span class='left'>Crédito vence: </span>
                      <span class='right'>".$dueDate->format('d')." de ".monthOfYear($dueDate->format('m'))." ".$dueDate->format('Y')."</span>
                  </div>
                  <div>
                      <span class='left'>Emitir cheque a nombre Grupo Orna s.a.</span>
                  </div>";
                }
              $output.="</td>";
            $output.="</tr>";
          $output.="</table>";
          
          $output.="<table style='min-height:70mm;height:70mm;border-top-width:2px;border-top-style:solid;'>";
            $output.="<thead style='border-bottom-width:1px;border-bottom-style:solid;'>";
              /*
              $output.="<tr>";
                $output.="<th class='centered' style='width:27%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>Código</th>";
                $output.="<th class='centered' style='width:33%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>Descripción</th>";
                $output.="<th class='centered' style='width:10%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>Cantidad</th>";
                $output.="<th class='centered' style='width:10%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>Bultos</th>";
                $output.="<th class='centered' style='width:10%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>P. Unitario</th>";
                $output.="<th class='centered' style='width:10%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>Total</th>";
              $output.="</tr>";
              */
              $output.="<tr>";
                $output.="<th style='width:20%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>Código</th>";
                $output.="<th style='width:30%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>Descripción</th>";
                $output.="<th class='centered' style='width:10%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>Cantidad</th>";
                $output.="<th class='centered' style='width:10%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>Bultos</th>";
                $output.="<th class='centered' style='width:10%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>P. Unitario</th>";
                $output.="<th class='centered' style='width:15%;font-weight:400;border-bottom-width:1px;border-bottom-style:dashed;'>Total</th>";
              $output.="</tr>";
            $output.="</thead>";
            
            $totalquantity=0;
            $totalprice=0;
            $output.="<tbody>";
            for ($i=0;$i<7;$i++){
              if (count($summedMovements)>$i){
                $summedMovement=$summedMovements[$i];
                //$output.="<tr style='font-size:13px;'>";
                $output.="<tr style='font-size:15px;'>";
                  $output.="<td>".$summedMovement['Product']['name']."</td>";
                  if ($summedMovement['StockMovement']['production_result_code_id']>0){
                    $output.="<td>".$summedMovement['Product']['name']." ".$summedMovement['ProductionResultCode']['code']."</td>"; //(".$summedMovement['StockItem']['raw_material_name'].")</td>";
                  }
                  else {
                    $output.="<td>".$summedMovement['Product']['name']."</td>";
                  }
                  $output.="<td class='centered'>".number_format($summedMovement[0]['total_product_quantity'],0,".",",")."</td>";
                  $output.="<td class='centered'>".number_format(round($summedMovement[0]['total_product_quantity']/($summedMovement['Product']['packaging_unit']?$summedMovement['Product']['packaging_unit']:1)),0,".",",")."</td>";
                  
                  $output.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price'],4,".",",")."</span></td>";
                  
                  $output.="<td class='centered'><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'],2,".",",")."</span></td>";
                  
                  $totalquantity+=$summedMovement[0]['total_product_quantity'];
                  $totalprice+=$summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'];
                $output.="</tr>";
              }
              else {
                if ($i==count($summedMovements)){
                  $output.="<tr>";
                    $output.="<td class='centered' colspan='6'>--------------------------------------------------------------------------- ÚLTIMA LÍNEA ---------------------------------------------------------------------------</td>";
                  $output.="</tr>";
                }
                else {

                   $output.="<tr>";
                    $output.="<td></td>";
                    $output.="<td>&nbsp;</td>";
                    $output.="<td>&nbsp;</td>";
                    $output.="<td>&nbsp;</td>";
                    $output.="<td>&nbsp;</td>";
                    $output.="<td>&nbsp;</td>";
                  $output.="</tr>";
                }
              }  
            }
            $output.="</tbody>";
          $output.="</table>";
          $output.="<table  style='height:57mm;'>";
            $output.="<tbody>";
              $output.="<tr>";
                $output.="<td style='width:50%;font-size:10.5px;'>";
                $output.="</td>";
                $output.="<td style='width:50%'>
                  <div style='width:60%;float:left;height:22.5mm;font-size:18px;line-height:1.6em;'>
                    <span class='right'>Sub Total ".$invoice['Currency']['abbreviation']."</span>
                    
                    
                    <span class='right'>IVA ".$invoice['Currency']['abbreviation']."</span>
                    <span class='right bold'>TOTAL ".$invoice['Currency']['abbreviation']."</span>
                    <span class='right'>Retención ".$invoice['Currency']['abbreviation']."</span>
                  </div>
                  <div style='width:40%;float:left;height:22.5mm;;font-size:20px;font-weight:700;'>  
                    <span class='amountright' style='width:100%;'>".number_format($totalprice,2,".",",")."</span>
                    
                    <span class='amountright' style='width:100%;'>".number_format($invoice['Invoice']['iva_price'],2,".",",")."</span>
                    <span class='amountright' style='width:100%;'>".number_format($invoice['Invoice']['total_price'],2,".",",")."</span>
                    <span class='amountright' style='width:100%;'>".number_format($invoice['Invoice']['retention_amount'],2,".",",")."</span>
                  </div>
                </td>";
              $output.="</tr>";  
            $output.="</tbody>";
          $output.="</table>";
          
          
          //$receiverName=trim($order['ThirdParty']['first_name']." ".$order['ThirdParty']['last_name']);
          $receiverName=trim($order['ThirdParty']['company_name']);
          if ($invoice['Invoice']['bool_credit']){
            $output.="<table  style='height:4mm;'>";
              $output.="<tbody>";
                $output.="<tr>";
                  $output.="<td style='width:100%;font-size:10.5px;padding-left:21.5mm'>";
                    $output.="<div>
                      <span class='left'>".$dueDate->format('d')." de ".monthOfYear($dueDate->format('m'))." ".$dueDate->format('Y')."</span>
                    </div>";
                  $output.="</td>";
                 $output.="</tr>";  
              $output.="</tbody>";
            $output.="</table>";    
            $output.="<table  style='height:1mm;'>";
              $output.="<tbody>";
                $output.="<tr>";
                  $output.="<td style='width:100%;font-size:10.5px;padding-left:9.5mm'>";
                    $output.="<div>
                      <span class='left'>".(empty($receiverName)?" ":$receiverName).".</span>
                    </div>";
                  $output.="</td>";
                 $output.="</tr>";  
              $output.="</tbody>";
            $output.="</table>";    
            $output.="<table  style='height:5mm;'>";
              $output.="<tbody>";
                $output.="<tr>";
                  $output.="<td style='width:100%;font-size:10.5px;padding-left:6.2mm'>";
                    $output.="<div>
                      <span class='left bold' style='font-size:1.25em'>".$invoice['Currency']['abbreviation']." ".number_format($invoice['Invoice']['total_price'],2,".",",")." (".$invoice['Currency']['full_name'].")</span>
                    </div>";
                  $output.="</td>";
                 $output.="</tr>";  
              $output.="</tbody>";
            $output.="</table>";    
            $output.="<table  style='height:4.6mm;'>";
              $output.="<tbody>";
                $output.="<tr>";
                  $output.="<td style='width:100%;font-size:10.5px;padding-left:52.2mm'>";
                    $output.="<div>
                      <span class='left'>".number_format($order['ThirdParty']['expiration_rate'],2,".",",")." %</span>
                    </div>";
                  $output.="</td>";
                 $output.="</tr>";  
              $output.="</tbody>";
            $output.="</table>";    
          }
          
          /*
          $output.="<table  style='height:22.5mm;'>";
            $output.="<tr>";
              $output.="<td style='width:68%;font-size:10.5px;'>";
               if ($invoice['Invoice']['bool_credit']){
                $output.="<div>
                  <span class='left'>Pagaré a la orden de GRUPO ORNA, S.A. en la ciudad de Managua por la cantidad de:</span>
                  <span class='left bold' style='font-size:1.25em'>".$invoice['Currency']['abbreviation']." ".number_format($invoice['Invoice']['total_price'],2,".",",")." (".$invoice['Currency']['full_name'].")</span>
                </div>
                <div>
                  <span style='text-align:justify'>Que les adeudamos por igual valor recibido a nuestra entera satisfacción. En caso de falta de pago en la fecha indicada incurriré en mora sin necesidad de requerimiento o intimidación judicial o extrajudicial y desde esa fecha hasta el pago total reconoceré y pagaré al acreedor intereses del 5% mensual sobre lo adeudado. Renuncio a mi domicilio, sujetándome al que elija mi acreedor.  Me obligo a cancelar dicha factura al tipo de cambio respecto al dólar de los estados Unidos de Norte América</span>
                </div>";
              }
              $output.="</td>";
              $output.="<td style='width:32%'>
                <div style='width:60%;float:left;height:22.5mm;font-size:15px;line-height:1.6em;'>
                  <span class='right'>Sub Total ".$invoice['Currency']['abbreviation']."</span>
                  
                  
                  <span class='right'>IVA ".$invoice['Currency']['abbreviation']."</span>
                  <span class='right bold'>TOTAL ".$invoice['Currency']['abbreviation']."</span>
                  <span class='right'>Retención ".$invoice['Currency']['abbreviation']."</span>
                </div>
                <div style='width:40%;float:left;height:22.5mm;;font-size:17px;font-weight:700;'>  
                  <span class='amountright' style='width:100%;'>".number_format($totalprice,2,".",",")."</span>
                  
                  <span class='amountright' style='width:100%;'>".number_format($invoice['Invoice']['iva_price'],2,".",",")."</span>
                  <span class='amountright' style='width:100%;'>".number_format($invoice['Invoice']['total_price'],2,".",",")."</span>
                  <span class='amountright' style='width:100%;'>".number_format($invoice['Invoice']['retention_amount'],2,".",",")."</span>
                </div>
              </td>";
            $output.="</tbody>";
          $output.="</table>";
          $output.="<table  style='height:15mm;font-size:0.9rem;'>";
            $output.="<tr>";
              $output.="<td class='centered' style='width:50mm;'>
                <div>
                  <span class='centered'>_______________________________</span>
                </div>
                <div>  
                  <span class='centered'>ENTREGUÉ CONFORME</span>
                </div>        
              </td>";
              $output.="<td class='centered' style='width:51mm;'>
                <div>
                  <span class='centered'>_______________________________</span>
                </div>
                <div>  
                  <span class='centered'>NOMBRE Y APELLIDO DE QUIEN RECIBE</span>
                </div>        
              </td>";
              $output.="<td class='centered' style='width:51mm;'>
                <div>
                  <span class='centered'>_______________________________</span>
                </div>
                <div>
                  <span class='left'>CÉDULA IDENTIDAD No</span>
                </div>        
              </td>";
              $output.="<td class='centered'style='width:50mm;'>
                <div>
                  <span class='centered'>_______________________________</span>
                </div>
                <div class='centered'>  
                  <span class='centered'>FIRMA</span>
                </div>        
              </td>";
           $output.="</tbody>";
          $output.="</table>";  
          */
        $output.='</div>';
      $output.='</div>';  
      $output.='<div class="col-sm-2">';  
        $output.='<div class="noprint" style="width:100mm;float:left;padding:0mm;">';  
          $output.=$this->Html->link('Detalle Factura',['controller'=>'Orders','action'=>'verVenta',$order['Order']['id']],['class'=>'btn btn-primary']);
        $output.="</div>";
      $output.='</div>';
    $output.='</div>';
  $output.='</div>';  
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>
</div>