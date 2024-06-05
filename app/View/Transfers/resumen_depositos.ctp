<div class="transfers index fullwidth">
<?php 
  echo "<h2>".__('Deposits')."</h2>";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";
			echo "<div class='col-md-10'>";
        echo $this->Form->create('Report');
        echo "<fieldset>";
          echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
          echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
        echo "</fieldset>";
        echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
        echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
        echo $this->Form->end(__('Refresh')); 
      echo "</div>";
      echo "<div class='col-md-2'>";
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul>";
          if ($bool_deposit_add_permission){
            echo "<li>".$this->Html->link(__('New Deposit'), array('action' => 'crearDeposito'))."</li>";
          }
        echo "</ul>";
      echo "</div>";
    echo "</div>";
  echo "</div>";  
	
	echo "<table cellpadding='0' cellspacing='0'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th>".$this->Paginator->sort('transfer_date')."</th>";
        echo "<th>".$this->Paginator->sort('bank_accounting_code_id')."</th>";
        echo "<th>".$this->Paginator->sort('bank_reference')."</th>";
        echo "<th>".$this->Paginator->sort('concept')."</th>";
				echo "<th>".$this->Paginator->sort('amount')."</th>";
        //echo "<th>Factura/Recibo</th>";
				echo "<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$totalCSAmount=0;
		$totalUSDAmount=0;
		foreach ($deposits as $deposit){
			echo "<tr>";
				$depositDateTime=new DateTime($deposit['Transfer']['transfer_date']);
				echo "<td>".$depositDateTime->format('d-m-Y')."</td>";
        echo "<td>".$this->Html->link($deposit['BankAccountingCode']['description'], ['controller' => 'accounting_codes', 'action' => 'view', $deposit['BankAccountingCode']['id']])."</td>";
				echo "<td>".h($deposit['Transfer']['bank_reference'])."</td>";
        echo "<td>".$this->Html->link($deposit['Transfer']['concept'],['action'=>'verDeposito',$deposit['Transfer']['id']])."</td>";
				echo "<td>".$deposit['Currency']['abbreviation']." <span class='amountright'>".number_format($deposit['Transfer']['amount'],2,".",",")."</span></td>";
				if ($deposit['Currency']['id']==CURRENCY_CS){
					$totalCSAmount+=$deposit['Transfer']['amount'];
				}
				elseif ($deposit['Currency']['id']==CURRENCY_USD){
					$totalUSDAmount+=$deposit['Transfer']['amount'];
				}
				//echo "<td>";
        //  echo $this->Html->link($deposit['AccountingRegister']['concept'], ['controller' => 'accounting_registers', 'action' => 'view', $deposit['AccountingRegister']['id']]);
        //echo "</td>";
				echo "<td class='actions'>";
					if ($bool_edit_permission) { 
						echo $this->Html->link(__('Edit'), ['action' => 'editarDeposito', $deposit['Transfer']['id']]); 
					}
					if ($bool_delete_permission) { 
						//echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $deposit['Transfer']['id']), array(), __('Are you sure you want to delete # %s?', $deposit['Transfer']['deposit_code']]); 
					}
				echo "</td>";
			echo "</tr>";
		}	
		if ($totalCSAmount>0){
			echo "<tr class='totalrow'>";
				echo "<td>Total</td>";
				echo "<td></td>";
        echo "<td></td>";
				echo "<td></td>";
				echo "<td>C$ <span class='amountright'>".number_format($totalCSAmount,2,".",",")."</span></td>";
				echo "<td></td>";
			echo "</tr>";
		}
		if ($totalUSDAmount>0){
			echo "<tr class='totalrow'>";
				echo "<td>Total</td>";
				echo "<td></td>";
        echo "<td></td>";
				echo "<td></td>";
				echo "<td>C$ <span class='amountright'>".number_format($totalUSDAmount,2,".",",")."</span></td>";
				echo "<td></td>";
			echo "</tr>";
		}
		echo "</tbody>";
	echo "</table>";
?>
</div>