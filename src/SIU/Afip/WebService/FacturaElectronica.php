<?php
/**
 *  Webservices de Factura Electrónica (wsfe1)
 *
 * @link http://www.afip.gob.ar/fe/documentos/manual_desarrollador_COMPG_v2_10.pdf WS Especificación
 *
 **/

namespace SIU\Afip\WebService;

use SIU\Afip\WebService\AfipWebService;

class FacturaElectronica extends AfipWebService
{
    public function __construct($afip)
    {
        parent::__construct($afip);

        $this->setConfig();
    }

    public function setConfig()
    {
        $this->setSoapVersion(SOAP_1_2);
        $this->setWSDL('wsfe-production.wsdl');
        $this->setUrl('https://servicios1.afip.gov.ar/wsfev1/service.asmx');
        $this->setWSDLTest('wsfe.wsdl');
        $this->setUrlTest('https://wswhomo.afip.gov.ar/wsfev1/service.asmx');
    }

    /**
     * Obtiene el último número de comprobante
     *
     * Obtiene de los servidores Afip la cantidad del último comprobante creado para
     * un determinado punto de venta y tipo de comprobante {@see WS Especificación
     * item 4.15}
     *
     * @param int $sales_point 	Punto de venta para solicitar el último comprobante
     * @param int $type 		Tipo de voucher para solicitar el último comprobante
     *
     * @return int
     **/
    public function getUltimoComprobante($sales_point, $type)
    {
        $req = array(
            'PtoVta' 	=> $sales_point,
            'CbteTipo' 	=> $type
            );

        return $this->ejecutar('FECompUltimoAutorizado', $req)->CbteNro;
    }

    /**
     * Crear un comprobante de AFIP
     *
     * Envía a los servidores de AFIP solicitud para crear un comprobante y 
     * asignarles un CAE {@see WS Especificación item 4.1}
     *
     * @param array $data Parámetros del comprobante {@see WS Especificación
     * 	item 4.1.3}
     * @param bool $return_response si es TRUE devuelve respuesta completa de AFIP
     *
     * @return array si $return_response esta seteado en FALSE retorna
     * 	[CAE => CAE asignado al comprobante, CAEFchVto => Fecha de caducidad
     * 	para CAE (yyyy-mm-dd)] de lo contrario retorna respuesta completa de
     * 	AFIP {@see WS Especificación item 4.1.3}
    **/
    public function crearComprobante($data, $return_response = false)
    {
        $req = array(
            'FeCAEReq' => array(
                'FeCabReq' => array(
                    'CantReg' 	=> $data['CbteHasta']-$data['CbteDesde']+1,
                    'PtoVta' 	=> $data['PtoVta'],
                    'CbteTipo' 	=> $data['CbteTipo']
                    ),
                'FeDetReq' => array(
                    'FECAEDetRequest' => &$data
                )
            )
        );

        unset($data['CantReg']);
        unset($data['PtoVta']);
        unset($data['CbteTipo']);

        if (isset($data['Tributos'])) {
            $data['Tributos'] = array('Tributo' => $data['Tributos']);
        }

        if (isset($data['Iva'])) {
            $data['Iva'] = array('AlicIva' => $data['Iva']);
        }

        if (isset($data['Opcionales'])) {
            $data['Opcionales'] = array('Opcional' => $data['Opcionales']);
        }

        $results = $this->ejecutar('FECAESolicitar', $req);

        if ($return_response === true) {
            return $results;
        } else {
            return array(
                'CAE' 		=> $results->FeDetResp->FECAEDetResponse->CAE,
                'CAEFchVto' => $this->formatDate($results->FeDetResp->FECAEDetResponse->CAEFchVto),
            );
        }
    }

    /**
     * Crear próximo comprobante de AFIP
     *
     * Este método combina getUltimoComprobante y crearComprobante para crear el próximo comprobante
     *
     * @param array $data El mismo $data que en crearComprobante excepto que
     * 	no necesita los atributos CbteDesde y CbteHasta
     *
     * @return array [CAE => CAE asignado al comprobante, CAEFchVto => Fecha de caducidad
     * 	para CAE (yyyy-mm-dd), voucher_number => Numero asignado para el comprobante]
    **/
    public function crearProximoComprobante($data)
    {
        $last_voucher = $this->getUltimoComprobante($data['PtoVta'], $data['CbteTipo']);
        
        $voucher_number = $last_voucher+1;

        $data['CbteDesde'] = $voucher_number;
        $data['CbteHasta'] = $voucher_number;

        $res 					= $this->crearComprobante($data);
        $res['voucher_number'] 	= $voucher_number;

        return $res;
    }

    /**
     * Obtiene la información completa del comprobante
     *
     * Solicita a los servidores de AFIP la información completa del comprobante {@see WS
     * Especificación item 4.19}
     *
     * @param int $number 		Número de comprobante para obtener información
     * @param int $sales_point 	Punto de venta del comprobante para obtener información
     * @param int $type 		Tipo de comprobante para obtener información	
     *
     * @return array|null regresa el array con la información completa del comprobante
     * 	{@see WS Especificación item 4.19} o null si no existe
    **/
    public function getComprobanteInfo($number, $sales_point, $type)
    {
        $req = array(
            'FeCompConsReq' => array(
                'CbteNro' 	=> $number,
                'PtoVta' 	=> $sales_point,
                'CbteTipo' 	=> $type
            )
        );

        try {
            $result = $this->ejecutar('FECompConsultar', $req);
        } catch (\Exception $e) {
            if ($e->getCode() == 602) {
                return null;
            } else {
                throw $e;
            }
        }

        return $result->ResultGet;
    }

