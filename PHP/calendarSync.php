<?php
/**
 * This script syncs users and members to spc_user, and shares calendars to specified groups
 *
 * @package    calendarSync.php
 * @author     Scott Hancock
 *
 */

 
require_once '../../Admin/Calendar/SpcEngine.php';


$dbServer   = 'localhost';
$dbUser     = 'XXXXXXX';
$dbPasswd   = 'XXXXXXX';
$dbDatabase = 'XXXXXXX';

try {
    $testDB = new PDO("mysql:host=$dbServer;dbname=$dbDatabase", $dbUser, $dbPasswd);
    $testDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
}

//get the admin record. Exit if there isn't one, because calendar hasn't been set up properly
$sql = "SELECT * FROM spc_users WHERE role = 'admin'";
$spcAdmin = $testDB->query($sql)->fetchAll(PDO::FETCH_ASSOC);
if (empty($spcAdmin)) {
    print "Calendar must be configured with Admin user before syncing.";
    exit;
} else {
    $spcTheme = $spcAdmin[0]['theme'];
    $spcAdminId = $spcAdmin[0]['id'];
    $spcTimezone = $spcAdmin[0]['timezone'];
    $spcLanguage = $spcAdmin[0]['language'];
}

//get all of the current spc_users
$sql = 'SELECT username FROM spc_users';
$spcUsers = $testDB->query($sql)->fetchAll(PDO::FETCH_GROUP);

//get everyone from member
$sql = 'SELECT * FROM member WHERE deleted = 0';
$allMembers = $testDB->query($sql)->fetchAll(PDO::FETCH_ASSOC);

//get everyone from users
$sql = 'SELECT * FROM users';
$allUsers = $testDB->query($sql)->fetchAll(PDO::FETCH_ASSOC);

//insert the users that aren't already there
foreach ($allUsers as $user) {
    
    if (!array_key_exists($user['Login'],$spcUsers)) {
        $email = (empty($user['Email'])) ? $user['Login'].'@test.com' : $user['Email'];
        $user = array('username' => $user['Login'], 'email' => $email);
        @Spc::createUser($user);        
    } else {
        //update the users that exist
        $email = (empty($user['Email'])) ? $user['Login'].'@test.com' : $user['Email'];
        $sth = $testDB->prepare('UPDATE spc_users SET password = ?, email = ? WHERE username = ?');        
        $sth->execute(array($user['Password'],$email,$user['Login']));      
    }
}

//insert members that aren't already there
foreach ($allMembers as $member) {
    
    if (!array_key_exists($member['Member'],$spcUsers)) {
        //insert the member into spc_users
        $email = (empty($member['Email'])) ? $member['Member'].'@test.com' : $member['Email'];
        $user = array('username' => $member['Member'], 'email' => $email);
        @Spc::createUser($user);
    } else {        
        //update the users that exist
        $email = (empty($member['Email'])) ? $member['Member'].'@test.com' : $member['Email'];
        $sth = $testDB->prepare('UPDATE spc_users SET password = ?, email = ? WHERE username = ?');        
        $sth->execute(array($member['Password'],$email,$member['Member']));        
    }
}

//run an update to get latest passwords from user table
$sth = $testDB->prepare('UPDATE spc_users sp, users u SET sp.password = u.Password WHERE sp.username = u.Login');        
$sth->execute(); 

//run an update to get latest passwords from member table
$sth = $testDB->prepare('UPDATE spc_users sp, member m SET sp.password = m.Password WHERE sp.username = m.Member AND m.deleted = 0');        
$sth->execute();

//get a list of auto-synced (from this script) shared calendar rows
$sql = "SELECT sc.cal_id, sc.id, sc.shared_user_id, u.username, sc.name FROM spc_calendar_shared_calendars sc
        LEFT JOIN spc_users u ON sc.shared_user_id = u.id
        WHERE auto_synced = 'yes'
        ORDER BY sc.cal_id, u.username";  
$sharedCalsAS = $testDB->query($sql)->fetchAll(PDO::FETCH_GROUP);

//create an array with indexes of calendar ids and their subscribed users for easy searching
$sortedSharedCalsAS = array();
foreach ($sharedCalsAS as $calId => $cal) {
    $data = array();
    foreach ($cal as $user) {
        $sortedSharedCalsAS[$calId][$user['shared_user_id']] = $user;
    }
}

