<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Input\InputArgument;


class CreateCheckUrlCommand extends Command
{
    
    protected static $defaultName = 'app:check-url';

    protected function configure(): void
    {
        $this->setDescription('Checks if website is till up.');

        $this           
            ->addArgument('website_url', InputArgument::REQUIRED, 'What is the website url?')
            ->addArgument('website_title', InputArgument::OPTIONAL, 'What is the website title?')
        ;

    }
 
    public function sendEmail( \Swift_Mailer $mailer)
    {   
        $message = (new \Swift_Message('Website warning message.'))
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody(
                $this->renderView('Dear Customer, we would like to inform you that your website has problem with response ')                 
                               
            );        
        
        $mailer->send($message);

        return;
    }

    public function sendSms(TexterInterface $texter)
    {
        $sms = new SmsMessage(
            // the phone number
            '+447564676676',
            // the message
            'Dear Customer, we would like to inform you that your website has problem with response'
        );

        $sentMessage = $texter->send($sms);
        return;
    }

    protected function execute(InputInterface $input, OutputInterface $output, TexterInterface $texter, \Swift_Mailer $mailer): int
    {
           
        $websiteUrl = $input->getArgument('website_url');

        $client = new GuzzleHttp\Client();
        $res = $client->request('GET', $websiteUrl);
        
                
        if($res->getStatusCode() == 200){            
            $output->write('Status code: 200. We confirm that website is up.  ');
            return Command::SUCCESS;

        } else {
            
            $output->write('Response unsuccessful. Website is not up. ');

            $this->sendEmail($mailer);
            $this->sendSms($texter);                        
            return Command::FAILURE;            
        }
    }
    

}