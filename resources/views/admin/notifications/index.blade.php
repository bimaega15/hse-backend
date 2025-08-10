@extends('admin.layouts')

@section('title', 'Notifications')

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Notifications</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">System</a></li>
                    <li class="breadcrumb-item active">Notifications</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card border-0 shadow-lg overflow-hidden">
                        <div class="card-body p-0">
                            <!-- Coming Soon Container -->
                            <div class="coming-soon-container">
                                <!-- Background Decoration -->
                                <div class="bg-decoration">
                                    <div class="shape shape-1"></div>
                                    <div class="shape shape-2"></div>
                                    <div class="shape shape-3"></div>
                                    <div class="floating-icons">
                                        <i class="ri-notification-3-line icon-float icon-1"></i>
                                        <i class="ri-bell-line icon-float icon-2"></i>
                                        <i class="ri-message-line icon-float icon-3"></i>
                                        <i class="ri-mail-line icon-float icon-4"></i>
                                        <i class="ri-alarm-line icon-float icon-5"></i>
                                        <i class="ri-calendar-line icon-float icon-6"></i>
                                    </div>
                                </div>

                                <!-- Main Content -->
                                <div class="content-wrapper">
                                    <div class="text-center">
                                        <!-- Main Icon -->
                                        <div class="main-icon-container mb-4">
                                            <div class="pulse-ring"></div>
                                            <div class="pulse-ring delay-1"></div>
                                            <div class="pulse-ring delay-2"></div>
                                            <i class="ri-notification-3-fill main-icon"></i>
                                        </div>

                                        <!-- Title -->
                                        <h1 class="coming-soon-title mb-3">
                                            Notifications Center
                                        </h1>
                                        <h2 class="coming-soon-subtitle mb-4">
                                            Coming Soon
                                        </h2>

                                        <!-- Description -->
                                        <p class="lead text-muted mb-5 mx-auto description-text">
                                            We're crafting an advanced notification system that will keep you informed about
                                            reports, observations, user activities, and system updates in real-time.
                                        </p>

                                        <!-- Features Preview -->
                                        <div class="row features-preview mb-5">
                                            <div class="col-md-4 mb-4">
                                                <div class="feature-card">
                                                    <div class="feature-icon">
                                                        <i class="ri-alarm-warning-line"></i>
                                                    </div>
                                                    <h5 class="feature-title">Real-time Alerts</h5>
                                                    <p class="feature-desc">Instant notifications for critical safety
                                                        incidents and reports</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-4">
                                                <div class="feature-card">
                                                    <div class="feature-icon">
                                                        <i class="ri-settings-3-line"></i>
                                                    </div>
                                                    <h5 class="feature-title">Smart Filtering</h5>
                                                    <p class="feature-desc">Customizable notification preferences and
                                                        priority levels</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-4">
                                                <div class="feature-card">
                                                    <div class="feature-icon">
                                                        <i class="ri-team-line"></i>
                                                    </div>
                                                    <h5 class="feature-title">Team Updates</h5>
                                                    <p class="feature-desc">Stay connected with your HSE team activities and
                                                        assignments</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Progress Indicator -->
                                        <div class="progress-container mb-4">
                                            <div class="progress-label">Development Progress</div>
                                            <div class="progress-bar-container">
                                                <div class="progress-bar">
                                                    <div class="progress-fill"></div>
                                                </div>
                                                <span class="progress-percentage">75%</span>
                                            </div>
                                        </div>

                                        <!-- Call to Action -->
                                        <div class="cta-section">
                                            <button class="btn btn-primary btn-lg me-3 notify-btn"
                                                onclick="enableNotifications()">
                                                <i class="ri-notification-3-line me-2"></i>
                                                Get Notified When Ready
                                            </button>
                                            <a href="{{ route('admin.dashboard.index') }}"
                                                class="btn btn-outline-secondary btn-lg">
                                                <i class="ri-arrow-left-line me-2"></i>
                                                Back to Dashboard
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .coming-soon-container {
            position: relative;
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .bg-decoration {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            width: 200px;
            height: 200px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 150px;
            height: 150px;
            top: 20%;
            right: 15%;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .floating-icons {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .icon-float {
            position: absolute;
            color: rgba(255, 255, 255, 0.2);
            font-size: 2rem;
            animation: iconFloat 8s ease-in-out infinite;
        }

        .icon-1 {
            top: 15%;
            left: 80%;
            animation-delay: 0s;
        }

        .icon-2 {
            top: 60%;
            left: 10%;
            animation-delay: 1s;
        }

        .icon-3 {
            top: 30%;
            left: 5%;
            animation-delay: 2s;
        }

        .icon-4 {
            top: 70%;
            right: 10%;
            animation-delay: 3s;
        }

        .icon-5 {
            bottom: 15%;
            right: 20%;
            animation-delay: 4s;
        }

        .icon-6 {
            top: 45%;
            right: 5%;
            animation-delay: 5s;
        }

        .content-wrapper {
            position: relative;
            z-index: 10;
            padding: 4rem 2rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .main-icon-container {
            position: relative;
            display: inline-block;
        }

        .pulse-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 120px;
            border: 3px solid #667eea;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .pulse-ring.delay-1 {
            animation-delay: 0.5s;
        }

        .pulse-ring.delay-2 {
            animation-delay: 1s;
        }

        .main-icon {
            position: relative;
            z-index: 5;
            font-size: 4rem;
            color: #667eea;
            background: #fff;
            padding: 1.5rem;
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            animation: bounce 2s infinite;
        }

        .coming-soon-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .coming-soon-subtitle {
            font-size: 2rem;
            font-weight: 300;
            color: #6c757d;
            position: relative;
        }

        .coming-soon-subtitle::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        .description-text {
            max-width: 600px;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .features-preview {
            max-width: 900px;
            margin: 0 auto;
        }

        .feature-card {
            background: #fff;
            border-radius: 15px;
            padding: 2rem 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .feature-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .feature-desc {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .progress-container {
            max-width: 400px;
            margin: 0 auto;
        }

        .progress-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        .progress-bar-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .progress-bar {
            flex: 1;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 75%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
            animation: progressAnimation 3s ease-in-out;
        }

        .progress-percentage {
            font-weight: 600;
            color: #667eea;
        }

        .cta-section {
            margin-top: 2rem;
        }

        .notify-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .notify-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes iconFloat {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            25% {
                transform: translateY(-10px) rotate(90deg);
            }

            50% {
                transform: translateY(-5px) rotate(180deg);
            }

            75% {
                transform: translateY(-15px) rotate(270deg);
            }
        }

        @keyframes pulse {
            0% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -50%) scale(1.5);
                opacity: 0;
            }
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        @keyframes progressAnimation {
            0% {
                width: 0%;
            }

            100% {
                width: 75%;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .content-wrapper {
                margin: 1rem;
                padding: 2rem 1rem;
            }

            .coming-soon-title {
                font-size: 2rem;
            }

            .coming-soon-subtitle {
                font-size: 1.5rem;
            }

            .main-icon {
                font-size: 3rem;
                padding: 1rem;
            }

            .pulse-ring {
                width: 100px;
                height: 100px;
            }

            .description-text {
                font-size: 1rem;
            }

            .feature-card {
                padding: 1.5rem 1rem;
            }

            .cta-section .btn {
                display: block;
                width: 100%;
                margin-bottom: 1rem;
            }

            .shape {
                display: none;
            }

            .icon-float {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .coming-soon-container {
                min-height: 70vh;
            }

            .coming-soon-title {
                font-size: 1.75rem;
            }

            .coming-soon-subtitle {
                font-size: 1.25rem;
            }

            .main-icon {
                font-size: 2.5rem;
                padding: 0.75rem;
            }

            .pulse-ring {
                width: 80px;
                height: 80px;
            }
        }
    </style>

    <script>
        function enableNotifications() {
            Swal.fire({
                title: 'Get Notified!',
                text: 'We\'ll let you know as soon as the notification system is ready!',
                icon: 'success',
                confirmButtonText: 'Awesome!',
                confirmButtonColor: '#667eea',
                showConfirmButton: true,
                timer: 3000
            });
        }

        // Add some interactive animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Animate feature cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                    }
                });
            }, observerOptions);

            // Observe feature cards
            document.querySelectorAll('.feature-card').forEach(card => {
                observer.observe(card);
            });
        });

        // Add CSS for fadeInUp animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
@endsection
