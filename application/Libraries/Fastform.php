<?php
namespace App\Libraries;

use System\Libraries\Session;
/**
 * Fastform Library
 * 
 * Dynamic form generation with ACF-style React integration.
 * Provides OOP interface for creating settings pages, plugin configs, and custom Fastform.
 * 
 * @package System\Libraries
 * @version 1.0.0
 */
class Fastform
{
    /** @var array Form configuration */
    protected $config = [];
    
    /** @var array Form fields */
    protected $fields = [];
    
    /** @var array Form tabs */
    protected $tabs = [];
    
    /** @var array Current data values */
    protected $data = [];
    
    /** @var string CSRF token */
    protected $csrfToken;
    
    /**
     * Create new Fastform instance
     * 
     * @param array $config Form configuration
     * 
     * @example
     * $form = new Fastform([
     *     'id' => 'plugin-settings',
     *     'title' => 'Plugin Settings',
     *     'submit_url' => admin_url('save')
     * ]);
     */
    public function __construct($config = [])
    {
        $defaults = [
            'id' => 'form-' . uniqid(),
            'title' => 'Form',
            'description' => '',
            'submit_url' => '',
            'redirect_url' => '',
            'method' => 'POST',
            'multilang' => false,
            'post_lang' => S_GET('post_lang') ?? APP_LANG,
            'submit_text' => __('Save Changes'),
            'cancel_url' => '',
            'ajax' => false,
        ];
        
        $this->config = array_merge($defaults, $config);
        $this->csrfToken = Session::csrf_token(600);
    }
    
    /**
     * Add a field to the form
     * 
     * @param string $type Field type
     * @param string $name Field name
     * @param string $label Field label
     * @param array $options Field options
     * @return self Chainable
     * 
     * @example
     * $form->addField('text', 'api_key', 'API Key', ['required' => true])
     *      ->addField('number', 'timeout', 'Timeout', ['default' => 30]);
     */
    public function addField($type, $name, $label, $options = [])
    {
        $field = array_merge([
            'id' => \App\Libraries\Fastuuid::timeuuid(),
            'type' => ucfirst($type),
            'field_name' => $name,
            'label' => $label,
            'description' => '',
            'required' => false,
            'visibility' => true,
            'synchronous' => false,
            'tab' => '',
            'order' => count($this->fields) + 1,
            'width_value' => 100,
            'width_unit' => '%',
            'position' => 'left',
            'placeholder' => '',
            'default_value' => '',
            'css_class' => '',
        ], $options);
        
        $field['type'] = ucfirst($type);
        $field = $this->applyTypeDefaults($field);
        
        $this->fields[] = $field;
        return $this;
    }
    
    /**
     * Add a tab
     * 
     * @param string $id Tab ID
     * @param string $label Tab label
     * @param array $options Tab options
     * @return self Chainable
     * 
     * @example
     * $form->addTab('general', 'General Settings', ['icon' => 'settings'])
     *      ->addTab('advanced', 'Advanced', ['icon' => 'sliders']);
     */
    public function addTab($id, $label, $options = [])
    {
        $this->tabs[] = array_merge([
            'id' => $id,
            'label' => $label,
            'icon' => '',
            'description' => '',
        ], $options);
        
        return $this;
    }
    
