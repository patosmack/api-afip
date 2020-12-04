### Consulta al padrón de AFIP alcance 5

Servicio de Consulta de Padrón Alcance 5. El servicio de Consulta de Padrón Alcance 5 permite acceder a los datos de la constancia de un contribuyente registrado en el Padrón de AFIP.

La documentación de AFIP de este Web Service se encuentra disponible en [Manual para el desarrollador](http://www.afip.gob.ar/ws/ws_sr_padron_a5/manual_ws_sr_padron_a5_v1.0.pdf)

#### Obtener datos de un contribuyente
```php
<?php
    $cuit = "20221064233";
        
    $padron_cinco = new \patosmack\Afip\WebService\PadronAlcanceCinco($this->afip);
        
    $result = $padron_cinco->getContribuyenteDetalle($cuit);
```

#### Obtener estado del servidor
```php
<?php
    $padron_cinco = new \patosmack\Afip\WebService\PadronAlcanceCinco($this->afip);
        
    $result = $padron_cinco->getEstadoServicio();
```