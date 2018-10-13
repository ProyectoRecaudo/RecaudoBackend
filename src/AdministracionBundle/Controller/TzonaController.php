<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tusuario;
use ModeloBundle\Entity\Tplaza;
use ModeloBundle\Entity\Tzona;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TzonaController extends Controller
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
     * Funcion para registrar una zona
     * recibe los datos en un json llamado json con los datos
     * codigozona
     * nombrezona, el nombre de la zona
     * zonaactivo, si la zona esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fkidusuario, el id del usuario al que sera asignada la zona
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newZonaAction(Request $request)
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

                    if (in_array("PERM_ZONAS", $permisosDeserializados)) {
                       
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));
                        
                        //entiti manager
                        $em = $this->getDoctrine()->getManager();

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Zona no creada!!',
                        );

                        if ($json != null) {

                            $codigoZona = (isset($params->codigozona)) ? $params->codigozona : null;
                            $nombreZona = (isset($params->nombrezona)) ? $params->nombrezona : null;
                            $zonaActivo = (isset($params->zonaactivo)) ? $params->zonaactivo : true;
                            $idPlaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null; 
                            $idUsuario = (isset($params->fkidusuario)) ? $params->fkidusuario : 0;
                            
                            if($idPlaza != null && $nombreZona !=null){
                                $usuario = $em->getRepository('ModeloBundle:Tusuario')->find($idUsuario);
                                $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                
                                if($usuario || $idUsuario == 0){
                                    
                                    if($plaza){
                                        $zonaDuplicated = $em->getRepository('ModeloBundle:Tzona')->findOneBy(array(
                                            "nombrezona" => $nombreZona,
                                            "fkidplaza"  => $idPlaza
                                        ));

                                        if(!$zonaDuplicated){
                                            $zona = new Tzona();
                                            $zona->setCodigozona($codigoZona); 
                                            $zona->setNombrezona($nombreZona);
                                            $zona->setZonaactivo($zonaActivo);
                                            $zona->setCreacionzona($today);
                                            $zona->setModificacionzona($today);
                                            $zona->setFkidusuario($usuario);
                                            $zona->setFkidplaza($plaza);
                                            
                                            $em->persist($zona);
                                            $em->flush();
                    
                                            $data = array(
                                                'status' => 'Exito',
                                                'msg'    => 'Zona creada!!',
                                                'zona'   => $zona,
                                            );
    
                                            $idZona = $zona->getPkidzona();
                                        
                                            //una vez insertados los datos en la zona se realiza el proceso de auditoria
                                            $datos = array(
                                                'idusuario'             => $identity->sub,
                                                'nombreusuario'         => $identity->name,
                                                'identificacionusuario' => $identity->identificacion,
                                                'accion'                => 'insertar',
                                                "tabla"                 => 'Tzona',
                                                "valoresrelevantes"     => 'idZona:'.$idZona.',nombreZona:'.$nombreZona,
                                                'idelemento'            => $idZona,
                                                'origen'                => 'web'
                                            );
            
                                            $auditoria = $this->get(Auditoria::class);
                                            $auditoria->auditoria(json_encode($datos));

                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Nombre de zona duplicado!!'
                                            );
                                        }
                                    }else{
                                        $data = array(
                                            'status'=> 'error',
                                            'msg'   => 'La Plaza no existe!!'
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
                                    'msg'    => 'El id de plaza o nombre de zona son nulos!!',
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
                'modulo'        => 'Zonas',
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
     * Funcion para modificar una zona
     * recibe los datos en un json llamado json con los datos
     * pkidzona=>obligatorio, id de la zona a editar
     * codigozona
     * nombrezona, el nombre de la zona
     * zonaactivo, si la zona esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fkidusuario, el id del usuario al que sera asignada la zona
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editZonaAction(Request $request)
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

                    if (in_array("PERM_ZONAS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Zona no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idZona = (isset($params->pkidzona)) ? $params->pkidzona : null;

                            if($idZona != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $zona = $em->getRepository('ModeloBundle:Tzona')->find($idZona);
                    
                                if($zona){
                                    
                                    if(isset($params->fkidplaza)){
                                        $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($params->fkidplaza);
                                        $idPlaza = $params->fkidplaza;
                                        
                                        if($plaza){
                                            $zona->setFkidplaza($plaza);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'  => 'La plaza no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idPlaza = $zona->getFkidplaza();
                                    }

                                    if(isset($params->codigozona)){
                                        $zona->setCodigozona($params->codigozona);
                                    }
                
                                    if(isset($params->nombrezona)){
                                        $nombreZona = $params->nombrezona;

                                        //revisa en la tabla Tzona si el nombre que se desea asignar no existe en la misma plaza
                                        $query = $em->getRepository('ModeloBundle:Tzona')->createQueryBuilder('z')
                                            ->where('z.nombrezona = :nombrezona and z.pkidzona != :pkidzona and z.fkidplaza = :fkidplaza')
                                            ->setParameter('nombrezona', $nombreZona)
                                            ->setParameter('pkidzona', $idZona)
                                            ->setParameter('fkidplaza', $idPlaza)
                                            ->getQuery();
                                        
                                        $zonaDuplicated = $query->getResult();

                                        if(!$zonaDuplicated){
                                            $zona->setNombrezona($params->nombrezona);
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Nombre de zona duplicado!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                
                                    if(isset($params->zonaactivo)){
                                        $zona->setZonaactivo($params->zonaactivo);
                                    }                                   
                
                                    if(isset($params->fkidusuario)){
                                        $usuario = $em->getRepository('ModeloBundle:Tusuario')->find($params->fkidusuario);
                                       
                                        if($usuario){
                                            $zona->setFkidusuario($usuario);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La usuario no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                
                                    $zona->setModificacionzona($today);
                
                                    $em->persist($zona);
                                    $em->flush();
                
                                    $data = array(
                                        'status' => 'Exito',
                                        'msg'    => 'Zona actualizada!!',
                                        'zona'   => $zona,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Tzona',
                                        "valoresrelevantes"     => 'idZona:'.$idZona.',nombreZona:'.$zona->getNombrezona(),
                                        'idelemento'            => $idZona,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La zona no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la zona a editar es nulo!!'
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
                'modulo'        => "Zonas",
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
     * Funcion para eliminar una zona
     * recibe los datos en un json llamado json con los datos
     * pkidzona=>obligatorio, id de la zona a eliminar
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function removeZonaAction(Request $request)
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

                    if (in_array("PERM_ZONAS", $permisosDeserializados)) {
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Zona no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idZona = (isset($params->pkidzona)) ? $params->pkidzona : null;

                            if($idZona != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $zona = $em->getRepository('ModeloBundle:Tzona')->find($idZona);
                    
                                if($zona){
                                    $nombreZona = $zona->getNombrezona();

                                    $sector = $em->getRepository('ModeloBundle:Tsector')->findOneBy(array(
                                        'fkidzona' => $idZona
                                    ));

                                    if(!$sector){
                                        
                                        $em->remove($zona);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'Exito',
                                            'msg'    => 'Zona eliminada!!'
                                        );
        
                                        $datos = array(
                                            "idusuario"             => $identity->sub,
                                            "nombreusuario"         => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            'accion'                => 'eliminar',
                                            "tabla"                 => 'Tzona',
                                            "valoresrelevantes"     => 'idZona:'.$idZona.',nombreZona:'.$nombreZona,
                                            'idelemento'            => $idZona,
                                            'origen'                => 'web'
                                        );
        
                                        $auditoria = $this->get(Auditoria::class);
                                        $auditoria->auditoria(json_encode($datos)); 

                                    }else{
                                        $data = array(
                                            'status' => 'Error',
                                            'msg'    => 'No se puede eliminar la zona, tiene sectores asignados!!'
                                        );
                                    }
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La zona no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la zona a eliminar es nulo!!'
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
                'modulo'        => "Zonas",
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
     * Funcion para mostrar las zonas registradas
     * En caso de querer filtrar las zonas por la plaza a 
     * la que pertenecen se debe enviar el parametro pkidplaza 
     * con el id de la plaza que se desea conocer.
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryZonaAction(Request $request)
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

                    $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                    $activo = $request->get('activo', null);   
                    if($activo != null && $activo == "true"){
                    if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                        

                        $query = "SELECT DISTINCT pkidzona,nombrezona,codigozona,fkidplaza,nombreplaza,fkidusuario,identificacion,nombreusuario,apellido,zonaactivo 
                                    FROM tzona 
                                    JOIN tplaza ON tzona.fkidplaza = tplaza.pkidplaza
                                    LEFT OUTER JOIN tusuario ON tzona.fkidusuario = tusuario.pkidusuario where zonaactivo = true
                                    ORDER BY nombrezona ASC;";
                                    
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $zonas = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($zonas as $zona) {
                            $zonasList = array(
                                "pkidzona"   => $zona['pkidzona'],
                                "nombrezona" => $zona['nombrezona'],
                                "codigozona" => $zona['codigozona'],
                                "plaza"      => array("pkidplaza" => $zona['fkidplaza'],"nombreplaza" => $zona['nombreplaza']),
                                "usuario"    => array("pkidusuario" => $zona['fkidusuario'],"identificacion" => $zona['identificacion'],"nombreusuario" => $zona['nombreusuario'],"apellido" => $zona['apellido']),
                                "zonaactivo" => $zona['zonaactivo'],
                            );
                            array_push($array_all, $zonasList);
                        }
                        
			            $data = array(
                            'status'    => 'Success',
                            'zonas'     => $array_all,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_ZONAS", $permisosDeserializados) || in_array("PERM_GENERICOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
                        
                        //Consulta de zonas por plaza
                        $where = "";
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if(isset($params->pkidplaza)){
                            $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($params->pkidplaza);
                            $join = "";
                            $sector = (isset($params->sector)) ? $params->sector : "false";
                           
                            if($sector == "true"){
                                $join = "INNER JOIN tsector ON tzona.pkidzona = tsector.fkidzona "; 
                            }
                            
                            if($plaza){
                                $where = $join."WHERE zonaactivo = true AND fkidplaza = ".$params->pkidplaza;
                            }else{
                                $data = array(
                                    'status'=> 'Error',
                                    'msg'   => 'La plaza no existe!!'
                                );
                                return $helpers->json($data);
                            }
                        }else{
                            $where = "WHERE nombrezona not LIKE 'SIN ZONA'";
                        }
                        
                        //Consulta para traer los datos de la zona, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT DISTINCT pkidzona,nombrezona,codigozona,fkidplaza,nombreplaza,fkidusuario,identificacion,nombreusuario,apellido,zonaactivo 
                                    FROM tzona 
                                    JOIN tplaza ON tzona.fkidplaza = tplaza.pkidplaza
                                    LEFT OUTER JOIN tusuario ON tzona.fkidusuario = tusuario.pkidusuario
                                    ".$where."
                                    ORDER BY nombrezona ASC;";
                                    
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $zonas = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($zonas as $zona) {
                            $zonasList = array(
                                "pkidzona"   => $zona['pkidzona'],
                                "nombrezona" => $zona['nombrezona'],
                                "codigozona" => $zona['codigozona'],
                                "plaza"      => array("pkidplaza" => $zona['fkidplaza'],"nombreplaza" => $zona['nombreplaza']),
                                "usuario"    => array("pkidusuario" => $zona['fkidusuario'],"identificacion" => $zona['identificacion'],"nombreusuario" => $zona['nombreusuario'],"apellido" => $zona['apellido']),
                                "zonaactivo" => $zona['zonaactivo'],
                            );
                            array_push($array_all, $zonasList);
                        }

                        $cabeceras = array("Zonas","Plaza","Usuario Asignado","Zona Activa/Inactiva");

                        $data = array(
                            'status'    => 'Success',
                            'cabeceras' => $cabeceras,
                            'zonas'     => $array_all,
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
                'modulo'        => "Zonas",
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