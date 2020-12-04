### Consulta al padrón de AFIP alcance 4

Servicio de Consulta de Padrón Alcance 4. El servicio de Consulta de Padrón Alcance 4 permite acceder a los datos de un contribuyente registrado en el Padrón de AFIP. Este WS se puede utilizar para acceder a datos de un contribuyente relacionados con su situación tributaria. Ejemplo: impuestos y regimenes en los que esta inscripto.

La documentación de AFIP de este Web Service se encuentra disponible en [Manual para el desarrollador](http://www.afip.gob.ar/ws/ws_sr_padron_a4/manual_ws_sr_padron_a4_v1.1.pdf)

Datos para pruebas en el ambiente de testing [Datos Prueba](http://www.afip.gob.ar/ws/ws_sr_padron_a4/datos-prueba-padron-a4.txt)

#### Obtener datos de un contribuyente
```php
<?php
    $cuit = "20221064233";
        
    $padron_cuatro = new \SIU\Afip\WebService\PadronAlcanceCuatro($this->afip);
        
    $result = $padron_cuatro->getContribuyenteDetalle($cuit);
```

#### Obtener estado del servidor
```php
<?php
    $padron_cuatro = new \SIU\Afip\WebService\PadronAlcanceCuatro($this->afip);
        
    $result = $padron_cuatro->getEstadoServicio();
```