# PANDUAN HANDOFF PENGEMBANGAN: MODUL PENANDAAN AREA OPERASI (SIMRS KHANZA JAVA)

> **Dokumen Handoff Teknis**: Dibuat khusus untuk pengembang Java SIMRS Khanza.  
> **Status**: *Ready for Development* (Tanpa perlu mengubah skema database yang sudah ada).  
> **Kompatibilitas**: Laravel 9 e-Dokter (Web/Mobile) & SIMRS Khanza Desktop (Java 8/11 Swing / NetBeans / MySQL / JasperReports).

---

## DAFTAR ISI
1. [Latar Belakang & Tujuan](#1-latar-belakang--tujuan)
2. [Arsitektur & Alur Kerja Data](#2-arsitektur--alur-kerja-data)
3. [Skema Database & Format Data JSON Base64](#3-skema-database--format-data-json-base64)
4. [Aset Template Gambar Anatomi](#4-aset-template-gambar-anatomi)
5. [Desain Antarmuka Java Swing (NetBeans Form)](#5-desain-antarmuka-java-swing-netbeans-form)
6. [Implementasi Kode Java (Blueprint Lengkap)](#6-implementasi-kode-java-blueprint-lengkap)
   - 6.1 `ModelPenandaanAreaOperasi.java`
   - 6.2 `ImageBase64Converter.java`
   - 6.3 `CanvasPenandaanPanel.java` (Graphics2D Pin & Coretan)
   - 6.4 `PenandaanAreaOperasiDAO.java`
   - 6.5 `WebappsDocUploader.java`
   - 6.6 `DlgPenandaanAreaOperasi.java`
7. [Desain Laporan JasperReports (.jrxml)](#7-desain-laporan-jasperreports-jrxml)
8. [Integrasi Titik Masuk Menu SIMRS Khanza](#8-integrasi-titik-masuk-menu-simrs-khanza)
9. [Checklist Pengujian & Kriteria Penerimaan (QA Acceptance)](#9-checklist-pengujian--kriteria-penerimaan-qa-acceptance)
10. [Langkah-Langkah Pengerjaan Pengembang](#10-langkah-langkah-pengerjaan-pengembang)

---

## 1. Latar Belakang & Tujuan

Modul **Penandaan Area Operasi** (*Surgical Site Marking*) adalah standar keselamatan pasien pra-bedah untuk mendokumentasikan lokasi tindakan operasi pada diagram anatomi tubuh secara visual.

Modul ini telah berjalan di aplikasi web/mobile **Laravel e-Dokter**. Dokumen handoff ini merinci implementasi modul yang sama pada aplikasi desktop **Java SIMRS Khanza** dengan prinsip:
1. **100% Shared Schema & Two-Way Sync**: Data yang disimpan di Java dapat dibuka/diedit di Laravel e-Dokter, dan sebaliknya.
2. **Interactive Drawing & Numbered Pins**: Dokter dapat menambahkan pin merah bernomor (1, 2, 3...) dan coretan spidol bebas pada 14 tampilan diagram anatomi tubuh.
3. **Template Otomatis Berdasarkan Jenis Kelamin**: Memuat diagram pria / wanita otomatis dari `pasien.jk`.
4. **Auto-Upload PDF & Berkas Digital**: Menghasilkan cetakan A4 JasperReports dengan QR Code validasi TTE dan otomatis mengarsipkannya ke `berkas_digital_perawatan` (kode `A05`).

---

## 2. Arsitektur & Alur Kerja Data

```
+-----------------------------------------------------------------------------------------+
|                              SIMRS KHANZA JAVA DESKTOP                                  |
|                                                                                         |
|  [Menu Ralan / Ranap / IBS]                                                             |
|           |                                                                             |
|           v                                                                             |
|  [DlgPenandaanAreaOperasi] <---> [CanvasPenandaanPanel] (Graphics2D Pin & Freehand)     |
|           |                                                                             |
|           v                                                                             |
|  [PenandaanAreaOperasiDAO]                                                              |
+-----------|-----------------------------------------------------------------------------+
            | (JDBC Query / Insert / Update)
            v
+-----------------------------------------------------------------------------------------+
|                                    MYSQL DATABASE                                       |
|                                                                                         |
|  1. penandaan_area_operasi (Tabel Utama, JSON Base64 14 View)                           |
|  2. reg_periksa, pasien, dokter (Relasi Pasien & Operator)                              |
|  3. berkas_digital_perawatan (Kode Berkas 'A05')                                        |
+-----------|-----------------------------------------------------------------------------+
            |                                         ^
            | (Sinkronisasi Dua Arah)                 | (Pencatatan Berkas)
            v                                         |
+------------------------------------+   +------------------------------------------------+
|          LARAVEL E-DOKTER          |   |                 WEBAPPS SERVER                 |
|  Livewire Canvas & Browser Print   |   |  webapps/upload-berkas-edokter.php             |
|  (Membaca & Menyimpan JSON Base64) |   |  (Penyimpanan File PDF di pages/upload/)       |
+------------------------------------+   +------------------------------------------------+
```

---

## 3. Skema Database & Format Data JSON Base64

### 3.1 DDL Tabel: `penandaan_area_operasi`

Tabel MySQL berikut telah tersedia di database SIMRS:

```sql
CREATE TABLE IF NOT EXISTS `penandaan_area_operasi` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `no_rawat` VARCHAR(17) NOT NULL,
  `no_rm` VARCHAR(15) NOT NULL,
  `nama_pasien` VARCHAR(100) NOT NULL,
  `tgl_lahir` DATE DEFAULT NULL,
  `prosedur_operasi` TEXT NOT NULL,
  `operator_id` VARCHAR(20) DEFAULT NULL,
  `nama_operator` VARCHAR(100) NOT NULL,
  `tanggal_penandaan` DATETIME NOT NULL,
  `gambar_penandaan_base64` LONGTEXT NOT NULL,
  `format_gambar` VARCHAR(20) DEFAULT 'image/png',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `penandaan_area_operasi_no_rawat_unique` (`no_rawat`),
  INDEX `penandaan_area_operasi_no_rm_index` (`no_rm`),
  INDEX `penandaan_area_operasi_operator_id_index` (`operator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

### 3.2 Struktur JSON Kolom `gambar_penandaan_base64`

Nilai kolom `gambar_penandaan_base64` adalah string JSON yang memetakan **14 kunci anatomi standar** ke string **Data URI PNG Base64**:

| No | Kunci JSON (*View Key*) | Keterangan Sudut Pandang Anatomi |
|:---|:---|:---|
| 1 | `body_front` | Tubuh tampak depan |
| 2 | `body_back` | Tubuh tampak belakang |
| 3 | `head_front` | Kepala tampak depan |
| 4 | `head_back` | Kepala tampak belakang |
| 5 | `head_left_profile` | Kepala tampak samping kiri |
| 6 | `head_right_profile` | Kepala tampak samping kanan |
| 7 | `hand_left_palm` | Telapak tangan kiri (*palmar*) |
| 8 | `hand_right_palm` | Telapak tangan kanan (*palmar*) |
| 9 | `hand_left_dorsal` | Punggung tangan kiri (*dorsal*) |
| 10 | `hand_right_dorsal` | Punggung tangan kanan (*dorsal*) |
| 11 | `foot_left_plantar` | Telapak kaki kiri (*plantar*) |
| 12 | `foot_right_plantar` | Telapak kaki kanan (*plantar*) |
| 13 | `foot_left_dorsal` | Punggung kaki kiri (*dorsal*) |
| 14 | `foot_right_dorsal` | Punggung kaki kanan (*dorsal*) |

Contoh isi payload JSON:
```json
{
  "body_front": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
  "body_back": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
  "head_front": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
  "hand_left_palm": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
  "foot_left_plantar": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA..."
}
```

---

## 4. Aset Template Gambar Anatomi

Letakkan file gambar template anatomi transparan pada folder resource project Java NetBeans:
`src/resource/diagrams/penandaan/`

Struktur folder:
```
src/
└── resource/
    └── diagrams/
        └── penandaan/
            ├── female/
            │   ├── body_front.png
            │   ├── body_back.png
            │   ├── head_front.png
            │   ├── head_back.png
            │   ├── head_left_profile.png
            │   ├── head_right_profile.png
            │   ├── hand_left_palm.png
            │   ├── hand_right_palm.png
            │   ├── hand_left_dorsal.png
            │   ├── hand_right_dorsal.png
            │   ├── foot_left_plantar.png
            │   ├── foot_right_plantar.png
            │   ├── foot_left_dorsal.png
            │   └── foot_right_dorsal.png
            └── male/
                └── (14 file dengan nama yang sama persis untuk anatomi pria)
```

---

## 5. Desain Antarmuka Java Swing (NetBeans Form)

Form diimplementasikan menggunakan `JDialog` modal bawaan Khanza: `DlgPenandaanAreaOperasi.java`.

### 5.1 Sketsa Layout Antarmuka

```text
+-------------------------------------------------------------------------------------------------------+
|  [X] PENANDAAN AREA OPERASI (SURGICAL SITE MARKING)                                                  |
+-------------------------------------------------------------------------------------------------------+
| No. Rawat : [ 2026/08/20/000001 ] [Cari]  Tgl Lahir : [ 1985-05-12 (41 Th) ]  JK : [ PEREMPUAN ]     |
| No. R.M.  : [ 012345           ]          Pasien    : [ SITI AMINAH                  ]               |
| Operator  : [ D001 ] [ dr. Budi Santoso, Sp.B                    ] [ ... ]  Tgl Simpan: 20/08/2026    |
| Prosedur  : [ Appendectomy Laparoskopi cito                                              ]            |
+-------------------------------------------------------------------------------------------------------+
| TAB VIEW DIAGRAM ANATOMI:                                                                             |
| [ 1. Tubuh Utuh (2 Diagram) ] [ 2. Kepala (4) ] [ 3. Tangan (4) ] [ 4. Kaki (4) ]                     |
+-------------------------------------------------------------------------------------------------------+
| TOOLBAR KANVAS:                                                                                       |
| Mode: (o) Pin Merah Bernomor   ( ) Coretan Spidol (Freehand)   [Warna: Merah v]  [Tebal Garis: [ 3 ]] |
| Aksi: [ Undo Aksi Terakhir ]  [ Bersihkan Kanvas Ini ]                                                |
+-------------------------------------------------------------------------------------------------------+
| AREA KANVAS (Grid Panel):                                                                             |
|                                                                                                       |
|    +------------------------------+     +------------------------------+                              |
|    |      TUBUH TAMPAK DEPAN      |     |     TUBUH TAMPAK BELAKANG    |                              |
|    |                              |     |                              |                              |
|    |         ( ( 1 ) )            |     |                              |                              |
|    |           /|\                |     |              /|\             |                              |
|    |          / | \               |     |             / | \            |                              |
|    |         /  |  \              |     |            /  |  \           |                              |
|    |            |                 |     |               |              |                              |
|    |           / \                |     |              / \             |                              |
|    |          /   \               |     |             /   \            |                              |
|    +------------------------------+     +------------------------------+                              |
+-------------------------------------------------------------------------------------------------------+
| STATUS: [ Data berhasil dimuat dari database. ]                                                       |
| TOMBOL: [ Simpan & Arsipkan A05 ]  [ Cetak Jasper ]  [ Hapus ]  [ Bersihkan Semua ]  [ Keluar ]       |
+-------------------------------------------------------------------------------------------------------+
```

---

## 6. Implementasi Kode Java (Blueprint Lengkap)

### 6.1 `ModelPenandaanAreaOperasi.java`

Simpan di paket: `rekammedis`

```java
package rekammedis;

import java.util.Date;

public class ModelPenandaanAreaOperasi {
    private Long id;
    private String noRawat;
    private String noRm;
    private String namaPasien;
    private Date tglLahir;
    private String prosedurOperasi;
    private String operatorId;
    private String namaOperator;
    private Date tanggalPenandaan;
    private String gambarJsonString;
    private String formatGambar = "image/png";

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }

    public String getNoRawat() { return noRawat; }
    public void setNoRawat(String noRawat) { this.noRawat = noRawat; }

    public String getNoRm() { return noRm; }
    public void setNoRm(String noRm) { this.noRm = noRm; }

    public String getNamaPasien() { return namaPasien; }
    public void setNamaPasien(String namaPasien) { this.namaPasien = namaPasien; }

    public Date getTglLahir() { return tglLahir; }
    public void setTglLahir(Date tglLahir) { this.tglLahir = tglLahir; }

    public String getProsedurOperasi() { return prosedurOperasi; }
    public void setProsedurOperasi(String prosedurOperasi) { this.prosedurOperasi = prosedurOperasi; }

    public String getOperatorId() { return operatorId; }
    public void setOperatorId(String operatorId) { this.operatorId = operatorId; }

    public String getNamaOperator() { return namaOperator; }
    public void setNamaOperator(String namaOperator) { this.namaOperator = namaOperator; }

    public Date getTanggalPenandaan() { return tanggalPenandaan; }
    public void setTanggalPenandaan(Date tanggalPenandaan) { this.tanggalPenandaan = tanggalPenandaan; }

    public String getGambarJsonString() { return gambarJsonString; }
    public void setGambarJsonString(String gambarJsonString) { this.gambarJsonString = gambarJsonString; }

    public String getFormatGambar() { return formatGambar; }
    public void setFormatGambar(String formatGambar) { this.formatGambar = formatGambar; }
}
```

---

### 6.2 `ImageBase64Converter.java`

Simpan di paket: `rekammedis.util`

```java
package rekammedis.util;

import java.awt.image.BufferedImage;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.util.Base64;
import javax.imageio.ImageIO;

public class ImageBase64Converter {

    private static final String DATA_URL_PREFIX = "data:image/png;base64,";

    public static String encodeToBase64(BufferedImage image) throws IOException {
        if (image == null) return null;
        ByteArrayOutputStream baos = new ByteArrayOutputStream();
        ImageIO.write(image, "png", baos);
        byte[] bytes = baos.toByteArray();
        return DATA_URL_PREFIX + Base64.getEncoder().encodeToString(bytes);
    }

    public static BufferedImage decodeFromBase64(String dataUri) throws IOException {
        if (dataUri == null || dataUri.trim().isEmpty()) {
            return null;
        }
        String base64Data = dataUri;
        if (base64Data.contains(",")) {
            base64Data = base64Data.substring(base64Data.indexOf(",") + 1);
        }
        byte[] bytes = Base64.getDecoder().decode(base64Data);
        ByteArrayInputStream bais = new ByteArrayInputStream(bytes);
        return ImageIO.read(bais);
    }
}
```

---

### 6.3 `CanvasPenandaanPanel.java`

Simpan di paket: `rekammedis`

```java
package rekammedis;

import java.awt.*;
import java.awt.event.*;
import java.awt.image.BufferedImage;
import java.util.ArrayList;
import java.util.List;
import javax.swing.JPanel;

public class CanvasPenandaanPanel extends JPanel {

    public enum ToolMode { PIN_NUMBERED, FREEHAND_DRAW }

    private BufferedImage baseTemplateImage;
    private ToolMode currentMode = ToolMode.PIN_NUMBERED;
    private Color drawColor = Color.RED;
    private int strokeWidth = 3;

    private final List<PinMarker> pinList = new ArrayList<>();
    private final List<DrawingPath> pathList = new ArrayList<>();
    private DrawingPath currentPath = null;

    public static class PinMarker {
        public int x, y;
        public int number;
        public Color color;

        public PinMarker(int x, int y, int number, Color color) {
            this.x = x;
            this.y = y;
            this.number = number;
            this.color = color;
        }
    }

    public static class DrawingPath {
        public List<Point> points = new ArrayList<>();
        public Color color;
        public int strokeWidth;

        public DrawingPath(Color color, int strokeWidth) {
            this.color = color;
            this.strokeWidth = strokeWidth;
        }
    }

    public CanvasPenandaanPanel() {
        setPreferredSize(new Dimension(280, 360));
        setBackground(Color.WHITE);

        MouseAdapter adapter = new MouseAdapter() {
            @Override
            public void mousePressed(MouseEvent e) {
                if (currentMode == ToolMode.PIN_NUMBERED) {
                    addPin(e.getX(), e.getY(), drawColor);
                } else if (currentMode == ToolMode.FREEHAND_DRAW) {
                    currentPath = new DrawingPath(drawColor, strokeWidth);
                    currentPath.points.add(e.getPoint());
                    pathList.add(currentPath);
                    repaint();
                }
            }

            @Override
            public void mouseDragged(MouseEvent e) {
                if (currentMode == ToolMode.FREEHAND_DRAW && currentPath != null) {
                    currentPath.points.add(e.getPoint());
                    repaint();
                }
            }

            @Override
            public void mouseReleased(MouseEvent e) {
                currentPath = null;
            }
        };

        addMouseListener(adapter);
        addMouseMotionListener(adapter);
    }

    public void setTemplateImage(BufferedImage img) {
        this.baseTemplateImage = img;
        this.pinList.clear();
        this.pathList.clear();
        repaint();
    }

    public void setToolMode(ToolMode mode) {
        this.currentMode = mode;
    }

    public void setDrawColor(Color color) {
        this.drawColor = color;
    }

    public void setStrokeWidth(int width) {
        this.strokeWidth = width;
    }

    public void addPin(int x, int y, Color color) {
        int nextNum = pinList.size() + 1;
        pinList.add(new PinMarker(x, y, nextNum, color));
        repaint();
    }

    public int getPinCount() {
        return pinList.size();
    }

    public void undoLastAction() {
        if (currentMode == ToolMode.PIN_NUMBERED && !pinList.isEmpty()) {
            pinList.remove(pinList.size() - 1);
            repaint();
        } else if (currentMode == ToolMode.FREEHAND_DRAW && !pathList.isEmpty()) {
            pathList.remove(pathList.size() - 1);
            repaint();
        }
    }

    public void clearAll() {
        pinList.clear();
        pathList.clear();
        repaint();
    }

    @Override
    protected void paintComponent(Graphics g) {
        super.paintComponent(g);
        Graphics2D g2 = (Graphics2D) g.create();
        g2.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);
        g2.setRenderingHint(RenderingHints.KEY_TEXT_ANTIALIASING, RenderingHints.VALUE_TEXT_ANTIALIAS_ON);

        // 1. Gambar Template Anatomi
        if (baseTemplateImage != null) {
            g2.drawImage(baseTemplateImage, 0, 0, getWidth(), getHeight(), null);
        }

        // 2. Gambar Coretan Bebas
        for (DrawingPath path : pathList) {
            if (path.points.size() < 2) continue;
            g2.setColor(path.color);
            g2.setStroke(new BasicStroke(path.strokeWidth, BasicStroke.CAP_ROUND, BasicStroke.JOIN_ROUND));
            for (int i = 0; i < path.points.size() - 1; i++) {
                Point p1 = path.points.get(i);
                Point p2 = path.points.get(i + 1);
                g2.drawLine(p1.x, p1.y, p2.x, p2.y);
            }
        }

        // 3. Gambar Pin Merah Bernomor
        int pinRadius = 11;
        for (PinMarker pin : pinList) {
            // Lingkaran luar pin
            g2.setColor(pin.color);
            g2.fillOval(pin.x - pinRadius, pin.y - pinRadius, pinRadius * 2, pinRadius * 2);
            
            // Garis tepi putih pin
            g2.setColor(Color.WHITE);
            g2.setStroke(new BasicStroke(1.5f));
            g2.drawOval(pin.x - pinRadius, pin.y - pinRadius, pinRadius * 2, pinRadius * 2);

            // Label angka di tengah pin
            g2.setFont(new Font("SansSerif", Font.BOLD, 11));
            FontMetrics fm = g2.getFontMetrics();
            String label = String.valueOf(pin.number);
            int tx = pin.x - (fm.stringWidth(label) / 2);
            int ty = pin.y + (fm.getAscent() / 2) - 2;
            g2.drawString(label, tx, ty);
        }

        g2.dispose();
    }

    public BufferedImage exportToImage() {
        int w = getWidth() > 0 ? getWidth() : 280;
        int h = getHeight() > 0 ? getHeight() : 360;
        BufferedImage output = new BufferedImage(w, h, BufferedImage.TYPE_INT_ARGB);
        Graphics2D g2 = output.createGraphics();
        paintComponent(g2);
        g2.dispose();
        return output;
    }
}
```

---

### 6.4 `PenandaanAreaOperasiDAO.java`

Simpan di paket: `rekammedis`

```java
package rekammedis;

import fungsi.koneksiDB;
import java.sql.*;

public class PenandaanAreaOperasiDAO {

    private final Connection conn;

    public PenandaanAreaOperasiDAO() {
        this.conn = koneksiDB.condb();
    }

    public PenandaanAreaOperasiDAO(Connection conn) {
        this.conn = conn;
    }

    public boolean simpanOrUpdate(ModelPenandaanAreaOperasi model) throws SQLException {
        String sql = "INSERT INTO penandaan_area_operasi ("
                   + "no_rawat, no_rm, nama_pasien, tgl_lahir, prosedur_operasi, "
                   + "operator_id, nama_operator, tanggal_penandaan, gambar_penandaan_base64, format_gambar"
                   + ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'image/png') "
                   + "ON DUPLICATE KEY UPDATE "
                   + "prosedur_operasi = VALUES(prosedur_operasi), "
                   + "operator_id = VALUES(operator_id), "
                   + "nama_operator = VALUES(nama_operator), "
                   + "tanggal_penandaan = VALUES(tanggal_penandaan), "
                   + "gambar_penandaan_base64 = VALUES(gambar_penandaan_base64), "
                   + "updated_at = NOW()";

        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setString(1, model.getNoRawat());
            ps.setString(2, model.getNoRm());
            ps.setString(3, model.getNamaPasien());
            if (model.getTglLahir() != null) {
                ps.setDate(4, new java.sql.Date(model.getTglLahir().getTime()));
            } else {
                ps.setNull(4, Types.DATE);
            }
            ps.setString(5, model.getProsedurOperasi());
            ps.setString(6, model.getOperatorId());
            ps.setString(7, model.getNamaOperator());
            ps.setTimestamp(8, new Timestamp(System.currentTimeMillis()));
            ps.setString(9, model.getGambarJsonString());

            return ps.executeUpdate() > 0;
        }
    }

    public ModelPenandaanAreaOperasi getByNoRawat(String noRawat) throws SQLException {
        String sql = "SELECT * FROM penandaan_area_operasi WHERE no_rawat = ?";
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setString(1, noRawat);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    ModelPenandaanAreaOperasi m = new ModelPenandaanAreaOperasi();
                    m.setId(rs.getLong("id"));
                    m.setNoRawat(rs.getString("no_rawat"));
                    m.setNoRm(rs.getString("no_rm"));
                    m.setNamaPasien(rs.getString("nama_pasien"));
                    m.setTglLahir(rs.getDate("tgl_lahir"));
                    m.setProsedurOperasi(rs.getString("prosedur_operasi"));
                    m.setOperatorId(rs.getString("operator_id"));
                    m.setNamaOperator(rs.getString("nama_operator"));
                    m.setTanggalPenandaan(rs.getTimestamp("tanggal_penandaan"));
                    m.setGambarJsonString(rs.getString("gambar_penandaan_base64"));
                    return m;
                }
            }
        }
        return null;
    }

    public boolean hapus(String noRawat) throws SQLException {
        String sql = "DELETE FROM penandaan_area_operasi WHERE no_rawat = ?";
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setString(1, noRawat);
            return ps.executeUpdate() > 0;
        }
    }
}
```

---

### 6.5 `WebappsDocUploader.java`

Simpan di paket: `rekammedis.util`

```java
package rekammedis.util;

import fungsi.koneksiDB;
import java.io.*;
import java.net.HttpURLConnection;
import java.net.URL;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.util.Base64;
import org.json.JSONObject;

public class WebappsDocUploader {

    private static final String UPLOAD_URL = "https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php";

    public static boolean uploadPdfAndSync(String noRawat, byte[] pdfBytes) throws Exception {
        String sanitizedNoRawat = noRawat.replace("/", "-");
        String prefix = "PENANDAAN_OPERASI_" + sanitizedNoRawat + "_";
        String namaFile = prefix + System.currentTimeMillis();
        String pdfBase64 = Base64.getEncoder().encodeToString(pdfBytes);

        JSONObject payload = new JSONObject();
        payload.put("file", pdfBase64);
        payload.put("no_rawat", noRawat);
        payload.put("kd_berkas", "A05");
        payload.put("nama_file", namaFile);

        URL url = new URL(UPLOAD_URL);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setRequestMethod("POST");
        conn.setRequestProperty("Content-Type", "application/json; charset=UTF-8");
        conn.setDoOutput(true);
        conn.setConnectTimeout(15000);
        conn.setReadTimeout(30000);

        try (OutputStream os = conn.getOutputStream()) {
            os.write(payload.toString().getBytes("UTF-8"));
            os.flush();
        }

        int responseCode = conn.getResponseCode();
        if (responseCode == HttpURLConnection.HTTP_OK) {
            BufferedReader br = new BufferedReader(new InputStreamReader(conn.getInputStream()));
            StringBuilder sb = new StringBuilder();
            String line;
            while ((line = br.readLine()) != null) {
                sb.append(line);
            }
            JSONObject resJson = new JSONObject(sb.toString());
            boolean success = resJson.optBoolean("success", false);

            if (success) {
                // Bersihkan record lama di berkas_digital_perawatan untuk no_rawat ini agar tidak duplikat
                String newLocation = "pages/upload/" + namaFile + ".pdf";
                Connection db = koneksiDB.condb();
                String cleanSql = "DELETE FROM berkas_digital_perawatan WHERE no_rawat = ? AND kode = 'A05' AND lokasi_file LIKE ? AND lokasi_file <> ?";
                try (PreparedStatement ps = db.prepareStatement(cleanSql)) {
                    ps.setString(1, noRawat);
                    ps.setString(2, "%" + prefix + "%");
                    ps.setString(3, newLocation);
                    ps.executeUpdate();
                }
            }
            return success;
        }
        return false;
    }
}
```

---

## 7. Desain Laporan JasperReports (.jrxml)

Buat file laporan: `report/rptPenandaanAreaOperasi.jrxml`  
Ukuran Kertas: **A4 Portrait** (Lebar: 595 pt, Tinggi: 842 pt, Margin: 20 pt).

### 7.1 Parameter Laporan
- `no_rawat` (String)
- `no_rm` (String)
- `nama_pasien` (String)
- `tgl_lahir_umur` (String)
- `jk` (String)
- `prosedur_operasi` (String)
- `nama_operator` (String)
- `tanggal_penandaan` (String)
- `qr_code_image` (`java.awt.Image` / `java.io.InputStream`)
- `img_body_front`, `img_body_back`, `img_head_front`, `img_head_back`, `img_head_left_profile`, `img_head_right_profile`, `img_hand_left_palm`, `img_hand_right_palm`, `img_hand_left_dorsal`, `img_hand_right_dorsal`, `img_foot_left_plantar`, `img_foot_right_plantar`, `img_foot_left_dorsal`, `img_foot_right_dorsal` (`java.awt.Image`)

---

## 8. Integrasi Titik Masuk Menu SIMRS Khanza

Tambahkan menu `Penandaan Area Operasi` pada 3 lokasi strategis di aplikasi Java Khanza:

### 8.1 Menu Registrasi / Rawat Jalan (`simrskhanza.DlgReg`)
Pada popup menu tabel registrasi (`jPopupMenu1`):
```java
MnPenandaanAreaOperasi = new javax.swing.JMenuItem();
MnPenandaanAreaOperasi.setText("Penandaan Area Operasi");
MnPenandaanAreaOperasi.addActionListener(evt -> {
    if (tbPetugas.getSelectedRow() != -1) {
        DlgPenandaanAreaOperasi form = new DlgPenandaanAreaOperasi(null, false);
        form.setPasien(
            tbPetugas.getValueAt(tbPetugas.getSelectedRow(), 0).toString(), // no_rawat
            tbPetugas.getValueAt(tbPetugas.getSelectedRow(), 1).toString(), // no_rkm_medis
            tbPetugas.getValueAt(tbPetugas.getSelectedRow(), 2).toString(), // nm_pasien
            tbPetugas.getValueAt(tbPetugas.getSelectedRow(), 3).toString(), // jk
            tbPetugas.getValueAt(tbPetugas.getSelectedRow(), 4).toString(), // tgl_lahir
            akses.getkode() // kd_dokter aktif
        );
        form.setSize(1100, 720);
        form.setLocationRelativeTo(this);
        form.setVisible(true);
    }
});
```

### 8.2 Menu Kamar Inap / Rawat Inap (`simrskhanza.DlgKamarInap` & `DlgRawatInap`)
Pada menu popup Rekam Medis pasien rawat inap.

### 8.3 Menu Jadwal & Kamar Bedah / IBS (`simrskhanza.DlgOperasi`)
Pada tombol formulir pra-bedah / checklist keselamatan operasi.

---

## 9. Checklist Pengujian & Kriteria Penerimaan (QA Acceptance)

| No | Pengujian | Langkah Pengujian | Kriteria Lolos (Pass) |
|:---|:---|:---|:---|
| 1 | **Buka Pasien Baru** | Buka pasien dari menu Ralan yang belum memiliki data penandaan | Identitas terisi otomatis, template anatomi termuat sesuai JK (L/P), kanvas bersih tanpa pin. |
| 2 | **Interaksi Pin Bernomor** | Klik pada beberapa titik diagram `Tubuh Depan` | Muncul lingkaran merah berisi angka 1, 2, 3... urut secara visual. |
| 3 | **Interaksi Spidol Bebas** | Pilih mode *Freehand*, drag kursor di atas diagram | Muncul garis spidol merah mulus di atas gambar tanpa menghapus pin. |
| 4 | **Undo & Clear** | Tekan tombol *Undo* lalu *Clear* | Titik pin terakhir atau coretan terhapus; *Clear* mengembalikan kanvas ke template awal. |
| 5 | **Validasi Form** | Kosongkan kolom `Prosedur Operasi` lalu klik Simpan | Muncul pesan validasi *"Prosedur operasi wajib diisi!"*, penyimpanan dibatalkan. |
| 6 | **Simpan ke Database** | Klik tombol *Simpan* | Data tersimpan di `penandaan_area_operasi`, payload JSON Base64 terbentuk lengkap. |
| 7 | **Sinkronisasi 2 Arah** | 1. Simpan di Java -> Buka di Laravel e-Dokter<br>2. Edit di e-Dokter -> Buka kembali di Java | Gambar dan pin termuat identik dan tepat di kedua platform. |
| 8 | **Cetak JasperReports** | Klik tombol *Cetak* di Java | JasperViewer menampilkan formulir A4 rapi lengkap dengan Kop RS, grid 14 gambar, dan QR Code TTE. |
| 9 | **Auto Upload Berkas A05** | Simpan formulir di Java | File PDF otomatis terunggah ke webapps dan tercatat di `berkas_digital_perawatan` (kode `A05`). |

---

## 10. Langkah-Langkah Pengerjaan Pengembang

1. **Import Aset**: Masukkan seluruh gambar 14 view anatomi ke `src/resource/diagrams/penandaan/` (`female/` & `male/`).
2. **Kompilasi Utility & Model**: Tambahkan `ModelPenandaanAreaOperasi.java`, `ImageBase64Converter.java`, dan `CanvasPenandaanPanel.java`.
3. **Kompilasi DAO**: Pasang `PenandaanAreaOperasiDAO.java` yang terhubung ke `koneksiDB.condb()`.
4. **Buat GUI Dialog**: Buka NetBeans, buat `DlgPenandaanAreaOperasi.form` dan atur tata letak `JTabbedPane`.
5. **Buat File Jasper**: Rancang `report/rptPenandaanAreaOperasi.jrxml` dan letakkan di direktori laporan Khanza.
6. **Pasang Uploader**: Implementasikan `WebappsDocUploader.java` pada aksi tombol Simpan.
7. **Pasang Entry Points**: Tambahkan menu pada `DlgReg.java`, `DlgKamarInap.java`, dan `DlgOperasi.java`.
8. **Jalankan Uji Coba**: Verifikasi menggunakan checklist di Bagian 9.
