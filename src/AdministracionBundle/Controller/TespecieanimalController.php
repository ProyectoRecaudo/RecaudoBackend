<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttarifaanimal;
use ModeloBundle\Entity\Ttarifapesaje;
use ModeloBundle\Entity\Tespecieanimal;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TespecieanimalController extends Controller
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
     * Funcion para registrar una especie de animal
     * recibe los datos en un json llamado json con los datos
     * codigoespecieanimal
     * nombreespecieanimal, el nombre de la especie de animal
     * especieanimalactivo, si la especie de animal esta activa o no
     * fkidtipoanimal, el id del tipo de animal al que pertenece
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newEspecieAnimalAction(Request $request)
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

                    if (in_array("PERM_ESPECIE_ANIMALES", $permisosDeserializados)) {
                       
                        //fecha y hora actuales en la especieanimal horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));
                        
                        //entiti manager
                        $em = $this->getDoctrine()->getManager();

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Especie de animal no creada!!',
                        );

                        if ($json != null) {

                            $codigoEspecieAnimal = (isset($params->codigoespecieanimal)) ? $params->codigoespecieanimal : null;
                            $nombreEspecieAnimal = (isset($params->nombreespecieanimal)) ? $params->nombreespecieanimal : null;
                            $especieanimalActivo = (isset($params->especieanimalactivo)) ? $params->especieanimalactivo : true;
                            $idTipoAnimal = (isset($params->fkidtipoanimal)) ? $params->fkidtipoanimal : null;
                            
                            if($nombreEspecieAnimal !=null){
                                                                      
                                if($idTipoAnimal != null){
                                    $tipoAnimal = $em->getRepository('ModeloBundle:Ttipoanimal')->find($idTipoAnimal);
                                    
                                    $especieanimalDuplicated = $em->getRepository('ModeloBundle:Tespecieanimal')->findOneBy(array(
                                        "nombreespecieanimal" => $nombreEspecieAnimal,
                                    ));

                                    if(!$especieanimalDuplicated){
                                        $especieanimal = new Tespecieanimal();
                                        $especieanimal->setCodigoespecieanimal($codigoEspecieAnimal); 
                                        $especieanimal->setNombreespecieanimal($nombreEspecieAnimal);
                                        $especieanimal->setEspecieAnimalactivo($especieanimalActivo);
                                        $especieanimal->setCreacionespecieanimal($today);
                                        $especieanimal->setModificacionespecieanimal($today);
                                        $especieanimal->setFkidtipoanimal($tipoAnimal);
                                                
                                        $em->persist($especieanimal);
                                        $em->flush();
                
                                        $data = array(
                                            'status'        => 'Exito',
                                            'msg'           => 'Especie de animal creada!!',
                                            'especieanimal' => $especieanimal,
                                        );

                                        $idEspecieAnimal = $especieanimal->getPkidespecieanimal();
                                    
                                        //una vez insertados los datos en la especieanimal se realiza el proceso de auditoria
                                        $datos = array(
                                            'idusuario'             => $identity->sub,
                                            'nombreusuario'         => $identity->name,
                                            'identificacionusuario' => $identity->identificacion,
                                            'accion'                => 'insertar',
                                            "tabla"                 => 'Tespecieanimal',
                                            "valoresrelevantes"     => 'idEspecieAnimal:'.$idEspecieAnimal.',nombreEspecieAnimal:'.$nombreEspecieAnimal,
                                            'idelemento'            => $idEspecieAnimal,
                                            'origen'                => 'web'
                                        );
        
                                        $auditoria = $this->get(Auditoria::class);
                                        $auditoria->auditoria(json_encode($datos));

                                    }else{
                                        $data = array(
                                            'status' => 'error',
                                            'msg'    => 'Nombre de especie de animal duplicado!!'
                                        );
                                    }
                                }else{
                                    $data = array(
                                        'status'=> 'error',
                                        'msg'   => 'El tipo de animal es nulo!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El nombre de especie de animal es nulo!!',
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
                'modulo'        => 'EspecieAnimal',
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
     * Funcion para modificar una especie de animal
     * recibe los datos en un json llamado json con los datos
     * pkidespecieanimal=>obligatorio, id de la especie de animal a editar
     * codigoespecieanimal
     * nombreespecieanimal, el nombre de la especie de animal
     * especieanimalactivo, si la especie de animal esta activa o no
     * fkidtipoanimal, el id del tipo de animal al que pertenece
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editEspecieAnimalAction(Request $request)
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

                    if (in_array("PERM_ESPECIE_ANIMALES", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la especieanimal horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Especie de animal no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idEspecieAnimal = (isset($params->pkidespecieanimal)) ? $params->pkidespecieanimal : null;

                            if($idEspecieAnimal != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $especieanimal = $em->getRepository('ModeloBundle:Tespecieanimal')->find($idEspecieAnimal);
                    
                                if($especieanimal){
                                    
                                    if(isset($params->codigoespecieanimal)){
                                        $especieanimal->setCodigoespecieanimal($params->codigoespecieanimal);
                                    }
                
                                    if(isset($params->nombreespecieanimal)){
                                        $nombreEspecieAnimal = $params->nombreespecieanimal;

                                        //revisa en la tabla Tespecieanimal si el nombre que se desea asignar no existe en la misma plaza
                                        $query = $em->getRepository('ModeloBundle:Tespecieanimal')->createQueryBuilder('ea')
                                            ->where('ea.nombreespecieanimal = :nombreespecieanimal and ea.pkidespecieanimal != :pkidespecieanimal')
                                            ->setParameter('nombreespecieanimal', $nombreEspecieAnimal)
                                            ->setParameter('pkidespecieanimal', $idEspecieAnimal)
                                            ->getQuery();
                                        
                                        $especieanimalDuplicated = $query->getResult();

                                        if(!$especieanimalDuplicated){
                                            $especieanimal->setNombreespecieanimal($params->nombreespecieanimal);
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Nombre de especie de animal duplicado!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                
                                    if(isset($params->especieanimalactivo)){
                                        $especieanimal->setEspecieAnimalactivo($params->especieanimalactivo);
                                    }                                   
                
                                    if(isset($params->fkidtipoanimal)){
                                        $tipoanimal = $em->getRepository('ModeloBundle:Ttipoanimal')->find($params->fkidtipoanimal);
                                       
                                        if($tipoanimal){
                                            $especieanimal->setFkidtipoanimal($tipoanimal);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'El tipo de animal no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                
                                    $especieanimal->setModificacionespecieanimal($today);
                
                                    $em->persist($especieanimal);
                                    $em->flush();
                
                                    $data = array(
                                        'status'        => 'Exito',
                                        'msg'           => 'EspecieAnimal actualizada!!',
                                        'especieanimal' => $especieanimal,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Tespecieanimal',
                                        "valoresrelevantes"     => 'idEspecieAnimal:'.$idEspecieAnimal.',nombreEspecieAnimal:'.$especieanimal->getNombreespecieanimal(),
                                        'idelemento'            => $idEspecieAnimal,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La especie de animal no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la especie de animal a editar es nulo!!'
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
                'modulo'        => "EspecieAnimales",
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
     * Funcion para eliminar una especieanimal
     * recibe los datos en un json llamado json con los datos
     * pkidespecieanimal=>obligatorio, id de la especieanimal a eliminar
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function removeEspecieAnimalAction(Request $request)
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

                    if (in_array("PERM_ESPECIE_ANIMALES", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la especieanimal horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Especie de animal no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idEspecieAnimal = (isset($params->pkidespecieanimal)) ? $params->pkidespecieanimal : null;

                            if($idEspecieAnimal != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $especieanimal = $em->getRepository('ModeloBundle:Tespecieanimal')->find($idEspecieAnimal);
                    
                                if($especieanimal){
                                    $nombreEspecieAnimal = $especieanimal->getNombreespecieanimal();
                                    
                                    $em->remove($especieanimal);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg'    => 'Especie de animal eliminada!!'
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'eliminar',
                                        "tabla"                 => 'Tespecieanimal',
                                        "valoresrelevantes"     => 'idEspecieAnimal:'.$idEspecieAnimal.',nombreEspecieAnimal:'.$nombreEspecieAnimal,
                                        'idelemento'            => $idEspecieAnimal,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                        
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La especie de animal no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la especie de animal a eliminar es nulo!!'
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
                'modulo'        => "EspecieAnimal",
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
     * Funcion para mostrar todas las especieanimals registradas
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryEspecieAnimalAction(Request $request)
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


                    $idTipoAnimal = $request->get('pkidtipoanimal', null); 

                    if($idTipoAnimal != null){
                    
                        if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                            $em = $this->getDoctrine()->getManager();
                            $db = $em->getConnection();
                            
                            $especiesanimal = $em->getRepository('ModeloBundle:Tespecieanimal')->createQueryBuilder('e')
                                                ->select('e.pkidespecieanimal,e.nombreespecieanimal')
                                                ->where('e.especieanimalactivo = :especieanimalactivo')
                                                ->andwhere('e.fkidtipoanimal = :fkidtipoanimal')
                                                ->setParameter('especieanimalactivo', true)
                                                ->setParameter('fkidtipoanimal', $idTipoAnimal)
                                                ->getQuery()
                                                ->getResult();

                            $data = array(
                                'status'        => 'Exito',
                                'especieanimal' => $especiesanimal,
                            );

                        }else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'El usuario no tiene permisos genericos !!',
                            );
                        }
                        return $helpers->json($data);
                    }

                    if (in_array("PERM_ESPECIE_ANIMALES", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        //Consulta para traer los datos de la especieanimal, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidespecieanimal,nombreespecieanimal,codigoespecieanimal,fkidtipoanimal,nombretipoanimal,especieanimalactivo 
                                    FROM tespecieanimal 
                                    JOIN ttipoanimal ON tespecieanimal.fkidtipoanimal = ttipoanimal.pkidtipoanimal
                                    ORDER BY nombreespecieanimal ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $especiesanimal = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($especiesanimal as $especieanimal) {
                            $especiesanimal = array(
                                "pkidespecieanimal"   => $especieanimal['pkidespecieanimal'],
                                "nombreespecieanimal" => $especieanimal['nombreespecieanimal'],
                                "codigoespecieanimal" => $especieanimal['codigoespecieanimal'],
                                "tipoanimal"          => array("pkidtipoanimal" => $especieanimal['fkidtipoanimal'],"nombretipoanimal" => $especieanimal['nombretipoanimal']),
                                "especieanimalactivo" => $especieanimal['especieanimalactivo'],
                            );
                            array_push($array_all, $especiesanimal);
                        }

                        $cabeceras=array();

                        $pkidespecieanimal = array("nombrecampo"=>"pkidespecieanimal","nombreetiqueta"=>"Id Especie Animal","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");                       
                        $nombretipoanimal = array("nombrecampo"=>"nombretipoanimal","nombreetiqueta"=>"Nombre Especie Animal","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $codigoespecieanimal = array("nombrecampo"=>"codigoespecieanimal","nombreetiqueta"=>"Codigo Especie Animal","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $tipoanimal = array("nombrecampo"=>"tipoanimal","nombreetiqueta"=>"tipoanimal","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>false,"fk"=>true,"fktable"=>"tipoanimal","pk"=>false,"type"=>"number");                       
                        $especieanimalactivo = array("nombrecampo"=>"especieanimalactivo","nombreetiqueta"=>"Especie Animal Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");              
                        

                        
                        array_push($cabeceras,$pkidespecieanimal);
                        array_push($cabeceras,$nombretipoanimal);
                        array_push($cabeceras,$codigoespecieanimal);
                        array_push($cabeceras,$tipoanimal);
                        array_push($cabeceras,$especieanimalactivo);

                        $title=array("Nueva","Especie Animal");

                        $data = array(
                            'status'         => 'Success',
                            'cabeceras'      => $cabeceras,
                            'especieanimal'  => $array_all,
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
                'modulo'        => "EspecieAnimal",
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