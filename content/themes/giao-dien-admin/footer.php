<?php
/**
 * Admin shell — đóng layout (main + wrapper + assets footer + </body></html>).
 */
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

use System\Libraries\Render\View;

echo View::include('parts/layout/footer', []);
