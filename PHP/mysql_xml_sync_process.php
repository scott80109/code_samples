<?php

/****
*  This script was written by Scott Hancock to read in MySQL XML schema dumps 
*  and use them to synchronize the DB schemas. 
****/


/************************************************************************/
/* This will be where we maintain the arrays to handle the insertion of 
 * mandatory test table data. Keyed on Table Name with data arrays
 ************************************************************************/

$insertData['users'] = array(array('Login' => 'admin',
							'Password' => 'XXXXXXXXXXXXXXX',
							'Level' => '500',
							'FirstName' => 'Administrator',
							'LastName' => 'test',
							'Email' => 'support@test.com'));

$insertData['users_level'] = array(array('level' => '100',
									     'level_desc' => 'Reports'),
								   array('level' => '500',
										 'level_desc' => 'Administrator'));

$insertData['swversion'] = array(array('idswVersion' => '1',
									   'iosReqSwVersion' => '2012.2.0',
									   'iosReqSwForceDate' => '2012-10-01 00:00:00',
									   'appUpdateServerURL' => 'http://'));

$insertData['appupdateenabled'] = array(array('appUpdateEnabled' => 'check',
									     'appUpdateEnabledText' => 'Check',
										 'order' => '0'),
										array('appUpdateEnabled' => 'force',
									     'appUpdateEnabledText' => 'Force',
										 'order' => '100'),
										array('appUpdateEnabled' => 'none',
									     'appUpdateEnabledText' => 'None',
										 'order' => '1'));

$insertData['cellularenabled'] = array(array('cellularEnabled' => 'any',
									     'cellularEnabledText' => 'Any',
										 'order' => '100'),
										array('cellularEnabled' => 'no',
									     'cellularEnabledText' => 'No',
										 'order' => '0'),
										array('cellularEnabled' => 'playbook',
									     'cellularEnabledText' => 'PlayBook',
										 'order' => '1'));										 

/***********************************************************************/


//login with root first to see if the database and testAdmin user even exists
$testDB = getRootConnection();

//see if testAdmin user exists. Add it if it doesn't.
$userExists = false;
$sql = 'SELECT * from mysql.user';
$users = $testDB->query($sql)->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
	if ($user['User'] == 'testAdmin') {
		$userExists = true;
		break;
	}
}
if (!$userExists) {
	$sql1 = "CREATE USER 'testAdmin'@'localhost' IDENTIFIED BY '****'";
	$sql2 = "GRANT ALL PRIVILEGES ON * . * TO 'testAdmin'@'localhost' IDENTIFIED BY '****' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0";
	$sql3 = "GRANT ALL PRIVILEGES ON `testAdmin\\_%` . * TO 'testAdmin'@'localhost'";
	$testDB->exec($sql1);
	$testDB->exec($sql2);
	$testDB->exec($sql3);
}
		
//figure out if the test database exists. Create it if it doesn't.
$exists = false;
$sql = 'SHOW databases';
$databases = $testDB->query($sql)->fetchAll(PDO::FETCH_ASSOC);
foreach ($databases as $db) {
	if ($db['Database'] == 'test') {
		$exists = true;
	}
}
if (!$exists) {
	$sql = 'CREATE DATABASE `test`';
	$testDB->exec($sql);
}

//now login to the test DB with testAdmin
$testDB = gettestConnection();   

//read in the XML mysql dump
$xml = simplexml_load_file('testDbMaster.xml') or die('Could not open MySQL XML dump!');
$theDatabase = $xml->database;

