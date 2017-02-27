-- MySQL dump 10.13  Distrib 5.6.30, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: magixsurvey
-- ------------------------------------------------------
-- Server version	5.6.30-1
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `accomplished`
--

LOCK TABLES `accomplished` WRITE;
/*!40000 ALTER TABLE `accomplished` DISABLE KEYS */;
/*!40000 ALTER TABLE `accomplished` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `answers`
--

LOCK TABLES `answers` WRITE;
/*!40000 ALTER TABLE `answers` DISABLE KEYS */;
INSERT INTO `answers` VALUES (1,NULL,1,1),(2,NULL,1,1),(3,NULL,1,1),(4,NULL,3,2),(5,NULL,3,2),(6,NULL,3,2),(7,NULL,3,2),(8,NULL,3,2),(9,NULL,5,4),(10,NULL,5,4),(11,NULL,5,4),(12,NULL,5,4),(13,NULL,7,5),(14,NULL,7,5),(15,NULL,7,5),(16,NULL,7,5),(17,NULL,7,5),(18,NULL,11,1),(19,18,11,1),(20,18,11,1),(21,18,11,1),(22,18,11,1),(23,NULL,11,1),(24,NULL,13,4),(25,NULL,13,4),(26,NULL,13,4),(27,NULL,13,4);
/*!40000 ALTER TABLE `answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `atypes`
--

LOCK TABLES `atypes` WRITE;
/*!40000 ALTER TABLE `atypes` DISABLE KEYS */;
INSERT INTO `atypes` VALUES (1,'radio'),(2,'checkbox'),(3,'text'),(4,'option'),(5,'number');
/*!40000 ALTER TABLE `atypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `langtext`
--

LOCK TABLES `langtext` WRITE;
/*!40000 ALTER TABLE `langtext` DISABLE KEYS */;
INSERT INTO `langtext` VALUES (1,'de','Fragebogen'),(2,'en','Survey'),(3,'de','Wie sch&auml;tzen Sie Ihre PC-Kenntnisse ein?'),(4,'en','How do you value your PC knowledge?'),(5,'de','Anf&auml;nger'),(6,'en','Beginner'),(7,'de','Fortgeschrittener'),(8,'en','Experienced'),(9,'de','Profi'),(10,'en','Professional'),(11,'de','Worauf achten Sie beim Kauf von Software?'),(12,'en','What do you consider bying software?'),(13,'de','Ansehen'),(14,'en','Image'),(15,'de','Preis'),(16,'en','Price'),(17,'de','Fuktionalit&auml;t'),(18,'en','Functionality'),(19,'de','Testberichte'),(20,'en','Reviews'),(21,'de','Meinungen'),(22,'en','Opinions'),(23,'de','Wieviele Software-Titel kauften Sie in den letzten 12 Monaten?'),(24,'en','How many software articles did you buy in the last 12 months?'),(25,'de','keines'),(26,'en','none'),(27,'de','1-3'),(28,'en','1-3'),(29,'de','3-5'),(30,'en','3-5'),(31,'de','mehr als 5'),(32,'en','more than 5'),(33,'de','Erstellen Sie bitte Ihre pers&ouml;nliche TOP5 der Art der Software, die Sie nutzen.'),(34,'en','Please create your TOP5 of software types you use.'),(35,'de','Foto Produkte'),(36,'en','Foto products'),(37,'de','Video Produkte'),(38,'en','Video products'),(39,'de','Musik Produkte'),(40,'en','Music products'),(41,'de','Online Produkte'),(42,'en','Online products'),(43,'de','Spiele'),(44,'en','Games'),(45,'de','Verf&uuml;gen Sie &uuml;ber einen Internet-Anschluss?'),(46,'en','Do you have an internet connection?'),(47,'de','Ja'),(48,'en','Yes'),(49,'de','Nein'),(50,'en','No'),(51,'de','Modem'),(52,'en','Modem'),(53,'de','ISDN'),(54,'en','ISDN'),(55,'de','DSL'),(56,'en','DSL'),(57,'de','Sonstige'),(58,'en','Others'),(59,'de','Wie alt sind Sie?'),(60,'en','How old are you?'),(61,'de','< 18'),(62,'en','< 18'),(63,'de','18 - 25'),(64,'en','18 - 25'),(65,'de','26 - 40'),(66,'en','26 - 40'),(67,'de','> 40'),(68,'en','> 40'),(69,'de','Benutzer'),(70,'en','User'),(71,'de','Administrator'),(72,'de','Gast'),(73,'en','Guest'),(74,'en','Administrator');
/*!40000 ALTER TABLE `langtext` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `language`
--

LOCK TABLES `language` WRITE;
/*!40000 ALTER TABLE `language` DISABLE KEYS */;
INSERT INTO `language` VALUES ('de','Deutsch'),('en','English');
/*!40000 ALTER TABLE `language` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (1,1),(3,1),(5,1),(7,1),(11,1),(13,1);
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `r_langtext`
--

LOCK TABLES `r_langtext` WRITE;
/*!40000 ALTER TABLE `r_langtext` DISABLE KEYS */;
INSERT INTO `r_langtext` VALUES (1,1,NULL,NULL,NULL),(2,1,NULL,NULL,NULL),(3,NULL,1,NULL,NULL),(11,NULL,3,NULL,NULL),(23,NULL,5,NULL,NULL),(33,NULL,7,NULL,NULL),(45,NULL,11,NULL,NULL),(59,NULL,13,NULL,NULL),(4,NULL,1,NULL,NULL),(12,NULL,3,NULL,NULL),(24,NULL,5,NULL,NULL),(34,NULL,7,NULL,NULL),(46,NULL,11,NULL,NULL),(60,NULL,13,NULL,NULL),(5,NULL,NULL,1,NULL),(7,NULL,NULL,2,NULL),(9,NULL,NULL,3,NULL),(6,NULL,NULL,1,NULL),(8,NULL,NULL,2,NULL),(10,NULL,NULL,3,NULL),(13,NULL,NULL,4,NULL),(15,NULL,NULL,5,NULL),(17,NULL,NULL,6,NULL),(19,NULL,NULL,7,NULL),(21,NULL,NULL,8,NULL),(14,NULL,NULL,4,NULL),(16,NULL,NULL,5,NULL),(18,NULL,NULL,6,NULL),(20,NULL,NULL,7,NULL),(22,NULL,NULL,8,NULL),(25,NULL,NULL,9,NULL),(27,NULL,NULL,10,NULL),(29,NULL,NULL,11,NULL),(31,NULL,NULL,12,NULL),(26,NULL,NULL,9,NULL),(28,NULL,NULL,10,NULL),(30,NULL,NULL,11,NULL),(32,NULL,NULL,12,NULL),(35,NULL,NULL,13,NULL),(37,NULL,NULL,14,NULL),(39,NULL,NULL,15,NULL),(41,NULL,NULL,16,NULL),(43,NULL,NULL,17,NULL),(36,NULL,NULL,13,NULL),(38,NULL,NULL,14,NULL),(40,NULL,NULL,15,NULL),(42,NULL,NULL,16,NULL),(44,NULL,NULL,17,NULL),(47,NULL,NULL,18,NULL),(48,NULL,NULL,18,NULL),(51,NULL,NULL,19,NULL),(52,NULL,NULL,19,NULL),(53,NULL,NULL,20,NULL),(54,NULL,NULL,20,NULL),(55,NULL,NULL,21,NULL),(56,NULL,NULL,21,NULL),(57,NULL,NULL,22,NULL),(58,NULL,NULL,22,NULL),(49,NULL,NULL,23,NULL),(50,NULL,NULL,23,NULL),(61,NULL,NULL,24,NULL),(62,NULL,NULL,24,NULL),(63,NULL,NULL,25,NULL),(64,NULL,NULL,25,NULL),(65,NULL,NULL,26,NULL),(66,NULL,NULL,26,NULL),(67,NULL,NULL,26,NULL),(68,NULL,NULL,26,NULL),(69,NULL,NULL,NULL,1),(70,NULL,NULL,NULL,1),(71,NULL,NULL,NULL,2),(74,NULL,NULL,NULL,2),(72,NULL,NULL,NULL,3),(73,NULL,NULL,NULL,3);
/*!40000 ALTER TABLE `r_langtext` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `r_roles`
--

LOCK TABLES `r_roles` WRITE;
/*!40000 ALTER TABLE `r_roles` DISABLE KEYS */;
INSERT INTO `r_roles` VALUES (1,1),(2,1),(3,1),(4,1),(4,2);
/*!40000 ALTER TABLE `r_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'user',1000),(2,'admin',0),(3,'guest',9999);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `surveys`
--

LOCK TABLES `surveys` WRITE;
/*!40000 ALTER TABLE `surveys` DISABLE KEYS */;
INSERT INTO `surveys` VALUES (1);
/*!40000 ALTER TABLE `surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'Max','Mustermann','max@mustermann.de','ebdb1656ffd5b099252c533e9ad42d11'),(2,'Rudi','Testmann','rudi@testmann.com','3d82c14b83ec3dad46f2f6b2fe7f09e8'),(3,'Demo','User','demo@berlin.lo','fe01ce2a7fbac8fafaed7c982a04e229'),(4,'Demo','Admin','admin@berlin.lo','21232f297a57a5a743894a0e4a801fc3');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-05-24 10:13:43
