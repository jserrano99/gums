<?php

include_once __DIR__ . '/funcionesDAO.php';

function procesoDelete($EqAltas) {
    global $connInte;
    try {
        $sentencia = " delete from eq_altas where "
                . " edificio = :edificio "
                . " and codigo_loc = :codigo_loc "
                . " and codigo_uni = :codigo_uni ";
        $query = $connInte->prepare($sentencia);
        $params = array(":edificio" => $EqAltas["edificio"],
            ":codigo_loc" => $EqAltas["codigo_loc"],
            ":codigo_uni" => $EqAltas["codigo_uni"]);
        $delete = $query->execute($params);
        if ($delete == 0) {
            echo "***ERROR EN DELETE EQ_ALTAS edificio= " . $EqAltas["edificio"]
            . " codigo_loc = " . $EqAltas["codigo_loc"]
            . " codigo_uni = " . $EqAltas["codigo_uni"]
            . "\n";
            return null;
        }
        echo "DELETE EQ_ALTAS edificio= " . $EqAltas["edificio"]
        . " codigo_loc = " . $EqAltas["codigo_loc"]
        . " codigo_uni = " . $EqAltas["codigo_uni"]
        . "\n";
        return true;
        
    } catch (PDOException $ex) {
        echo "***PDOERROR EN DELETE EQ_ALTAS EN BD INTERMEDIA " . $ex->getMessage() . "\n";
        return null;
    }
}

function procesoInsert($EqAltas) {
    global $connInte;
    try {
        $sentencia = " insert into eq_altas "
                . " ( edificio, codigo_loc, codigo_uni)  values "
                . " ( :edificio, :codigo_loc, :codigo_uni) ";
        $query = $connInte->prepare($sentencia);
        $params = array(":edificio" => $EqAltas["edificio"],
            ":codigo_loc" => $EqAltas["codigo_loc"],
            ":codigo_uni" => $EqAltas["codigo_uni"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "***ERROR EN INSERT EQ_ALTAS edificio= " . $EqAltas["edificio"]
            . " codigo_loc = " . $EqAltas["codigo_loc"]
            . " codigo_uni = " . $EqAltas["codigo_uni"]
            . "\n";
            return null;
        }
        echo "INSERT EQ_ALTAS edificio= " . $EqAltas["edificio"]
        . " codigo_loc = " . $EqAltas["codigo_loc"]
        . " codigo_uni = " . $EqAltas["codigo_uni"]
        . "\n";
        return true;
        
    } catch (PDOException $ex) {
        echo "***PDOERROR EN INSERT EQ_ALTAS EN BD INTERMEDIA " . $ex->getMessage() . "\n";
        return null;
    }
}

echo " ++++++ COMIENZA PROCESO SINCRONIZACIÓN EQ_ALTAS +++++++++++ \n";
/*
 * Conexión a la base de datos de Control en Mysql 
 */
$connGums = connGums();
if (!$connGums) {
    exit(111);
}

/*
 * recogemos el parametro para ver si estamos en pruebas en validación o en producción
 */
$tipo = $argv[1];
$eqaltas_id = $argv[2];
$accion = $argv[3];

if ($tipo == 'REAL') {
    echo " ENTORNO = PRODUCCIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(2, 'I'));
} else {
    echo " ENTORNO = VALIDACIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(1, 'I'));
}

$EqAltas = selectEqAltasById($eqaltas_id);
if (!$EqAltas) {
    exit(1);
}

echo " SINCRONIZACIÓN : ID=" . $EqAltas["id"]
 . " EDIFICIO=" . $EqAltas["edificio"]
 . " CODIGO_LOC= " . $EqAltas["codigo_loc"]
 . " CODIGO_UNI= " . $EqAltas["codigo_uni"]
 . " ACCION =" . $accion
 . "\n";

if ($accion == 'DELETE') {
    if (!procesoDelete($EqAltas)) {
        exit(1);
    }
}

if ($accion == 'INSERT') {
    if (!procesoInsert($EqAltas)) {
        exit(1);
    }
}

echo " FIN SINCRONIZACIÓN " . "\n";
exit(0);
