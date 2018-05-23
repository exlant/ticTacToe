<?php

namespace Project\Exlant\mail;
use Swift_Message;
use Swift_SmtpTransport;
use Swift_Mailer;
use Project\Exlant\mail\mailEntity;
require_once '/var/www/tictactoe.pp.ua/public/Project/swiftmailer/lib/swift_required.php';

class mail extends mailEntity
{
    public function __construct($name, $body = null, $subject = null, $mailCallBack = null) 
    {
        if($body){
            $this->setBody($body);
        }
        if($subject){
            $this->setSubject($subject);
        }
        if($mailCallBack){
            $this->setMailCallBack($mailCallBack);
        }
        $this->setName($name)
             ->setConfig();
        parent::__construct();
    }
    
    public function sendMessage()
    {
        $config =  $this->getConfig();
        if(!$config){
            return false;
        }
        $transport = Swift_SmtpTransport::newInstance(
                $config['server'], 
                $config['port'], 
                $config['protocol']
            )
                ->setUsername($config['mail'])
                ->setPassword($config['pass']);
        
        $mailer = Swift_Mailer::newInstance($transport);
        $message = Swift_Message::newInstance()
                ->setSubject($this->getSubject())
                ->setFrom(array($config['mail'] => $config['name']))
                ->setTo(array($config['sendTo']['mail'] => $config['sendTo']['name']))
                ->setBody($this->getMergeBody())
                ;
        return $mailer->send($message);
    }
}
