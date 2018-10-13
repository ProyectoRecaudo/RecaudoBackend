<?php

namespace SeguridadBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Symfony\Component\Validator\Constraints as Assert;

class LoginController extends Controller
{
    /**
     * @Route("/login")
     */

     /* 
     Esta función valida el usuario y contraseña, para asi, obtener el token con el cual el usuario
     se va a autenticar, si es valido el usuario y contraseña, se autenticará con un token, en caso contrario,
     el sistema le informará que es incorrecto
     */
    
    public function loginAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);

        //recibir Json por Post
        $json = $request->get('json', null);
        //array para devolver por defecto
        $data = array(
            'status' => 'error',
            'msg' => 'Por favor, envie los parametros!'
        );
        if ($json != null) {

                //convertimos el json en un objeto de php
            $params = json_decode($json);

                //Nummero identificacion del usuario
            $identificacion = (isset($params->identificacion)) ? $params->identificacion : null;
            if(strlen($identificacion)>10){
                $data = array(
                    'status' => 'error',
                    'msg' => 'En identificacion solo se permiten 10 caracteres'
                );

                return $helpers->json($data);
            }
                //Contraseña del usuario
            $contrasenia = (isset($params->contrasenia)) ? $params->contrasenia : null;
                //Obtener el token con toda su informacion sin cifrado
            $getHash = (isset($params->getHash)) ? $params->getHash : null;

                //Cifrar la contraseña del usuario
            $pwd = hash('sha256',$contrasenia);
            //$pwd = $contrasenia;

            if ($identificacion != null  && $contrasenia != null) {
                //Habilitar el servicio de JWT
                $jwt_auth = $this->get(JwtAuth::class);

                if($getHash ==null || $getHash==false){
                            //Metodo para obtener el token, retorna la informacion del usuario cifrada
                        $singup= $jwt_auth->singup($identificacion,$pwd);

                }else{
                    //Metodo para obtener el token, retonra la informacion del usuario sin cifrar
                    $singup = $jwt_auth->singup($identificacion,$pwd,true);
                }

                return $this->json($singup);
            

               
            } else {
                $data = array(
                    'status' => 'error',
                    'msg' => 'Identificacion o contrasenia incorrecta'
                );

            }

        }

        return $helpers->json($data);
    }

}