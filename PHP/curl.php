<?php

error_reporting(E_ALL);
error_reporting(E_STRICT);

$tables=array("teammember"=>"team","positionmember"=>"position","member"=>"member","team"=>"team","position"=>"position","member_type"=>"type","priority_group"=>"priority","devices"=>"serialNumber");
//$tables=array("member"=>"member");

    $dbServer='localhost';
    $dbUser='xxxxxxx';
    $dbPasswd='xxxxxxx!';
    $dbname='xxxxxxx';

    try
    {
        $testDB = new PDO("mysql:host=$dbServer;dbname=$dbname", $dbUser, $dbPasswd) ;
        $testDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e)
    {
        echo $e->getMessage();
        die;
    }


$sql="SELECT licenseKey,serverAuthUser,serverAuthPassword,serviceURL FROM test.settings_traveltest;";

$sth = $testDB->prepare($sql);
$sth->execute();
$result = $sth->fetchAll(PDO::FETCH_ASSOC);


$serviceURL=$result[0][serviceURL];
$serverAuthUser=$result[0][serverAuthUser];
$serverAuthPassword=$result[0][serverAuthPassword];
$licenseKey=$result[0][licenseKey];

foreach($tables as $table=>$keyField)
{

$script="list".ucwords(strtolower($table))."s";
$now = date("Y-m-d H:i:s");



//get the oldest sync date for the table
$sql = "SELECT syncDate FROM test.$table ORDER BY syncDate LIMIT 1;";
$sth = $testDB->prepare($sql);
$sth->execute();
$result = $sth->fetchAll(PDO::FETCH_ASSOC);
if (!empty($result[0]['syncDate'])) {
    $earliestSyncDate = strtotime($result[0]['syncDate']);
} else {
    $earliestSyncDate = 0;
}

//if oldest sync date is more than 24 hours ago, sync the whole table
$current = strtotime("now");
if (($current - $earliestSyncDate) > 86400) {
    $syncAll = true;
} else {
    $syncAll = false;
}


//get timestamp for yesterday if we are only syncing newest records
if (!$syncAll) {
    $yesterday = strtotime("-1 day");
} else {
    $yesterday = null;
}

//always truncate and re-sync the entire member table
/*if ($table == 'member') {
    $syncAll = true;
    $yesterday = null;
    try {
        $sql = "DELETE FROM $dbname.$table WHERE 1=1;";
        $testDB->exec($sql);
    } catch(PDOException $e) {
        echo $e->getMessage();
        die;
    }
}*/

//$syncAll = true;

// jSON URL which should be requested
$url = "$serviceURL/".$script.".php";
$username = $serverAuthUser;
$password = "$serverAuthPassword";

$postVars = Array("plUser" => "XXXXXXXX","plPassword" => "XXXXXX","modifiedDate" => $yesterday);

// Initializing curl
$ch = curl_init();

// Configuring curl options
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//This is a simple Auth Mech to make sure we are the correct User
curl_setopt($ch, CURLOPT_POSTFIELDS,"XXXXX");



$result =  curl_exec($ch); // Getting jSON result string


//echo 'Curl error: ' . curl_error($ch);
//$info = curl_getinfo($ch);
//print_r($info);

curl_close($ch);

if (json_decode($result) !=NULL)
{
    $rows = json_decode($result);

    foreach ($rows as $keyValue=>$memberAttrib)
    {

        foreach ($memberAttrib as $ma) {

            $ins = "INSERT INTO `$dbname`.`$table` (\n";
            $val= ")VALUES(\n";
            $dup= ") ON DUPLICATE KEY UPDATE\n\tsyncDate='$now',\n";
            $ins=$ins."\t`$keyField`,\n\tsyncDate,\n";
            $val=$val."\t:$keyField,\n\t'$now',\n";
            $binding = array(":$keyField"=>"$keyValue");

            foreach ($ma as $field => $value) {

                $ins=$ins."\t`$field`,\n";
                $val=$val."\t:$field,\n";
                $dup=$dup."\t`$field`=VALUES(`$field`),\n";
                if ($field=="isTraveltest")
                {
                $binding[":$field"]=1;
                }
                else
                {
                $binding[":$field"]=$value;
                }

            }

            $ins=substr($ins,0,-2)."\n";
            $val=substr($val,0,-2)."\n";
            $dup=substr($dup,0,-2).";\n";
            $sql="$ins$val$dup\n";

            try {
                $sth = $testDB->prepare($sql);
                $sth->execute($binding);
            }
            catch(PDOException $e)
            {
                //var_dump($sql);
                //var_dump($binding);
                echo $e->getMessage();
                //die;
            }

        }

    }

    //only do deletions if we're doing a full sync
    //and not for the teammember or positionmember tables
    if ($syncAll && $table != 'positionmember' && $table != 'teammember') {
        try
        {
            $sql = "DELETE FROM $dbname.$table WHERE syncDate < '$now';";
            $testDB->exec($sql);
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            die;
        }
    }

    //re-sync the memberIds to the teammember and positionmember tables
    if ($table == 'member') {
        try {
            $sql = "UPDATE $dbname.positionmember pm, $dbname.member m SET pm.deleted = 1 WHERE pm.memberId = m.memberId AND m.deleted=1;";
            $testDB->exec($sql);
            $sql = "UPDATE $dbname.teammember tm, $dbname.member m SET tm.deleted = 1 WHERE tm.memberId = m.memberId AND m.deleted=1;";
            $testDB->exec($sql);
            $sql = "DELETE from $dbname.positionmember WHERE deleted=1;";
            $testDB->exec($sql);
            $sql = "DELETE from $dbname.teammember WHERE deleted=1;";
            $testDB->exec($sql);
            $sql = "UPDATE $dbname.positionmember pm, $dbname.member m SET pm.memberId = m.memberId WHERE pm.member = m.member AND m.deleted=0 AND pm.deleted=0;";
            $testDB->exec($sql);
            $sql = "UPDATE $dbname.teammember tm, $dbname.member m SET tm.memberId = m.memberId WHERE tm.member = m.member AND m.deleted=0 AND tm.deleted=0;";
            $testDB->exec($sql);
        } catch(PDOException $e) {
            echo $e->getMessage();
            die;
        }
    }
}
else
{
    echo "BAD JSON - ";
}
echo "SYNCED::$table\n";
}


?>
