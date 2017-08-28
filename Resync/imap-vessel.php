<?php

/*
 * Imap
 * @author Abhijit <abhijagtap73@gmail.com> * 
 */

Class imap {

    private $mailbox;
    public $charset = "US-ASCII";
    public $htmlmsg;
    public $plainmsg;
    public $attachments = array();
    public $fromaddress;
    public $toaddress;
    public $subject;
    public $Date;
    public $MailDate;
    public $savedir = __DIR__ . '/imap-dump/';
    public $save_into_db = TRUE;

    /*
     * database details
     */
    public $serverIP = "localhost";
    public $userName = "****";
    public $userPassword = "********";
    public $databaseName = "db_name";

    public function __construct($authhost, $email, $emailPassword) {

        $stream = imap_open($authhost, $email, $emailPassword);
        if (FALSE === $stream) {
            throw new Exception('Connect failed: ' . imap_last_error());
        }
        $this->mailbox = $stream;
        /*$boxes = imap_list($stream, $authhost, '*');
        echo "<pre>";
        print_r($boxes);
        die;*/
    }

    public function getMailbox() {
        return $this->mailbox;
    }
    
    public function gethtml() {
        return $this->htmlmsg;
    }

    public function getLastThred($relationArr) {
        foreach ($relationArr as $key => $thred) {
            echo $relationArr[$key]['num'];
            echo '<br>';
        }
    }

    public function fetch($uid) {
        echo '<pre>';
        $result = imap_fetch_overview($this->mailbox, "$uid:*", FT_UID);
        if (FALSE === $result) {
            throw new Exception('Search failed: ' . imap_last_error());
        }
        foreach ($result as $data) {
            $data->message_id = htmlspecialchars($data->message_id);
            $data->in_reply_to = htmlspecialchars($data->in_reply_to);
        }
        print_r($result);
        die;
    }

    public function thread() {
        echo '<pre>';
        $mails = imap_thread($this->mailbox, 1);
        if (FALSE === $mails) {
            throw new Exception('Search failed: ' . imap_last_error());
        }
        foreach ($mails as $key => $thred) {
            $tempArr = explode('.', $key);
            $index = $tempArr[0];
            $key = $tempArr[1];
            $relationArr[$index][$key] = $thred;
        }return $relationArr;
        //print_r($relationArr);
        die;
    }
    
     public function fetchbody($msg_number, $part_number) {
        echo '<pre>';
        $mails = imap_fetchbody($this->mailbox, $msg_number, $part_number); 
        if (FALSE === $mails) {
            throw new Exception('Search failed: ' . imap_last_error());
        }       
        print_r($mails);
        die;
    }

    public function sendreply($uid, $body) {
        $gmail_address = 'amit.prospus@gmail.com';
        $to = 'kunalprospus@gmail.com';
        $subject = 'Re: One';
        $body = 'Teststetstetstesttsetstetstestetstetsetstetsetsetstestetst';
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: <' . $gmail_address . '>' . " \r\n" .
                'Reply-To:  <' . $gmail_address . '>' . "\r\n" .
                'Subject: ' . $subject . "\r\n" .
                'To: ' . $to . "\r\n" .
                'In-Reply-To: <CAADyZMC6MoD9m1R1j-G6itAH=vYQaai3CLDiFwHGmbQaEAL37Q@mail.gmail.com>' . "\r\n" .
                'References:  <CAL8184ginjqRduwOCmsrroOqEfJ+quVFZC2ED=52gKrMoDkkmQ@mail.gmail.com> <CAADyZMCivhz9OZU+-MGro+aOMvOxMLzZyQw0PY45jP7pyX70zA@mail.gmail.com> <CAL8184jbOsCiK10-1BgRa=JPzTNbzKnj=CDy3tu+yBB1BzAwDQ@mail.gmail.com> <CAADyZMCsmTi9m1mjU84LtEHeWOSn-Keem77Bsgu6-j+565Jocg@mail.gmail.com> <CAL8184iNbzyzpc0EK-ju2dL8xE0KyuQN7RtptYkpaS060ex00Q@mail.gmail.com>' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

        $result = imap_mail($to, $subject, $body, $headers);
        var_dump($result);
    }

    /**
     * @param string $criteria
     * @param int $options
     * @param int $this->charset
     * @return IMAPMessage[]
     * @throws Exception
     */
    public function search($criteria, $options = NULL, $charset = NULL) {
        $mails = imap_search($this->mailbox, $criteria, SE_UID, $this->charset);
        if (FALSE === $mails) {
            throw new Exception('Search failed: ' . imap_last_error());
        }
        if ($this->save_into_db) {
            $sql = new MySQLiQuery($this->serverIP, $this->userName, $this->userPassword, $this->databaseName);
        }
        foreach ($mails as $mail) {
            echo '$mail UID - ' . $mail . '<br>';
            $attachments_temp = array();
            $this->getmsg($this->mailbox, $mail);
            foreach ($this->attachments as $key => $value) {
                $savepath = $this->savedir . $key;
                file_put_contents($savepath, $value);
                $attachments_temp[] = $savepath;
            }
            if ($this->save_into_db) {
                $queryResults = $sql->insert(
                        "mails", array("msg_uid", "charset", "fromaddress", "toaddress", "Date", "MailDate", "subject", "htmlmsg", "plainmsg", "attachments"), array($mail, $this->charset, $this->fromaddress, $this->toaddress, date("Y-m-d H:i:s", strtotime($this->Date)), date("Y-m-d H:i:s", strtotime($this->MailDate)), $this->subject, $this->htmlmsg, $this->plainmsg, json_encode($attachments_temp)), MySQLiQuery::HIGH_PRIORITY
                );
                echo $queryResults . " mail successfully saved in to db:)";
            }

            echo "--------------------------------------------" . "<br/>";
            echo "Email Charset :" . $this->charset . "<br/>";
            echo "Date :" . $this->Date . "<br/>";
            echo "MailDate :" . $this->MailDate . "<br/>";
            echo "From :" . $this->fromaddress . "<br/>";
            echo "To :" . $this->toaddress . "<br/>";
            echo "Subject :" . $this->subject . "<br/>";
            if ($this->plainmsg != "") {
                echo "Plain Message" . $this->charset . "<br/>";
            }
            if ($this->htmlmsg != "") {
                echo "HTML Message" . $this->htmlmsg . "<br/>";
            }
            if (!empty($this->attachments)) {
                echo "Attachments :";
                foreach ($this->attachments as $key => $value) {
                    echo $key . "<br/>";
                }
            }

            echo "--------------------------------------------" . "<br/>";
        }
    }

    public function getmsg($mbox, $mid) {
        // HEADER
        $mid = imap_msgno($mbox, $mid);
        $h = imap_header($mbox, $mid);
        // added code here to get date, from, to, cc, subject...
        $this->fromaddress = $h->fromaddress;
        $this->toaddress = $h->toaddress;
        $this->subject = $h->subject;
        $this->Date = $h->Date;
        $this->MailDate = $h->MailDate;

        // BODY
        $s = imap_fetchstructure($mbox, $mid);
        if (!$s->parts)  // simple
            $this->getpart($mbox, $mid, $s, 0);  // pass 0 as part-number
        else {  // multipart: cycle through each part
            foreach ($s->parts as $partno0 => $p)
                $this->getpart($mbox, $mid, $p, $partno0 + 1);
        }
    }

    function getpart($mbox, $mid, $p, $partno) {
        // $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple        
        // DECODE DATA
        $this->htmlmsg = '';
        $data = ($partno) ?
                imap_fetchbody($mbox, $mid, $partno) : // multipart
                imap_body($mbox, $mid);  // simple
        // Any part may be encoded, even plain text messages, so check everything.
        if ($p->encoding == 4)
            $data = quoted_printable_decode($data);
        elseif ($p->encoding == 3)
            $data = base64_decode($data);

        // PARAMETERS
        // get all parameters, like charset, filenames of attachments, etc.
        $params = array();
        if ($p->parameters)
            foreach ($p->parameters as $x)
                $params[strtolower($x->attribute)] = $x->value;
        if ($p->dparameters)
            foreach ($p->dparameters as $x)
                $params[strtolower($x->attribute)] = $x->value;

        // ATTACHMENT
        // Any part with a filename is an attachment,
        // so an attached text file (type 0) is not mistaken as the message.
        if ($params['filename'] || $params['name']) {
            // filename may be given as 'Filename' or 'Name' or both
            $filename = ($params['filename']) ? $params['filename'] : $params['name'];
            // filename may be encoded, so see imap_mime_header_decode()
            $this->attachments[$filename] = $data;  // this is a problem if two files have same name
        }

        // TEXT
        if ($p->type == 0 && $data) {
            // Messages may be split in different parts because of inline attachments,
            // so append parts together with blank row.
            if (strtolower($p->subtype) == 'plain')
                $this->plainmsg .= trim($data) . "\n\n";
            else
                $this->htmlmsg .= $data . "<br><br>";
            $this->charset = $params['charset'];  // assume all parts are same charset
        }

        // EMBEDDED MESSAGE
        // Many bounce notifications embed the original message as type 2,
        // but AOL uses type 1 (multipart), which is not handled here.
        // There are no PHP functions to parse embedded messages,
        // so this just appends the raw source to the main message.
        elseif ($p->type == 2 && $data) {
            $this->plainmsg .= $data . "\n\n";
        }

        // SUBPART RECURSION
        if ($p->parts) {
            foreach ($p->parts as $partno0 => $p2)
                $this->getpart($mbox, $mid, $p2, $partno . '.' . ($partno0 + 1));  // 1.2, 1.2.1, etc.
        }
    }

    public function close() {
        imap_expunge($this->mailbox);
        imap_close($this->mailbox);
    }
    
    public function getUniqueEmailArr($arr){
        $res = array();
        foreach($arr as $row)
        {
            if($row['branch']){
                $isreplytrue = $this->checkforreplyid($row['num']);
                if(!$isreplytrue)
                    $res[] = $row;
            }
            if($row['branch']==0 && $row['next']==0){
                    $res[] = $row;
            }
        }
        return $res;
    }
    
     public function getUniqueSentArr($arr){
        $res = array();
        if(!count($arr)){
            return $res;
        }
        foreach($arr as $row)
        {
            if($row['branch'] && $row['next'])
            {
                $isreplytrue = $this->checkforreplyid($row['num']);
                if(!$isreplytrue)
                    $res[] = $row;
            }  
            if($row['branch'] && !$row['next'])
            {
                $isreplytrue = $this->checkforreplyid($row['num']);
                if(!$isreplytrue)
                    $res[] = $row;
            }  
            if($row['branch']==0 && $row['next']==0){
                    $res[] = $row;
            }
        }
        return $res;
    }
    
    public function checkforreplyid($uid){
        $result = imap_fetch_overview($this->mailbox, "$uid", FT_UID);  
        if (FALSE === $result) {
            throw new Exception('Search failed: ' . imap_last_error());
        }
        foreach ($result as $data) {            
            $data->in_reply_to = htmlspecialchars($data->in_reply_to);
        }
        $res = 0;
        if($result[0]->in_reply_to !== '') {
            $res = 1;
        }
        return $res;
    }
    
    public function arraySearch($sentImap,$arr,$index,$val,$flag = array(),$type)
    {        
        foreach($arr as $key=>$row){
            if($row[$index]==$val){
                //print_r($row);die;
                if($row['next'] && !$row['branch']){
                    static $i =0;
                   $msg_id = $this->fetchMessageId($sentImap,$row['num'],$type); 
                   $flag[$row['num']] =  $msg_id;
                   $next = $row['next'];
                   $i++;
                   $msg_id = $this->fetchMessageId($sentImap,$arr[$next]['num'],$type); 
                   $flag[$arr[$next]['num']] =  $msg_id;
                   if($arr[$next]['next'] == 0 && $arr[$next]['branch'] == 0)
                   {
                       return $flag;
                   } else {
                       return $this->arraySearch($sentImap,$arr,$index,$arr[$next]['num'],$flag,$type);
                   }
                } else {
                $msg_id = $this->fetchMessageId($sentImap,$row['num'],$type);
                $flag[$row['num']] = $msg_id;
                for($i=$key+1;$i<$row['branch'];$i++)
                {
                    if($arr[$next]['next'] == 0 && $arr[$next]['branch'] == 0)
                   {
                       return $flag;
                   } else {
                    if($arr[$i]['branch'] == 0){
                    $msg_id = $this->fetchMessageId($sentImap,$arr[$i]['num'],$type); 
                    $flag[$arr[$i]['num']] =  $msg_id;
                    }
                   }
                }
                }
                break;
            }
        }
        return $flag;
    }
    
    public function fetchMessageId($sentImap,$uid,$type)
    {
        $res = array();
        $result = imap_fetch_overview($sentImap, "$uid", FT_UID);
        if (FALSE === $result) {
            throw new Exception('Search failed: ' . imap_last_error());
        }
        foreach ($result as $data) {            
            $data->message_id = htmlspecialchars($data->message_id);
        }
        $res['msg_id'] = $result[0]->message_id;
        $res['type'] = $type;
        $res['udate'] = $result[0]->udate;
        //$this->getmsg($sentImap, $uid);
        //$res['html'] = $this->gethtml();
        return $res;
    }
    
    public function objectArrSearch($arr,$index,$val)
    {
        $flag = 0;
        foreach($arr as $key=>$row){
            if($row->$index==$val){
                $flag = $row->uid;
                break;
            }
        }
        return $flag;
    }
}