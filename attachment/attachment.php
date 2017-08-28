<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    
//ini_set("SMTP","ssl://smtp.gmail.com");
//for demo purposes we are gonna send an email to ourselves
//$from = "kunalprospus@gmail.com";
//$to = "amit.prospus@gmail.com";
$to = "kunalprospus@gmail.com";
$from = "amit.prospus@gmail.com";
$envelope = array("from"=>$from,"to"=>$to);
$headers = "From: ".$from."\r\n".
           "Reply-To: ".$from."\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"PHP-mixed\"\r\n"; 
$headers .= "X-Priority: 3\r\n";
$headers .= "X-MSMail-Priority: Normal\r\n";    
$headers .= "X-Mailer: PHP/" . phpversion();
$subject = "attachment test Multiple Kunal 334";
 $filename = ["Koala.jpg"];
    $envelope["from"] = $from;
    $envelope["to"] = $to; 
    $envelope["subject"] = $subject;
    $part1["type"] = TYPEMULTIPART;
    $part1["subtype"] = "mixed";
    $part2["type"] = TYPETEXT;
    $part2["subtype"] = "plain";
    $part2["description"] = "imap_mail_compose() function";
    $part2["contents.data"] = "message 1:xxxxxxxxxxxxxxxxxxxxxxxxxx";
    $part3["type"] = TYPETEXT;
    $part3["subtype"] = "plain";
    $part3["description"] = "Example";
    $part3["contents.data"] = "message 2:yyyyyyyyyyyyyyyyyyyyyyyyyy";
    $i = 4;
    foreach($filename as $file){
    $filename = $file;
    $file_handle = fopen($filename, 'r+');
    ${"part".$i}["type"] = TYPEAPPLICATION;
    ${"part".$i}["encoding"] = ENCBASE64;
    ${"part".$i}["subtype"] = "octet-stream";
    ${"part".$i}["description"] = 'Test';
    ${"part".$i}['disposition.type'] = 'attachment';
    ${"part".$i}['disposition'] = array('filename' => $filename);
    ${"part".$i}['type.parameters'] = array('name' => $filename);
    ${"part".$i}["contents.data"] = chunk_split(base64_encode(file_get_contents($filename)));
    $i++;
    }
    $body[1] = $part1;
    $body[2] = $part2;
    $body[3] = $part3;
    for($j= 4; $j <= $i-1; $j ++){
     $body[$j] = ${"part".$j};   
    }    
    $msg = imap_mail_compose($envelope, $body);
    //list($msgheader, $msgbody) = preg_split("\r\n|\r|\n", $msg, 2);
    imap_mail($to, $subject, $msg, $headers);
    
    $mbox = imap_open("{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail", $from, "Prospus1");
    var_dump(imap_last_error());
    $mailbox = "{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail";
    if (imap_append($mbox, $mailbox, $msg) === false) {
        echo imap_last_error() . "\n";
        echo "TEST FAILED : could not append new message to mailbox '{$mailbox}'\n";
        exit;
    }
    // connect to the email account

// save the sent email to your Sent folder by just passing a string composed 
// of the entire message + headers. 
// Notice the 'r' format for the date function, which formats the date correctly for messaging.
//imap_append($mbox, "{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail",$msg);
//var_dump(imap_last_error());
//echo "Message saved to Send folder!<br />";

$some   = imap_search($mbox, 'SUBJECT "'.$subject.'"', SE_UID);
print_r($some);
imap_close($mbox);
?>