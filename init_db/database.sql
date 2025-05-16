CREATE DATABASE IF NOT EXISTS `db_inventory`;

USE `db_inventory`;

CREATE TABLE IF NOT EXISTS `tb_inventory` (
  `id_barang` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_barang` VARCHAR(20) NOT NULL,
  `nama_barang` VARCHAR(50) NOT NULL,
  `jumlah_barang` INT NOT NULL,
  `satuan_barang` VARCHAR(20) NOT NULL,
  `harga_beli` DOUBLE(20,2) NOT NULL,
  `status_barang` BOOLEAN NOT NULL,
  PRIMARY KEY (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER $$

CREATE PROCEDURE update_status_barang()
BEGIN
  UPDATE tb_inventory
  SET status_barang = 
    CASE 
      WHEN jumlah_barang = 0 THEN 0
      ELSE 1
    END;
END$$

DELIMITER ;

