# Dokumentasi Class Warna Soft Periwinkle Theme

## Daftar Isi
1. [Palet Warna](#palet-warna)
2. [CSS Variables](#css-variables)
3. [Class Utilities](#class-utilities)
4. [Gradient Utilities](#gradient-utilities)
5. [Contoh Penggunaan](#contoh-penggunaan)

---

## Palet Warna

### Warna Utama
Palet warna menggunakan gradasi dari soft pastel hingga vibrant:

| Nama Warna | Hex Code | HSL | Penggunaan |
|------------|----------|-----|------------|
| **Powder Petal** | `#efd9ce` | `hsla(20, 51%, 87%, 1)` | Background light, input groups |
| **Mauve** | `#dec0f1` | `hsla(277, 64%, 85%, 1)` | Hover states, alerts |
| **Wisteria** | `#b79ced` | `hsla(260, 69%, 77%, 1)` | Secondary elements, sidebar hover |
| **Soft Periwinkle** | `#957fef` | `hsla(252, 78%, 72%, 1)` | Primary variations, gradients |
| **Medium Slate Blue** | `#7161ef` | `hsla(247, 82%, 66%, 1)` | Primary color utama |

### Visualisasi Gradasi
```
Powder Petal → Mauve → Wisteria → Soft Periwinkle → Medium Slate Blue
   (Lightest)                                    (Darkest/Primary)
```

---

## CSS Variables

Semua warna tersedia sebagai CSS variables yang dapat digunakan di custom CSS:

```css
:root {
    /* Color Palette */
    --powder-petal: #efd9ceff;
    --mauve: #dec0f1ff;
    --wisteria: #b79cedff;
    --soft-periwinkle: #957fefff;
    --medium-slate-blue: #7161efff;
    
    /* Primary Color Variations */
    --primary: var(--medium-slate-blue);
    --primary-dark: var(--soft-periwinkle);
    --primary-light: var(--wisteria);
    --primary-lighter: var(--mauve);
    --primary-lightest: var(--powder-petal);
}
```

### Penggunaan CSS Variables
```css
.custom-element {
    background-color: var(--medium-slate-blue);
    border-color: var(--soft-periwinkle);
    color: var(--primary);
}
```

---

## Class Utilities

### Background Colors

#### Primary Background
```html
<!-- Solid Primary -->
<div class="bg-primary">Background Primary</div>

<!-- Gradient Primary -->
<div class="bg-gradient-primary">Gradient Primary</div>
<div class="bg-gradient-primary-vertical">Gradient Primary Vertical</div>
```

#### Text Colors
```html
<!-- Primary Text -->
<p class="text-primary">Text dengan warna primary</p>
<p class="text-primary-light">Text dengan warna primary light</p>
<p class="text-primary-lighter">Text dengan warna primary lighter</p>
```

### Buttons

#### Primary Button
```html
<!-- Standard Primary Button -->
<button class="btn btn-primary">Primary Button</button>

<!-- Primary dengan Gradient (otomatis) -->
<button class="btn btn-primary">Gradient Button</button>
```

**Fitur:**
- Background gradient otomatis: `Medium Slate Blue → Soft Periwinkle`
- Hover effect dengan gradient terbalik
- Smooth transition dan shadow effect

### Cards

#### Primary Card
```html
<!-- Card dengan Primary Header -->
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Card Title</h3>
    </div>
    <div class="card-body">
        Card content
    </div>
</div>

<!-- Card Outline -->
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Outline Card</h3>
    </div>
    <div class="card-body">
        Card content
    </div>
</div>
```

### Badges

```html
<!-- Primary Badge -->
<span class="badge badge-primary">Primary Badge</span>
<span class="badge bg-primary">Primary Badge (Bootstrap 5)</span>
```

### Alerts

```html
<!-- Primary Alert -->
<div class="alert alert-primary">
    <strong>Info!</strong> Ini adalah alert dengan warna primary.
</div>
```

**Styling:**
- Background: `Mauve`
- Border: `Wisteria`
- Text: `Medium Slate Blue`

### Tables

```html
<!-- Primary Table -->
<table class="table table-primary">
    <thead>
        <tr>
            <th>Header</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Data</td>
        </tr>
    </tbody>
</table>
```

### Forms

#### Form Controls dengan Primary Focus
```html
<!-- Input dengan Primary Focus -->
<input type="text" class="form-control" placeholder="Input field">

<!-- Select dengan Primary Focus -->
<select class="form-control">
    <option>Option 1</option>
</select>

<!-- Custom Checkbox -->
<div class="custom-control custom-checkbox">
    <input type="checkbox" class="custom-control-input" id="customCheck">
    <label class="custom-control-label" for="customCheck">Checkbox</label>
</div>

<!-- Custom Switch -->
<div class="custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="customSwitch">
    <label class="custom-control-label" for="customSwitch">Toggle Switch</label>
</div>
```

**Fitur Focus:**
- Border color: `Medium Slate Blue`
- Box shadow dengan opacity 25%

### Navigation

#### Nav Tabs
```html
<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link active" href="#">Active Tab</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#">Tab</a>
    </li>
</ul>
```

#### Nav Pills
```html
<ul class="nav nav-pills">
    <li class="nav-item">
        <a class="nav-link active" href="#">Active Pill</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#">Pill</a>
    </li>
</ul>
```

### Pagination

```html
<nav>
    <ul class="pagination">
        <li class="page-item active">
            <a class="page-link" href="#">1</a>
        </li>
        <li class="page-item">
            <a class="page-link" href="#">2</a>
        </li>
    </ul>
</nav>
```

### Dropdowns

```html
<div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
        Dropdown
    </button>
    <div class="dropdown-menu">
        <a class="dropdown-item active" href="#">Active Item</a>
        <a class="dropdown-item" href="#">Item</a>
    </div>
</div>
```

### Modals

```html
<!-- Primary Modal -->
<div class="modal modal-primary">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Modal Title</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                Modal content
            </div>
        </div>
    </div>
</div>
```

### Progress Bars

```html
<div class="progress">
    <div class="progress-bar bg-primary" style="width: 50%">50%</div>
</div>
```

### List Groups

```html
<ul class="list-group">
    <li class="list-group-item active">Active Item</li>
    <li class="list-group-item">Item</li>
</ul>
```

### Breadcrumbs

```html
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active">Current Page</li>
    </ol>
</nav>
```

### Toasts

```html
<div class="toast" role="alert">
    <div class="toast-header bg-primary text-white">
        <strong class="mr-auto">Toast Header</strong>
        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
            <span>&times;</span>
        </button>
    </div>
    <div class="toast-body">
        Toast message
    </div>
</div>
```

---

## Gradient Utilities

### Background Gradients

#### Horizontal Gradient (135deg)
```html
<div class="bg-gradient-primary">
    Gradient dari Medium Slate Blue ke Soft Periwinkle
</div>
```

#### Vertical Gradient (180deg)
```html
<div class="bg-gradient-primary-vertical">
    Gradient vertical dari Medium Slate Blue → Soft Periwinkle → Wisteria
</div>
```

### Custom Gradient dengan CSS Variables

```css
.custom-gradient {
    background: linear-gradient(135deg, 
        var(--medium-slate-blue) 0%, 
        var(--soft-periwinkle) 50%, 
        var(--wisteria) 100%);
}
```

---

## Shadow Utilities

### Primary Shadow
```html
<!-- Standard Shadow -->
<div class="shadow-primary">
    Element dengan shadow primary
</div>

<!-- Large Shadow -->
<div class="shadow-primary-lg">
    Element dengan shadow primary large
</div>
```

**Shadow Colors:**
- `shadow-primary`: `rgba(113, 97, 239, 0.15)`
- `shadow-primary-lg`: `rgba(113, 97, 239, 0.175)`

---

## Focus Ring Utilities

```html
<!-- Input dengan Primary Focus Ring -->
<input type="text" class="form-control focus-ring-primary" placeholder="Focus me">

<!-- Button dengan Primary Focus Ring -->
<button class="btn btn-secondary focus-ring-primary">Button</button>
```

**Focus Ring Color:** `rgba(113, 97, 239, 0.25)`

---

## AdminLTE Specific Components

### Sidebar Navigation

```html
<!-- Sidebar dengan Primary Theme -->
<aside class="main-sidebar sidebar-primary">
    <nav class="nav-sidebar">
        <ul class="nav nav-pills nav-sidebar flex-column">
            <li class="nav-item">
                <a href="#" class="nav-link active">
                    <i class="nav-icon fas fa-home"></i>
                    <p>Active Menu</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-user"></i>
                    <p>Menu Item</p>
                </a>
            </li>
        </ul>
    </nav>
</aside>
```

**Styling:**
- Background: Gradient `Medium Slate Blue → Soft Periwinkle`
- Active item: `Medium Slate Blue`
- Hover: `Wisteria`

### Info Box

```html
<!-- Primary Info Box -->
<div class="info-box bg-primary">
    <span class="info-box-icon"><i class="fas fa-user"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Total Users</span>
        <span class="info-box-number">1,234</span>
    </div>
</div>

<!-- Primary Gradient Info Box -->
<div class="info-box bg-primary-gradient">
    <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Growth</span>
        <span class="info-box-number">+15%</span>
    </div>
</div>
```

### Small Box

```html
<div class="small-box bg-primary">
    <div class="inner">
        <h3>150</h3>
        <p>New Orders</p>
    </div>
    <div class="icon">
        <i class="fas fa-shopping-cart"></i>
    </div>
    <a href="#" class="small-box-footer">
        More info <i class="fas fa-arrow-circle-right"></i>
    </a>
</div>
```

### Timeline

```html
<div class="timeline">
    <div class="time-label">
        <span class="bg-primary">Timeline</span>
    </div>
    <div>
        <i class="fas fa-envelope bg-primary"></i>
        <div class="timeline-item timeline-item-primary">
            <span class="time"><i class="fas fa-clock"></i> 12:05</span>
            <h3 class="timeline-header">Timeline Item</h3>
            <div class="timeline-body">
                Content here
            </div>
        </div>
    </div>
</div>
```

---

## DataTables Integration

### Primary Styled DataTables

```html
<table id="example" class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Position</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Data</td>
            <td>Data</td>
        </tr>
    </tbody>
</table>
```

**Styling Otomatis:**
- Pagination active button: Gradient primary
- Pagination hover: Gradient `Wisteria → Soft Periwinkle`

---

## Select2 Integration

```html
<select class="form-control select2" id="example">
    <option value="1">Option 1</option>
    <option value="2">Option 2</option>
</select>
```

**Styling:**
- Highlighted option: `Medium Slate Blue`
- Selected option: `Wisteria`
- Selected tags: `Medium Slate Blue`

---

## Contoh Penggunaan Lengkap

### Form dengan Primary Theme

```html
<form>
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Title</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" placeholder="Enter name">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" placeholder="Enter email">
            </div>
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="agree">
                    <label class="custom-control-label" for="agree">I agree</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Submit</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
        </div>
    </div>
</form>
```

### Dashboard Card dengan Gradient

```html
<div class="row">
    <div class="col-md-4">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Sales</h5>
                <h2 class="card-text">Rp 1.234.567</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-primary-gradient">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Users</span>
                <span class="info-box-number">5,678</span>
            </div>
        </div>
    </div>
</div>
```

### Navigation dengan Primary Theme

```html
<nav class="navbar navbar-primary">
    <a class="navbar-brand" href="#">Brand</a>
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link active" href="#">Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">About</a>
        </li>
    </ul>
</nav>
```

---

## Custom CSS dengan Variables

### Contoh Custom Component

```css
.custom-widget {
    background: linear-gradient(135deg, 
        var(--medium-slate-blue) 0%, 
        var(--soft-periwinkle) 100%);
    border: 2px solid var(--wisteria);
    color: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(113, 97, 239, 0.3);
}

.custom-widget:hover {
    background: linear-gradient(135deg, 
        var(--soft-periwinkle) 0%, 
        var(--medium-slate-blue) 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(113, 97, 239, 0.4);
}
```

```html
<div class="custom-widget">
    Custom Widget Content
</div>
```

---

## Responsive Considerations

Semua class warna dan utilities sudah responsive-ready. Sidebar dan navigation akan otomatis menyesuaikan di mobile dengan gradient yang sama.

---

## Browser Support

Semua class utilities didukung di:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Opera (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

---

## Tips & Best Practices

1. **Konsistensi Warna**: Gunakan `Medium Slate Blue` sebagai primary color utama
2. **Gradient Usage**: Gunakan gradient untuk elemen penting seperti headers dan buttons
3. **Contrast**: Pastikan kontras cukup untuk accessibility (WCAG AA)
4. **Hover States**: Semua interactive elements sudah memiliki hover states yang konsisten
5. **Focus States**: Form controls memiliki focus ring dengan warna primary

---

## Changelog

### Version 1.0.0 (Current)
- Initial release dengan Soft Periwinkle color palette
- Support untuk semua Bootstrap 4/5 components
- AdminLTE 3 integration
- DataTables dan Select2 styling
- Gradient utilities
- Shadow utilities
- Focus ring utilities

---

## Support

Untuk pertanyaan atau issue terkait theme, silakan hubungi tim development.

---

**Last Updated:** {{ date('Y-m-d') }}
**Theme Version:** 1.0.0
**Color Palette:** Soft Periwinkle

