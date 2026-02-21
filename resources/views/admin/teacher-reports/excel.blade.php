<table border="1">
    <thead>
        <tr>
            <th colspan="10" style="text-align: center; font-size: 16px; font-weight: bold;">
                LAPORAN ABSENSI GURU
            </th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center;">
                Sistem Absensi Sekolah
            </th>
        </tr>
        <tr>
            <th colspan="2">Periode</th>
            <th colspan="8">{{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</th>
        </tr>
        <tr>
            <th colspan="2">Guru</th>
            <th colspan="8">{{ $filterInfo['teacher_name'] }}</th>
        </tr>
        <tr>
            <th colspan="2">Tanggal Cetak</th>
            <th colspan="8">{{ \Carbon\Carbon::now()->format('d F Y H:i') }}</th>
        </tr>
        <tr>
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
            <td>{{ $index + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
            <td>{{ $attendance->teacher->name }}</td>
            <td>{{ $attendance->teacher->email }}</td>
            <td>{{ $attendance->teacher->jabatan ?? '-' }}</td>
            <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</td>
            <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</td>
            <td>{{ $attendance->getWorkDurationFormatted() }}</td>
            <td>
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
