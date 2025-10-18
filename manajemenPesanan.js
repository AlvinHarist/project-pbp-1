document.addEventListener("DOMContentLoaded", function () {
  const daftarPesanan = document.getElementById("daftar-pesanan");
  const tombolRentang = document.querySelectorAll(".rentang-btn");

  function loadPesanan(range = 'all') {
    fetch(`get_pesanan.php?range=${range}`)
      .then(response => response.json())
      .then(data => {
        daftarPesanan.innerHTML = ""; 

        if (data.length === 0) {
          daftarPesanan.innerHTML = "<p>Tidak ada pesanan.</p>";
          return;
        }

        // Buat tabel
        const table = document.createElement("table");
        table.classList.add("tabel-pesanan");

        table.innerHTML = `
          <thead>
            <tr>
              <th>ID Pesanan</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th>Total Harga</th>
              <th>ID User</th>
              <th>ID Buku</th>
            </tr>
          </thead>
          <tbody></tbody>
        `;

        const tbody = table.querySelector("tbody");

        data.forEach(pesanan => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${pesanan.id}</td>
            <td>${pesanan.Tanggal}</td>
            <td>
              <select class="status-select" data-id="${pesanan.id}">
                <option value="Pending" ${pesanan.status === "Pending" ? "selected" : ""}>Pending</option>
                <option value="Dikirim" ${pesanan.status === "Dikirim" ? "selected" : ""}>Dikirim</option>
                <option value="Dibayar" ${pesanan.status === "Dibayar" ? "selected" : ""}>Dibayar</option>
                <option value="Selesai" ${pesanan.status === "Selesai" ? "selected" : ""}>Selesai</option>
              </select>
            </td>
            <td>Rp ${Number(pesanan.Total_harga).toLocaleString('id-ID')}</td>
            <td>${pesanan.id_user}</td>
            <td>${pesanan.id_buku}</td>
          `;
          tbody.appendChild(row);
        });

        daftarPesanan.appendChild(table);
      })
      .catch(error => console.error("Gagal memuat pesanan:", error));
  }

  // 🔹 Default: tampilkan semua data
  loadPesanan();

  // 🔹 Filter tombol rentang
  tombolRentang.forEach(btn => {
    btn.addEventListener("click", () => {
      const range = btn.dataset.range;
      loadPesanan(range);
    });
  });

  // 🔹 Update status
  document.addEventListener("change", function (e) {
    if (e.target.classList.contains("status-select")) {
      const id = e.target.dataset.id;
      const status = e.target.value;

      fetch("updateStatus.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&status=${status}`
      })
      .then(res => res.text())
      .then(msg => alert(msg))
      .catch(err => console.error("Gagal update status:", err));
    }
  });
});