<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tabogado;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TabogadoController extends Controller
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
     * Funcion para registrar un abogado
     * recibe los datos en un json llamado json con los datos
     * codigoabogado, puede ser nulo 
     * nombreabogado, el nombre del abogado
     * direccionabogado, direccion del abogado, puede ser nulo 
     * telefonoabogado, telefono del abogado, puede ser nulo 
     * abogadoactivo, si el abogado esta activa o no
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newAbogadoAction(Request $request)
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

                    if (in_array("PERM_ABOGADOS", $permisosDeserializados)) {
                       
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));
                        
                        //entiti manager
                        $em = $this->getDoctrine()->getManager();
                        
                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $codigoAbogado = (isset($params->codigoabogado)) ? $params->codigoabogado : null;
                            $nombreAbogado = (isset($params->nombreabogado)) ? $params->nombreabogado : null;
                            $direccionAbogado = (isset($params->direccionabogado)) ? $params->direccionabogado : null;
                            $telefonoAbogado = (isset($params->telefonoabogado)) ? $params->telefonoabogado : null;
                            $abogadoActivo = (isset($params->abogadoactivo)) ? $params->abogadoactivo : true;
                            
                            if($nombreAbogado !=null){
                                                
                                $abogadoDuplicated = $em->getRepository('ModeloBundle:Tabogado')->findOneBy(array(
                                    "nombreabogado" => $nombreAbogado,
                                ));

                                if(!$abogadoDuplicated){
                                    $abogado = new Tabogado();
                                    $abogado->setCodigoabogado($codigoAbogado); 
                                    $abogado->setNombreabogado($nombreAbogado);
                                    $abogado->setDireccionabogado($direccionAbogado);
                                    $abogado->setTelefonoabogado($telefonoAbogado);
                                    $abogado->setAbogadoactivo($abogadoActivo);
                                    $abogado->setCreacionabogado($today);
                                    $abogado->setModificacionabogado($today);
                                                
                                    $em->persist($abogado);
                                    $em->flush();
            
                                    $data = array(
                                        'status'  => 'Exito',
                                        'msg'     => 'Abogado creado!!',
                                        'abogado' => $abogado,
                                    );

                                    $idAbogado = $abogado->getPkidabogado();
                                
                                    //una vez insertados los datos en la abogado se realiza el proceso de auditoria
                                    $datos = array(
                                        'idusuario'             => $identity->sub,
                                        'nombreusuario'         => $identity->name,
                                        'identificacionusuario' => $identity->identificacion,
                                        'accion'                => 'insertar',
                                        "tabla"                 => 'Tabogado',
                                        "valoresrelevantes"     => 'idAbogado:'.$idAbogado.',nombreAbogado:'.$nombreAbogado,
                                        'idelemento'            => $idAbogado,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'Nombre de abogado duplicado!!'
                                    );
                                }
                                   
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El nombre del abogado es nulo!!',
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
                'modulo'        => 'Abogado',
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
     * Funcion para modificar un abogado
     * recibe los datos en un json llamado json con los datos
     * pkidabogado=>obligatorio, id del abogado a editar
     * codigoabogado, puede ser vacio
     * nombreabogado, el nombre del abogado
     * direccionabogado, direccion del abogado, puede ser nulo 
     * telefonoabogado, telefono del abogado, puede ser nulo 
     * abogadoactivo, si el abogado esta activa o no
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editAbogadoAction(Request $request)
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

                    if (in_array("PERM_ABOGADOS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la abogado horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);


                        if ($json != null) {
                            
                            $idAbogado = (isset($params->pkidabogado)) ? $params->pkidabogado : null;

                            if($idAbogado != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $abogado = $em->getRepository('ModeloBundle:Tabogado')->find($idAbogado);
                    
                                if($abogado){
                                    
                                    if(isset($params->codigoabogado)){
                                        $abogado->setCodigoabogado($params->codigoabogado);
                                    }
                
                                    if(isset($params->nombreabogado)){
                                        $nombreAbogado = $params->nombreabogado;

                                        //revisa en la tabla Tabogado si el nombre que se desea asignar existe 
                                        $query = $em->getRepository('ModeloBundle:Tabogado')->createQueryBuilder('ta')
                                            ->where('ta.nombreabogado = :nombreabogado and ta.pkidabogado != :pkidabogado')
                                            ->setParameter('nombreabogado', $nombreAbogado)
                                            ->setParameter('pkidabogado', $idAbogado)
                                            ->getQuery();
                                        
                                        $abogadoDuplicated = $query->getResult();

                                        if(!$abogadoDuplicated){
                                            $abogado->setNombreabogado($params->nombreabogado);
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Nombre de abogado duplicado!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }

                                    if(isset($params->direccionabogado)){
                                        $abogado->setDireccionabogado($params->direccionabogado);
                                    }

                                    if(isset($params->telefonoabogado)){
                                        $abogado->setTelefonoabogado($params->telefonoabogado);
                                    }
                
                                    if(isset($params->abogadoactivo)){
                                        $abogado->setAbogadoactivo($params->abogadoactivo);
                                    }                                   
                
                                    $abogado->setModificacionabogado($today);
                
                                    $em->persist($abogado);
                                    $em->flush();
                
                                    $data = array(
                                        'status'  => 'Exito',
                                        'msg'     => 'Abogado actualizado!!',
                                        'abogado' => $abogado,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Tabogado',
                                        "valoresrelevantes"     => 'idAbogado:'.$idAbogado.',nombreAbogado:'.$abogado->getNombreabogado(),
                                        'idelemento'            => $idAbogado,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'El abogado no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del abogado a editar es nulo!!'
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
                'modulo'        => "Abogado",
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
     * Funcion para eliminar una abogado
     * recibe los datos en un json llamado json con los datos
     * pkidabogado=>obligatorio, id de la abogado a eliminar
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function removeAbogadoAction(Request $request)
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

                    if (in_array("PERM_ABOGADOS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la abogado horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);


                        if ($json != null) {
                            
                            $idAbogado = (isset($params->pkidabogado)) ? $params->pkidabogado : null;

                            if($idAbogado != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $abogado = $em->getRepository('ModeloBundle:Tabogado')->find($idAbogado);
                    
                                if($abogado){
                                    $nombreAbogado = $abogado->getNombreabogado();

                                    $asignacionabogado = $em->getRepository('ModeloBundle:Tproceso')->findOneBy(array(
                                        'fkidabogado' => $idAbogado
                                    ));

                                    if(!$asignacionabogado){
                                        
                                        $em->remove($abogado);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'Exito',
                                            'msg'    => 'Abogado eliminado!!'
                                        );
        
                                        $datos = array(
                                            "idusuario"             => $identity->sub,
                                            "nombreusuario"         => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            'accion'                => 'eliminar',
                                            "tabla"                 => 'Tabogado',
                                            "valoresrelevantes"     => 'idAbogado:'.$idAbogado.',nombreAbogado:'.$nombreAbogado,
                                            'idelemento'            => $idAbogado,
                                            'origen'                => 'web'
                                        );
        
                                        $auditoria = $this->get(Auditoria::class);
                                        $auditoria->auditoria(json_encode($datos)); 

                                    }else{
                                        $data = array(
                                            'status' => 'Error',
                                            'msg'    => 'No se puede eliminar el abogado, tiene procesos asignados!!'
                                        );
                                    }
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'el abogado no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del abogado a eliminar es nulo!!'
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
                'modulo'        => "Abogado",
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
     * Funcion para mostrar todas las abogados registradas
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryAbogadoAction(Request $request)
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
                        
                        $query = "SELECT pkidabogado,nombreabogado 
                                    FROM tabogado where abogadoactivo = true
                                    ORDER BY nombreabogado ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $abogadoes = $stmt->fetchAll();

                        $data = array(
                            'status'    => 'Exito',
                            'abogado' => $abogadoes,
                        );

                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_ABOGADOS", $permisosDeserializados)) {
                        

                        //Consulta para traer los datos de la abogado, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidabogado,nombreabogado,codigoabogado,direccionabogado,telefonoabogado,abogadoactivo 
                                    FROM tabogado
                                    ORDER BY nombreabogado ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $abogadoes = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($abogadoes as $abogado) {
                            $abogadoList = array(
                                "pkidabogado"      => $abogado['pkidabogado'],
                                "nombreabogado"    => $abogado['nombreabogado'],
                                "codigoabogado"    => $abogado['codigoabogado'],
                                "direccionabogado" => $abogado['direccionabogado'],
                                "telefonoabogado"  => $abogado['telefonoabogado'],
                                "abogadoactivo"    => $abogado['abogadoactivo']
                            );
                            array_push($array_all, $abogadoList);
                        }

                        $cabeceras=array();

                        $pkidabogado = array("nombrecampo"=>"pkidabogado","nombreetiqueta"=>"Id Abogado","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");                       
                        $nombreabogado = array("nombrecampo"=>"nombreabogado","nombreetiqueta"=>"Nombre Abogado","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $codigoabogado = array("nombrecampo"=>"codigoabogado","nombreetiqueta"=>"Codigo Abogado","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $direccionabogado = array("nombrecampo"=>"direccionabogado","nombreetiqueta"=>"Direccion Abogado","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $telefonoabogado = array("nombrecampo"=>"telefonoabogado","nombreetiqueta"=>"Telefono Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"number");              
                        $abogadoactivo = array("nombrecampo"=>"abogadoactivo","nombreetiqueta"=>"Abogado Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");              
                        
                        
                        array_push($cabeceras,$pkidabogado);
                        array_push($cabeceras,$nombreabogado);
                        array_push($cabeceras,$codigoabogado);
                        array_push($cabeceras,$direccionabogado);
                        array_push($cabeceras,$telefonoabogado);
                        array_push($cabeceras,$abogadoactivo);

                        $title=array("Nuevo","Abogado");

                        $data = array(
                            'status'    => 'Exito',
                            'cabeceras' => $cabeceras,
                            'abogado'   => $array_all,
                            'title' => $title
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
                'modulo'        => "Abogado",
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