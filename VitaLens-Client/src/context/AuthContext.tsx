/* eslint-disable react-refresh/only-export-components */
import { createContext, useContext, useState, type ReactNode } from "react";
import type { AuthUser, RegisterData } from "../types/auth";
import { login as apiLogin, register as apiRegister } from "../api/authentication";

interface AuthContextType {
  user: AuthUser | null;
  token: string | null;
  login: (email: string, password: string) => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
  logout: () => void;
  isAuthenticated: boolean;
  isLoading: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(() => {
    try {
      const stored = localStorage.getItem("user");
      return stored ? JSON.parse(stored) : null;
    } catch {
      return null;
    }
  });

  const [token, setToken] = useState<string | null>(() => {
    return localStorage.getItem("token");
  });

  const [isLoading, setIsLoading] = useState(false);

  const handleLogin = async (email: string, password: string): Promise<void> => {
    setIsLoading(true);
    try {
      const authData = await apiLogin(email, password);
      
      const { token, ...userData } = authData; 
      
      setUser(userData); 
      setToken(token);

      localStorage.setItem("user", JSON.stringify(userData));
      localStorage.setItem("token", token);
    } finally {
      setIsLoading(false);
    }
  };

  const handleRegister = async (data: RegisterData): Promise<void> => {
    setIsLoading(true);
    try {
      const authData = await apiRegister(data);
      
      // FIXED: Same fix for register
      const { token, ...userData } = authData;

      setUser(userData);
      setToken(token);

      localStorage.setItem("user", JSON.stringify(userData));
      localStorage.setItem("token", token);
    } finally {
      setIsLoading(false);
    }
  };

  const logout = (): void => {
    setUser(null);
    setToken(null);
    localStorage.removeItem("user");
    localStorage.removeItem("token");
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        login: handleLogin,
        register: handleRegister,
        logout,
        isAuthenticated: !!user,
        isLoading,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextType {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth must be used within AuthProvider");
  }
  return context;
}