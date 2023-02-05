<?php

libxml_use_internal_errors(true);

$report_file = "reporte.xml";

if (file_exists($report_file)) {
    $xml = new SimpleXMLElement(file_get_contents($report_file));
} else {
    exit('No se pudo encontrar el fichero de reportes');
}

if ($xml === false) {
    echo "Error cargando fichero XML\n";
    foreach(libxml_get_errors() as $error) {
        echo "\t", $error->message;
        exit();
    }
}

function Color($valor)
{
    switch(true)
    {
        case $valor <= 20:
            $retorno = "danger";
            echo $retorno;
            break;
        case $valor <= 60:
            $retorno = "warning";
            echo $retorno;
            break;
        case $valor <= 100:
            $retorno = "success";
            echo $retorno;
            break;
        default: echo "primary";
    }
}

function ColorEspacio($valor)
{
    switch(true)
    {
        case $valor <= 40:
            $retorno = "success";
            return $retorno;
        case $valor > 40 and $valor < 80:
            $retorno = "warning";
            return $retorno;
        case $valor >= 80:
            $retorno = "danger";
            return $retorno;
        default: return "primary";
    }
}
function formatBytes($bytes, $precision = 2) {
    if ($bytes > pow(1024,3)) return round($bytes / pow(1024,3), $precision)."GB";
    else if ($bytes > pow(1024,2)) return round($bytes / pow(1024,2), $precision)."MB";
    else if ($bytes > 1024) return round($bytes / 1024, $precision)."KB";
    else return ($bytes)."B";
}

function particionDisco($xml, $disco, $valor = "")
{
    $mostrar = "";
    foreach ($xml->Partition_Information->children() as $child)
    {
        $disco1 = substr($child["Disk"],0,-1);
        if ($disco == $disco1)
        {
            $espacio = 100 - substr(str_replace(" ", "", $child["Free_Space_Percent"]), 0, -1);
            $mostrar .= $child["Drive"] . "<br>";
        }
    }
    return $mostrar;
}

function porcientoDisco($xml, $disco)
{
    $mostrar = "";
    foreach ($xml->Partition_Information->children() as $child)
    {
        $disco1 = substr($child["Disk"],0,-1);
        if ($disco == $disco1)
        {
            $espacio = 100 - substr(str_replace(" ", "", $child["Free_Space_Percent"]), 0, -1);
            $color = ColorEspacio($espacio);
            $mostrar .= '<div class="progress mb-2" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">';
            $mostrar .= '<div class="progress-bar bg-' . $color . '" style="width: '. $espacio . '%; min-width:25px" >' . $espacio . '%</div>';
            $mostrar .= '</div>';
        }
    }
    return $mostrar;
}

