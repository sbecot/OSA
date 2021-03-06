/**--------------------------------------------------------
* Module Name : RunTimeAppliance
* Version : 1.0.0
*
* Software Name : OpenSourceAppliance
* Version : 2.0
*
* Copyright (c) 2012 – 2013 France Télécom
* This software is distributed under the Apache 2 license
* <http://www.apache.org/licenses/LICENSE-2.0.html>
*
*--------------------------------------------------------
* File Name   : ApplianceManager/RunTimeAppliance/sql/1.0-to-2.0.sql
*
* Created     : 2012-10-01
* Authors     : Benoit HERARD <benoit.herard(at)orange.com>
*
* Description :
* 	Upgrade database form version 1.0 to version 2.0
*--------------------------------------------------------
* History     :
*
* 1.0.0 - 2013-12-03 : Release of the file
**/
CREATE  TABLE `authtoken` (
`token` VARCHAR(255) NOT NULL ,
`validUntil` DATETIME NULL ,
`userName` VARCHAR(45) NULL ,
PRIMARY KEY (`token`) ,
INDEX `fk_authtoken_1` (`userName` ASC) ,
INDEX `idx_date` (`validUntil` ASC) ,
CONSTRAINT `fk_authtoken_1`
FOREIGN KEY (`userName` )
REFERENCES `users` (`userName` )
ON DELETE CASCADE
ON UPDATE NO ACTION) ENGINE = MEMORY;


CREATE TABLE `nodes` (
`nodeName` varchar(45) NOT NULL,
`nodeDescription` varchar(2000) DEFAULT NULL,
`isHTTPS` tinyint(1) NOT NULL,
`isBasicAuthEnabled` tinyint(1) NOT NULL,
`isCookieAuthEnabled` tinyint(1) NOT NULL,
`serverFQDN` varchar(255) NOT NULL,
`localIP` varchar(45) NOT NULL,
`port` int(11) NOT NULL,
`privateKey` text,
`cert` text,
`additionalConfiguration` text,
PRIMARY KEY (`nodeName`),
UNIQUE KEY `UNQ_BIND` (`localIP`,`port`, `serverFQDN`)
)  ENGINE=InnoDB;





ALTER TABLE `services`
ADD COLUMN `onAllNodes` TINYINT(1) NOT NULL DEFAULT 1 ,
CHANGE COLUMN `isHitLoggingEnabled` `isHitLoggingEnabled` TINYINT(1) NULL DEFAULT 0  ;


ALTER TABLE `services`
DROP INDEX `Ind_unq_fontend` ;

CREATE  TABLE `servicesnodes` (
`serviceName` VARCHAR(45) NOT NULL ,
`nodeName` VARCHAR(45) NOT NULL ,
PRIMARY KEY (`serviceName`, `nodeName`) ,
INDEX `fk_servicesnodes_1` (`serviceName` ASC) ,
INDEX `fk_servicesnodes_2` (`nodeName` ASC) ,
CONSTRAINT `fk_servicesnodes_1`
FOREIGN KEY (`serviceName` )
REFERENCES `services` (`serviceName` )
ON DELETE CASCADE
ON UPDATE NO ACTION,
CONSTRAINT `fk_servicesnodes_2`
FOREIGN KEY (`nodeName` )
REFERENCES `nodes` (`nodeName` )
ON DELETE CASCADE
ON UPDATE NO ACTION);




ALTER TABLE `usersquotas` DROP FOREIGN KEY `FK_user_quotas_resource` , DROP FOREIGN KEY `FK_user_quotas_user` ;
ALTER TABLE `usersquotas`
ADD CONSTRAINT `FK_user_quotas_resource`
FOREIGN KEY (`serviceName` )
REFERENCES `services` (`serviceName` )
ON DELETE CASCADE
ON UPDATE RESTRICT,
ADD CONSTRAINT `FK_user_quotas_user`
FOREIGN KEY (`userName` )
REFERENCES `users` (`userName` )
ON DELETE CASCADE
ON UPDATE RESTRICT;












/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` (`groupName`,`description`) select 'valid-user','*** Any valid user ***' from dual where not exists (select 'x' from groups where groupName='valid-user');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;


/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` (`serviceName`,`reqSec`,`reqDay`,`reqMonth`,`frontEndEndPoint`,`isGlobalQuotasEnabled`,`isUserQuotasEnabled`,`groupName`,`backEndEndPoint`,`backEndUsername`,`backEndPassword`,`isIdentityForwardingEnabled`,`isPublished`,`isHitLoggingEnabled`,`isUserAuthenticationEnabled`) VALUES
('ApplianceManagerAdminAuthToken',0,0,0,'/ApplianceManagerAdmin/auth/token',0,0,'valid-user','http://127.0.0.1:PRIVATE_VHOST_PORT/ApplianceManager/auth/token','','002',1,1,0,1);
INSERT INTO `services` (`serviceName`,`reqSec`,`reqDay`,`reqMonth`,`frontEndEndPoint`,`isGlobalQuotasEnabled`,`isUserQuotasEnabled`,`groupName`,`backEndEndPoint`,`backEndUsername`,`backEndPassword`,`isIdentityForwardingEnabled`,`isPublished`,`isHitLoggingEnabled`,`isUserAuthenticationEnabled`) VALUES
('ApplianceManagerAdminAuth',0,0,0,'/ApplianceManagerAdmin/auth/',0,0,NULL,'http://127.0.0.1:PRIVATE_VHOST_PORT/ApplianceManager/auth/','','002',1,1,0,0);
UPDATE services s1,
(SELECT s2.backEndUsername FROM services s2 where s2.serviceName='ApplianceManagerAdmin') user,
(SELECT s2.backEndPassword FROM services s2 where s2.serviceName='ApplianceManagerAdmin') pass
SET s1.backEndUsername=user.backEndUsername,
s1.backEndPassword=pass.backEndPassword
WHERE s1.serviceName in ('ApplianceManagerAdminAuthToken','ApplianceManagerAdminAuth');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
