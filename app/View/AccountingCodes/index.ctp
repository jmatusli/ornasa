<div class="accountingCodes index">
<?php 
/*function RecursiveAccountingCodes($array) { 

    if (count($array)) { 
        echo "\n<ul>\n"; 
        foreach ($array as $vals) { 
			if ($vals['AccountingCode']['bool_active']){
				echo "<li id=\"".$vals['AccountingCode']['id']."\">".$vals['AccountingCode']['code']." ".$vals['AccountingCode']['description']; 
			}
            if (count($vals['children'])) { 
                RecursiveAccountingCodes($vals['children']); 
            } 
            echo "</li>\n"; 
        } 
        echo "</ul>\n"; 
    } 
} */
$this->IndexTree->RecursiveAccountingCodes($accountingCodes);

?>

</div>

<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Accounting Code'), array('action' => 'add'))."</li>";
		}
		if ($bool_accountingregister_index_permission){
			//echo "<li>".$this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index'))."</li>";
		}
		if ($bool_accountingregister_add_permission){
			//echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))."</li>";
		}
	echo "</ul>";
?>
</div>
