<?php
namespace App\Controllers\Backend;

use App\Controllers\BackendController;
use App\Models\FilesModel;
use App\Libraries\Fastlang as Flang;
use System\Libraries\Render\View;

class FilesController extends BackendController {

    protected $filesModel;

    public function __construct()
    {
        parent::__construct();
        $this->filesModel = new FilesModel();
        $config_files = config('files', 'Uploads');
        unset($config_files['storage_path']);
        $this->data('config_files', $config_files);
        Flang::load('files', APP_LANG);
    }

    public function index(){
        // Check permission
        if (!$this->checkPermission('index')) {
            throw new \System\Core\AppException('You do not have permission to access this page', 403, null, 403);
        }

        $this->data('title', 'Files List - Timeline');
        echo View::make('files_index', $this->data)->render();
    }

    public function timeline() {
        // Check permission
        if (!$this->checkPermission('index')) {
            throw new \System\Core\AppException('You do not have permission to access this page', 403, null, 403);
        }
        // try {
        //     $page = S_GET('page') ? (int)S_GET('page') : 1;
        //     $limit = S_GET('limit') ? (int)S_GET('limit') : 20;
        //     $search = S_GET('q') ? S_GET('q') : '';

        //     $where = '';
        //     $params = [];

        //     if (!empty($search)) {
        //         $where = 'name LIKE ?';
        //         $params[] = '%' . $search . '%';
        //     }
        //     $files = $this->filesModel->getFiles($where, $params, 'created_at DESC', $page, $limit);
        // } catch (\Exception $e) {
        //     $this->error($e->getMessage(), [], 500);
        // }
        // $this->data('files', $files);
        if (!defined('APP_DEBUGBAR_SKIP')) {
            define('APP_DEBUGBAR_SKIP', true);
        }
        $this->data('title', 'Files List - Timeline');
        View::addJs('files-timeline-imagify', 'js/iMagify.2.0.js', [], (string) time(), false, false, false, false);
        echo View::make('files_timeline', $this->data)->render();
    }

    public function manage() {
        // Check permission
        if (!$this->checkPermission('manage')) {
            throw new \System\Core\AppException('You do not have permission to access this page', 403, null, 403);
        }

        echo View::make('Files/manage', $this->data)->render();        
    }

  

  
   
}


