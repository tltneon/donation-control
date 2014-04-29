<?php
/*
CREATE TABLE IF NOT EXISTS `donations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `steamId` varchar(24) NOT NULL,
  `itemId` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `steamId` (`steamId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
*/


define('NineteenEleven', TRUE);
require_once'../includes/config.php';
require_once '../includes/class_lib.php';
$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB);
$tools = new tools;
$ConvertID = new SteamIDConvert;
$sq = new SteamQuery;
$log = new log;
$sb = new SourceBans;
$notes = "Key Donation"; //what to add in the notes column of DC when key is traded
$keyId = "5021"; //valve item id for key
$keyVal ='1.50'; //what you value a key at, for accounting in DC.
$keyDays = "7"; //how many days of perks per key
$now = date('U');


function sendmail($subject, $mail_body){
	global $mail;
	if(sys_email){
	    $mailHeader = "From: ". $mail['name'] . " <" . $mail['email'] . ">\r\n";

	    if ($mail['useBCC']) {
	        $to = $mail['recipient'] .', ' . $mail['BCC'];
	    }else{
	        $to = $mail['recipient'];
	    } 
	    @mail($to, $subject, $mail_body, $mailHeader);
	}
}


$sql = "SELECT * FROM `donations` WHERE `processed` = '0';";

$result = $mysqli->query($sql) or die($mysqli->error ." ". $mysqli->errono);

while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
	if(!$idArray = $ConvertID->SteamIDCheck($row['steamId'])){
		$log->logBot("Failed to match Steam ID: " . $row['steamId']);
		continue;
	}
	$itemId = $row['itemId'];
	$donateTime = strtotime($row['timestamp']);
	$donorInfo = $sq->GetPlayerSummaries($idArray['steamID64']);
	$donorName = $donorInfo->response->players[0]->personaname;

	if ($itemId==$keyId) {
		$log->logBot("$donorName (".$idArray['steamid'].") donated one key ");
	}else{
		$log->logBot("Recieved item $itemId from $donorName. Exiting script, not action taken.");
		continue;
	}

	//check Donation Control database for current donor.
	$DCsql = "SELECT * FROM `donors` WHERE `steam_id`='". $idArray['steamid'] ."';";
	$DCresult = $mysqli->query($DCsql);

	if ($DCresult->num_rows >= 1) {
		//current donor
		$log->logBot("Found {$donorName} in Donations Control database");
		$DCdonor = $DCresult->fetch_array(MYSQLI_ASSOC);
		$DCtier = $DCdonor['tier'];
		$DCstatus = $DCdonor['activated']; //1 = active 2 = inactive
		$total_amount = $keyVal + $DCdonor['total_amount'];

		if ($DCtier == 1 && $DCstatus == 2) {
	    		$expiration_date = strtotime("+" . $keyDays . " days",$now);
	    		$sbNew = true;
		}elseif($DCtier == 1 && $DCstatus == 1){
				$expiration_date = strtotime("+" .$keyDays . " days",$DCdonor['expiration_date']);
				$sbNew = false;
		}else{

                $subject = "[TRADE BOT] PROBLEM PROCESSING KEY DONATION";
                $mail_body = "{$donorName}:".$idArray['steamid']." is marked as ". $group2['name'] ." in the Donations Control database, this will cause a conflict with the trade bot.\r\n No perks have been granted, please either return the key or manually enter the donation!";
                sendmail($subject,$mail_body);
                $log->logBot("{$donorName}:".$idArray['steamid']." is marked as ". $group2['name'] ." in Donataions Control, EXITING...NO ACTION TAKEN.");
                continue;
		}
			$sql = "UPDATE `donors` SET `activated` = '1', `total_amount` = '$total_amount', `current_amount` = '$keyVal', `notes` = '$notes',`expiration_date` = '$expiration_date' WHERE `steam_id` = '".$idArray['steamid']."';";
		    echo "current donor<br />";
		    echo $sql;
	}else{
		//make new donor in Donations Control.

		$expiration_date = strtotime("+" . $keyDays . " days",$now);
		$sql = "INSERT INTO `donors` (username,
										steam_id,
										sign_up_date,
										current_amount,
										total_amount,
										expiration_date,
										steam_link,
										notes,
										activated,
										tier) 
										VALUES 
										('{$donorName}',
										'".$idArray['steamid']."',
										'{$now}',
										'{$keyVal}',
										'{$keyVal}',
										'{$expiration_date}',
										'".$idArray['steam_link']."',
										'{$notes}',
										'1',
										'1');";
		$sbNew = true;
	}
	$mysqli->query($sql) or die($mysqli->error." ".$mysqli->errno);
		if ($sbNew) {
			if($sb->addDonor($idArray['steamid'], $donorName, '1')){
				//$sb->queryServers('sm_reloadadmins');	
			}else{
			    $subject = "[TRADE BOT] Sourcebans insertion failed.";
				$mail_body = "I was able to add {$donorName}:".$idArray['steamid']." to the Donations Control database, but was unable to insert them into sourcebans, manual action nessicary.";
				sendmail($subject,$mail_body);
				$log->logBot($mail_body);
				continue;
			}
		}
		
	$mysqli->query("UPDATE `donations` SET `processed` = 1 WHERE id ='".$row['id']."';");

    $subject = "[TRADE BOT] New key donation from {$donorName}";
    $mail_body = "{$donorName}:".$idArray['steamid']." has traded a key to me, and their perks have been automatically activated."; 
    sendmail($subject,$mail_body);
    $log->logBot($mail_body);
    $log->logBot("-------------------------------------------------------------------------");
    $log->logAction("AUTOMAIC ACTION: {$donorName}:".$idArray['steamid'].": Traded 1 key");
}


