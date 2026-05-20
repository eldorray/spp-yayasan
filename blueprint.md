# PRD: Aplikasi Keuangan Sekolah Yayasan untuk MI dan SMP

## 1. Ringkasan Eksekutif

Aplikasi ini adalah sistem keuangan sekolah custom untuk satu yayasan yang memiliki dua instansi pendidikan, yaitu **MI** dan **SMP**. Fokus utama aplikasi adalah membantu petugas sekolah mencatat, memantau, dan melaporkan pembayaran siswa secara rapi.

Kebutuhan utama aplikasi:

1. Mencatat pembayaran siswa per bulan.
2. Membedakan data antara MI dan SMP.
3. Mencatat pembayaran kegiatan tambahan seperti study tour, camping, LDK, dan kegiatan lain.
4. Menerapkan aturan khusus untuk SMP:
   - Siswa dari **Kota Tangerang** tidak dikenakan biaya bulanan sekolah.
   - Siswa dari **luar Kota Tangerang** tetap memiliki tagihan bulanan.
5. Membuat laporan pembayaran, tunggakan, dan rekap pemasukan.

Aplikasi ini ditujukan untuk digunakan oleh admin keuangan sekolah, kepala sekolah, dan pihak yayasan.

---

## 2. Latar Belakang dan Masalah

Saat ini proses pencatatan keuangan sekolah sering kali dilakukan secara manual menggunakan buku, Excel, atau catatan terpisah. Cara tersebut berisiko menyebabkan:

- Data pembayaran tercecer.
- Sulit membedakan pembayaran MI dan SMP.
- Sulit mengetahui siswa yang sudah bayar dan belum bayar.
- Kesalahan pencatatan nominal.
- Sulit membuat laporan bulanan.
- Sulit menerapkan aturan khusus seperti gratis biaya bulanan untuk siswa SMP domisili Kota Tangerang.
- Sulit melacak pembayaran kegiatan tambahan.

Aplikasi ini dibuat untuk menyederhanakan proses administrasi keuangan sekolah agar lebih rapi, cepat, dan mudah diaudit.

---

## 3. Tujuan Produk

### 3.1 Tujuan Bisnis

- Membantu yayasan memiliki sistem pencatatan keuangan yang terpusat.
- Mengurangi kesalahan pencatatan pembayaran.
- Mempercepat pembuatan laporan keuangan sekolah.
- Mempermudah monitoring pemasukan dari MI dan SMP.

### 3.2 Tujuan Pengguna

- Admin dapat mencatat pembayaran siswa dengan cepat.
- Admin dapat melihat status pembayaran siswa per bulan.
- Admin dapat mencatat pembayaran kegiatan tambahan.
- Kepala sekolah dan yayasan dapat melihat laporan tanpa harus menunggu rekap manual.

### 3.3 Tujuan Operasional

- Memisahkan data keuangan MI dan SMP.
- Menyediakan laporan pembayaran harian, bulanan, per kelas, per siswa, dan per instansi.
- Menampilkan daftar tunggakan secara otomatis.
- Menerapkan aturan tagihan berdasarkan domisili siswa.

### 3.4 Tujuan Teknis

- Sistem mudah digunakan oleh admin non-teknis.
- Data tersimpan aman dan terstruktur.
- Sistem mendukung multi-instansi dalam satu yayasan.
- Sistem dapat dikembangkan menjadi pembayaran online di masa depan.

---

## 4. Non-Goals

Untuk versi awal, aplikasi **tidak mencakup**:

- Pembayaran online via payment gateway.
- Integrasi bank otomatis.
- Sistem akuntansi lengkap.
- Payroll guru dan karyawan.
- Inventaris sekolah.
- Absensi siswa.
- Akademik atau nilai siswa.
- Aplikasi mobile untuk orang tua.
- Notifikasi WhatsApp otomatis, kecuali masuk ke pengembangan berikutnya.
- Multi-yayasan.

---

## 5. Target Pengguna

### 5.1 Admin Keuangan

