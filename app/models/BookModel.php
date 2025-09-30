<?php
class BookModel {
    protected $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function searchByTitleOrAuthor($keyword) {
        $keyword = "%" . $this->db->real_escape_string($keyword) . "%";
        $sql = "SELECT id, Judul, Penulis, Harga, Tanggal_Masuk FROM buku WHERE Judul LIKE ? OR Penulis LIKE ? ORDER BY Tanggal_Masuk DESC";
        if ($stmt = $this->db->prepare($sql)) {
            $stmt->bind_param('ss', $keyword, $keyword);
            $stmt->execute();
            $result = $stmt->get_result();
            $rows = [];
            while ($r = $result->fetch_assoc()) {
                $rows[] = $r;
            }
            $stmt->close();
            return $rows;
        }
        // fallback
        $res = $this->db->query("SELECT id, Judul, Penulis, Harga, Tanggal_Masuk FROM buku WHERE Judul LIKE '" . $keyword . "' OR Penulis LIKE '" . $keyword . "'");
        $rows = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) { $rows[] = $r; }
        }
        return $rows;
    }
}
