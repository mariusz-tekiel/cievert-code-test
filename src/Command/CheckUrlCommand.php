<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CheckUrlCommand extends Command 
{
    protected static $defaultName = 'app:check-url';
    
    protected function configure()
    {
                
        $this           
            ->setDescription('Checks if website is till up.')
            ->addArgument('website_url', InputArgument::REQUIRED, 'What is the website url?')
            ->addArgument('website_title', InputArgument::OPTIONAL, 'What is the website title?')
            ->addArgument('customer_phone_no', InputArgument::OPTIONAL, 'What is the customer phone no?')
            ->addArgument('customer_email', InputArgument::OPTIONAL, 'What is the customer email address?')  
            ->setHelp('This command checks if given website is still up');          
        ;        
    }

    public function sendEmail(\Swift_Mailer $mailer)
    {   
        $customerEmail = $input->getArgument('customer_email');
        $message = (new \Swift_Message('Website warning message.'))
            ->setFrom('mtekiel777@gmail.com')
            ->setTo($customerEmail)
            ->setBody(
                $this->renderView('Dear Customer, we would like to inform you that your website has problem with response ')                
         
            );        
        
        $mailer->send($message);
        return;
    }

    public function sendSms(TexterInterface $texter)
    {
        $customerPhoneNo = $input->getArgument('customer_phone_no');
        $sms = new SmsMessage(
            // the phone number
            $customerPhoneNo,
            // the message
            'Dear Customer, we would like to inform you that your website has problem with response'
        );

        $sentMessage = $texter->send($sms);
        return;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $websiteUrl = $input->getArgument('website_url');

        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $websiteUrl);
                        
        if($res->getStatusCode() == 200){            
            $output->write('We confirm that website is up.  ');
            return Command::SUCCESS;

        } else {
            
            $output->write('Response unsuccessful. Website is not up. ');

            $this->sendEmail($mailer);
            $this->sendSms($texter);                        
            return Command::FAILURE;            
       }
    }
}

