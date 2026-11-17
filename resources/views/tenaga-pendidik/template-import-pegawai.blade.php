<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Import Excel Pegawai</title>
</head>

<body>

    <table>
        <tr>
            <th><b>No</b></th>
            <th><b>Nama Lengkap</b></th>
            <th><b>Jabatan</b></th>
            <th><b>No HP</b></th>
            <th><b>NIP/NIPY</b></th>
            <th><b>Status Kepegawaian</b></th>
        </tr>
        <tr>
            <td>1</td>
            <td>Uzumaki Sasuke</td>
            <td>Guru</td>
            <td>081234567890</td>
            <td>0987654321</td>
            <td>
                {{ $spg->status_pegawai }}
            </td>
        </tr>
    </table>
</body>

</html>
