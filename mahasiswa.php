<?php 
require_once('connection.php');
require_once('functions.php');

// buat variabel query

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
if(empty($_GET)) {
    // Cek token pada setiap permintaan
    $headers = getallheaders();
    $apiToken = isset($headers['Auth']) ? trim(str_replace('Bearer', '', $headers['Auth'])) : '';

    if ($apiToken === 'fira') {
        $data = "SELECT * FROM mahasiswa";
        $query = mysqli_query($conn, $data);
        
        $result = query_data($query);
        
        
        http_response_code(200);
        // merubah menjadi json
        echo json_encode (
            array('result' => $result)
        );
    } else {
        // Token tidak valid, beri respon error
        http_response_code(401);
        echo json_encode(array('message' => 'Unauthorized'));
    }
    // 
} else if($_GET['id']) {

    // ambil data dari header
    $headers = getallheaders();
    $apiToken = isset($headers['Auth']) ? trim(str_replace('Bearer', '', $headers['Auth'])) : '';

    if($apiToken == 'fira') {
        $data = "SELECT * FROM mahasiswa where id = " . $_GET['id'];
        $query = mysqli_query($conn, $data);
        
        $result = query_data($query);
    
        if($result != []) {
            // merubah menjadi json
            http_response_code(200);
            echo json_encode (
                array('result' => $result)
            );
    
        } else {
            http_response_code(404);        
            echo json_encode(array('message' => 'Data tidak di temukan.'));
        }
        
    } else {
    
        // Token tidak valid, beri respon error
        http_response_code(401);
        echo json_encode(array('message' => 'Unauthorized'));
        
    }

}
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ambil data dari header
    $headers = getallheaders();
    $apiToken = isset($headers['Auth']) ? trim(str_replace('Bearer', '', $headers['Auth'])) : '';
 
    if($apiToken == 'fira') {
        $input = json_decode(file_get_contents("php://input"), true);
    
        if($input['nama'] == null || $input['npm'] == null || $input['kelamin'] == null) {
            echo json_encode(array('message' => 'Masukkan semua data.'));
        } else {
            
            // Validate and sanitize input
            $nama = mysqli_real_escape_string($conn, $input['nama']);
            $npm = mysqli_real_escape_string($conn, $input['npm']);
            $kelamin = mysqli_real_escape_string($conn, $input['kelamin']);
        
            // Insert new data
            $insertQuery = "INSERT INTO mahasiswa (nama, npm, kelamin) VALUES ('$nama', '$npm', '$kelamin')";
            $insertResult = mysqli_query($conn, $insertQuery);
        
            if ($insertResult) {
                http_response_code(200);
                echo json_encode(array('message' => 'Data inserted successfully.'));
            } else {
                http_response_code(400);
                echo json_encode(array('message' => 'Failed to insert data.'));
            }
        }
    } else {
        http_response_code(401);
        echo json_encode(array('message' => 'Unauthorized'));
    }
}  else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    // ambil data dari header
    $headers = getallheaders();
    $apiToken = isset($headers['Auth']) ? trim(str_replace('Bearer', '', $headers['Auth'])) : '';
 
    if($apiToken == 'fira') {

        $input = json_decode(file_get_contents("php://input"), true);
    
        // Pastikan id tidak kosong dan valid
        if (isset($input['id']) && is_numeric($input['id'])) {
            $id = mysqli_real_escape_string($conn, $input['id']);
    
            // Cek apakah ID ada di database sebelum menghapus
            $checkQuery = "SELECT * FROM mahasiswa WHERE id = $id";
            $checkResult = mysqli_query($conn, $checkQuery);
    
            if (mysqli_num_rows($checkResult) > 0) {
                // Lakukan penghapusan
                $deleteQuery = "DELETE FROM mahasiswa WHERE id = $id";
                $deleteResult = mysqli_query($conn, $deleteQuery);
    
                if ($deleteResult) {
                    http_response_code(200);
                    echo json_encode(array('message' => 'Data deleted successfully.'));
                } else {
                    echo json_encode(array('message' => 'Failed to delete data.'));
                }
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 404, 'message' => 'Data dengan ID tersebut tidak ditemukan.'));
            }
        } else {
            echo json_encode(array('message' => 'ID tidak valid.'));
        }
    } else {
        http_response_code(401);
        echo json_encode(array('message' => 'Unauthorized'));
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    $header = getallheaders();
    
    $apiToken = (isset($header['Auth'])) ? trim(str_replace('Bearer', '', $header['Auth'])) : '';

    if($apiToken == 'fira') {
        $input = json_decode(file_get_contents("php://input"), true);
    
        // Pastikan id tidak kosong dan valid
        if (isset($input['id']) && is_numeric($input['id'])) {
            $id = mysqli_real_escape_string($conn, $input['id']);
    
            // Cek apakah ID ada di database sebelum mengupdate
            $checkQuery = "SELECT * FROM mahasiswa WHERE id = $id";
            $checkResult = mysqli_query($conn, $checkQuery);
    
            if (mysqli_num_rows($checkResult) > 0) {
                // Membangun kueri UPDATE berdasarkan data yang diterima
                $updateFields = array();
                if (isset($input['nama'])) {
                    $nama = mysqli_real_escape_string($conn, $input['nama']);
                    $updateFields[] = "nama = '$nama'";
                }
                if (isset($input['npm'])) {
                    $npm = mysqli_real_escape_string($conn, $input['npm']);
                    $updateFields[] = "npm = '$npm'";
                }
                if (isset($input['kelamin'])) {
                    $kelamin = mysqli_real_escape_string($conn, $input['kelamin']);
                    $updateFields[] = "kelamin = '$kelamin'";
                }
    
                // Pastikan ada setidaknya satu kolom yang akan diupdate
                if (!empty($updateFields)) {
                    $updateQuery = "UPDATE mahasiswa SET " . implode(', ', $updateFields) . " WHERE id = $id";
                    $updateResult = mysqli_query($conn, $updateQuery);
    
                    if ($updateResult) {
                        http_response_code(200);
                        echo json_encode(array('message' => 'Data updated successfully.'));
                    } else {
                        
                        http_response_code(400);
                        echo json_encode(array('message' => 'Failed to update data.'));
                    }
                } else {
                    echo json_encode(array('message' => 'Tidak ada data yang akan diupdate.'));
                }
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 404, 'message' => 'Data dengan ID tersebut tidak ditemukan.'));
            }
        } else {
            http_response_code(404);
            echo json_encode(array('message' => 'ID tidak valid.'));
        }
        
    } else {
        http_response_code(401);
        echo json_encode(array('message' => 'Unauthorized'));
    }
}
?>
