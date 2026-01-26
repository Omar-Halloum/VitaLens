<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 0px; }
        body { 
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
            background: white;
            margin: 0px;
            padding: 0px;
        }
        .container {
            width: 100%;
            margin: 0;
            margin-top: 0 !important;
            background: white;
        }
        
        .header { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white; 
            padding: 40px 30px;
            text-align: center;
            margin-top: 0 !important;
        }
        
        .header h1 { 
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .header p { 
            font-size: 16px;
            opacity: 0.95;
            font-weight: 300;
        }
        
        .patient-info { 
            background: #f1f5f9;
            padding: 20px 30px;
            border-bottom: 2px solid #e2e8f0;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .info-item {
            font-size: 14px;
            color: #475569;
        }
        .info-item strong { 
            color: #0f172a;
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
        }
        
        .content { padding: 30px; }
        
        .section-title {
            font-size: 24px;
            color: #0f172a;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #10b981;
            font-weight: 700;
        }
        
        .risk-card {
            margin-bottom: 20px;
            border-radius: 10px;
            border: 2px solid;
            overflow: hidden;
            page-break-inside: avoid;
        }
        
        .risk-card-header {
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .risk-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }
        
        .risk-badge {
            padding: 6px 16px;
            border-radius: 20px;
            color: white;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        
        .risk-insight {
            padding: 16px 20px;
            border-top: 1px solid rgba(0,0,0,0.08);
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
        }
        
        .risk-factors {
            padding: 16px 20px;
            border-top: 1px solid rgba(0,0,0,0.08);
            background: rgba(0,0,0,0.02);
        }
        
        .factors-title {
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        
        .factor-list {
            list-style: none;
        }
        
        .factor-item {
            padding: 6px 0;
            font-size: 13px;
            color: #475569;
            display: flex;
            align-items: center;
        }
        
        .factor-item:before {
            content: "•";
            margin-right: 10px;
            font-weight: bold;
            font-size: 16px;
        }
        
        .risk-low {
            border-color: #10b981;
            background: #f0fdf4;
        }
        .risk-low .risk-card-header { background: #d1fae5; }
        .risk-low .risk-badge { background: #10b981; }
        .risk-low .factor-item:before { color: #10b981; }
        
        .risk-medium {
            border-color: #f59e0b;
            background: #fffbeb;
        }
        .risk-medium .risk-card-header { background: #fef3c7; }
        .risk-medium .risk-badge { background: #f59e0b; }
        .risk-medium .factor-item:before { color: #f59e0b; }
        
        .risk-high {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .risk-high .risk-card-header { background: #fee2e2; }
        .risk-high .risk-badge { background: #ef4444; }
        .risk-high .factor-item:before { color: #ef4444; }
        
        .legend {
            background: #eff6ff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            border-left: 4px solid #3b82f6;
        }
        
        .legend-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 12px;
        }
        
        .legend-item {
            font-size: 13px;
            color: #1e40af;
            margin-bottom: 8px;
        }
        
        .footer { 
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            margin-top: 30px;
        }
        
        .footer p { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🩺 VitaLens Health Report</h1>
            <p>AI-Powered Risk Analysis</p>
        </div>

        <div class="patient-info">
            <div class="info-item">
                <strong>Patient</strong>
                {{ $patient->name }}
            </div>
            <div class="info-item">
                <strong>Email</strong>
                {{ $patient->email }}
            </div>
            <div class="info-item">
                <strong>Report Date</strong>
                {{ $date }}
            </div>
            <div class="info-item">
                <strong>Source Document</strong>
                {{ $filename }}
            </div>
        </div>

        <div class="content">
            <h2 class="section-title">Risk Assessment Results</h2>

            @forelse($predictions as $pred)
                @php
                    $prob = $pred['probability'];
                    $level = $prob >= 0.7 ? 'high' : ($prob >= 0.4 ? 'medium' : 'low');
                    $levelLabel = strtoupper($level);
                    $percent = round($prob * 100);
                    $riskName = $pred['risk_name'] ?? 'Unknown Risk';
                    
                    $factors = $pred['factors'] ?? [];
                    $insight = $pred['ai_insight'] ?: 'Analyzing risk profile. Please consult with your healthcare provider for personalized guidance.';
                    
                @endphp

                <div class="risk-card risk-{{ $level }}">
                    <div class="risk-card-header">
                        <span class="risk-title">{{ $riskName }}</span>
                        <span class="risk-badge">{{ $percent }}% - {{ $levelLabel }}</span>
                    </div>
                    
                    @if(count($factors) > 0)
                        <div class="risk-factors">
                            <div class="factors-title">Contributing Health Factors</div>
                            <ul class="factor-list">
                                @foreach($factors as $factor)
                                    <li class="factor-item">
                                        {{ $factor['display_name'] ?? $factor['feature_name'] }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @empty
                <div style="padding: 30px; text-align: center; color: #64748b; background: #f1f5f9; border-radius: 8px; border: 2px dashed #cbd5e1;">
                    <strong>No Risk Predictions Available</strong>
                    <p style="margin-top: 8px; font-size: 14px;">More health data may be needed to generate predictions.</p>
                </div>
            @endforelse

            <div class="legend">
                <div class="legend-title">Understanding Your Risk Levels</div>
                <div class="legend-item"><strong>🟢 LOW (0-39%):</strong> Minimal concern. Continue healthy habits.</div>
                <div class="legend-item"><strong>🟡 MEDIUM (40-69%):</strong> Moderate risk. Consider lifestyle adjustments.</div>
                <div class="legend-item"><strong>🔴 HIGH (70-100%):</strong> Elevated risk. Consult with your healthcare provider.</div>
            </div>
        </div>

        <div class="footer">
            <p><strong>Generated by VitaLens AI</strong> • {{ now()->format('F j, Y \a\t g:i A') }}</p>
            <p>This report is for informational purposes only and does not constitute medical advice.</p>
        </div>
    </div>
</body>
</html>