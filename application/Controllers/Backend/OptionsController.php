<?php

namespace App\Controllers\Backend;

use App\Controllers\BackendController;
use System\Libraries\Session;
use App\Models\OptionsModel;
use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;
use System\Libraries\Validate;

class OptionsController extends BackendController
{

    protected $optionsModel;
    protected $post_lang;

    protected $allowed_types;
    public function __construct()
    {
        parent::__construct();
        $this->optionsModel = new OptionsModel();

        Flang::load('Backend/Global', APP_LANG);
        Flang::load('Backend/Options', APP_LANG);
        $config_files = config('files', 'Uploads');
        $this->allowed_types = $config_files['allowed_types'] ?? [];
        $this->post_lang = S_REQUEST('post_lang') ?? APP_LANG;
    }

    /**
     * Display options management lists with pagination.
     * Shows all options in a table format with search, filter, and pagination.
     */
    public function lists()
    {
        // Get filter parameters
        $search = S_GET('q') ?? '';
        $limit = S_GET('limit') ?? option('default_limit');
        $type = S_GET('type') ?? '';
        $status = S_GET('status') ?? '';
        $sort = S_GET('sort') ?? 'id';
        $order = S_GET('order') ?? 'DESC';
        $paged = S_GET('page') ?? 1;

        // Validate page and limit
        if ($paged < 1) {
            $paged = 1;
        }
        if ($limit < 1) {
            $limit = option('default_limit');
        }

        // Build WHERE clause
        $where = '';
        $params = [];

        // Search filter (name, label, description)
        if (!empty($search)) {
            $where = "(name LIKE ? OR label LIKE ? OR description LIKE ?)";
            $params = [
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%'
            ];
        }

        // Type filter
        if (!empty($type)) {
            if (!empty($where)) {
                $where .= " AND type = ?";
            } else {
                $where = "type = ?";
            }
            $params[] = $type;
        }

        // Status filter
        if (!empty($status)) {
            if (!empty($where)) {
                $where .= " AND status = ?";
            } else {
                $where = "status = ?";
            }
            $params[] = $status;
        }

        // Build ORDER BY clause
        if (!empty($sort) && !empty($order)) {
            $orderBy = $sort . ' ' . $order;
        } else {
            $orderBy = 'id DESC';
        }

        // Get paginated options
        $options = $this->optionsModel->getOptionsFieldsPagination('*', $where, $params, $orderBy, $paged, $limit);

        // Pass data to view
        $this->data('options', $options);
        $this->data('search', $search);
        $this->data('limit', $limit);
        $this->data('type', $type);
        $this->data('status', $status);
        $this->data('sort', $sort);
        $this->data('order', $order);
        $this->data('title', __('Options Management'));
        $this->data('csrf_token', Session::csrf_token());

        echo View::make('options_lists', $this->data)->render();
    }

    /**
     * Display options settings form (original index functionality).
     * This is the form where users can submit/update option values.
     */
    public function index()
    {
        if (!empty($_POST)) {
            return $this->_updateValue($_POST);
        }

        // ✅ Get options metadata (structure) from options table
        $options = $this->optionsModel->getOptions();

        // ✅ Load values from storage system instead of options table
        foreach ($options as $index => $option) {
            $key = $option['name'];

            // Get optional config to check if synchronous
            $optional = json_decode($option['optional'], true) ?? [];
            $isSynchronous = $optional['synchronous'] ?? false;

            // Determine language for storage
            $lang = $this->post_lang;
            if ($isSynchronous) {
                $lang = 'all'; // Synchronous field
            }

            // ✅ Get value from storage
            $value = storage_get($key, 'application', $lang, $option['default_value'] ?? null);

            // Set value for display
            $options[$index]['value'] = $value;
        }

        $this->data('options', $options);

        $this->data('errors', []);
        $this->data('post_lang', $this->post_lang);
        $this->data('title', __('Website Settings'));
        $this->data('csrf_token', Session::csrf_token(600));
        echo View::make('options_index', $this->data)->render();
    }

