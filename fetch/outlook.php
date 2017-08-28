<?php
ini_set('max_execution_time', 500);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);
require_once 'imap-outlook.php';
/*
 * imap credentials goes here
 */
function sortByUdate($a, $b) {
    return $a['udate'] - $b['udate'];
}
$inboxhost = "{imap-mail.outlook.com:993/ssl}Inbox";
$senthost = "{imap-mail.outlook.com:993/ssl}Sent";
$email = "vwtester2@hotmail.com"; //dummy text. I swear i dont have typo here
$emailPassword = "Prospus2"; //dummy text. No typo here.
//$authhost = "{imap.gmail.com:993/imap/ssl}INBOX";
//$email = "careers@prospus.com"; //dummy text. I swear i dont have typo here
//$emailPassword = "carBigMoney2"; //dummy text. No typo here.
$inboxImap = new imap($inboxhost, $email, $emailPassword);
$sentImap = new imap($senthost, $email, $emailPassword);
$inboxStreamObj = $inboxImap->getMailbox();
$sentStreamObj = $sentImap->getMailbox();

// Get Thred Complete
$inboxThread = $inboxImap->thread();
$sentThread = $sentImap->thread();
//print_r($inboxThread);die;
$inboxOverview = imap_fetch_overview($inboxStreamObj,"1:*",FT_UID);
foreach($inboxOverview as $data){
    $data->message_id = htmlspecialchars($data->message_id);
    $data->in_reply_to = htmlspecialchars($data->in_reply_to);
}
$sentOverview = imap_fetch_overview($sentStreamObj,"1:*",FT_UID);
foreach($sentOverview as $data){
    $data->message_id = htmlspecialchars($data->message_id);
    $data->in_reply_to = htmlspecialchars($data->in_reply_to);
}
/*print_r($sentOverview);echo "-----------------------------------------------------------------";
print_r($inboxOverview);die;*/

$sentmail = $sentImap->getUniqueSentArr($sentThread);
foreach($sentmail as $key=>$thred)
    $relationArr[]= $thred['num'];
$tempArr = implode(',',$relationArr);
$sentEmailList = imap_fetch_overview($sentStreamObj,"$tempArr",FT_UID);
foreach($sentEmailList as $data)
    $data->message_id = htmlspecialchars($data->message_id);

$relationArr = array();
$emails = $inboxImap->getUniqueEmailArr($inboxThread);
foreach($emails as $key=>$thred)
    $relationArr[]= $thred['num'];
$tempArr = implode(',',$relationArr);
$inboxEmailList = imap_fetch_overview($inboxStreamObj,"$tempArr",FT_UID);
foreach($inboxEmailList as $data)
    $data->message_id = htmlspecialchars($data->message_id);

$finalarr = array();  
$emailList = array_merge($sentEmailList,$inboxEmailList);
foreach($emailList as $email)
{   //echo $email->uid;
   // print_r($email);echo "------------------------------------------------------";die;
    //print_r($sentThread);die;
    //$finalarr[$email->uid] = $email;
    $flag = array();
    $res = $sentImap->arraySearch($sentStreamObj,$sentThread,'num',$email->uid,$flag,'sent');
    if(count($res)){
        $msg_id = current($res)['msg_id'];
        $res1 = $inboxImap->objectArrSearch($inboxOverview,'in_reply_to',$msg_id);
        $res2 = $inboxImap->arraySearch($inboxStreamObj,$inboxThread,'num',$res1,$flag,'inbox');
        $res2 = $res+$res2;
    } else {
        $res = $inboxImap->arraySearch($inboxStreamObj,$inboxThread,'num',$email->uid,$flag,'inbox');
        if(count($res)) {
        $msg_id = current($res)['msg_id'];
        $res1 = $sentImap->objectArrSearch($sentOverview,'in_reply_to',$msg_id);
        $res2 = $sentImap->arraySearch($sentStreamObj,$sentThread,'num',$res1,$flag,'sent');
        $res2 = $res+$res2;
        }
    }
    $finalarr[$email->uid]->childs = $res2;
    //print_r($res2);die;
    /*foreach($res as $key=>$row){
            $obj = $sentImap->getMailbox();
            $sentImap->getmsg($obj,$key);
            $html = $sentImap->gethtml();
            echo $html;
            echo "kkkkkkkkkkkk";
    }
    foreach($res2 as $key=>$row){
            $obj = $inboxImap->getMailbox();
            $inboxImap->getmsg($obj,$key);
            $html = $inboxImap->gethtml();
            echo $html;
            echo "llllllllll";
    }die;*/
    //print_r($res);print_r($res2);die;print_r($res2);print_r($inboxOverview);die;
}

foreach($finalarr as $key=>$data){
    uasort($data->childs, 'sortByUdate');
}

print_r($finalarr);
die;


