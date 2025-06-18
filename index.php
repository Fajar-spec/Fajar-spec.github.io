<?php
require 'db.php';

$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT * FROM perkara WHERE nomor_perkara LIKE ? ORDER BY tanggal_daftar DESC");
    $like_search = "%" . $search . "%";
    $stmt->bind_param("s", $like_search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM perkara ORDER BY tanggal_daftar DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Informasi Perkara - Pengadilan Agama Simalungun</title>
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap" rel="stylesheet" />
<style>
  body {
    margin: 0; padding: 0;
    font-family: 'Merriweather', serif;
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
    letter-spacing: 1.2px;
    box-shadow: 0 4px 8px rgb(0 40 85 / 0.4);
  }
  main {
    max-width: 1000px;
    margin: 3rem auto 4rem;
    padding: 0 1.5rem;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgb(0 0 0 / 0.1);
  }
  h1 {
    font-weight: 700;
    color: #002855;
    border-bottom: 3px solid #f0c14b;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
    text-align: center;
  }
  form.search-form {
    margin-bottom: 1.5rem;
    text-align: center;
  }
  input[type="search"] {
    width: 300px;
    max-width: 90%;
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 8px 0 0 8px;
    font-size: 1rem;
    outline: none;
  }
  button.search-button {
    padding: 11px 20px;
    font-size: 1rem;
    font-weight: 700;
    background-color: #002855;
    border: none;
    color: #f0c14b;
    border-radius: 0 8px 8px 0;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  button.search-button:hover,
  button.search-button:focus {
    background-color: #004080;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  th, td {
    padding: 15px 12px;
    border: 1px solid #ccc;
    font-size: 0.95rem;
  }
  th {
    background-color: #e6e6e6;
    color: #002855;
    font-weight: 700;
  }
  tbody tr:nth-child(even) {
    background-color: #f9f9f9;
  }
  tbody tr:hover {
    background-color: #f0f0f5;
  }
  td {
    vertical-align: middle;
  }
  /* Responsive styles */
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
      background: white;
      box-shadow: 0 1px 5px rgba(0,0,0,0.1);
      border-radius: 6px;
      padding: 1rem;
    }
    tbody td {
      border: none;
      padding-left: 50%;
      position: relative;
      text-align: left;
      font-size: 0.9rem;
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
  footer {
    text-align: center;
    padding: 1rem;
    color: #555;
    font-size: 0.9rem;
    border-top: 1px solid #ddd;
    margin-top: auto;
  }
</style>
</head>
<body>
<header>
  Pengadilan Agama Simalungun - Informasi Perkara
</header>
<main>
  <h1>Daftar Perkara</h1>
  <form class="search-form" method="GET" action="">
    <input type="search" name="search" placeholder="Cari nomor perkara..." value="<?php echo htmlspecialchars($search); ?>" aria-label="Cari nomor perkara" />
    <button class="search-button" type="submit">Cari</button>
  </form>
  <?php if ($search !== ''): ?>
    <p>Hasil pencarian untuk: <strong><?php echo htmlspecialchars($search); ?></strong></p>
  <?php endif; ?>
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
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="7" style="text-align:center; font-style:italic;">Tidak ada data perkara ditemukan.</td></tr>
      <?php else: ?>
        <?php while ($row = $result->fetch_assoc()) : ?>
          <tr>
            <td data-label="Nomor Perkara"><?php echo htmlspecialchars($row['nomor_perkara']); ?></td>
            <td data-label="Penggugat"><?php echo htmlspecialchars($row['penggugat']); ?></td>
            <td data-label="Tergugat"><?php echo htmlspecialchars($row['tergugat']); ?></td>
            <td data-label="Jenis Perkara"><?php echo htmlspecialchars($row['jenis_perkara']); ?></td>
            <td data-label="Status"><?php echo htmlspecialchars($row['status']); ?></td>
            <td data-label="Tanggal Daftar"><?php echo htmlspecialchars($row['tanggal_daftar']); ?></td>
            <td data-label="Keterangan"><?php echo nl2br(htmlspecialchars($row['keterangan'])); ?></td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
    </tbody>
  </table>
</main>
<footer>
  &copy; <?php echo date('Y'); ?> Pengadilan Agama Simalungun - Akses Informasi Perkara Publik
</footer>
</body>
</html>
