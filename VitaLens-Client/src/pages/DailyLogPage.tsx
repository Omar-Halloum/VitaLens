import { useGetHabitLogs } from '../hooks/useGetHabitLogs';
import { HabitInput } from '../components/HabitLog/HabitInput/HabitInput';
import { LogList } from '../components/HabitLog/LogList/LogList';
import styles from '../styles/DailyLogPage.module.css';

export function DailyLogPage() {
  const { data: logs = [], isLoading, refetch } = useGetHabitLogs();

  const handleLogSuccess = () => {
    refetch();
  };

  return (
    <div className={styles.container}>
      <div className={styles.header}>
        <h1 className={styles.pageTitle}>Habit Journal</h1>
      </div>

      <HabitInput onSuccess={handleLogSuccess} />

      <section className={styles.historySection}>
        <div className={styles.sectionHeader}>
          <h2 className={styles.sectionTitle}>
            <i className="fas fa-history"></i>
            Habit History
          </h2>
        </div>
        
        <LogList logs={logs} isLoading={isLoading} />
      </section>
    </div>
  );
}