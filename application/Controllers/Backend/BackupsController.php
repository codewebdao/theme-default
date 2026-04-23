<?php
namespace App\Controllers\Backend;

use App\Controllers\BackendController;
use App\Models\OptionsModel;
use System\Libraries\Session;
use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;
use System\Libraries\Validate;

class BackupsController extends BackendController {
    
    protected $optionsModel;
    protected $backupPath;

    public function __construct()
    {
        if (!config('backups')) {
            //Show notice user have disable backup at config file
            Session::flash('error', Flang::__("You have disable backup at application/Config/Config.php"));
            redirect(admin_url('home'));
        }
        parent::__construct();
        $this->writableFolder = explode(PATH_ROOT, WRITE_PATH)[1];
        $this->backupPath = PATH_WRITE . 'backups/';
        Flang::load('Backend/Global');
        Flang::load('Backend/Backups');
        $this->optionsModel = new OptionsModel();
    }

    // Danh sách backup
    public function index() {
        // Lấy danh sách backup từ option site_backups_list
        $backups_data = option('site_backups_list', APP_LANG, false);
        $backups_data = is_array($backups_data) ? $backups_data : json_decode($backups_data??'[]', true);
        if (empty($backups_data)) {
            $backups_data = [];
        }

        $search = S_GET('q') ?? '';
        $type = S_GET('type') ?? '';
        $sort = S_GET('sort') ?? 'created_at';
        $order = S_GET('order') ?? 'DESC';

        // Filter data
        $filtered_data = $backups_data;
        
        if (!empty($search)) {
            $filtered_data = array_filter($backups_data, function($backup) use ($search) {
                return stripos($backup['name'], $search) !== false || 
                       stripos($backup['description'], $search) !== false;
            });
        }

        if (!empty($type)) {
            $filtered_data = array_filter($filtered_data, function($backup) use ($type) {
                return $backup['type'] === $type;
            });
        }

        // Sort data
        if (!empty($sort)) {
            usort($filtered_data, function($a, $b) use ($sort, $order) {
                $a_val = $a[$sort] ?? '';
                $b_val = $b[$sort] ?? '';
                
                if ($order === 'ASC') {
                    return $a_val <=> $b_val;
                } else {
                    return $b_val <=> $a_val;
                }
            });
        }

        // Show all data without pagination
        $total = count($filtered_data);

        $backups = [
            'data' => $filtered_data,
            'total' => $total
        ];
        
        $this->data('backups', $backups);
        $this->data('title', __('Backup Management'));
        $this->data('csrf_token', Session::csrf_token());
        
        echo View::make('backups_index', $this->data)->render();
    }

    // Cấu hình backup
    public function settings() {
        if (HAS_POST('submit')) {
            $csrf_token = S_POST('csrf_token') ?? '';
            if (!Session::csrf_verify($csrf_token)) {
                $this->data('error', __('csrf_failed'));
            } else {
                $input = [
                    'backup_auto' => S_POST('backup_auto') ?? 0,
                    'backup_frequency' => S_POST('backup_frequency') ?? 'daily',
                    'backup_time' => S_POST('backup_time') ?? '02:00',
                    'backup_max' => S_POST('backup_max') ?? 10,
                    'backup_database' => S_POST('backup_database') ?? 1,
                    'backup_files' => S_POST('backup_files') ?? 1,
                    'backup_email_notifications' => S_POST('backup_email_notifications') ?? 0,
                    'backup_email' => S_POST('backup_email') ?? '',
                ];

                $rules = [
                    'backup_auto' => [
                        'rules' => [Validate::in([0, 1])],
                        'messages' => [__('Invalid auto backup setting')]
                    ],
                    'backup_frequency' => [
                        'rules' => [Validate::in(['hourly', 'daily', 'weekly', 'monthly'])],
                        'messages' => [__('Invalid backup frequency')]
                    ],
                    'backup_time' => [
                        'rules' => [Validate::regex('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/')],
                        'messages' => [__('Invalid backup time format')]
                    ],
                    'backup_max' => [
                        'rules' => [Validate::NumericVal(), Validate::between(1, 100)],
                        'messages' => [__('Max backups must be between 1 and 100'), __('Max backups must be between 1 and 100')]
                    ],
                    'backup_database' => [
                        'rules' => [Validate::in([0, 1])],
                        'messages' => [__('Invalid database backup setting')]
                    ],
                    'backup_files' => [
                        'rules' => [Validate::in([0, 1])],
                        'messages' => [__('Invalid files backup setting')]
                    ],
                    'backup_email_notifications' => [
                        'rules' => [Validate::in([0, 1])],
                        'messages' => [__('Invalid email notification setting')]
                    ],
                    'backup_email' => [
                        'rules' => [Validate::optional(Validate::email())],
                        'messages' => [__('Invalid email address')]
                    ]
                ];

                $validator = new Validate();
                if (!$validator->check($input, $rules)) {
                    $errors = $validator->getErrors();
                    $this->data('errors', $errors);
                } else {
                    // Chuyển đổi settings thành format Repeater
                    $repeater_data = [];
                    foreach ($input as $key => $value) {
                        $repeater_data[] = [
                            'backup_key' => $key,
                            'backup_value' => $value
                        ];
                    }
                    
                    // Lưu vào option site_backups
                    set_option('site_backups', $repeater_data);
                    
                    Session::flash('success', __('Backup settings updated successfully'));
                    redirect(admin_url('backups/settings'));
                }
            }
        }

        $settings = $this->_getSettings();
        $settings['csrf_token'] = Session::csrf_token(600);
        $this->data('settings', $settings);
        $this->data('title', __('Backup Settings'));
        $this->data('csrf_token', Session::csrf_token());
        
        echo View::make('backups_settings', $this->data)->render();
    }

