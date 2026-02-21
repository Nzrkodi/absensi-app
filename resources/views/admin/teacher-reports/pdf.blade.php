<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi Guru</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h2 {
            margin: 5px 0;
            color: #333;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 5px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.data th, table.data td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table.data th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN ABSENSI GURU</h2>
        <p>Sistem Absensi Sekolah</p>
    </div>
    
    <div class="info">
        <table>
            <tr>
                <td width="20%"><strong>Periode</strong></td>
                <td>: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</td>
            </tr>
            <tr>
                <td><strong>Guru</strong></td>
                <td>: {{ $filterInfo['teacher_name'] }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Cetak</strong></td>
                <td>: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</td>
            </tr>
        </table>
    </div>
    
    <table class="data">
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th>Tanggal</th>
                <th>Nama Guru</th>
                <th>Email</th>
                <th>Jabatan</th>
                <th class="text-center">Clock In</th>
                <th class="text-center">Clock Out</th>
                <th class="text-center">Durasi</th>
                <th class="text-center">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                <td>{{ $attendance->teacher->name }}</td>
                <td>{{ $attendance->teacher->email }}</td>
                <td>{{ $attendance->teacher->jabatan ?? '-' }}</td>
                <td class="text-center">
                    {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                </td>
                <td class="text-center">
                    {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
                </td>
                <td class="text-center">
                    {{ $attendance->getWorkDurationFormatted() }}
                </td>
                <td class="text-center">
                    @if($attendance->status == 'hadir')
                        Hadir
                    @elseif($attendance->status == 'izin')
                        Izin
                    @elseif($attendance->status == 'sakit')
                        Sakit
                    @else
                        Tidak Hadir
                    @endif
                </td>
                <td>{{ $attendance->notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Print / Save as PDF
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>
</body>
</html>
