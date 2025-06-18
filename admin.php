<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt_del = $conn->prepare("DELETE FROM perkara WHERE id = ?");
    $stmt_del->bind_param("i", $delete_id);
    if ($stmt_del->execute()) {
        $message = 'Perkara berhasil dihapus.';
    } else {
        $error = 'Gagal menghapus perkara.';
    }
    header("Location: admin.php?msg=" . urlencode($message));
    exit();
}

if (isset($_POST['add_perkara'])) {
    $nomor_perkara = trim($_POST['nomor_perkara']);
    $penggugat = trim($_POST['penggugat']);
    $tergugat = trim($_POST['tergugat']);
    $jenis_perkara = trim($_POST['jenis_perkara']);
    $status = trim($_POST['status']);
    $tanggal_daftar = $_POST['tanggal_daftar'];
    $keterangan = trim($_POST['keterangan']);

    if ($nomor_perkara === '' || $penggugat === '' || $tergugat === '' || $jenis_perkara === '' || $status === '' || $tanggal_daftar === '') {
        $error = 'Semua field wajib diisi kecuali keterangan.';
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM perkara WHERE nomor_perkara = ?");
        $stmt_check->bind_param("s", $nomor_perkara);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $error = 'Nomor Perkara sudah terdaftar.';
        } else {
            $stmt = $conn->prepare("INSERT INTO perkara (nomor_perkara, penggugat, tergugat, jenis_perkara, status, tanggal_daftar, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $nomor_perkara, $penggugat, $tergugat, $jenis_perkara, $status, $tanggal_daftar, $keterangan);
            if ($stmt->execute()) {
                $message = 'Perkara berhasil ditambahkan.';
            } else {
                $error = 'Gagal menambahkan perkara.';
            }
        }
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

$result = $conn->query("SELECT * FROM perkara ORDER BY tanggal_daftar DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Panel Admin - Pengadilan Agama Simalungun</title>
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap" rel="stylesheet" />
<style>
  body {
    font-family: 'Merriweather', serif;
    margin: 0;
    background-color: #f5f5f7;
    color: #1a1a1a;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }
  header {
    background-color: #002855;
    color: #f0c14b;
    padding: 1.5rem 2rem;
    text-align: center;
    font-weight: 700;
    font-size: 1.8rem;
    box-shadow: 0 4px 8px rgb(0 40 85 / 0.4);
  }
  main {
    max-width: 1100px;
    margin: 3rem auto 4rem;
    padding: 2rem 2rem 3rem;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgb(0 0 0 / 0.1);
    color: #1a1a1a;
  }
  h1 {
    font-weight: 700;
    font-size: 2.2rem;
    border-bottom: 3px solid #f0c14b;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
    color: #002855;
    text-align: center;
  }
  .logout {
    position: fixed;
    top: 1rem;
    right: 1rem;
    background: #f0c14b;
    color: #002855;
    font-weight: 700;
    padding: 0.5rem 1rem;
    border-radius: 24px;
    text-decoration: none;
    box-shadow: 0 2px 6px rgb(0 0 0 / 0.15);
    transition: background-color 0.3s ease;
  }
  .logout:hover,
  .logout:focus {
    background: #d4ab18;
    color: #000;
  }
  form {
    margin-bottom: 2rem;
  }
  form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 700;
    color: #002855;
  }
  form input[type="text"],
  form input[type="date"],
  form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-family: 'Merriweather', serif;
    font-size: 1rem;
    color: #1a1a1a;
  }
  form textarea {
    resize: vertical;
    min-height: 80px;
  }
  form button {
    background-color: #002855;
    color: #f0c14b;
    font-weight: 700;
    padding: 12px 0;
    border: none;
    border-radius: 30px;
    font-size: 1.1rem;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.3s ease;
  }
  form button:hover,
  form button:focus {
    background-color: #004080;
  }
  .message {
    color: green;
    font-weight: 700;
    margin-bottom: 1rem;
    text-align: center;
  }
  .error {
    color: #cc0000;
    font-weight: 700;
    margin-bottom: 1rem;
    text-align: center;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  th,
  td {
    padding: 12px;
    border: 1px solid #ccc;
    text-align: left;
    font-size: 1rem;
  }
  th {
    background-color: #e6e6e6;
    color: #002855;
  }
  tbody tr:nth-child(even) {
    background-color: #f9f9f9;
  }
  tbody tr:hover {
    background-color: #f0f0f5;
  }
  .action-links a {
    margin-right: 10px;
    text-decoration: none;
    color: #0066cc;
    font-weight: 700;
  }
  .action-links a:hover,
  .action-links a:focus {
    text-decoration: underline;
  }
  @media (max-width: 768px) {
    main {
      margin: 2rem 1rem 3rem;
      padding: 1rem;
    }
    table, thead, tbody, th, td, tr {
      display: block;
    }
    thead tr {
      position: absolute;
      top: -9999px;
      left: -9999px;
    }
    tbody tr {
      margin-bottom: 1.5rem;
      background: #fff;
      box-shadow: 0 1px 5px rgba(0,0,0,0.1);
      padding: 1rem;
      border-radius: 8px;
    }
    tbody td {
      border: none;
      padding-left: 50%;
      position: relative;
      text-align: left;
      font-size: 0.95rem;
    }
    tbody td::before {
      content: attr(data-label);
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      font-weight: 700;
      color: #002855;
      white-space: nowrap;
    }
  }
</style>
</head>
<body>
<a href="admin_profile.php" class="logout" aria-label="Kembali ke profil admin">Profil Admin</a>
<a href="logout.php" class="logout" aria-label="Logout">Logout</a>
<main role="main">
<h1>Panel Admin - Kelola Perkara</h1>

<?php if($message): ?>
  <p class="message" role="alert"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<?php if($error): ?>
  <p class="error" role="alert"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="POST" action="" aria-label="Form tambah perkara baru">
  <label for="nomor_perkara">Nomor Perkara </label>
  <input id="nomor_perkara" name="nomor_perkara" type="text" required />
  <label for="penggugat">Penggugat </label>
  <input id="penggugat" name="penggugat" type="text" required />
  <label for="tergugat">Tergugat </label>
  <input id="tergugat" name="tergugat" type="text" required />
  <label for="jenis_perkara">Jenis Perkara </label>
  <input id="jenis_perkara" name="jenis_perkara" type="text" required />
  <label for="status">Status </label>
  <input id="status" name="status" type="text" required />
  <label for="tanggal_daftar">Tanggal Daftar </label>
  <input id="tanggal_daftar" name="tanggal_daftar" type="date" required />
  <label for="keterangan">Keterangan</label>
  <textarea id="keterangan" name="keterangan" rows="4"></textarea>
  <button type="submit" name="add_perkara">Tambah Perkara</button>
</form>

<table role="table" aria-label="Daftar perkara yang terdaftar">
  <thead>
    <tr>
      <th scope="col">Nomor Perkara</th>
      <th scope="col">Penggugat</th>
      <th scope="col">Tergugat</th>
      <th scope="col">Jenis Perkara</th>
      <th scope="col">Status</th>
      <th scope="col">Tanggal Daftar</th>
      <th scope="col">Keterangan</th>
      <th scope="col">Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = $result->fetch_assoc()) : ?>
      <tr>
        <td data-label="Nomor Perkara"><?php echo htmlspecialchars($row['nomor_perkara']); ?></td>
        <td data-label="Penggugat"><?php echo htmlspecialchars($row['penggugat']); ?></td>
        <td data-label="Tergugat"><?php echo htmlspecialchars($row['tergugat']); ?></td>
        <td data-label="Jenis Perkara"><?php echo htmlspecialchars($row['jenis_perkara']); ?></td>
        <td data-label="Status"><?php echo htmlspecialchars($row['status']); ?></td>
        <td data-label="Tanggal Daftar"><?php echo htmlspecialchars($row['tanggal_daftar']); ?></td>
        <td data-label="Keterangan"><?php echo nl2br(htmlspecialchars($row['keterangan'])); ?></td>
        <td data-label="Aksi" class="action-links">
          <a href="admin_edit.php?id=<?php echo $row['id']; ?>" aria-label="Edit perkara nomor <?php echo htmlspecialchars($row['nomor_perkara']); ?>">Edit</a>
          <a href="admin.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Yakin hapus perkara ini?')" aria-label="Hapus perkara nomor <?php echo htmlspecialchars($row['nomor_perkara']); ?>" style="color:#c00;">Hapus</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>
</main>
</body>
</html>
