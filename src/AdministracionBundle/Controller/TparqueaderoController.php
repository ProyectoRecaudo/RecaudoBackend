<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tusuario;
use ModeloBundle\Entity\Tparqueadero;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TparqueaderoController extends Controller
{
    /**
     * @Route("/")
    */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /**
     * @Route("/new")
     * Funcion para registrar un parqueadero
     * recibe los datos en un json llamado json con los datos
     * codigoparqueadero, codigo puede ser nulo
     * numeroparqueadero, el numero de la parqueadero
     * parqueaderoactivo, si el parqueadero esta activa o no
     * fkidtipoparqueadero, el id del tipo de parqueadero al que pertenece
     * fkidplaza, el id de la plaza al que sera asignado el parqueadero
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newParqueaderoAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        
        try{

            if (!empty($request->get('authorization')) && !empty($request->get('json'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_PARQUEADEROS", $permisosDeserializados)) {
                       
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));
                        
                        //entiti manager
                        $em = $this->getDoctrine()->getManager();

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);


                        if ($json != null) {

                            $codigoParqueadero = (isset($params->codigoparqueadero)) ? $params->codigoparqueadero : null;
                            $numeroParqueadero = (isset($params->numeroparqueadero)) ? $params->numeroparqueadero : null;
                            $parqueaderoActivo = (isset($params->parqueaderoactivo)) ? $params->parqueaderoactivo : true;
                            $idTipoParqueadero = (isset($params->fkidtipoparqueadero)) ? $params->fkidtipoparqueadero : null;
                            $idPlaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null;
                            
                            if($numeroParqueadero != null && $idTipoParqueadero !=null && $idPlaza !=null){
                                $tipoParqueadero = $em->getRepository('ModeloBundle:Ttipoparqueadero')->find($idTipoParqueadero);
                                $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                
                                if($tipoParqueadero){
                                    
                                    if($plaza){

                                        $parqueaderoDuplicated = $em->getRepository('ModeloBundle:Tparqueadero')->findOneBy(array(
                                            "numeroparqueadero" => $numeroParqueadero,
                                            "fkidplaza" => $idPlaza
                                        ));

                                        if(!$parqueaderoDuplicated){
                                            $parqueadero = new Tparqueadero();
                                            $parqueadero->setCodigoparqueadero($codigoParqueadero); 
                                            $parqueadero->setNumeroparqueadero($numeroParqueadero);
                                            $parqueadero->setParqueaderoactivo($parqueaderoActivo);
                                            $parqueadero->setCreacionparqueadero($today);
                                            $parqueadero->setModificacionparqueadero($today);
                                            $parqueadero->setFkidtipoparqueadero($tipoParqueadero);
                                            $parqueadero->setFkidplaza($plaza);
                                                                    
                                            $em->persist($parqueadero);
                                            $em->flush();
                    
                                            $data = array(
                                                'status'      => 'Exito',
                                                'msg'         => 'Parqueadero creado!!',
                                                'parqueadero' => $parqueadero,
                                            );

                                            $idParqueadero = $parqueadero->getPkidparqueadero();
                                        
                                            //una vez insertados los datos en el parqueadero se realiza el proceso de auditoria
                                            $datos = array(
                                                'idusuario'             => $identity->sub,
                                                'nombreusuario'         => $identity->name,
                                                'identificacionusuario' => $identity->identificacion,
                                                'accion'                => 'insertar',
                                                "tabla"                 => 'Tparqueadero',
                                                "valoresrelevantes"     => 'idParqueadero:'.$idParqueadero.',numeroParqueadero:'.$numeroParqueadero,
                                                'idelemento'            => $idParqueadero,
                                                'origen'                => 'web'
                                            );
            
                                            $auditoria = $this->get(Auditoria::class);
                                            $auditoria->auditoria(json_encode($datos));

                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Numero de parqueadero duplicado!!'
                                            );
                                        }
                                    }else{
                                        $data = array(
                                            'status'=> 'error',
                                            'msg'   => 'El tipo de parqueadero no existe!!'
                                        );
                                    }
                                    
                                }else{
                                    $data = array(
                                        'status'=> 'error',
                                        'msg'   => 'La plaza no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El numero de parqueadero, o tipoo de parqueadero, o id plaza son nulos!!',
                                );
                            }                          
                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'Parametro json es nulo!!',
                            );
                        }
                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'No tiene los permisos!!',
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'msg'    => 'Token no valido!!',
                    );
                }
            } else {
                $data = array(
                    'status' => 'error',
                    'msg'    => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        }catch(\Exception $e){

            $trace = $e->getTrace();

            $data = array(
                'idusuario'     => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo'        => 'Parqueaderos',
                'metodo'        => $trace[0]['function'],
                'mensaje'       => $e->getMessage(),
                'tipoExepcion'  => $trace[0]['class'],
                'pila'          => $e->getTraceAsString(),
                'origen'        => 'web'
            );

            try{
                $excepcion = $this->get(Auditoria::class);
                $excepcion->exepcion(json_encode($data));

            }catch (\Exception $a){}

            throw $e;
        }
    }

    /**
     * @Route("/edit")
     * Funcion para modificar un parqueadero
     * recibe los datos en un json llamado json con los datos
     * pkidparqueadero=>obligatorio, id del parqueadero a editar
     * codigoparqueadero, codigo puede ser nulo
     * numeroparqueadero, el numero de la parqueadero
     * parqueaderoactivo, si el parqueadero esta activa o no
     * fkidtipoparqueadero, el id del tipo de parqueadero al que pertenece
     * fkidplaza, el id de la plaza al que sera asignado el parqueadero
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editParqueaderoAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        try {

            if (!empty($request->get('authorization')) && !empty($request->get('json'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_PARQUEADEROS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la parqueadero horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);


                        if ($json != null) {
                            
                            $idParqueadero = (isset($params->pkidparqueadero)) ? $params->pkidparqueadero : null;

                            if($idParqueadero != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $parqueadero = $em->getRepository('ModeloBundle:Tparqueadero')->find($idParqueadero);
                    
                                if($parqueadero){

                                    if(isset($params->fkidplaza)){
                                        $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($params->fkidplaza);
                                       
                                        if($plaza){
                                            $parqueadero->setFkidplaza($plaza);
                                            $idPlaza = $params->fkidplaza;
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La plaza no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idPlaza = $parqueadero->getFkidplaza();
                                    }
                                    
                                
                                    if(isset($params->codigoparqueadero)){
                                        $parqueadero->setCodigoparqueadero($params->codigoparqueadero);
                                    }
                                    
                                    if(isset($params->numeroparqueadero)){
                                        $numeroParqueadero = $params->numeroparqueadero;

                                        //revisa en la tabla Tparqueadero si el nombre que se desea asignar existe
                                        $query = $em->getRepository('ModeloBundle:Tparqueadero')->createQueryBuilder('p')
                                            ->where('p.numeroparqueadero = :numeroparqueadero and p.pkidparqueadero != :pkidparqueadero and p.fkidplaza = :fkidplaza')
                                            ->setParameter('numeroparqueadero', $numeroParqueadero)
                                            ->setParameter('pkidparqueadero', $idParqueadero)
                                            ->setParameter('fkidplaza', $idPlaza)
                                            ->getQuery();
                                        
                                        $parqueaderoDuplicated = $query->getResult();

                                        if(!$parqueaderoDuplicated){
                                            $parqueadero->setNumeroparqueadero($numeroParqueadero);
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Numero de parqueadero duplicado!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }

                                    if(isset($params->parqueaderoactivo)){
                                        $parqueadero->setParqueaderoactivo($params->parqueaderoactivo);
                                    }                                   
                
                                    if(isset($params->fkidtipoparqueadero)){
                                        $tipoParqueadero = $em->getRepository('ModeloBundle:Ttipoparqueadero')->find($params->fkidtipoparqueadero);
                                       
                                        if($tipoParqueadero){
                                            $parqueadero->setFkidtipoparqueadero($tipoParqueadero);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'El tipo de parqueadero no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                
                                    $parqueadero->setModificacionparqueadero($today);
                
                                    $em->persist($parqueadero);
                                    $em->flush();
                
                                    $data = array(
                                        'status' => 'Exito',
                                        'msg'    => 'Parqueadero actualizado!!',
                                        'parqueadero' => $parqueadero,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Tparqueadero',
                                        "valoresrelevantes"     => 'idParqueadero:'.$idParqueadero.',numeroParqueadero:'.$parqueadero->getNumeroparqueadero(),
                                        'idelemento'            => $idParqueadero,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'El parqueadero no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del parqueadero a editar es nulo!!'
                                );
                            }

                        }else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'Parametro json es nulo!!',
                            );
                        }

                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'No tiene los permisos!!',
                        );
                    }
                }else {
                    $data = array(
                        'status' => 'error',
                        'msg'    => 'Token no valido !!',
                    );
                }
            } else {
                $data = array(
                    'status' => 'error',
                    'msg'    => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        }catch(\Exception $e){

            $trace = $e->getTrace();

            $data = array(
                'idusuario'     => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo'        => "Parqueaderos",
                'metodo'        => $trace[0]['function'],
                'mensaje'       => $e->getMessage(),
                'tipoExepcion'  => $trace[0]['class'],
                'pila'          => $e->getTraceAsString(),
                'origen'        => "Web",
            );

            try{
                $excepcion = $this->get(Auditoria::class);
                $excepcion->exepcion(json_encode($data));
               
            }catch (\Exception $a){}

            throw $e;
        }
    }

    /**
     * @Route("/remove")
     * Funcion para eliminar un parqueadero
     * recibe los datos en un json llamado json con los datos
     * pkidparqueadero=>obligatorio, id del parqueadero a eliminar
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function removeParqueaderoAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        try {

            if (!empty($request->get('authorization')) && !empty($request->get('json'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_PARQUEADEROS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la parqueadero horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);


                        if ($json != null) {
                            
                            $idParqueadero = (isset($params->pkidparqueadero)) ? $params->pkidparqueadero : null;

                            if($idParqueadero != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $parqueadero = $em->getRepository('ModeloBundle:Tparqueadero')->find($idParqueadero);
                    
                                if($parqueadero){
                                    $numeroParqueadero = $parqueadero->getNumeroparqueadero();
                                    
                                    $tarifaParqueadero = $em->getRepository('ModeloBundle:Ttarifaparqueadero')->findOneBy(array(
                                        'fkidparqueadero' => $idParqueadero
                                    ));

                                    if(!$tarifaParqueadero){

                                        $em->remove($parqueadero);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'Exito',
                                            'msg'    => 'Parqueadero eliminado!!'
                                        );
        
                                        $datos = array(
                                            "idusuario"             => $identity->sub,
                                            "nombreusuario"         => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            'accion'                => 'eliminar',
                                            "tabla"                 => 'Tparqueadero',
                                            "valoresrelevantes"     => 'idParqueadero:'.$idParqueadero.',numeroParqueadero:'.$numeroParqueadero,
                                            'idelemento'            => $idParqueadero,
                                            'origen'                => 'web'
                                        );
        
                                        $auditoria = $this->get(Auditoria::class);
                                        $auditoria->auditoria(json_encode($datos)); 
                                    }else{
                                        $data = array(
                                            'status' => 'Error',
                                            'msg'    => 'No se puede eliminar el parqueadero, tiene tarifas asociadas!!'
                                        );
                                    }

                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'El parqueadero no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del parqueadero a eliminar es nulo!!'
                                );
                            }

                        }else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'Parametro json es nulo!!',
                            );
                        }

                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'No tiene los permisos!!',
                        );
                    }
                }else {
                    $data = array(
                        'status' => 'error',
                        'msg'    => 'Token no valido !!',
                    );
                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg'    => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        }catch(\Exception $e){

            $trace = $e->getTrace();

            $data = array(
                'idusuario'     => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo'        => "Parqueaderos",
                'metodo'        => $trace[0]['function'],
                'mensaje'       => $e->getMessage(),
                'tipoExepcion'  => $trace[0]['class'],
                'pila'          => $e->getTraceAsString(),
                'origen'        => "Web",
            );

            try{

                $excepcion = $this->get(Auditoria::class);
                $excepcion->exepcion(json_encode($data));
               
            }catch (\Exception $a){}

            throw $e;
        }
    }

    /**
     * @Route("/query")
     * Funcion para mostrar todas las parqueaderos registrados
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryParqueaderoAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        try {
            if (!empty($request->get('authorization'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    $activo = $request->get('activo', null);   
                    if($activo != null && $activo == "true"){
                    if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
                        
                        $query = "SELECT pkidparqueadero,numeroparqueadero,codigoparqueadero,fkidtipoparqueadero,nombretipoparqueadero,fkidplaza,nombreplaza,parqueaderoactivo 
                                    FROM tparqueadero 
                                    JOIN ttipoparqueadero ON tparqueadero.fkidtipoparqueadero = ttipoparqueadero.pkidtipoparqueadero
                                    JOIN tplaza ON tparqueadero.fkidplaza = tplaza.pkidplaza where parqueaderoactivo=true
                                    ORDER BY numeroparqueadero ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $parqueaderos = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($parqueaderos as $parqueadero) {
                            $parqueaderosList = array(
                                "pkidparqueadero"   => $parqueadero['pkidparqueadero'],
                                "numeroparqueadero" => $parqueadero['numeroparqueadero'],
                                "codigoparqueadero" => $parqueadero['codigoparqueadero'],
                                "tipoparqueadero"   => array("pkidtipoparqueadero" => $parqueadero['fkidtipoparqueadero'],"nombretipoparqueadero" => $parqueadero['nombretipoparqueadero']),
                                "plaza"             => array("pkidplaza" => $parqueadero['fkidplaza'],"nombreplaza" => $parqueadero['nombreplaza']),
                                "parqueaderoactivo" => $parqueadero['parqueaderoactivo'],
                            );
                            array_push($array_all, $parqueaderosList);
                        }

			            $data = array(
                            'status'    => 'Success',
                            'parqueadero'     => $array_all,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_PARQUEADEROS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        //Consulta para traer los datos de la parqueadero, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidparqueadero,numeroparqueadero,codigoparqueadero,fkidtipoparqueadero,nombretipoparqueadero,fkidplaza,nombreplaza,parqueaderoactivo 
                                    FROM tparqueadero 
                                    JOIN ttipoparqueadero ON tparqueadero.fkidtipoparqueadero = ttipoparqueadero.pkidtipoparqueadero
                                    JOIN tplaza ON tparqueadero.fkidplaza = tplaza.pkidplaza
                                    ORDER BY numeroparqueadero ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $parqueaderos = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($parqueaderos as $parqueadero) {
                            $parqueaderosList = array(
                                "pkidparqueadero"   => $parqueadero['pkidparqueadero'],
                                "numeroparqueadero" => $parqueadero['numeroparqueadero'],
                                "codigoparqueadero" => $parqueadero['codigoparqueadero'],
                                "tipoparqueadero"   => array("pkidtipoparqueadero" => $parqueadero['fkidtipoparqueadero'],"nombretipoparqueadero" => $parqueadero['nombretipoparqueadero']),
                                "plaza"             => array("pkidplaza" => $parqueadero['fkidplaza'],"nombreplaza" => $parqueadero['nombreplaza']),
                                "parqueaderoactivo" => $parqueadero['parqueaderoactivo'],
                            );
                            array_push($array_all, $parqueaderosList);
                        }

                        $cabeceras = array("Numero Parqueadero","Tipo de parqueadero","Plaza","Parqueadero Activa/Inactiva");

                        $data = array(
                            'status'       => 'Exito',
                            'cabeceras'    => $cabeceras,
                            'parqueaderos' => $array_all,
                        );

                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos !!',
                        );
                    }

                } else {
                    $data = array(
                        'status' => 'error',
                        'msg'    => 'Acceso no autorizado !!',
                    );
                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg'    => 'Envie los parametros, por favor !!',
                );
            }

            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $data = array(
                'idusuario'     => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo'        => "Parqueadero",
                'metodo'        => $trace[0]['function'],
                'mensaje'       => $e->getMessage(),
                'tipoExepcion'  => $trace[0]['class'],
                'pila'          => $e->getTraceAsString(),
                'origen'        => "Web",
            );

            try {

                $excepcion = $this->get(Auditoria::class);
                $excepcion->exepcion(json_encode($data));

            } catch (\Exception $a) {

            }

            throw $e;
        }

    }
}