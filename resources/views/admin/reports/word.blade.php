<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 1in;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header h2 {
            margin: 0 0 15px 0;
            font-size: 14px;
            font-weight: normal;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-table td {
            padding: 2px 5px;
            font-size: 11px;
            vertical-align: top;
        }
        
        .info-table td:first-child {
            width: 15%;
            font-weight: bold;
        }
        
        .info-table td:nth-child(2) {
            width: 35%;
        }
        
        .info-table td:nth-child(3) {
            width: 15%;
            font-weight: bold;
        }
        
        .info-table td:nth-child(4) {
            width: 35%;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-size: 9px;
        }
        
        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .center {
            text-align: center;
        }
        
        .footer {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-size: 10px;
        }
        
        .signature-section {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-box {
            display: inline-block;
            text-align: center;
            margin-left: 50px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            width: 150px;
            margin: 40px auto 5px auto;
        }
        
        @page {
            margin: 1in;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ABSENSI SISWA</h1>
        <h2>Sistem Absensi Sekolah</h2>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>Periode</td>
                <td>: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</td>
                <td>Kelas</td>
                <td>: {{ $filterInfo['class_name'] }}</td>
            </tr>
            <tr>
                <td>Siswa</td>
                <td>: {{ $filterInfo['student_name'] }}</td>
                <td>Tanggal Cetak</td>
                <td>: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%">No</th>
                <th style="width: 10%">Tanggal</th>
                <th style="width: 12%">NISN</th>
                <th style="width: 20%">Nama Siswa</th>
                <th style="width: 10%">Kelas</th>
                <th style="width: 8%">Clock In</th>
                <th style="width: 8%">Clock Out</th>
                <th style="width: 10%">Status</th>
                <th style="width: 18%">Keterangan</th>
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
                <td colspan="9" class="center" style="padding: 15px;">
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

    <div class="signature-section">
        <div class="signature-box">
            <div>Mengetahui,</div>
            <div class="signature-line"></div>
            <div>Kepala Sekolah</div>
        </div>
    </div>
</body>
</html>