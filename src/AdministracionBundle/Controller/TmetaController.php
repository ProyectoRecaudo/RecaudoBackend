<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tmeta;
use ModeloBundle\Entity\Tfactura;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TmetaController extends Controller
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
     * Funcion para registrar una meta 
     * recibe los datos en un json llamado json con los datos
     * mes, mes en el que se genera las metas
     * pkidsector, identificador del sector, para crear metas por sector
     * pkidzona, identificador de la zona, para crear metas por zona
     * pkidplaza, identificador de la plaza, para crear metas por plaza
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newMetaAction(Request $request)
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                       
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
                            'msg'    => 'Meta  no creada!!',
                        );

                        if ($json != null) {
                           
                            /** 
                             * se calculan los totales de valores a recaudar por sector y mes 
                             * totaltarifapuesto, la suma total del valor que se debe recaudar en cada puesto fijo 
                             * totalsaldodeuda, suma total de tods las deudas en los puestos fijos
                             * totalcuotaacuerdo, suma total de las cuotas de acuerdos en puestos fijos
                             * totalsaldodeudaacuerdo, suma total de la deuda de acuerdos en puestos fijos
                             * totalsaldomultas, suma total de las multas en puestos fijos
                             * totalpuestoeventual, suma total de los valores en puestos eventuales en los recibos del anterior mes
                            */

                            $idSector = (isset($params->pkidsector)) ? $params->pkidsector : null; 
                            $idZona = (isset($params->pkidzona)) ? $params->pkidzona : null;
                            $idPlaza = (isset($params->pkidplaza)) ? $params->pkidplaza : null;
                            $mes = (isset($params->mes)) ? $params->mes : null;
                            
                            
                            if($idSector !=null || $idZona !=null || $idPlaza !=null){

                                if($mes !=null){
                                   
                                    $metas = array();
                                    $errores = array();
                                    
                                    if($idPlaza !=null){
                                        
                                        $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                                
                                        if($plaza){
                                            $sectores = $em->createQueryBuilder()
                                            ->select("s.pkidsector")
                                            ->from('ModeloBundle:Tsector','s')
                                            ->join("s.fkidzona","z")
                                            ->where('z.fkidplaza = :fkidplaza')
                                            ->setParameter('fkidplaza', $idPlaza)
                                            ->getQuery()
                                            ->getResult();
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'La plaza no existe!!',
                                            );
                                            
                                            return $helpers->json($data);
                                        }

                                    }elseif ($idZona != null){
                                        
                                        $zona = $em->getRepository('ModeloBundle:Tzona')->find($idZona);
                                
                                        if($zona){
                                            $sectores = $em->createQueryBuilder()
                                                ->select("s.pkidsector")
                                                ->from('ModeloBundle:Tsector','s')
                                                ->where('s.fkidzona = :fkidzona')
                                                ->setParameter('fkidzona', $idZona)
                                                ->getQuery()
                                                ->getResult();
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'La zona no existe!!',
                                            );
                                            
                                            return $helpers->json($data);
                                        }
                                    }else{

                                        $sector = $em->getRepository('ModeloBundle:Tsector')->find($idSector);
                                
                                        if($sector){
                                            $sectores = array(
                                                array("pkidsector" => $idSector)
                                            );
                                        }else{
                                            $data = array(
                                                'status'=> 'error',
                                                'msg'   => "El sector no existe!!"
                                            );
                                            return $helpers->json($data);
                                        }
                                    }

                                    foreach ($sectores as $sector) {

                                        $idSector = $sector['pkidsector'];
                                        $sector = $em->getRepository('ModeloBundle:Tsector')->find($idSector);
                                
                                        $factura = $em->getRepository('ModeloBundle:Tfactura')->createQueryBuilder('f')
                                            ->select("sum(f.tarifapuesto) as totaltarifapuesto,
                                                        sum(f.saldodeuda) as totalsaldodeuda,
                                                        sum(f.valorcuotaacuerdo) as totalcuotaacuerdo,
                                                        sum(f.saldodeudaacuerdo) as totalsaldodeudaacuerdo,
                                                        sum(f.saldomultas) as totalsaldomultas,
                                                        f.mesfacturaletras")
                                            ->where('f.fkidsector = :fkidsector')
                                            ->andwhere('f.facturaactivo = :facturaactivo')
                                            ->andwhere('f.mesfacturanumero = :mesfacturanumero')
                                            ->andwhere('f.year = :year')
                                            ->setParameter('fkidsector', $idSector)
                                            ->setParameter('facturaactivo', true)
                                            ->setParameter('mesfacturanumero', $mes)
                                            ->setParameter('year', $today->format('Y'))
                                            ->groupBy ("f.mesfacturaletras")
                                            ->getQuery()
                                            ->getResult();
                                        
                                        if($factura){
                                            
                                            $query = "SELECT
                                                            sum(r.valorecibopuestoeventual) as totalvalorpuestoeventual
                                                        FROM trecibopuestoeventual as r
                                                        WHERE 
                                                            date_part('month',r.creacionrecibopuestoeventual) = ".($mes-1)." AND
                                                            date_part('year',r.creacionrecibopuestoeventual) =". $today->format('Y')." AND
                                                            r.fkidsector = $idSector 
                                                            
                                                        GROUP BY date_part('month',r.creacionrecibopuestoeventual)";

                                            $stmt = $db->prepare($query);
                                            $params = array();
                                            $stmt->execute($params);
                                            $puestoEventual = $stmt->fetchAll();
                                            
                                            if($puestoEventual){

                                                $meta = $em->getRepository('ModeloBundle:Tmeta')->findOneBy(array(
                                                    "fkidsector" => $idSector,
                                                    "mesnumero"  => $mes,
                                                    "metaactivo" => true
                                                ));

                                                /**
                                                 * si ya existe una meta para el sector en el mismo mes, se modifica la existente, 
                                                 * si no se crea una nueva
                                                 */
                                                if(!$meta){
                                                    $meta = new Tmeta();
                                                    $meta->setCreacionmeta($today);
                                                }

                                                $meta->setTotaltarifapuesto($factura[0]['totaltarifapuesto']);
                                                $meta->setTotalsaldodeuda($factura[0]['totalsaldodeuda']);
                                                $meta->setTotalcuotaacuerdo($factura[0]['totalcuotaacuerdo']);
                                                $meta->setTotalsaldodeudaacuerdo($factura[0]['totalsaldodeudaacuerdo']);
                                                $meta->setTotalsaldomultas($factura[0]['totalsaldomultas']);
                                                $meta->setTotalvalorpuestoeventual($puestoEventual[0]['totalvalorpuestoeventual']);
                                                $meta->setMesletras($factura[0]['mesfacturaletras']);
                                                $meta->setMesnumero($mes);
                                                $meta->setMetaactivo(true);
                                                $meta->setModificacionmeta($today);
                                                $meta->setFkidsector($sector);
                                                
                                                $em->persist($meta);

                                                array_push($metas, $meta);
 
                                            }else{
                                                $data = array(
                                                    'status'=> 'error',
                                                    'msg'   => "No hay recibos para el mes ".($mes-1)." en el sector $idSector!!"
                                                );
                                                array_push($errores, $data);
                                            }

                                        }else{
                                            $data = array(
                                                'status'=> 'error',
                                                'msg'   => "No hay facturas generadas para el mes $mes en el sector $idSector!!"
                                            );
                                            array_push($errores, $data);
                                        }                                        
                                    }

                                    if($errores){
                                        $data = array(
                                            'status'   => 'Error',
                                            'msg'      => 'Metas no creadas!!',
                                            'errores'  => $errores
                                        );
                                    }else{
                                        $em->flush();
                                        $data = array(
                                            'status' => 'Exito',
                                            'msg'    => 'Metas creadas!!',
                                            'metas'  => $metas
                                        );

                                        foreach($metas as $meta){

                                            //una vez insertados los datos en la meta se realiza la insercion en auditoria
                                            $datos = array(
                                                'idusuario'             => $identity->sub,
                                                'nombreusuario'         => $identity->name,
                                                'identificacionusuario' => $identity->identificacion,
                                                'accion'                => 'insertar',
                                                "tabla"                 => 'Tmeta',
                                                "valoresrelevantes"     => 'idMeta:'.$meta->getPkidmeta().',mes:'.$meta->getMesletras(),
                                                'idelemento'            => $meta->getPkidmeta(),
                                                'origen'                => 'web'
                                            );

                                            $auditoria = $this->get(Auditoria::class);
                                            $auditoria->auditoria(json_encode($datos));
                                        }
                                    }
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'El mes es nulo!!',
                                    );
                                }   
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'Se debe seleccionar una plaza, zona o sector, para generar las metas!!',
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
                'modulo'        => 'Meta',
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
     * @Route("/query")
     * Funcion para las metas por recaudador y sector 
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryMetaAction(Request $request)
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();                        
                                            
                        //Consulta para traer los datos de la meta, los sectores y el recaudador
                        $query = "SELECT 
                                    tmeta.pkidmeta, 
                                    tmeta.totaltarifapuesto, 
                                    tmeta.totalsaldodeuda, 
                                    tmeta.totalcuotaacuerdo, 
                                    tmeta.totalsaldodeudaacuerdo, 
                                    tmeta.totalsaldomultas,  
                                    tmeta.totalvalorpuestoeventual, 
                                    tmeta.mesletras, 
                                    tmeta.mesnumero,
                                    tmeta.metaactivo,
                                    tusuario.pkidusuario,
                                    tusuario.identificacion,
                                    tusuario.nombreusuario,
                                    tusuario.apellido,
                                    tmeta.fkidsector,
                                    tsector.pkidsector,
                                    tsector.nombresector,
                                    tzona.pkidzona,
                                    tzona.nombrezona,
                                    tplaza.pkidplaza,
                                    tplaza.nombreplaza
                                FROM 
                                    tmeta
                                    JOIN tsector ON tmeta.fkidsector = tsector.pkidsector
                                    JOIN tzona ON tsector.fkidzona = tzona.pkidzona
                                    JOIN tplaza ON tzona.fkidplaza = tplaza.pkidplaza
                                    JOIN tusuario ON tzona.fkidusuario = tusuario.pkidusuario
                                ORDER BY tmeta.mesnumero ASC";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $metas = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($metas as $meta) {
                            $metasList = array(
                                "pkidmeta"                 => $meta['pkidmeta'],
                                "totaltarifapuesto"        => $meta['totaltarifapuesto'],
                                "totalsaldodeuda"          => $meta['totalsaldodeuda'],  
                                "totalcuotaacuerdo"        => $meta['totalcuotaacuerdo'], 
                                "totalsaldodeudaacuerdo"   => $meta['totalsaldodeudaacuerdo'], 
                                "totalvalorpuestoeventual" => $meta['totalvalorpuestoeventual'], 
                                "mesletras"                => $meta['mesletras'],
                                "mesnumero"                => $meta['mesnumero'],
                                "Usuario"                  => array(
                                                                "pkidusuario"    => $meta['pkidusuario'],
                                                                "identificacion" => $meta['identificacion'],
                                                                "nombreusuario"  => $meta['nombreusuario'],
                                                                "apellido"       => $meta['apellido'],
                                                            ),
                                "sector"                   => array(
                                                                "pkidsector"   => $meta['pkidsector'],
                                                                "nombresector" => $meta['nombresector'],
                                                            ),
                                "zona"                     => array(
                                                                "pkidzona"   => $meta['pkidzona'],
                                                                "nombrezona" => $meta['nombrezona'],
                                                            ),
                                "plaza"                    => array(
                                                                "pkidplaza"   => $meta['pkidplaza'],
                                                                "nombreplaza" => $meta['nombreplaza'],
                                                            ),
                                "metaactivo"               => $meta['metaactivo']
                            );
                            array_push($array_all, $metasList);
                        }

                        $cabeceras = array("Meta","Usuario","Sector","Zona","Plaza","Meta Activo/Inactivo");

                        $data = array(
                            'status'    => 'Success',
                            'cabeceras' => $cabeceras,
                            'meta'      => $array_all,
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
                'modulo'        => "Meta",
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