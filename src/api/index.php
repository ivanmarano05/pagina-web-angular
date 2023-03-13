<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Authorization, X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header('Access-Control-Allow-Methods: POST, GET, PATCH, DELETE');
header("Allow: GET, POST, PATCH, DELETE");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {    
   return 0;    
}  

date_default_timezone_set('America/Argentina/Buenos_Aires');

define ('JWT_ALG', 'HS256');
define ('JWT_KEY', 'DayR7RxvEM4T4efkoEZBSV4E5E47DArq8vxiB3O_zeL0yjMpogyFIV0pTqJv6llMkCOJK-ZW0rY3lVDjVqrmtdaQZHepj-D4L43GB7mzywkDtr7K-LpjvfKdRRGEqIcvYAPBjCVXOSKLa6tiiuj4KecC1fYPTwAuEjbkhVSEO57Q-X4mZ862gojPl4Jl6Ao6-pe2A0XnzvBwK7S34UmDh7Xabv-Tjb4_j80Te09uv4ppA_gwW611MlQnwCxX-3nWfeWafx6hq8bDm4ZuFy60mkwhGPqgJ-arFV3A4zja4SYfTdfGQjYFIhTcR3ZJCnHREBd1VY1M_y3KfTVcEIZW1A');
define ('JWT_EXP', 3000); // segundos

//require_once '../config/db.php';
//require_once '../config/jwt.php';

define("DBBASE", "miequipo");
define("DBUSER", "miequipo");
define("DBPASS", "miequipo");
define("DBHOST", "localhost");
 
if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = array();
        $copy_server = array(
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        );

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }

        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }

}

spl_autoload_register(function ($nombre_clase) {
    include __DIR__.'/'.str_replace('\\', '/', $nombre_clase) . '.php';
});

use \Firebase\JWT\JWT;

/***************************** RUTEO ********************************/

$metodo = strtolower($_SERVER['REQUEST_METHOD']);
$comandos = explode('/', strtolower($_GET['comando']));
$funcionNombre = $metodo . ucfirst($comandos[0]);
$idUsuario = 0;

$parametros = array_slice($comandos, 1);
if(count($parametros) > 0 && $metodo == 'get')
	$funcionNombre = $funcionNombre.'ConParametros';


if(function_exists($funcionNombre))
	call_user_func_array ($funcionNombre, $parametros);
else
	header(' ', true, 400);


/***************************** SALIDA ********************************/


function outputJson($data, $codigo = 200)
{
    header('', true, $codigo);
    header('Content-type: application/json');
    print json_encode($data);
    die;
}

function outputError($codigo = 500, $mensaje = "")
{
    switch ($codigo) {
        case 400:
            header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad request", true, 400);
            break;
        case 401:
            header($_SERVER["SERVER_PROTOCOL"] . " 401 Unauthorized", true, 401);
            break;
        case 403:
            header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden", true, 403);
            break;
        case 404:
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
            break;
        default:
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error", true, 500);
            break;
    }
    print json_encode($mensaje);
    die;
}

/*function outputError($headerStatus, $mensaje=""){
	header(' ', true, $headerStatus);
	header('Content-Type: application/json');
	print json_encode($mensaje);
	die;
}*/

/***************************** BBDD ********************************/

function conectarBD()
{
    $link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBBASE);
    if ($link === false) {
        outputError(500, "Falló la conexión: " . mysqli_connect_error());
    }
    mysqli_set_charset($link, 'utf8');
    return $link;
}

/*function autenticar($usuario, $clave) {
	if($usuario=='pepe@pepe.com' && $clave=='123') {
		return [
			'nombre' => 'Pepe',
			'id'	 => 15,
		];
	}
	if($usuario=='coco@coco.com' && $clave=='123') {
		return [
			'nombre' => 'Coco',
			'id'	 => 12,
		];
	}
	return false;
}*/

/*function requiereAutorizacion() {
	try {
		$headers = getallheaders();
		if (!isset($headers['Authorization'])) {
			throw new Exception("Token requerido", 1);
		}
		list($jwt) = sscanf($headers['Authorization'], 'Bearer %s');
		$decoded = JWT::decode($jwt, JWT_KEY, [JWT_ALG]);
	} catch(Exception $e) {
		outputError(401);
	}
	return $decoded;
}*/

