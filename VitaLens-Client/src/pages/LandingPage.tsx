import { Link } from 'react-router-dom';
import { useTheme } from '../context/ThemeContext';
import styles from '../styles/LandingPage.module.css';
import logo from '../assets/VitaLens-logo.png';

export function LandingPage() {
  const { isDark, toggleTheme } = useTheme();

  return (
    <div className={styles.page}>
      {/* Header */}
      <header className={styles.header}>
        <Link to="/" className={styles.logo}>
          <img src={logo} alt="VitaLens Logo" className={styles.logoImg} />
          <span>VitaLens</span>
        </Link>
        <nav className={styles.navMenu}>
          <a href="#features">Features</a>
          <a href="#how-it-works">How It Works</a>
          <a href="#dashboard">Dashboard</a>
        </nav>
        <div className={styles.authButtons}>
          <button 
            className={styles.themeToggle} 
            onClick={toggleTheme}
            title="Toggle theme"
            aria-label="Toggle dark mode"
          >
            <i className={isDark ? 'fas fa-moon' : 'fas fa-sun'}></i>
          </button>
          <Link to="/login" className={`${styles.btn} ${styles.btnOutline}`}>
            Sign In
          </Link>
          <Link to="/register" className={`${styles.btn} ${styles.btnPrimary}`}>
            Get Started
          </Link>
        </div>
      </header>

      {/* Hero */}
      <section className={styles.hero}>
        <div className={styles.container}>
          <h1>AI That Understands<br/>Your <span className={styles.highlight}>Health</span></h1>
          <p>VitaLens analyzes your medical documents, lab reports, and health habits to predict risks, extract insights, and guide your wellness journey, all in one secure, privacy-first platform.</p>
          <div className={styles.heroCta}>
            <Link to="/register" className={`${styles.btn} ${styles.btnPrimary} ${styles.btnLarge}`}>
              Get Started Now
            </Link>
            <a href="#how-it-works" className={`${styles.btn} ${styles.btnOutline} ${styles.btnLarge}`}>
              See How It Works
            </a>
          </div>
          <div className={styles.heroImage}>
            <i className="fas fa-chart-line" style={{ fontSize: '64px', opacity: 0.3 }}></i>
          </div>
        </div>
      </section>

      {/* Features */}
      <section className={styles.features} id="features">
        <div className={styles.container}>
          <h2 className={styles.sectionTitle}>Powerful Features for Your Health</h2>
          <p className={styles.sectionSubtitle}>Everything you need to take control of your health data, powered by medical-grade AI and machine learning.</p>
          <div className={styles.featuresGrid}>
            <div className={styles.featureCard}>
              <div className={styles.featureIcon}>
                <i className="fas fa-file-medical"></i>
              </div>
              <h3>Document Intelligence</h3>
              <p>Upload PDFs or photos of lab reports, prescriptions, and medical records. Our AI extracts every metric instantly.</p>
            </div>
            <div className={styles.featureCard}>
              <div className={styles.featureIcon}>
                <i className="fas fa-chart-line"></i>
              </div>
              <h3>Risk Prediction</h3>
              <p>Get personalized risk scores for diabetes, heart disease, hypertension, and kidney disease using clinical ML models.</p>
            </div>
            <div className={styles.featureCard}>
              <div className={styles.featureIcon}>
                <i className="fas fa-heartbeat"></i>
              </div>
              <h3>Habit Tracking</h3>
              <p>Log your daily habits and the AI will extract sleep, activity, and lifestyle metrics automatically.</p>
            </div>
            <div className={styles.featureCard}>
              <div className={styles.featureIcon}>
                <i className="fas fa-comments"></i>
              </div>
              <h3>Health AI Chat</h3>
              <p>Ask questions about your health history and get instant answers from an AI that knows your complete medical context.</p>
            </div>
          </div>
        </div>
      </section>

      {/* How It Works */}
      <section className={styles.howItWorks} id="how-it-works">
        <div className={styles.container}>
          <h2 className={styles.sectionTitle}>How VitaLens Works</h2>
          <p className={styles.sectionSubtitle}>Transform your health data into actionable insights in three simple steps.</p>
          <div className={styles.processGrid}>
            <div className={styles.processCard}>
              <div className={styles.stepBadge}>
                <span className={styles.stepNum}>1</span>
              </div>
              <div className={styles.stepIcon}>
                <i className="fas fa-cloud-upload-alt"></i>
              </div>
              <h3>Upload & Scan</h3>
              <p>Snap a photo or upload PDFs of your lab results, prescriptions, and medical records. Our AI instantly processes all major formats.</p>
              <div className={styles.stepHighlight}>
                <i className="fas fa-check-circle"></i>
                <span>Lab reports, prescriptions, and more</span>
              </div>
            </div>
            
            <div className={styles.processCard}>
              <div className={styles.stepBadge}>
                <span className={styles.stepNum}>2</span>
              </div>
              <div className={styles.stepIcon}>
                <i className="fas fa-brain"></i>
              </div>
              <h3>AI Analysis</h3>
              <p>Advanced machine learning extracts key health metrics, identifies patterns, and calculates personalized risk scores for multiple conditions.</p>
              <div className={styles.stepHighlight}>
                <i className="fas fa-check-circle"></i>
                <span>20+ health metrics tracked automatically</span>
              </div>
            </div>
            
            <div className={styles.processCard}>
              <div className={styles.stepBadge}>
                <span className={styles.stepNum}>3</span>
              </div>
              <div className={styles.stepIcon}>
                <i className="fas fa-chart-line"></i>
              </div>
              <h3>Get Insights</h3>
              <p>View your health dashboard, track trends over time, and chat with your personal AI health assistant for instant answers about your results.</p>
              <div className={styles.stepHighlight}>
                <i className="fas fa-check-circle"></i>
                <span>Real-time health insights & predictions</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Stats */}
      <section className={styles.statsSection}>
        <div className={styles.container}>
          <h2 className={styles.sectionTitle}>Trusted by Thousands</h2>
          <p className={styles.sectionSubtitle}>Join the growing community of health-conscious individuals using AI to stay ahead of their wellness.</p>
          <div className={styles.statsGrid}>
            <div className={styles.statCard}>
              <span className={styles.statNumber}>50K+</span>
              <div className={styles.statLabel}>Active Users</div>
              <div className={styles.statDescription}>Trust VitaLens daily</div>
            </div>
            <div className={styles.statCard}>
              <span className={styles.statNumber}>2M+</span>
              <div className={styles.statLabel}>Documents Processed</div>
              <div className={styles.statDescription}>Accurately analyzed</div>
            </div>
            <div className={styles.statCard}>
              <span className={styles.statNumber}>99.2%</span>
              <div className={styles.statLabel}>AI Accuracy</div>
              <div className={styles.statDescription}>Medical-grade precision</div>
            </div>
            <div className={styles.statCard}>
              <span className={styles.statNumber}>24/7</span>
              <div className={styles.statLabel}>Health Support</div>
              <div className={styles.statDescription}>Always available</div>
            </div>
          </div>
        </div>
      </section>

      {/* Dashboard Preview */}
      <section className={styles.dashboardSection} id="dashboard">
        <div className={styles.container}>
          <div className={styles.sectionHeader}>
            <h2 className={styles.sectionTitle}>Your Personal Health Dashboard</h2>
            <p className={styles.sectionSubtitle}>All your health insights in one place. Clear, actionable, and completely private.</p>
          </div>
          <div className={styles.dashboardContainer}>
            <div className={styles.dashboardContent}>
              <div className={styles.dashboardText}>
                <h3>Complete Health Intelligence at Your Fingertips</h3>
                <p>Track your health metrics, view risk predictions, and monitor trends over time. Your data is encrypted and never shared or sold. All AI processing happens securely in your private account.</p>
                <div className={styles.dashboardFeatures}>
                  <div className={styles.dashboardFeature}>
                    <i className="fas fa-chart-line"></i>
                    <span>Real-time health metrics tracking</span>
                  </div>
                  <div className={styles.dashboardFeature}>
                    <i className="fas fa-brain"></i>
                    <span>AI-powered risk predictions</span>
                  </div>
                  <div className={styles.dashboardFeature}>
                    <i className="fas fa-history"></i>
                    <span>Historical data analysis</span>
                  </div>
                  <div className={styles.dashboardFeature}>
                    <i className="fas fa-mobile-alt"></i>
                    <span>Access anywhere, anytime</span>
                  </div>
                </div>
                <Link to="/register" className={`${styles.btn} ${styles.btnPrimary} ${styles.btnLarge}`}>
                  Get Started Now
                </Link>
              </div>
              <div className={styles.dashboardImageWrapper}>
                <div className={styles.dashboardImage}>
                  <i className="fas fa-chart-area" style={{ fontSize: '64px', opacity: 0.2 }}></i>
                  <span>Dashboard preview coming soon</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className={styles.ctaSection}>
        <div className={`${styles.container} ${styles.ctaContent}`}>
          <h2>Ready to Transform Your Health?</h2>
          <p>Join thousands of users who've taken control of their wellness with AI-powered insights.</p>
          <Link to="/register" className={`${styles.btn} ${styles.btnLarge}`}>
            Get Started Now
          </Link>
        </div>
      </section>

      {/* Footer */}
      <footer className={styles.footer}>
        <div className={styles.container}>
          <div className={styles.footerContent}>
            <div className={styles.footerAbout}>
              <div className={styles.footerLogo}>
                <img src={logo} alt="VitaLens Logo" className={styles.logoImg} />
                <span>VitaLens</span>
              </div>
              <p>AI-powered health intelligence platform that transforms your medical documents into actionable insights. Secure, private, and built for proactive wellness.</p>
              <div className={styles.socialLinks}>
                <a href="#" className={styles.socialLink} aria-label="Twitter"><i className="fab fa-twitter"></i></a>
                <a href="#" className={styles.socialLink} aria-label="Facebook"><i className="fab fa-facebook"></i></a>
                <a href="#" className={styles.socialLink} aria-label="LinkedIn"><i className="fab fa-linkedin"></i></a>
                <a href="#" className={styles.socialLink} aria-label="Instagram"><i className="fab fa-instagram"></i></a>
              </div>
            </div>
            <div className={styles.footerLinks}>
              <h3>Product</h3>
              <ul>
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#">Mobile App</a></li>
                <li><a href="#">Integrations</a></li>
              </ul>
            </div>
            <div className={styles.footerLinks}>
              <h3>Resources</h3>
              <ul>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Documentation</a></li>
                <li><a href="#">API</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
              </ul>
            </div>
            <div className={styles.footerLinks}>
              <h3>Company</h3>
              <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Press Kit</a></li>
                <li><a href="#">Support</a></li>
              </ul>
            </div>
          </div>
          <div className={styles.footerBottom}>
            &copy; 2026 VitaLens. All rights reserved. Health predictions are informational and not a substitute for professional medical advice.
          </div>
        </div>
      </footer>
    </div>
  );
}