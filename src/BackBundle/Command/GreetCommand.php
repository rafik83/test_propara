<?php

namespace BackBundle\Command;

use BackBundle\Entity\LogMu;
use BackBundle\Entity\LogZip;
use BackBundle\Entity\PrintDepots;
use BackBundle\Entity\Stat;
use BackBundle\Entity\ZipFile;
use FrontBundle\Entity\Company;
use FrontBundle\Entity\Role;
use FrontBundle\Entity\Salary;
use BackBundle\Entity\Bu;
use FrontBundle\Entity\RhUser;
use FrontBundle\Entity\User;
use FrontBundle\Entity\ActivateSalary;
use FrontBundle\Entity\SignedDoc;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Finder\SplFileInfo;





class GreetCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('demo:greet')
                ->setDescription('Changement password all fulloffice')
                ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $db = $this->getContainer()->get('doctrine')->getManager();
        $repZip = $db->getRepository('BackBundle:ZipFile');
        $repSalary = $db->getRepository('FrontBundle:Salary');
        $repDocSign = $db->getRepository('FrontBundle:SignedDoc');
        $rep = $db->getRepository('BackBundle:Bu');
        $repMu = $db->getRepository('BackBundle:MassUpload');
        $repImport = $db->getRepository('BackBundle:Import');
        $repCompany = $db->getRepository('FrontBundle:Company');
        $repUser = $db->getRepository('FrontBundle:User');

//        $output->writeln("<-----------Debut Update Admin user--------->");
//        $UserEdit = $db->getRepository('FrontBundle:User')->find(1);
//        $plainPassword = $this->generatePassword(8);
//        $output->writeln(" password for edit user");
//        $output->writeln(var_dump($plainPassword));
//        $salt = uniqid(mt_rand());
//        $UserEdit->setSalt($salt); // Unique salt for user
//        // Set encrypted password
//        $encoder = $this->getContainer()->get('security.encoder_factory')->getEncoder($UserEdit);
//        $password = $encoder->encodePassword($plainPassword, $salt);
//        $UserEdit->setPassword($password);
//        $db->persist($UserEdit);
//        $db->flush();
//        $output->writeln("Update  user with success");
//        $output->writeln("<--------------Fin Update Admin user--------------->");
        
        $cc = $db->getRepository('FrontBundle:Role')->findOneBy(array('role' => 'ROLE_RH_LIMITE'));
        $user = $db->getRepository('FrontBundle:User')->find();
        
        var_dump($cc);
        die('icici');
        
        $role = new Role();
        $role->setName('RH_WRITE');
        $role->setRole('ROLE_RH_LIMITE');
        $role->setDesc('');
        $db->persist($role);
        $db->flush();
        

        


        $user = new User();
        $userRh = new RhUser();
        $user->setUsername('admin');
        $plainPassword = $this->generatePassword(8);
        var_dump('pwd');
        var_dump($plainPassword);//WtdBfod2
        $user->setIsActive(true);
        $user->setSalt(uniqid(mt_rand())); // Unique salt for user
        $user->setIsActive(true);
        $user->addRole($db->getRepository('FrontBundle:Role')->findOneBy(array('role' => 'ROLE_ADMIN')));
        // Set encrypted password
        $encoder = $this->getContainer()->get('security.encoder_factory')
                ->getEncoder($user);
        $password = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($password);
        $userRh->setEmail('turki@intersa.fr');
        $userRh->setNom('admin');
        $userRh->setPrenom('admin');
        $userRh->setUser($user);

        $db->persist($userRh);
        $db->flush();
        
        
        
        
        
        
        
        
        
        
        
        
        




        $output->writeln("<------debut Modification username with num secu-------->");

        $AllSalaryUser = $this->UpdateUserNameWithNumSecu();

        foreach ($AllSalaryUser as $key => $value) {
            $user_id = $value['user_id'];
//            if ($user_id == 94) {
                $ObjUser = $repUser->find($user_id);
                if ($ObjUser) {
                    $num_secu = $value['num_secu'];
                    $first13 = substr($num_secu, 0, 13); //setUsername
                    $ObjUser->setUsername($first13);
                    //$db->persist($ObjUser);
                    $db->flush($ObjUser);
                }
//            }
        }

        die('fin');

        $output->writeln("<------end Modification username with num secu-------->");
        die('fin');

        $output->writeln("<------debut Modification username with num secu-------->");

        $AllSalary = $this->RequetteAllUserWithIdNotEgals1();



//        $output->writeln("<------debut Modification zipcode + ville-------->");
//        $AllSalary = $this->RequetteAllSalaryWithVilleZipCodeNotNull();


        foreach ($AllSalary as $key => $value) {

            //$output->writeln(var_dump($value['ville']));
            $userid = $value['user_id'];
            $numsecu = $value['num_secu'];
            // user id =2 === > numsecu = 2700467482570 65

            $User = $repUser->find($userid);
            if ($User) {
//                $output->writeln("<------ num secu-------->");
//                $output->writeln(var_dump($numsecu));
//                $output->writeln("<------Username-------->");
//                $output->writeln(var_dump($User->getUsername()));
                $first13 = substr($numsecu, 0, 13);
//                $output->writeln(var_dump($first13));

                
                $User->setUsername($first13);

                $db->persist($User);
                $db->flush($User);
//                die('herre');
            }
        }
//        $output->writeln("<------end Modification zipcode + ville-------->");
        $output->writeln("<------end Modification username with num secu-------->");
    }

    public function UpdateUserNameWithNumSecu() {

//        $query_ref = "AND  dnt.ref_donateur ='" . $value . "'  ";

        $sql = "SELECT s.id,u.id as user_id,s.num_secu,u.username,s.nom,s.prenom,s.matricule,s.company_id FROM 3cexternpaie_fulloffice_db.salary s

left join 3cexternpaie_fulloffice_db.user u on s.user_id = u.id



where u.username  LIKE '%a%' or  '%b%' or  '%c%' or  '%d%' or  '%e%' or  '%f%' or  '%g%' or  '%h%' or  '%i%' or  '%j%' 

or  '%k%' or  '%l%' or  '%m%' or  '%n%' or  '%o%' or  '%p%' or  '%q%' or  '%r%' or  '%s%' or  '%t%' or  '%u%' or  '%v%'or  '%w%' or  '%x%' or  '%y%' or  '%z%' ";



        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function RequetteAllUserWithIdNotEgals1() {
        $sql = "SELECT user_id,num_secu FROM 3cexternpaie_fulloffice_db.salary";

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function RequetteAllSalaryWithVilleZipCodeNotNull() {
        $sql = "SELECT id as id,user_id as user_id,company_id as company_id,matricule as matricule, num_secu as num_secu,  nom as nom , prenom as prenom , ville as ville , zipcode as zipcode FROM 3cexternpaie_fulloffice_db.salary

where ville is not NULL and zipcode is not NULL";

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function generatePassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

}
