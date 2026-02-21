<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi Guru</title>
</head>
<body>
    <div style="text-align: center; margin-bottom: 20px;">
        <h2>LAPORAN ABSENSI GURU</h2>
        <p>Sistem Absensi Sekolah</p>
    </div>
    
    <table style="margin-bottom: 20px;">
        <tr>
            <td width="150"><strong>Periode</strong></td>
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
    
    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Guru</th>
                <th>Email</th>
                <th>Jabatan</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                <td>{{ $attendance->teacher->name }}</td>
                <td>{{ $attendance->teacher->email }}</td>
                <td>{{ $attendance->teacher->jabatan ?? '-' }}</td>
                <td style="text-align: center;">
                    {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                </td>
                <td style="text-align: center;">
                    {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
                </td>
                <td style="text-align: center;">
                    {{ $attendance->getWorkDurationFormatted() }}
                </td>
                <td style="text-align: center;">
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
                <td colspan="10" style="text-align: center;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
