<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./images/logo.png" type="image/png">
    <title>Rehan School</title>
    <style>
        /* Base styles */
        body, html {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.5;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            box-sizing: border-box;
        }
        /* Header styles */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
        }
        .logo {
            position: absolute;
            left: 1rem;
        }
        .school-name {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 1.5rem;
            font-weight: bold;
        }
        nav {
            margin-left: auto;
        }
        nav a {
            margin-left: 1rem;
            text-decoration: none;
            color: #333;
        }
        /* Hero section styles */
        .hero {
            background: linear-gradient(to right, #2563eb, #4f46e5);
            color: white;
            text-align: center;
            padding: 4rem 0;
        }
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .hero p {
            font-size: 1.25rem;
            max-width: 600px;
            margin: 0 auto 2rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #fff;
            color: #2563eb;
            text-decoration: none;
            border-radius: 0.25rem;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #f0f0f0;
        }
        /* About section styles */
        .about {
            background-color: #f3f4f6;
            padding: 4rem 0;
            text-align: center;
        }
        .features {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 2rem;
        }
        .feature {
            flex-basis: calc(33.333% - 2rem);
            margin: 1rem;
            padding: 1.5rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        /* Stats section styles */
        .stats {
            padding: 4rem 0;
            text-align: center;
        }
        .stat-grid {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .stat {
            flex-basis: calc(33.333% - 2rem);
            margin: 1rem;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2563eb;
        }
        /* Testimonials section styles */
        .testimonials {
            background-color: #f3f4f6;
            padding: 4rem 0;
            text-align: center;
        }
        .testimonial-grid {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .testimonial {
            flex-basis: calc(33.333% - 2rem);
            margin: 1rem;
            padding: 1.5rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .testimonial img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 1rem;
        }
        /* Courses section styles */
        .courses {
            padding: 4rem 0;
            text-align: center;
        }
        .course-grid {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .course {
            flex-basis: calc(33.333% - 2rem);
            margin: 1rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .course img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .course-content {
            padding: 1.5rem;
        }
        /* CTA section styles */
        .cta {
            background-color: #2563eb;
            color: white;
            text-align: center;
            padding: 4rem 0;
        }
        /* Footer styles */
        footer {
            background-color: #f3f4f6;
            padding: 1.5rem;
            text-align: center;
        }
        footer a {
            margin: 0 0.5rem;
            color: #333;
            text-decoration: none;
        }
        /* Responsive styles */
        @media (max-width: 768px) {
            .feature, .stat, .testimonial, .course {
                flex-basis: calc(50% - 2rem);
            }
        }
        @media (max-width: 480px) {
            .feature, .stat, .testimonial, .course {
                flex-basis: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <a href="#" class="logo" aria-label="Rehan School">
            <img src="./images/logo.png" alt="Rehan School Logo" width="50" height="50">
        </a>
        <div class="school-name">Rehan School</div>
        <nav>
            <a href="#about">About</a>
            <a href="#courses">Courses</a>
            <a href="#contact">Contact</a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h1>Empowering Education with Innovation</h1>
                <p>Rehan School leverages AI and the internet to provide accessible, cutting-edge education for all.</p>
                <a href="login.php" class="btn">Get Started</a>
            </div>
        </section>

        <section id="about" class="about">
            <div class="container">
                <h2>About Us</h2>
                <p>Rehan School is revolutionizing education through AI-driven learning programs, accessible online courses, and a global reach. We're committed to making quality education available to everyone, anywhere.</p>
                <div class="features">
                    <div class="feature">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        <h3>AI-Driven Learning</h3>
                        <p>Personalized learning experiences powered by artificial intelligence</p>
                    </div>
                    <div class="feature">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                        <h3>Global Reach</h3>
                        <p>Access courses from anywhere in the world, at any time</p>
                    </div>
                    <div class="feature">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <h3>Affordable Tuition</h3>
                        <p>Quality education at a fraction of traditional costs</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="stats">
            <div class="container">
                <div class="stat-grid">
                    <div class="stat">
                        <div class="stat-number">10,000+</div>
                        <p>Students Enrolled</p>
                    </div>
                    <div class="stat">
                        <div class="stat-number">50+</div>
                        <p>Countries Served</p>
                    </div>
                    <div class="stat">
                        <div class="stat-number">100+</div>
                        <p>Courses Available</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="testimonials">
            <div class="container">
                <h2>What Our Students Say</h2>
                <div class="testimonial-grid">
                    <div class="testimonial">
                        <img src="/placeholder.svg?height=100&width=100" alt="Sarah J." width="100" height="100">
                        <p>"Rehan School transformed my learning experience. The AI-driven courses are tailored to my pace and style."</p>
                        <h4>Sarah J.</h4>
                        <p>Computer Science Student</p>
                    </div>
                    <div class="testimonial">
                        <img src="/placeholder.svg?height=100&width=100" alt="Michael T." width="100" height="100">
                        <p>"As a working professional, the flexibility of Rehan School's online courses has been invaluable to my career growth."</p>
                        <h4>Michael T.</h4>
                        <p>Data Analyst</p>
                    </div>
                    <div class="testimonial">
                        <img src="/placeholder.svg?height=100&width=100" alt="Aisha M." width="100" height="100">
                        <p>"The global community at Rehan School exposed me to diverse perspectives, enhancing my learning beyond just academics."</p>
                        <h4>Aisha M.</h4>
                        <p>International Relations Major</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="courses" class="courses">
            <div class="container">
                <h2>Our Courses</h2>
                <div class="course-grid">
                    <div class="course">
                        <img src="/placeholder.svg?height=200&width=300" alt="Introduction to AI" width="300" height="200">
                        <div class="course-content">
                            <h3>Introduction to AI</h3>
                            <p>Learn the fundamentals of Artificial Intelligence and its applications.</p>
                            <a href="#" class="btn">Learn More</a>
                        </div>
                    </div>
                    <div class="course">
                        <img src="/placeholder.svg?height=200&width=300" alt="Web Development Bootcamp" width="300" height="200">
                        <div class="course-content">
                            <h3>Web Development Bootcamp</h3>
                            <p>Master full-stack web development with modern technologies.</p>
                            <a href="#" class="btn">Learn More</a>
                        </div>
                    </div>
                    <div class="course">
                        <img src="/placeholder.svg?height=200&width=300" alt="Data Science Essentials" width="300" height="200">
                        <div class="course-content">
                            <h3>Data Science Essentials</h3>
                            <p>Dive into the world of data analysis, visualization, and machine learning.</p>
                            <a href="#" class="btn">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta">
            <div class="container">
                <h2>Ready to Start Your Learning Journey?</h2>
                <p>Join Rehan School today and unlock a world of innovative, accessible education.</p>
                <a href="#" class="btn">Register Now</a>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Rehan School. All rights reserved.</p>
        <nav>
            <a href="#">Terms of Service</a>
            <a href="#">Privacy</a>
        </nav>
    </footer>
</body>
</html>