| Aspek | Detail |
|---|---|
| Profil | Staff TU atau bendahara sekolah |
| Kebutuhan | Input pembayaran, cek status bayar, cetak laporan |
| Pain Point | Pencatatan manual memakan waktu dan rawan salah |
| Ekspektasi | Sistem cepat, sederhana, dan tidak membingungkan |

### 5.2 Kepala Sekolah MI

| Aspek | Detail |
|---|---|
| Profil | Pimpinan instansi MI |
| Kebutuhan | Melihat laporan pembayaran MI |
| Pain Point | Harus menunggu rekap manual dari admin |
| Ekspektasi | Bisa melihat laporan ringkas dan akurat |

### 5.3 Kepala Sekolah SMP

| Aspek | Detail |
|---|---|
| Profil | Pimpinan instansi SMP |
| Kebutuhan | Melihat laporan siswa luar Kota Tangerang yang wajib bayar, serta pembayaran kegiatan |
| Pain Point | Perlu membedakan siswa gratis dan siswa berbayar |
| Ekspektasi | Sistem otomatis membedakan status tagihan berdasarkan domisili |

### 5.4 Yayasan

| Aspek | Detail |
|---|---|
| Profil | Pengelola utama dua instansi |
| Kebutuhan | Melihat total pemasukan MI dan SMP |
| Pain Point | Data tiap sekolah tersebar |
| Ekspektasi | Dashboard gabungan dan laporan per instansi |

---

## 6. Value Proposition

Aplikasi ini membantu yayasan dan sekolah mencatat pembayaran siswa secara lebih rapi, terpusat, dan sesuai aturan khusus tiap instansi.

Nilai utama:

- Satu sistem untuk dua instansi: MI dan SMP.
- Status pembayaran siswa lebih mudah dilacak.
- Aturan gratis untuk siswa SMP Kota Tangerang dapat diterapkan otomatis.
- Pembayaran kegiatan tambahan dapat dicatat terpisah dari pembayaran bulanan.
- Laporan keuangan bisa dibuat lebih cepat dan akurat.

---

## 7. Aturan Bisnis Utama

| ID | Aturan Bisnis | Penjelasan |
|---|---|---|
| BR-001 | Yayasan memiliki dua instansi | Instansi awal adalah MI dan SMP |
| BR-002 | MI memiliki pembayaran bulanan siswa | Semua siswa MI diasumsikan memiliki tagihan bulanan |
| BR-003 | SMP memiliki pembayaran bulanan khusus siswa luar Kota Tangerang | Siswa SMP domisili Kota Tangerang gratis biaya bulanan |
| BR-004 | Siswa SMP Kota Tangerang tetap bisa memiliki tagihan kegiatan | Gratis hanya berlaku untuk biaya bulanan sekolah |
| BR-005 | Kegiatan tambahan dapat dibuat oleh admin | Contoh: study tour, camping, LDK, ujian, seragam, buku |
| BR-006 | Tagihan kegiatan dapat berlaku untuk semua siswa atau siswa tertentu | Misalnya hanya kelas tertentu atau peserta tertentu |
| BR-007 | Pembayaran dapat dilakukan sebagian atau lunas | Sistem harus mendukung cicilan |
| BR-008 | Setiap pembayaran harus memiliki bukti transaksi internal | Minimal nomor transaksi atau kuitansi |
| BR-009 | Data MI dan SMP harus bisa difilter terpisah | Untuk laporan dan operasional |
| BR-010 | Admin tidak boleh menghapus transaksi tanpa jejak | Harus ada log atau status pembatalan |
| BR-011 | Tahun ajaran adalah scope utama data operasional | Kelas, penempatan siswa, tagihan, tarif, kegiatan, dan pembayaran terikat pada tahun ajaran |
| BR-012 | Hanya satu tahun ajaran yang aktif pada satu waktu | UI auto-select tahun ajaran aktif; admin dapat melihat data tahun ajaran sebelumnya |
| BR-013 | Perubahan tarif tidak berlaku retroaktif | Tarif disimpan per tahun ajaran; mengubah tarif tahun baru tidak mempengaruhi tagihan tahun sebelumnya |
| BR-014 | Data tahun ajaran sebelumnya tetap tersedia | Tunggakan, riwayat pembayaran, dan laporan tahun sebelumnya tetap bisa diakses dan tidak hilang saat tahun ajaran berganti |
| BR-015 | Tunggakan tahun lalu masih bisa dibayar | Siswa yang masih memiliki tunggakan dari tahun ajaran sebelumnya tetap bisa melakukan pembayaran kapan saja |
| BR-016 | Data master siswa tidak terikat tahun ajaran | Data dasar siswa (NIS, nama, domisili) bersifat permanen dan tidak perlu diinput ulang setiap tahun ajaran baru |
| BR-017 | Penempatan kelas dilakukan setiap tahun ajaran baru | Siswa lama ditempatkan ke kelas baru (kenaikan kelas); siswa yang lulus atau keluar tidak ditempatkan sehingga otomatis tidak memiliki tagihan baru |
| BR-018 | Data siswa dapat disinkronisasi dari API Data Induk | Data diambil dari `https://datainduk.ypdhalmadani.sch.id/api/[source]/allSiswa`; siswa yang sudah ada (berdasarkan NISN/NIS) diperbarui, siswa baru ditambahkan, siswa yang sudah ada tidak dihapus |

