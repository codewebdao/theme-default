<?php
/**
 * Localization System Test Dashboard
 * 
 * Comprehensive testing interface for Localization_helper.php functions
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Localization Test' ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .header h1 {
            font-size: 32px;
            color: #2d3748;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #718096;
            font-size: 16px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-card.total .value { color: #667eea; }
        .stat-card.passed .value { color: #48bb78; }
        .stat-card.failed .value { color: #f56565; }
        .stat-card.rate .value { color: #ed8936; }
        
        .actions {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: #f56565;
            color: white;
        }
        
        .btn-danger:hover {
            background: #e53e3e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 101, 101, 0.4);
        }
        
        .category {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .category h2 {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .test-list {
            display: grid;
            gap: 15px;
        }
        
        .test-item {
            background: #f7fafc;
            border-left: 4px solid #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .test-item:hover {
            background: #edf2f7;
            border-left-color: #667eea;
        }
        
        .test-item.passed {
            border-left-color: #48bb78;
        }
        
        .test-item.failed {
            border-left-color: #f56565;
        }
        
        .test-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .test-status {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .test-item.passed .test-status {
            background: #48bb78;
            color: white;
        }
        
        .test-item.failed .test-status {
            background: #f56565;
            color: white;
        }
        
        .test-function {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #667eea;
            font-weight: 600;
        }
        
        .test-description {
            color: #718096;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .test-result {
            background: white;
            padding: 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #2d3748;
            overflow-x: auto;
        }
        
        .test-result-label {
            font-size: 12px;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #667eea;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #48bb78;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #f56565;
        }

        .language-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
        }

        .language-card {
            background: #f7fafc;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .language-grid.language-grid-intl {
            max-height: 420px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .language-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        .language-emoji {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .language-code {
            font-weight: 700;
            color: #2d3748;
            font-size: 16px;
        }

        .language-name {
            color: #4a5568;
            font-size: 14px;
            margin-top: 4px;
        }
        
        .language-native {
            color: #a0aec0;
            font-size: 12px;
            margin-top: 2px;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 24px;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            /* User simulation responsive */
            .category > div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
            
            .category > div > div[style*="grid-template-columns: repeat"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
            $languageEmojis = [];
            if (!empty($languages_full)) {
                foreach ($languages_full as $langItem) {
                    $languageEmojis[$langItem['code']] = $langItem['emoji'];
                }
            }
        ?>
        <!-- Header -->
        <div class="header">
            <h1>🌍 Localization System Test Dashboard</h1>
            <p>Comprehensive testing for all Localization_helper.php functions with real-time validation</p>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card total">
                <h3>Total Tests</h3>
                <div class="value"><?= $stats['total'] ?></div>
            </div>
            <div class="stat-card passed">
                <h3>Passed</h3>
                <div class="value"><?= $stats['passed'] ?></div>
            </div>
            <div class="stat-card failed">
                <h3>Failed</h3>
                <div class="value"><?= $stats['failed'] ?></div>
            </div>
            <div class="stat-card rate">
                <h3>Success Rate</h3>
                <div class="value"><?= $stats['success_rate'] ?>%</div>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <button class="btn btn-primary" onclick="refreshRates()">🔄 Refresh Exchange Rates</button>
            <button class="btn btn-danger" onclick="clearRates()">🗑️ Clear Exchange Rates Cache</button>
            <a href="<?= admin_url() ?>" class="btn btn-primary">← Back to Dashboard</a>
        </div>

        <div id="alert-container"></div>

        <!-- User Simulation Section -->
        <div class="category" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h2 style="color: white; border-bottom-color: rgba(255,255,255,0.3);">👤 User Simulation & Preferences</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                <!-- Current Preferences -->
                <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px;">
                    <h3 style="margin-bottom: 15px; font-size: 18px;">Current Preferences</h3>
                    <div style="display: grid; gap: 10px;">
                        <div>
                            <strong>Currency:</strong> 
                            <span id="current-currency"><?= $current_preferences['currency'] ?></span>
                            (<?= ec_currency_symbol($current_preferences['currency']) ?>)
                        </div>
                        <div>
                            <strong>Country:</strong> 
                            <span id="current-country"><?= $current_preferences['country'] ?></span>
                            (<?= ec_country_name($current_preferences['country']) ?>)
                        </div>
                        <div>
                            <strong>Locale:</strong> 
                            <span id="current-locale"><?= $current_preferences['locale'] ?></span>
                            (<?= ec_language_name($current_preferences['locale']) ?>)
                        </div>
                    </div>
                </div>

                <!-- Sample Prices Preview -->
                <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px;">
                    <h3 style="margin-bottom: 15px; font-size: 18px;">Live Price Preview</h3>
                    <div id="price-preview" style="display: grid; gap: 8px;">
                        <?php foreach ($sample_prices as $sample): ?>
                        <div>
                            <strong><?= $sample['amount'] ?></strong> → 
                            <span style="color: #ffd700;"><?= $sample['formatted'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Preference Form -->
            <div style="background: white; padding: 25px; border-radius: 8px; color: #2d3748;">
                <h3 style="margin-bottom: 20px; color: #2d3748;">Change Preferences</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <!-- Currency Selector -->
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #4a5568;">Currency</label>
                        <select id="select-currency" style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 6px; font-size: 14px;">
                            <?php foreach ($currencies as $code => $curr): ?>
                            <option value="<?= $code ?>" <?= $code === $current_preferences['currency'] ? 'selected' : '' ?>>
                                <?= $code ?>: <?= $curr['name'] ?> (<?= $curr['symbol'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Country Selector -->
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #4a5568;">Country</label>
                        <select id="select-country" style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 6px; font-size: 14px;">
                            <?php foreach ($countries as $code => $name): ?>
                            <option value="<?= $code ?>" <?= $code === $current_preferences['country'] ? 'selected' : '' ?>>
                                <?= $code ?> - <?= $name ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Locale Selector -->
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #4a5568;">Language</label>
                        <select id="select-locale" style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 6px; font-size: 14px;">
                            <?php foreach ($languages as $code => $lang): ?>
                            <option value="<?= $code ?>" <?= $code === $current_preferences['locale'] ? 'selected' : '' ?>>
                                <?= $languageEmojis[$code] ?? ($lang['flag'] ?? '') ?> <?= $code ?> - <?= $lang['name'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button class="btn btn-primary" onclick="setPreferences()">💾 Save Preferences</button>
                    <button class="btn btn-primary" onclick="autoDetect()" style="background: #48bb78;">🔍 Auto-Detect</button>
                    <button class="btn btn-danger" onclick="clearPreferences()">🗑️ Clear Preferences</button>
                </div>
            </div>
        </div>

        <!-- Languages & Flags -->
        <?php if (!empty($languages_full)): ?>
        <div class="category">
            <h2>🌐 Supported Languages &amp; Flags</h2>
            <div class="language-grid">
                <?php foreach ($languages_full as $langItem): ?>
                <div class="language-card">
                    <div class="language-emoji"><?= $langItem['emoji'] ?: '🏳️' ?></div>
                    <div class="language-code"><?= strtoupper($langItem['code']) ?></div>
                    <div class="language-name"><?= htmlspecialchars($langItem['name']) ?></div>
                    <?php if (!empty($langItem['native']) && $langItem['native'] !== $langItem['name']): ?>
                        <div class="language-native"><?= htmlspecialchars($langItem['native']) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($languages_intl)): ?>
        <div class="category">
            <h2>🌍 Intl Language Catalogue (<?= count($languages_intl) ?>)</h2>
            <p style="margin-bottom: 20px; color: #718096;">Languages retrieved from Symfony Intl library.</p>
            <div class="language-grid language-grid-intl">
                <?php foreach ($languages_intl as $langItem): ?>
                <div class="language-card">
                    <div class="language-emoji"><?= $langItem['emoji'] ?: '🏳️' ?></div>
                    <div class="language-code"><?= htmlspecialchars(strtoupper($langItem['code'])) ?></div>
                    <div class="language-name"><?= htmlspecialchars($langItem['name']) ?></div>
                    <?php if (!empty($langItem['native']) && $langItem['native'] !== $langItem['name']): ?>
                        <div class="language-native"><?= htmlspecialchars($langItem['native']) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Test Results by Category -->
        <?php foreach ($tests as $category => $categoryTests): ?>
        <div class="category">
            <h2><?= ucwords(str_replace('_', ' ', $category)) ?></h2>
            <div class="test-list">
                <?php foreach ($categoryTests as $test): ?>
                <div class="test-item <?= $test['status'] ? 'passed' : 'failed' ?>">
                    <div class="test-header">
                        <div class="test-status">
                            <?= $test['status'] ? '✓' : '✗' ?>
                        </div>
                        <div style="flex: 1;">
                            <div class="test-function"><?= htmlspecialchars($test['function']) ?></div>
                            <div class="test-description"><?= htmlspecialchars($test['description']) ?></div>
                        </div>
                    </div>
                    <div class="test-result-label">Result:</div>
                    <div class="test-result">
                        <?= htmlspecialchars(
                            is_string($test['result']) 
                                ? $test['result'] 
                                : json_encode($test['result'], JSON_PRETTY_PRINT)
                        ) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        function showAlert(message, type = 'success') {
            const container = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <span style="font-size: 20px;">${type === 'success' ? '✓' : '✗'}</span>
                <span>${message}</span>
            `;
            container.appendChild(alert);
            
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }

        async function refreshRates() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '⏳ Refreshing...';
            
            try {
                const response = await fetch('<?= base_url('LocalizationTest/refresh_rates') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message + ' (Count: ' + data.info.count + ', Age: ' + data.info.age_hours + 'h)', 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('Error refreshing rates: ' + error.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '🔄 Refresh Exchange Rates';
            }
        }

        async function clearRates() {
            if (!confirm('Are you sure you want to clear the exchange rates cache?')) {
                return;
            }
            
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '⏳ Clearing...';
            
            try {
                const response = await fetch('<?= base_url('LocalizationTest/clear_rates') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('Error clearing cache: ' + error.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '🗑️ Clear Exchange Rates Cache';
            }
        }

        async function setPreferences() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '⏳ Saving...';
            
            const currency = document.getElementById('select-currency').value;
            const country = document.getElementById('select-country').value;
            const locale = document.getElementById('select-locale').value;
            
            try {
                const response = await fetch('<?= base_url('LocalizationTest/set_preferences') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ currency, country, locale })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    
                    // Update current preferences display
                    document.getElementById('current-currency').textContent = data.preferences.currency;
                    document.getElementById('current-country').textContent = data.preferences.country;
                    document.getElementById('current-locale').textContent = data.preferences.locale;
                    
                    // Reload to show new formatting
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message + ': ' + data.errors.join(', '), 'error');
                }
            } catch (error) {
                showAlert('Error saving preferences: ' + error.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '💾 Save Preferences';
            }
        }

        async function clearPreferences() {
            if (!confirm('Clear all user preferences and use defaults?')) {
                return;
            }
            
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '⏳ Clearing...';
            
            try {
                const response = await fetch('<?= base_url('LocalizationTest/clear_preferences') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('Error clearing preferences: ' + error.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '🗑️ Clear Preferences';
            }
        }

        async function autoDetect() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '⏳ Detecting...';
            
            try {
                const response = await fetch('<?= base_url('LocalizationTest/auto_preferences') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(
                        'Auto-detected: ' + 
                        data.preferences.country + ' → ' + 
                        data.preferences.currency + ' → ' + 
                        data.preferences.locale,
                        'success'
                    );
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('Error auto-detecting: ' + error.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '🔍 Auto-Detect';
            }
        }
    </script>
</body>
</html>

