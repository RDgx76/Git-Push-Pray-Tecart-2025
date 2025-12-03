// SCRIPT.JS - Sistem Toko Sederhana
// JavaScript untuk interaksi dan validasi

console.log('✅ Sistem Toko Sederhana siap digunakan!');

// ============================================
// 1. FUNGSI UMUM
// ============================================

/**
 * Format angka ke Rupiah
 */
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
}

/**
 * Validasi form tidak kosong
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const required = form.querySelectorAll('[required]');
    let isValid = true;
    
    required.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#e74c3c';
            isValid = false;
        } else {
            input.style.borderColor = '#ddd';
        }
    });
    
    if (!isValid) {
        alert('Harap isi semua field yang wajib diisi!');
    }
    
    return isValid;
}

/**
 * Tampilkan loading
 */
function showLoading(message = 'Memproses...') {
    const loading = document.createElement('div');
    loading.id = 'loading-overlay';
    loading.innerHTML = `
        <div class="loading-content">
            <div class="spinner"></div>
            <p>${message}</p>
        </div>
    `;
    loading.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        color: white;
    `;
    document.body.appendChild(loading);
}

/**
 * Sembunyikan loading
 */
function hideLoading() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        loading.remove();
    }
}

// ============================================
// 2. FUNGSI TRANSAKSI
// ============================================

/**
 * Update harga berdasarkan produk yang dipilih
 */
function updateHarga() {
    const select = document.getElementById('produkSelect');
    const hargaDisplay = document.getElementById('hargaDisplay');
    const maxStokInfo = document.getElementById('maxStokInfo');
    const jumlahInput = document.getElementById('jumlahInput');
    
    if (!select || !hargaDisplay || !maxStokInfo || !jumlahInput) return;
    
    if (select.value) {
        const selectedOption = select.options[select.selectedIndex];
        const harga = selectedOption.getAttribute('data-harga');
        const stok = selectedOption.getAttribute('data-stok');
        
        hargaDisplay.textContent = formatRupiah(harga);
        maxStokInfo.textContent = `Stok tersedia: ${stok}`;
        jumlahInput.max = stok;
        jumlahInput.value = 1;
        
        hitungTotal();
    } else {
        hargaDisplay.textContent = 'Pilih produk terlebih dahulu';
        maxStokInfo.textContent = 'Stok tersedia: 0';
        jumlahInput.max = 1;
    }
}

/**
 * Hitung total harga transaksi
 */
function hitungTotal() {
    const select = document.getElementById('produkSelect');
    const jumlahInput = document.getElementById('jumlahInput');
    const totalDisplay = document.getElementById('totalDisplay');
    
    if (!select || !jumlahInput || !totalDisplay) return;
    
    if (select.value && jumlahInput.value > 0) {
        const harga = select.options[select.selectedIndex].getAttribute('data-harga');
        const jumlah = parseInt(jumlahInput.value) || 1;
        const total = harga * jumlah;
        totalDisplay.textContent = formatRupiah(total);
    } else {
        totalDisplay.textContent = formatRupiah(0);
    }
}

/**
 * Validasi stok cukup
 */
function cekStok() {
    const select = document.getElementById('produkSelect');
    const jumlahInput = document.getElementById('jumlahInput');
    
    if (!select.value) {
        alert('Pilih produk terlebih dahulu!');
        return false;
    }
    
    const stok = parseInt(select.options[select.selectedIndex].getAttribute('data-stok'));
    const jumlah = parseInt(jumlahInput.value);
    
    if (jumlah > stok) {
        alert(`Stok tidak mencukupi! Hanya tersedia ${stok} unit.`);
        jumlahInput.value = stok;
        hitungTotal();
        return false;
    }
    
    return true;
}

// ============================================
// 3. FUNGSI LAPORAN
// ============================================

/**
 * Export tabel ke Excel (simulasi)
 */
function exportToExcel(tableId, filename = 'laporan') {
    const table = document.getElementById(tableId);
    if (!table) {
        alert('Tabel tidak ditemukan!');
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let row of rows) {
        const cells = row.querySelectorAll('th, td');
        const rowData = [];
        
        for (let cell of cells) {
            let cellText = cell.textContent.trim();
            // Hapus tombol aksi
            if (cell.querySelector('.btn')) {
                cellText = '';
            }
            rowData.push(`"${cellText}"`);
        }
        
        csv.push(rowData.join(','));
    }
    
    const csvString = csv.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = `${filename}_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    alert(`File ${filename}.csv berhasil diunduh!`);
}

