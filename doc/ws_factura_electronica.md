### Acceso a los métodos de Facturación Electrónica

La especificación de este Web Service se encuentra disponible en [Facturación Electrónica - Manuales para el desarrollador](http://www.afip.gob.ar/fe/documentos/manual_desarrollador_COMPG_v2_10.pdf)

#### Obtener número del último comprobante creado
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);
        
    $punto_venta = 1;
    $tipo_comprobante = 5;

    $result = $factura_electronica->getUltimoComprobante($punto_venta, $tipo_comprobante);
```

#### Crear y asignar CAE a un comprobante

```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);
        
    $data = array(
		'CantReg' 	=> 1,  // Cantidad de comprobantes a registrar
		'PtoVta' 	=> 1,  // Punto de venta
		'CbteTipo' 	=> 6,  // Tipo de comprobante (ver tipos disponibles) 
		'Concepto' 	=> 1,  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
		'DocTipo' 	=> 99, // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
		'DocNro' 	=> 0,  // Número de documento del comprador (0 consumidor final)
		'CbteDesde' 	=> 1,  // Número de comprobante o numero del primer comprobante en caso de ser mas de uno
		'CbteHasta' 	=> 1,  // Número de comprobante o numero del último comprobante en caso de ser mas de uno
		'CbteFch' 	=> intval(date('Ymd')), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
		'ImpTotal' 	=> 121, // Importe total del comprobante
		'ImpTotConc' 	=> 0,   // Importe neto no gravado
		'ImpNeto' 	=> 100, // Importe neto gravado
		'ImpOpEx' 	=> 0,   // Importe exento de IVA
		'ImpIVA' 	=> 21,  //Importe total de IVA
		'ImpTrib' 	=> 0,   //Importe total de tributos
		'MonId' 	=> 'PES', //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos) 
		'MonCotiz' 	=> 1,     // Cotización de la moneda usada (1 para pesos argentinos)  
		'Iva' 		=> array( // (Opcional) Alícuotas asociadas al comprobante
			array(
				'Id' 		=> 5, // Id del tipo de IVA (5 para 21%)(ver tipos disponibles) 
				'BaseImp' 	=> 100, // Base imponible
				'Importe' 	=> 21 // Importe 
			)
		), 
	);

    $result = $factura_electronica->crearComprobante($data);

	$result['CAE']; //CAE asignado el comprobante
	$result['CAEFchVto']; //Fecha de vencimiento del CAE (yyyy-mm-dd)

```

#### Crear y asignar CAE a siguiente comprobante
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);

	$result = $factura_electronica->crearProximoComprobante($data);     

	$result['CAE']; //CAE asignado el comprobante
	$result['CAEFchVto']; //Fecha de vencimiento del CAE (yyyy-mm-dd)
	$result['voucher_number']; //Número asignado al comprobante

```

#### Obtener información de un comprobante
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);

    $punto_venta = 1;
    $tipo_comprobante = 5;
    $num_comprobante = 5;

	$result = $factura_electronica->getComprobanteInfo($num_comprobante, $punto_venta, $tipo_comprobante);     
```

#### Obtener tipos de comprobantes disponibles
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);

	$result = $factura_electronica->getTiposCbte();     
```

#### Obtener tipos de conceptos disponibles
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);

	$result = $factura_electronica->getTiposConcepto();     
```

#### Obtener tipos de documentos disponibles
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);

	$result = $factura_electronica->getTiposDoc();     
```

#### Obtener tipos de alícuotas disponibles
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);

	$result = $factura_electronica->getTiposAlicuotas();     
```

#### Obtener tipos de monedas disponibles
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);

	$result = $factura_electronica->getTiposMonedas();     
```

#### Obtener tipos de opciones disponibles para el comprobante
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);

	$result = $factura_electronica->getTiposOpcional();     
```

#### Obtener tipos de tributos disponibles
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);

	$result = $factura_electronica->getTiposTributos();     
```

#### Transformar formato de fecha que utiliza AFIP (yyyymmdd) a yyyy-mm-dd
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);

	$result = $factura_electronica->formatDate('19970508'); //Nos devuelve 1997-05-08    
```

#### Obtener estado del servidor
```php
<?php
    $factura_electronica = new \patosmack\Afip\WebService\FacturaElectronica($this->afip);
        
    $result = $factura_electronica->getEstadoServicio();
```