//iterate through ALL tables. If exists, check structure. If not, create it.
foreach ($theDatabase->{'table_structure'} as $table) {
	$tableName = (string)$table->attributes()->name;
	
	//don't create tables for views
	if ($tableName == 'new_view' || strpos($tableName, 'vw_') !== false) {
		continue;
	}
	
	//does table exist? If so, verify it is valid. If not, create it.
	if(tableExists($tableName)) {
		verifyTable($theDatabase, $table);
	} else {
		createTable($table);

		//populate specific tables if we just created it
		switch ($tableName) {
			case 'users':
				insertMandatory($tableName, $insertData['users']);
				break;
			case 'users_level':
				insertMandatory($tableName, $insertData['users_level']);
				break;
			case 'swversion':
				insertMandatory($tableName, $insertData['swversion']);
				break;
			case 'appupdateenabled':
				insertMandatory($tableName, $insertData['appupdateenabled']);
				break;
			case 'cellularenabled':
				insertMandatory($tableName, $insertData['cellularenabled']);
				break;
		}
	}
}

//create views
createViews();

//add triggers
addTriggers();
 
//All done. Echo finished message.
echo "All tables match!";
exit;




/******************
 * Functions
 *****************/

function getRootConnection()
{
	$host       = 'localhost';
	$dbuser     = 'root';
	$dbpassword = 'XXXXXXX';
	$testDB = new PDO("mysql:host=$host", $dbuser, $dbpassword);
	return $testDB;
}

function gettestConnection()
{
	$host       = 'localhost';
	$dbuser     = 'root';
	$dbpassword = 'XXXXXXXXX';
	$dbname     = 'XXXXXXXXX';
	$testDB = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpassword);
	return $testDB;
}

function tableExists($name) 
{
	$testDB = gettestConnection();
	$sql = "SHOW tables";
	$tables = $testDB->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	if (count($tables) == 0) {
		return false;
	}
	
	foreach ($tables as $table) {
		if ($table['Tables_in_test'] == $name) {
			return true;
		}
	}
	
	return false;
}

function createTable($table)
{
	$tableName = (string)$table->attributes()->name;
	
	$testDB = gettestConnection();
	
	$sql = "CREATE TABLE IF NOT EXISTS `$tableName` (\n";
	
	foreach ($table->field as $column) {
		
		$name = (string)$column->attributes()->Field;
		$type = (string)$column->attributes()->Type;
		$nullFlag = (string)$column->attributes()->Null;
		if (isset($column->attributes()->Default)) {
			$default = (string)$column->attributes()->Default;
			if ($default == 'CURRENT_TIMESTAMP') {
				$default = "DEFAULT CURRENT_TIMESTAMP";
			} else {
				$default = ($default == '') ? '' : "DEFAULT '$default'";
			}
		} else {
			$default = null;
		}
		$nullStr = ($nullFlag == 'YES') ? 'DEFAULT NULL' : 'NOT NULL';
		$key = (string)$column->attributes()->Key;
		$extra = (string)$column->attributes()->Extra;
		$comment = (string)$column->attributes()->Comment;
		$commentStr = (strlen($comment) == 0) ? '' : "COMMENT '$comment'";
		
		$sql .= "`$name` $type $extra $nullStr $default $commentStr,\n";
	}
	
	//trim off the last comma 
	$sql = substr_replace($sql ,"",-2);
	$sql .= "\n";
	
	//get the keys
	$primaries = array();
	$foreigns = array();
	$uniques = array();
	$indexes = array();
	$nonUniques = array();
	
	foreach ($table->key as $key) {
		
		$kName = (string)$key->attributes()->Key_name;
		$cName = (string)$key->attributes()->Column_name;
		$kUnique = (string)$key->attributes()->Non_unique;
		if ($kName == 'PRIMARY') {
			$primaries[] = array('keyName' => $kName, 'column' => $cName);
		} elseif (preg_match('/-FK/i', $kName) ||
				preg_match('/FK_/i', $kName)) {
			$foreigns[] = array('keyName' => $kName, 'column' => $cName);
		} else {
			$uniques[] = array('keyName' => $kName, 'column' => $cName);
		}
	}
	
	//add the key syntax
	if (!empty($primaries)) {
		if (count($primaries) > 1) {
			//PRIMARY KEY (`Member`,`messageDate`,`fileName`)
			$sql .= ",PRIMARY KEY (";
			foreach ($primaries as $p) {
				$sql .= "`".$p['column']."`,";
			}
			//remove the last comma
			$sql = substr_replace($sql ,"",-1);
			$sql .= ")\n";
		} else {
			$sql .= ",PRIMARY KEY (`".$primaries[0]['column']."`)\n";
		}
	}
	
	if (!empty($uniques)) {
		foreach ($uniques as $u) {
			//UNIQUE KEY `serialNumber_UNIQUE` (`serialNumber`),
			$sql .= ",UNIQUE KEY `".$u['keyName']."` (`".$u['column']."`)\n";
		}
	}
	
	if (!empty($foreigns)) {
		foreach ($foreigns as $fk) {
			$theArray = array('fk_appUpdateEnabled');
			if (in_array($fk['keyName'], $theArray)) {
				$delete = 'ON DELETE NO ACTION';
			} else {
				$delete = 'ON DELETE CASCADE';
			}
			$sql .= ",KEY `".$fk['keyName']."` (`".$fk['column']."`)\n";
			$sql .= ",CONSTRAINT `".$fk['keyName']."` FOREIGN KEY (`".$fk['column']."`) REFERENCES `".strtolower($fk['column'])."` (`".$fk['column']."`) $delete ON UPDATE CASCADE\n";
		}
	}
	
	//trim off the last ,
	//$sql = substr_replace($sql ,"",-1);
	$sql .= ")\n";
	
	//add options
	$engine = (string)$table->options->attributes()->Engine;
	$charset = (string)$table->options->attributes()->Collation;
	if (isset($table->options->attributes()->Auto_increment)) {
		$ai = (string)$table->options->attributes()->Auto_increment;
		$ai = ($ai == '') ? '' : "AUTO_INCREMENT=$ai";
	} else {
		$ai = null;
	}
	
	if ($charset == 'latin1_swedish_ci') {
		$charset = 'latin1';
	}
	
	$engine = ($engine == '') ? '' : "ENGINE=$engine";
	
	switch ($charset) {
		case 'utf8_general_ci':
			$charset = 'utf8';
			break;
		case 'latin1_swedish_ci':
			$charset = 'latin1';
			break;
	}
	
	$charset = ($charset == '') ? '' : "DEFAULT CHARSET=$charset";
	$sql .= "$engine $ai $charset\n\n";
	$testDB->exec($sql);
}