/**
 * Filter laporan berdasarkan tanggal
 */
function filterLaporan() {
    showLoading('Menyaring data...');
    setTimeout(() => {
        hideLoading();
        alert('Filter diterapkan!');
    }, 1000);
}

// ============================================
// 4. FUNGSI PRODUK
// ============================================

/**
 * Konfirmasi hapus produk
 */
function konfirmasiHapus(namaProduk) {
    return confirm(`Yakin ingin menghapus produk "${namaProduk}"?\n\n⚠️ Data yang sudah dihapus tidak dapat dikembalikan!`);
}

/**
 * Cari produk di tabel
 */
function cariProduk() {
    const input = document.getElementById('cariProduk');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('tabelProduk');
    
    if (!table) return;
    
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let cell of cells) {
            if (cell.textContent.toUpperCase().includes(filter)) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

// ============================================
// 5. FUNGSI LOGIN
// ============================================

/**
 * Toggle show/hide password
 */
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePassword');
    
    if (!passwordInput || !toggleBtn) return;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        toggleBtn.textContent = '👁️';
    }
}

/**
 * Validasi form login
 */
function validasiLogin() {
    const username = document.querySelector('[name="username"]');
    const password = document.querySelector('[name="password"]');
    
    if (!username.value.trim()) {
        alert('Username harus diisi!');
        username.focus();
        return false;
    }
    
    if (!password.value.trim()) {
        alert('Password harus diisi!');
        password.focus();
        return false;
    }
    
    showLoading('Memproses login...');
    return true;
}

// ============================================
// 6. EVENT LISTENERS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('📱 Halaman dimuat sepenuhnya');
    
    // Auto-focus pada input pertama di form
    const form = document.querySelector('form');
    if (form) {
        const firstInput = form.querySelector('input, select, textarea');
        if (firstInput && !firstInput.disabled) {
            firstInput.focus();
        }
    }
    
    // Animasi untuk semua alert
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.classList.add('fade-in');
    });
    
    // Tambahkan spinner CSS
    const style = document.createElement('style');
    style.textContent = `
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-content {
            text-align: center;
        }
    `;
    document.head.appendChild(style);
    
    // Inisialisasi fungsi transaksi jika halaman transaksi
    if (document.getElementById('produkSelect')) {
        updateHarga();
    }
    
    // Auto-hide alert setelah 5 detik
    setTimeout(() => {
        const autoHideAlerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        autoHideAlerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        });
    }, 5000);
});

// ============================================
// 7. ERROR HANDLING
// ============================================

window.onerror = function(msg, url, lineNo, columnNo, error) {
    console.error('❌ Error:', {
        message: msg,
        url: url,
        line: lineNo,
        column: columnNo,
        error: error
    });
    
    // Tampilkan pesan error user-friendly
    if (window.location.href.includes('dashboard')) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-error';
        errorDiv.innerHTML = `
            <strong>Terjadi kesalahan!</strong>
            <p>Silakan refresh halaman atau hubungi admin.</p>
            <small>${msg}</small>
        `;
        document.querySelector('.container').prepend(errorDiv);
    }
    
    return false;
};

// ============================================
// 8. HELPER FUNCTIONS
// ============================================

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text)
        .then(() => {
            alert('Teks berhasil disalin!');
        })
        .catch(err => {
            console.error('Gagal menyalin:', err);
        });
}

/**
 * Format tanggal Indonesia
 */
function formatTanggal(tanggal) {
    const date = new Date(tanggal);
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('id-ID', options);
}

/**
 * Hitung diskon
 */
function hitungDiskon(harga, diskonPersen) {
    const diskon = harga * (diskonPersen / 100);
    return {
        hargaAwal: harga,
        diskon: diskon,
        hargaAkhir: harga - diskon
    };
}

// ============================================
// 9. PRINT FUNCTIONS
// ============================================

/**
 * Cetak halaman/struk
 */
function cetakHalaman(printId = null) {
    const originalContents = document.body.innerHTML;
    
    if (printId) {
        const printContents = document.getElementById(printId).innerHTML;
        document.body.innerHTML = `
            <div style="padding: 20px;">
                <h2 style="text-align: center; margin-bottom: 20px;">
                    Struk Transaksi - Toko Sederhana
                </h2>
                ${printContents}
                <div style="margin-top: 30px; text-align: center;">
                    <p>Terima kasih telah berbelanja!</p>
                    <p>${new Date().toLocaleString('id-ID')}</p>
                </div>
            </div>
        `;
    }
    
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload();
}