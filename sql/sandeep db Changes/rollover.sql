
ALTER TABLE `dbsolis`.`product_service_claim`
ADD COLUMN `rollover_type` VARCHAR(45) NOT NULL DEFAULT 0 AFTER `ex_sunday`;

ALTER TABLE `dbsolis`.`product_service_claim`
ADD COLUMN `rollover_value` VARCHAR(45) NOT NULL DEFAULT 0 AFTER `ex_sunday`;



ALTER TABLE `dbsolis`.`product_service_claim`
ADD COLUMN `rollover_adult_value` VARCHAR(45) NOT NULL DEFAULT 0 AFTER `ex_sunday`;

ALTER TABLE `dbsolis`.`product_service_claim`
ADD COLUMN `rollover_child_value` VARCHAR(45) NOT NULL DEFAULT 0 AFTER `ex_sunday`;

ALTER TABLE `dbsolis`.`product_service_claim`
ADD COLUMN `rollover_teen_value` VARCHAR(45) NOT NULL DEFAULT 0 AFTER `ex_sunday`;

ALTER TABLE `dbsolis`.`product_service_claim`
ADD COLUMN `rollover_infant_value` VARCHAR(45) NOT NULL DEFAULT 0 AFTER `ex_sunday`;