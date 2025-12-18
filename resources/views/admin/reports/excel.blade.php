<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0 0 5px 0;
            font-size: 18px;
        }
        
        .header h2 {
            color: #6c757d;
            margin: 0 0 15px 0;
            font-size: 14px;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 3px 5px;
            font-size: 11px;
            border: 1px solid #ddd;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            font-size: 10px;
        }
        
        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ABSENSI SISWA</h1>
        <h2>Sistem Absensi Sekolah</h2>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Periode:</strong></td>
            <td>{{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</td>
            <td><strong>Kelas:</strong></td>
            <td>{{ $filterInfo['class_name'] }}</td>
        </tr>
        <tr>
            <td><strong>Siswa:</strong></td>
            <td>{{ $filterInfo['student_name'] }}</td>
            <td><strong>Tanggal Cetak:</strong></td>
            <td>{{ \Carbon\Carbon::now()->format('d F Y H:i') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>NISN</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $attendance->date->format('d/m/Y') }}</td>
                <td>{{ $attendance->student->nisn ?? '-' }}</td>
                <td>{{ $attendance->student->name ?? '-' }}</td>
                <td>{{ $attendance->student->class->name ?? '-' }}</td>
                <td class="center">
                    {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                </td>
                <td class="center">
                    {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
                </td>
                <td class="center">
                    @if($attendance->status === 'present')
                        Hadir
                    @elseif($attendance->status === 'late')
                        Terlambat
                    @elseif($attendance->status === 'absent')
                        Tidak Hadir
                    @elseif($attendance->status === 'sick')
                        Sakit
                    @elseif($attendance->status === 'permission')
                        Izin
                    @else
                        {{ ucfirst($attendance->status) }}
                    @endif
                </td>
                <td>{{ $attendance->notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="center">
                    Tidak ada data absensi untuk periode yang dipilih
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <br>
    <p style="font-size: 10px; color: #666;">
        <strong>Total:</strong> {{ $attendances->count() }} record ditemukan<br>
        Laporan dibuat secara otomatis oleh Sistem Absensi Sekolah
    </p>
</body>
</html>