    /**
     * Obtiene de los servidores AFIP los tipos de comprobantes disponibles {@see WS
     * Especificación item 4.4}
     *
     * @return array Todos los tipos de comprobantes disponibles
    **/
    public function getTiposCbte()
    {
        return $this->ejecutar('FEParamGetTiposCbte')->ResultGet->CbteTipo;
    }

    /**
     * Obtiene de los servidores de AFIP los conceptos de comprobantes disponibles {@see WS
     * Especificación item 4.5}
     *
     * @return array Todos los conceptos de cupones disponibles
    **/
    public function getTiposConcepto()
    {
        return $this->ejecutar('FEParamGetTiposConcepto')->ResultGet->ConceptoTipo;
    }

    /**
     * Obtiene de los servidores AFIP los tipos de documentos disponibles {@see WS
     * Especificación item 4.6}
     *
     * @return array Todos los tipos de documentos disponibles
    **/
    public function getTiposDoc()
    {
        return $this->ejecutar('FEParamGetTiposDoc')->ResultGet->DocTipo;
    }

    /**
     * Obtiene de los servidores de AFIP las alícuotas disponibles {@see WS
     * Especificación item 4.7}
     *
     * @return array Todas las alícuotas disponibles
    **/
    public function getTiposAlicuotas()
    {
        return $this->ejecutar('FEParamGetTiposIva')->ResultGet->IvaTipo;
    }

    /**
     * Obtiene de los servidores de AFIP las monedas disponibles {@see WS
     * Especificación item 4.8}
     *
     * @return array Todos los tipos de monedas
    **/
    public function getTiposMonedas()
    {
        return $this->ejecutar('FEParamGetTiposMonedas')->ResultGet->Moneda;
    }

    /**
     * 
     * Obtiene de los servidores de AFIP los datos opcionales disponibles de comprobantes
     * {@see WS Especificación item 4.9}
     *
     * @return array Todos los datos opcionales de comprobantes disponibles
    **/
    public function getTiposOpcional()
    {
        return $this->ejecutar('FEParamGetTiposOpcional')->ResultGet->OpcionalTipo;
    }

    /**
     * Obtiene de los servidores de AFIP los tipos de impuestos disponibles {@see WS
     * Especificación item 4.10}
     *
     * @return array Todos los impuestos disponibles
    **/
    public function getTiposTributos()
    {
        return $this->ejecutar('FEParamGetTiposTributos')->ResultGet->TributoTipo;
    }

    /**
     * Verifica el estado y la disponibilidad de los elementos principales del servicio {@see WS
     * Especificación item 4.14}
     *
     * @return object { AppServer => Web Service status,
     * DbServer => Database status, AuthServer => Autentication
     * server status}
    **/
    public function getEstadoServicio()
    {
        return $this->ejecutar('FEDummy');
    }

    /**
     * Cambia la fecha del formato utilizado por la AFIP (yyyymmdd) a yyyy-mm-dd
     *
     * @param string|int fecha para formatear
     *
     * @return string fecha en formato yyyy-mm-dd
    **/
    public function formatDate($date)
    {
        return date_format(\DateTime::CreateFromFormat('Ymd', $date.''), 'Y-m-d');
    }

    /**
     * Envía solicitud a servidores AFIP
     *
     * @param string 	$operation 	Operación SOAP para hacer
     * @param array 	$params 	Parámetros para enviar
     *
     * @return mixed Resultados de la operación
     **/
    public function ejecutar($operation, $params = array())
    {
        $params = array_replace($this->getWSInitialRequest($operation), $params);

        $results = parent::ejecutar($operation, $params);

        $this->_CheckErrors($operation, $results);

        return $results->{$operation.'Result'};
    }

    /**
     * Hacer parámetros de solicitud por defecto para la mayoría de las operaciones
     *
     * @param string $operation Operación SOAP para hacer
     *
     * @return array Parámetros de solicitud
     **/
    private function getWSInitialRequest($operation)
    {
        if ($operation == 'FEDummy') {
            return array();
        }

        $afip = $this->getAfip();
        
        $ta = $afip->getServiceTA('wsfe');

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
     * @param string 	$operation 	Operación SOAP para verificar
     * @param mixed 	$results 	Respuesta de AFIP
     *
     * @throws Exception si existe un error en respuesta
     *
     * @return void
     **/
    private function _CheckErrors($operation, $results)
    {
        $res = $results->{$operation.'Result'};

        if ($operation == 'FECAESolicitar') {
            if (isset($res->FeDetResp->FECAEDetResponse->Observaciones) && $res->FeDetResp->FECAEDetResponse->Resultado != 'A') {
				$res->Errors = new \StdClass();
                $res->Errors->Err = $res->FeDetResp->FECAEDetResponse->Observaciones->Obs;
            }
        }

        if (isset($res->Errors)) {
            $err = is_array($res->Errors->Err) ? $res->Errors->Err[0] : $res->Errors->Err;
            throw new \Exception('('.$err->Code.') '.$err->Msg, $err->Code);
        }
    }
}
