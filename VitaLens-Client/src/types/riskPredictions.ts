export interface RiskType {
    id: number;
    key: string;
    display_name: string;
}

export type ConfidenceLevel = 'low' | 'medium' | 'high';

export interface RiskPredictionRaw {
    id: number;
    user_id: number;
    risk_type_id: number;
    probability: string; 
    confidence_level: ConfidenceLevel;
    created_at: string;
    updated_at: string;
    risk_type: RiskType;
}

export interface RiskPrediction extends Omit<RiskPredictionRaw, 'probability'> {
    probability: number;
}