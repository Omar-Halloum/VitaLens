export interface FeatureDefinition {
    id: number;
    feature_name: string;
    display_name: string;
    description?: string;
}

export interface EngineeredFeature {
    id: number;
    user_id: number;
    feature_definition_id: number;
    feature_value: string; // Decimal string from backend
    created_at: string;
    updated_at: string;
    feature_definition: FeatureDefinition;
}