---

## 8. Alur Pergantian Tahun Ajaran

### 8.1 Proses Pergantian

Ketika tahun ajaran baru dimulai, admin melakukan langkah berikut:

1. **Buat tahun ajaran baru** (misal 2026/2027).
2. **Penempatan siswa lama ke kelas baru** — siswa naik kelas atau pindah kelas sesuai keputusan sekolah.
3. **Input siswa baru** — murid baru yang masuk di tahun ajaran ini diinput sebagai data siswa baru, lalu ditempatkan ke kelas.
4. **Siswa lulus atau keluar** — tidak ditempatkan ke kelas manapun di tahun ajaran baru, sehingga otomatis tidak memiliki tagihan baru.
5. **Atur tarif tagihan baru** — nominal tagihan bulanan untuk tahun ajaran baru diatur (bisa sama atau berbeda dari tahun sebelumnya).
6. **Generate tagihan bulanan** — tagihan baru dibuat untuk siswa yang sudah ditempatkan di tahun ajaran aktif.
7. **Aktifkan tahun ajaran baru** — UI berpindah ke tahun ajaran baru sebagai default.

### 8.2 Data Tahun Ajaran Sebelumnya

- Semua data tahun ajaran sebelumnya **tetap tersimpan dan dapat diakses**.
- **Tunggakan tahun lalu tidak hilang** — tetap muncul di laporan tunggakan dan masih bisa dibayar kapan saja.
- **Riwayat pembayaran** tetap bisa dilihat untuk keperluan audit.
- **Laporan tahun sebelumnya** tetap bisa di-generate dan di-export.
- Admin dapat berpindah ke tahun ajaran sebelumnya untuk melihat atau mengelola data lama.

### 8.3 Hubungan Data Siswa dan Tahun Ajaran

| Data | Terikat Tahun Ajaran? | Keterangan |
|---|---|---|
| Data dasar siswa (NIS, nama, domisili) | Tidak | Data permanen, input sekali saja |
| Penempatan kelas | Ya | Siswa ditempatkan ke kelas setiap tahun ajaran |
| Tagihan bulanan | Ya | Dibuat per tahun ajaran berdasarkan penempatan |
| Tagihan kegiatan | Ya | Kegiatan terjadi dalam tahun ajaran tertentu |
| Pembayaran | Ya | Mengikuti tagihan yang terikat tahun ajaran |
| Tunggakan | Ya (tapi lintas tahun) | Tunggakan tahun lalu tetap bisa dibayar di tahun berjalan |

---

## 9. Use Case Utama

### 9.1 Input Data Siswa

