/*
SQLyog Community Edition- MySQL GUI v7.14 
MySQL - 5.0.45 : Database - L4DStats
*********************************************************************
*/ 
/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`L4DStats` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `L4DStats`;

/*Table structure for table `Game` */

DROP TABLE IF EXISTS `Game`;

CREATE TABLE `Game` (
  `GameID` int(11) NOT NULL auto_increment,
  `LogFile` varchar(50) default NULL,
  `Players` varchar(1024) default NULL,
  `Date` datetime default NULL,
  `Kills` int(11) default NULL,
  `Map` varchar(50) default NULL,
  `ServerID` int(11) default '0',
  PRIMARY KEY  (`GameID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `Player` */

DROP TABLE IF EXISTS `Player`;

CREATE TABLE `Player` (
  `PlayerID` int(11) NOT NULL auto_increment,
  `PlayerName` varchar(50) default NULL,
  `SteamID` varchar(20) default NULL,
  PRIMARY KEY  (`PlayerID`),
  UNIQUE KEY `SteamID` (`SteamID`),
  KEY `Player` (`PlayerName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `Statistics` */

DROP TABLE IF EXISTS `Statistics`;

CREATE TABLE `Statistics` (
  `StatID` int(11) NOT NULL auto_increment,
  `GameID` int(11) default NULL,
  `PlayerID` int(11) default NULL,
  `AreaID` int(11) default NULL,
  `EntityKilled` int(11) default NULL,
  `Weapon` enum('autoshotgun','boomer','boomer_claw','dual_pistols','hunter_claw','hunting_rifle','infected','inferno','molotov','pipe_bomb','pistol','prop_minigun','pumpshotgun','rifle','smg','smoker_claw','tank_claw','tank_rock','witch','gascan','oxygentank','propanetank','prop_car_alarm','env_explosion','pain_pills','first_aid_kit','unknown') default 'unknown',
  `Headshot` tinyint(1) default NULL,
  `StatType` int(11) default '0' COMMENT '0 = DEATH',
  `StatDamage` int(11) default '0',
  PRIMARY KEY  (`StatID`),
  KEY `PlayerID` (`PlayerID`),
  KEY `GameID` (`GameID`),
  KEY `Entity` (`EntityKilled`),
  KEY `StatType` (`StatType`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `Weapons` */

DROP TABLE IF EXISTS `Weapons`;

CREATE TABLE `Weapons` (
  `WeaponID` int(11) NOT NULL auto_increment,
  `WeaponEntityName` varchar(30) default NULL,
  `WeaponFriendlyName` varchar(30) default NULL,
  PRIMARY KEY  (`WeaponID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
