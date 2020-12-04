<?php
/**
 *  Webservices de envio de datos para la generacion del formulario anual de ganancias
 *
 * @link https://www.afip.gob.ar/ws/wsddjj/WSPresentaciondeDDJJManualparaelDesarrollador.pdf WS EspecificaciÃ³n
 *
 **/

namespace SIU\Afip\WebService;

use SIU\Afip\WebService\AfipWebService;

class FormularioAnualGanancias extends AfipWebService
{
    public function __construct($afip)
    {
        parent::__construct($afip);

        $this->setConfig();
    }

    public function setConfig()
    {
        $this->setSoapVersion(SOAP_1_1);
        $this->setWSDL('uploadPresentacionServiceParent_produccion.wsdl');
        $this->setUrl('https://aws.afip.gov.ar/setiws/webservices/uploadPresentacionService?wsdl');
        $this->setWSDLTest('uploadPresentacionServiceParent.wsdl');
        $this->setUrlTest('https://awshomo.afip.gov.ar/setiws/webservices/uploadPresentacionService?wsdl');
    }

   
    /**
     * Verifica el estado y la disponibilidad de los elementos principales del servicio 
     *
     * @return object { AppServer => Web Service status,
     * DbServer => Database status, AuthServer => Autentication
     * server status}
     **/
    public function getEstadoServicio()
    {
    	return $this->ejecutar('dummy');
    }
    
    
    /**
     * Envia solicitud a servidores AFIP
     *
     * @param string 	$operation 	Operacion SOAP para hacer
     * @param array 	$params 	Parametros para enviar
     *
     * @return mixed Resultados de la operacion
     **/
    public function ejecutar($operation, $params = array())
    {
        $params = array_replace($this->getWSInitialRequest($operation), $params);

        $results = parent::ejecutar($operation, $params);
        
        $this->_CheckErrors($operation, $results);

        return $results->return;
    }

    /**
     * Hacer parametros de solicitud por defecto para la mayori­a de las operaciones
     *
     * @param string $operation Operacion SOAP para hacer
     *
     * @return array Parametros de solicitud
     **/
    private function getWSInitialRequest($operation)
    {
        if ($operation == 'dummy') {
            return array();
        }

        $afip = $this->getAfip();
        
        $ta = $afip->getServiceTA('presentacionprocessor');

        return array(
            'Auth' => array(
                'Token' => $ta->getToken(),
                'Sign' 	=> $ta->getSign(),
                'Cuit' 	=> $afip->getCuit()
                )
        );
    }

    /**
     * Compruebe si se produce un error en la solicitud del servicio web
     *
     * @param string 	$operation 	Operacion SOAP para verificar
     * @param mixed 	$results 	Respuesta de AFIP
     *
     * @throws Exception si existe un error en respuesta
     *
     * @return void
     **/
    private function _CheckErrors($operation, $results)
    {

        $res =  $results->return;

    	if (isset($res->Errors)) {
            $err = is_array($res->Errors->Err) ? $res->Errors->Err[0] : $res->Errors->Err;
            throw new \Exception('('.$err->Code.') '.$err->Msg, $err->Code);
        }
    }
    
    
    /**
     * Envio de datos del TXT de para ian incorporacion a los registros de afip
     *
     * Envi­a a los servidores de AFIP los datos de los agentes para la generacion 
     * del formulario anual de ganancias
     *
     * @param array $data Parametros del envio 
     * 
     * @param bool $return_response si es TRUE devuelve respuesta completa de AFIP
     *
     **/    
    public function enviarArchivoGanancias($datos){
    	
    	$afip = $this->getAfip();
    	
    	$ta = $afip->getServiceTA('presentacionprocessor');

    	
    	$params = array(
    			'token'             		=> $ta->getToken(),
    			'sign'			            => $ta->getSign(),
    			'representadoCuit'			=> $afip->getCuit(),
    			'presentacion'				=> $datos
    	);
    	
    	try {
    		$response =  $this->ejecutar('upload', $params);
    		
    		return array('error' => false, 'mensaje' => $response);
    		    		
    	} catch (\Exception $e) {
    		if (strpos($e->getMessage(), 'No existe') !== false) {
    			return array('error' => true, 'mensaje' => $e);
    		} else {
    			return array('error' => true, 'mensaje' => $e);
    		}
    	}
    	
    }
}
