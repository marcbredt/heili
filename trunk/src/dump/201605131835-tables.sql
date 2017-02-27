-- MySQL dump 10.13  Distrib 5.6.30, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: magixsurvey
-- ------------------------------------------------------
-- Server version	5.6.30-1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accomplished`
--

DROP TABLE IF EXISTS `accomplished`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accomplished` (
  `acid` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `qid` int(11) NOT NULL,
  `aid` int(11) NOT NULL,
  PRIMARY KEY (`acid`),
  KEY `sid` (`sid`),
  KEY `cid` (`cid`),
  KEY `qid` (`qid`),
  KEY `aid` (`aid`),
  CONSTRAINT `accomplished_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `surveys` (`sid`),
  CONSTRAINT `accomplished_ibfk_2` FOREIGN KEY (`cid`) REFERENCES `user` (`cid`),
  CONSTRAINT `accomplished_ibfk_3` FOREIGN KEY (`qid`) REFERENCES `questions` (`qid`),
  CONSTRAINT `accomplished_ibfk_4` FOREIGN KEY (`aid`) REFERENCES `answers` (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `answers`
--

DROP TABLE IF EXISTS `answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `answers` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `paid` int(11) DEFAULT NULL,
  `qid` int(11) NOT NULL,
  `atype` int(11) NOT NULL,
  PRIMARY KEY (`aid`),
  KEY `atype` (`atype`),
  KEY `qid` (`qid`),
  CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`qid`) REFERENCES `questions` (`qid`),
  CONSTRAINT `answers_ibfk_2` FOREIGN KEY (`atype`) REFERENCES `atypes` (`atid`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `atypes`
--

DROP TABLE IF EXISTS `atypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `atypes` (
  `atid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`atid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `langtext`
--

DROP TABLE IF EXISTS `langtext`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `langtext` (
  `ltid` int(11) NOT NULL AUTO_INCREMENT,
  `lab` char(2) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`ltid`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `language`
--

DROP TABLE IF EXISTS `language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `language` (
  `lab` char(2) NOT NULL,
  `text` varchar(30) NOT NULL,
  PRIMARY KEY (`lab`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  PRIMARY KEY (`qid`),
  KEY `sid` (`sid`),
  CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `surveys` (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `r_langtext`
--

DROP TABLE IF EXISTS `r_langtext`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_langtext` (
  `ltid` int(11) NOT NULL,
  `sid` int(11) DEFAULT NULL,
  `qid` int(11) DEFAULT NULL,
  `aid` int(11) DEFAULT NULL,
  UNIQUE KEY `ltid` (`ltid`,`sid`,`qid`,`aid`),
  KEY `sid` (`sid`),
  KEY `qid` (`qid`),
  KEY `aid` (`aid`),
  CONSTRAINT `r_langtext_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `surveys` (`sid`),
  CONSTRAINT `r_langtext_ibfk_2` FOREIGN KEY (`qid`) REFERENCES `questions` (`qid`),
  CONSTRAINT `r_langtext_ibfk_3` FOREIGN KEY (`aid`) REFERENCES `answers` (`aid`),
  CONSTRAINT `r_langtext_ibfk_4` FOREIGN KEY (`ltid`) REFERENCES `langtext` (`ltid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `surveys`
--

DROP TABLE IF EXISTS `surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `surveys` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(30) NOT NULL DEFAULT '',
  `lastname` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(80) NOT NULL,
  `password` char(32) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-05-06 16:42:53
