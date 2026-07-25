<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'Report' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        h2 { text-align: center; margin-bottom: 3px; font-size: 16px; }
        h4 { text-align: center; margin: 5px 0 3px; color: #666; font-weight: normal; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th { background: #f0f0f0; font-weight: bold; text-align: left; padding: 5px 4px; border: 1px solid #ddd; font-size: 9px; }
        td { padding: 4px; border: 1px solid #ddd; font-size: 9px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .summary-row { background: #fafafa; }
        .footer { text-align: center; margin-top: 8px; font-size: 8px; color: #999; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <h4>{{ $subtitle ?? '' }}</h4>
    @yield('content')
    <div class="footer">Generated on {{ now()->format('d-m-Y H:i:s') }}</div>
</body>
</html>
