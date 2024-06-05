<?php 
	class InventoryCountDisplayHelper extends AppHelper {
		var $helpers = array('Html'); // include the html helper
		
		//function showInventoryTotals($stockItems,$productCategoryId,$headertitle,$productionResultCodeArray=[],$extraStyling=''){
    function showInventoryTotals($stockItems,$productCategoryId,$plantId,$params=[]){  
			$divClass="";
			switch($productCategoryId){
				case CATEGORY_PRODUCED:
					$divClass="produced";
					break;
				case CATEGORY_OTHER:
					$divClass="other";
					break;
				case CATEGORY_RAW:
					$divClass="raw";
					break;
        default:
          $divClass="productcategory";
			}
      $divId="inventoryTotals.".$plantId.".".$productCategoryId;
      
      
			$inventoryTotalDiv='<div 
        id="'.$divId.'" class="'.$divClass.'" '.(empty($params['style'])?"":(' style="'.$params['style'].'"')).'>';
			
      //$inventoryTotalDiv.='<p>plantId is '.$plantId.'</p>';
      if (empty($stockItems)){
         $inventoryTotalDiv.="<h3>No hay productos para ".(empty($params['header_title'])?"Productos":$params['header_title'])."</h3>"; 
      }
      else {
        $inventoryTotalDiv.="<h3>".(empty($params['header_title'])?"Productos":$params['header_title'])."</h3>";
        $inventoryTotalDiv.="<table>";
        $inventoryTotalDiv.="<thead>";
        $inventoryTotalDiv.="<tr>";
        if ($productCategoryId==CATEGORY_PRODUCED && $plantId == PLANT_SANDINO){
            $inventoryTotalDiv.="<th>".__('Raw Material')."</th>";
        }
        $inventoryTotalDiv.="<th class='hidden'>Product Id</th>";
        $inventoryTotalDiv.="<th>".__('Product')."</th>";
        
        if ($productCategoryId==CATEGORY_PRODUCED && $plantId == PLANT_SANDINO){
          // should show the results for each production result code as a column
          if (empty($params['production_result_codes']) || in_array(PRODUCTION_RESULT_CODE_A,$params['production_result_codes'])){
            $inventoryTotalDiv.="<th class='centered'>A</th>";
          }
          if (empty($params['production_result_codes']) || in_array(PRODUCTION_RESULT_CODE_B,$params['production_result_codes'])){
            $inventoryTotalDiv.="<th class='centered'>B</th>";
          }  
          if (empty($params['production_result_codes']) || in_array(PRODUCTION_RESULT_CODE_C,$params['production_result_codes'])){
            $inventoryTotalDiv.="<th class='centered'>C</th>";
          }
          //$inventoryTotalDiv.="<th>".__('Total Quantity')."</th>";
        }
        else {
          $inventoryTotalDiv.="<th class='centered'>".__('Inventory Total')."</th>";
        }
        $inventoryTotalDiv.="</tr>";
        $inventoryTotalDiv.="</thead>";
        $inventoryTotalDiv.="<tbody>";
        foreach ($stockItems as $stockItem){
          $inventoryTotalDiv.="<tr>"; 
          if ($productCategoryId == CATEGORY_PRODUCED && $plantId == PLANT_SANDINO){
            if (!empty($stockItem['0']['Remaining'])){
              //pr($stockItem['0']['Remaining']);
              // only print out lines that have remaining quantities
              $inventoryTotalDiv.="<td>".$stockItem['RawMaterial']['abbreviation']."</td>";
              $inventoryTotalDiv.="<td class='hidden'>"; 
              $inventoryTotalDiv.=$stockItem['Product']['id'];
              $inventoryTotalDiv.="</td>";
              $inventoryTotalDiv.="<td>"; 
              $inventoryTotalDiv.=$stockItem['Product']['name'];
              $inventoryTotalDiv.="</td>";
              if (empty($params['production_result_codes']) || in_array(PRODUCTION_RESULT_CODE_A,$params['production_result_codes'])){
                $inventoryTotalDiv.="<td class='centered'>"; 
                $inventoryTotalDiv.=number_format($stockItem['0']['Remaining_A'],0,".",",");
                $inventoryTotalDiv.="</td>";
              }
              if (empty($params['production_result_codes']) || in_array(PRODUCTION_RESULT_CODE_B,$params['production_result_codes'])){
                $inventoryTotalDiv.="<td class='centered'>"; 
                $inventoryTotalDiv.=number_format($stockItem['0']['Remaining_B'],0,".",",");
                $inventoryTotalDiv.="</td>";
              }
              if (empty($params['production_result_codes']) || in_array(PRODUCTION_RESULT_CODE_C,$params['production_result_codes'])){
                $inventoryTotalDiv.="<td class='centered'>"; 
                  $inventoryTotalDiv.=number_format($stockItem['0']['Remaining_C'],0,".",",");
                $inventoryTotalDiv.="</td>";
              }
            }
          }
          else {
            if ($stockItem[0]['inventory_total']>0){
              $inventoryTotalDiv.="<td class='hidden'>"; 
              $inventoryTotalDiv.=$stockItem['Product']['id'];
              $inventoryTotalDiv.="</td>";
              $inventoryTotalDiv.="<td>"; 
              $inventoryTotalDiv.=$stockItem['Product']['name'];
              $inventoryTotalDiv.="</td>";
              $inventoryTotalDiv.="<td class='centered'>"; 
              $inventoryTotalDiv.=number_format($stockItem[0]['inventory_total'],0,".",",");
              $inventoryTotalDiv.="</td>";  
            }
          }
          $inventoryTotalDiv.="</tr>";
        }
        $inventoryTotalDiv.="</tbody>";
        $inventoryTotalDiv.="</table>";	
      }
			$inventoryTotalDiv.="</div>";	
			return $inventoryTotalDiv;
		}
	}
?>