function createViews()
{
	$testDB = gettestConnection();
	
	$sql = "CREATE ALGORITHM=UNDEFINED DEFINER=`testAdmin`@`localhost` SQL SECURITY DEFINER VIEW `vw_downloadlog` AS select `downloadlog`.`instigator` AS `instigator`,`downloadlog`.`Member` AS `Member`,`downloadlog`.`messageDate` AS `MessageDate`,`downloadlog`.`fileName` AS `fileName`,`downloadlog`.`fileType` AS `fileType`,`downloadlog`.`fileSize` AS `fileSize`,`downloadlog`.`Type` AS `Type`,`downloadlog`.`Duration` AS `Duration`,date_format(`downloadlog`.`messageDate`,_utf8'%m/%d/%Y') AS `MessageDay` from `downloadlog`";
	$testDB->exec($sql);
	
	$sql = "CREATE ALGORITHM=UNDEFINED DEFINER=`testAdmin`@`%` SQL SECURITY DEFINER VIEW `vw_notificationdownldperhour` AS select concat(date_format(`vw_downloadlog`.`MessageDate`,'%H'),':00') AS `DownloadDate`,count(`vw_downloadlog`.`Type`) AS `TTLDownloads` from `vw_downloadlog` where ((`vw_downloadlog`.`fileType` = 'Notification') and (`vw_downloadlog`.`Type` = 'Complete') and (date_format(`vw_downloadlog`.`MessageDate`,'%m/%d/%y') = date_format(curdate(),'%m/%d/%y'))) group by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y %H') order by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y %H') desc";
	$testDB->exec($sql);
	
	$sql = "CREATE ALGORITHM=UNDEFINED DEFINER=`testAdmin`@`%` SQL SECURITY DEFINER VIEW `vw_notificationdownldperweek` AS select date_format(`vw_downloadlog`.`MessageDate`,'%m/%d') AS `DownloadDate`,count(`vw_downloadlog`.`Type`) AS `TTLDownloads` from `vw_downloadlog` where ((`vw_downloadlog`.`fileType` = 'Notification') and (`vw_downloadlog`.`Type` = 'Complete') and (date_format(`vw_downloadlog`.`MessageDate`,'%m/%d/%y') > date_format((curdate() - interval 7 day),'%m/%d/%y'))) group by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y') order by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y') desc";
	$testDB->exec($sql);
	
	$sql = "CREATE ALGORITHM=UNDEFINED DEFINER=`testAdmin`@`%` SQL SECURITY DEFINER VIEW `vw_playbookdownldperhour` AS select concat(date_format(`vw_downloadlog`.`MessageDate`,'%H'),':00') AS `DownloadDate`,count(`vw_downloadlog`.`Type`) AS `TTLDownloads` from `vw_downloadlog` where ((`vw_downloadlog`.`fileType` = 'PlayBook') and (`vw_downloadlog`.`Type` = 'Complete') and (date_format(`vw_downloadlog`.`MessageDate`,'%m/%d/%y') = date_format(curdate(),'%m/%d/%y'))) group by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y %H') order by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y %H') desc";
	$testDB->exec($sql);
	
	$sql = "CREATE ALGORITHM=UNDEFINED DEFINER=`testAdmin`@`%` SQL SECURITY DEFINER VIEW `vw_playbookdownldperweek` AS select date_format(`vw_downloadlog`.`MessageDate`,'%m/%d') AS `DownloadDate`,count(`vw_downloadlog`.`Type`) AS `TTLDownloads` from `vw_downloadlog` where ((`vw_downloadlog`.`fileType` = 'PlayBook') and (`vw_downloadlog`.`Type` = 'Complete') and (date_format(`vw_downloadlog`.`MessageDate`,'%m/%d/%y') > date_format((curdate() - interval 7 day),'%m/%d/%y'))) group by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y') order by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y') desc";
	$testDB->exec($sql);
	
	$sql = "CREATE ALGORITHM=UNDEFINED DEFINER=`testAdmin`@`%` SQL SECURITY DEFINER VIEW `vw_videodwnldperhour` AS select concat(date_format(`vw_downloadlog`.`MessageDate`,'%H'),':00') AS `DownloadDate`,count(`vw_downloadlog`.`Type`) AS `TTLDownloads` from `vw_downloadlog` where ((`vw_downloadlog`.`fileType` = 'GameFilm') and (`vw_downloadlog`.`Type` = 'Complete') and (date_format(`vw_downloadlog`.`MessageDate`,'%m/%d/%y') = date_format(curdate(),'%m/%d/%y'))) group by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y %H') order by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y %H') desc";
	$testDB->exec($sql);
	
	$sql = "CREATE ALGORITHM=UNDEFINED DEFINER=`testAdmin`@`%` SQL SECURITY DEFINER VIEW `vw_videodwnldperweek` AS select date_format(`vw_downloadlog`.`MessageDate`,'%m/%d') AS `DownloadDate`,count(`vw_downloadlog`.`Type`) AS `TTLDownloads` from `vw_downloadlog` where ((`vw_downloadlog`.`fileType` = 'GameFilm') and (`vw_downloadlog`.`Type` = 'Complete') and (date_format(`vw_downloadlog`.`MessageDate`,'%m/%d/%y') > date_format((curdate() - interval 7 day),'%m/%d/%y'))) group by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y') order by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y') desc";
	$testDB->exec($sql);
	
	$sql = "CREATE ALGORITHM=UNDEFINED DEFINER=`testAdmin`@`%` SQL SECURITY DEFINER VIEW `new_view` AS select concat(date_format(`vw_downloadlog`.`MessageDate`,'%H'),':00') AS `DownloadDate`,count(`vw_downloadlog`.`Type`) AS `TTLDownloads` from `vw_downloadlog` where ((`vw_downloadlog`.`fileType` = 'Notification') and (`vw_downloadlog`.`Type` = 'Complete') and (date_format(`vw_downloadlog`.`MessageDate`,'%m/%d/%y') = date_format(curdate(),'%m/%d/%y'))) group by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y %H') order by date_format(`vw_downloadlog`.`MessageDate`,'%d/%m/%y %H') desc";
	$testDB->exec($sql);
	
}

