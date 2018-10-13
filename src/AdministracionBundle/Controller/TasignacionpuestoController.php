<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tasignacionpuesto;
use ModeloBundle\Entity\Tpuesto;
use ModeloBundle\Entity\Tbeneficiario;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TasignacionpuestoController extends Controller
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
     * Funcion para registrar una asignacion de puesto
     * recibe los datos en un json llamado json con los datos
     * numeroresolucionasignacionpuesto, el numero de la resolucion que oficializa la asignacion
     * estadoasignacionpuesto, el estado en el que se encuentra la asignacion, puede ser nulo
     * asignacionpuestoactivo, si la asignacion de puesto esta activa o no
     * valortarifapuesto, valor de la tarifa para cada puesto, puede ser nulo
     * fkidpuesto, el id de la puesto a la que pertenece la asignacion
     * fkidbeneficiario, el id del beneficiario al que sera asociada la asignacion
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newAsignacionPuestoAction(Request $request)
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

                    if (in_array("PERM_ASIGNACION_PUESTOS", $permisosDeserializados)) {
                
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));
                        
                        //entiti manager
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Asignacion de Puesto no creada!!',
                        );

                        if ($json != null) {
                            
                            $numeroresolucionAsignacionPuesto = (isset($params->numeroresolucionasignacionpuesto)) ? $params->numeroresolucionasignacionpuesto : null;
                            $estadoAsignacionPuesto = (isset($params->estadoasignacionpuesto)) ? $params->estadoasignacionpuesto : null;
                            $asignacionPuestoActivo = (isset($params->asignacionpuestoactivo)) ? $params->asignacionpuestoactivo : true;
                            $valorTarifaPuesto = (isset($params->valortarifapuesto)) ? $params->valortarifapuesto : 0; 
                            $idPuesto = (isset($params->fkidpuesto)) ? $params->fkidpuesto : null; 
                            $idBeneficiario = (isset($params->fkidbeneficiario)) ? $params->fkidbeneficiario : null; 
                            
                            if($numeroresolucionAsignacionPuesto !=null && $idPuesto != null && $idBeneficiario !=null){
                                $puesto = $em->getRepository('ModeloBundle:Tpuesto')->find($idPuesto);
                                $beneficiario = $em->getRepository('ModeloBundle:Tbeneficiario')->find($idBeneficiario);
                                
                                if($puesto){
                                   
                                    if($beneficiario){
                                    
                                        //valida si el numero de resolucion no se repite.
                                        $resolucionDuplicated = $em->getRepository('ModeloBundle:Tasignacionpuesto')->findOneBy(array(
                                            "numeroresolucionasignacionpuesto" => $numeroresolucionAsignacionPuesto
                                        ));
                                        
                                        if(!$resolucionDuplicated){

                                            //Valida si el puesto tiene una asignacion a beneficiario activa
                                            $puestoDuplicated = $em->getRepository('ModeloBundle:Tasignacionpuesto')->findOneBy(array(
                                                "fkidpuesto" => $idPuesto,
                                                "asignacionpuestoactivo" => "true"
                                            ));
                                            
                                            if(!$puestoDuplicated){

                                                //Valida si el beneficiario tiene una asignacion a puesto activa en la misma plaza
                                                $query= "SELECT pkidasignacionpuesto 
                                                            FROM tasignacionpuesto
                                                            JOIN tpuesto ON tpuesto.pkidpuesto = tasignacionpuesto.fkidpuesto
                                                            JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                                            JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                                            JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza
                                                        WHERE tplaza.pkidplaza = (SELECT pkidplaza
                                                                                    FROM tpuesto
                                                                                    JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                                                                    JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                                                                    JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza
                                                                                    WHERE tpuesto.pkidpuesto = ".$idPuesto.")
                                                        AND tasignacionpuesto.fkidbeneficiario = ".$idBeneficiario." AND tasignacionpuesto.asignacionpuestoactivo = true";
                                                
                                                $stmt = $db->prepare($query);
                                                $parametros = array();
                                                $stmt->execute($parametros);
                                                $beneficiarioDuplicated = $stmt->fetchAll();
                                                
                                                if(!$beneficiarioDuplicated){
                                                
                                                    $asignacionpuesto = new Tasignacionpuesto();
                                                    $asignacionpuesto->setNumeroresolucionasignacionpuesto($numeroresolucionAsignacionPuesto);
                                                    $asignacionpuesto->setRutaresolucionasignacionpuesto("sin documento");
                                                    $asignacionpuesto->setEstadoasignacionpuesto($estadoAsignacionPuesto); 
                                                    $asignacionpuesto->setAsignacionpuestoactivo($asignacionPuestoActivo);
                                                    $asignacionpuesto->setValortarifapuesto($valorTarifaPuesto); 
                                                    $asignacionpuesto->setSaldodeuda(0); 
                                                    $asignacionpuesto->setSaldo(0); 
                                                    $asignacionpuesto->setValorincrementoporcentual(0); 
                                                    $asignacionpuesto->setCreacionasignacionpuesto($today);
                                                    $asignacionpuesto->setModificacionasignacionpuesto($today);
                                                    $asignacionpuesto->setFkidbeneficiario($beneficiario);
                                                    $asignacionpuesto->setFkidpuesto($puesto);
        
                                                    if (isset($_FILES['fichero_usuario'])) {
        
                                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
                                                                                                            
                                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
                    
                                                                $em->persist($asignacionpuesto);
                                                                $em->flush();
                                                                
                                                                $idAsignacionPuesto = $asignacionpuesto->getPkidasignacionpuesto();
                    
                                                                $dir_subida = '../web/documentos/';
                                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                                $fichero_subido = $dir_subida . basename($idAsignacionPuesto . "_asignacionpuesto_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
                    
                                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                                    $asignacionpuesto_doc = $em->getRepository('ModeloBundle:Tasignacionpuesto')->findOneBy(array(
                                                                        "pkidasignacionpuesto" => $asignacionpuesto->getPkidasignacionpuesto(),
                                                                    ));
                                                                    
                                                                    $asignacionpuesto_doc->setRutaresolucionasignacionpuesto($fichero_subido);
                                                                    $em->persist($asignacionpuesto_doc);
                                                                    $em->flush();
                    
                                                                    $data = array(
                                                                        'status'           => 'Exito',
                                                                        'msg'              => 'Asignacion de Puesto creada !!',
                                                                        'asignacionpuesto' => $asignacionpuesto_doc,
                                                                    );
                    
                                                                    $datos = array(
                                                                        'idusuario'             => $identity->sub,
                                                                        'nombreusuario'         => $identity->name,
                                                                        'identificacionusuario' => $identity->identificacion,
                                                                        'accion'                => 'insertar',
                                                                        "tabla"                 => 'Tasignacionpuesto',
                                                                        "valoresrelevantes"     => 'idAsignacionPuesto:'.$idAsignacionPuesto.',numeroResolucionAsignacionPuesto:'.$numeroresolucionAsignacionPuesto,
                                                                        'idelemento'            => $idAsignacionPuesto,
                                                                        'origen'                => 'web'
                                                                    );
                                
                                                                    $auditoria = $this->get(Auditoria::class);
                                                                    $auditoria->auditoria(json_encode($datos));
                    
                                                                }else{
                                                                    $em->remove($asignacionpuesto);
                                                                    $em->flush();
                    
                                                                    $data = array(
                                                                        'status' => 'Error',
                                                                        'msg'    => 'No se ha podido ingresar el documento pdf, intente nuevamente !!',
                                                                    );
                                                                }
                                                            }else{
                                                                $data = array(
                                                                    'status' => 'Error',
                                                                    'msg'    => 'Solo se aceptan archivos en formato PDF !!',
                                                                );
                                                            }
                                                        }else{
                                                            $data = array(
                                                                'status' => 'Error',
                                                                'msg'    => 'El tamaño del documento debe ser MAX 5MB !!',
                                                            );
                                                        }
                                                    }else{
                                                        $em->persist($asignacionpuesto);
                                                        $em->flush();
                                
                                                        $data = array(
                                                            'status'           => 'Exito',
                                                            'msg'              => 'Asignacion de Puesto creada!!',
                                                            'asignacionpuesto' => $asignacionpuesto,
                                                        );
                
                                                        $idAsignacionPuesto = $asignacionpuesto->getPkidasignacionpuesto();
                                                    
                                                        //una vez insertados los datos en la asignacionpuesto se realiza el proceso de auditoria
                                                        $datos = array(
                                                            'idusuario'             => $identity->sub,
                                                            'nombreusuario'         => $identity->name,
                                                            'identificacionusuario' => $identity->identificacion,
                                                            'accion'                => 'insertar',
                                                            "tabla"                 => 'Tasignacionpuesto',
                                                            "valoresrelevantes"     => 'idAsignacionPuesto:'.$idAsignacionPuesto.',numeroResolucionAsignacionPuesto:'.$numeroresolucionAsignacionPuesto,
                                                            'idelemento'            => $idAsignacionPuesto,
                                                            'origen'                => 'web'
                                                        );
                        
                                                        $auditoria = $this->get(Auditoria::class);
                                                        $auditoria->auditoria(json_encode($datos));
                                                    }
                                                }else{
                                                    $data = array(
                                                        'status' => 'error',
                                                        'msg'    => 'El beneficiario ya tiene un puesto asignado en la misma plaza!!'
                                                    );
                                                }
                                            }else{
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'El puesto ya tiene un beneficiario asignado!!'
                                                );
                                            }
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Resolucion duplicada!!'
                                            );
                                        }
                                    }else{
                                        $data = array(
                                            'status'=> 'error',
                                            'msg'   => 'El beneficiario no existe!!'
                                        );
                                    }
                                }else{
                                    $data = array(
                                        'status'=> 'error',
                                        'msg'   => 'El puesto no existe!!'
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
                'modulo'        => 'AsignacionPuesto',
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
     * Funcion para modificar una asignacionpuesto
     * recibe los datos en un json llamado json con los datos
     * pkidasignacionpuesto=>obligatorio, id de la asignacionpuesto a editar
     * numeroresolucionasignacionpuesto, el numero de la resolucion que oficializa la asignacion
     * estadoasignacionpuesto, el estado en el que se encuentra la asignacion, puede ser nulo
     * asignacionpuestoactivo, si la asignacion de puesto esta activa o no
     * valortarifapuesto, valor de la tarifa para cada puesto 
     * fkidpuesto, el id del puesto a la que pertenece la asignacion
     * fkidbeneficiario, el id del beneficiario al que sera asociada la asignacion
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editAsignacionPuestoAction(Request $request)
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

                    if (in_array("PERM_ASIGNACION_PUESTOS", $permisosDeserializados)) {
                        
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
                            'msg'    => 'Asignacion de Puesto no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idAsignacionPuesto = (isset($params->pkidasignacionpuesto)) ? $params->pkidasignacionpuesto : null;

                            if($idAsignacionPuesto != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $db = $em->getConnection();

                                $asignacionpuesto = $em->getRepository('ModeloBundle:Tasignacionpuesto')->find($idAsignacionPuesto);

                                if($asignacionpuesto){

                                    $idPuesto = (isset($params->fkidpuesto)) ? $params->fkidpuesto : $asignacionpuesto->getFkidpuesto();
                                    $puesto = $em->getRepository('ModeloBundle:Tpuesto')->find($idPuesto);
                                    if(!(isset($params->fkidpuesto))){
                                        $idPuesto = $puesto->getPkidpuesto();
                                    }

                                    $idBeneficiario = (isset($params->fkidbeneficiario)) ? $params->fkidbeneficiario : $asignacionpuesto->getFkidbeneficiario();
                                    $beneficiario = $em->getRepository('ModeloBundle:Tbeneficiario')->find($idBeneficiario);
                                    if(!(isset($params->fkidbeneficiario))){
                                        $idBeneficiario = $beneficiario->getPkidbeneficiario();
                                    }

                                    $asignacionPuestoActivo = (isset($params->asignacionpuestoactivo)) ? $params->asignacionpuestoactivo : false;

                                    //si la tarifa cambia crea una nueva asignacion desactivada con los datos anteriores para guardar historial 
                                    if(isset($params->valortarifapuesto) && $params->valortarifapuesto != $asignacionpuesto->getValortarifapuesto()){
                                        
                                        $asignacionpuestoNew = new Tasignacionpuesto();

                                        $asignacionpuestoNew->setNumeroresolucionasignacionpuesto($asignacionpuesto->getNumeroresolucionasignacionpuesto());
                                        $asignacionpuestoNew->setRutaresolucionasignacionpuesto($asignacionpuesto->getRutaresolucionasignacionpuesto());
                                        $asignacionpuestoNew->setEstadoasignacionpuesto($asignacionpuesto->getEstadoasignacionpuesto()); 
                                        $asignacionpuestoNew->setAsignacionPuestoactivo(false);
                                        $asignacionpuestoNew->setValortarifapuesto($asignacionpuesto->getValortarifapuesto());
                                        $asignacionpuestoNew->setValorincrementoporcentual($asignacionpuesto->getValorincrementoporcentual());
                                        $asignacionpuestoNew->setSaldodeuda($asignacionpuesto->getSaldodeuda()); 
                                        $asignacionpuestoNew->setSaldo($asignacionpuesto->getSaldo()); 
                                        $asignacionpuestoNew->setCreacionasignacionpuesto($asignacionpuesto->getCreacionasignacionpuesto());
                                        $asignacionpuestoNew->setModificacionasignacionpuesto($today);
                                        $asignacionpuestoNew->setFkidbeneficiario($asignacionpuesto->getFkidbeneficiario());
                                        $asignacionpuestoNew->setFkidpuesto($asignacionpuesto->getFkidpuesto());

                                        $asignacionpuesto->setValortarifapuesto($params->valortarifapuesto);
                                        $asignacionpuesto->setValorincrementoporcentual(0);

                                        $em->persist($asignacionpuestoNew);
                                    }

                                    $asignacionpuesto->setModificacionasignacionpuesto($today);

                                    if(isset($params->numeroresolucionasignacionpuesto)){

                                        if($asignacionpuesto->getNumeroresolucionasignacionpuesto() != $params->numeroresolucionasignacionpuesto){
                                            $numeroresolucionAsignacionPuesto = $params->numeroresolucionasignacionpuesto;

                                            //Valida si existe un registro con el mismo numero de resolucion que sea diferente a la que se esta editando
                                            $resolucionDuplicated = $em->getRepository('ModeloBundle:Tasignacionpuesto')->createQueryBuilder('a')
                                                ->where('a.numeroresolucionasignacionpuesto = :numeroresolucionasignacionpuesto')
                                                ->andwhere('a.pkidasignacionpuesto != :pkidasignacionpuesto')
                                                ->setParameter('numeroresolucionasignacionpuesto', $numeroresolucionAsignacionPuesto)
                                                ->setParameter('pkidasignacionpuesto', $idAsignacionPuesto)
                                                ->getQuery()
                                                ->getResult();
    
                                            if(!$resolucionDuplicated){
                                                $asignacionpuesto->setNumeroresolucionasignacionpuesto($numeroresolucionAsignacionPuesto);
                                            }else{
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'Resolucion duplicada!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                    }
                          
                                    if(isset($params->estadoasignacionpuesto)){
                                        $asignacionpuesto->setEstadoasignacionpuesto($params->estadoasignacionpuesto);
                                    }
                                   
                                    //validacion de puesto 
                                    if($puesto){
                                      
                                        if($asignacionPuestoActivo == true){
                                            //Valida si el puesto tiene una asignacion a beneficiario activa que sea diferente a la que se esta editando
                                            $query = $em->getRepository('ModeloBundle:Tasignacionpuesto')->createQueryBuilder('a')
                                                ->where('a.fkidpuesto = :fkidpuesto and a.pkidasignacionpuesto != :pkidasignacionpuesto and a.asignacionpuestoactivo = :asignacionpuestoactivo')
                                                ->setParameter('fkidpuesto',$idPuesto)
                                                ->setParameter('pkidasignacionpuesto', $idAsignacionPuesto)
                                                ->setParameter('asignacionpuestoactivo', true)
                                                ->getQuery();
                                        
                                            $puestoDuplicated = $query->getResult();

                                            if($puestoDuplicated){
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'El puesto ya tiene un beneficiario asignado!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                    }else{
                                        $data = array(
                                            'status'=> 'Error',
                                            'msg'   => 'La puesto no existe!!'
                                        );
                                        return $helpers->json($data);
                                    }
                                    
                                    //validacion de beneficiario
                                    if($beneficiario){

                                        if($asignacionPuestoActivo == true){
                                            //Valida si el beneficiario tiene una asignacion a puesto activa en la misma plaza que sea diferente a la que se esta editando
                                            $query= "SELECT 
                                                        pkidasignacionpuesto 
                                                    FROM tasignacionpuesto
                                                        JOIN tpuesto ON tpuesto.pkidpuesto = tasignacionpuesto.fkidpuesto
                                                        JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                                        JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                                        JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza
                                                    WHERE 
                                                        tasignacionpuesto.fkidbeneficiario = ".$idBeneficiario." 
                                                        AND tasignacionpuesto.asignacionpuestoactivo = true 
                                                        AND tasignacionpuesto.pkidasignacionpuesto != ".$idAsignacionPuesto."
                                                        AND tplaza.pkidplaza = (SELECT 
                                                                                    pkidplaza
                                                                                FROM tpuesto
                                                                                    JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                                                                    JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                                                                    JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza
                                                                                WHERE tpuesto.pkidpuesto = ".$idPuesto.");";
                                                    
                                            
                                            $stmt = $db->prepare($query);
                                            $parametros = array();
                                            $stmt->execute($parametros);
                                            $beneficiarioDuplicated = $stmt->fetchAll();
                                            
                                            if($beneficiarioDuplicated){
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'El beneficiario ya tiene un puesto asignado en la misma plaza!!'
                                                );
                                                return $helpers->json($data);
                                            }        
                                        }
                                    }else{
                                        $data = array(
                                            'status'=> 'Error',
                                            'msg'   => 'El beneficiario no existe!!'
                                        );
                                        return $helpers->json($data);
                                    }

                                    if(isset($params->fkidpuesto)){ 
                                        $asignacionpuesto->setFkidpuesto($puesto);
                                    }

                                    if(isset($params->fkidbeneficiario)){
                                        $asignacionpuesto->setFkidbeneficiario($beneficiario);
                                    }

                                    if(isset($params->asignacionpuestoactivo)){
                                        $asignacionpuesto->setAsignacionpuestoactivo($asignacionPuestoActivo);
                                    }

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
    
                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
    
                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($idAsignacionPuesto . "_asignacionpuesto_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
    
                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                    $doc_old = $asignacionpuesto->getRutaresolucionasignacionpuesto();
                                                    if ($doc_old != "sin documento") {
                                                        unlink($doc_old);
                                                    }
                                                    $asignacionpuesto->setRutaresolucionasignacionpuesto($fichero_subido);
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
                                        if(isset($params->rutaresolucionasignacionpuesto)){
                                            $documento = $params->rutaresolucionasignacionpuesto;
                                            /**
                                             * Si la ruta es falsa, se elimina el documento
                                             */
                                            if($documento == false){
                                                $documento_old = $asignacionpuesto->getRutaresolucionasignacionpuesto();
                                                if($documento_old != 'sin documento'){
                                                    unlink($documento_old);
                                                    $asignacionpuesto->setRutaresolucionasignacionpuesto("sin documento");
                                                }
                                            }
                                        }
                                    }
                
                                    $em->persist($asignacionpuesto);
                                    $em->flush();

                                    $data = array(
                                        'status'           => 'Exito',
                                        'msg'              => 'Asignacion de Puesto actualizada!!',
                                        'asignacionpuesto' => $asignacionpuesto,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Tasignacionpuesto',
                                        "valoresrelevantes"     => 'idAsignacionPuesto:'.$idAsignacionPuesto.',saldoDeuda:'.$asignacionpuesto->getSaldodeuda(),
                                        'idelemento'            => $idAsignacionPuesto,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                    
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La Asignacion de Puesto no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la Asignacion de Puesto a editar es nulo!!'
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
            }else{
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
                'modulo'        => "AsignacionPuesto",
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
     * @Route("/edittipo")
     * Funcion para modificar la tarifa en varias asignaciones de puesto
     * por tipo de puesto, recibe los datos en un json llamado un json que tenga
     * fkidtipopuesto, el tipo de puesto al que se van a asignar las tarifas,
     * valortarifapuesto, valor la tarifa que sera asignada
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editTipoAction(Request $request)
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

                    if (in_array("PERM_ASIGNACION_PUESTOS", $permisosDeserializados)) {
                       
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
                            'msg'    => 'Asignaciones de puesto no actualizadas!!',
                        );

                        if ($json != null) {
                            
                            $idTipoPuesto = (isset($params->fkidtipopuesto)) ? $params->fkidtipopuesto : null;
                            $valorTarifaPuesto = (isset($params->valortarifapuesto)) ? $params->valortarifapuesto : 0;
                            
                            if($idTipoPuesto != null){

                                $tipopuesto = $em->getRepository('ModeloBundle:Ttipopuesto')->find($idTipoPuesto);

                                if($tipopuesto != null){
                            
                                    $em = $this->getDoctrine()->getManager();
                                    $db = $em->getConnection();

                                    $asignaciones = $em->getRepository('ModeloBundle:Tasignacionpuesto')->createQueryBuilder('a')
                                                        ->join('a.fkidpuesto','p')
                                                        ->where('a.asignacionpuestoactivo = :asignacionpuestoactivo')
                                                        ->andwhere('p.fkidtipopuesto = :fkidtipopuesto')
                                                        ->setParameter('asignacionpuestoactivo', true)
                                                        ->setParameter('fkidtipopuesto', $idTipoPuesto)
                                                        ->getQuery()
                                                        ->getResult();
                                    
                                    if($asignaciones){

                                        $asignacionespuesto = array();

                                        foreach($asignaciones as $asignacion){

                                            $asignacionNew = new Tasignacionpuesto();
                                            $asignacionNew->setNumeroresolucionasignacionpuesto($asignacion->getNumeroresolucionasignacionpuesto());
                                            $asignacionNew->setRutaresolucionasignacionpuesto($asignacion->getRutaresolucionasignacionpuesto());
                                            $asignacionNew->setEstadoasignacionpuesto($asignacion->getEstadoasignacionpuesto()); 
                                            $asignacionNew->setAsignacionPuestoactivo(false);
                                            $asignacionNew->setValortarifapuesto($asignacion->getValortarifapuesto());
                                            $asignacionNew->setValorincrementoporcentual($asignacion->getValorincrementoporcentual());
                                            $asignacionNew->setSaldodeuda($asignacion->getSaldodeuda()); 
                                            $asignacionNew->setSaldo($asignacion->getSaldo()); 
                                            $asignacionNew->setCreacionasignacionpuesto($asignacion->getCreacionasignacionpuesto());
                                            $asignacionNew->setModificacionasignacionpuesto($today);
                                            $asignacionNew->setFkidbeneficiario($asignacion->getFkidbeneficiario());
                                            $asignacionNew->setFkidpuesto($asignacion->getFkidpuesto());

                                            $asignacion->setValortarifapuesto($params->valortarifapuesto);
                                            $asignacion->setValorincrementoporcentual(0);

                                            $em->persist($asignacionNew);
                                            
                                            array_push($asignacionespuesto, $asignacion);
            
                                            $datos = array(
                                                "idusuario"             => $identity->sub,
                                                "nombreusuario"         => $identity->name,
                                                "identificacionusuario" => $identity->identificacion,
                                                'accion'                => 'editar',
                                                "tabla"                 => 'Tasignacionpuesto',
                                                "valoresrelevantes"     => 'idAsignacionPuesto:'.$asignacion->getPkidasignacionpuesto().',tarifa:'.$params->valortarifapuesto,
                                                'idelemento'            => $asignacionNew->getPkidasignacionpuesto(),
                                                'origen'                => 'web'
                                            );
            
                                            $auditoria = $this->get(Auditoria::class);
                                            $auditoria->auditoria(json_encode($datos)); 
                                        }

                                        $em->flush();

                                        $data = array(
                                            'status'           => 'Exito',
                                            'msg'              => 'Asignaciones de Puesto actualizadas!!',
                                            'asignacionpuesto' => $asignacionespuesto,
                                        );

                                    }else{
                                        $data = array(
                                            'status' => 'error',
                                            'msg'    => 'No existen asignaciones de puesto en este tipo de puesto!!'
                                        );
                                    }  
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'El tipo de puesto no existe!!'
                                    );
                                }  
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El tipo de puesto es nulo!!'
                                );
                            }
                        }else{
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'Parametro json es nulo!!',
                            );
                        }
                    }else{
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'No tiene los permisos!!',
                        );
                    }
                }else{
                    $data = array(
                        'status' => 'error',
                        'msg'    => 'Token no valido !!',
                    );
                }
            } else{
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
                'modulo'        => "AsignacionPuesto",
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
     * Funcion para mostrar todos los puestos con asignaciones asociadas
     * recibe un token en una variable llamada authorization
     * para retornar los puestos disponibles recibe el parametro
     * puesto con valor true.
     * Para retornar las asignaciones de puesto activas recibe el parametro
     * activo con valor true.
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryAsignacionPuestoAction(Request $request)
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

                    $em = $this->getDoctrine()->getManager();
                    $db = $em->getConnection();

                    $puesto = $request->get('puesto', null); 
                      
                    if($puesto != null && $puesto == "true"){

                        if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                           //retorna los puestos disponibles
                            $query = " SELECT pkidpuesto,numeropuesto
                                       FROM tpuesto 
                                       WHERE 
                                            tpuesto.pkidpuesto not in (SELECT fkidpuesto
                                                                        FROM tasignacionpuesto
                                                                        WHERE tasignacionpuesto.asignacionpuestoactivo = true)";
                            
                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $puestos = $stmt->fetchAll();

                            $data = array(
                                'status' => 'Exito',
                                'puesto' => $puestos,
                            );
                            
                            return $helpers->json($data);

                        }else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'El usuario no tiene permisos genericos !!',
                            );
                        }
                        return $helpers->json($data);
                    }

                    $activo = $request->get('activo', null); 
                      
                    if($activo != null && $activo == "true"){
                        if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                            
                            $query = "SELECT 
                                        pkidasignacionpuesto, 
                                        numeroresolucionasignacionpuesto, 
                                        rutaresolucionasignacionpuesto, 
                                        estadoasignacionpuesto, 
                                        asignacionpuestoactivo,
                                        valortarifapuesto,  
                                        saldodeuda,
                                        saldofavor,
                                        pkidpuesto, 
                                        numeropuesto, 
                                        pkidtipopuesto,
                                        nombretipopuesto,
                                        pkidsector,
                                        nombresector,
                                        pkidplaza,
                                        nombreplaza,
                                        pkidbeneficiario, 
                                        nombrebeneficiario, 
                                        identificacionbeneficiario
                                    FROM 
                                        tasignacionpuesto
                                        JOIN tpuesto ON tpuesto.pkidpuesto = tasignacionpuesto.fkidpuesto
                                        JOIN ttipopuesto ON ttipopuesto.pkidtipopuesto = tpuesto.fkidtipopuesto
                                        JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                        JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                        JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza
                                        JOIN tbeneficiario ON tbeneficiario.pkidbeneficiario = tasignacionpuesto.fkidbeneficiario
                                    WHERE tasignacionpuesto.asignacionpuestoactivo = true
                                    ORDER BY tasignacionpuesto.numeroresolucionasignacionpuesto ASC";
                            
                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $asignacionpuestos = $stmt->fetchAll();

                            $data = array(
                                'status'           => 'Exito',
                                'asignacionpuesto' => $asignacionpuestos,
                            );

                        }else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'El usuario no tiene permisos genericos !!',
                            );
                        }
                        return $helpers->json($data);
                    }

                    if (in_array("PERM_ASIGNACION_PUESTOS", $permisosDeserializados)) {
                    
                        //Consulta para traer los datos de la asignacionpuesto, la puesto y el usuario a los que se encuentra asignada.
                        $query = "SELECT 
                                    pkidasignacionpuesto, 
                                    numeroresolucionasignacionpuesto, 
                                    rutaresolucionasignacionpuesto, 
                                    estadoasignacionpuesto, 
                                    asignacionpuestoactivo,
                                    valortarifapuesto,  
                                    saldodeuda,
                                    saldofavor,
                                    pkidpuesto, 
                                    numeropuesto, 
                                    pkidtipopuesto,
                                    nombretipopuesto,
                                    pkidsector,
                                    nombresector,
                                    pkidplaza,
                                    nombreplaza,
                                    pkidbeneficiario, 
                                    nombrebeneficiario, 
                                    identificacionbeneficiario
                                FROM 
                                    tasignacionpuesto
                                    JOIN tpuesto ON tpuesto.pkidpuesto = tasignacionpuesto.fkidpuesto
                                    JOIN ttipopuesto ON ttipopuesto.pkidtipopuesto = tpuesto.fkidtipopuesto
                                    JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                    JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                    JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza
                                    JOIN tbeneficiario ON tbeneficiario.pkidbeneficiario = tasignacionpuesto.fkidbeneficiario
                                ORDER BY tasignacionpuesto.numeroresolucionasignacionpuesto ASC";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $asignacionpuestos = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($asignacionpuestos as $asignacionpuesto) {

                            $asignacionpuestosList = array(
                                "pkidasignacionpuesto"             => $asignacionpuesto['pkidasignacionpuesto'],
                                "numeroresolucionasignacionpuesto" => $asignacionpuesto['numeroresolucionasignacionpuesto'],
                                "rutaresolucionasignacionpuesto"   => $asignacionpuesto['rutaresolucionasignacionpuesto'],
                                "estadoasignacionpuesto"           => $asignacionpuesto['estadoasignacionpuesto'],
                                "valortarifapuesto"                => $asignacionpuesto['valortarifapuesto'],                                    
                                "saldodeuda"                       => $asignacionpuesto['saldodeuda'],
                                "saldofavor"                       => $asignacionpuesto['saldofavor'],
                                "plaza"                            => array("pkidplaza" => $asignacionpuesto['pkidplaza'], "nombreplaza" => $asignacionpuesto['nombreplaza']),
                                "sector"                           => array("pkidsector" => $asignacionpuesto['pkidsector'],"nombresector" => $asignacionpuesto['nombresector']), 
                                "tipopuesto"                       => array("pkidtipopuesto" => $asignacionpuesto['pkidtipopuesto'], "nombretipopuesto" => $asignacionpuesto['nombretipopuesto']),
                                "puesto"                           => array("pkidpuesto" => $asignacionpuesto['pkidpuesto'], "numeropuesto" => $asignacionpuesto['numeropuesto']),
                                "beneficiario"                     => array("pkidbeneficiario" => $asignacionpuesto['pkidbeneficiario'], "nombrebeneficiario" => $asignacionpuesto['nombrebeneficiario'], "identificacionbeneficiario" => $asignacionpuesto['identificacionbeneficiario']),
                                "asignacionpuestoactivo"           => $asignacionpuesto['asignacionpuestoactivo'],
                            );
                            array_push($array_all, $asignacionpuestosList);
                        }

                        $cabeceras = array("Asignacion de Puesto","Numero de Resolución","Documento","Estado","Tarifa","Saldo","Plaza","Sector","Puesto","Beneficiario","Asignacion de Puesto Activa/Inactiva");

                        $data = array(
                            'status'           => 'Success',
                            'cabeceras'        => $cabeceras,
                            'asignacionpuesto' => $array_all,
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
                'modulo'        => "AsignacionPuesto",
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