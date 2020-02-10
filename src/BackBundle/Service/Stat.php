<?php
namespace BackBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAware;
use \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class Stat extends ContainerAware
{

    private $client;
    public function __construct(EntityManager $em,$client)
    {
        $this->em = $em;
        $this->client = $client;
    }

    public function getStats()
    {
        $dashbord = array();
        //Count the size in local
        $folder = '/docs_sign/'.$this->client;
        $dashbord['lsize'] = $this->filesizeFormatted( $this->foldersize($folder));
        $dashbord['lnb'] =  $this->foldernb($folder);
        //Count the size in certeurope
        $fp = $this->em->getRepository('FrontBundle:SignedDoc')
            ->createQueryBuilder('s')
            ->select("SUM(s.size) as ts, count(s.id) as nb")
            ->getQuery()
            ->getOneOrNullResult();

        $dashbord['esize'] = $this->filesizeFormatted($fp['ts']);
        $dashbord['enb'] = $fp['nb'];
        
//        var_dump($dashbord);
//        die('$dashbord');

        return $dashbord;
    }

    private function foldersize($path) {
        $total_size = 0;
        $files = scandir($path);
        $cleanPath = rtrim($path, '/'). '/';

        foreach($files as $t) {

            if ($t<>"." && $t<>".." && pathinfo($t, PATHINFO_EXTENSION) <> "csv") {
                $currentFile = $cleanPath . $t;
                if (is_dir($currentFile)) {
                    $size = $this->foldersize($currentFile);
                    $total_size += $size;
                }
                else {
                    $size = filesize($currentFile);
                    $total_size += $size;

                }
            }
        }

        return $total_size;

    }


    private function foldernb($path) {
        $total_nb = 0;
        $files = scandir($path);
        $cleanPath = rtrim($path, '/'). '/';

        foreach($files as $t) {
            if ($t<>"." && $t<>".." && pathinfo($t, PATHINFO_EXTENSION) <> "csv") {
                $currentFile = $cleanPath . $t;
                if (is_dir($currentFile)) {
                    $nb = $this->foldernb($currentFile);
                    $total_nb += $nb;
                }
                else {
                    $total_nb++;
                }
            }
        }

        return  $total_nb;
    }

    function filesizeFormatted($bytes)
    {


        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return '1 byte';
        } else {
            return '0 bytes';
        }
    }

}