<?php
// koneksi ke database
$host = "192.168.1.8";
$user = "admin"; // ganti kalo passwordnya di-set
$pass = "password123"; // isi kalau ada password
$db   = "db_siswa";

$conn = new mysqli($host, $user, $pass, $db);

// cek koneksi
if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

// ambil data
$sql = "SELECT * FROM siswa";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Siswa</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; background: #fafafa; }
    h1 { text-align: center; color: #333; }
    table { border-collapse: collapse; width: 80%; margin: 20px auto; background: #fff; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
    th, td { padding: 10px 15px; border: 1px solid #ccc; text-align: center; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>
  <h1>Daftar Nilai Siswa</h1>
  <table>
    <tr>
      <th>ID</th>
      <th>Nama Siswa</th>
      <th>Jurusan</th>
      <th>Angkatan</th>
      <th>Nilai Akhir</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row["id"]) ?></td>
          <td><?= htmlspecialchars($row["nama"]) ?></td>
          <td><?= htmlspecialchars($row["jurusan"]) ?></td>
          <td><?= htmlspecialchars($row["angkatan"]) ?></td>
          <td><?= htmlspecialchars($row["nilai_akhir"]) ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="5">Belum ada data siswa</td></tr>
    <?php endif; ?>
  </table>
</body>
</html>

<?php
$conn->close();
?>