| Komponen | Detail |
|---|---|
| Aktor | Admin |
| Tujuan | Menambahkan data siswa baru ke sistem |
| Precondition | Admin sudah login, tahun ajaran aktif tersedia |
| Alur Utama | Admin pilih instansi, isi data siswa (NIS, nama, domisili), simpan, lalu tempatkan ke kelas pada tahun ajaran aktif |
| Alternate Flow | Admin import data siswa dari Excel |
| Edge Case | NIS duplikat, kelas belum dibuat, domisili kosong |
| Output | Data siswa tersimpan dan ditempatkan ke kelas pada tahun ajaran aktif |

### 9.2 Penempatan Siswa (Kenaikan Kelas)

| Komponen | Detail |
|---|---|
| Aktor | Admin |
| Tujuan | Menempatkan siswa lama ke kelas baru di tahun ajaran baru |
| Precondition | Tahun ajaran baru sudah dibuat, kelas sudah tersedia |
| Alur Utama | Admin pilih tahun ajaran baru, pilih kelas tujuan, pilih siswa yang naik kelas, simpan |
| Alternate Flow | Admin melakukan promosi massal (semua siswa kelas X naik ke kelas Y) |
| Edge Case | Siswa lulus tidak ditempatkan, siswa pindah instansi |
| Output | Siswa terdaftar di kelas baru pada tahun ajaran baru dan siap dibuatkan tagihan |

### 9.3 Sinkronisasi Data Siswa dari Data Induk

| Komponen | Detail |
|---|---|
| Aktor | Admin |
| Tujuan | Menarik dan menyinkronkan data siswa dari sistem Data Induk Yayasan |
| Precondition | Admin sudah login, koneksi ke API Data Induk tersedia |
| Alur Utama | Admin pilih instansi (MI/SMP sebagai source), klik tombol sinkronisasi, sistem mengambil data dari `https://datainduk.ypdhalmadani.sch.id/api/[source]/allSiswa`, sistem mencocokkan berdasarkan NISN/NIS, data yang sudah ada diperbarui, data baru ditambahkan |
| Alternate Flow | Admin melihat preview perubahan sebelum konfirmasi sinkronisasi |
| Edge Case | API tidak tersedia, data NISN/NIS duplikat, format data tidak sesuai |
| Output | Data siswa tersinkronisasi; laporan hasil sync ditampilkan (jumlah ditambah, diperbarui, gagal) |

### 9.4 Input Pembayaran Bulanan

| Komponen | Detail |
|---|---|
| Aktor | Admin Keuangan |
| Tujuan | Mencatat pembayaran bulanan siswa |
| Precondition | Data siswa dan periode tagihan sudah tersedia |
| Alur Utama | Admin cari siswa, pilih bulan, input nominal, pilih metode bayar, simpan |
| Alternate Flow | Pembayaran sebagian dicatat sebagai cicilan |
| Edge Case | Siswa SMP Kota Tangerang tidak memiliki tagihan bulanan |
| Output | Status pembayaran berubah menjadi lunas atau sebagian |

### 9.5 Input Pembayaran Kegiatan

| Komponen | Detail |
|---|---|
| Aktor | Admin Keuangan |
| Tujuan | Mencatat uang kegiatan seperti study tour, camping, LDK |
| Precondition | Kegiatan sudah dibuat |
| Alur Utama | Admin pilih kegiatan, cari siswa, input nominal bayar, simpan |
| Alternate Flow | Admin membuat tagihan kegiatan untuk banyak siswa sekaligus |
| Edge Case | Siswa tidak ikut kegiatan, pembayaran melebihi tagihan |
| Output | Status pembayaran kegiatan tercatat |

### 9.6 Lihat Tunggakan

| Komponen | Detail |
|---|---|
| Aktor | Admin, Kepala Sekolah, Yayasan |
| Tujuan | Melihat siswa yang belum membayar |
| Precondition | Tagihan sudah dibuat |
| Alur Utama | User pilih tahun ajaran, instansi, kelas, bulan, jenis tagihan |
| Alternate Flow | Export laporan ke Excel/PDF; filter tunggakan lintas tahun ajaran |
| Edge Case | Siswa bebas tagihan karena domisili Kota Tangerang; tunggakan dari tahun ajaran sebelumnya |
| Output | Daftar tunggakan tampil |

