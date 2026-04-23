<?php
return [    
    'welcome_user_member' => 'Chào mừng đến với trang quản trị thành viên',
    'list user' => "Danh sách thành viên",
    'title_add_member' => 'Thêm thành viên',
    'title_edit_member' => 'Chỉnh sửa thành viên',
    'username'  =>  'Tên đăng nhập',
    'fullname'  =>  'Họ và tên',
    'select_fillter' => 'Lọc',
    'password'  =>  'Mật khẩu',
    'active_accouont' => 'Kích hoạt tài khoản',
    'update_member_success' => 'Cập nhật thành viên thành công',
    'create_member_success' => 'Thêm thành viên thành công',
    'password_repeat' => 'Nhập lại mật khẩu',
    'not_find_user' => 'Không tìm thấy member',
    'email'  =>  'Email',
    'phone'  =>  'Số điện thoại',
    'role' => 'Quyền',
    'select_role' => 'Select Roles',
    'status' => 'Trạng thái',
    'select_status' => 'Select Status',
    'submit_add' => 'Thêm',
    'submit_edit' => 'Cập nhật',
    'submit_update' => 'Cập nhật thành công',

    // Validation
    'loginname_invalid'  =>  'Cần nhập đúng Username hoặc Email, không kí tự đặc biệt.',
    'username_double'  =>  '%1%: Tên đăng nhập đã tồn tại trong hệ thống.',
    'username_invalid'  =>  'Tên đăng nhập phải là chữ, số, không có ký tự đặc biệt.',
    'username_length'   =>  'Độ dài tên đăng nhập yêu cầu từ %1% đến %2% ký tự.',
    'email_double'  =>  '%1%: Email đã tồn tại trong hệ thống.',
    'email_exist'   => 'Email %1% không tồn tại trong hệ thống',
    'email_invalid'  =>  'Email phải có định dạng email@domain.com',
    'email_length'    =>  'Độ dài email yêu cầu từ %1% đến %2% ký tự.',
    'password_length'   =>  'Độ dài mật khẩu yêu cầu từ %1% đến %2% ký tự.',
    'password_repeat_invalid'   =>  'Mật khẩu xác nhận %1% không khớp với mật khẩu.',
    'fullname_length'   =>  'Độ dài họ và tên yêu cầu từ %1% đến %2% ký tự.',
    'fullname_invalid'  =>  'Họ và tên phải là chữ, không chứa số or kí tự đặc biệt.',
    'phone_invalid'  =>  'Số điện thoại phải có định dạng 0123456789',
    'phone_length' => 'Số điện thoại phải từ %1% đến %2%', 
    'active' => 'Hành động',
    'role_option' => 'Vai trò phải được chọn',
    'permission_array_json' => 'Quyền phải được trọn',

    // Placeholders
    'placeholder_username' => 'Tên đăng nhập',
    'placeholder_fullname' => 'Họ và tên',
    'placeholder_email' => 'Email',
    'placeholder_search' => 'Tìm kiếm',
    'placeholder_phone' => 'Số điện thoại',
    'placeholder_password' => 'Mật khẩu',
    'placeholder_password_repeat' => 'Xác nhận mật khẩu',
    'button_edit_member' => 'Chỉnh sửa',
    'no_data' => 'Không có dữ liệu',
    'change_password' => 'Bạn muốn đổi mật khẩu?',
    'permission'        => 'Quyền',
    'select_role'       => 'Chọn quyền',
    'btn_search'        => 'Tìm',
    'action'            => 'Hành động',
    
    // Page titles and descriptions
    'Users Management' => 'Quản lý người dùng',
    'Manage system users and their permissions' => 'Quản lý người dùng và phân quyền',
    'Dashboard' => 'Bảng điều khiển',
    'Users' => 'Người dùng',
    
    // Table headers
    'ID' => 'ID',
    'Username' => 'Tên đăng nhập',
    'Full Name' => 'Họ và tên',
    'Email' => 'Email',
    'Phone' => 'Số điện thoại',
    'Role' => 'Quyền',
    'Status' => 'Trạng thái',
    'Actions' => 'Hành động',
    
    // Filter options
    'All Roles' => 'Tất cả quyền',
    'Admin' => 'Quản trị viên',
    'Moderator' => 'Điều hành viên',
    'Author' => 'Tác giả',
    'Member' => 'Thành viên',
    
    // Status values
    'Active' => 'Hoạt động',
    'Inactive' => 'Không hoạt động',
    
    // Buttons and actions
    'Add User' => 'Thêm người dùng',
    'Delete Selected' => 'Xóa đã chọn',
    'Deleting...' => 'Đang xóa...',
    'Edit User' => 'Chỉnh sửa người dùng',
    'Delete User' => 'Xóa người dùng',
    
    // Messages
    'Please select items to delete' => 'Vui lòng chọn các mục cần xóa',
    'Are you sure you want to delete selected items?' => 'Bạn có chắc chắn muốn xóa các mục đã chọn?',
    'Error deleting items' => 'Lỗi khi xóa các mục',
    'Network error occurred' => 'Đã xảy ra lỗi mạng',
    'No users found.' => 'Không tìm thấy người dùng',
    'Showing %1% to %2% of %3% results' => 'Hiển thị %1% đến %2% trong tổng số %3% kết quả',
    'No results' => 'Không có kết quả',
    
    // Confirmations
    'Are you sure you want to delete this item?' => 'Bạn có chắc chắn muốn xóa mục này?',
    'Are you sure you want to change the status?' => 'Bạn có chắc chắn muốn thay đổi trạng thái?',
    
    // New flash messages
    'User added successfully' => 'Thêm người dùng thành công',
    'Failed to add user' => 'Không thể thêm người dùng',
    'User not found' => 'Không tìm thấy người dùng',
    'User updated successfully' => 'Cập nhật người dùng thành công',
    'Failed to update user' => 'Không thể cập nhật người dùng',
    'Please fix the validation errors' => 'Vui lòng sửa các lỗi xác thực',
    'User status updated successfully' => 'Cập nhật trạng thái người dùng thành công',
    'Failed to update user status' => 'Không thể cập nhật trạng thái người dùng',
    'Users deleted successfully' => 'Xóa người dùng thành công',
    'No users selected for deletion' => 'Không có người dùng nào được chọn để xóa'
];