//get the list of users that should have shared calendars
$sql = "SELECT Subquery.cal_id,
       Subquery.Member,
       Subquery.spc_users_id,
       Subquery.name,
       Subquery.description,
       Subquery.color,
       Subquery.admin_id,
       IFNULL(pl_calendar_user_share.permission,'see') AS permission
  FROM    test.pl_calendar_user_share pl_calendar_user_share
       RIGHT JOIN
          (SELECT Subquery.cal_id,
                  Subquery.Member,
                  Subquery.spc_users_id,
                  spc_calendar_calendars.name,
                  spc_calendar_calendars.description,
                  spc_calendar_calendars.color,
                  spc_calendar_calendars.admin_id
             FROM    test.spc_calendar_calendars spc_calendar_calendars
                  INNER JOIN
                     (SELECT DISTINCT
                             pl_calendar_user_share.cal_id,
                             spc_users.username AS Member,
                             spc_users.id AS spc_users_id
                        FROM    test.pl_calendar_user_share pl_calendar_user_share
                             INNER JOIN
                                test.spc_users spc_users
                             ON (pl_calendar_user_share.spc_user_id =
                                    spc_users.id)
                      UNION
                      SELECT DISTINCT
                             pl_calendar_team_share.cal_id,
                             teammember.Member,
                             spc_users.id
                        FROM    (   test.teammember teammember
                                 INNER JOIN
                                    test.spc_users spc_users
                                 ON (teammember.Member = spc_users.username))
                             INNER JOIN
                                test.pl_calendar_team_share pl_calendar_team_share
                             ON (pl_calendar_team_share.team =
                                    teammember.Team)
                       WHERE (teammember.deleted = 0)
                      UNION
                      SELECT DISTINCT
                             pl_calendar_position_share.cal_id,
                             positionmember.Member,
                             spc_users.id AS spc_users_id
                        FROM    (   test.positionmember positionmember
                                 INNER JOIN
                                    test.spc_users spc_users
                                 ON (positionmember.Member =
                                        spc_users.username))
                             INNER JOIN
                                test.pl_calendar_position_share pl_calendar_position_share
                             ON (pl_calendar_position_share.position =
                                    positionmember.Position)
                       WHERE (positionmember.deleted = 0)) Subquery
                  ON (spc_calendar_calendars.id = Subquery.cal_id)
           ORDER BY Subquery.cal_id ASC, Subquery.Member ASC) Subquery
       ON     (pl_calendar_user_share.cal_id = Subquery.cal_id)
          AND (pl_calendar_user_share.spc_user_id = Subquery.spc_users_id)
    ORDER BY Subquery.cal_id ASC, Subquery.Member ASC";
$groupMembers = $testDB->query($sql)->fetchAll(PDO::FETCH_GROUP);
//print_r($groupMembers);
//print_r($sortedSharedCalsAS);

//go through the auto-synced list, and see which ones are no longer needed
foreach ($sortedSharedCalsAS as $calId => $cal) {
    $found = false;
    $userId = key($cal);
    
    //print "$userId '$found'\n";
    $rowId = $cal[$userId]['id'];

    if (array_key_exists($calId, $groupMembers)) {
        //var_dump($groupMembers[$calId]);exit;
        foreach ($groupMembers[$calId] as $member) {
            if ($member['spc_users_id'] == $userId) {
                $found = true;
                break;
            }
        }
    }
    
    //User must have been removed from group. delete the row.
    if (!$found) {
        $sql = "DELETE FROM spc_calendar_shared_calendars WHERE id = $rowId";
        $sth = $testDB->prepare($sql);        
        $sth->execute();  
    } 
}

//get a list of ALL the current shared calendars
$sql = "SELECT sc.cal_id, sc.id, sc.shared_user_id, u.username, sc.name FROM spc_calendar_shared_calendars sc
        LEFT JOIN spc_users u ON sc.shared_user_id = u.id
        ORDER BY sc.cal_id, u.username";  
$sharedCals = $testDB->query($sql)->fetchAll(PDO::FETCH_GROUP);

//create an array with indexes of calendar ids and their subscribed users for easy searching
$sortedSharedCals = array();
foreach ($sharedCals as $calId => $cal) {
    $data = array();
    foreach ($cal as $user) {
        $sortedSharedCals[$calId][$user['shared_user_id']] = $user;
    }
}

//now go through the list of group members
foreach ($groupMembers as $calId => $memberList) {
    foreach ($memberList as $member) {
        
        $data = array(':type'           => 'user',
                      ':user_id'        => $member['admin_id'],
                      ':cal_id'         => $calId,
                      ':shared_user_id' => $member['spc_users_id'],
                      ':permission'     => $member['permission'],
                      ':name'           => $member['name'],
                      ':description'    => $member['description'],
                      ':color'          => $member['color'],
                      ':status'         => 'on',
                      ':show_in_list'   => 1,
                      ':auto_synced'    => 'yes'
                      );
        
        //if they already have the share set up, update it
        if (isset($sortedSharedCals[$calId][$member['spc_users_id']])) {
            $rowId = $sortedSharedCals[$calId][$member['spc_users_id']]['id'];
            $sql = "UPDATE spc_calendar_shared_calendars SET type=:type, user_id=:user_id, cal_id=:cal_id, shared_user_id=:shared_user_id,
                    permission=:permission, name=:name, description=:description, color=:color, status=:status, show_in_list=:show_in_list,
                    auto_synced=:auto_synced WHERE id = $rowId";
            $sth = $testDB->prepare($sql);        
            $sth->execute($data);
        } else {
            //add the share row
            $sql = "INSERT INTO spc_calendar_shared_calendars (type,user_id,cal_id,shared_user_id,permission,name,description,color,status,show_in_list,auto_synced)
                    VALUES (:type,:user_id,:cal_id,:shared_user_id,:permission,:name,:description,:color,:status,:show_in_list,:auto_synced)";
            $sth = $testDB->prepare($sql);        
            $sth->execute($data);
        }
    }
    
 
}



//echo "finished\n";