    /**
     * Set current data values
     * 
     * @param array $data Current values
     * @return self Chainable
     * 
     * @example
     * $form->setData([
     *     'api_key' => 'sk_live_...',
     *     'timeout' => 30
     * ]);
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Render form HTML (with React ACF)
     * 
     * @return string HTML
     */
    public function render()
    {
        $optionsData = $this->convertToOptionsFormat();
        
        // app_lang: if passed in config use it (e.g. ['all'] for single tab), else array_keys(APP_LANGUAGES)
        $appLang = isset($this->config['app_lang']) ? $this->config['app_lang'] : array_keys(APP_LANGUAGES);
        $postLang = (is_array($appLang) && count($appLang) === 1 && ($appLang[0] ?? '') === 'all')
            ? 'all'
            : $this->config['post_lang'];
        
        $acfData = [
            'lang' => $postLang,
            'ADMIN_URL' => admin_url(),
            'app_lang' => $appLang,
            'post_lang' => $postLang,
            'page' => 'options',
            'inputConfig' => $this->getInputConfig(),
            'optionsData' => $optionsData,
        ];
        
        ob_start();
        ?>

<script>
    window.ACF_DATA = <?= json_encode($acfData, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
</script>

        <div class="pc-container">
            <div class="pc-content">
                <!-- Header -->
                <div class="flex flex-col gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-foreground"><?= htmlspecialchars($this->config['title']) ?></h1>
                        <?php if (!empty($this->config['description'])): ?>
                            <p class="text-muted-foreground"><?= htmlspecialchars($this->config['description']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Flash Messages -->
                    <?php if (Session::has_flash('success')): ?>
                        <div class="bg-success/10 border border-success/20 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <i data-lucide="check-circle" class="h-5 w-5 text-success flex-shrink-0"></i>
                                <p class="text-sm text-success"><?= Session::flash('success') ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (Session::has_flash('error')): ?>
                        <div class="bg-destructive/10 border border-destructive/20 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <i data-lucide="alert-circle" class="h-5 w-5 text-destructive flex-shrink-0"></i>
                                <p class="text-sm text-destructive"><?= Session::flash('error') ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <style>
                    .loading-spinner {
                        display: inline-block;
                        width: 20px;
                        height: 20px;
                        border: 3px solid #f3f3f3;
                        border-top: 3px solid #3498db;
                        border-radius: 50%;
                        animation: spin 1s linear infinite;
                    }
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                </style>
                
                <link rel="stylesheet" href="<?= theme_assets('css/posts_add.css') ?>">
                <script type="module" src="<?= theme_assets('js/posts_add.js') ?>"></script>

                <div id="initial-loading" style="display: flex; justify-content: center; align-items: center; height: 200px; flex-direction: column;">
                    <div class="loading-spinner"></div>
                    <p style="margin-top: 10px; color: #666;"><?= __('Loading Form...') ?></p>
                </div>
                
                <div id="root"></div>
                
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    
    /**
     * Process form submission
     * 
     * @param callable|null $callback Custom save callback
     * @return array Result
     */
    public function processSubmit($callback = null)
    {
        $errors = [];
        $lang = S_POST('post_lang') ?? S_GET('post_lang') ?? $this->config['post_lang'];
        
        // Validate CSRF
        if (!HAS_POST('csrf_token') || !Session::csrf_verify(S_POST('csrf_token'))) {
            return [
                'success' => false,
                'errors' => ['csrf' => __('Invalid security token')],
                'message' => __('Invalid security token')
            ];
        }
        
        // Validate fields
        foreach ($this->fields as $field) {
            $fieldName = $field['field_name'];
            $value = S_POST($fieldName) ?? null;
            
            if (!empty($field['required']) && empty($value)) {
                $errors[$fieldName] = __('This field is required');
                continue;
            }
            
            $fieldError = $this->validateField($field, $value);
            if ($fieldError) {
                $errors[$fieldName] = $fieldError;
            }
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'message' => __('Please fix the errors below')
            ];
        }
        
        // Save data
        if ($callback && is_callable($callback)) {
            $success = $callback($_POST, $lang);
        } else {
            $success = $this->saveToOptions($_POST, $lang);
        }
        
        return [
            'success' => $success,
            'message' => $success ? __('Settings saved successfully') : __('Failed to save'),
            'redirect' => $this->config['redirect_url'] ?? null
        ];
    }
    
    /**
     * Convert to optionsData format for React
     * 
     * @return array optionsData structure
     */
    protected function convertToOptionsFormat()
    {
        $options = [];
        
        // Add tabs configuration as first item
        if (!empty($this->tabs)) {
            $tabOptions = [];
            foreach ($this->tabs as $tab) {
                $tabOptions[] = [
                    'value' => $tab['id'],
                    'label' => $tab['label'],
                    'is_group' => false,
                    'icon' => $tab['icon'] ?? '',
                ];
            }
            
            $options[] = [
                'id' => 1,
                'label' => 'Tabs',
                'type' => 'Select',
                'name' => $this->config['id'] . '_tabs',
                'description' => '',
                'status' => 'active',
                'value' => '',
                'optional' => json_encode([
                    'id' => 1,
                    'type' => 'Select',
                    'options' => $tabOptions,
                    'visibility' => false,
                    'option_group' => $this->config['id'],
                ]),
            ];
        }
        
        // Convert fields
        $optionId = 10;
        foreach ($this->fields as $field) {
            $fieldName = $field['field_name'];
            $currentValue = $this->data[$fieldName] ?? $field['default_value'] ?? '';
            
            $optional = $field;
            $optional['option_group'] = $field['tab'] ?? 'general';
            $optional['save_file'] = false;
            
            $options[] = [
                'id' => $optionId++,
                'label' => $field['label'],
                'type' => $field['type'],
                'name' => $fieldName,
                'description' => $field['description'] ?? '',
                'status' => 'active',
                'value' => is_array($currentValue) ? json_encode($currentValue) : $currentValue,
                'optional' => json_encode($optional),
            ];
        }
        
        return [
            'options' => $options,
            'errors' => [],
            'post_lang' => $this->config['post_lang'],
            'title' => $this->config['title'],
            'csrf_token' => $this->csrfToken,
            'submit_url' => $this->config['submit_url'] ?? '',
        ];
    }
    
    /**
     * Apply type-specific defaults
     * 
     * @param array $field Field configuration
     * @return array Field with defaults
     */
    protected function applyTypeDefaults($field)
    {
        switch ($field['type']) {
            case 'Text':
            case 'Email':
            case 'URL':
            case 'Password':
                $field['min'] = $field['min'] ?? null;
                $field['max'] = $field['max'] ?? 255;
                break;
                
            case 'Number':
                $field['min'] = $field['min'] ?? null;
                $field['max'] = $field['max'] ?? null;
                $field['step'] = $field['step'] ?? 1;
                break;
                
            case 'Textarea':
                $field['rows'] = $field['rows'] ?? 3;
                break;
                
            case 'Select':
            case 'Radio':
            case 'Checkbox':
                $field['options'] = $field['options'] ?? [];
                $field['multiple'] = $field['multiple'] ?? false;
                break;
                
            case 'Image':
            case 'File':
                $field['allow_types'] = $field['allow_types'] ?? [];
                $field['max_file_size'] = $field['max_file_size'] ?? 10;
                $field['multiple'] = $field['multiple'] ?? false;
                break;
        }
        
        return $field;
    }
    
    /**
     * Validate field value
     * 
     * @param array $field Field configuration
     * @param mixed $value Field value
     * @return string|null Error message
     */
    protected function validateField($field, $value)
    {
        switch ($field['type']) {
            case 'Email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return __('Invalid email address');
                }
                break;
                
            case 'URL':
                if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                    return __('Invalid URL');
                }
                break;
                
            case 'Number':
                if ($value !== '' && !is_numeric($value)) {
                    return __('Must be a number');
                }
                if (isset($field['min']) && $value < $field['min']) {
                    return sprintf(__('Minimum value is %s'), $field['min']);
                }
                if (isset($field['max']) && $value > $field['max']) {
                    return sprintf(__('Maximum value is %s'), $field['max']);
                }
                break;
                
            case 'Text':
            case 'Textarea':
                if (isset($field['min']) && strlen($value) < $field['min']) {
                    return sprintf(__('Minimum length is %s'), $field['min']);
                }
                if (isset($field['max']) && strlen($value) > $field['max']) {
                    return sprintf(__('Maximum length is %s'), $field['max']);
                }
                break;
        }
        
        return null;
    }
    
