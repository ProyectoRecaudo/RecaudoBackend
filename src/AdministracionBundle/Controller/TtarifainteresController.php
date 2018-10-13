<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttarifainteres;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtarifainteresController extends Controller
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
     * Funcion para registrar una tarifainteres
     * recibe los datos en un json llamado json con los datos
     * valortarifainteres, valor de la tarifa que se va a asignar
     * descripciontarifainteres, una breve descripcion de la tarifa, puede ser nulo
     * numeroresoluciontarifainteres, el numero de la resolucion que oficializa la tarifa
     * tarifainteresactivo, si la tarifainteres esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fechainicio, fecha de inicio de validez de la tarifa
     * fechafin, fecha fin de validez de la tarifa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newTarifaInteresAction(Request $request)
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

                    if (in_array("PERM_TARIFAS", $permisosDeserializados)) {
                       
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
                            'msg'    => 'Tarifa Interes no creada!!',
                        );

                        if ($json != null) {

                            $valorTarifaInteres = (isset($params->valortarifainteres)) ? $params->valortarifainteres : null;
                            $descripcionTarifaInteres = (isset($params->descripciontarifainteres)) ? $params->descripciontarifainteres : null;
                            $numeroresolucionTarifaInteres = (isset($params->numeroresoluciontarifainteres)) ? $params->numeroresoluciontarifainteres : null;
                            $documentoresolucionTarifaInteres = (isset($params->documentoresoluciontarifainteres)) ? $params->documentoresoluciontarifainteres : null;
                            $tarifaInteresActivo = (isset($params->tarifainteresactivo)) ? $params->tarifainteresactivo : true;
                            $idPlaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null;  
                            $fechaInicio = (isset($params->fechainicio)) ? new \DateTime($params->fechainicio) : null;
                            $fechaFin = (isset($params->fechafin)) ? new \DateTime($params->fechafin) : null;
                           
                            if($idPlaza != null && $valorTarifaInteres !=null && $numeroresolucionTarifaInteres !=null){
                                $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                
                                if($plaza){
                                    $resolucionDuplicated = $em->getRepository('ModeloBundle:Ttarifainteres')->findOneBy(array(
                                        "numeroresoluciontarifainteres" => $numeroresolucionTarifaInteres
                                    ));

                                    if(!$resolucionDuplicated){

                                        $ultimaTarifaInteres = $em->getRepository('ModeloBundle:Ttarifainteres')->findOneBy(array(
                                            "tarifainteresactivo" => true,
                                            "fkidplaza" => $idPlaza
                                        ));

                                        //si existe una tarifa activa, la desactiva para crear una nueva. 
                                        if($ultimaTarifaInteres && $tarifaInteresActivo == true){
                                            $ultimaTarifaInteres->setTarifaInteresactivo(false);
                                        }
                                        
                                        $tarifainteres = new Ttarifainteres();
                                        $tarifainteres->setValortarifainteres($valorTarifaInteres); 
                                        $tarifainteres->setDescripciontarifainteres($descripcionTarifaInteres); 
                                        $tarifainteres->setNumeroresoluciontarifainteres($numeroresolucionTarifaInteres); 
                                        $tarifainteres->setDocumentoresoluciontarifainteres("sin documento");
                                        $tarifainteres->setTarifaInteresactivo($tarifaInteresActivo);
                                        $tarifainteres->setCraciontarifainteres($today);
                                        $tarifainteres->setModificaciontarifainteres($today);
                                        $tarifainteres->setFkidplaza($plaza);
                                        $tarifainteres->setFechainicio($fechaInicio);
                                        $tarifainteres->setFechafin($fechaFin);

                                        if (isset($_FILES['fichero_usuario'])) {

                                            if ($_FILES['fichero_usuario']['size'] <= 5242880) {
                                                                                                
                                                if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
        
                                                    $em->persist($tarifainteres);
                                                    $em->flush();
                                                    
                                                    $idTarifaInteres = $tarifainteres->getPkidtarifainteres();
        
                                                    $dir_subida = '../web/documentos/';
                                                    $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                    $fichero_subido = $dir_subida . basename($idTarifaInteres . "_tarifainteres_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
        
                                                    if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                        $tarifainteres_doc = $em->getRepository('ModeloBundle:Ttarifainteres')->findOneBy(array(
                                                            "pkidtarifainteres" => $tarifainteres->getPkidtarifainteres(),
                                                        ));
                                                        
                                                        $tarifainteres_doc->setDocumentoresoluciontarifainteres($fichero_subido);
                                                        $em->persist($tarifainteres_doc);
                                                        $em->flush();
        
                                                        $data = array(
                                                            'status'       => 'Exito',
                                                            'msg'          => 'tarifa interes creada !!',
                                                            'tarifainteres' => $tarifainteres_doc,
                                                        );
        
                                                        $datos = array(
                                                            'idusuario'             => $identity->sub,
                                                            'nombreusuario'         => $identity->name,
                                                            'identificacionusuario' => $identity->identificacion,
                                                            'accion'                => 'insertar',
                                                            "tabla"                 => 'Ttarifainteres',
                                                            "valoresrelevantes"     => 'idTarifaInteres:'.$idTarifaInteres.',valorTarifaInteres:'.$valorTarifaInteres,
                                                            'idelemento'            => $idTarifaInteres,
                                                            'origen'                => 'web'
                                                        );
                    
                                                        $auditoria = $this->get(Auditoria::class);
                                                        $auditoria->auditoria(json_encode($datos));
        
                                                    } else {
                                                        $em->remove($tarifainteres);
                                                        $em->flush();
        
                                                        $data = array(
                                                            'status' => 'Error',
                                                            'msg'    => 'No se ha podido ingresar el documento pdf, intente nuevamente !!',
                                                        );
                                                    }
                                                } else {
                                                    $data = array(
                                                        'status' => 'Error',
                                                        'msg'    => 'Solo se aceptan archivos en formato PDF !!',
                                                    );
                                                }
                                            } else {
                                                $data = array(
                                                    'status' => 'Error',
                                                    'msg'    => 'El tamaño del documento debe ser MAX 5MB !!',
                                                );
                                            }
                                        }else{
                                            $em->persist($tarifainteres);
                                            $em->flush();
                    
                                            $data = array(
                                                'status'       => 'Exito',
                                                'msg'          => 'Tarifa Interes creada!!',
                                                'tarifainteres' => $tarifainteres,
                                            );
    
                                            $idTarifaInteres = $tarifainteres->getPkidtarifainteres();
                                        
                                            //una vez insertados los datos en la tarifainteres se realiza el proceso de auditoria
                                            $datos = array(
                                                'idusuario'             => $identity->sub,
                                                'nombreusuario'         => $identity->name,
                                                'identificacionusuario' => $identity->identificacion,
                                                'accion'                => 'insertar',
                                                "tabla"                 => 'Ttarifainteres',
                                                "valoresrelevantes"     => 'idTarifaInteres:'.$idTarifaInteres.',valorTarifaInteres:'.$valorTarifaInteres,
                                                'idelemento'            => $idTarifaInteres,
                                                'origen'                => 'web'
                                            );
            
                                            $auditoria = $this->get(Auditoria::class);
                                            $auditoria->auditoria(json_encode($datos));
                                        }
                                    }else{
                                        $data = array(
                                            'status' => 'error',
                                            'msg'    => 'resolucion duplicada!!'
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
                'modulo'        => 'TarifaInteres',
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
     * Funcion para modificar una tarifainteres
     * recibe los datos en un json llamado json con los datos
     * pkidtarifainteres=>obligatorio, id de la tarifainteres a editar
     * valortarifainteres, valor de la tarifa que se va a asignar
     * descripciontarifainteres, una breve descripcion de la tarifa, puede ser nulo
     * numeroresoluciontarifainteres, el numero de la resolucion que oficializa la tarifa
     * tarifainteresactivo, si la tarifainteres esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fechainicio, fecha de inicio de validez de la tarifa
     * fechafin, fecha fin de validez de la tarifa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editTarifaInteresAction(Request $request)
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

                    if (in_array("PERM_TARIFAS", $permisosDeserializados)) {
                        
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
                            'msg'    => 'Tarifa Interes no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idTarifaInteres = (isset($params->pkidtarifainteres)) ? $params->pkidtarifainteres : null;

                            if($idTarifaInteres != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $tarifainteres = $em->getRepository('ModeloBundle:Ttarifainteres')->find($idTarifaInteres);
                    
                                if($tarifainteres){
                          
                                    if(isset($params->valortarifainteres)){
                                        if($params->valortarifainteres != $tarifainteres->getValortarifainteres()){
                                            $tarifainteres->setValortarifainteres($params->valortarifainteres);
                                            $tarifainteres->setValorincrementoporcentual(0);
                                        }
                                    }

                                    if(isset($params->descripciontarifainteres)){
                                        $tarifainteres->setDescripciontarifainteres($params->descripciontarifainteres);
                                    }

                                    if(isset($params->numeroresoluciontarifainteres)){
                                        $numeroresolucionTarifaInteres = $params->numeroresoluciontarifainteres;

                                        //revisa en la tabla Ttarifainteres si el valor que se desea asignar no existe en la misma plaza y en el mismo tipo
                                         $query = $em->getRepository('ModeloBundle:Ttarifainteres')->createQueryBuilder('t')
                                            ->where('t.numeroresoluciontarifainteres = :numeroresoluciontarifainteres and t.pkidtarifainteres != :pkidtarifainteres')
                                            ->setParameter('numeroresoluciontarifainteres', $numeroresolucionTarifaInteres)
                                            ->setParameter('pkidtarifainteres', $idTarifaInteres)
                                            ->getQuery();
                                     
                                        $resolucionDuplicated = $query->getResult();

                                        if(!$resolucionDuplicated){
                                            $tarifainteres->setNumeroresoluciontarifainteres($numeroresolucionTarifaInteres);
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Resolucion duplicada!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                                    
                                    if(isset($params->fkidplaza)){
                                        
                                        $idPlaza = $params->fkidplaza;
                                        $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                                                                                
                                        if($plaza){
                                            $tarifainteres->setFkidplaza($plaza);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La plaza no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idPlaza = $tarifainteres->getFkidplaza();
                                    }
                
                                    if(isset($params->tarifainteresactivo)){

                                        if($params->tarifainteresactivo == true){
                                            
                                            $tarifaInteresDuplicated = $em->createQueryBuilder()->select("t")
                                                ->from('ModeloBundle:Ttarifainteres', 't')
                                                ->where('t.tarifainteresactivo = :tarifainteresactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->andwhere('t.pkidtarifainteres != :pkidtarifainteres')
                                                ->setParameter('tarifainteresactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->setParameter('pkidtarifainteres', $idTarifaInteres)
                                                ->getQuery()
                                                ->getResult();
                                            
                                            if($tarifaInteresDuplicated){
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'Ya existe una tarifa interes activa en la misma plaza!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                        $tarifainteres->setTarifainteresactivo($params->tarifainteresactivo);                                        
                                    }

                                    $tarifainteres->setModificaciontarifainteres($today);

                                    if(isset($params->fechainicio)){
                                        $tarifainteres->setFechainicio(new \DateTime($params->fechainicio));
                                    }

                                    if(isset($params->fechafin)){
                                        $tarifainteres->setFechafin(new \DateTime($params->fechafin));
                                    }

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
    
                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
    
                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($idTarifaInteres . "_tarifainteres_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
    
                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                    $doc_old = $tarifainteres->getDocumentoresoluciontarifainteres();
                                                    if ($doc_old != "sin documento") {
                                                        unlink($doc_old);
                                                    }
                                                    $tarifainteres->setDocumentoresoluciontarifainteres($fichero_subido);
                                                } else {
                                                    $data = array(
                                                        'status' => 'Error',
                                                        'msg'    => 'No se ha podido ingresar el documento pdf, intente nuevamente !!',
                                                    );
                                                    return $helpers->json($data);
                                                }
                                            } else {
                                                $data = array(
                                                    'status' => 'Error',
                                                    'msg'    => 'Solo se aceptan archivos en formato PDF !!',
                                                );
                                                return $helpers->json($data);
                                            }
                                        } else {
                                            $data = array(
                                                'status' => 'Error',
                                                'msg'    => 'El tamaño del documento debe ser MAX 5MB !!',
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $documento = $params->documentoresoluciontarifainteres;
                                        /**
                                         * Si no se envia un documento y la ruta es falsa, se elimina el documento
                                         */
                                        if(!isset($documento) || $documento == false){
                                            
                                            $documento_old = $tarifainteres->getDocumentoresoluciontarifainteres();
                                            if($documento_old != 'sin documento'){
                                                unlink($documento_old);
                                                $tarifainteres->setDocumentoresoluciontarifainteres("sin documento");
                                        
                                            }
                                        }
                                    }
                
                                    $em->persist($tarifainteres);
                                    $em->flush();
                
                                    $data = array(
                                        'status'        => 'Exito',
                                        'msg'           => 'Tarifa Interes actualizada!!',
                                        'tarifainteres' => $tarifainteres,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Ttarifainteres',
                                        "valoresrelevantes"     => 'idTarifaInteres:'.$idTarifaInteres.',valorTarifaInteres:'.$tarifainteres->getValortarifainteres(),
                                        'idelemento'            => $idTarifaInteres,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La tarifa interes no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la tarifa interes a editar es nulo!!'
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
                'modulo'        => "TarifaInteres",
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
     * Funcion para mostrar las tarifainteress registradas
     * Recibe los parametros para filtrar
     * pkidplaza, filtro por plazas a la que pertenece la tarifa
     * pidtipoanimal, tipo de animal al que pertenece la tarifa
     * al enviar los parametros filtra tambien por tarifas activas
     * con el id de la plaza que se desea conocer.
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryTarifaInteresAction(Request $request)
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

                    $idPlaza = $request->get('pkidplaza', null); 

                    if($idPlaza != null){

                        if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                            $em = $this->getDoctrine()->getManager();
                            $db = $em->getConnection();
                            
                            //Consulta de tarifa interes por plaza
                                                           
                            $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                                                        
                            if($plaza){
                                $tarifasInteres = $em->getRepository('ModeloBundle:Ttarifainteres')->createQueryBuilder('t')
                                                ->select('t.pkidtarifainteres,t.valortarifainteres')
                                                ->where('t.tarifainteresactivo = :tarifainteresactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->setParameter('tarifainteresactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->getQuery()
                                                ->getResult();
                                
                                $data = array(
                                    'status'         => 'Exito',
                                    'tarifasinteres'  => $tarifasInteres,
                                );
                            }else{
                                $data = array(
                                    'status'=> 'Error',
                                    'msg'   => 'La plaza no existe!!'
                                );
                               
                            }
                        }
                        return $helpers->json($data);
                    }

                    if (in_array("PERM_TARIFAS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
                                         
                        //Consulta para traer los datos de la tarifainteres, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidtarifainteres,valortarifainteres,descripciontarifainteres,numeroresoluciontarifainteres,
                                         documentoresoluciontarifainteres,fechainicio,fechafin,fkidplaza,nombreplaza,tarifainteresactivo 
                                    FROM ttarifainteres 
                                        JOIN tplaza ON ttarifainteres.fkidplaza = tplaza.pkidplaza
                                    ORDER BY numeroresoluciontarifainteres ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tarifasinteres = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($tarifasinteres as $tarifainteres) {
                            $tarifasinteresList = array(
                                "pkidtarifainteres"                => $tarifainteres['pkidtarifainteres'],
                                "valortarifainteres"               => $tarifainteres['valortarifainteres'],
                                "descripciontarifainteres"         => $tarifainteres['descripciontarifainteres'],
                                "numeroresoluciontarifainteres"    => $tarifainteres['numeroresoluciontarifainteres'],
                                "documentoresoluciontarifainteres" => $tarifainteres['documentoresoluciontarifainteres'],
                                "fechainicio"                      => $tarifainteres['fechainicio'],
                                "fechafin"                         => $tarifainteres['fechafin'],
                                "pkidplaza"                        => $tarifainteres['fkidplaza'],
                                "nombreplaza"                      => $tarifainteres['nombreplaza'],
                                "tarifainteresactivo"              => $tarifainteres['tarifainteresactivo'],
                            );
                            array_push($array_all, $tarifasinteresList);
                        }

                        $cabeceras = array(
                            array(
                                "nombrecampo"    => "valortarifainteres",
                                "nombreetiqueta" => "Tarifa Interes"
                            ),
                            array(
                                "nombrecampo"    => "descripciontarifainteres",
                                "nombreetiqueta" => "Descripción"
                            ),
                            array(
                                "nombrecampo"    => "numeroresoluciontarifainteres",
                                "nombreetiqueta" => "Numero de Resolucion"
                            ),
                            array(
                                "nombrecampo"    => "documentoresoluciontarifainteres",
                                "nombreetiqueta" => "Documento"
                            ),
                            array(
                                "nombrecampo"    => "fechainicio",
                                "nombreetiqueta" => "Fecha de Inicio"
                            ),
                            array(
                                "nombrecampo"    => "fechafin",
                                "nombreetiqueta" => "Fecha de Fin"
                            ),
                            array(
                                "nombrecampo"    => "nombreplaza",
                                "nombreetiqueta" => "Plaza"
                            ),
                            array(
                                "nombrecampo"    => "tarifainteresactivo",
                                "nombreetiqueta" => "Tarifa Interes Activa/Inactiva"
                            ),
                        );

                        $data = array(
                            'status'        => 'Exito',
                            'cabeceras'     => $cabeceras,
                            'tarifainteres' => $array_all,
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
                'modulo'        => "TarifaInteres",
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