    // Helper method để lấy settings
    private function _getSettings()
    {
        $backup_settings = option('site_backups', APP_LANG, false);
        $backup_settings = is_array($backup_settings) ? $backup_settings : json_decode($backup_settings??'[]', true);
        if (empty($backup_settings)) {
            $backup_settings = [];
        }
        // Default settings
        $defaults = [
            'backup_auto' => '0',
            'backup_frequency' => 'daily',
            'backup_time' => '02:00',
            'backup_max' => '10',
            'backup_database' => '1',
            'backup_files' => '1',
            'backup_email_notifications' => '0',
            'backup_email' => ''
        ];

        // Nếu có settings từ option, merge với defaults
        if (!empty($backup_settings) && is_array($backup_settings)) {
            $result = [];
            foreach ($backup_settings as $setting) {
                if (isset($setting['backup_key']) && isset($setting['backup_value'])) {
                    $result[$setting['backup_key']] = $setting['backup_value'];
                }
            }
            return array_merge($defaults, $result);
        }

        return $defaults;
    }

    // Tạo backup thủ công
    public function create() {
        if (HAS_POST('submit')) {
            $csrf_token = S_POST('csrf_token') ?? '';
            if (!Session::csrf_verify($csrf_token)) {
                return $this->error(__('csrf_failed'), [], 403);
            }

            $type = S_POST('type') ?? 'full';
            $name = S_POST('name') ?? '';
            $description = S_POST('description') ?? '';

            $input = [
                'type' => $type,
                'name' => $name,
                'description' => $description,
            ];

            $rules = [
                'type' => [
                    'rules' => [Validate::in(['full', 'database', 'files'])],
                    'messages' => [__('Invalid backup type')]
                ],
                'name' => [
                    'rules' => [Validate::notEmpty(), Validate::length(3, 100)],
                    'messages' => [__('Backup name is required'), __('Backup name must be between 3 and 100 characters')]
                ],
                'description' => [
                    'rules' => [Validate::optional(Validate::length(0, 255))],
                    'messages' => [__('Description too long')]
                ]
            ];

            $validator = new Validate();
            if (!$validator->check($input, $rules)) {
                $errors = $validator->getErrors();
                return $this->error(__('Validation failed'), $errors, 400);
            }

            $result = $this->createBackup($input);
            if ($result['success']) {
                return $this->success($result, __('Backup created successfully'));
            } else {
                return $this->error($result['message'] ?? __('Failed to create backup'), [], 500);
            }
        }

        return $this->error(__('Invalid request'), [], 400);
    }

