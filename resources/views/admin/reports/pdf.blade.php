<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        
        .header h2 {
            color: #6c757d;
            margin: 0 0 15px 0;
            font-size: 16px;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .info-table td {
            padding: 3px 10px;
            font-size: 11px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        .data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        
        .data-table td.center {
            text-align: center;
        }
        
        .status-present { color: #28a745; font-weight: bold; }
        .status-late { color: #ffc107; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .status-sick { color: #17a2b8; font-weight: bold; }
        .status-permission { color: #6c757d; font-weight: bold; }
        
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            color: #6c757d;
        }
        
        .signature {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-box {
            display: inline-block;
            text-align: center;
            margin-left: 50px;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin: 50px auto 5px auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ABSENSI SISWA</h1>
        <h2>Sistem Absensi Sekolah</h2>
        
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
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 12%">Tanggal</th>
                <th style="width: 15%">NISN</th>
                <th style="width: 25%">Nama Siswa</th>
                <th style="width: 12%">Kelas</th>
                <th style="width: 8%">Clock In</th>
                <th style="width: 8%">Clock Out</th>
                <th style="width: 10%">Status</th>
                <th style="width: 15%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $attendance->date->format('d/m/Y') }}</td>
                <td>{{ $attendance->student->nisn ?? '-' }}</td>
                <td>{{ $attendance->student->user->name ?? '-' }}</td>
                <td>{{ $attendance->student->class->name ?? '-' }}</td>
                <td class="center">
                    {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                </td>
                <td class="center">
                    {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
                </td>
                <td class="center">
                    @if($attendance->status === 'present')
                        <span class="status-present">Hadir</span>
                    @elseif($attendance->status === 'late')
                        <span class="status-late">Terlambat</span>
                    @elseif($attendance->status === 'absent')
                        <span class="status-absent">Tidak Hadir</span>
                    @elseif($attendance->status === 'sick')
                        <span class="status-sick">Sakit</span>
                    @elseif($attendance->status === 'permission')
                        <span class="status-permission">Izin</span>
                    @else
                        {{ ucfirst($attendance->status) }}
                    @endif
                </td>
                <td>{{ $attendance->notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="center" style="padding: 20px; color: #6c757d;">
                    Tidak ada data absensi untuk periode yang dipilih
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div style="float: left;">
            <strong>Total:</strong> {{ $attendances->count() }} record ditemukan
        </div>
        <div style="float: right;">
            Laporan dibuat secara otomatis oleh Sistem Absensi Sekolah
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="signature">
        <div class="signature-box">
            <div>Mengetahui,</div>
            <div class="signature-line"></div>
            <div>Kepala Sekolah</div>
        </div>
    </div>
</body>
</html>