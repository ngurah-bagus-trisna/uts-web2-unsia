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
<body>
  <div class="container mx-auto mt-10">
    <h1 class="text-2xl font-bold mb-4">Aplikasi Manajemen Barang</h1>
    <h2 class="text-xl font-bold mb-4">Tambah Barang</h2>
    <form action="" method="POST" class="mb-4">
      <input type="text" name="item_name" placeholder="Nama Barang" required class="border p-2 mr-2">
      <input type="number" name="item_quantity" placeholder="Jumlah" required class="border p-2 mr-2">
      <input type="text" name="item_code" placeholder="Kode Barang" required class="border p-2 mr-2">
      <select name="item_unit" class="border p-2 mr-2">
        <option value="pcs">Pcs</option>
        <option value="box">Box</option>
        <option value="kg">Kg</option>
        <option value="liter">Liter</option>
      </select>
      <input type="number" name="item_price" placeholder="Harga Beli" required class="border p-2 mr-2">
      <input type="radio" name="status_barang" value="1" checked class="mr-2">Availible
      <input type="radio" name="status_barang" value="0" class="mr-2">Not Available
      <button type="submit" name="submit" class="bg-blue-500 text-white p-2">Tambah Barang</button>
    </form>

    <h2 class="text-xl font-bold mb-4">Daftar Barang</h2>
    <table class="min-w-full bg-white border border-gray-300">
      <thead>
        <tr>
          <th class="border px-4 py-2">ID</th>
          <th class="border px-4 py-2">Nama Barang</th>
          <th class="border px-4 py-2">Kode Barang</th>
          <th class="border px-4 py-2">Jumlah</th>
          <th class="border px-4 py-2">Satuan</th>
          <th class="border px-4 py-2">Harga Beli</th>
          <th class="border px-4 py-2">Status Barang</th>
          <th class="border px-4 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $pdo->query("SELECT * FROM tb_inventory");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          echo "<tr>";
          echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['id_barang']) . "</td>";
          echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['nama_barang']) . "</td>";
          echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['kode_barang']) . "</td>";
          echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['jumlah_barang']) . "</td>";
          echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['satuan_barang']) . "</td>";
          echo "<td class='border px-4 py-2'>Rp. " . htmlspecialchars($row['harga_beli']) . "</td>";
          echo "<td class='border px-4 py-2'>" ;
          if (htmlspecialchars($row['status_barang']) == 1) {
            echo "<span class='text-green-500'>Tersedia</span>";
          } else {
            echo "<span class='text-red-500'>Tidak Tersedia</span>";
          }  "</td>";

          echo "<td class='border px-4 py-2'>";


          echo "<form action='' method='POST' class='inline' onsubmit='return confirmDelete()'>";
          echo "<input type='hidden' name='item_id' value='" . htmlspecialchars($row['id_barang']) . "'>";
          echo "<button type='submit' class='bg-red-500 text-white p-1'>Hapus</button>";
          echo "</form>";
          echo "</td>";
          echo "</tr>";
        }
        ?>

  <script>
    function confirmDelete() {
      return confirm('Apakah Anda yakin ingin menghapus barang ini?');
    }
  </script>
</body>
</html>