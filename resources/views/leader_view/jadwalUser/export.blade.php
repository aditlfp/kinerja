<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>export, {{ Auth::user()->kerjasama->client->name }}</title>
</head>
<head>
	<style>
		*,
		body {
			font-family: Arial, Helvetica, sans-serif; 
		}

		table {
			border-collapse: collapse;
			width: 100%;
		}

		table,
		th,
		td {
			border: 1px solid black;
		}

		td {
			text-align: center;
		}

		th {
			background-color: rgb(19, 110, 170);
			color: white;
		}

		tr:nth-child(even) {
			background-color: #cbd5e1;
		}
		.page-break{
			page-break-before: always;
		}
	</style>
</head>

<body>
    <main>
        <div class="title">
            <img class="hero" src="{{ $base64 }}" width="60px">
            <span class="sub-title" style="vertical-align: 20px; font-weight: bolder; font-size: 25px;">Jadwal PT. Surya
                Amanah Cendekia</span>
            <span></span>
        </div>
        <div style="text-align: center; width: 100%; font-size: 12px; text-transform: uppercase; font-weight: bold; margin-bottom: 1rem;">
            <p style="margin: 0; color: #22c55e;">DAY OF CREW</p>
            <p style="margin: 0; color: #22c55e;">{{ $kerjabagus->client->name }}</p>
            <p style="margin: 0; color: #22c55e;">PERIODE {{ \Carbon\Carbon::createFromFormat('Y-m-d', $str1)->isoFormat('MMMM Y') }}</p>
        </div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="3">No.</th>
                        <th rowspan="3">Nama lengkap</th>
                        <th colspan="{{ $totalHari + 1 }}">Tanggal</th>
                    </tr>
                    @php
                        $starte = \Carbon\Carbon::createFromFormat('Y-m-d', $str1);
                        $ende = \Carbon\Carbon::createFromFormat('Y-m-d', $end1);
                        
                        $daysAbbreviations = ['S', 'S', 'R', 'K', 'J', 'S', 'M'];
                    @endphp
                    <tr>
                        @for ($date = $starte->copy(); $date->lte($ende); $date->addDay())
                            <th class="p-2 bg-stone-300 border-r-slate-400 border-r-[1.1px]" style="padding-right: 3px; padding-left: 3px;">{{ $date->format('d') }}</th>
                        @endfor
                    </tr>
                    <tr>
                        @for ($date = $starte->copy(); $date->lte($ende); $date->addDay())
                            @php
                                $dayIndex = $date->dayOfWeek;
                            @endphp
                            @if($daysAbbreviations[$dayIndex] == 'M')
                                <th class="p-2 bg-stone-300 border-r-slate-400 border-r-[1.1px]" style="background-color: #ef4444;">{{ $daysAbbreviations[$dayIndex] }}</th>
                            @else
                                <th class="p-2 bg-stone-300 border-r-slate-400 border-r-[1.1px]">{{ $daysAbbreviations[$dayIndex] }}</th>
                            @endif
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sortedData = collect($expPDF)->sortBy('user.nama_lengkap');
                        $previousUser = null;
                        $n = 1;
                    @endphp
                    @forelse ($sortedData as $data)
                        @if ($previousUser != $data->nama_lengkap)
                            <tr>
                                <!--Valid name cuy-->
                                @php
                                    $previousUser = $data->nama_lengkap;
                                    $userAbsensi = collect($expPDF)->where('user', $data->user);
                                @endphp
                                <td>{{ $n++ }}</td>
                                <td>{{ $data->nama_lengkap }}</td>
                                @for ($date = $starte->copy(); $date->lte($ende); $date->addDay())
                                    @php
                                        $jadwal = $data->jadwalUser->firstWhere('tanggal', $date->format('Y-m-d'));
                                        $hasil = $jadwal ? $jadwal->status : 'off' ;
                                    @endphp
                                    @if ($hasil == 'M')
                                        <td style="background-color: rgb(56 189 248);">{{ $hasil }}</td>
                                    @else					
                                        <td style="background-color: rgb(251 146 60); font-size: 15px; text-transform: lowercase;">{{ $hasil }}</td>
                                    @endif
                                    @endfor
                            </tr>
                        @endif
                    @empty
                        
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>