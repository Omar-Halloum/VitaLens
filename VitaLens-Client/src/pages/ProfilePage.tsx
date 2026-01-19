import { useState } from 'react';
import { useGetUserProfile } from '../hooks/useGetUserProfile';
import { useUpdateProfile } from '../hooks/useUpdateProfile';
import { ProfileFormField } from '../components/ProfileFormField/ProfileFormField';
import type { UpdateProfileData, AuthUser } from '../types/auth';
import styles from '../styles/ProfilePage.module.css';

interface ProfileFormProps {
  user: AuthUser;
}

function ProfileForm({ user }: ProfileFormProps) {
  const updateProfileMutation = useUpdateProfile();
  const [isEditing, setIsEditing] = useState(false);
  const [showSuccess, setShowSuccess] = useState(false);
  
  // Initialize state directly from props
  const [formData, setFormData] = useState({
    name: user.name || '',
    height: user.height ? Math.round(Number(user.height)).toString() : '',
    weight: user.weight ? Math.round(Number(user.weight)).toString() : '',
  });

  const handleEdit = () => {
    setIsEditing(true);
    setShowSuccess(false);
  };

  const handleCancel = () => {
    setFormData({
      name: user.name || '',
      height: user.height?.toString() || '',
      weight: user.weight?.toString() || '',
    });
    setIsEditing(false);
    setShowSuccess(false);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    const updates: UpdateProfileData = {};
    
    if (formData.name !== user.name) {
      updates.name = formData.name;
    }
    if (formData.height && parseFloat(formData.height) !== user.height) {
      updates.height = parseFloat(formData.height);
    }
    if (formData.weight && parseFloat(formData.weight) !== user.weight) {
      updates.weight = parseFloat(formData.weight);
    }

    try {
      await updateProfileMutation.mutateAsync(updates);
      setIsEditing(false);
      setShowSuccess(true);
      
      setTimeout(() => {
        setShowSuccess(false);
      }, 3000);
    } catch (error) {
      console.error('Profile update failed:', error);
    }
  };

  return (
    <div className={styles.profileCard}>
      <div className={styles.profileHeader}>
        <h2 className={styles.profileTitle}>Personal Information</h2>
        {!isEditing && (
          <button onClick={handleEdit} className={styles.editBtn}>
            Edit
          </button>
        )}
      </div>

      <form onSubmit={handleSubmit}>
        <ProfileFormField
          label="Username"
          id="name"
          type="text"
          value={formData.name}
          onChange={(value) => setFormData({ ...formData, name: value })}
          readOnly={!isEditing}
        />

        <ProfileFormField
          label="Height (cm)"
          id="height"
          type="number"
          value={formData.height}
          onChange={(value) => setFormData({ ...formData, height: value })}
          readOnly={!isEditing}
          min="50"
          max="300"
        />

        <ProfileFormField
          label="Weight (kg)"
          id="weight"
          type="number"
          value={formData.weight}
          onChange={(value) => setFormData({ ...formData, weight: value })}
          readOnly={!isEditing}
          min="20"
          max="500"
        />

        {showSuccess && (
          <div className={styles.successMessage}>
            <i className="fas fa-check-circle"></i>
            Profile updated successfully!
          </div>
        )}

        {isEditing && (
          <div className={styles.formActions}>
            <button 
              type="button" 
              onClick={handleCancel} 
              className={styles.cancelBtn}
              disabled={updateProfileMutation.isPending}
            >
              Cancel
            </button>
            <button 
              type="submit" 
              className={styles.saveBtn}
              disabled={updateProfileMutation.isPending}
            >
              {updateProfileMutation.isPending ? 'Saving...' : 'Save Changes'}
            </button>
          </div>
        )}
      </form>
    </div>
  );
}

export function ProfilePage() {
  const { data: user, isLoading, error } = useGetUserProfile();

  if (error) {
    return (
      <div className={styles.container}>
        <div className={styles.error}>
          <i className="fas fa-exclamation-circle"></i>
          Failed to load profile. Please try again.
        </div>
      </div>
    );
  }

  return (
    <div className={styles.container}>
      <h1 className={styles.pageTitle}>Account Settings</h1>

      {isLoading || !user ? (
        <div className={styles.loading}>
          <i className="fas fa-spinner fa-spin"></i>
          Loading profile...
        </div>
      ) : (
        // Key forces a fresh component (and state reset) when data changes
        <ProfileForm 
          user={user} 
          key={`${user.id}-${user.name}-${user.weight}-${user.height}`}
        />
      )}
    </div>
  );
}