<?php 

function query_data($data) {

    $result = array();
    while($row = mysqli_fetch_array($data)) {
        array_push($result, array(
            'id' => $row['id'],
            'nama' => $row['nama'],
            'npm' => $row['npm'],
            'kelamin' => $row['kelamin']
        ));
    }

    return $result;
}


?>