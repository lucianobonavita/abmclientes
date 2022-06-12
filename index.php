<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (file_exists("archivo.txt")) {
    //si el archivo existe, cargo los clientes en la variable aClientes 
    $strJson = file_get_contents("archivo.txt");
    $aClientes = json_decode($strJson, true);
} else {
    //si el archivo no existe es por que no hay clientes
    $aClientes = array();
}

if (isset($_GET["id"])) {
    $id = $_GET["id"];
} else {
    $id = "";
}

if (isset($_GET["do"]) && $_GET["do"] == "eliminar") {
    unset($aClientes[$id]);

    //convertir clientes en json
    $strJson = json_encode($aClientes);

    //almacenar el json en el archivoj
    file_put_contents("archivo.txt", $strJson);

    header("location: index.php");
}

if ($_POST) {
    $dni = $_POST["txtDni"];
    $nombre = $_POST["txtNombre"];
    $telefono = $_POST["txtTelefono"];
    $correo =  $_POST["txtCorreo"];
    $nombreImagen = "";


    if ($_FILES["archivo"]["error"] === UPLOAD_ERR_OK) {
        $nombreAleatorio = date("Ymdhmsi") . rand(1000, 2000); //genera un nombre aleatorio con año, fecha y hora
        $archivo_tmp = $_FILES["archivo"]["tmp_name"];
        $extension = pathinfo($_FILES["archivo"]["name"], PATHINFO_EXTENSION);
        if ($extension == "jpg" || $extension == "png" || $extension == "jpeg") {
            $nombreImagen = "$nombreAleatorio.$extension";
            move_uploaded_file($archivo_tmp, "imagenes/$nombreImagen"); //guarda el archivo fisicamente 
            //files/202104082045371010.pdf
        }
    }

    if ($id >= 0) {
        //si no se subió una imagen y estoy editando conservar en $nombreImagen el nombre +
        // + de la imagen anterior que esta asocicada al cliente que estamos editando.
        if($_FILES["archivo"]["error"] !== UPLOAD_ERR_OK){
            $nombreImagen = $aClientes[$id]["imagen"];

        } else{
            //si viene una imagen nueva y habia una anterior, eliminar la anterior
            if(file_exists("imagenes/" . $aClientes[$id]["imagen"])){
                unlink("imagenes/" . $aClientes[$id]["imagen"]);
            }
        }
  
        //Estoy editando
        $aClientes[$id] = array(
            "dni" => $dni,
            "nombre" => $nombre,
            "telefono" => $telefono,
            "correo" => $correo,
            "imagen" => $nombreImagen,
        );

        
    } else {

        //estoy ingresando un nuevo cliente
        $aClientes[] = array(
            "dni" => $dni,
            "nombre" => $nombre,
            "telefono" => $telefono,
            "correo" => $correo,
            "imagen" => $nombreImagen,
        );
    }


    //convertir el array de clientes en json
    $strJson = json_encode($aClientes);
    //almacenar en un archivo.txt el json
    file_put_contents("archivo.txt", $strJson);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abm clientes</title>
    <link rel="stylesheet" href="css/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="css/fontawesome/css/fontawesome.min.css" />
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
</head>

<body>
    <main class="container">
        <div class="row">
            <div class="col-12 text-center pt-5 pb-5">
                <h1>Registro de clientes</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-6 shadow rounded">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="pb-3">
                        <label for="">Dni: *</label>
                        <input type="text" name="txtDni" id="txtDni" class="form-control" required value="<?php echo isset($aClientes[$id]) ? $aClientes[$id]["dni"] : ""; ?>">
                    </div>

                    <div class="pb-3">
                        <label for="">Nombre: *</label>
                        <input type="text" name="txtNombre" id="txtNombre" class="form-control" required value="<?php echo isset($aClientes[$id]) ? $aClientes[$id]["nombre"] : ""; ?>">
                    </div>

                    <div class="pb-3">
                        <label for="">telefono:</label>
                        <input type="tel" name="txtTelefono" id="txtTelefono" class="form-control" value="<?php echo isset($aClientes[$id]) ? $aClientes[$id]["telefono"] : ""; ?>">
                    </div>

                    <div class="pb-3">
                        <label for="">Correo: *</label>
                        <input type="text" name="txtCorreo" id="txtCorreo" class="form-control" required value="<?php echo isset($aClientes[$id]) ? $aClientes[$id]["correo"] : ""; ?>">
                    </div>

                    <div class="pb-3">
                        <label for="">archivo adjunto</label>
                        <input type="file" name="archivo" id="archivo">
                        <P>Archivos admitidos: .jpg .jpeg .pgn</P>
                    </div>
                    <div class="pb-3">
                        <button type="submit" name="btnEnviar" class="btn btn-primary text-white">Guardar</button>
                        <a href="index.php" class="btn btn-danger my-2">NUEVO</a>

                    </div>

                </form>
            </div>

            <div class="col-6 ps-5">
                <table class="table table-hover shadow rounded">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Dni</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($aClientes as $pos => $cliente) : ?>
                            <tr>
                                <td><img src="imagenes/<?php echo $cliente["imagen"]; ?>" class="img-thumbnail"></td>
                                <td><?php echo $cliente["dni"]; ?> </td>
                                <td><?php echo $cliente["nombre"]; ?> </td>
                                <td><?php echo $cliente["correo"]; ?> </td>
                                <td>
                                    <a href="?id=<?php echo $pos; ?>"><i class="fa-solid fa-pencil"></i></a>
                                    <a href="?id=<?php echo $pos; ?>&do=eliminar"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>

</html>