    // Helper method để tạo backup
    private function createBackup($data)
    {
        try {
            if (!is_dir($this->backupPath)) {
                mkdir($this->backupPath, 0755, true);
            }

            // Lấy domain name
            $domain = option('site_url', 'localhost');
            $domain = str_replace(['http://', 'https://'], '', $domain);
            $domain = str_replace(['/', '\\'], '-', $domain);
            $domain = preg_replace('/[^a-zA-Z0-9\-\.]/', '', $domain);
            if (empty($domain)) {
                $domain = 'localhost';
            }

            $timestamp = date('Y-m-d__H-i-s');
            $random_string = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 15);
            $extension = ($data['type'] === 'database') ? 'sql.gz' : 'zip';
            $filename = "{$domain}-{$timestamp}-{$random_string}.{$extension}";
            $file_path = $this->backupPath . $filename;

            // Tạo backup dựa trên type
            $result = $this->performBackup($data['type'], $file_path);
            
            if (!$result['success']) {
                return $result;
            }

            $file_size = file_exists($file_path) ? filesize($file_path) : 0;

            $backup_data = [
                'id' => uniqid(),
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'type' => $data['type'],
                'file_path' => $file_path,
                'file_size' => $file_size,
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Lấy danh sách backup hiện tại
            $backups_list = option('site_backups_list', APP_LANG, false);
            $backups_list = is_array($backups_list) ? $backups_list : json_decode($backups_list??'[]', true);
            if (empty($backups_list)) {
                $backups_list = [];
            }

            // Thêm backup mới vào đầu danh sách
            array_unshift($backups_list, $backup_data);

            // Lưu lại danh sách
            set_option('site_backups_list', $backups_list);
            
            return [
                'success' => true,
                'id' => $backup_data['id'],
                'message' => __('Backup created successfully')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Helper method để thực hiện backup
    private function performBackup($type, $file_path)
    {
        switch ($type) {
            case 'database':
                return $this->backupDatabase($file_path);
            case 'files':
                return $this->backupFiles($file_path);
            case 'full':
                return $this->backupFull($file_path);
            default:
                return ['success' => false, 'message' => __('Invalid backup type')];
        }
    }

    // Helper method để backup database
    private function backupDatabase($file_path)
    {
        try {
            $db_config = db_config();
            
            $dump = new \Ifsnop\Mysqldump\Mysqldump(
                "mysql:host={$db_config['db_host']};port={$db_config['db_port']};dbname={$db_config['db_database']};charset={$db_config['db_charset']}",
                $db_config['db_username'],
                $db_config['db_password'],
                [
                    'add-drop-table' => true,
                    'single-transaction' => true,
                    'lock-tables' => false,
                    'add-locks' => false,
                    'extended-insert' => true,
                    'disable-keys' => true,
                    'where' => '',
                    'no-create-info' => false,
                    'skip-triggers' => true,
                    'add-drop-trigger' => false,
                    'skip-comments' => true,
                    'skip-dump-date' => true,
                    'skip-definer' => true,
                    'compress' => \Ifsnop\Mysqldump\Mysqldump::GZIP
                ]
            );
            
            $dump->start($file_path);
            
            if (file_exists($file_path)) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to create database backup file'];
            }
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Database backup failed: ' . $e->getMessage()];
        }
    }

    // Helper method để backup files
    private function backupFiles($file_path)
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($file_path, \ZipArchive::CREATE) !== TRUE) {
                return ['success' => false, 'message' => 'Cannot create zip file'];
            }
            $exclude_dirs = [
                $this->writableFolder . '/backups',  // Skip folder backups
                'node_modules',
                'vendor',
                '.git'
            ];
            
            $exclude_files = [
                'temp_db_dump.sql',  // Skip temp database dump file
                'temp_db_dump.sql.gz'  // Skip temp database dump file
            ];

            // Backup toàn bộ PATH_ROOT, chỉ skip folder backups
            $this->addDirectoryToZip($zip, PATH_ROOT, '', $exclude_dirs, $exclude_files);

            $zip->close();
            return ['success' => true];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Helper method để backup full (database + files)
    private function backupFull($file_path)
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($file_path, \ZipArchive::CREATE) !== TRUE) {
                return ['success' => false, 'message' => 'Cannot create zip file'];
            }

            // Backup database vào zip
            $db_config = db_config();
            $sql_file = $this->backupPath . 'temp_db_dump.sql.gz';
            