function limpiarTokensExpirados () {
    $link = conectarBD();
    $resultado = mysqli_query($link, "SELECT token FROM tokens");
    while ($fila=mysqli_fetch_assoc($resultado)) {
        $jwt = $fila['token'];
        try {
            JWT::decode($jwt, JWT_KEY, [JWT_ALG]);
        } catch(Exception $e) {
            $jwtSql = mysqli_real_escape_string($link, $jwt);
            mysqli_query($link, "DELETE FROM tokens WHERE token = '$jwtSql'");
        }
    }
    mysqli_close($link);
}

function getEquipo() {
    $payload = requireLogin();
    $id = $payload->uid;
    settype($id, 'integer');
    $link = conectarBD();

    $resultado = mysqli_query($link,
    "SELECT equipo.id, equipo.nombre, pelota.id AS pelota_id, pelota.imagen AS pelotaImagen FROM equipo
        INNER JOIN pelota ON equipo.pelota_id = pelota.id
    WHERE equipo.usuario_id = $id");

	if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
        die;
    }

    $ret = mysqli_fetch_assoc($resultado);
    settype($ret['id'], 'integer');
    settype($ret['pelota_id'], 'integer');
    $ret['pelotaImagen'] = base64_encode($ret['pelotaImagen']);

    mysqli_free_result($resultado);
    $idEquipo = $ret['id'];
    $resultado = mysqli_query($link,
    "SELECT jugador_id AS id FROM equipo_jugador
    WHERE equipo_id = $idEquipo");
    
	if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }
    $ret['jugadores'] = [];
    while($ret2 = mysqli_fetch_assoc($resultado)) {
        $ret['jugadores'][] = $ret2['id'] + 0;
    }

    mysqli_close($link);
    outputJson($ret);
}

function getEquipoConParametros($idEquipo) {
    $payload = requireLogin();
    $link = conectarBD();

    $resultado = mysqli_query($link,
    "SELECT equipo.id, equipo.nombre, pelota.nombre AS pelota, pelota.imagen AS pelotaImagen FROM equipo
        INNER JOIN pelota ON equipo.pelota_id = pelota.id
    WHERE equipo.id = $idEquipo");
    
	if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }

    $ret = mysqli_fetch_assoc($resultado);
    settype($ret['id'], 'integer');
    $ret['pelotaImagen'] = base64_encode($ret['pelotaImagen']);

    settype($ret['id'], 'integer');
    $ret['pelotaImagen'] = base64_encode($ret['pelotaImagen']);

    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);
}

function getEquipos() {
    $payload = requireLogin();
    $link = conectarBD();
    $resultado = mysqli_query($link, "SELECT equipo.id, equipo.nombre, usuario.nombre AS nombre_usuario FROM equipo INNER JOIN usuario ON equipo.usuario_id = usuario.id;");
	if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }

    $ret = [];
    
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ret[] = [
            'id'       => $fila['id'],
            'nombre'   => $fila['nombre'],
            'usuario'  => $fila['nombre_usuario']
        ];
    }

    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);
}

function getMensajes() {
    $payload = requireLogin();
    $link = conectarBD();
    $resultado = mysqli_query($link, "SELECT m.id, m.mensaje, m.imagen, usuario.nombre AS nombre_usuario FROM mensaje AS m INNER JOIN usuario ON m.usuario_id = usuario.id ORDER BY m.id DESC");
	if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }

    $ret = [];
    /*$imagenBlob;

    $pizza  = "porción1 porción2 porción3 porción4 porción5 porción6";
    $porciones = explode(" ", $pizza);
    echo $porciones[0]; // porción1
    echo $porciones[1]; // porción2*/
    
    while ($fila = mysqli_fetch_assoc($resultado)) {

        /*$imagenBlob = base64_encode($fila['imagen']);
        echo ("Imagen BLOB: $imagenBlob");
        $separador = explode(",", $imagenBlob);
        echo $separador[1];
        $imagenReal = $separador[1];*/

        $ret[] = [
            'id'       => $fila['id'],
            'mensaje'  => $fila['mensaje'],
            //'imagen'   => $imagenReal,
            'imagen'   => base64_encode($fila['imagen']),
            'usuario'  => $fila['nombre_usuario']
        ];
    }

    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);
}

