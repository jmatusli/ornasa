CREATE DEFINER=`root`@`localhost` PROCEDURE `orna1114_ornasa`.`sp_saldo`(in productId int,in pwarehouseId int,in pdate datetime,in prawMaterialId int,in returnv int)
begin

if returnv=0 then
 DROP TEMPORARY TABLE IF EXISTS temp_resultados;
 CREATE TEMPORARY TABLE temp_resultados (
    nameraw text,
    abbreviationraw text,
    name text,
    product_type_id int,
    id int,
    packaging_unit int,
    raw_material_id int,
    Remaining float,
    Remaining_A float,
    Remaining_B float,
    Remaining_C float,
    Saldo float,
    Saldo_A float,
    Saldo_B float,
    Saldo_C float
  );
end if;  


 if returnv=0 then
 INSERT INTO temp_resultados (
    nameraw,
    abbreviationraw,
    name,
    product_type_id,
    id,
    packaging_unit,
    raw_material_id,
    Remaining,
    Remaining_A,
    Remaining_B,
    Remaining_C,
    Saldo,
    Saldo_A,
    Saldo_B,
    Saldo_C
  )
  select nameraw,
    abbreviationraw,name, product_type_id,id, packaging_unit,raw_material_id,Remaining,Remaining_A, 
	Remaining_B,Remaining_C,Saldo, Saldo_A, Saldo_B,Saldo_C from (
   select `Productos`.name nameraw,`Productos`.abbreviation  abbreviationraw,`rsf`.* from ( 
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
  where `Producto`.id=productId
  AND `StockItemLog`.product_quantity<>0
  and `StockItem`.`warehouse_id` = pwarehouseId
  group by `StockItemLog`.product_id,`StockItem`.raw_material_id
 )sd 
  group by  name,product_type_id,product_id,packaging_unit, raw_material_id) `rsf`
  left join `orna1114_ornasa`.`products` AS `Productos` on `Productos`.id=`rsf`.`raw_material_id`
  -- where Remaining<>0 or Remaining_A<>0 or Remaining_B<>0 or Remaining_C<>0
 )as `0`;
 
 else 
  select * from (
   select `Productos`.name nameraw,`Productos`.abbreviation  abbreviationraw,`rsf`.* from ( 
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
  where `Producto`.id=productId
  and (case when prawMaterialId>0 then case when `StockItem`.raw_material_id=prawMaterialId then true else false end else true end)
  AND `StockItemLog`.product_quantity<>0
  and `StockItem`.`warehouse_id` = pwarehouseId
  group by `StockItemLog`.product_id,`StockItem`.raw_material_id
 )sd 
  group by  name,product_type_id,product_id,packaging_unit, raw_material_id) `rsf`
  left join `orna1114_ornasa`.`products` AS `Productos` on `Productos`.id=`rsf`.`raw_material_id`
  -- where Remaining<>0 or Remaining_A<>0 or Remaining_B<>0 or Remaining_C<>0
 ) as `0`;
 
 
  end if;
  
end