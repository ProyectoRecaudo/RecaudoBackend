<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttarifaanimal;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtarifaanimalController extends Controller
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
     * Funcion para registrar una tarifaanimal
     * recibe los datos en un json llamado json con los datos
     * valortarifaanimal, valor de la tarifa que se va a asignar
     * descripciontarifaanimal, una breve descripcion de la tarifa, puede ser nulo
     * numeroresoluciontarifaanimal, el numero de la resolucion que oficializa la tarifa
     * tarifaanimalactivo, si la tarifaanimal esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fkidtipoanimal, el id del tipo de animal al que sera asociada la tarifa
     * fechainicio, fecha de inicio de validez de la tarifa
     * fechafin, fecha fin de validez de la tarifa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newTarifaAnimalAction(Request $request)
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
                            'msg'    => 'Tarifa Animal no creada!!',
                        );

                        if ($json != null) {

                            $valorTarifaAnimal = (isset($params->valortarifaanimal)) ? $params->valortarifaanimal : null;
                            $descripcionTarifaAnimal = (isset($params->descripciontarifaanimal)) ? $params->descripciontarifaanimal : null;
                            $numeroresolucionTarifaAnimal = (isset($params->numeroresoluciontarifaanimal)) ? $params->numeroresoluciontarifaanimal : null;
                            $documentoresolucionTarifaAnimal = (isset($params->documentoresoluciontarifaanimal)) ? $params->documentoresoluciontarifaanimal : null;
                            $tarifaAnimalActivo = (isset($params->tarifaanimalactivo)) ? $params->tarifaanimalactivo : true;
                            $idPlaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null; 
                            $idTipoAnimal = (isset($params->fkidtipoanimal)) ? $params->fkidtipoanimal : null; 
                            $fechaInicio = (isset($params->fechainicio)) ? new \DateTime($params->fechainicio) : null;
                            $fechaFin = (isset($params->fechafin)) ? new \DateTime($params->fechafin) : null;
                            
                            if($idPlaza != null && $valorTarifaAnimal !=null && $numeroresolucionTarifaAnimal !=null && $idTipoAnimal !=null){
                                $tipoAnimal = $em->getRepository('ModeloBundle:Ttipoanimal')->find($idTipoAnimal);
                                $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                
                                if($tipoAnimal){
                                    
                                    if($plaza){
                                        $resolucionDuplicated = $em->getRepository('ModeloBundle:Ttarifaanimal')->findOneBy(array(
                                            "numeroresoluciontarifaanimal" => $numeroresolucionTarifaAnimal
                                        ));

                                        if(!$resolucionDuplicated){

                                            $ultimaTarifaanimal = $em->getRepository('ModeloBundle:Ttarifaanimal')->findOneBy(array(
                                                "tarifaanimalactivo" => true,
                                                "fkidplaza"          => $idPlaza,
                                                "fkidtipoanimal"     => $idTipoAnimal
                                            ));
                                            
                                            //si existe una tarifa activa, la desactiva para crear una nueva. 
                                            if($ultimaTarifaanimal && $tarifaAnimalActivo == true){
                                                $ultimaTarifaanimal->setTarifaAnimalactivo(false);
                                            }

                                            $tarifaanimal = new Ttarifaanimal();
                                            $tarifaanimal->setValortarifaanimal($valorTarifaAnimal); 
                                            $tarifaanimal->setDescripciontarifaanimal($descripcionTarifaAnimal); 
                                            $tarifaanimal->setNumeroresoluciontarifaanimal($numeroresolucionTarifaAnimal); 
                                            $tarifaanimal->setDocumentoresoluciontarifaanimal("sin documento");
                                            $tarifaanimal->setTarifaAnimalactivo($tarifaAnimalActivo);
                                            $tarifaanimal->setCraciontarifaanimal($today);
                                            $tarifaanimal->setModificaciontarifaanimal($today);
                                            $tarifaanimal->setFkidtipoanimal($tipoAnimal);
                                            $tarifaanimal->setFkidplaza($plaza);
                                            $tarifaanimal->setFechainicio($fechaInicio);
                                            $tarifaanimal->setFechafin($fechaFin);

                                            if (isset($_FILES['fichero_usuario'])) {

                                                if ($_FILES['fichero_usuario']['size'] <= 5242880) {
                                                                                                    
                                                    if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
            
                                                        $em->persist($tarifaanimal);
                                                        $em->flush();
                                                        
                                                        $idTarifaAnimal = $tarifaanimal->getPkidtarifaanimal();
            
                                                        $dir_subida = '../web/documentos/';
                                                        $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                        $fichero_subido = $dir_subida . basename($idTarifaAnimal . "_tarifaanimal_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
            
                                                        if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                            $tarifaanimal_doc = $em->getRepository('ModeloBundle:Ttarifaanimal')->findOneBy(array(
                                                                "pkidtarifaanimal" => $tarifaanimal->getPkidtarifaanimal(),
                                                            ));
                                                            
                                                            $tarifaanimal_doc->setDocumentoresoluciontarifaanimal($fichero_subido);
                                                            $em->persist($tarifaanimal_doc);
                                                            $em->flush();
            
                                                            $data = array(
                                                                'status'       => 'Exito',
                                                                'msg'          => 'Tarifa animal creada !!',
                                                                'tarifaanimal' => $tarifaanimal_doc,
                                                            );
            
                                                            $datos = array(
                                                                'idusuario'             => $identity->sub,
                                                                'nombreusuario'         => $identity->name,
                                                                'identificacionusuario' => $identity->identificacion,
                                                                'accion'                => 'insertar',
                                                                "tabla"                 => 'Ttarifaanimal',
                                                                "valoresrelevantes"     => 'idTarifaAnimal:'.$idTarifaAnimal.',valorTarifaAnimal:'.$valorTarifaAnimal,
                                                                'idelemento'            => $idTarifaAnimal,
                                                                'origen'                => 'web'
                                                            );
                        
                                                            $auditoria = $this->get(Auditoria::class);
                                                            $auditoria->auditoria(json_encode($datos));
            
                                                        } else {
                                                            $em->remove($tarifaanimal);
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
                                                $em->persist($tarifaanimal);
                                                $em->flush();
                        
                                                $data = array(
                                                    'status'       => 'Exito',
                                                    'msg'          => 'Tarifa Animal creada!!',
                                                    'tarifaanimal' => $tarifaanimal,
                                                );
        
                                                $idTarifaAnimal = $tarifaanimal->getPkidtarifaanimal();
                                            
                                                //una vez insertados los datos en la tarifaanimal se realiza el proceso de auditoria
                                                $datos = array(
                                                    'idusuario'             => $identity->sub,
                                                    'nombreusuario'         => $identity->name,
                                                    'identificacionusuario' => $identity->identificacion,
                                                    'accion'                => 'insertar',
                                                    "tabla"                 => 'Ttarifaanimal',
                                                    "valoresrelevantes"     => 'idTarifaAnimal:'.$idTarifaAnimal.',valorTarifaAnimal:'.$valorTarifaAnimal,
                                                    'idelemento'            => $idTarifaAnimal,
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
                                        'status'=> 'error',
                                        'msg'   => 'El tipo de animal no existe!!'
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
                'modulo'        => 'TarifaAnimal',
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
     * Funcion para modificar una tarifaanimal
     * recibe los datos en un json llamado json con los datos
     * pkidtarifaanimal=>obligatorio, id de la tarifaanimal a editar
     * valortarifaanimal, valor de la tarifa que se va a asignar
     * descripciontarifaanimal, una breve descripcion de la tarifa, puede ser nulo
     * numeroresoluciontarifaanimal, el numero de la resolucion que oficializa la tarifa
     * tarifaanimalactivo, si la tarifaanimal esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fkidtipoanimal, el id del tipo de animal al que sera asociada la tarifa
     * fechainicio, fecha de inicio de validez de la tarifa
     * fechafin, fecha fin de validez de la tarifa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editTarifaAnimalAction(Request $request)
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
                            'msg'    => 'Tarifa Animal no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idTarifaAnimal = (isset($params->pkidtarifaanimal)) ? $params->pkidtarifaanimal : null;

                            if($idTarifaAnimal != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $tarifaanimal = $em->getRepository('ModeloBundle:Ttarifaanimal')->find($idTarifaAnimal);
                    
                                if($tarifaanimal){
                          
                                    if(isset($params->valortarifaanimal)){

                                        if($params->valortarifaanimal != $tarifaanimal->getValortarifaanimal()){
                                            $tarifaanimal->setValortarifaanimal($params->valortarifaanimal);
                                            $tarifaanimal->setValorincrementoporcentual(0);
                                        }
                                    }

                                    if(isset($params->descripciontarifaanimal)){
                                        $tarifaanimal->setDescripciontarifaanimal($params->descripciontarifaanimal);
                                    }

                                    if(isset($params->numeroresoluciontarifaanimal)){
                                        
                                        $numeroresolucionTarifaAnimal = $params->numeroresoluciontarifaanimal;
                                        //revisa en la tabla Ttarifaanimal si el valor que se desea asignar no existe en la misma plaza y en el mismo tipo
                                        $query = $em->getRepository('ModeloBundle:Ttarifaanimal')->createQueryBuilder('t')
                                            ->where('t.numeroresoluciontarifaanimal = :numeroresoluciontarifaanimal and t.pkidtarifaanimal != :pkidtarifaanimal')
                                            ->setParameter('numeroresoluciontarifaanimal', $numeroresolucionTarifaAnimal)
                                            ->setParameter('pkidtarifaanimal', $idTarifaAnimal)
                                            ->getQuery();
                                     
                                        $resolucionDuplicated = $query->getResult();

                                        if(!$resolucionDuplicated){
                                            $tarifaanimal->setNumeroresoluciontarifaanimal($numeroresolucionTarifaAnimal);
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
                                            $tarifaanimal->setFkidplaza($plaza);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La plaza no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idPlaza = $tarifaanimal->getFkidplaza();
                                    }
                                    

                                    if(isset($params->fkidtipoanimal)){
                                       
                                        $idTipoAnimal = $params->fkidtipoanimal;
                                        $tipoAnimal = $em->getRepository('ModeloBundle:Ttipoanimal')->find($idTipoAnimal);
                                       
                                        if($tipoAnimal){
                                            $tarifaanimal->setFkidtipoanimal($tipoAnimal);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'El tipo de animal no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idTipoAnimal = $tarifaanimal->getFkidtipoanimal();
                                    }

                                    if(isset($params->tarifaanimalactivo)){

                                        if($params->tarifaanimalactivo == true){
                                            
                                            $tarifaanimalDuplicated = $em->createQueryBuilder()->select("t")
                                                ->from('ModeloBundle:Ttarifaanimal', 't')
                                                ->where('t.tarifaanimalactivo = :tarifaanimalactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->andwhere('t.fkidtipoanimal = :fkidtipoanimal')
                                                ->andwhere('t.pkidtarifaanimal != :pkidtarifaanimal')
                                                ->setParameter('tarifaanimalactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->setParameter('fkidtipoanimal', $idTipoAnimal)
                                                ->setParameter('pkidtarifaanimal', $idTarifaAnimal)
                                                ->getQuery()
                                                ->getResult();
                                            
                                            if($tarifaanimalDuplicated){
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'Ya existe una tarifa activa en la misma plaza y tipo de animal!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                        
                                        $tarifaanimal->setTarifaanimalactivo($params->tarifaanimalactivo);
                                    }     
                
                                    $tarifaanimal->setModificaciontarifaanimal($today);

                                    if(isset($params->fechainicio)){
                                        $tarifaanimal->setFechainicio(new \DateTime($params->fechainicio));
                                    }

                                    if(isset($params->fechafin)){
                                        $tarifaanimal->setFechafin(new \DateTime($params->fechafin));
                                    }

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
    
                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
    
                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($idTarifaAnimal . "_tarifaanimal_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
    
                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                    $doc_old = $tarifaanimal->getDocumentoresoluciontarifaanimal();
                                                    if ($doc_old != "sin documento") {
                                                        unlink($doc_old);
                                                    }
                                                   $tarifaanimal->setDocumentoresoluciontarifaanimal($fichero_subido);
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
                                        $documento = $params->documentoresoluciontarifaanimal;
                                        /**
                                         * Si no se envia un documento y la ruta es falsa, se elimina el documento
                                         */
                                        if(!isset($documento) || $documento == false){
                                            
                                            $documento_old = $tarifaanimal->getDocumentoresoluciontarifaanimal();
                                            if($documento_old != 'sin documento'){
                                                unlink($documento_old);
                                                $tarifaanimal->setDocumentoresoluciontarifaanimal("sin documento");
                                            }
                                           
                                        }
                                    }
                
                                    $em->persist($tarifaanimal);
                                    $em->flush();
                
                                    $data = array(
                                        'status'       => 'Exito',
                                        'msg'          => 'Tarifa Animal actualizada!!',
                                        'tarifaanimal' => $tarifaanimal,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Ttarifaanimal',
                                        "valoresrelevantes"     => 'idTarifaAnimal:'.$idTarifaAnimal.',valorTarifaAnimal:'.$tarifaanimal->getValortarifaanimal(),
                                        'idelemento'            => $idTarifaAnimal,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La tarifa animal no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la tarifa animal a editar es nulo!!'
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
                'modulo'        => "TarifaAnimal",
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
     * Funcion para mostrar las tarifaanimals registradas
     * Recibe los parametros para filtrar
     * pkidplaza, filtro por plazas a la que pertenece la tarifa
     * pidtipoanimal, tipo de animal al que pertenece la tarifa
     * al enviar los parametros filtra tambien por tarifas activas
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryTarifaAnimalAction(Request $request)
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

                    $idPlaza = $request->get('pkidplaza', null); 

                    if($idPlaza != null){
                        
                        $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);

                        if($plaza){
                            
                            $idTipoAnimal = $request->get('pkidtipoanimal', null); 

                            if($idTipoAnimal != null){
                                
                                $tipoAnimal = $em->getRepository('ModeloBundle:Ttipoanimal')->find($idTipoAnimal);
                                                                    
                                if($tipoAnimal){

                                    if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                                       
                                        $tarifasAnimal = $em->getRepository('ModeloBundle:Ttarifaanimal')->createQueryBuilder('t')
                                                ->select('t.pkidtarifaanimal,t.valortarifaanimal')
                                                ->where('t.tarifaanimalactivo = :tarifaanimalactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->andwhere('t.fkidtipoanimal = :fkidtipoanimal')
                                                ->setParameter('tarifaanimalactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->setParameter('fkidtipoanimal', $idTipoAnimal)
                                                ->getQuery()
                                                ->getResult();

                                        $data = array(
                                            'status'         => 'Exito',
                                            'tarifasanimal'  => $tarifasAnimal,
                                        );
                                        
                                    }else {
                                        $data = array(
                                            'status' => 'error',
                                            'msg'    => 'El usuario no tiene permisos genericos !!',
                                        );
                                    }
                                }else{
                                    $data = array(
                                        'status'=> 'Error',
                                        'msg'   => 'el tipo de animal no existe!!'
                                    );
                                    return $helpers->json($data);
                                }
                            }
                        }else{
                            $data = array(
                                'status'=> 'Error',
                                'msg'   => 'La plaza no existe!!'
                            );
                        }
                        
                        return $helpers->json($data);
                    }

                    if (in_array("PERM_TARIFAS", $permisosDeserializados)) {
                                            
                        //Consulta para traer los datos de la tarifaanimal, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidtarifaanimal,valortarifaanimal,descripciontarifaanimal,numeroresoluciontarifaanimal,
                                         documentoresoluciontarifaanimal,fechainicio,fechafin,fkidplaza,nombreplaza,fkidtipoanimal,nombretipoanimal,tarifaanimalactivo 
                                    FROM ttarifaanimal 
                                    JOIN tplaza ON ttarifaanimal.fkidplaza = tplaza.pkidplaza
                                    JOIN ttipoanimal ON ttarifaanimal.fkidtipoanimal = ttipoanimal.pkidtipoanimal
                                    ORDER BY numeroresoluciontarifaanimal ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tarifasanimal = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($tarifasanimal as $tarifaanimal) {
                            $tarifasanimalList = array(
                                "pkidtarifaanimal"                => $tarifaanimal['pkidtarifaanimal'],
                                "valortarifaanimal"               => $tarifaanimal['valortarifaanimal'],
                                "descripciontarifaanimal"         => $tarifaanimal['descripciontarifaanimal'],
                                "numeroresoluciontarifaanimal"    => $tarifaanimal['numeroresoluciontarifaanimal'],
                                "documentoresoluciontarifaanimal" => $tarifaanimal['documentoresoluciontarifaanimal'],
                                "fechainicio"                     => $tarifaanimal['fechainicio'],
                                "fechafin"                        => $tarifaanimal['fechafin'],
                                "pkidplaza"                       => $tarifaanimal['fkidplaza'],
                                "nombreplaza"                     => $tarifaanimal['nombreplaza'],
                                "pkidtipoanimal"                  => $tarifaanimal['fkidtipoanimal'],
                                "nombretipoanimal"                => $tarifaanimal['nombretipoanimal'],
                                "tarifaanimalactivo"              => $tarifaanimal['tarifaanimalactivo'],
                            );
                            array_push($array_all, $tarifasanimalList);
                        }

                        $cabeceras = array(
                            array(
                                "nombrecampo"    => "valortarifaanimal",
                                "nombreetiqueta" => "Tarifa Animal"
                            ),
                            array(
                                "nombrecampo"    => "descripciontarifaanimal",
                                "nombreetiqueta" => "Descripción"
                            ),
                            array(
                                "nombrecampo"    => "numeroresoluciontarifaanimal",
                                "nombreetiqueta" => "Numero de Resolucion"
                            ),
                            array(
                                "nombrecampo"    => "documentoresoluciontarifaanimal",
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
                                "nombrecampo"    => "nombretipoanimal",
                                "nombreetiqueta" => "Tipo de Animal"
                            ),
                            array(
                                "nombrecampo"    => "tarifaanimalactivo",
                                "nombreetiqueta" => "Tarifa Animal Activa/Inactiva"
                            ),
                        );

                        $data = array(
                            'status'       => 'Exito',
                            'cabeceras'    => $cabeceras,
                            'tarifaanimal' => $array_all,
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
                'modulo'        => "TarifaAnimal",
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