function verifyTable($database, $table)
{
	//get the name of the table passed in the validate
	$tableName = (string)$table->attributes()->name;
	$previousColumnName = null;
	
	//get the DB connection
	$testDB = gettestConnection();
	
	//get a description of the table
	$sql = "DESC `$tableName`";
	$desc = $testDB->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	//find the table in the XML master copy
	foreach ($database->{'table_structure'} as $t) {
		if ($tableName == $t->attributes()->name) {
			$masterTable = $t;
			break;
		}
	}

	//set master values
	$masterColumns = $masterTable->field;
	$masterKeys	   = $masterTable->key;
	$masterOptions = $masterTable->options;
	
	//verify columns are in sync using local vs master. Drop extra local columns. 
	foreach ($desc as $column) {
		
		$cName = $column['Field'];
		$found = false;
		
		//find the master column desc
		foreach ($masterColumns as $c) {
			if ($cName == (string)$c->attributes()->Field) {
				
				$doesMatch = true;
				$found = true;

				if (trim($column['Field']) != trim((string)$c->attributes()->Field)) {
					$doesMatch = false;
				}
				if (trim($column['Type']) != trim((string)$c->attributes()->Type)) {
					$doesMatch = false;
				}
				if (trim($column['Null']) != trim((string)$c->attributes()->Null)) {
					$doesMatch = false;
				}
				if (trim($column['Key']) != trim((string)$c->attributes()->Key)) {
					$doesMatch = false;
				}
				if (trim($column['Default']) != trim((string)$c->attributes()->Default)) {
					$doesMatch = false;
				}
				if (trim($column['Extra']) != trim((string)$c->attributes()->Extra)) {
					$doesMatch = false;
				}
				
				//if there are differences, call function to alter table
				if (!$doesMatch) {
					alterColumn($tableName, $column, $c);
				}
			}
		}
		
		//if we didn't find the column, drop it
		if (!$found) {
			$sql = "ALTER TABLE `$tableName` DROP `$cName`";
			$testDB->exec($sql);
		}
	}
	
	//verify columns are in sync using master vs local in case columns don't exist locally. Add missing columns.
	$previousColumnName = null;
	foreach ($masterColumns as $c) {
	
		$found = false;
	
		//find the master column desc
		foreach ($desc as $column) {
			$cName = $column['Field'];
			if ($cName == (string)$c->attributes()->Field) {
	
				$found = true;
				
				//we already synced up existing columns, so no need to check again. This
				//is only to find missing local columns.
			}
		}
		
		//if we didn't find the column, add it
		if (!$found) {
			addColumn($tableName, (string)$c->attributes()->Field, $c, $previousColumnName);
		}
		
		//track the previous column for alters
		$previousColumnName = (string)$c->attributes()->Field;
	
	}	
	
	//verify the keys are in sync
	$sql = "SHOW INDEXES FROM `$tableName`;";
	$indexes = $testDB->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	
	//compare master to local, and add any missing keys
	foreach ($masterKeys as $key) {
		
		$kName = (string)$key->attributes()->Key_name;
		$cName = (string)$key->attributes()->Column_name;
		$found = false;
		
		foreach ($indexes as $index) {
			
			if ($index['Key_name'] == $kName) {
				$found = true;
			}
		}
		
		if (!$found) {
			//add the index
			if ($kName == 'PRIMARY') {
				$sql = "ALTER TABLE `$tableName` ADD PRIMARY KEY ( `".$key['Column_name']."` )";
			} elseif (preg_match('/-FK/i', $kName) ||
				preg_match('/FK_/i', $kName)) {
				//add the key
				$sql = "ALTER TABLE `$tableName` ADD KEY `$kName` ( `$cName` ) ";
				$testDB->exec($sql);
				$sql = "ALTER TABLE `$tableName` ADD CONSTRAINT `$kName` FOREIGN KEY ( `$cName` ) REFERENCES `".strtolower($cName)."` (`$cName`) ON DELETE NO ACTION ON UPDATE CASCADE";
			}else {
				$sql = "ALTER TABLE `$tableName` ADD UNIQUE `$kName` ( `".$key['Column_name']."` ) ";
			}
			
			$testDB->exec($sql);
		}
	
	}
}

