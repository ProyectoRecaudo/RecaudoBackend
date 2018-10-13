<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttarifaparqueadero;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtarifaparqueaderoController extends Controller
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
     * Funcion para registrar una tarifaparqueadero
     * recibe los datos en un json llamado json con los datos
     * valortarifaparqueadero, valor de la tarifa que se va a asignar
     * descripciontarifaparqueadero, una breve descripcion de la tarifa, puede ser nulo
     * numeroresoluciontarifaparqueadero, el numero de la resolucion que oficializa la tarifa
     * tarifaparqueaderoactivo, si la tarifaparqueadero esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fkidtipoparqueadero, el id del tipo de parqueadero al que sera asociada la tarifa
     * fechainicio, fecha de inicio de validez de la tarifa
     * fechafin, fecha fin de validez de la tarifa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newTarifaParqueaderoAction(Request $request)
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
                            'msg'    => 'Tarifa Parqueadero no creada!!',
                        );

                        if ($json != null) {

                            $valorTarifaParqueadero = (isset($params->valortarifaparqueadero)) ? $params->valortarifaparqueadero : null;
                            $descripcionTarifaParqueadero = (isset($params->descripciontarifaparqueadero)) ? $params->descripciontarifaparqueadero : null;
                            $numeroresolucionTarifaParqueadero = (isset($params->numeroresoluciontarifaparqueadero)) ? $params->numeroresoluciontarifaparqueadero : null;
                            $documentoresolucionTarifaParqueadero = (isset($params->documentoresoluciontarifaparqueadero)) ? $params->documentoresoluciontarifaparqueadero : null;
                            $tarifaParqueaderoActivo = (isset($params->tarifaparqueaderoactivo)) ? $params->tarifaparqueaderoactivo : true;
                            $idPlaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null; 
                            $idTipoParqueadero = (isset($params->fkidtipoparqueadero)) ? $params->fkidtipoparqueadero : null; 
                            $fechaInicio = (isset($params->fechainicio)) ? new \DateTime($params->fechainicio) : null;
                            $fechaFin = (isset($params->fechafin)) ? new \DateTime($params->fechafin) : null;
                            
                            if($idPlaza != null && $valorTarifaParqueadero !=null && $numeroresolucionTarifaParqueadero !=null && $idTipoParqueadero !=null){
                                
                                $tipoParqueadero = $em->getRepository('ModeloBundle:Ttipoparqueadero')->find($idTipoParqueadero);
                                $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                
                                if($tipoParqueadero){
                                    
                                    if($plaza){
                                        $resolucionDuplicated = $em->getRepository('ModeloBundle:Ttarifaparqueadero')->findOneBy(array(
                                            "numeroresoluciontarifaparqueadero" => $numeroresolucionTarifaParqueadero
                                        ));

                                        if(!$resolucionDuplicated){

                                            $ultimaTarifaparqueadero = $em->getRepository('ModeloBundle:Ttarifaparqueadero')->findOneBy(array(
                                                "tarifaparqueaderoactivo" => true,
                                                "fkidplaza"               => $idPlaza,
                                                "fkidtipoparqueadero"     => $idTipoParqueadero
                                            ));
                                            
                                            //si existe una tarifa activa, la desactiva para crear una nueva. 
                                            if($ultimaTarifaparqueadero && $tarifaParqueaderoActivo == true){
                                                $ultimaTarifaparqueadero->setTarifaParqueaderoactivo(false);
                                            }
                                            
                                            $tarifaparqueadero = new Ttarifaparqueadero();
                                            $tarifaparqueadero->setValortarifaparqueadero($valorTarifaParqueadero); 
                                            $tarifaparqueadero->setDescripciontarifaparqueadero($descripcionTarifaParqueadero); 
                                            $tarifaparqueadero->setNumeroresoluciontarifaparqueadero($numeroresolucionTarifaParqueadero); 
                                            $tarifaparqueadero->setDocumentoresoluciontarifaparqueadero("sin documento");
                                            $tarifaparqueadero->setTarifaParqueaderoactivo($tarifaParqueaderoActivo);
                                            $tarifaparqueadero->setCraciontarifaparqueadero($today);
                                            $tarifaparqueadero->setModificaciontarifaparqueadero($today);
                                            $tarifaparqueadero->setFkidtipoparqueadero($tipoParqueadero);
                                            $tarifaparqueadero->setFkidplaza($plaza);
                                            $tarifaparqueadero->setFechainicio($fechaInicio);
                                            $tarifaparqueadero->setFechafin($fechaFin);

                                            if (isset($_FILES['fichero_usuario'])) {

                                                if ($_FILES['fichero_usuario']['size'] <= 5242880) {
                                                                                                    
                                                    if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
            
                                                        $em->persist($tarifaparqueadero);
                                                        $em->flush();
                                                        
                                                        $idTarifaParqueadero = $tarifaparqueadero->getPkidtarifaparqueadero();
            
                                                        $dir_subida = '../web/documentos/';
                                                        $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                        $fichero_subido = $dir_subida . basename($idTarifaParqueadero . "_tarifaparqueadero_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
            
                                                        if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                            $tarifaparqueadero_doc = $em->getRepository('ModeloBundle:Ttarifaparqueadero')->findOneBy(array(
                                                                "pkidtarifaparqueadero" => $tarifaparqueadero->getPkidtarifaparqueadero(),
                                                            ));
                                                            
                                                            $tarifaparqueadero_doc->setDocumentoresoluciontarifaparqueadero($fichero_subido);
                                                            $em->persist($tarifaparqueadero_doc);
                                                            $em->flush();
            
                                                            $data = array(
                                                                'status'       => 'Exito',
                                                                'msg'          => 'Tarifa Parqueadero creada !!',
                                                                'tarifaparqueadero' => $tarifaparqueadero_doc,
                                                            );
            
                                                            $datos = array(
                                                                'idusuario'             => $identity->sub,
                                                                'nombreusuario'         => $identity->name,
                                                                'identificacionusuario' => $identity->identificacion,
                                                                'accion'                => 'insertar',
                                                                "tabla"                 => 'Ttarifaparqueadero',
                                                                "valoresrelevantes"     => 'idTarifaParqueadero:'.$idTarifaParqueadero.',valorTarifaParqueadero:'.$valorTarifaParqueadero,
                                                                'idelemento'            => $idTarifaParqueadero,
                                                                'origen'                => 'web'
                                                            );
                        
                                                            $auditoria = $this->get(Auditoria::class);
                                                            $auditoria->auditoria(json_encode($datos));
            
                                                        } else {
                                                            $em->remove($tarifaparqueadero);
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
                                                $em->persist($tarifaparqueadero);
                                                $em->flush();
                        
                                                $data = array(
                                                    'status'            => 'Exito',
                                                    'msg'               => 'Tarifa Parqueadero creada!!',
                                                    'tarifaparqueadero' => $tarifaparqueadero,
                                                );
        
                                                $idTarifaParqueadero = $tarifaparqueadero->getPkidtarifaparqueadero();
                                            
                                                //una vez insertados los datos en la tarifaparqueadero se realiza el proceso de auditoria
                                                $datos = array(
                                                    'idusuario'             => $identity->sub,
                                                    'nombreusuario'         => $identity->name,
                                                    'identificacionusuario' => $identity->identificacion,
                                                    'accion'                => 'insertar',
                                                    "tabla"                 => 'Ttarifaparqueadero',
                                                    "valoresrelevantes"     => 'idTarifaParqueadero:'.$idTarifaParqueadero.',valorTarifaParqueadero:'.$valorTarifaParqueadero,
                                                    'idelemento'            => $idTarifaParqueadero,
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
                                        'msg'   => 'El tipo de parqueadero no existe!!'
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
                'modulo'        => 'TarifaParqueadero',
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
     * Funcion para modificar una tarifaparqueadero
     * recibe los datos en un json llamado json con los datos
     * pkidtarifaparqueadero=>obligatorio, id de la tarifaparqueadero a editar
     * valortarifaparqueadero, valor de la tarifa que se va a asignar
     * descripciontarifaparqueadero, una breve descripcion de la tarifa, puede ser nulo
     * numeroresoluciontarifaparqueadero, el numero de la resolucion que oficializa la tarifa
     * tarifaparqueaderoactivo, si la tarifaparqueadero esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * fkidtipoparqueadero, el id del tipo de parqueadero al que sera asociada la tarifa
     * fechainicio, fecha de inicio de validez de la tarifa
     * fechafin, fecha fin de validez de la tarifa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editTarifaParqueaderoAction(Request $request)
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
                            'msg'    => 'Tarifa Parqueadero no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idTarifaParqueadero = (isset($params->pkidtarifaparqueadero)) ? $params->pkidtarifaparqueadero : null;

                            if($idTarifaParqueadero != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $tarifaparqueadero = $em->getRepository('ModeloBundle:Ttarifaparqueadero')->find($idTarifaParqueadero);
                    
                                if($tarifaparqueadero){
                                          
                                    if(isset($params->valortarifaparqueadero)){
                                        if($params->valortarifaparqueadero != $tarifaparqueadero->getValortarifaparqueadero()){
                                            $tarifaparqueadero->setValortarifaparqueadero($params->valortarifaparqueadero);
                                            $tarifaparqueadero->setValorincrementoporcentual(0);
                                        }
                                    }

                                    if(isset($params->descripciontarifaparqueadero)){
                                        $tarifaparqueadero->setDescripciontarifaparqueadero($params->descripciontarifaparqueadero);
                                    }

                                    if(isset($params->numeroresoluciontarifaparqueadero)){
                                        
                                        $numeroresolucionTarifaParqueadero = $params->numeroresoluciontarifaparqueadero;

                                        $query = $em->getRepository('ModeloBundle:Ttarifaparqueadero')->createQueryBuilder('t')
                                            ->where('t.numeroresoluciontarifaparqueadero = :numeroresoluciontarifaparqueadero and t.pkidtarifaparqueadero != :pkidtarifaparqueadero')
                                            ->setParameter('numeroresoluciontarifaparqueadero', $numeroresolucionTarifaParqueadero)
                                            ->setParameter('pkidtarifaparqueadero', $idTarifaParqueadero)
                                            ->getQuery();
                                     
                                        $resolucionDuplicated = $query->getResult();

                                        if(!$resolucionDuplicated){
                                            $tarifaparqueadero->setNumeroresoluciontarifaparqueadero($numeroresolucionTarifaParqueadero);
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
                                            $tarifaparqueadero->setFkidplaza($plaza);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La plaza no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idPlaza = $tarifaparqueadero->getFkidplaza();
                                    }

                                    if(isset($params->fkidtipoparqueadero)){
                                        
                                        $idTipoParqueadero = $params->fkidtipoparqueadero;
                                        $tipoParqueadero = $em->getRepository('ModeloBundle:Ttipoparqueadero')->find($idTipoParqueadero);
                                       
                                        if($tipoParqueadero){
                                            $tarifaparqueadero->setFkidtipoparqueadero($tipoParqueadero);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'El tipo de parqueadero no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idTipoParqueadero = $tarifaparqueadero->getFkidtipoparqueadero();
                                    }

                                    if(isset($params->tarifaparqueaderoactivo)){
                                        
                                        if($params->tarifaparqueaderoactivo == true){
                                            
                                            $tarifaparqueaderoDuplicated = $em->createQueryBuilder()->select("t")
                                                ->from('ModeloBundle:Ttarifaparqueadero', 't')
                                                ->where('t.tarifaparqueaderoactivo = :tarifaparqueaderoactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->andwhere('t.fkidtipoparqueadero = :fkidtipoparqueadero')
                                                ->andwhere('t.pkidtarifaparqueadero != :pkidtarifaparqueadero')
                                                ->setParameter('tarifaparqueaderoactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->setParameter('fkidtipoparqueadero', $idTipoParqueadero)
                                                ->setParameter('pkidtarifaparqueadero', $idTarifaParqueadero)
                                                ->getQuery()
                                                ->getResult();
                                            
                                            if($tarifaparqueaderoDuplicated){
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'Ya existe una tarifa activa en la misma plaza y tipo de parqueadero!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                        $tarifaparqueadero->setTarifaParqueaderoactivo($params->tarifaparqueaderoactivo);
                                    }  
                
                                    $tarifaparqueadero->setModificaciontarifaparqueadero($today);

                                    if(isset($params->fechainicio)){
                                        $tarifaparqueadero->setFechainicio(new \DateTime($params->fechainicio));
                                    }

                                    if(isset($params->fechafin)){
                                        $tarifaparqueadero->setFechafin(new \DateTime($params->fechafin));
                                    }

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
    
                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
    
                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($idTarifaParqueadero . "_tarifaparqueadero_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
    
                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                    $doc_old = $tarifaparqueadero->getDocumentoresoluciontarifaparqueadero();
                                                    if ($doc_old != "sin documento") {
                                                        unlink($doc_old);
                                                    }
                                                    $tarifaparqueadero->setDocumentoresoluciontarifaparqueadero($fichero_subido);
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
                                        $documento = $params->documentoresoluciontarifaparqueadero;
                                        /**
                                         * Si no se envia un documento y la ruta es falsa, se elimina el documento
                                         */
                                        if(!isset($documento) || $documento == false){
                                            
                                            $documento_old = $tarifaparqueadero->getDocumentoresoluciontarifaparqueadero();

                                            if($documento_old != 'sin documento'){
                                                unlink($documento_old);
                                                $tarifaparqueadero->setDocumentoresoluciontarifaparqueadero("sin documento");
                                            }
                                        }
                                    }
                
                                    $em->persist($tarifaparqueadero);
                                    $em->flush();
                
                                    $data = array(
                                        'status'            => 'Exito',
                                        'msg'               => 'Tarifa Parqueadero actualizada!!',
                                        'tarifaparqueadero' => $tarifaparqueadero,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Ttarifaparqueadero',
                                        "valoresrelevantes"     => 'idTarifaParqueadero:'.$idTarifaParqueadero.',valorTarifaParqueadero:'.$tarifaparqueadero->getValortarifaparqueadero(),
                                        'idelemento'            => $idTarifaParqueadero,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La tarifa parqueadero no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la tarifa parqueadero a editar es nulo!!'
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
                'modulo'        => "TarifaParqueadero",
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
     * Funcion para mostrar las tarifas parqueaderos registradas
     * Recibe los parametros para filtrar
     * pkidplaza, filtro por plazas a la que pertenece la tarifa
     * pidtipoparqueadero, tipo de parqueadero al que pertenece la tarifa
     * al enviar los parametros filtra tambien por tarifas activas
     * con el id de la plaza que se desea conocer.
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryTarifaParqueaderoAction(Request $request)
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
                            
                            $idTipoParqueadero = $request->get('pkidtipoparqueadero', null); 

                            if($idTipoParqueadero != null){
                                
                                $tipoParqueadero = $em->getRepository('ModeloBundle:Ttipoparqueadero')->find($idTipoParqueadero);
                                                                    
                                if($tipoParqueadero){

                                    if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                                       
                                        $tarifasParqueadero = $em->getRepository('ModeloBundle:Ttarifaparqueadero')->createQueryBuilder('t')
                                                ->select('t.pkidtarifaparqueadero,t.valortarifaparqueadero')
                                                ->where('t.tarifaparqueaderoactivo = :tarifaparqueaderoactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->andwhere('t.fkidtipoparqueadero = :fkidtipoparqueadero')
                                                ->setParameter('tarifaparqueaderoactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->setParameter('fkidtipoparqueadero', $idTipoParqueadero)
                                                ->getQuery()
                                                ->getResult();

                                        $data = array(
                                            'status'              => 'Exito',
                                            'tarifasparqueadero'  => $tarifasParqueadero,
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
                                        'msg'   => 'el tipo de parqueadero no existe!!'
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
         
                        //Consulta para traer los datos de la tarifaparqueadero, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT pkidtarifaparqueadero,valortarifaparqueadero,descripciontarifaparqueadero,numeroresoluciontarifaparqueadero,
                                         documentoresoluciontarifaparqueadero,fechainicio,fechafin,fkidplaza,nombreplaza,fkidtipoparqueadero,nombretipoparqueadero,tarifaparqueaderoactivo 
                                    FROM ttarifaparqueadero 
                                        JOIN tplaza ON ttarifaparqueadero.fkidplaza = tplaza.pkidplaza
                                        JOIN ttipoparqueadero ON ttarifaparqueadero.fkidtipoparqueadero = ttipoparqueadero.pkidtipoparqueadero
                                    ORDER BY numeroresoluciontarifaparqueadero ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tarifaparqueaderos = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($tarifaparqueaderos as $tarifaparqueadero) {
                            $tarifaparqueaderosList = array(
                                "pkidtarifaparqueadero"                => $tarifaparqueadero['pkidtarifaparqueadero'],
                                "valortarifaparqueadero"               => $tarifaparqueadero['valortarifaparqueadero'],
                                "descripciontarifaparqueadero"         => $tarifaparqueadero['descripciontarifaparqueadero'],
                                "numeroresoluciontarifaparqueadero"    => $tarifaparqueadero['numeroresoluciontarifaparqueadero'],
                                "documentoresoluciontarifaparqueadero" => $tarifaparqueadero['documentoresoluciontarifaparqueadero'],
                                "fechainicio"                          => $tarifaparqueadero['fechainicio'],
                                "fechafin"                             => $tarifaparqueadero['fechafin'],
                                "pkidplaza"                            => $tarifaparqueadero['fkidplaza'],
                                "nombreplaza"                          => $tarifaparqueadero['nombreplaza'],
                                "pkidtipoparqueadero"                  => $tarifaparqueadero['fkidtipoparqueadero'],
                                "nombretipoparqueadero"                => $tarifaparqueadero['nombretipoparqueadero'],
                                "tarifaparqueaderoactivo"              => $tarifaparqueadero['tarifaparqueaderoactivo'],
                            );
                            array_push($array_all, $tarifaparqueaderosList);
                        }

                        $cabeceras = array(
                            array(
                                "nombrecampo"    => "valortarifaparqueadero",
                                "nombreetiqueta" => "Tarifa Parqueadero"
                            ),
                            array(
                                "nombrecampo"    => "descripciontarifaparqueadero",
                                "nombreetiqueta" => "Descripción"
                            ),
                            array(
                                "nombrecampo"    => "numeroresoluciontarifaparqueadero",
                                "nombreetiqueta" => "Numero de Resolucion"
                            ),
                            array(
                                "nombrecampo"    => "documentoresoluciontarifaparqueadero",
                                "nombreetiqueta" => "Documento"
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
                                "nombrecampo"    => "nombretipoparqueadero",
                                "nombreetiqueta" => "Tipo de Parqueadero"
                            ),
                            array(
                                "nombrecampo"    => "tarifaparqueaderoactivo",
                                "nombreetiqueta" => "Tarifa Parqueadero Activa/Inactiva"
                            ),
                        );
                        $data = array(
                            'status'            => 'Exito',
                            'cabeceras'         => $cabeceras,
                            'tarifaparqueadero' => $array_all,
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
                'modulo'        => "TarifaParqueadero",
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