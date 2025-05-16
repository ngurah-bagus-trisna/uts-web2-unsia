<?php

session_start();

$env = parse_ini_file('.env');

try {
  $db_name = $env['DB_NAME'];
  $db_host = $env['DB_HOST'];
  $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
  $username = $env['DB_USERNAME'];
  $password = $env['DB_PASSWORD'];

  $pdo = new PDO($dsn, $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  #echo "Database connection successful.";
} catch (PDOException $e) {
  #echo "Database connection failed: " . $e->getMessage();
}

$nama_barang = '';
$jumlah_barang = '';
$kode_barang = '';
$satuan_barang = '';
$harga_beli = '';
$status_barang = '';

if (isset($_POST['submit'])) {
    $nama_barang = $_POST['item_name'];
    $jumlah_barang = $_POST['item_quantity'];
    $kode_barang = $_POST['item_code'];
    $satuan_barang = $_POST['item_unit'];
    $harga_beli = $_POST['item_price'];
    $status_barang = $_POST['status_barang'];

    try {
        $stmt = $pdo->prepare("INSERT INTO tb_inventory (nama_barang, jumlah_barang, kode_barang, satuan_barang, harga_beli, status_barang) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama_barang, $jumlah_barang, $kode_barang, $satuan_barang, $harga_beli, $status_barang]);
        $_SESSION['message'] = 'Barang berhasil ditambahkan!';
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Gagal menambahkan barang: ' . htmlspecialchars($e->getMessage());
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['item_id'])) {
  $item_id = $_POST['item_id'];
  try {
    $stmt = $pdo->prepare("DELETE FROM tb_inventory WHERE id_barang = ?");
    $stmt->execute([$item_id]);
    $_SESSION['message'] = 'Barang berhasil dihapus!';
  } catch (PDOException $e) {
    $_SESSION['message'] = 'Gagal menghapus barang: ' . htmlspecialchars($e->getMessage());
  }
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aplikasi Manajemen Barang</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50">
  <div class="container mx-auto px-4 py-8 max-w-7xl">
    <header class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Aplikasi Manajemen Barang</h1>
      <p class="text-gray-600">Kelola inventori barang dengan mudah</p>
    </header>

    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-xl font-semibold text-gray-700 mb-4 flex items-center">
        <i class="fas fa-plus-circle mr-2 text-blue-500"></i> Tambah Barang
      </h2>
      <form action="" method="POST" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label for="item_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
            <input type="text" id="item_name" name="item_name" placeholder="Masukkan nama barang" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label for="item_quantity" class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
            <input type="number" id="item_quantity" name="item_quantity" placeholder="0" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label for="item_code" class="block text-sm font-medium text-gray-700 mb-1">Kode Barang</label>
            <input type="text" id="item_code" name="item_code" placeholder="Kode unik" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label for="item_unit" class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
            <select id="item_unit" name="item_unit" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="pcs">Pcs</option>
              <option value="box">Box</option>
              <option value="kg">Kg</option>
              <option value="liter">Liter</option>
            </select>
          </div>
          
          <div>
            <label for="item_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Beli</label>
            <div class="relative">
              <span class="absolute left-3 top-2 text-gray-500">Rp</span>
              <input type="number" id="item_price" name="item_price" placeholder="0" required 
                     class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Barang</label>
            <div class="flex space-x-4">
              <label class="inline-flex items-center">
                <input type="radio" name="status_barang" value="1" checked 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-gray-700">Tersedia</span>
              </label>
              <label class="inline-flex items-center">
                <input type="radio" name="status_barang" value="0" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-gray-700">Tidak Tersedia</span>
              </label>
            </div>
          </div>
        </div>
        
        <div class="pt-2">
          <button type="submit" name="submit" 
                  class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <i class="fas fa-save mr-2"></i> Tambah Barang
          </button>
        </div>
      </form>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-700 flex items-center">
          <i class="fas fa-boxes mr-2 text-blue-500"></i> Daftar Barang
        </h2>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Barang</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php
            $stmt = $pdo->query("SELECT * FROM tb_inventory");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              echo "<tr class='hover:bg-gray-50'>";
              echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['id_barang']) . "</td>";
              echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . htmlspecialchars($row['nama_barang']) . "</td>";
              echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['kode_barang']) . "</td>";
              echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['jumlah_barang']) . "</td>";
              echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['satuan_barang']) . "</td>";
              echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>Rp " . number_format(htmlspecialchars($row['harga_beli']), 0, ',', '.') . "</td>";
              echo "<td class='px-6 py-4 whitespace-nowrap text-sm'>";
              if (htmlspecialchars($row['status_barang']) == 1) {
                echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800'>Tersedia</span>";
              } else {
                echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800'>Tidak Tersedia</span>";
              }
              echo "</td>";
              echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium'>";
              echo "<form action='' method='POST' class='inline' onsubmit='return confirmDelete()'>";
              echo "<input type='hidden' name='item_id' value='" . htmlspecialchars($row['id_barang']) . "'>";
              echo "<button type='submit' class='text-red-600 hover:text-red-900 flex items-center'>";
              echo "<i class='fas fa-trash mr-1'></i> Hapus";
              echo "</button>";
              echo "</form>";
              echo "</td>";
              echo "</tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    function confirmDelete() {
      return confirm('Apakah Anda yakin ingin menghapus barang ini?');
    }
  </script>
</body>
</html>