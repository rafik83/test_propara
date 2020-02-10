<?php
/*
 * This file is part of the fulldon project
 *
 * (c) SAMI BOUSSACSOU <boussacsou@intersa.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace BackBundle\Command;

use BackBundle\Entity\LogMu;
use BackBundle\Entity\LogZip;
use BackBundle\Entity\PrintDepots;
use BackBundle\Entity\Stat;
use BackBundle\Entity\ZipFile;
use FrontBundle\Entity\Company;
use FrontBundle\Entity\Role;
use FrontBundle\Entity\Salary;
use FrontBundle\Entity\SignedDoc;
use FrontBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Validator\Constraints\DateTime;


class ArchiveCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('archive:sign')
            ->setDescription('Archive des signatures')
            ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $this->getContainer()->get('doctrine')->getManager();
        $repSalary = $db->getRepository('FrontBundle:Salary');
        $repSignedDoc = $db->getRepository('FrontBundle:SignedDoc');
        $repDocument = $db->getRepository('FrontBundle:Document');
        $repCat = $db->getRepository('FrontBundle:Category');
        // Get all salaries
        $allSalaries = $repSalary->findAll();
        // create a directory for each salary
        $archiveDir = '/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/archives/';

        if(!is_dir($archiveDir)){
            mkdir($archiveDir, 0777, true);
        }
        $archiveDirTmp = $archiveDir . 'tmp/';

        if(!is_dir($archiveDirTmp)){
            mkdir($archiveDirTmp, 0777, true);
        }
        foreach($allSalaries as $salary) {

            $salaryDir = $archiveDirTmp.str_replace(' ','_',str_replace('\'','',trim($salary->getCompany()->getNom()))).'/'.$salary->getMatricule().'/';

            if(!is_dir($salaryDir)){
                mkdir($salaryDir, 0777, true);
            }
            $bulletinDir = $salaryDir.'bulletins/';
            if(!is_dir($bulletinDir)){
                mkdir($bulletinDir, 0777, true);
            }
            $documentDir = $salaryDir.'documents/';
            if(!is_dir($documentDir)){
                mkdir($documentDir, 0777, true);
            }
            $signedDoc = $repSignedDoc->findBy(array('salary' => $salary));
            foreach($signedDoc as $sd) {
                $certSign = $this->getContainer()->get('cert_sign.server');
                $data = $certSign->getDocument($sd->getSignature(), $sd->getRecord());
                $etatdoc = 1;
                if($sd->getObsolete())
                {
                    $etatdoc = 0;
                }
                $fileName = $sd->getMonth().'_'.$sd->getYear().'_'.$etatdoc.'_'.uniqid().'.pdf';
                $fileLocation = $bulletinDir . $fileName;
                $fp = fopen($fileLocation, 'wb');
                //utf8encoding
                file_put_contents($fileLocation,$data);
                fclose($fp);
            }
            // save the documents
            $documents = $repDocument->findBy(array('salary' => $salary));
            foreach($documents as $doc) {
                $data = file_get_contents('/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/' . $doc->getDoc());
                $categorie = $doc->getCategory()->getCode();
                $type='origin';
                if($doc->getSpecialSigned()) {
                    $type ='signed';
                }
                $fileName = $type.'_'.$doc->getDoc();
                $catDir = $documentDir . $categorie.'/';
                if(!is_dir($catDir)){
                    mkdir($catDir, 0777, true);
                }
                $fileLocation = $catDir.$fileName;
                $fp = fopen($fileLocation, 'wb');
                //utf8encoding
                file_put_contents($fileLocation,$data);
                fclose($fp);
            }

        }
        $filenamezip = 'Archive'.'-'.date('d-m-Y-H-i-s').'.zip';
        // Get real path for our folder

        $rootPath = realpath($archiveDirTmp);

        // Initialize archive object
        $zip = new \ZipArchive();
        $zip->open($archiveDir.$filenamezip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
        // delete the directory archive
        $this->deleteDirectory($archiveDirTmp);
    }
    private function  deleteDirectory($directory)
    {
        if (!$dh = @opendir($directory)) {
            return false;
        }
        while ($file = readdir($dh)) {
            if ($file == "." || $file == "..") {
                continue;
            }
            if (is_dir($directory . "/" . $file)) {
                $this->deleteDirectory($directory . "/" . $file);
            }
            if (is_file($directory . "/" . $file)) {
                unlink($directory . "/" . $file);
            }
        }
        closedir($dh);
        rmdir($directory);
    }
}