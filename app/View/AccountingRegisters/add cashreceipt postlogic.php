<?php
		if ($this->request->is('post')) {
			//pr($this->request->data);
			
			if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
				$postedInvoiceData=$this->request->data['Invoice'];
			}
			
			$receipt_date=$this->request->data['CashReceipt']['receipt_date'];
			$cashReceiptDateAsString = $this->CashReceipt->deconstruct('receipt_date', $this->request->data['CashReceipt']['receipt_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
						
			$cashReceiptDateArray=array();
			$cashReceiptDateArray['year']=$receipt_date['year'];
			$cashReceiptDateArray['month']=$receipt_date['month'];
			$cashReceiptDateArray['day']=$receipt_date['day'];
					
			$receipt_code=$this->request->data['CashReceipt']['receipt_code'];
			$namedReceipts=$this->CashReceipt->find('all',array(
				'conditions'=>array(
					'receipt_code'=>$receipt_code,
				)
			));
			
			$currency_id=$this->request->data['CashReceipt']['currency_id'];
			
			if (count($namedReceipts)>0){
				$this->Session->setFlash(__('Ya existe un recibo de caja con el mismo código!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
			}
			else if ($cashReceiptDateAsString>date('Y-m-d 23:59:59')){
				$this->Session->setFlash(__('La fecha de recibo de caja no puede estar en el futuro!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
			}
			elseif ($cashReceiptDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			else if ($this->request->data['CashReceipt']['bool_annulled']){
				$datasource=$this->CashReceipt->getDataSource();
				try {
					//pr($this->request->data);
					$datasource->begin();

					$this->CashReceipt->create();
					$CashReceiptData=array();
					
					$CashReceiptData['CashReceipt']['receipt_code']=$this->request->data['CashReceipt']['receipt_code'];
					$CashReceiptData['CashReceipt']['receipt_date']=$this->request->data['CashReceipt']['receipt_date'];
					$CashReceiptData['CashReceipt']['bool_annulled']=true;
					$CashReceiptData['CashReceipt']['client_id']=$this->request->data['CashReceipt']['client_id'];
					$CashReceiptData['CashReceipt']['concept']=$this->request->data['CashReceipt']['concept'];
					$CashReceiptData['CashReceipt']['observation']=$this->request->data['CashReceipt']['observation'];
					$CashReceiptData['CashReceipt']['cash_receipt_type_id']=$cash_receipt_type_id;
					$CashReceiptData['CashReceipt']['amount']=0;
					$CashReceiptData['CashReceipt']['currency_id']=CURRENCY_CS;
			
					if (!$this->CashReceipt->save($CashReceiptData)) {
						echo "Problema guardando el recibo de caja";
						pr($this->validateErrors($this->CashReceipt));
						throw new Exception();
					}
					
					$datasource->commit();
						
					// SAVE THE USERLOG 
					$this->recordUserActivity($this->Session->read('User.username'),"Se registró el recibo de caja con número ".$this->request->data['CashReceipt']['receipt_code']);
					$this->Session->setFlash(__('Se guardó el recibo de caja.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
					return $this->redirect(array('action' => 'index'));
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar la remisión.  Por favor vuelva a intentar.'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
				}
			}					
			else if ($this->request->data['CashReceipt']['cashbox_accounting_code_id']==0){
				$this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en un recibo de caja!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
			}
			else if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER&&$this->request->data['CashReceipt']['credit_accounting_code_id']==0){
				$this->Session->setFlash(__('Se debe seleccionar la cuenta contable del HABER!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
			}
			else if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT&&$this->request->data['CashReceipt']['bool_retention']&&strlen($this->request->data['CashReceipt']['retention_number'])==0){
				$this->Session->setFlash(__('Se debe indicar el número de retención si se aplican retenciones!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
			}
			else {
					$datasource=$this->CashReceipt->getDataSource();
					try {
						$datasource->begin();
						if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
							$this->request->data['CashReceipt']['amount']=$this->request->data['CashReceipt']['amount_total_payment'];
						}
						$this->CashReceipt->create();
						if (!$this->CashReceipt->save($this->request->data)) {
							echo "Problema guardando el recibo de caja";
							pr($this->validateErrors($this->CashReceipt));
							throw new Exception();
						}
						$cash_receipt_id=$this->CashReceipt->id;
						
						// now prepare the accounting registers
						// debit=caja selected by client
						// for otros ingresos, credit = code selected by client, 
						// for credit invoices, credit = facturas por cobrar 
						// amount = total
						
						if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
							$total_cuentas_por_cobrar=$this->request->data['CashReceipt']['amount_cuentas_por_cobrar'];
							$total_increment=$this->request->data['CashReceipt']['amount_increment'];
							$total_discount=$this->request->data['CashReceipt']['amount_discount'];
							$total_erdiff=$this->request->data['CashReceipt']['amount_difference_exchange_rate'];
							$total_retention=$this->request->data['CashReceipt']['amount_retention_paid'];
							$total_cash_receipt=$this->request->data['CashReceipt']['amount_total_payment'];
						}
						else {
							$total_cash_receipt=$this->request->data['CashReceipt']['amount'];
							$total_retention=0;
						}
						
						if ($currency_id==CURRENCY_USD){
							$this->loadModel('ExchangeRate');
							$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptDateAsString);
							$total_CS=round($total_cash_receipt*$applicableExchangeRate['ExchangeRate']['rate'],2);
							$total_retention_CS=round($total_retention*$applicableExchangeRate['ExchangeRate']['rate'],2);
						}
						else {
							$total_CS=$total_cash_receipt;
							$total_retention_CS=$total_retention;
						}
						
						$accountingRegisterData['AccountingRegister']['register_date']=$cashReceiptDateArray;
						
						$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
						$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
						$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
						if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
							//$accountingRegisterData['AccountingRegister']['amount']=$total_CS+$total_increment+$total_erdiff-$total_discount;
							$accountingRegisterData['AccountingRegister']['amount']=$total_CS+$total_retention_CS+$total_discount;
						}
						else {
							$accountingRegisterData['AccountingRegister']['amount']=$total_CS;
						}
						$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
						$accountingRegisterData['AccountingRegister']['concept']="Recibo de Caja ".$receipt_code;
						$accountingRegisterData['AccountingRegister']['observation']="Recibo de Caja ".$receipt_code;
						
						// THIS GOES TO THE CASHBOX
						$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['CashReceipt']['cashbox_accounting_code_id'];
						//$accountingCode=$this->AccountingCode->read(null,$this->request->data['CashReceipt']['cashbox_accounting_code_id']);
						$accountingRegisterData['AccountingMovement'][0]['concept']="Recibo de Caja ".$receipt_code;
						$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
						$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$total_CS;
						
						//pr($accountingRegisterData);
						if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
							$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=$this->request->data['CashReceipt']['credit_accounting_code_id'];
							//$accountingCode=$this->AccountingCode->read(null,$this->request->data['CashReceipt']['credit_accounting_code_id']);
							$accountingRegisterData['AccountingMovement'][1]['concept']="Recibo de Caja ".$receipt_code;
							$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
							$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$total_CS;
						}
						if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
							$client_id=$this->request->data['CashReceipt']['client_id'];
							$this->loadModel('ThirdParty');
							$thisClient=$this->ThirdParty->read(null,$client_id);
							
							if (empty($thisClient['ThirdParty']['accounting_code_id'])){
								$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
								//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES);
							}
							else {
								$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
								//$accountingCode=$this->AccountingCode->read(null,$thisClient['ThirdParty']['accounting_code_id']);
							}
							$accountingRegisterData['AccountingMovement'][1]['concept']="Recibo de Caja ".$receipt_code;
							$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
							$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$total_cuentas_por_cobrar;
							if ($total_increment>0){
								$accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_OTROS_INGRESOS;
								//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_CUENTAS_OTROS INGRESOS);
								$accountingRegisterData['AccountingMovement'][2]['concept']="Recibo de Caja ".$receipt_code;
								$accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
								$accountingRegisterData['AccountingMovement'][2]['credit_amount']=$total_increment;
							}
							if ($total_discount>0){
								$accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_DESCUENTO_SOBRE_VENTA;
								//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_DESCUENTO_SOBRE_VENTA);
								$accountingRegisterData['AccountingMovement'][3]['concept']="Recibo de Caja ".$receipt_code;
								$accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
								$accountingRegisterData['AccountingMovement'][3]['debit_amount']=$total_discount;
							}
							if ($total_erdiff>0){
								$accountingRegisterData['AccountingMovement'][4]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_DIFERENCIA_CAMBIARIA;
								//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_DIFERENCIA_CAMBIARIA);
								$accountingRegisterData['AccountingMovement'][4]['concept']="Recibo de Caja ".$receipt_code;
								$accountingRegisterData['AccountingMovement'][4]['currency_id']=CURRENCY_CS;
								$accountingRegisterData['AccountingMovement'][4]['credit_amount']=$total_erdiff;
							}
							if (!$this->request->data['CashReceipt']['bool_retention']){
								$accountingRegisterData['AccountingMovement'][5]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
								//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_RETENCIONES_POR_COBRAR);
								$accountingRegisterData['AccountingMovement'][5]['concept']="Retención Recibo de Caja ".$receipt_code;
								$accountingRegisterData['AccountingMovement'][5]['currency_id']=CURRENCY_CS;
								$accountingRegisterData['AccountingMovement'][5]['debit_amount']=$total_retention_CS;
							}
						}
						
						//pr($accountingRegisterData);
						$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
						//echo "accounting register saved for otros ingresos<br/>";
				
						$AccountingRegisterCashReceiptData=array();
						$AccountingRegisterCashReceiptData['accounting_register_id']=$accounting_register_id;
						$AccountingRegisterCashReceiptData['cash_receipt_id']=$cash_receipt_id;
						$this->AccountingRegisterCashReceipt->create();
						if (!$this->AccountingRegisterCashReceipt->save($AccountingRegisterCashReceiptData)) {
							pr($this->validateErrors($this->AccountingRegisterCashReceipt));
							echo "problema al guardar el lazo entre asiento contable y recibo de caja";
							throw new Exception();
						}
						//echo "link accounting register cash receipt saved<br/>";			
				
						if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
							foreach ($this->request->data['Invoice'] as $invoice){
								$this->Invoice->id=$invoice['invoice_id'];
								$existingInvoice=array();
								$existingInvoice=$this->Invoice->find('first',array(
									'conditions'=>array(
										'Invoice.id'=>$invoice['invoice_id'],
									),
								));
								//pr($existingInvoice);
								
								$invoice_total_price_invoice_currency=$existingInvoice['Invoice']['total_price'];
								$invoice_currency_id=$existingInvoice['Invoice']['currency_id'];
								
								if ($invoice_currency_id==CURRENCY_CS){
									$invoice_total_price_CS=$invoice_total_price_invoice_currency;
								}
								elseif ($invoice_currency_id==CURRENCY_CS){
									$invoiceDate=$existingInvoice['Invoice']['invoice_date'];
									$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($invoiceDate);
									$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
									$invoice_total_price_CS=$invoice_total_price_invoice_currency*$exchangeRateInvoiceDate;
								}
								else {
									$invoice_total_price_CS=$invoice_total_price_invoice_currency;
								}
								
								$invoice_paid_already_CS=$this->Invoice->getAmountPaidAlreadyCS($existingInvoice['Invoice']['id']);
								$invoice_pending_CS=$invoice_total_price_CS-$invoice_paid_already_CS;
								$amount_paid_CS=$invoice['creditpayment'];
								if ($amount_paid_CS>=$invoice_pending_CS){
									$InvoiceData['bool_paid']=true;
								}
								else {
									$InvoiceData['bool_paid']=false;
								}
							
								if (!$this->Invoice->save($InvoiceData)) {
									pr($this->validateErrors($this->Invoice));
									echo "problema al guardar la factura";
									throw new Exception();
								}
								
								if ($invoice['payment']==NULL){
									$invoice['payment']=0;
								}
								if ($invoice['retentionpayment']==NULL){
									$invoice['retentionpayment']=0;
								}
								if (($invoice['payment']>0)||($invoice['retentionpayment']>0)){
									$CashReceiptInvoiceData=array();
									$CashReceiptInvoiceData['cash_receipt_id']=$cash_receipt_id;
									$CashReceiptInvoiceData['invoice_id']=$invoice['invoice_id'];
									$CashReceiptInvoiceData['amount']=$invoice['payment']-$invoice['increment']+$invoice['discount']-$invoice['exchangeratedifference'];
									$CashReceiptInvoiceData['increment']=$invoice['increment'];
									$CashReceiptInvoiceData['discount']=$invoice['discount'];
									$CashReceiptInvoiceData['erdiff']=$invoice['exchangeratedifference'];
									$CashReceiptInvoiceData['payment']=$invoice['payment'];
									$CashReceiptInvoiceData['payment_retention']=$invoice['retentionpayment'];
									$CashReceiptInvoiceData['payment_credit_CS']=$invoice['creditpayment'];
									$CashReceiptInvoiceData['payment_increment_CS']=$invoice['incpayment'];
									$CashReceiptInvoiceData['payment_discount_CS']=$invoice['descpayment'];
									$CashReceiptInvoiceData['payment_erdiff_CS']=$invoice['difpayment'];
									$CashReceiptInvoiceData['currency_id']=$currency_id;
									
									
									$this->CashReceiptInvoice->create();
									if (!$this->CashReceiptInvoice->save($CashReceiptInvoiceData)) {
										pr($this->validateErrors($this->CashReceiptInvoice));
										echo "problema al guardar el lazo entre el recibo de caja y la factura";
										throw new Exception();
									}
								}
							}
						}
						$datasource->commit();
						
						// SAVE THE USERLOG FOR THE REMISSION
						$this->recordUserActivity($this->Session->read('User.username'),"Recibo de caja registrado con número ".$this->request->data['CashReceipt']['receipt_code']);
						$this->Session->setFlash(__('Se guardó el recibo de caja.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
						return $this->redirect(array('action' => 'index'));
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('No se podía guardar el recibo de caja.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
					}
				//}
			}
		}