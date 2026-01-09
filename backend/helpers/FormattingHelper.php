<?php
/**
 * Formatting Helper Functions
 * 
 * String formatting, date/time formatting, number conversions
 */

/**
 * Format date range in Indonesian
 * 
 * @param string $startDate Start date (Y-m-d)
 * @param string $endDate End date (Y-m-d)
 * @return string Formatted range (e.g., "1 - 10 Januari 2024")
 */
function formatTanggalRange($startDate, $endDate) {
    $months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $start = strtotime($startDate);
    $end   = strtotime($endDate);

    $startDay   = date('j', $start);
    $startMonth = (int)date('m', $start) - 1;
    $startYear  = date('Y', $start);

    $endDay     = date('j', $end);
    $endMonth   = (int)date('m', $end) - 1;
    $endYear    = date('Y', $end);

    if ($startMonth === $endMonth && $startYear === $endYear) {
        // Same month and year
        return "$startDay - $endDay {$months[$startMonth]} $startYear";
    } elseif ($startYear === $endYear) {
        // Same year, different month
        return "$startDay {$months[$startMonth]} - $endDay {$months[$endMonth]} $startYear";
    } else {
        // Different year
        return "$startDay {$months[$startMonth]} $startYear - $endDay {$months[$endMonth]} $endYear";
    }
}

/**
 * Format single date in Indonesian
 * 
 * @param string $date Date string (Y-m-d or timestamp)
 * @param bool $withDay Include day of week
 * @return string Formatted date (e.g., "10 Januari 2024")
 */
function formatTanggalIndonesia($date, $withDay = false) {
    $months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    $timestamp = is_numeric($date) ? $date : strtotime($date);
    
    if ($timestamp === false) {
        return '';
    }

    $day     = date('j', $timestamp);
    $month   = (int)date('m', $timestamp) - 1;
    $year    = date('Y', $timestamp);
    $dayName = $days[date('w', $timestamp)];

    $result = "{$day} {$months[$month]} {$year}";
    
    if ($withDay) {
        $result = "$dayName, $result";
    }

    return $result;
}

/**
 * Format waktu (time) for undangan/invitation
 * Handles start time and optional end time with Indonesian format
 * 
 * @param string $startTime Start time (H:i or HH:mm)
 * @param string $endTime End time (H:i or HH:mm) or "Selesai"
 * @return string Formatted time (e.g., "09.00 WIB s.d Selesai" or "09.00 - 12.00 WIB")
 */
function formatWaktuUndangan($startTime, $endTime = '') {
    if (empty($startTime)) {
        return '';
    }

    // Convert start time to HH.mm format (with dot, Indonesian style)
    $startTimestamp = strtotime($startTime);
    if ($startTimestamp === false) {
        return '';
    }
    
    $startFormatted = str_replace(':', '.', date('H:i', $startTimestamp));

    // Handle end time
    if (empty($endTime) || strtolower(trim($endTime)) === 'selesai') {
        // No end time or "Selesai" - return with s.d. Selesai
        return "{$startFormatted} WIB s.d. Selesai";
    }

    // End time exists - format it
    $endTimestamp = strtotime($endTime);
    if ($endTimestamp === false) {
        return "{$startFormatted} WIB s.d Selesai";
    }
    
    $endFormatted = str_replace(':', '.', date('H:i', $endTimestamp));

    // Return time range with s.d.
    return "{$startFormatted} s.d. {$endFormatted} WIB";
}

/**
 * Convert number to Indonesian words
 * 
 * @param int $number Number to convert
 * @return string Formatted text (e.g., "satu ribu dua ratus tiga puluh empat")
 */
function numberToWords($number) {
    $words = [
        0 => 'nol', 1 => 'satu', 2 => 'dua', 3 => 'tiga', 4 => 'empat',
        5 => 'lima', 6 => 'enam', 7 => 'tujuh', 8 => 'delapan', 9 => 'sembilan',
        10 => 'sepuluh', 11 => 'sebelas', 20 => 'dua puluh', 30 => 'tiga puluh',
        40 => 'empat puluh', 50 => 'lima puluh', 60 => 'enam puluh',
        70 => 'tujuh puluh', 80 => 'delapan puluh', 90 => 'sembilan puluh'
    ];

    if ($number == 0) {
        return $words[0];
    }

    if ($number < 0) {
        return 'minus ' . numberToWords(-$number);
    }

    if ($number < 10) {
        return $words[$number];
    }

    if ($number < 20) {
        return $words[10 + ($number - 10)];
    }

    if ($number < 100) {
        return $words[($number / 10) * 10] . ($number % 10 ? ' ' . $words[$number % 10] : '');
    }

    if ($number < 1000) {
        return 'seratus ' . ($number % 100 ? numberToWords($number % 100) : '');
    }

    if ($number < 1000000) {
        return numberToWords((int)($number / 1000)) . ' ribu' . ($number % 1000 ? ' ' . numberToWords($number % 1000) : '');
    }

    return '';
}

/**
 * Format jumlah lampiran (number of attachments) to Indonesian text
 * Returns only the number word (e.g., "Dua", "Tiga")
 * 
 * @param int $count Number of attachments
 * @return string Number in Indonesian word form (e.g., "Dua", "Tiga")
 */
function formatJumlahLampiran($count) {
    return ucfirst(numberToWords($count));
}
?>
