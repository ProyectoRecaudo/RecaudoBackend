<?php
namespace SeguridadBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth{
    
    //Manager de doctrine
    public $manager;
    //Llave para generar un token
    public $key;

    public function __construct($manager){
        $this->manager = $manager;
        $this->key = "ClaveSecreta321";
    }

    public function singup($identificacion,$contrasenia,$getHash=null){
        
        //Obtener el usuario con los parametros enviados
        $user= $this->manager->getRepository('ModeloBundle:Tusuario')->findOneBy(array(
            "identificacion" => $identificacion,
            "contrasenia" => $contrasenia
        ));

        $singnup = false;

        if(is_object($user)){
            $singnup = true;
        }


        if($singnup==true){

            $usuarioBloqueado=$this->manager->getRepository("ModeloBundle:Tusuario")->findOneBy(array("identificacion"=>$identificacion,"usuarioactivo"=>"TRUE"));
        
            $idrol = $this->manager->getRepository("ModeloBundle:Trol")->find($user->getFkidrol());

          if($usuarioBloqueado){
                        $em = $this->manager;
                        $db = $em->getConnection();

                        $query = "SELECT pkidmodulo, nombremodulo, moduloactivo,icono,nombrepermiso FROM tmodulo join trolmodulo 
                        on tmodulo.pkidmodulo=trolmodulo.fkidmodulo join trol on trol.pkidrol=trolmodulo.fkidrol join tusuario on trol.pkidrol=tusuario.fkidrol where tusuario.pkidusuario=".$user->getPkidusuario()." order by nombremodulo ASC";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $modulo = $stmt->fetchAll();
                        $array_push=array();
                        foreach($modulo as $value){
                            $array_int=array("pkidmodulo"=>$value['pkidmodulo'],
                            "nombremodulo"=>$value['nombremodulo'],
                            "moduloactivo"=>$value['moduloactivo'],
                            "icono"=>$value['icono'],
                            "nombrepermiso"=>$value['nombrepermiso'],
                        );
                        array_push($array_push,$array_int);
                        }

            //Generar un token jwt
            $token = array(
                "sub" => $user->getPkidusuario(),
                "identificacion" =>$user->getIdentificacion(),
                "name" =>$user->getNombreusuario(),
                "surname" => $user->getApellido(),
                "roles"=> $idrol->getNombrerol(),
                "rutaimagen"=> $user->getRutaimagen(),
                "numerorecibo"=> $user->getNumerorecibo(),
                "permisos"=> $idrol->getPermiso(),
                "modulos"=> $array_push,
                "iat"=> time(),
                "exp" => time()+(7*24*60*60)//fecha de caducidad para el token, una semana                                     despues de crearse
            );

            //Token codificado
            $jwt = JWT::encode($token,$this->key,'HS256');
            //Token decodificado
            $decode = JWT::decode($jwt,$this->key,array('HS256'));
            if($getHash ==null){
                $data = $jwt;
            }else{
                $data = $decode;
            }
            }else{
            $data = array(
                'status' => 'error',
                'msg' => 'El usuario se encuentra desactivado, comuniquese con el administrador...'
            );
            }

           //
            }else{
                //Retornar respuesta de error
                $data = array(
                    'status' => 'error',
                    'msg' => 'Identificación o contraseña incorrecta'
                );
        
            }
            //Retorna token generado
        return $data;
    }


    public function checkToken($jwt,$getIdentity=false){
        $auth = false;

        try{
            $decode = JWT::decode($jwt,$this->key,array('HS256'));
        }catch(\UnexpectedValueException $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }


        if(isset($decode) && is_object($decode) && isset($decode->sub)){
            $auth = true;
        }else{
            $auth = false;
        }
        if($getIdentity==false){
            return $auth;
        }else{
            return $decode;
        }
    }
}


?>