function addColumn ($tableName, $columnName, $masterColumn, $previousColumnName)
{
	//get DB connection
	$testDB = gettestConnection();
	
	//get attributes of column
	$name = (string)$masterColumn->attributes()->Field;
	$type = (string)$masterColumn->attributes()->Type;
	$nullFlag = (string)$masterColumn->attributes()->Null;
	$key = (string)$masterColumn->attributes()->Key;
	$extra = (string)$masterColumn->attributes()->Extra;
	
	//set default value string
	if (isset($masterColumn->attributes()->Default)) {
		$default = (string)$masterColumn->attributes()->Default;
		if ($default == 'CURRENT_TIMESTAMP') {
			$default = "DEFAULT CURRENT_TIMESTAMP";
		} else {
			$default = ($default == '') ? '' : "DEFAULT '$default'";
		}
	} else {
		$default = null;
	}
	
	//set null string
	$nullStr = ($nullFlag == 'YES') ? 'NULL DEFAULT NULL' : 'NOT NULL';
	
	//set location of column to be added
	if (is_null($previousColumnName)) {
		$location = 'FIRST';
	} else {
		$location = "AFTER `$previousColumnName`";
	}
	
	//set primary key string
	$key = ($key == 'PRI') ? ",ADD PRIMARY KEY ( `".$name."` )" : '';
	
	//create SQL string for ALTER command
	$sql = "ALTER TABLE `$tableName` ADD `$columnName` $type $nullStr $location $key";
	
	//execute sql
	$testDB->exec($sql);
}

