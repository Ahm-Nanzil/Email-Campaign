<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>Web Development Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #333333;
            --muted-text: #6c757d;
            --primary-color: #5e60ce;
            --secondary-color: #4ea8de;
            --gradient: linear-gradient(135deg, #5e60ce, #4ea8de);
            --service-bg: #f8f9fa;
            --testimonial-bg: #f0f7ff;
            --card-footer-bg: #f8f9fa;
            --divider-color: rgba(94, 96, 206, 0.2);
            --border-color: rgba(0, 0, 0, 0.1);
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-color: #121212;
                --card-bg: #1e1e1e;
                --text-color: #e0e0e0;
                --muted-text: #a0a0a0;
                --primary-color: #7a7bf7;
                --secondary-color: #5db8ee;
                --gradient: linear-gradient(135deg, #7a7bf7, #5db8ee);
                --service-bg: #2a2a2a;
                --testimonial-bg: #252536;
                --card-footer-bg: #252525;
                --divider-color: rgba(122, 123, 247, 0.2);
                --border-color: rgba(255, 255, 255, 0.1);
                --shadow-color: rgba(0, 0, 0, 0.25);
            }
        }
        
        body {
            background-color: var(--bg-color);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--text-color);
        }
        
        .logo-container {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        .logo {
            width: 120px;
            height: auto;
            max-width: 100%;
            filter: drop-shadow(0 2px 4px var(--shadow-color));
        }

        @media (max-width: 576px) { 
            .logo {
                width: 90px; 
            }
        }
        
        .card {
            max-width: 800px;
            margin: 0 auto;
            border-radius: 12px;
            box-shadow: 0 8px 24px var(--shadow-color);
            background-color: var(--card-bg);
            padding: 0;
            overflow: hidden;
            border: none;
        }
        
        .card-header {
            background: var(--gradient);
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path d="M0 50 L50 0 L100 50 L50 100 Z" fill="rgba(255,255,255,0.05)"/></svg>') repeat;
            opacity: 0.5;
        }
        
        .header-icon {
            background-color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 4px 12px var(--shadow-color);
        }
        
        .dev-icon {
            width: 50px;
            height: 50px;
        }
        
        .welcome-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            color: white;
            text-align: center;
        }
        
        .welcome-subtitle {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0;
            text-align: center;
        }
        
        .btn-primary {
            background: var(--gradient);
            border: none;
            border-radius: 30px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(78, 168, 222, 0.3);
            color: white;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(78, 168, 222, 0.4);
            color: white;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--muted-text);
        }

        .social-icons {
            width: 100%;
            text-align: center;
            margin: 15px auto;
        }
        
        .social-icons a {
            display: inline-block;
            margin: 0 5px;
        }
        
        .social-icons img {
            transition: transform 0.3s ease;
            width: 30px;
            height: 30px;
            filter: brightness(0.85);
        }
        
        @media (prefers-color-scheme: dark) {
            .social-icons img {
                filter: brightness(1.5);
            }
        }
        
        .social-icons img:hover {
            transform: scale(1.15);
        }

        .support-text {
            font-size: 14px;
            color: var(--muted-text);
        }

        .footer-logo {
            margin-top: 15px;
            height: 30px;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(to right, rgba(94, 96, 206, 0.1), var(--divider-color), rgba(94, 96, 206, 0.1));
            margin: 20px 0;
        }
        
        .content-wrapper {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 30px;
            margin: 15px;
            box-shadow: 0 2px 8px var(--shadow-color);
        }
        
        .card-footer {
            background-color: var(--card-footer-bg);
            border-top: none;
            padding: 20px;
        }
        
        a {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .greeting-btn {
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            margin: 25px auto;
            display: block;
            box-shadow: 0 4px 12px rgba(78, 168, 222, 0.3);
            cursor: pointer;
        }
        
        .greeting-btn:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(78, 168, 222, 0.4);
        }
        
        .responsive-logo {
            height: 40px; 
            width: auto;  
            max-width: 100%; 
        }
        
        @media (max-width: 576px) { 
            .responsive-logo {
                height: 30px; 
            }
        }
        
        .service-section {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin: 30px 0;
        }
        
        .service-item {
            width: 100%;
            margin-bottom: 25px;
            background-color: var(--service-bg);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px var(--shadow-color);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        @media (min-width: 768px) {
            .service-item {
                width: 48%;
            }
        }
        
        .service-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px var(--shadow-color);
        }
        
        .service-icon {
            background: var(--gradient);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .service-icon img {
            width: 25px;
            height: 25px;
            filter: brightness(0) invert(1);
        }
        
        .service-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-color);
        }
        
        .testimonial {
            background-color: var(--testimonial-bg);
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            position: relative;
            border-left: 4px solid var(--secondary-color);
        }
        
        .testimonial::before {
            content: """;
            position: absolute;
            top: 0;
            left: 10px;
            font-size: 60px;
            color: rgba(78, 168, 222, 0.2);
            line-height: 1;
        }
        
        .cta-section {
            text-align: center;
            margin: 30px 0 20px;
        }
        
        .highlight {
            background: linear-gradient(transparent 70%, var(--divider-color) 30%);
            padding: 0 3px;
        }
        
        .profile-links {
            background-color: var(--service-bg);
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            box-shadow: 0 2px 8px var(--shadow-color);
        }
        
        .profile-links a {
            display: inline-block;
            margin: 0 10px 10px;
            transition: all 0.3s ease;
        }
        
        .profile-links a:hover {
            transform: translateY(-2px);
        }
        
        .profile-links img {
            width: 30px;
            height: 30px;
            margin-right: 5px;
            vertical-align: middle;
            filter: grayscale(0.3);
        }
        
        @media (prefers-color-scheme: dark) {
            .profile-links img {
                filter: brightness(1.5) grayscale(0.3);
            }
        }
        
        .profile-links span {
            vertical-align: middle;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="logo-container">
            <img src="https://cdn-icons-png.flaticon.com/512/5968/5968204.png" alt="Nanzil Dev Logo" class="logo">
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <div class="header-icon">
                    <img src="https://cdn-icons-png.flaticon.com/512/3159/3159310.png" alt="Web Development Icon" class="dev-icon">
                </div>
                <h1 class="welcome-title">Elevate Your Online Presence</h1>
                <p class="welcome-subtitle">Professional Web Development Services by Ahm Nanzil</p>
            </div>
            
            <div class="content-wrapper">
                <p>Dear Business Owner,</p>
                
                <p>In today's digital landscape, a powerful website isn't just a nice-to-have—it's essential for business growth. 
                I specialize in creating stunning, high-performance websites that help businesses like yours <span class="highlight">attract more customers</span> 
                and <span class="highlight">drive revenue</span>.</p>
                
                <div class="divider"></div>
                
                <h3 style="color: var(--primary-color); margin-bottom: 20px;">My Web Development Services</h3>
                
                <div class="service-section">
                    <div class="service-item">
                        <div class="service-icon">
                            <img src="https://cdn-icons-png.flaticon.com/512/2920/2920277.png" alt="Responsive Design">
                        </div>
                        <h4 class="service-title">Responsive Web Design</h4>
                        <p>Beautiful, mobile-friendly websites that look perfect on any device, from smartphones to desktops.</p>
                    </div>
                    
                    <div class="service-item">
                        <div class="service-icon">
                            <img src="https://cdn-icons-png.flaticon.com/512/2535/2535533.png" alt="E-commerce">
                        </div>
                        <h4 class="service-title">E-commerce Solutions</h4>
                        <p>Powerful online stores with secure payment gateways, inventory management, and seamless checkout.</p>
                    </div>
                    
                    <div class="service-item">
                        <div class="service-icon">
                            <img src="https://cdn-icons-png.flaticon.com/512/1378/1378647.png" alt="SEO">
                        </div>
                        <h4 class="service-title">SEO Optimization</h4>
                        <p>On-page and technical SEO to improve your rankings and drive more organic traffic to your website.</p>
                    </div>
                    
                    <div class="service-item">
                        <div class="service-icon">
                            <img src="https://cdn-icons-png.flaticon.com/512/1055/1055687.png" alt="CMS">
                        </div>
                        <h4 class="service-title">Content Management</h4>
                        <p>Easy-to-use CMS solutions that give you complete control over your website's content.</p>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="testimonial">
                    <p style="font-style: italic;">"Working with Ahm Nanzil transformed our online presence completely. Our new website not only looks amazing but has increased our leads by 45% in just three months!"</p>
                    <p style="text-align: right; margin-bottom: 0;"><strong>Sarah Johnson</strong> - Marketing Director</p>
                </div>
                
                <div class="cta-section">
                    <h3 style="color: var(--primary-color); margin-bottom: 20px;">Ready to Transform Your Online Presence?</h3>
                    <p>I'm offering a <strong>free website audit and consultation</strong> for new clients. Let's discuss how I can help your business grow online.</p>
                </div>
                
                <div class="profile-links">
                    <h4 style="margin-bottom: 15px; color: var(--primary-color);">Connect With Me</h4>
                    <a href="https://github.com/Ahm-Nanzil" target="_blank">
                        <img src="https://cdn-icons-png.flaticon.com/512/733/733609.png" alt="GitHub">
                        <span>GitHub</span>
                    </a>
                    <a href="https://www.linkedin.com/in/ahmnanzil" target="_blank">
                        <img src="https://cdn-icons-png.flaticon.com/512/733/733617.png" alt="LinkedIn">
                        <span>LinkedIn</span>
                    </a>
                    <a href="https://ahmnanzil.mooo.com" target="_blank">
                        <img src="https://cdn-icons-png.flaticon.com/512/869/869636.png" alt="Portfolio">
                        <span>Portfolio</span>
                    </a>
                    <a href="https://www.fiverr.com/s/pd6X9ZE" target="_blank">
                        <img src="https://cdn-icons-png.flaticon.com/512/5968/5968850.png" alt="Fiverr">
                        <span>Fiverr</span>
                    </a>
                    <a href="https://www.upwork.com/freelancers/~0188c3e0f408323508" target="_blank">
                        <img src="https://cdn-icons-png.flaticon.com/512/5968/5968708.png" alt="Upwork">
                        <span>Upwork</span>
                    </a>
                </div>
            </div>
            
            <div style="text-align: center; margin: 10px 0 30px;">
                <button class="greeting-btn">Schedule Your Free Consultation</button>
            </div>
            
            <div class="logo-section text-center">
                <img src="https://cdn-icons-png.flaticon.com/512/5968/5968204.png" 
                     alt="Nanzil Dev Logo" 
                     class="img-fluid responsive-logo">
            </div>
            
            <div class="card-footer">
                <div class="divider"></div>
                <p style="text-align: center; margin-bottom: 15px;"><strong>Ahm Nanzil - Web Developer</strong></p>
                <p style="text-align: center; margin-bottom: 5px;">Web Development & Design Services</p>
                <p style="text-align: center; margin-bottom: 5px;">contact@ahmnanzil.mooo.com</p>
                <p style="text-align: center;"><a href="https://ahmnanzil.mooo.com">ahmnanzil.mooo.com</a></p>
                <div class="divider"></div>
                <p class="support-text text-center mb-1">Questions? I'm here to help! <a href="#">Contact me</a></p>
            </div>
        </div>
        
        <div class="footer">
            <div class="social-icons" style="text-align: center;">
                <a href="https://github.com/Ahm-Nanzil" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733609.png" alt="GitHub">
                </a>
                <a href="https://www.linkedin.com/in/ahmnanzil" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733617.png" alt="LinkedIn">
                </a>
                <a href="https://www.fiverr.com/s/pd6X9ZE" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/5968/5968850.png" alt="Fiverr">
                </a>
                <a href="https://www.upwork.com/freelancers/~0188c3e0f408323508" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/5968/5968708.png" alt="Upwork">
                </a>
            </div>
            
            <p class="mb-1">Privacy Policy | Unsubscribe | View in Browser</p>
            <p class="mb-3">Copyright © 2025 Ahm Nanzil. All rights reserved.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>