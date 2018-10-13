<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tusuario;
use ModeloBundle\Entity\Tbeneficiario;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TbeneficiarioController extends Controller
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
     * Funcion para registrar un beneficiario
     * recibe los datos en un json llamado json con los datos
     * codigobeneficiario, puede ser nulo
     * nombrebeneficiario, el nombre del beneficiario
     * identificacionbeneficiario, una identificacon que sea unico para el beneficiario
     * generobeneficiario, el genero del beneficiario, femenino - masculino, puede ser nulo
     * edadbeneficiario, la edad actual del beneficiario, puede ser nulo
     * direccionbeneficiario, la direccion de residencia del beneficiario, puede ser nulo
     * telefonobeneficiario, el telefono de contacto actuual del beneficiario, puede ser nulo
     * observaciones, puede ser nulo
     * beneficiarioactivo, si la beneficiario esta activo o no
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newBeneficiarioAction(Request $request)
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

                    if (in_array("PERM_BENEFICIARIOS", $permisosDeserializados)) {
                       
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
                            'msg'    => 'Beneficiario no creado!!',
                        );

                        if ($json != null) {

                            $codigoBeneficiario = (isset($params->codigobeneficiario)) ? $params->codigobeneficiario : null;
                            $nombreBeneficiario = (isset($params->nombrebeneficiario)) ? $params->nombrebeneficiario : null;
                            $identificacionBeneficiario = (isset($params->identificacionbeneficiario)) ? $params->identificacionbeneficiario : null;
                            $generoBeneficiario = (isset($params->generobeneficiario)) ? $params->generobeneficiario : null;
                            $edadBeneficiario = (isset($params->edadbeneficiario)) ? $params->edadbeneficiario : null;
                            $direccionBeneficiario = (isset($params->direccionbeneficiario)) ? $params->direccionbeneficiario : null;
                            $telefonoBeneficiario = (isset($params->telefonobeneficiario)) ? $params->telefonobeneficiario : null;
                            $observaciones = (isset($params->observaciones)) ? $params->observaciones : null;
                            $beneficiarioActivo = (isset($params->beneficiarioactivo)) ? $params->beneficiarioactivo : true;
                                                    
                            if($identificacionBeneficiario != null && $nombreBeneficiario !=null){
                               
                                $beneficiarioDuplicated = $em->getRepository('ModeloBundle:Tbeneficiario')->findOneBy(array(
                                    "identificacionbeneficiario" => $identificacionBeneficiario,
                                ));

                                if(!$beneficiarioDuplicated){

                                    $beneficiario = new Tbeneficiario();
                                    $beneficiario->setCodigobeneficiario($codigoBeneficiario); 
                                    $beneficiario->setnombrebeneficiario($nombreBeneficiario);
                                    $beneficiario->setIdentificacionbeneficiario($identificacionBeneficiario);
                                    $beneficiario->setGenerobeneficiario($generoBeneficiario);
                                    $beneficiario->setEdadbeneficiario($edadBeneficiario);
                                    $beneficiario->setDireccionbeneficiario($direccionBeneficiario);
                                    $beneficiario->setTelefonobeneficiario($telefonoBeneficiario);
                                    $beneficiario->setObservaciones($observaciones);
                                    $beneficiario->setBeneficiarioactivo($beneficiarioActivo);
                                    $beneficiario->setCreacionbeneficiario($today);
                                    $beneficiario->setModificacionbeneficiario($today);
                                                            
                                    $em->persist($beneficiario);
                                    $em->flush();
            
                                    $data = array(
                                        'status'       => 'Exito',
                                        'msg'          => 'Beneficiario creado!!',
                                        'beneficiario' => $beneficiario,
                                    );

                                    $idBeneficiario = $beneficiario->getPkidbeneficiario();
                                
                                    //una vez insertados los datos en el beneficiario se realiza el proceso de auditoria
                                    $datos = array(
                                        'idusuario'             => $identity->sub,
                                        'nombreusuario'         => $identity->name,
                                        'identificacionusuario' => $identity->identificacion,
                                        'accion'                => 'insertar',
                                        "tabla"                 => 'Tbeneficiario',
                                        "valoresrelevantes"     => 'idBeneficiario:'.$idBeneficiario.',nombreBeneficiario:'.$nombreBeneficiario,
                                        'idelemento'            => $idBeneficiario,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'Identificacion de beneficiario duplicada!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'Algunos de los valores son nulos!!',
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
                'modulo'        => 'Beneficiarios',
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
     * Funcion para modificar un beneficiario
     * recibe los datos en un json llamado json con los datos
     * pkidbeneficiario=>obligatorio, id del beneficiario a editar
     * codigobeneficiario, puede ser nulo
     * nombrebeneficiario, el nombre del beneficiario
     * identificacionbeneficiario, una identificacon que sea unico para el beneficiario
     * generobeneficiario, el genero del beneficiario, femenino - masculino, puede ser nulo
     * edadbeneficiario, la edad actual del beneficiario, puede ser nulo
     * direccionbeneficiario, la direccion de residencia del beneficiario, puede ser nulo
     * telefonobeneficiario, el telefono de contacto actuual del beneficiario, puede ser nulo
     * observaciones, puede ser nulo
     * beneficiarioactivo, si la beneficiario esta activo o no
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editBeneficiarioAction(Request $request)
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

                    if (in_array("PERM_BENEFICIARIOS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la beneficiario horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Beneficiario no actualizado!!',
                        );

                        if ($json != null) {
                            
                            $idBeneficiario = (isset($params->pkidbeneficiario)) ? $params->pkidbeneficiario : null;

                            if($idBeneficiario != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $beneficiario = $em->getRepository('ModeloBundle:Tbeneficiario')->find($idBeneficiario);
                    
                                if($beneficiario){
                                   
                                    if(isset($params->codigobeneficiario)){
                                        $beneficiario->setCodigobeneficiario($params->codigobeneficiario);
                                    }
                                    
                                    if(isset($params->nombrebeneficiario)){
                                        $beneficiario->setnombrebeneficiario($params->nombrebeneficiario);
                                    }

                                    if(isset($params->identificacionbeneficiario)){
                                        $identificacionBeneficiario = $params->identificacionbeneficiario;

                                        //revisa en la tabla Tbeneficiario si el nombre que se desea asignar existe
                                        $query = $em->getRepository('ModeloBundle:Tbeneficiario')->createQueryBuilder('b')
                                            ->where('b.identificacionbeneficiario = :identificacionbeneficiario and b.pkidbeneficiario != :pkidbeneficiario')
                                            ->setParameter('identificacionbeneficiario', $identificacionBeneficiario)
                                            ->setParameter('pkidbeneficiario', $idBeneficiario)
                                            ->getQuery();
                                        
                                        $beneficiarioDuplicated = $query->getResult();

                                        if(!$beneficiarioDuplicated){
                                            $beneficiario->setIdentificacionbeneficiario($identificacionBeneficiario);
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Identificacion de beneficiario duplicada!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }

                                    if(isset($params->generobeneficiario)){
                                        $beneficiario->setGenerobeneficiario($params->generobeneficiario);
                                    }

                                    if(isset($params->edadbeneficiario)){
                                        $beneficiario->setEdadbeneficiario($params->edadbeneficiario);
                                    }

                                    if(isset($params->direccionbeneficiario)){
                                        $beneficiario->setDireccionbeneficiario($params->direccionbeneficiario);
                                    }

                                    if(isset($params->telefonobeneficiario)){
                                        $beneficiario->setTelefonobeneficiario($params->telefonobeneficiario);
                                    }

                                    if(isset($params->observaciones)){
                                        $beneficiario->setObservaciones($params->observaciones);
                                    }
                
                                    if(isset($params->beneficiarioactivo)){
                                        $beneficiario->setBeneficiarioactivo($params->beneficiarioactivo);
                                    }                                   
                                
                                    $beneficiario->setModificacionbeneficiario($today);
                
                                    $em->persist($beneficiario);
                                    $em->flush();
                
                                    $data = array(
                                        'status'       => 'Exito',
                                        'msg'          => 'Beneficiario actualizado!!',
                                        'beneficiario' => $beneficiario,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Tbeneficiario',
                                        "valoresrelevantes"     => 'idBeneficiario:'.$idBeneficiario.',nombreBeneficiario:'.$beneficiario->getNombrebeneficiario(),
                                        'idelemento'            => $idBeneficiario,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'El beneficiario no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del beneficiario a editar es nulo!!'
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
                'modulo'        => "Beneficiarios",
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
     * Funcion para eliminar un beneficiario
     * recibe los datos en un json llamado json con los datos
     * pkidbeneficiario=>obligatorio, id del beneficiario a eliminar
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function removeBeneficiarioAction(Request $request)
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

                    if (in_array("PERM_BENEFICIARIOS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la beneficiario horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Beneficiario no actualizado!!',
                        );

                        if ($json != null) {
                            
                            $idBeneficiario = (isset($params->pkidbeneficiario)) ? $params->pkidbeneficiario : null;

                            if($idBeneficiario != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $beneficiario = $em->getRepository('ModeloBundle:Tbeneficiario')->find($idBeneficiario);
                    
                                if($beneficiario){
                                    $nombreBeneficiario = $beneficiario->getNombrebeneficiario();
                                    
                                    $asignacionPuesto = $em->getRepository('ModeloBundle:Tasignacionpuesto')->findOneBy(array(
                                        'fkidbeneficiario' => $idBeneficiario
                                    ));

                                    if(!$asignacionPuesto){

                                        $cartera = $em->getRepository('ModeloBundle:Tproceso')->findOneBy(array(
                                            'fkidbeneficiario' => $idBeneficiario
                                        ));
    
                                        if(!$cartera){

                                            $documento = $em->getRepository('ModeloBundle:Tdocumentobeneficiario')->findOneBy(array(
                                                'fkidbeneficiario' => $idBeneficiario
                                            ));
        
                                            if(!$documento){
                                                $em->remove($beneficiario);
                                                $em->flush();

                                                $data = array(
                                                    'status' => 'Exito',
                                                    'msg'    => 'Beneficiario eliminado!!'
                                                );
                
                                                $datos = array(
                                                    "idusuario"             => $identity->sub,
                                                    "nombreusuario"         => $identity->name,
                                                    "identificacionusuario" => $identity->identificacion,
                                                    'accion'                => 'eliminar',
                                                    "tabla"                 => 'Tbeneficiario',
                                                    "valoresrelevantes"     => 'idBeneficiario:'.$idBeneficiario.',nombreBeneficiario:'.$nombreBeneficiario,
                                                    'idelemento'            => $idBeneficiario,
                                                    'origen'                => 'web'
                                                );
                
                                                $auditoria = $this->get(Auditoria::class);
                                                $auditoria->auditoria(json_encode($datos)); 
                                            }else{
                                                $data = array(
                                                    'status' => 'Error',
                                                    'msg'    => 'No se puede eliminar el beneficiario, tiene documentos asociados!!'
                                                );
                                            }
                                        }else{
                                            $data = array(
                                                'status' => 'Error',
                                                'msg'    => 'No se puede eliminar el beneficiario, tiene procesos asignados!!'
                                            );
                                        }
                                    }else{
                                        $data = array(
                                            'status' => 'Error',
                                            'msg'    => 'No se puede eliminar el beneficiario, tiene asignaciones de puesto asociadas!!'
                                        );
                                    }
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'El beneficiario no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id del beneficiario a eliminar es nulo!!'
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
                'modulo'        => "Beneficiarios",
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
     * Funcion para mostrar todas los beneficiarios registrados
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryBeneficiarioAction(Request $request)
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
                        
                        $query = "SELECT pkidbeneficiario,identificacionbeneficiario,nombrebeneficiario
                                  FROM tbeneficiario where beneficiarioactivo=true                             
                                  ORDER BY nombrebeneficiario ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $beneficiarios = $stmt->fetchAll();

                        $data = array(
                            'status'    => 'Exito',
                            'beneficiario' => $beneficiarios,
                        );

                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_BENEFICIARIOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        //Consulta para traer los datos de la beneficiario, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidbeneficiario,identificacionbeneficiario,nombrebeneficiario,
                                         codigobeneficiario,generobeneficiario,edadbeneficiario,direccionbeneficiario,
                                         telefonobeneficiario,observaciones,beneficiarioactivo 
                                  FROM tbeneficiario                             
                                  ORDER BY nombrebeneficiario ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $beneficiarios = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($beneficiarios as $beneficiario) {
                            $beneficiariosList = array(
                                "pkidbeneficiario"           => $beneficiario['pkidbeneficiario'],
                                "identificacionbeneficiario" => $beneficiario['identificacionbeneficiario'],
                                "nombrebeneficiario"         => $beneficiario['nombrebeneficiario'],
                                "codigobeneficiario"         => $beneficiario['codigobeneficiario'],
                                "generobeneficiario"         => $beneficiario['generobeneficiario'],
                                "edadbeneficiario"           => $beneficiario['edadbeneficiario'],
                                "direccionbeneficiario"      => $beneficiario['direccionbeneficiario'],
                                "telefonobeneficiario"       => $beneficiario['telefonobeneficiario'],
                                "observaciones"              => $beneficiario['observaciones'],
                                "beneficiarioactivo"         => $beneficiario['beneficiarioactivo'],
                            );
                            array_push($array_all, $beneficiariosList);
                        }

                        $cabeceras=array(
                            array("nombrecampo"=>"pkidbeneficiario","nombreetiqueta"=>"Id Beneficiario","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number"),   
                            array("nombrecampo"=>"identificacionbeneficiario","nombreetiqueta"=>"Identificacion Beneficiario","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=> false,"type"=>"string"),  
                            array("nombrecampo"=>"nombrebeneficiario","nombreetiqueta"=>"Nombre Beneficiario","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string"),  
                            array("nombrecampo"=>"codigobeneficiario","nombreetiqueta"=>"codigo Beneficiario","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string"),  
                            array("nombrecampo"=>"generobeneficiario","nombreetiqueta"=>"Genero Beneficiario","create-required"=>false,"update-required"=>false,"update"=> true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string"),  
                            array("nombrecampo"=>"edadbeneficiario","nombreetiqueta"=>"Edad Beneficiario","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"number"),  
                            array("nombrecampo"=>"direccionbeneficiario","nombreetiqueta"=>"Direccion Beneficiario","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string"),  
                            array("nombrecampo"=>"telefonobeneficiario","nombreetiqueta"=>"Telefono Beneficiario","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"number"),  
                            array("nombrecampo"=>"observaciones","nombreetiqueta"=>"Observaciones","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string"),  
                            array("nombrecampo"=>"beneficiarioactivo","nombreetiqueta"=>"Beneficiario Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean"),                      
                        );

                        $title=array("Nuevo","Beneficiario");

                        $data = array(
                            'status'        => 'Success',
                            'cabeceras'     => $cabeceras,
                            'beneficiario' => $array_all,
                            'title'         => $title
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
                'modulo'        => "Beneficiario",
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