function alterColumn ($tableName, $localCol, $masterCol)
{
	//get DB handle
	$testDB = gettestConnection();
	
	$columnName = $localCol['Field'];
	$nullStr = ((string)$masterCol->attributes()->Null == 'YES') ? 'NULL DEFAULT NULL' : 'NOT NULL';
	$extra = (string)$masterCol->attributes()->Extra;
	$default = (string)$masterCol->attributes()->Default;
	if ($default == 'CURRENT_TIMESTAMP') {
		$default = "DEFAULT CURRENT_TIMESTAMP";
	} else {
		$default = ($default == '') ? '' : "DEFAULT '$default'";
	}
	
	$sql = "ALTER TABLE `$tableName` CHANGE `$columnName` `$columnName` ".(string)$masterCol->attributes()->Type." $extra $nullStr $default";
	$testDB->exec($sql);
}

function insertMandatory($table, $insertData)
{
	//get the DB handle
	$testDB = gettestConnection();

	//insert the data here
	foreach ($insertData as $insertRow) {
		$bind = ':'.implode(',:', array_keys($insertRow));
		$sql  = 'insert into `'.$table.'`(`'.implode('`,`', array_keys($insertRow)).'`) '.
				'values ('.$bind.')';
		$stmt = $testDB->prepare($sql);
		$stmt->execute(array_combine(explode(',',$bind), array_values($insertRow)));
	}

	return true;
}

function addTriggers() {
	
	$testDB = gettestConnection();
	
	//$sql = "DROP TRIGGER IF EXISTS `test`.`createGuidInsertTrigger`";
	//$testDB->exec($sql);
	
	$sql = "CREATE TRIGGER `test`.`createGuidInsertTrigger` BEFORE INSERT
			ON test.member FOR EACH ROW
			BEGIN
				SET new.memberId := (SELECT uuid());
			END";
	$testDB->exec($sql);

}


