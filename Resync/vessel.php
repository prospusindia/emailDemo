<?php
ini_set('max_execution_time', 500);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);
require_once 'imap-vessel.php';
/*
 * imap credentials goes here
 */
function sortByUdate($a, $b) {
    return $a['udate'] - $b['udate'];
}
$inboxhost = "{mail.vesselwise.com:143/imap/novalidate-cert}";
$senthost = "{mail.vesselwise.com:143/imap/novalidate-cert}Sent";
$email = "bhanu.bhowmik"; //dummy text. I swear i dont have typo here
$emailPassword = "Prospus1"; //dummy text. No typo here.
//$authhost = "{imap.gmail.com:993/imap/ssl}INBOX";
//$email = "careers@prospus.com"; //dummy text. I swear i dont have typo here
//$emailPassword = "carBigMoney2"; //dummy text. No typo here.
$inboxImap = new imap($inboxhost, $email, $emailPassword);
$sentImap = new imap($senthost, $email, $emailPassword);
$inboxStreamObj = $inboxImap->getMailbox();
$sentStreamObj = $sentImap->getMailbox();

// Get Thred Complete
$inboxThread1 = $inboxImap->thread();
$sentThread1 = $sentImap->thread();
//print_r($inboxThread1); die;
foreach($inboxThread1 as $key=>$data){
    if(!($data['num'] > 1454)){
        $inboxThread1[$key] = '';
    }
}
foreach($sentThread1 as $key=>$data){   
    if(!($data['num'] > 13)){
        $sentThread1[$key] = '';
    }
}
$sentThread = array_filter($sentThread1);
$inboxThread = array_filter($inboxThread1);
//print_r($inboxThread); print_r($sentThread); die;
$sentEmailList = $inboxEmailList = array();
//print_r($sentThread);die;
$inboxOverview = imap_fetch_overview($inboxStreamObj,"1454:*",FT_UID);
foreach($inboxOverview as $data){
    $data->message_id = htmlspecialchars($data->message_id);
    $data->in_reply_to = htmlspecialchars($data->in_reply_to);
}
$sentOverview = imap_fetch_overview($sentStreamObj,"13:*",FT_UID);
foreach($sentOverview as $data){
    $data->message_id = htmlspecialchars($data->message_id);
    $data->in_reply_to = htmlspecialchars($data->in_reply_to);
}
//print_r($sentOverview);echo "-----------------------------------------------------------------";
//print_r($inboxOverview);die;

$sentmail = $sentImap->getUniqueSentArr($sentThread);
if(count($sentmail)) {
    foreach($sentmail as $key=>$thred)
        $relationArr[]= $thred['num'];
    $tempArr = implode(',',$relationArr);
    $sentEmailList = imap_fetch_overview($sentStreamObj,"$tempArr",FT_UID);
    foreach($sentEmailList as $data)
       $data->message_id = htmlspecialchars($data->message_id);
}
$relationArr = array();
$tempArr = '';
//print_r($inboxThread);
$emails = $inboxImap->getUniqueEmailArr($inboxThread);
if(count($emails)) {
    foreach($emails as $key=>$thred)
        $relationArr[]= $thred['num'];
    //print_r($relationArr);
    $tempArr = implode(',',$relationArr);
    $inboxEmailList = imap_fetch_overview($inboxStreamObj,"$tempArr",FT_UID);
    foreach($inboxEmailList as $data)
        $data->message_id = htmlspecialchars($data->message_id);
}
$finalarr = array();  
$emailList = array_merge($sentEmailList,$inboxEmailList);

foreach($emailList as $email)
{   //echo $email->uid;
   // print_r($email);echo "------------------------------------------------------";die;
    //print_r($sentThread);die;
    //$finalarr[$email->uid] = $email;
    $res = $res1 = $res2 =$flag = array();
    $res = $sentImap->arraySearch($sentStreamObj,$sentThread,'num',$email->uid,$flag,'sent');
    if(count($res)){
        $msg_id = current($res)['msg_id'];
        $res1 = $inboxImap->objectArrSearch($inboxOverview,'in_reply_to',$msg_id);   
        if($res1)
        $res2 = $inboxImap->arraySearch($inboxStreamObj,$inboxThread,'num',$res1,$flag,'inbox');  
    } else {
        $res = $inboxImap->arraySearch($inboxStreamObj,$inboxThread,'num',$email->uid,$flag,'inbox');
        if(count($res)) {
        $msg_id = current($res)['msg_id'];
        $res1 = $sentImap->objectArrSearch($sentOverview,'in_reply_to',$msg_id);
        if($res1)
        $res2 = $sentImap->arraySearch($sentStreamObj,$sentThread,'num',$res1,$flag,'sent');        
        }
    }
    $res2 = $res+$res2;
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

$arraydel = array();
$keyexist = array();
foreach($finalarr as $key=>$result){    
        foreach($result->childs as $key1=>$data){
        if (array_key_exists($key1,$result->childs))
        {
         $keyexist[$key1]++;
        }
    }
}

foreach($keyexist as $key2=>$temp){
    if($temp > 1){
        $finalarr[$key2] = '';
    }
}
print_r(array_filter($finalarr));
die;
