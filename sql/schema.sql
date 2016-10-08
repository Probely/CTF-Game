-- MySQL dump 10.13  Distrib 5.1.57, for apple-darwin10.8.0 (i386)
--
-- Host: localhost    Database: codebits
-- ------------------------------------------------------
-- Server version	5.1.53

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

CREATE DATABASE IF NOT EXISTS CTF;
USE CTF;

--
-- Table structure for table `answers`
--

DROP TABLE IF EXISTS `answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `answers` (
  `teamid` int(11) NOT NULL,
  `cat` varchar(20) NOT NULL,
  `question` int(11) NOT NULL,
  `points` int(11) DEFAULT NULL,
  `answeredts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`teamid`,`cat`,`question`),
  KEY `teamid` (`teamid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `answers`
--

LOCK TABLES `answers` WRITE;
/*!40000 ALTER TABLE `answers` DISABLE KEYS */;
#INSERT INTO `answers` VALUES (1,'Trivia',100,90,'2011-10-31 17:23:50'),(1,'Trivia',200,200,'2011-10-31 17:23:50'),(1,'Trivia',400,400,'2011-10-31 17:23:50'),(2,'Forensics',300,300,'2011-10-31 17:23:40'),(2,'Trivia',100,100,'2011-10-31 17:23:50'),(2,'Trivia',300,300,'2011-10-31 17:23:50'),(2,'Web Hacking',100,100,'2011-10-31 17:24:29'),(2,'Web Hacking',200,180,'2011-11-02 14:45:43'),(3,'Trivia',100,80,'2011-11-02 14:45:43'),(3,'Trivia',200,160,'2011-11-02 14:45:43'),(4,'Trivia',100,80,'2011-11-02 14:45:43'),(4,'Web Hacking',200,200,'2011-11-02 14:45:43'),(5,'Trivia',100,80,'2011-11-02 14:45:43');

insert into answers values (5,'Trivia',100,100,now()); insert into answers values (5,'Trivia',200,100,now());insert into answers values (5,'Trivia',300,100,now()); insert into answers values (5,'Trivia',400,100,now());
insert into answers values (5,'Pwnable',100,100,now()); insert into answers values (5,'Pwnable',200,100,now());insert into answers values (5,'Pwnable',300,100,now());insert into answers values (5,'Pwnable',400,100,now());
insert into answers values (5,'Forensics',100,100,now()); insert into answers values (5,'Forensics',200,100,now());insert into answers values (5,'Forensics',300,100,now());insert into answers values (5,'Forensics',400,100,now());
insert into answers values (5,'Web Hacking',100,100,now());insert into answers values (5,'Web Hacking',200,100,now()); insert into answers values (5,'Web Hacking',300,100,now()); insert into answers values (5,'Web Hacking',400,100,now());

/*!40000 ALTER TABLE `answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game`
--

DROP TABLE IF EXISTS `game`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game` (
  `leadcat` varchar(20),
  `leadpoints` int(11),
  `yellowshirt` int(11) DEFAULT NULL,
  `lastleadreset` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`leadcat`, `leadpoints`),
  KEY `yellowshirt` (`yellowshirt`),
  CONSTRAINT `yellowshirt` FOREIGN KEY (`yellowshirt`) REFERENCES `teams` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game`
--

LOCK TABLES `game` WRITE;
/*!40000 ALTER TABLE `game` DISABLE KEYS */;
INSERT INTO `game` (leadcat,leadpoints,end)  VALUES ('Trivia',100,from_unixtime(unix_timestamp(now())+10800));
/*!40000 ALTER TABLE `game` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `team` varchar(80) DEFAULT NULL,
  `tkey` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tkey_UNIQUE` (`tkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `hints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hint` TEXT NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'Team1',sha1('5bd7b888f1327e07ecfcbed64a7ba4154bbe08bc')),
                           (2,'team2',sha1('da1caf48a41f09b76df6e6692f03573508925364')),
                           (3,'Team3',sha1('6e91a41941be135959c97a26ad43cbc6c8a68364')),
                           (4,'team4',sha1('92e1b5d17c3b6ad12b6a711d70a077a3d32d27c9')),
                           (5,'Team5',sha1('7849f1efc1578926a42a91198d872a47b6614adf')),
                           (6,'team6',sha1('b8a49af1f5801d6943f271887511cac890cceaca')),
                           (7,'Team7',sha1('caf47b50a973d1dcec68095021111caef0b8a680')),
                           (8,'team8',sha1('b87611d1ab526a72f015417131faf618dcead012')),
                           (9,'Team9',sha1('7fe046eb111dd620d9fc9b1b11cd0d6d0c35fb61')),
                          (10,'team10',sha1('a31f110ac892c1c4fad24f5cc7131e58b3311396'));
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

CREATE USER 'pixels'@'localhost' IDENTIFIED BY 'xxxxxxxxxxx';
GRANT SELECT,INSERT,UPDATE,DELETE ON CTF.* TO 'pixels'@'localhost';


-- Dump completed on 2011-11-02 15:06:05
