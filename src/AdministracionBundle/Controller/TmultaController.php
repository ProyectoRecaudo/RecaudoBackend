<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tmulta;
use ModeloBundle\Entity\Tasignacionpuesto;
use ModeloBundle\Entity\Tbeneficiario;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TmultaController extends Controller
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
     * Funcion para registrar una multa 
     * recibe los datos en un json llamado json con los datos
     * valormulta, el valor de la multa que se da la beneficiario
     * resolucionmulta, el numero de la resolucion que oficializa el multa
     * multaactivo, si la multa  esta activa o no
     * valorcuotamensualmulta, valor que el beneficiario pagara cada mes junto con el pago del asignacionpuesto
     * descripcionmulta, descripcion de porque fue impuesta la multa
     * interes, interes que se asigna a la multa por mora--pendiente de verificacion. 
     * fkidasignacionpuesto, el id del asignacionpuesto al que pertenece el multa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newMultaAction(Request $request)
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

                    if (in_array("PERM_MULTAS", $permisosDeserializados)) {
                       
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
                            'msg'    => 'Multa  no creada!!',
                        );

                        if ($json != null) {
                            //return $helpers->json($json);
                            
                            $resolucionMulta = (isset($params->resolucionmulta)) ? $params->resolucionmulta : null;
                            $multaActivo = (isset($params->multaactivo)) ? $params->multaactivo : true;
                            $valorMulta = (isset($params->valormulta)) ? $params->valormulta : null;
                            $valorCuotaMensualMulta = (isset($params->valorcuotamensualmulta)) ? $params->valorcuotamensualmulta : null;
                            $descripcionMulta = (isset($params->descripcionmulta)) ? $params->descripcionmulta : null;
                            $interes = (isset($params->interes)) ? $params->interes : 0;
                            $idAsignacionPuesto = (isset($params->fkidasignacionpuesto)) ? $params->fkidasignacionpuesto : null; 
                            
                            if($resolucionMulta !=null && $valorMulta !=null && $valorCuotaMensualMulta !=null && $idAsignacionPuesto !=null){
                                
                                $asignacionpuesto = $em->getRepository('ModeloBundle:Tasignacionpuesto')->find($idAsignacionPuesto);

                                if($asignacionpuesto){

                                    //valida si el numero de resolucion no se repite.
                                    $resolucionDuplicated = $em->getRepository('ModeloBundle:Tmulta')->findOneBy(array(
                                        "resolucionmulta" => $resolucionMulta
                                    ));
                                    
                                    if(!$resolucionDuplicated){
                                               
                                        $multa = new Tmulta();
                                        $multa->setResolucionmulta($resolucionMulta);
                                        $multa->setDocumentomulta("sin documento");
                                        $multa->setMultaactivo($multaActivo);
                                        $multa->setValormulta($valorMulta);
                                        $multa->setValorcuotamensualmulta($valorCuotaMensualMulta);
                                        $multa->setDescripcionmulta($descripcionMulta);
                                        $multa->setInteres($interes);
                                        $multa->setSaldomulta(0);
                                        $multa->setCreacionmulta($today);
                                        $multa->setModificacionmulta($today); 
                                        $multa->setFkidasignacionpuesto($asignacionpuesto);                           

                                        if (isset($_FILES['fichero_usuario'])) {

                                            if ($_FILES['fichero_usuario']['size'] <= 5242880) {
                                                                                                
                                                if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
        
                                                    $em->persist($multa);
                                                    $em->flush();
                                                    
                                                    $idMulta = $multa->getPkidmulta();
        
                                                    $dir_subida = '../web/documentos/';
                                                    $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                    $fichero_subido = $dir_subida . basename($idMulta . "_multa_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
        
                                                    if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                        $multa_doc = $em->getRepository('ModeloBundle:Tmulta')->findOneBy(array(
                                                            "pkidmulta" => $multa->getPkidmulta(),
                                                        ));
                                                        
                                                        $multa_doc->setDocumentomulta($fichero_subido);
                                                        $em->persist($multa_doc);
                                                        $em->flush();
        
                                                        $data = array(
                                                            'status' => 'Exito',
                                                            'msg'    => 'Multa creada!!',
                                                            'multa'  => $multa_doc,
                                                        );
        
                                                        $datos = array(
                                                            'idusuario'             => $identity->sub,
                                                            'nombreusuario'         => $identity->name,
                                                            'identificacionusuario' => $identity->identificacion,
                                                            'accion'                => 'insertar',
                                                            "tabla"                 => 'Tmulta',
                                                            "valoresrelevantes"     => 'idMulta:'.$idMulta.',resolucionMulta:'.$resolucionMulta,
                                                            'idelemento'            => $idMulta,
                                                            'origen'                => 'web'
                                                        );
                    
                                                        $auditoria = $this->get(Auditoria::class);
                                                        $auditoria->auditoria(json_encode($datos));
        
                                                    } else {
                                                        $em->remove($multa);
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
                                            $em->persist($multa);
                                            $em->flush();
                    
                                            $data = array(
                                                'status' => 'Exito',
                                                'msg'    => 'Multa creada!!',
                                                'multa'  => $multa,
                                            );
    
                                            $idMulta = $multa->getPkidmulta();
                                        
                                            //una vez insertados los datos en la multa se realiza el asignacionpuesto de auditoria
                                            $datos = array(
                                                'idusuario'             => $identity->sub,
                                                'nombreusuario'         => $identity->name,
                                                'identificacionusuario' => $identity->identificacion,
                                                'accion'                => 'insertar',
                                                "tabla"                 => 'Tmulta',
                                                "valoresrelevantes"     => 'idMulta:'.$idMulta.',resolucionMulta:'.$resolucionMulta,
                                                'idelemento'            => $idMulta,
                                                'origen'                => 'web'
                                            );
            
                                            $auditoria = $this->get(Auditoria::class);
                                            $auditoria->auditoria(json_encode($datos));
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
                                        'msg'   => 'El asignacionpuesto no existe!!'
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
                'modulo'        => 'Multa',
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
     * Funcion para modificar una multa
     * recibe los datos en un json llamado json con los datos
     * pkidmulta=>obligatorio, id de la multa a editar
     * valormulta, el valor de la multa que se da la beneficiario
     * resolucionmulta, el numero de la resolucion que oficializa el multa
     * multaactivo, si la multa  esta activa o no
     * valorcuotamensualmulta, valor que el beneficiario pagara cada mes junto con el pago del asignacionpuesto
     * descripcionmulta, descripcion de porque fue impuesta la multa
     * interes, interes que se asigna a la multa por mora--pendiente de verificacion. 
     * fkidasignacionpuesto, el id del asignacionpuesto al que pertenece el multa
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editMultaAction(Request $request)
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

                    if (in_array("PERM_MULTAS", $permisosDeserializados)) {
                        
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
                            'msg'    => 'Multa no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idMulta = (isset($params->pkidmulta)) ? $params->pkidmulta : null;

                            if($idMulta != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $db = $em->getConnection();

                                $multa = $em->getRepository('ModeloBundle:Tmulta')->find($idMulta);

                                if($multa){
                                    
                                    if(isset($params->resolucionmulta)){

                                        if($multa->getResolucionmulta() != $params->resolucionmulta){
                                            $resolucionMulta = $params->resolucionmulta;

                                            //Valida si existe un registro con el mismo numero de resolucion que sea diferente a la que se esta editando
                                            $resolucionDuplicated = $em->getRepository('ModeloBundle:Tmulta')->createQueryBuilder('a')
                                                ->where('a.resolucionmulta = :resolucionmulta')
                                                ->andwhere('a.pkidmulta != :pkidmulta')
                                                ->setParameter('resolucionmulta', $resolucionMulta)
                                                ->setParameter('pkidmulta', $idMulta)
                                                ->getQuery()
                                                ->getResult();
    
                                            if(!$resolucionDuplicated){
                                                $multa->setResolucionmulta($resolucionMulta);
                                            }else{
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'Resolucion duplicada!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                    }

                                    if(isset($params->multaactivo)){
                                        $multa->setMultaactivo($params->multaactivo);
                                    }

                                    if(isset($params->valormulta)){
                                        $multa->setValormulta($params->valormulta);
                                    }

                                    if(isset($params->valorcuotamensualmulta)){
                                       $multa->setValorcuotamensualmulta($params->valorcuotamensualmulta);
                                    }    

                                    if(isset($params->descripcionmulta)){
                                        $multa->setDescripcionmulta($params->descripcionmulta);
                                    }
                                    
                                    if(isset($params->interes)){
                                        $multa->setInteres($params->interes);
                                    }

                                    $multa->setModificacionmulta($today); 
 
                                    if(isset($params->fkidasignacionpuesto)){ 

                                        $idAsignacionPuesto = $params->fkidasignacionpuesto;
                                        $asignacionpuesto = $em->getRepository('ModeloBundle:Tasignacionpuesto')->find($idAsignacionPuesto);

                                        if($asignacionpuesto){
                                            $multa->setFkidasignacionpuesto($asignacionpuesto);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La asignacionpuesto no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                    
                                    $multa->setModificacionmulta($today);

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
    
                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
    
                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($idMulta . "_multa_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
    
                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                   $multa->setDocumentomulta($fichero_subido);
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
                                    }
                
                                    $em->persist($multa);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg'    => 'Multa  actualizada!!',
                                        'multa'  => $multa,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Tmulta',
                                        "valoresrelevantes"     => 'idMulta:'.$idMulta.',resolucionMulta:'.$multa->getResolucionmulta(),
                                        'idelemento'            => $idMulta,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La Multa  no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la Multa  a editar es nulo!!'
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
                'modulo'        => "Multa",
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
     * Funcion para mostrar todos los asignacionpuestos con asignaciones asociadas
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryMultaAction(Request $request)
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
                        
                        $query = "SELECT 
                                    tmulta.pkidmulta, 
                                    tmulta.resolucionmulta, 
                                    tmulta.documentomulta, 
                                    tmulta.multaactivo, 
                                    tmulta.valormulta, 
                                    tmulta.valorcuotamensualmulta, 
                                    tmulta.descripcionmulta, 
                                    tmulta.interes,
                                    tmulta.saldomulta,  
                                    tmulta.fechapagototal,  
                                    tasignacionpuesto.pkidasignacionpuesto,
                                    tasignacionpuesto.numeroresolucionasignacionpuesto,
                                    tasignacionpuesto.asignacionpuestoactivo,
                                    tbeneficiario.pkidbeneficiario, 
                                    tbeneficiario.nombrebeneficiario, 
                                    tbeneficiario.identificacionbeneficiario,
                                    tbeneficiario.beneficiarioactivo,
                                    tpuesto.pkidpuesto, 
                                    tpuesto.numeropuesto, 
                                    tpuesto.puestoactivo,
                                    tsector.pkidsector, 
                                    tsector.nombresector, 
                                    tzona.pkidzona, 
                                    tzona.nombrezona, 
                                    tplaza.pkidplaza, 
                                    tplaza.nombreplaza
                                FROM 
                                    tmulta 
                                    JOIN tasignacionpuesto ON tasignacionpuesto.pkidasignacionpuesto = tmulta.fkidasignacionpuesto
                                    JOIN tbeneficiario ON tbeneficiario.pkidbeneficiario = tasignacionpuesto.fkidbeneficiario
                                    JOIN tpuesto ON tpuesto.pkidpuesto = tasignacionpuesto.fkidpuesto 
                                    JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                    JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                    JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza where multaactivo=true
                                ORDER BY tmulta.resolucionmulta ASC";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $multas = $stmt->fetchAll();

                        $data = array(
                            'status'    => 'Success',
                            'multa'     => $multas,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_MULTAS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();                        
                                            
                        //Consulta para traer los datos de la multa, la asignacionpuesto y el usuario a los que se encuentra asignada.
                        $query = "SELECT 
                                    tmulta.pkidmulta, 
                                    tmulta.resolucionmulta, 
                                    tmulta.documentomulta, 
                                    tmulta.multaactivo, 
                                    tmulta.valormulta, 
                                    tmulta.valorcuotamensualmulta, 
                                    tmulta.descripcionmulta, 
                                    tmulta.interes,
                                    tmulta.saldomulta,  
                                    tmulta.fechapagototal,  
                                    tasignacionpuesto.pkidasignacionpuesto,
                                    tasignacionpuesto.numeroresolucionasignacionpuesto,
                                    tasignacionpuesto.asignacionpuestoactivo,
                                    tbeneficiario.pkidbeneficiario, 
                                    tbeneficiario.nombrebeneficiario, 
                                    tbeneficiario.identificacionbeneficiario,
                                    tbeneficiario.beneficiarioactivo,
                                    tpuesto.pkidpuesto, 
                                    tpuesto.numeropuesto, 
                                    tpuesto.puestoactivo,
                                    tsector.pkidsector, 
                                    tsector.nombresector, 
                                    tzona.pkidzona, 
                                    tzona.nombrezona, 
                                    tplaza.pkidplaza, 
                                    tplaza.nombreplaza
                                FROM 
                                    tmulta 
                                    JOIN tasignacionpuesto ON tasignacionpuesto.pkidasignacionpuesto = tmulta.fkidasignacionpuesto
                                    JOIN tbeneficiario ON tbeneficiario.pkidbeneficiario = tasignacionpuesto.fkidbeneficiario
                                    JOIN tpuesto ON tpuesto.pkidpuesto = tasignacionpuesto.fkidpuesto 
                                    JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                    JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                    JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza
                                ORDER BY tmulta.resolucionmulta ASC";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $multas = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($multas as $multa) {
                            $multasList = array(
                                "pkidmulta"              => $multa['pkidmulta'],
                                "resolucionmulta"        => $multa['resolucionmulta'],
                                "documentomulta"         => $multa['documentomulta'],  
                                "valormulta"             => $multa['valormulta'], 
                                "valorcuotamensualmulta" => $multa['valorcuotamensualmulta'], 
                                "descripcionmulta"       => $multa['descripcionmulta'], 
                                "interes"                => $multa['interes'],
                                "saldomulta"             => $multa['saldomulta'], 
                                "fechapagototal"         => $multa['fechapagototal'],
                                "asignacionpuesto"       => array(
                                                            "pkidasignacionpuesto"             => $multa['pkidasignacionpuesto'],
                                                            "numeroresolucionasignacionpuesto" => $multa['numeroresolucionasignacionpuesto'],
                                                            "asignacionpuestoactivo"           => $multa['asignacionpuestoactivo'],
                                                         ),
                                "beneficiario"           => array(
                                                            "pkidbeneficiario"           => $multa['pkidbeneficiario'],
                                                            "identificacionbeneficiario" => $multa['identificacionbeneficiario'],
                                                            "nombrebeneficiario"         => $multa['nombrebeneficiario'],
                                                            "beneficiarioactivo"         => $multa['beneficiarioactivo'],
                                                         ),
                                "puesto"                 => array(
                                                            "pkidpuesto"   => $multa['pkidpuesto'],
                                                            "numeropuesto" => $multa['numeropuesto'],
                                                            "puestoactivo" => $multa['puestoactivo'],
                                                         ),
                                "sector"                 => array(
                                                            "pkidsector"   => $multa['pkidsector'],
                                                            "nombresector" => $multa['nombresector'],
                                                         ),
                                "zona"                   => array(
                                                            "pkidzona"   => $multa['pkidzona'],
                                                            "nombrezona" => $multa['nombrezona'],
                                                         ),
                                "plaza"                  => array(
                                                            "pkidplaza"   => $multa['pkidplaza'],
                                                            "nombreplaza" => $multa['nombreplaza'],
                                                         ),
                                "multaactivo"            => $multa['multaactivo']
                            );
                            array_push($array_all, $multasList);
                        }

                        $cabeceras = array("Multa","Asignacion de Puesto","Beneficiario","Puesto","Sector","Zona","Plaza","Multa Activo/Inactivo");

                        $data = array(
                            'status'    => 'Success',
                            'cabeceras' => $cabeceras,
                            'multa'     => $array_all,
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
                'modulo'        => "Multa",
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