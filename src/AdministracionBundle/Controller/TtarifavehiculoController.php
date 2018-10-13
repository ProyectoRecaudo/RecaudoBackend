<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttarifavehiculo;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtarifavehiculoController extends Controller
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
     * Funcion para registrar una tarifavehiculo
     * recibe los datos en un json llamado json con los datos
     * valortarifavehiculo, valor de la tarifa que se va a asignar
     * descripciontarifavehiculo, una breve descripcion de la tarifa, puede ser nulo
     * numeroresoluciontarifavehiculo, el numero de la resolucion que oficializa la tarifa
     * tarifavehiculoactivo, si la tarifavehiculo esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fkidtipovehiculo, el id del tipo de vehiculo al que sera asociada la tarifa
     * fechainicio, fecha de inicio de validez de la tarifa
     * fechafin, fecha fin de validez de la tarifa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newTarifaVehiculoAction(Request $request)
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
                            'msg'    => 'Tarifa Vehiculo no creada!!',
                        );

                        if ($json != null) {

                            $valorTarifaVehiculo = (isset($params->valortarifavehiculo)) ? $params->valortarifavehiculo : null;
                            $descripcionTarifaVehiculo = (isset($params->descripciontarifavehiculo)) ? $params->descripciontarifavehiculo : null;
                            $numeroresolucionTarifaVehiculo = (isset($params->numeroresoluciontarifavehiculo)) ? $params->numeroresoluciontarifavehiculo : null;
                            $documentoresolucionTarifaVehiculo = (isset($params->documentoresoluciontarifavehiculo)) ? $params->documentoresoluciontarifavehiculo : null;
                            $tarifaVehiculoActivo = (isset($params->tarifavehiculoactivo)) ? $params->tarifavehiculoactivo : true;
                            $idPlaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null; 
                            $idTipoVehiculo = (isset($params->fkidtipovehiculo)) ? $params->fkidtipovehiculo : null;  
                            $fechaInicio = (isset($params->fechainicio)) ? new \DateTime($params->fechainicio) : null;
                            $fechaFin = (isset($params->fechafin)) ? new \DateTime($params->fechafin) : null;
                            
                            if($idPlaza != null && $valorTarifaVehiculo !=null && $numeroresolucionTarifaVehiculo !=null && $idTipoVehiculo !=null){
                                $tipoVehiculo = $em->getRepository('ModeloBundle:Ttipovehiculo')->find($idTipoVehiculo);
                                $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                
                                if($tipoVehiculo){
                                    
                                    if($plaza){
                                        $resolucionDuplicated = $em->getRepository('ModeloBundle:Ttarifavehiculo')->findOneBy(array(
                                            "numeroresoluciontarifavehiculo" => $numeroresolucionTarifaVehiculo
                                        ));

                                        if(!$resolucionDuplicated){

                                            $ultimaTarifavehiculo = $em->getRepository('ModeloBundle:Ttarifavehiculo')->findOneBy(array(
                                                "tarifavehiculoactivo" => true,
                                                "fkidplaza"            => $idPlaza,
                                                "fkidtipovehiculo"     => $idTipoVehiculo
                                            ));
                                            
                                            //si existe una tarifa activa, la desactiva para crear una nueva. 
                                            if($ultimaTarifavehiculo && $tarifaVehiculoActivo == true){
                                                $ultimaTarifavehiculo->setTarifaVehiculoactivo(false);
                                            }
                                            
                                            $tarifavehiculo = new Ttarifavehiculo();
                                            $tarifavehiculo->setValortarifavehiculo($valorTarifaVehiculo); 
                                            $tarifavehiculo->setDescripciontarifavehiculo($descripcionTarifaVehiculo); 
                                            $tarifavehiculo->setNumeroresoluciontarifavehiculo($numeroresolucionTarifaVehiculo); 
                                            $tarifavehiculo->setDocumentoresoluciontarifavehiculo("sin documento");
                                            $tarifavehiculo->setTarifaVehiculoactivo($tarifaVehiculoActivo);
                                            $tarifavehiculo->setCraciontarifavehiculo($today);
                                            $tarifavehiculo->setModificaciontarifavehiculo($today);
                                            $tarifavehiculo->setFkidtipovehiculo($tipoVehiculo);
                                            $tarifavehiculo->setFkidplaza($plaza);
                                            $tarifavehiculo->setFechainicio($fechaInicio);
                                            $tarifavehiculo->setFechafin($fechaFin);

                                            if (isset($_FILES['fichero_usuario'])) {

                                                if ($_FILES['fichero_usuario']['size'] <= 5242880) {
                                                                                                    
                                                    if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
            
                                                        $em->persist($tarifavehiculo);
                                                        $em->flush();
                                                        
                                                        $idTarifaVehiculo = $tarifavehiculo->getPkidtarifavehiculo();
            
                                                        $dir_subida = '../web/documentos/';
                                                        $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                        $fichero_subido = $dir_subida . basename($idTarifaVehiculo . "_tarifavehiculo_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
            
                                                        if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                            $tarifavehiculo_doc = $em->getRepository('ModeloBundle:Ttarifavehiculo')->findOneBy(array(
                                                                "pkidtarifavehiculo" => $tarifavehiculo->getPkidtarifavehiculo(),
                                                            ));
                                                            
                                                            $tarifavehiculo_doc->setDocumentoresoluciontarifavehiculo($fichero_subido);
                                                            $em->persist($tarifavehiculo_doc);
                                                            $em->flush();
            
                                                            $data = array(
                                                                'status'       => 'Exito',
                                                                'msg'          => 'Tarifa Vehiculo creada !!',
                                                                'tarifavehiculo' => $tarifavehiculo_doc,
                                                            );
            
                                                            $datos = array(
                                                                'idusuario'             => $identity->sub,
                                                                'nombreusuario'         => $identity->name,
                                                                'identificacionusuario' => $identity->identificacion,
                                                                'accion'                => 'insertar',
                                                                "tabla"                 => 'Ttarifavehiculo',
                                                                "valoresrelevantes"     => 'idTarifaVehiculo:'.$idTarifaVehiculo.',valorTarifaVehiculo:'.$valorTarifaVehiculo,
                                                                'idelemento'            => $idTarifaVehiculo,
                                                                'origen'                => 'web'
                                                            );
                        
                                                            $auditoria = $this->get(Auditoria::class);
                                                            $auditoria->auditoria(json_encode($datos));
            
                                                        } else {
                                                            $em->remove($tarifavehiculo);
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
                                                $em->persist($tarifavehiculo);
                                                $em->flush();
                        
                                                $data = array(
                                                    'status'       => 'Exito',
                                                    'msg'          => 'Tarifa Vehiculo creada!!',
                                                    'tarifavehiculo' => $tarifavehiculo,
                                                );
        
                                                $idTarifaVehiculo = $tarifavehiculo->getPkidtarifavehiculo();
                                            
                                                //una vez insertados los datos en la tarifavehiculo se realiza el proceso de auditoria
                                                $datos = array(
                                                    'idusuario'             => $identity->sub,
                                                    'nombreusuario'         => $identity->name,
                                                    'identificacionusuario' => $identity->identificacion,
                                                    'accion'                => 'insertar',
                                                    "tabla"                 => 'Ttarifavehiculo',
                                                    "valoresrelevantes"     => 'idTarifaVehiculo:'.$idTarifaVehiculo.',valorTarifaVehiculo:'.$valorTarifaVehiculo,
                                                    'idelemento'            => $idTarifaVehiculo,
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
                                        'msg'   => 'El tipo de vehiculo no existe!!'
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
                'modulo'        => 'TarifaVehiculo',
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
     * Funcion para modificar una tarifavehiculo
     * recibe los datos en un json llamado json con los datos
     * pkidtarifavehiculo=>obligatorio, id de la tarifavehiculo a editar
     * valortarifavehiculo, valor de la tarifa que se va a asignar
     * descripciontarifavehiculo, una breve descripcion de la tarifa, puede ser nulo
     * numeroresoluciontarifavehiculo, el numero de la resolucion que oficializa la tarifa
     * tarifavehiculoactivo, si la tarifavehiculo esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fkidtipovehiculo, el id del tipo de vehiculo al que sera asociada la tarifa
     * fechainicio, fecha de inicio de validez de la tarifa
     * fechafin, fecha fin de validez de la tarifa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editTarifaVehiculoAction(Request $request)
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
                            'msg'    => 'Tarifa Vehiculo no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idTarifaVehiculo = (isset($params->pkidtarifavehiculo)) ? $params->pkidtarifavehiculo : null;

                            if($idTarifaVehiculo != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $tarifavehiculo = $em->getRepository('ModeloBundle:Ttarifavehiculo')->find($idTarifaVehiculo);
                    
                                if($tarifavehiculo){
                                          
                                    if(isset($params->valortarifavehiculo)){
                                        if($params->valortarifavehiculo != $tarifavehiculo->getValortarifavehiculo()){
                                            $tarifavehiculo->setValortarifavehiculo($params->valortarifavehiculo);
                                            $tarifavehiculo->setValorincrementoporcentual(0);
                                        }
                                    }

                                    if(isset($params->descripciontarifavehiculo)){
                                        $tarifavehiculo->setDescripciontarifavehiculo($params->descripciontarifavehiculo);
                                    }

                                    if(isset($params->numeroresoluciontarifavehiculo)){
                                        
                                        $numeroresolucionTarifaVehiculo = $params->numeroresoluciontarifavehiculo;

                                        //revisa en la tabla Ttarifavehiculo si el valor que se desea asignar no existe en la misma plaza y en el mismo tipo
                                        $query = $em->getRepository('ModeloBundle:Ttarifavehiculo')->createQueryBuilder('t')
                                            ->where('t.numeroresoluciontarifavehiculo = :numeroresoluciontarifavehiculo and t.pkidtarifavehiculo != :pkidtarifavehiculo')
                                            ->setParameter('numeroresoluciontarifavehiculo', $numeroresolucionTarifaVehiculo)
                                            ->setParameter('pkidtarifavehiculo', $idTarifaVehiculo)
                                            ->getQuery();
                                     
                                        $resolucionDuplicated = $query->getResult();

                                        if(!$resolucionDuplicated){
                                            $tarifavehiculo->setNumeroresoluciontarifavehiculo($numeroresolucionTarifaVehiculo);
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
                                            $tarifavehiculo->setFkidplaza($plaza);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La plaza no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idPlaza = $tarifavehiculo->getFkidplaza();
                                    }

                                    if(isset($params->fkidtipovehiculo)){
                                        
                                        $idTipoVehiculo = $params->fkidtipovehiculo;
                                        $tipoVehiculo = $em->getRepository('ModeloBundle:Ttipovehiculo')->find($idTipoVehiculo);
                                       
                                        if($tipoVehiculo){
                                            $tarifavehiculo->setFkidtipovehiculo($tipoVehiculo);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'El tipo de vehiculo no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idTipoVehiculo = $tarifavehiculo->getFkidtipovehiculo();
                                    }

                                    if(isset($params->tarifavehiculoactivo)){
                                        
                                        if($params->tarifavehiculoactivo == true){
                                            
                                            $tarifavehiculoDuplicated = $em->createQueryBuilder()->select("t")
                                                ->from('ModeloBundle:Ttarifavehiculo', 't')
                                                ->where('t.tarifavehiculoactivo = :tarifavehiculoactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->andwhere('t.fkidtipovehiculo = :fkidtipovehiculo')
                                                ->andwhere('t.pkidtarifavehiculo != :pkidtarifavehiculo')
                                                ->setParameter('tarifavehiculoactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->setParameter('fkidtipovehiculo', $idTipoVehiculo)
                                                ->setParameter('pkidtarifavehiculo', $idTarifaVehiculo)
                                                ->getQuery()
                                                ->getResult();
                                            
                                            if($tarifavehiculoDuplicated){
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'Ya existe una tarifa activa en la misma plaza y tipo de vehiculo!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                        $tarifavehiculo->setTarifaVehiculoactivo($params->tarifavehiculoactivo);
                                    }  
                
                                    $tarifavehiculo->setModificaciontarifavehiculo($today);

                                    if(isset($params->fechainicio)){
                                        $tarifavehiculo->setFechainicio(new \DateTime($params->fechainicio));
                                    }

                                    if(isset($params->fechafin)){
                                        $tarifavehiculo->setFechafin(new \DateTime($params->fechafin));
                                    }

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
    
                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
    
                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($idTarifaVehiculo . "_tarifavehiculo_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
    
                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                    $doc_old = $tarifavehiculo->getDocumentoresoluciontarifavehiculo();
                                                    if ($doc_old != "sin documento") {
                                                        unlink($doc_old);
                                                    }
                                                    $tarifavehiculo->setDocumentoresoluciontarifavehiculo($fichero_subido);
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
                                        $documento = $params->documentoresoluciontarifavehiculo;
                                        /**
                                         * Si no se envia un documento y la ruta es falsa, se elimina el documento
                                         */
                                        if(!isset($documento) || $documento == false){
                                            
                                            $documento_old = $tarifavehiculo->getDocumentoresoluciontarifavehiculo();
                                            
                                            if($documento_old != 'sin documento'){
                                                unlink($documento_old);
                                                $tarifavehiculo->setDocumentoresoluciontarifavehiculo("sin documento");
                                            }
                                        }
                                    }
                
                                    $em->persist($tarifavehiculo);
                                    $em->flush();
                
                                    $data = array(
                                        'status'       => 'Exito',
                                        'msg'          => 'Tarifa Vehiculo actualizada!!',
                                        'tarifavehiculo' => $tarifavehiculo,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Ttarifavehiculo',
                                        "valoresrelevantes"     => 'idTarifaVehiculo:'.$idTarifaVehiculo.',valorTarifaVehiculo:'.$tarifavehiculo->getValortarifavehiculo(),
                                        'idelemento'            => $idTarifaVehiculo,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La tarifa vehiculo no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la tarifa vehiculo a editar es nulo!!'
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
                'modulo'        => "TarifaVehiculo",
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
     * Funcion para mostrar las tarifas vehiculos registradas
     * Recibe los parametros para filtrar
     * pkidplaza, filtro por plazas a la que pertenece la tarifa
     * pidtipovehiculo, tipo de vehiculo al que pertenece la tarifa
     * al enviar los parametros filtra tambien por tarifas activas
     * con el id de la plaza que se desea conocer.
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryTarifaVehiculoAction(Request $request)
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
                           
                            //Consulta de tarifa vehiculo por plaza
                                                           
                            $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                                                        
                            if($plaza){
                                $tarifasVehiculo = $em->getRepository('ModeloBundle:Ttarifavehiculo')->createQueryBuilder('t')
                                                ->select('t.pkidtarifavehiculo,t.valortarifavehiculo')
                                                ->where('t.tarifavehiculoactivo = :tarifavehiculoactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->setParameter('tarifavehiculoactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->getQuery()
                                                ->getResult();
                                
                                $data = array(
                                    'status'           => 'Exito',
                                    'tarifasvehiculo'  => $tarifasVehiculo,
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
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
         
                        //Consulta para traer los datos de la tarifavehiculo, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidtarifavehiculo,valortarifavehiculo,descripciontarifavehiculo,numeroresoluciontarifavehiculo,
                                         documentoresoluciontarifavehiculo,fechainicio,fechafin,fkidplaza,nombreplaza,fkidtipovehiculo,nombretipovehiculo,tarifavehiculoactivo 
                                    FROM ttarifavehiculo 
                                    JOIN tplaza ON ttarifavehiculo.fkidplaza = tplaza.pkidplaza
                                    JOIN ttipovehiculo ON ttarifavehiculo.fkidtipovehiculo = ttipovehiculo.pkidtipovehiculo
                                    ORDER BY numeroresoluciontarifavehiculo ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tarifavehiculos = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($tarifavehiculos as $tarifavehiculo) {
                            $tarifavehiculosList = array(
                                "pkidtarifavehiculo"                => $tarifavehiculo['pkidtarifavehiculo'],
                                "valortarifavehiculo"               => $tarifavehiculo['valortarifavehiculo'],
                                "descripciontarifavehiculo"         => $tarifavehiculo['descripciontarifavehiculo'],
                                "numeroresoluciontarifavehiculo"    => $tarifavehiculo['numeroresoluciontarifavehiculo'],
                                "documentoresoluciontarifavehiculo" => $tarifavehiculo['documentoresoluciontarifavehiculo'],
                                "fechainicio"                       => $tarifavehiculo['fechainicio'],
                                "fechafin"                          => $tarifavehiculo['fechafin'],
                                "pkidplaza"                         => $tarifavehiculo['fkidplaza'],
                                "nombreplaza"                       => $tarifavehiculo['nombreplaza'],
                                "pkidtipovehiculo"                  => $tarifavehiculo['fkidtipovehiculo'],
                                "nombretipovehiculo"                => $tarifavehiculo['nombretipovehiculo'],
                                "tarifavehiculoactivo"              => $tarifavehiculo['tarifavehiculoactivo'],
                            );
                            array_push($array_all, $tarifavehiculosList);
                        }

                        $cabeceras = array(
                            array(
                                "nombrecampo"    => "valortarifavehiculo",
                                "nombreetiqueta" => "Tarifa Vehiculo"
                            ),
                            array(
                                "nombrecampo"    => "descripciontarifavehiculo",
                                "nombreetiqueta" => "Descripción"
                            ),
                            array(
                                "nombrecampo"    => "numeroresoluciontarifavehiculo",
                                "nombreetiqueta" => "Numero de Resolucion"
                            ),
                            array(
                                "nombrecampo"    => "documentoresoluciontarifavehiculo",
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
                                "nombrecampo"    => "nombretipovehiculo",
                                "nombreetiqueta" => "Tipo de Vehiculo"
                            ),
                            array(
                                "nombrecampo"    => "tarifavehiculoactivo",
                                "nombreetiqueta" => "Tarifa Vehiculo Activa/Inactiva"
                            ),
                        );
                        $data = array(
                            'status'         => 'Exito',
                            'cabeceras'      => $cabeceras,
                            'tarifavehiculo' => $array_all,
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
                'modulo'        => "TarifaVehiculo",
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