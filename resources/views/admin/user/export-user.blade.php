<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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
    </style>
</head>

<body>
    <main>
        <div class="title">
            <span class="sub-title" style="vertical-align: 20px; font-weight: bolder; font-size: 25px;">PT. Surya
                Amanah Cendekia</span>
            @forelse ($data->unique('kerjasama_id') as $items)
                <p style="font-weight: 700;">User Mitra {{ $items->kerjasama->client->name }}</p>
            @empty
            @endforelse
        </div>
        <div class="table-wrapper">
            <table class="border">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User ID</th>
                        <th>Nama Lengkap</th>
                        <th>User Name</th>
                        <th>Password</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $n = 1;
                    @endphp
                    @forelse ($data as $items)
                        <tr>
                            <td>{{ $n++ }}</td>
                            <td>{{ $items->id }}</td>
                            <td style="text-align: start;">{{ $items->nama_lengkap }}</td>
                            <td>{{ $items->name }}</td>
                            <td style="padding: 4px 0px;">
                                @if( $items->pw_temp )
                                    <span style="background-color: #fef08a; color: #854d0e; padding: 2px 6px; border-radius: 4px; font-weight: bold; border: 1px solid #facc15; font-family: monospace;">
                                        {{ $items->kerjasama_id == 1 ? '12345678' : $items->pw_temp }}
                                    </span>
                                @else
                                    <span style="background-color: #fef08a; color: #854d0e; padding: 2px 6px; border-radius: 4px; font-weight: bold; border: 1px solid #facc15; font-family: monospace;">
                                        {{ $items->kerjasama_id == 1 ? '12345678' : '123456' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <td colspan="31" class="text-center">Kosong</td>
                    @endforelse
                </tbody>
            </table>

        </div>

    </main>
</body>

</html>