function postEquipo()
{
    $payload = requireLogin();
    $idUsuario = $payload->uid;
    $link = conectarBD();
    $datos = json_decode(file_get_contents('php://input'), true);

    if (json_last_error()) {
        outputError(400, "El formato de datos es incorrecto");
    }
    if (($datos['nombre']) === ""){
        outputError(400, "No has escrito el nombre");
    }
    if (!(isset($datos['nombre']))){
        outputError(400, "No has escrito el nombre");
    }
    if (!(isset($datos['pelota_id']))) {
        outputError(400, "No has seleccionado una pelota");
    }
    if (!(isset($datos['jugadores']))) {
        outputError(400, "No has seleccionado jugadores");
    }

    $jugadoresRecibidos = count($datos['jugadores']);
    $jugadoresEsperados = 11;

    if ($jugadoresRecibidos !== $jugadoresEsperados){
        /*var_dump(count($datos['jugadores']));
        var_dump($jugadoresRecibidos);
        var_dump($jugadoresEsperados);*/
        outputError(400, "Debes seleccionar 11 jugadores");
    }

    $nombre = $datos['nombre'];
    $pelota = $datos['pelota_id'] + 0;
    
    $sql = "INSERT INTO equipo (nombre, pelota_id, usuario_id) VALUES ('$nombre', $pelota, $idUsuario)";
    
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
    }
    
    $idEquipo = $link->insert_id;

	foreach($datos['jugadores'] as $idJugador) {
        $sql = "INSERT INTO equipo_jugador (equipo_id, jugador_id) VALUES ($idEquipo,$idJugador)";
        $result = mysqli_query($link, $sql);
	    if ($result===false) {
			outputError(500);
		}
	}
	//}

    mysqli_close($link);
    outputJson(['id' => $idEquipo]);
}

function postMensaje()
{
    $payload = requireLogin();
    $idUsuario = $payload->uid;
    $link = conectarBD();
    $datos = json_decode(file_get_contents('php://input'), true);

    if (json_last_error()) {
        outputError(400, "El formato de datos es incorrecto");
    }
    if (($datos['mensaje']) === ""){
        outputError(400, "No has escrito el nombre");
    }
    if (!(isset($datos['mensaje']))){
        outputError(400, "No has escrito el nombre");
    }

    $mensaje = $datos['mensaje'];
    $imagen = $datos['imagen'];

    if($imagen == null) {
        $imagen = "";

        $sql = "INSERT INTO mensaje (mensaje, imagen, usuario_id) VALUES ('$mensaje', '$imagen', $idUsuario)";
    
        $resultado = mysqli_query($link, $sql);
        if ($resultado === false) {
            outputError(500, "Falló la consulta: " . mysqli_error($link));
        }

    } else {

        $stmt = mysqli_prepare($link, "INSERT INTO mensaje (mensaje, imagen, usuario_id) VALUES (?, ?, ?)");

        $stmt->bind_param('sbi', $mensaje, $imagen, $idUsuario);

        //$base64_data = base64_decode(base64_encode(file_get_contents('3413983627135443.jpg')));
        //$stmt->send_long_data(1, $base64_data);

        $imagen = explode(",", $imagen);

        $stmt->send_long_data(1, base64_decode($imagen[1]));

        mysqli_stmt_execute($stmt);
        printf("%d Row inserted.\n", mysqli_stmt_affected_rows($stmt));
        mysqli_stmt_close($stmt);
    }

    mysqli_close($link);
    outputJson(['mensaje' => $mensaje, 'imagen' => $imagen, 'usuario' => $idUsuario]);
}

/*function postPelotas()
{
    $payload = requireLogin();
    $link = conectarBD();
    $datos = json_decode(file_get_contents('php://input'), true);

    $imagen = $datos['imagen'];

    $stmt = mysqli_prepare($link, "UPDATE pelota AS p SET p.imagen = ? WHERE p.id = 2;");

    $stmt->bind_param('b', $imagen);

    $imagen = explode(",", $imagen);

    $stmt->send_long_data(0, base64_decode($imagen[1]));

    mysqli_stmt_execute($stmt);
    printf("%d Row inserted.\n", mysqli_stmt_affected_rows($stmt));
    mysqli_stmt_close($stmt);
    

    mysqli_close($link);
    outputJson(['imagen' => $imagen]);
}*/

