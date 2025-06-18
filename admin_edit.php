<?php
session_start();
require 'db.php';

// Pastikan yang mengakses adalah admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$error = '';
$message = '';

// Ambil ID perkara dari query string
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: admin.php');
    exit();
}

// Ambil data perkara sesuai ID
$stmt = $conn->prepare("SELECT * FROM perkara WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Location: admin.php');
    exit();
}

$perkara = $result->fetch_assoc();

// Proses update data setelah submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        // Cek jika nomor_perkara yang baru sama dengan nomor perkara lain (unik)
        $stmt_check = $conn->prepare("SELECT id FROM perkara WHERE nomor_perkara = ? AND id <> ?");
        $stmt_check->bind_param("si", $nomor_perkara, $id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $error = 'Nomor Perkara sudah digunakan oleh data lain.';
        } else {
            // Update data perkara
            $stmt_update = $conn->prepare("UPDATE perkara SET nomor_perkara = ?, penggugat = ?, tergugat = ?, jenis_perkara = ?, status = ?, tanggal_daftar = ?, keterangan = ? WHERE id = ?");
            $stmt_update->bind_param("sssssssi", $nomor_perkara, $penggugat, $tergugat, $jenis_perkara, $status, $tanggal_daftar, $keterangan, $id);
            if ($stmt_update->execute()) {
                $message = 'Data perkara berhasil diperbarui.';
                // Refresh data setelah update untuk ditampilkan di form
                $stmt = $conn->prepare("SELECT * FROM perkara WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $perkara = $result->fetch_assoc();
            } else {
                $error = 'Gagal memperbarui data perkara.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id" >
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Perkara - Admin PA Simalungun</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <style>
    body {
      margin: 0; padding: 20px;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: #f1f1f1;
      min-height: 100vh;
      display: flex;
      justify-content: center;
    }
    .container {
      background: rgba(255 255 255 / 0.1);
      border-radius: 12px;
      padding: 2rem 2.5rem;
      box-shadow: 0 8px 32px rgb(0 0 0 / 0.3);
      width: 100%;
      max-width: 600px;
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255 255 255 / 0.2);
    }
    h1 {
      margin-top: 0;
      margin-bottom: 1.5rem;
      text-align: center;
      font-weight: 700;
      font-size: 1.8rem;
      background: linear-gradient(90deg, #a18cd1, #fbc2eb);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .message {
      color: #a0f0a0;
      font-weight: 700;
      margin-bottom: 1rem;
      text-align: center;
    }
    .error {
      color: #ff6b6b;
      font-weight: 700;
      margin-bottom: 1rem;
      text-align: center;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    label {
      font-weight: 600;
      font-size: 1rem;
    }
    input[type="text"],
    input[type="date"],
    textarea {
      padding: 10px 14px;
      font-size: 1rem;
      border-radius: 8px;
      border: none;
      background: rgba(255 255 255 / 0.15);
      color: #f1f1f1;
      resize: vertical;
      outline: none;
      transition: background-color 0.3s ease;
    }
    input[type="text"]:focus,
    input[type="date"]:focus,
    textarea:focus {
      background: rgba(255 255 255 / 0.3);
    }
    textarea {
      min-height: 80px;
    }
    button {
      background: #8e2de2;
      border: none;
      color: white;
      padding: 12px 0;
      border-radius: 30px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover,
    button:focus {
      background: #4a00e0;
    }
    .back-link {
      display: inline-flex;
      align-items: center;
      margin-top: 1rem;
      color: #fbc2eb;
      text-decoration: none;
      font-weight: 600;
      font-size: 1rem;
      gap: 6px;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <main class="container" role="main">
    <h1>Edit Perkara</h1>

    <?php if ($message): ?>
      <div class="message" role="alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="error" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="nomor_perkara">Nomor Perkara </label>
      <input type="text" id="nomor_perkara" name="nomor_perkara" required value="<?php echo htmlspecialchars($perkara['nomor_perkara']); ?>" />

      <label for="penggugat">Penggugat </label>
      <input type="text" id="penggugat" name="penggugat" required value="<?php echo htmlspecialchars($perkara['penggugat']); ?>" />

      <label for="tergugat">Tergugat </label>
      <input type="text" id="tergugat" name="tergugat" required value="<?php echo htmlspecialchars($perkara['tergugat']); ?>" />

      <label for="jenis_perkara">Jenis Perkara </label>
      <input type="text" id="jenis_perkara" name="jenis_perkara" required value="<?php echo htmlspecialchars($perkara['jenis_perkara']); ?>" />

      <label for="status">Status </label>
      <input type="text" id="status" name="status" required value="<?php echo htmlspecialchars($perkara['status']); ?>" />

      <label for="tanggal_daftar">Tanggal Daftar </label>
      <input type="date" id="tanggal_daftar" name="tanggal_daftar" required value="<?php echo htmlspecialchars($perkara['tanggal_daftar']); ?>" />

      <label for="keterangan">Keterangan</label>
      <textarea id="keterangan" name="keterangan"><?php echo htmlspecialchars($perkara['keterangan']); ?></textarea>

      <button type="submit">Simpan Perubahan</button>
    </form>

    <a href="admin.php" class="back-link" aria-label="Kembali ke panel admin">
      <span class="material-icons" aria-hidden="true">arrow_back</span> Kembali ke Admin
    </a>
  </main>
</body>
</html>
