<?php
namespace BackBundle\WebServices;
/*
 * This file is part of the fulldon project
 *
 * (c) SAMI BOUSSACSOU <boussacsou@intersa.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CertSignServer
{


    private $service;
    private $profileSign;
    private $profileArc;
    private $profileSto;
    private $profileDoc;
    private $profileCon;
    private $signer;
    private $algo;


    public function __construct($localCert, $login, $password, $location, $profileCon, $profileSign, $profileArc,
                                $profileSto, $profileDoc, $signer, $authPass, $algo)
    {

        $wsdl = $location . "?wsdl";
        $this->service = new \SoapClient(
            $wsdl,
            array(
                'local_cert' => $localCert,
                'login' => $login,
                'password' => $password,
                'trace' => 1,
                'exception' => 1,
                'location' => $location
            )
        );
     //Affectation

        $this->profileSign = $profileSign;
        $this->profileArc = $profileArc;
        $this->profileDoc = $profileDoc;
        $this->profileSto = $profileSto;
        $this->profileCon = $profileCon;
        $this->signer = $signer;
        $this->algo = $algo;

    }

    public function createRecord($owner, $ownerDesc) {
        $request = new \StdClass();
        $request->recordDTO = new \StdClass();
        $request->recordDTO->contractId = $this->profileCon;
        $request->recordDTO->ownerDescription =  $ownerDesc;
        $request->recordDTO->ownerId =  $owner;
        $request->recordDTO->storageProfile =  $this->profileSto;
        $result = $this->service->createRecord($request);
        return $result;
    }
    public function addDocument($file, $record) {


        $digestValue=hash_file($this->algo,$file,TRUE);
        // create a document
        $request = new \StdClass();
        $request->requestDTO = new \StdClass();
        $request->requestDTO->recordId =  $record;
        $request->requestDTO->requestId =  '';
        $request->requestDTO->outputDocumentsAttachmentType =  'inline';
        $request->documentDTO = new \StdClass();
        $request->documentDTO->charset =  'utf-8';
        $request->documentDTO->document =   new \StdClass();
        $request->documentDTO->outputDocumentsAttachmentType =  'inline';

        $request->documentDTO->document->data = file_get_contents($file);
        $request->documentDTO->document->digestAlgdId = $this->algo;
        $request->documentDTO->document->digestAlgdValue = $digestValue;
        $request->documentDTO->document->requestAttachmentType = 'inline';
        $request->documentDTO->document->uri = basename($file);
        $request->documentDTO->documentProfile = $this->profileDoc;

        $result = $this->service->addDocument($request);

        return $result ;
    }
    public  function signDocument($record, $doc) {

        //$signatureName = 'sg-'.md5(uniqid());
        $request = new \StdClass();
        $request->requestDTO = new \StdClass();
        $request->requestDTO->recordId = $record;
        $request->signatureDTO = new \StdClass();
        $request->signatureDTO->signatureProfile = $this->profileSign;
        $request->signatureDTO->certifLevel = "NOT_CERTIFIED";
        $request->signatureDTO->mode = "refobj";
        $request->signatureDTO->encapsulatingObjectId = $doc;
        $request->signatureDTO->keepPDFACompliance = true;
        $request->signatureDTO->allocateTimeStampContainer = true;
        $request->signatureDTO->predictedSignatureSize = 1;
        $request->signatureDTO->returnUpdatedSignature = false;
        $request->signatureDTO->signatureAlreadyExists = false;
        $request->signatureDTO->visible = false;
        //$request->signatureDTO->signatureName = $signatureName;
        $request->claimedIdentity = new \StdClass();
        $request->claimedIdentity->name = $this->signer;
        $result = $this->service->signObjectPDF($request);

        return $result;
    }


    public function getSignature($signature, $record){


        $request = new \StdClass();
        $request->requestDTO = new \StdClass();
        $request->requestDTO->recordId = $record;
        $request->signatureObjectId = $signature;
        $result = $this->service->getSignature($request);
        $data = $result->getSignatureReturn->data;

        return $data;
    }
    public function getDocument($doc, $record){


        $request = new \StdClass();
        $request->requestDTO = new \StdClass();
        $request->requestDTO->recordId = $record;
        $request->documentObjectId = $doc;
        $result = $this->service->getDocument($request);
        $data = $result->getDocumentReturn->data;

        return $data;
    }
    public function archiveRecord($record) {

        $request = new \StdClass();
        $request->requestDTO = new \StdClass();
        $request->requestDTO->recordId = $record;
        $request->archivingDTO = new \StdClass();
        $request->archivingDTO->keywords = new \StdClass();
        $request->archivingDTO->archivingProfile = $this->profileArc;
        $result = $this->service->requestArchiveRecord($request);

        return $result;
    }

    public function closeRecord($record) {

        $request = new \StdClass();
        $request->requestDTO = new \StdClass();
        $request->requestDTO->recordId = $record;
        $request->operationDTO = new \StdClass();
        $request->operationDTO->archivingDTO = new \StdClass();
        $request->operationDTO->archivingDTO->archivingProfile = $this->profileArc;
        $request->operationDTO->archivingDTO->keywords = new \StdClass();
        $result = $this->service->closeRecord($request);

        $result;
    }


}