/*function postMensaje()
{
    $payload = requireLogin();
    $idUsuario = $payload->uid;
    $link = conectarBD();
    $datos = json_decode(file_get_contents('php://input'), true);

    if (json_last_error()) {
        outputError(400, "El formato de datos es incorrecto");
    }
    if (!(isset($datos['mensaje']))) {
        outputError(400, "El mensaje debe estar completo.");
    }

    $mensaje = $datos['mensaje'];
    $imagen  = $datos['imagen'];

    if($imagen == null) {
        $imagen = "";
    }
    
    $sql = "INSERT INTO mensaje (mensaje, imagen, usuario_id) VALUES ('$mensaje', '$imagen', $idUsuario)";
    
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
    }

    mysqli_close($link);
    outputJson(['mensaje' => $mensaje]);
}*/

function patchEquipo($idEquipo) {
    $payload = requireLogin();
    settype($idEquipo, 'integer');
	$link = conectarBD();
	$datos = json_decode(file_get_contents('php://input'), true);

    if (json_last_error()) {
        outputError(400, "El formato de datos es incorrecto");
    }
    if (($datos['nombre']) === ""){
        outputError(400, "No has escrito el nombre");
    }
    if (!(isset($datos['pelota_id']))) {
        outputError(400, "No has seleccionado una pelota");
    }
    if (!(isset($datos['jugadores']))) {
        outputError(400, "No has seleccionado jugadores");
    }

    $jugadoresRecibidos = count($datos['jugadores']);
    $jugadoresEsperados = 11;

    if ($jugadoresRecibidos !== $jugadoresEsperados){
        /*var_dump(count($datos['jugadores']));
        var_dump($jugadoresRecibidos);
        var_dump($jugadoresEsperados);*/
        outputError(400, "Debes seleccionar 11 jugadores");
    }

	$nombre = $datos['nombre'];
    $pelota = $datos['pelota_id'] + 0;
    
	$sql = "UPDATE equipo SET nombre = '$nombre', pelota_id = $pelota WHERE id = $idEquipo";

    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
    }

	$sql = "DELETE FROM equipo_jugador WHERE equipo_id = $idEquipo";

    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
    }

	foreach($datos['jugadores'] as $idJugador) {
		$sql = "INSERT INTO equipo_jugador (equipo_id, jugador_id) VALUES ($idEquipo,$idJugador)";
        $resultado = mysqli_query($link, $sql);
        if ($resultado===false) {
			outputError(500);
		}
	}
    mysqli_close($link);
	outputJson(['id' => $idEquipo]);
}

function getJugadores() {
    $payload = requireLogin();
    $link = conectarBD();
    $resultado = mysqli_query($link, "SELECT * FROM jugador");
	if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }

    $ret = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ret[] = [
            'id'       => $fila['id'] + 0,
            'nombre'   => $fila['nombre'],
            'posicion' => $fila['posicion'],
            'pais   '  => $fila['pais']
        ];
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);
}

function getJugadoresConParametros($idEquipo) {
    $payload = requireLogin();
    //$id = $payload->uid;
    $link = conectarBD();
    $resultado = mysqli_query($link, "SELECT * FROM jugador INNER JOIN equipo_jugador ON jugador.id = equipo_jugador.jugador_id WHERE equipo_jugador.equipo_id = $idEquipo");
	if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }

    $ret = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ret[] = [
            'id'       => $fila['id'],
            'nombre'   => $fila['nombre'],
            'posicion' => $fila['posicion'],
            'pais'     => $fila['pais']
        ];
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);
}

function getPelotas() {
    $payload = requireLogin();
    $link = conectarBD();
    $resultado = mysqli_query($link, "SELECT id, nombre, imagen FROM pelota");
	if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }

    $ret = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ret[] = [
            'id'       => $fila['id'] + 0,
            'nombre'   => $fila['nombre'],
            'imagen'   => base64_encode($fila['imagen'])
        ];
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);
}

function getUsuario() {
    $payload = requireLogin();
    outputJson(['rol' => $payload->rol]);
}

function getUsuarios() {
    $payload = requireLogin();
    $link = conectarBD();
    $resultado = mysqli_query($link, "SELECT id, nombre, rol FROM usuario");
	if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }

    $ret = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ret[] = [
            'id'       => $fila['id'],
            'nombre'   => $fila['nombre'],
            'rol'      => $fila['rol']
        ];
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);
}

function deleteUsuario($idUsuario)
{
    $payload = requireLogin();
    $idUsuario+=0;
    $link = conectarBD();
    $sql = "SELECT id FROM usuario WHERE id=$idUsuario";
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }
    mysqli_free_result($resultado);
    $sql = "DELETE FROM usuario WHERE id=$idUsuario";
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
    }
    mysqli_close($link);
    outputJson([]);
}

