<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- The frame of the print pages (the excuse slip and the medical certificate): the page set-up, the "Print Mode" bar and
         the print dialog that opens by itself (re-audit 2026-11-13, DRY: both pages wrote it out). The page passes $heading,
         $orientation ('portrait' / 'landscape'), $width and $badgeClasses (whole class names, so the Tailwind build keeps
         them), and fills the sections print_styles and print_content. --}}
    <title>Print {{ $heading }} - {{ $requestDocument->name }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
    <style>
        @page {
            size: letter {{ $orientation }};
            margin: 0.3in;
        }

        @media print {
            body {
                background-color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-container {
                width: 100% !important;
                min-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            background-color: #f3f4f6;
            font-family: 'Arial', sans-serif;
        }

        .print-container {
            width: {{ $width }};
            margin: 10px auto;
            background: white;
            padding: 0.3in;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

@yield('print_styles')
    </style>
</head>

<body class="bg-gray-100">
    <!-- Top Action Bar (Hidden when printing) -->
    <div class="no-print bg-white border-b p-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="{{ $badgeClasses }} px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Print Mode</span>
            <h1 class="font-bold text-gray-800">{{ $heading }}</h1>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold transition-all shadow-md flex items-center gap-2">
                <i class="fas fa-print"></i> Print Now (Ctrl+P)
            </button>
            <button onclick="window.close()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-bold transition-all">
                Close Tab
            </button>
        </div>
    </div>

    <div class="print-container">
@yield('print_content')
    </div>

    <link rel="stylesheet" href="{{ asset('assets/front/vendor/font-awesome/css/all.min.css') }}">
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>