            try {
                $dump = new \Ifsnop\Mysqldump\Mysqldump(
                    "mysql:host={$db_config['db_host']};port={$db_config['db_port']};dbname={$db_config['db_database']};charset={$db_config['db_charset']}",
                    $db_config['db_username'],
                    $db_config['db_password'],
                    [
                        'add-drop-table' => true,
                        'single-transaction' => true,
                        'lock-tables' => false,
                        'add-locks' => false,
                        'extended-insert' => true,
                        'disable-keys' => true,
                        'where' => '',
                        'no-create-info' => false,
                        'skip-triggers' => true,
                        'add-drop-trigger' => false,
                        'skip-comments' => true,
                        'skip-dump-date' => true,
                        'skip-definer' => true,
                        'compress' => \Ifsnop\Mysqldump\Mysqldump::GZIP
                    ]
                );
                
                $dump->start($sql_file);
                
                if (file_exists($sql_file)) {
                    $zip->addFile($sql_file, 'database.sql.gz');
                }
            } catch (\Exception $e) {
                // Log error but continue with files backup
                error_log('Database backup failed in full backup: ' . $e->getMessage());
            }

            // Backup files vào zip
            $exclude_dirs = [
                $this->writableFolder . '/backups',  // Skip folder backups
                'node_modules',
                '.git'
            ];
            
            $exclude_files = [
                'temp_db_dump.sql',  // Skip temp database dump file
                'temp_db_dump.sql.gz'  // Skip temp database dump file
            ];

            // Backup toàn bộ PATH_ROOT, chỉ skip folder backups
            $this->addDirectoryToZip($zip, PATH_ROOT, '', $exclude_dirs, $exclude_files);

            $zip->close();
            
            // Xóa file SQL tạm
            if (file_exists($sql_file)) {
                unlink($sql_file);
            }
            
            return ['success' => true];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Helper method để xóa tất cả bảng trong database
    private function dropAllTables($pdo, $prefix = '')
    {
        try {
            // Tắt foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Lấy danh sách tất cả bảng
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // Xóa từng bảng
            foreach ($tables as $table) {
                // Chỉ xóa bảng có prefix phù hợp (nếu có prefix)
                if (empty($prefix) || strpos($table, $prefix) === 0) {
                    $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
                }
            }
            
            // Bật lại foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
        } catch (\Exception $e) {
            // Log error nhưng không throw để không làm gián đoạn quá trình restore
            error_log('Error dropping tables: ' . $e->getMessage());
        }
    }

    // Helper method để thêm thư mục vào zip
    private function addDirectoryToZip($zip, $source, $destination, $exclude_dirs = [], $exclude_files = [])
    {
        if (!is_dir($source)) {
            return; // Skip if source directory doesn't exist
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            // Tạo relative path từ source
            $relative_path = str_replace($source, '', $file->getPathname());
            $relative_path = ltrim($relative_path, '/\\'); // Remove leading slash/backslash
            
            // Thêm destination prefix nếu có
            if (!empty($destination)) {
                $relative_path = rtrim($destination, '/\\') . '/' . $relative_path;
            }
            
            // Kiểm tra xem có nên exclude không
            $should_exclude = false;
            
            // Check exclude directories
            foreach ($exclude_dirs as $exclude_dir) {
                if (strpos($relative_path, $exclude_dir) !== false) {
                    $should_exclude = true;
                    break;
                }
            }
            
            // Check exclude files
            if (!$should_exclude && !empty($exclude_files)) {
                $filename = basename($file->getPathname());
                foreach ($exclude_files as $exclude_file) {
                    if ($filename === $exclude_file) {
                        $should_exclude = true;
                        break;
                    }
                }
            }
            
            if (!$should_exclude) {
                if ($file->isDir()) {
                    $zip->addEmptyDir($relative_path);
                } else {
                    $zip->addFile($file->getPathname(), $relative_path);
                }
            }
        }
    }

    // Xóa backup
    public function delete($backup_id = null) {
        if(!empty($backup_id)) {
            $this->_delete($backup_id);
        } elseif(HAS_POST('ids')) {
            $ids = S_POST('ids');
            $ids = json_decode($ids, true);
            foreach($ids as $id) {
                $this->_delete($id);
            }
            $this->success([], __('Backups deleted successfully'));
        } else {
            $this->error(__('No backups selected for deletion'));
        }
        redirect(admin_url('backups/index'));
    }

