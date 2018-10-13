<?php
namespace SeguridadBundle\Services;

class Helpers{
    public $manager;

    public function __construct($manager){
        $this->manager=$manager;
    }

    public function json($data){
        $normalizers = array(new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer());
        $encoders = array("json" => new \Symfony\Component\Serializer\Encoder\JsonEncoder());

        $serializer = new \Symfony\Component\Serializer\Serializer($normalizers,$encoders);

        $json = $serializer->serialize($data,'json');

        $responde = new \Symfony\Component\HttpFoundation\Response();

        $responde->setContent($json);
        $responde->headers->set('Content-Type','application/json');
        return $responde;


    }
}