function libreDisco($xml, $disco)
{

    $mostrar = "";
    foreach ($xml->Partition_Information->children() as $child)
    {
        $disco1 = substr($child["Disk"],0,-1);
        if ($disco == $disco1)
        {
            $valor = $child["Free_Space"];
            if(strchr($valor, "M"))
            {
                if(strchr($valor, ","))
                {
                    $coma = strpos($valor, ",") + 2;
                    $mostrar .= substr($valor, 0, $coma) . " Gb<br>";
                } else {
                    $mostrar .= $valor . "<br>";    
                }
            } else {
                $mostrar .= $valor . "<br>";
            }
        }
    }
    return $mostrar;
}
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="300">
    <meta author="michelvf@nauta.cu">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hard Disk Sentinel</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link type=»image/x-icon» href=»favicon.ico» rel=»shortcut icon» />
    <style>
        main > .container {
            padding: 60px 15px 0;
        }
    </style>
  </head>
  <body class="d-flex flex-column h-100 bg-secondary bg-gradient bg-opacity-25">
    <div class="container">
        <br>
        <div class="card">
            <div class="card-header bg-success p-2 text-dark bg-opacity-25 text-center display-6">
            Salud de los Discos Duros
            </div>
            <div class="card-body">
                <?php
                    echo "<b>Software:</b> " . $xml->General_Information->Application_Information->Installed_version . "<br>";
                    echo "<b>Fecha:</b> " . $xml->General_Information->Application_Information->Current_Date_And_Time . "<br>";
                    echo "<b>Tiempo de creación del reporte:</b> " . $xml->General_Information->Application_Information->Report_Creation_Time . "<br>";
                    echo "<b>PC:</b> " . $xml->General_Information->Computer_Information->Computer_Name . "<br>";
                    echo "<b>Versión del Sistema Operativo:</b> " . $xml->General_Information->System_Information->OS_Version . "<br>";
                    echo "<b>Encendido hace:</b> " . $xml->General_Information->System_Information->Uptime . "<br>";
                ?>
            <br><br>
            <div class="row">
                <div class="col">
                    <table class="table table-striped">
                        <thead class="bg-primary bg-gradient bg-opacity-25">
                            <tr>
                                <th class="border-end">Disco(s)</th>
                                <th>Propiedades</th>
                                <th>Partición</th>
                                <th>Espacio ocupado</th>
                                <th>Libre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($xml->children() as $child)
                                {
                                    if ($child->Hard_Disk_Summary)
                                    { 
                                    ?>
                            <tr>
                                <td class="border-end col-3">
                                    <?php
                                        $disco = $child->Hard_Disk_Summary->Hard_Disk_Device;
                                        $porciento = substr(str_replace(" ", "", $child->Hard_Disk_Summary->Health), 0, -1);
                                        echo "<b>" . $child->Hard_Disk_Summary->Hard_Disk_Model_ID . "</b>  (" . $child->Hard_Disk_Summary->Total_Size . ")";
                                    ?>
                                    <br>
                                    <div class="row">
                                        <div class="col-6 text-start">Salud:</div>
                                        <div class="col-6 text-end text-<?php Color($porciento); ?>"><?php echo $porciento; ?>%</div>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar progress-bar-striped bg-<?php echo Color($porciento); ?>" role="progressbar" style="width: <?php echo $porciento; ?>%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>   
                                    </div><br>
                                    <?php
                                        echo "<b>Temperatura: </b>" . $child->Hard_Disk_Summary->Current_Temperature . "<br>";
                                        $desempeno = substr(str_replace(" ", "", $child->Hard_Disk_Summary->Performance), 0, -1);
                                    ?>
                                </td>
                                <td class="border-end">
                                    <div class="row">
                                    <div class="col-6 text-start">Desempeño:</div>
                                    <div class="col-6 text-end text-<?php Color($desempeno); ?>"><?php echo $desempeno; ?>%</div>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar progress-bar-striped bg-<?php Color($desempeno); ?>" role="progressbar" style="width: <?php echo $desempeno; ?>%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div><br>
                                    <div class="col">
                                        <?php
                                            echo "<strong>Tiempo de vida estimado: </strong>" . $child->Hard_Disk_Summary->Estimated_remaining_lifetime . "<br>";
                                            echo "<b>Descripción: </b>" . $child->Hard_Disk_Summary->Description . "<br>";
                                            echo "<b>" . $child->Hard_Disk_Summary->Tip . "</b><br>";
                                            echo "<b>Vida restante estimada: </b>" . $child->Hard_Disk_Summary->Estimated_remaining_lifetime . "<br>";
                                            
                                        ?>
                                    </div>
                                </td>
                                <!-- Particion del disco duro -->
                                <td class="border-end col-1">
                                    <?php echo particionDisco($xml, $disco); ?>
                                </td>
                                <td class="border-end col-2">
                                    <?php echo porcientoDisco($xml, $disco); ?>
                                </td>
                                <td class="col-1 text-end">
                                    <?php echo libreDisco($xml, $disco); ?>
                                </td>
                            </tr>
                            <?php
                                    }
                                }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr  class="bg-primary bg-gradient bg-opacity-25">
                                <th class="border-end">Disco(s)</th>
                                <th>Propiedades</th>
                                <th>Partición</th>
                                <th>Espacio ocupado</th>
                                <th>Libre</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div></div>
    <footer class="footer mt-auto py-3">
        <div class="container text-center">
            <span class="text-muted">Creado por michelvf@nauta.cu</span>
        </div>
    </footer>
    <!-- <script src="js/bootstrap.bundle.min.js"></script> -->
  </body>
</html>