### 9.7 Cetak atau Unduh Kuitansi

| Komponen | Detail |
|---|---|
| Aktor | Admin Keuangan |
| Tujuan | Memberikan bukti pembayaran |
| Precondition | Pembayaran sudah disimpan |
| Alur Utama | Admin buka detail transaksi, klik cetak kuitansi |
| Alternate Flow | Kuitansi diunduh sebagai PDF |
| Edge Case | Transaksi dibatalkan |
| Output | Kuitansi pembayaran tersedia |

---

## 10. User Journey

### 10.1 Admin Keuangan

1. Login ke aplikasi.
2. Sistem auto-select tahun ajaran aktif.
3. Pilih instansi: MI atau SMP.
4. Sinkronisasi data siswa dari Data Induk (jika ada data baru).
5. Kelola data siswa dan penempatan kelas.
6. Buat atau cek tagihan bulanan.
7. Input pembayaran siswa.
8. Input pembayaran kegiatan.
9. Cek siswa yang belum bayar.
10. Cetak kuitansi.
11. Export laporan bulanan.

### 10.2 Kepala Sekolah

1. Login.
2. Pilih dashboard instansi masing-masing.
3. Lihat ringkasan pemasukan.
4. Lihat daftar tunggakan.
5. Unduh laporan jika diperlukan.

### 10.3 Yayasan

1. Login.
2. Lihat dashboard gabungan MI dan SMP.
3. Bandingkan pemasukan antar instansi.
4. Lihat laporan bulanan.
5. Export laporan untuk arsip.

---

## 11. Scope Produk

### 11.1 MVP

| Prioritas | Fitur |
|---|---|
| P0 | Login dan role pengguna |
| P0 | Manajemen instansi MI dan SMP |
| P0 | Manajemen tahun ajaran (sebagai scope utama data operasional) |
| P0 | Manajemen kelas (per tahun ajaran dan instansi) |
| P0 | Manajemen siswa |
| P0 | Sinkronisasi data siswa dari API Data Induk |
| P0 | Penempatan siswa ke kelas per tahun ajaran |
| P0 | Domisili siswa, khususnya Kota Tangerang dan luar Kota Tangerang |
| P0 | Tarif tagihan bulanan per tahun ajaran |
| P0 | Input pembayaran bulanan |
| P0 | Aturan gratis bulanan untuk siswa SMP domisili Kota Tangerang |
| P0 | Input pembayaran kegiatan |
| P0 | Status pembayaran: belum bayar, sebagian, lunas |
| P0 | Laporan pembayaran bulanan |
| P0 | Laporan tunggakan |
| P0 | Cetak kuitansi sederhana |
| P1 | Export Excel |
| P1 | Export PDF |
| P1 | Import data siswa dari Excel |
| P1 | Audit log transaksi |

### 11.2 V1

| Prioritas | Fitur |
|---|---|
| P1 | Dashboard grafik pemasukan |
| P1 | Kenaikan kelas siswa (promosi antar tahun ajaran) |
| P1 | Diskon atau keringanan manual |
| P1 | Pembatalan transaksi dengan alasan |
| P1 | Multi-admin |
| P1 | Laporan per kegiatan |
| P1 | Filter laporan berdasarkan kelas, bulan, instansi, dan jenis tagihan |
| P2 | Notifikasi WhatsApp manual |
| P2 | Template kuitansi custom |

### 11.3 Future Release

| Prioritas | Fitur |
|---|---|
| P2 | Payment gateway |
| P2 | Portal orang tua |
| P2 | Notifikasi WhatsApp otomatis |
| P2 | Integrasi rekening bank |
| P2 | Mobile app |
| P2 | Akuntansi dasar |
| P2 | Rekap pengeluaran sekolah |
| P2 | Laporan laba rugi yayasan |

---

## 12. Functional Requirements

