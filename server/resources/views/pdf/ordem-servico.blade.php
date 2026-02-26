<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ccc;
        }
        td.label {
            font-weight: bold;
            width: 35%;
        }
    </style>
</head>
<body>

<h1>Ordem de Serviço</h1>

<table>
    @foreach ($campos as $label => $valor)
        <tr>
            <td class="label">{{ $label }}:</td>
            <td>{{ $valor }}</td>
        </tr>
    @endforeach
</table>

</body>
</html>