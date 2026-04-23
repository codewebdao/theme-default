<?php
// This page is very important, it helps create multiple languages for the website. It has a special link with the Posts Controller
namespace App\Controllers\Backend;

use App\Controllers\BackendController;
use App\Models\LanguagesModel;
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;
use System\Libraries\Render\View;
use System\Libraries\Validate;




class LanguagesController extends BackendController
{
    protected $languagesModel;
    protected $assets;


    public function __construct()
    {
        parent::__construct();
        Flang::load('Backend/Global');
        Flang::load('Backend/Languages');
        $this->languagesModel = new LanguagesModel();
    }

    // List all languages
    public function index()
    {
        $search = S_GET('q') ?? '';
        $limit = S_GET('limit') ?? option('default_limit');
        $status = S_GET('status') ?? '';
        $sort = S_GET('sort') ?? 'status';
        $order = S_GET('order') ?? 'ASC';
        $paged = S_GET('page') ?? 1;

        // If page = 0 or negative, set to 1
        if ($paged < 1) {
            $paged = 1;
        }

        // If limit is invalid, set to 10
        if ($limit < 1) {
            $limit = option('default_limit');
        }
        $where = '';
        $params = [];
        if (!empty($search)) {
            if (!empty($where)) {
                $where .= " AND name LIKE ? ";
            } else {
                $where .= "name LIKE ? ";
            }
            $params[] = '%' . $search . '%';
        }
        if (!empty($status)) {
            if (!empty($where)) {
                $where .= " AND status = ? ";
            } else {
                $where .= "status = ? ";
            }
            $params[] = $status;
        }

        if (!empty($sort) && !empty($order)) {
            $orderBy = $sort . ' ' . $order;
        } else {
            $orderBy = 'status ASC, id ASC';
        }
        $languages = $this->languagesModel->getLanguagesFieldsPagination('*', $where, $params, $orderBy, $paged, $limit);
        $this->data('languages', $languages);
        $this->data('limit', $limit);
        $this->data('title', __('tile_languages'));
        $this->data('csrf_token', Session::csrf_token()); //token security
        // languages_index
        View::addJs('languages-page-js', 'js/language.js', [], null, true, false, true, false);
        echo View::make('languages_index', $this->data)->render();
    }
    // Add new language
    public function add()
    {
        // Validate form add new language
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf_token = S_POST('csrf_token') ?? '';
            $name = S_POST('name') ?? '';
            $code = S_POST('code') ?? '';
            $flag = S_POST('flag') ?? '';
            $locale = S_POST('locale') ?? '';
            $status = S_POST('status') ?? 'inactive';
            $default = S_POST('is_default') ?? 0;

            // check CSRF token
            if (!Session::csrf_verify($csrf_token)) {
                Session::flash('error', __('csrf_failed'));
                redirect(admin_url('languages'));
            }

            // Auto-generate locale if not provided: code_FLAG (e.g., en_US, vi_VN)
            if (empty($locale) && !empty($code) && !empty($flag)) {
                $locale = strtolower($code) . '_' . strtoupper($flag);
            }

            $data = [
                'name' => $name,
                'code' => strtolower($code),
                'flag' => strtolower($flag),
                'locale' => $locale,
                'status' => $status,
                'is_default' => 0,
            ];

            $rules = [
                'name' =>  [
                    'rules' => [Validate::length(2, 80)],
                    'messages' => [sprintf(__('length_error'), 3, 80)]
                ],
                'code' => [
                    'rules' => [Validate::alpha(), Validate::lowercase(), Validate::length(2, 2)],
                    'messages' => [__('notalpha'), __('lowercase_error'), sprintf(__('length_error'), 2, 2)]
                ],
                'flag' => [
                    'rules' => [Validate::alpha(), Validate::lowercase(), Validate::length(2, 2)],
                    'messages' => [__('notalpha'), __('lowercase_error'), sprintf(__('length_error'), 2, 2)]
                ],
                'locale' => [
                    'rules' => [Validate::length(2, 15)],
                    'messages' => [sprintf(__('length_error'), 2, 15)]
                ],
                'is_default' => [
                    'rules' => [Validate::in([0, 1])],
                    'messages' => [__('in_error')]
                ],
                'status' => [
                    'rules' => [Validate::in(['active', 'inactive'])],
                    'messages' => [__('in_error')]
                ]
            ];
            $validator = new Validate();
            if (!$validator->check($data, $rules)) {
                $errors = $validator->getErrors();
                foreach ($errors as $field => $messagesArray) {
                    foreach ($messagesArray as $message) {
                        $messages[] = ucfirst($field) . ": " . $message;
                    }
                }
                $errorMessage = implode("<br>", $messages);
                Session::flash('error', $errorMessage);
            } else {
                if ($this->languagesModel->getLanguageByCode($code)) {
                    Session::flash('error', __('Language code already exists'));
                } else {
                    $result = $this->languagesModel->addLanguage($data);

                    if (!isset($result['success']) || !isset($result['id'])) {
                        Session::flash('error', __('Failed to add language'));
                    } else {
                        Session::flash('success', __('Language added successfully'));
                        if ($default) {
                            $this->_setdefault($result['id']);
                        }
                        \System\Libraries\Events::run('Backend\\LanguagesAddEvent', $result);
                        $this->_init_config();
                    }
                }
            }
        }
        redirect(admin_url('languages'));
    }

    // Edit language
    public function edit($id)
    {
        $language = $this->languagesModel->getLanguageById($id);

        if (!$language) {
            Session::flash('error', __('Language not found'));
            redirect(admin_url('languages'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf_token = S_POST('csrf_token') ?? '';
            $name = S_POST('name') ?? '';
            $code = S_POST('code') ?? '';
            $flag = S_POST('flag') ?? '';
            $locale = S_POST('locale') ?? '';
            $status = S_POST('status') ?? 'inactive';
            $default = S_POST('is_default') ?? 0;

            // Auto-generate locale if not provided: code_FLAG (e.g., en_US, vi_VN)
            if (empty($locale) && !empty($code) && !empty($flag)) {
                $locale = strtolower($code) . '_' . strtoupper($flag);
            }

            if (!empty($this->languagesModel->getLanguageByCode($code, $id))) {
                Session::flash('error', __('Language code already exists'));
                redirect(admin_url('languages'));
            }

            $data = [
                'name' => $name,
                'code' => strtolower($code),
                'flag' => strtolower($flag),
                'locale' => $locale,
                'status' => $status,
                'is_default' => $default,
            ];

            // check CSRF token
            if (!Session::csrf_verify($csrf_token)) {
                Session::flash('error', __('csrf_failed'));
                redirect(admin_url('languages'));
            }

            $rules = [
                'name' =>  [
                    'rules' => [Validate::length(3, 80)],
                    'messages' => [sprintf(__('length_error'), 3, 80)]
                ],
                'code' => [
                    'rules' => [Validate::alpha(), Validate::lowercase(), Validate::length(2, 2)],
                    'messages' => [__('notalpha'), __('lowercase_error'), sprintf(__('length_error'), 2, 2)]
                ],
                'flag' => [
                    'rules' => [Validate::alpha(), Validate::lowercase(), Validate::length(2, 2)],
                    'messages' => [__('notalpha'), __('lowercase_error'), sprintf(__('length_error'), 2, 2)]
                ],
                'locale' => [
                    'rules' => [Validate::length(2, 15)],
                    'messages' => [sprintf(__('length_error'), 2, 15)]
                ],
                'is_default' => [
                    'rules' => [Validate::in([0, 1])],
                    'messages' => [__('in_error')]
                ],
                'status' => [
                    'rules' => [Validate::in(['active', 'inactive'])],
                    'messages' => [__('in_error')]
                ]
            ];
            $validator = new Validate();
            if (!$validator->check($data, $rules)) {
                $errors = $validator->getErrors();
                foreach ($errors as $field => $messagesArray) {
                    foreach ($messagesArray as $message) {
                        $messages[] = ucfirst($field) . ": " . $message;
                    }
                }
                $errorMessage = implode("<br>", $messages);
                Session::flash('error', $errorMessage);
            } else {
                $result = $this->languagesModel->setLanguage($id, $data);
                if (!isset($result['success'])) {
                    Session::flash('error', __('Failed to update language'));
                } else {
                    Session::flash('success', __('Language updated successfully'));
                    \System\Libraries\Events::run('Backend\\LanguagesEditEvent', $result);
                    if ($default) {
                        $this->_setdefault($id);
                    }
                    $this->_init_config();
                }
            }
            redirect(admin_url('languages'));
        }


        $this->data('csrf_token', Session::csrf_token()); //token security
        $this->data('language', $language);
        $this->data('title', __('Edit Language') . ' ' . $language['name']);
        View::addJs('languages-page-js', 'js/language.js', [], null, true, false, true, false);
        echo View::make('languages_edit', $this->data)->render();
    }

    // Delete language
    public function delete($id)
    {
        $language = $this->languagesModel->getLanguageById($id);

        if (!$language) {
            Session::flash('error', __('Language not found'));
            redirect(admin_url('languages'));
        }

        // Don't allow deletion of default language
        if ($language['is_default'] == 1) {
            Session::flash('error', __('Cannot delete default language'));
            redirect(admin_url('languages'));
        }

        if ($this->languagesModel->deleteLanguage([$id])) {
            Session::flash('success', __('Language deleted successfully'));
            \System\Libraries\Events::run('Backend\\LanguagesDeleteEvent', $language);
            $this->_init_config();
            redirect(admin_url('languages'));
        } else {
            Session::flash('error', __('Failed to delete language'));
            redirect(admin_url('languages'));
        }
    }

    public function setdefault($id)
    {
        if (!$this->_setdefault($id)) {
            Session::flash('error', __('Failed to set default language'));
        } else {
            Session::flash('success', __('Default language updated successfully'));
            $this->_init_config();
        }
        redirect(admin_url('languages'));
    }

    public function _setdefault($id)
    {
        $language = $this->languagesModel->getLanguageById($id);

        if (!$language) {
            Session::flash('error', __('Language not found'));
            redirect(admin_url('languages'));
        }

        $this->languagesModel->unsetDefaultLanguage();

        $data = [
            'is_default' => 1,
            'status' => 'active'
        ];
        $result = $this->languagesModel->setLanguage($id, $data);
        if (!isset($result['success']) || !isset($result['id'])) {
            return false;
        } else {
            return true;
        }
    }

    public function _init_config()
    {
        // Get language list from database
        $lang_list = $this->languagesModel->getActiveLanguages();

        // Get language code list
        $codes = array_column($lang_list, 'code');
        $names = array_column($lang_list, 'name');
        $configLanguages = array();
        foreach ($lang_list as $lang) {
            $configLanguages[$lang['code']] = [
                'name' => $lang['name'],
                'flag' => $lang['flag'],
                'locale' => $lang['locale'],
            ];
        }
        // Get default language
        $default_language = $this->languagesModel->getDefaultLanguage();
        $default_code = !empty($default_language['code']) ? $default_language['code'] : null;
        if (empty($default_code) || empty($lang_list)) {
            return false;
        }

        // Create config file content using "heredoc" for easy reading and editing
        $config_content = <<<'EOD'
define('APP_LANG_DF', '%s');
define('APP_LANGUAGES', %s);

$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_path = preg_replace('#/+#', '/', $uri_path); // Replace multiple consecutive / with a single /
$uri_segments = explode('/', trim($uri_path, '/'));

// Check if the first segment is in the language list
if (!empty($uri_segments[0]) && isset(APP_LANGUAGES[$uri_segments[0]])) {
    define('APP_LANG', $uri_segments[0]);
    define('APP_LOCALE', APP_LANGUAGES[$uri_segments[0]]['locale']);
} else {
    if (!empty($_REQUEST['lang']) && isset(APP_LANGUAGES[$_REQUEST['lang']])) {
        define('APP_LANG', $_REQUEST['lang']);
        define('APP_LOCALE', APP_LANGUAGES[$_REQUEST['lang']]['locale']);
    }else{
        define('APP_LANG', APP_LANG_DF);
        define('APP_LOCALE', APP_LANGUAGES[APP_LANG_DF]['locale']);
    }
}
unset($uri_path);
unset($uri_segments);
EOD;

        // Replace placeholders in heredoc
        $config_content = sprintf($config_content, $default_code, var_export($configLanguages, true));
        // Config file save path
        $config_path = PATH_APP . 'Config/Languages.php';

        // Save content to file
        try {
            file_put_contents($config_path, "<?php\n" . $config_content);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($config_path);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    public function changestatus($id)
    {
        $language = $this->languagesModel->getLanguageById($id);

        if (!$language) {
            Session::flash('error', __('Language not found'));
            redirect(admin_url('languages'));
        }
        if ($language['is_default'] == 1) {
            Session::flash('error', __('Cannot change status of default language'));
            redirect(admin_url('languages'));
        }
        $status = $language['status'] == 'active' ? 'inactive' : 'active';
        $data = [
            'status' => $status
        ];
        $status = $this->languagesModel->setLanguage($id, $data);

        if (!$status['success']) {
            Session::flash('error', __('Failed to change language status'));
        } else {
            Session::flash('success', __('Language status updated successfully'));
            $this->_init_config();
        }
        redirect(admin_url('languages'));
    }
}
