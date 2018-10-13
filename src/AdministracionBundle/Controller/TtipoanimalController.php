<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttipoanimal;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtipoanimalController extends Controller
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
     * Funcion para registrar un tipo de animal
     * recibe los datos en un json llamado json con los datos
     * codigotipoanimal, puede ser nulo 
     * nombretipoanimal, el nombre del tipo de animal
     * descripciontipoanimal, descripcion del tipo de animal, puede ser nulo 
     * tipoanimalactivo, si el tipo de animal esta activa o no
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newTipoAnimalAction(Request $request)
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

                    if (in_array("PERM_TIPO_ANIMALES", $permisosDeserializados)) {
                       
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
                            'msg'    => 'Tipo de animal no creado!!',
                        );

                        if ($json != null) {

                            $codigoTipoAnimal = (isset($params->codigotipoanimal)) ? $params->codigotipoanimal : null;
                            $nombreTipoAnimal = (isset($params->nombretipoanimal)) ? $params->nombretipoanimal : null;
                            $descripcionTipoAnimal = (isset($params->descripciontipoanimal)) ? $params->descripciontipoanimal : null;
                            $tipoanimalActivo = (isset($params->tipoanimalactivo)) ? $params->tipoanimalactivo : true;
                           
                            if($nombreTipoAnimal !=null){
                                                
                                $tipoanimalDuplicated = $em->getRepository('ModeloBundle:Ttipoanimal')->findOneBy(array(
                                    "nombretipoanimal" => $nombreTipoAnimal,
                                ));

                                if(!$tipoanimalDuplicated){
                                    $tipoanimal = new Ttipoanimal();
                                    $tipoanimal->setCodigotipoanimal($codigoTipoAnimal); 
                                    $tipoanimal->setNombretipoanimal($nombreTipoAnimal);
                                    $tipoanimal->setDescripciontipoanimal($descripcionTipoAnimal);
                                    $tipoanimal->setTipoAnimalactivo($tipoanimalActivo);
                                    $tipoanimal->setCreaciontipoanimal($today);
                                    $tipoanimal->setModificaciontipoanimal($today);
                                                
                                    $em->persist($tipoanimal);
                                    $em->flush();
            
                                    $data = array(
                                        'status'     => 'Exito',
                                        'msg'        => 'Tipo de Animal creado!!',
                                        'tipoanimal' => $tipoanimal,
                                    );

                                    $idTipoAnimal = $tipoanimal->getPkidtipoanimal();
                                
                                    //una vez insertados los datos en la tipoanimal se realiza el proceso de auditoria
                                    $datos = array(
                                        'idusuario'             => $identity->sub,
                                        'nombreusuario'         => $identity->name,
                                        'identificacionusuario' => $identity->identificacion,
                                        'accion'                => 'insertar',
                                        "tabla"                 => 'Ttipoanimal',
                                        "valoresrelevantes"     => 'idTipoAnimal:'.$idTipoAnimal.',nombreTipoAnimal:'.$nombreTipoAnimal,
                                        'idelemento'            => $idTipoAnimal,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'Nombre de tipo de animal duplicado!!'
                                    );
                                }
                                   
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El nombre del tipo de animal es nulo!!',
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
                'modulo'        => 'TipoAnimal',
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
     * Funcion para modificar un tipo de animal
     * recibe los datos en un json llamado json con los datos
     * pkidtipoanimal=>obligatorio, id del tipo de animal a editar
     * codigotipoanimal, puede ser vacio
     * nombretipoanimal, el nombre del tipo de animal
     * descripciontipoanimal, descripcion del tipo de animal, puede ser nulo 
     * tipoanimalactivo, si el tipo de animal esta activa o no
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editTipoAnimalAction(Request $request)
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

                    if (in_array("PERM_TIPO_ANIMALES", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la tipoanimal horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Tipo de Animal no actualizado!!',
                        );

                        if ($json != null) {
                            
                            $idTipoAnimal = (isset($params->pkidtipoanimal)) ? $params->pkidtipoanimal : null;

                            if($idTipoAnimal != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $tipoanimal = $em->getRepository('ModeloBundle:Ttipoanimal')->find($idTipoAnimal);
                    
                                if($tipoanimal){
                                    
                                    if(isset($params->codigotipoanimal)){
                                        $tipoanimal->setCodigotipoanimal($params->codigotipoanimal);
                                    }
                
                                    if(isset($params->nombretipoanimal)){
                                        $nombreTipoAnimal = $params->nombretipoanimal;

                                        //revisa en la tabla Ttipoanimal si el nombre que se desea asignar no existe en la misma plaza
                                        $query = $em->getRepository('ModeloBundle:Ttipoanimal')->createQueryBuilder('ta')
                                            ->where('ta.nombretipoanimal = :nombretipoanimal and ta.pkidtipoanimal != :pkidtipoanimal')
                                            ->setParameter('nombretipoanimal', $nombreTipoAnimal)
                                            ->setParameter('pkidtipoanimal', $idTipoAnimal)
                                            ->getQuery();
                                        
                                        $tipoanimalDuplicated = $query->getResult();

                                        if(!$tipoanimalDuplicated){
                                            $tipoanimal->setNombretipoanimal($params->nombretipoanimal);
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Nombre de tipo de animal duplicado!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }

                                    if(isset($params->descripciontipoanimal)){
                                        $tipoanimal->setDescripciontipoanimal($params->descripciontipoanimal);
                                    }
                
                                    if(isset($params->tipoanimalactivo)){
                                        $tipoanimal->setTipoAnimalactivo($params->tipoanimalactivo);
                                    }                                   
                
                                    $tipoanimal->setModificaciontipoanimal($today);
                
                                    $em->persist($tipoanimal);
                                    $em->flush();
                
                                    $data = array(
                                        'status'     => 'Exito',
                                        'msg'        => 'Tipo de Animal actualizado!!',
                                        'tipoanimal' => $tipoanimal,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Ttipoanimal',
                                        "valoresrelevantes"     => 'idTipoAnimal:'.$idTipoAnimal.',nombreTipoAnimal:'.$tipoanimal->getNombretipoanimal(),
                                        'idelemento'            => $idTipoAnimal,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'El tipo de animal no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del tipo de animal a editar es nulo!!'
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
                'modulo'        => "TipoAnimal",
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
     * Funcion para eliminar una tipoanimal
     * recibe los datos en un json llamado json con los datos
     * pkidtipoanimal=>obligatorio, id de la tipoanimal a eliminar
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function removeTipoAnimalAction(Request $request)
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

                    if (in_array("PERM_TIPO_ANIMALES", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la tipoanimal horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Tipo de Animal no actualizado!!',
                        );

                        if ($json != null) {
                            
                            $idTipoAnimal = (isset($params->pkidtipoanimal)) ? $params->pkidtipoanimal : null;

                            if($idTipoAnimal != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $tipoanimal = $em->getRepository('ModeloBundle:Ttipoanimal')->find($idTipoAnimal);
                    
                                if($tipoanimal){
                                    $nombreTipoAnimal = $tipoanimal->getNombretipoanimal();

                                    //especie animal
                                    $especieanimal = $em->getRepository('ModeloBundle:Tespecieanimal')->findOneBy(array(
                                        'fkidtipoanimal' => $idTipoAnimal
                                    ));
                                    //tarifa pesaje
                                    $tarifaPesaje = $em->getRepository('ModeloBundle:Ttarifapesaje')->findOneBy(array(
                                        'fkidtipoanimal' => $idTipoAnimal
                                    ));
                                    //tarifa animal
                                    $tarifaAnimal = $em->getRepository('ModeloBundle:Ttarifaanimal')->findOneBy(array(
                                        'fkidtipoanimal' => $idTipoAnimal
                                    ));

                                    if(!$especieanimal){
                                        
                                        if(!$tarifaPesaje){

                                            if(!$tarifaAnimal){
                                        
                                                $em->remove($tipoanimal);
                                                $em->flush();

                                                $data = array(
                                                    'status' => 'Exito',
                                                    'msg'    => 'Tipo de animal eliminado!!'
                                                );
                
                                                $datos = array(
                                                    "idusuario"             => $identity->sub,
                                                    "nombreusuario"         => $identity->name,
                                                    "identificacionusuario" => $identity->identificacion,
                                                    'accion'                => 'eliminar',
                                                    "tabla"                 => 'Ttipoanimal',
                                                    "valoresrelevantes"     => 'idTipoAnimal:'.$idTipoAnimal.',nombreTipoAnimal:'.$nombreTipoAnimal,
                                                    'idelemento'            => $idTipoAnimal,
                                                    'origen'                => 'web'
                                                );
                
                                                $auditoria = $this->get(Auditoria::class);
                                                $auditoria->auditoria(json_encode($datos)); 
                                            }else{
                                                $data = array(
                                                    'status' => 'Error',
                                                    'msg'    => 'No se puede eliminar el tipo de animal, tiene tarifas de animales asignadas!!'
                                                );
                                            }
                                        }else{
                                            $data = array(
                                                'status' => 'Error',
                                                'msg'    => 'No se puede eliminar el tipo de animal, tiene tarifas de pesaje asignados!!'
                                            );
                                        }

                                    }else{
                                        $data = array(
                                            'status' => 'Error',
                                            'msg'    => 'No se puede eliminar el tipo de animal, tiene especies de animal asignadas!!'
                                        );
                                    }
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'el tipo de animal no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del tipo de animal a eliminar es nulo!!'
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
                'modulo'        => "TipoAnimal",
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
     * Funcion para mostrar todas las tipoanimals registradas
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryTipoAnimalAction(Request $request)
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

                        $query = "SELECT pkidtipoanimal,nombretipoanimal,codigotipoanimal,descripciontipoanimal,tipoanimalactivo 
                                    FROM ttipoanimal where tipoanimalactivo=true
                                    ORDER BY nombretipoanimal ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tipoanimales = $stmt->fetchAll();
                        
			            $data = array(
                            'status'    => 'Success',
                            'tipoanimal'     => $tipoanimales,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_TIPO_ANIMALES", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        //Consulta para traer los datos de la tipoanimal, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidtipoanimal,nombretipoanimal,codigotipoanimal,descripciontipoanimal,tipoanimalactivo 
                                    FROM ttipoanimal
                                    ORDER BY nombretipoanimal ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tipoanimales = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($tipoanimales as $tipoanimal) {
                            $tipoanimalList = array(
                                "pkidtipoanimal"        => $tipoanimal['pkidtipoanimal'],
                                "nombretipoanimal"      => $tipoanimal['nombretipoanimal'],
                                "codigotipoanimal"      => $tipoanimal['codigotipoanimal'],
                                "descripciontipoanimal" => $tipoanimal['descripciontipoanimal'],
                                "tipoanimalactivo"      => $tipoanimal['tipoanimalactivo']
                            );
                            array_push($array_all, $tipoanimalList);
                        }

                        $cabeceras=array();

                        $pkidtipoanimal = array("nombrecampo"=>"pkidtipoanimal","nombreetiqueta"=>"Id Tipo Animal","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");                       
                        $nombretipoanimal = array("nombrecampo"=>"nombretipoanimal","nombreetiqueta"=>"Nombre Tipo Animal","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $codigotipoanimal = array("nombrecampo"=>"codigotipoanimal","nombreetiqueta"=>"Codigo Tipo Animal","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $descripciontipoanimal = array("nombrecampo"=>"descripciontipoanimal","nombreetiqueta"=>"Descripcion Tipo Animal","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $tipoanimalactivo = array("nombrecampo"=>"tipoanimalactivo","nombreetiqueta"=>"Tipo Animal Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");              
                        
                        
                        array_push($cabeceras,$pkidtipoanimal);
                        array_push($cabeceras,$nombretipoanimal);
                        array_push($cabeceras,$codigotipoanimal);
                        array_push($cabeceras,$descripciontipoanimal);
                        array_push($cabeceras,$tipoanimalactivo);

                        $title=array("Nuevo","Tipo Animal");

                        $data = array(
                            'status'     => 'Success',
                            'cabeceras'  => $cabeceras,
                            'tipoanimal' => $array_all,
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
                'modulo'        => "TipoAnimal",
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