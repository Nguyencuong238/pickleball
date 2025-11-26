<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cấu Hình Giải Đấu - Hệ Thống Quản Lý Giải Đấu</title>
    <link rel="icon" href="{{asset('assets/images/logo.jpeg')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/tournament-styles.css') }}">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- jQuery (Required for Toastr) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Page-specific styles */
        .tournament-header-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: var(--radius-xl);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .tournament-header-banner::before {
            content: '🏆';
            position: absolute;
            font-size: 12rem;
            opacity: 0.1;
            right: -2rem;
            bottom: -3rem;
        }

        .tournament-header-content {
            position: relative;
            z-index: 1;
        }

        .tournament-header-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .tournament-header-meta {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .header-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            opacity: 0.95;
        }

        /* Tabs */
        .config-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: var(--bg-white);
            padding: 0.5rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            overflow-x: auto;
        }

        .config-tab {
            flex: 1;
            min-width: 150px;
            padding: 1rem 1.5rem;
            background: transparent;
            border: none;
            border-radius: var(--radius-lg);
            cursor: pointer;
            font-weight: 600;
            color: var(--text-secondary);
            transition: all var(--transition);
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .config-tab:hover {
            background: var(--bg-light);
        }

        .config-tab.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: var(--shadow-md);
        }

        .tab-pane {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .tab-pane.active {
            display: block;
        }

        /* Step Indicator */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            padding: 0 2rem;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 10%;
            right: 10%;
            height: 3px;
            background: var(--border-color);
            z-index: -1;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            background: transparent;
            max-width: 200px;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-white);
            border: 3px solid var(--border-color);
            color: var(--text-light);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 0.75rem;
            transition: all var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-color: transparent;
            box-shadow: var(--shadow-lg);
            transform: scale(1.15);
        }

        .step.completed .step-circle {
            background: var(--accent-green);
            color: white;
            border-color: transparent;
        }

        .step.completed .step-circle::after {
            content: '✓';
        }

        .step-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .step.active .step-label {
            color: var(--primary-color);
            font-weight: 700;
        }

        /* Content Items */
        .content-item {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
            transition: all var(--transition);
        }

        .content-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .content-item h4 {
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .content-item p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        /* Item Lists */
        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .item-card {
            background: var(--bg-white);
            padding: 1.25rem;
            border-radius: var(--radius-lg);
            border: 2px solid var(--border-color);
            text-align: center;
            cursor: pointer;
            transition: all var(--transition);
        }

        .item-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .item-card.selected {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-color: transparent;
            box-shadow: var(--shadow-md);
        }

        .item-card strong {
            display: block;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .item-card p {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        /* Bracket */
        .bracket-container {
            background: var(--bg-light);
            padding: 2rem;
            border-radius: var(--radius-xl);
            overflow-x: auto;
            margin-top: 2rem;
        }

        .bracket-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            min-width: 1000px;
        }

        .bracket-column h4 {
            text-align: center;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .bracket-match {
            background: var(--bg-white);
            padding: 1rem;
            border-radius: var(--radius-lg);
            border: 2px solid var(--border-color);
            margin-bottom: 1rem;
        }

        .bracket-player {
            padding: 0.875rem;
            background: var(--bg-light);
            border-radius: var(--radius-md);
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all var(--transition);
        }

        .bracket-player:hover {
            transform: translateX(3px);
        }

        .bracket-player:last-child {
            margin-bottom: 0;
        }

        .bracket-player.winner {
            background: linear-gradient(135deg, var(--accent-green), #22C55E);
            color: white;
            font-weight: 700;
        }

        /* Athlete List */
        .athlete-list {
            margin-top: 1.5rem;
        }

        .athlete-item {
            background: var(--bg-white);
            padding: 1.25rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 2px solid var(--border-color);
            transition: all var(--transition);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .athlete-item:hover {
            border-color: var(--primary-color);
            transform: translateX(3px);
            box-shadow: var(--shadow-md);
        }

        .athlete-info {
            flex: 1;
            min-width: 250px;
        }

        .athlete-name {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .athlete-details {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .athlete-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* Group Cards */
        .group-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .group-card {
            background: var(--bg-white);
            border-radius: var(--radius-xl);
            border: 2px solid var(--border-color);
            overflow: hidden;
            transition: all var(--transition);
        }

        .group-card:hover {
            border-color: var(--primary-color);
            box-shadow: var(--shadow-lg);
        }

        .group-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.25rem;
            text-align: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .group-players {
            list-style: none;
            padding: 1.25rem;
        }

        .group-players li {
            padding: 1rem;
            background: var(--bg-light);
            margin-bottom: 0.75rem;
            border-radius: var(--radius-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all var(--transition);
        }

        .group-players li:hover {
            background: rgba(0, 217, 181, 0.1);
            transform: translateX(3px);
        }

        .group-players li:last-child {
            margin-bottom: 0;
        }

        /* Match Items */
        .match-list {
            margin-top: 1.5rem;
        }

        .match-item {
            background: var(--bg-white);
            padding: 1.5rem;
            border-radius: var(--radius-xl);
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition);
        }

        .match-item:hover {
            box-shadow: var(--shadow-lg);
        }

        .match-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--bg-light);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .match-info {
            flex: 1;
            min-width: 250px;
        }

        .match-title {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .match-details {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.8;
        }

        .match-players {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 1.5rem;
            align-items: center;
            margin-top: 1rem;
        }

        .player-side {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
        }

        .player-name {
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
            font-size: 1.05rem;
        }

        .score-input {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .score-input input {
            width: 70px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 700;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
        }

        .vs-divider {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.75rem;
        }

        /* Rankings Table */
        .rankings-table {
            width: 100%;
            margin-top: 1.5rem;
            border-collapse: separate;
            border-spacing: 0 0.75rem;
        }

        .rankings-table thead tr {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .rankings-table th {
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 700;
            white-space: nowrap;
            font-size: 0.95rem;
        }

        .rankings-table th:first-child {
            border-radius: var(--radius-lg) 0 0 var(--radius-lg);
        }

        .rankings-table th:last-child {
            border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
        }

        .rankings-table tbody tr {
            background: var(--bg-white);
            transition: all var(--transition);
        }

        .rankings-table tbody tr:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .rankings-table td {
            padding: 1rem 1.25rem;
            white-space: nowrap;
        }

        .rankings-table tbody tr td:first-child {
            border-radius: var(--radius-lg) 0 0 var(--radius-lg);
        }

        .rankings-table tbody tr td:last-child {
            border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
        }

        .rank-medal {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-weight: 700;
            color: white;
            font-size: 1rem;
        }

        .rank-1 {
            background: linear-gradient(135deg, #FFD700, #FFA500);
        }

        .rank-2 {
            background: linear-gradient(135deg, #C0C0C0, #808080);
        }

        .rank-3 {
            background: linear-gradient(135deg, #CD7F32, #8B4513);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 1rem 0;
        }

        .checkbox-group input[type="checkbox"] {
            width: 22px;
            height: 22px;
            margin-right: 0.75rem;
            cursor: pointer;
            accent-color: var(--primary-color);
        }

        .checkbox-group label {
            cursor: pointer;
            user-select: none;
            font-weight: 500;
        }

        .btn-logout:hover {
            background: var(--status-danger);
            color: #fff;
        }

        .sidebar-brand {
            text-decoration: none;
        }

        .modal-content {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .modal-content::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }

        .sidebar-header {
            justify-content: center;
            padding: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .tournament-header-title {
                font-size: 1.5rem;
            }

            .config-tabs {
                overflow-x: scroll;
            }

            .step-indicator {
                padding: 0 1rem;
            }

            .step-label {
                font-size: 0.75rem;
            }

            .bracket-grid {
                grid-template-columns: 1fr;
            }

            .match-players {
                grid-template-columns: 1fr;
            }

            .vs-divider {
                display: none;
            }

            .item-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .group-grid {
                grid-template-columns: 1fr;
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        .status-live {
            animation: pulse 1.5s infinite;
        }
    </style>
    @yield('css')
</head>

<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="/" class="sidebar-brand">
                    <img src="{{ asset('assets/images/logo.jpeg') }}" alt="OnePickleball" width="80px">
                </a>
                {{-- <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <span>☰</span>
                </button> --}}
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Tổng Quan</div>
                    <a href="{{ route('homeyard.overview') }}" class="nav-item">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Tổng quan</span>
                    </a>

                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Quản Lý Giải Đâu</div>

                    <a href="{{ route('homeyard.tournaments') }}" class="nav-item">
                        <span class="nav-icon">🏆</span>
                        <span class="nav-text">Giải đấu</span>
                        {{-- <span class="nav-badge">{{ $tournamentsCount ?? 0 }}</span> --}}
                    </a>

                    {{-- <a href="{{ route('homeyard.dashboard') }}" class="nav-item">
                        <span class="nav-icon">⚙️</span>
                        <span class="nav-text">Cấu hình giải</span>
                    </a> --}}

                    <a href="{{ route('homeyard.matches') }}" class="nav-item">
                        <span class="nav-icon">🎾</span>
                        <span class="nav-text">Trận đấu</span>
                        {{-- <span class="nav-badge">{{ $matchesCount ?? 0 }}</span> --}}
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Quản lý Sân</div>
                    <a href="{{ route('homeyard.stadiums.index') }}" class="nav-item">
                        <span class="nav-icon">🏢</span>
                        <span class="nav-text">Quản Lý Cụm Sân</span>
                    </a>
                    <a href="{{ route('homeyard.courts') }}" class="nav-item">
                        <span class="nav-icon">🏟️</span>
                        <span class="nav-text">Quản Lý Sân</span>
                    </a>
                    <a href="{{ route('homeyard.bookings') }}" class="nav-item">
                        <span class="nav-icon">📅</span>
                        <span class="nav-text">Đặt Sân</span>
                    </a>
                    <a href="{{ route('homeyard.socials.index') }}" class="nav-item">
                        <span class="nav-icon">🎾</span>
                        <span class="nav-text">Quản lý thi đấu Social</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Vận động viên</div>

                    <a href="{{ route('homeyard.athletes') }}" class="nav-item">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">Vận động viên</span>
                        {{-- <span class="nav-badge">248</span> --}}
                    </a>
                    <a href="{{ route('homeyard.rankings') }}" class="nav-item">
                        <span class="nav-icon">🏅</span>
                        <span class="nav-text">Xếp hạng</span>
                    </a>

                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Hệ Thống</div>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">⚙️</span>
                        <span class="nav-text">Cài đặt</span>
                    </a>
                    <form method="POST" action="{{route('logout')}}">
                        @csrf                     
                        <a href="#" class="nav-item btn-logout"
                            onclick="event.preventDefault();this.closest('form').submit();">
                            <span class="nav-icon">↪️</span>
                            <span class="nav-text">Đăng xuất</span> 
                        </a>
                    </form>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        @yield('content')
    </div>
    @yield('js')
    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        }

        // Show config tab
        function showConfigTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-pane').forEach(tab => {
                tab.classList.remove('active');
            });

            // Remove active from all buttons
            document.querySelectorAll('.config-tab').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            const tabElement = document.getElementById(tabName);
            if (tabElement) {
                tabElement.classList.add('active');
            }

            // Add active to the corresponding button
            const buttons = document.querySelectorAll('.config-tab');
            buttons.forEach(btn => {
                if (btn.getAttribute('onclick') === `showConfigTab('${tabName}')`) {
                    btn.classList.add('active');
                }
            });

            // Scroll to top
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Initialize active tab from session
        function initializeActiveTab() {
            @if(session('activeTab'))
                showConfigTab('{{ session('activeTab') }}');
            @endif
        }

        // Call on page load
        document.addEventListener('DOMContentLoaded', initializeActiveTab);

        // Step navigation - Next Step
        function nextStep(stepNumber) {
            const steps = document.querySelectorAll('.step');
            const cards = document.querySelectorAll('#config .card');
            
            steps.forEach((step, index) => {
                step.classList.remove('active', 'completed');
                if (index + 1 < stepNumber) {
                    step.classList.add('completed');
                } else if (index + 1 === stepNumber) {
                    step.classList.add('active');
                }
            });

            // Hide all cards and show only the active step cards
            cards.forEach((card, index) => {
                card.style.display = 'none';
            });

            // Show cards for the current step
            if (stepNumber === 1) {
                if (cards[0]) cards[0].style.display = 'block'; // Step 1 card
            } else if (stepNumber === 2) {
                if (cards[1]) cards[1].style.display = 'block'; // Step 2 card
            } else if (stepNumber === 3) {
                if (cards[2]) cards[2].style.display = 'block'; // Step 3 - Round card
                if (cards[3]) cards[3].style.display = 'block'; // Step 3 - Court card
            } else if (stepNumber === 4) {
                if (cards[4]) cards[4].style.display = 'block'; // Step 4 card
            }

            window.scrollTo({
                top: 300,
                behavior: 'smooth'
            });
        }

        // Step navigation - Previous Step
        function prevStep(stepNumber) {
            const steps = document.querySelectorAll('.step');
            const cards = document.querySelectorAll('#config .card');
            
            steps.forEach((step, index) => {
                step.classList.remove('active', 'completed');
                if (index + 1 < stepNumber) {
                    step.classList.add('completed');
                } else if (index + 1 === stepNumber) {
                    step.classList.add('active');
                }
            });

            // Hide all cards and show only the active step cards
            cards.forEach((card, index) => {
                card.style.display = 'none';
            });

            // Show cards for the current step
            if (stepNumber === 1) {
                if (cards[0]) cards[0].style.display = 'block'; // Step 1 card
            } else if (stepNumber === 2) {
                if (cards[1]) cards[1].style.display = 'block'; // Step 2 card
            } else if (stepNumber === 3) {
                if (cards[2]) cards[2].style.display = 'block'; // Step 3 - Round card
                if (cards[3]) cards[3].style.display = 'block'; // Step 3 - Court card
            } else if (stepNumber === 4) {
                if (cards[4]) cards[4].style.display = 'block'; // Step 4 card
            }

            window.scrollTo({
                top: 300,
                behavior: 'smooth'
            });
        }


        @if(session('step')) 
            setTimeout(function() {
                nextStep({{ session('step') }});
            }, 500);
        @endif

        // Store added content
        let addedContents = JSON.parse(localStorage.getItem('addedContents') || '[]');

        // Add content
        function addContent() {
            const name = document.getElementById('contentName').value;
            const type = document.getElementById('contentType').value;
            const age = document.getElementById('contentAge').value;
            const maxPlayers = document.getElementById('contentMaxPlayers').value;
            const prize = document.getElementById('contentPrize').value;

            if (!name || !maxPlayers) {
                alert('Vui lòng điền đầy đủ thông tin!');
                return;
            }

            // Create unique ID for the content
            const contentId = Date.now();
            
            // Add to storage
            const newContentObj = {
                id: contentId,
                name: name,
                type: type,
                age: age,
                maxPlayers: maxPlayers,
                prize: prize
            };
            addedContents.push(newContentObj);
            localStorage.setItem('addedContents', JSON.stringify(addedContents));

            const contentList = document.getElementById('contentList');
            const newContent = document.createElement('div');
            newContent.className = 'content-item';
            newContent.setAttribute('data-content-id', contentId);
            newContent.innerHTML = `
                <h4>${name}</h4>
                <p><strong>Loại:</strong> ${type} | <strong>Độ tuổi:</strong> ${age} | <strong>Số VĐV:</strong> ${maxPlayers} | <strong>Giải thưởng:</strong> ${parseInt(prize).toLocaleString('vi-VN')} VNĐ</p>
                <button class="btn btn-secondary btn-sm">✏️ Chỉnh sửa</button>
                <button class="btn btn-danger btn-sm" onclick="removeContent(${contentId})">🗑️ Xóa</button>
            `;
            contentList.appendChild(newContent);

            // Update Step 4 dropdown
            updateStep4Dropdown();

            // Clear
            document.getElementById('contentName').value = '';
            document.getElementById('contentMaxPlayers').value = '';
            document.getElementById('contentPrize').value = '';
        }

        // Remove content
        function removeContent(contentId) {
            // Remove from storage
            addedContents = addedContents.filter(c => c.id !== contentId);
            localStorage.setItem('addedContents', JSON.stringify(addedContents));

            // Remove from DOM
            const contentItem = document.querySelector(`[data-content-id="${contentId}"]`);
            if (contentItem) {
                contentItem.remove();
            }

            // Update Step 4 dropdown
            updateStep4Dropdown();
        }

        // Update Step 4 dropdown with added content
        function updateStep4Dropdown() {
            const select = document.getElementById('tournamentCategorySelect');
            if (!select) return;

            // Keep the first option
            const firstOption = select.querySelector('option[value=""]');
            
            // Remove all options except the first one
            const allOptions = select.querySelectorAll('option');
            allOptions.forEach((option, index) => {
                if (index > 0) {
                    option.remove();
                }
            });

            // Add content items as options
            addedContents.forEach(content => {
                const option = document.createElement('option');
                option.value = content.id;
                option.textContent = content.name;
                select.appendChild(option);
            });
        }

        // Initialize Step 4 dropdown on page load
        function initializeStep4Dropdown() {
            setTimeout(() => {
                addedContents = JSON.parse(localStorage.getItem('addedContents') || '[]');
                updateStep4Dropdown();
            }, 100);
        }

        document.addEventListener('DOMContentLoaded', initializeStep4Dropdown);

        // Add round
        function addRound() {
            const name = document.getElementById('roundName').value;
            const date = document.getElementById('roundDate').value;
            const time = document.getElementById('roundTime').value;

            if (!name || !date || !time) {
                alert('Vui lòng điền đầy đủ thông tin!');
                return;
            }

            alert('Đã thêm vòng đấu: ' + name);

            // Clear
            document.getElementById('roundName').value = '';
            document.getElementById('roundDate').value = '';
            document.getElementById('roundTime').value = '';
        }

        // Add court
        function addCourt() {
            const name = document.getElementById('courtName').value;

            if (!name) {
                alert('Vui lòng nhập tên sân!');
                return;
            }

            alert('Đã thêm sân: ' + name);

            // Clear
            document.getElementById('courtName').value = '';
        }

        // Filter athletes by category
        function filterAthletesByCategory() {
            const categorySelect = document.getElementById('tournamentCategorySelect');
            const selectedCategoryId = categorySelect.value;
            const athleteRows = document.querySelectorAll('.athlete-row');

            athleteRows.forEach(row => {
                const rowCategoryId = row.getAttribute('data-category-id');
                
                // Show row if no category is selected or if the row matches the selected category
                if (selectedCategoryId === '' || rowCategoryId === selectedCategoryId) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Court card selection with highlight
        document.addEventListener('click', (e) => {
            const courtCard = e.target.closest('.court-card');
            if (courtCard) {
                courtCard.classList.toggle('selected');
                // Store selected courts
                storeSelectedCourts();
            }
        });

        // Store selected courts
        function storeSelectedCourts() {
            const selectedCourts = [];
            document.querySelectorAll('.court-card.selected').forEach(card => {
                selectedCourts.push(card.getAttribute('data-court-id'));
            });
            localStorage.setItem('selectedCourts', JSON.stringify(selectedCourts));
        }

        // Restore selected courts on page load
        function restoreSelectedCourts() {
            const selectedCourts = JSON.parse(localStorage.getItem('selectedCourts') || '[]');
            selectedCourts.forEach(courtId => {
                const courtCard = document.querySelector(`.court-card[data-court-id="${courtId}"]`);
                if (courtCard) {
                    courtCard.classList.add('selected');
                }
            });
        }

        // Restore on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', restoreSelectedCourts);
        } else {
            restoreSelectedCourts();
        }

        // Other item card selection (non-court cards)
        document.addEventListener('click', (e) => {
            const itemCard = e.target.closest('.item-card:not(.court-card)');
            if (itemCard) {
                itemCard.classList.toggle('selected');
            }
        });

        // Set active nav item based on current URL
        function setActiveNavItem() {
            const currentPath = window.location.pathname;
            const navItems = document.querySelectorAll('.nav-item');

            navItems.forEach(item => {
                // Remove active class from all items
                item.classList.remove('active');

                // Get the href attribute
                const href = item.getAttribute('href');

                // Check if current path matches the href
                if (href && (currentPath === href || currentPath.includes(href.split('/').pop()))) {
                    item.classList.add('active');
                }
            });
        }

        // Call on page load
        setActiveNavItem();

        // Initialize step view
        function initializeSteps() {
            const cards = document.querySelectorAll('#config .card');
            cards.forEach((card, index) => {
                // Hide all cards except the first one
                if (index > 0) {
                    card.style.display = 'none';
                }
            });
        }

        // Initialize
        if (window.innerWidth <= 1024) {
            toggleSidebar();
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth <= 1024) {
                document.getElementById('sidebar').classList.add('collapsed');
                document.getElementById('mainContent').classList.add('sidebar-collapsed');
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initializeSteps);

        console.log('Tournament Config Dashboard v2 Loaded');
    </script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Configure Toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "3000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Display session messages when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif

            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif

            @if(session('warning'))
                toastr.warning('{{ session('warning') }}');
            @endif

            @if(session('info'))
                toastr.info('{{ session('info') }}');
            @endif

            // Handle validation errors
            @if($errors->any())
                @foreach($errors->all() as $error)
                    toastr.error('{{ $error }}');
                @endforeach
            @endif
        });
    </script>
</body>

</html>
