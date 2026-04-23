<?php

use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

// Lấy thông báo flash nếu có
if (Session::has_flash('success')) {
    $success = Session::flash('success');
}
if (Session::has_flash('error')) {
    $error = Session::flash('error');
}

// Đoạn code bên dưới giả định rằng bạn đang có sẵn $csrf_token
// và biến $errors chứa các thông báo lỗi được trả về từ server
// Ở thực tế, bạn sẽ tuỳ biến lại tùy vào controller và logic của dự án bạn.
?>
<!doctype html>
<html lang="en" class="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr"
    dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">

<head>
    <title>Register | Able Pro Dashboard Template</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0,minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description"
        content="Able Pro is trending dashboard template made using Bootstrap 5 design framework..." />
    <meta name="keywords"
        content="Bootstrap admin template, Dashboard UI Kit, Dashboard Template, Backend Panel, react dashboard, angular dashboard" />
    <meta name="author" content="Phoenixcoded" />

    <!-- Gọi các file CSS/JS cần thiết cho backend -->
    <?= \System\Libraries\Render::renderAsset('head', 'backend') ?>
</head>

<body>
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg fixed inset-0 bg-white dark:bg-themedark-cardbg z-[1034]">
        <div class="loader-track h-[5px] w-full inline-block absolute overflow-hidden top-0 bg-primary-500/10">
            <div
                class="loader-fill w-[300px] h-[5px] bg-primary-500 absolute top-0 left-0 transition-[transform_0.2s_linear] origin-left animate-[2.1s_cubic-bezier(0.65,0.815,0.735,0.395)_0s_infinite_normal_none_running_loader-animate]">
            </div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <div class="auth-main relative">
        <div class="auth-wrapper v2 flex items-center w-full h-full min-h-screen">
            <!-- Cột bên trái (hình nền, logo, intro...) -->
            <div class="auth-sidecontent img-fluid h-screen hidden lg:flex flex-col items-center justify-center w-1/2"
                style="max-width: 50%; background: url(https://elstar.themenate.net/img/others/auth-cover-bg.jpg) no-repeat; background-size: cover;">
                <div class="logo" style="width: auto;">
                    <?= _img("/backend/assets/images/user/logo2.png", 'Elstar logo', false, 'mx-auto', '', 150)?>
                </div>
                <div class="mt-4">
                    <div class="mb-6 flex items-center gap-4">
                        <span class="avatar avatar-circle avatar-md">
                            <?= _img("/backend/assets/images/user/logo2.png", 'Elstar logo', false, 'avatar-img avatar-circle rounded-full', '', 40, 40)?>
                        </span>
                        <div class="text-white">
                            <div class="font-semibold text-base">CMSFullForm</div>
                            <span class="opacity-80">CTO, Onward</span>
                        </div>
                    </div>
                    <p class="text-lg text-white opacity-80">
                        Elstar comes with a complete set of UI components crafted with Tailwind CSS...
                    </p>
                </div>
                <p class="text-white text-center">Copyright ©
                    <span class="font-semibold">CMSFullForm</span>
                    2025
                    </span>
            </div>

            <!-- Cột bên phải (chứa form đăng ký) -->
            <div
                class="auth-form flex items-center justify-center grow flex-col min-h-screen bg-cover relative p-6 bg-theme-cardbg dark:bg-themedark-cardbg">
                <div class="card sm:my-12 w-full max-w-[480px] border-none shadow-none">
                    <div class="card-body sm:!p-10">
                        <!-- Logo, nút Google -->
                        <div class="text-center">
                            <a href="#">
                                <!-- <img src="/backend/assets/images/logo-dark.svg" alt="img" class="mx-auto"> -->
                                <?= _img("/backend/assets/images/user/logo3.png", 'img', false, 'mx-auto', '', 160, 160)?>
                            </a>
                            <div class="grid my-4">
                                <!-- Thay nút Google đăng nhập thành link thực tế của bạn -->
                                <a href="<?= auth_url('login_google') ?>"
                                    class="btn mt-2 flex items-center justify-center gap-2 text-theme-bodycolor dark:text-themedark-bodycolor bg-theme-bodybg dark:bg-themedark-bodybg border border-theme-border dark:border-themedark-border hover:border-primary-500 dark:hover:border-primary-500">
                                    <?= _img("/backend/assets/images/authentication/google.png", 'img', false)?>
                                    <span><?= Flang::__('login_google') ?></span>
                                </a>
                            </div>
                        </div>

                        <!-- Dòng kẻ "hoặc" -->
                        <div class="relative my-5">
                            <div aria-hidden="true" class="absolute flex inset-0 items-center">
                                <div class="w-full border-t border-theme-border dark:border-themedark-border"></div>
                            </div>
                            <div class="relative flex justify-center">
                                <span class="px-4 bg-theme-cardbg dark:bg-themedark-cardbg">OR</span>
                            </div>
                        </div>

                        <!-- Tiêu đề form -->
                        <h4 class="text-center font-medium mb-4">
                            <?= Flang::__('register_welcome') ?>
                        </h4>

                        <!-- Thông báo success/error -->
                        <?php if (!empty($success)): ?>
                            <div class="bg-green-100 text-green-800 p-4 mb-4 rounded">
                                <?= $success; ?>
                            </div>
                        <?php elseif (!empty($error)): ?>
                            <div class="bg-red-200 text-red-800 p-4 mb-4 rounded">
                                <?= $error; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Form đăng ký -->
                        <form name="signupForm" action="<?= auth_url('register') ?>" method="post" class="space-y-4">
                            <!-- CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                            <div class="flex gap-3">
                                <!-- Username -->
                                <div class="">
                                    <label for="username" class="block mb-2 text-sm font-medium text-gray-900 hidden">
                                        <?= Flang::__('username') ?>
                                    </label>
                                    <input
                                        type="text"
                                        name="username"
                                        id="username"
                                        class="form-control"
                                        placeholder="<?= Flang::__('placeholder_username') ?>"
                                        required />
                                    <?php if (!empty($errors['username'])): ?>
                                        <div class="text-red-500 mt-2 text-sm">
                                            <?php foreach ($errors['username'] as $err): ?>
                                                <p><?= $err; ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Họ tên đầy đủ -->
                                <div class="">
                                    <label for="fullname" class="block mb-2 text-sm font-medium text-gray-900 hidden">
                                        <?= Flang::__('fullname') ?>
                                    </label>
                                    <input
                                        type="text"
                                        name="fullname"
                                        id="fullname"
                                        class="form-control"
                                        placeholder="<?= Flang::__('placeholder_fullname') ?>"
                                        required
                                        value="<?= Session::get('fullname') ?? '' ?>" />
                                    <?php if (!empty($errors['fullname'])): ?>
                                        <div class="text-red-500 mt-2 text-sm">
                                            <?php foreach ($errors['fullname'] as $err): ?>
                                                <p><?= $err; ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 hidden">
                                    <?= Flang::__('email') ?>
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    class="form-control"
                                    placeholder="<?= Flang::__('placeholder_email') ?>"
                                    required
                                    value="<?= Session::get('email') ?? '' ?>"
                                    <?php echo empty(Session::get('email')) ? '' : 'readonly'; ?> />
                                <?php if (!empty($errors['email'])): ?>
                                    <div class="text-red-500 mt-2 text-sm">
                                        <?php foreach ($errors['email'] as $err): ?>
                                            <p><?= $err; ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Phone -->
                            <div class="mb-3">
                                <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 hidden">
                                    <?= Flang::__('phone') ?>
                                </label>
                                <input
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    class="form-control"
                                    placeholder="<?= Flang::__('placeholder_phone') ?>"
                                    required />
                                <?php if (!empty($errors['phone'])): ?>
                                    <div class="text-red-500 mt-2 text-sm">
                                        <?php foreach ($errors['phone'] as $err): ?>
                                            <p><?= $err; ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex gap-3">
                                <!-- Password -->
                            <div class="">
                                <label for="password" class="block mb-2 text-sm font-medium text-gray-900 hidden">
                                    <?= Flang::__('password') ?>
                                </label>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control"
                                    placeholder="<?= Flang::__('placeholder_password') ?>"
                                    required />
                                <?php if (!empty($errors['password'])): ?>
                                    <div class="text-red-500 mt-2 text-sm">
                                        <?php foreach ($errors['password'] as $err): ?>
                                            <p><?= $err; ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Password repeat -->
                            <div class="">
                                <label for="password_repeat" class="block mb-2 text-sm font-medium text-gray-900 hidden">
                                    <?= Flang::__('password_repeat') ?>
                                </label>
                                <input
                                    type="password"
                                    name="password_repeat"
                                    id="password_repeat"
                                    class="form-control"
                                    placeholder="<?= Flang::__('placeholder_password_repeat') ?>"
                                    required />
                                <?php if (!empty($errors['password_repeat'])): ?>
                                    <div class="text-red-500 mt-2 text-sm">
                                        <?php foreach ($errors['password_repeat'] as $err): ?>
                                            <p><?= $err; ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            </div> 

                            <!-- Nút Đăng ký -->
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary w-full">
                                    <?= Flang::__('sign_up') ?>
                                </button>
                            </div>
                        </form>

                        <!-- Link chuyển sang Login -->
                        <div class="flex justify-between items-end flex-wrap mt-4">
                            <h6 class="f-w-500 mb-0">
                                <?= Flang::__('have_account') ?>
                            </h6>
                            <a href="<?= auth_url('login') ?>" class="text-primary-500">
                                <?= Flang::__('login') ?>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gọi file JS cuối trang -->
    <?= \System\Libraries\Render::renderAsset('footer', 'backend') ?>
</body>

</html>