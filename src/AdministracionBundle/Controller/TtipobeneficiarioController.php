<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttipobeneficiario;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtipobeneficiarioController extends Controller
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
     * Funcion para registrar un tipo de beneficiario
     * recibe los datos en un json llamado json con los datos
     * codigotipobeneficiario, puede ser nulo 
     * nombretipobeneficiario, el nombre del tipo de beneficiario
     * descripciontipobeneficiario, descripcion del tipo de beneficiario, puede ser nulo 
     * tipobeneficiarioactivo, si el tipo de beneficiario esta activa o no
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newTipoBeneficiarioAction(Request $request)
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

                    if (in_array("PERM_TIPO_BENEFICIARIOS", $permisosDeserializados)) {
                       
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));
                        
                        //entiti manager
                        $em = $this->getDoctrine()->getManager();

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);


                        if ($json != null) {

                            $codigoTipoBeneficiario = (isset($params->codigotipobeneficiario)) ? $params->codigotipobeneficiario : null;
                            $nombreTipoBeneficiario = (isset($params->nombretipobeneficiario)) ? $params->nombretipobeneficiario : null;
                            $descripcionTipoBeneficiario = (isset($params->descripciontipobeneficiario)) ? $params->descripciontipobeneficiario : null;
                            $tipobeneficiarioActivo = (isset($params->tipobeneficiarioactivo)) ? $params->tipobeneficiarioactivo : true;
                            
                            if($nombreTipoBeneficiario !=null){
                                                
                                $tipobeneficiarioDuplicated = $em->getRepository('ModeloBundle:Ttipobeneficiario')->findOneBy(array(
                                    "nombretipobeneficiario" => $nombreTipoBeneficiario,
                                ));

                                if(!$tipobeneficiarioDuplicated){
                                    $tipobeneficiario = new Ttipobeneficiario();
                                    $tipobeneficiario->setCodigotipobeneficiario($codigoTipoBeneficiario); 
                                    $tipobeneficiario->setNombretipobeneficiario($nombreTipoBeneficiario);
                                    $tipobeneficiario->setDescripciontipobeneficiario($descripcionTipoBeneficiario);
                                    $tipobeneficiario->setTipoBeneficiarioactivo($tipobeneficiarioActivo);
                                    $tipobeneficiario->setCreaciontipobeneficiario($today);
                                    $tipobeneficiario->setModificaciontipobeneficiario($today);
                                                
                                    $em->persist($tipobeneficiario);
                                    $em->flush();
            
                                    $data = array(
                                        'status'     => 'Exito',
                                        'msg'        => 'Tipo de Beneficiario creado!!',
                                        'tipobeneficiario' => $tipobeneficiario,
                                    );

                                    $idTipoBeneficiario = $tipobeneficiario->getPkidtipobeneficiario();
                                
                                    //una vez insertados los datos en la tipobeneficiario se realiza el proceso de auditoria
                                    $datos = array(
                                        'idusuario'             => $identity->sub,
                                        'nombreusuario'         => $identity->name,
                                        'identificacionusuario' => $identity->identificacion,
                                        'accion'                => 'insertar',
                                        "tabla"                 => 'Ttipobeneficiario',
                                        "valoresrelevantes"     => 'idTipoBeneficiario:'.$idTipoBeneficiario.',nombreTipoBeneficiario:'.$nombreTipoBeneficiario,
                                        'idelemento'            => $idTipoBeneficiario,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'Nombre de tipo de beneficiario duplicado!!'
                                    );
                                }
                                   
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El nombre del tipo de beneficiario es nulo!!',
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
                'modulo'        => 'TipoBeneficiario',
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
     * Funcion para modificar un tipo de beneficiario
     * recibe los datos en un json llamado json con los datos
     * pkidtipobeneficiario=>obligatorio, id del tipo de beneficiario a editar
     * codigotipobeneficiario, puede ser vacio
     * nombretipobeneficiario, el nombre del tipo de beneficiario
     * descripciontipobeneficiario, descripcion del tipo de beneficiario, puede ser nulo 
     * tipobeneficiarioactivo, si el tipo de beneficiario esta activa o no
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editTipoBeneficiarioAction(Request $request)
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

                    if (in_array("PERM_TIPO_BENEFICIARIOS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la tipobeneficiario horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);


                        if ($json != null) {
                            
                            $idTipoBeneficiario = (isset($params->pkidtipobeneficiario)) ? $params->pkidtipobeneficiario : null;

                            if($idTipoBeneficiario != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $tipobeneficiario = $em->getRepository('ModeloBundle:Ttipobeneficiario')->find($idTipoBeneficiario);
                    
                                if($tipobeneficiario){
                                    
                                    if(isset($params->codigotipobeneficiario)){
                                        $tipobeneficiario->setCodigotipobeneficiario($params->codigotipobeneficiario);
                                    }
                
                                    if(isset($params->nombretipobeneficiario)){
                                        $nombreTipoBeneficiario = $params->nombretipobeneficiario;

                                        //revisa en la tabla Ttipobeneficiario si el nombre que se desea asignar no existe en la misma plaza
                                        $query = $em->getRepository('ModeloBundle:Ttipobeneficiario')->createQueryBuilder('tb')
                                            ->where('tb.nombretipobeneficiario = :nombretipobeneficiario and tb.pkidtipobeneficiario != :pkidtipobeneficiario')
                                            ->setParameter('nombretipobeneficiario', $nombreTipoBeneficiario)
                                            ->setParameter('pkidtipobeneficiario', $idTipoBeneficiario)
                                            ->getQuery();
                                        
                                        $tipobeneficiarioDuplicated = $query->getResult();

                                        if(!$tipobeneficiarioDuplicated){
                                            $tipobeneficiario->setNombretipobeneficiario($params->nombretipobeneficiario);
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Nombre de tipo de beneficiario duplicado!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }

                                    if(isset($params->descripciontipobeneficiario)){
                                        $tipobeneficiario->setDescripciontipobeneficiario($params->descripciontipobeneficiario);
                                    }
                
                                    if(isset($params->tipobeneficiarioactivo)){
                                        $tipobeneficiario->setTipoBeneficiarioactivo($params->tipobeneficiarioactivo);
                                    }                                   
                
                                    $tipobeneficiario->setModificaciontipobeneficiario($today);
                
                                    $em->persist($tipobeneficiario);
                                    $em->flush();
                
                                    $data = array(
                                        'status'           => 'Exito',
                                        'msg'              => 'Tipo de Beneficiario actualizado!!',
                                        'tipobeneficiario' => $tipobeneficiario,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Ttipobeneficiario',
                                        "valoresrelevantes"     => 'idTipoBeneficiario:'.$idTipoBeneficiario.',nombreTipoBeneficiario:'.$tipobeneficiario->getNombretipobeneficiario(),
                                        'idelemento'            => $idTipoBeneficiario,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'El tipo de beneficiario no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del tipo de beneficiario a editar es nulo!!'
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
                'modulo'        => "TipoBeneficiario",
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
     * Funcion para eliminar una tipobeneficiario
     * recibe los datos en un json llamado json con los datos
     * pkidtipobeneficiario=>obligatorio, id de la tipobeneficiario a eliminar
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function removeTipoBeneficiarioAction(Request $request)
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

                    if (in_array("PERM_TIPO_BENEFICIARIOS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la tipobeneficiario horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);


                        if ($json != null) {
                            
                            $idTipoBeneficiario = (isset($params->pkidtipobeneficiario)) ? $params->pkidtipobeneficiario : null;

                            if($idTipoBeneficiario != null){
                                                            
                                $em = $this->getDoctrine()->getManager();
                                $tipobeneficiario = $em->getRepository('ModeloBundle:Ttipobeneficiario')->find($idTipoBeneficiario);
                               
                    
                                if($tipobeneficiario){
                                    $nombreTipoBeneficiario = $tipobeneficiario->getNombretipobeneficiario();
                                    
                                    $beneficiario = $em->getRepository('ModeloBundle:Tbeneficiario')->findOneBy(array(
                                        'fkidtipobeneficiario' => $idTipoBeneficiario
                                    ));
                                    
                                    if(!$beneficiario){
                                        
                                        $em->remove($tipobeneficiario);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'Exito',
                                            'msg'    => 'Tipo de beneficiario eliminado!!'
                                        );
        
                                        $datos = array(
                                            "idusuario"             => $identity->sub,
                                            "nombreusuario"         => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            'accion'                => 'eliminar',
                                            "tabla"                 => 'Ttipobeneficiario',
                                            "valoresrelevantes"     => 'idTipoBeneficiario:'.$idTipoBeneficiario.',nombreTipoBeneficiario:'.$nombreTipoBeneficiario,
                                            'idelemento'            => $idTipoBeneficiario,
                                            'origen'                => 'web'
                                        );
        
                                        $auditoria = $this->get(Auditoria::class);
                                        $auditoria->auditoria(json_encode($datos)); 

                                    }else{
                                        $data = array(
                                            'status' => 'Error',
                                            'msg'    => 'No se puede eliminar el tipo de beneficiario, tiene beneficiarios asignados!!'
                                        );
                                    }
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'el tipo de beneficiario no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del tipo de beneficiario a eliminar es nulo!!'
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
                'modulo'        => "TipoBeneficiario",
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
     * Funcion para mostrar todas las tipobeneficiarios registradas
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryTipoBeneficiarioAction(Request $request)
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
                        
                        $query = "SELECT pkidtipobeneficiario,nombretipobeneficiario,codigotipobeneficiario,descripciontipobeneficiario,tipobeneficiarioactivo 
                                    FROM ttipobeneficiario where tipobeneficiarioactivo=true
                                    ORDER BY nombretipobeneficiario ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tipobeneficiarios = $stmt->fetchAll();

			        $data = array(
                            'status'    => 'Success',
                            'tipobeneficiario'     => $tipobeneficiarios,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_TIPO_BENEFICIARIOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        //Consulta para traer los datos de la tipobeneficiario, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidtipobeneficiario,nombretipobeneficiario,codigotipobeneficiario,descripciontipobeneficiario,tipobeneficiarioactivo 
                                    FROM ttipobeneficiario
                                    ORDER BY nombretipobeneficiario ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tipobeneficiarios = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($tipobeneficiarios as $tipobeneficiario) {
                            $tipobeneficiarioList = array(
                                "pkidtipobeneficiario"        => $tipobeneficiario['pkidtipobeneficiario'],
                                "nombretipobeneficiario"      => $tipobeneficiario['nombretipobeneficiario'],
                                "codigotipobeneficiario"      => $tipobeneficiario['codigotipobeneficiario'],
                                "descripciontipobeneficiario" => $tipobeneficiario['descripciontipobeneficiario'],
                                "tipobeneficiarioactivo"      => $tipobeneficiario['tipobeneficiarioactivo']
                            );
                            array_push($array_all, $tipobeneficiarioList);
                        }

                        $cabeceras=array();

                        $pkidtipobeneficiario = array("nombrecampo"=>"pkidtipobeneficiario","nombreetiqueta"=>"Id Tipo Beneficiario","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");                       
                        $nombretipobeneficiario = array("nombrecampo"=>"nombretipobeneficiario","nombreetiqueta"=>"Nombre Tipo Beneficiario","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $codigotipobeneficiario = array("nombrecampo"=>"codigotipobeneficiario","nombreetiqueta"=>"Codigo Tipo Beneficiario","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $descripciontipobeneficiario = array("nombrecampo"=>"descripciontipobeneficiario","nombreetiqueta"=>"Descripcion Tipo Beneficiario","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $tipobeneficiarioactivo = array("nombrecampo"=>"tipobeneficiarioactivo","nombreetiqueta"=>"Tipo Beneficiario Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");              
                        

                        array_push($cabeceras,$pkidtipobeneficiario);
                        array_push($cabeceras,$nombretipobeneficiario);
                        array_push($cabeceras,$codigotipobeneficiario);
                        array_push($cabeceras,$descripciontipobeneficiario);
                        array_push($cabeceras,$tipobeneficiarioactivo);

                        $title=array("Nuevo","Tipo Beneficiario");

                        $data = array(
                            'status'           => 'Exito',
                            'cabeceras'        => $cabeceras,
                            'tipobeneficiario' => $array_all,
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
                'modulo'        => "TipoBeneficiario",
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