| ID | Fitur | Deskripsi | User Story | Priority | Acceptance Criteria |
|---|---|---|---|---|---|
| FR-001 | Login | Pengguna masuk ke sistem menggunakan akun | Sebagai user, saya ingin login agar hanya pengguna berwenang yang bisa mengakses data | P0 | User dapat login dengan email/username dan password; user gagal login jika kredensial salah |
| FR-002 | Role Pengguna | Sistem mendukung role berbeda | Sebagai yayasan, saya ingin membatasi akses user agar data aman | P0 | Role minimal: Super Admin, Admin Keuangan, Kepala Sekolah, Yayasan |
| FR-003 | Manajemen Instansi | Sistem memiliki MI dan SMP | Sebagai admin, saya ingin memisahkan instansi agar data tidak tercampur | P0 | Admin dapat melihat dan memilih MI/SMP |
| FR-004 | Manajemen Kelas | Admin dapat membuat kelas per instansi dan tahun ajaran | Sebagai admin, saya ingin mengatur kelas per tahun ajaran agar siswa terorganisir | P0 | Kelas dapat dibuat, diedit, dinonaktifkan; kelas terikat pada tahun ajaran dan instansi |
| FR-005 | Manajemen Siswa | Admin dapat menambah data siswa | Sebagai admin, saya ingin menyimpan data siswa agar pembayaran bisa dicatat | P0 | Data minimal: NIS, nama, instansi, domisili, status aktif |
| FR-005a | Penempatan Siswa | Admin dapat menempatkan siswa ke kelas per tahun ajaran | Sebagai admin, saya ingin menempatkan siswa ke kelas setiap tahun ajaran agar data kelas selalu akurat | P0 | Siswa dapat ditempatkan ke kelas berbeda tiap tahun ajaran; satu siswa hanya di satu kelas per tahun ajaran |
| FR-005b | Manajemen Tahun Ajaran | Admin dapat membuat dan mengaktifkan tahun ajaran | Sebagai admin, saya ingin mengelola tahun ajaran agar data operasional terpisah per periode | P0 | Tahun ajaran memiliki nama (misal 2024/2025), status aktif/tidak aktif; hanya satu tahun ajaran aktif pada satu waktu; tahun ajaran menjadi scope untuk kelas, penempatan siswa, tagihan, dan pembayaran |
| FR-006 | Domisili Siswa | Sistem menyimpan domisili siswa | Sebagai admin, saya ingin menandai domisili agar aturan biaya SMP bisa otomatis | P0 | Domisili dapat dipilih: Kota Tangerang atau Luar Kota Tangerang |
| FR-007 | Tagihan Bulanan MI | Sistem membuat tagihan bulanan untuk siswa MI per tahun ajaran | Sebagai admin MI, saya ingin mencatat bayaran bulanan siswa MI | P0 | Semua siswa MI aktif pada tahun ajaran berjalan bisa memiliki tagihan bulanan |
| FR-008 | Tagihan Bulanan SMP | Sistem membuat tagihan hanya untuk siswa SMP luar Kota Tangerang per tahun ajaran | Sebagai admin SMP, saya ingin sistem otomatis mengecualikan siswa Kota Tangerang | P0 | Siswa SMP Kota Tangerang tidak muncul sebagai wajib bayar bulanan |
| FR-008a | Tarif Tagihan | Admin dapat mengatur nominal tarif tagihan bulanan per tahun ajaran dan instansi | Sebagai admin, saya ingin mengatur tarif per tahun ajaran agar perubahan tarif tidak mempengaruhi data tahun sebelumnya | P0 | Tarif tersimpan per tahun ajaran; perubahan tarif tidak mengubah tagihan yang sudah dibuat |
| FR-009 | Input Pembayaran Bulanan | Admin mencatat pembayaran per siswa dan bulan | Sebagai admin, saya ingin input pembayaran agar status siswa terupdate | P0 | Pembayaran bisa dicatat per bulan, nominal, metode bayar, tanggal |
| FR-010 | Pembayaran Sebagian | Sistem mendukung pembayaran cicilan | Sebagai admin, saya ingin mencatat pembayaran sebagian agar pembayaran bertahap bisa dilacak | P0 | Status menjadi sebagian jika nominal dibayar kurang dari tagihan |
| FR-011 | Status Lunas | Sistem menandai lunas otomatis | Sebagai admin, saya ingin sistem menandai lunas agar tidak perlu hitung manual | P0 | Status lunas jika total bayar sama atau lebih dari nominal tagihan |
| FR-012 | Kegiatan Tambahan | Admin dapat membuat jenis kegiatan per tahun ajaran | Sebagai admin SMP, saya ingin membuat tagihan kegiatan seperti LDK atau camping | P0 | Kegiatan memiliki nama, nominal, instansi, tahun ajaran, target siswa, tanggal |
| FR-013 | Pembayaran Kegiatan | Admin mencatat pembayaran kegiatan | Sebagai admin, saya ingin input pembayaran kegiatan agar pemasukan non-bulanan tercatat | P0 | Pembayaran kegiatan tercatat per siswa dan per kegiatan |
| FR-014 | Target Tagihan Kegiatan | Kegiatan dapat ditujukan ke siswa tertentu | Sebagai admin, saya ingin memilih peserta kegiatan agar tagihan tidak salah sasaran | P1 | Admin dapat memilih semua siswa, per kelas, atau siswa tertentu |
| FR-015 | Laporan Pembayaran | Sistem menampilkan laporan pembayaran | Sebagai kepala sekolah, saya ingin melihat laporan agar tahu pemasukan sekolah | P0 | Laporan bisa difilter per tahun ajaran, tanggal, bulan, kelas, instansi, jenis tagihan |
| FR-016 | Laporan Tunggakan | Sistem menampilkan siswa belum bayar | Sebagai admin, saya ingin melihat tunggakan agar mudah follow up | P0 | Daftar tunggakan menampilkan nama siswa, kelas, periode, nominal |
| FR-017 | Kuitansi | Sistem menghasilkan bukti pembayaran | Sebagai admin, saya ingin mencetak kuitansi agar siswa punya bukti bayar | P0 | Kuitansi menampilkan nomor transaksi, nama siswa, nominal, tanggal, petugas |
| FR-018 | Export Excel | Admin dapat export laporan | Sebagai yayasan, saya ingin export data agar mudah diarsipkan | P1 | Laporan dapat diunduh dalam format Excel |
| FR-019 | Export PDF | Admin dapat export laporan PDF | Sebagai kepala sekolah, saya ingin laporan PDF agar mudah dibagikan | P1 | Laporan dapat diunduh dalam format PDF |
| FR-020 | Import Siswa | Admin dapat import data siswa | Sebagai admin, saya ingin upload Excel agar input data awal lebih cepat | P1 | Sistem membaca template Excel dan menolak data invalid |
| FR-020a | Sinkronisasi Siswa dari Data Induk | Sistem dapat menarik data siswa dari API Data Induk Yayasan | Sebagai admin, saya ingin sinkronisasi data siswa dari sistem data induk agar data selalu up-to-date tanpa input manual | P0 | Data diambil dari `https://datainduk.ypdhalmadani.sch.id/api/[source]/allSiswa`; siswa yang sudah ada (berdasarkan NISN/NIS) akan diperbarui; siswa baru akan ditambahkan; proses sync tidak menghapus siswa yang sudah ada di sistem |
| FR-021 | Audit Log | Sistem mencatat perubahan penting | Sebagai yayasan, saya ingin melihat jejak perubahan agar transaksi bisa diaudit | P1 | Setiap input, edit, batal transaksi tercatat dengan user dan waktu |
| FR-022 | Pembatalan Transaksi | Admin dapat membatalkan transaksi dengan alasan | Sebagai admin, saya ingin membatalkan transaksi salah input tanpa menghapus data | P1 | Transaksi berubah status menjadi batal dan alasan wajib diisi |