    /**
     * Save to options storage
     * 
     * @param array $postData POST data
     * @param string $lang Language
     * @return bool Success
     */
    protected function saveToOptions($postData, $lang)
    {
        foreach ($this->fields as $field) {
            $fieldName = $field['field_name'];
            if (isset($postData[$fieldName])) {
                $value = $postData[$fieldName];
                
                // Type casting
                switch ($field['type']) {
                    case 'Number':
                        $value = is_numeric($value) ? (int)$value : 0;
                        break;
                    case 'Boolean':
                        $value = in_array($value, [1, '1', 'true', true, 'on'], true) ? 1 : 0;
                        break;
                }
                
                $saveLang = ($field['synchronous'] ?? false) ? APP_LANG_DF : $lang;
                
                if (!set_option($fieldName, $value, $saveLang)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get input styling configuration
     * 
     * @return array Input config
     */
    protected function getInputConfig()
    {
        return [
            'border' => ['width' => 1, 'style' => 'solid', 'color' => '#e2e8f0', 'radius' => 6],
            'background' => ['color' => '#ffffff', 'hover' => '#f8fafc', 'focus' => '#ffffff'],
            'text' => ['color' => '#1e293b', 'fontSize' => 14, 'fontWeight' => 'normal'],
            'spacing' => ['padding' => ['x' => 12, 'y' => 8], 'margin' => ['x' => 0, 'y' => 2], 'fieldGap' => 16],
            'size' => ['height' => 38, 'minHeight' => 38],
            'wrapper' => [
                'enabled' => true,
                'border' => ['width' => 1, 'style' => 'solid', 'color' => '#f1f5f9', 'radius' => 6],
                'background' => '#ffffff',
                'padding' => ['x' => 12, 'y' => 12],
                'margin' => ['x' => 0, 'y' => 8]
            ],
            'label' => ['color' => '#374151', 'fontSize' => 14, 'fontWeight' => 'medium', 'marginBottom' => 4],
            'effects' => ['transition' => 'all 0.2s ease', 'focusRing' => true, 'hoverEnabled' => true],
            'outerWrapper' => [
                'enabled' => true,
                'border' => ['width' => 0, 'style' => 'solid', 'color' => 'transparent', 'radius' => 8],
                'background' => '#fafafa',
                'padding' => ['x' => 0, 'y' => 0],
                'margin' => ['x' => 0, 'y' => 0],
                'shadow' => true
            ]
        ];
    }
    
    /**
     * Get form configuration
     * 
     * @return array Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * Get all fields
     * 
     * @return array Fields
     */
    public function getFields()
    {
        return $this->fields;
    }
    
    /**
     * Get all tabs
     * 
     * @return array Tabs
     */
    public function getTabs()
    {
        return $this->tabs;
    }
}

