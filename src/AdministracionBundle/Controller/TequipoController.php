<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tusuario;
use ModeloBundle\Entity\Tequipo;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TequipoController extends Controller
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
     * Funcion para registrar un equipo
     * recibe los datos en un json llamado json con los datos
     * codigoequipo
     * nombrequipo, el nombre del equipo
     * descripcionequipo, descripcion asociada al equipo, puede ser nulo
     * identificacionequipo, un codigo o identificacon que sea unico para el equipo
     * equipoactivo, si la equipo esta activo o no
     * fkidusuario, el id del usuario al que sera asignada el equipo, puede ser nulo
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newEquipoAction(Request $request)
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

                    if (in_array("PERM_EQUIPO_COMPUTOS", $permisosDeserializados)) {
                       
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));
                        
                        //entiti manager
                        $em = $this->getDoctrine()->getManager();

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);


                        if ($json != null) {

                            $codigoEquipo = (isset($params->codigoequipo)) ? $params->codigoequipo : null;
                            $nombreEquipo = (isset($params->nombrequipo)) ? $params->nombrequipo : null;
                            $descripcionEquipo = (isset($params->descripcionequipo)) ? $params->descripcionequipo : null;
                            $identificacionEquipo = (isset($params->identificacionequipo)) ? $params->identificacionequipo : null;
                            $equipoActivo = (isset($params->equipoactivo)) ? $params->equipoactivo : true;
                            $idUsuario = (isset($params->fkidusuario)) ? $params->fkidusuario : 0;
                            
                            if($identificacionEquipo != null && $nombreEquipo !=null){
                                $usuario = $em->getRepository('ModeloBundle:Tusuario')->find($idUsuario);
                
                                if($usuario || $idUsuario == 0){
                                    
                                    $equipoDuplicated = $em->getRepository('ModeloBundle:Tequipo')->findOneBy(array(
                                        "identificacionequipo" => $identificacionEquipo, "fkidusuario" => $idUsuario
                                    ));

                                    if(!$equipoDuplicated){
                                        $equipo = new Tequipo();
                                        $equipo->setCodigoequipo($codigoEquipo); 
                                        $equipo->setNombrequipo($nombreEquipo);
                                        $equipo->setDescripcionequipo($descripcionEquipo);
                                        $equipo->setIdentificacionequipo($identificacionEquipo);
                                        $equipo->setEquipoactivo($equipoActivo);
                                        $equipo->setCreacionequipo($today);
                                        $equipo->setModificacionequipo($today);
                                        $equipo->setFkidusuario($usuario);
                                                                
                                        $em->persist($equipo);
                                        $em->flush();
                
                                        $data = array(
                                            'status' => 'Exito',
                                            'msg'    => 'Equipo creado!!',
                                            'equipo' => $equipo,
                                        );

                                        $idEquipo = $equipo->getPkidequipo();
                                    
                                        //una vez insertados los datos en el equipo se realiza el proceso de auditoria
                                        $datos = array(
                                            'idusuario'             => $identity->sub,
                                            'nombreusuario'         => $identity->name,
                                            'identificacionusuario' => $identity->identificacion,
                                            'accion'                => 'insertar',
                                            "tabla"                 => 'Tequipo',
                                            "valoresrelevantes"     => 'idEquipo:'.$idEquipo.',nombreEquipo:'.$nombreEquipo,
                                            'idelemento'            => $idEquipo,
                                            'origen'                => 'web'
                                        );
        
                                        $auditoria = $this->get(Auditoria::class);
                                        $auditoria->auditoria(json_encode($datos));

                                    }else{
                                        $data = array(
                                            'status' => 'error',
                                            'msg'    => 'Identificacion de equipo duplicada!!'
                                        );
                                    }
                                    
                                }else{
                                    $data = array(
                                        'status'=> 'error',
                                        'msg'   => 'El Usuario no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'La identificacion de equipo o nombre de equipo son nulos!!',
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
                'modulo'        => 'Equipos',
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
     * Funcion para modificar un equipo
     * recibe los datos en un json llamado json con los datos
     * pkidequipo=>obligatorio, id del equipo a editar
     * codigoequipo
     * nombrequipo, el nombre del equipo
     * desripcionequipo, descripcion asociada al equipo, puede ser nulo
     * identificacionequipo, un codigo o identificacon que sea unico para el equipo
     * equipoactivo, si el equipo esta activo o no
     * fkidusuario, el id del usuario al que sera asignada el equipo, puede ser nulo
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editEquipoAction(Request $request)
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

                    if (in_array("PERM_EQUIPO_COMPUTOS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la equipo horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);


                        if ($json != null) {
                            
                            $idEquipo = (isset($params->pkidequipo)) ? $params->pkidequipo : null;

                            if($idEquipo != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $equipo = $em->getRepository('ModeloBundle:Tequipo')->find($idEquipo);
                    
                                if($equipo){
                                
                                    if(isset($params->codigoequipo)){
                                        $equipo->setCodigoequipo($params->codigoequipo);
                                    }
                                    
                                    if(isset($params->nombrequipo)){
                                        $equipo->setNombrequipo($params->nombrequipo);
                                    }

                                    if(isset($params->identificacionequipo)){
                                        $identificacionEquipo = $params->identificacionequipo;
                                        $fkidusuario = $params->fkidusuario;

                                        //revisa en la tabla Tequipo si el nombre que se desea asignar existe
                                        $query = $em->getRepository('ModeloBundle:Tequipo')->createQueryBuilder('e')
                                            ->where('e.identificacionequipo = :identificacionequipo and e.pkidequipo != :pkidequipo and e.fkidusuario = :fkidusuario')
                                            ->setParameter('identificacionequipo', $identificacionEquipo)
                                            ->setParameter('fkidusuario', $fkidusuario)
                                            ->setParameter('pkidequipo', $idEquipo)
                                            ->getQuery();
                                        
                                        $equipoDuplicated = $query->getResult();

                                        if(!$equipoDuplicated){
                                            $equipo->setIdentificacionequipo($identificacionEquipo);
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Identificacion de equipo duplicada!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }

                                    if(isset($params->descripcionequipo)){
                                        $equipo->setDescripcionequipo($params->descripcionequipo);
                                    }
                
                                    if(isset($params->equipoactivo)){
                                        $equipo->setEquipoactivo($params->equipoactivo);
                                    }                                   
                
                                    if(isset($params->fkidusuario)){
                                        $usuario = $em->getRepository('ModeloBundle:Tusuario')->find($params->fkidusuario);
                                       
                                        if($usuario){
                                            $equipo->setFkidusuario($usuario);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La usuario no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                
                                    $equipo->setModificacionequipo($today);
                
                                    $em->persist($equipo);
                                    $em->flush();
                
                                    $data = array(
                                        'status' => 'Exito',
                                        'msg'    => 'Equipo actualizado!!',
                                        'equipo' => $equipo,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Tequipo',
                                        "valoresrelevantes"     => 'idEquipo:'.$idEquipo.',nombreEquipo:'.$equipo->getNombrequipo(),
                                        'idelemento'            => $idEquipo,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'El equipo no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del equipo a editar es nulo!!'
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
                'modulo'        => "Equipos",
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
     * Funcion para eliminar un equipo
     * recibe los datos en un json llamado json con los datos
     * pkidequipo=>obligatorio, id del equipo a eliminar
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function removeEquipoAction(Request $request)
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

                    if (in_array("PERM_EQUIPO_COMPUTOS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la equipo horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);


                        if ($json != null) {
                            
                            $idEquipo = (isset($params->pkidequipo)) ? $params->pkidequipo : null;

                            if($idEquipo != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $equipo = $em->getRepository('ModeloBundle:Tequipo')->find($idEquipo);
                    
                                if($equipo){
                                    $nombreEquipo = $equipo->getNombrequipo();
                               
                                    $em->remove($equipo);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg'    => 'Equipo eliminado!!'
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'eliminar',
                                        "tabla"                 => 'Tequipo',
                                        "valoresrelevantes"     => 'idEquipo:'.$idEquipo.',nombreEquipo:'.$nombreEquipo,
                                        'idelemento'            => $idEquipo,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 

                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'El equipo no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del equipo a eliminar es nulo!!'
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
                'modulo'        => "Equipos",
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
     * Funcion para mostrar todas los equipos registrados
     * recibe un token en una variable llamada authorization en caso de 
     * una busqueda de todos los equipos
     * para realizar una busquedad de la identificacon de equipo al momento 
     * de hacer login no se debe enviar authorizacion, pero en cambio recibe 
     * un json llamado json con los parametros:
     * identificacionequipo, el identificador del equipo, ej mac
     * fkidusuario, el id del usuario que se ha registrado
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryEquipoAction(Request $request)
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

                    $identityid = $identity->sub;
                    $identitynom = $identity->name;

                    $activo = $request->get('activo', null);   
                    if($activo != null && $activo == "true"){
                    if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
                        
                        $query = "SELECT pkidequipo,identificacionequipo,nombrequipo 
                                    FROM tequipo 
                                    LEFT OUTER JOIN tusuario ON tequipo.fkidusuario = tusuario.pkidusuario where equipoactivo=true
                                    ORDER BY identificacionequipo ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $equipos = $stmt->fetchAll();

                        $data = array(
                            'status'    => 'Exito',
                            'equipos' => $equipos,
                        );

                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_EQUIPO_COMPUTOS", $permisosDeserializados)) {
                
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
                        
                        //Consulta para traer los datos de la equipo, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidequipo,identificacionequipo,nombrequipo,codigoequipo,descripcionequipo,fkidusuario,identificacion,nombreusuario,equipoactivo 
                                    FROM tequipo 
                                    LEFT OUTER JOIN tusuario ON tequipo.fkidusuario = tusuario.pkidusuario
                                    ORDER BY identificacionequipo ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $equipos = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($equipos as $equipo) {
                            $equiposList = array(
                                "pkidequipo"           => $equipo['pkidequipo'],
                                "identificacionequipo" => $equipo['identificacionequipo'],
                                "nombrequipo"          => $equipo['nombrequipo'],
                                "codigoequipo"         => $equipo['codigoequipo'],
                                "descripcionequipo"    => $equipo['descripcionequipo'],
                                "usuario"              => array("pkidusuario" => $equipo['fkidusuario'],"identificacion" => $equipo['identificacion'],"nombreusuario" => $equipo['nombreusuario']),
                                "equipoactivo"         => $equipo['equipoactivo'],
                            );
                            array_push($array_all, $equiposList);
                        }

                        $cabeceras = array("Identificacion Equipo","Equipos","Descripcion","Usuario Asignado","Equipo Activa/Inactiva");

                        $data = array(
                            'status'    => 'Exito',
                            'cabeceras' => $cabeceras,
                            'equipos'   => $array_all,
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
                if (!empty($request->get('json'))) {
                    //Recoger datos post
                    $json = $request->get("json", null);
                    $params = json_decode($json);

                    if ($json != null) {
                        
                        $identificacionEquipo = (isset($params->identificacionequipo)) ? $params->identificacionequipo : null;
                        $fkidusuario = (isset($params->fkidusuario)) ? $params->fkidusuario : null;

                        if($identificacionEquipo != null && $fkidusuario !=null){
                        
                            $em = $this->getDoctrine()->getManager();
                            $equipo = $em->getRepository('ModeloBundle:Tequipo')->findOneBy(array(
                                "identificacionequipo" => $identificacionEquipo,
                                "fkidusuario"          => $fkidusuario
                            ));

                            if($equipo){
                                if($equipo->getEquipoactivo() == true){
                                    $data = array(
                                        'status' => 'exito'
                                    );
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'Equipo bloqueado'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error'
                                );
                            }

                            //al no enviar un token se busca los datos para el error desde la tabla usuario
                            $usuario = $em->getRepository('ModeloBundle:Tusuario')->find($fkidusuario);
                            $identityid = $usuario->getPkidusuario();
                            $identitynom = $usuario->getNombreusuario();
                        }else{
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'algunos datos son nulos!!'
                            );
                        }

                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Parametro json es nulo!!',
                        );
                    }

                }else{
                    $data = array(
                        'status' => 'error',
                        'msg'    => 'Envie los parametros, por favor !!',
                    );
                }
                
            }

            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();
  
            $data = array(
                'idusuario'     => $identityid,
                'nombreusuario' => $identitynom,
                'modulo'        => "Equipo",
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