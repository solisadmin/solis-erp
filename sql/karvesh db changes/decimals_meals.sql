ALTER TABLE `dbsolis`.`tblservice_contract_mealsupplement` 
CHANGE COLUMN `adult_count` `adult_count` DECIMAL(10,2) NOT NULL DEFAULT '0' ;

ALTER TABLE `dbsolis`.`tblservice_contract_mealsupplement_childages` 
CHANGE COLUMN `child_count` `child_count` DECIMAL(10,2) NOT NULL DEFAULT '0' ;


ALTER TABLE `dbsolis`.`tblservice_contract_extrasupplement` 
CHANGE COLUMN `adult_count` `adult_count` DECIMAL(10,2) NOT NULL DEFAULT '0' ;

ALTER TABLE `dbsolis`.`tblservice_contract_extrasupplement_childages` 
CHANGE COLUMN `child_count` `child_count` DECIMAL(10,2) NOT NULL DEFAULT '0' ;


ALTER TABLE `dbsolis`.`tblspecial_offer_flatrate_mealsupp` 
CHANGE COLUMN `adult` `adult` DECIMAL(10,2) NULL DEFAULT NULL ;


ALTER TABLE `dbsolis`.`tblspecial_offer_flatrate_mealsupp_children_ages` 
CHANGE COLUMN `child_count` `child_count` DECIMAL(10,2) NOT NULL DEFAULT '0' ;
