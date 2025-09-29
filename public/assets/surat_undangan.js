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

    // Jenis undangan selector
    $('.jenis-option').click(function() {
        $('.jenis-option').removeClass('active');
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
        const jabatan = $('#jabatan_luar').val().trim();
        
        if (nama && jabatan) {
            const value = `L|${nama}|${jabatan}`;
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
            showSuccessMessage('✓ Peserta eksternal berhasil ditambahkan');
            
            const btn = $(this);
            btn.html('✓ Ditambahkan').addClass('btn-success').removeClass('btn-primary surat-undangan');
            setTimeout(() => {
                btn.html('➕ Tambah').removeClass('btn-success').addClass('btn-primary surat-undangan');
            }, 2000);
            
            $('#nama_luar, #jabatan_luar').val('');
        } else {
            alert('Mohon isi nama dan jabatan peserta eksternal');
        }
    });

    // Generate preview function
    function generatePreview() {
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

        // Get selected pegawai
        const selectedPegawai = $('#pegawai').val() || [];
        let undanganRows = '';
        
        if (selectedPegawai.length > 0) {
            let currentNo = 1;
            selectedPegawai.forEach((nip) => {
                if (nip.startsWith('L|')) {
                    const parts = nip.split('|');
                    const namaJabatan = `${parts[1] || 'Nama Eksternal'}<br>${parts[2] || 'Jabatan Eksternal'}`;
                    
                    undanganRows += `
                        <tr>
                            <td style="text-align: center; border: 1px solid #000; padding: 8px; width: 30px;">${currentNo}.</td>
                            <td style="border: 1px solid #000; padding: 8px;">${namaJabatan}</td>
                        </tr>
                    `;
                } else {
                    const pegawaiData = window.findPegawaiByNip(nip);
                    const namaJabatan = `${pegawaiData.nama_pegawai}<br>NIP ${pegawaiData.nip}<br>${pegawaiData.pangkat}, ${pegawaiData.golongan}<br>${pegawaiData.jabatan}`;
                    undanganRows += `
                        <tr>
                            <td style="text-align: center; border: 1px solid #000; padding: 8px; width: 30px;">${currentNo}.</td>
                            <td style="border: 1px solid #000; padding: 8px;">${namaJabatan}</td>
                        </tr>
                    `;
                }
                currentNo++;
            });
        } else {
            undanganRows = `
                <tr>
                    <td colspan="2" style="text-align: center; font-style: italic; color: #666; border: 1px solid #000; padding: 8px;">
                        Belum ada peserta yang dipilih
                    </td>
                </tr>
            `;
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
                    <div><strong>Nomor</strong>: ___________________</div>
                    <div><strong>Lampiran</strong>: satu lembar</div>
                    <div><strong>Hal</strong>: Undangan</div>
                </div>

                <!-- Yth. -->
                <div style="margin: 20px 0; font-size: 11pt;">
                    <div style="font-weight: bold;">Yth. Peserta Kegiatan</div>
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
                    <p>Untuk informasi lebih lanjut mengenai rapat dan konfirmasi kehadiran, tim ${gender} dapat menghubungi ${narahubung} di nomor ${noNarahubung}.</p>
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
                    <div style="text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 20px;">
                        Lampiran<br>
                        Nomor: ___________________<br>
                        Tanggal: ${tanggalFormatted}
                    </div>
                    <div style="margin-bottom: 15px; text-align: center; font-weight: bold;">
                        <em>Yth.</em>
                    </div>
                    <table style="width: 100%; border-collapse: collapse; margin: 12px 0;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid #000; padding: 8px; background-color: #f5f5f5; text-align: center; width: 30px;">No.</th>
                                <th style="border: 1px solid #000; padding: 8px; background-color: #f5f5f5; text-align: center;">Nama dan Jabatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${undanganRows}
                        </tbody>
                    </table>
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
    $('#suratUndanganForm input, #suratUndanganForm textarea, #suratUndanganForm select').on('input change', function() {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(() => {
            generatePreview();
            showUpdateIndicator();
        }, 1000);
    });

    // Form validation and download confirmation
    $('#suratUndanganForm').on('submit', function(e) {
        const pegawai = $('#pegawai').val();
        if (!pegawai || pegawai.length === 0) {
            e.preventDefault();
            alert('Mohon pilih minimal satu peserta undangan');
            return false;
        }
        
        if ($(e.originalEvent.submitter).attr('name') === 'action' && $(e.originalEvent.submitter).val() === 'export_word') {
            e.preventDefault();
            if (confirm('Apakah Anda yakin data surat undangan sudah benar dan siap untuk diunduh?')) {
                // Allow form to submit normally
                e.target.submit();
            }
            return false;
        }
    });

    // Set default tanggal ke hari ini + 7 hari
    const today = new Date();
    today.setDate(today.getDate() + 7);
    const defaultDate = today.toISOString().split('T')[0];
    $('#tanggal').val(defaultDate);

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

    // Initial preview
    setTimeout(() => {
        generatePreview();
        showUpdateIndicator();
    }, 500);
});