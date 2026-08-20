# Penandaan Area Operasi (Java SIMRS Khanza) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengembangkan modul Penandaan Area Operasi (*Surgical Site Marking*) pada aplikasi desktop Java SIMRS Khanza lengkap dengan kanvas interaktif (Pin bernomor & Freehand drawing), sinkronisasi skema JSON Base64 dua arah dengan Laravel e-Dokter, laporan JasperReports A4, dan auto-upload berkas digital perawatan (`A05`).

**Architecture:** Modul dibangun menggunakan Java Swing (`JDialog` & custom `JPanel` Graphics2D) yang berkomunikasi dengan MySQL melalui DAO JDBC Prepared Statements. Gambar dikonversi ke format JSON Base64 data-URL yang identik dengan Laravel e-Dokter. Ekspor laporan menggunakan JasperReports dan diunggah ke server webapps melalui HTTP POST multipart/JSON.

**Tech Stack:** Java 8 / 11, Swing / NetBeans GUI Builder, MySQL Connector/J, JasperReports 6.x, ZXing QR Code, `org.json` / `Gson`, Apache HttpClient / `HttpURLConnection`.

## Global Constraints
- Menggunakan skema tabel MySQL `penandaan_area_operasi` yang sama dengan Laravel e-Dokter.
- Struktur JSON `gambar_penandaan_base64` wajib memuat 14 kunci sudut pandang anatomi (`body_front`, `body_back`, `head_front`, dll) dengan format data-URL `data:image/png;base64,...`.
- Kode berkas digital pada `berkas_digital_perawatan` wajib menggunakan kode `A05`.
- Layout cetak JasperReports wajib berukuran A4 Portrait (210 x 297 mm) dengan QR Code verifikasi TTE.

---

### Task 1: Setup Diagram Assets & Utility Converter

**Files:**
- Create: `src/resource/diagrams/penandaan/female/*.png` (14 template diagram anatomi perempuan)
- Create: `src/resource/diagrams/penandaan/male/*.png` (14 template diagram anatomi laki-laki)
- Create: `src/rekammedis/util/ImageBase64Converter.java`
- Test: `test/rekammedis/util/ImageBase64ConverterTest.java`

**Interfaces:**
- Consumes: `java.awt.image.BufferedImage`, `byte[]`
- Produces: 
  - `ImageBase64Converter.encodeToBase64(BufferedImage img): String`
  - `ImageBase64Converter.decodeFromBase64(String dataUri): BufferedImage`

- [ ] **Step 1: Write Unit Test for Base64 Converter**

```java
package rekammedis.util;

import java.awt.*;
import java.awt.image.BufferedImage;
import org.junit.Test;
import static org.junit.Assert.*;

public class ImageBase64ConverterTest {

    @Test
    public void testEncodeAndDecodeRoundtrip() throws Exception {
        BufferedImage original = new BufferedImage(100, 100, BufferedImage.TYPE_INT_ARGB);
        Graphics2D g = original.createGraphics();
        g.setColor(Color.RED);
        g.fillRect(0, 0, 100, 100);
        g.dispose();

        String base64Uri = ImageBase64Converter.encodeToBase64(original);
        assertNotNull(base64Uri);
        assertTrue(base64Uri.startsWith("data:image/png;base64,"));

        BufferedImage decoded = ImageBase64Converter.decodeFromBase64(base64Uri);
        assertNotNull(decoded);
        assertEquals(100, decoded.getWidth());
        assertEquals(100, decoded.getHeight());
        assertEquals(Color.RED.getRGB(), decoded.getRGB(50, 50));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Expected: FAIL compilation with "Class ImageBase64Converter not found".

- [ ] **Step 3: Implement ImageBase64Converter**

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

- [ ] **Step 4: Run test to verify it passes**

Expected: PASS (All assertions match).

- [ ] **Step 5: Commit**

```bash
git add src/resource/diagrams/ src/rekammedis/util/ImageBase64Converter.java test/rekammedis/util/ImageBase64ConverterTest.java
git commit -m "feat(java): add diagram assets and ImageBase64Converter utility"
```

---

### Task 2: Interactive Swing Canvas Component (`CanvasPenandaanPanel`)

**Files:**
- Create: `src/rekammedis/CanvasPenandaanPanel.java`
- Test: `test/rekammedis/CanvasPenandaanPanelTest.java`

**Interfaces:**
- Consumes: Mouse events, template `BufferedImage`
- Produces: 
  - `setTemplateImage(BufferedImage img): void`
  - `setToolMode(ToolMode mode): void`
  - `undoLastAction(): void`
  - `clearAll(): void`
  - `exportToImage(): BufferedImage`

- [ ] **Step 1: Write Unit Test for Canvas Penandaan Component**

```java
package rekammedis;

