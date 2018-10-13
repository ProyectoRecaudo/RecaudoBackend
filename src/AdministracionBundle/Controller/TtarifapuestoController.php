<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttarifapuesto;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtarifapuestoController extends Controller
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
     * Funcion para registrar una tarifapuesto
     * recibe los datos en un json llamado json con los datos
     * valortarifapuesto, valor de la tarifa que se va a asignar
     * numeroresoluciontarifapuesto, el numero de la resolucion que oficializa la tarifa
     * tarifapuestoactivo, si la tarifapuesto esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newTarifaPuestoAction(Request $request)
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
                            'msg'    => 'Tarifa de puesto no creada!!',
                        );

                        if ($json != null) {

                            $valorTarifaPuesto = (isset($params->valortarifapuesto)) ? $params->valortarifapuesto : null;
                            $numeroresolucionTarifaPuesto = (isset($params->numeroresoluciontarifapuesto)) ? $params->numeroresoluciontarifapuesto : null;
                            $documentoresolucionTarifaPuesto = (isset($params->documentoresoluciontarifapuesto)) ? $params->documentoresoluciontarifapuesto : null;
                            $tarifaPuestoActivo = (isset($params->tarifapuestoactivo)) ? $params->tarifapuestoactivo : true;
                            $idPlaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null;
                            
                            if($idPlaza != null && $valorTarifaPuesto !=null && $numeroresolucionTarifaPuesto !=null){
                                $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                
                                if($plaza){
                                    $resolucionDuplicated = $em->getRepository('ModeloBundle:Ttarifapuesto')->findOneBy(array(
                                        "numeroresoluciontarifapuesto" => $numeroresolucionTarifaPuesto
                                    ));

                                    if(!$resolucionDuplicated){

                                        $tarifapuesto = new Ttarifapuesto();
                                        $tarifapuesto->setValortarifapuesto($valorTarifaPuesto); 
                                        $tarifapuesto->setNumeroresoluciontarifapuesto($numeroresolucionTarifaPuesto); 
                                        $tarifapuesto->setDocumentoresoluciontarifapuesto("sin documento");
                                        $tarifapuesto->setTarifaPuestoactivo($tarifaPuestoActivo);
                                        $tarifapuesto->setCraciontarifapuesto($today);
                                        $tarifapuesto->setModificaciontarifapuesto($today);
                                        $tarifapuesto->setFkidplaza($plaza);

                                        if (isset($_FILES['fichero_usuario'])) {

                                            if ($_FILES['fichero_usuario']['size'] <= 5242880) {
                                                                                                
                                                if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
        
                                                    $em->persist($tarifapuesto);
                                                    $em->flush();
                                                    
                                                    $idTarifaPuesto = $tarifapuesto->getPkidtarifapuesto();
        
                                                    $dir_subida = '../web/documentos/';
                                                    $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                    $fichero_subido = $dir_subida . basename($idTarifaPuesto . "_tarifapuesto_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
        
                                                    if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                        $tarifapuesto_doc = $em->getRepository('ModeloBundle:Ttarifapuesto')->findOneBy(array(
                                                            "pkidtarifapuesto" => $tarifapuesto->getPkidtarifapuesto(),
                                                        ));
                                                        
                                                        $tarifapuesto_doc->setDocumentoresoluciontarifapuesto($fichero_subido);
                                                        $em->persist($tarifapuesto_doc);
                                                        $em->flush();
        
                                                        $data = array(
                                                            'status'       => 'Exito',
                                                            'msg'          => 'Tarifa de puesto creada !!',
                                                            'tarifapuesto' => $tarifapuesto_doc,
                                                        );
        
                                                        $datos = array(
                                                            'idusuario'             => $identity->sub,
                                                            'nombreusuario'         => $identity->name,
                                                            'identificacionusuario' => $identity->identificacion,
                                                            'accion'                => 'insertar',
                                                            "tabla"                 => 'Ttarifapuesto',
                                                            "valoresrelevantes"     => 'idTarifaPuesto:'.$idTarifaPuesto.',valorTarifaPuesto:'.$valorTarifaPuesto,
                                                            'idelemento'            => $idTarifaPuesto,
                                                            'origen'                => 'web'
                                                        );
                    
                                                        $auditoria = $this->get(Auditoria::class);
                                                        $auditoria->auditoria(json_encode($datos));
        
                                                    } else {
                                                        $em->remove($tarifapuesto);
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
                                            $em->persist($tarifapuesto);
                                            $em->flush();
                    
                                            $data = array(
                                                'status'       => 'Exito',
                                                'msg'          => 'Tarifa de puesto creada!!',
                                                'tarifapuesto' => $tarifapuesto,
                                            );
    
                                            $idTarifaPuesto = $tarifapuesto->getPkidtarifapuesto();
                                        
                                            //una vez insertados los datos en la tarifapuesto se realiza el proceso de auditoria
                                            $datos = array(
                                                'idusuario'             => $identity->sub,
                                                'nombreusuario'         => $identity->name,
                                                'identificacionusuario' => $identity->identificacion,
                                                'accion'                => 'insertar',
                                                "tabla"                 => 'Ttarifapuesto',
                                                "valoresrelevantes"     => 'idTarifaPuesto:'.$idTarifaPuesto.',valorTarifaPuesto:'.$valorTarifaPuesto,
                                                'idelemento'            => $idTarifaPuesto,
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
                'modulo'        => 'TarifaPuesto',
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
     * Funcion para modificar una tarifapuesto
     * recibe los datos en un json llamado json con los datos
     * pkidtarifapuesto=>obligatorio, id de la tarifapuesto a editar
     * valortarifapuesto, valor de la tarifa que se va a asignar
     * numeroresoluciontarifapuesto, el numero de la resolucion que oficializa la tarifa
     * tarifapuestoactivo, si la tarifapuesto esta activa o no
     * fkidplaza, el id de la plaza a la que pertenece
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editTarifaPuestoAction(Request $request)
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
                            'msg'    => 'Tarifa de puesto no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idTarifaPuesto = (isset($params->pkidtarifapuesto)) ? $params->pkidtarifapuesto : null;

                            if($idTarifaPuesto != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $tarifapuesto = $em->getRepository('ModeloBundle:Ttarifapuesto')->find($idTarifaPuesto);
                    
                                if($tarifapuesto){
                          
                                    if(isset($params->valortarifapuesto)){

                                        if($params->valortarifapuesto != $tarifapuesto->getValortarifapuesto()){
                                            $tarifapuesto->setValortarifapuesto($params->valortarifapuesto);
                                            $tarifapuesto->setValorincrementoporcentual(0);
                                        }
                                    }

                                    if(isset($params->numeroresoluciontarifapuesto)){
                                        
                                        $numeroresolucionTarifaPuesto = $params->numeroresoluciontarifapuesto;
                                        //revisa en la tabla Ttarifapuesto si el valor que se desea asignar no existe en la misma plaza y en el mismo tipo
                                        $query = $em->getRepository('ModeloBundle:Ttarifapuesto')->createQueryBuilder('t')
                                            ->where('t.numeroresoluciontarifapuesto = :numeroresoluciontarifapuesto and t.pkidtarifapuesto != :pkidtarifapuesto')
                                            ->setParameter('numeroresoluciontarifapuesto', $numeroresolucionTarifaPuesto)
                                            ->setParameter('pkidtarifapuesto', $idTarifaPuesto)
                                            ->getQuery();
                                     
                                        $resolucionDuplicated = $query->getResult();

                                        if(!$resolucionDuplicated){
                                            $tarifapuesto->setNumeroresoluciontarifapuesto($numeroresolucionTarifaPuesto);
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
                                            $tarifapuesto->setFkidplaza($plaza);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La plaza no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idPlaza = $tarifapuesto->getFkidplaza();
                                    }

                                    if(isset($params->tarifapuestoactivo)){     
                                        $tarifapuesto->setTarifapuestoactivo($params->tarifapuestoactivo);
                                    }     
                
                                    $tarifapuesto->setModificaciontarifapuesto($today);

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
    
                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
    
                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($idTarifaPuesto . "_tarifapuesto_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
    
                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                    $doc_old = $tarifapuesto->getDocumentoresoluciontarifapuesto();
                                                    if ($doc_old != "sin documento") {
                                                        unlink($doc_old);
                                                    }
                                                    $tarifapuesto->setDocumentoresoluciontarifapuesto($fichero_subido);
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
                                            
                                            $documento_old = $tarifapuesto->getDocumentoresoluciontarifapuesto();

                                            unlink($documento_old);
                                            $tarifapuesto->setDocumentoresoluciontarifapuesto("sin documento");
                                        }
                                    }
                
                                    $em->persist($tarifapuesto);
                                    $em->flush();
                
                                    $data = array(
                                        'status'       => 'Exito',
                                        'msg'          => 'Tarifa de puesto actualizada!!',
                                        'tarifapuesto' => $tarifapuesto,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Ttarifapuesto',
                                        "valoresrelevantes"     => 'idTarifaPuesto:'.$idTarifaPuesto.',valorTarifaPuesto:'.$tarifapuesto->getValortarifapuesto(),
                                        'idelemento'            => $idTarifaPuesto,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La tarifa de puesto no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la tarifa de puesto a editar es nulo!!'
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
                'modulo'        => "TarifaPuesto",
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
     * Funcion para mostrar las tarifapuestos registradas
     * Recibe un json llamado json con los parametros para filtrar
     * pkidplaza, filtro por plazas a la que pertenece la tarifa
     * al enviar un json filtra tambien por tarifas activas
     * con el id de la plaza que se desea conocer.
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryTarifaPuestoAction(Request $request)
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
                            
                            //Consulta de tarifa animales por tipo de animal
                            $where = "";     

                            if(!empty($request->get('json'))){
                                $json = $request->get("json", null);
                                $params = json_decode($json);
                                
                                if($json != null){
                                    $where = "";
    
                                    if(isset($params->pkidplaza)){
                                        $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($params->pkidplaza);
                                                                    
                                        if($plaza){
                                            $where = "AND fkidplaza = ".$params->pkidplaza;
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La plaza no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                                }
                            }

                            //Consulta para traer los datos de la tarifapuesto, la plaza y el usuario a los que se encuentra asignada.
                            $query = "SELECT 
                                        pkidtarifapuesto,
                                        valortarifapuesto,
                                        numeroresoluciontarifapuesto,
                                        documentoresoluciontarifapuesto,
                                        fkidplaza,
                                        nombreplaza,
                                        tarifapuestoactivo 
                                      FROM ttarifapuesto 
                                        JOIN tplaza ON ttarifapuesto.fkidplaza = tplaza.pkidplaza
                                      WHERE tarifapuestoactivo = true $where
                                        ORDER BY numeroresoluciontarifapuesto ASC;";
                            
                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $tarifapuestos = $stmt->fetchAll();
                            
                            $data = array(
                                'status'        => 'Success',
                                'tarifaspuesto' => $tarifapuestos,
                            );
                        }else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'El usuario no tiene permisos genericos !!',
                            );
                        }
                        return $helpers->json($data);
                    }

                    if (in_array("PERM_TARIFAS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
                        
                        //Consulta para traer los datos de la tarifapuesto, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT 
                                    pkidtarifapuesto,
                                    valortarifapuesto,
                                    numeroresoluciontarifapuesto,
                                    documentoresoluciontarifapuesto,
                                    fkidplaza,
                                    nombreplaza,
                                    tarifapuestoactivo 
                                FROM ttarifapuesto 
                                    JOIN tplaza ON ttarifapuesto.fkidplaza = tplaza.pkidplaza
                                WHERE tarifapuestoactivo = true $where
                                    ORDER BY numeroresoluciontarifapuesto ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tarifapuestos = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($tarifapuestos as $tarifapuesto) {
                            $tarifapuestosList = array(
                                "pkidtarifapuesto"                => $tarifapuesto['pkidtarifapuesto'],
                                "valortarifapuesto"               => $tarifapuesto['valortarifapuesto'],
                                "numeroresoluciontarifapuesto"    => $tarifapuesto['numeroresoluciontarifapuesto'],
                                "documentoresoluciontarifapuesto" => $tarifapuesto['documentoresoluciontarifapuesto'],
                                "pkidplaza"                       => $tarifapuesto['fkidplaza'],
                                "nombreplaza"                     => $tarifapuesto['nombreplaza'],
                                "pkidtipoanimal"                  => $tarifapuesto['fkidtipoanimal'],
                                "nombretipoanimal"                => $tarifapuesto['nombretipoanimal'],
                                "tarifapuestoactivo"              => $tarifapuesto['tarifapuestoactivo'],
                            );
                            array_push($array_all, $tarifapuestosList);
                        }

                        $cabeceras = array(
                            array(
                                "nombrecampo"    => "valortarifapuesto",
                                "nombreetiqueta" => "Tarifa de puesto"
                            ),
                            array(
                                "nombrecampo"    => "numeroresoluciontarifapuesto",
                                "nombreetiqueta" => "Numero de Resolucion"
                            ),
                            array(
                                "nombrecampo"    => "documentoresoluciontarifapuesto",
                                "nombreetiqueta" => "Documento"
                            ),
                            array(
                                "nombrecampo"    => "nombreplaza",
                                "nombreetiqueta" => "Plaza"
                            ),
                            array(
                                "nombrecampo"    => "tarifapuestoactivo",
                                "nombreetiqueta" => "Tarifa de puesto Activa/Inactiva"
                            ),
                        );

                        $data = array(
                            'status'       => 'Exito',
                            'cabeceras'    => $cabeceras,
                            'tarifapuesto' => $array_all,
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
                'modulo'        => "TarifaPuesto",
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