    private function _delete($backup_id) {
        // Lấy danh sách backup hiện tại
        $backups_list = option('site_backups_list', APP_LANG, false);
        $backups_list = is_array($backups_list) ? $backups_list : json_decode($backups_list??'[]', true);
        if (empty($backups_list)) {
            return false;
        }

        // Tìm backup cần xóa
        $backup_index = null;
        foreach ($backups_list as $index => $backup) {
            if ($backup['id'] == $backup_id) {
                $backup_index = $index;
                break;
            }
        }

        if ($backup_index !== null) {
            $backup = $backups_list[$backup_index];
            
            // Xóa file backup
            if (file_exists($backup['file_path'])) {
                unlink($backup['file_path']);
            }

            // Xóa khỏi danh sách
            unset($backups_list[$backup_index]);
            $backups_list = array_values($backups_list); // Re-index array

            // Lưu lại danh sách
            set_option('site_backups_list', $backups_list);
            
            \System\Libraries\Events::run('Backend\\BackupsDeleteEvent', $backup_id);
            return true;
        }
        return false;
    }

    // Download backup
    public function download($backup_id) {
        $backup = $this->getBackupById($backup_id);
        
        if (!$backup) {
            Session::flash('error', __('Backup not found'));
            redirect(admin_url('backups/index'));
        }

        $file_path = $backup['file_path'];
        if (!file_exists($file_path)) {
            Session::flash('error', __('Backup file not found'));
            redirect(admin_url('backups/index'));
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }

    // Restore backup
    public function restore($backup_id) {
        $backup = $this->getBackupById($backup_id);
        
        if (!$backup) {
            Session::flash('error', __('Backup not found'));
            redirect(admin_url('backups/index'));
        }

        $file_path = $backup['file_path'];
        if (!file_exists($file_path)) {
            Session::flash('error', __('Backup file not found'));
            redirect(admin_url('backups/index'));
        }

        // Restore directly
        $result = $this->restoreBackup($backup);
        if ($result['success']) {
            Session::flash('success', __('Backup restored successfully'));
        } else {
            Session::flash('error', $result['message'] ?? __('Failed to restore backup'));
        }
        
        redirect(admin_url('backups/index'));
    }


    // Helper method để lấy backup theo ID
    private function getBackupById($id)
    {
        $backups_list = option('site_backups_list', APP_LANG, false);
        $backups_list = is_array($backups_list) ? $backups_list : json_decode($backups_list??'[]', true);
        if (empty($backups_list)) {
            return null;
        }

        foreach ($backups_list as $backup) {
            if ($backup['id'] == $id) {
                return $backup;
            }
        }
        return null;
    }

    // Helper method để restore backup
    private function restoreBackup($backup)
    {
        try {
            $file_path = $backup['file_path'];
            $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
            
            if ($file_extension === 'sql' || $file_extension === 'gz') {
                // Database only backup (sql or sql.gz)
                $this->restoreDatabase($file_path);
            } else {
                // Full or files backup (zip)
                $zip = new \ZipArchive();
                if ($zip->open($file_path) !== TRUE) {
                    return ['success' => false, 'message' => __('Cannot open backup file')];
                }

                // Extract files
                $extract_path = PATH_WRITE . 'temp_restore/';
                if (!is_dir($extract_path)) {
                    mkdir($extract_path, 0755, true);
                }

                $zip->extractTo($extract_path);
                $zip->close();

                // Restore database nếu có
                $db_file = $extract_path . 'database.sql';
                $db_file_gz = $extract_path . 'database.sql.gz';
                
                if (file_exists($db_file_gz)) {
                    $this->restoreDatabase($db_file_gz);
                } elseif (file_exists($db_file)) {
                    $this->restoreDatabase($db_file);
                }

                // Restore files - copy all contents from extract_path to PATH_ROOT
                $this->restoreFiles($extract_path);

                // Cleanup
                $this->removeDirectory($extract_path);
            }

            return ['success' => true, 'message' => __('Backup restored successfully')];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Helper method để restore database
    private function restoreDatabase($sql_file)
    {
        try {
            $db_config = db_config();
            
            // Kết nối database
            $pdo = new \PDO(
                "mysql:host={$db_config['db_host']};port={$db_config['db_port']};dbname={$db_config['db_database']};charset={$db_config['db_charset']}",
                $db_config['db_username'],
                $db_config['db_password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$db_config['db_charset']}"
                ]
            );
            
            // Xóa tất cả bảng hiện tại trước khi restore
            $this->dropAllTables($pdo, $db_config['db_prefix']);
            
            // Kiểm tra file có phải gzip không
            $is_gzipped = $this->isGzipped($sql_file);
            
            if ($is_gzipped) {
                // Đọc file gzip và giải nén
                $handle = gzopen($sql_file, 'rb');
                if (!$handle) {
                    throw new \Exception('Cannot open gzipped SQL file');
                }
                
                $sql_content = '';
                while (!gzeof($handle)) {
                    $sql_content .= gzread($handle, 8192);
                }
                gzclose($handle);
                
                if (empty($sql_content)) {
                    throw new \Exception('Cannot read gzipped SQL file content');
                }
            } else {
                // Đọc file SQL thường
                $sql_content = file_get_contents($sql_file);
                if ($sql_content === false) {
                    throw new \Exception('Cannot read SQL file');
                }
            }
            
            // Split SQL statements more carefully
            $sql_statements = [];
            $lines = explode("\n", $sql_content);
            $current_statement = '';
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                // Skip empty lines and comments
                if (empty($line) || preg_match('/^--/', $line) || preg_match('/^\/\*/', $line)) {
                    continue;
                }
                
                // Skip SET statements that might cause issues
                if (preg_match('/^SET\s+/i', $line)) {
                    continue;
                }
                
                $current_statement .= $line . "\n";
                
                // If line ends with semicolon, we have a complete statement
                if (substr(rtrim($line), -1) === ';') {
                    $statement = trim($current_statement);
                    if (!empty($statement)) {
                        $sql_statements[] = $statement;
                    }
                    $current_statement = '';
                }
            }
            
            // Add any remaining statement
            if (!empty(trim($current_statement))) {
                $sql_statements[] = trim($current_statement);
            }
            
            // Execute each SQL statement
            $count = 0;
            $errors = [];
            
            foreach ($sql_statements as $sql) {
                if (!empty(trim($sql))) {
                    try {
                        // Skip problematic statements
                        if (preg_match('/^(SET|USE|LOCK|UNLOCK|DELIMITER)/i', trim($sql))) {
                            continue;
                        }
                        
                        // Skip statements with problematic content
                        if (strpos($sql, 'Add Default Language Code at URL') !== false) {
                            continue;
                        }
                        
                        $pdo->exec($sql);
                        $count++;
                        
                    } catch (\Exception $e) {
                        // Continue with next statement instead of stopping
                        $errors[] = "Error executing SQL: " . $e->getMessage();
                        continue;
                    }
                }
            }
            
        } catch (\Exception $e) {
            throw new \Exception('Database restore failed: ' . $e->getMessage());
        }
    }

    // Helper method để restore files
    private function restoreFiles($extract_path)
    {
        // Restore toàn bộ từ extract_path về PATH_ROOT
        // Skip database.sql và database.sql.gz files vì đã restore database rồi
        error_log("Restoring files from: " . $extract_path . " to: " . PATH_ROOT);
        
        // 1. Lấy danh sách files trong backup
        $backup_files = $this->getBackupFileList($extract_path);
        error_log("Backup contains " . count($backup_files) . " files");
        
        // 2. Xóa files thừa không có trong backup
        $this->cleanupExtraFiles(PATH_ROOT, $backup_files);
        
        // 3. Copy files từ backup
        $this->copyDirectoryContents($extract_path, PATH_ROOT, ['database.sql', 'database.sql.gz']);
        error_log("Files restore completed");
    }

    // Helper method để copy directory contents (không copy thư mục gốc)
    private function copyDirectoryContents($source, $destination, $exclude_files = [])
    {
        if (!is_dir($source)) {
            error_log("Source directory does not exist: " . $source);
            return;
        }

        error_log("Starting copy from: " . $source . " to: " . $destination);
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $file_count = 0;
        foreach ($iterator as $file) {
            $relative_path = str_replace($source, '', $file->getPathname());
            $relative_path = ltrim($relative_path, '/\\'); // Remove leading slash/backslash
            $target = rtrim($destination, '/\\') . '/' . $relative_path;
            
            // Check if file should be excluded
            $should_exclude = false;
            if (!empty($exclude_files)) {
                $filename = basename($file->getPathname());
                foreach ($exclude_files as $exclude_file) {
                    if ($filename === $exclude_file) {
                        $should_exclude = true;
                        break;
                    }
                }
            }
            
            if (!$should_exclude) {
                if ($file->isDir()) {
                    if (!is_dir($target)) {
                        mkdir($target, 0755, true);
                        error_log("Created directory: " . $target);
                    }
                } else {
                    // Ensure target directory exists
                    $target_dir = dirname($target);
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }
                    if (copy($file->getPathname(), $target)) {
                        $file_count++;
                        if ($file_count % 100 === 0) {
                            error_log("Copied $file_count files...");
                        }
                    } else {
                        error_log("Failed to copy file: " . $file->getPathname() . " to " . $target);
                    }
                }
            }
        }
        
        error_log("Copy completed. Total files copied: " . $file_count);
    }

    // Helper method để lấy danh sách files trong backup
    private function getBackupFileList($extract_path)
    {
        $files = [];
        
        if (!is_dir($extract_path)) {
            return $files;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extract_path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relative_path = str_replace($extract_path, '', $file->getPathname());
                $relative_path = ltrim($relative_path, '/\\');
                
                // Skip database.sql và database.sql.gz
                $filename = basename($file->getPathname());
                if ($filename !== 'database.sql' && $filename !== 'database.sql.gz') {
                    $files[] = $relative_path;
                }
            }
        }
        
        return $files;
    }

