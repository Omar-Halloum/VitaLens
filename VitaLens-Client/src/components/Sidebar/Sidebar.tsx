import { Link, useLocation } from 'react-router-dom';
import styles from './Sidebar.module.css';

export function Sidebar() {
  const location = useLocation();

  const isActive = (path: string) => {
    return location.pathname === path ? styles.activeLink : '';
  };

  return (
    <aside className={styles.sidebar}>
      <Link to="/" className={styles.logo}>
        <i className="fas fa-heartbeat"></i>
        VitaLens
      </Link>
      <ul className={styles.navLinks}>
        <li>
          <Link to="/dashboard" className={isActive('/dashboard')}>
            <i className="fas fa-home"></i> Dashboard
          </Link>
        </li>
        <li>
          <Link to="/documents" className={isActive('/documents')}>
            <i className="fas fa-file-medical"></i> Documents
          </Link>
        </li>
        <li>
          <Link to="/logs" className={isActive('/logs')}>
            <i className="fas fa-clock"></i> Daily Logs
          </Link>
        </li>
        <li>
          <Link to="/chat" className={isActive('/chat')}>
            <i className="fas fa-question-circle"></i> Ask AI
          </Link>
        </li>
      </ul>
    </aside>
  );
}