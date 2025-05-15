CREATE DATABASE IF NOT EXISTS `db_inventory`;

USE `db_inventory`;

CREATE TABLE IF NOT EXISTS `tb_inventory` (
  `id_barang` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_barang` VARCHAR(20) NOT NULL,
  `nama_barang` VARCHAR(50) NOT NULL,
  `jumlah_barang` INT NOT NULL,
  `satuan_barang` VARCHAR(20) NOT NULL,
  `harga_beli` DOUBLE(20,2) NOT NULL,
  PRIMARY KEY (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

