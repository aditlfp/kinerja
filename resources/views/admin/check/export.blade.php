<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid black;
        }
        table, th, td {
            border: 1px solid black;
        }
        td {
            text-align: start;
        }
        th {
            background-color: rgb(19, 110, 170);
            color: white;
        }
        tr:nth-child(even) {
            background-color: #cbd5e1;
        }
        .page-break {
            page-break-before: always;
        }
        .title {
            text-align: center;
            margin-top: 20px;
            font-size: 25px;
        }
        .hero {
            width: 60px;
            vertical-align: middle;
        }
        .sub-title {
            font-weight: bolder;
        }
        .date-range {
            text-align: center;
            font-size: 14px;
            margin: 16px auto 12px auto;
        }
        .client-name {
            font-weight: bold;
        }
        .table-wrapper {
            margin: 0 auto;
        }
        .border {
            border: 1px solid black;
        }
        .image-cell {
            width: 120px;
            text-align: center;
        }
    </style>
</head>
<body>
    <main>
        <!--@php-->
        <!--    $starte = \Carbon\Carbon::createFromFormat('Y-m-d', $str1);-->
        <!--    $ende = \Carbon\Carbon::createFromFormat('Y-m-d', $end1);-->
        <!--@endphp-->
        <!--<div class="title">-->
        <!--    <img class="hero" src="{{ $base64 }}" alt="Company Logo">-->
        <!--    <span class="sub-title">Laporan PT. Surya Amanah Cendekia</span>-->
        <!--</div>-->
        <div class="date-range">
            <span class="client-name">FOTO CHECK POINT</span><br>
            <span class="client-name" style="text-transform: uppercase;">{{ $user->nama_lengkap }}</span><br>
            <br>
            <span style="text-transform: uppercase;" class="client-name">PERIODE {{ $nowMonth }}</span>
        </div>
        <div class="table-wrapper">
            <table class="border">
                <thead>
                    <tr>
                        <th >No.</th>
                        <th>Foto Check Point</th>
                        <th >Nama</th>
                        <th >Tipe check</th>
                        <th >Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                    @endphp
                    @forelse ($cp as $arr)
                        @foreach ($arr->items as $item)
                            @php $img = $item->images->first()->path ?? null; @endphp
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td class="image-cell">
                                    @if ($img)
                                        <img src="{{ asset('storage/images/' . $img) }}" alt="Check Point Image" width="120px">
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ $arr->user->nama_lengkap }}</td>
                                <td style="text-align: center;">{{ $arr->type_check }}</td>
                                <td style="padding-left: 5px; border-right: 1px solid black;">{{ $item->deskripsi }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center">KOSONG</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
    <script>
        window.print();
    </script>
</body>
</html>
