<?php

include_once __DIR__ . '/funcionesDAO.php';

function procesoDelete($EqFco) {
    global $connInte;
    try {
        $sentencia = " delete from eq_fco where "
                . " edificio = :edificio "
                . " and codigo_loc = :codigo_loc "
                . " and codigo_uni = :codigo_uni ";
        $query = $connInte->prepare($sentencia);
        $params = array(":edificio" => $EqFco["edificio"],
            ":codigo_loc" => $EqFco["codigo_loc"],
            ":codigo_uni" => $EqFco["codigo_uni"]);
        $delete = $query->execute($params);
        if ($delete == 0) {
            echo "***ERROR EN DELETE EQ_FCO edificio= " . $EqFco["edificio"]
            . " codigo_loc = " . $EqFco["codigo_loc"]
            . " codigo_uni = " . $EqFco["codigo_uni"]
            . "\n";
            return null;
        }
        echo "DELETE EQ_FCO edificio= " . $EqFco["edificio"]
        . " codigo_loc = " . $EqFco["codigo_loc"]
        . " codigo_uni = " . $EqFco["codigo_uni"]
        . "\n";
        return true;
        
    } catch (PDOException $ex) {
        echo "***PDOERROR EN DELETE EQ_FCO EN BD INTERMEDIA " . $ex->getMessage() . "\n";
        return null;
    }
}

function procesoInsert($EqFco) {
    global $connInte;
    try {
        $sentencia = " insert into eq_fco "
                . " ( edificio, codigo_loc, codigo_uni)  values "
                . " ( :edificio, :codigo_loc, :codigo_uni) ";
        $query = $connInte->prepare($sentencia);
        $params = array(":edificio" => $EqFco["edificio"],
            ":codigo_loc" => $EqFco["codigo_loc"],
            ":codigo_uni" => $EqFco["codigo_uni"]);
        $res = $query->execute($params);
        if ($res == 0) {
            echo "***ERROR EN INSERT EQ_FCO edificio= " . $EqFco["edificio"]
            . " codigo_loc = " . $EqFco["codigo_loc"]
            . " codigo_uni = " . $EqFco["codigo_uni"]
            . "\n";
            return null;
        }
        echo "INSERT EQ_FCO edificio= " . $EqFco["edificio"]
        . " codigo_loc = " . $EqFco["codigo_loc"]
        . " codigo_uni = " . $EqFco["codigo_uni"]
        . "\n";
        return true;
        
    } catch (PDOException $ex) {
        echo "***PDOERROR EN INSERT EQ_FCO EN BD INTERMEDIA " . $ex->getMessage() . "\n";
        return null;
    }
}

echo " ++++++ COMIENZA PROCESO SINCRONIZACIÓN EQ_FCO +++++++++++ \n";
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
$eqmodocupa_id = $argv[2];
$accion = $argv[3];

if ($tipo == 'REAL') {
    echo " ENTORNO = PRODUCCIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(2, 'I'));
} else {
    echo " ENTORNO = VALIDACIÓN \n";
    $connInte = conexionPDO(SelectBaseDatos(1, 'I'));
}

$EqFco = selectEqFcoById($eqmodocupa_id);
if (!$EqFco) {
    exit(1);
}

echo " SINCRONIZACIÓN EQ_FCO : ID=" . $EqFco["id"]
 . " EDIFICIO=" . $EqFco["edificio"]
 . " CODIGO_LOC= " . $EqFco["codigo_loc"]
 . " CODIGO_UNI= " . $EqFco["codigo_uni"]
 . " ACCION =" . $accion
 . "\n";

if ($accion == 'DELETE') {
    if (!procesoDelete($EqFco)) {
        exit(1);
    }
}

if ($accion == 'INSERT') {
    if (!procesoInsert($EqFco)) {
        exit(1);
    }
}

echo " FIN SINCRONIZACIÓN " . "\n";
exit(0);
