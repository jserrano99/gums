<?php

require_once __DIR__ . '/../../vendor/autoload.php';

function conexionPDO($datosConexion) {
    global $ERROR, $sdterr;
    $conexion = 0;
    $host = $datosConexion['maquina'];
    $service = $datosConexion['puerto'];
    $database = $datosConexion['esquema'];
    $server = $datosConexion['servidor'];
    $protocol = "onsoctcp";
    $user = $datosConexion['usuario'];
    $pass = $datosConexion['password'];

    $cadena = "informix:host=" . $host
            . "; service=" . $service
            . "; database=" . $database
            . "; server=" . $server
            . "; protocol=" . $protocol;
    try {
        $conexion = new \PDO($cadena, $user, $pass);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //$conexion->setAttribute(PDO);
        echo 'CONEXIÓN GENERADA CORRECTAMENTE: ' . $cadena . "\n";
    } catch (PDOException $e) {
        echo "**PDOERROR EN CONEXION: " . $cadena . " MENSAJE ERROR: " . $e->getMessage() . " \n";
        return null;
    }
    return $conexion;
}

function connGums() {
    $filename = __DIR__ . '/../config/parameters.yml';
    $parametros = \Symfony\Component\Yaml\Yaml::parseFile($filename);
    $host_name = $parametros["parameters"]["database_host"];
    $database = $parametros["parameters"]["database_name"];
    $user_name = $parametros["parameters"]["database_user"];
    $password = $parametros["parameters"]["database_password"];

    try {
        $cadena = "mysql:host=" . $host_name
                . "; dbname=" . $database;

        $conn = new PDO($cadena, $user_name, $password);
        $conn->setAttribute(PDO::ATTR_PERSISTENT, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo " conexión a = " . $cadena . "\n";
    } catch (PDOException $ex) {
        echo "**PDOERROR EN LA CONEXIÓN A GUMS " . $ex->getMessage() . " \n";
        return null;
    }
    return $conn;
}

function selectBaseDatosAreas($tipo) {
    global $connGums;
    try {
        $sql = "select t1.id "
                . " ,t1.alias "
                . " ,t1.maquina"
                . " ,t1.puerto"
                . " ,t1.servidor"
                . " ,t1.esquema"
                . " ,t1.usuario"
                . " ,t1.password "
                . " ,t2.codigo as edificio "
                . " from gums_base_datos  as t1 "
                . " inner join gums_edificio as t2 on t2.id = t1.edificio_id "
                . " where tipo_bd_id = :tipo and activa = 'S' and areas = 'S'";
        $query = $connGums->prepare($sql);
        $params = [":tipo" => $tipo];
        $query->execute($params);
        $resultSet = $query->fetchALL(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $error = $ex->getMessage();
        echo $error . " \n";
        return null;
    }
    return $resultSet;
}

function selectBaseDatos($tipo, $areas) {
    global $connGums;
    try {
        $sql = "select t1.id, alias, maquina, puerto, servidor, esquema, usuario, password, t2.codigo as edificio"
                . " from gums_base_datos as t1  "
                . " inner join gums_edificio as t2 on t1.edificio_id = t2.id  "
                . " where tipo_bd_id = :tipo and activa = 'S' and areas = :areas ";

        $query = $connGums->prepare($sql);
        $params = [":tipo" => $tipo,
            ":areas" => $areas];
        $res = $query->execute($params);
        $resultSet = $query->fetch(PDO::FETCH_ASSOC);
        if (count($resultSet) == 0) {
            return null;
        } else {
            return $resultSet;
        }
    } catch (PDOException $ex) {
        $error = $ex->getMessage();
        echo $error . " \n";
        return null;
    }
}

function SelectBaseDatosEdificio($tipo, $edificio) {
    global $connGums;
    try {
        $sql = "select t1.id, t1.alias, t1.maquina, t1.puerto, t1.servidor, t1.esquema, t1.usuario, t1. password, t2.codigo as edificio"
                . " from gums_base_datos  as t1 "
                . "inner join gums_edificio as t2  on t2.id = t1.edificio_id"
                . " where t1.tipo_bd_id = :tipo and t1.activa = 'S' and "
                . "t2.codigo = :edificio ";

        $query = $connGums->prepare($sql);
        $params = [":tipo" => $tipo,
            ":edificio" => $edificio];
        $query->execute($params);
        $resultSet = $query->fetch(PDO::FETCH_ASSOC);
        if (count($resultSet) == 0) {
            return null;
        } else {
            return $resultSet;
        }
    } catch (PDOException $ex) {
        $error = $ex->getMessage();
        echo $error . " \n";
        return null;
    }
}

function selectEdificio($edificio) {
    global $connGums;
    try {
        $sql = "select * from gums_edificio "
                . " where codigo = :edificio ";
        $query = $connGums->prepare($sql);
        $params = array(":edificio" => $edificio);
        $query->execute($params);
        $resultSet = $query->fetch(PDO::FETCH_ASSOC);
        if (count($resultSet) == 0) {
            return null;
        } else {
            return $resultSet;
        }
    } catch (PDOException $ex) {
        $error = $ex->getMessage();
        echo $error . " \n";
        return null;
    }
}

function selectAusenciaByCodigo($codigo) {
    global $connGums;
    try {
        $sentencia = " select * from gums_ausencias where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetchALL(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_AUSENCIAS CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectEqModOcupaById($id) {
    global $connGums;
    try {
        $sentencia = " select t1.*, t2.codigo as codigo_uni, t3.codigo as edificio "
                . " from gums_eq_modocupa as t1 "
                . " inner join gums_modocupa as t2 on t1.modocupa_id = t2.id "
                . " inner join gums_edificio as t3 on t1.edificio_id = t3.id "
                . " where t1.id = :id";
        $query = $connGums->prepare($sentencia);
        $params = array(":id" => $id);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT GUMS_EQ_MODOCUPA: " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectModOcupaById($id) {
    global $connGums;
    try {
        $sentencia = " select t1.* from gums_modocupa as t1 "
                . " where t1.id = :id";
        $query = $connGums->prepare($sentencia);
        $params = array(":id" => $id);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT GUMS_EQ_MODOCUPA: " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectMoviPatById($id) {
    global $connGums;
    try {
        $sentencia = " select t1.* from gums_movipat as t1 "
                . " where t1.id = :id";
        $query = $connGums->prepare($sentencia);
        $params = array(":id" => $id);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT GUMS_MOVIPAT: " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectModoPagoById($id) {
    global $connGums;
    try {
        $sentencia = " select t1.* from gums_modopago as t1 "
                . " where t1.id = :id";
        $query = $connGums->prepare($sentencia);
        $params = array(":id" => $id);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT GUMS_MODODPAGO: " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectAltasById($id) {
    global $connGums;
    try {
        $sentencia = " select t1.*, t2.codigo as modocupa, t3.codigo as modopago, t4.codigo as movipat from gums_altas as t1 "
                . " inner join gums_modocupa as t2 on t2.id = t1.modocupa_id "
                . " inner join gums_modopago as t3 on t3.id = t1.modopago_id"
                . " inner join gums_movipat as t4 on t4.id = t1.movipat_id"
                . " where t1.id = :id";
        $query = $connGums->prepare($sentencia);
        $params = array(":id" => $id);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT GUMS_MODODPAGO: " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectEqAltasById($id) {
    global $connGums;
    try {
        $sentencia = " select t1.*, t2.codigo as codigo_uni, t3.codigo as edificio from gums_eq_altas as t1 "
                . " inner join gums_altas as t2 on t2.id = t1.altas_id "
                . " inner join gums_edificio as t3 on t3.id = t1.edificio_id"
                . " where t1.id = :id";
        $query = $connGums->prepare($sentencia);
        $params = array(":id" => $id);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT GUMS_EQ_ALTAS: " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectFcoById($id) {
    global $connGums;
    try {
        $sentencia = " select t1.* from gums_fco as t1 "
                . " where t1.id = :id";
        $query = $connGums->prepare($sentencia);
        $params = array(":id" => $id);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT GUMS_FCO: " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectTipoIltById($id) {
    global $connGums;
    try {
        $sentencia = " select t1.* from gums_tipo_ilt as t1 "
                . " where t1.id = :id";
        $query = $connGums->prepare($sentencia);
        $params = array(":id" => $id);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "**PDOERROR EN SELECT GUMS_TIPO_ILT: " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectIdModOcupa($codigo) {
    global $connGums;
    try {
        $sentencia = " select id from gums_modocupa where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res["id"];
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_MODOCUPA CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectIdOcupacion($codigo) {
    global $connGums;
    try {
        $sentencia = " select id from gums_ocupacion where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res["id"];
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT gums_ocupacion CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectIdMoviPat($codigo) {
    global $connGums;
    try {
        $sentencia = " select id from gums_movipat where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res["id"];
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_MOVIPAT CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectIdTipoIlt($codigo) {
    global $connGums;
    try {
        $sentencia = " select id from gums_tipo_ilt where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res["id"];
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_TIPO_ILT CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectIdEpiAcc($codigo) {
    global $connGums;
    try {
        $sentencia = " select id from gums_epiacc where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res["id"];
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_EPIACC CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectIdFco($codigo) {
    global $connGums;
    try {
        $sentencia = " select id from gums_fco where codigo = :codigo";
        $query = $connGums->prepare($sentencia);
        $params = array(":codigo" => $codigo);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res["id"];
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_FCO CODIGO= " . $codigo . " " . $ex->getMessage() . "\n";
        return null;
    }
}

function selectAusenciaById($id) {
    global $connGums;
    try {
        $sentencia = " select t1.*, t2.codigo as ocupacion, t3.codigo as ocupacion_new, t4.codigo as modocupa"
                . " , t5.codigo as patronal, t6.codigo as tipo_ilt, t7.codigo as epiacc, t8.codigo as fco from gums_ausencias as t1 "
                . " left join gums_ocupacion as t2 on t2.id = t1.ocupacion_id "
                . " left join gums_ocupacion as t3 on t3.id = t1.ocupacion_new_id "
                . " left join gums_modocupa as t4 on t4.id = t1.modocupa_id "
                . " left join gums_movipat as t5 on t5.id = t1.movipat_id "
                . " left join gums_tipo_ilt as t6 on t6.id = t1.tipo_ilt_id "
                . " left join gums_epiacc as t7 on t7.id = t1.epiacc_id"
                . " left join gums_fco as t8 on t8.id= t1.fco_id"
                . "  where t1.id = :id";
        $query = $connGums->prepare($sentencia);
        $params = array(":id" => $id);
        $query->execute($params);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $ex) {
        echo "** ERROR EN SELECT GUMS_AUSENCIA id= " . $id . " " . $ex->getMessage() . "\n";
        return null;
    }
}