import java.awt.Color;
import java.awt.image.BufferedImage;
import org.junit.Test;
import static org.junit.Assert.*;

public class CanvasPenandaanPanelTest {

    @Test
    public void testAddPinAndExport() {
        CanvasPenandaanPanel panel = new CanvasPenandaanPanel();
        panel.setSize(300, 400);

        BufferedImage bg = new BufferedImage(300, 400, BufferedImage.TYPE_INT_ARGB);
        panel.setTemplateImage(bg);

        panel.addPin(150, 200, Color.RED);
        assertEquals(1, panel.getPinCount());

        panel.undoLastAction();
        assertEquals(0, panel.getPinCount());

        panel.addPin(100, 100, Color.RED);
        BufferedImage exported = panel.exportToImage();
        assertNotNull(exported);
        assertEquals(300, exported.getWidth());
        assertEquals(400, exported.getHeight());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Expected: FAIL compilation with "Class CanvasPenandaanPanel not found".

- [ ] **Step 3: Implement CanvasPenandaanPanel with Graphics2D Rendering**

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
        setPreferredSize(new Dimension(300, 380));
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

        if (baseTemplateImage != null) {
            g2.drawImage(baseTemplateImage, 0, 0, getWidth(), getHeight(), null);
        }

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

        int pinRadius = 11;
        for (PinMarker pin : pinList) {
            g2.setColor(pin.color);
            g2.fillOval(pin.x - pinRadius, pin.y - pinRadius, pinRadius * 2, pinRadius * 2);
            
            g2.setColor(Color.WHITE);
            g2.setStroke(new BasicStroke(1.5f));
            g2.drawOval(pin.x - pinRadius, pin.y - pinRadius, pinRadius * 2, pinRadius * 2);

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
        BufferedImage output = new BufferedImage(getWidth() > 0 ? getWidth() : 300, getHeight() > 0 ? getHeight() : 380, BufferedImage.TYPE_INT_ARGB);
        Graphics2D g2 = output.createGraphics();
        paintComponent(g2);
        g2.dispose();
        return output;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add src/rekammedis/CanvasPenandaanPanel.java test/rekammedis/CanvasPenandaanPanelTest.java
git commit -m "feat(java): implement interactive CanvasPenandaanPanel component"
```

---

### Task 3: Model & DAO Layer (`PenandaanAreaOperasiDAO`)

**Files:**
- Create: `src/rekammedis/ModelPenandaanAreaOperasi.java`
- Create: `src/rekammedis/PenandaanAreaOperasiDAO.java`
- Test: `test/rekammedis/PenandaanAreaOperasiDAOTest.java`

**Interfaces:**
- Consumes: MySQL Connection, `ModelPenandaanAreaOperasi`
- Produces:
  - `simpanOrUpdate(ModelPenandaanAreaOperasi model): boolean`
  - `getByNoRawat(String noRawat): ModelPenandaanAreaOperasi`
  - `hapus(String noRawat): boolean`

- [ ] **Step 1: Write Unit/Mock Test for DAO CRUD**

```java
package rekammedis;

import org.junit.Test;
import static org.junit.Assert.*;

public class PenandaanAreaOperasiDAOTest {

    @Test
    public void testModelGetterAndSetter() {
        ModelPenandaanAreaOperasi m = new ModelPenandaanAreaOperasi();
        m.setNoRawat("2026/08/20/000001");
        m.setNoRm("012345");
        m.setNamaPasien("Siti Aminah");
        m.setProsedurOperasi("Appendectomy");
        m.setGambarJsonString("{\"body_front\":\"data:image/png;base64,...\"}");

        assertEquals("2026/08/20/000001", m.getNoRawat());
        assertEquals("012345", m.getNoRm());
        assertEquals("Siti Aminah", m.getNamaPasien());
        assertEquals("Appendectomy", m.getProsedurOperasi());
        assertTrue(m.getGambarJsonString().contains("body_front"));
    }
}
```

- [ ] **Step 2: Implement ModelPenandaanAreaOperasi POJO**

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

    // Getters and Setters
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

- [ ] **Step 3: Implement PenandaanAreaOperasiDAO**

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

- [ ] **Step 4: Run test to verify it passes**

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add src/rekammedis/ModelPenandaanAreaOperasi.java src/rekammedis/PenandaanAreaOperasiDAO.java test/rekammedis/PenandaanAreaOperasiDAOTest.java
git commit -m "feat(java): add Model and DAO layer for penandaan area operasi"
```

---

### Task 4: Main UI Form Dialog (`DlgPenandaanAreaOperasi`)

**Files:**
- Create: `src/rekammedis/DlgPenandaanAreaOperasi.java`
- Create: `src/rekammedis/DlgPenandaanAreaOperasi.form` (NetBeans GUI Form)

**Interfaces:**
- Consumes: `DlgReg`, `DlgKamarInap`, `DlgOperasi`
- Produces: Modal GUI Dialog with 14 Anatomy Tabs, Toolbar, Action Handlers for Save, Print, Clear, Delete.

- [ ] **Step 1: Design GUI Structure in NetBeans Form / Java Swing**
  - Header Panel: `TNoRawat`, `TNoRM`, `TPasien`, `TTglLahir`, `TUmur`, `TJk`, `KdDokter`, `TDokter`, `TProsedur`.
  - Center Panel: `JTabbedPane` dengan 4 Tab:
    1. Tab 1: `Tubuh Depan` & `Tubuh Belakang` (2 kanvas).
    2. Tab 2: `Kepala Depan`, `Kepala Belakang`, `Kepala Kiri`, `Kepala Kanan` (4 kanvas).
    3. Tab 3: `Tangan Kiri Palmar`, `Tangan Kanan Palmar`, `Tangan Kiri Dorsal`, `Tangan Kanan Dorsal` (4 kanvas).
    4. Tab 4: `Kaki Kiri Plantar`, `Kaki Kanan Plantar`, `Kaki Kiri Dorsal`, `Kaki Kaki Kanan Dorsal` (4 kanvas).
  - Toolbar Panel: RadioButton `Pin Merah`, RadioButton `Coretan Bebas`, Button `Undo`, Button `Clear Kanvas Ini`.
  - Footer Panel: Button `BtnSimpan`, Button `BtnCetak`, Button `BtnHapus`, Button `BtnKeluar`.

- [ ] **Step 2: Implement Load and Save Handlers in `DlgPenandaanAreaOperasi.java`**
  - Implement `setPasien(...)` to load patient demographics & auto-select male/female diagram templates.
  - Implement `muatDataTersimpan()` to parse JSON Base64 and load existing drawings into respective `CanvasPenandaanPanel`.
  - Implement `simpanData()` to export all 14 panels into Base64 PNGs, compose the JSON payload, execute `PenandaanAreaOperasiDAO.simpanOrUpdate()`, and trigger auto-upload.

- [ ] **Step 3: Commit**

```bash
git add src/rekammedis/DlgPenandaanAreaOperasi.java src/rekammedis/DlgPenandaanAreaOperasi.form
git commit -m "feat(java): implement DlgPenandaanAreaOperasi Swing dialog"
```

---

### Task 5: JasperReports Report Design (`rptPenandaanAreaOperasi.jrxml`)

**Files:**
- Create: `report/rptPenandaanAreaOperasi.jrxml`
- Create: `src/rekammedis/util/PenandaanReportHelper.java`

**Interfaces:**
- Consumes: `ModelPenandaanAreaOperasi`, Hospital Settings, Decoded Images
- Produces: Compiled `JasperPrint` object & PDF `byte[]` binary

- [ ] **Step 1: Create JRXML Layout in A4 Portrait**
  - KOP Instansi & Logo RS.
  - Parameter: `no_rawat`, `no_rkm_medis`, `nama_pasien`, `tgl_lahir_umur`, `jk`, `prosedur_operasi`, `nama_operator`, `tanggal_penandaan`, `qr_code_image`.
  - Grid 14 parameter `java.awt.Image`: `img_body_front`, `img_body_back`, `img_head_front`, dll.
  - TTE QR Code box at bottom right.

- [ ] **Step 2: Implement PenandaanReportHelper for Report Rendering**

```java
package rekammedis.util;

import java.awt.Image;
import java.io.InputStream;
import java.util.HashMap;
import java.util.Map;
import net.sf.jasperreports.engine.*;
import net.sf.jasperreports.engine.data.JRBeanCollectionDataSource;
import rekammedis.ModelPenandaanAreaOperasi;

public class PenandaanReportHelper {

    public static JasperPrint generateJasperPrint(ModelPenandaanAreaOperasi model, Map<String, Image> diagramImages, String qrCodeUrl) throws Exception {
        InputStream reportStream = PenandaanReportHelper.class.getResourceAsStream("/report/rptPenandaanAreaOperasi.jasper");
        if (reportStream == null) {
            reportStream = PenandaanReportHelper.class.getResourceAsStream("/report/rptPenandaanAreaOperasi.jrxml");
        }

        Map<String, Object> params = new HashMap<>();
        params.put("no_rawat", model.getNoRawat());
        params.put("no_rm", model.getNoRm());
        params.put("nama_pasien", model.getNamaPasien());
        params.put("prosedur_operasi", model.getProsedurOperasi());
        params.put("nama_operator", model.getNamaOperator());
        params.put("tanggal_penandaan", model.getTanggalPenandaan());

        // Put 14 decoded images
        for (Map.Entry<String, Image> entry : diagramImages.entrySet()) {
            params.put("img_" + entry.getKey(), entry.getValue());
        }

        return JasperFillManager.fillReport(reportStream, params, new JREmptyDataSource());
    }

    public static byte[] exportToPdfBytes(JasperPrint jasperPrint) throws JRException {
        return JasperExportManager.exportReportToPdf(jasperPrint);
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add report/rptPenandaanAreaOperasi.jrxml src/rekammedis/util/PenandaanReportHelper.java
git commit -m "feat(java): add JasperReports template and PDF compilation helper"
```

---

### Task 6: Webapps Doc Uploader & Berkas Digital Sync

**Files:**
- Create: `src/rekammedis/util/WebappsDocUploader.java`
- Modify: `src/rekammedis/DlgPenandaanAreaOperasi.java`

**Interfaces:**
- Consumes: `no_rawat`, PDF `byte[]`
- Produces: HTTP POST to `upload-berkas-edokter.php`, Database cleanup of old `A05` records.

- [ ] **Step 1: Implement WebappsDocUploader HTTP Client**

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
                // Clean up older records to prevent duplicate files in berkas_digital_perawatan
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

- [ ] **Step 2: Integrate Uploader trigger into Save Action in `DlgPenandaanAreaOperasi.java`**
- [ ] **Step 3: Commit**

```bash
git add src/rekammedis/util/WebappsDocUploader.java src/rekammedis/DlgPenandaanAreaOperasi.java
git commit -m "feat(java): implement auto-upload to webapps and berkas digital A05 sync"
```

---

### Task 7: Entry Points Integration in SIMRS Khanza Menus

**Files:**
- Modify: `src/simrskhanza/DlgReg.java` (Menu Ralan)
- Modify: `src/simrskhanza/DlgKamarInap.java` (Menu Ranap)
- Modify: `src/simrskhanza/DlgOperasi.java` (Menu IBS / Kamar Operasi)

**Interfaces:**
- Consumes: Table row selection from Khanza UI
- Produces: Opens modal `DlgPenandaanAreaOperasi` with pre-filled patient info.

- [ ] **Step 1: Add Popup Menu Item in `DlgReg.java`**
  - Add `MnPenandaanAreaOperasi` under `Rekam Medis` menu.
  - Attach ActionListener to open `DlgPenandaanAreaOperasi`.

- [ ] **Step 2: Add Popup Menu Item in `DlgKamarInap.java` / `DlgRawatInap.java`**
  - Add `MnPenandaanAreaOperasi` in inpatient context menu.

- [ ] **Step 3: Add Action Button in `DlgOperasi.java`**
  - Add button / menu item `Penandaan Area Operasi` in pre-operative documentation tab.

- [ ] **Step 4: Commit**

```bash
git add src/simrskhanza/DlgReg.java src/simrskhanza/DlgKamarInap.java src/simrskhanza/DlgOperasi.java
git commit -m "feat(java): integrate Penandaan Area Operasi entry points into Ralan, Ranap, and OK"
```

---

### Task 8: End-to-End Validation & Acceptance Testing

**Files:**
- Test: `test/rekammedis/PenandaanAreaOperasiE2ETest.java`

- [ ] **Step 1: Execute Unit Tests**
  - Run all tests for Base64 Converter, Canvas Panel, DAO, and Report Helper.
- [ ] **Step 2: Verify Two-Way Sync (Java <-> Laravel e-Dokter)**
  - Insert record via Java Swing -> Verify display in Laravel e-Dokter web/mobile view.
  - Update pin via Laravel e-Dokter -> Verify updated drawing loaded in Java Swing.
- [ ] **Step 3: Verify Berkas Digital A05 Archive**
  - Verify generated PDF uploaded to `pages/upload/` and accessible via SIMRS Khanza Berkas Digital dialog.
- [ ] **Step 4: Commit and finalize release**

```bash
git commit --allow-empty -m "chore(java): complete verification of penandaan area operasi module"
```
