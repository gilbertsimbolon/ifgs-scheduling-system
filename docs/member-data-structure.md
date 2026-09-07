# Dokumentasi Arsitektur Struktur Data Member (IFGS)

Dokumen ini menjelaskan struktur data dan arsitektur backend untuk entitas **Member** pada Sistem Informasi Indo Fitness Gym Sport Tondano (IFGS).

---

## 1. Alasan Pemisahan User dan Member
Pemisahan antara tabel `users` dan `members` menerapkan prinsip pemisahan tanggung jawab (*Separation of Concerns*):
- **Tabel `users`**: Bertanggung jawab penuh terhadap aspek autentikasi sistem (kredensial login seperti `email` dan `password`), identitas global akun (`name`, `slug`), serta status keaktifan akun autentikasi (`status`).
- **Tabel `members`**: Bertanggung jawab menyimpan identitas operasional gym (seperti `member_code`) serta bertindak sebagai induk relasi untuk modul keanggotaan (*membership*), reservasi, dan kunjungan gym di masa mendatang.

Akun member ditentukan oleh penetapan role **`Member`** melalui **Spatie Laravel Permission**, sehingga tidak diperlukan kolom role atau sistem autentikasi kedua.

---

## 2. Relasi One-to-One (1 : 1)
Hubungan antara `User` dan `Member` dirancang sebagai relasi **One-to-One**:
- Model `User` memiliki relasi `hasOne(Member::class)`.
- Model `Member` memiliki relasi `belongsTo(User::class)`.
- Pada tingkat basis data, kolom `user_id` pada tabel `members` didefinisikan dengan index **`UNIQUE`** dan foreign key constraint `constrained('users')->cascadeOnDelete()`. Hal ini menjamin bahwa satu pengguna tidak dapat memiliki lebih dari satu profil member, dan jika akun pengguna dihapus (hard delete), data profil member terkait ikut terhapus secara bersih tanpa meninggalkan *orphaned record*.

---

## 3. Struktur Tabel `members`
Tabel `members` dibangun dengan skema minimalis dan efisien:
- `id` (bigint unsigned, auto-increment, primary key)
- `user_id` (bigint unsigned, foreign key referencing `users.id`, unique index)
- `member_code` (varchar(255), unique index, non-nullable)
- `created_at` & `updated_at` (timestamps)

---

## 4. Mekanisme Member Code (`member_code`)
- **Format**: `IFGS-YYYYMM-XXXX` (contoh: `IFGS-202609-0001`).
- **Generasi Otomatis**: Kode dibuat secara otomatis pada lifecycle hook Eloquent (`booted()` -> `static::creating(...)`) jika saat pembuatan record `member_code` tidak diisi secara eksplisit.
- **Pencegahan Kolisi / Duplikasi**: Generator mencari urutan sequence terakhir pada bulan berjalan, lalu melakukan verifikasi eksistensi (`where('member_code', $code)->exists()`) untuk memastikan kode benar-benar unik.

---

## 5. Alasan Tidak Menambahkan Data Personal Berlebihan
Sesuai arahan dan kebutuhan skripsi IFGS:
- Sistem menghindari *over-engineering* dan asumsi berlebih.
- Field personal seperti NIK, nomor KTP, alamat lengkap, tanggal lahir, jenis kelamin, pekerjaan, foto, nomor WhatsApp, dan kontak darurat **sengaja tidak ditambahkan** pada commit ini karena belum menjadi kebutuhan operasional yang terverifikasi.
- Kredensial dasar (`name`, `email`) sudah ditangani oleh tabel `users`.

---

## 6. Perbedaan Account Status vs. Membership Status
- **`users.status`** (`Active` / `Inactive`): Menunjukkan status operasional akun autentikasi pengguna untuk dapat login ke sistem. Penonaktifan member sehari-hari dilakukan melalui status akun ini tanpa menghapus data secara destruktif.
- **Membership Status**: Menunjukkan status paket keanggotaan (misal: aktif, habis masa berlaku, ditangguhkan). Status ini merupakan domain modul *Membership* yang akan dibangun pada commit berikutnya dan sengaja **tidak** dimasukkan ke dalam tabel `members`.
