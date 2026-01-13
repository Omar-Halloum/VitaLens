export interface RiskType {
    id: number;
    key: string;
    display_name: string;
}

export interface RiskPrediction {
    id: number;
    user_id: number;
    risk_type_id: number;
    probability: string; // Decimal string from backend
    confidence_level: string; // 'low' | 'medium' | 'high'
    created_at: string;
    updated_at: string;
    risk_type: RiskType;
}