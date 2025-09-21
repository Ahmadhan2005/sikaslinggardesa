// SIKASLINGGAR JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
    }

    // Smooth Scrolling for Navigation Links
    const navLinks = document.querySelectorAll('a[href^="#"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = target.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Header Scroll Effect
    const header = document.querySelector('.header');
    let lastScrollY = window.scrollY;
    
    window.addEventListener('scroll', function() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Hide header when scrolling down, show when scrolling up
        if (currentScrollY > lastScrollY && currentScrollY > 100) {
            header.style.transform = 'translateY(-100%)';
        } else {
            header.style.transform = 'translateY(0)';
        }
        
        lastScrollY = currentScrollY;
    });

    // Hero Scroll Animation
    const heroScroll = document.querySelector('.hero-scroll');
    if (heroScroll) {
        heroScroll.addEventListener('click', function() {
            const featuresSection = document.querySelector('.features');
            if (featuresSection) {
                featuresSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    // Intersection Observer for Animation on Scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);

    // Observe elements for scroll animations
    const animatedElements = document.querySelectorAll('.feature-card, .section-header');
    animatedElements.forEach(el => {
        observer.observe(el);
    });

    // Counter Animation for Stats
    function animateCounters() {
        const counters = document.querySelectorAll('.stat-info p');
        counters.forEach(counter => {
            const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;
            
            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    counter.textContent = 'Rp ' + Math.floor(current).toLocaleString('id-ID');
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = 'Rp ' + target.toLocaleString('id-ID');
                }
            };
            
            setTimeout(updateCounter, 500);
        });
    }

    // Trigger counter animation when hero card is visible
    const heroCard = document.querySelector('.hero-card');
    if (heroCard) {
        const cardObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    cardObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        cardObserver.observe(heroCard);
    }

    // Floating Animation for Hero Card
    function floatingAnimation() {
        const heroCard = document.querySelector('.hero-card');
        if (heroCard) {
            let start = null;
            
            function animate(timestamp) {
                if (!start) start = timestamp;
                const progress = (timestamp - start) / 3000;
                
                const translateY = Math.sin(progress * Math.PI * 2) * 10;
                heroCard.style.transform = `translateY(${translateY}px)`;
                
                requestAnimationFrame(animate);
            }
            
            requestAnimationFrame(animate);
        }
    }

    // Start floating animation
    floatingAnimation();

    // Typing Effect for Hero Title
    function typeWriter() {
        const heroTitle = document.querySelector('.hero-title');
        if (heroTitle) {
            const text = heroTitle.textContent;
            const speed = 100;
            let i = 0;
            
            heroTitle.textContent = '';
            heroTitle.style.borderRight = '3px solid #4caf50';
            
            function type() {
                if (i < text.length) {
                    heroTitle.textContent += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                } else {
                    setTimeout(() => {
                        heroTitle.style.borderRight = 'none';
                    }, 1000);
                }
            }
            
            setTimeout(type, 1000);
        }
    }

    // Start typing effect
    // typeWriter();

    // Parallax Effect for Hero Background
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.hero-bg');
        
        parallaxElements.forEach(element => {
            const speed = 0.5;
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });

    // Button Hover Effects
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.05)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Feature Card Hover Effects
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-15px) scale(1.02)';
            this.style.boxShadow = '0 20px 50px rgba(0, 0, 0, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 5px 30px rgba(0, 0, 0, 0.1)';
        });
    });

    // Loading Animation
    function showLoadingAnimation() {
        const body = document.body;
        body.style.overflow = 'hidden';
        
        const loader = document.createElement('div');
        loader.className = 'loader';
        loader.innerHTML = `
            <div class="loader-content">
                <div class="spinner"></div>
                <h3>SIKASLINGGAR</h3>
                <p>Memuat...</p>
            </div>
        `;
        
        body.appendChild(loader);
        
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => {
                body.removeChild(loader);
                body.style.overflow = 'auto';
            }, 500);
        }, 1500);
    }

    // Uncomment to show loading animation
    // showLoadingAnimation();

    // Add CSS for loader dynamically
    const loaderStyles = `
        .loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2e7d32, #388e3c);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        
        .loader-content {
            text-align: center;
            color: white;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    
    const styleSheet = document.createElement('style');
    styleSheet.textContent = loaderStyles;
    document.head.appendChild(styleSheet);

    // Add additional CSS for scroll animations
    const additionalStyles = `
        .animate.feature-card {
            animation: slideInUp 0.8s ease-out;
        }
        
        .animate.section-header {
            animation: slideInUp 0.6s ease-out;
        }
        
        .header.scrolled {
            background: rgba(46, 125, 50, 0.95);
            backdrop-filter: blur(20px);
        }
        
        .header {
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .nav-menu.active {
                display: block;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: linear-gradient(135deg, #2e7d32, #388e3c);
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
                border-radius: 0 0 10px 10px;
                padding: 20px;
                animation: slideInDown 0.3s ease;
            }
            
            .nav-menu.active ul {
                flex-direction: column;
                gap: 15px;
            }
            
            .mobile-menu-toggle.active {
                transform: rotate(90deg);
            }
            
            @keyframes slideInDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        }
    `;
    
    const additionalStyleSheet = document.createElement('style');
    additionalStyleSheet.textContent = additionalStyles;
    document.head.appendChild(additionalStyleSheet);

    console.log('SIKASLINGGAR - Website berhasil dimuat!');
});