---

## 13. Non-Functional Requirements

### 13.1 Performance

- Halaman utama terbuka maksimal 3 detik untuk data normal.
- Pencarian siswa maksimal 2 detik.
- Export laporan maksimal 10 detik untuk data satu tahun ajaran.

### 13.2 Security

- Password harus dienkripsi.
- Setiap user memiliki role dan permission.
- Admin MI tidak boleh mengubah data SMP jika tidak diberi izin.
- Transaksi tidak boleh dihapus permanen.
- Session otomatis logout setelah periode tidak aktif.

### 13.3 Reliability

- Data pembayaran tidak boleh hilang setelah disimpan.
- Sistem harus mencegah transaksi ganda pada bulan dan siswa yang sama, kecuali sebagai cicilan.
- Sistem harus memiliki backup database berkala.

### 13.4 Scalability

Sistem harus mendukung minimal:

- 2 instansi.
- 1.000 siswa aktif.
- 10 tahun ajaran.
- 100.000 transaksi pembayaran.

### 13.5 Accessibility

- Tampilan mudah dibaca.
- Tombol utama jelas.
- Form input tidak terlalu panjang.
- Error message harus spesifik.

### 13.6 Maintainability

- Kode harus modular.
- Aturan biaya harus bisa diubah tanpa mengubah kode besar.
- Jenis tagihan harus dapat ditambah dari admin panel.

