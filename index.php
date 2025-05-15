<?php

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
    <form action="add_item.php" method="POST" class="mb-4">
      <input type="text" name="item_name" placeholder="Nama Barang" required class="border p-2 mr-2"> <br>
      <input type="number" name="item_quantity" placeholder="Jumlah" required class="border p-2 mr-2">
      <input type="text" name="item_code" placeholder="Kode Barang" required class="border p-2 mr-2">
      <input type="text" name="item_unit" placeholder="Satuan Barang" required class="border p-2 mr-2">
      <input type="number" name="item_price" placeholder="Harga Beli" required class="border p-2 mr-2">
      <button type="submit" class="bg-blue-500 text-white p-2">Tambah Barang</button>
    </form>

    <h2 class="text-xl font-bold mb-4">Daftar Barang</h2>
    <table class="min-w-full bg-white border border-gray-300">
      <thead>
        <tr>
          <th class="border px-4 py-2">ID</th>
          <th class="border px-4 py-2">Nama Barang</th>
          <th class="border px-4 py-2">Jumlah</th>
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
          echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['harga_beli']) . "</td>";

          echo "<td class='border px-4 py-2'>";


          echo "<form action='delete_item.php' method='POST' class='inline'>";
          echo "<input type='hidden' name='item_id' value='" . htmlspecialchars($row['id']) . "'>";
          echo "<button type='submit' class='bg-red-500 text-white p-1'>Hapus</button>";
          echo "</form>";
          echo "</td>";
          echo "</tr>";
        }
        ?>
</body>
</html>