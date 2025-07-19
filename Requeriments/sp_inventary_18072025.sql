 
-- DROP PROCEDURE `orna1114_ornasa`.`sp_inventary`; 

CREATE DEFINER=`root`@`localhost` PROCEDURE `orna1114_ornasa`.`sp_inventary`(in productType int,in pdate datetime,in warehouseid INT)
begin 
  select * from (
   select `Productos`.id idraw,`Productos`.name nameraw,`Productos`.abbreviation  abbreviationraw,`rsf`.* from ( 
  select   name,product_type_id,product_id id,packaging_unit, raw_material_id,
  sum(case when raw_material_id is null then total else (`Remaining_A`+`Remaining_B`+`Remaining_C`) end)Remaining,
  sum(`Remaining_A`)`Remaining_A`,
  sum(`Remaining_B`)`Remaining_B`,
  sum(`Remaining_C`)`Remaining_C`,
  sum(`Saldo`)`Saldo`,
  sum(`Saldo_A`)`Saldo_A`,
  sum(`Saldo_B`)`Saldo_B`,
  sum(`Saldo_C`)`Saldo_C`
  from ( 
 
  select `Producto`.name,`Producto`.packaging_unit,`Producto`.product_type_id,`StockItemLog`.product_id,`StockItem`.raw_material_id,
  sum(`StockItemLog`.product_quantity)total, 
  sum(round(`StockItemLog`.`product_quantity`*`StockItemLog`.`product_unit_price`,3)) Saldo,
  sum(case when `StockItem`.`production_result_code_id`=1 then 
				`StockItemLog`.`product_quantity` else 0 end
				) `Remaining_A`,
  sum(case when `StockItem`.`production_result_code_id`=1 then 
				round(`StockItemLog`.`product_quantity`*`StockItemLog`.`product_unit_price`,3)else 0 end
				) `Saldo_A`,				
				sum(case when `StockItem`.`production_result_code_id`=2 then 
				`StockItemLog`.`product_quantity` else 0 end
				)`Remaining_B`,
				 sum(case when `StockItem`.`production_result_code_id`=2 then 
				round(`StockItemLog`.`product_quantity`*`StockItemLog`.`product_unit_price`,3)else 0 end
				) `Saldo_B`,	
				sum(case when `StockItem`.`production_result_code_id`=3 then 
				`StockItemLog`.`product_quantity` else 0 end)
				`Remaining_C`,
				 sum(case when `StockItem`.`production_result_code_id`=3 then 
				round(`StockItemLog`.`product_quantity`*`StockItemLog`.`product_unit_price`,3)else 0 end
				) `Saldo_C`	
  
  from `orna1114_ornasa`.`stock_item_logs` AS `StockItemLog` 
  join `orna1114_ornasa`.`stock_items` AS `StockItem`  on `StockItem`.id=`StockItemLog`.stockitem_id
  join ( SELECT `StockItemx`.id,max(`StockItemLogx`.id) idx FROM `orna1114_ornasa`.`stock_item_logs` AS `StockItemLogx` 
    join `orna1114_ornasa`.`stock_items` AS `StockItemx` on `StockItemx`.id=`StockItemLogx`.stockitem_id
    join `orna1114_ornasa`.`products` AS `Productox` on `Productox`.id=`StockItemLogx`.`product_id`
  WHERE  `StockItemLogx`.`stockitem_date` < DATE_ADD(pdate, INTERVAL 1 DAY)  
   and (case when ((`Productox`.product_type_id=11 or `Productox`.product_type_id=9 or `Productox`.product_type_id=18) 
   and `StockItemx`.`stockitem_depletion_date` > pdate) then true  
   when (`Productox`.product_type_id not in(9,11,12,17)) then true else false end)
   -- se omite tipo 9 y 11, ya fueron evaluados en el case y los tipos 12,17 se omiten por estar fijos
  group by `StockItemx`.id) sm on sm.idx=`StockItemLog`.id
  join `orna1114_ornasa`.`products` AS `Producto` on `Producto`.id=`StockItemLog`.`product_id`
  where `Producto`.product_type_id=productType
  and (case when productType<>11 AND `StockItemLog`.product_quantity<>0 then true else case when productType=11 then true else false end end)
  and `StockItem`.`warehouse_id` = warehouseid
  group by `StockItemLog`.product_id,`StockItem`.raw_material_id
 )sd 
  -- group by  name,product_type_id,product_id,packaging_unit, raw_material_id) `rsf`
  group by  product_id, raw_material_id) `rsf`
  left join `orna1114_ornasa`.`products` AS `Productos` on `Productos`.id=`rsf`.`raw_material_id`
  -- where Remaining<>0 or Remaining_A<>0 or Remaining_B<>0 or Remaining_C<>0

 ) as `0`
     order by id asc,name asc,idraw desc,nameraw desc;
  
end