    //Use _ for can not access = url.
    private function _updateValue($data)
    {
        $rules = [];
        $errors = [];
        $saved = 0;

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                // ✅ Get option metadata from options table (structure only)
                $option = $this->optionsModel->getByName($key);
                if (empty($option)) {
                    unset($data[$key]);
                    continue;
                }

                // ✅ Type conversion
                switch ($option['type']) {
                    case 'Boolean':
                        if ($value == 'false' || $value == 'False' || $value == 0 || $value == '0') {
                            $value = false;
                        } else {
                            $value = true;
                        }
                        break;
                    case 'Number':
                        $optionOptional = json_decode($option['optional'], true);
                        if (!empty($optionOptional) && isset($optionOptional['step']) && $optionOptional['step'] < 1) {
                            $value = (float)$value;
                        } else {
                            $value = (int)$value;
                        }
                        break;
                    case 'Reference':

                        // Xử lý Reference field: chỉ lưu ID(s)
                        // Nếu value là JSON string, decode nó
                        $value = _json_decode($value);
                        // Lấy optional config để check multiple mode
                        $optionOptional = _json_decode($option['optional']);
                        $isMultiple = !empty($optionOptional['multiple']) ||
                            (!empty($optionOptional['reference']) &&
                                (!empty($optionOptional['reference']['selectionMode']) &&
                                    $optionOptional['reference']['selectionMode'] === 'multiple'));

                        if ($isMultiple) {
                            // Multiple mode: lưu array ids
                            if (is_array($value)) {
                                // Nếu là array objects, extract ids
                                $ids = [];
                                foreach ($value as $item) {
                                    if (is_array($item) && isset($item['id'])) {
                                        $ids[] = (int)$item['id'];
                                    } elseif (is_numeric($item)) {
                                        $ids[] = (int)$item;
                                    } elseif (is_object($item) && isset($item->id)) {
                                        $ids[] = (int)$item->id;
                                    }
                                }
                                $value = $ids;
                            } elseif (is_numeric($value)) {
                                // Nếu chỉ là 1 số, convert thành array
                                $value = [(int)$value];
                            } elseif (is_object($value) && isset($value->id)) {
                                // Nếu là object, extract id
                                $value = [(int)$value->id];
                            } else {
                                $value = [];
                            }
                        } else {
                            // Single mode: lưu single id
                            if (is_array($value)) {
                                // Nếu là array, lấy id đầu tiên
                                if (!empty($value[0])) {
                                    $firstItem = $value[0];
                                    if (is_array($firstItem) && isset($firstItem['id'])) {
                                        $value = (int)$firstItem['id'];
                                    } elseif (is_numeric($firstItem)) {
                                        $value = (int)$firstItem;
                                    } elseif (is_object($firstItem) && isset($firstItem->id)) {
                                        $value = (int)$firstItem->id;
                                    } else {
                                        $value = null;
                                    }
                                } else {
                                    // Array ( [id] => 6 [display_text] => 6 )
                                    $value = $value['id'];
                                }
                            }
                        }
                        break;
                }

                // ✅ Get optional config for validation and synchronous check
                $optional = _json_decode($option['optional']);
                $rules[$key] = $this->_rules_validate($optional);

                // ✅ Determine language for storage
                $lang = $this->post_lang;
                if (isset($optional['synchronous']) && $optional['synchronous']) {
                    $lang = 'all'; // Synchronous field
                }

                // ✅ Save to storage system
                if (set_option($key, $value, $lang)) {
                    $saved++;
                } else {
                    $errors[] = sprintf(__('Failed to save option: %s'), $key);
                }
            }
        }

        // ✅ Validate
        $validator = new Validate();
        if (!$validator->check($data, $rules)) {
            $errors = array_merge($errors, $validator->getErrors());
        }

        $errorString = '';
        foreach ($errors as $fieldName => $error) {
            $errorString .= '<b>' . $fieldName . '</b>: ' . implode(', ', $error) . '. &nbsp; &nbsp; &nbsp;';
        }

        // ✅ Set flash messages
        if (!empty($errors)) {
            Session::flash('error', $errorString);
        } else {
            Session::flash('success', __('Options updated successfully') . ' (' . $saved . ' ' . __('items') . ')');
        }

        redirect(admin_url('options/index/') . '?post_lang=' . $this->post_lang);
    }
    private function _rules_validate($optional)
    {
        // Decode JSON if necessary
        $optional = is_string($optional) ? json_decode($optional, true) : $optional;

        // Result array containing rules and messages
        $rules = [
            'rules' => [],
            'messages' => []
        ];

        // Check required field
        if (!empty($optional['required'])) {
            $rules['rules'][] = Validate::notEmpty();
            $rules['messages'][] = __('not_empty');
        }

        // Check minimum and maximum length
        if (!empty($optional['min']) || !empty($optional['max'])) {
            $min = !empty($optional['min']) ? $optional['min'] : 1;
            $max = !empty($optional['max']) ? $optional['max'] : 255;
            $rules['rules'][] = Validate::length($min, $max);
            $rules['messages'][] = __('not_min_max');
        }

        // Check data type if it's 'Number'
        if (!empty($optional['type']) && $optional['type'] == 'Number') {
            $rules['rules'][] = Validate::NumericVal();
            $rules['messages'][] = "{$optional['label']} must be a number.";
        }

        // Check if it's 'slug'
        if (!empty($optional['field_name']) && $optional['field_name'] == 'slug') {
            $rules['rules'][] = Validate::lowercase();
            $rules['messages'][] = __('lowercase_error');
        }

        // Check if it's email
        if (!empty($optional['type']) && $optional['type'] == 'Email') {
            $rules['rules'][] = Validate::email();
            $rules['messages'][] = __('email_valid');
        }

        // Check if it's URL
        if (!empty($optional['type']) && $optional['type'] == 'URL') {
            $rules['rules'][] = Validate::url();
            $rules['messages'][] = __('url_valid');
        }

        // Check if it's date field
        if (!empty($optional['type']) && $optional['type'] == 'Date') {
            $rules['rules'][] = Validate::date();
            $rules['messages'][] = __('date_valid');
        }

        return $rules;
    }

    private function _validate($data = [], $isField = false)
    {
        //Start Validation
        $errors = [];
        //validate Upload File Type if is Upload Files form.
        if (!empty($data['allow_types'])) {
            $filesDiff = array_diff($data['allow_types'], $this->allowed_types);
            if (!empty($filesDiff)) {
                $errors[] =  sprintf(__('posttype_allow_types_required'), implode(',', $filesDiff));
            }
        }
        $validator = new Validate();
        $rules = [
            'type' => [
                'rules' => [Validate::in(['Text', 'Email', 'Number', 'Password',  'Date', 'DateTime', 'ColorPicker',  'URL', 'OEmbed', 'Textarea', 'Boolean', 'Checkbox', 'Radio', 'Select', 'File', 'Image', 'WYSIWYG', 'Reference', 'Repeater', 'User'])],
                'messages' => [__('field_type_invalid')]
            ],
            'label' => [
                'rules' => [Validate::length(1, 100)],
                'messages' => [__('field_label_length')]
            ],
            'description' => [
                'rules' => [Validate::length(0, 250)],
                'messages' => [
                    __('field_description_length')
                ]
            ],
            'status' => [
                'rules' => [Validate::in([true, false])],
                'messages' => [
                    __('field_status_invalid')
                ]
            ],
            'visibility' => [
                'rules' => [Validate::in([true, false])],
                'messages' => [
                    __('field_visibility_invalid')
                ]
            ],
            'collapsed' => [
                'rules' => [Validate::in([true, false])],
                'messages' => [__('field_collapsed_invalid')]
            ],
            'css_class' => [
                'rules' => [Validate::lowercase()],
                'messages' => [__('field_css_class_lowercase')]
            ],
            'placeholder' => [
                'rules' => [Validate::length(0, 250)],
                'messages' => [
                    __('field_placeholder_length')
                ]
            ],
            'default_value' => [
                'rules' => [Validate::length(0, 9999)],
                'messages' => [
                    __('field_default_value_length')
                ]
            ],
            'order' => [
                'rules' => [Validate::notEmpty(), Validate::NumericVal()],
                'messages' => [
                    __('field_order_required'),
                    __('field_order_numeric')
                ]
            ],
            'min' => [
                'rules' => [Validate::optional(Validate::NumericVal())],
                'messages' => [__('field_min_invalid')]
            ],
            'max' => [
                'rules' => [Validate::optional(Validate::NumericVal())],
                'messages' => [__('field_max_invalid')]
            ],
            'rows' => [
                'rules' => [Validate::optional(Validate::NumericVal())],
                'messages' => [__('field_rows_invalid')]
            ],

            'multiple' => [
                'rules' => [Validate::in([null, true, false])],
                'messages' => [__('field_multiple_invalid')]
            ],
            'position' => [
                'rules' => [Validate::in(['left', 'top', 'right', 'bottom'])],
                'messages' => [
                    __('field_position_invalid')
                ]
            ],
            'width_unit' => [
                'rules' => [Validate::in(['px', '%', 'em', 'rem', 'vw', 'vh'])],
                'messages' => [
                    __('field_width_unit_invalid')
                ]
            ],
            'width_value' => [
                'rules' => [Validate::NumericVal()],
                'messages' => [
                    __('field_width_value_invalid')
                ]
            ],
        ];
        if (!$isField && !empty($data['name'])) {
            $rules['name'] = [
                'rules' => [Validate::length(3, 100), Validate::regex('/^[a-z0-9_]+$/')],
                'messages' => [
                    __('field_name_length'),
                    sprintf(__('field_name_invalid'), 'a-z0-9_')
                ]
            ];
        }

        if (!$validator->check($data, $rules)) {
            $errors = $validator->getErrors();
        }
        if (!empty($data['fields']) && is_array($data['fields'])) {
            foreach ($data['fields'] as $item) {
                $itemErrors = $this->_validate($item, true);
                if (is_array($itemErrors) && !empty($itemErrors)) {
                    $errors = array_merge($errors, $itemErrors);
                }
            }
        }
        return $errors;
    }

    // get render posttype
    public function add()
    {
        if (HAS_POST('list_options') && HAS_POST('csrf_token')) {
            if (!Session::csrf_verify(S_POST('csrf_token'))) {
                $this->data('errors', ["csrf_token" => __('csrf_failed')]);
                Session::flash('error', __('Invalid security token'));
            } else {
                $errors = array();
                $options_json = S_POST('list_options') ?? '';
                $options = json_decode($options_json, true);
                $addItems = [];
                foreach ($options as $key => $option) {
                    $errors[$key] = $this->_validate($option, false);
                    if (is_array($errors[$key]) && !empty($errors[$key])) {
                        continue;
                    } else {
                        $errors[$key] = [];
                        if ($this->optionsModel->getByName($option['name'])) {
                            //Data Option is valid but Option exist in Database.
                            $errors[$key]['duplicates'] = [__('database_exist') . ' - ' . $option['name']];
                        } else {
                            if (empty($option['option_group'])) {
                                $option['option_group'] = 'general';
                            }
                            $addItem = array(
                                'label' => $option['label'],
                                'type' => $option['type'],
                                'name' => $option['name'],
                                'description' => $option['description'],
                                'status' => $option['status'],
                                'optional' => json_encode($option),
                                'created_at' => _DateTime(),
                                'updated_at' => _DateTime()
                            );
                            if ($this->optionsModel->addOptions($addItem)) {
                                $addItems[] = $addItem;
                                unset($options[$key]);
                            } else {
                                $errors[$key]['database'] = [__('database_add_fail') . ' - ' . $option['name']];
                            }
                        }
                    }
                }
                \System\Libraries\Events::run('Backend\\OptionAddEvent', $addItems);
                if (count($options) <= 0) {
                    Session::flash('success', __('Options added successfully'));
                    redirect(admin_url('options/index'));
                }
                $_POST['list_options'] = json_encode($options);
                $this->data('errors', $errors);
                Session::flash('error', __('Please fix the errors below') . ':' . json_encode($errors));
            }
        }

        // get list options group =.=
        $option_group = $this->optionsModel->getbyname('option_groups');
        if (!empty($option_group) && !empty($option_group['optional'])) {
            $optional = is_string($option_group['optional']) ? json_decode($option_group['optional'], true) : $option_group['optional'];
            $option_groups =  $optional['options'];
        }
        $allPostTypes = posttype_active();
        $this->data('allPostTypes', $allPostTypes);
        $this->data('allowed_types', $this->allowed_types);
        $this->data('option_groups', $option_groups);
        $this->data('isEditing', false);
        $this->data('title', __('options_add_welcome'));
        $this->data('csrf_token', Session::csrf_token(600));
        echo View::make('options_add', $this->data)->render();
    }

    public function edit($id)
    {
        $option = $this->optionsModel->getById($id);
        if (!$option) {
            redirect(admin_url('options/index/'));
        } else {
            $editOption = json_decode($option['optional'], true);
            $editOption['id'] = $option['id'];
            $editOption['created_at'] = $option['created_at'];
            $editOption['updated_at'] = $option['updated_at'];
            $editOption['collapsed'] = false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!empty(S_POST('csrf_token')) && Session::csrf_verify(S_POST('csrf_token'))) {

                $options = S_POST('list_options') ?? '';
                $options = is_string($options) ? json_decode($options, true) : $options;
                if (!empty($options)) $option = $options[0];
                if (!empty($option)) {
                    $errors = $this->_validate($option, false);
                    $checkExit = $this->optionsModel->getByName($option['name']);
                    if ($checkExit && $checkExit['id'] != $id) {
                        //Data Option is valid but Option exist in Database.
                        $errors['duplicates'] = [__('database_exist') . ' - ' . $option['name']];
                    }
                    if (empty($errors)) {
                        $optionalData = $option;
                        $newdata = array(
                            'id' => $option['id'],
                            'label' => $option['label'],
                            'type' => $option['type'],
                            'name' => $option['name'],
                            'description' => $option['description'],
                            'status' => $option['status'],
                            'optional' => json_encode($optionalData),
                            'created_at' => _DateTime(),
                            'updated_at' => _DateTime()
                        );
                        if ($this->optionsModel->setOptions($id, $newdata)) {
                            \System\Libraries\Events::run('Backend\\OptionEditEvent', $newdata);
                            Session::flash('success', __('Option updated successfully'));
                            redirect(admin_url('options/edit/' . $id));
                        } else {
                            Session::flash('error', __('Failed to update option'));
                        }
                    } else {
                        Session::flash('error', __('Please fix the errors below') . ':' . json_encode($errors));
                    }
                }
            } else {
                $this->data('errors', ["csrf_token" => __('csrf_failed')]);
                Session::flash('error', __('Invalid security token'));
            }
        }
        $option_group = $this->optionsModel->getbyname('option_groups');
        if (!empty($option_group) && !empty($option_group['optional'])) {
            $optional = is_string($option_group['optional']) ? json_decode($option_group['optional'], true) : $option_group['optional'];
            $option_groups =  $optional['options'];
        }
        $allPostTypes = posttype_active();
        $this->data('allPostTypes', $allPostTypes);
        $this->data('option_groups', $option_groups);
        $this->data('allowed_types', $this->allowed_types);
        $this->data('options', [$editOption]);
        $this->data('isEditing', true);
        $this->data('title', __('option_edit_title') . ' ' . (isset($editOption['name']) ? $editOption['name'] : ''));
        $this->data('csrf_token', Session::csrf_token(600)); // Create new token for first load
        echo View::make('options_add', $this->data)->render();
    }

    public function delete($id)
    {
        // just remove, check if name is option_group then skip

        $option = $this->optionsModel->getById($id);
        if ($id > 1 && $option && $option['name'] != 'option_group') {
            if ($this->optionsModel->delOptions($id)) {
                \System\Libraries\Events::run('Backend\\OptionDeleteEvent', $option);
                Session::flash('success', __('Option deleted successfully'));
                redirect(admin_url('options/lists'));
            } else {
                Session::flash('error', __('Failed to delete option'));
                redirect(admin_url('options/lists'));
            }
        } else {
            Session::flash('error', __('Option not found or cannot be deleted'));
            redirect(admin_url('options/lists'));
        }
    }
}
