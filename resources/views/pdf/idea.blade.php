<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $project['title'] }} — WorkHelper</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            color: #1e293b;
            line-height: 1.55;
        }
        .page { padding: 36px 42px 56px; }
        .header {
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .brand {
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #4f46e5;
            margin-bottom: 8px;
        }
        .doc-type {
            font-size: 9pt;
            color: #64748b;
            margin-bottom: 14px;
        }
        h1 {
            font-size: 22pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 14px;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-num {
            font-size: 8pt;
            font-weight: bold;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #4f46e5;
            margin-bottom: 8px;
        }
        .section-body {
            font-size: 10.5pt;
            color: #334155;
            text-align: justify;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        .type-badge {
            display: inline-block;
            background: #eef2ff;
            color: #4338ca;
            font-size: 11pt;
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #c7d2fe;
        }
        .occupation-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 22px;
        }
        .occupation-box .label {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .occupation-box h2 { font-size: 12pt; color: #0f172a; margin-bottom: 6px; }
        .occupation-box p { font-size: 10pt; color: #475569; }
        .revenue-highlight {
            background: #ecfdf5;
            border: 2px solid #6ee7b7;
            border-radius: 10px;
            padding: 18px 20px;
            margin-top: 8px;
            page-break-inside: avoid;
        }
        .revenue-highlight .label {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #047857;
            margin-bottom: 6px;
        }
        .revenue-highlight .amount {
            font-size: 22pt;
            font-weight: bold;
            color: #065f46;
        }
        .meta-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .meta-row td {
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        .meta-row td:last-child { padding-right: 0; padding-left: 10px; }
        .mini-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            page-break-inside: avoid;
        }
        .mini-card .label {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .mini-card .value {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
            word-wrap: break-word;
        }
        .stack-box {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 10pt;
            color: #312e81;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 42px;
            right: 42px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 8pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="brand">WorkHelper</div>
            <div class="doc-type">Mini étude de projet — Idée produit numérique</div>
        </div>

        @if (! empty($occupation_name))
            <div class="occupation-box">
                <div class="label">Contexte métier (ESCO)</div>
                <h2>{{ $occupation_name }}</h2>
                @if (! empty($occupation_description))
                    <p>{{ $occupation_description }}</p>
                @endif
            </div>
        @endif

        <div class="section">
            <div class="section-num">01</div>
            <div class="section-title">Titre du projet</div>
            <h1>{{ $project['title'] }}</h1>
        </div>

        <div class="section">
            <div class="section-num">02</div>
            <div class="section-title">Type d'application</div>
            <span class="type-badge">{{ $project['application_type'] }}</span>
        </div>

        <div class="section">
            <div class="section-num">03</div>
            <div class="section-title">Description complète</div>
            <div class="section-body">{{ $project['description'] }}</div>
        </div>

        <div class="section">
            <div class="section-num">04</div>
            <div class="section-title">Pourquoi ce projet est utile</div>
            <div class="section-body">{{ $project['why_useful'] }}</div>
        </div>

        <table class="meta-row">
            <tr>
                <td>
                    <div class="mini-card">
                        <div class="label">05 — Durée estimée de développement</div>
                        <div class="value">{{ $project['development_duration'] }}</div>
                    </div>
                </td>
                <td>
                    <div class="mini-card">
                        <div class="label">07 — Modèle économique</div>
                        <div class="value">{{ $project['business_model'] }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section">
            <div class="section-num">06</div>
            <div class="section-title">Stack technologique recommandée</div>
            <div class="stack-box">{{ $project['recommended_stack'] }}</div>
        </div>

        <div class="section">
            <div class="section-num">08</div>
            <div class="section-title">Potentiel de revenus mensuels</div>
            <div class="revenue-highlight">
                <div class="label">Estimation indicative</div>
                <div class="amount">{{ $formatted_revenue }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <strong>WorkHelper</strong> — Généré le {{ $generated_at }} · Document indicatif
    </div>
</body>
</html>
