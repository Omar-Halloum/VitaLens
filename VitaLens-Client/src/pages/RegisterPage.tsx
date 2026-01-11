import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import type { RegisterData } from '../types/auth';
import logo from '../assets/VitaLens-logo.png';
import styles from '../styles/AuthPage.module.css';

export function RegisterPage() {
  const [formData, setFormData] = useState<RegisterData>({
    username: '',
    email: '',
    password: '',
    gender: '',
    birthdate: '',
    height: 0,
    weight: 0,
  });
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  const { register, isLoading } = useAuth();
  const { isDark, toggleTheme } = useTheme();
  const navigate = useNavigate();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: name === 'height' || name === 'weight' ? Number(value) : value,
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    try {
      await register(formData);
      navigate('/dashboard');
    } catch (err) {
      setError((err as Error).message || 'Registration failed');
    }
  };

  return (
    <div className={styles.pageContainer}>
      <div className={styles.authContainer}>
        <Link to="/" className={styles.backButton}>
          <i className="fas fa-arrow-left"></i>
        </Link>

        <div className={styles.logo}>
          <img src={logo} alt="VitaLens" className={styles.logoImg} />
          <h1>Create Your VitaLens Account</h1>
          <p>Join others using AI to monitor their health</p>
        </div>

        <div className={styles.authCard}>
          <button 
            className={styles.themeToggle}
            onClick={toggleTheme}
            title="Toggle theme"
            aria-label="Toggle theme"
          >
            <i className={isDark ? 'fas fa-moon' : 'fas fa-sun'}></i>
          </button>

          {error && <div className={styles.error}>{error}</div>}

          <form onSubmit={handleSubmit}>
            <div className={styles.formGroup}>
              <label htmlFor="username">Username</label>
              <input
                type="text"
                id="username"
                name="username"
                className={styles.formControl}
                placeholder="Omar"
                value={formData.username}
                onChange={handleChange}
                required
                disabled={isLoading}
              />
            </div>

            <div className={styles.formGroup}>
              <label htmlFor="email">Email</label>
              <input
                type="email"
                id="email"
                name="email"
                className={styles.formControl}
                placeholder="you@example.com"
                value={formData.email}
                onChange={handleChange}
                required
                disabled={isLoading}
              />
            </div>

            <div className={styles.formGroup}>
              <label htmlFor="password">Password</label>
              <div className={styles.passwordWrapper}>
                <input
                  type={showPassword ? 'text' : 'password'}
                  id="password"
                  name="password"
                  className={styles.formControl}
                  placeholder="••••••••"
                  value={formData.password}
                  onChange={handleChange}
                  required
                  minLength={8}
                  disabled={isLoading}
                />
                <button
                  type="button"
                  className={styles.passwordToggle}
                  onClick={() => setShowPassword(!showPassword)}
                  aria-label={showPassword ? 'Hide password' : 'Show password'}
                >
                  <i className={showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'}></i>
                </button>
              </div>
            </div>

            <hr className={styles.divider} />

            <h2 className={styles.sectionTitle}>Your Basic Info</h2>

            <div className={styles.row}>
              <div className={styles.formGroup}>
                <label htmlFor="gender">Gender</label>
                <select
                  id="gender"
                  name="gender"
                  className={styles.formControl}
                  value={formData.gender}
                  onChange={handleChange}
                  required
                  disabled={isLoading}
                >
                  <option value="">Select</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
              </div>

              <div className={styles.formGroup}>
                <label htmlFor="birthdate">Birth Date</label>
                <input
                  type="date"
                  id="birthdate"
                  name="birthdate"
                  className={styles.formControl}
                  value={formData.birthdate}
                  onChange={handleChange}
                  required
                  disabled={isLoading}
                />
              </div>
            </div>

            <div className={styles.row}>
              <div className={styles.formGroup}>
                <label htmlFor="height">Height (cm)</label>
                <input
                  type="number"
                  id="height"
                  name="height"
                  className={styles.formControl}
                  placeholder="e.g. 175"
                  value={formData.height || ''}
                  onChange={handleChange}
                  min="50"
                  max="250"
                  required
                  disabled={isLoading}
                />
              </div>

              <div className={styles.formGroup}>
                <label htmlFor="weight">Weight (kg)</label>
                <input
                  type="number"
                  id="weight"
                  name="weight"
                  className={styles.formControl}
                  placeholder="e.g. 70"
                  value={formData.weight || ''}
                  onChange={handleChange}
                  min="20"
                  max="300"
                  required
                  disabled={isLoading}
                />
              </div>
            </div>

            <button 
              type="submit" 
              className={styles.btn}
              disabled={isLoading}
            >
              {isLoading ? 'Creating Account...' : 'Create Account'}
            </button>
          </form>

          <div className={styles.authLink}>
            Already have an account? <Link to="/login">Sign in</Link>
          </div>
        </div>
      </div>
    </div>
  );
}