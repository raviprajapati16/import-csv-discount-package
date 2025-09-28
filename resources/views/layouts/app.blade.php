<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bulk Import & Image Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 15px 30px rgba(0, 0, 0, 0.15);
            --radius: 12px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: #4a5568;
            line-height: 1.6;
        }

        .navbar {
            background: var(--gradient) !important;
            box-shadow: var(--shadow);
            /* padding: 1rem 0; */
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 50px;
            padding: 0.5rem 1rem !important;
            margin: 0 0.2rem;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .card {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        h1 {
            color: var(--dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }

        h1:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--gradient);
            border-radius: 2px;
        }

        .btn-primary {
            background: var(--gradient);
            border: none;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 50px;
            /* padding: 0.75rem 2rem; */
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-outline-primary:hover i{
            color: white;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .upload-area {
            border: 2px dashed #cbd5e0;
            padding: 3rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border-radius: var(--radius);
            margin: 1.5rem 0;
            background-color: #fafbfc;
            cursor: pointer;
        }

        .upload-area:hover, .upload-area.drag-over {
            border-color: var(--primary);
            background-color: #f0f4ff;
        }

        .upload-area i {
            color: var(--primary);
        }

        .progress-container {
            background: #e2e8f0;
            border-radius: 10px;
            height: 12px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-bar {
            height: 100%;
            background: var(--gradient);
            border-radius: 10px;
            transition: width 0.4s ease;
            position: relative;
        }

        .progress-bar:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background-image: linear-gradient(-45deg, rgba(255, 255, 255, 0.2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.2) 75%, transparent 75%, transparent);
            background-size: 20px 20px;
            animation: move 1s linear infinite;
        }

        @keyframes move {
            0% { background-position: 0 0; }
            100% { background-position: 20px 0; }
        }

        .file-item {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin: 0.75rem 0;
            border-left: 4px solid var(--primary);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .file-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .file-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
        }

        .file-info {
            flex: 1;
        }

        .file-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .file-status {
            font-size: 0.85rem;
        }

        .results-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            margin: 1.5rem 0;
            box-shadow: var(--shadow);
            border-top: 4px solid var(--success);
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.25rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hidden {
            display: none;
        }

        .modal-content {
            border: none;
            border-radius: var(--radius);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-close {
            filter: invert(1);
        }

        .error-list {
            max-height: 200px;
            overflow-y: auto;
            background: #fff5f5;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .error-item {
            color: #e53e3e;
            font-size: 0.9rem;
            padding: 0.25rem 0;
            border-bottom: 1px solid #fed7d7;
        }

        .error-item:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1.25rem;
            }
            
            h1 {
                font-size: 1.75rem;
            }
            
            .upload-area {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>

<body>
    @if(!str_starts_with(Route::currentRouteName(), 'discount-test'))
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-cloud-upload-alt me-2"></i>Bulk Import System
            </a>
            <div class="navbar-nav">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-upload me-1"></i> Import & Upload
                </a>
                <a class="nav-link" href="{{ route('products.index') }}">
                    <i class="fas fa-boxes me-1"></i> Products
                </a>
            </div>
        </div>
    </nav>
    @endif

    <div class="container mt-4">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    @yield('scripts')
</body>

</html>