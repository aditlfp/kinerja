<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jadwal Kerja {{ $month }}</title>
    <style>
        @page { size: A4 landscape; margin: 4mm; }
        body { font-family: Arial, sans-serif; font-size: 7px; margin: 0; padding: 0; }
        h3 { margin: 0 0 2px; font-size: 11px; text-transform: uppercase; }
        .meta { font-size: 7px; color: #555; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; page-break-inside: avoid; }
        th, td { border: 1px solid #333; padding: 1.5px 1px; text-align: center; font-size: 6.5px; line-height: 1; }
        th { background: #e5e7eb; font-weight: bold; }
        th.user-col, td.user-col {
            width: 145px;
            text-align: left;
            padding: 1.5px 3px;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: keep-all;
            line-height: 1.05;
            font-size: 6.5px;
        }
        .pagi { background: #fef08a; font-weight: bold; }
        .siang { background: #bfdbfe; font-weight: bold; }
        .sore { background: #fed7aa; font-weight: bold; }
        .malam { background: #ddd6fe; font-weight: bold; }
        .off { background: #fecaca; color: #991b1b; font-weight: bold; }
        .legend { margin-top: 4px; font-size: 7px; }
        .legend span { display: inline-block; padding: 1px 3px; margin-right: 3px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h3>Jadwal Kerja Karyawan</h3>
    <div class="meta">Periode: {{ $month }} | Client: {{ $clientName ?? (Auth::user()->kerjasama->client->name ?? '-') }}</div>

    <table>
        <thead>
            <tr>
                <th class="user-col">Nama Karyawan</th>
                @foreach ($days as $d)
                    <th>{{ $d->format('d') }}<br><small>{{ substr($d->locale('id')->isoFormat('ddd'), 0, 1) }}</small></th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $u)
                <tr>
                    <td class="user-col">{{ $u->nama_lengkap }}</td>
                    @foreach ($days as $d)
                        @php
                            $status = $schedules->get($u->id . '|' . $d->toDateString())?->status ?? 'OFF';
                            $cls = strtolower($status);
                        @endphp
                        <td class="{{ $cls }}">{{ substr($status, 0, 1) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ $days->count() + 1 }}">Tidak ada data karyawan</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="legend">
        <b>Keterangan:</b>
        <span class="pagi">P = Pagi</span>
        <span class="siang">S = Siang</span>
        <span class="sore">R = Sore</span>
        <span class="malam">M = Malam</span>
        <span class="off">O = OFF</span>
    </div>
</body>
</html>
