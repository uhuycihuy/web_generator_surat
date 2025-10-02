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
        
        // Update hidden select to maintain order
        const pegawaiSelect = $('#pegawai');
        pegawaiSelect.empty();
        selectedEmployees.forEach(emp => {
            pegawaiSelect.append(new Option('', emp.nip, true, true));
        });
        pegawaiSelect.trigger('change');
    }
    
    // Employee checkbox change (including dynamically added external employees)
    $(document).on('change', '.employee-checkbox', function() {
        const nip = $(this).closest('.employee-item').attr('data-nip');
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

    // Jenis surat selector
    $('.jenis-option').click(function() {
        $('.jenis-option').removeClass('active');
        $(this).addClass('active');
        
        const jenis = $(this).data('jenis');
        $('#jenis_surat').val(jenis);
        
        // Reset selected employees when switching letter type
        selectedEmployees = [];
        $('.employee-checkbox').prop('checked', false);
        $('.employee-item-external').remove();
        updateSelectedCount();
        
        if (jenis === 'undangan') {
            $('.tugas-fields').hide();
            $('.undangan-fields').show();
            $('.tugas-external-fields').hide();
            $('.undangan-external-fields').show();
            $('#label-acara').text('Acara');
            $('#label-pegawai').text('Peserta Undangan');
            $('#preview-title').text('Preview');
            $('#acara').attr('placeholder', 'Contoh: Rapat Koordinasi Penyusunan Program Kerja Tahun 2025');
            
            // Set required fields for undangan
            $('#tanggal, #waktu_awal, #agenda, #narahubung, #no_narahubung').attr('required', 'required');
            $('#tgl_mulai, #lokasi_tugas, #dipa').removeAttr('required');
            
            // Set default tanggal ke hari ini + 7 hari
            const today = new Date();
            today.setDate(today.getDate() + 7);
            const defaultDate = today.toISOString().split('T')[0];
            $('#tanggal').val(defaultDate);
        } else {
            $('.undangan-fields').hide();
            $('.tugas-fields').show();
            $('.undangan-external-fields').hide();
            $('.tugas-external-fields').show();
            $('#label-acara').text('Kegiatan/Acara');
            $('#label-pegawai').text('Daftar Pegawai');
            $('#preview-title').text('Preview Surat Tugas');
            $('#acara').attr('placeholder', 'Contoh: Rekonsiliasi Kebutuhan dan Penyusunan Prognosis Anggaran');
            
            // Set required fields for tugas
            $('#tgl_mulai, #lokasi_tugas, #dipa').attr('required', 'required');
            $('#tanggal, #waktu_awal, #agenda, #narahubung, #no_narahubung').removeAttr('required');
        }
        
        updateFormAction();
        generatePreview();
    });

    // Jenis undangan selector (online/offline)
    $(document).on('click', '.jenis-option-undangan', function() {
        $('.jenis-option-undangan').removeClass('active');
        $(this).addClass('active');
        
        const jenis = $(this).data('jenis');
        $('#jenis_undangan').val(jenis);
        
        if (jenis === 'online') {
            $('.offline-fields').hide();
            $('.online-fields').show();
            $('#lokasi').removeAttr('required');
            $('#media').attr('required', 'required');
        } else {
            $('.online-fields').hide();
            $('.offline-fields').show();
            $('#media').removeAttr('required');
            $('#lokasi').attr('required', 'required');
        }
        
        generatePreview();
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
        const nip = $(this).attr('data-nip');
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
        const jenisSurat = $('#jenis_surat').val();
        let nama, jabatan, value, text;
        
        if (jenisSurat === 'undangan') {
            nama = $('#nama_luar_undangan').val().trim();
            jabatan = $('#jabatan_luar_undangan').val().trim();
            value = `L|${nama}|${jabatan}`;
            text = `${nama} - ${jabatan} (Eksternal)`;
        } else {
            nama = $('#nama_luar').val().trim();
            const nip = $('#nip_luar').val().trim();
            const pangkat = $('#pangkat_luar').val().trim();
            const golongan = $('#golongan_luar').val().trim();
            jabatan = $('#jabatan_luar').val().trim();
            value = `L|${nama}|${nip || ''}|${pangkat || '-'}|${golongan || '-'}|${jabatan}`;
            text = `${nama} - ${jabatan} (Eksternal)`;
        }
        
        if (nama && jabatan) {
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
            
            // Clear form fields
            if (jenisSurat === 'undangan') {
                $('#nama_luar_undangan, #jabatan_luar_undangan').val('');
            } else {
                $('#nama_luar, #nip_luar, #pangkat_luar, #golongan_luar, #jabatan_luar').val('');
            }
        } else {
            alert('Mohon isi nama dan jabatan pegawai eksternal');
        }
    });

    // Update form action based on jenis surat
    function updateFormAction() {
        const jenisSurat = $('#jenis_surat').val();
        if (jenisSurat === 'undangan') {
            $('#generatorSuratForm').attr('action', '../backend/controllers/SuratUndanganController.php');
        } else {
            $('#generatorSuratForm').attr('action', '../backend/controllers/SuratTugasController.php');
        }
    }

    // Generate preview function
    function generatePreview() {
        const jenisSurat = $('#jenis_surat').val();
        
        if (jenisSurat === 'undangan') {
            generateUndanganPreview();
        } else {
            generateTugasPreview();
        }
    }

    // Generate Surat Tugas Preview
    function generateTugasPreview() {
        const acara = $('#acara').val() || '[Acara belum diisi]';
        const tglMulai = $('#tgl_mulai').val();
        const tglSelesai = $('#tgl_selesai').val();
        const lokasi = $('#lokasi_tugas').val() || '[Lokasi belum diisi]';
        const dipa = $('#dipa').val() || 'SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024';
        const jabatanPejabat = $('#jabatan_pejabat option:selected').text() || '[Jabatan belum dipilih]';
        const namaPejabat = $('#nama_pejabat option:selected').text() || '[Pejabat belum dipilih]';
        const nipPejabat = $('#nama_pejabat').val() || '';
        const tembusan = $('#tembusan').val();
        
        // Format tanggal
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
        
        let pegawaiRows = '';

        if (selectedEmployees.length > 0) {
            selectedEmployees.forEach((sel, index) => {
                const nip = sel.nip;
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
                    // internal employee: lookup by nip
                    const pegawaiData = window.findPegawaiByNip(nip);
                    console.debug('[debug] Tugas preview - pegawaiData for', nip, ':', pegawaiData);
                    
                    // Build pangkat/golongan display only when present
                    const pangkat = pegawaiData.pangkat ? String(pegawaiData.pangkat).trim() : '';
                    const golongan = pegawaiData.golongan ? String(pegawaiData.golongan).trim() : '';
                    const pangkatGolongan = [pangkat, golongan].filter(Boolean).join(', ');

                    let detail = `<strong>${pegawaiData.nama_pegawai}</strong><br>${pegawaiData.nip}`;
                    if (pangkatGolongan) {
                        detail += `<br>${pangkatGolongan}`;
                    }

                    pegawaiRows += `
                        <tr>
                            <td style="text-align: center; border: 1px solid #000; padding: 6px;">${index + 1}.</td>
                            <td style="border: 1px solid #000; padding: 6px;">${detail}</td>
                            <td style="border: 1px solid #000; padding: 6px;">${pegawaiData.jabatan || ''}</td>
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
                
                <div style="text-align: center; font-size: 12pt; margin: 20px 0;">
                    <div style="margin-bottom: 5px;">SURAT TUGAS</div>
                    <div style="font-size: 11pt;">Nomor</div>
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
                    <div style="display: table; width: 100%;">
                        <div style="display: table-cell; width: 50%; vertical-align: top;">
                            Tembusan:<br>
                            ${tembusan.replace(/\n/g, '<br>')}
                        </div>
                        <div style="display: table-cell; width: 50%; vertical-align: top;"></div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        $('#previewContent').html(previewHTML);
    }

    // Generate Surat Undangan Preview
    function generateUndanganPreview() {
        const jenisUndangan = $('#jenis_undangan').val();
        const acara = $('#acara').val() || '[Acara belum diisi]';
        const tanggal = $('#tanggal').val();
        const waktuAwal = $('#waktu_awal').val();
        const waktuAkhir = $('#waktu_akhir').val();
        const lokasi = $('#lokasi').val() || '[Tempat belum diisi]';
        const media = $('#media').val() || '[Media belum diisi]';
        const rapatId = $('#rapat_id').val() || '';
        const kataSandi = $('#kata_sandi').val() || '';
        const tautan = $('#tautan').val() || '';
        const agenda = $('#agenda').val() || '[Agenda belum diisi]';
        const kalimatOpsional = $('#kalimat_opsional').val();
        const narahubung = $('#narahubung').val() || '';
        const noNarahubung = $('#no_narahubung').val() || '';
        const gender = $('#gender').val() || 'Saudara';
        const tembusan = $('#tembusan').val();
        const nipPejabat = $('#nama_pejabat').val() || '';
        const namaPejabat = nipPejabat ? $('#nama_pejabat option:selected').text() : '[Pejabat belum dipilih]';
        const jabatanPejabat = $('#jabatan_pejabat option:selected').text() || '[Jabatan belum dipilih]';

        // Format tanggal Indonesia
        let tanggalFormatted = '[Tanggal belum diisi]';
        if (tanggal) {
            const date = new Date(tanggal);
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            tanggalFormatted = days[date.getDay()] + ', ' + date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
        }

        // Format waktu
        let waktuFormatted = '[Waktu belum diisi]';
        if (waktuAwal) {
            waktuFormatted = waktuAwal;
            if (waktuAkhir) {
                waktuFormatted += ' - ' + waktuAkhir + ' WIB';
            } else {
                waktuFormatted += ' - selesai WIB';
            }
        }

        function generateUndanganList() {
            let undanganList = '';
            
            if (selectedEmployees.length > 0) {
                selectedEmployees.forEach((sel, index) => {
                    const nip = sel.nip;
                    if (nip.startsWith('L|')) {
                        const parts = nip.split('|');
                        const nama = parts[1] || 'Nama Eksternal';
                        const jabatan = parts[2] || 'Jabatan Eksternal';
                        
                        undanganList += `
                            <div style="margin-bottom: 8px; line-height: 1.4;">
                                ${index + 1}. ${nama}, ${jabatan}
                            </div>
                        `;
                    } else {
                        const pegawaiData = window.findPegawaiByNip(nip);
                        const jabatan = pegawaiData.jabatan || '';
                        
                        undanganList += `
                            <div style="margin-bottom: 8px; line-height: 1.4;">
                                ${index + 1}. ${pegawaiData.nama_pegawai}, ${jabatan}
                            </div>
                        `;
                    }
                });
            } else {
                undanganList = `
                    <div style="text-align: center; font-style: italic; color: #666; padding: 20px;">
                        Belum ada peserta yang dipilih
                    </div>
                `;
            }
            
            return undanganList;
        }

        // Template preview
        const previewHTML = `
            <div style="font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.4; color: #000;">
                <!-- Header -->
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

                <!-- Nomor, Lampiran, Hal -->
                <div style="margin: 20px 0; font-size: 11pt;">
                    <div>Nomor :</div>
                    <div>Lampiran : satu lembar</div>
                    <div>Hal : Undangan</div>
                </div>

                <!-- Yth. -->
                <div style="margin: 20px 0; font-size: 11pt;">
                    <div>Yth. Peserta Kegiatan</div>
                    <div>(daftar terlampir)</div>
                </div>

                <!-- Isi -->
                <div style="text-align: justify; line-height: 1.5; margin: 15px 0;">
                    <p>Dalam rangka kegiatan ${acara}, Sehubungan dengan hal tersebut, kami mengundang ${gender} untuk berkenan hadir dan berpartisipasi dalam rapat yang akan dilaksanakan pada:</p>
                </div>

                <!-- Detail Acara -->
                <table style="width: 100%; margin: 10px 0; font-size: 11pt;">
                    <tr>
                        <td style="width: 120px; vertical-align: top;">hari, tanggal</td>
                        <td style="width: 10px; vertical-align: top;">:</td>
                        <td style="vertical-align: top;">${tanggalFormatted}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">waktu</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">${waktuFormatted}</td>
                    </tr>
                    ${jenisUndangan === 'online' ? `
                    <tr>
                        <td style="vertical-align: top;">media</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">${media}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">rapat id</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">${rapatId || '[Meeting ID belum diisi]'}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">kata sandi</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">${kataSandi || '[Kata sandi belum diisi]'}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">tautan</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">${tautan ? `<a href="${tautan}">${tautan}</a>` : '[Tautan belum diisi]'}</td>
                    </tr>
                    ` : `
                    <tr>
                        <td style="vertical-align: top;">tempat</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">${lokasi}</td>
                    </tr>
                    `}
                    <tr>
                        <td style="vertical-align: top;">agenda</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">${agenda.replace(/\n/g, '<br>')}</td>
                    </tr>
                </table>

                <!-- Penutup -->
                <div style="margin: 15px 0; text-align: justify; line-height: 1.5;">
                    ${kalimatOpsional ? `<p>${kalimatOpsional}</p><br/>` : ''}
                    ${narahubung && noNarahubung ? `
                    <p>Untuk informasi lebih lanjut mengenai rapat dan konfirmasi kehadiran, tim Bapak/Ibu dapat menghubungi narahubung kami melalui ${gender} ${narahubung} di nomor gawai ${noNarahubung}.</p>
                    <br/>
                    ` : ''}
                    <p>Demikian surat ini kami sampaikan. Atas perhatian dan kerja sama yang baik, kami ucapkan terima kasih.</p>
                </div>

                <!-- TTD -->
                <div style="margin-top: 30px; display: table; width: 100%;">
                    <div style="display: table-cell; width: 50%; vertical-align: top;"></div>
                    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: top;">
                        <div style="margin-bottom: 12px;">${jabatanPejabat},</div>
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

                <!-- Lampiran -->
                <div style="page-break-before: always; margin-top: 30px;">
                    <div style="text-align: left; font-size: 11pt; margin-bottom: 20px;">
                        <div>Lampiran</div>
                        <div>Nomor :</div>
                        <div>Tanggal :</div>
                    </div>
                    <div style="margin: 20px 0; font-style: italic;">
                        <em>Yth.</em>
                    </div>
                    <div style="margin-left: 0;">
                        ${generateUndanganList()}
                    </div>
                </div>
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
    $('#generatorSuratForm input, #generatorSuratForm textarea, #generatorSuratForm select').on('input change', function() {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(() => {
            generatePreview();
            showUpdateIndicator();
        }, 1000);
    });

    // Form validation and download confirmation
    $('#generatorSuratForm').on('submit', function(e) {
        const pegawai = $('#pegawai').val();
        if (!pegawai || pegawai.length === 0) {
            e.preventDefault();
            const jenisSurat = $('#jenis_surat').val();
            const message = jenisSurat === 'undangan' ? 'Mohon pilih minimal satu peserta undangan' : 'Mohon pilih minimal satu pegawai yang akan ditugaskan';
            alert(message);
            return false;
        }
        
        // Debug: log form data
        console.log('Form action:', $(this).attr('action'));
        console.log('Selected employees order:', selectedEmployees.map(e => e.nip));
        console.log('Form pegawai field:', $('#pegawai').val());
        console.log('Form data:', $(this).serialize());
        
        const submitter = e.originalEvent.submitter;
        if (submitter && submitter.name === 'action' && submitter.value === 'export_word') {
            const jenisSurat = $('#jenis_surat').val();
            const message = jenisSurat === 'undangan' ? 'Apakah Anda yakin data surat undangan sudah benar dan siap untuk diunduh?' : 'Apakah Anda yakin data surat tugas sudah benar dan siap untuk diunduh?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
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

    // Initialize
    updateFormAction();
    setTimeout(() => {
        generatePreview();
        showUpdateIndicator();
    }, 500);
});