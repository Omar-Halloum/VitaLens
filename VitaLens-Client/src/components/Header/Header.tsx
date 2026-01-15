import { Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import styles from './Header.module.css';

export function Header() {
  const { user, logout } = useAuth();
  const { isDark, toggleTheme } = useTheme();

  return (
    <header className={styles.header}>
      <div className={styles.welcome}>
        Welcome, {user?.name || 'User'}!
      </div>
      <div className={styles.headerActions}>
        <button
          className={styles.themeToggle}
          onClick={toggleTheme}
          title="Toggle theme"
          aria-label="Toggle dark mode"
        >
          <i className={isDark ? 'fas fa-moon' : 'fas fa-sun'}></i>
        </button>
        <Link to="/profile" className={styles.iconBtn} title="Profile">
          <i className={user?.name ? 'fas fa-user-circle' : 'fas fa-user'}></i>
        </Link>
        <button className={styles.logoutBtn} onClick={logout}>
          Logout
        </button>
      </div>
    </header>
  );
}