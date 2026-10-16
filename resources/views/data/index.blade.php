<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลรีวิวหนังสือและภาพยนตร์</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 32px;
            background: #f1f5f9;
            color: #1e293b;
            font-family: Arial, Helvetica, sans-serif;
        }
        main { max-width: 1200px; margin: 0 auto; }
        h1 { margin: 0 0 8px; }
        .intro { margin: 0 0 28px; color: #64748b; }
        section {
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px #0f172a12;
        }
        .section-heading {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin-bottom: 14px;
        }
        h2 { margin: 0; font-size: 20px; }
        .count { color: #64748b; font-size: 14px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #f8fafc; color: #475569; }
        .empty { padding: 18px 12px; color: #64748b; text-align: center; }
        @media (max-width: 640px) { body { padding: 16px; } section { padding: 14px; } }
    </style>
</head>
<body>
<main>
    <h1>ข้อมูลรีวิวหนังสือและภาพยนตร์</h1>
    <p class="intro">รายการหนังสือ ภาพยนตร์ และรีวิวจากฐานข้อมูล</p>

    @foreach ($tables as $table)
        <section>
            <div class="section-heading">
                <h2>{{ $table['label'] }} ({{ $table['name'] }})</h2>
                <span class="count">{{ $table['records']->count() }} รายการ</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            @foreach ($table['columns'] as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($table['records'] as $record)
                            <tr>
                                @foreach ($table['columns'] as $column)
                                    <td>{{ $record->getAttribute($column) ?? '—' }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td class="empty" colspan="{{ max(count($table['columns']), 1) }}">ยังไม่มีข้อมูล</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach
</main>
</body>
</html>
