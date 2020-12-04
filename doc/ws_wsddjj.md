### Envio de datos a la afip correspondientes a los agentes de las instituciones para el calculo de ganancias

Servicio que envia datos a la Afip. Este objetivo del ws es permitir que los Organismos y los Contribuyentes puedan presentar DDJJ en AFIP en forma automática sin intervención humana.

La documentación de AFIP de este Web Service se encuentra disponible en [Manual para el desarrollador](https://www.afip.gob.ar/ws/wsddjj/WSPresentaciondeDDJJManualparaelDesarrollador.pdf)

#### Obtener datos de un contribuyente
```php
<?php
    $ganancias_envio = new \patosmack\Afip\WebService\FormularioAnualGanancias($this->s__afip);

	$datos_enviar = array(	'fileName' => 'F1357.MD5_ARCHIVO.gz" ,
							'presentacionDataHandler' => file_get_contents($archivo)
						);
    	
	$result = $ganancias_envio->enviarArchivoGanancias($datos_enviar);	
```

#### Obtener estado del servidor
```php
<?php
    $form_afip = new \patosmack\Afip\WebService\FormularioAnualGanancias($this->s__afip);

	$result = $form_afip->getEstadoServicio();
```