    // Helper method để xóa files thừa không có trong backup
    private function cleanupExtraFiles($target_path, $backup_files)
    {
        error_log("Starting cleanup of extra files...");
        
        // Tạo set để lookup nhanh
        $backup_files_set = array_flip($backup_files);
        
        // Lấy danh sách files hiện tại
        $current_files = $this->getCurrentFileList($target_path);
        
        $deleted_count = 0;
        foreach ($current_files as $file) {
            // Skip các thư mục quan trọng không nên xóa
            if ($this->shouldSkipFile($file)) {
                continue;
            }
            
            // Nếu file không có trong backup, xóa nó
            if (!isset($backup_files_set[$file])) {
                $full_path = rtrim($target_path, '/\\') . '/' . $file;
                if (file_exists($full_path)) {
                    if (unlink($full_path)) {
                        $deleted_count++;
                        if ($deleted_count % 50 === 0) {
                            error_log("Deleted $deleted_count extra files...");
                        }
                    } else {
                        error_log("Failed to delete file: " . $full_path);
                    }
                }
            }
        }
        
        error_log("Cleanup completed. Deleted $deleted_count extra files");
    }

    // Helper method để lấy danh sách files hiện tại
    private function getCurrentFileList($target_path)
    {
        $files = [];
        
        if (!is_dir($target_path)) {
            return $files;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($target_path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relative_path = str_replace($target_path, '', $file->getPathname());
                $relative_path = ltrim($relative_path, '/\\');
                $files[] = $relative_path;
            }
        }
        
        return $files;
    }