---

## 14. UX dan UI Requirements

### 14.1 Prinsip Desain

- Sederhana dan familiar untuk admin sekolah.
- Fokus pada input cepat.
- Minim klik untuk mencatat pembayaran.
- Warna status pembayaran harus jelas.
- Data penting mudah difilter dan dicari.

### 14.2 Dashboard

Menampilkan:

- Total pemasukan bulan ini.
- Total pembayaran MI.
- Total pembayaran SMP.
- Jumlah siswa belum bayar.
- Jumlah transaksi hari ini.
- Ringkasan pembayaran kegiatan.

### 14.3 Data Siswa

Menampilkan:

- Nama siswa.
- NIS.
- Instansi.
- Kelas.
- Domisili.
- Status aktif.
- Status pembayaran bulan berjalan.

### 14.4 Input Pembayaran

Form berisi:

- Cari siswa.
- Pilih jenis tagihan.
- Pilih bulan atau kegiatan.
- Nominal tagihan.
- Nominal dibayar.
- Metode pembayaran.
- Tanggal pembayaran.
- Catatan.
- Tombol simpan dan cetak kuitansi.

### 14.5 Laporan

Filter:

- Tahun ajaran.
- Instansi.
- Kelas.
- Bulan.
- Jenis tagihan.
- Status bayar.
- Tanggal transaksi.

---

## 15. Information Architecture

```text
Dashboard
├── Ringkasan Yayasan
├── Ringkasan MI
├── Ringkasan SMP
└── Grafik Pemasukan

Data Master
├── Instansi
│   ├── MI
│   └── SMP
├── Tahun Ajaran (scope utama)
│   ├── Tahun Ajaran Aktif (auto-select di UI)
│   └── Arsip Tahun Ajaran Sebelumnya
├── Kelas (per tahun ajaran dan instansi)
├── Siswa
│   ├── Penempatan Siswa (per tahun ajaran)
│   └── Sinkronisasi dari API Data Induk
└── Jenis Tagihan

Tagihan
├── Tarif Tagihan (per tahun ajaran dan instansi)
├── Tagihan Bulanan (per tahun ajaran)
├── Tagihan Kegiatan (per tahun ajaran)
└── Keringanan / Gratis

Pembayaran
├── Input Pembayaran Bulanan
├── Input Pembayaran Kegiatan
├── Riwayat Transaksi
└── Cetak Kuitansi

Laporan
├── Laporan Harian
├── Laporan Bulanan
├── Laporan Per Siswa
├── Laporan Per Kelas
├── Laporan Per Instansi
├── Laporan Kegiatan
└── Laporan Tunggakan

Pengaturan
├── User dan Role
├── Nominal Tagihan (per tahun ajaran)
├── Template Kuitansi
├── Backup Data
└── Audit Log

<!--  -->
