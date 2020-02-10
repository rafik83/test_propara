<?php

/*
 * This file is part of the fulldon project
 *
 * (c) SAMI BOUSSACSOU <boussacsou@intersa.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BackBundle\Twig;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ThemeExtension extends \Twig_Extension {

    private $client;

    public function __construct(EntityManager $em, $container, $client) {
        $this->em = $em;
        $this->client = $client;
        $this->container = $container;
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('get_theme', array($this, 'themeFilter')),
            new \Twig_SimpleFilter('change_color', array($this, 'colorFilter')),
            new \Twig_SimpleFilter('is_waiting_sign', array($this, 'isWaitingSign')),
            new \Twig_SimpleFilter('get_stats', array($this, 'getStats')),
            new \Twig_SimpleFilter('get_salary', array($this, 'getSalary')),
            new \Twig_SimpleFilter('authorize_user', array($this, 'authorizeUser')),
        );
    }

    public function themeFilter($val) {
        $repCause = $this->em->getRepository('BackBundle:Personnalisation');
        $theme = $repCause->find(1);
        if (is_object($theme)) {
            if ($val == 'logo') {
                return $theme->getLogo();
            } elseif ('couleur') {
                return $theme->getCouleur();
            }
        } else {
            if ($val == 'logo') {
                return '';
            } elseif ('couleur') {
                return '#000';
            }
        }
    }

    public function getSalary($matricule, $company = null) {
        $repSalary = $this->em->getRepository('FrontBundle:Salary');
        $repCompany = $this->em->getRepository('FrontBundle:Company');


        if (is_null($company)) {
            $salary = $repSalary->findOneBy(array('matricule' => $matricule));
        } else {
            $myCompany = $repCompany->find($company);
            $salary = $repSalary->findOneBy(array('matricule' => $matricule, 'company' => $myCompany));
        }
        if (is_object($salary)) {
            return $salary->getFullName();
        } elseif (is_null($salary)) {
            return false;
        } else {
            return 'Choisir une entreprise';
        }
    }

    public function authorizeUser($var) {
        $Role = $this->em->getRepository('FrontBundle:Role')->find(4);
        if ($Role) {
            $responsable = $this->em->getRepository('FrontBundle:Responsable')->findOneBy(array('role' => $Role));
            if (count($responsable)) {
                return true;
            } else {
                return false;
            }
        }
//        $responsable = $this->em->getRepository('FrontBundle:Responsable')->findOneBy(array('role' => $this->em->getRepository('FrontBundle:User')->find($var)));
    }

    public function isWaitingSign($var) {

        $repDocs = $this->em->getRepository('FrontBundle:Document');
        $salary = $this->em->getRepository('FrontBundle:Salary')->findOneBy(array('user' => $this->em->getRepository('FrontBundle:User')->find($var)));
        $sdocs = $repDocs->findBy(array('visibility' => true, 'specialDoc' => true, 'specialSigned' => false, 'salary' => $salary));
        if (count($sdocs)) {
            return true;
        } else {
            return false;
        }
    }

    public function colorFilter($hex, $percent) {
        $hash = '';
        if (stristr($hex, '#')) {
            $hex = str_replace('#', '', $hex);
            $hash = '#';
        }
        /// HEX TO RGB
        $rgb = array(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
        //// CALCULATE
        for ($i = 0; $i < 3; $i++) {
            // See if brighter or darker
            if ($percent > 0) {
                // Lighter
                $rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1 - $percent));
            } else {
                // Darker
                $positivePercent = $percent - ($percent * 2);
                $rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1 - $positivePercent));
            }
            // In case rounding up causes us to go to 256
            if ($rgb[$i] > 255) {
                $rgb[$i] = 255;
            }
        }
        //// RBG to Hex
        $hex = '';
        for ($i = 0; $i < 3; $i++) {
            // Convert the decimal digit to hex
            $hexDigit = dechex($rgb[$i]);
            // Add a leading zero if necessary
            if (strlen($hexDigit) == 1) {
                $hexDigit = "0" . $hexDigit;
            }
            // Append to the hex string
            $hex .= $hexDigit;
        }
        return $hash . $hex;
    }

    public function getName() {
        return 'multisign_theme';
    }

    public function getStats($var) {
        return $this->container->get('fulloffice.stat')->getStats();
    }

}
