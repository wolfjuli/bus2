<?php

error_reporting(0);

function generateGet($table, $key) {
    $ret = "select * from `$table`".($key?" WHERE id=$key":'');

    if(count($_GET) <= 0)
        return $ret . " order by id";

    $ret .= (strpos($ret, "WHERE") > 0) ? " AND (" : " WHERE (";

    foreach($_GET as $key => $value ) {
        $ret .= "`$key` like '%$value%' OR ";
    }

    return substr($ret, 0, count($ret) - 4) . ") order by id";
}

function generateArray($result) {
    $ret = '[';
    for ($i=0;$i<mysqli_num_rows($result);$i++) {
        $obj = mysqli_fetch_object($result);
        $ret .= ($i>0 ? ',' : '') .  json_encode($obj);
    }
    return $ret . ']';
}

function generateObject($result) {
    $ret = '{';
    for ($i=0;$i<mysqli_num_rows($result);$i++) {
        $obj = mysqli_fetch_object($result);
        $ret .= ($i>0 ? ',' : '')  . '"' . $obj->id . '": ' . json_encode($obj);
    }
    return $ret . '}';
}


// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);

// connect to the mysql database
$link = mysqli_connect('localhost', 'bus', '2435parts4324', 'bus');
mysqli_set_charset($link,'utf8');

// retrieve the table and key from the path
$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
if($table == "array") {
    $type = "array";
    $table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
} else {
    $type = "object";
}
$key = array_shift($request)+0;

// escape the columns and values from the input object
$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
$values = array_map(function ($value) use ($link) {
  if ($value===null) return null;
  return mysqli_real_escape_string($link,(string)$value);
},array_values($input));

// build the SET part of the SQL command
$set = '';
$insert = '';
for ($i=0;$i<count($columns);$i++) {
  $set.=($i>0?',':'').'`'.$columns[$i].'`=';
  $set.=($values[$i]===null?'NULL':"'".$values[$i]."'");
  $insert .= ($i>0?',':'');
  $insert .= ($values[$i]===null?'NULL':(
              is_numeric($values[$i])?$values[$i]: "'".$values[$i]."'")
          );
}


// create SQL based on HTTP method
switch ($method) {
  case 'GET':
    $sql = generateGet($table, $key ); break;
  case 'PUT':
    $sql = "update `$table` set $set where id=$key"; break;
  case 'POST':
    $sql = "insert into `$table`(" . implode(', ', $columns) . ") values (". $insert . ")"; break;
  case 'DELETE':
    $sql = "delete from `$table` where id=$key"; break;
}



// excecute SQL statement
$result = mysqli_query($link,$sql);

// die if SQL statement failed
if (!$result) {
    http_response_code(404);
    print_r($sql);
    print_r(mysqli_error($link));
}

// print results, insert id or affected row count
if ($method == 'GET') {
  if($type == "array")
      echo generateArray($result);
  else
      echo generateObject($result);

} elseif ($method == 'POST') {
  echo mysqli_insert_id($link);
} else {
  echo mysqli_affected_rows($link);
}

// close mysql connection
mysqli_close($link);