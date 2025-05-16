<?php
session_start();

$env = parse_ini_file('.env');

try {
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
        $env['DB_USERNAME'],
        $env['DB_PASSWORD']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['message'] = "Database connection failed: " . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

$formData = [
    'item_name' => '',
    'item_quantity' => '',
    'item_code' => '',
    'item_unit' => 'pcs',
    'item_price' => '',
    'status_barang' => '1'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tb_inventory 
                (nama_barang, jumlah_barang, kode_barang, satuan_barang, harga_beli, status_barang) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['item_name'],
                $_POST['item_quantity'],
                $_POST['item_code'],
                $_POST['item_unit'],
                $_POST['item_price'],
                $_POST['status_barang']
            ]);
            $_SESSION['message'] = 'Barang berhasil ditambahkan!';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Gagal menambahkan barang: ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
            $formData = $_POST; 
        }
    }
    
    elseif (isset($_POST['delete'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM tb_inventory WHERE id_barang = ?");
            $stmt->execute([$_POST['item_id']]);
            $_SESSION['message'] = 'Barang berhasil dihapus!';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Gagal menghapus barang: ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }
    }
    
    elseif (isset($_POST['update'])) {
        try {
            $stmt = $pdo->prepare("CALL update_status_barang()");
            $stmt->execute();
            $_SESSION['message'] = 'Status barang berhasil diperbarui!';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Gagal memperbarui status barang: ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }
    }
    
    elseif (isset($_POST['stock_update'])) {
        try {
            $itemId = $_POST['item_id'];
            $amount = $_POST['amount'];
            $changeType = $_POST['change_type'];
            
            // Get current stock
            $stmt = $pdo->prepare("SELECT jumlah_barang FROM tb_inventory WHERE id_barang = ?");
            $stmt->execute([$itemId]);
            $currentStock = $stmt->fetchColumn();
            
            // Calculate new stock
            $newStock = ($changeType === 'increase') 
                ? $currentStock + $amount 
                : $currentStock - $amount;
            
            if ($newStock < 0) {
                throw new Exception('Stok tidak boleh kurang dari 0');
            }
            
            // Update stock
            $updateStmt = $pdo->prepare("UPDATE tb_inventory SET jumlah_barang = ? WHERE id_barang = ?");
            $updateStmt->execute([$newStock, $itemId]);
            
            $_SESSION['message'] = 'Stok berhasil diperbarui!';
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Gagal memperbarui stok: ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Manajemen Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-7xl">

        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-4 p-4 <?= $_SESSION['message_type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> rounded-md">
                <?= $_SESSION['message'] ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>


        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Aplikasi Manajemen Barang</h1>
            <p class="text-gray-600">Kelola inventori barang dengan mudah</p>
        </header>

        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-700 flex items-center">
                    <i class="fas fa-plus-circle mr-2 text-blue-500"></i> Tambah Barang
                </h2>
                
                <form action="" method="POST">
                    <button type="submit" name="update" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fas fa-sync-alt mr-2"></i> Update Status Barang
                    </button>
                </form>
            </div>

            <form action="" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <?php foreach ([
                        'item_name' => ['label' => 'Nama Barang', 'type' => 'text', 'placeholder' => 'Masukkan nama barang'],
                        'item_quantity' => ['label' => 'Jumlah', 'type' => 'number', 'placeholder' => '0'],
                        'item_code' => ['label' => 'Kode Barang', 'type' => 'text', 'placeholder' => 'Kode unik'],
                    ] as $name => $field): ?>
                        <div>
                            <label for="<?= $name ?>" class="block text-sm font-medium text-gray-700 mb-1"><?= $field['label'] ?></label>
                            <input type="<?= $field['type'] ?>" id="<?= $name ?>" name="<?= $name ?>" 
                                   value="<?= htmlspecialchars($formData[$name]) ?>" 
                                   placeholder="<?= $field['placeholder'] ?>" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Unit Select -->
                    <div>
                        <label for="item_unit" class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                        <select id="item_unit" name="item_unit" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php foreach (['pcs' => 'Pcs', 'box' => 'Box', 'kg' => 'Kg', 'liter' => 'Liter'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $formData['item_unit'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="item_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Beli</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" id="item_price" name="item_price" 
                                   value="<?= htmlspecialchars($formData['item_price']) ?>" 
                                   placeholder="0" required
                                   class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Barang</label>
                        <div class="flex space-x-4">
                            <?php foreach ([1 => 'Tersedia', 0 => 'Tidak Tersedia'] as $value => $label): ?>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="status_barang" value="<?= $value ?>" 
                                           <?= $formData['status_barang'] == $value ? 'checked' : '' ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-gray-700"><?= $label ?></span>
                                </label>
                            <?php endforeach; ?>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $stmt = $pdo->query("SELECT * FROM tb_inventory");
                        $nomor = 1;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $nomor ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['kode_barang']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['jumlah_barang']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['satuan_barang']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $row['status_barang'] == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $row['status_barang'] == 1 ? 'Tersedia' : 'Tidak Tersedia' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button onclick="showEditStockModal(<?= $row['id_barang'] ?>)" 
                                            class="text-blue-600 hover:text-blue-900 flex items-center">
                                        <i class="fas fa-edit mr-1"></i> Edit Stok
                                    </button>
                                    <form action="" method="POST" class="inline">
                                        <input type="hidden" name="item_id" value="<?= $row['id_barang'] ?>">
                                        <button type="submit" name="delete" 
                                                class="text-red-600 hover:text-red-900 flex items-center"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">
                                            <i class="fas fa-trash mr-1"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php 
                        $nomor++;
                        endwhile; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="editStockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Stok Barang</h3>
            <form id="editStockForm" method="POST">
                <input type="hidden" name="stock_update" value="1">
                <input type="hidden" id="modal_item_id" name="item_id" value="">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Jenis Perubahan</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="change_type" value="increase" checked 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-gray-700">Penambahan Stok</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="change_type" value="decrease" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-gray-700">Pemakaian Stok</span>
                        </label>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="modal_amount" class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                    <input type="number" id="modal_amount" name="amount" placeholder="Masukkan jumlah" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideEditStockModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showEditStockModal(itemId) {
            document.getElementById('modal_item_id').value = itemId;
            document.getElementById('editStockModal').classList.remove('hidden');
        }

        function hideEditStockModal() {
            document.getElementById('editStockModal').classList.add('hidden');
        }

        document.getElementById('editStockModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideEditStockModal();
            }
        });
    </script>
</body>
</html>