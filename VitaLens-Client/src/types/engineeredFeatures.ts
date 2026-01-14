export interface FeatureDefinition {
    id: number;
    feature_name: string;
    display_name: string;
    description?: string;
}

export interface EngineeredFeatureRaw {
    id: number;
    user_id: number;
    feature_definition_id: number;
    feature_value: string; 
    created_at: string;
    updated_at: string;
    feature_definition: FeatureDefinition;
}

export interface EngineeredFeature extends Omit<EngineeredFeatureRaw, 'feature_value'> {
    feature_value: number;
}