    // Helper method để kiểm tra file có nên skip không
    private function shouldSkipFile($file)
    {
        // Skip các thư mục quan trọng không nên xóa
        $skip_dirs = [
            $this->writableFolder . '/backups/',  // Bỏ qua thư mục backups
            $this->writableFolder . '/cache/',
            $this->writableFolder . '/logs/',
            $this->writableFolder . '/uploads/',
            'node_modules/',
            'vendor/',
            '.git/',
            '.env',
            'composer.lock',
            'package-lock.json'
        ];
        
        foreach ($skip_dirs as $skip_dir) {
            if (strpos($file, $skip_dir) === 0) {
                return true;
            }
        }
        
        return false;
    }

    // Helper method để kiểm tra file có phải gzip không
    private function isGzipped($filename)
    {
        $handle = fopen($filename, 'rb');
        if (!$handle) {
            return false;
        }
        
        $magic = fread($handle, 2);
        fclose($handle);
        
        // Kiểm tra magic number của gzip (1f 8b)
        return $magic === "\x1f\x8b";
    }

    // Helper method để copy directory
    private function copyDirectory($source, $destination, $exclude_files = [])
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $relative_path = str_replace($source, '', $file->getPathname());
            $relative_path = ltrim($relative_path, '/\\'); // Remove leading slash/backslash
            $target = rtrim($destination, '/\\') . '/' . $relative_path;
            
            // Check if file should be excluded
            $should_exclude = false;
            if (!empty($exclude_files)) {
                $filename = basename($file->getPathname());
                foreach ($exclude_files as $exclude_file) {
                    if ($filename === $exclude_file) {
                        $should_exclude = true;
                        break;
                    }
                }
            }
            
            if (!$should_exclude) {
                if ($file->isDir()) {
                    if (!is_dir($target)) {
                        mkdir($target, 0755, true);
                    }
                } else {
                    // Ensure target directory exists
                    $target_dir = dirname($target);
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }
                    copy($file->getPathname(), $target);
                }
            }
        }
    }

    // Helper method để xóa directory
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}
