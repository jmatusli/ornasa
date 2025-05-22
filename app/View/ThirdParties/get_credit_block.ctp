<?php 
   echo '<h4>Estado de Crédito del Cliente</h4>';
   //echo '<p>Bool credit applied is '.$boolCreditApplied.'</p>';
  $boolCreditExceeded=false;
  $creditWarning='';
  if ($clientCreditStatus['ThirdParty']['credit_saldo'] < 0){
    $boolCreditExceeded=true;
    $creditWarning= "El cliente ".$clientCreditStatus['ThirdParty']['company_name']." tiene un límite de crédito de ".$clientCreditStatus['ThirdParty']['credit_amount'].", pero un pago pendiente de ".$clientCreditStatus['ThirdParty']['pending_payment']." entonces solamente se pueden emitir facturas de contado.  ";  
  }
  $boolSavingAllowed=true;
  $creditCheckResult='';      
  if ($clientCreditDays == 0){
    $creditCheckResult="Este cliente no tiene ni una plaza ni un límite de crédito, entonces solamente se pueden emitir facturas de contado.  ";
    if ($userRoleId!= ROLE_ADMIN  && $boolCreditApplied){
      $boolCreditApplied=false;
      $creditWarning= "El cliente ".$clientCreditStatus['ThirdParty']['company_name']." no tiene ni una plaza ni un límite de crédito, entonces solamente se pueden emitir facturas de contado. ";  
    }
  }
  if ($boolCreditExceeded && $boolCreditApplied && !$boolCreditAuthorized && $canApplyCredit!=1){
    $boolSavingAllowed=false;    
    $creditCheckResult="Factura de crédito no permitido.";
  }
  
  if (!$boolCreditAuthorized){
    echo '<p class="notallowed" id="creditWarning">'.$creditWarning.'</p>';
  }
 
  if ($userRoleId == ROLE_ADMIN || $canApplyCredit==1 ){
    echo $this->Form->input('set_save_allowed',['id'=>'SetSaveAllowed','type'=>'checkbox','label'=>'Guardar Venta','checked'=>$boolSavingAllowed]);
  }
  echo $this->Form->input('save_allowed',['id'=>'SaveAllowed','type'=>'hidden','label'=>'Guardar Venta','readonly'=>'readonly','value'=>($boolSavingAllowed?'1':'0')]);
  //pr($boolCreditAuthorized);
  //pr($creditAuthorizationUserId);
  echo $this->Form->input('credit_authorization_user_id',['label'=>false,'type'=>'hidden','id'=>'creditAuthorizationUserId','value'=>($boolCreditAuthorized?$creditAuthorizationUserId:0)]);
  //pr($creditAuthorizationUsers);
  echo $this->Form->input('credit_username',['label'=>'Crédito autorizado por','id'=>'CreditUsername','value'=>($creditAuthorizationUserId > 0?($creditAuthorizationUsers[$creditAuthorizationUserId]):'Crédito de cliente'),'readonly'=>true,'div'=>['class'=>($boolCreditApplied?'':'d-none')]]);
  
  echo $this->Form->input('retention_allowed',['id'=>'RetentionAllowed','type'=>'hidden','readonly'=>'readonly','value'=>1]);
  //echo $this->Form->input('bool_credit',['type'=>'checkbox','id'=>'BoolCredit','label'=>'Crédito','checked'=>($userRoleId == ROLE_ADMIN? $boolCreditApplied:!$boolCreditExceeded && $boolCreditApplied)]);
  echo $this->Form->input('bool_credit',['type'=>'checkbox','id'=>'BoolCredit','label'=>'Crédito','checked'=>$boolCreditApplied]);
  if ($userRoleId== ROLE_ADMIN /* || $canApplyCredit==1 */){
    echo $this->Form->input('Client.credit_days',['label'=>'Días de Crédito','value'=>$clientCreditDays]);
  } 
  else {
    echo $this->Form->input('Client.credit_days',['label'=>'Días de Crédito','value'=>$clientCreditDays,'readonly'=>true]);
  }      
  echo $this->Form->input('Client.credit_saldo',['type'=>'hidden','value'=>$clientCreditStatus['ThirdParty']['credit_saldo']]);
  echo  '<dl class="narrow">';
    echo '<dt>'.__('Límite Crédito').'</dt>';
    echo '<dd id="ClientCreditLimit" class="CScurrency"><span class="currency">C$</span><span class="amountright">'.$clientCreditStatus['ThirdParty']['credit_amount'].'</span></dd>';
    echo '<dt>'.__('Pago Pendiente').'</dt>';
    echo '<dd id="ClientCreditPending" class="CScurrency"><span class="currency">C$</span><span class="amountright">'.$clientCreditStatus['ThirdParty']['pending_payment'].'</span></dd>';
  echo  '</dl>';
  
  if ($clientCreditStatus['ThirdParty']['credit_amount'] > 0 && !empty($clientCreditStatus['ThirdParty']['pending_invoices'])){
    $tableHead='';      
    $tableHead.='<thead>';
      $tableHead.='<tr>';
        $tableHead.='<th>Fecha</th>';
        $tableHead.='<th class="centered">#</th>';
        $tableHead.='<th>Monto</th>';
        $tableHead.='<th>Dias</th>';
      $tableHead.='</tr>';
    $tableHead.='</thead>';
    
    $tableBodyRows='';
    $totalPending=0;
    foreach ($clientCreditStatus['ThirdParty']['pending_invoices'] as $pendingInvoice){
      $invoiceDateTime = new DateTime($pendingInvoice['Invoice']['invoice_date']);
	   $noww = new DateTime();
	   $now = time();
	  $current_date=$noww->format('Y-m-d');
	  $invoice_date=strtotime($invoiceDateTime->format('Y-m-d'));
	  $datediff= round(($now -$invoice_date) / (60 * 60 * 24))-1;
	  
	   
      $totalPending+=$pendingInvoice['Invoice']['total_price'];
      
      $tableRow='';
      $tableRow.='<tr>';
        $tableRow.='<td>'.$invoiceDateTime->format('d-m-Y').'</td>';
        $tableRow.='<td>'.$pendingInvoice['Invoice']['invoice_code'].'</td>';
        $tableRow.='<td class="centered"><span class="currency">'.$pendingInvoice['Currency']['abbreviation'].'</span><span class="amountright">'.$pendingInvoice['Invoice']['total_price'].'</span></td>';
        $tableRow.='<td>'.$datediff.'</td>';     
	 $tableRow.='</tr>';
      
      $tableBodyRows.=$tableRow;
    }
    
    $totalRow='';
    $totalRow.='<tr class="totalrow">';
      $totalRow.='<td>Total</td>';
      $totalRow.='<td></td>';
      $totalRow.='<td class="centered"><span class="currency"></span><span class="amountright">'.$totalPending.'</span></td>';
      $totalRow.='<td class="centered"><span class="currency"></td>';
    $totalRow.='</tr>';
      
    $tableBody='<tbody>'.$totalRow.$tableBodyRows.$totalRow.'</tbody>';
    $pendingPaymentsTable='<table id="pagosPendientesCliente">'.$tableHead.$tableBody.'</table>';
    echo $pendingPaymentsTable;
  }
     