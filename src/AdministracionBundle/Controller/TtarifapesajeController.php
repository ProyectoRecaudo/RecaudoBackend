<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttarifapesaje;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtarifapesajeController extends Controller
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
     * Funcion para registrar una tarifapesaje
     * recibe los datos en un json llamado json con los datos
     * valortarifapesaje, valor de la tarifa que se va a asignar
     * descripciontarifapesaje, una breve descripcion de la tarifa, puede ser nulo
     * numeroresoluciontarifapesaje, el numero de la resolucion que oficializa la tarifa
     * tarifapesajeactivo, si la tarifapesaje esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fechainicio, fecha de inicio de validez de la tarifa
     * fechafin, fecha fin de validez de la tarifa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newTarifaPesajeAction(Request $request)
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
                            'msg'    => 'Tarifa Pesaje no creada!!',
                        );

                        if ($json != null) {

                            $valorTarifaPesaje = (isset($params->valortarifapesaje)) ? $params->valortarifapesaje : null;
                            $descripcionTarifaPesaje = (isset($params->descripciontarifapesaje)) ? $params->descripciontarifapesaje : null;
                            $numeroresolucionTarifaPesaje = (isset($params->numeroresoluciontarifapesaje)) ? $params->numeroresoluciontarifapesaje : null;
                            $documentoresolucionTarifaPesaje = (isset($params->documentoresoluciontarifapesaje)) ? $params->documentoresoluciontarifapesaje : null;
                            $tarifaPesajeActivo = (isset($params->tarifapesajeactivo)) ? $params->tarifapesajeactivo : true;
                            $idPlaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null; 
                            $fechaInicio = (isset($params->fechainicio)) ? new \DateTime($params->fechainicio) : null;
                            $fechaFin = (isset($params->fechafin)) ? new \DateTime($params->fechafin) : null;
                           
                            if($idPlaza != null && $valorTarifaPesaje !=null && $numeroresolucionTarifaPesaje !=null && $fechaInicio != null && $fechaFin != null){
                                $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                
                                if($plaza){
                                    $resolucionDuplicated = $em->getRepository('ModeloBundle:Ttarifapesaje')->findOneBy(array(
                                        "numeroresoluciontarifapesaje" => $numeroresolucionTarifaPesaje
                                    ));

                                    if(!$resolucionDuplicated){

                                        $ultimaTarifaPesaje = $em->getRepository('ModeloBundle:Ttarifapesaje')->findOneBy(array(
                                            "tarifapesajeactivo" => true,
                                            "fkidplaza" => $idPlaza
                                        ));

                                        //si existe una tarifa activa, la desactiva para crear una nueva. 
                                        if($ultimaTarifaPesaje && $tarifaPesajeActivo == true){
                                            $ultimaTarifaPesaje->setTarifaPesajeactivo(false);
                                        }
                                        
                                        $tarifapesaje = new Ttarifapesaje();
                                        $tarifapesaje->setValortarifapesaje($valorTarifaPesaje); 
                                        $tarifapesaje->setDescripciontarifapesaje($descripcionTarifaPesaje); 
                                        $tarifapesaje->setNumeroresoluciontarifapesaje($numeroresolucionTarifaPesaje); 
                                        $tarifapesaje->setDocumentoresoluciontarifapesaje("sin documento");
                                        $tarifapesaje->setTarifaPesajeactivo($tarifaPesajeActivo);
                                        $tarifapesaje->setCreaciontarifapesaje($today);
                                        $tarifapesaje->setModificaciontarifapesaje($today);
                                        $tarifapesaje->setFkidplaza($plaza);
                                        $tarifapesaje->setFechainicio($fechaInicio);
                                        $tarifapesaje->setFechafin($fechaFin);

                                        if (isset($_FILES['fichero_usuario'])) {

                                            if ($_FILES['fichero_usuario']['size'] <= 5242880) {
                                                                                                
                                                if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
        
                                                    $em->persist($tarifapesaje);
                                                    $em->flush();
                                                    
                                                    $idTarifaPesaje = $tarifapesaje->getPkidtarifapesaje();
        
                                                    $dir_subida = '../web/documentos/';
                                                    $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                    $fichero_subido = $dir_subida . basename($idTarifaPesaje . "_tarifapesaje_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
        
                                                    if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                        $tarifapesaje_doc = $em->getRepository('ModeloBundle:Ttarifapesaje')->findOneBy(array(
                                                            "pkidtarifapesaje" => $tarifapesaje->getPkidtarifapesaje(),
                                                        ));
                                                        
                                                        $tarifapesaje_doc->setDocumentoresoluciontarifapesaje($fichero_subido);
                                                        $em->persist($tarifapesaje_doc);
                                                        $em->flush();
        
                                                        $data = array(
                                                            'status'       => 'Exito',
                                                            'msg'          => 'tarifa pesaje creada !!',
                                                            'tarifapesaje' => $tarifapesaje_doc,
                                                        );
        
                                                        $datos = array(
                                                            'idusuario'             => $identity->sub,
                                                            'nombreusuario'         => $identity->name,
                                                            'identificacionusuario' => $identity->identificacion,
                                                            'accion'                => 'insertar',
                                                            "tabla"                 => 'Ttarifapesaje',
                                                            "valoresrelevantes"     => 'idTarifaPesaje:'.$idTarifaPesaje.',valorTarifaPesaje:'.$valorTarifaPesaje,
                                                            'idelemento'            => $idTarifaPesaje,
                                                            'origen'                => 'web'
                                                        );
                    
                                                        $auditoria = $this->get(Auditoria::class);
                                                        $auditoria->auditoria(json_encode($datos));
        
                                                    } else {
                                                        $em->remove($tarifapesaje);
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
                                            $em->persist($tarifapesaje);
                                            $em->flush();
                    
                                            $data = array(
                                                'status'       => 'Exito',
                                                'msg'          => 'Tarifa Pesaje creada!!',
                                                'tarifapesaje' => $tarifapesaje,
                                            );
    
                                            $idTarifaPesaje = $tarifapesaje->getPkidtarifapesaje();
                                        
                                            //una vez insertados los datos en la tarifapesaje se realiza el proceso de auditoria
                                            $datos = array(
                                                'idusuario'             => $identity->sub,
                                                'nombreusuario'         => $identity->name,
                                                'identificacionusuario' => $identity->identificacion,
                                                'accion'                => 'insertar',
                                                "tabla"                 => 'Ttarifapesaje',
                                                "valoresrelevantes"     => 'idTarifaPesaje:'.$idTarifaPesaje.',valorTarifaPesaje:'.$valorTarifaPesaje,
                                                'idelemento'            => $idTarifaPesaje,
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
                'modulo'        => 'TarifaPesaje',
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
     * Funcion para modificar una tarifapesaje
     * recibe los datos en un json llamado json con los datos
     * pkidtarifapesaje=>obligatorio, id de la tarifapesaje a editar
     * valortarifapesaje, valor de la tarifa que se va a asignar
     * descripciontarifapesaje, una breve descripcion de la tarifa, puede ser nulo
     * numeroresoluciontarifapesaje, el numero de la resolucion que oficializa la tarifa
     * tarifapesajeactivo, si la tarifapesaje esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fechainicio, fecha de inicio de validez de la tarifa
     * fechafin, fecha fin de validez de la tarifa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editTarifaPesajeAction(Request $request)
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
                            'msg'    => 'Tarifa Pesaje no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idTarifaPesaje = (isset($params->pkidtarifapesaje)) ? $params->pkidtarifapesaje : null;

                            if($idTarifaPesaje != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $tarifapesaje = $em->getRepository('ModeloBundle:Ttarifapesaje')->find($idTarifaPesaje);
                    
                                if($tarifapesaje){
                          
                                    if(isset($params->valortarifapesaje)){
                                        if($params->valortarifapesaje != $tarifapesaje->getValortarifapesaje()){
                                            $tarifapesaje->setValortarifapesaje($params->valortarifapesaje);
                                            $tarifapesaje->setValorincrementoporcentual(0);
                                        }
                                    }

                                    if(isset($params->descripciontarifapesaje)){
                                        $tarifapesaje->setDescripciontarifapesaje($params->descripciontarifapesaje);
                                    }

                                    if(isset($params->numeroresoluciontarifapesaje)){
                                        $numeroresolucionTarifaPesaje = $params->numeroresoluciontarifapesaje;

                                        //revisa en la tabla Ttarifapesaje si el valor que se desea asignar no existe en la misma plaza y en el mismo tipo
                                         $query = $em->getRepository('ModeloBundle:Ttarifapesaje')->createQueryBuilder('t')
                                            ->where('t.numeroresoluciontarifapesaje = :numeroresoluciontarifapesaje and t.pkidtarifapesaje != :pkidtarifapesaje')
                                            ->setParameter('numeroresoluciontarifapesaje', $numeroresolucionTarifaPesaje)
                                            ->setParameter('pkidtarifapesaje', $idTarifaPesaje)
                                            ->getQuery();
                                     
                                        $resolucionDuplicated = $query->getResult();

                                        if(!$resolucionDuplicated){
                                            $tarifapesaje->setNumeroresoluciontarifapesaje($numeroresolucionTarifaPesaje);
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
                                            $tarifapesaje->setFkidplaza($plaza);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La plaza no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idPlaza = $tarifapesaje->getFkidplaza();
                                    }
                
                                    if(isset($params->tarifapesajeactivo)){

                                        if($params->tarifapesajeactivo == true){
                                            
                                            $tarifaPesajeDuplicated = $em->createQueryBuilder()->select("t")
                                                ->from('ModeloBundle:Ttarifapesaje', 't')
                                                ->where('t.tarifapesajeactivo = :tarifapesajeactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->andwhere('t.pkidtarifapesaje != :pkidtarifapesaje')
                                                ->setParameter('tarifapesajeactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->setParameter('pkidtarifapesaje', $idTarifaPesaje)
                                                ->getQuery()
                                                ->getResult();
                                            
                                            if($tarifaPesajeDuplicated){
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'Ya existe una tarifa pesaje activa en la misma plaza!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                        $tarifapesaje->setTarifapesajeactivo($params->tarifapesajeactivo);                                        
                                    }

                                    $tarifapesaje->setModificaciontarifapesaje($today);

                                    if(isset($params->fechainicio)){
                                        $tarifapesaje->setFechainicio(new \DateTime($params->fechainicio));
                                    }

                                    if(isset($params->fechafin)){
                                        $tarifapesaje->setFechafin(new \DateTime($params->fechafin));
                                    }

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
    
                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
    
                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($idTarifaPesaje . "_tarifapesaje_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
    
                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                    $doc_old = $tarifapesaje->getDocumentoresoluciontarifapesaje();
                                                    if ($doc_old != "sin documento") {
                                                        unlink($doc_old);
                                                    }
                                                    $tarifapesaje->setDocumentoresoluciontarifapesaje($fichero_subido);
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
                                        $documento = $params->documentoresoluciontarifapesaje;
                                        /**
                                         * Si no se envia un documento y la ruta es falsa, se elimina el documento
                                         */
                                        if(!isset($documento) || $documento == false){
                                            
                                            $documento_old = $tarifapesaje->getDocumentoresoluciontarifapesaje();

                                            if($documento_old != 'sin documento'){
                                                unlink($documento_old);
                                                $tarifapesaje->setDocumentoresoluciontarifapesaje("sin documento");
                                            }
                                        }
                                    }
                
                                    $em->persist($tarifapesaje);
                                    $em->flush();
                
                                    $data = array(
                                        'status'       => 'Exito',
                                        'msg'          => 'Tarifa Pesaje actualizada!!',
                                        'tarifapesaje' => $tarifapesaje,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Ttarifapesaje',
                                        "valoresrelevantes"     => 'idTarifaPesaje:'.$idTarifaPesaje.',valorTarifaPesaje:'.$tarifapesaje->getValortarifapesaje(),
                                        'idelemento'            => $idTarifaPesaje,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La tarifa pesaje no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la tarifa pesaje a editar es nulo!!'
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
                'modulo'        => "TarifaPesaje",
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
     * Funcion para mostrar las tarifapesajes registradas
     * Recibe pkidplaza para filtrar por plazas a la que pertenece la tarifa
     * activo para recornar las tarifas activas
     * con el id de la plaza que se desea conocer.
     * recibe el parametro tarifa con valor true para retornar las plazas que tangan
     * tarifas activas. 
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryTarifaPesajeAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        try {
            if (!empty($request->get('authorization'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                $em = $this->getDoctrine()->getManager();
                $db = $em->getConnection();

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    $tarifa = $request->get('tarifa', null); 

                    if($tarifa != null && $tarifa == true){

                        if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                            
                            //Consulta de tarifa pesaje por plaza
                                                           
                            $tarifasPesaje = $em->getRepository('ModeloBundle:Ttarifapesaje')->createQueryBuilder('t')
                                                ->select('p.pkidplaza,p.nombreplaza')
                                                ->join('t.fkidplaza','p')
                                                ->getQuery()
                                                ->getResult();
                                
                            $data = array(
                                'status'         => 'Exito',
                                'tarifaspesaje'  => $tarifasPesaje,
                            );
                            
                            return $helpers->json($data);
                        }
                    }

                    $idPlaza = $request->get('pkidplaza', null); 

                    if($idPlaza != null){

                        if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                           
                            //Consulta de tarifa pesaje por plaza
                                                           
                            $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                                                        
                            if($plaza){
                                $tarifasPesaje = $em->getRepository('ModeloBundle:Ttarifapesaje')->createQueryBuilder('t')
                                                ->select('t.pkidtarifapesaje,t.valortarifapesaje')
                                                ->where('t.tarifapesajeactivo = :tarifapesajeactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->setParameter('tarifapesajeactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->getQuery()
                                                ->getResult();
                                
                                $data = array(
                                    'status'         => 'Exito',
                                    'tarifaspesaje'  => $tarifasPesaje,
                                );
                            }else{
                                $data = array(
                                    'status'=> 'Error',
                                    'msg'   => 'La plaza no existe!!'
                                );
                            }
                            return $helpers->json($data);
                        }
                    }
                    
                    if (in_array("PERM_TARIFAS", $permisosDeserializados)) {
                                       
                        //Consulta para traer los datos de la tarifapesaje, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidtarifapesaje,valortarifapesaje,descripciontarifapesaje,numeroresoluciontarifapesaje,
                                        documentoresoluciontarifapesaje,fechainicio,fechafin,fkidplaza,nombreplaza,tarifapesajeactivo
                                    FROM ttarifapesaje 
                                        JOIN tplaza ON ttarifapesaje.fkidplaza = tplaza.pkidplaza
                                    ORDER BY numeroresoluciontarifapesaje ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tarifaspesaje = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($tarifaspesaje as $tarifapesaje) {
                            $tarifaspesajeList = array(
                                "pkidtarifapesaje"                => $tarifapesaje['pkidtarifapesaje'],
                                "valortarifapesaje"               => $tarifapesaje['valortarifapesaje'],
                                "descripciontarifapesaje"         => $tarifapesaje['descripciontarifapesaje'],
                                "numeroresoluciontarifapesaje"    => $tarifapesaje['numeroresoluciontarifapesaje'],
                                "documentoresoluciontarifapesaje" => $tarifapesaje['documentoresoluciontarifapesaje'],
                                "fechainicio"                     => $tarifapesaje['fechainicio'],
                                "fechafin"                        => $tarifapesaje['fechafin'],
                                "pkidplaza"                       => $tarifapesaje['fkidplaza'],
                                "nombreplaza"                     => $tarifapesaje['nombreplaza'],
                                "tarifapesajeactivo"              => $tarifapesaje['tarifapesajeactivo'],
                            );
                            array_push($array_all, $tarifaspesajeList);
                        }

                        $cabeceras = array(
                            array(
                                "nombrecampo"    => "valortarifapesaje",
                                "nombreetiqueta" => "Tarifa Pesaje"
                            ),
                            array(
                                "nombrecampo"    => "descripciontarifapesaje",
                                "nombreetiqueta" => "Descripción"
                            ),
                            array(
                                "nombrecampo"    => "numeroresoluciontarifapesaje",
                                "nombreetiqueta" => "Numero de Resolucion"
                            ),
                            array(
                                "nombrecampo"    => "documentoresoluciontarifapesaje",
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
                                "nombrecampo"    => "tarifapesajeactivo",
                                "nombreetiqueta" => "Tarifa Pesaje Activa/Inactiva"
                            ),
                        );

                        $data = array(
                            'status'        => 'Exito',
                            'cabeceras'     => $cabeceras,
                            'tarifapesaje'  => $array_all,
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
                'modulo'        => "TarifaPesaje",
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