function deleteMensaje($idMensaje)
{
    $payload = requireLogin();
    $idMensaje+=0;
    $link = conectarBD();
    $sql = "SELECT id FROM mensaje WHERE id=$idMensaje";
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }
    mysqli_free_result($resultado);
    $sql = "DELETE FROM mensaje WHERE id=$idMensaje";
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500, "Falló la consulta: " . mysqli_error($link));
    }
    mysqli_close($link);
    outputJson([]);
}

function getInicio() {
	$payload = requireLogin();
	outputJson(['data' => 'Inicio']);
}

function getCrearEquipo() {
	$payload = requireLogin();
	outputJson(['data' => 'Crear equipo']);
}

function getMiEquipo() {
	$payload = requireLogin();
	outputJson(['data' => 'Mi equipo']);
}

function getVerEquipos() {
	$payload = requireLogin();
	outputJson(['data' => 'Ver equipos']);
}

function getPerfil() {
	$payload = requireLogin();
	outputJson(['id' => $payload->uid, 'nombre' => $payload->nombre]);
}

function requireLogin () {
    $authHeader = getallheaders();
    try
    {
        list($jwt) = @sscanf($authHeader['Authorization'], 'Bearer %s');
        $datos = JWT::decode($jwt, JWT_KEY, [JWT_ALG]);
        $link = conectarBD();
        $jwtSql = mysqli_real_escape_string($link, $jwt);
        $resultado = mysqli_query($link, $sql = "SELECT 1 FROM tokens WHERE token = '$jwtSql'");
        if (!$resultado) {
            outputError(500, mysqli_error($link));
        } elseif (mysqli_num_rows($resultado)!=1) {
            outputError(401);
        }
        return $datos;
        mysqli_close($link);
    } catch(Exception $e) {
        outputError(401);
    }
}

function postLogin() {
    limpiarTokensExpirados(); // borra de la BBDD todos los tokens inválidos (expirados).
    $loginData = json_decode(file_get_contents("php://input"), true);
    $link = conectarBD();

    $email = mysqli_real_escape_string($link, $loginData['email']);
    $clave = mysqli_real_escape_string($link, $loginData['clave']);

    $sql = "SELECT id, nombre, rol FROM usuario WHERE email='$email' AND clave='$clave'";
    $resultado = mysqli_query($link, $sql);
    if($resultado && mysqli_num_rows($resultado)==1) {
        $logged = mysqli_fetch_assoc($resultado);
        $data = [
            'uid'       => $logged['id'],
            'nombre'    => $logged['nombre'],
            'rol'       => $logged['rol'],
            'exp'       => time() + JWT_EXP,
        ];
        $jwt = JWT::encode($data, JWT_KEY, JWT_ALG);
        $jwtSql = mysqli_real_escape_string($link, $jwt);
        mysqli_query($link, "DELETE FROM tokens WHERE token = '$jwtSql'");
        if (mysqli_query($link, "INSERT INTO tokens (token) VALUES ('$jwtSql')")) {
            outputJson(['jwt' => $jwt]);
        } else {
            outputError(500, mysqli_error($link));
        }
    }
    outputError(401);
}

/*function postLogin() {
	$loginData = json_decode(file_get_contents("php://input"), true);
	$logged = autenticar($loginData['email'], $loginData['clave']);

	if($logged===false) {
		outputError(401, "Las credenciales de acceso son incorrectas");
	}
	$payload = [
		'uid'		=> $logged['id'],
		'nombre'	=> $logged['nombre'],
		'exp'		=> time() + JWT_EXP,
	];
	$jwt = JWT::encode($payload, JWT_KEY, JWT_ALG);
	outputJson(['jwt'=>$jwt]);
}*/

function postLogout() {
    requireLogin();
    $link = conectarBD();
    $authHeader = getallheaders();
    list($jwt) = @sscanf( $authHeader['Authorization'], 'Bearer %s');
    if (!mysqli_query($link, "DELETE FROM tokens WHERE token = '$jwt'")) {
        outputError(403);
    }
    mysqli_close($link);
    outputJson([]);
}

function postRefresh() {
	$payload = requireLogin();
	$payload->exp = time() + JWT_EXP;
	$jwt = JWT::encode($payload, JWT_KEY);
	outputJson(['jwt'=>$jwt]);
}
