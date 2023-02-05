<?php

libxml_use_internal_errors(true);

if (file_exists('report.xml')) {
    $xml = new SimpleXMLElement(file_get_contents('report.xml'));
} else {
    exit('No se pudo encontrar el fichero: report.xml.');
}

if ($xml === false) {
    echo "Error cargando fichero XML\n";
    foreach(libxml_get_errors() as $error) {
        echo "\t", $error->message;
    }
}

function Color($valor)
{
    switch(true)
    {
        case $valor <= 20:
            $retorno = "bg-danger";
            echo $retorno;
            break;
        case $valor <= 60:
            $retorno = "bg-warning";
            echo $retorno;
            break;
        case $valor <= 100:
            $retorno = "bg-success";
            echo $retorno;
            break;
        default: echo "bg-primary";
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <div class="container">
        <h1>Salud de los Discos Duros</h1><br>
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
        <?php
            foreach ($xml->children() as $child)
            {
                if ($child->Hard_Disk_Summary)
                {
                    ?><div class="col-4 py-3">
                    <?php
                        echo "<b>" . $child->Hard_Disk_Summary->Hard_Disk_Model_ID . "</b>  (" . $child->Hard_Disk_Summary->Total_Size . ")<br>";
                        //echo "Número de Serie: " . $child->Hard_Disk_Summary->Hard_Disk_Serial_Number . "\n";
                        echo "<b>Salud: </b>" . $child->Hard_Disk_Summary->Health . "<br>";
                        $porciento = substr(str_replace(" ", "", $child->Hard_Disk_Summary->Health), 0, -1);
                    ?>
                    <div class="progress">
                        <div 
                            class="progress-bar progress-bar-striped <?php Color($porciento); ?>"
                            style="width:
                                <?php echo $porciento; ?>%" 
                            min-width:20px>
                            <?php echo $porciento; ?> %
                        </div>
                    </div>
                    <?php
                    echo "<b>Temperatura: </b>" . $child->Hard_Disk_Summary->Current_Temperature . "<br>";
                    ?></div>
                    <div class="col-8">
                        <?php
                            echo "<b>Desempeño: </b>"; //. $child->Hard_Disk_Summary->Performance . "<br>";
                        ?>
                                <div class="progress">
                                    <div 
                                        class="progress-bar progress-bar-striped <?php Color($porciento); ?>"
                                        style="width:
                                            <?php echo $porciento; ?>%" 
                                        min-width:20px>
                                        <?php echo $porciento; ?> %
                                    </div>
                                </div>
                        <?php
                            echo "<b>Tiempo de vida estimado: </b>" . $child->Hard_Disk_Summary->Estimated_remaining_lifetime . "<br>";
                            echo "<b>Descripción: </b>" . $child->Hard_Disk_Summary->Description . "<br>";
                            echo "<b>" . $child->Hard_Disk_Summary->Tip . "</b><br>";
                            echo "<b>Vida restante estimada: </b>" . $child->Hard_Disk_Summary->Estimated_remaining_lifetime . "<br>";
                    
                    ?></div><hr><?php
                }
            }
        ?>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
  </body>
</html>
