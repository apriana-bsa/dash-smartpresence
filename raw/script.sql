CREATE TABLE `pengaturan`
(
    `batas_kemiripan_absen_wajah`                       DECIMAL(3,0), # 0 s/d 100
    `batas_kemiripan_konfirmasi_absen_wajah`            DECIMAL(3,0), # 0 s/d 100
    `batas_kemiripan_pendaftaran_wajah`                 DECIMAL(3,0), # 40 s/d 100
    `gunakan_absen_wajah_otomatis`                      ENUM('y','t') NOT NULL,  # ya | tidak
    `batas_kemiripan_absen_wajah_otomatis`              DECIMAL(3,0), # 0 s/d 100
    `batas_kemiripan_konfirmasi_absen_wajah_otomatis`   DECIMAL(3,0), # 0 s/d 100
    `pemindai_rfid`                                     ENUM('y','t'), # ya | tidak
    `pemindai_nfc`                                      ENUM('y','t'), # ya | tidak
    `pemindai_barcode`                                  ENUM('y','t'), # ya | tidak
    `absen_harus_dengan_alasan`                         ENUM('y','t'), # ya | tidak
    `batas_konfirmasi_absen`                            INT UNSIGNED NOT NULL, # dalam satuan hari
    `default_konfirmasi_absen`                          ENUM('v','na') NOT NULL, # valid | not approved
    `utc`                                               VARCHAR(6) NOT NULL, # timezone
    `toleransi_waktu_server`                            INT UNSIGNED NOT NULL, # dalam detik
    `gps_harus_aktif`                                   ENUM('y','t') NOT NULL, # ya | tidak
    `toleransi_jarak_gps`                               INT UNSIGNED NOT NULL, # dalam meter
    `end_of_day`                                        TIME NOT NULL,
    `mesin_polapengaman_pakai`                          ENUM('y', 't') NOT NULL, # ya | tidak
    `mesin_polapengaman`                                VARCHAR(9) NOT NULL,
    `mesin_deteksiekspresi`                             ENUM('y', 't') NOT NULL, # ya | tidak
    `mesin_deteksiekspresi_batas`                       DECIMAL(3,0),  # 0 s/d 100
    `mesin_tampilportal`                                ENUM('y','t') NOT NULL, # jika y maka pada datacapture muncul tombol untuk portal pegawai
    `mesin_tampillatarpeta`                             ENUM('y','t') NOT NULL, # jika y maka pada datacapture muncul peta pada background
    `employee_ijinkantukarshift`                        ENUM('y','t') NOT NULL, # jika y maka pada employee muncul tombol untuk tukar shift
    `employee_ijinkanpengajuanlembur`                   ENUM('y','t') NOT NULL,
    `employee_ijinkangantifotoprofile`                  ENUM('y','t') NOT NULL,
    `employee_tracker_gunakandefault`                  	ENUM('y','t') NOT NULL,
    `employee_tracker_intervaldefault`                  INT UNSIGNED NOT NULL, # dalam satuan menit
    `employee_tracker_lamashiftberakhir`                INT UNSIGNED NOT NULL, # dalam satuan jam
    `default_perlakuanlembur`                           ENUM('tanpalembur','konfirmasi','lembur') NOT NULL,
    `kirimsms`                                          VARCHAR(2) NOT NULL, # masuk dan keluar, contoh: kirim masuk dan keluar: yy, kirim masuk saja: yt, tidak kirim apa2: tt
    `format_sms_absen`                                  TEXT NOT NULL, # tag: {company} {name} {pin} {inout[IN|OUT]} {note[WITH NOTE]} {datetime[id]} {datetime[en]} {crlf}
    `format_sms_verifikasi_lupa_pwd_pegawai`            TEXT NOT NULL, # tag: {company} {name} {pin} {verification_code} {expired[id]} {expired[en]} {crlf}
    `format_sms_lupa_pwd_pegawai`                       TEXT NOT NULL # tag: {company} {name} {pin} {username} {password} {crlf}
) Engine=InnoDB;

INSERT INTO pengaturan VALUES(
    90,         # batas_kemiripan_absen_wajah
    40,         # batas_kemiripan_konfirmasi_absen_wajah
    40,         # batas_kemiripan_pendaftaran_wajah
    't',        # gunakan_absen_wajah_otomatis    
    90,         # batas_kemiripan_absen_wajah_otomatis
    40,         # batas_kemiripan_konfirmasi_absen_wajah_otomatis
    't',        # pemindai_rfid
    't',        # pemindai_nfc
    't',        # pemindai_barcode
    't',        # absen_harus_dengan_alasan
    2,          # batas_konfirmasi_absen
    'v',        # default_konfirmasi_absen
    '+08:00',   # utc
    120,        # toleransi_waktu_server
    'y',        # gps_harus_aktif
    100,        # toleransi_jarak_gps
    '04:00',    # end_of_day
    't',        # mesin_polapengaman_pakai
    '',         # mesin_polapengaman
    't',        # mesin_deteksiekspresi
    50,         # mesin_deteksiekspresi_batas
    't',        # mesin_tampilportal,
    'y',        # mesin_tampillatarpeta
    'y',        # employee_ijinkantukarshift,
    't',        # employee_ijinkanpengajuanlembur,
    'y',        # employee_ijinkangantifotoprofile,
    't',        # employee_tracker_gunakandefault
    5,          # employee_tracker_intervaldefault
    12,         # employee_tracker_lamashiftberakhir
    'lembur',   # default_perlakuanlembur,
    'tt',        # kirimsms,
    '{name} is CHECK-{inout[IN|OUT]}{note[WITH NOTE]} on {datetime[id]}',      # format_sms_absen
    'VERFICIATION CODE: {verification_code}.{crlf}Valid until next 12 hours.', # format_sms_verifikasi_lupa_pwd_pegawai
    '{company}{crlf}{name}{crlf}{pin}{crlf}{username}{crlf}{password}'         # format_sms_lupa_pwd_pegawai
    );

CREATE TABLE `atributvariable`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `atribut`           VARCHAR(100) NOT NULL,
    `penting`           ENUM('y','t') NOT NULL,
    `inserted`          DATETIME NOT NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `atribut`
(
    `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `atribut`               VARCHAR(100) NOT NULL,
    `tampilpadaringkasan`   ENUM('y','t') NOT NULL,
    `penting`               ENUM('y','t') NOT NULL,
    `inserted`              DATETIME NOT NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `atributnilai`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idatribut`         INT(11) UNSIGNED NOT NULL,
    `nilai`             VARCHAR(100) NOT NULL,
    `inserted`          DATETIME NOT NULL,
    CONSTRAINT `FK_atributnilai_idatribut_atribut` FOREIGN KEY (`idatribut`) REFERENCES `atribut` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;


CREATE TABLE `agama`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `agama`             VARCHAR(100) NOT NULL,
    `urutan`            INT(11) UNSIGNED NOT NULL,
    `inserted`          DATETIME NOT NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

INSERT INTO `agama` VALUES(1, 'Islam', 1, NOW());
INSERT INTO `agama` VALUES(2, 'Kristen', 2, NOW());
INSERT INTO `agama` VALUES(3, 'Katolik', 3, NOW());
INSERT INTO `agama` VALUES(4, 'Hindu', 4, NOW());
INSERT INTO `agama` VALUES(5, 'Budha', 5, NOW());
INSERT INTO `agama` VALUES(6, 'Kong Hu Cu', 6, NOW());

CREATE TABLE `harilibur`
(
    `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tanggalawal`     DATE NOT NULL,
    `tanggalakhir`    DATE NOT NULL,
    `keterangan`      TEXT NOT NULL,
    `inserted`        DATETIME NOT NULL,
    INDEX `idx_harilibur_tanggalawal` (`tanggalawal`),
    INDEX `idx_harilibur_tanggalakhir` (`tanggalakhir`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `hariliburagama`
(
    `idharilibur`    INT(11) UNSIGNED NOT NULL,
    `idagama`        INT(11) UNSIGNED NOT NULL,
    `inserted`       DATETIME NOT NULL,
    CONSTRAINT `FK_harilibur_agama_idharilibur` FOREIGN KEY (`idharilibur`) REFERENCES `harilibur` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_harilibur_agama_idagama` FOREIGN KEY (`idagama`) REFERENCES `agama` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`idharilibur`, `idagama`)
) Engine=InnoDB;

CREATE TABLE `hariliburatribut`
(
    `id`                 INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idharilibur`        INT(11) UNSIGNED NOT NULL,
    `idatributnilai`     INT(11) UNSIGNED NOT NULL,
    UNIQUE KEY `unique_hariliburatribut_idharilibur_idatributnilai` (`idharilibur`,`idatributnilai`),
    INDEX `idx_hariliburatribut_idharilibur` (`idharilibur`),
    INDEX `idx_hariliburatribut_idatributnilai` (`idatributnilai`),
    CONSTRAINT `FK_hariliburatribut_idharilibur_harilibur` FOREIGN KEY (`idharilibur`) REFERENCES `harilibur` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_hariliburatribut_idatributnilai_atributnilai` FOREIGN KEY (`idatributnilai`) REFERENCES `atributnilai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `alasanmasukkeluar`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `alasan`            VARCHAR(100) NOT NULL,
    `icon`              VARCHAR(30) NOT NULL,
    `tampilsaat`        ENUM('', 'm', 'k', 'mk') NOT NULL,  # masuk | keluar | masuk & keluar
    `tampilpadamesin`   ENUM('y', 't') NOT NULL,  # ya | tidak 
    `terhitungkerja`    ENUM('y', 't') NOT NULL,  # ya | tidak
    `urutan`            INT(11) UNSIGNED NOT NULL,
    `digunakan`         ENUM ('y','t') NOT NULL, # ya | tidak
    `inserted`          DATETIME NOT NULL,
    INDEX `idx_alasanmasukkeluar_tampilsaat` (`tampilsaat`),
    INDEX `idx_alasanmasukkeluar_urutan` (`urutan`),
    INDEX `idx_alasanmasukkeluar_digunakan` (`digunakan`),
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `alasantidakmasuk`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `alasan`            VARCHAR(100) NOT NULL,
    `kategori`          ENUM('','s','i','d','a','c') NOT NULL, # sakit | ijin | dispensasi | alpha | cuti
    `urutan`            INT(11) UNSIGNED NOT NULL,
    `digunakan`         ENUM ('y','t') NOT NULL, # ya | tidak
    `inserted`          DATETIME NOT NULL,
    INDEX `idx_alasantidakmasuk_kategori` (`kategori`),
    INDEX `idx_alasantidakmasuk_urutan` (`urutan`),
    INDEX `idx_alasantidakmasuk_digunakan` (`digunakan`),
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jamkerjakategori`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`              VARCHAR(100) NOT NULL,
    `digunakan`         ENUM ('y','t') NOT NULL, # ya | tidak
    `inserted`          DATETIME NOT NULL,
    INDEX `idx_jamkerjakategori_digunakan` (`digunakan`),
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jamkerja`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`              VARCHAR(100) NOT NULL,
    `idkategori`        INT(11) UNSIGNED,
    `jenis`             ENUM('','full','shift') NOT NULL,
    `toleransi`         INT UNSIGNED NOT NULL, /* toleransi dalam menit */
    `acuanterlambat`   ENUM('jadwal','toleransi') NOT NULL,
    `hitunglemburstlh`  INT UNSIGNED NOT NULL, /* terhitung lembur adalah n menit setelah jam pulang*/
    `digunakan`         ENUM ('y','t') NOT NULL, # ya | tidak
    `inserted`          DATETIME NOT NULL,
    INDEX `idx_jamkerja_jenis` (`jenis`),
    INDEX `idx_jamkerja_digunakan` (`digunakan`),
    CONSTRAINT `FK_jamkerja_idkategori` FOREIGN KEY (`idkategori`) REFERENCES `jamkerjakategori` (`id`) ON DELETE SET NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jamkerjafull`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idjamkerja`                INT(11) UNSIGNED NOT NULL,
    `berlakumulai`              DATE NOT NULL,
    `_1_masukkerja`             ENUM ('y','t') NOT NULL, /* ini hari minggu */
    `_1_jammasuk`               TIME,
    `_1_jampulang`              TIME,
    `_2_masukkerja`             ENUM ('y','t') NOT NULL,
    `_2_jammasuk`               TIME,
    `_2_jampulang`              TIME,
    `_3_masukkerja`             ENUM ('y','t') NOT NULL,
    `_3_jammasuk`               TIME,
    `_3_jampulang`              TIME,
    `_4_masukkerja`             ENUM ('y','t') NOT NULL,
    `_4_jammasuk`               TIME,
    `_4_jampulang`              TIME,
    `_5_masukkerja`             ENUM ('y','t') NOT NULL,
    `_5_jammasuk`               TIME,
    `_5_jampulang`              TIME,
    `_6_masukkerja`             ENUM ('y','t') NOT NULL,
    `_6_jammasuk`               TIME,
    `_6_jampulang`              TIME,
    `_7_masukkerja`             ENUM ('y','t') NOT NULL,
    `_7_jammasuk`               TIME,
    `_7_jampulang`              TIME,
    `inserted`                  DATETIME NOT NULL,
    UNIQUE KEY `unique_idjamkerja_berlakumulai` (`idjamkerja`,`berlakumulai`),
    INDEX `idx_jamkerjafull_berlakumulai` (`berlakumulai`),
    CONSTRAINT `FK_jamkerjafull_idjamkerja_jamkerja` FOREIGN KEY (`idjamkerja`) REFERENCES `jamkerja` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jamkerjafullistirahat`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idjamkerjafull`            INT(11) UNSIGNED NOT NULL,
    `hari`                      ENUM('1','2','3','4','5','6','7') NOT NULL,
    `jamawal`                   TIME NOT NULL,
    `jamakhir`                  TIME NOT NULL,
    `inserted`                  DATETIME NOT NULL,
    INDEX `idx_jamkerjafullistirahat_hari` (`hari`),
    CONSTRAINT `FK_jamkerjafullistirahat_idjamkerjafull_jamkerjafull` FOREIGN KEY (`idjamkerjafull`) REFERENCES `jamkerjafull` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jamkerjashift_jenis`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`              VARCHAR(100) NOT NULL,
    `digunakan`         ENUM ('y','t') NOT NULL, # ya | tidak
    `inserted`          DATETIME NOT NULL,
    INDEX `idx_jamkerjashift_jenis_digunakan` (`digunakan`),
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jamkerjashift`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idjamkerja`                INT(11) UNSIGNED NOT NULL,
    `namashift`                 VARCHAR(100) NOT NULL,
    `kode`                      VARCHAR(20) NOT NULL,
    `idjenis`                   INT(11) UNSIGNED,
    `_0_masuk`                  ENUM ('y','t') NOT NULL, /* khusus jika hari libur */
    `_1_masuk`                  ENUM ('y','t') NOT NULL, /* ini hari minggu */
    `_2_masuk`                  ENUM ('y','t') NOT NULL, /* ini hari senin */
    `_3_masuk`                  ENUM ('y','t') NOT NULL, /* ini hari selasa */
    `_4_masuk`                  ENUM ('y','t') NOT NULL, /* ini hari rabu */
    `_5_masuk`                  ENUM ('y','t') NOT NULL, /* ini hari kamis */
    `_6_masuk`                  ENUM ('y','t') NOT NULL, /* ini hari jumat */
    `_7_masuk`                  ENUM ('y','t') NOT NULL, /* ini hari sabtu */
    `urutan`                    INT(11) UNSIGNED NOT NULL,
    `digunakan`                 ENUM ('y','t') NOT NULL, # ya | tidak
    `inserted`                  DATETIME NOT NULL,
    INDEX `idx_jamkerjashift_kode` (`kode`),
    INDEX `idx_jamkerjashift_digunakan` (`digunakan`),
    CONSTRAINT `FK_jamkerjashift_idjamkerja_jamkerja` FOREIGN KEY (`idjamkerja`) REFERENCES `jamkerja` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_jamkerjashift_idjenis` FOREIGN KEY (`idjenis`) REFERENCES `jamkerjashift_jenis` (`id`) ON DELETE SET NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jamkerjashiftdetail`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idjamkerjashift`           INT(11) UNSIGNED NOT NULL,
    `berlakumulai`              DATE NOT NULL,
    `jammasuk`                  TIME NOT NULL,
    `jampulang`                 TIME NOT NULL,
    `jamistirahatmulai`         TIME,
    `jamistirahatselesai`       TIME,
    `inserted`                  DATETIME NOT NULL,
    UNIQUE KEY `unique_idjamkerjashift_berlakumulai` (`idjamkerjashift`,`berlakumulai`),
    INDEX `idx_jamkerjashiftdetail_berlakumulai` (`berlakumulai`),
    CONSTRAINT `FK_jamkerjashiftdetail_idjamkerjashift_jamkerjashift` FOREIGN KEY (`idjamkerjashift`) REFERENCES `jamkerjashift` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jamkerjakhusus`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `keterangan`                VARCHAR(100),
    `tanggalawal`               DATE NOT NULL,
    `tanggalakhir`              DATE NOT NULL,
    `toleransi`                 INT UNSIGNED NOT NULL, /* toleransi dalam menit */
    `hitunglemburstlh`          INT UNSIGNED NOT NULL, /* terhitung lembur adalah n menit setelah jam pulang*/
    `jammasuk`                  TIME NOT NULL,
    `jampulang`                 TIME NOT NULL,
    `inserted`                  DATETIME NOT NULL,
    INDEX `idx_jamkerjakhusus_tanggalawal` (`tanggalawal`),
    INDEX `idx_jamkerjakhusus_tanggalakhir` (`tanggalakhir`),
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jamkerjakhususistirahat`
(
    `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idjamkerjakhusus`      INT(11) UNSIGNED NOT NULL,
    `jamawal`               TIME NOT NULL,
    `jamakhir`              TIME NOT NULL,
    `inserted`              DATETIME NOT NULL,
    CONSTRAINT `FK_jamkerjakhususistirahat_idjamkerjakhusus` FOREIGN KEY (`idjamkerjakhusus`) REFERENCES `jamkerjakhusus` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jamkerjakhususjamkerja`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idjamkerjakhusus`          INT(11) UNSIGNED NOT NULL,
    `idjamkerja`                INT(11) UNSIGNED NOT NULL,
    `inserted`                  DATETIME NOT NULL,
    UNIQUE KEY `unique_idjamkerjakhususjamkerja` (`idjamkerjakhusus`, `idjamkerja`),
    CONSTRAINT `FK_jamkerjakhususjamkerja_idjamkerjakhusus` FOREIGN KEY (`idjamkerjakhusus`) REFERENCES `jamkerjakhusus` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_jamkerjakhususjamkerja_idjamkerja_jamkerja` FOREIGN KEY (`idjamkerja`) REFERENCES `jamkerja` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `lokasi`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`              VARCHAR(30) NOT NULL,
    `lat`               DOUBLE NOT NULL,
    `lon`               DOUBLE NOT NULL,
    `jaraktoleransi` 	ENUM('default','ditentukan') NOT NULL,
    `radius`			INT(11) NOT NULL,
    `inserted`          DATETIME NOT NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `pegawai`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`              VARCHAR(100) NOT NULL,
    `idagama`           INT(11) UNSIGNED,
    `pin`               VARCHAR(8),
    `pemindai`          VARCHAR(64),
    `nomorhp`           VARCHAR(20) NOT NULL,
    `gunakantracker`	ENUM('d','y','t') NOT NULL, # default | ya | tidak
    `password`          VARCHAR(255) NOT NULL,
    `gcmid`             VARCHAR(256),
    `removeads`         ENUM('y','t') NOT NULL, # ya | tidak
    `flexytime`         ENUM('y','t') NOT NULL, # ya | tidak
    `status`            ENUM('a','t'), # aktif | tidak aktif
    `tanggalaktif`      DATE NOT NULL,
    `tanggaltdkaktif`   DATE,
    `checksum_img`      VARCHAR(32),
    `checksum_voice`    VARCHAR(32),
    `inserted`          DATETIME NOT NULL,
    `del`               ENUM('y','t') NOT NULL,
    `del_waktu`         DATETIME,
    UNIQUE KEY `unique_pegawai_pin` (`pin`),
    UNIQUE KEY `unique_pegawai_pemindai` (`pemindai`),
    INDEX `idx_pegawai_nama` (`nama`),
    INDEX `idx_pegawai_status` (`status`),
    INDEX `idx_pegawai_tanggalaktif` (`tanggalaktif`),
    INDEX `idx_pegawai_tanggaltdkaktif` (`tanggaltdkaktif`),
    INDEX `idx_pegawai_del` (`del`),
    CONSTRAINT `FK_pegawai_idagama` FOREIGN KEY (`idagama`) REFERENCES `agama` (`id`) ON DELETE SET NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `pegawaitracker_log`
(
    `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai`     INT(11) UNSIGNED NOT NULL,
    `waktu`         DATETIME NOT NULL,
    `lat`		    DOUBLE NOT NULL,
    `lon`		    DOUBLE NOT NULL,
    `idlogabsen`    INT(11) UNSIGNED,
    `inserted`      DATETIME NOT NULL,
    UNIQUE KEY `unique_pegawaitracker_log` (`idpegawai`, `waktu`, `lat`, `lon`),
    CONSTRAINT `FK_pegawaitracker_log_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `pegawaijamkerja`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai`                 INT(11) UNSIGNED NOT NULL,
    `idjamkerja`                INT(11) UNSIGNED NOT NULL,
    `berlakumulai`              DATE NOT NULL,
    `inserted`                  DATETIME NOT NULL,
    INDEX `idx_pegawaijamkerja_berlakumulai` (`berlakumulai`),
    UNIQUE KEY `unique_idpegawai_idjamkerja_berlakumulai` (`idpegawai`, `idjamkerja`,`berlakumulai`),
    CONSTRAINT `FK_pegawaijamkerja_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_pegawaijamkerja_idjamkerja_jamkerja` FOREIGN KEY (`idjamkerja`) REFERENCES `jamkerja` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `pegawaiatribut`
(
    `id`                 INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai`          INT(11) UNSIGNED NOT NULL,
    `idatributnilai`     INT(11) UNSIGNED NOT NULL,
    UNIQUE KEY `unique_pegawaiatribut_idpegawai_idatributnilai` (`idpegawai`,`idatributnilai`),
    INDEX `idx_pegawaiatribut_idpegawai` (`idpegawai`),
    INDEX `idx_pegawaiatribut_idatributnilai` (`idatributnilai`),
    CONSTRAINT `FK_pegawaiatribut_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_pegawaiatribut_idatributnilai_atributnilai` FOREIGN KEY (`idatributnilai`) REFERENCES `atributnilai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `pegawaiatributvariable`
(
    `id`                 INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai`          INT(11) UNSIGNED NOT NULL,
    `idatributvariable`  INT(11) UNSIGNED NOT NULL,
    `variable`           VARCHAR(100) NOT NULL,
    UNIQUE KEY `unique_pegawaiatribut_idpegawai_idatributnilai` (`idpegawai`,`idatributvariable`),
    INDEX `idx_pegawaiatributvariable_idpegawai` (`idpegawai`),
    INDEX `idx_pegawaiatributvariable_idatributvariable` (`idatributvariable`),
    CONSTRAINT `FK_pegawaiatributvariable_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_pegawaiatributvariable_idatributvariable_atributvariable` FOREIGN KEY (`idatributvariable`) REFERENCES `atributvariable` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `pegawailokasi`
(
    `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai`    INT(11) UNSIGNED NOT NULL,
    `idlokasi`     INT(11) UNSIGNED NOT NULL,
    UNIQUE KEY `unique_pegawailokasi_idpegawai_idlokasi` (`idpegawai`,`idlokasi`),
    INDEX `idx_pegawailokasi_idpegawai` (`idpegawai`),
    INDEX `idx_pegawailokasi_idlokasi` (`idlokasi`),
    CONSTRAINT `FK_pegawailokasi_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_pegawailokasi_idlokasi_lokasi` FOREIGN KEY (`idlokasi`) REFERENCES `lokasi` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jamkerjakhususpegawai`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idjamkerjakhusus`          INT(11) UNSIGNED NOT NULL,
    `idpegawai`                 INT(11) UNSIGNED NOT NULL,
    `inserted`                  DATETIME NOT NULL,
    UNIQUE KEY `unique_idjamkerjakhusus_idpegawai` (`idjamkerjakhusus`, `idpegawai`),
    CONSTRAINT `FK_jamkerjakhususpegawai_idjamkerjakhusus` FOREIGN KEY (`idjamkerjakhusus`) REFERENCES `jamkerjakhusus` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_jamkerjakhususpegawai_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `pegawai_forgetpwd`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai`         INT(11) UNSIGNED NOT NULL,
    `kodeverifikasi`    VARCHAR(6) NOT NULL,
    `expired`           DATETIME NOT NULL,
    INDEX `idx_pegawai_forgetpwd_kodeverifikasi` (`kodeverifikasi`),
    UNIQUE KEY `unique_idpegawai` (`idpegawai`),
    CONSTRAINT `FK_pegawai_forgetpwd_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jadwalshift`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tanggal`                   DATE NOT NULL,
    `idpegawai`                 INT(11) UNSIGNED NOT NULL,
    `idjamkerjashift`           INT(11) UNSIGNED,
    `inserted`                  DATETIME NOT NULL,
    UNIQUE KEY `unique_tanggal_idpegawai_idjamkerjashift` (`tanggal`,`idpegawai`,`idjamkerjashift`),
    INDEX `idx_jadwalshift_tanggal` (`tanggal`),
    INDEX `FK_jadwalshift_idpegawai_pegawai` (`idpegawai`),
    INDEX `FK_jadwalshift_idjamkerjashift_jamkerjashift` (`idjamkerjashift`),
    PRIMARY KEY (`id`, `tanggal`)
) Engine=InnoDB
PARTITION BY RANGE COLUMNS(tanggal) (
    PARTITION p_ VALUES LESS THAN (MAXVALUE)
);

CREATE TABLE `jadwalshifttukar`
(
    `id`                   INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai_a`          INT(11) UNSIGNED NOT NULL,
    `periode_a`            VARCHAR(4) NOT NULL, # yymm
    `idpegawai_b`          INT(11) UNSIGNED NOT NULL,
    `periode_b`            VARCHAR(4) NOT NULL, # yymm
    `status`               ENUM('','v','w','a','d','c','e') NOT NULL, # v=valid jika langsung dari dashboard | w=waiting | a=accepted | d=declined | c=canceled | e=expired
    `inserted`             DATETIME NOT NULL,
    INDEX `idx_jadwalshifttukar_periode_a` (`periode_a`),
    INDEX `idx_jadwalshifttukar_periode_b` (`periode_b`),
    CONSTRAINT `FK_jadwalshifttukar_idpegawai_a_pegawai` FOREIGN KEY (`idpegawai_a`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_jadwalshifttukar_idpegawai_b_pegawai` FOREIGN KEY (`idpegawai_b`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `jadwalshifttukardetail`
(
    `id`                   INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idjadwalshifttukar`   INT(11) UNSIGNED NOT NULL,
    `ab`                   ENUM('a','b') NOT NULL, -- pegawai_a atau pegawai_b
    `tanggal`              DATE NOT NULL,
    `idjamkerjashift`      INT(11) UNSIGNED,
    INDEX `idx_jadwalshifttukardetail_ab` (`ab`),
    INDEX `idx_jadwalshifttukardetail_tanggal` (`tanggal`),
    CONSTRAINT `FK_jadwalshifttukardetail_idjadwalshifttukar_jadwalshifttukar` FOREIGN KEY (`idjadwalshifttukar`) REFERENCES `jadwalshifttukar` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_jadwalshifttukardetail_idjamkerjashift_jamkerjashift` FOREIGN KEY (`idjamkerjashift`) REFERENCES `jamkerjashift` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `ijintidakmasuk`
(
    `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai`               INT UNSIGNED NOT NULL,
    `tanggalawal`             DATE NOT NULL,
    `tanggalakhir`            DATE NOT NULL,
    `idalasantidakmasuk`      INT UNSIGNED,
    `keterangan`              TEXT NOT NULL,
    `filename`                VARCHAR(50) NOT NULL, # berisi path (yyyy/mm/yyyymmddhhmmss_[random6digit], contoh: 2016/10/20161028115431_123456
    `status`                  ENUM('','c','a','na') NOT NULL, # approved | confirm | not approved
    `inserted`                DATETIME NOT NULL,
    `updated`                 DATETIME,
    INDEX `idx_ijintidakmasuk_tanggalawal` (`tanggalawal`),
    INDEX `idx_ijintidakmasuk_tanggalakhir` (`tanggalakhir`),
    INDEX `idx_ijintidakmasuk_status` (`status`),
    CONSTRAINT `FK_ijintidakmasuk_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_ijintidakmasuk_idalasantidakmasuk__alasantidakmasuk` FOREIGN KEY (`idalasantidakmasuk`) REFERENCES `alasantidakmasuk` (`id`) ON DELETE SET NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `facesample`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai`         INT(11) UNSIGNED NOT NULL,
    `filename`          VARCHAR(50) NOT NULL,
    `checksum`          VARCHAR(32) NOT NULL,
    `inserted`          DATETIME NOT NULL,
    CONSTRAINT `FK_facesample_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `fingersample`
(
    `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai`     INT(11) UNSIGNED NOT NULL,
    `algoritma`     VARCHAR(20) NOT NULL,
    `finger_id`     INT(11) UNSIGNED NOT NULL,
    `size`          INT(11) UNSIGNED NOT NULL,
    `valid`         INT(11) UNSIGNED NOT NULL,
    `template`      TEXT NOT NULL,
    `checksum`      VARCHAR(32) NOT NULL,
    `inserted`      DATETIME NOT NULL,
    `deleted`       DATETIME,
    UNIQUE KEY `unique_fingersample_idpegawai_checksum` (`idpegawai`,`checksum`),
    CONSTRAINT `FK_fingersample_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `slideshow`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`              VARCHAR(100) NOT NULL,
    `timeout`           INT(11) UNSIGNED NOT NULL, # dalam detik
    `durasiperslide`    INT(11) UNSIGNED NOT NULL, # dalam detik
    `inserted`          DATETIME NOT NULL,
    `updated`           DATETIME,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `slideshowimage`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idslideshow`       INT(11) UNSIGNED NOT NULL,
    `filename`          VARCHAR(255) NOT NULL,
    `checksum`          VARCHAR(32) NOT NULL,
    CONSTRAINT `FK_slideshowimage_idslideshow_slideshow` FOREIGN KEY (`idslideshow`) REFERENCES `slideshow` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `slideshowwaktu`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idslideshow`       INT(11) UNSIGNED NOT NULL,
    `waktumulai`        TIME NOT NULL,
    `waktuselesai`      TIME NOT NULL,
    CONSTRAINT `FK_slideshowwaktu_idslideshow_slideshow` FOREIGN KEY (`idslideshow`) REFERENCES `slideshow` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `fingerprintconnector`
(
    `id`                     INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`                   VARCHAR(30) NOT NULL,
    `username`				 VARCHAR(4) NOT NULL,
    `password`				 VARCHAR(255) NOT NULL,
    `keterangan`             TEXT NOT NULL,
    `pushapi`                VARCHAR(100) NOT NULL,
    `intervalceksync`        INT(11) NOT NULL DEFAULT 7200, # interval sinkron dgn server dalam satuan detik.
    `sync_data_pada`         VARCHAR(100) NOT NULL, # antar data dipisahkan dgn pipeline, contoh -> 01:00-03:00|04:00-06:00
    `clear_data_pada`        VARCHAR(100) NOT NULL, # antar data dipisahkan dgn pipeline, contoh -> 01:00-03:00|04:00-06:00
    `status`                 ENUM('a','t') NOT NULL, # aktif | tidak
    `lastsync`               DATETIME,
    `inserted`               DATETIME NOT NULL,
    UNIQUE KEY `unique_fingerprintconnector_username` (`username`),
    INDEX `idx_fingerprintconnector_status` (`status`),
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `mesin`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`                      VARCHAR(30) NOT NULL,
    `jenis`					    ENUM('smartphone', 'fingerprint') NOT NULL,
    `deviceid`                  VARCHAR(13), # xxxx-yyyyzzzz
    `deviceidreset`             DATETIME,
    `cekjamserver`              ENUM('y','t') NOT NULL,
    `utcdefault`                ENUM('y','t') NOT NULL,
    `utc`                       VARCHAR(6) NOT NULL, # timezone
    `ijinkanpendaftaran`        ENUM('y','t') NOT NULL,
    `idslideshow`               INT(11) UNSIGNED,
    `kamera_opsi`               ENUM('depan','belakang','bebas') NOT NULL,
    `gcmid`                     VARCHAR(256),
    `perangkat_bt_rfidnfc`      VARCHAR(17) NOT NULL,
    `perangkat_bt_bukakunci`    VARCHAR(17) NOT NULL,
    `status`                    ENUM('bs','th') NOT NULL, # bebas | terhubung
    `fp_comkey`       	 	    INT(11) UNSIGNED NOT NULL,
    `fp_ip`       	 		    VARCHAR(50) NOT NULL,
    `fp_soapport`       	    INT(11) UNSIGNED NOT NULL,
    `fp_udpport`       	 	    INT(11) UNSIGNED NOT NULL,
    `fp_idfingerprintconnector` INT(11) UNSIGNED,
    `fp_serialnumber`           VARCHAR(32),
    `fp_algoritma`              VARCHAR(32) NOT NULL,
    `fp_intervaltarik`          INT(11) NOT NULL DEFAULT 30, # tarik data dari finger print dalam satuan detik
    `fp_ijinkanadmin`           ENUM('y','t') NOT NULL DEFAULT 't',
    `fp_lat`        		    DOUBLE NOT NULL DEFAULT 0,
    `fp_lon`        		    DOUBLE NOT NULL DEFAULT 0,
    `fp_komunikasi`             VARCHAR(32) NOT NULL, # lihat keterangan dibawah ***
    `fp_status`        		    ENUM('i','r') NOT NULL DEFAULT 'i', # inactive | ready
    `lastsync`                  DATETIME,
    `inserted`                  DATETIME NOT NULL,
    `del`                       ENUM('y','t') NOT NULL,
    UNIQUE KEY `unique_mesin_deviceid` (`deviceid`),
    INDEX `idx_mesin_deviceid` (`deviceid`),
    INDEX `idx_mesin_deviceidreset` (`deviceidreset`),
    INDEX `idx_mesin_idslideshow` (`idslideshow`),
    INDEX `idx_mesin_status` (`status`),
    INDEX `idx_mesin_fp_status` (`fp_status`),
    CONSTRAINT `FK_mesin_idslideshow_slideshow` FOREIGN KEY (`idslideshow`) REFERENCES `slideshow` (`id`) ON DELETE SET NULL,
    CONSTRAINT `FK_mesin_fp_idfingerprintconnector` FOREIGN KEY (`fp_idfingerprintconnector`) REFERENCES `fingerprintconnector` (`id`) ON DELETE SET NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

/*
    const METODE_UTILITY_MESIN = 'UDP'; --> s/u
    const METODE_PEGAWAI_READ = 'UDP'; --> s/u
    const METODE_PEGAWAI_INSERT = 'UDP'; --> s/u
    const METODE_PEGAWAI_DELETE = 'UDP'; --> s/u
    const METODE_FINGERSAMPLE_READ = 'UDP'; --> s/u
    const METODE_FINGERSAMPLE_INSERT = 'UDP'; --> s/u
    const METODE_FINGERSAMPLE_DELETE = 'UDP'; --> s/u
    const METODE_LOGABSEN_READ = 'UDP'; --> s/u
    const METODE_LOGABSEN_DELETEALL = 'UDP'; --> s/u
    const KUNCI_SAAT_SINKRON = 't'; --> y/t
    const KUNCI_SAAT_DELETEALL = 't'; --> y/t
    const RESTART_SETELAH_DELETEALL = 't'; --> y/t
*/

CREATE TABLE `mesinatribut`
(
    `id`                 INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idmesin`            INT(11) UNSIGNED NOT NULL,
    `idatributnilai`     INT(11) UNSIGNED NOT NULL,
    UNIQUE (`idmesin`,`idatributnilai`),
    INDEX `idx_mesinatribut_idmesin` (`idmesin`),
    INDEX `idx_mesinatribut_idatributnilai` (`idatributnilai`),
    CONSTRAINT `FK_mesinatribut_idmesin_mesin` FOREIGN KEY (`idmesin`) REFERENCES `mesin` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_mesinatribut_idatributnilai_atributnilai` FOREIGN KEY (`idatributnilai`) REFERENCES `atributnilai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `parameterekspor`
(
    `gunakanpwd`                ENUM('y','t') NOT NULL,
    `pwd`                       BLOB, # AES_DECRYPT / AES_DECRYPT(pwd, 'e754251708594345576d9407126e4d46')
    `logokiri`                  VARCHAR(255) NOT NULL,
    `logokanan`                 VARCHAR(255) NOT NULL,
    `header_1_teks`             VARCHAR(255) NOT NULL,
    `header_1_fontstyle`        ENUM('normal','bold','italic','underline') NOT NULL,
    `header_2_teks`             VARCHAR(255) NOT NULL,
    `header_2_fontstyle`        ENUM('normal','bold','italic','underline') NOT NULL,
    `header_3_teks`             VARCHAR(255) NOT NULL,
    `header_3_fontstyle`        ENUM('normal','bold','italic','underline') NOT NULL,
    `header_4_teks`             VARCHAR(255) NOT NULL,
    `header_4_fontstyle`        ENUM('normal','bold','italic','underline') NOT NULL,
    `header_5_teks`             VARCHAR(255) NOT NULL,
    `header_5_fontstyle`        ENUM('normal','bold','italic','underline') NOT NULL,
    `footerkiri_1_teks`         VARCHAR(255) NOT NULL,
    `footerkiri_1_fontstyle`    ENUM('normal','bold','italic','underline') NOT NULL,
    `footerkiri_2_teks`         VARCHAR(255) NOT NULL,
    `footerkiri_2_fontstyle`    ENUM('normal','bold','italic','underline') NOT NULL,
    `footerkiri_3_teks`         VARCHAR(255) NOT NULL,
    `footerkiri_3_fontstyle`    ENUM('normal','bold','italic','underline') NOT NULL,
    `footerkiri_4_separator`    INT UNSIGNED NOT NULL,
    `footerkiri_5_teks`         VARCHAR(255) NOT NULL,
    `footerkiri_5_fontstyle`    ENUM('normal','bold','italic','underline') NOT NULL,
    `footerkiri_6_teks`         VARCHAR(255) NOT NULL,
    `footerkiri_6_fontstyle`    ENUM('normal','bold','italic','underline') NOT NULL,
    `footertengah_1_teks`       VARCHAR(255) NOT NULL,
    `footertengah_1_fontstyle`  ENUM('normal','bold','italic','underline') NOT NULL,
    `footertengah_2_teks`       VARCHAR(255) NOT NULL,
    `footertengah_2_fontstyle`  ENUM('normal','bold','italic','underline') NOT NULL,
    `footertengah_3_teks`       VARCHAR(255) NOT NULL,
    `footertengah_3_fontstyle`  ENUM('normal','bold','italic','underline') NOT NULL,
    `footertengah_4_separator`  INT UNSIGNED NOT NULL,
    `footertengah_5_teks`       VARCHAR(255) NOT NULL,
    `footertengah_5_fontstyle`  ENUM('normal','bold','italic','underline') NOT NULL,
    `footertengah_6_teks`       VARCHAR(255) NOT NULL,
    `footertengah_6_fontstyle`  ENUM('normal','bold','italic','underline') NOT NULL,
    `footerkanan_1_teks`        VARCHAR(255) NOT NULL,
    `footerkanan_1_fontstyle`   ENUM('normal','bold','italic','underline') NOT NULL,
    `footerkanan_2_teks`        VARCHAR(255) NOT NULL,
    `footerkanan_2_fontstyle`   ENUM('normal','bold','italic','underline') NOT NULL,
    `footerkanan_3_teks`        VARCHAR(255) NOT NULL,
    `footerkanan_3_fontstyle`   ENUM('normal','bold','italic','underline') NOT NULL,
    `footerkanan_4_separator`   INT UNSIGNED NOT NULL,
    `footerkanan_5_teks`        VARCHAR(255) NOT NULL,
    `footerkanan_5_fontstyle`   ENUM('normal','bold','italic','underline') NOT NULL,
    `footerkanan_6_teks`        VARCHAR(255) NOT NULL,
    `footerkanan_6_fontstyle`   ENUM('normal','bold','italic','underline') NOT NULL,
    `updated`                   DATETIME
) Engine=InnoDB;

INSERT INTO parameterekspor VALUES(
    't',        # gunakanpwd
    '',         # pwd
    '',         # logokiri
    '',         # logokanan
    '',         # header_1_teks
    'normal',   # header_1_fontstyle
    '',         # header_2_teks
    'normal',   # header_2_fontstyle
    '',         # header_3_teks
    'normal',   # header_3_fontstyle
    '',         # header_4_teks
    'normal',   # header_4_fontstyle
    '',         # header_5_teks
    'normal',   # header_5_fontstyle
    '',         # footerkiri_1_teks
    'normal',   # footerkiri_1_fontstyle
    '',         # footerkiri_2_teks
    'normal',   # footerkiri_2_fontstyle
    '',         # footerkiri_3_teks
    'normal',   # footerkiri_3_fontstyle
    0,          # footerkiri_4_separator
    '',         # footerkiri_5_teks
    'normal',   # footerkiri_5_fontstyle
    '',         # footerkiri_6_teks
    'normal',   # footerkiri_6_fontstyle
    '',         # footertengah_1_teks
    'normal',   # footertengah_1_fontstyle
    '',         # footertengah_2_teks
    'normal',   # footertengah_2_fontstyle
    '',         # footertengah_3_teks
    'normal',   # footertengah_3_fontstyle
    0,          # footertengah_4_separator
    '',         # footertengah_5_teks
    'normal',   # footertengah_5_fontstyle
    '',         # footertengah_6_teks
    'normal',   # footertengah_6_fontstyle
    '',         # footerkanan_1_teks
    'normal',   # footerkanan_1_fontstyle
    '',         # footerkanan_2_teks
    'normal',   # footerkanan_2_fontstyle
    '',         # footerkanan_3_teks
    'normal',   # footerkanan_3_fontstyle
    0,          # footerkanan_4_separator
    '',         # footerkanan_5_teks
    'normal',   # footerkanan_5_fontstyle
    '',         # footerkanan_6_teks
    'normal',   # footerkanan_6_fontstyle
    NULL        # updated
);

CREATE TABLE `logabsen`
(
    `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `waktu`                 DATETIME NOT NULL,
    `idpegawai`             INT(11) UNSIGNED NOT NULL,
    `idmesin`               INT(11) UNSIGNED,
    `masukkeluar`           ENUM('m','k') NOT NULL,
    `idalasanmasukkeluar`   INT(11) UNSIGNED,
    `terhitungkerja`        ENUM('y', 't') NOT NULL,
    `lat`                   DOUBLE,
    `lon`                   DOUBLE,
    `status`                ENUM('','v','c','na') NOT NULL, # valid | confirm | not approved
    `konfirmasi`            VARCHAR(10), # f: face | l: location
    `filename`              VARCHAR(50),
    `checksum`              VARCHAR(32),
    `sumber`				ENUM('manual','smartphone','fingerprint') NOT NULL,
    `flag`                  ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur') NOT NULL,
    `flag_keterangan`       VARCHAR(255) NOT NULL,
    `dataasli`              VARCHAR(160), # waktu|idpegawai|idmesin|masukkeluar|idalasanmasukkeluar|terhitungkerja|lat|lon|status|konfirmasi|filename
    `inserted`              DATETIME NOT NULL,
    `updated`               DATETIME,
    UNIQUE KEY `unique_logabsen_waktu_idpegawai` (`waktu`, `idpegawai`),
    INDEX `idx_logabsen_waktu` (`waktu`),
    INDEX `idx_logabsen_masukkeluar` (`masukkeluar`),
    INDEX `idx_logabsen_status` (`status`),
    INDEX `idx_logabsen_idpegawai` (`idpegawai`),
    INDEX `idx_logabsen_idmesin` (`idmesin`),
    INDEX `idx_logabsen_idalasanmasukkeluar` (`idalasanmasukkeluar`),
    INDEX `idx_logabsen_sumber` (`sumber`),
    INDEX `idx_logabsen_flag` (`flag`),
    PRIMARY KEY (`id`, `waktu`, `idpegawai`)
) Engine=InnoDB
PARTITION BY RANGE COLUMNS(waktu) (
    PARTITION p_ VALUES LESS THAN (MAXVALUE)
);

CREATE TABLE `logabsen_backup`
(
    `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `waktu`                 DATETIME NOT NULL,
    `idpegawai`             INT(11) UNSIGNED NOT NULL,
    `idmesin`               INT(11) UNSIGNED,
    `masukkeluar`           ENUM('m','k') NOT NULL,
    `idalasanmasukkeluar`   INT(11) UNSIGNED,
    `terhitungkerja`        ENUM('y', 't') NOT NULL,
    `lat`                   DOUBLE,
    `lon`                   DOUBLE,
    `status`                ENUM('','v','c','na') NOT NULL, # valid | confirm | not approved
    `konfirmasi`            VARCHAR(10), # f: face | l: location
    `filename`              VARCHAR(50),
    `checksum`              VARCHAR(32),
    `sumber`				ENUM('manual','smartphone','fingerprint') NOT NULL,
    `flag`                  ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur') NOT NULL,
    `flag_keterangan`       VARCHAR(255) NOT NULL,
    `dataasli`              VARCHAR(160), # waktu|idpegawai|idmesin|masukkeluar|idalasanmasukkeluar|terhitungkerja|lat|lon|status|konfirmasi|filename
    `inserted`              DATETIME NOT NULL,
    `updated`               DATETIME,
    UNIQUE KEY `unique_logabsen_backup_waktu_idpegawai` (`waktu`, `idpegawai`),
    INDEX `idx_logabsen_backup_waktu` (`waktu`),
    INDEX `idx_logabsen_backup_masukkeluar` (`masukkeluar`),
    INDEX `idx_logabsen_backup_status` (`status`),
    INDEX `idx_logabsen_backup_sumber` (`sumber`),
    INDEX `idx_logabsen_flag` (`flag`),
    CONSTRAINT `FK_logabsen_backup_idmesin_mesin` FOREIGN KEY (`idmesin`) REFERENCES `mesin` (`id`) ON DELETE SET NULL,
    CONSTRAINT `FK_logabsen_backup_idalasanmasukkeluar_alasanmasukkeluar` FOREIGN KEY (`idalasanmasukkeluar`) REFERENCES `alasanmasukkeluar` (`id`) ON DELETE SET NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `logabsen_del`
(
    `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `waktu`                 DATETIME NOT NULL,
    `idpegawai`             INT(11) UNSIGNED NOT NULL,
    `idmesin`               INT(11) UNSIGNED,
    `masukkeluar`           ENUM('m','k') NOT NULL,
    `idalasanmasukkeluar`   INT(11) UNSIGNED,
    `terhitungkerja`        ENUM('y', 't') NOT NULL,
    `lat`                   DOUBLE,
    `lon`                   DOUBLE,
    `status`                ENUM('','v','c','na') NOT NULL, # valid | confirm | not approved
    `konfirmasi`            VARCHAR(10), # f: face | l: location
    `filename`              VARCHAR(50),
    `checksum`              VARCHAR(32),
    `sumber`				ENUM('manual','smartphone','fingerprint') NOT NULL,
    `flag`                  ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur') NOT NULL,
    `flag_keterangan`       VARCHAR(255) NOT NULL,
    `dataasli`              VARCHAR(160), # waktu|idpegawai|idmesin|masukkeluar|idalasanmasukkeluar|terhitungkerja|lat|lon|status|konfirmasi|filename
    `inserted`              DATETIME NOT NULL,
    `updated`               DATETIME,
    `del_waktu`             DATETIME,
    INDEX `idx_logabsen_waktu` (`waktu`),
    INDEX `idx_logabsen_masukkeluar` (`masukkeluar`),
    INDEX `idx_logabsen_status` (`status`),
    INDEX `idx_logabsen_sumber` (`sumber`),
    INDEX `idx_logabsen_flag` (`flag`),
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `rekapshift`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tanggal`                   DATE NOT NULL,
    `idpegawai`                 INT(11) UNSIGNED NOT NULL,
    `idjamkerjashift`           INT(11) UNSIGNED,
    `masukkerja`                ENUM('y', 't') NOT NULL,
    `waktumasuk`                DATETIME,
    `waktukeluar`               DATETIME,
    `selisihmasuk`              INT, # satuan detik
    `selisihkeluar`             INT, # satuan detik
    `lamakerja`                 INT, # satuan detik
    `lamalembur`                INT, # satuan detik
    `flag_terlambat`            ENUM('','y','t') NOT NULL,
    `flag_pulangawal`           ENUM('','y','t') NOT NULL,
    `flag_lembur`               ENUM('','y','t') NOT NULL,
    `inserted`                  DATETIME NOT NULL,
    UNIQUE KEY `unique_rekapshift_idpegawai_tanggal` (`idpegawai`,`tanggal`,`idjamkerjashift`),
    INDEX `idx_rekapshift_tanggal` (`tanggal`),
    INDEX `idx_rekapshift_idjamkerjashift` (`idjamkerjashift`),
    CONSTRAINT `FK_rekapshift_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
);

CREATE TABLE `rekapabsen`
(
    `id`                          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai`                   INT(11) UNSIGNED NOT NULL,
    `tanggal`                     DATE NOT NULL,
    `idharilibur`                 INT UNSIGNED,
    `masukkerja`                  ENUM('y', 't') NOT NULL,
    `jumlahsesi`                  INT(11) UNSIGNED NOT NULL,
    `idalasantidakmasuk`          INT UNSIGNED,
    `alasantidakmasukkategori`    ENUM('','s','i','d','a','c'), # sakit | ijin | dispensasi | alpha | cuti
    `idjamkerja`                  INT UNSIGNED,
    `idjamkerjakhusus`            INT UNSIGNED,
    `jadwalmasukkerja`            ENUM('y', 't') NOT NULL,
    `jenisjamkerja`               ENUM('','full','shift') NOT NULL,
    `jadwallamakerja`             INT UNSIGNED NOT NULL,
    `idalasanmasuk`               INT UNSIGNED,
    `waktumasuk`                  DATETIME,
    `waktukeluar`                 DATETIME,
    `selisihmasuk`                INT, # satuan detik
    `selisihkeluar`               INT, # satuan detik
    `lamakerja`                   INT, # satuan detik (sudah include dengan lamaflexytime)
    `lamaflexytime`               INT, # satuan detik
    `lamalembur`                  INT, # satuan detik
    `overlap`                     INT, # satuan detik
    `flag_terlambat`              ENUM('','y','t') NOT NULL,
    `flag_pulangawal`             ENUM('','y','t') NOT NULL,
    `flag_lembur`                 ENUM('','y','t') NOT NULL,
    `absentidaklengkap`           ENUM('','m','k') NOT NULL, # m-> tidak absen masuk, k->tidak absen keluar
    `status`                      ENUM('','w', 'v', 'd') NOT NULL, # waiting for verfication, verified, drop
    UNIQUE KEY `unique_rekapabsen_idpegawai_tanggal` (`idpegawai`,`tanggal`),
    INDEX `idx_rekapabsen_tanggal` (`tanggal`),
    INDEX `idx_rekapabsen_alasantidakmasukkategori` (`alasantidakmasukkategori`),
    INDEX `idx_rekapabsen_absentidaklengkap` (`absentidaklengkap`),
    CONSTRAINT `FK_rekapabsen_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_rekapabsen_idharilibur_harilibur` FOREIGN KEY (`idharilibur`) REFERENCES `harilibur` (`id`) ON DELETE SET NULL,
    CONSTRAINT `FK_rekapabsen_idalasantidakmasuk__alasantidakmasuk` FOREIGN KEY (`idalasantidakmasuk`) REFERENCES `alasantidakmasuk` (`id`) ON DELETE SET NULL,
    CONSTRAINT `FK_rekapabsen_idjamkerja_jamkerja` FOREIGN KEY (`idjamkerja`) REFERENCES `jamkerja` (`id`) ON DELETE SET NULL,
    CONSTRAINT `FK_rekapabsen_idjamkerjakhusus_jamkerjakhusus` FOREIGN KEY (`idjamkerjakhusus`) REFERENCES `jamkerjakhusus` (`id`) ON DELETE SET NULL,
    CONSTRAINT `FK_rekapabsen_idalasanmasuk_alasanmasukkeluar` FOREIGN KEY (`idalasanmasuk`) REFERENCES `alasanmasukkeluar` (`id`) ON DELETE SET NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE rekapabsen_jadwal (
    `id`                    INT UNSIGNED AUTO_INCREMENT,
    `idrekapabsen`          INT(11) UNSIGNED NOT NULL,
    `idjamkerjashift`       INT UNSIGNED,
    `waktu`                 DATETIME,
    `masukkeluar`           ENUM('m','k'),
    `checking`              ENUM('', 'start','end'), # start: absen masuk, end: absen pulang (dalam satu sesi selain istirahat)
    `shiftpertamaterakhir`  ENUM('','pertama','terakhir'), # apakah shift tsb adalah shift pertama atau terakhir pada tanggal tsb?
    `shiftsambungan`        ENUM('y','t'), # apakah shift tsb terdapat sambungan dari shift sebelumnya/sesudahnya yg telah dijadwalkan?
    CONSTRAINT `FK_rekapabsen_jadwal_idrekapabsen_rekapabsen` FOREIGN KEY (`idrekapabsen`) REFERENCES `rekapabsen` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_rekapabsen_jadwal_idjamkerjashift_jamkerjashift` FOREIGN KEY (`idjamkerjashift`) REFERENCES `jamkerjashift` (`id`) ON DELETE SET NULL,
    PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE `rekapabsen_logabsen_all`
(
    `id`                INT UNSIGNED AUTO_INCREMENT,
    `idrekapabsen`      INT(11) UNSIGNED NOT NULL,
    `idlogabsen`        INT UNSIGNED,
    `waktu`             DATETIME,
    `masukkeluar`       ENUM('m','k'),
    `idalasan`          INT UNSIGNED,
    `terhitungkerja`    ENUM('y','t'),
    `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
    `status`            ENUM('','v','c','na') NOT NULL, # valid | confirm | not approved
    INDEX `idx_rekapabsen_logabsen_all_idlogabsen` (`idlogabsen`),
    CONSTRAINT `FK_rekapabsen_logabsen_all_idrekapabsen_rekapabsen` FOREIGN KEY (`idrekapabsen`) REFERENCES `rekapabsen` (`id`) ON DELETE CASCADE,
    PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE `rekapabsen_logabsen`
(
    `id`                INT UNSIGNED AUTO_INCREMENT,
    `idrekapabsen`      INT(11) UNSIGNED NOT NULL,
    `idlogabsen`        INT UNSIGNED,
    `waktu`             DATETIME,
    `masukkeluar`       ENUM('m','k'),
    `idalasan`          INT UNSIGNED,
    `terhitungkerja`    ENUM('y','t'),
    `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
    INDEX `idx_rekapabsen_logabsen_all_idlogabsen` (`idlogabsen`),
    CONSTRAINT `FK_rekapabsen_logabsen_idrekapabsen_rekapabsen` FOREIGN KEY (`idrekapabsen`) REFERENCES `rekapabsen` (`id`) ON DELETE CASCADE,
    PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE `rekapabsen_hasil`
(
    `id`                INT UNSIGNED AUTO_INCREMENT,
    `idrekapabsen`      INT(11) UNSIGNED NOT NULL,
    `idlogabsen`        INT UNSIGNED,
    `idjamkerjashift`   INT UNSIGNED,
    `terhitung`         ENUM('','k','l'),
    `flag`              ENUM('','j','p'),
    `waktu`             DATETIME,
    `masukkeluar`       ENUM('m','k'),
    `override`          ENUM('y','t'),
    INDEX `idx_rekapabsen_hasil_idlogabsen` (`idlogabsen`),
    CONSTRAINT `FK_rekapabsen_hasil_idrekapabsen_rekapabsen` FOREIGN KEY (`idrekapabsen`) REFERENCES `rekapabsen` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_rekapabsen_hasil_idjamkerjashift_jamkerjashift` FOREIGN KEY (`idjamkerjashift`) REFERENCES `jamkerjashift` (`id`) ON DELETE SET NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE `_peringkatabsen`
(
    `idpegawai`                   INT(11) UNSIGNED NOT NULL,
    `peringkat`                   INT NOT NULL, # peringkat ke-
    `masukkerja`                  INT NOT NULL, # berapa kali
    `lamakerja`                   INT NOT NULL, # satuan detik
    `terlambat`                   INT NOT NULL, # berapa kali
    `terlambatlama`               INT NOT NULL, # satuan detik
    `pulangawal`                  INT NOT NULL, # berapa kali
    `pulangawallama`              INT NOT NULL, # satuan detik
    `lamalembur`                  INT NOT NULL, # satuan detik
    `inserted`                    DATETIME,
    INDEX `idx__peringkatabsen_peringkat` (`peringkat`),
    CONSTRAINT `FK__peringkatabsen_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`idpegawai`)
) ENGINE=InnoDB;

# untuk batasan user yang login hanya bisa melihat data sesuai dengan atribut
CREATE TABLE `batasan`
(
    `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `namabatasan`           VARCHAR(100) NOT NULL,
    `inserted`              DATETIME NOT NULL,
    `updated`               DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `batasanatribut`
(
    `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idbatasan`             INT(11) UNSIGNED NOT NULL,
    `idatributnilai`        INT(11) UNSIGNED NOT NULL,
    UNIQUE KEY `unique_batasanatribut_idbatasan_idatributnilai` (`idbatasan`,`idatributnilai`),
    INDEX `idx_batasanatribut_idbatasan` (`idbatasan`),
    INDEX `idx_batasanatribut_idatributnilai` (`idatributnilai`),
    CONSTRAINT `FK_batasanatribut_idbatasan_batasan` FOREIGN KEY (`idbatasan`) REFERENCES `batasan` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_batasanatribut_idatributnilai_atributnilai` FOREIGN KEY (`idatributnilai`) REFERENCES `atributnilai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `batasanemail`
(
    `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`                 VARCHAR(255) NOT NULL,
    `idbatasan`             INT(11) UNSIGNED NOT NULL,
    `inserted`              DATETIME NOT NULL,
    `updated`               DATETIME,
    UNIQUE KEY `unique_batasanuser_email` (`email`),
    INDEX `idx_batasanuser_idbatasan` (`idbatasan`),
    CONSTRAINT `FK_batasanuser_idbatasan_batasan` FOREIGN KEY (`idbatasan`) REFERENCES `batasan` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `customdashboard_node`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`                      VARCHAR(100) NOT NULL,        
    `judul`                     VARCHAR(100) NOT NULL,
    `icon`                      VARCHAR(100) NOT NULL, #pilihan pakai font-awesome   
    `warna`                     VARCHAR(10) NOT NULL,
    `query_jenis`               ENUM('kehadiran','master') NOT NULL, 
    `query_kehadiran`           ENUM('semua','full', 'shift') NOT NULL, 
    `query_kehadiran_data`      ENUM('sudahabsen','belumabsen','adadikantor','ijintidakmasuk','terlambat','pulangawal','lamalembur','lamakerja','masuknormal','pulangnormal') NOT NULL, 
    `query_kehadiran_if`        TEXT NOT NULL, 
    `query_kehadiran_group`     ENUM('','agama','jamkerja','jamkerjajenis','jamkerjashift_jenis','jamkerjakategori','alasantidakmasuk', 'alasantidakmasuk_kategori') NOT NULL, # kalau group tidak ada angka pada node, hanya ada detail saja.
    `query_kehadiran_periode`   ENUM('','navigasi-tanggal') NOT NULL,
    `query_master_data`         ENUM('pegawai') NOT NULL,
    `query_master_if`           TEXT NOT NULL, 
    `query_master_group`        ENUM('','agama','jamkerja','jamkerjajenis','jamkerjashift_jenis','jamkerjakategori') NOT NULL, # kalau group tidak ada angka pada node, hanya ada detail saja.
    `query_master_periode`      ENUM('','navigasi-tanggal') NOT NULL,
    `waktutampil`               ENUM('y','t') NOT NULL,
    `waktutampil_awal`          TIME,
    `waktutampil_akhir`         TIME, 
    `inserted`                  DATETIME NOT NULL,
    `updated`                   DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `customdashboard`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`                      VARCHAR(100) NOT NULL,     
    `tampil_konfirmasi`         ENUM('y','t') NOT NULL,
    `tampil_peringkat`          ENUM('y','t') NOT NULL,
    `tampil_3lingkaran`         ENUM('y','t') NOT NULL,
    `tampil_sudahbelumabsen`    ENUM('y','t') NOT NULL,
    `tampil_terlambatdll`       ENUM('y','t') NOT NULL,
    `tampil_pulangawaldll`      ENUM('y','t') NOT NULL,
    `tampil_totalgrafik`        ENUM('y','t') NOT NULL,
    `tampil_peta`               ENUM('y','t') NOT NULL,
    `tampil_harilibur`          ENUM('y','t') NOT NULL,
    `tampil_riwayatdashboard`   ENUM('y','t') NOT NULL,
    `inserted`                  DATETIME NOT NULL,
    `updated`                   DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `customdashboard_detail`
(
    `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `urutan`                    INT(11) UNSIGNED NOT NULL,
    `idcustomdashboard`         INT(11) UNSIGNED NOT NULL,
    `idcustomdashboard_node`    INT(11) UNSIGNED NOT NULL,
    UNIQUE KEY `unique_customdashboard_detail` (`idcustomdashboard`,`idcustomdashboard_node`),
    INDEX `idx_customdashboard_detail_idcustomdashboard` (`idcustomdashboard`),
    INDEX `idx_customdashboard_detail_idcustomdashboard_node` (`idcustomdashboard_node`),
    CONSTRAINT `FK_customdashboard_detail_idcustomdashboard` FOREIGN KEY (`idcustomdashboard`) REFERENCES `customdashboard` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_customdashboard_detail_idcustomdashboard_node` FOREIGN KEY (`idcustomdashboard_node`) REFERENCES `customdashboard_node` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `customdashboard_email`
(
    `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`                 VARCHAR(255) NOT NULL,
    `idcustomdashboard`     INT(11) UNSIGNED NOT NULL,
    `inserted`              DATETIME NOT NULL,
    `updated`               DATETIME,
    UNIQUE KEY `unique_customdashboard_email` (`email`),
    INDEX `idx_customdashboard_email_idbatasan` (`idcustomdashboard`),
    CONSTRAINT `FK_customdashboard_email_idcustomdashboard` FOREIGN KEY (`idcustomdashboard`) REFERENCES `customdashboard` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `authtoken_mesin`
(
    `idmesin`           INT(11) UNSIGNED NOT NULL,
    `idtoken`           VARCHAR(32) NOT NULL,
    `expired`           DATETIME NOT NULL,
    `refreshtoken`      VARCHAR(32) NOT NULL,
    `refreshexpired`    DATETIME NOT NULL,
    `inserted`          DATETIME NOT NULL,
    CONSTRAINT `FK_authtokenmesin_idmesin_mesin` FOREIGN KEY (`idmesin`) REFERENCES `mesin` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`idmesin`, `refreshtoken`)
) Engine=InnoDB;

CREATE TABLE `authtokenblacklist_mesin`
(
    `idmesin`           INT(11) UNSIGNED NOT NULL,
    `idtoken`           VARCHAR(32) NOT NULL,
    `expired`           DATETIME NOT NULL,
    `inserted`          DATETIME NOT NULL,
    PRIMARY KEY (`idmesin`, `idtoken`)
) Engine=InnoDB;

CREATE TABLE `authtoken_pegawai`
(
    `idpegawai`         INT(11) UNSIGNED NOT NULL,
    `idtoken`           VARCHAR(32) NOT NULL,
    `expired`           DATETIME NOT NULL,
    `refreshtoken`      VARCHAR(32) NOT NULL,
    `refreshexpired`    DATETIME NOT NULL,
    `inserted`          DATETIME NOT NULL,
    CONSTRAINT `FK_authtokenpegawai_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`idpegawai`, `refreshtoken`)
) Engine=InnoDB;

CREATE TABLE `authtokenblacklist_pegawai`
(
    `idpegawai`         INT(11) UNSIGNED NOT NULL,
    `idtoken`           VARCHAR(32) NOT NULL,
    `expired`           DATETIME NOT NULL,
    `inserted`          DATETIME NOT NULL,
    PRIMARY KEY (`idpegawai`, `idtoken`)
) Engine=InnoDB;

CREATE TABLE `authtoken_fingerprintconnector`
(
    `idfingerprintconnector` INT(11) UNSIGNED NOT NULL,
    `idtoken`                VARCHAR(32) NOT NULL,
    `expired`                DATETIME NOT NULL,
    `refreshtoken`           VARCHAR(32) NOT NULL,
    `refreshexpired`         DATETIME NOT NULL,
    `inserted`               DATETIME NOT NULL,
    CONSTRAINT `FK_authtokenfingerprintconnector_idfingerprintconnector` FOREIGN KEY (`idfingerprintconnector`) REFERENCES `fingerprintconnector` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`idfingerprintconnector`, `refreshtoken`)
) Engine=InnoDB;

CREATE TABLE `authtokenblacklist_fingerprintconnector`
(
    `idfingerprintconnector`  INT(11) UNSIGNED NOT NULL,
    `idtoken`                 VARCHAR(32) NOT NULL,
    `expired`                 DATETIME NOT NULL,
    `inserted`                DATETIME NOT NULL,
    PRIMARY KEY (`idfingerprintconnector`, `idtoken`)
) Engine=InnoDB;

CREATE TABLE `uniqueid`
(
    `uniqueid`          VARCHAR(4) NOT NULL,
    PRIMARY KEY (`uniqueid`)
) Engine=InnoDB;

CREATE TABLE `_postingabsen`
(
    `tanggal`           VARCHAR(10) NOT NULL,
    `keterangan`        TEXT,
    `inserted`          DATETIME NOT NULL,
    INDEX `idx__postingabsen_tanggal` (`tanggal`),
    PRIMARY KEY (`tanggal`)
) Engine=InnoDB;

CREATE TABLE `_logpegawai`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `waktu`             DATETIME NOT NULL,
    `idpegawai`         INT(11) UNSIGNED NOT NULL,
    `keterangan`        TEXT NOT NULL,
    `method`            VARCHAR(20),
    `path`              VARCHAR(255),
    `body`              TEXT,
    INDEX `idx__loguser_waktu` (`waktu`),
    CONSTRAINT `FK__logpegawai_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `grantaccesskey`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `grantaccesskey`    VARCHAR(6) NOT NULL,
    `expired`           DATETIME NOT NULL,
    `inserted`          DATETIME NOT NULL,
    INDEX `idx_grantaccesskey_grantaccesskey` (`grantaccesskey`),
    UNIQUE KEY `unique_grantaccesskey_grantaccesskey` (`grantaccesskey`),
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `troubleshooting_token`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idmesin`           INT(11) UNSIGNED,
    `versi`             VARCHAR(30) NOT NULL,
    `accesstoken`       TEXT NOT NULL,
    `refreshtoken`      VARCHAR(32) NOT NULL,
    `inserted`          DATETIME NOT NULL,
    INDEX `idx_troubleshooting_token_idmesin` (`idmesin`),
    INDEX `idx_troubleshooting_token_inserted` (`inserted`),
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `troubleshooting_errorlog`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idmesin`           INT(11) UNSIGNED,
    `versi`             VARCHAR(30) NOT NULL,
    `waktu`             VARCHAR(14) NOT NULL,
    `refid`             VARCHAR(30),
    `idpegawai`         INT(11) UNSIGNED,
    `keterangan`        TEXT,
    `inserted`          DATETIME NOT NULL,
    INDEX `idx_troubleshooting_errorlog_idmesin` (`idmesin`),
    INDEX `idx_troubleshooting_errorlog_waktu` (`waktu`),
    INDEX `idx_troubleshooting_errorlog_idpegawai` (`idpegawai`),
    INDEX `idx_troubleshooting_errorlog_inserted` (`inserted`),
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `cuti`
(
    `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tahun`          INT(11) UNSIGNED NOT NULL,
    `idpegawai`      INT(11) UNSIGNED NOT NULL,
    `jumlah`         INT(11) UNSIGNED NOT NULL,
    INDEX `idx_cuti_tahun` (`tahun`),
    UNIQUE KEY `unique_cuti_tahun_idpegawai` (`tahun`,`idpegawai`),
    CONSTRAINT `FK_cuti_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `pengaturan_peringkat`
(
    `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `urutan`        INT(11) UNSIGNED,
    `nama`          VARCHAR(100) NOT NULL,
    `order`         ENUM('asc','desc') NOT NULL,
    `dipakai`       ENUM('y','t') NOT NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

INSERT INTO pengaturan_peringkat VALUES(NULL, 1,'orderby_masukkerja','desc','y');
INSERT INTO pengaturan_peringkat VALUES(NULL, 2,'orderby_masukkerja_d','desc','y');
INSERT INTO pengaturan_peringkat VALUES(NULL, 3,'orderby_masukkerja_i','desc','y');
INSERT INTO pengaturan_peringkat VALUES(NULL, 4,'masukkerja0','desc','y');
INSERT INTO pengaturan_peringkat VALUES(NULL, 5,'terlambat0','asc','y');
INSERT INTO pengaturan_peringkat VALUES(NULL, 6,'pulangawal0','asc','y');
INSERT INTO pengaturan_peringkat VALUES(NULL, 7,'lamakerja0','desc','y');
INSERT INTO pengaturan_peringkat VALUES(NULL, 8,'lamalembur0','desc','y');
INSERT INTO pengaturan_peringkat VALUES(NULL, 9,'terlambatlama0','asc','y');
INSERT INTO pengaturan_peringkat VALUES(NULL, 10,'pulangawallama0','asc','y');

CREATE TABLE `konfirmasi_lembur`
(
    `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idpegawai`     INT(11) UNSIGNED,
    `idlogabsen`    INT(11) UNSIGNED,
    `jenisjamkerja` ENUM('full','shift') NOT NULL,
    `status`        ENUM('c','a','na'), # confirm | apporve | not apporve
    `inserted`      DATETIME NOT NULL,
	UNIQUE KEY `unique_konfirmasi_lembur_idlogabsen` (`idlogabsen`),
    INDEX `idx_konfirmasi_lembur_idlogabsen` (`idlogabsen`),
    INDEX `idx_konfirmasi_lembur_jenisjamkerja` (`jenisjamkerja`),
    INDEX `idx_konfirmasi_lembur_status` (`status`),
    CONSTRAINT `FK_konfirmasi_lembur_idpegawai_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `customtv`
(
    `header1`                   TEXT NOT NULL,
    `header2`                   TEXT NOT NULL,
    `bahasa`                    ENUM('id','en','cn') NOT NULL,
    `atribut_nip`               INT(11) UNSIGNED,  -- diambil dari atributvariable
    `atribut_nip_caption`       VARCHAR(100) NOT NULL,
    `atribut_jabatan`           INT(11) UNSIGNED, -- diambil dari atribut
    `atribut_jabatan_caption`   VARCHAR(100) NOT NULL,
    `tampil_terlambat`          ENUM('y','t') NOT NULL,
    `tampil_pulangawal`         ENUM('y','t') NOT NULL,
    `tampil_ijintidakmasuk`     ENUM('y','t') NOT NULL,
    `tampil_kehadiranterbaik`   ENUM('y','t') NOT NULL,
    `tampil_belumabsen`         ENUM('y','t') NOT NULL,
    `tampil_logabsen`           ENUM('y','t') NOT NULL,
    `warna_background`          VARCHAR(10) NOT NULL,
    `warna_headerfooter`        VARCHAR(10) NOT NULL,
    `warna_headerfooter_text`   VARCHAR(10) NOT NULL,
    `warna_card`                VARCHAR(10) NOT NULL,
    `warna_card_text`           VARCHAR(10) NOT NULL,
    CONSTRAINT `FK_customtv_atribut_nip` FOREIGN KEY (`atribut_nip`) REFERENCES `atributvariable` (`id`) ON DELETE SET NULL,
    CONSTRAINT `FK_customtv_atribut_jabatan` FOREIGN KEY (`atribut_jabatan`) REFERENCES `atribut` (`id`) ON DELETE SET NULL
) Engine=InnoDB;

INSERT INTO customtv VALUES(
    'Smart Presence',
    'Bantuan : (+62) 361 419 145',
    'id',
    NULL,
    'NIP',
    NULL,
    'Jabatan',
    'y',
    'y',
    'y',
    'y',
    't',
    't',
    'ffffff',
    '8e44ad',
    'ffffff',
    'eeeeee',
    '101010'
);

CREATE TABLE `tvgroup`
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`              VARCHAR(50) NOT NULL,
    `judul`             VARCHAR(50) NOT NULL,
    `jenis`             ENUM('terlambat', 'pulangawal', 'ijintidakmasuk', 'kehadiranterbaik', 'belumabsen', 'logabsen') NOT NULL,
    `baris1_label`      VARCHAR(50) NOT NULL,
    `baris1_data`       VARCHAR(100) NOT NULL,
    `baris2_label`      VARCHAR(50) NOT NULL,
    `baris2_data`       VARCHAR(100) NOT NULL,
    `baris3_label`      VARCHAR(50) NOT NULL,
    `baris3_data`       VARCHAR(100) NOT NULL,
    `warna_background`  VARCHAR(10) NOT NULL,
    `warna_teks`        VARCHAR(10) NOT NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `tv`
(
    `id`                            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`                          VARCHAR(50) NOT NULL,
    `header_baris1`                 VARCHAR(200) NOT NULL,
    `header_baris2`                 VARCHAR(200) NOT NULL,
    `orientasi`                     ENUM('vertical', 'horizontal') NOT NULL,
    `jumlah_kolom_horizontal`       ENUM('1', '2', '3', '4') NOT NULL,
    `interval_refresh_data`         INT NOT NULL, # satuannya detik
    `interval_slide`                INT NOT NULL, # satuannya detik
    `bahasa`                        ENUM('id','en','cn') NOT NULL,
    `warna_background`              VARCHAR(10) NOT NULL,
    `headerfooter_warna_background` VARCHAR(10) NOT NULL,
    `headerfooter_warna_teks`       VARCHAR(10) NOT NULL,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `tvdetail`
(
    `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idtv`          INT(11) UNSIGNED NOT NULL,
    `idtvgroup`     INT(11) UNSIGNED NOT NULL,
    `urutan`        INT(11) UNSIGNED NOT NULL,
    UNIQUE KEY `unique_tvdetail` (`idtv`,`idtvgroup`),
    CONSTRAINT `FK_tvdetail_idtv` FOREIGN KEY (`idtv`) REFERENCES `tv` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_tvdetail_idtvgroup` FOREIGN KEY (`idtvgroup`) REFERENCES `tvgroup` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `tvgroupatribut` (
  `idtvgroup`       INT(11) UNSIGNED NOT NULL,
  `idatributnilai`  INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`idtvgroup`,`idatributnilai`)
) ENGINE=InnoDB;

CREATE TABLE perlakuanlembur_atribut
(
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idatributnilai`    INT(11) UNSIGNED NOT NULL,
    `perlakuanlembur`   ENUM('tanpalembur','konfirmasi','lembur') NOT NULL,
    UNIQUE KEY `unique_perlakuanlembur_atribut_idatributnilai` (`idatributnilai`),
    INDEX `idx_perlakuanlembur_atribut_perlakuanlembur` (`perlakuanlembur`),
    CONSTRAINT `FK_perlakuanlembur_atribut_idatributnilai_atributnilai` FOREIGN KEY (`idatributnilai`) REFERENCES `atributnilai` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id`)
) Engine=InnoDB;

CREATE TABLE `pekerjaan` (
  `id` 			int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nama` 		varchar(200) NOT NULL,
  `satuan`      varchar(20) NOT NULL,
  `digunakan` 	enum('y','t') NOT NULL,
  `inserted` 	datetime NOT NULL,
  `updated` 	datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `pekerjaan_user` (
  `id`          INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `idpekerjaan` INT(11) UNSIGNED NOT NULL,
  `iduser`      INT(11) UNSIGNED NOT NULL,
  `idpegawai`	INT(11) UNSIGNED,
  `tanggal`     DATE NOT NULL,
  `keterangan`  TEXT NOT NULL,
  `satuan`      DECIMAL(10,2) NOT NULL,
  `inserted`    DATETIME NOT NULL,
  `updated`     DATETIME,
  CONSTRAINT `FK_pekerjaan_user_idpekerjaan_pekerjaan` FOREIGN KEY (`idpekerjaan`) REFERENCES `pekerjaan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_pekerjaan_user_pegawai` FOREIGN KEY (`idpegawai`) REFERENCES `pegawai` (`id`) ON DELETE SET NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

DELIMITER //

DROP FUNCTION IF EXISTS acakstring//
CREATE FUNCTION acakstring(s TEXT) RETURNS TEXT
BEGIN
    RETURN REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(s,'a','*'),'i','*'),'u','*'),'e','*'),'o','*'),'0','*'),'1','*'),'2','*'),'3','*'),'4','*'),'5','*'),'6','*'),'7','*'),'8','*'),'9','*');
END//

DROP FUNCTION IF EXISTS insert_grantaccesskey//
CREATE FUNCTION insert_grantaccesskey(_expired DATETIME) RETURNS VARCHAR(6)
BEGIN
    DECLARE _flag_duplicate INT DEFAULT FALSE;
    DECLARE _grantaccesskey VARCHAR(6) DEFAULT '';
    DECLARE CONTINUE HANDLER FOR SQLSTATE '23000' SET _flag_duplicate = TRUE;

    REPEAT
        SET _flag_duplicate = FALSE;
        SET _grantaccesskey = LPAD(FLOOR(RAND()*999999),6,'0');

        INSERT INTO grantaccesskey VALUES(NULL, _grantaccesskey, _expired, NOW());
    UNTIL (_flag_duplicate=FALSE) END REPEAT;

    RETURN _grantaccesskey;
END//

DROP EVENT IF EXISTS delete_authtoken_expired//
CREATE EVENT delete_authtoken_expired ON SCHEDULE EVERY 1 HOUR DO
BEGIN
    DELETE FROM authtoken_mesin WHERE refreshexpired<NOW();
    DELETE FROM authtokenblacklist_mesin WHERE expired<NOW();

    DELETE FROM authtoken_pegawai WHERE refreshexpired<NOW();
    DELETE FROM authtokenblacklist_pegawai WHERE expired<NOW();

    DELETE FROM authtoken_fingerprintconnector WHERE refreshexpired<NOW();
    DELETE FROM authtokenblacklist_fingerprintconnector WHERE expired<NOW();

    DELETE FROM fingersample WHERE ISNULL(deleted)=false AND TIMESTAMPDIFF(DAY, deleted, NOW())>7;
END//

DROP EVENT IF EXISTS event_every_1_day//
CREATE EVENT event_every_1_day ON SCHEDULE EVERY 1 DAY DO
BEGIN
    DELETE FROM pegawaitracker_log WHERE TIMESTAMPDIFF(DAY, inserted, NOW())>60;
END//

DROP EVENT IF EXISTS reset_deviceid//
CREATE EVENT reset_deviceid ON SCHEDULE EVERY 1 HOUR DO
BEGIN
    UPDATE mesin SET deviceid=NULL, deviceidreset=NULL  WHERE ISNULL(deviceidreset)=false AND deviceidreset<=NOW();
    DELETE FROM grantaccesskey WHERE expired<NOW();
    DELETE FROM troubleshooting_token WHERE TIMESTAMPDIFF(DAY, inserted, NOW())>14;
    DELETE FROM troubleshooting_errorlog WHERE TIMESTAMPDIFF(DAY, inserted, NOW())>14;
END//

DROP TRIGGER IF EXISTS before_update_mesin//
CREATE TRIGGER before_update_mesin BEFORE UPDATE ON mesin
FOR EACH ROW
BEGIN
    IF (OLD.status='th' AND NEW.status='bs') THEN
        INSERT INTO authtokenblacklist_mesin SELECT idmesin, idtoken, expired, NOW() FROM authtoken_mesin WHERE idmesin=OLD.id AND expired>NOW();
        DELETE FROM authtoken_mesin WHERE idmesin=OLD.id;
    END IF;
END//

DROP TRIGGER IF EXISTS before_delete_mesin//
CREATE TRIGGER before_delete_mesin BEFORE DELETE ON mesin
FOR EACH ROW
BEGIN
    INSERT INTO authtokenblacklist_mesin SELECT idmesin, idtoken, expired, NOW() FROM authtoken_mesin WHERE idmesin=OLD.id AND expired>NOW();
    DELETE FROM authtoken_mesin WHERE idmesin=OLD.id;
END//

DROP TRIGGER IF EXISTS before_delete_pegawai//
CREATE TRIGGER before_delete_pegawai BEFORE DELETE ON pegawai
FOR EACH ROW
BEGIN
    INSERT INTO authtokenblacklist_pegawai SELECT idpegawai, idtoken, expired, NOW() FROM authtoken_pegawai WHERE idpegawai=OLD.id AND expired>NOW();
    DELETE FROM authtoken_pegawai WHERE idpegawai=OLD.id;
END//

DROP PROCEDURE IF EXISTS fill_uniqueid//
CREATE PROCEDURE fill_uniqueid()
BEGIN
    DECLARE i INT DEFAULT 0;
    TRUNCATE uniqueid;
    START TRANSACTION;
    loop01: LOOP
        SET i=i+1;
        IF i>=10000 THEN
            LEAVE loop01;
        ELSE
            INSERT INTO uniqueid VALUES(LPAD(i,4,'0'));
        END IF;
    END LOOP loop01;
    COMMIT;
END //
CALL fill_uniqueid()//
DROP PROCEDURE IF EXISTS fill_uniqueid//

DROP PROCEDURE IF EXISTS hitungrekapabsen_log//
CREATE PROCEDURE hitungrekapabsen_log(IN _idlogabsen INT, IN _idpegawai INT, IN _waktu DATETIME)
BEGIN
    # cara panggil:
    #   CALL hitungrekapabsen_log(NOT NULL, NULL, NULL)
    #   CALL hitungrekapabsen_log(NULL, NOT NULL, NOT NULL)
    # diperlukan parameter _waktu untuk mengetahui tanggal absen tersebut terhitung pada tanggal brp?
    DECLARE _end_of_day TIME DEFAULT NULL;
    DECLARE _tanggal DATE DEFAULT NULL;
    DECLARE _jenisjamkerja VARCHAR(5) DEFAULT NULL;

    IF ISNULL(_idlogabsen)=false THEN
        SELECT idpegawai, waktu INTO _idpegawai, _waktu FROM logabsen WHERE id=_idlogabsen LIMIT 1;
    END IF;

    IF ISNULL(_idpegawai)=false AND ISNULL(_waktu)=false THEN
        # ambil jenis jam kerja
        SET _jenisjamkerja = getpegawaijamkerja(_idpegawai, "jenis", DATE(_waktu));

        IF (ISNULL(_jenisjamkerja)=false) THEN
            IF (_jenisjamkerja='full') THEN
                # ambil end_of_day di pengaturan
                SELECT end_of_day INTO _end_of_day FROM pengaturan LIMIT 1;

                IF ISNULL(_end_of_day)=false THEN
                    IF TIME(_waktu)<=_end_of_day THEN
                        # tanggal ikut hari sebelumnya
                        SET _tanggal = DATE_SUB(DATE(_waktu), INTERVAL 1 DAY);
                    ELSE
                        # tanggal ikut hari sekarang
                        SET _tanggal = DATE(_waktu);
                    END IF;
                END IF;

                IF ISNULL(_tanggal)=false THEN
                    CALL posting(_tanggal, _idpegawai ,'y');
                END IF;
            ELSEIF (_jenisjamkerja='shift') THEN
                SET _tanggal = DATE(_waktu);
                CALL posting(_tanggal, _idpegawai ,'y');

                SET _tanggal = DATE_SUB(DATE(_waktu), INTERVAL 1 DAY);
                CALL posting(_tanggal, _idpegawai ,'y');
            END IF;
        END IF;
    END IF;
END //

DROP PROCEDURE IF EXISTS hitungrekapabsen_ijintidakmasuk//
CREATE PROCEDURE hitungrekapabsen_ijintidakmasuk(IN _idijintidakmasuk INT, IN _idpegawai INT, IN _tanggalawal DATE, IN _tanggalakhir DATE, IN _status VARCHAR(1))
BEGIN
    # cara panggil:
    #   CALL hitungrekapabsen_ijintidakmasuk(NOT NULL, NULL, NULL, NULL, NULL)
    #   CALL hitungrekapabsen_ijintidakmasuk(NULL, NOT NULL, NOT NULL, NOT NULL, NOT NULL)

    DECLARE _tanggal DATE;

    IF ISNULL(_idijintidakmasuk)=false THEN
        SELECT
            idpegawai, tanggalawal, tanggalakhir, status INTO
            _idpegawai, _tanggalawal, _tanggalakhir, _status
        FROM ijintidakmasuk
        WHERE id=_idijintidakmasuk LIMIT 1;
    END IF;

    IF ISNULL(_idpegawai)=false AND
       ISNULL(_tanggalawal)=false AND ISNULL(_tanggalakhir)=false AND
       ISNULL(_status)=false AND
       _status='a' THEN
        SET _tanggal = _tanggalawal;
        WHILE(_tanggal <= _tanggalakhir AND _tanggal <= CURRENT_DATE()) DO
            CALL posting(_tanggal, _idpegawai ,'t');
            SET _tanggal=DATE_ADD(_tanggal, INTERVAL 1 DAY);
        END WHILE;

    END IF;
END //

DROP PROCEDURE IF EXISTS hitungrekapabsen_harilibur//
CREATE PROCEDURE hitungrekapabsen_harilibur(IN _idharilibur INT, IN _tanggalawal DATE, IN _tanggalakhir DATE)
BEGIN
    # cara panggil:
    #   CALL hitungrekapabsen_harilibur(NOT NULL, NOT NULL, NOT NULL)
    # tidak melakukan pengecekan hariliburatribut karena nanti bisa panjang procedure nya.

    DECLARE done INT DEFAULT FALSE;
    DECLARE _idpegawai INT;
    DECLARE _tanggal DATE;
    DECLARE cur_pegawai CURSOR FOR
        SELECT
            id
        FROM
            pegawai
        WHERE
            tanggalaktif<=_tanggal AND
            del='t' AND
            ((status='a' AND (ISNULL(tanggaltdkaktif)=true OR (ISNULL(tanggaltdkaktif)=false AND tanggalaktif<=_tanggal))) OR (status='t' AND ISNULL(tanggaltdkaktif)=false AND tanggaltdkaktif>_tanggal));
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    IF ISNULL(_idharilibur)=false THEN
        SELECT tanggalawal, tanggalakhir INTO _tanggalawal, _tanggalakhir FROM harilibur WHERE id=_idharilibur;
    END IF;

    IF ISNULL(_tanggalawal)=false AND ISNULL(_tanggalakhir)=false THEN
        SET _tanggal = _tanggalawal;
        WHILE(_tanggal <= _tanggalakhir AND _tanggal <= CURRENT_DATE()) DO
            DELETE FROM rekapabsen WHERE tanggal=_tanggal AND idpegawai IN (SELECT id from pegawai);
            OPEN cur_pegawai;
            pegawai_loop: LOOP
                SET done=false;
                FETCH cur_pegawai INTO _idpegawai;
                IF done THEN
                    LEAVE pegawai_loop;
                ELSE
                    CALL posting(_tanggal, _idpegawai, 't');
                END IF;
            END LOOP pegawai_loop;
            CLOSE cur_pegawai;

            SET _tanggal=DATE_ADD(_tanggal, INTERVAL 1 DAY);
        END WHILE;
    END IF;
END //

DROP PROCEDURE IF EXISTS hitungrekapabsen_pertanggal//
CREATE PROCEDURE hitungrekapabsen_pertanggal(IN _tanggalawal DATE, IN _tanggalakhir DATE)
BEGIN
    # cara panggil:
    #   CALL hitungrekapabsen_pertanggal(NOT NULL, NULL)
    #   CALL hitungrekapabsen_pertanggal(NOT NULL, NOT NULL)

    DECLARE done INT DEFAULT FALSE;
    DECLARE _idpegawai INT;
    DECLARE _tanggal DATE;
    DECLARE cur_pegawai CURSOR FOR
        SELECT
            id
        FROM
            pegawai
        WHERE
            tanggalaktif<=_tanggal AND
            del='t' AND
            ((status='a' AND (ISNULL(tanggaltdkaktif)=true OR (ISNULL(tanggaltdkaktif)=false AND tanggalaktif<=_tanggal))) OR (status='t' AND ISNULL(tanggaltdkaktif)=false AND tanggaltdkaktif>_tanggal));
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET _tanggalakhir = IFNULL(_tanggalakhir, _tanggalawal);

    IF DATEDIFF(_tanggalakhir, _tanggalawal)<=31 THEN
        SET _tanggal = _tanggalawal;
        WHILE(_tanggal <= _tanggalakhir AND _tanggal <= CURRENT_DATE()) DO
            DELETE FROM rekapabsen WHERE tanggal=_tanggal AND idpegawai IN (SELECT id from pegawai);
            OPEN cur_pegawai;
            pegawai_loop: LOOP
                SET done=false;
                FETCH cur_pegawai INTO _idpegawai;
                IF done THEN
                    LEAVE pegawai_loop;
                ELSE
                    CALL posting(_tanggal, _idpegawai, 't');
                END IF;
            END LOOP pegawai_loop;
            CLOSE cur_pegawai;

            SET _tanggal=DATE_ADD(_tanggal, INTERVAL 1 DAY);
        END WHILE;
    ELSE
        SIGNAL SQLSTATE '90000' SET MESSAGE_TEXT = 'Forbidden, more than 31 days';
    END IF;
END //

DROP PROCEDURE IF EXISTS hitungrekapabsen_perpegawai_pertanggal//
CREATE PROCEDURE hitungrekapabsen_perpegawai_pertanggal(IN _idpegawai INT, IN _tanggalawal DATE, IN _tanggalakhir DATE)
BEGIN
    # cara panggil:
    #   CALL hitungrekapabsen_pertanggal(NOT NULL, NOT NULL, NULL)
    #   CALL hitungrekapabsen_pertanggal(NOT NULL, NOT NULL, NOT NULL)

    DECLARE _tanggal DATE;

    SET _tanggalakhir = IFNULL(_tanggalakhir, _tanggalawal);

    IF DATEDIFF(_tanggalakhir, _tanggalawal)<=365 THEN
        SET _tanggal = _tanggalawal;
        WHILE(_tanggal <= _tanggalakhir AND _tanggal <= CURRENT_DATE()) DO
            DELETE FROM rekapabsen WHERE tanggal=_tanggal AND idpegawai=_idpegawai;
            CALL posting(_tanggal, _idpegawai, 't');
            SET _tanggal=DATE_ADD(_tanggal, INTERVAL 1 DAY);
        END WHILE;
    ELSE
        SIGNAL SQLSTATE '90000' SET MESSAGE_TEXT = 'Forbidden, more than 365 days';
    END IF;
END //

DROP PROCEDURE IF EXISTS hitungrekapabsen_pertanggal_pakaifilter//
CREATE PROCEDURE hitungrekapabsen_pertanggal_pakaifilter(IN _tanggalawal DATE, IN _tanggalakhir DATE, IN _atribut TEXT, IN _jamkerja TEXT)
BEGIN
	-- cara panggil:
	-- CALL hitungrekapabsen_pertanggal_pakaifilter('2017-08-25', '2017-08-25', '39,68', '69,60,61');

    DECLARE done INT DEFAULT FALSE;
    DECLARE _idpegawai INT;
    DECLARE _lakukan_posting VARCHAR(1);
    DECLARE _tanggal DATE;
    DECLARE cur_pegawai CURSOR FOR
        SELECT
            id,
            IF(((status='a' AND (ISNULL(tanggaltdkaktif)=true OR (ISNULL(tanggaltdkaktif)=false AND tanggalaktif<=_tanggal))) OR (status='t' AND ISNULL(tanggaltdkaktif)=false AND tanggaltdkaktif>_tanggal))=true,'1','0') as lakukan_posting
        FROM
            pegawai
        WHERE
            tanggalaktif<=_tanggal AND
            (ISNULL(_atribut)=true OR id IN (SELECT idpegawai FROM pegawaiatribut WHERE INSTR(CAST(_atribut AS BINARY), CONCAT(',',idatributnilai,','))>0 )) AND
            (ISNULL(_jamkerja)=true OR INSTR(CAST(_jamkerja AS BINARY), CONCAT(',',getpegawaijamkerja(id,'id',CURRENT_DATE()),','))>0);
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET _tanggalakhir = IFNULL(_tanggalakhir, _tanggalawal);
    SET _atribut = CONCAT(',',_atribut,',');
    SET _jamkerja = CONCAT(',',_jamkerja,',');

    IF DATEDIFF(_tanggalakhir, _tanggalawal)<=31 THEN
        SET _tanggal = _tanggalawal;
        WHILE(_tanggal <= _tanggalakhir AND _tanggal <= CURRENT_DATE()) DO
            OPEN cur_pegawai;
            pegawai_loop: LOOP
                SET done=false;
                FETCH cur_pegawai INTO _idpegawai, _lakukan_posting;
                IF done THEN
                    LEAVE pegawai_loop;
                ELSE
                    DELETE FROM rekapabsen WHERE tanggal=_tanggal AND idpegawai=_idpegawai;
                    IF _lakukan_posting='1' THEN
                        CALL posting(_tanggal, _idpegawai, 't');
                    END IF;
                END IF;
            END LOOP pegawai_loop;
            CLOSE cur_pegawai;

            SET _tanggal=DATE_ADD(_tanggal, INTERVAL 1 DAY);
        END WHILE;
    ELSE
        SIGNAL SQLSTATE '90000' SET MESSAGE_TEXT = 'Forbidden, more than 31 days';
    END IF;
END //

DROP PROCEDURE IF EXISTS hitungrekapabsen_perpegawai_pertanggal//
CREATE PROCEDURE hitungrekapabsen_perpegawai_pertanggal(IN _idpegawai INT , IN _tanggalawal DATE, IN _tanggalakhir DATE)
BEGIN
    DECLARE _tanggal DATE;

    SET _tanggalakhir = IFNULL(_tanggalakhir, _tanggalawal);

    IF DATEDIFF(_tanggalakhir, _tanggalawal)<=31 THEN
        SET _tanggal = _tanggalawal;
        WHILE(_tanggal <= _tanggalakhir AND _tanggal <= CURRENT_DATE()) DO
            CALL posting(_idpegawai, _tanggal, 't');
            SET _tanggal=DATE_ADD(_tanggal, INTERVAL 1 DAY);
        END WHILE;
    ELSE
        SIGNAL SQLSTATE '90000' SET MESSAGE_TEXT = 'Forbidden, more than 31 days';
    END IF;
END //

DROP PROCEDURE IF EXISTS buatperingkatabsen//
CREATE PROCEDURE buatperingkatabsen(_tanggal DATE)
BEGIN
    DECLARE _sql_orderby VARCHAR(512) DEFAULT '';

    SELECT 
        GROUP_CONCAT(CONCAT(nama,' ',`order`) ORDER BY urutan ASC SEPARATOR ',') as order01 INTO _sql_orderby
    FROM
        pengaturan_peringkat
    WHERE
        dipakai='y'
    GROUP BY 
        dipakai;

    IF (_sql_orderby <> '') THEN
        SET _sql_orderby =  CONCAT(' ORDER BY ', _sql_orderby);
    END IF;

    DELETE FROM _peringkatabsen;
    SET @_peringkat = 0;
    SET @stmt_text=CONCAT('
        INSERT INTO _peringkatabsen
            SELECT
                idpegawai,
                @_peringkat:=@_peringkat+1,
                masukkerja0,
                lamakerja0,
                terlambat0,
                terlambatlama0,
                pulangawal0,
                pulangawallama0,
                lamalembur0,
                NOW() as inserted0
            FROM
            (
                SELECT
                    ra.idpegawai,
                    SUM(IF(ISNULL(ra.idalasantidakmasuk)=FALSE AND atm.kategori="i",0,IF(ra.masukkerja="y",1,0))) as masukkerja0,
                    SUM(IF(ra.masukkerja="y" AND (ISNULL(ra.idalasantidakmasuk)=true OR atm.kategori NOT IN ("i","d")),1,0)) as orderby_masukkerja,
                    SUM(IF(ra.masukkerja="y" AND ISNULL(ra.idalasantidakmasuk)=false AND atm.kategori = "d",1,0)) as orderby_masukkerja_d,
                    SUM(IF(ra.masukkerja="y" AND ISNULL(ra.idalasantidakmasuk)=false AND atm.kategori = "i",1,0)) as orderby_masukkerja_i,
                    SUM(IF(ISNULL(ra.idalasantidakmasuk)=FALSE AND atm.kategori="i",0,IFNULL(ra.lamakerja,0))) as lamakerja0,
                    SUM(IF(ra.selisihmasuk<0,1,0)) as terlambat0,
                    -1*SUM(IF(ra.selisihmasuk<0,ra.selisihmasuk,0)) as terlambatlama0,
                    SUM(IF(ra.selisihkeluar<0,1,0)) as pulangawal0,
                    -1*SUM(IF(ra.selisihkeluar<0,ra.selisihkeluar,0)) as pulangawallama0,
                    SUM(IFNULL(ra.lamalembur,0)) as lamalembur0
                FROM
                    rekapabsen ra
                    LEFT JOIN alasantidakmasuk atm ON atm.id=ra.idalasantidakmasuk
                WHERE
                    (ra.tanggal BETWEEN DATE_FORMAT("',_tanggal,'", "%Y-%m-01") AND "',_tanggal,'")
                GROUP BY
                    ra.idpegawai
                ',_sql_orderby,'
            ) x;        
                          ');
    PREPARE stmt FROM @stmt_text;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //

-- CALL buatperingkatabsen(CURRENT_DATE())//

DROP PROCEDURE IF EXISTS posting_end_of_day//
CREATE PROCEDURE posting_end_of_day()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _tanggal DATE DEFAULT DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY);
    # DECLARE _tanggal DATE DEFAULT "2016-06-11";
    DECLARE _issudahposting INT DEFAULT 0;
    DECLARE _batas_konfirmasi_absen INT UNSIGNED;
    DECLARE _default_konfirmasi_absen ENUM('v','na');
    DECLARE _end_of_day TIME;
    DECLARE _idpegawai INT UNSIGNED;
    DECLARE _idlogabsen INT UNSIGNED;
    DECLARE _waktu DATETIME;
    DECLARE _temp INT UNSIGNED;

    DECLARE cur_logabsen CURSOR FOR
        SELECT
            id as idlogabsen,
            idpegawai,
            waktu
        FROM
            logabsen
        WHERE
            status='c' AND
            TIMESTAMPDIFF(DAY, waktu, NOW())>=_batas_konfirmasi_absen
        ORDER BY
            waktu ASC;

    DECLARE cur_ijintidakmasuk CURSOR FOR
        SELECT
            idpegawai
        FROM
            ijintidakmasuk
        WHERE
            (_tanggal BETWEEN tanggalawal AND tanggalakhir) AND
            status='a'
        ORDER BY
            inserted ASC;

    DECLARE cur_pegawai CURSOR FOR
        SELECT
            pg.id,
            ra.id as idrekapabsen
        FROM
            pegawai pg
            LEFT JOIN rekapabsen ra ON ra.idpegawai=pg.id AND ra.tanggal=_tanggal
        WHERE
            pg.tanggalaktif<=_tanggal AND
            pg.del='t' AND
            ((pg.status='a' AND (ISNULL(pg.tanggaltdkaktif)=true OR (ISNULL(pg.tanggaltdkaktif)=false AND pg.tanggalaktif<=_tanggal))) OR (pg.status='t' AND ISNULL(pg.tanggaltdkaktif)=false AND pg.tanggaltdkaktif>_tanggal))
        HAVING
            ISNULL(ra.id)=true;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    # cek apakah hari ini sudah posting?
    SET _issudahposting = 0;
    SELECT COUNT(*) INTO _issudahposting FROM _postingabsen WHERE tanggal=_tanggal;
    IF _issudahposting = 0 THEN
        DELETE FROM _postingabsen WHERE tanggal<_tanggal;
        DELETE FROM _logpegawai WHERE TIMESTAMPDIFF(DAY, waktu, NOW())>90;

        # ambil nilai dari pengaturan
        SELECT
            batas_konfirmasi_absen, default_konfirmasi_absen, end_of_day INTO
            _batas_konfirmasi_absen, _default_konfirmasi_absen, _end_of_day
        FROM
            pengaturan;

        IF _end_of_day<=CURRENT_TIME() THEN
            INSERT INTO _postingabsen VALUES(_tanggal, '', NOW()) ON DUPLICATE KEY UPDATE inserted=NOW();

            # set default status konfirmasi jika sudah lebih dari batas
            OPEN cur_logabsen;
            logabsen_loop: LOOP
                SET done=false;
                FETCH cur_logabsen INTO _idlogabsen, _idpegawai, _waktu;
                IF done THEN
                    LEAVE  logabsen_loop;
                ELSE
                    # posting satu per satu
                    UPDATE logabsen SET status= _default_konfirmasi_absen WHERE id=_idlogabsen;
                    CALL hitungrekapabsen_log(NULL, _idpegawai, _waktu);
                END IF;
            END LOOP  logabsen_loop;
            CLOSE cur_logabsen;

            # posting absen yang ijin tidak masuk
            OPEN cur_ijintidakmasuk;
            ijintidakmasuk_loop: LOOP
                SET done=false;
                FETCH cur_ijintidakmasuk INTO _idpegawai;
                IF done THEN
                    LEAVE  ijintidakmasuk_loop;
                ELSE
                    CALL posting(_tanggal, _idpegawai, 't');
                END IF;
            END LOOP ijintidakmasuk_loop;
            CLOSE cur_ijintidakmasuk;

            # posting absen yang belum di posting pada tanggal sebelumnya
            OPEN cur_pegawai;
            pegawai_loop: LOOP
                SET done=false;
                FETCH cur_pegawai INTO _idpegawai, _temp;
                IF done THEN
                    LEAVE pegawai_loop;
                ELSE
                    CALL posting(_tanggal, _idpegawai, 't');
                END IF;
            END LOOP pegawai_loop;
            CLOSE cur_pegawai;

            CALL buatperingkatabsen(_tanggal);
        END IF;
    END IF;
END//

DROP EVENT IF EXISTS posting_end_of_day_event//
CREATE EVENT posting_end_of_day_event ON SCHEDULE EVERY 15 MINUTE DO
BEGIN
    CALL posting_end_of_day();
END//

DROP PROCEDURE IF EXISTS generategrafikabsen//
CREATE PROCEDURE generategrafikabsen(IN _tanggalawal DATE, IN _tanggalakhir DATE)
BEGIN
    DECLARE _tanggal DATE;

    DROP TEMPORARY TABLE IF EXISTS _grafikabsen;
    CREATE TEMPORARY TABLE _grafikabsen (
        `tanggal`       DATE,
        `jadwal_masuk`  INT UNSIGNED,
        `jum_masuk`     INT UNSIGNED,
        `jum_tdk_masuk` INT UNSIGNED,
        `jum_terlambat` INT UNSIGNED,
        INDEX `idx__grafikabsen_tanggal` (`tanggal`)
        ) ENGINE=Memory;
    TRUNCATE _grafikabsen;
    IF ISNULL(_tanggalawal)=false AND ISNULL(_tanggalakhir)=false AND _tanggalawal<=_tanggalakhir THEN
        SET _tanggal = _tanggalawal;
        WHILE(_tanggal <= _tanggalakhir) DO
            INSERT INTO _grafikabsen VALUES (_tanggal, 0, 0, 0, 0);
            SET _tanggal=DATE_ADD(_tanggal, INTERVAL 1 DAY);
        END WHILE;

        UPDATE
            _grafikabsen ga,
            (
            SELECT
                ra.tanggal,
                SUM(IF(ra.jadwalmasukkerja='y' OR ra.masukkerja='y',1,0)) as jadwal_masuk,
                SUM(IF(ra.masukkerja='y',1,0)) as jum_masuk,
                SUM(IF(ra.masukkerja='t',1,0)) as jum_tdk_masuk,
                SUM(IF(ra.selisihmasuk<0,1,0)) as jum_terlambat
            FROM
                rekapabsen ra
            WHERE
                ra.tanggal BETWEEN _tanggalawal AND _tanggalakhir
            GROUP BY ra.tanggal
            ) x
        SET
            ga.jadwal_masuk=x.jadwal_masuk,
            ga.jum_masuk=x.jum_masuk,
            ga.jum_tdk_masuk=x.jum_tdk_masuk,
            ga.jum_terlambat=x.jum_terlambat
        WHERE
            ga.tanggal=x.tanggal;
    END IF;
END//

DROP PROCEDURE IF EXISTS generategrafikabsen_email//
CREATE PROCEDURE generategrafikabsen_email(IN _tanggalawal DATE, IN _tanggalakhir DATE, IN _email VARCHAR(255))
BEGIN
    DECLARE _tanggal DATE;
    DECLARE _jumlah_batasanatributnilai INT DEFAULT 0;

    DROP TEMPORARY TABLE IF EXISTS _grafikabsen;
    CREATE TEMPORARY TABLE _grafikabsen (
        `tanggal`       DATE,
        `jadwal_masuk`  INT UNSIGNED,
        `jum_masuk`     INT UNSIGNED,
        `jum_tdk_masuk` INT UNSIGNED,
        `jum_terlambat` INT UNSIGNED,
        INDEX `idx__grafikabsen_tanggal` (`tanggal`)
        ) ENGINE=Memory;
    TRUNCATE _grafikabsen;

    CREATE TEMPORARY TABLE IF NOT EXISTS _batasanatributnilai (
        `idatributnilai`  INT UNSIGNED,
        INDEX `idx__batasanatributnilai_idatributnilai` (`idatributnilai`)
        ) ENGINE=Memory;
    TRUNCATE _batasanatributnilai;

    INSERT INTO _batasanatributnilai SELECT idatributnilai FROM batasanemail be, batasanatribut ba WHERE be.idbatasan=ba.idbatasan AND be.email=_email;

    SELECT COUNT(*) INTO _jumlah_batasanatributnilai FROM _batasanatributnilai;

    IF ISNULL(_tanggalawal)=false AND ISNULL(_tanggalakhir)=false AND _tanggalawal<=_tanggalakhir THEN
        SET _tanggal = _tanggalawal;
        WHILE(_tanggal <= _tanggalakhir) DO
            INSERT INTO _grafikabsen VALUES (_tanggal, 0, 0, 0, 0);
            SET _tanggal=DATE_ADD(_tanggal, INTERVAL 1 DAY);
        END WHILE;

        IF (_jumlah_batasanatributnilai=0) THEN
            UPDATE
                _grafikabsen ga,
                (
                SELECT
                    ra.tanggal,
                    SUM(IF(ra.jadwalmasukkerja='y' OR ra.masukkerja='y',1,0)) as jadwal_masuk,
                    SUM(IF(ra.masukkerja='y',1,0)) as jum_masuk,
                    SUM(IF(ra.jadwalmasukkerja='y' AND ra.masukkerja='t',1,0)) as jum_tdk_masuk,
                    SUM(IF(ra.selisihmasuk<0,1,0)) as jum_terlambat
                FROM
                    rekapabsen ra
                WHERE
                    ra.tanggal BETWEEN _tanggalawal AND _tanggalakhir
                GROUP BY ra.tanggal
                ) x
            SET
                ga.jadwal_masuk=x.jadwal_masuk,
                ga.jum_masuk=x.jum_masuk,
                ga.jum_tdk_masuk=x.jum_tdk_masuk,
                ga.jum_terlambat=x.jum_terlambat
            WHERE
                ga.tanggal=x.tanggal;
        ELSE
            UPDATE
                _grafikabsen ga,
                (
                SELECT
                    ra.tanggal,
                    SUM(IF(ra.jadwalmasukkerja='y' OR ra.masukkerja='y',1,0)) as jadwal_masuk,
                    SUM(IF(ra.masukkerja='y',1,0)) as jum_masuk,
                    SUM(IF(ra.jadwalmasukkerja='y' AND ra.masukkerja='t',1,0)) as jum_tdk_masuk,
                    SUM(IF(ra.selisihmasuk<0,1,0)) as jum_terlambat
                FROM
                    rekapabsen ra
                WHERE
                    ra.tanggal BETWEEN _tanggalawal AND _tanggalakhir AND
                    ra.idpegawai IN (
                        SELECT 
                            DISTINCT(p.id)
                        FROM
                            pegawai p,
                            pegawaiatribut pa,
                            _batasanatributnilai ban
                        WHERE
                            p.del='t' AND
                            ((p.status='a' AND (ISNULL(p.tanggaltdkaktif)=true OR (ISNULL(p.tanggaltdkaktif)=false AND p.tanggalaktif<=_tanggal))) OR (p.status='t' AND ISNULL(p.tanggaltdkaktif)=false AND p.tanggaltdkaktif>_tanggal)) AND
                            p.id=pa.idpegawai AND
                            pa.idatributnilai=ban.idatributnilai
                        )
                GROUP BY ra.tanggal
                ) x
            SET
                ga.jadwal_masuk=x.jadwal_masuk,
                ga.jum_masuk=x.jum_masuk,
                ga.jum_tdk_masuk=x.jum_tdk_masuk,
                ga.jum_terlambat=x.jum_terlambat
            WHERE
                ga.tanggal=x.tanggal;
        END IF;
    END IF;
END//

DROP PROCEDURE IF EXISTS generategrafikabsen_perpegawai//
CREATE PROCEDURE generategrafikabsen_perpegawai(IN _idpegawai INT, IN _tanggalawal DATE, IN _tanggalakhir DATE)
BEGIN
    DECLARE _tanggal DATE;

    CREATE TEMPORARY TABLE IF NOT EXISTS _grafikabsen_perpegawai (
        `tanggal`       DATE,
        `hari_libur`    ENUM('y','t') NOT NULL,
        `waktu_masuk`   DATETIME,
        `total_lama`    INT UNSIGNED,
        `lama_kerja`    INT UNSIGNED,
        `lama_lembur`   INT UNSIGNED,
        `terlambat`     INT UNSIGNED,
        INDEX `idx__grafikabsen_perpegawai_tanggal` (`tanggal`)
        ) ENGINE=Memory;
    TRUNCATE _grafikabsen_perpegawai;
    IF ISNULL(_idpegawai)=false AND ISNULL(_tanggalawal)=false AND ISNULL(_tanggalakhir)=false AND _tanggalawal<=_tanggalakhir THEN
        SET _tanggal = _tanggalawal;
        WHILE(_tanggal <= _tanggalakhir) DO
            INSERT INTO _grafikabsen_perpegawai VALUES (_tanggal, 'y', null, 0, 0, 0, 0);
            SET _tanggal=DATE_ADD(_tanggal, INTERVAL 1 DAY);
        END WHILE;

        UPDATE
            _grafikabsen_perpegawai ga,
            (
            SELECT
                ra.tanggal,
                IF(ra.jadwalmasukkerja='y','t','y') as hari_libur,
                ra.waktumasuk as waktu_masuk,
                ROUND((ra.lamakerja+ra.lamalembur) / 3600, 2) as total_lama,
                ROUND(ra.lamakerja / 3600, 2) as lama_kerja,
                ROUND(ra.lamalembur / 3600, 2) as lama_lembur,
                ROUND(IF(ra.selisihmasuk<0,-1*ra.selisihmasuk,0) / 3600, 2) as terlambat
            FROM
                rekapabsen ra
            WHERE
                ra.idpegawai=_idpegawai AND
                ra.tanggal BETWEEN _tanggalawal AND _tanggalakhir
            GROUP BY ra.tanggal
            ) x
        SET
            ga.hari_libur=x.hari_libur,
            ga.waktu_masuk=x.waktu_masuk,
            ga.total_lama=x.total_lama,
            ga.lama_kerja=x.lama_kerja,
            ga.lama_lembur=x.lama_lembur,
            ga.terlambat=x.terlambat
        WHERE
            ga.tanggal=x.tanggal;
    END IF;
END//

# _ygditampilkan = id | nama | jenis | digunakan
DROP FUNCTION IF EXISTS getpegawaijamkerja//
CREATE FUNCTION getpegawaijamkerja(_idpegawai INT, _ygditampilkan VARCHAR(10), _tanggal DATE) RETURNS VARCHAR(100)
BEGIN
    DECLARE _hasil VARCHAR(100) DEFAULT '';
    DECLARE _id INT DEFAULT NULL;
    DECLARE _nama VARCHAR(100) DEFAULT '';
    DECLARE _jenis VARCHAR(100) DEFAULT NULL;
    DECLARE _digunakan VARCHAR(100) DEFAULT NULL;
    SELECT
        jk.id, jk.nama, jk.jenis, jk.digunakan INTO
        _id, _nama, _jenis, _digunakan
    FROM
        pegawaijamkerja pjk,
        jamkerja jk
    WHERE
        jk.id=pjk.idjamkerja AND
        pjk.idpegawai=_idpegawai AND
        pjk.berlakumulai<=_tanggal
    ORDER BY
        pjk.berlakumulai DESC
    LIMIT 1
    ;

    CASE _ygditampilkan
        WHEN 'id' THEN SET _hasil = _id;
        WHEN 'nama' THEN SET _hasil = _nama;
        WHEN 'jenis' THEN SET _hasil = _jenis;
        WHEN 'digunakan' THEN SET _hasil = _digunakan;
    END CASE;
    RETURN _hasil;
END //

DROP FUNCTION IF EXISTS getatributtampilpadaringkasan//
CREATE FUNCTION getatributtampilpadaringkasan(_idpegawai INT) RETURNS VARCHAR(100)
BEGIN
    DECLARE _atribut VARCHAR(100) DEFAULT '';
    SELECT
        GROUP_CONCAT(DISTINCT an.nilai ORDER BY a.atribut SEPARATOR '|') INTO _atribut
    FROM
        pegawaiatribut pa,
        atributnilai an,
        atribut a
    WHERE
        pa.idatributnilai=an.id AND
        an.idatribut=a.id AND
        pa.idpegawai=_idpegawai AND
        a.tampilpadaringkasan='y'
    ;

    RETURN IFNULL(_atribut,'');
END //

DROP FUNCTION IF EXISTS getatributpegawai//
CREATE FUNCTION getatributpegawai(_idpegawai INT, _idatribut INT) RETURNS VARCHAR(100)
BEGIN
    DECLARE _atributpenting VARCHAR(100) DEFAULT '';
    SELECT
        GROUP_CONCAT(an.nilai SEPARATOR ',') INTO _atributpenting
    FROM
        pegawaiatribut pa,
        atributnilai an
    WHERE
        pa.idatributnilai=an.id AND
        an.idatribut=_idatribut AND
        pa.idpegawai=_idpegawai
    LIMIT 1;

    RETURN IFNULL(_atributpenting,'');
END //

DROP FUNCTION IF EXISTS getatributvariablepegawai//
CREATE FUNCTION getatributvariablepegawai(_idpegawai INT, _idatributvariable INT) RETURNS VARCHAR(100)
BEGIN
    DECLARE _atributvariable VARCHAR(100) DEFAULT '';
    SELECT
        variable INTO _atributvariable
    FROM
        pegawaiatributvariable
    WHERE
        idatributvariable=_idatributvariable AND
        idpegawai=_idpegawai
    LIMIT 1;

    RETURN IFNULL(_atributvariable,'');
END //

DROP PROCEDURE IF EXISTS getpegawailengkap_blade//
CREATE PROCEDURE getpegawailengkap_blade(OUT _atributpenting_controller TEXT, OUT _atributpenting_blade TEXT, OUT _atributvariablepenting_controller TEXT, OUT _atributvariablepenting_blade TEXT)
BEGIN
    SET _atributpenting_controller='';
    SET _atributpenting_blade='';
    SET _atributvariablepenting_controller='';
    SET _atributvariablepenting_blade='';

    SELECT
        IFNULL(GROUP_CONCAT(CONCAT('_a_',id) ORDER BY atribut,id ASC SEPARATOR '|'),'') as atributpenting_controller,
        IFNULL(GROUP_CONCAT(REPLACE(atribut,'|','') ORDER BY atribut,id ASC SEPARATOR '|'),'') as atributpenting_blade
        INTO
        _atributpenting_controller,
        _atributpenting_blade
    FROM atribut WHERE penting='y';

    SELECT
        IFNULL(GROUP_CONCAT(CONCAT('_av_',id) ORDER BY atribut,id ASC SEPARATOR '|'),'') as atributvariablepenting_controller,
        IFNULL(GROUP_CONCAT(REPLACE(atribut,'|','') ORDER BY atribut,id ASC SEPARATOR '|'),'') as atributvariablepenting_blade
        INTO
        _atributvariablepenting_controller,
        _atributvariablepenting_blade
    FROM atributvariable WHERE penting='y';
END//

# output berupa temporary table _pegawailengkap berisi pegawai dgn semua atribut/atributvariable penting
DROP PROCEDURE IF EXISTS getpegawailengkap_controller//
CREATE PROCEDURE getpegawailengkap_controller(OUT _atributpenting TEXT, OUT _atributvariablepenting TEXT, IN _where TEXT)
BEGIN
    DECLARE _atributpenting_sqlcreate TEXT DEFAULT '';
    DECLARE _atributpenting_sqlinsert TEXT DEFAULT '';
    DECLARE _atributvariablepenting_sqlcreate TEXT DEFAULT '';
    DECLARE _atributvariablepenting_sqlinsert TEXT DEFAULT '';

    SET _atributpenting='';
    SET _atributvariablepenting='';

    SELECT
        IFNULL(GROUP_CONCAT(CONCAT('`_a_',id,'` VARCHAR(100) NOT NULL,') ORDER BY atribut,id ASC SEPARATOR ' '),'') as atributpenting_sqlcreate,
        IFNULL(GROUP_CONCAT(CONCAT(',getatributpegawai(id, ',id,')') ORDER BY atribut,id ASC SEPARATOR ' '),'') as atributpenting_sqlinsert,
        IFNULL(GROUP_CONCAT(CONCAT(',_a_',id) ORDER BY atribut,id ASC SEPARATOR ''),'') as atributpenting
        INTO
        _atributpenting_sqlcreate,
        _atributpenting_sqlinsert,
        _atributpenting
    FROM atribut WHERE penting='y';

    SELECT
        IFNULL(GROUP_CONCAT(CONCAT('`_av_',id,'` VARCHAR(100) NOT NULL,') ORDER BY atribut,id ASC SEPARATOR ' '),'') as atributvariablepenting_sqlcreate,
        IFNULL(GROUP_CONCAT(CONCAT(',getatributvariablepegawai(id, ',id,')') ORDER BY atribut,id ASC SEPARATOR ' '),'') as atributvariablepenting_sqlinsert,
        IFNULL(GROUP_CONCAT(CONCAT('_av_',id,',') ORDER BY atribut,id ASC SEPARATOR ''),'') as atributvariablepenting
        INTO
        _atributvariablepenting_sqlcreate,
        _atributvariablepenting_sqlinsert,
        _atributvariablepenting
    FROM atributvariable WHERE penting='y';

    DROP TEMPORARY TABLE IF EXISTS _pegawailengkap;

    SET @stmt_text=CONCAT('
                            CREATE TEMPORARY TABLE _pegawailengkap
                            (
                                `id`    INT UNSIGNED NOT NULL,
                                ',_atributpenting_sqlcreate,'
                                ',_atributvariablepenting_sqlcreate,'
                                PRIMARY KEY (`id`)
                            ) ENGINE=Memory;
                          ');
    PREPARE stmt FROM @stmt_text;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET @stmt_text=CONCAT('
                            INSERT INTO _pegawailengkap
                                SELECT
                                    id
                                    ',_atributpenting_sqlinsert,'
                                    ',_atributvariablepenting_sqlinsert,'
                                FROM
                                    pegawai
                                WHERE
                                    del="t"
                                ',_where,'
                          ');
    PREPARE stmt FROM @stmt_text;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END//

DROP FUNCTION IF EXISTS `sec2hour`//
CREATE FUNCTION `sec2hour`(input int) RETURNS varchar(30)
BEGIN
  DECLARE _hasil VARCHAR(30) DEFAULT '';
  SET input = FLOOR(input / 60);
  SELECT CONCAT(FLOOR(input/60),CONCAT(':',LPAD(input MOD 60,2,'0'))) INTO _hasil;
  RETURN IFNULL(_hasil,'');
END //

# dapatan daftar pegawai yang jamkerja adalah shift pada periode yymm
# output berupa temporary table _pegawai
DROP PROCEDURE IF EXISTS pegawaishiftyymm
//
CREATE PROCEDURE pegawaishiftyymm(IN _yymm VARCHAR(4))
BEGIN
    DECLARE _tglawal DATE DEFAULT NULL;

    SET _tglawal = STR_TO_DATE(CONCAT('20',LEFT(_yymm,2),'-',RIGHT(_yymm,2),'-01'),'%Y-%m-%d');

    DROP TEMPORARY TABLE IF EXISTS _pegawai;
    CREATE TEMPORARY TABLE _pegawai
    (
        `id`    INT(11) UNSIGNED NOT NULL,
        INDEX `idx__pegawaijamkerja_id` (`id`)
    ) Engine=MEMORY;

    INSERT INTO _pegawai
        (
        SELECT
            pjk.idpegawai
        FROM
            pegawai p,
            jamkerja jk,
            pegawaijamkerja pjk
        WHERE
            p.id=pjk.idpegawai AND
            p.del="t" AND
            p.tanggalaktif<=LAST_DAY(_tglawal) AND
            (ISNULL(p.tanggaltdkaktif)=true OR p.tanggaltdkaktif>_tglawal) AND
            jk.id=pjk.idjamkerja AND
            jk.jenis='shift' AND
            pjk.berlakumulai BETWEEN _tglawal AND LAST_DAY(_tglawal)
        )
        UNION
        (
            SELECT
                pjk.idpegawai
            FROM
                pegawai p,
                jamkerja jk,
                pegawaijamkerja pjk,
                (
                    SELECT
                        pjk.idpegawai, MAX(pjk.berlakumulai) as berlakumulai
                    FROM
                        pegawaijamkerja pjk
                    WHERE
                        pjk.berlakumulai < _tglawal
                    GROUP BY
                        pjk.idpegawai
                ) x
            WHERE
                p.id=pjk.idpegawai AND
                p.del="t" AND
                p.tanggalaktif<=LAST_DAY(_tglawal) AND
                (ISNULL(p.tanggaltdkaktif)=true OR p.tanggaltdkaktif>_tglawal) AND
                jk.id=pjk.idjamkerja AND
                pjk.idpegawai=x.idpegawai AND
                pjk.berlakumulai=x.berlakumulai AND
                jk.jenis='shift' AND
                pjk.berlakumulai < _tglawal
        );
END //

# dapatan daftar jamkerja tanggal 1,2,3...31 sesuai dgn yymmdd (dd boleh dipakai, boleh tidak)
# output berupa temporary table _jadwalshift
DROP PROCEDURE IF EXISTS pegawaishiftpertanggal
//
CREATE PROCEDURE pegawaishiftpertanggal(IN _idpegawai INT, IN _yymmdd VARCHAR(6))
BEGIN
    DECLARE done INT DEFAULT FALSE;

    DECLARE _tglawal DATE DEFAULT NULL;
    DECLARE _tglberjalan DATE DEFAULT NULL;
    DECLARE _jumlahhari INT DEFAULT 0;
    DECLARE _i INT DEFAULT 0;
    DECLARE _idjamkerja INT DEFAULT NULL;
    DECLARE _nama VARCHAR(100) DEFAULT '';
    DECLARE _jenis VARCHAR(5) DEFAULT '';

    DECLARE _id INT UNSIGNED DEFAULT NULL;
    DECLARE _tanggalawal DATE DEFAULT NULL;
    DECLARE _tanggalakhir DATE DEFAULT NULL;
    DECLARE _alasan VARCHAR(100) DEFAULT NULL;
    DECLARE _keterangan TEXT DEFAULT NULL;
    DECLARE _is_libur INT DEFAULT 0;
    DECLARE _ada INT DEFAULT 0;

    DECLARE cur_harilibur CURSOR FOR
        SELECT
            id, tanggalawal, tanggalakhir
        FROM
            _harilibur;

    DECLARE cur_ijintikdamasuk CURSOR FOR
        SELECT
            itm.id, atm.alasan, itm.keterangan, itm.tanggalawal, itm.tanggalakhir
        FROM
            ijintidakmasuk itm
            LEFT JOIN alasantidakmasuk atm ON itm.idalasantidakmasuk=atm.id
        WHERE
            itm.idpegawai=_idpegawai AND
            (
                (_tglawal BETWEEN itm.tanggalawal AND itm.tanggalakhir) OR
                (itm.tanggalawal BETWEEN _tglawal AND LAST_DAY(_tglawal))
            );

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    IF LENGTH(_yymmdd)=6 THEN
        SET _tglawal = STR_TO_DATE(_yymmdd,'%y%m%d');
        SET _jumlahhari = 1;
    ELSE
        SET _tglawal = STR_TO_DATE(CONCAT('20',LEFT(_yymmdd,2),'-',RIGHT(_yymmdd,2),'-01'),'%Y-%m-%d');
        SET _jumlahhari = DAY(LAST_DAY(_tglawal));
    END IF;

    DROP TEMPORARY TABLE IF EXISTS _pegawaijamkerja;
    CREATE TEMPORARY TABLE _pegawaijamkerja
    (
        `idjamkerja`    INT(11) UNSIGNED NOT NULL,
        `nama`          VARCHAR(100) NOT NULL,
        `jenis`         ENUM('','full','shift') NOT NULL,
        `berlakumulai`  DATE NOT NULL
    ) Engine=MEMORY;
    TRUNCATE _pegawaijamkerja;
    INSERT INTO _pegawaijamkerja
        SELECT
            pjk.idjamkerja,
            jk.nama,
            jk.jenis,
            pjk.berlakumulai
        FROM
            jamkerja jk,
            pegawaijamkerja pjk
        WHERE
            jk.id=pjk.idjamkerja AND
            pjk.idpegawai=_idpegawai AND
            pjk.berlakumulai<=LAST_DAY(_tglawal)
        ORDER BY
            pjk.berlakumulai DESC
        LIMIT 31;

    DROP TEMPORARY TABLE IF EXISTS _jadwalshift;
    CREATE TEMPORARY TABLE _jadwalshift
    (
        `tanggal`               DATE NOT NULL,
        `dayinweek`             INT NOT NULL,
        `idjamkerja`            INT(11) UNSIGNED,
        `nama`                  VARCHAR(100) NOT NULL,
        `jenis`                 ENUM('','full','shift') NOT NULL,
        `harilibur`             ENUM('y','t') NOT NULL,
        `idijintidakmasuk`      INT(11) UNSIGNED,
        `alasantidakmasuk`      VARCHAR(100),
        `keterangantidakmasuk`  VARCHAR(512)
    ) Engine=MEMORY;
    TRUNCATE _jadwalshift;

    SET _i=0;
    SET _tglberjalan = _tglawal;
    WHILE _i<_jumlahhari DO
        SET _idjamkerja = NULL;
        SET _nama = '';
        SET _jenis = '';

        SELECT
            idjamkerja, nama, jenis INTO _idjamkerja, _nama, _jenis
        FROM
            _pegawaijamkerja
        WHERE
            berlakumulai<=_tglberjalan
        ORDER BY
            berlakumulai DESC
        LIMIT 1;

        INSERT INTO _jadwalshift VALUES(_tglberjalan, DAYOFWEEK(_tglberjalan), _idjamkerja, _nama, _jenis, 't', NULL, NULL, NULL);

        SET _tglberjalan = DATE_ADD(_tglberjalan, INTERVAL 1 DAY);
        SET _i = _i + 1;
    END WHILE;

    # update untuk kolom harilibur='y'
    DROP TEMPORARY TABLE IF EXISTS _harilibur;
    CREATE TEMPORARY TABLE _harilibur
    (
        `id`            INT(11) UNSIGNED NOT NULL,
        `tanggalawal`   DATE NOT NULL,
        `tanggalakhir`  DATE NOT NULL,
        PRIMARY KEY (`id`)
    ) Engine=MEMORY;
    TRUNCATE _harilibur;

    INSERT INTO _harilibur
        SELECT
            id,
            tanggalawal,
            tanggalakhir
        FROM
            harilibur
        WHERE
            (
                (_tglawal BETWEEN tanggalawal AND tanggalakhir) OR
                (tanggalawal BETWEEN _tglawal AND LAST_DAY(_tglawal))
            );

    OPEN cur_harilibur;
    read_loop: LOOP
        SET done=false;
        FETCH cur_harilibur INTO _id, _tanggalawal, _tanggalakhir;
        IF done THEN
            LEAVE read_loop;
        ELSE
            SET _is_libur = 1;
            SET _ada = 0;
            SELECT 1 INTO _ada FROM hariliburatribut WHERE idharilibur=_id LIMIT 1;
            IF (_ada=1) THEN
                SET _ada = 0;
                SELECT
                    1 INTO _ada
                FROM
                    hariliburatribut hat,
                    pegawaiatribut pa
                WHERE
                    pa.idpegawai=_idpegawai AND
                    hat.idharilibur=_id AND
                    hat.idatributnilai=pa.idatributnilai
                LIMIT 1;
                IF (_ada=0) THEN
                    # DELETE FROM _harilibur WHERE id=_id;
                    SET _is_libur = 0;
                END IF;
            END IF;

            IF (_is_libur=1) THEN
                UPDATE _jadwalshift SET harilibur='y' WHERE tanggal BETWEEN _tanggalawal AND _tanggalakhir;
            END IF;
        END IF;
    END LOOP read_loop;
    CLOSE cur_harilibur;

    OPEN cur_ijintikdamasuk;
    read_loop: LOOP
        SET done=false;
        FETCH cur_ijintikdamasuk INTO _id, _alasan, _keterangan, _tanggalawal, _tanggalakhir;
        IF done THEN
            LEAVE read_loop;
        ELSE
            UPDATE _jadwalshift SET idijintidakmasuk=_id, alasantidakmasuk=_alasan, keterangantidakmasuk=LEFT(_keterangan,512) WHERE tanggal BETWEEN _tanggalawal AND _tanggalakhir;
        END IF;
    END LOOP read_loop;
    CLOSE cur_ijintikdamasuk;
END //

# dapatan daftar jamkerja tanggal 1,2,3...31 sesuai dgn yymm dengan kemungkinan apa saja shift yang bisa dipilih
# output berupa temporary table _jadwalshiftkombinasi
DROP PROCEDURE IF EXISTS _cekpegawaishift
//
CREATE PROCEDURE _cekpegawaishift(IN _idpegawai INT, IN _yymm VARCHAR(4))
BEGIN
    DECLARE _tglawal DATE DEFAULT NULL;
    DECLARE _jumlahhari INT DEFAULT 0;
    DECLARE _i INT DEFAULT 0;
    DECLARE _idjamkerja INT DEFAULT NULL;
    DECLARE _nama VARCHAR(100) DEFAULT '';
    DECLARE _jenis VARCHAR(5) DEFAULT '';

    SET _tglawal = STR_TO_DATE(CONCAT('20',LEFT(_yymm,2),'-',RIGHT(_yymm,2),'-01'),'%Y-%m-%d');
    SET _jumlahhari = DAY(LAST_DAY(_tglawal));

    DROP TEMPORARY TABLE IF EXISTS _pegawaijamkerja;
    CREATE TEMPORARY TABLE _pegawaijamkerja
    (
        `idjamkerja`    INT(11) UNSIGNED NOT NULL,
        `nama`          VARCHAR(100) NOT NULL,
        `jenis`         ENUM('','full','shift') NOT NULL,
        `berlakumulai`  DATE NOT NULL
    ) Engine=MEMORY;
    TRUNCATE _pegawaijamkerja;
    INSERT INTO _pegawaijamkerja
        SELECT
            pjk.idjamkerja,
            jk.nama,
            jk.jenis,
            pjk.berlakumulai
        FROM
            jamkerja jk,
            pegawaijamkerja pjk
        WHERE
            jk.id=pjk.idjamkerja AND
            pjk.idpegawai=_idpegawai AND
            pjk.berlakumulai<=LAST_DAY(_tglawal)
        ORDER BY
            pjk.berlakumulai DESC
        LIMIT 31;

    DROP TEMPORARY TABLE IF EXISTS _jadwalshiftkombinasi;
    CREATE TEMPORARY TABLE _jadwalshiftkombinasi
    (
        `tanggal`       DATE NOT NULL,
        `idjamkerja`    INT(11) UNSIGNED,
        `idjamkerjashift`    INT(11) UNSIGNED
    ) Engine=MEMORY;
    TRUNCATE _jadwalshiftkombinasi;

    SET _i=0;
    WHILE _i<_jumlahhari DO
        SET _idjamkerja = NULL;
        SET _nama = '';
        SET _jenis = '';

        SELECT
            idjamkerja, nama, jenis INTO _idjamkerja, _nama, _jenis
        FROM
            _pegawaijamkerja
        WHERE
            berlakumulai<=_tglawal
        ORDER BY
            berlakumulai DESC
        LIMIT 1;

        INSERT INTO _jadwalshiftkombinasi SELECT _tglawal, _idjamkerja, id FROM jamkerjashift WHERE idjamkerja=_idjamkerja;

        SET _tglawal = DATE_ADD(_tglawal, INTERVAL 1 DAY);
        SET _i = _i + 1;
    END WHILE;
END //

DROP FUNCTION IF EXISTS cekpengaruhjadwalshift//
CREATE FUNCTION cekpengaruhjadwalshift(_idpegawaijamkerja INT, _paramidpegawai INT, _menjadi_idjamkerja INT, _menjadi_berlakumulai DATE) RETURNS VARCHAR(1)
BEGIN
    DECLARE _hasil VARCHAR(1) DEFAULT '0'; # 1: bermasalah (berpengaruh), 0: tidak ada masalah
    DECLARE _mode VARCHAR(10) DEFAULT '';
    DECLARE _pegawai_id INT DEFAULT NULL;
    DECLARE _jamkerja_id INT DEFAULT NULL;
    DECLARE _jamkerja_jenis VARCHAR(5) DEFAULT '';
    DECLARE _jamkerja_berlakumulai DATE DEFAULT NULL;
    DECLARE _menjadi_jenis VARCHAR(5) DEFAULT '';
    DECLARE _sebelum_id INT DEFAULT NULL;
    DECLARE _sebelum_jenis VARCHAR(5) DEFAULT '';
    DECLARE _sebelum_berlakumulai DATE DEFAULT NULL;
    DECLARE _sesudah_id INT DEFAULT NULL;
    DECLARE _sesudah_jenis VARCHAR(5) DEFAULT '';
    DECLARE _sesudah_berlakumulai DATE DEFAULT NULL;

    # jika di insert (POST)
    IF (ISNULL(_paramidpegawai)=false) AND (ISNULL(_idpegawaijamkerja)=true) THEN
        IF (ISNULL(_menjadi_idjamkerja)=false AND ISNULL(_menjadi_berlakumulai)=false) THEN
            SET _mode = 'insert';
        END IF;
    ELSE
        # jika di update atau delete (PUT atau DELETE)
        IF (ISNULL(_menjadi_idjamkerja)=false AND ISNULL(_menjadi_berlakumulai)=false) THEN
            SET _mode = 'update';
        ELSE
            SET _mode = 'delete';
        END IF;

    END IF;

    IF (_mode='insert') THEN
        SET _pegawai_id = _paramidpegawai;
        SELECT
            jk.jenis INTO
            _jamkerja_jenis
        FROM
            jamkerja jk
        WHERE
            jk.id=_menjadi_idjamkerja
        LIMIT 1;
    ELSEIF (_mode='update') OR (_mode='delete') THEN
        SELECT
            pjk.idpegawai, pjk.idjamkerja, jk.jenis, pjk.berlakumulai INTO
            _pegawai_id, _jamkerja_id, _jamkerja_jenis, _jamkerja_berlakumulai
        FROM
            pegawaijamkerja pjk,
            jamkerja jk
        WHERE
            pjk.idjamkerja=jk.id AND
            pjk.id=_idpegawaijamkerja
        LIMIT 1;
    END IF;

    IF _jamkerja_jenis='shift' THEN
        IF (_mode='insert') THEN
            SET _sebelum_id = _menjadi_idjamkerja;
            SET _sebelum_jenis = 'shift';
            SET _sebelum_berlakumulai = _menjadi_berlakumulai;

            # cari jam kerja sesudah
            SELECT
                pjk.idjamkerja, jk.jenis, pjk.berlakumulai INTO
                _sesudah_id, _sesudah_jenis, _sesudah_berlakumulai
            FROM
                pegawaijamkerja pjk,
                jamkerja jk
            WHERE
                pjk.idjamkerja=jk.id AND
                pjk.idpegawai=_pegawai_id AND
                pjk.berlakumulai>_menjadi_berlakumulai
            ORDER BY
                pjk.berlakumulai ASC
            LIMIT 1;
        ELSEIF (_mode='update') OR (_mode='delete') THEN
            # cari jam kerja sebelum
            SELECT
                pjk.idjamkerja, jk.jenis, pjk.berlakumulai INTO
                _sebelum_id, _sebelum_jenis, _sebelum_berlakumulai
            FROM
                pegawaijamkerja pjk,
                jamkerja jk
            WHERE
                pjk.idjamkerja=jk.id AND
                pjk.idpegawai=_pegawai_id AND
                pjk.berlakumulai<_jamkerja_berlakumulai
            ORDER BY
                pjk.berlakumulai DESC
            LIMIT 1;

            # cari jam kerja sesudah
            SELECT
                pjk.idjamkerja, jk.jenis, pjk.berlakumulai INTO
                _sesudah_id, _sesudah_jenis, _sesudah_berlakumulai
            FROM
                pegawaijamkerja pjk,
                jamkerja jk
            WHERE
                pjk.idjamkerja=jk.id AND
                pjk.idpegawai=_pegawai_id AND
                pjk.berlakumulai>_jamkerja_berlakumulai
            ORDER BY
                pjk.berlakumulai ASC
            LIMIT 1;
        END IF;

        IF (_mode='update') THEN
            IF ISNULL(_menjadi_idjamkerja)=false THEN
                SELECT jenis INTO _jamkerja_jenis FROM jamkerja WHERE id=_menjadi_idjamkerja;
                SET _menjadi_jenis = _jamkerja_jenis;
            ELSE
                SET _menjadi_idjamkerja = _jamkerja_id;
                SET _menjadi_jenis = _jamkerja_jenis;
            END IF;
            IF ISNULL(_menjadi_berlakumulai)=true THEN
                SET _menjadi_berlakumulai = _jamkerja_berlakumulai;
            END IF;

            IF (_menjadi_berlakumulai<_jamkerja_berlakumulai AND ISNULL(_sebelum_id)=TRUE) OR
               (_menjadi_berlakumulai<_jamkerja_berlakumulai AND _menjadi_berlakumulai>_sebelum_berlakumulai) THEN
               SET _sebelum_id=_menjadi_idjamkerja;
               SET _sebelum_jenis=_menjadi_jenis;
               SET _sebelum_berlakumulai=_menjadi_berlakumulai;
            ELSEIF (_menjadi_berlakumulai>_jamkerja_berlakumulai AND ISNULL(_sesudah_id)=TRUE) OR
               (_menjadi_berlakumulai>_jamkerja_berlakumulai AND _menjadi_berlakumulai<_sesudah_berlakumulai) THEN
               SET _sesudah_id=_menjadi_idjamkerja;
               SET _sesudah_jenis=_menjadi_jenis;
               SET _sesudah_berlakumulai=_menjadi_berlakumulai;
            ELSEIF (_menjadi_berlakumulai=_jamkerja_berlakumulai) THEN
               SET _sebelum_id=_menjadi_idjamkerja;
               SET _sebelum_jenis=_menjadi_jenis;
               SET _sebelum_berlakumulai=_menjadi_berlakumulai;
            END IF;
        END IF;

        IF ISNULL(_sebelum_id)=false AND ISNULL(_sesudah_id)=false THEN
            IF _sebelum_jenis='shift' THEN
                SELECT 1 INTO _hasil FROM jadwalshift WHERE idpegawai=_pegawai_id AND tanggal>=_sebelum_berlakumulai AND tanggal<_sesudah_berlakumulai AND idjamkerjashift NOT IN (SELECT id FROM jamkerjashift WHERE idjamkerja=_sebelum_id) LIMIT 1;
            ELSE
                SELECT 1 INTO _hasil FROM jadwalshift WHERE idpegawai=_pegawai_id AND tanggal>=_sebelum_berlakumulai AND tanggal<_sesudah_berlakumulai LIMIT 1;
            END IF;
        ELSEIF ISNULL(_sebelum_id)=false AND ISNULL(_sesudah_id)=true THEN
            IF _sebelum_jenis='shift' THEN
                SELECT 1 INTO _hasil FROM jadwalshift WHERE idpegawai=_pegawai_id AND tanggal>=_sebelum_berlakumulai AND idjamkerjashift NOT IN (SELECT id FROM jamkerjashift WHERE idjamkerja=_sebelum_id) LIMIT 1;
            ELSE
                SELECT 1 INTO _hasil FROM jadwalshift WHERE idpegawai=_pegawai_id AND tanggal>=_sebelum_berlakumulai LIMIT 1;
            END IF;
        ELSEIF ISNULL(_sebelum_id)=true AND ISNULL(_sesudah_id)=false THEN
            SELECT 1 INTO _hasil FROM jadwalshift WHERE idpegawai=_pegawai_id AND tanggal<_sesudah_berlakumulai LIMIT 1;
        ELSE
            SELECT 1 INTO _hasil FROM jadwalshift WHERE idpegawai=_pegawai_id LIMIT 1;
        END IF;

    END IF;

    RETURN _hasil;
END //

DROP PROCEDURE IF EXISTS troubleshooting_tukarlogabsen
//
CREATE PROCEDURE troubleshooting_tukarlogabsen(IN _idpegawai_a INT, IN _idpegawai_b INT, IN _tglawal DATE, IN _tglakhir DATE)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS _temp_logabsentukar;
    CREATE TEMPORARY TABLE _temp_logabsentukar
    (
        `id`                    INT(11) UNSIGNED,
        `idpegawai`             INT(11) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=Memory;
    INSERT INTO _temp_logabsentukar 
    	SELECT 
    		id, idpegawai
    	FROM
    		logabsen
    	WHERE
    		idpegawai IN (_idpegawai_a, _idpegawai_b) AND 
    		waktu>=CONCAT(_tglawal, " 00:00:00") AND waktu<=CONCAT(_tglakhir, " 23:59:59")
    	;
    UPDATE _temp_logabsentukar SET idpegawai=idpegawai*10;
    UPDATE _temp_logabsentukar SET idpegawai=_idpegawai_a WHERE idpegawai=_idpegawai_b*10;
    UPDATE _temp_logabsentukar SET idpegawai=_idpegawai_b WHERE idpegawai=_idpegawai_a*10;

    UPDATE logabsen la, _temp_logabsentukar tla SET la.idpegawai=tla.idpegawai WHERE la.id=tla.id;
END //

# dapatan daftar jamkerja tanggal 1,2,3...31 sesuai dgn yymm + jadwal terpilih
# output berupa temporary table _jadwalshift
DROP PROCEDURE IF EXISTS pegawaishiftterpilihpertanggal
//
CREATE PROCEDURE pegawaishiftterpilihpertanggal(IN _idpegawai INT, IN _yymm VARCHAR(4))
BEGIN
    DECLARE _tglawal DATE DEFAULT NULL;
    DECLARE _tglakhir DATE DEFAULT NULL;

    SET _tglawal = STR_TO_DATE(CONCAT('20',LEFT(_yymm,2),'-',RIGHT(_yymm,2),'-01'),'%Y-%m-%d');
    SET _tglakhir = LAST_DAY(_tglawal);

    CALL pegawaishiftpertanggal(_idpegawai, _yymm);
    ALTER TABLE _jadwalshift ADD jadwal VARCHAR(512) NOT NULL;
    UPDATE _jadwalshift SET jadwal='';

    UPDATE
        _jadwalshift _js,
        (SELECT js.tanggal, IF(ISNULL(js.idjamkerjashift)=true,'libur',GROUP_CONCAT(CONCAT(js.idjamkerjashift,'#',jks.namashift) ORDER BY jks.urutan ASC SEPARATOR '|')) as jadwal FROM jadwalshift js LEFT JOIN jamkerjashift jks ON js.idjamkerjashift=jks.id WHERE js.idpegawai=_idpegawai AND js.tanggal BETWEEN _tglawal AND _tglakhir GROUP BY tanggal) js01
    SET
        _js.jadwal=js01.jadwal
    WHERE
        _js.tanggal=js01.tanggal;

END //

DROP PROCEDURE IF EXISTS laporanabsenperbulan//
CREATE PROCEDURE laporanabsenperbulan(
                                        IN _yymm VARCHAR(4),
                                        IN _atribut TEXT,
                                        IN _lang VARCHAR(2)
                                    )
BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE _temp_id INT DEFAULT 0;
    DECLARE totalhari INT DEFAULT 0;
    DECLARE _tanggal_first DATE DEFAULT NULL;
    DECLARE _tanggal_last DATE DEFAULT NULL;
    DECLARE _lang_tidakabsen VARCHAR(30) DEFAULT '-';
    DECLARE _lang_masuk VARCHAR(30) DEFAULT 'In';
    DECLARE _lang_keluar VARCHAR(30) DEFAULT 'Out';
    DECLARE _lang_alasantidakmasuk VARCHAR(30) DEFAULT 'Note absent';
    DECLARE _lang_alasanmasuk VARCHAR(30) DEFAULT 'Note in';
    DECLARE _lang_terlambat VARCHAR(30) DEFAULT 'Late';
    DECLARE _lang_pulangawal VARCHAR(30) DEFAULT 'Early out';
    DECLARE _lang_lamakerja VARCHAR(30) DEFAULT 'Work duration';
    DECLARE _lang_lamalembur VARCHAR(30) DEFAULT 'Overtime duration';

    DECLARE done INT DEFAULT FALSE;
    DECLARE cur_atribut CURSOR FOR
    SELECT
        id
    FROM
        __atribut
    ORDER BY
        atribut ASC, id ASC;
    DECLARE cur_atributvariable CURSOR FOR
    SELECT
        id
    FROM
        __atributvariable
    ORDER BY
        atribut ASC, id ASC;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;


    IF _lang='id' THEN
        SET _lang_tidakabsen = '-';
        SET _lang_masuk = 'Masuk';
        SET _lang_keluar = 'Keluar';
        SET _lang_alasantidakmasuk = 'Catatan tdk masuk';
        SET _lang_alasanmasuk = 'Catatan masuk';
        SET _lang_terlambat = 'Terlambat';
        SET _lang_pulangawal = 'Pulang awal';
        SET _lang_lamakerja = 'Lama kerja';
        SET _lang_lamalembur = 'Lama lembur';
    END IF;

    SET _tanggal_first = DATE(CONCAT("20",SUBSTR(_yymm,1,2),"-",SUBSTR(_yymm,3,2),"-01"));
    SET _tanggal_last = LAST_DAY(DATE(CONCAT("20",SUBSTR(_yymm,1,2),"-",SUBSTR(_yymm,3,2),"-01")));
    SET totalhari=DAY(_tanggal_last);

    DROP TEMPORARY TABLE IF EXISTS temp_laporanabsenperbulan;
    SET @stmt_text='CREATE TEMPORARY TABLE IF NOT EXISTS temp_laporanabsenperbulan(
                      idpegawai INT UNSIGNED NOT NULL,
                      nama VARCHAR(100),
                   ';

    DROP TEMPORARY TABLE IF EXISTS __atribut;
    CREATE TEMPORARY TABLE __atribut (id INT(11) NOT NULL, atribut VARCHAR(100) NOT NULL);
    INSERT INTO __atribut
        SELECT
            id, atribut
        FROM
            atribut
        WHERE
            penting='y';

    DROP TEMPORARY TABLE IF EXISTS __atributvariable;
    CREATE TEMPORARY TABLE __atributvariable (id INT(11) NOT NULL, atribut VARCHAR(100) NOT NULL);
    INSERT INTO __atributvariable
        SELECT
            id, atribut
        FROM
            atributvariable
        WHERE
            penting='y';

    SET i=0;
    OPEN cur_atribut;
    atribut_loop: LOOP
        SET i=i+1;
        SET done=false;
        FETCH cur_atribut INTO _temp_id;
        IF done THEN
            LEAVE atribut_loop;
        ELSE
            SET @stmt_text=CONCAT(@stmt_text, 'atribut_',i,' VARCHAR(100), ');
        END IF;
    END LOOP atribut_loop;
    CLOSE cur_atribut;

    SET i=0;
    OPEN cur_atributvariable;
    atributvariable_loop: LOOP
        SET i=i+1;
        SET done=false;
        FETCH cur_atributvariable INTO _temp_id;
        IF done THEN
            LEAVE atributvariable_loop;
        ELSE
            SET @stmt_text=CONCAT(@stmt_text, 'atributvariable_',i,' VARCHAR(100), ');
        END IF;
    END LOOP atributvariable_loop;
    CLOSE cur_atributvariable;

    SET i=0;
    loop01: LOOP
        SET i=i+1;
        IF i>totalhari THEN
            LEAVE loop01;
        ELSE
            SET @stmt_text=CONCAT(@stmt_text,
                                  'harilibur_',i,'              VARCHAR(100) DEFAULT "",',
                                  'masukkerja_',i,'             ENUM("y", "t") DEFAULT "t",',
                                  'alasantidakmasuk_',i,'       VARCHAR(100) DEFAULT "",',
                                  'jadwalmasukkerja_',i,'       ENUM("y", "t") DEFAULT "t",',
                                  'jenisjamkerja_',i,'          ENUM("","full","shift") DEFAULT "",',
                                  'jadwallamakerja_',i,'        INT UNSIGNED NOT NULL,',
                                  'alasanmasuk_',i,'            VARCHAR(100) DEFAULT "",',
                                  'waktumasuk_',i,'             DATETIME DEFAULT NULL,',
                                  'waktukeluar_',i,'            DATETIME DEFAULT NULL,',
                                  'selisihmasuk_',i,'           INT DEFAULT 0,',
                                  'selisihkeluar_',i,'          INT DEFAULT 0,',
                                  'lamakerja_',i,'              INT DEFAULT 0,',
                                  'lamalembur_',i,'             INT DEFAULT 0,',
                                  'keterangan_',i,'             VARCHAR(10) DEFAULT "",'
                                  'tooltip_',i,'                VARCHAR(512) DEFAULT "",'
                                );
        END IF;
    END LOOP loop01;
    SET @stmt_text=CONCAT(@stmt_text,
                          '  jumlahmasuk        INT UNSIGNED DEFAULT 0,',
                          '  jumlahmasukshift   INT UNSIGNED DEFAULT 0,',
                          '  jumlahterlambat    INT UNSIGNED DEFAULT 0,',
                          '  lamakerja          INT UNSIGNED DEFAULT 0,',
                          '  lamakerja_hhmm     VARCHAR(10) DEFAULT "00:00",',
                          '  PRIMARY KEY (idpegawai)',
                          ') ENGINE=Memory'
                          );

    PREPARE stmt FROM @stmt_text;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET @stmt_text='TRUNCATE temp_laporanabsenperbulan';
    PREPARE stmt FROM @stmt_text;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET @stmt_text='DROP TEMPORARY TABLE IF EXISTS temp_pegawai';
    PREPARE stmt FROM @stmt_text;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET @stmt_text='CREATE TEMPORARY TABLE temp_pegawai (id INT UNSIGNED) ENGINE=Memory';
    PREPARE stmt FROM @stmt_text;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET @stmt_text='TRUNCATE temp_pegawai';
    PREPARE stmt FROM @stmt_text;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    IF _atribut='' THEN
        SET @stmt_text=CONCAT(' INSERT INTO temp_pegawai
                                    SELECT
                                        DISTINCT(pg.id)
                                    FROM
                                        pegawai pg
                                    WHERE
                                        pg.del="t"
                              ');
    ELSE
        SET @stmt_text=CONCAT(' INSERT INTO temp_pegawai
                                    SELECT
                                        DISTINCT(pg.id)
                                    FROM
                                        pegawai pg,
                                        pegawaiatribut pa
                                    WHERE
                                        pa.idpegawai=pg.id AND
                                        pg.del="t" AND
                                        pa.idatributnilai IN (',_atribut,')
                             ');
    END IF;
    PREPARE stmt FROM @stmt_text;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    INSERT INTO temp_laporanabsenperbulan (idpegawai,nama)
        SELECT
            id, nama
        FROM
            pegawai
        WHERE
            del='t' AND
            (
                ((status='a' AND (ISNULL(tanggaltdkaktif)=true OR (ISNULL(tanggaltdkaktif)=false AND tanggalaktif<=_tanggal_first))) OR (status='t' AND ISNULL(tanggaltdkaktif)=false AND tanggaltdkaktif>_tanggal_first)) OR
                ((status='a' AND (ISNULL(tanggaltdkaktif)=true OR (ISNULL(tanggaltdkaktif)=false AND tanggalaktif<=_tanggal_last))) OR (status='t' AND ISNULL(tanggaltdkaktif)=false AND tanggaltdkaktif>_tanggal_last))
            ) AND
            id IN (SELECT id FROM temp_pegawai);

    SET i=0;
    loop02: LOOP
        SET i=i+1;
        IF i>totalhari THEN
            LEAVE loop02;
        ELSE
            SET @stmt_text=CONCAT( 'UPDATE
                                        temp_laporanabsenperbulan t,
                                        (
                                        SELECT
                                            ra.idpegawai,
                                            ra.tanggal,
                                            LEFT(IFNULL(hl.keterangan,""),100) as harilibur,
                                            ra.masukkerja,
                                            IFNULL(atm.alasan,"") as alasantidakmasuk,
                                            ra.jadwalmasukkerja,
                                            ra.jenisjamkerja,
                                            ra.jadwallamakerja,
                                            IFNULL(amk.alasan,"") as alasanmasuk,
                                            ra.waktumasuk,
                                            ra.waktukeluar,
                                            ra.selisihmasuk,
                                            ra.selisihkeluar,
                                            ra.lamakerja,
                                            ra.lamalembur,
                                            IF( ra.jadwalmasukkerja="y",
                                                IF( ra.masukkerja="y",
                                                    IF(ra.selisihmasuk<0, "*", "."),
                                                    ra.alasantidakmasukkategori
                                                ),
                                                "-"
                                            ) as keterangan
                                        FROM
                                            temp_pegawai tp,
                                            rekapabsen ra
                                            LEFT JOIN alasantidakmasuk atm ON atm.id=ra.idalasantidakmasuk
                                            LEFT JOIN alasanmasukkeluar amk ON amk.id=ra.idalasanmasuk
                                            LEFT JOIN harilibur hl ON hl.id=ra.idharilibur
                                        WHERE
                                            tp.id=ra.idpegawai AND
                                            DATE_FORMAT(ra.tanggal, "%y%m") = ',_yymm,'
                                        ) r
                                    SET
                                        t.harilibur_',i,'=r.harilibur,
                                        t.masukkerja_',i,'=r.masukkerja,
                                        t.alasantidakmasuk_',i,'=r.alasantidakmasuk,
                                        t.jadwalmasukkerja_',i,'=r.jadwalmasukkerja,
                                        t.jenisjamkerja_',i,'=r.jenisjamkerja,
                                        t.jadwallamakerja_',i,'=r.jadwallamakerja,
                                        t.alasanmasuk_',i,'=r.alasanmasuk,
                                        t.waktumasuk_',i,'=r.waktumasuk,
                                        t.waktukeluar_',i,'=r.waktukeluar,
                                        t.selisihmasuk_',i,'=r.selisihmasuk,
                                        t.selisihkeluar_',i,'=r.selisihkeluar,
                                        t.lamakerja_',i,'=r.lamakerja,
                                        t.lamalembur_',i,'=r.lamalembur,
                                        t.keterangan_',i,'=r.keterangan,
                                        t.tooltip_',i,'=LEFT(IFNULL(CONCAT("',_lang_masuk,': ",IFNULL(DATE_FORMAT(r.waktumasuk,"%d/%m/%Y %T"),"',_lang_tidakabsen,'"),CHAR(13),CHAR(10),
                                                      IF(ISNULL(r.alasanmasuk)=false,CONCAT("',_lang_alasanmasuk,': ",r.alasanmasuk,CHAR(13),CHAR(10)),""),
                                                                        IF(r.selisihmasuk<0,CONCAT("',_lang_terlambat,': ",sec2hour(-1*r.selisihmasuk),CHAR(13),CHAR(10)),""),
                                                                        "----------------------------------------------",CHAR(13),CHAR(10),
                                                                        "',_lang_keluar,': ",IFNULL(DATE_FORMAT(r.waktukeluar,"%d/%m/%Y %T"),"',_lang_tidakabsen,'"),CHAR(13),CHAR(10),
                                                                        IF(r.selisihkeluar<0,CONCAT("',_lang_pulangawal,': ",sec2hour(r.selisihkeluar),CHAR(13),CHAR(10)),"")
                                                     ),""),512)
                                    WHERE
                                        DAY(r.tanggal)=',i,' AND
                                        r.idpegawai=t.idpegawai
                                    ');
            PREPARE stmt FROM @stmt_text;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END IF;
    END LOOP loop02;

    SET @stmt_text=CONCAT( 'UPDATE
                                temp_laporanabsenperbulan t,
                                (
                                SELECT
                                    ra.idpegawai,
                                    IFNULL(SUM(IF(ra.masukkerja="y",1,0)),0) as jumlahmasuk,
                                    IFNULL(SUM(IF(ra.masukkerja="y" AND ra.jenisjamkerja="shift",ra.jumlahsesi,0)),0) as jumlahmasukshift,
                                    IFNULL(SUM(IF(ra.selisihmasuk<0,1,0)),0) as jumlahterlambat,
                                    IFNULL(SUM(IF(ra.masukkerja="y",ra.lamakerja+ra.lamalembur,0)),0) as lamakerja
                                FROM
                                    temp_pegawai tp,
                                    rekapabsen ra
                                WHERE
                                    tp.id=ra.idpegawai AND
                                    DATE_FORMAT(ra.tanggal, "%y%m") = ',_yymm,'
                                GROUP BY
                                    ra.idpegawai
                                ) r
                            SET
                                t.jumlahmasuk=r.jumlahmasuk,
                                t.jumlahmasukshift=r.jumlahmasukshift,
                                t.jumlahterlambat=r.jumlahterlambat,
                                t.lamakerja=r.lamakerja,
                                t.lamakerja_hhmm=SEC_TO_TIME(r.lamakerja)
                            WHERE
                                r.idpegawai=t.idpegawai
                        ');
    PREPARE stmt FROM @stmt_text;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET i=0;
    OPEN cur_atribut;
    atribut_loop: LOOP
        SET i=i+1;
        SET done=false;
        FETCH cur_atribut INTO _temp_id;
        IF done THEN
            LEAVE atribut_loop;
        ELSE
            SET @stmt_text=CONCAT( 'UPDATE
                                        temp_laporanabsenperbulan
                                    SET
                                        atribut_',i,' = getatributpegawai(idpegawai, ',_temp_id,')
                                ');
            PREPARE stmt FROM @stmt_text;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END IF;
    END LOOP atribut_loop;
    CLOSE cur_atribut;

    SET i=0;
    OPEN cur_atributvariable;
    atributvariable_loop: LOOP
        SET i=i+1;
        SET done=false;
        FETCH cur_atributvariable INTO _temp_id;
        IF done THEN
            LEAVE atributvariable_loop;
        ELSE
            SET @stmt_text=CONCAT( 'UPDATE
                                        temp_laporanabsenperbulan
                                    SET
                                        atributvariable_',i,' = getatributvariablepegawai(idpegawai, ',_temp_id,')
                                ');
            PREPARE stmt FROM @stmt_text;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END IF;
    END LOOP atributvariable_loop;
    CLOSE cur_atributvariable;
    
END //

DROP PROCEDURE IF EXISTS troubleshooting_tukarlogabsen
//
CREATE PROCEDURE troubleshooting_tukarlogabsen(IN _idpegawai_a INT, IN _idpegawai_b INT, IN _tglawal DATE, IN _tglakhir DATE)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS _temp_logabsentukar;
    CREATE TEMPORARY TABLE _temp_logabsentukar
    (
        `id`                    INT(11) UNSIGNED,
        `idpegawai`             INT(11) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=Memory;
    INSERT INTO _temp_logabsentukar 
    	SELECT 
    		id, idpegawai
    	FROM
    		logabsen 
    	WHERE
    		idpegawai IN (_idpegawai_a, _idpegawai_b) AND 
    		waktu>=CONCAT(_tglawal, " 00:00:00") AND waktu<=CONCAT(_tglakhir, " 23:59:59");
    		
    UPDATE _temp_logabsentukar SET idpegawai=idpegawai*10;
    UPDATE _temp_logabsentukar SET idpegawai=_idpegawai_a WHERE idpegawai=_idpegawai_b*10;
    UPDATE _temp_logabsentukar SET idpegawai=_idpegawai_b WHERE idpegawai=_idpegawai_a*10;

    UPDATE logabsen la, _temp_logabsentukar tla SET la.idpegawai=tla.idpegawai WHERE la.id=tla.id;
END //

DROP PROCEDURE IF EXISTS utils_hapuslogabsenshift_stack//
CREATE PROCEDURE utils_hapuslogabsenshift_stack(IN _tanggal DATE)
BEGIN
    DECLARE done INT DEFAULT FALSE;

    DECLARE _idpegawai INT UNSIGNED DEFAULT NULL;

    DECLARE _jadwal_id INT UNSIGNED DEFAULT NULL;
    DECLARE _jadwal_idjamkerjashift, _jadwal_idjamkerjashift_sebelum INT UNSIGNED DEFAULT NULL;
    DECLARE _jadwal_waktu DATETIME DEFAULT NULL;
    DECLARE _jadwal_masukkeluar, _jadwal_masukkeluar_sebelum ENUM('m','k');

    DECLARE _temp_int1 INT;
    DECLARE _temp_time1 TIME;
    DECLARE _temp_time2 TIME;
    DECLARE _temp_datetime1 DATETIME;
    DECLARE _temp_datetime2 DATETIME;

    DECLARE cur_pegawai CURSOR FOR
        SELECT
            p.id
        FROM
            pegawai p,
            pegawaijamkerja pjk,
            jamkerja jk
        WHERE
            pjk.idpegawai=p.id AND
            p.tanggalaktif<=_tanggal AND
            p.del='t' AND
            ((p.status='a' AND (ISNULL(p.tanggaltdkaktif)=true OR (ISNULL(p.tanggaltdkaktif)=false AND p.tanggalaktif<=_tanggal))) OR (p.status='t' AND ISNULL(p.tanggaltdkaktif)=false AND p.tanggaltdkaktif>_tanggal)) AND
            jk.id=pjk.idjamkerja AND
            jk.jenis='shift' AND
            pjk.berlakumulai<=_tanggal
        ORDER BY
            pjk.berlakumulai DESC;

    DECLARE cur_jadwalshift CURSOR FOR
        SELECT
            x.idjamkerjashift,
            x.jammasuk,
            x.jampulang
        FROM
            jadwalshift js,
            (
                SELECT
                    idjamkerjashift,
                    jammasuk,
                    jampulang
                FROM
                    jamkerjashiftdetail
                WHERE
                    (idjamkerjashift, berlakumulai) IN (SELECT idjamkerjashift, MAX(berlakumulai) FROM jamkerjashiftdetail WHERE berlakumulai<=_tanggal GROUP BY idjamkerjashift)
            ) x
        WHERE
            js.idjamkerjashift=x.idjamkerjashift AND
            js.tanggal=_tanggal AND
            js.idpegawai=_idpegawai
        ORDER BY
            x.jammasuk ASC,
            x.jampulang ASC;

    DECLARE cur_jadwal_asc CURSOR FOR
        SELECT
            id,
            idjamkerjashift,
            waktu,
            masukkeluar
        FROM
            _jadwal
        ORDER BY waktu ASC;

    DECLARE cur_jadwal_desc CURSOR FOR
        SELECT
            id,
            idjamkerjashift,
            waktu,
            masukkeluar
        FROM
            _jadwal
        ORDER BY waktu DESC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwal (
        `id`                    INT UNSIGNED AUTO_INCREMENT,
        `idjamkerjashift`       INT UNSIGNED,
        `waktu`                 DATETIME,
        `masukkeluar`           ENUM('m','k'),
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    DROP TEMPORARY TABLE IF EXISTS _sambungan;    
    CREATE TEMPORARY TABLE _sambungan (
        `idjamkerjashift`       INT UNSIGNED
    ) ENGINE=Memory;

    DROP TEMPORARY TABLE IF EXISTS _pegawaishiftsambung;
    CREATE TEMPORARY TABLE IF NOT EXISTS _pegawaishiftsambung (
        `id`       INT UNSIGNED
    ) ENGINE=Memory;
    TRUNCATE _pegawaishiftsambung;

    OPEN cur_pegawai;
    read_loop: LOOP
        SET done=false;
        FETCH cur_pegawai INTO _idpegawai;
        IF done THEN
            LEAVE read_loop;
        ELSE
            TRUNCATE _jadwal;
            TRUNCATE _sambungan;

            OPEN cur_jadwalshift;
            read_loop01: LOOP
                SET done=false;
                FETCH cur_jadwalshift INTO _temp_int1, _temp_time1, _temp_time2;
                IF done THEN
                    LEAVE read_loop01;
                ELSE
                    IF ISNULL(_temp_time2)=false AND ISNULL(_temp_time1)=false THEN
                        CALL time2datetime(_tanggal, _temp_time1, _temp_time2, _temp_datetime1, _temp_datetime2);

                        INSERT INTO _jadwal VALUES (NULL, _temp_int1, _temp_datetime1, 'm');
                        INSERT INTO _jadwal VALUES (NULL, _temp_int1, _temp_datetime2, 'k');
                    END IF;
                END IF;
            END LOOP read_loop01;
            CLOSE cur_jadwalshift;

            # hilangkan masukkeluar yang berulang, contoh: kmkkkkkkkkmk --> mkmk
            # ... hilangkan masuk ketemu masuk
            SET _jadwal_idjamkerjashift_sebelum=NULL;
            SET _jadwal_masukkeluar_sebelum=NULL;
            OPEN cur_jadwal_asc;
            read_loop01: LOOP
                SET done=false;
                FETCH cur_jadwal_asc INTO _jadwal_id, _jadwal_idjamkerjashift, _jadwal_waktu, _jadwal_masukkeluar;
                IF done THEN
                    LEAVE read_loop01;
                ELSE
                    IF (ISNULL(_jadwal_masukkeluar_sebelum)=true) OR
                       (ISNULL(_jadwal_masukkeluar_sebelum)=false AND _jadwal_masukkeluar_sebelum <> _jadwal_masukkeluar) THEN
                        SET _jadwal_idjamkerjashift_sebelum = _jadwal_idjamkerjashift;
                        SET _jadwal_masukkeluar_sebelum = _jadwal_masukkeluar;
                    ELSE
                        IF _jadwal_masukkeluar='m' THEN
                            DELETE FROM _jadwal WHERE id=_jadwal_id;
                            INSERT INTO _sambungan VALUES (_jadwal_idjamkerjashift_sebelum);
                            INSERT INTO _sambungan VALUES (_jadwal_idjamkerjashift);
                        END IF;
                    END IF;
                END IF;
            END LOOP read_loop01;
            CLOSE cur_jadwal_asc;

            # ... hilangkan keluar ketemu keluar
            SET _jadwal_idjamkerjashift_sebelum=NULL;
            SET _jadwal_masukkeluar_sebelum=NULL;
            OPEN cur_jadwal_desc;
            read_loop01: LOOP
                SET done=false;
                FETCH cur_jadwal_desc INTO _jadwal_id, _jadwal_idjamkerjashift, _jadwal_waktu, _jadwal_masukkeluar;
                IF done THEN
                    LEAVE read_loop01;
                ELSE
                    IF (ISNULL(_jadwal_masukkeluar_sebelum)=true) OR
                       (ISNULL(_jadwal_masukkeluar_sebelum)=false AND _jadwal_masukkeluar_sebelum <> _jadwal_masukkeluar) THEN
                        SET _jadwal_idjamkerjashift_sebelum = _jadwal_idjamkerjashift;
                        SET _jadwal_masukkeluar_sebelum = _jadwal_masukkeluar;
                    ELSE
                        IF _jadwal_masukkeluar='k' THEN
                            DELETE FROM _jadwal WHERE id=_jadwal_id;
                            INSERT INTO _sambungan VALUES (_jadwal_idjamkerjashift_sebelum);
                            INSERT INTO _sambungan VALUES (_jadwal_idjamkerjashift);
                        END IF;
                    END IF;
                END IF;
            END LOOP read_loop01;
            CLOSE cur_jadwal_desc;

            # eliminasi, hapus yang tidak bersambung
            DELETE FROM _jadwal WHERE idjamkerjashift NOT IN (SELECT idjamkerjashift FROM _sambungan);

            SET _temp_int1=0;
            SELECT COUNT(*) INTO _temp_int1 FROM _jadwal;
            IF _temp_int1>0 THEN
                INSERT INTO _pegawaishiftsambung VALUES(_idpegawai);
            END IF;

        END IF;
    END LOOP read_loop;
    CLOSE cur_pegawai;

    SELECT p.id, p.pin, p.nama FROM pegawai p, _pegawaishiftsambung _pss WHERE p.del='t' AND _pss.id=p.id;
END//

DROP PROCEDURE IF EXISTS pegawaijenisjamkerja//
CREATE PROCEDURE pegawaijenisjamkerja(IN _tanggal DATE, IN _jenisjamkerja ENUM('','full','shift'))
BEGIN
    DROP TABLE IF EXISTS _pegawaijenisjamkerja;
    CREATE TABLE _pegawaijenisjamkerja
    (
        idpegawai       INT UNSIGNED NOT NULL,
        PRIMARY KEY (idpegawai)
    ) ENGINE=Memory;
    TRUNCATE _pegawaijenisjamkerja;

    INSERT INTO _pegawaijenisjamkerja
        SELECT
            pjk.idpegawai
        FROM
            pegawaijamkerja pjk,
            jamkerja jk,
            (
                SELECT
                    idpegawai,
                    MAX(berlakumulai) as berlakumulai
                FROM
                    pegawaijamkerja
                WHERE
                    berlakumulai<=_tanggal
                GROUP BY
                    idpegawai
            ) _pjk
        WHERE
            jk.jenis=_jenisjamkerja AND
            pjk.idjamkerja=jk.id AND
            pjk.idpegawai=_pjk.idpegawai AND
            pjk.berlakumulai=_pjk.berlakumulai
        GROUP BY
            pjk.idpegawai
    ;
END//

DROP PROCEDURE IF EXISTS export_txt//
CREATE PROCEDURE `export_txt`(IN _tanggalawal DATE, IN _tanggalakhir DATE)
BEGIN
    DECLARE _tanggal DATE;
    DECLARE _temp_time1 TIME;
    DECLARE _end_of_day_awal DATETIME;
    DECLARE _end_of_day_akhir DATETIME;

    IF (ISNULL(_tanggalawal)=false) AND (ISNULL(_tanggalakhir)=false) THEN
        IF DATEDIFF(_tanggalakhir, _tanggalawal)<=90 THEN
            DROP TEMPORARY TABLE IF EXISTS `_export_txt`;
            CREATE TEMPORARY TABLE IF NOT EXISTS `_export_txt`
            (
                tanggal         DATE NOT NULL,
                pegawai_id      INT UNSIGNED NOT NULL,
                pegawai_pin     VARCHAR(8),
                waktu_masuk     DATETIME,
                waktu_keluar    DATETIME
            ) Engine=Memory;
            TRUNCATE _export_txt;

            SELECT end_of_day INTO _temp_time1 FROM pengaturan LIMIT 1;

            SET _tanggal = _tanggalawal;
            WHILE(_tanggal <= _tanggalakhir) DO
                SET _end_of_day_awal = STR_TO_DATE(CONCAT(_tanggal,' ',IFNULL(_temp_time1, '00:00:00')),'%Y-%m-%d %H:%i:%s');
                SET _end_of_day_akhir = DATE_ADD(STR_TO_DATE(CONCAT(_tanggal,' ',IFNULL(_temp_time1, '00:00:00')),'%Y-%m-%d %H:%i:%s'), INTERVAL 1 DAY);

                INSERT INTO _export_txt SELECT ra.tanggal, p.id, p.pin, NULL, NULL FROM rekapabsen ra, pegawai p WHERE ra.idpegawai=p.id AND ra.tanggal=_tanggal;

                UPDATE
                    _export_txt et, (SELECT idpegawai, MIN(waktu) as waktumasuk FROM logabsen WHERE masukkeluar='m' AND waktu BETWEEN _end_of_day_awal AND _end_of_day_akhir GROUP BY idpegawai) la
                SET
                    et.waktu_masuk=la.waktumasuk
                WHERE
                    et.pegawai_id=la.idpegawai AND
                    et.tanggal=_tanggal;

                UPDATE
                    _export_txt et, (SELECT idpegawai, MAX(waktu) as waktukeluar FROM logabsen WHERE masukkeluar='k' AND waktu BETWEEN _end_of_day_awal AND _end_of_day_akhir GROUP BY idpegawai) la
                SET
                    et.waktu_keluar=la.waktukeluar
                WHERE
                    et.pegawai_id=la.idpegawai AND
                    et.tanggal=_tanggal;

                SET _tanggal=DATE_ADD(_tanggal, INTERVAL 1 DAY);
            END WHILE;
        END IF;
    END IF;
END//

DROP PROCEDURE IF EXISTS generateharilibur//
CREATE PROCEDURE generateharilibur(IN _tahun INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _sql01 TEXT DEFAULT '';
    DECLARE _id INT DEFAULT NULL;
    DECLARE _agama VARCHAR(100) DEFAULT '';
    DECLARE cur_agama CURSOR FOR
        SELECT
            id,
            agama
        FROM
            agama
        ORDER BY
            urutan ASC, id ASC;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SELECT
        GROUP_CONCAT(CONCAT('`_',id,'` VARCHAR(1) DEFAULT "0"') ORDER BY urutan ASC, id ASC SEPARATOR ',')  INTO _sql01
    FROM
        agama;

    DROP TEMPORARY TABLE IF EXISTS _harilibur;
    SET @stmt_text=CONCAT('
                            CREATE TEMPORARY TABLE _harilibur
                            (
                                `id`            INT UNSIGNED,
                                `tanggalawal`   DATE,
                                `tanggalakhir`  DATE,
                                `keterangan`    VARCHAR(512),
                                ',_sql01,',
                                PRIMARY KEY (`id`)
                            ) ENGINE=Memory;
                          ');
    PREPARE stmt FROM @stmt_text;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    INSERT INTO _harilibur(id, tanggalawal, tanggalakhir, keterangan)
        SELECT
            id, tanggalawal, tanggalakhir, LEFT(keterangan,512)
        FROM
            harilibur
        WHERE
            YEAR(tanggalawal)=_tahun OR
            YEAR(tanggalakhir)=_tahun;

    OPEN cur_agama;
    agama_loop: LOOP
        SET done=false;
        FETCH cur_agama INTO _id, _agama;
        IF done THEN
            LEAVE agama_loop;
        ELSE
            SET @stmt_text=CONCAT('
                                    UPDATE
                                        _harilibur _hl,
                                        (
                                            SELECT
                                                hl.id
                                            FROM
                                                harilibur hl
                                                LEFT JOIN (SELECT idharilibur, COUNT(*) as jumlah FROM hariliburagama GROUP BY idharilibur) hla_ada ON hla_ada.idharilibur=hl.id
                                                LEFT JOIN (SELECT idharilibur, COUNT(*) as jumlah FROM hariliburagama WHERE idagama=',_id,' GROUP BY idharilibur) hla_agama ON hla_agama.idharilibur=hl.id
                                            WHERE
                                                (YEAR(tanggalawal)=',_tahun,' OR YEAR(tanggalakhir)=',_tahun,') AND
                                                (IFNULL(hla_agama.jumlah,0)>=1 OR IFNULL(hla_ada.jumlah,0)=0)
                                        ) cekagama
                                    SET
                                        _hl._',_id,'="1"
                                    WHERE
                                        _hl.id=cekagama.id
                                  ');
            PREPARE stmt FROM @stmt_text;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END IF;
    END LOOP agama_loop;
    CLOSE cur_agama;
END//

DROP PROCEDURE IF EXISTS get_cuti//
CREATE PROCEDURE get_cuti(IN _tahun INT)
BEGIN
    DECLARE _firstDate DATE;
    DECLARE _lastDate DATE;

    DROP TEMPORARY TABLE IF EXISTS _ijintidakmasuk;
    CREATE TEMPORARY TABLE _ijintidakmasuk
    (
        `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `idpegawai`     INT UNSIGNED NOT NULL,
        `tanggalawal`   DATE NOT NULL,
        `tanggalakhir`  DATE NOT NULL,
        INDEX `idx__ijintidakmasuk_idpegawai` (`idpegawai`),
        PRIMARY KEY (`id`)
    ) ENGINE=Memory;
    INSERT INTO _ijintidakmasuk
        SELECT
            itm.id,
            itm.idpegawai,
            itm.tanggalawal,
            itm.tanggalakhir
        FROM
            ijintidakmasuk itm,
            alasantidakmasuk atm            
        WHERE
            itm.idalasantidakmasuk=atm.id AND
            atm.kategori='c' AND        
            itm.status='a' AND
            (YEAR(itm.tanggalawal)=_tahun OR YEAR(itm.tanggalakhir)=_tahun);

    SET _firstDate = STR_TO_DATE(CONCAT(_tahun,'-01-01'), '%Y-%m-%d');
    SET _lastDate = STR_TO_DATE(CONCAT(_tahun,'-12-31'), '%Y-%m-%d');

    UPDATE _ijintidakmasuk SET tanggalawal=_firstDate WHERE tanggalawal<_firstDate;
    UPDATE _ijintidakmasuk SET tanggalakhir=_lastDate WHERE tanggalakhir>_lastDate;

    DROP TEMPORARY TABLE IF EXISTS _cuti;
    CREATE TEMPORARY TABLE _cuti
    (
        `idpegawai`     INT UNSIGNED NOT NULL,
        `lama`          INT NOT NULL,
        PRIMARY KEY (`idpegawai`)
    ) ENGINE=Memory;

    INSERT INTO _cuti
        SELECT idpegawai, SUM(DATEDIFF(tanggalakhir, tanggalawal)+1)
        FROM _ijintidakmasuk
        GROUP BY idpegawai;
END //

DROP PROCEDURE IF EXISTS get_cuti_pegawai//
CREATE PROCEDURE get_cuti_pegawai(IN _tahun INT, IN _idpegawai INT, IN _idijintidakmasuk INT)
BEGIN
    DECLARE _firstDate DATE;
    DECLARE _lastDate DATE;

    DROP TEMPORARY TABLE IF EXISTS _ijintidakmasuk_pegawai;
    CREATE TEMPORARY TABLE _ijintidakmasuk_pegawai
    (
        `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `idpegawai`     INT UNSIGNED NOT NULL,
        `tanggalawal`   DATE NOT NULL,
        `tanggalakhir`  DATE NOT NULL,
        INDEX `idx__ijintidakmasuk_idpegawai` (`idpegawai`),
        PRIMARY KEY (`id`)
    ) ENGINE=Memory;
    INSERT INTO _ijintidakmasuk_pegawai
        SELECT
            itm.id,
            itm.idpegawai,
            itm.tanggalawal,
            itm.tanggalakhir
        FROM
            ijintidakmasuk itm,
            alasantidakmasuk atm
        WHERE
            itm.idalasantidakmasuk=atm.id AND
            atm.kategori='c' AND
            itm.status IN ('c','a') AND
            (YEAR(itm.tanggalawal)=_tahun OR YEAR(itm.tanggalakhir)=_tahun) AND
            itm.idpegawai=_idpegawai AND
            (ISNULL(_idijintidakmasuk)=true OR itm.id<>_idijintidakmasuk);

    SET _firstDate = STR_TO_DATE(CONCAT(_tahun,'-01-01'), '%Y-%m-%d');
    SET _lastDate = STR_TO_DATE(CONCAT(_tahun,'-12-31'), '%Y-%m-%d');

    UPDATE _ijintidakmasuk_pegawai SET tanggalawal=_firstDate WHERE tanggalawal<_firstDate;
    UPDATE _ijintidakmasuk_pegawai SET tanggalakhir=_lastDate WHERE tanggalakhir>_lastDate;

    DROP TEMPORARY TABLE IF EXISTS _cuti_pegawai;
    CREATE TEMPORARY TABLE _cuti_pegawai
    (
        `idpegawai`     INT UNSIGNED NOT NULL,
        `jatah`         INT NOT NULL,
        `lama`          INT NOT NULL,
        PRIMARY KEY (`idpegawai`)
    ) ENGINE=Memory;

    INSERT INTO _cuti_pegawai
        SELECT
            p.id,
            IFNULL(c.jumlah,0),
            0
        FROM
            pegawai p
            LEFT JOIN cuti c ON c.idpegawai=p.id AND c.tahun=_tahun
        WHERE
            p.id=_idpegawai;

    UPDATE
        _cuti_pegawai cp,
        (
            SELECT
                idpegawai,
                SUM(DATEDIFF(tanggalakhir, tanggalawal) + 1) as lama
            FROM _ijintidakmasuk_pegawai
            GROUP BY idpegawai
        ) q
    SET
        cp.lama=q.lama
    WHERE
        cp.idpegawai=q.idpegawai;

END //

# _ygditampilkan = id, masukkerja, jammasuk, jampulang
DROP FUNCTION IF EXISTS getidjamkerjafull_berlaku//
CREATE FUNCTION getidjamkerjafull_berlaku(_idjamkerja INT, _ygditampilkan VARCHAR(10), _tanggal DATE) RETURNS VARCHAR(30)
BEGIN
    DECLARE _dayofweek INT;
    DECLARE _id INT DEFAULT NULL;
    DECLARE _masukkerja ENUM('y', 't') DEFAULT NULL;
    DECLARE _jammasuk TIME DEFAULT NULL;
    DECLARE _jampulang TIME DEFAULT NULL;

    SET _dayofweek=DAYOFWEEK(_tanggal);

    SELECT
        id,
        IF(_dayofweek=1, _1_masukkerja, IF(_dayofweek=2, _2_masukkerja, IF(_dayofweek=3, _3_masukkerja, IF(_dayofweek=4, _4_masukkerja, IF(_dayofweek=5, _5_masukkerja, IF(_dayofweek=6, _6_masukkerja, IF(_dayofweek=7, _7_masukkerja, NULL))))))),
        IF(_dayofweek=1, _1_jammasuk, IF(_dayofweek=2, _2_jammasuk, IF(_dayofweek=3, _3_jammasuk, IF(_dayofweek=4, _4_jammasuk, IF(_dayofweek=5, _5_jammasuk, IF(_dayofweek=6, _6_jammasuk, IF(_dayofweek=7, _7_jammasuk, NULL))))))),
        IF(_dayofweek=1, _1_jampulang, IF(_dayofweek=2, _2_jampulang, IF(_dayofweek=3, _3_jampulang, IF(_dayofweek=4, _4_jampulang, IF(_dayofweek=5, _5_jampulang, IF(_dayofweek=6, _6_jampulang, IF(_dayofweek=7, _7_jampulang, NULL)))))))
        INTO
        _id,
        _masukkerja,
        _jammasuk,
        _jampulang
    FROM
        jamkerjafull
    WHERE
        idjamkerja=_idjamkerja AND
        berlakumulai<=_tanggal
    ORDER BY
        berlakumulai DESC
    LIMIT 1
    ;

    CASE _ygditampilkan
        WHEN 'id' THEN RETURN _id;
        WHEN 'masukkerja' THEN RETURN _masukkerja;
        WHEN 'jammasuk' THEN RETURN _jammasuk;
        WHEN 'jampulang' THEN RETURN _jampulang;
    END CASE;
    RETURN '';
END //

#nilai kembali adalah y atau tidak
DROP FUNCTION IF EXISTS apakahadajadwalshift//
CREATE FUNCTION apakahadajadwalshift(_idpegawai INT, _tanggal DATE) RETURNS VARCHAR(1)
BEGIN
    DECLARE _ada VARCHAR(1) DEFAULT 't';
    SET _ada = 't';
    SELECT
        'y' INTO _ada
    FROM
        jadwalshift
    WHERE
        idpegawai=_idpegawai AND
        ISNULL(idjamkerjashift)=false AND
        tanggal=_tanggal
    LIMIT 1;

    RETURN _ada;
END //

#nilai kembali adalah y atau tidak
DROP FUNCTION IF EXISTS apakahpegawaiharilibur//
CREATE FUNCTION apakahpegawaiharilibur(_idpegawai INT, _tanggal DATE) RETURNS VARCHAR(1)
BEGIN
    DECLARE _ada VARCHAR(1) DEFAULT 't';
    DECLARE _flag INT DEFAULT 0;
    DECLARE _jumlahagama INT DEFAULT 0;
    DECLARE _jumlahatribut INT DEFAULT 0;
    DECLARE done INT DEFAULT FALSE;
    DECLARE _idharilibur INT;
    DECLARE cur_harilibur CURSOR FOR
        SELECT
            id
        FROM
            harilibur
        WHERE
            _tanggal>=tanggalawal AND _tanggal<=tanggalakhir;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET _ada = 't';

    OPEN cur_harilibur;
    harilibur_loop: LOOP
        SET done=false;
        FETCH cur_harilibur INTO _idharilibur;
        IF done THEN
            LEAVE harilibur_loop;
        ELSE
            SET _jumlahagama = 0;
            SELECT 1 INTO _jumlahagama FROM hariliburagama WHERE idharilibur=_idharilibur LIMIT 1;
            SET _jumlahatribut = 0;
            SELECT 1 INTO _jumlahatribut FROM hariliburatribut WHERE idharilibur=_idharilibur LIMIT 1;
            # jika berlaku untuk semua (tanpa agama dan atribut)
            IF _jumlahagama=0 AND _jumlahatribut=0 THEN
                SET _ada = 'y';
                LEAVE harilibur_loop;
            ELSE
                #cek agama
                SET _flag = 0;
                SELECT
                    1 INTO _flag
                FROM
                    hariliburagama
                WHERE
                    idharilibur=_idharilibur AND
                    idagama IN (SELECT idagama FROM pegawai WHERE id=_idpegawai)
                LIMIT 1;
                IF(_flag=1) THEN
                    SET _ada = 'y';
                    LEAVE harilibur_loop;
                END IF ;

                #cek atribut
                SET _flag = 0;
                SELECT
                    1 INTO _flag
                FROM
                    hariliburatribut
                WHERE
                    idharilibur=_idharilibur AND
                    idatributnilai IN (SELECT idatributnilai FROM pegawaiatribut WHERE idpegawai=_idpegawai)
                LIMIT 1;

                IF(_flag=1) THEN
                    SET _ada = 'y';
                    LEAVE harilibur_loop;
                END IF ;
            END IF;
        END IF;
    END LOOP harilibur_loop;
    CLOSE cur_harilibur;

    RETURN _ada;
END //

# output adalah temporary table berisi pegawai yang harusnya absen
DROP PROCEDURE IF EXISTS pegawai_seharusnya_absen//
CREATE PROCEDURE pegawai_seharusnya_absen(IN _tanggal DATE)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS _pegawai_seharusnya_absen;
    CREATE TEMPORARY TABLE _pegawai_seharusnya_absen (
        `idpegawai`         INT UNSIGNED,
        `idjamkerja`        INT UNSIGNED,
        `jenisjamkerja`     ENUM('','full','shift'),
        `jadwalmasuk`       ENUM('y','t') NOT NULL,
        INDEX `idx__pegawai_seharusnya_absen_idpegawai` (`idpegawai`),
        INDEX `idx__pegawai_seharusnya_absen_idjamkerja` (`idjamkerja`),
        INDEX `idx__pegawai_seharusnya_absen_jenisjamkerja` (`jenisjamkerja`),
        INDEX `idx__pegawai_seharusnya_absen_jadwalmasuk` (`jadwalmasuk`),
        PRIMARY KEY (idpegawai)
    ) ENGINE=Memory;

    INSERT IGNORE INTO _pegawai_seharusnya_absen
        SELECT
            id,
            getpegawaijamkerja(id,"id",_tanggal),
            NULL,
            't'
        FROM
            pegawai
        WHERE
            del='t' AND
            ((status='a' AND (ISNULL(tanggaltdkaktif)=true OR (ISNULL(tanggaltdkaktif)=false AND tanggalaktif<=_tanggal))) OR (status='t' AND ISNULL(tanggaltdkaktif)=false AND tanggaltdkaktif>_tanggal))
    ;

    #periksa untuk pegawai yg jamkerja full dan shift
    UPDATE
        _pegawai_seharusnya_absen _psa,
        jamkerja jk
    SET
        _psa.jenisjamkerja=jk.jenis,
        _psa.jadwalmasuk=IF(jk.jenis='full',
                            IF(CAST(getidjamkerjafull_berlaku(_psa.idjamkerja, 'masukkerja', _tanggal) AS BINARY)='y',
                               IF(CAST(apakahpegawaiharilibur(_psa.idpegawai, _tanggal) AS BINARY)='y','t','y'),
                               't'
                              ),
                            IF(CAST(apakahadajadwalshift(_psa.idpegawai, _tanggal) AS BINARY)='y', 'y', 't')
                           )
    WHERE
        _psa.idjamkerja=jk.id;

    #cek ijintidakmasuk
    UPDATE
        _pegawai_seharusnya_absen
    SET
        jadwalmasuk='t'
    WHERE
        jadwalmasuk='y' AND
        idpegawai IN (SELECT DISTINCT(idpegawai) FROM ijintidakmasuk WHERE _tanggal>=tanggalawal AND _tanggal<=tanggalakhir AND status='a');

    DELETE FROM _pegawai_seharusnya_absen WHERE jadwalmasuk='t';
END//

DROP FUNCTION IF EXISTS `remove_non_numeric_and_letters`//
CREATE FUNCTION `remove_non_numeric_and_letters`(input TEXT) RETURNS TEXT
BEGIN
    DECLARE output TEXT DEFAULT '';
    DECLARE iterator INT DEFAULT 1;
    WHILE iterator < (LENGTH(input) + 1) DO
      IF SUBSTRING(input, iterator, 1) IN
         ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z')
      THEN
        SET output = CONCAT(output, SUBSTRING(input, iterator, 1));
      ELSE 
        SET output = CONCAT(output, '_');
      END IF;
      SET iterator = iterator + 1;
    END WHILE;
    RETURN output;
END //

DROP PROCEDURE IF EXISTS check_pegawaitracker//
CREATE PROCEDURE check_pegawaitracker(IN _idpegawai INT)
BEGIN
    DECLARE _tracker VARCHAR(5) DEFAULT 'off';
    DECLARE _interval INT DEFAULT 1;
    DECLARE _masukkeluar ENUM('m','k') DEFAULT 'k';
    DECLARE _waktu DATETIME DEFAULT 'k';
    DECLARE _trackerexpired DATETIME;
    DECLARE _jenis VARCHAR(100) DEFAULT 'full';
    DECLARE _gunakantracker ENUM('d','y','t') DEFAULT 't';
    DECLARE _pengaturan_gunakandefault ENUM('y','t') DEFAULT 't';
    DECLARE _pengaturan_lamashiftberakhir INT DEFAULT 12;
    DECLARE _pengaturan_endofday TIME DEFAULT '04:00:00';
    DECLARE _ada ENUM('y','t') DEFAULT 'y';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET _ada = 't';

    SET _trackerexpired = NOW();
    
    # ambil data dari pengaturan
    SELECT 
        end_of_day, employee_tracker_gunakandefault, employee_tracker_intervaldefault, employee_tracker_lamashiftberakhir INTO 
        _pengaturan_endofday, _pengaturan_gunakandefault, _interval, _pengaturan_lamashiftberakhir 
    FROM pengaturan LIMIT 1;

    # apakah pengaturan tracker dipakai?
    SET _ada = 'y';
    SELECT gunakantracker INTO _gunakantracker FROM pegawai WHERE id=_idpegawai AND del='t' AND status='a' LIMIT 1;
    IF _ada = 'y' THEN
        IF _gunakantracker = 'd' AND _pengaturan_gunakandefault = 'y' THEN
            SET _gunakantracker = 'y';
        END IF;
        IF _gunakantracker = 'y' THEN
            # cek status logabsen terakhir
            SET _ada = 'y';
            SELECT waktu, masukkeluar INTO _waktu, _masukkeluar FROM logabsen WHERE idpegawai=_idpegawai AND waktu BETWEEN SUBDATE(NOW(), INTERVAL 3 DAY) AND NOW() ORDER BY waktu DESC LIMIT 1;

            IF _ada = 'y' THEN
                #jika terakhir adalah masuk, maka tracker ON
                IF _masukkeluar = 'm' THEN
                    # jika waktu skrg kurang dari endofdaym tracker = on
                    SET _trackerexpired = ADDDATE(_waktu, INTERVAL _pengaturan_lamashiftberakhir HOUR);
                    IF NOW() < _trackerexpired THEN
                        SET _tracker = 'on';
                    END IF;
                END IF;
            END IF;

        END IF;
    END IF;

    SELECT _tracker as 'tracker', _interval as 'interval', _trackerexpired as 'expired';
END //

DROP TRIGGER IF EXISTS after_insert_logabsen//
CREATE TRIGGER after_insert_logabsen AFTER INSERT ON logabsen
FOR EACH ROW
BEGIN
    IF (ISNULL(NEW.lat)=false AND NEW.lat<>0 AND ISNULL(NEW.lon)=false AND NEW.lon<>0) THEN
        INSERT INTO pegawaitracker_log VALUES(NULL, NEW.idpegawai, NEW.waktu, NEW.lat, NEW.lon, NEW.id, NOW()) ON DUPLICATE KEY UPDATE idlogabsen=NEW.id, inserted=NOW();
    END IF;
END//

DROP FUNCTION IF EXISTS getlokasiabsen//
CREATE FUNCTION getlokasiabsen(_param_lat DOUBLE, _param_lon DOUBLE) RETURNS TEXT
BEGIN
    DECLARE _data_ada BOOLEAN DEFAULT TRUE;
    DECLARE _toleransi_jarak_gps INT DEFAULT 0;
    DECLARE _lokasi TEXT DEFAULT '';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET _data_ada = FALSE;

    SET _data_ada = TRUE;
    SELECT toleransi_jarak_gps INTO _toleransi_jarak_gps FROM pengaturan;
    IF _data_ada=TRUE THEN
        SET _data_ada = TRUE;

        SELECT
            IFNULL(GROUP_CONCAT(DISTINCT nama ORDER BY nama SEPARATOR ", "),"") INTO _lokasi
        FROM 
            lokasi
        WHERE
            st_distance_sphere(POINT(lon,lat),POINT(_param_lon,_param_lat))<=IF(jaraktoleransi='default',_toleransi_jarak_gps,radius);

        IF _data_ada=TRUE THEN
            RETURN IFNULL(_lokasi,'');
        END IF;
    END IF;
    RETURN '';
END//

DROP PROCEDURE IF EXISTS preparelaporanlokasiabsen//
CREATE PROCEDURE preparelaporanlokasiabsen(IN _tglawal DATE, IN _tglakhir DATE, IN _idlokasi TEXT, IN _atribut TEXT)
BEGIN
    DECLARE _data_ada BOOLEAN DEFAULT TRUE;
    DECLARE _waktuawal DATETIME DEFAULT NULL;
    DECLARE _waktuakhir DATETIME DEFAULT NULL;
    DECLARE _where_lokasi TEXT DEFAULT '';
    DECLARE _toleransi_jarak_gps INT DEFAULT 0;
    DECLARE _lokasi TEXT DEFAULT '';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET _data_ada = FALSE;

    SET _waktuawal = STR_TO_DATE(CONCAT(_tglawal,' 00:00:00'),'%Y-%m-%d %H:%i:%s');
    SET _waktuakhir = STR_TO_DATE(CONCAT(_tglakhir,' 23:59:59'),'%Y-%m-%d %H:%i:%s');

    DROP TEMPORARY TABLE IF EXISTS temp_preparelaporanlokasiabsen;
    CREATE TEMPORARY TABLE temp_preparelaporanlokasiabsen (
        idlogabsen  INT UNSIGNED NOT NULL,
        lokasi      VARCHAR(200),
        PRIMARY KEY (idlogabsen)
    ) Engine=Memory;

    SET _data_ada = TRUE;
    SELECT toleransi_jarak_gps INTO _toleransi_jarak_gps FROM pengaturan;


    SET _where_lokasi = '';
    IF _idlokasi<>'' THEN
        SET _where_lokasi = CONCAT(' l.id IN (',_idlokasi,') AND ');
    END IF;

    IF _data_ada=TRUE THEN
        IF _atribut='' THEN
            SET @stmt_text=CONCAT(' INSERT INTO temp_preparelaporanlokasiabsen
                                        SELECT
                                            la.id, 
                                            getlokasiabsen(la.lat,la.lon) as lok 
                                        FROM
                                            logabsen la,
                                            lokasi l
                                        WHERE
                                            (la.waktu BETWEEN "',_waktuawal,'" AND "',_waktuakhir,'") AND
                                            ',_where_lokasi,'
                                            st_distance_sphere(POINT(l.lon,l.lat),POINT(la.lon,la.lat))<=IF(jaraktoleransi="default",',_toleransi_jarak_gps,',l.radius)
                                        GROUP BY
                                            la.id
                                        HAVING 
                                            lok<>""
                                  ');
        ELSE
            SET @stmt_text=CONCAT(' INSERT INTO temp_preparelaporanlokasiabsen
                                        SELECT
                                            la.id, 
                                            getlokasiabsen(la.lat,la.lon) as lok 
                                        FROM
                                            logabsen la,
                                            pegawaiatribut pa,
                                            lokasi l
                                        WHERE
                                            pa.idpegawai=la.idpegawai AND
                                            (la.waktu BETWEEN "',_waktuawal,'" AND "',_waktuakhir,'") AND
                                            pa.idatributnilai IN (',_atribut,') AND
                                            ',_where_lokasi,'
                                            st_distance_sphere(POINT(l.lon,l.lat),POINT(la.lon,la.lat))<=IF(jaraktoleransi="default",',_toleransi_jarak_gps,',l.radius)
                                        GROUP BY
                                            la.id
                                        HAVING 
                                            lok<>""
                                  ');
        END IF;

        PREPARE stmt FROM @stmt_text;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//

# ###################### POSTING #####################

DROP PROCEDURE IF EXISTS time2datetime//
CREATE PROCEDURE time2datetime(IN _tanggal DATE, IN _time1 TIME, IN _time2 TIME, INOUT _datetime1 DATETIME, INOUT _datetime2 DATETIME)
BEGIN
    SET _datetime1 = STR_TO_DATE(CONCAT(_tanggal,' ',_time1),'%Y-%m-%d %H:%i:%s');
    -- jika jam istirahat selesai >= istirahat mulai
    IF _time2>=_time1 THEN
        SET _datetime2 = STR_TO_DATE(CONCAT(_tanggal,' ',_time2),'%Y-%m-%d %H:%i:%s');
    ELSE
        SET _datetime2 = DATE_ADD(STR_TO_DATE(CONCAT(_tanggal,' ',_time2),'%Y-%m-%d %H:%i:%s'), INTERVAL 1 DAY);
    END IF;
END //

DROP FUNCTION IF EXISTS time2datetime_f//
CREATE FUNCTION time2datetime_f(_tanggal DATE, _time1 TIME, _time2 TIME, _prosesnomer INT) RETURNS DATETIME
BEGIN
    DECLARE _datetime1 DATETIME DEFAULT NULL;
    DECLARE _datetime2 DATETIME DEFAULT NULL;

    SET _datetime1 = STR_TO_DATE(CONCAT(_tanggal,' ',_time1),'%Y-%m-%d %H:%i:%s');
    -- jika jam istirahat selesai >= istirahat mulai
    IF _prosesnomer=2 THEN
        IF _time2>=_time1 THEN
            SET _datetime2 = STR_TO_DATE(CONCAT(_tanggal,' ',_time2),'%Y-%m-%d %H:%i:%s');
        ELSE
            SET _datetime2 = DATE_ADD(STR_TO_DATE(CONCAT(_tanggal,' ',_time2),'%Y-%m-%d %H:%i:%s'), INTERVAL 1 DAY);
        END IF;
        RETURN _datetime2;
    END IF;
    RETURN _datetime1;
END //

DROP PROCEDURE IF EXISTS posting_persiapanjadwalbersambung//
CREATE PROCEDURE posting_persiapanjadwalbersambung(IN _tanggal DATE, IN _idpegawai INT UNSIGNED)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _idjamkerjashift INT DEFAULT 0;
    DECLARE _jamkerja_id INT;
    DECLARE _jamkerja_jenis ENUM ('full','shift');

    DECLARE _jadwalbeberapahari_dataada VARCHAR(1);
    DECLARE _jadwalbeberapahari_jammasuk TIME;
    DECLARE _jadwalbeberapahari_jampulang TIME;

    DECLARE _simpan INT;

    DECLARE _adadata INT;
    DECLARE _adadata_jkkjk INT;
    DECLARE _adadata_jkkp INT;
    DECLARE _adadata_ijintidakmasuk INT;
    DECLARE _adadata_jadwaljamkerjakhusus INT;

    DECLARE _dayofweek INT;
    DECLARE _jadwal_masukkerja ENUM('y','t') DEFAULT NULL; # variable penampung untuk jam kerja full

    DECLARE _temp_id INT;
    DECLARE _temp_i INT;
    DECLARE _temp_tanggal DATE;
    DECLARE _temp_waktumasuk, _temp_waktupulang DATETIME;
    DECLARE _temp_waktumasuk_sebelum, _temp_waktupulang_sebelum DATETIME;

    DECLARE cur_jadwalbeberapahari CURSOR FOR
        SELECT
            idjamkerjashift
        FROM
            jadwalshift
        WHERE
            idpegawai=_idpegawai AND
            tanggal=_temp_tanggal
        ORDER BY
            tanggal ASC;

    DECLARE cur_temp_jadwalbeberapahari CURSOR FOR
        SELECT
            id,
            tanggal,
            waktumasuk,
            waktupulang
        FROM
            _temp_jadwalbeberapahari
        ORDER BY
            waktumasuk ASC,
            waktupulang ASC;

    DECLARE cur_jamkerjakhusus CURSOR FOR
        SELECT
            id,
            jammasuk,
            jampulang
        FROM
            jamkerjakhusus
        WHERE
            _temp_tanggal BETWEEN tanggalawal AND tanggalakhir;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;


    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwalbeberapahari (
        `id`            INT UNSIGNED,
        `tanggal`       DATE,
        `waktumasuk`    DATETIME,
        `waktupulang`   DATETIME
    ) ENGINE=Memory;
    CREATE TEMPORARY TABLE IF NOT EXISTS _temp_jadwalbeberapahari LIKE _jadwalbeberapahari;
    TRUNCATE _jadwalbeberapahari;
    TRUNCATE _temp_jadwalbeberapahari;

    # dapatkan jadwal dari h-2 s/d h+2
    SET _temp_i=-2;
    REPEAT
        SET _temp_tanggal = ADDDATE(_tanggal, INTERVAL _temp_i DAY);

        # cek ijintidakmasuk
        SET _adadata_ijintidakmasuk = 0;
        SELECT
            1 INTO _adadata_ijintidakmasuk
        FROM
            ijintidakmasuk
        WHERE
            idpegawai=_idpegawai AND
            (_temp_tanggal BETWEEN tanggalawal AND tanggalakhir) AND
            status = 'a'
        LIMIT 1;

        IF _adadata_ijintidakmasuk=0 THEN
            # dapatkan idjamkerja dan jenisnya
            SET _jamkerja_id=NULL;
            SET _jamkerja_jenis=NULL;
            SELECT
                jk.id, jk.jenis INTO
                _jamkerja_id, _jamkerja_jenis
            FROM
                pegawai p,
                pegawaijamkerja pjk,
                jamkerja jk
            WHERE
                pjk.idpegawai=p.id AND
                p.id=_idpegawai AND
                p.del='t' AND
                ((p.status='a' AND (ISNULL(p.tanggaltdkaktif)=true OR (ISNULL(p.tanggaltdkaktif)=false AND p.tanggalaktif<=_temp_tanggal))) OR (p.status='t' AND ISNULL(p.tanggaltdkaktif)=false AND p.tanggaltdkaktif>_temp_tanggal)) AND
                jk.id=pjk.idjamkerja AND
                pjk.berlakumulai<=_temp_tanggal
            ORDER BY
                pjk.berlakumulai DESC
            LIMIT 1;

            # jika punya jam kerja
            IF ISNULL(_jamkerja_id)=false THEN
                SET _adadata_jadwaljamkerjakhusus = 0;
                # dapatkan jadwal dari jamkerjakhusus
                OPEN cur_jamkerjakhusus;
                read_loop_cur_jamkerjakhusus: LOOP
                    SET done=false;
                    FETCH cur_jamkerjakhusus INTO _temp_id, _temp_waktumasuk, _temp_waktupulang;
                    IF done THEN
                        LEAVE read_loop_cur_jamkerjakhusus;
                    ELSE
                        # cek apakah ada detail (ada data pada jamkerjakhususjamkerja dan jamkerjakhususpegawai)
                        SET _simpan=0;
                        SET _adadata_jkkjk = 0;
                        SELECT 1 INTO _adadata_jkkjk FROM jamkerjakhususjamkerja WHERE idjamkerjakhusus=_temp_id LIMIT 1;
                        SET _adadata_jkkp = 0;
                        SELECT 1 INTO _adadata_jkkp FROM jamkerjakhususpegawai WHERE idjamkerjakhusus=_temp_id LIMIT 1;
                        IF _adadata_jkkjk=0 AND _adadata_jkkp=0 THEN
                            SET _simpan=1;
                        ELSE
                            IF _adadata_jkkjk=1 THEN
                                SET _adadata = 0;
                                SELECT 1 INTO _adadata FROM jamkerjakhususjamkerja WHERE idjamkerjakhusus=_temp_id AND idjamkerja=_jamkerja_id LIMIT 1;
                                IF _adadata=1 THEN
                                    SET _simpan=1;
                                END IF;
                            END IF;
                            IF _simpan=0 AND _adadata_jkkp=1 THEN
                                SET _adadata = 0;
                                SELECT 1 INTO _adadata FROM jamkerjakhususpegawai WHERE idjamkerjakhusus=_temp_id AND idpegawai=_idpegawai LIMIT 1;
                                IF _adadata=1 THEN
                                    SET _simpan=1;
                                END IF;
                            END IF;
                        END IF;
                        IF _simpan=1 THEN
                            INSERT INTO _temp_jadwalbeberapahari VALUES( NULL,
                                                                  _temp_tanggal,
                                                                  STR_TO_DATE(CONCAT(_temp_tanggal,' ',_temp_waktumasuk),'%Y-%m-%d %H:%i:%s'),
                                                                  STR_TO_DATE(CONCAT(_temp_tanggal,' ',_temp_waktupulang),'%Y-%m-%d %H:%i:%s')
                                                                );
                            SET _adadata_jadwaljamkerjakhusus = 1;
                        END IF;
                    END IF;
                END LOOP read_loop_cur_jamkerjakhusus;
                CLOSE cur_jamkerjakhusus;

                IF _adadata_jadwaljamkerjakhusus = 0 THEN
                    IF _jamkerja_jenis='full' THEN

                        SET _dayofweek=DAYOFWEEK(_tanggal);

                        SET @_masukkerja=NULL,
                            @_jammasuk=NULL,
                            @_jampulang=NULL;
                        SET @stmt_text=CONCAT(' SELECT
                                                    _',_dayofweek,'_masukkerja,
                                                    _',_dayofweek,'_jammasuk,
                                                    _',_dayofweek,'_jampulang
                                                    INTO
                                                    @_masukkerja,
                                                    @_jammasuk,
                                                    @_jampulang
                                                FROM
                                                    jamkerjafull
                                                WHERE
                                                    berlakumulai<="',_temp_tanggal,'" AND
                                                    idjamkerja=',_jamkerja_id,'
                                                ORDER BY
                                                    berlakumulai DESC
                                                LIMIT 1');
                        PREPARE stmt FROM @stmt_text;
                        EXECUTE stmt;
                        DEALLOCATE PREPARE stmt;

                        SET _jadwal_masukkerja = IFNULL(@_masukkerja,'t');

                        IF _jadwal_masukkerja='y' THEN
                            CALL time2datetime(_temp_tanggal, @_jammasuk, @_jampulang, _temp_waktumasuk, _temp_waktupulang);
                            INSERT INTO _temp_jadwalbeberapahari VALUES(NULL, _temp_tanggal, _temp_waktumasuk, _temp_waktupulang);
                        END IF;

                    ELSEIF _jamkerja_jenis='shift' THEN
                        OPEN cur_jadwalbeberapahari;
                            read_loop_cur_jadwalbeberapahari: LOOP
                                SET done=false;
                                FETCH cur_jadwalbeberapahari INTO _idjamkerjashift;
                                IF done THEN
                                    LEAVE read_loop_cur_jadwalbeberapahari;
                                ELSE
                                    IF ISNULL(_idjamkerjashift)=false THEN
                                        SET _jadwalbeberapahari_dataada='t';
                                        SELECT
                                            'y',
                                            jammasuk,
                                            jampulang
                                            INTO
                                            _jadwalbeberapahari_dataada,
                                            _jadwalbeberapahari_jammasuk,
                                            _jadwalbeberapahari_jampulang
                                        FROM
                                            jamkerjashiftdetail
                                        WHERE
                                            idjamkerjashift=_idjamkerjashift AND
                                            berlakumulai<=_temp_tanggal
                                        ORDER BY
                                            berlakumulai DESC
                                        LIMIT 1;
                                        IF _jadwalbeberapahari_dataada='y' THEN
                                            SET _temp_waktumasuk = STR_TO_DATE(CONCAT(_temp_tanggal,' ',_jadwalbeberapahari_jammasuk),'%Y-%m-%d %H:%i:%s');
                                            IF _jadwalbeberapahari_jampulang>_jadwalbeberapahari_jammasuk THEN
                                                SET _temp_waktupulang = STR_TO_DATE(CONCAT(_temp_tanggal,' ',_jadwalbeberapahari_jampulang),'%Y-%m-%d %H:%i:%s');
                                            ELSE
                                                SET _temp_waktupulang = STR_TO_DATE(CONCAT(ADDDATE(_temp_tanggal, INTERVAL 1 DAY),' ',_jadwalbeberapahari_jampulang),'%Y-%m-%d %H:%i:%s');
                                            END IF;
                                            INSERT INTO _temp_jadwalbeberapahari VALUES(NULL, _temp_tanggal, _temp_waktumasuk, _temp_waktupulang);
                                        END IF;
                                    END IF;
                                END IF;
                            END LOOP read_loop_cur_jadwalbeberapahari;
                            CLOSE cur_jadwalbeberapahari;

                    END IF;
                END IF;
            END IF;
        END IF;
        SET _temp_i=_temp_i+1;
    UNTIL _temp_i>2 END REPEAT;

#     SELECT * FROM _temp_jadwalbeberapahari;

    # eliminasi yang bersambung
    SET _temp_i=0;
    SET _temp_waktumasuk_sebelum = NULL;
    SET _temp_waktupulang_sebelum = NULL;
    OPEN cur_temp_jadwalbeberapahari;
    read_loop_cur_temp_jadwalbeberapahari: LOOP
        SET done=false;
        FETCH cur_temp_jadwalbeberapahari INTO _temp_id, _temp_tanggal, _temp_waktumasuk, _temp_waktupulang;
        IF done THEN
            LEAVE read_loop_cur_temp_jadwalbeberapahari;
        ELSE
            IF ISNULL(_temp_waktumasuk_sebelum)=true AND ISNULL(_temp_waktupulang_sebelum)=true THEN
                SET _temp_waktumasuk_sebelum = _temp_waktumasuk;
                SET _temp_waktupulang_sebelum = _temp_waktupulang;
                SET _temp_i = _temp_i + 1;
                INSERT INTO _jadwalbeberapahari VALUES(_temp_i, _temp_tanggal, _temp_waktumasuk, _temp_waktupulang);
            ELSE
                IF _temp_waktumasuk<=_temp_waktupulang_sebelum THEN
                    IF _temp_waktupulang>_temp_waktupulang_sebelum THEN
                        SET _temp_waktupulang_sebelum = _temp_waktupulang;
                        UPDATE _jadwalbeberapahari SET waktupulang = _temp_waktupulang WHERE id = _temp_i;
                    END IF;
                ELSE
                    SET _temp_waktumasuk_sebelum = _temp_waktumasuk;
                    SET _temp_waktupulang_sebelum = _temp_waktupulang;
                    SET _temp_i = _temp_i + 1;
                    INSERT INTO _jadwalbeberapahari VALUES(_temp_i, _temp_tanggal, _temp_waktumasuk, _temp_waktupulang);
                END IF;
            END IF;
        END IF;
    END LOOP read_loop_cur_temp_jadwalbeberapahari;
    CLOSE cur_temp_jadwalbeberapahari;

#     SELECT * FROM _jadwalbeberapahari;

END //

# output berupa table _jadwalbersambung + dan (UPDATE _jadwal SET shiftsambungan='y' WHERE id=_jadwal_id);
DROP PROCEDURE IF EXISTS utils_cekjadwalbersambung//
CREATE PROCEDURE utils_cekjadwalbersambung(
                                            IN _analisa ENUM('kemarin','besok'),
                                            IN _tanggal DATE,
                                            IN _idpegawai INT UNSIGNED,
                                            IN _pegawai_idagama INT UNSIGNED
                                          )
BEGIN
    DECLARE _jamkerja_id INT UNSIGNED DEFAULT NULL;
    DECLARE _jamkerja_jenis ENUM ('full','shift') DEFAULT 'full';

    DECLARE _harilibur_id INT UNSIGNED DEFAULT NULL;

    DECLARE _temp_int1 INT;
    DECLARE _temp_time1 TIME;
    DECLARE _temp_time2 TIME;

    DECLARE _jamkerja_waktumasuk DATETIME DEFAULT NULL;
    DECLARE _jamkerja_waktupulang DATETIME DEFAULT NULL;

    DECLARE _jadwal_masukkerja ENUM('y','t') DEFAULT NULL; # variable penampung untuk jam kerja full
    DECLARE _dayofweek INT;

    DECLARE _jadwal_id INT UNSIGNED DEFAULT NULL;
    DECLARE _jadwal_waktu DATETIME DEFAULT NULL;

    # table _jadwalbersambung sudah create sebelumnya pada procedure posting_persiapanjadwal
    # Untuk jaga2 saja: supaya tdk error, table _jadwalbersambung di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwalbersambung (
        `analisa`        ENUM('kemarin','besok') NOT NULL,
        `waktumasuk`     DATETIME,
        `waktupulang`    DATETIME
    ) ENGINE=Memory;

    # table _jadwal sudah diisi pada procedure posting_persiapanjadwal
    # Untuk jaga2 saja: supaya tdk error, table _jadwal di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwal (
        `id`                    INT UNSIGNED AUTO_INCREMENT,
        `idjamkerjashift`       INT UNSIGNED,
        `waktu`                 DATETIME,
        `masukkeluar`           ENUM('m','k'),
        `checking`              ENUM('', 'start','end'), # start: absen masuk, end: absen pulang (dalam satu sesi selain istirahat)
        `shiftpertamaterakhir`  ENUM('', 'pertama', 'terakhir'), # apakah shift tsb adalah shift pertama atau terakhir pada tanggal tsb?
        `shiftsambungan`        ENUM('y','t'), # apakah shift tsb terdapat sambungan dari shift sebelumnya/sesudahnya yg telah dijadwalkan?
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    IF _analisa='kemarin' THEN
        SET _tanggal = DATE_SUB(_tanggal, INTERVAL 1 DAY);
    ELSEIF _analisa='besok' THEN
        SET _tanggal = DATE_ADD(_tanggal, INTERVAL 1 DAY);
    END IF;

    # apakah pada tanggal kemarin/besok, idpegawai ada? jamkerja-nya ada?
    SELECT
        jk.id, jk.jenis INTO
        _jamkerja_id, _jamkerja_jenis
    FROM
        pegawai p,
        pegawaijamkerja pjk,
        jamkerja jk
    WHERE
        pjk.idpegawai=p.id AND
        p.id=_idpegawai AND
        p.del='t' AND
        ((p.status='a' AND (ISNULL(p.tanggaltdkaktif)=true OR (ISNULL(p.tanggaltdkaktif)=false AND p.tanggalaktif<=_tanggal))) OR (p.status='t' AND ISNULL(p.tanggaltdkaktif)=false AND p.tanggaltdkaktif>_tanggal)) AND
        jk.id=pjk.idjamkerja AND
        pjk.berlakumulai<=_tanggal
    ORDER BY
        pjk.berlakumulai DESC
    LIMIT 1;

    IF ISNULL(_jamkerja_id) = false THEN
        # cek apakah _tanggal ada hari libur
        SELECT
            hl.id INTO _harilibur_id
        FROM
            harilibur hl,
            hariliburatribut hla
        WHERE
            hl.id=hla.idharilibur AND
            (_tanggal BETWEEN hl.tanggalawal AND hl.tanggalakhir)   AND
            hla.idatributnilai IN (SELECT idatributnilai FROM pegawaiatribut WHERE idpegawai=_idpegawai)
        LIMIT 1;

        IF ISNULL(_harilibur_id) = true THEN
            SELECT hl.id, hla.id INTO _harilibur_id, _temp_int1
            FROM
                harilibur hl
                LEFT JOIN hariliburatribut hla ON hl.id=hla.idharilibur
            WHERE
                (_tanggal BETWEEN hl.tanggalawal AND hl.tanggalakhir)
            HAVING ISNULL(hla.id) = true
            LIMIT 1;
        END IF;

        # jika ada idharilibur, cek apakah agamannya benar?
        IF ISNULL(_harilibur_id) = false THEN
            SET _temp_int1 = 0;
            SELECT COUNT(*) INTO _temp_int1 FROM hariliburagama WHERE idharilibur=_harilibur_id AND idagama=_pegawai_idagama;
            IF _temp_int1 = 0 THEN
                SET _temp_int1 = 0;
                SELECT COUNT(*) INTO _temp_int1 FROM hariliburagama WHERE idharilibur=_harilibur_id;
                IF _temp_int1 > 0 THEN
                    SET _harilibur_id = NULL;
                END IF;
            END IF;
        END IF;

        SET _jamkerja_waktumasuk = NULL,
            _jamkerja_waktupulang = NULL;

        # ... cek jadwal kerja khusus --> jamkerjakhususpegawai
        SELECT
            jkk.jammasuk, jkk.jampulang INTO
            _jamkerja_waktumasuk, _jamkerja_waktupulang
        FROM
            jamkerjakhusus jkk,
            jamkerjakhususpegawai jkkp
        WHERE
            jkk.id=jkkp.idjamkerjakhusus AND
            jkkp.idpegawai=_idpegawai AND
            _tanggal BETWEEN jkk.tanggalawal AND jkk.tanggalakhir
        ORDER BY
            jkk.inserted ASC
        LIMIT 1;

        # ... jika TIDAK ADA jam kerja khusus
        IF NOT (ISNULL(_jamkerja_waktumasuk)=false AND ISNULL(_jamkerja_waktupulang)=false) THEN
            IF (_jamkerja_jenis='full') THEN
                SET _dayofweek=DAYOFWEEK(_tanggal);

                SET @_masukkerja=NULL,
                    @_jammasuk=NULL,
                    @_jampulang=NULL;
                SET @stmt_text=CONCAT(' SELECT
                                            _',_dayofweek,'_masukkerja,
                                            _',_dayofweek,'_jammasuk,
                                            _',_dayofweek,'_jampulang
                                            INTO
                                            @_masukkerja,
                                            @_jammasuk,
                                            @_jampulang
                                        FROM
                                            jamkerjafull
                                        WHERE
                                            berlakumulai<="',_tanggal,'" AND
                                            idjamkerja=',_jamkerja_id,'
                                        ORDER BY
                                            berlakumulai DESC
                                        LIMIT 1');
                PREPARE stmt FROM @stmt_text;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;

                SET _jadwal_masukkerja = IFNULL(@_masukkerja,'t');

                # jika jadwal ada, tidak libur
                IF _jadwal_masukkerja='y' THEN
                    CALL time2datetime(_tanggal, @_jammasuk, @_jampulang, _jamkerja_waktumasuk, _jamkerja_waktupulang);
                END IF;
            ELSEIF (_jamkerja_jenis='shift') THEN
                SELECT
                    x.jammasuk, x.jampulang INTO
                    _temp_time1, _temp_time2
                FROM
                    jadwalshift js,
                    (
                        SELECT
                            idjamkerjashift,
                            jammasuk,
                            jampulang,
                            jamistirahatmulai,
                            jamistirahatselesai
                        FROM
                            jamkerjashiftdetail
                        WHERE
                            (idjamkerjashift, berlakumulai) IN (SELECT idjamkerjashift, MAX(berlakumulai) FROM jamkerjashiftdetail WHERE berlakumulai<=_tanggal GROUP BY idjamkerjashift)
                    ) x
                WHERE
                    js.idjamkerjashift=x.idjamkerjashift AND
                    js.tanggal=_tanggal AND
                    js.idpegawai=_idpegawai
                ORDER BY
                    IF(_analisa='kemarin',1,-1)*x.jammasuk DESC,
                    IF(_analisa='kemarin',1,-1)*x.jampulang DESC
                LIMIT 1;

                CALL time2datetime(_tanggal, _temp_time1, _temp_time2, _jamkerja_waktumasuk, _jamkerja_waktupulang);
            END IF;
        END IF;

        # ambil waktu pada shift pertama / terakhir
        IF _analisa='kemarin' THEN
            SELECT id, waktu INTO _jadwal_id, _jadwal_waktu FROM _jadwal ORDER BY waktu ASC LIMIT 1;
            UPDATE _jadwal SET shiftpertamaterakhir='pertama' WHERE id=_jadwal_id;
        ELSEIF _analisa='besok' THEN
            SELECT id, waktu INTO _jadwal_id, _jadwal_waktu FROM _jadwal ORDER BY waktu DESC LIMIT 1;
            UPDATE _jadwal SET shiftpertamaterakhir='terakhir' WHERE id=_jadwal_id;
        END IF;
        IF ISNULL(_jadwal_id)=false THEN
            IF (_jadwal_waktu BETWEEN _jamkerja_waktumasuk AND _jamkerja_waktupulang) THEN
                INSERT INTO _jadwalbersambung VALUES(_analisa, _jamkerja_waktumasuk, _jamkerja_waktupulang);
                UPDATE _jadwal SET shiftsambungan='y' WHERE id=_jadwal_id;
            END IF;
        END IF;
    END IF;
END //

# output berupa table _jadwal
DROP PROCEDURE IF EXISTS posting_persiapanjadwal//
CREATE PROCEDURE posting_persiapanjadwal(IN _tanggal DATE,
                                         IN _idpegawai INT UNSIGNED,
                                         IN _pegawai_idagama INT UNSIGNED,
                                         IN _jamkerja_id INT UNSIGNED,
                                         IN _jamkerja_jenis ENUM('full','shift'),
                                         INOUT _harilibur_id INT UNSIGNED,
                                         INOUT _jadwal_masukkerja ENUM('y','t'),
                                         INOUT _jadwal_toleransi INT,
                                         INOUT _jadwal_hitunglemburstlh INT UNSIGNED,
                                         INOUT _jamkerjakhusus_id INT UNSIGNED,
                                         INOUT _rekapabsen_jumlahsesi INT,
                                         INOUT _rekapabsen_jadwallamakerja INT
                                         )
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _jamkerjakhusus_toleransi INT DEFAULT 0;
    DECLARE _jamkerjakhusus_hitunglemburstlh INT DEFAULT 0;
    DECLARE _jamkerjakhusus_jammasuk TIME DEFAULT NULL;
    DECLARE _jamkerjakhusus_jampulang TIME DEFAULT NULL;

    DECLARE _jadwal_id, _jadwal_id_sebelum INT UNSIGNED DEFAULT NULL;
    DECLARE _jadwal_waktu, _jadwal_waktu_sebelum DATETIME DEFAULT NULL;
    DECLARE _jadwal_masukkeluar, _jadwal_masukkeluar_sebelum ENUM('m','k');

    DECLARE _idjamkerjafull INT UNSIGNED DEFAULT NULL;

    DECLARE _temp_int1 INT;
    DECLARE _temp_int2 INT;
    DECLARE _temp_time1 TIME;
    DECLARE _temp_time2 TIME;
    DECLARE _temp_time3 TIME;
    DECLARE _temp_time4 TIME;
    DECLARE _temp_datetime1 DATETIME;
    DECLARE _temp_datetime2 DATETIME;
    DECLARE _temp_datetime3 DATETIME;
    DECLARE _temp_datetime4 DATETIME;
    DECLARE _dayofweek INT;

    DECLARE cur_jamkerjafullistirahat CURSOR FOR
        SELECT
            jamawal,
            jamakhir
        FROM
            jamkerjafullistirahat
        WHERE
            idjamkerjafull=_idjamkerjafull AND
            hari=_dayofweek;

    DECLARE cur_jamkerjakhususistirahat CURSOR FOR
        SELECT
            jamawal,
            jamakhir
        FROM
            jamkerjakhususistirahat
        WHERE
            idjamkerjakhusus=_jamkerjakhusus_id;

    DECLARE cur_jadwalshift CURSOR FOR
        SELECT
            idjamkerjashift,
            jammasuk,
            jampulang,
            jamistirahatmulai,
            jamistirahatselesai
        FROM
            _jamkerjashiftdetail
        ORDER BY
            jammasuk ASC,
            jampulang ASC;

    DECLARE cur_jadwal_asc CURSOR FOR
        SELECT
            id,
            waktu,
            masukkeluar
        FROM
            _jadwal
        ORDER BY
            waktu ASC,
            IF(checking='',IF(masukkeluar='m',0,1),IF(masukkeluar='m',1,0)) ASC
        ;

    DECLARE cur_jadwal_desc CURSOR FOR
        SELECT
            id,
            waktu,
            masukkeluar
        FROM
            _jadwal
        ORDER BY
            waktu DESC,
            IF(checking='',IF(masukkeluar='m',0,1),IF(masukkeluar='m',1,0)) DESC
    ;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET _jadwal_masukkerja = 't';
    SET _jamkerjakhusus_id = NULL;
    SET _rekapabsen_jumlahsesi = 0;

    # cek apakah _tanggal ada hari libur
    SET _harilibur_id = NULL;
    SELECT
        hl.id INTO _harilibur_id
    FROM
        harilibur hl,
        hariliburatribut hla
    WHERE
        hl.id=hla.idharilibur AND
        (_tanggal BETWEEN hl.tanggalawal AND hl.tanggalakhir)   AND
        hla.idatributnilai IN (SELECT idatributnilai FROM pegawaiatribut WHERE idpegawai=_idpegawai)
    LIMIT 1;

    IF ISNULL(_harilibur_id) = true THEN
        SELECT hl.id, hla.id INTO _harilibur_id, _temp_int1
        FROM
            harilibur hl
            LEFT JOIN hariliburatribut hla ON hl.id=hla.idharilibur
        WHERE
            (_tanggal BETWEEN hl.tanggalawal AND hl.tanggalakhir)
        HAVING ISNULL(hla.id) = true
        LIMIT 1;
    END IF;

    # jika ada idharilibur, cek apakah agamannya benar?
    IF ISNULL(_harilibur_id) = false THEN
        SET _temp_int1 = 0;
        SELECT COUNT(*) INTO _temp_int1 FROM hariliburagama WHERE idharilibur=_harilibur_id AND idagama=_pegawai_idagama;
        IF _temp_int1 = 0 THEN
            SET _temp_int1 = 0;
            SELECT COUNT(*) INTO _temp_int1 FROM hariliburagama WHERE idharilibur=_harilibur_id;
            IF _temp_int1 > 0 THEN
                SET _harilibur_id = NULL;
            END IF;
        END IF;
    END IF;

    # persiapkan jamkerjashiftdetail
    CREATE TEMPORARY TABLE IF NOT EXISTS _jamkerjashiftdetail
    (
        `idjamkerjashift`       INT(11) UNSIGNED NOT NULL,
        `jammasuk`              TIME NOT NULL,
        `jampulang`             TIME NOT NULL,
        `jamistirahatmulai`     TIME,
        `jamistirahatselesai`   TIME
    ) Engine=Memory;
    TRUNCATE _jamkerjashiftdetail;

    # persiapkan jadwal
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwal (
        `id`                    INT UNSIGNED AUTO_INCREMENT,
        `idjamkerjashift`       INT UNSIGNED,
        `waktu`                 DATETIME,
        `masukkeluar`           ENUM('m','k'),
        `checking`              ENUM('', 'start','end'), # start: absen masuk, end: absen pulang (dalam satu sesi selain istirahat)
        `shiftpertamaterakhir`  ENUM('', 'pertama', 'terakhir'), # apakah shift tsb adalah shift pertama atau terakhir pada tanggal tsb?
        `shiftsambungan`        ENUM('y','t'), # apakah shift tsb terdapat sambungan dari shift sebelumnya/sesudahnya yg telah dijadwalkan?
        PRIMARY KEY(id)
    ) ENGINE=Memory;
    TRUNCATE _jadwal;

    # ... cek jadwal kerja khusus --> jamkerjakhususpegawai
    SET _jamkerjakhusus_id = NULL,
        _jamkerjakhusus_toleransi = 0,
        _jamkerjakhusus_hitunglemburstlh = 0,
        _jamkerjakhusus_jammasuk = NULL,
        _jamkerjakhusus_jampulang = NULL;

    SELECT
        jkk.id, jkk.toleransi, jkk.hitunglemburstlh, jkk.jammasuk, jkk.jampulang INTO
        _jamkerjakhusus_id, _jamkerjakhusus_toleransi, _jamkerjakhusus_hitunglemburstlh, _jamkerjakhusus_jammasuk, _jamkerjakhusus_jampulang
    FROM
        jamkerjakhusus jkk,
        jamkerjakhususpegawai jkkp
    WHERE
        jkk.id=jkkp.idjamkerjakhusus AND
        jkkp.idpegawai=_idpegawai AND
        _tanggal BETWEEN jkk.tanggalawal AND jkk.tanggalakhir
    ORDER BY
        jkk.inserted ASC
    LIMIT 1;

    # ... jika _jamkerjakhusus_id masih NULL, cek lagi pada jamkerjakhususjamkerja, siapa tahu idjamkerjanya ada
    if (ISNULL(_jamkerjakhusus_id)=true) THEN
        SELECT
            jkk.id, jkk.toleransi, jkk.hitunglemburstlh, jkk.jammasuk, jkk.jampulang INTO
            _jamkerjakhusus_id, _jamkerjakhusus_toleransi, _jamkerjakhusus_hitunglemburstlh, _jamkerjakhusus_jammasuk, _jamkerjakhusus_jampulang
        FROM
            jamkerjakhusus jkk,
            jamkerjakhususjamkerja jkkjk
        WHERE
            jkk.id=jkkjk.idjamkerjakhusus AND
            jkkjk.idjamkerja=_jamkerja_id AND
            _tanggal BETWEEN jkk.tanggalawal AND jkk.tanggalakhir
        ORDER BY
            jkk.inserted ASC
        LIMIT 1;
    END IF;

    # ... jika ada jam kerja khusus
    IF (ISNULL(_jamkerjakhusus_id)=false) THEN
        SET _jadwal_masukkerja = 'y';
        SET _jadwal_toleransi = _jamkerjakhusus_toleransi;
        SET _jadwal_hitunglemburstlh = _jamkerjakhusus_hitunglemburstlh;

        SET _rekapabsen_jumlahsesi = 1;

        CALL time2datetime(_tanggal, _jamkerjakhusus_jammasuk, _jamkerjakhusus_jampulang, _temp_datetime1, _temp_datetime2);

        INSERT INTO _jadwal VALUES (NULL, NULL, _temp_datetime1, 'm', 'start', '', 't');
        INSERT INTO _jadwal VALUES (NULL, NULL, _temp_datetime2, 'k', 'end', '', 't');

        OPEN cur_jamkerjakhususistirahat;
        read_loop: LOOP
            SET done=false;
            FETCH cur_jamkerjakhususistirahat INTO _temp_time1, _temp_time2;
            IF done THEN
                LEAVE read_loop;
            ELSE
                IF ISNULL(_temp_time2)=false AND ISNULL(_temp_time1)=false THEN
                    CALL time2datetime(_tanggal, _temp_time1, _temp_time2, _temp_datetime3, _temp_datetime4);

                    # jika istirahat berada diantara jam masuk dan pulang, maka masukkan ke jadwal
                    IF (_temp_datetime3 BETWEEN _temp_datetime1 AND _temp_datetime2) AND
                       (_temp_datetime4 BETWEEN _temp_datetime1 AND _temp_datetime2) AND
                       (_temp_datetime4>_temp_datetime3) THEN
                        INSERT INTO _jadwal VALUES (NULL, NULL, _temp_datetime3, 'k', '', '', 't');
                        INSERT INTO _jadwal VALUES (NULL, NULL, _temp_datetime4, 'm', '', '', 't');
                    END IF;
                END IF;
            END IF;
        END LOOP read_loop;
    ELSE
        IF (_jamkerja_jenis='full') THEN
            IF (ISNULL(_harilibur_id)=true) THEN # jika BUKAN hari libur
                SET _rekapabsen_jumlahsesi = 1;

                SET _dayofweek=DAYOFWEEK(_tanggal);

                SET @_idjamkerjafull=NULL,
                    @_masukkerja=NULL,
                    @_jammasuk=NULL,
                    @_jampulang=NULL;
                SET @stmt_text=CONCAT(' SELECT
                                            id,
                                            _',_dayofweek,'_masukkerja,
                                            _',_dayofweek,'_jammasuk,
                                            _',_dayofweek,'_jampulang
                                            INTO
                                            @_idjamkerjafull,
                                            @_masukkerja,
                                            @_jammasuk,
                                            @_jampulang
                                        FROM
                                            jamkerjafull
                                        WHERE
                                            berlakumulai<="',_tanggal,'" AND
                                            idjamkerja=',_jamkerja_id,'
                                        ORDER BY
                                            berlakumulai DESC
                                        LIMIT 1');
                PREPARE stmt FROM @stmt_text;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;

                SET _jadwal_masukkerja = IFNULL(@_masukkerja,'t');
                SET _idjamkerjafull = @_idjamkerjafull;

                # jika jadwal ada, tidak libur
                IF _jadwal_masukkerja='y' THEN
                    CALL time2datetime(_tanggal, @_jammasuk, @_jampulang, _temp_datetime1, _temp_datetime2);
                    INSERT INTO _jadwal VALUES (NULL, NULL, _temp_datetime1, 'm', 'start', '', 't');
                    INSERT INTO _jadwal VALUES (NULL, NULL, _temp_datetime2, 'k', 'end', '', 't');

                    OPEN cur_jamkerjafullistirahat;
                    read_loop: LOOP
                        SET done=false;
                        FETCH cur_jamkerjafullistirahat INTO _temp_time1, _temp_time2;
                        IF done THEN
                            LEAVE read_loop;
                        ELSE
                            IF ISNULL(_temp_time2)=false AND ISNULL(_temp_time1)=false THEN
                                CALL time2datetime(_tanggal, _temp_time1, _temp_time2, _temp_datetime3, _temp_datetime4);
                                # jika istirahat berada diantara jam masuk dan pulang, maka masukkan ke jadwal
                                IF (_temp_datetime3 BETWEEN _temp_datetime1 AND _temp_datetime2) AND
                                   (_temp_datetime4 BETWEEN _temp_datetime1 AND _temp_datetime2) AND
                                   (_temp_datetime4>_temp_datetime3) THEN
                                    INSERT INTO _jadwal VALUES (NULL, NULL, _temp_datetime3, 'k', '', '', 't');
                                    INSERT INTO _jadwal VALUES (NULL, NULL, _temp_datetime4, 'm', '', '', 't');
                                END IF;
                            END IF;
                        END IF;
                    END LOOP read_loop;
                END IF;
            END IF;
        ELSEIF (_jamkerja_jenis='shift') THEN
            # periksa jadwal shift apakah libur?
            SET _jadwal_masukkerja = 't';
            SELECT
                'y' INTO _jadwal_masukkerja
            FROM
                jadwalshift
            WHERE
                tanggal=_tanggal AND
                idpegawai=_idpegawai AND
                ISNULL(idjamkerjashift)=false
            LIMIT 1;

            # jika ada jadwalshift
            IF _jadwal_masukkerja='y' THEN
                INSERT INTO _jamkerjashiftdetail
                    SELECT
                        x.idjamkerjashift,
                        x.jammasuk,
                        x.jampulang,
                        x.jamistirahatmulai,
                        x.jamistirahatselesai
                    FROM
                        jadwalshift js,
                        (
                            SELECT
                                idjamkerjashift,
                                jammasuk,
                                jampulang,
                                jamistirahatmulai,
                                jamistirahatselesai
                            FROM
                                jamkerjashiftdetail
                            WHERE
                                (idjamkerjashift, berlakumulai) IN (SELECT idjamkerjashift, MAX(berlakumulai) FROM jamkerjashiftdetail WHERE berlakumulai<=_tanggal GROUP BY idjamkerjashift)
                        ) x
                    WHERE
                        js.idjamkerjashift=x.idjamkerjashift AND
                        js.tanggal=_tanggal AND
                        js.idpegawai=_idpegawai
                    ORDER BY
                        x.jammasuk ASC,
                        x.jampulang ASC;

#               INSERT INTO _jamkerjashiftdetail VALUES(1,'08:00:00','13:00:00','09:00:00','11:00:00');
#               INSERT INTO _jamkerjashiftdetail VALUES(2,'10:00:00','13:00:00','11:00:00','12:00:00');
#               INSERT INTO _jamkerjashiftdetail VALUES(3,'12:00:00','20:00:00',NULL,NULL);

#               INSERT INTO _jamkerjashiftdetail VALUES(1,'08:00:00','13:00:00','09:00:00','11:00:00');
#               INSERT INTO _jamkerjashiftdetail VALUES(2,'10:00:00','13:00:00','11:00:00','12:00:00');
#               INSERT INTO _jamkerjashiftdetail VALUES(3,'12:00:00','20:00:00','12:00:00','13:00:00');

#               INSERT INTO _jamkerjashiftdetail VALUES(1,'08:00:00','13:00:00','09:00:00','11:00:00');
#               INSERT INTO _jamkerjashiftdetail VALUES(2,'10:00:00','13:00:00','11:00:00','12:30:00');
#               INSERT INTO _jamkerjashiftdetail VALUES(3,'12:00:00','20:00:00','12:30:00','13:00:00');

                # ambil jamkerjashift yang pertama
                SET _rekapabsen_jumlahsesi = 0;
                OPEN cur_jadwalshift;
                read_loop: LOOP
                    SET done=false;
                    FETCH cur_jadwalshift INTO _temp_int1, _temp_time1, _temp_time2, _temp_time3, _temp_time4;
                    IF done THEN
                        LEAVE read_loop;
                    ELSE
                        IF ISNULL(_temp_time2)=false AND ISNULL(_temp_time1)=false THEN
                            SET _rekapabsen_jumlahsesi = _rekapabsen_jumlahsesi + 1;
                            CALL time2datetime(_tanggal, _temp_time1, _temp_time2, _temp_datetime1, _temp_datetime2);

                            INSERT INTO _jadwal VALUES (NULL, _temp_int1, _temp_datetime1, 'm', 'start', '', 't');

                            # cek dahulu, apakah ada waktu keluar diantara jadwal jam kerja lain.
                            SET _temp_int2=0;
                            SELECT
                                1 INTO _temp_int2
                            FROM
                                _jamkerjashiftdetail
                            WHERE
                                _temp_datetime2>=time2datetime_f(_tanggal, jammasuk, jampulang, 1) AND
                                _temp_datetime2<time2datetime_f(_tanggal, jammasuk, jampulang, 2) AND
                                idjamkerjashift<>_temp_int1
                            LIMIT 1;
                            IF _temp_int2=0 THEN
                                INSERT INTO _jadwal VALUES (NULL, _temp_int1, _temp_datetime2, 'k', 'end', '', 't');
                            END IF;

                            # untuk jam istirahat masuk/keluar
                            IF ISNULL(_temp_time3)=false AND ISNULL(_temp_time4)=false THEN
                                CALL time2datetime(_tanggal, _temp_time3, _temp_time4, _temp_datetime3, _temp_datetime4);
                                # jika istirahat berada diantara jam masuk dan pulang, maka masukkan ke jadwal
                                IF (_temp_datetime3 BETWEEN _temp_datetime1 AND _temp_datetime2) AND
                                   (_temp_datetime4 BETWEEN _temp_datetime1 AND _temp_datetime2) AND
                                   (_temp_datetime4 > _temp_datetime3) THEN
                                    INSERT INTO _jadwal VALUES (NULL, _temp_int1, _temp_datetime3, 'k', '', '', 't');
                                    INSERT INTO _jadwal VALUES (NULL, _temp_int1, _temp_datetime4, 'm', '', '', 't');
                                END IF;
                            END IF;
                        END IF;
                    END IF;
                END LOOP read_loop;
                CLOSE cur_jadwalshift;

# SELECT * FROM _jadwal ORDER BY waktu ASC, IF(checking='',IF(masukkeluar='m',0,1),IF(masukkeluar='m',1,0)) ASC;

                # cari apakah shiftsambungan jamkerja pada tanggal sebelumnya?
                CREATE TEMPORARY TABLE IF NOT EXISTS _jadwalbersambung (
                    `analisa`       ENUM('kemarin','besok') NOT NULL,
                    `waktumasuk`    DATETIME,
                    `waktupulang`   DATETIME
                ) ENGINE=Memory;
                TRUNCATE _jadwalbersambung;
                CALL utils_cekjadwalbersambung('kemarin', _tanggal, _idpegawai, _pegawai_idagama);
                CALL utils_cekjadwalbersambung('besok', _tanggal, _idpegawai, _pegawai_idagama);

                # hilangkan masukkeluar yang berulang, contoh: kmkkkkkkkkmk --> mkmk
                # ... hilangkan masuk ketemu masuk
                SET _jadwal_masukkeluar_sebelum=NULL;
                OPEN cur_jadwal_asc;
                read_loop: LOOP
                    SET done=false;
                    FETCH cur_jadwal_asc INTO _jadwal_id,  _jadwal_waktu, _jadwal_masukkeluar;
                    IF done THEN
                        LEAVE read_loop;
                    ELSE
                        IF (ISNULL(_jadwal_masukkeluar_sebelum)=true) OR
                           (ISNULL(_jadwal_masukkeluar_sebelum)=false AND _jadwal_masukkeluar_sebelum <> _jadwal_masukkeluar) THEN
                            SET _jadwal_masukkeluar_sebelum = _jadwal_masukkeluar;
                        ELSE
                            IF _jadwal_masukkeluar='m' THEN
                                DELETE FROM _jadwal WHERE id=_jadwal_id;
                            END IF;
                        END IF;
                    END IF;
                END LOOP read_loop;
                CLOSE cur_jadwal_asc;

# SELECT * FROM _jadwal ORDER BY waktu ASC, IF(checking='',IF(masukkeluar='m',0,1),IF(masukkeluar='m',1,0)) ASC;

                # ... hilangkan keluar ketemu keluar
                SET _jadwal_masukkeluar_sebelum=NULL;
                OPEN cur_jadwal_desc;
                read_loop: LOOP
                    SET done=false;
                    FETCH cur_jadwal_desc INTO _jadwal_id,  _jadwal_waktu, _jadwal_masukkeluar;
                    IF done THEN
                        LEAVE read_loop;
                    ELSE
                        IF (ISNULL(_jadwal_masukkeluar_sebelum)=true) OR
                           (ISNULL(_jadwal_masukkeluar_sebelum)=false AND _jadwal_masukkeluar_sebelum <> _jadwal_masukkeluar) THEN
                            SET _jadwal_masukkeluar_sebelum = _jadwal_masukkeluar;
                        ELSE
                            IF _jadwal_masukkeluar='k' THEN
                                DELETE FROM _jadwal WHERE id=_jadwal_id;
                            END IF;
                        END IF;
                    END IF;
                END LOOP read_loop;
                CLOSE cur_jadwal_desc;

# SELECT * FROM _jadwal ORDER BY waktu ASC, IF(checking='',IF(masukkeluar='m',0,1),IF(masukkeluar='m',1,0)) ASC;

                # hilangkan jadwal mk atau km (sepasang) yang jam-nya sama
                SET _jadwal_id_sebelum=NULL;
                SET _jadwal_waktu_sebelum=NULL;
                OPEN cur_jadwal_asc;
                read_loop: LOOP
                    SET done=false;
                    FETCH cur_jadwal_asc INTO _jadwal_id,  _jadwal_waktu, _jadwal_masukkeluar;
                    IF done THEN
                        LEAVE read_loop;
                    ELSE
                        IF ISNULL(_jadwal_id_sebelum)=false AND ISNULL(_jadwal_waktu_sebelum)=false THEN
                            IF _jadwal_waktu_sebelum=_jadwal_waktu THEN
                                DELETE FROM _jadwal WHERE id=_jadwal_id_sebelum;
                                DELETE FROM _jadwal WHERE id=_jadwal_id;

                                SET _jadwal_id_sebelum=NULL;
                                SET _jadwal_waktu_sebelum=NULL;
                            END IF;
                        END IF;
                        SET _jadwal_id_sebelum = _jadwal_id;
                        SET _jadwal_waktu_sebelum = _jadwal_waktu;
                    END IF;
                END LOOP read_loop;
                CLOSE cur_jadwal_asc;

# SELECT * FROM _jadwal ORDER BY waktu ASC, IF(checking='',IF(masukkeluar='m',0,1),IF(masukkeluar='m',1,0)) ASC;
            END IF;
        END IF;
    END IF;

    # hitung _rekapabsen_jadwallamakerja
    SET _rekapabsen_jadwallamakerja = 0;
    SET _jadwal_masukkeluar_sebelum=NULL,
        _jadwal_waktu_sebelum=NULL;
    OPEN cur_jadwal_asc;
    read_loop: LOOP
        SET done=false;
        FETCH cur_jadwal_asc INTO _jadwal_id,  _jadwal_waktu, _jadwal_masukkeluar;
        IF done THEN
            LEAVE read_loop;
        ELSE
            IF _jadwal_masukkeluar='m' THEN
                IF ISNULL(_jadwal_masukkeluar_sebelum)=true THEN
                    SET _jadwal_masukkeluar_sebelum = _jadwal_masukkeluar;
                    SET _jadwal_waktu_sebelum = _jadwal_waktu;
                END IF;
            ELSEIF _jadwal_masukkeluar='k' THEN
                IF ISNULL(_jadwal_masukkeluar_sebelum)=false THEN
                    SET _rekapabsen_jadwallamakerja = _rekapabsen_jadwallamakerja + TIMESTAMPDIFF(SECOND, _jadwal_waktu_sebelum, _jadwal_waktu);
                    SET _jadwal_masukkeluar_sebelum=NULL,
                        _jadwal_waktu_sebelum=NULL;
                END IF;
            END IF;
        END IF;
    END LOOP read_loop;
    CLOSE cur_jadwal_asc;
END//

# output berupa table _logabsen yang sudah dieliminasi
DROP PROCEDURE IF EXISTS posting_eliminasilogabsen//
CREATE PROCEDURE posting_eliminasilogabsen()
BEGIN
    DECLARE done INT DEFAULT FALSE;

    DECLARE _pertama_id INT UNSIGNED;

    DECLARE _logabsen_id, _logabsen_id_sebelum INT UNSIGNED;
    DECLARE _logabsen_waktu DATETIME;
    DECLARE _logabsen_masukkeluar, _logabsen_masukkeluar_sebelum ENUM('m','k');
    DECLARE _logabsen_idalasan, _logabsen_idalasan_sebelum INT UNSIGNED;
    DECLARE _logabsen_terhitungkerja, _logabsen_terhitungkerja_sebelum ENUM('y','t');

    DECLARE cur_logabsen CURSOR FOR
    SELECT
        id,
        waktu,
        masukkeluar,
        idalasan,
        terhitungkerja
    FROM
        _logabsen
    ORDER BY
        waktu ASC,
        masukkeluar DESC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    # table _logabsen sudah diisi pada procedure posting_persiapanlogabsen
    # Untuk jaga2 saja: supaya tdk error, table _logabsen di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen (
        `id`                INT UNSIGNED AUTO_INCREMENT,
        `idlogabsen`        INT UNSIGNED,
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `idalasan`          INT UNSIGNED,
        `terhitungkerja`    ENUM('y','t'),
        `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
        `del`               ENUM('y','t'),
        INDEX `idx__log_waktu` (`waktu`),
        INDEX `idx__log_masukkeluar` (`masukkeluar`),
        INDEX `idx__log_del` (`del`),
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    # pastikan record yang pertama adalah 'm' +
    # hilangkan semua yang terhitungkerja='y' +
    # hilangkan alasan keluar dan masuk adalah sama (berpasangan)
    SET _pertama_id=NULL;
    SET _logabsen_id_sebelum=NULL,
        _logabsen_masukkeluar_sebelum=NULL,
        _logabsen_idalasan_sebelum=NULL,
        _logabsen_terhitungkerja_sebelum=NULL;
    OPEN cur_logabsen;
    read_loop: LOOP
        SET done=false;
        FETCH cur_logabsen INTO _logabsen_id, _logabsen_waktu, _logabsen_masukkeluar, _logabsen_idalasan, _logabsen_terhitungkerja;
        IF done THEN
            LEAVE read_loop;
        ELSE
            IF _logabsen_masukkeluar='m' AND _logabsen_masukkeluar_sebelum='k' AND
               _logabsen_terhitungkerja='y' AND _logabsen_terhitungkerja_sebelum='y' AND
               ISNULL(_logabsen_idalasan)=false AND ISNULL(_logabsen_idalasan_sebelum)=false AND
               _logabsen_idalasan=_logabsen_idalasan_sebelum THEN
                UPDATE _logabsen SET del='y' WHERE id=_logabsen_id_sebelum OR id=_logabsen_id;
                SET _logabsen_id_sebelum=NULL,
                    _logabsen_masukkeluar_sebelum = NULL,
                    _logabsen_terhitungkerja_sebelum = NULL,
                    _logabsen_idalasan_sebelum = NULL;
            ELSE
                IF NOT (ISNULL(_pertama_id)=true AND _logabsen_masukkeluar='k') THEN
                    IF ISNULL(_pertama_id)=true THEN
                        SET _pertama_id=_logabsen_id;
                    END IF;
                    SET _logabsen_id_sebelum=_logabsen_id,
                        _logabsen_masukkeluar_sebelum = _logabsen_masukkeluar,
                        _logabsen_idalasan_sebelum = _logabsen_idalasan,
                        _logabsen_terhitungkerja_sebelum = _logabsen_terhitungkerja;
                ELSE
                    UPDATE _logabsen SET del='y' WHERE id=_logabsen_id;
                END IF;
            END IF;
        END IF;
    END LOOP read_loop;
    CLOSE cur_logabsen;
    DELETE FROM _logabsen WHERE del='y';

    # hilangkan masukkeluar yang berulang dan harus dimulai dari masuk, contoh: kmkkkkkkkkmk --> mkmk
    SET _logabsen_masukkeluar_sebelum=NULL;
    OPEN cur_logabsen;
    read_loop: LOOP
        SET done=false;
        FETCH cur_logabsen INTO _logabsen_id, _logabsen_waktu, _logabsen_masukkeluar, _logabsen_idalasan, _logabsen_terhitungkerja;
        IF done THEN
            LEAVE read_loop;
        ELSE
            IF (ISNULL(_logabsen_masukkeluar_sebelum)=true AND _logabsen_masukkeluar = 'm') OR
               (ISNULL(_logabsen_masukkeluar_sebelum)=false AND _logabsen_masukkeluar_sebelum <> _logabsen_masukkeluar) THEN
                SET _logabsen_masukkeluar_sebelum = _logabsen_masukkeluar;
            ELSE
                UPDATE _logabsen SET del='y' WHERE id=_logabsen_id;
            END IF;
        END IF;
    END LOOP read_loop;
    CLOSE cur_logabsen;
    DELETE FROM _logabsen WHERE del='y';

    # pastikan record terakhir adalah keluar
    SET _logabsen_id=NULL, _logabsen_masukkeluar=NULL;
    SELECT id, masukkeluar INTO _logabsen_id, _logabsen_masukkeluar FROM _logabsen ORDER BY waktu DESC, masukkeluar DESC LIMIT 1;
    IF ISNULL(_logabsen_masukkeluar)=false AND _logabsen_masukkeluar='m' THEN
        DELETE FROM _logabsen WHERE id=_logabsen_id;
    END IF;
END //

DROP PROCEDURE IF EXISTS cari_jadwalshift_24jam//
CREATE PROCEDURE cari_jadwalshift_24jam(
                                        IN _analisa ENUM('sebelum','sesudah'),
                                        IN _tanggal DATE,
                                        IN _idpegawai INT UNSIGNED,
                                        OUT _interval INT UNSIGNED
                                      )
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _lanjut VARCHAR(1) DEFAULT 't';

    DECLARE _i INT;

    DECLARE _temp_int1 INT;
    DECLARE _temp_int2 INT;
    DECLARE _temp_time1 TIME;
    DECLARE _temp_time2 TIME;
    DECLARE _temp_time3 TIME;
    DECLARE _temp_time4 TIME;
    DECLARE _temp_datetime1 DATETIME;
    DECLARE _temp_datetime2 DATETIME;

    DECLARE _jadwal_lamakerja INT UNSIGNED DEFAULT NULL;
    DECLARE _jadwal_id INT UNSIGNED DEFAULT NULL;
    DECLARE _jadwal_waktu, _jadwal_waktu_sebelum DATETIME DEFAULT NULL;
    DECLARE _jadwal_masukkeluar, _jadwal_masukkeluar_sebelum ENUM('m','k');

    DECLARE cur_temp_jadwalshift CURSOR FOR
        SELECT
            idjamkerjashift,
            jammasuk,
            jampulang,
            jamistirahatmulai,
            jamistirahatselesai
        FROM
            _temp_jamkerjashiftdetail
        ORDER BY
            jammasuk ASC,
            jampulang ASC;

    DECLARE cur_temp_jadwal_asc CURSOR FOR
        SELECT
            id,
            waktu,
            masukkeluar
        FROM
            _temp_jadwal
        ORDER BY
            waktu ASC,
            IF(masukkeluar='m',1,0) ASC
        ;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET _interval = 24;
    IF _analisa='sebelum' OR _analisa='sesudah' THEN
        # persiapkan jamkerjashiftdetail
        CREATE TEMPORARY TABLE IF NOT EXISTS _temp_jamkerjashiftdetail
        (
            `idjamkerjashift`       INT(11) UNSIGNED NOT NULL,
            `jammasuk`              TIME NOT NULL,
            `jampulang`             TIME NOT NULL,
            `jamistirahatmulai`     TIME,
            `jamistirahatselesai`   TIME
        ) Engine=Memory;

        # persiapkan jadwal
        CREATE TEMPORARY TABLE IF NOT EXISTS _temp_jadwal (
            `id`                    INT UNSIGNED AUTO_INCREMENT,
            `idjamkerjashift`       INT UNSIGNED,
            `waktu`                 DATETIME,
            `masukkeluar`           ENUM('m','k'),
            `checking`              ENUM('', 'start','end'), # start: absen masuk, end: absen pulang (dalam satu sesi selain istirahat)
            `shiftpertamaterakhir`  ENUM('', 'pertama', 'terakhir'), # apakah shift tsb adalah shift pertama atau terakhir pada tanggal tsb?
            `shiftsambungan`        ENUM('y','t'), # apakah shift tsb terdapat sambungan dari shift sebelumnya/sesudahnya yg telah dijadwalkan?
            PRIMARY KEY(id)
        ) ENGINE=Memory;
        TRUNCATE _temp_jadwal;

        SET _i = 0;
        REPEAT
            SET _lanjut = 't';
            IF _analisa='sebelum' THEN
                SET _tanggal=SUBDATE(_tanggal, INTERVAL 1 DAY);
            ELSEIF _analisa='sesudah' THEN
                SET _tanggal=ADDDATE(_tanggal, INTERVAL 1 DAY);
            END IF;

            TRUNCATE _temp_jamkerjashiftdetail;
            TRUNCATE _temp_jadwal;

            INSERT INTO _temp_jamkerjashiftdetail
                SELECT
                    x.idjamkerjashift,
                    x.jammasuk,
                    x.jampulang,
                    x.jamistirahatmulai,
                    x.jamistirahatselesai
                FROM
                    jadwalshift js,
                    (
                        SELECT
                            idjamkerjashift,
                            jammasuk,
                            jampulang,
                            jamistirahatmulai,
                            jamistirahatselesai
                        FROM
                            jamkerjashiftdetail
                        WHERE
                            (idjamkerjashift, berlakumulai) IN (SELECT idjamkerjashift, MAX(berlakumulai) FROM jamkerjashiftdetail WHERE berlakumulai<=_tanggal GROUP BY idjamkerjashift)
                    ) x
                WHERE
                    js.idjamkerjashift=x.idjamkerjashift AND
                    js.tanggal=_tanggal AND
                    js.idpegawai=_idpegawai
                ORDER BY
                    x.jammasuk ASC,
                    x.jampulang ASC;

            OPEN cur_temp_jadwalshift;
            read_loop: LOOP
                SET done=false;
                FETCH cur_temp_jadwalshift INTO _temp_int1, _temp_time1, _temp_time2, _temp_time3, _temp_time4;
                IF done THEN
                    LEAVE read_loop;
                ELSE
                    IF ISNULL(_temp_time2)=false AND ISNULL(_temp_time1)=false THEN
                        CALL time2datetime(_tanggal, _temp_time1, _temp_time2, _temp_datetime1, _temp_datetime2);

                        INSERT INTO _temp_jadwal VALUES (NULL, _temp_int1, _temp_datetime1, 'm', 'start', '', 't');

                        # cek dahulu, apakah ada waktu keluar diantara jadwal jam kerja lain.
                        SET _temp_int2=0;
                        SELECT
                            1 INTO _temp_int2
                        FROM
                            _temp_jamkerjashiftdetail
                        WHERE
                            _temp_datetime2>=time2datetime_f(_tanggal, jammasuk, jampulang, 1) AND
                            _temp_datetime2<time2datetime_f(_tanggal, jammasuk, jampulang, 2) AND
                            idjamkerjashift<>_temp_int1
                        LIMIT 1;
                        IF _temp_int2=0 THEN
                            INSERT INTO _temp_jadwal VALUES (NULL, _temp_int1, _temp_datetime2, 'k', 'end', '', 't');
                        END IF;
                    END IF;
                END IF;
            END LOOP read_loop;
            CLOSE cur_temp_jadwalshift;

            # hilangkan masukkeluar yang berulang, contoh: kmkkkkkkkkmk --> mkmk
            # ... hilangkan masuk ketemu masuk
            SET _jadwal_masukkeluar_sebelum=NULL;
            OPEN cur_temp_jadwal_asc;
            read_loop: LOOP
                SET done=false;
                FETCH cur_temp_jadwal_asc INTO _jadwal_id,  _jadwal_waktu, _jadwal_masukkeluar;
                IF done THEN
                    LEAVE read_loop;
                ELSE
                    IF (ISNULL(_jadwal_masukkeluar_sebelum)=true) OR
                       (ISNULL(_jadwal_masukkeluar_sebelum)=false AND _jadwal_masukkeluar_sebelum <> _jadwal_masukkeluar) THEN
                        SET _jadwal_masukkeluar_sebelum = _jadwal_masukkeluar;
                    ELSE
                        IF _jadwal_masukkeluar='m' THEN
                            DELETE FROM _temp_jadwal WHERE id=_jadwal_id;
                        END IF;
                    END IF;
                END IF;
            END LOOP read_loop;
            CLOSE cur_temp_jadwal_asc;

            #hitung lama kerja
            SET _jadwal_lamakerja = 0;
            SET _jadwal_masukkeluar_sebelum=NULL,
                _jadwal_waktu_sebelum=NULL;
            OPEN cur_temp_jadwal_asc;
            read_loop: LOOP
                SET done=false;
                FETCH cur_temp_jadwal_asc INTO _jadwal_id,  _jadwal_waktu, _jadwal_masukkeluar;
                IF done THEN
                    LEAVE read_loop;
                ELSE
                    IF _jadwal_masukkeluar='m' THEN
                        IF ISNULL(_jadwal_masukkeluar_sebelum)=true THEN
                            SET _jadwal_masukkeluar_sebelum = _jadwal_masukkeluar;
                            SET _jadwal_waktu_sebelum = _jadwal_waktu;
                        END IF;
                    ELSEIF _jadwal_masukkeluar='k' THEN
                        IF ISNULL(_jadwal_masukkeluar_sebelum)=false THEN
                            SET _jadwal_lamakerja = _jadwal_lamakerja + TIMESTAMPDIFF(SECOND, _jadwal_waktu_sebelum, _jadwal_waktu);
                            SET _jadwal_masukkeluar_sebelum=NULL,
                                _jadwal_waktu_sebelum=NULL;
                        END IF;
                    END IF;
                END IF;
            END LOOP read_loop;
            CLOSE cur_temp_jadwal_asc;

            IF _jadwal_lamakerja>=24*60*60 THEN
                SET _lanjut = 'y';
                SET _interval = _interval + 24;
            END IF;

            SET _i=_i+1;

        UNTIL (_lanjut='t') OR (_i=10) END REPEAT;

    END IF;
END//

# output berupa table _logabsen
DROP PROCEDURE IF EXISTS posting_persiapanlogabsen//
CREATE PROCEDURE posting_persiapanlogabsen( IN _tanggal DATE,
                                            IN _idpegawai INT UNSIGNED,
                                            IN _jamkerja_jenis ENUM('full','shift'),
                                            IN _jadwal_toleransi INT,
                                            IN _jadwal_acuanterlambat enum('jadwal','toleransi'),
                                            IN _pegawai_flexytime ENUM('y','t'),
                                            INOUT _terapkan_flexytime ENUM('y','t'),
                                            INOUT _jadwal_waktupulang_normal DATETIME,
                                            INOUT _jadwal_waktupulang_flexytime DATETIME,
                                            INOUT _rekapabsen_masukkerja ENUM('y','t'),
                                            INOUT _rekapabsen_idalasanmasuk INT UNSIGNED,
                                            INOUT _rekapabsen_waktumasuk DATETIME,
                                            INOUT _rekapabsen_waktukeluar DATETIME,
                                            INOUT _rekapabsen_selisihmasuk INT,
                                            INOUT _kurangabsen_masuk ENUM('y','t'),
                                            INOUT _kurangabsen_keluar ENUM('y','t'),
                                            INOUT _flag_terlambat ENUM('','y','t')
                                          )
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _temp_int INT;
    DECLARE _temp_time1 TIME;
    DECLARE _temp_datetime1 DATETIME;
    DECLARE _temp_datetime2 DATETIME;
    DECLARE _temp_datetime3 DATETIME;
    DECLARE _temp_masukkeluar1, _temp_masukkeluar2, _temp_masukkeluar3 ENUM('m','k');
    DECLARE _end_of_day_awal DATETIME;
    DECLARE _end_of_day_akhir DATETIME;

    DECLARE _temp_rekapabsen_masukkerja ENUM('y','t');
    DECLARE _temp_rekapabsen_idalasanmasuk INT UNSIGNED;
    DECLARE _temp_rekapabsen_waktumasuk DATETIME;

    DECLARE _logabsen_id INT UNSIGNED;
    DECLARE _logabsen_masukkeluar ENUM('m','k');
    DECLARE _logabsen_idalasan INT UNSIGNED;
    DECLARE _logabsen_terhitungkerja ENUM('y','t');

    DECLARE _jadwal_id INT UNSIGNED;
    DECLARE _jadwal_waktu DATETIME;
    DECLARE _jadwal_masukkeluar ENUM('m','k');
    DECLARE _jadwal_shiftsambungan ENUM('y','t');

    DECLARE _interval INT UNSIGNED DEFAULT 0;
    DECLARE _interval_end_of_day_awal INT UNSIGNED DEFAULT 12;
    DECLARE _interval_end_of_day_akhir INT UNSIGNED DEFAULT 12;

    DECLARE _temp_flag_terlambat ENUM('','y','t') DEFAULT '';
    DECLARE _temp_rekapabsen_selisihmasuk INT DEFAULT 0;
    DECLARE _idjadwal_terakhir INT UNSIGNED DEFAULT 0;

    DECLARE cur_logabsen CURSOR FOR
        SELECT
            id,
            waktu,
            masukkeluar,
            idalasan,
            terhitungkerja
        FROM
            _logabsen
        ORDER BY
            waktu ASC,
            masukkeluar DESC;

    DECLARE cur_jadwal_checking CURSOR FOR
        SELECT
            id,
            waktu,
            masukkeluar
        FROM
            _jadwal
        WHERE
            checking='start'
        ORDER BY
            waktu ASC,
            masukkeluar ASC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET _terapkan_flexytime = 't';
    SET _temp_rekapabsen_selisihmasuk = 0;
    SET _rekapabsen_selisihmasuk = 0;
    SET _kurangabsen_masuk = 't';
    SET _kurangabsen_keluar = 't';

    CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen_all (
        `idlogabsen`        INT UNSIGNED,
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `idalasan`          INT UNSIGNED,
        `terhitungkerja`    ENUM('y','t'),
        `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
        `status`            ENUM('','v','c','na') NOT NULL
    ) ENGINE=Memory;
    TRUNCATE _logabsen_all;

    CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen (
        `id`                INT UNSIGNED AUTO_INCREMENT,
        `idlogabsen`        INT UNSIGNED,
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `idalasan`          INT UNSIGNED,
        `terhitungkerja`    ENUM('y','t'),
        `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
        `del`               ENUM('y','t'),
        INDEX `idx__log_waktu` (`waktu`),
        INDEX `idx__log_masukkeluar` (`masukkeluar`),
        INDEX `idx__log_del` (`del`),
        PRIMARY KEY(id)
    ) ENGINE=Memory;
    TRUNCATE _logabsen;

    # table _jadwal sudah diisi pada procedure posting_persiapanjadwal
    # Untuk jaga2 saja: supaya tdk error, table _jadwal di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwal (
        `id`                    INT UNSIGNED AUTO_INCREMENT,
        `idjamkerjashift`       INT UNSIGNED,
        `waktu`                 DATETIME,
        `masukkeluar`           ENUM('m','k'),
        `checking`              ENUM('', 'start','end'), # start: absen masuk, end: absen pulang (dalam satu sesi selain istirahat)
        `shiftpertamaterakhir`  ENUM('', 'pertama', 'terakhir'), # apakah shift tsb adalah shift pertama atau terakhir pada tanggal tsb?
        `shiftsambungan`        ENUM('y','t'), # apakah shift tsb terdapat sambungan dari shift sebelumnya/sesudahnya yg telah dijadwalkan?
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    IF (_jamkerja_jenis='full') THEN
        # _end_of_day_awal dan _end_of_day_akhir diambil dari pengaturan
        SELECT end_of_day INTO _temp_time1 FROM pengaturan LIMIT 1;
        IF _temp_time1>='12:00:00' THEN
            SET _end_of_day_awal = DATE_SUB(STR_TO_DATE(CONCAT(_tanggal,' ',IFNULL(_temp_time1, '00:00:00')),'%Y-%m-%d %H:%i:%s'), INTERVAL 1 DAY);
            SET _end_of_day_akhir = STR_TO_DATE(CONCAT(_tanggal,' ',IFNULL(_temp_time1, '00:00:00')),'%Y-%m-%d %H:%i:%s');
        ELSE
            SET _end_of_day_awal = STR_TO_DATE(CONCAT(_tanggal,' ',IFNULL(_temp_time1, '00:00:00')),'%Y-%m-%d %H:%i:%s');
            SET _end_of_day_akhir = DATE_ADD(STR_TO_DATE(CONCAT(_tanggal,' ',IFNULL(_temp_time1, '00:00:00')),'%Y-%m-%d %H:%i:%s'), INTERVAL 1 DAY);
        END IF;

        INSERT INTO _logabsen_all
            SELECT
                id,
                waktu,
                masukkeluar,
                idalasanmasukkeluar,
                terhitungkerja,
                flag,
                status
            FROM
                logabsen
            WHERE
                idpegawai=_idpegawai AND
                waktu > _end_of_day_awal AND waktu <= _end_of_day_akhir;

        INSERT INTO _logabsen
            SELECT
                NULL,
                idlogabsen,
                waktu,
                masukkeluar,
                idalasan,
                terhitungkerja,
                flag,
                't'
            FROM
                _logabsen_all
            WHERE
                status='v';

        SET _temp_masukkeluar1 = NULL;
        SELECT masukkeluar INTO _temp_masukkeluar1 FROM _logabsen ORDER BY waktu ASC LIMIT 1;
        IF (_temp_masukkeluar1='k') THEN
            SET _kurangabsen_masuk = 'y';
        END IF;

        SET _temp_masukkeluar1 = NULL;
        SELECT masukkeluar INTO _temp_masukkeluar1 FROM _logabsen ORDER BY waktu DESC LIMIT 1;
        IF (_temp_masukkeluar1='m') THEN
            SET _kurangabsen_keluar = 'y';
        END IF;

    ELSEIF (_jamkerja_jenis='shift') THEN
        # _end_of_day_awal dan _end_of_day_akhir diambil dari _jadwal
        SELECT waktu, shiftsambungan INTO _end_of_day_awal, _jadwal_shiftsambungan FROM _jadwal ORDER BY waktu ASC LIMIT 1;
        IF _jadwal_shiftsambungan='y' THEN
            CALL cari_jadwalshift_24jam('sebelum',_tanggal, _idpegawai, _interval);
        END IF;
        SET _interval_end_of_day_awal = 12 + _interval;

        SELECT waktu, shiftsambungan INTO _end_of_day_akhir, _jadwal_shiftsambungan FROM _jadwal ORDER BY waktu DESC LIMIT 1;
        IF _jadwal_shiftsambungan='y' THEN
            CALL cari_jadwalshift_24jam('sesudah',_tanggal, _idpegawai, _interval);
        END IF;
        SET _interval_end_of_day_akhir = 12 + _interval;

        INSERT INTO _logabsen_all
            SELECT
                id,
                waktu,
                masukkeluar,
                idalasanmasukkeluar,
                terhitungkerja,
                flag,
                status
            FROM
                logabsen
            WHERE
                idpegawai=_idpegawai AND
                waktu > DATE_SUB(_end_of_day_awal, INTERVAL _interval_end_of_day_awal HOUR) AND waktu <= DATE_ADD(_end_of_day_akhir, INTERVAL _interval_end_of_day_akhir HOUR);

        # persiapkan _logabsen_eliminasi
        INSERT INTO _logabsen
            SELECT
                NULL,
                idlogabsen,
                waktu,
                masukkeluar,
                idalasan,
                terhitungkerja,
                flag,
                't'
            FROM
                _logabsen_all
            WHERE
                status='v';

        SET _temp_masukkeluar1 = NULL;
        SELECT masukkeluar INTO _temp_masukkeluar1 FROM _logabsen ORDER BY waktu ASC LIMIT 1;
        IF (_temp_masukkeluar1='k') THEN
            SET _kurangabsen_masuk = 'y';
        END IF;

        SET _temp_masukkeluar1 = NULL;
        SELECT masukkeluar INTO _temp_masukkeluar1 FROM _logabsen ORDER BY waktu DESC LIMIT 1;
        IF (_temp_masukkeluar1='m') THEN
            SET _kurangabsen_keluar = 'y';
        END IF;

        # buat percobaan _logabsen_raw
        CALL posting_eliminasilogabsen();

        CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen_eliminasi (
            `id`                INT UNSIGNED AUTO_INCREMENT,
            `idlogabsen`        INT UNSIGNED,
            `waktu`             DATETIME,
            `masukkeluar`       ENUM('m','k'),
            `idalasan`          INT UNSIGNED,
            `terhitungkerja`    ENUM('y','t'),
            `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
            `del`               ENUM('y','t'),
            INDEX `idx__log_waktu` (`waktu`),
            INDEX `idx__log_masukkeluar` (`masukkeluar`),
            INDEX `idx__log_del` (`del`),
            PRIMARY KEY(id)
        ) ENGINE=Memory;
        TRUNCATE _logabsen_eliminasi;

        INSERT INTO _logabsen_eliminasi SELECT * FROM _logabsen;

        TRUNCATE _logabsen;
        # ini baru proses sebenarnya
        INSERT INTO _logabsen
            SELECT
                NULL,
                idlogabsen,
                waktu,
                masukkeluar,
                idalasan,
                terhitungkerja,
                flag,
                't'
            FROM
                _logabsen_eliminasi
            WHERE
                waktu > _end_of_day_awal AND waktu < _end_of_day_akhir;

        # tambahakan _logabsen_eliminasi sebelum _end_of_day_awal jika yang pertama adalah MASUK
        SET _logabsen_id=NULL, _logabsen_masukkeluar=NULL;
        SELECT id, masukkeluar INTO _logabsen_id, _logabsen_masukkeluar FROM _logabsen_eliminasi WHERE waktu<=_end_of_day_awal  ORDER BY waktu DESC LIMIT 1;
        IF (_logabsen_masukkeluar='m') THEN
            INSERT INTO _logabsen
                SELECT
                    NULL,
                    idlogabsen,
                    waktu,
                    masukkeluar,
                    idalasan,
                    terhitungkerja,
                    flag,
                    't'
                FROM
                    _logabsen_eliminasi
                WHERE
                    id=_logabsen_id;
        END IF;

        # tambahakan _logabsen_eliminasi sesudah _end_of_day_akhir jika yang pertama adalah KELUAR
        SET _logabsen_id=NULL, _logabsen_masukkeluar=NULL;
        SELECT id, masukkeluar INTO _logabsen_id, _logabsen_masukkeluar FROM _logabsen_eliminasi WHERE waktu>=_end_of_day_akhir ORDER BY waktu ASC LIMIT 1;
        IF (_logabsen_masukkeluar='k') THEN
            INSERT INTO _logabsen
                SELECT
                    NULL,
                    idlogabsen,
                    waktu,
                    masukkeluar,
                    idalasan,
                    terhitungkerja,
                    flag,
                    't'
                FROM
                    _logabsen_eliminasi
                WHERE
                    id=_logabsen_id;
        END IF;
    END IF;

    # hitung _rekapabsen_selisihmasuk, ini hanya sebagai cadangan saja jika pada saat itu hanya ada absen masuk, tidak ada absen keluar
    SET _temp_masukkeluar1 = NULL,
        _temp_datetime1 = NULL,
        _temp_masukkeluar2 = NULL,
        _temp_datetime2 = NULL,
        _temp_rekapabsen_masukkerja = 't',
        _temp_rekapabsen_idalasanmasuk = NULL,
        _temp_rekapabsen_waktumasuk = NULL,
        _temp_flag_terlambat = '';

    SELECT masukkeluar, waktu INTO _temp_masukkeluar1, _temp_datetime1 FROM _jadwal ORDER BY waktu ASC LIMIT 1;
    SELECT masukkeluar, waktu INTO _temp_masukkeluar2, _temp_datetime2 FROM _jadwal ORDER BY waktu DESC LIMIT 1;
    IF _temp_masukkeluar1='m' AND _temp_masukkeluar2='k' THEN
        # ambil log absen pertama sebelum jadwal jam masuk
        SET _temp_masukkeluar3 = NULL,
            _temp_datetime3 = NULL,
            _temp_int = NULL;

        SELECT
            masukkeluar, waktu, idalasanmasukkeluar INTO
            _temp_masukkeluar3, _temp_datetime3, _temp_int
        FROM
            logabsen
        WHERE
            status='v' AND
            idpegawai=_idpegawai AND
            waktu<=_temp_datetime1 AND
            waktu>=DATE_SUB(_temp_datetime1, INTERVAL _interval_end_of_day_awal HOUR)
        ORDER BY
            waktu DESC
        LIMIT 1;

        # pastikan log absen pertama tsb adalah M
        IF NOT (ISNULL(_temp_masukkeluar3)=false AND _temp_masukkeluar3='m') THEN
            # ambil log absen pertama setelah jadwal jam masuk dan sebelum jadwal jam pulang
            SET _temp_masukkeluar3 = NULL,
                _temp_datetime3 = NULL,
                _temp_int = NULL;

            SELECT
                masukkeluar, waktu, idalasanmasukkeluar, IF(flag='tidak-terlambat','y','') INTO
                _temp_masukkeluar3, _temp_datetime3, _temp_int, _temp_flag_terlambat
            FROM
                logabsen
            WHERE
                status='v' AND
                idpegawai=_idpegawai AND
                waktu>_temp_datetime1 AND
                waktu<=_temp_datetime2
            ORDER BY
                waktu ASC
            LIMIT 1;
        END IF;

        IF (ISNULL(_temp_masukkeluar3)=false AND _temp_masukkeluar3='m') THEN
            # selisih masuk = jadwal dikurangi logabsen
            SET _temp_rekapabsen_selisihmasuk = TIMESTAMPDIFF(SECOND, _temp_datetime3, _temp_datetime1);
            IF _temp_rekapabsen_selisihmasuk<0 THEN
                # jika pegawai terlambat, pegawai pakai flexytime, dan jamkerja adalah full, maka ubah jadwalnya!
                IF _pegawai_flexytime='y' AND _jamkerja_jenis='full' THEN
                    # _end_of_day_akhir
                    SET _idjadwal_terakhir = NULL;
                    SELECT id, waktu INTO _idjadwal_terakhir, _jadwal_waktupulang_normal FROM _jadwal WHERE masukkeluar='k' AND checking='end' AND waktu<=_end_of_day_akhir ORDER BY waktu DESC LIMIT 1;
                    IF ISNULL(_idjadwal_terakhir)=false THEN
                        SET _jadwal_waktupulang_flexytime = ADDDATE(_jadwal_waktupulang_normal, INTERVAL -1*_temp_rekapabsen_selisihmasuk SECOND);
                        UPDATE _jadwal SET waktu = _jadwal_waktupulang_flexytime WHERE id = _idjadwal_terakhir;
                        SET _terapkan_flexytime = 'y';
                    END IF;
                END IF;

                IF -1*_temp_rekapabsen_selisihmasuk<_jadwal_toleransi*60 THEN
                    SET _temp_rekapabsen_selisihmasuk = 0;
                ELSE
                    # untuk handle acuan terlambat berdasarkan toleransi atau jadwal
                    IF _jadwal_acuanterlambat='toleransi' THEN
                        SET _temp_rekapabsen_selisihmasuk = _temp_rekapabsen_selisihmasuk + (_jadwal_toleransi*60);
                    END IF;
                END IF;
            END IF;
            IF _temp_flag_terlambat<>'y' THEN
                SET _rekapabsen_selisihmasuk = _temp_rekapabsen_selisihmasuk;
            END IF;
            SET _temp_rekapabsen_masukkerja='y';
            SET _temp_rekapabsen_idalasanmasuk=_temp_int;
            SET _temp_rekapabsen_waktumasuk=_temp_datetime3;
        END IF;
    END IF;

    CALL posting_eliminasilogabsen();

    # isikan paramter OUT rekapabsen
    SET _rekapabsen_masukkerja = 't',
        _rekapabsen_idalasanmasuk = NULL,
        _rekapabsen_waktumasuk = NULL,
        _rekapabsen_waktukeluar = NULL;

    SELECT
        'y',
        idalasan,
        waktu
        INTO
        _rekapabsen_masukkerja,
        _rekapabsen_idalasanmasuk,
        _rekapabsen_waktumasuk
    FROM
        _logabsen
    WHERE
        masukkeluar='m'
    ORDER BY
        waktu ASC
    LIMIT 1;

    SELECT
        waktu INTO _rekapabsen_waktukeluar
    FROM
        _logabsen
    WHERE
        masukkeluar='k'
    ORDER BY
        waktu DESC
    LIMIT 1;

    IF _rekapabsen_masukkerja='y' THEN
        # checking... perbaiki waktu, jika masuk terlambat tetapi ada alasan terhitung kerja
        OPEN cur_jadwal_checking;
        read_loop: LOOP
            SET done=false;
            FETCH cur_jadwal_checking INTO _jadwal_id, _jadwal_waktu, _jadwal_masukkeluar;
            IF done THEN
                LEAVE read_loop;
            ELSE
                # cari end checking...
                SET _temp_datetime1 = NULL;
                SELECT waktu INTO _temp_datetime1 FROM _jadwal WHERE waktu>_jadwal_waktu AND checking='end' ORDER BY waktu ASC LIMIT 1;

                IF (ISNULL(_temp_datetime1)=false) THEN
                    SET _logabsen_id=NULL, _logabsen_masukkeluar=NULL, _logabsen_idalasan=NULL, _logabsen_terhitungkerja=NULL;
                    SELECT
                        id, masukkeluar, idalasan, terhitungkerja INTO
                        _logabsen_id, _logabsen_masukkeluar, _logabsen_idalasan, _logabsen_terhitungkerja
                    FROM
                        _logabsen
                    WHERE
                        waktu>_jadwal_waktu AND
                        waktu<_temp_datetime1
                    ORDER BY
                        waktu ASC
                    LIMIT 1;

                    IF ISNULL(_logabsen_idalasan)=false AND _logabsen_masukkeluar='m' AND _logabsen_terhitungkerja='y' THEN
                        UPDATE _logabsen SET waktu=_jadwal_waktu WHERE id=_logabsen_id;
                    END IF;
                END IF;
            END IF;
        END LOOP read_loop;
        CLOSE cur_jadwal_checking;
    END IF;

    IF (_rekapabsen_masukkerja='t') THEN
        SET _rekapabsen_masukkerja = _temp_rekapabsen_masukkerja;
    END IF;
    IF (ISNULL(_rekapabsen_idalasanmasuk)=TRUE) THEN
        SET _rekapabsen_idalasanmasuk = _temp_rekapabsen_idalasanmasuk;
    END IF;
    IF (ISNULL(_rekapabsen_waktumasuk)=TRUE) THEN
        SET _rekapabsen_waktumasuk = _temp_rekapabsen_waktumasuk;
        SET _flag_terlambat = _temp_flag_terlambat;
    END IF;
END//

# memproses table _jadwal dan _logabsen ,output berupa table _hasil
DROP PROCEDURE IF EXISTS posting_hitungkerja//
CREATE PROCEDURE posting_hitungkerja(INOUT _rekapabsen_lamakerja INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _jadwallogabsen_id INT UNSIGNED DEFAULT NULL;
    DECLARE _jadwallogabsen_idlogabsen INT UNSIGNED DEFAULT NULL;
    DECLARE _jadwallogabsen_idjamkerjashift INT UNSIGNED DEFAULT NULL;
    DECLARE _jadwallogabsen_flag VARCHAR(1) DEFAULT NULL;
    DECLARE _jadwallogabsen_waktu DATETIME DEFAULT NULL;
    DECLARE _jadwallogabsen_masukkeluar ENUM('m','k');

    DECLARE _temp_id INT UNSIGNED;
    DECLARE _temp_flag ENUM('j','p');
    DECLARE _temp_waktu, _temp_waktu_sebelum DATETIME;
    DECLARE _temp_masukkeluar, _temp_masukkeluar_sebelum ENUM('m','k');
    DECLARE _temp_override ENUM('y','t');

    DECLARE _logabsen_waktu_terakhir DATETIME;
    DECLARE _jadwal_waktu_pertama DATETIME;
    DECLARE _jadwal_waktu_terakhir DATETIME;

    DECLARE _masuk_saat_jadwal_keluar BOOLEAN;

    DECLARE cur_jadwallogabsen CURSOR FOR
        SELECT
            id,
            idlogabsen,
            idjamkerjashift,
            flag,
            waktu,
            masukkeluar
        FROM
            _jadwallogabsen
        ORDER BY
            waktu ASC,
            masukkeluar DESC,
            flag ASC;

    DECLARE cur_hasil CURSOR FOR
        SELECT
            id,
            flag,
            waktu,
            masukkeluar,
            override
        FROM
            _hasil
        ORDER BY
            waktu ASC,
            masukkeluar DESC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET _rekapabsen_lamakerja = 0;

    # table _jadwal sudah diisi pada procedure posting_persiapanjadwal
    # Untuk jaga2 saja: supaya tdk error, table _jadwal di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwal (
        `id`                    INT UNSIGNED AUTO_INCREMENT,
        `idjamkerjashift`       INT UNSIGNED,
        `waktu`                 DATETIME,
        `masukkeluar`           ENUM('m','k'),
        `checking`              ENUM('', 'start','end'), # start: absen masuk, end: absen pulang (dalam satu sesi selain istirahat)
        `shiftpertamaterakhir`  ENUM('', 'pertama', 'terakhir'), # apakah shift tsb adalah shift pertama atau terakhir pada tanggal tsb?
        `shiftsambungan`        ENUM('y','t'), # apakah shift tsb terdapat sambungan dari shift sebelumnya/sesudahnya yg telah dijadwalkan?
        PRIMARY KEY(id)
    ) ENGINE=Memory;
    # table _logabsen sudah diisi pada procedure posting_persiapanlogabsen
    # Untuk jaga2 saja: supaya tdk error, table _logabsen di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen (
        `id`                INT UNSIGNED AUTO_INCREMENT,
        `idlogabsen`        INT UNSIGNED,
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `idalasan`          INT UNSIGNED,
        `terhitungkerja`    ENUM('y','t'),
        `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
        `del`               ENUM('y','t'),
        INDEX `idx__log_waktu` (`waktu`),
        INDEX `idx__log_masukkeluar` (`masukkeluar`),
        INDEX `idx__log_del` (`del`),
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    CREATE TEMPORARY TABLE IF NOT EXISTS _hasil (
        `id`                INT UNSIGNED,
        `idlogabsen`        INT UNSIGNED,
        `idjamkerjashift`   INT UNSIGNED,
        `terhitung`         ENUM('k','l'),
        `flag`              ENUM('j','p'),
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `override`          ENUM('y','t'),
        INDEX `idx__absen_waktu` (`waktu`),
        INDEX `idx__absen_masukkeluar` (`masukkeluar`)
    ) ENGINE=Memory;
    TRUNCATE _hasil;

    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwallogabsen (
        `id`                INT UNSIGNED AUTO_INCREMENT,
        `idlogabsen`        INT UNSIGNED,
        `idjamkerjashift`   INT UNSIGNED,
        `flag`              ENUM('j','p'),
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        INDEX `idx__gabungan_jadwal_log_flag` (`flag`),
        INDEX `idx__gabungan_jadwal_log_waktu` (`waktu`),
        INDEX `idx__gabungan_jadwal_log_masukkeluar` (`masukkeluar`),
        PRIMARY KEY(id)
    ) ENGINE=Memory;
    TRUNCATE _jadwallogabsen;

    INSERT INTO _jadwallogabsen SELECT id, idlogabsen, NULL, 'p', waktu, masukkeluar FROM _logabsen;
    INSERT INTO _jadwallogabsen SELECT NULL, NULL, idjamkerjashift, 'j', waktu, masukkeluar FROM _jadwal;
    SET _masuk_saat_jadwal_keluar = false;
    SET _temp_id = NULL,
        _temp_flag = NULL,
        _temp_waktu = NULL,
        _temp_masukkeluar = NULL;

    OPEN cur_jadwallogabsen;
    read_loop: LOOP
        SET done=false;
        FETCH cur_jadwallogabsen INTO _jadwallogabsen_id,
                                      _jadwallogabsen_idlogabsen,
                                      _jadwallogabsen_idjamkerjashift,
                                      _jadwallogabsen_flag,
                                      _jadwallogabsen_waktu,
                                      _jadwallogabsen_masukkeluar;
        IF done THEN
            LEAVE read_loop;
        ELSE
            IF _jadwallogabsen_flag = 'p' THEN
                SET _masuk_saat_jadwal_keluar = false;
                IF _jadwallogabsen_masukkeluar = 'm' THEN
                    IF NOT IFNULL((_temp_masukkeluar = 'k' AND _temp_flag = 'j'),false) THEN
                        IF _temp_masukkeluar = 'm' THEN
                            IF _temp_flag = 'j' AND _temp_waktu <= _jadwallogabsen_waktu THEN
                                DELETE FROM _hasil WHERE id=_temp_id;
                                SET _temp_id = _jadwallogabsen_id,
                                    _temp_flag = _jadwallogabsen_flag,
                                    _temp_waktu = _jadwallogabsen_waktu,
                                    _temp_masukkeluar = _jadwallogabsen_masukkeluar;
                                INSERT INTO _hasil VALUES (_jadwallogabsen_id,
                                                           _jadwallogabsen_idlogabsen,
                                                           _jadwallogabsen_idjamkerjashift,
                                                           'k', # terhitung kerja
                                                           _jadwallogabsen_flag,
                                                           _jadwallogabsen_waktu,
                                                           _jadwallogabsen_masukkeluar,
                                                           'y');
                            END IF;
                        ELSE
                            SET _temp_id = _jadwallogabsen_id,
                                _temp_flag = _jadwallogabsen_flag,
                                _temp_waktu = _jadwallogabsen_waktu,
                                _temp_masukkeluar = _jadwallogabsen_masukkeluar;
                            INSERT INTO _hasil VALUES (_jadwallogabsen_id,
                                                       _jadwallogabsen_idlogabsen,
                                                       _jadwallogabsen_idjamkerjashift,
                                                       'k', # terhitung kerja
                                                       _jadwallogabsen_flag,
                                                       _jadwallogabsen_waktu,
                                                       _jadwallogabsen_masukkeluar,
                                                       't');
                        END IF;
                    ELSE
                        SET _masuk_saat_jadwal_keluar = true;
                    END IF;
                ELSEIF _jadwallogabsen_masukkeluar = 'k' THEN
                    IF NOT IFNULL((_temp_masukkeluar = 'k' AND _temp_flag = 'j'),false) THEN
                        IF _temp_masukkeluar = 'k' THEN
                            IF _temp_flag = 'j' AND _temp_waktu >= _jadwallogabsen_waktu THEN
                                DELETE FROM _hasil WHERE id=_temp_id;
                                SET _temp_id = _jadwallogabsen_id,
                                    _temp_flag = _jadwallogabsen_flag,
                                    _temp_waktu = _jadwallogabsen_waktu,
                                    _temp_masukkeluar = _jadwallogabsen_masukkeluar;
                                INSERT INTO _hasil VALUES (_jadwallogabsen_id,
                                                           _jadwallogabsen_idlogabsen,
                                                           _jadwallogabsen_idjamkerjashift,
                                                           'k', # terhitung kerja
                                                           _jadwallogabsen_flag,
                                                           _jadwallogabsen_waktu,
                                                           _jadwallogabsen_masukkeluar,
                                                           'y');
                            END IF;
                        ELSE
                            SET _temp_id = _jadwallogabsen_id,
                                _temp_flag = _jadwallogabsen_flag,
                                _temp_waktu = _jadwallogabsen_waktu,
                                _temp_masukkeluar = _jadwallogabsen_masukkeluar;
                            INSERT INTO _hasil VALUES (_jadwallogabsen_id,
                                                       _jadwallogabsen_idlogabsen,
                                                       _jadwallogabsen_idjamkerjashift,
                                                       'k', # terhitung kerja
                                                       _jadwallogabsen_flag,
                                                       _jadwallogabsen_waktu,
                                                       _jadwallogabsen_masukkeluar,
                                                       't');
                        END IF;
                    END IF;
                END IF;
            ELSEIF _jadwallogabsen_flag = 'j' THEN
                IF _jadwallogabsen_masukkeluar = 'm' THEN
                    IF _temp_masukkeluar = 'm' THEN
                        IF _temp_flag = 'p' AND _temp_waktu < _jadwallogabsen_waktu THEN
                            DELETE FROM _hasil WHERE id=_temp_id;
                            SET _temp_id = _jadwallogabsen_id,
                                _temp_flag = _jadwallogabsen_flag,
                                _temp_waktu = _jadwallogabsen_waktu,
                                _temp_masukkeluar = _jadwallogabsen_masukkeluar;
                            INSERT INTO _hasil VALUES (_jadwallogabsen_id,
                                                       _jadwallogabsen_idlogabsen,
                                                       _jadwallogabsen_idjamkerjashift,
                                                       'k', # terhitung kerja
                                                       _jadwallogabsen_flag,
                                                       _jadwallogabsen_waktu,
                                                       _jadwallogabsen_masukkeluar,
                                                       'y');
                        END IF;
                    ELSE
                        IF _masuk_saat_jadwal_keluar = false THEN
                            SET _temp_id = _jadwallogabsen_id,
                                _temp_flag = _jadwallogabsen_flag,
                                _temp_waktu = _jadwallogabsen_waktu,
                                _temp_masukkeluar = _jadwallogabsen_masukkeluar;
                            INSERT INTO _hasil VALUES (_jadwallogabsen_id,
                                                       _jadwallogabsen_idlogabsen,
                                                       _jadwallogabsen_idjamkerjashift,
                                                       'k', # terhitung kerja
                                                       _jadwallogabsen_flag,
                                                       _jadwallogabsen_waktu,
                                                       _jadwallogabsen_masukkeluar,
                                                       't');
                        ELSE
                            SET _temp_id = _jadwallogabsen_id,
                                _temp_flag = _jadwallogabsen_flag,
                                _temp_waktu = _jadwallogabsen_waktu,
                                _temp_masukkeluar = _jadwallogabsen_masukkeluar;
                            INSERT INTO _hasil VALUES (_jadwallogabsen_id,
                                                       _jadwallogabsen_idlogabsen,
                                                       _jadwallogabsen_idjamkerjashift,
                                                       'k', # terhitung kerja
                                                       _jadwallogabsen_flag,
                                                       _jadwallogabsen_waktu,
                                                       _jadwallogabsen_masukkeluar,
                                                       'y');
                        END IF;
                    END IF;
                ELSEIF _jadwallogabsen_masukkeluar = 'k' THEN
                    IF _temp_masukkeluar = 'k' THEN
                        IF _temp_flag = 'p' AND _temp_waktu > _jadwallogabsen_waktu THEN
                            DELETE FROM _hasil WHERE id=_temp_id;
                            SET _temp_id = _jadwallogabsen_id,
                                _temp_flag = _jadwallogabsen_flag,
                                _temp_waktu = _jadwallogabsen_waktu,
                                _temp_masukkeluar = _jadwallogabsen_masukkeluar;
                            INSERT INTO _hasil VALUES (_jadwallogabsen_id,
                                                       _jadwallogabsen_idlogabsen,
                                                       _jadwallogabsen_idjamkerjashift,
                                                       'k', # terhitung kerja
                                                       _jadwallogabsen_flag,
                                                       _jadwallogabsen_waktu,
                                                       _jadwallogabsen_masukkeluar,
                                                       'y');
                        END IF;
                    ELSE
                        SET _temp_id = _jadwallogabsen_id,
                            _temp_flag = _jadwallogabsen_flag,
                            _temp_waktu = _jadwallogabsen_waktu,
                            _temp_masukkeluar = _jadwallogabsen_masukkeluar;
                        INSERT INTO _hasil VALUES (_jadwallogabsen_id,
                                                   _jadwallogabsen_idlogabsen,
                                                   _jadwallogabsen_idjamkerjashift,
                                                   'k', # terhitung kerja
                                                   _jadwallogabsen_flag,
                                                   _jadwallogabsen_waktu,
                                                   _jadwallogabsen_masukkeluar,
                                                   't');
                    END IF;
                END IF;
            END IF;
        END IF;
    END LOOP read_loop;
    CLOSE cur_jadwallogabsen;

    # hapus yang tidak diperlukan (yg flag='j' dan _ovveride='t')
    OPEN cur_hasil;
    read_loop: LOOP
        SET done=false;
        FETCH cur_hasil INTO _temp_id, _temp_flag, _temp_waktu, _temp_masukkeluar, _temp_override;
        IF done THEN
            LEAVE read_loop;
        ELSE
            IF (_temp_flag='j' AND _temp_override='t') THEN
                DELETE FROM _hasil WHERE id=_temp_id;
            ELSE
                LEAVE read_loop;
            END IF;
        END IF;
    END LOOP read_loop;
    CLOSE cur_hasil;

    # hapus data yang melebihi waktu terakhir atau sebelum waktu pertama
    SELECT waktu INTO _logabsen_waktu_terakhir FROM _logabsen ORDER BY waktu DESC LIMIT 1;
    SELECT waktu INTO _jadwal_waktu_pertama FROM _jadwal ORDER BY waktu ASC LIMIT 1;
    SELECT waktu INTO _jadwal_waktu_terakhir FROM _jadwal ORDER BY waktu DESC LIMIT 1;

    DELETE FROM _hasil WHERE waktu>_logabsen_waktu_terakhir OR waktu>_jadwal_waktu_terakhir OR waktu<_jadwal_waktu_pertama;

    # pastikan record terakhir adalah keluar
    SET _temp_id=NULL, _temp_masukkeluar=NULL;
    SELECT id, masukkeluar INTO _temp_id, _temp_masukkeluar FROM _hasil ORDER BY waktu DESC, masukkeluar DESC LIMIT 1;
    IF ISNULL(_temp_masukkeluar)=false AND _temp_masukkeluar='m' THEN
        DELETE FROM _hasil WHERE id=_temp_id;
    END IF;

    #hitung _rekapabsen_lamakerja
    SET _rekapabsen_lamakerja = 0;
    SET _temp_masukkeluar_sebelum=NULL,
        _temp_waktu_sebelum=NULL;
    OPEN cur_hasil;
    read_loop: LOOP
        SET done=false;
        FETCH cur_hasil INTO _temp_id, _temp_flag, _temp_waktu, _temp_masukkeluar, _temp_override;
        IF done THEN
            LEAVE read_loop;
        ELSE
            IF _temp_masukkeluar='m' THEN
                IF ISNULL(_temp_masukkeluar_sebelum)=true THEN
                    SET _temp_masukkeluar_sebelum = _temp_masukkeluar;
                    SET _temp_waktu_sebelum = _temp_waktu;
                END IF;
            ELSEIF _temp_masukkeluar='k' THEN
                IF ISNULL(_temp_masukkeluar_sebelum)=false THEN
                    SET _rekapabsen_lamakerja = _rekapabsen_lamakerja + TIMESTAMPDIFF(SECOND, _temp_waktu_sebelum, _temp_waktu);
                    SET _temp_masukkeluar_sebelum=NULL,
                        _temp_waktu_sebelum=NULL;
                END IF;
            END IF;
        END IF;
    END LOOP read_loop;
    CLOSE cur_hasil;
END//

DROP PROCEDURE IF EXISTS posting_hitungflexytime//
CREATE PROCEDURE posting_hitungflexytime(
                                            IN _jadwal_waktupulang_normal DATETIME,
                                            IN _jadwal_waktupulang_flexytime DATETIME,
                                            INOUT _rekapabsen_lamaflexytime INT
                                         )
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _logabsen_id INT UNSIGNED;
    DECLARE _logabsen_idlogabsen INT UNSIGNED;
    DECLARE _logabsen_waktu_m, _logabsen_waktu_k  DATETIME;
    DECLARE _hasil_waktu_m, _hasil_waktu_k DATETIME;
    DECLARE _logabsen_masukkeluar ENUM('m','k');
    DECLARE _logabsen_idalasan INT UNSIGNED;
    DECLARE _logabsen_terhitungkerja ENUM('y','t');
    DECLARE cur_logabsen CURSOR FOR
        SELECT
            id,
            idlogabsen,
            waktu,
            masukkeluar,
            idalasan,
            terhitungkerja
        FROM
            _logabsen
        ORDER BY
            waktu ASC,
            masukkeluar DESC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    # table _logabsen sudah diisi pada procedure posting_persiapanlogabsen
    # Untuk jaga2 saja: supaya tdk error, table _logabsen di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen (
        `id`                INT UNSIGNED AUTO_INCREMENT,
        `idlogabsen`        INT UNSIGNED,
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `idalasan`          INT UNSIGNED,
        `terhitungkerja`    ENUM('y','t'),
        `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
        `del`               ENUM('y','t'),
        INDEX `idx__log_waktu` (`waktu`),
        INDEX `idx__log_masukkeluar` (`masukkeluar`),
        INDEX `idx__log_del` (`del`),
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    SET _rekapabsen_lamaflexytime = 0;
    SET _logabsen_waktu_m = NULL;
    OPEN cur_logabsen;
    read_loop: LOOP
        SET done=false;
        FETCH cur_logabsen INTO _logabsen_id, _logabsen_idlogabsen, _logabsen_waktu_k, _logabsen_masukkeluar, _logabsen_idalasan, _logabsen_terhitungkerja;
        IF done THEN
            LEAVE read_loop;
        ELSE
            IF ISNULL(_logabsen_waktu_m)=true THEN
                IF (_logabsen_masukkeluar='m') THEN
                    SET _logabsen_waktu_m = _logabsen_waktu_k;
                END IF;
            ELSE
                IF (_logabsen_masukkeluar='k') THEN
                    SET _hasil_waktu_m = NULL;
                    IF (_jadwal_waktupulang_normal BETWEEN _logabsen_waktu_m AND _logabsen_waktu_k) THEN
                        SET _hasil_waktu_m = _jadwal_waktupulang_normal;
                    ELSEIF (_logabsen_waktu_m BETWEEN _jadwal_waktupulang_normal AND _jadwal_waktupulang_flexytime) THEN
                        SET _hasil_waktu_m = _logabsen_waktu_m;
                    END IF;

                    SET _hasil_waktu_k = NULL;
                    IF (_jadwal_waktupulang_flexytime BETWEEN _logabsen_waktu_m AND _logabsen_waktu_k) THEN
                        SET _hasil_waktu_k = _jadwal_waktupulang_flexytime;
                    ELSEIF (_logabsen_waktu_k BETWEEN _jadwal_waktupulang_normal AND _jadwal_waktupulang_flexytime) THEN
                        SET _hasil_waktu_k = _logabsen_waktu_k;
                    END IF;

                    IF (ISNULL(_hasil_waktu_m)=false) AND (ISNULL(_hasil_waktu_k)=false) THEN
                        SET _rekapabsen_lamaflexytime = _rekapabsen_lamaflexytime + TIMESTAMPDIFF(SECOND, _hasil_waktu_m, _hasil_waktu_k);
                    END IF;

                    SET _logabsen_waktu_m = NULL;
                END IF;
            END IF;
        END IF;
    END LOOP read_loop;
    CLOSE cur_logabsen;
END//

#hitung _rekapabsen_lamalembur, output adalah table _hasil
DROP PROCEDURE IF EXISTS posting_hitunglembur//
CREATE PROCEDURE posting_hitunglembur(
                                        IN _jadwal_hitunglemburstlh INT,
                                        INOUT _rekapabsen_lamalembur INT
                                     )
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _start_terhitung_lembur DATETIME DEFAULT NULL;

    DECLARE _logabsen_id INT UNSIGNED;
    DECLARE _logabsen_idlogabsen INT UNSIGNED;
    DECLARE _logabsen_waktu, _logabsen_waktu_masuk_sebelum, _logabsen_waktu_dipakai  DATETIME;
    DECLARE _logabsen_masukkeluar ENUM('m','k');
    DECLARE _logabsen_idalasan INT UNSIGNED;
    DECLARE _logabsen_terhitungkerja ENUM('y','t');

    DECLARE cur_logabsen CURSOR FOR
        SELECT
            id,
            idlogabsen,
            waktu,
            masukkeluar,
            idalasan,
            terhitungkerja
        FROM
            _logabsen
        ORDER BY
            waktu ASC,
            masukkeluar DESC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    # table _jadwal sudah diisi pada procedure posting_persiapanjadwal
    # Untuk jaga2 saja: supaya tdk error, table _jadwal di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwal (
        `id`                    INT UNSIGNED AUTO_INCREMENT,
        `idjamkerjashift`       INT UNSIGNED,
        `waktu`                 DATETIME,
        `masukkeluar`           ENUM('m','k'),
        `checking`              ENUM('', 'start','end'), # start: absen masuk, end: absen pulang (dalam satu sesi selain istirahat)
        `shiftpertamaterakhir`  ENUM('', 'pertama', 'terakhir'), # apakah shift tsb adalah shift pertama atau terakhir pada tanggal tsb?
        `shiftsambungan`        ENUM('y','t'), # apakah shift tsb terdapat sambungan dari shift sebelumnya/sesudahnya yg telah dijadwalkan?
        PRIMARY KEY(id)
    ) ENGINE=Memory;
    # table _logabsen sudah diisi pada procedure posting_persiapanlogabsen
    # Untuk jaga2 saja: supaya tdk error, table _logabsen di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen (
        `id`                INT UNSIGNED AUTO_INCREMENT,
        `idlogabsen`        INT UNSIGNED,
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `idalasan`          INT UNSIGNED,
        `terhitungkerja`    ENUM('y','t'),
        `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
        `del`               ENUM('y','t'),
        INDEX `idx__log_waktu` (`waktu`),
        INDEX `idx__log_masukkeluar` (`masukkeluar`),
        INDEX `idx__log_del` (`del`),
        PRIMARY KEY(id)
    ) ENGINE=Memory;
    # table _hasil seharusnya sudah dicreate dan diinsert posting_hitungkerja
    # Untuk jaga2 saja: supaya tdk error, table _hasil di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _hasil (
        `id`                INT UNSIGNED,
        `idlogabsen`        INT UNSIGNED,
        `idjamkerjashift`   INT UNSIGNED,
        `terhitung`         ENUM('k','l'),
        `flag`              ENUM('j','p'),
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `override`          ENUM('y','t'),
        INDEX `idx__absen_waktu` (`waktu`),
        INDEX `idx__absen_masukkeluar` (`masukkeluar`)
    ) ENGINE=Memory;

    # dapatkan waktu kerja terakhir (waktu pulang)
    SELECT
        DATE_ADD(waktu, INTERVAL _jadwal_hitunglemburstlh MINUTE) INTO _start_terhitung_lembur
    FROM
        _jadwal
    WHERE
        masukkeluar='k' AND
        checking='end'
    ORDER BY waktu DESC LIMIT 1;

    SET _rekapabsen_lamalembur = 0;
    IF ISNULL(_start_terhitung_lembur)=false THEN
        # potong _logabsen mulai dari _start_terhitung_lembur
        SET _logabsen_waktu_masuk_sebelum = NULL;
        OPEN cur_logabsen;
        read_loop: LOOP
            SET done=false;
            FETCH cur_logabsen INTO _logabsen_id, _logabsen_idlogabsen, _logabsen_waktu, _logabsen_masukkeluar, _logabsen_idalasan, _logabsen_terhitungkerja;
            IF done THEN
                LEAVE read_loop;
            ELSE
                IF ISNULL(_logabsen_waktu_masuk_sebelum)=true THEN
                    IF (_logabsen_masukkeluar='m') THEN
                        SET _logabsen_waktu_masuk_sebelum = _logabsen_waktu;
                    END IF;
                ELSE
                    IF (_logabsen_masukkeluar='k') THEN
                        SET _logabsen_waktu_dipakai = NULL;

                        IF (_start_terhitung_lembur BETWEEN _logabsen_waktu_masuk_sebelum AND _logabsen_waktu) THEN
                            # jika _start_terhitung_lembur berada diantara masuk dan keluar
                            SET _logabsen_waktu_dipakai = _start_terhitung_lembur;
                        ELSEIF (_start_terhitung_lembur<_logabsen_waktu_masuk_sebelum) THEN
                            # jika _start_terhitung_lembur berada diantara masuk dan keluar
                            SET _logabsen_waktu_dipakai = _logabsen_waktu_masuk_sebelum;
                        END IF;

                        IF (ISNULL(_logabsen_waktu_dipakai)=false) AND (_logabsen_waktu_dipakai<>_logabsen_waktu) THEN
                            SET _rekapabsen_lamalembur = _rekapabsen_lamalembur + TIMESTAMPDIFF(SECOND, _logabsen_waktu_dipakai, _logabsen_waktu);

                            INSERT INTO _hasil VALUES (_logabsen_id,
                                                       _logabsen_idlogabsen,
                                                       NULL,
                                                       'l', # terhitung lembur
                                                       'p',
                                                       _logabsen_waktu_dipakai,
                                                       'm',
                                                       't');

                            INSERT INTO _hasil VALUES (_logabsen_id,
                                                       _logabsen_idlogabsen,
                                                       NULL,
                                                       'l', # terhitung lembur
                                                       'p',
                                                       _logabsen_waktu,
                                                       'k',
                                                       't');
                        END IF;

                        SET _logabsen_waktu_masuk_sebelum = NULL;
                    END IF;
                END IF;
            END IF;
        END LOOP read_loop;
        CLOSE cur_logabsen;
    END IF;
END//

#util bantuan yang akan dipanggil pada saat posting.
DROP PROCEDURE IF EXISTS utils_hitungselisihmasukkeluar//
CREATE PROCEDURE utils_hitungselisihmasukkeluar (
                                                  IN _waktu_patokan DATETIME,
                                                  IN _toleransi INT UNSIGNED,
                                                  IN _jadwal_acuanterlambat enum('jadwal','toleransi'),
                                                  IN _checking ENUM('start','end'),
                                                  IN _start_of_session DATETIME, # batasan waktu awal pengecekan logabsen dalam sesi
                                                  IN _end_of_session DATETIME,  # batasan waktu akhir pengecekan logabsen dalam sesi
                                                  INOUT _selisih INT
                                                )
BEGIN
    DECLARE _logabsen_waktu DATETIME DEFAULT NULL;
    DECLARE _logabsen_masukkeluar ENUM('m','k') DEFAULT NULL;

    # table _logabsen sudah diisi pada procedure posting_persiapanlogabsen
    # Untuk jaga2 saja: supaya tdk error, table _logabsen di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen (
        `id`                INT UNSIGNED AUTO_INCREMENT,
        `idlogabsen`        INT UNSIGNED,
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `idalasan`          INT UNSIGNED,
        `terhitungkerja`    ENUM('y','t'),
        `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
        `del`               ENUM('y','t'),
        INDEX `idx__log_waktu` (`waktu`),
        INDEX `idx__log_masukkeluar` (`masukkeluar`),
        INDEX `idx__log_del` (`del`),
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    SET _selisih = 0;

    IF ISNULL(_waktu_patokan)=false THEN
        SET _logabsen_waktu = NULL,
        _logabsen_masukkeluar = NULL;

        IF _checking='start' THEN
            #cari logabsen persis sebelum dan sesudah _waktu_start, diutamakan yang sebelum _waktu_start
            SELECT
                waktu, masukkeluar INTO
                _logabsen_waktu, _logabsen_masukkeluar
            FROM
                _logabsen
            WHERE
                waktu<=_waktu_patokan AND
                waktu BETWEEN _start_of_session AND _end_of_session
            LIMIT 1;

            IF NOT (ISNULL(_logabsen_masukkeluar)=false AND _logabsen_masukkeluar='m') THEN
                SET _logabsen_waktu = NULL,
                    _logabsen_masukkeluar = NULL;
                SELECT
                    waktu, masukkeluar INTO
                    _logabsen_waktu, _logabsen_masukkeluar
                FROM
                    _logabsen
                WHERE
                    waktu>_waktu_patokan AND
                    waktu BETWEEN _start_of_session AND _end_of_session
                LIMIT 1;

                IF NOT (ISNULL(_logabsen_masukkeluar)=false AND _logabsen_masukkeluar='m') THEN
                    SET _logabsen_waktu = NULL,
                        _logabsen_masukkeluar = NULL;
                END IF;
            END IF;
        ELSEIF _checking='end' THEN
            #cari logabsen terakhir yang keluar
            SELECT
                waktu INTO _logabsen_waktu
            FROM
                _logabsen
            WHERE
                masukkeluar='k' AND
                waktu BETWEEN _start_of_session AND _end_of_session
            ORDER BY
                waktu DESC
            LIMIT 1;

        END IF;

        # cek apakah _logabsen_waktu tidak null
        IF ISNULL(_logabsen_waktu)=false THEN
            IF (_logabsen_waktu BETWEEN _waktu_patokan AND DATE_ADD(_waktu_patokan, INTERVAL _toleransi MINUTE)) THEN
                SET _selisih = 0;
            ELSE
                IF (_toleransi>0) AND (_jadwal_acuanterlambat='toleransi') THEN
                    SET _selisih = TIMESTAMPDIFF(SECOND, _logabsen_waktu, DATE_ADD(_waktu_patokan, INTERVAL _toleransi MINUTE));
                ELSE
                    SET _selisih = TIMESTAMPDIFF(SECOND, _logabsen_waktu, _waktu_patokan);
                END IF;
            END IF;
        END IF;

    END IF;
END //

#hitung posting_hitungselisihmasukkeluar, output variable _rekapabsen_selisihmasuk dan _rekapabsen_selisihkeluar
DROP PROCEDURE IF EXISTS posting_hitungselisihmasukkeluar//
CREATE PROCEDURE posting_hitungselisihmasukkeluar(
                                        IN _jadwal_toleransi INT,
                                        IN _jadwal_acuanterlambat enum('jadwal','toleransi'),
                                        INOUT _rekapabsen_selisihmasuk INT,
                                        INOUT _rekapabsen_selisihkeluar INT,
                                        INOUT _rekapabsen_overlap INT
                                       )
BEGIN
    DECLARE done INT DEFAULT FALSE;

    DECLARE _jadwal_idjamkerjashift INT UNSIGNED DEFAULT NULL;
    DECLARE _jadwal_waktu DATETIME DEFAULT NULL;
    DECLARE _jadwal__checking ENUM('', 'start','end') DEFAULT '';
    DECLARE _jadwal_shiftpertamaterakhir ENUM('', 'pertama', 'terakhir') DEFAULT NULL;
    DECLARE _jadwal_shiftsambungan ENUM('y', 't') DEFAULT NULL;

    DECLARE _selisih INT DEFAULT 0;
    DECLARE _temp_overlap INT DEFAULT 0;
    DECLARE _start_of_session DATETIME DEFAULT NULL;
    DECLARE _end_of_session DATETIME DEFAULT NULL;

    DECLARE cur_jadwal CURSOR FOR
        SELECT
            idjamkerjashift,
            waktu,
            checking,
            shiftpertamaterakhir,
            shiftsambungan
        FROM
            _jadwal
        WHERE
            checking='start' OR
            checking='end'
        ORDER BY
            waktu ASC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    # table _jadwal sudah diisi pada procedure posting_persiapanjadwal
    # Untuk jaga2 saja: supaya tdk error, table _jadwal di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwal (
        `id`                    INT UNSIGNED AUTO_INCREMENT,
        `idjamkerjashift`       INT UNSIGNED,
        `waktu`                 DATETIME,
        `masukkeluar`           ENUM('m','k'),
        `checking`              ENUM('', 'start','end'), # start: absen masuk, end: absen pulang (dalam satu sesi selain istirahat)
        `shiftpertamaterakhir`  ENUM('', 'pertama', 'terakhir'), # apakah shift tsb adalah shift pertama atau terakhir pada tanggal tsb?
        `shiftsambungan`        ENUM('y','t'), # apakah shift tsb terdapat sambungan dari shift sebelumnya/sesudahnya yg telah dijadwalkan?
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    # table _jadwalbersambung sudah diisi pada procedure utils_cekjadwalbersambung
    # Untuk jaga2 saja: supaya tdk error, table _jadwalbersambung di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwalbersambung (
        `analisa`        ENUM('kemarin','besok') NOT NULL,
        `waktumasuk`     DATETIME,
        `waktupulang`    DATETIME
    ) ENGINE=Memory;

    SET _rekapabsen_overlap = 0;

    OPEN cur_jadwal;
    read_loop: LOOP
        SET done=false;
        FETCH cur_jadwal INTO _jadwal_idjamkerjashift, _jadwal_waktu, _jadwal__checking, _jadwal_shiftpertamaterakhir, _jadwal_shiftsambungan;
        IF done THEN
            LEAVE read_loop;
        ELSE
            # cari _start_of_session dan _end_of_session
            SET _start_of_session = DATE_SUB(_jadwal_waktu, INTERVAL 2 DAY);
            SET _end_of_session = DATE_ADD(_jadwal_waktu, INTERVAL 2 DAY);
            SELECT waktu INTO _start_of_session FROM _jadwal WHERE checking='end' AND waktu<_jadwal_waktu ORDER BY waktu ASC LIMIT 1;
            SELECT waktu INTO _end_of_session FROM _jadwal WHERE checking='start' AND waktu>_jadwal_waktu ORDER BY waktu ASC LIMIT 1;

            # dapatkan waktu start dan waktu end
            CALL utils_hitungselisihmasukkeluar( _jadwal_waktu,
                                                 IF(_jadwal__checking='start',_jadwal_toleransi,0),
                                                _jadwal_acuanterlambat,
                                                 _jadwal__checking,
                                                 _start_of_session,
                                                 _end_of_session,
                                                 _selisih);

            # jika _jamkerja_jenis=shift DAN
            IF ISNULL(_jadwal_idjamkerjashift)=false THEN
                # shift tsb adalah shift pertama DAN
                # shift tsb adalah sambungan dari shift pada hari kemarin-nya
                IF _jadwal_shiftpertamaterakhir='pertama' AND
                   _jadwal_shiftsambungan='y' THEN
                    SELECT TIMESTAMPDIFF(SECOND, _jadwal_waktu, waktupulang) INTO _temp_overlap FROM  _jadwalbersambung WHERE analisa='kemarin' LIMIT 1;
                    IF _selisih<-1*_temp_overlap THEN
                        SET _rekapabsen_overlap = 0;
                    ELSE
                        IF _selisih>0 THEN
                            # berarti sudah absen di shift/jamkerja sebelumnya (bersambung), maka set selisih=0
                            SET _selisih = 0;
                            SET _rekapabsen_overlap = _temp_overlap;
                        ELSE
                            SET _rekapabsen_overlap = _temp_overlap+_selisih;
                        END IF;
                    END IF;
                # shift tsb adalah shift terakhir DAN
                # shift tsb adalah sambungan dari shift pada hari besok-nya
                ELSEIF _jadwal_shiftpertamaterakhir='terakhir' AND
                       _jadwal_shiftsambungan='y' THEN
                    IF -1*_selisih>0 THEN
                        # berarti sudah absen di shift/jamkerja sebelumnya (bersambung), maka set selisih=0
                        SET _selisih = 0;
                    END IF;
                END IF;
            END IF;

            IF _jadwal__checking='start' THEN
                SET _rekapabsen_selisihmasuk = _rekapabsen_selisihmasuk + _selisih;
            ELSEIF _jadwal__checking='end' THEN
                SET _rekapabsen_selisihkeluar = _rekapabsen_selisihkeluar+ (-1 * _selisih);
            END IF;
        END IF;
    END LOOP read_loop;
    CLOSE cur_jadwal;

END//

DROP FUNCTION IF EXISTS ceksimpanrekap//
# _flag_posting_otomatis bernilai "y" dan "t"
# nilai kembali adalah 'y' atau 't'
CREATE FUNCTION ceksimpanrekap(_tanggal DATE, _idpegawai INT UNSIGNED, _flag_posting_otomatis VARCHAR(1), _jamkerja_jenis ENUM ('full','shift')) RETURNS VARCHAR(1)
BEGIN
    DECLARE _jum_masuk INT UNSIGNED DEFAULT 0;
    DECLARE _jum_keluar INT UNSIGNED DEFAULT 0;
    DECLARE _masukkeluar ENUM('m','k') DEFAULT  NULL;
    DECLARE _simpan_rekap ENUM('y','t') DEFAULT 'y';

    SET _simpan_rekap = 'y';

    IF _flag_posting_otomatis='y' AND _jamkerja_jenis='shift' THEN
        # cek apakah logabsen hanya ada 1 dan hanya keluar saja
        SET _jum_masuk = 0;
        SET _jum_keluar = 0;
        SELECT
            SUM(IF(masukkeluar='m',1,0)) as jummasuk, SUM(IF(masukkeluar='k',1,0)) as jumkeluar INTO
            _jum_masuk, _jum_keluar
        FROM
            logabsen
        WHERE
            idpegawai=_idpegawai AND
            waktu>=CONCAT(_tanggal, ' 00:00:00') AND waktu<=CONCAT(_tanggal, ' 23:59:59');

        IF _jum_masuk=0 AND _jum_keluar>0 THEN

            SET _masukkeluar='m';
            # cek apakah pada hari kemarin, logabsen terakhir adalah masuk (m)?
            SELECT
                masukkeluar INTO _masukkeluar
            FROM
                logabsen
            WHERE
                idpegawai=_idpegawai AND
                waktu>=CONCAT(DATE_SUB(_tanggal, INTERVAL 1 DAY), ' 00:00:00') AND waktu<=CONCAT(DATE_SUB(_tanggal, INTERVAL 1 DAY), ' 23:59:59')
            ORDER BY
                waktu DESC
            LIMIT 1;

            IF ISNULL(_masukkeluar)=false AND _masukkeluar='m' THEN
                SET _simpan_rekap = 't';
            END IF;
        END IF;
    END IF;
    RETURN _simpan_rekap;
END //

DROP PROCEDURE IF EXISTS posting_rekapabsen_checkflag//
CREATE PROCEDURE posting_rekapabsen_checkflag(
                                    IN _toleransi_terlambat INT,
                                    IN _jadwal_acuanterlambat enum('jadwal','toleransi'),
                                    IN _default_perlakuanlembur ENUM('tanpalembur','konfirmasi','lembur'),
                                    INOUT _rekapabsen_selisihmasuk INT,
                                    INOUT _rekapabsen_selisihkeluar INT,
                                    INOUT _rekapabsen_lamalembur INT,
                                    INOUT _flag_terlambat ENUM('','y','t'),
                                    INOUT _flag_pulangawal ENUM('','y','t'),
                                    INOUT _flag_lembur ENUM('','y','t')
                                  )
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _adadata INT;
    DECLARE _absen_pertama INT;

    DECLARE _temp_rekapabsen_lamalembur INT;

    DECLARE _jadwal_waktu DATETIME;
    DECLARE _jadwal_checking ENUM('', 'start','end');

    DECLARE _jadwal_start DATETIME;
    DECLARE _jadwal_end DATETIME;

    DECLARE _selisih_terlambat INT;
    DECLARE _selisih_pulangawal INT;
    DECLARE _selisih_lembur INT;

    DECLARE _logabsen_waktu DATETIME;
    DECLARE _logabsen_idalasan INT UNSIGNED;
    DECLARE _logabsen_terhitungkerja ENUM('y','t');
    DECLARE _logabsen_flag ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur');

    DECLARE cur_jadwal CURSOR FOR
        SELECT
            waktu,
            checking
        FROM
            _jadwal
        WHERE
            (checking='start' OR checking='end')
        ORDER BY
            waktu ASC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    # table _logabsen sudah diisi pada procedure posting_persiapanlogabsen
    # Untuk jaga2 saja: supaya tdk error, table _logabsen di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen (
        `id`                INT UNSIGNED AUTO_INCREMENT,
        `idlogabsen`        INT UNSIGNED,
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `idalasan`          INT UNSIGNED,
        `terhitungkerja`    ENUM('y','t'),
        `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
        `del`               ENUM('y','t'),
        INDEX `idx__log_waktu` (`waktu`),
        INDEX `idx__log_masukkeluar` (`masukkeluar`),
        INDEX `idx__log_del` (`del`),
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    # table _jadwal sudah diisi pada procedure posting_persiapanjadwal
    # Untuk jaga2 saja: supaya tdk error, table _jadwal di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwal (
        `id`                    INT UNSIGNED AUTO_INCREMENT,
        `idjamkerjashift`       INT UNSIGNED,
        `waktu`                 DATETIME,
        `masukkeluar`           ENUM('m','k'),
        `checking`              ENUM('', 'start','end'), # start: absen masuk, end: absen pulang (dalam satu sesi selain istirahat)
        `shiftpertamaterakhir`  ENUM('', 'pertama', 'terakhir'), # apakah shift tsb adalah shift pertama atau terakhir pada tanggal tsb?
        `shiftsambungan`        ENUM('y','t'), # apakah shift tsb terdapat sambungan dari shift sebelumnya/sesudahnya yg telah dijadwalkan?
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    SET _flag_terlambat='';
    SET _flag_pulangawal='';
    SET _flag_lembur='';

    SET _selisih_terlambat=0;
    SET _selisih_pulangawal=0;
    SET _selisih_lembur=0;
    SET _absen_pertama = 1;
    SET _jadwal_start = NULL;
    SET _jadwal_end = NULL;

    OPEN cur_jadwal;
    read_loop: LOOP
        SET done=false;
        FETCH cur_jadwal INTO _jadwal_waktu, _jadwal_checking;
        IF done THEN
            LEAVE read_loop;
        ELSE
            IF _jadwal_checking='start' THEN
                SET _jadwal_start = _jadwal_waktu;
            ELSEIF _jadwal_checking='end' THEN
                SET _jadwal_end = _jadwal_waktu;
                IF ISNULL(_jadwal_start)=false AND ISNULL(_jadwal_end)=false THEN
                    # cek terlambat
                    SET _adadata = 0;
                    SELECT
                        1, waktu, idalasan, terhitungkerja, flag
                        INTO
                        _adadata, _logabsen_waktu, _logabsen_idalasan, _logabsen_terhitungkerja, _logabsen_flag
                    FROM
                        _logabsen
                    WHERE
                        masukkeluar='m' AND
                        waktu>_jadwal_start AND waktu<_jadwal_end
                    ORDER BY waktu ASC LIMIT 1;

                    IF _adadata=1 THEN
                        IF _logabsen_flag='tidak-terlambat' THEN
                            SET _flag_terlambat = 'y';
                            IF NOT (ISNULL(_logabsen_idalasan)=false AND _logabsen_terhitungkerja='y') THEN
                                SET _selisih_terlambat = _selisih_terlambat + TIMESTAMPDIFF(SECOND, _jadwal_start, _logabsen_waktu);
                                IF _absen_pertama=1 THEN
                                    IF _selisih_terlambat<=_toleransi_terlambat*60 THEN
                                        SET _selisih_terlambat = 0;
                                    ELSEIF _jadwal_acuanterlambat='toleransi' THEN
                                        SET _selisih_terlambat = TIMESTAMPDIFF(SECOND, DATE_ADD(_jadwal_start, INTERVAL _toleransi_terlambat MINUTE), _logabsen_waktu);
                                    END IF;
                                END IF;
                            END IF;
                        END IF;
                        SET _absen_pertama=0;
                    END IF;

                    # cek pulang awal
                    SET _adadata = 0;
                    SELECT
                        1, waktu, idalasan, terhitungkerja, flag
                        INTO
                        _adadata, _logabsen_waktu, _logabsen_idalasan, _logabsen_terhitungkerja, _logabsen_flag
                    FROM
                        _logabsen
                    WHERE
                        masukkeluar='k' AND
                        waktu>_jadwal_start AND waktu<_jadwal_end
                    ORDER BY waktu DESC LIMIT 1;
                    IF _adadata=1 THEN
                        IF _logabsen_flag='tidak-pulangawal' THEN
                            SET _flag_pulangawal = 'y';
                            IF NOT (ISNULL(_logabsen_idalasan)=false AND _logabsen_terhitungkerja='y') THEN
                                SET _selisih_pulangawal = _selisih_pulangawal + TIMESTAMPDIFF(SECOND, _logabsen_waktu, _jadwal_end);
                            END IF;
                        END IF;
                    END IF;
                END IF;

                SET _jadwal_start = NULL;
                SET _jadwal_end = NULL;
            END IF;
        END IF;
    END LOOP read_loop;
    CLOSE cur_jadwal;

    SET _rekapabsen_selisihmasuk = _rekapabsen_selisihmasuk + _selisih_terlambat;
    SET _rekapabsen_selisihkeluar = _rekapabsen_selisihkeluar + _selisih_pulangawal;

    # cek lembur
    SET _temp_rekapabsen_lamalembur = _rekapabsen_lamalembur;

    IF (_default_perlakuanlembur='tanpalembur') OR (_default_perlakuanlembur='konfirmasi') THEN
        SET _rekapabsen_lamalembur = 0;
    END IF;

    SET _adadata = 0;
    SELECT
        1, flag
        INTO
        _adadata, _logabsen_flag
    FROM
        _logabsen
    WHERE
        (flag = 'lembur' OR flag = 'tidak-lembur')
    ORDER BY waktu DESC LIMIT 1;

    IF _adadata=1 THEN
        IF _logabsen_flag='tidak-lembur' THEN
            SET _flag_lembur = 't';
            SET _rekapabsen_lamalembur = 0;
        ELSEIF _logabsen_flag='lembur' THEN
            SET _flag_lembur = 'y';
            SET _rekapabsen_lamalembur = _temp_rekapabsen_lamalembur;
        END IF;
    END IF;
END //


DROP PROCEDURE IF EXISTS posting_rekapshift_checkflag//
CREATE PROCEDURE posting_rekapshift_checkflag(
                                    _toleransi_terlambat INT,
                                    _default_perlakuanlembur ENUM('tanpalembur','konfirmasi','lembur'),
                                    IN _sesi_waktuawal DATETIME,
                                    IN _sesi_waktuakhir DATETIME,
                                    IN _sesi_end_of_day DATETIME,
                                    INOUT _rekapshift_selisihmasuk INT,
                                    INOUT _rekapshift_selisihkeluar INT,
                                    INOUT _rekapshift_lamalembur INT,
                                    INOUT _flag_terlambat ENUM('','y','t'),
                                    INOUT _flag_pulangawal ENUM('','y','t'),
                                    INOUT _flag_lembur ENUM('','y','t')
                                  )
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _adadata INT;
    DECLARE _absen_pertama INT;

    DECLARE _temp_rekapabsen_lamalembur INT;

    DECLARE _selisih_terlambat INT;
    DECLARE _selisih_pulangawal INT;
    DECLARE _selisih_lembur INT;

    DECLARE _logabsen_waktu DATETIME;
    DECLARE _logabsen_idalasan INT UNSIGNED;
    DECLARE _logabsen_terhitungkerja ENUM('y','t');
    DECLARE _logabsen_flag ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur');

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    # table _logabsen sudah diisi pada procedure posting_persiapanlogabsen
    # Untuk jaga2 saja: supaya tdk error, table _logabsen di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen (
        `id`                INT UNSIGNED AUTO_INCREMENT,
        `idlogabsen`        INT UNSIGNED,
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `idalasan`          INT UNSIGNED,
        `terhitungkerja`    ENUM('y','t'),
        `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
        `del`               ENUM('y','t'),
        INDEX `idx__log_waktu` (`waktu`),
        INDEX `idx__log_masukkeluar` (`masukkeluar`),
        INDEX `idx__log_del` (`del`),
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    # table _jadwal sudah diisi pada procedure posting_persiapanjadwal
    # Untuk jaga2 saja: supaya tdk error, table _jadwal di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwal (
        `id`                    INT UNSIGNED AUTO_INCREMENT,
        `idjamkerjashift`       INT UNSIGNED,
        `waktu`                 DATETIME,
        `masukkeluar`           ENUM('m','k'),
        `checking`              ENUM('', 'start','end'), # start: absen masuk, end: absen pulang (dalam satu sesi selain istirahat)
        `shiftpertamaterakhir`  ENUM('', 'pertama', 'terakhir'), # apakah shift tsb adalah shift pertama atau terakhir pada tanggal tsb?
        `shiftsambungan`        ENUM('y','t'), # apakah shift tsb terdapat sambungan dari shift sebelumnya/sesudahnya yg telah dijadwalkan?
        PRIMARY KEY(id)
    ) ENGINE=Memory;

    SET _flag_terlambat='';
    SET _flag_pulangawal='';
    SET _flag_lembur='';

    SET _selisih_terlambat=0;
    SET _selisih_pulangawal=0;
    SET _selisih_lembur=0;
    SET _absen_pertama = 1;


    # cek terlambat
    SET _adadata = 0;
    SELECT
        1, waktu, idalasan, terhitungkerja, flag
        INTO
        _adadata, _logabsen_waktu, _logabsen_idalasan, _logabsen_terhitungkerja, _logabsen_flag
    FROM
        _logabsen
    WHERE
        masukkeluar='m' AND
        waktu>_sesi_waktuawal AND waktu<_sesi_waktuakhir
    ORDER BY waktu ASC LIMIT 1;

    IF _adadata=1 THEN
        IF _logabsen_flag='tidak-terlambat' THEN
            SET _flag_terlambat = 'y';
            IF NOT (ISNULL(_logabsen_idalasan)=false AND _logabsen_terhitungkerja='y') THEN
                SET _selisih_terlambat = _selisih_terlambat + TIMESTAMPDIFF(SECOND, _sesi_waktuawal, _logabsen_waktu);
                IF _absen_pertama=1 AND _selisih_terlambat<=_toleransi_terlambat*60 THEN
                    SET _selisih_terlambat = 0;
                END IF;
            END IF;
        END IF;
        SET _absen_pertama=0;
    END IF;

    # cek pulang awal
    SET _adadata = 0;

    SELECT
        1, waktu, idalasan, terhitungkerja, flag
        INTO
        _adadata, _logabsen_waktu, _logabsen_idalasan, _logabsen_terhitungkerja, _logabsen_flag
    FROM
        _logabsen
    WHERE
        masukkeluar='k' AND
        waktu>_sesi_waktuawal AND waktu<_sesi_waktuakhir
    ORDER BY waktu DESC LIMIT 1;
    IF _adadata=1 THEN
        IF _logabsen_flag='tidak-pulangawal' THEN
            SET _flag_pulangawal = 'y';
            IF NOT (ISNULL(_logabsen_idalasan)=false AND _logabsen_terhitungkerja='y') THEN
                SET _selisih_pulangawal = _selisih_pulangawal + TIMESTAMPDIFF(SECOND, _logabsen_waktu, _sesi_waktuakhir);
            END IF;
        END IF;
    END IF;

    SET _rekapshift_selisihmasuk = _rekapshift_selisihmasuk + _selisih_terlambat;
    SET _rekapshift_selisihkeluar = _rekapshift_selisihkeluar + _selisih_pulangawal;

    # cek lembur
    SET _temp_rekapabsen_lamalembur = _rekapshift_lamalembur;

    IF (_default_perlakuanlembur='tanpalembur') OR (_default_perlakuanlembur='konfirmasi') THEN
        SET _rekapshift_lamalembur = 0;
    END IF;

    SET _adadata = 0;
    SELECT
        1, flag
        INTO
        _adadata, _logabsen_flag
    FROM
        _logabsen
    WHERE
        (flag = 'lembur' OR flag = 'tidak-lembur') AND
        (waktu BETWEEN _sesi_waktuawal AND _sesi_end_of_day)
    LIMIT 1;

    IF _adadata=1 THEN
        IF _logabsen_flag='tidak-lembur' THEN
            SET _flag_lembur = 't';
            SET _rekapshift_lamalembur = 0;
        ELSEIF _logabsen_flag='lembur' THEN
            SET _flag_lembur = 'y';
            SET _rekapshift_lamalembur = _temp_rekapabsen_lamalembur;
        END IF;
    END IF;
END //

DROP PROCEDURE IF EXISTS posting_rekapshift//
CREATE PROCEDURE posting_rekapshift( IN _tanggal DATE,
                                     IN _idpegawai INT UNSIGNED,
                                     IN _default_perlakuanlembur ENUM('tanpalembur','konfirmasi','lembur'),
                                     IN _flag_posting_otomatis VARCHAR(1)
                                   )
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE _idjamkerjashift INT DEFAULT 0;

    DECLARE _logabsen_adadata VARCHAR(1);
    DECLARE _logabsen_waktu DATETIME;
    DECLARE _logabsen_masukkeluar ENUM('m','k');
    DECLARE _logabsen_idalasan INT UNSIGNED;
    DECLARE _logabsen_terhitungkerja VARCHAR(1);
    DECLARE _logabsen_flag ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur');

    DECLARE _jadwalshift_dataada VARCHAR(1);
    DECLARE _jadwalshift_jammasuk TIME;
    DECLARE _jadwalshift_jampulang TIME;
    DECLARE _jadwalshift_waktumasuk DATETIME;
    DECLARE _jadwalshift_waktupulang DATETIME;
    DECLARE _jadwalshift_waktulembur DATETIME;

    DECLARE _temp_id INT UNSIGNED;
    DECLARE _temp_flag ENUM('j','p');
    DECLARE _temp_waktu, _temp_waktu_sebelum DATETIME;
    DECLARE _temp_masukkeluar, _temp_masukkeluar_sebelum ENUM('m','k');
    DECLARE _temp_override ENUM('y','t');

    DECLARE _rekapshift_masukkerja ENUM('y','t');
    DECLARE _rekapshift_waktumasuk DATETIME;
    DECLARE _rekapshift_waktukeluar DATETIME;
    DECLARE _rekapshift_selisihmasuk INT DEFAULT 0;
    DECLARE _rekapshift_selisihkeluar INT DEFAULT 0;
    DECLARE _rekapshift_lamakerja INT UNSIGNED DEFAULT 0;
    DECLARE _rekapshift_lamalembur INT UNSIGNED DEFAULT 0;
    DECLARE _rekapshift_flag_terlambat ENUM('','y','t');
    DECLARE _rekapshift_flag_pulangawal ENUM('','y','t');
    DECLARE _rekapshift_flag_lembur ENUM('','y','t');
    DECLARE _toleransi INT DEFAULT 0;
    DECLARE _hitunglemburstlh INT DEFAULT 0;

    DECLARE _start_of_session DATETIME DEFAULT NULL;
    DECLARE _end_of_session DATETIME DEFAULT NULL;

    DECLARE cur_jadwalshift CURSOR FOR
        SELECT
            idjamkerjashift
        FROM
            jadwalshift
        WHERE
            idpegawai=_idpegawai AND
            tanggal=_tanggal
        ORDER BY
            idjamkerjashift ASC;

    DECLARE cur_logabsen CURSOR FOR
        SELECT
            id,
            idlogabsen,
            waktu,
            masukkeluar,
            idalasan,
            terhitungkerja
        FROM
            _logabsen
        ORDER BY
            waktu ASC,
            masukkeluar DESC;

    DECLARE cur_hasil CURSOR FOR
        SELECT
            id,
            flag,
            waktu,
            masukkeluar,
            override
        FROM
            _hasil
        ORDER BY
            waktu ASC,
            masukkeluar DESC;


    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    # table _logabsen sudah diisi pada procedure posting_persiapanlogabsen
    # Untuk jaga2 saja: supaya tdk error, table _logabsen di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen (
        `id`                INT UNSIGNED AUTO_INCREMENT,
        `idlogabsen`        INT UNSIGNED,
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `idalasan`          INT UNSIGNED,
        `terhitungkerja`    ENUM('y','t'),
        `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
        `del`               ENUM('y','t'),
        INDEX `idx__log_waktu` (`waktu`),
        INDEX `idx__log_masukkeluar` (`masukkeluar`),
        INDEX `idx__log_del` (`del`),
        PRIMARY KEY(id)
    ) ENGINE=Memory;
    # table _hasil seharusnya sudah dicreate dan diinsert posting_hitungkerja
    # Untuk jaga2 saja: supaya tdk error, table _hasil di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _hasil (
        `id`                INT UNSIGNED,
        `idlogabsen`        INT UNSIGNED,
        `idjamkerjashift`   INT UNSIGNED,
        `terhitung`         ENUM('k','l'),
        `flag`              ENUM('j','p'),
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `override`          ENUM('y','t'),
        INDEX `idx__absen_waktu` (`waktu`),
        INDEX `idx__absen_masukkeluar` (`masukkeluar`)
    ) ENGINE=Memory;
    # table _hasil seharusnya sudah dicreate dan diinsert posting_persiapanjadwalshift
    # Untuk jaga2 saja: supaya tdk error, table _hasil di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwalbeberapahari (
        `id`            INT UNSIGNED,
        `tanggal`       DATE,
        `waktumasuk`    DATETIME,
        `waktupulang`   DATETIME
    ) ENGINE=Memory;

    CALL posting_persiapanjadwalbersambung(_tanggal, _idpegawai);

    OPEN cur_jadwalshift;
    read_loop_cur_jadwalshift: LOOP
        SET done=false;
        FETCH cur_jadwalshift INTO _idjamkerjashift;
        IF done THEN
            LEAVE read_loop_cur_jadwalshift;
        ELSE
# IF _idjamkerjashift=64 THEN
#     SELECT * FROM _logabsen;
# END IF;
            SET _jadwalshift_dataada='t';
            SELECT
                'y',
                jammasuk,
                jampulang
                INTO
                _jadwalshift_dataada,
                _jadwalshift_jammasuk,
                _jadwalshift_jampulang
            FROM
                jamkerjashiftdetail
            WHERE
                idjamkerjashift=_idjamkerjashift AND
                berlakumulai<=_tanggal
            ORDER BY
                berlakumulai DESC
            LIMIT 1;

            IF _jadwalshift_dataada='y' THEN
                CALL time2datetime(_tanggal, _jadwalshift_jammasuk, _jadwalshift_jampulang, _jadwalshift_waktumasuk, _jadwalshift_waktupulang);

                SET _rekapshift_waktumasuk = NULL;
                SET _rekapshift_waktukeluar = NULL;
                SET _rekapshift_selisihmasuk = 0;
                SET _rekapshift_selisihkeluar = 0;
                SET _rekapshift_lamakerja = 0;
                SET _rekapshift_lamalembur = 0;

                SET _hitunglemburstlh = 0;
                SELECT
                    toleransi, hitunglemburstlh INTO _toleransi, _hitunglemburstlh
                FROM
                    jamkerjashift jks,
                    jamkerja jk
                WHERE
                    jks.idjamkerja=jk.id AND
                    jks.id=_idjamkerjashift
                LIMIT 1;

                # tentukan start dan end session
                SET _start_of_session = SUBDATE(_jadwalshift_waktumasuk, INTERVAL 8 HOUR);
                SELECT waktupulang INTO _start_of_session FROM _jadwalbeberapahari WHERE waktupulang<_jadwalshift_waktumasuk ORDER BY waktupulang DESC LIMIT 1;
                SET _end_of_session = ADDDATE(_jadwalshift_waktupulang, INTERVAL 8 HOUR);
                SELECT waktumasuk INTO _end_of_session FROM _jadwalbeberapahari WHERE waktumasuk>_jadwalshift_waktupulang ORDER BY waktumasuk ASC LIMIT 1;

                SET _logabsen_adadata = 't';
                SELECT 'y' INTO _logabsen_adadata FROM _logabsen WHERE (waktu BETWEEN _start_of_session AND _end_of_session) LIMIT 1;

                #hitung waktu masukmasuk dan selisihmasuk
                IF _logabsen_adadata = 'y' THEN
                    # jika ada data _logabsen

                    #hitung waktu masukkeluar
                    SET _logabsen_waktu = NULL;
                    SET _logabsen_masukkeluar=NULL;
                    SET _logabsen_terhitungkerja=NULL;
                    SELECT
                        waktu, masukkeluar, idalasan, terhitungkerja INTO _logabsen_waktu, _logabsen_masukkeluar, _logabsen_idalasan, _logabsen_terhitungkerja
                    FROM
                        _logabsen
                    WHERE
                        (waktu BETWEEN _start_of_session AND _end_of_session) AND
                        waktu<=_jadwalshift_waktumasuk
                    ORDER BY
                        waktu DESC
                    LIMIT 1;
                    IF ISNULL(_logabsen_masukkeluar)=false AND ISNULL(_logabsen_terhitungkerja)=false AND _logabsen_masukkeluar='m' THEN
                        SET _rekapshift_waktumasuk = _logabsen_waktu;
                        SET _rekapshift_selisihmasuk = TIMESTAMPDIFF(SECOND, _logabsen_waktu, _jadwalshift_waktumasuk);
                    ELSE
                        SET _logabsen_waktu = NULL;
                        SET _logabsen_masukkeluar=NULL;
                        SET _logabsen_terhitungkerja=NULL;
                        SELECT
                            waktu, masukkeluar, idalasan, terhitungkerja INTO _logabsen_waktu, _logabsen_masukkeluar, _logabsen_idalasan, _logabsen_terhitungkerja
                        FROM
                            _logabsen
                        WHERE
                            (waktu BETWEEN _start_of_session AND _end_of_session) AND
                            waktu>_jadwalshift_waktumasuk
                        ORDER BY
                            waktu ASC
                        LIMIT 1;
                        IF ISNULL(_logabsen_masukkeluar)=false AND ISNULL(_logabsen_terhitungkerja)=false AND _logabsen_masukkeluar='m' THEN
                            IF _logabsen_waktu>_jadwalshift_waktumasuk AND ISNULL(_logabsen_idalasan)=false AND _logabsen_masukkeluar='m' AND _logabsen_terhitungkerja='y' THEN
                                # perbaiki pada table _hasil, supaya dianggap tdk terlambat
                                UPDATE _hasil SET waktu=_jadwalshift_waktumasuk WHERE waktu=_logabsen_waktu AND masukkeluar='m' LIMIT 1;
                                # ganti nilai waktu masuk supaya tdk terlambat
                                SET _logabsen_waktu = _jadwalshift_waktumasuk;
                            END IF;
                            SET _rekapshift_waktumasuk = _logabsen_waktu;
                            SET _rekapshift_selisihmasuk = TIMESTAMPDIFF(SECOND,_logabsen_waktu, _jadwalshift_waktumasuk);
                            IF _rekapshift_selisihmasuk<0 AND -1*_rekapshift_selisihmasuk<=_toleransi*60 THEN
                                SET _rekapshift_selisihmasuk = 0;
                            END IF;

                        END IF;
                    END IF;

                    #hitung waktu selisihkeluar
                    SET _logabsen_waktu = NULL;
                    SET _logabsen_masukkeluar=NULL;
                    SET _logabsen_terhitungkerja=NULL;
                    SELECT
                        waktu, masukkeluar, terhitungkerja INTO _logabsen_waktu, _logabsen_masukkeluar, _logabsen_terhitungkerja
                    FROM
                        _logabsen
                    WHERE
                        (waktu BETWEEN _start_of_session AND _end_of_session) AND
                        waktu>=_jadwalshift_waktupulang
                    ORDER BY
                        waktu ASC
                    LIMIT 1;
                    IF ISNULL(_logabsen_masukkeluar)=false AND ISNULL(_logabsen_terhitungkerja)=false AND _logabsen_masukkeluar='k' THEN
                        SET _rekapshift_waktukeluar = _logabsen_waktu;
                        SET _rekapshift_selisihkeluar = TIMESTAMPDIFF(SECOND, _jadwalshift_waktupulang, _logabsen_waktu);
                    ELSE
                        SET _logabsen_waktu = NULL;
                        SET _logabsen_masukkeluar=NULL;
                        SET _logabsen_terhitungkerja=NULL;
                        SELECT
                            waktu, masukkeluar, terhitungkerja INTO _logabsen_waktu, _logabsen_masukkeluar, _logabsen_terhitungkerja
                        FROM
                            _logabsen
                        WHERE
                            (waktu BETWEEN _start_of_session AND _end_of_session) AND
                            waktu<_jadwalshift_waktupulang
                        ORDER BY
                            waktu DESC
                        LIMIT 1;
                        IF ISNULL(_logabsen_masukkeluar)=false AND ISNULL(_logabsen_terhitungkerja)=false AND _logabsen_masukkeluar='k' THEN
                            SET _rekapshift_waktukeluar = _logabsen_waktu;
                            SET _rekapshift_selisihkeluar = TIMESTAMPDIFF(SECOND, _jadwalshift_waktupulang, _logabsen_waktu);
                        END IF;
                    END IF;
                ELSE
                    # jika TIDAK ada data _logabsen

                    SET _logabsen_waktu = NULL;
                    SET _logabsen_masukkeluar=NULL;
                    SET _logabsen_terhitungkerja=NULL;
                    SELECT
                        waktu, masukkeluar, idalasanmasukkeluar, terhitungkerja INTO _logabsen_waktu, _logabsen_masukkeluar, _logabsen_idalasan, _logabsen_terhitungkerja
                    FROM
                        logabsen
                    WHERE
                        (waktu BETWEEN _start_of_session AND _end_of_session) AND
                        status='v' AND
                        idpegawai=_idpegawai AND
                        waktu<=_jadwalshift_waktumasuk
                    ORDER BY
                        waktu DESC
                    LIMIT 1;
                    IF ISNULL(_logabsen_masukkeluar)=false AND ISNULL(_logabsen_terhitungkerja)=false AND _logabsen_masukkeluar='m' THEN
                        SET _rekapshift_waktumasuk = _logabsen_waktu;
                        SET _rekapshift_selisihmasuk = TIMESTAMPDIFF(SECOND, _logabsen_waktu, _jadwalshift_waktumasuk);
                    ELSE
                        SET _logabsen_waktu = NULL;
                        SET _logabsen_masukkeluar=NULL;
                        SET _logabsen_terhitungkerja=NULL;
                        SELECT
                            waktu, masukkeluar, idalasanmasukkeluar, terhitungkerja INTO _logabsen_waktu, _logabsen_masukkeluar, _logabsen_idalasan, _logabsen_terhitungkerja
                        FROM
                            logabsen
                        WHERE
                            (waktu BETWEEN _start_of_session AND _end_of_session) AND
                            status='v' AND
                            idpegawai=_idpegawai AND
                            waktu>_jadwalshift_waktumasuk
                        ORDER BY
                            waktu ASC
                        LIMIT 1;
                        IF ISNULL(_logabsen_masukkeluar)=false AND ISNULL(_logabsen_terhitungkerja)=false AND _logabsen_masukkeluar='m' THEN
                            IF _logabsen_waktu>_jadwalshift_waktumasuk AND ISNULL(_logabsen_idalasan)=false AND _logabsen_masukkeluar='m' AND _logabsen_terhitungkerja='y' THEN
                                # perbaiki pada table _hasil, supaya dianggap tdk terlambat
                                UPDATE _hasil SET waktu=_jadwalshift_waktumasuk WHERE waktu=_logabsen_waktu AND masukkeluar='m' LIMIT 1;
                                # ganti nilai waktu masuk supaya tdk terlambat
                                SET _logabsen_waktu = _jadwalshift_waktumasuk;
                            END IF;
                            SET _rekapshift_waktumasuk = _logabsen_waktu;
                            SET _rekapshift_selisihmasuk = TIMESTAMPDIFF(SECOND, _jadwalshift_waktumasuk, _logabsen_waktu);
                        END IF;
                    END IF;
                END IF;

                #hitung lama kerja
                SET _temp_masukkeluar_sebelum=NULL,
                    _temp_waktu_sebelum=NULL;
                OPEN cur_hasil;
                read_loop_cur_hasil: LOOP
                    SET done=false;
                    FETCH cur_hasil INTO _temp_id, _temp_flag, _temp_waktu, _temp_masukkeluar, _temp_override;
                    IF done THEN
                        LEAVE read_loop_cur_hasil;
                    ELSE
                        IF _temp_masukkeluar='m' THEN
                            IF ISNULL(_temp_masukkeluar_sebelum)=true THEN
                                SET _temp_masukkeluar_sebelum = _temp_masukkeluar;
                                SET _temp_waktu_sebelum = _temp_waktu;
                            END IF;
                        ELSEIF _temp_masukkeluar='k' THEN
                            IF ISNULL(_temp_masukkeluar_sebelum)=false THEN
                                IF (_temp_waktu_sebelum<=_jadwalshift_waktupulang) AND (_temp_waktu>=_jadwalshift_waktumasuk) THEN
                                    IF _temp_waktu_sebelum<_jadwalshift_waktumasuk THEN
                                        SET _temp_waktu_sebelum = _jadwalshift_waktumasuk;
                                    END IF;
                                    IF _temp_waktu>_jadwalshift_waktupulang THEN
                                        SET _temp_waktu = _jadwalshift_waktupulang;
                                    END IF;
                                    SET _rekapshift_lamakerja = _rekapshift_lamakerja + TIMESTAMPDIFF(SECOND, _temp_waktu_sebelum, _temp_waktu);
                                END IF;
                            END IF;
                            SET _temp_masukkeluar_sebelum=NULL,
                                _temp_waktu_sebelum=NULL;
                            IF _temp_waktu>=_jadwalshift_waktupulang THEN
                                LEAVE read_loop_cur_hasil;
                            END IF;
                        END IF;
                    END IF;
                END LOOP read_loop_cur_hasil;
                CLOSE cur_hasil;

                #hitung lama lembur
                SET _jadwalshift_waktulembur = ADDDATE(_jadwalshift_waktupulang, INTERVAL _hitunglemburstlh MINUTE);
                SET _temp_masukkeluar_sebelum=NULL,
                    _temp_waktu_sebelum=NULL;
                OPEN cur_hasil;
                read_loop_cur_hasil: LOOP
                    SET done=false;
                    FETCH cur_hasil INTO _temp_id, _temp_flag, _temp_waktu, _temp_masukkeluar, _temp_override;
                    IF done THEN
                        LEAVE read_loop_cur_hasil;
                    ELSE
                        IF _temp_masukkeluar='m' THEN
                            IF ISNULL(_temp_masukkeluar_sebelum)=true THEN
                                SET _temp_masukkeluar_sebelum = _temp_masukkeluar;
                                SET _temp_waktu_sebelum = _temp_waktu;
                            END IF;
                        ELSEIF _temp_masukkeluar='k' THEN
                            IF ISNULL(_temp_masukkeluar_sebelum)=false THEN
                                IF (_temp_waktu_sebelum<=_end_of_session) AND (_temp_waktu>=_jadwalshift_waktulembur) THEN
                                    IF _temp_waktu_sebelum<_jadwalshift_waktulembur THEN
                                        SET _temp_waktu_sebelum = _jadwalshift_waktulembur;
                                    END IF;
                                    IF _temp_waktu>_end_of_session THEN
                                        SET _temp_waktu = _end_of_session;
                                    END IF;
                                    SET _rekapshift_lamalembur = _rekapshift_lamalembur + TIMESTAMPDIFF(SECOND, _temp_waktu_sebelum, _temp_waktu);
                                END IF;
                            END IF;
                            SET _temp_masukkeluar_sebelum=NULL,
                                _temp_waktu_sebelum=NULL;
                            IF _temp_waktu>=_end_of_session THEN
                                LEAVE read_loop_cur_hasil;
                            END IF;
                        END IF;
                    END IF;
                END LOOP read_loop_cur_hasil;
                CLOSE cur_hasil;

                SET _rekapshift_masukkerja = 't';
                IF ISNULL(_rekapshift_waktumasuk)=false THEN
                    SET _rekapshift_masukkerja = 'y';
                END IF;

                # periksa apakah masuk konfirmasi lembur
                IF _default_perlakuanlembur='konfirmasi' THEN
                    IF _flag_posting_otomatis='y' THEN
                        IF _rekapshift_lamalembur>0 THEN
                            # cek apakah ada flag lembur?
                            SET _logabsen_adadata = 0;
                            SELECT 1 INTO _logabsen_adadata FROM _logabsen WHERE flag='lembur' OR flag='tidak-lembur' LIMIT 1;
                            IF _logabsen_adadata=0 THEN
                                # cari logabsen yang paling akhir
                                SET _logabsen_adadata = 0;
                                SELECT
                                    1, idlogabsen, flag
                                    INTO
                                    _logabsen_adadata, _temp_id, _logabsen_flag
                                FROM
                                    _logabsen
                                WHERE
                                    (waktu BETWEEN _jadwalshift_waktumasuk AND _end_of_session)
                                ORDER BY waktu DESC LIMIT 1;
                                IF _logabsen_adadata=1 THEN
                                    IF _logabsen_flag='' THEN
                                        # masukkan ke table konfirmasi_lembur
                                        INSERT IGNORE INTO konfirmasi_lembur VALUES(NULL, _idpegawai, _temp_id, 'shift', 'c', NOW());
                                    END IF;
                                END IF;
                            END IF;
                        END IF;
                    END IF;
                END IF;


                # perhatikan flag terlambat
                CALL posting_rekapshift_checkflag(
                                       _toleransi,
                                       _default_perlakuanlembur,
                                       _jadwalshift_waktumasuk,
                                       _jadwalshift_waktupulang,
                                       _end_of_session,
                                       _rekapshift_selisihmasuk,
                                       _rekapshift_selisihkeluar,
                                       _rekapshift_lamalembur,
                                       _rekapshift_flag_terlambat,
                                       _rekapshift_flag_pulangawal,
                                       _rekapshift_flag_lembur);

                #simpan di rekapshift
                INSERT INTO rekapshift VALUES (
                    NULL,                           #id
                    _tanggal,                       #tanggal
                    _idpegawai,                     #idpegawai
                    _idjamkerjashift,               #idjamkerjashift
                    _rekapshift_masukkerja,         #masukkerja
                    _rekapshift_waktumasuk,         #waktumasuk
                    _rekapshift_waktukeluar,        #waktukeluar
                    _rekapshift_selisihmasuk,       #selisihmasuk
                    _rekapshift_selisihkeluar,      #selisihkeluar
                    _rekapshift_lamakerja,          #lamakerja
                    _rekapshift_lamalembur,         #lamakerja
                    _rekapshift_flag_terlambat,     #flag_terlambat
                    _rekapshift_flag_pulangawal,    #flag_pulangawal
                    _rekapshift_flag_lembur,        #_flag_lembur
                    NOW()                           #inserted
                );

            END IF;

        END IF;
    END LOOP read_loop_cur_jadwalshift;
    CLOSE cur_jadwalshift;

END //

DROP PROCEDURE IF EXISTS posting//
# _flag_posting_otomatis bernilai "y" dan "t"
CREATE PROCEDURE posting(IN _tanggal DATE, IN _idpegawai INT UNSIGNED, IN _flag_posting_otomatis VARCHAR(1))
BEGIN
    DECLARE _data_ada BOOLEAN DEFAULT TRUE;
    DECLARE _jamkerja_id INT UNSIGNED DEFAULT NULL;
    DECLARE _jamkerja_jenis ENUM ('full','shift') DEFAULT 'full';
    DECLARE _jamkerjakhusus_id INT UNSIGNED DEFAULT NULL;

    DECLARE _adalogabsen INT UNSIGNED DEFAULT NULL;
    DECLARE _temp_id INT;
    DECLARE _logabsen_flag ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur');
    DECLARE _default_perlakuanlembur ENUM('tanpalembur','konfirmasi','lembur');

    DECLARE _harilibur_id INT UNSIGNED DEFAULT NULL;
    DECLARE _jadwal_masukkerja ENUM('y','t') DEFAULT 't';
    DECLARE _jadwal_toleransi INT DEFAULT NULL;
    DECLARE _jadwal_acuanterlambat enum('jadwal','toleransi') DEFAULT 'jadwal';
    DECLARE _jadwal_hitunglemburstlh INT UNSIGNED DEFAULT NULL;

    DECLARE _ijintidakmasuk_idalasan INT UNSIGNED DEFAULT NULL;
    DECLARE _ijintidakmasuk_terhitungkerja ENUM('y','t') DEFAULT 't';
    DECLARE _ijintidakmasuk_kategori ENUM('s','i','d','a','c') DEFAULT NULL;

    DECLARE _rekapabsen_id INT UNSIGNED DEFAULT NULL;
    DECLARE _rekapabsen_masukkerja ENUM('y','t') DEFAULT 't';
    DECLARE _rekapabsen_jumlahsesi INT DEFAULT 0;
    DECLARE _rekapabsen_jadwallamakerja INT DEFAULT 0;
    DECLARE _rekapabsen_idalasanmasuk INT UNSIGNED DEFAULT NULL;
    DECLARE _rekapabsen_waktumasuk DATETIME DEFAULT NULL;
    DECLARE _rekapabsen_waktukeluar DATETIME DEFAULT NULL;
    DECLARE _rekapabsen_selisihmasuk INT DEFAULT 0;
    DECLARE _rekapabsen_selisihkeluar INT DEFAULT 0;
    DECLARE _rekapabsen_lamakerja INT DEFAULT 0;
    DECLARE _rekapabsen_lamaflexytime INT DEFAULT 0;
    DECLARE _rekapabsen_lamalembur INT DEFAULT 0;
    DECLARE _rekapabsen_overlap INT DEFAULT 0;
    DECLARE _rekapabsen_absentidaklengkap ENUM('','m','k') DEFAULT '';
    DECLARE _rekapabsen_flag_terlambat ENUM('','y','t') DEFAULT '';
    DECLARE _rekapabsen_flag_pulangawal ENUM('','y','t') DEFAULT '';
    DECLARE _rekapabsen_flag_lembur ENUM('','y','t') DEFAULT '';


    DECLARE _kurangabsen_masuk ENUM('y','t') DEFAULT 't';
    DECLARE _kurangabsen_keluar ENUM('y','t') DEFAULT 't';

    DECLARE _pegawai_flexytime ENUM('y','t') DEFAULT 't';
    DECLARE _pegawai_idagama INT;
    DECLARE _terapkan_flexytime ENUM('y','t') DEFAULT 't';
    DECLARE _jadwal_waktupulang_normal DATETIME DEFAULT NULL;
    DECLARE _jadwal_waktupulang_flexytime DATETIME DEFAULT NULL;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET _data_ada = FALSE;

    DELETE FROM rekapshift WHERE tanggal=_tanggal AND idpegawai=_idpegawai;
    DELETE FROM rekapabsen WHERE idpegawai=_idpegawai AND tanggal=_tanggal;

    # table _jadwal sudah diisi pada procedure posting_persiapanjadwal
    # Untuk jaga2 saja: supaya tdk error, table _jadwal di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _jadwal (
        `id`                    INT UNSIGNED AUTO_INCREMENT,
        `idjamkerjashift`       INT UNSIGNED,
        `waktu`                 DATETIME,
        `masukkeluar`           ENUM('m','k'),
        `checking`              ENUM('', 'start','end'), # start: absen masuk, end: absen pulang (dalam satu sesi selain istirahat)
        `shiftpertamaterakhir`  ENUM('', 'pertama', 'terakhir'), # apakah shift tsb adalah shift pertama atau terakhir pada tanggal tsb?
        `shiftsambungan`        ENUM('y','t'), # apakah shift tsb terdapat sambungan dari shift sebelumnya/sesudahnya yg telah dijadwalkan?
        PRIMARY KEY(id)
    ) ENGINE=Memory;
    TRUNCATE _jadwal;

    # table _logabsen sudah diisi pada procedure posting_persiapanlogabsen
    # Untuk jaga2 saja: supaya tdk error, table _logabsen di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _logabsen (
        `id`                INT UNSIGNED AUTO_INCREMENT,
        `idlogabsen`        INT UNSIGNED,
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `idalasan`          INT UNSIGNED,
        `terhitungkerja`    ENUM('y','t'),
        `flag`              ENUM('','tidak-terlambat', 'tidak-pulangawal', 'lembur', 'tidak-lembur'),
        `del`               ENUM('y','t'),
        INDEX `idx__log_waktu` (`waktu`),
        INDEX `idx__log_masukkeluar` (`masukkeluar`),
        INDEX `idx__log_del` (`del`),
        PRIMARY KEY(id)
    ) ENGINE=Memory;
    TRUNCATE _logabsen;

    # table _hasil seharusnya sudah dicreate dan diinsert posting_hitungkerja
    # Untuk jaga2 saja: supaya tdk error, table _hasil di create lagi jika belum di create.
    CREATE TEMPORARY TABLE IF NOT EXISTS _hasil (
        `id`                INT UNSIGNED,
        `idlogabsen`        INT UNSIGNED,
        `idjamkerjashift`   INT UNSIGNED,
        `terhitung`         ENUM('k','l'),
        `flag`              ENUM('j','p'),
        `waktu`             DATETIME,
        `masukkeluar`       ENUM('m','k'),
        `override`          ENUM('y','t'),
        INDEX `idx__absen_waktu` (`waktu`),
        INDEX `idx__absen_masukkeluar` (`masukkeluar`)
    ) ENGINE=Memory;
    TRUNCATE _hasil;

    SET _data_ada = TRUE;
    SELECT
        idagama, flexytime INTO
        _pegawai_idagama, _pegawai_flexytime
    FROM
        pegawai
    WHERE
        id=_idpegawai AND
        del='t' AND
        ((status='a' AND (ISNULL(tanggaltdkaktif)=true OR (ISNULL(tanggaltdkaktif)=false AND tanggalaktif<=_tanggal))) OR (status='t' AND ISNULL(tanggaltdkaktif)=false AND tanggaltdkaktif>_tanggal)) AND
        id=_idpegawai
    LIMIT 1;
    IF _data_ada = TRUE THEN
        # apakah idpegawai ada? jamkerja-nya ada?
        SELECT
            jk.id, jk.jenis ,jk.toleransi, jk.acuanterlambat, jk.hitunglemburstlh INTO
            _jamkerja_id, _jamkerja_jenis, _jadwal_toleransi, _jadwal_acuanterlambat, _jadwal_hitunglemburstlh
        FROM
            pegawaijamkerja pjk,
            jamkerja jk
        WHERE
            pjk.idpegawai=_idpegawai AND
            jk.id=pjk.idjamkerja AND
            pjk.berlakumulai<=_tanggal
        ORDER BY
            pjk.berlakumulai DESC
        LIMIT 1
        ;

        # jika jenis adalah shift, abaikan hitunglemburstlh
    #   IF (_jamkerja_jenis='shift') THEN
    #       SET _jadwal_hitunglemburstlh=9999;
    #   END IF;

        SELECT default_perlakuanlembur INTO _default_perlakuanlembur FROM pengaturan LIMIT 1;

        # jika pegawai ada dan ada jamkerjanya
        IF ISNULL(_jamkerja_id) = false THEN
            IF ceksimpanrekap(_tanggal, _idpegawai, _flag_posting_otomatis, _jamkerja_jenis)='y' THEN
                # persiapan jadwal beberapa hari untuk mengetahui start dan end session

                # persiapan jadwal yang berlaku
                CALL posting_persiapanjadwal(
                                      _tanggal,
                                      _idpegawai,
                                      _pegawai_idagama,
                                      _jamkerja_id,
                                      _jamkerja_jenis,
                                      _harilibur_id,
                                      _jadwal_masukkerja,
                                      _jadwal_toleransi,
                                      _jadwal_hitunglemburstlh,
                                      _jamkerjakhusus_id,
                                      _rekapabsen_jumlahsesi,
                                      _rekapabsen_jadwallamakerja
                                    );
                # persiapan logabsen
                CALL posting_persiapanlogabsen( _tanggal,
                                                _idpegawai,
                                                _jamkerja_jenis,
                                                _jadwal_toleransi,
                                                _jadwal_acuanterlambat,
                                                _pegawai_flexytime,
                                                _terapkan_flexytime,
                                                _jadwal_waktupulang_normal,
                                                _jadwal_waktupulang_flexytime,
                                                _rekapabsen_masukkerja,
                                                _rekapabsen_idalasanmasuk,
                                                _rekapabsen_waktumasuk,
                                                _rekapabsen_waktukeluar,
                                                _rekapabsen_selisihmasuk,
                                                _kurangabsen_masuk,
                                                _kurangabsen_keluar,
                                                _rekapabsen_flag_terlambat
                                              );
                # jika _logabsen ada isinya
                SET _adalogabsen = 0;
                SELECT 1 INTO _adalogabsen FROM _logabsen LIMIT 1;

                IF _adalogabsen=1 THEN
                    SET _rekapabsen_selisihmasuk = 0;

                    # eliminasi hasil
                    CALL posting_hitungkerja(
                                                 _rekapabsen_lamakerja
                                               );

                    CALL posting_hitungselisihmasukkeluar(
                                                 _jadwal_toleransi,
                                                 _jadwal_acuanterlambat,
                                                 _rekapabsen_selisihmasuk,
                                                 _rekapabsen_selisihkeluar,
                                                 _rekapabsen_overlap
                                               );

                    IF (_terapkan_flexytime='y') THEN
                        # pastikan _jadwal_waktupulang_normal TIDAK SAMA DENGAN _jadwal_waktupulang_flexytime
                        IF ISNULL(_jadwal_waktupulang_normal)=false AND ISNULL(_jadwal_waktupulang_flexytime)=false AND _jadwal_waktupulang_normal<>_jadwal_waktupulang_flexytime THEN
                            CALL posting_hitungflexytime(
                                                       _jadwal_waktupulang_normal,
                                                       _jadwal_waktupulang_flexytime,
                                                       _rekapabsen_lamaflexytime
                                                     );
                        END IF;
                    END IF;

                    #IF (_jamkerja_jenis='full') THEN
                        CALL posting_hitunglembur(
                                                   _jadwal_hitunglemburstlh,
                                                   _rekapabsen_lamalembur
                                                 );
                    #END IF;

                    # periksa apakah masuk konfirmasi lembur
                    IF (_jamkerja_jenis='full') THEN
                        IF _default_perlakuanlembur='konfirmasi' THEN
                            IF _flag_posting_otomatis='y' THEN
                                IF _rekapabsen_lamalembur>0 THEN
                                    # cek apakah ada flag lembur?
                                    SET _adalogabsen = 0;
                                    SELECT 1 INTO _adalogabsen FROM _logabsen WHERE flag='lembur' OR flag='tidak-lembur' LIMIT 1;
                                    IF _adalogabsen=0 THEN
                                        # cari logabsen yang paling akhir
                                        SET _adalogabsen = 0;
                                        SELECT
                                            1, idlogabsen, flag
                                            INTO
                                            _adalogabsen, _temp_id, _logabsen_flag
                                        FROM
                                            _logabsen
                                        ORDER BY waktu DESC LIMIT 1;
                                        IF _adalogabsen=1 THEN
                                            IF _logabsen_flag='' THEN
                                                # masukkan ke table konfirmasi_lembur
                                                INSERT IGNORE INTO konfirmasi_lembur VALUES(NULL, _idpegawai, _temp_id, 'full', 'c', NOW());
                                            END IF;
                                        END IF;
                                    END IF;
                                END IF;
                            END IF;
                        END IF;
                    END IF;

                    # perhatikan flag terlambat, pulang awal, lembur
                    CALL posting_rekapabsen_checkflag(
                                           _jadwal_toleransi,
                                            _jadwal_acuanterlambat,
                                           _default_perlakuanlembur,
                                           _rekapabsen_selisihmasuk,
                                           _rekapabsen_selisihkeluar,
                                           _rekapabsen_lamalembur,
                                           _rekapabsen_flag_terlambat,
                                           _rekapabsen_flag_pulangawal,
                                           _rekapabsen_flag_lembur);

                ELSE
                    # cek ijin tidak masuk
                    SELECT
                        itm.idalasantidakmasuk, IF(ISNULL(atm.kategori) OR atm.kategori IN ('s','i','a','c'),'t','y'), atm.kategori INTO
                        _ijintidakmasuk_idalasan, _ijintidakmasuk_terhitungkerja, _ijintidakmasuk_kategori
                    FROM
                        ijintidakmasuk itm
                        LEFT JOIN alasantidakmasuk atm ON itm.idalasantidakmasuk=atm.id
                    WHERE
                        itm.idpegawai=_idpegawai AND
                        itm.status='a' AND
                        (_tanggal BETWEEN itm.tanggalawal AND itm.tanggalakhir)
                    ORDER BY
                        IF(ISNULL(atm.kategori) OR atm.kategori IN ('s','i','a','c'),'t','y') DESC
                    LIMIT 1;

                    IF (_ijintidakmasuk_terhitungkerja='y') THEN
                        SET _rekapabsen_masukkerja = 'y';
                        SET _rekapabsen_lamakerja = _rekapabsen_jadwallamakerja;
                    END IF;
                END IF;

                SET _rekapabsen_absentidaklengkap = '';
                IF (_jadwal_masukkerja='y' AND _rekapabsen_masukkerja='y' AND ISNULL(_ijintidakmasuk_idalasan)=true AND _rekapabsen_lamakerja*100/_rekapabsen_jadwallamakerja<80) THEN
                    IF (_kurangabsen_masuk='y') THEN
                        SET _rekapabsen_absentidaklengkap = 'm';
                    ELSEIF (_kurangabsen_keluar='y') THEN
                        SET _rekapabsen_absentidaklengkap = 'k';
                    END IF;
                END IF;

                # hitung rekapan
                INSERT INTO rekapabsen VALUES (
                    NULL,                          # id
                    _idpegawai,                    # idpegawai
                    _tanggal,                      # tanggal
                    _harilibur_id,                 # idharilibur
                    _rekapabsen_masukkerja,        # masukkerja
                    _rekapabsen_jumlahsesi,        # jumlahsesi
                    _ijintidakmasuk_idalasan,      # idalasantidakmasuk
                    _ijintidakmasuk_kategori,      # alasantidakmasukkategori
                    _jamkerja_id,                  # idjamkerja
                    _jamkerjakhusus_id,            # idjamkerjafullkhusus
                    _jadwal_masukkerja,            # jadwalmasukkerja
                    _jamkerja_jenis,               # jenisjamkerja
                    _rekapabsen_jadwallamakerja,   # jadwallamakerja
                    _rekapabsen_idalasanmasuk,     # idalasanmasuk
                    _rekapabsen_waktumasuk,        # waktumasuk
                    _rekapabsen_waktukeluar,       # waktukeluar
                    _rekapabsen_selisihmasuk,      # selisihmasuk
                    _rekapabsen_selisihkeluar,     # selisihkeluar
                    _rekapabsen_lamakerja,         # lamakerja
                    _rekapabsen_lamaflexytime,     # lamaflexytime
                    _rekapabsen_lamalembur,        # lamalembur
                    _rekapabsen_overlap,           # overlap
                    _rekapabsen_flag_terlambat,    # flag_terlambat
                    _rekapabsen_flag_pulangawal,   # flag_pulangawal
                    _rekapabsen_flag_lembur,       # flag_lembur
                    _rekapabsen_absentidaklengkap, # absentidaklengkap
                    'w'                            # status
                );

                SELECT LAST_INSERT_ID() INTO _rekapabsen_id;

                INSERT INTO rekapabsen_jadwal SELECT NULL, _rekapabsen_id, idjamkerjashift, waktu, masukkeluar, checking, shiftpertamaterakhir, shiftsambungan FROM _jadwal ORDER BY waktu ASC;
                INSERT INTO rekapabsen_logabsen_all SELECT NULL, _rekapabsen_id, idlogabsen, waktu, masukkeluar, idalasan, terhitungkerja, flag, status FROM _logabsen_all ORDER BY waktu ASC;
                INSERT INTO rekapabsen_logabsen SELECT NULL, _rekapabsen_id, idlogabsen, waktu, masukkeluar, idalasan, terhitungkerja, flag FROM _logabsen ORDER BY waktu ASC;
                INSERT INTO rekapabsen_hasil SELECT NULL, _rekapabsen_id, idlogabsen, idjamkerjashift, terhitung, flag, waktu, masukkeluar, override FROM _hasil ORDER BY waktu ASC;

                IF (ISNULL(_rekapabsen_waktumasuk)=false AND ISNULL(_rekapabsen_waktukeluar)=true) THEN
                    INSERT IGNORE INTO rekapabsen_logabsen SELECT NULL, _rekapabsen_id, id, waktu, masukkeluar, idalasanmasukkeluar, terhitungkerja, flag FROM logabsen WHERE idpegawai=_idpegawai AND masukkeluar="m" AND waktu=_rekapabsen_waktumasuk ORDER BY waktu DESC LIMIT 1;
                END IF;

                #hitung rekapshift
                IF (_jamkerja_jenis='shift' AND ISNULL(_jamkerjakhusus_id)=true) THEN
                    CALL posting_rekapshift(_tanggal, _idpegawai, _default_perlakuanlembur, _flag_posting_otomatis);
                END IF;

            END IF;

        END IF;
    END IF;
END//

DELIMITER ;
