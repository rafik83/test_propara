<?php
namespace FrontBundle\Helper;

use Symfony\Component\DependencyInjection\ContainerAware;

class LoginHelper extends ContainerAware {

    public function sendMail($title, $email, $message){
        if (isset($email) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailAdmin = $this->container->getParameter('email_admin');
            $mail = \Swift_Message::newInstance()
                ->setSubject($title)
                ->setFrom($emailAdmin)
                ->setTo($email)
                ->setBody($message)
                ->setContentType('text/html');
            $this->container->get('mailer')->getTransport()->start();
            $this->container->get('mailer')->send($mail);
            $this->container->get('mailer')->getTransport()->stop();
        }
    }
    public function sendMailAdmins($title, $email, $message){
        if (is_array($email) && count($email)  > 0) {
            $emailAdmin = $this->container->getParameter('email_admin');
            $mail = \Swift_Message::newInstance()
                ->setSubject($title)
                ->setFrom($emailAdmin)
                ->setTo($email)
                ->setBody($message)
                ->setContentType('text/html');
            $this->container->get('mailer')->getTransport()->start();
            $this->container->get('mailer')->send($mail);
            $this->container->get('mailer')->getTransport()->stop();
            
            
        }
    }
}