$(document).ready(function() {
    // Custom employee selector
    let selectedEmployees = [];
    
    function updateSelectedCount() {
        $('.employee-count').text(`(${selectedEmployees.length})`);
        
        if (selectedEmployees.length > 0) {
            let html = '';
            selectedEmployees.forEach(emp => {
                const isExternal = emp.nip.startsWith('L|');
                const className = isExternal ? 'selected-employee-external' : 'selected-employee-internal';
                html += `<span class="selected-employee-item ${className}">
                    ${emp.name}
                    <span class="selected-employee-remove" data-nip="${emp.nip}">×</span>
                </span>`;
            });
            $('#selected-employees').html(html).show();
        } else {
            $('#selected-employees').hide();
        }
        
        // Update hidden select
        $('#pegawai').val(selectedEmployees.map(emp => emp.nip)).trigger('change');
    }
    
    // Employee checkbox change (including dynamically added external employees)
    $(document).on('change', '.employee-checkbox', function() {
        const nip = $(this).closest('.employee-item').data('nip');
        const name = $(this).siblings('.employee-name').text().trim();
        
        if ($(this).is(':checked')) {
            selectedEmployees.push({ nip, name });
            showSuccessMessage('✓ ' + name + ' berhasil dipilih');
        } else {
            selectedEmployees = selectedEmployees.filter(emp => emp.nip !== nip);
            showSuccessMessage('ℹ ' + name + ' dihapus dari daftar');
        }
        
        updateSelectedCount();
    });
    
    // Search functionality
    $('#employee-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.employee-item').each(function() {
            const name = $(this).find('.employee-name').text().toLowerCase();
            if (name.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Toggle pegawai luar form
    $('#tambah_pegawai_luar').change(function() {
        if ($(this).is(':checked')) {
            $('#pegawai_luar_form').slideDown();
        } else {
            $('#pegawai_luar_form').slideUp();
        }
    });

    // Remove selected employee
    $(document).on('click', '.selected-employee-remove', function() {
        const nip = $(this).data('nip');
        const emp = selectedEmployees.find(e => e.nip === nip);
        
        selectedEmployees = selectedEmployees.filter(e => e.nip !== nip);
        
        // Uncheck checkbox if internal employee
        if (!nip.startsWith('L|')) {
            $(`.employee-checkbox[id="emp_${nip}"]`).prop('checked', false);
        } else {
            // Remove external employee from select and DOM
            $(`#pegawai option[value="${nip}"]`).remove();
            $(`.employee-item[data-nip="${nip}"]`).remove();
        }
        
        updateSelectedCount();
        showSuccessMessage('ℹ ' + emp.name + ' dihapus dari daftar');
    });

    // Add external participant
    $('#tambah_luar').click(function() {
        const nama = $('#nama_luar').val().trim();
        const nip = $('#nip_luar').val().trim();
        const pangkat = $('#pangkat_luar').val().trim();
        const golongan = $('#golongan_luar').val().trim();
        const jabatan = $('#jabatan_luar').val().trim();
        
        if (nama && jabatan) {
            const value = `L|${nama}|${nip || ''}|${pangkat || '-'}|${golongan || '-'}|${jabatan}`;
            const text = `${nama} - ${jabatan} (Eksternal)`;
            
            selectedEmployees.push({ nip: value, name: nama });
            
            // Add to employee list with external styling
            const externalItem = `
                <div class="employee-item employee-item-external" data-nip="${value}">
                    <input type="checkbox" class="employee-checkbox" id="emp_${value}" checked>
                    <label for="emp_${value}" class="employee-name">
                        ${nama} (Eksternal)
                    </label>
                </div>
            `;
            $('#employee-list').append(externalItem);
            
            const newOption = new Option(text, value, true, true);
            $('#pegawai').append(newOption).trigger('change');
            
            updateSelectedCount();
            showSuccessMessage('✓ Pegawai eksternal berhasil ditambahkan');
            
            const btn = $(this);
            btn.html('✓ Ditambahkan').addClass('btn-success').removeClass('btn-secondary');
            setTimeout(() => {
                btn.html('➕ Tambah').removeClass('btn-success').addClass('btn-secondary');
            }, 2000);
            
            $('#nama_luar, #nip_luar, #pangkat_luar, #golongan_luar, #jabatan_luar').val('');
        } else {
            alert('Mohon isi nama dan jabatan pegawai eksternal');
        }
    });

    // Function untuk generate preview
    function generatePreview() {
        const acara = $('#acara').val() || '[Acara belum diisi]';
        const tglMulai = $('#tgl_mulai').val();
        const tglSelesai = $('#tgl_selesai').val();
        const lokasi = $('#lokasi').val() || '[Lokasi belum diisi]';
        const dipa = $('#dipa').val() || 'SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024';
        const jabatanPejabat = $('#jabatan_pejabat option:selected').text() || '[Jabatan belum dipilih]';
        const namaPejabat = $('#nama_pejabat option:selected').text() || '[Pejabat belum dipilih]';
        const nipPejabat = $('#nama_pejabat').val() || '';
        const tembusan = $('#tembusan').val();
        
        // Format tanggal (sesuai PHP formatTanggalRange)
        let tanggalFormatted = '[Tanggal belum diisi]';
        if (tglMulai) {
            const startDate = new Date(tglMulai);
            const endDate = tglSelesai ? new Date(tglSelesai) : startDate;

            if (endDate < startDate) {
                tanggalFormatted = 'Error: Tanggal akhir tidak boleh sebelum tanggal awal';
            } else {
                const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

                const hari1 = hari[startDate.getDay()];
                const hari2 = hari[endDate.getDay()];
                const tgl1 = startDate.getDate();
                const tgl2 = endDate.getDate();
                const bln1 = bulan[startDate.getMonth()];
                const bln2 = bulan[endDate.getMonth()];
                const thn1 = startDate.getFullYear();
                const thn2 = endDate.getFullYear();

                if (bln1 === bln2 && thn1 === thn2) {
                    if (tgl1 === tgl2) {
                        tanggalFormatted = `${hari1}, tanggal ${tgl1} ${bln1} ${thn1}`;
                    } else {
                        tanggalFormatted = `${hari1}-${hari2}, tanggal ${tgl1}-${tgl2} ${bln1} ${thn1}`;
                    }
                } else {
                    tanggalFormatted = `${hari1}, tanggal ${tgl1} ${bln1} ${thn1} - ${hari2}, ${tgl2} ${bln2} ${thn2}`;
                }
            }
        }
        
        // Get selected pegawai
        const selectedPegawai = $('#pegawai').val() || [];
        let pegawaiRows = '';
        
        if (selectedPegawai.length > 0) {
            selectedPegawai.forEach((nip, index) => {
                if (nip.startsWith('L|')) {
                    const parts = nip.split('|');
                    let detailPegawai = `<strong>${parts[1] || 'Nama Eksternal'}</strong>`;
                    
                    if (parts[2] && parts[2].trim() !== '' && parts[2] !== '-') {
                        detailPegawai += `<br>${parts[2]}`;
                    }
                    
                    const pangkat = parts[3] && parts[3].trim() !== '' && parts[3] !== '-' ? parts[3] : '';
                    const golongan = parts[4] && parts[4].trim() !== '' && parts[4] !== '-' ? parts[4] : '';
                    
                    if (pangkat || golongan) {
                        const pangkatGolongan = [pangkat, golongan].filter(item => item).join(', ');
                        if (pangkatGolongan) {
                            detailPegawai += `<br>${pangkatGolongan}`;
                        }
                    }
                    
                    pegawaiRows += `
                        <tr>
                            <td style="text-align: center; border: 1px solid #000; padding: 6px;">${index + 1}.</td>
                            <td style="border: 1px solid #000; padding: 6px;">${detailPegawai}</td>
                            <td style="border: 1px solid #000; padding: 6px;">${parts[5] || 'Jabatan Eksternal'}</td>
                        </tr>
                    `;
                } else {
                    const pegawaiData = window.findPegawaiByNip(nip);
                    pegawaiRows += `
                        <tr>
                            <td style="text-align: center; border: 1px solid #000; padding: 6px;">${index + 1}.</td>
                            <td style="border: 1px solid #000; padding: 6px;">
                                <strong>${pegawaiData.nama_pegawai}</strong><br>
                                ${pegawaiData.nip}<br>
                                ${pegawaiData.pangkat}, ${pegawaiData.golongan}
                            </td>
                            <td style="border: 1px solid #000; padding: 6px;">${pegawaiData.jabatan}</td>
                        </tr>
                    `;
                }
            });
        } else {
            pegawaiRows = `
                <tr>
                    <td colspan="3" style="text-align: center; font-style: italic; color: #666; border: 1px solid #000; padding: 6px;">
                        Belum ada pegawai yang dipilih
                    </td>
                </tr>
            `;
        }
        
        // Template preview
        const previewHTML = `
            <div style="font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.4; color: #000;">
                <div style="text-align: center; margin-bottom: 25px; border-bottom: 2px solid #000; padding-bottom: 12px;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 70px; border: none; text-align: center; vertical-align: middle;">
                                <div style="width: 60px; height: 60px; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; font-size: 7pt; color: #666;">
                                   <img src="assets/dikti.png" alt="LOGO KEMENDIKTI" style="max-width: 100%; max-height: 100%;">
                                </div>
                            </td>
                            <td style="border: none; text-align: center; vertical-align: middle;">
                                <div style="font-size: 11pt; font-weight: bold; line-height: 1.2;">
                                    KEMENTERIAN PENDIDIKAN TINGGI, SAINS,<br>
                                    DAN TEKNOLOGI<br>
                                    <strong>DIREKTORAT JENDERAL SAINS DAN TEKNOLOGI</strong>
                                </div>
                                <div style="font-size: 9pt; margin-top: 6px; line-height: 1.3;">
                                    Jalan Jenderal Sudirman, Senayan, Jakarta 10270<br>
                                    Telepon (021) 57946104, Pusat Panggilan ULT DIKTI 126<br>
                                    Laman <span style="text-decoration: underline;">www.kemdiktisaintek.go.id</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div style="text-align: center; font-weight: bold; font-size: 12pt; margin: 20px 0; text-decoration: underline;">
                    <strong>SURAT TUGAS</strong><br>
                    <span style="font-weight: normal; font-size: 10pt;">Nomor: </span>
                </div>
                
                <div style="margin: 15px 0; text-align: justify; line-height: 1.5;">
                    <p>Dalam rangka kegiatan ${acara}, dengan ini Sekretaris Direktorat Jenderal Sains dan Teknologi menugaskan kepada nama di bawah ini,</p>
                </div>
                
                <table style="width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 10pt;">
                    <thead>
                        <tr>
                            <th style="width: 40px; border: 1px solid #000; padding: 6px; background-color: #f5f5f5; text-align: center;">No.</th>
                            <th style="width: 50%; border: 1px solid #000; padding: 6px; background-color: #f5f5f5; text-align: center;">Nama, NIP, Pangkat dan Golongan</th>
                            <th style="width: 45%; border: 1px solid #000; padding: 6px; background-color: #f5f5f5; text-align: center;">Jabatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${pegawaiRows}
                    </tbody>
                </table>
                
                <div style="margin: 15px 0; text-align: justify; line-height: 1.5;">
                    <p>Untuk hadir dan melaksanakan tugas dalam kegiatan dimaksud yang akan diselenggarakan pada hari ${tanggalFormatted}, bertempat di ${lokasi}</p>
                    
                    <br/>
                    <p>Biaya kegiatan dibebankan kepada DIPA Satuan Kerja Direktorat Jenderal Sains dan Teknologi, Nomor: ${dipa}.</p>
                    
                    <br/>
                    <p>Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan yang bersangkutan diharapkan membuat laporan.</p>
                </div>
                
                <div style="margin-top: 30px; display: table; width: 100%;">
                    <div style="display: table-cell; width: 50%; vertical-align: top;"></div>
                    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: top;">
                        <div style="margin-bottom: 12px;"> ${jabatanPejabat} ,</div>
                        <div style="margin: 60px 0 12px 0;"></div>
                        <div style="font-weight: bold;">${namaPejabat}</div>
                        <div style="font-size: 9pt;">NIP ${nipPejabat}</div>
                    </div>
                </div>
                
                ${tembusan ? `
                <div style="margin-top: 30px; clear: both;">
                    <strong>Tembusan:</strong><br>
                    ${tembusan.replace(/\n/g, '<br>')}
                </div>
                ` : ''}
            </div>
        `;
        
        $('#previewContent').html(previewHTML);
    }

    // Show update indicator function
    function showUpdateIndicator() {
        const indicator = document.getElementById('updateIndicator');
        indicator.classList.add('show');
        setTimeout(() => {
            indicator.classList.remove('show');
        }, 1500);
    }

    // Show success message function
    function showSuccessMessage(message) {
        const successMsg = document.getElementById('successMessage');
        successMsg.textContent = message;
        
        successMsg.classList.remove('info');
        
        if (message.includes('ℹ')) {
            successMsg.classList.add('info');
        }
        
        successMsg.classList.add('show');
        setTimeout(() => {
            successMsg.classList.remove('show');
            setTimeout(() => {
                successMsg.classList.remove('info');
            }, 400);
        }, 3000);
    }

    // Auto preview on form change (debounced)
    let previewTimeout;
    $('#suratTugasForm input, #suratTugasForm textarea, #suratTugasForm select').on('input change', function() {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(function() {
            generatePreview();
            showUpdateIndicator();
        }, 1000);
    });

    // Form validation and download confirmation
    $('#suratTugasForm').on('submit', function(e) {
        const pegawai = $('#pegawai').val();
        if (!pegawai || pegawai.length === 0) {
            e.preventDefault();
            alert('Mohon pilih minimal satu pegawai yang akan ditugaskan');
            return false;
        }
        
        if ($(e.originalEvent.submitter).attr('name') === 'action' && $(e.originalEvent.submitter).val() === 'export_word') {
            e.preventDefault();
            if (confirm('Apakah Anda yakin data surat tugas sudah benar dan siap untuk diunduh?')) {
                // Allow form to submit normally
                e.target.submit();
            }
            return false;
        }
    });

    // Set tanggal selesai minimal sama dengan tanggal mulai
    $('#tgl_mulai').change(function() {
        const tglMulai = $(this).val();
        $('#tgl_selesai').attr('min', tglMulai);
    });

    // Add smooth animations on load
    $('.form-group').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(20px)'
        });
        
        setTimeout(() => {
            $(this).css({
                'transition': 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)',
                'opacity': '1',
                'transform': 'translateY(0)'
            });
        }, index * 100);
    });

    // Load preview saat halaman pertama kali dibuka
    setTimeout(function() {
        generatePreview();
        showUpdateIndicator();
    }, 500);
});