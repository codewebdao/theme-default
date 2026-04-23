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
?>
<!doctype html>
<html lang="en" class="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr"
    dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">

<head>
    <title>Forgot Password | Able Pro Dashboard Template</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0,minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description"
          content="Able Pro is trending dashboard template made using Bootstrap 5 design framework..." />
    <meta name="keywords"
          content="Bootstrap admin template, Dashboard UI Kit, Dashboard Template, Backend Panel, react dashboard, angular dashboard" />
    <meta name="author" content="Phoenixcoded" />

    <!-- Gọi các file CSS/JS cho backend -->
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

            <!-- Cột bên phải (chứa form forgot password) -->
            <div
                class="auth-form flex items-center justify-center grow flex-col min-h-screen bg-cover relative p-6 bg-theme-cardbg dark:bg-themedark-cardbg">
                <div class="card sm:my-12 w-full max-w-[480px] border-none shadow-none">
                    <div class="card-body sm:!p-10">
                        <!-- Logo -->
                        <div class="text-center">
                            <a href="#">
                                <?= _img("/logo.png", 'img', false, 'mx-auto')?>
                            </a>
                        </div>

                        <!-- Tiêu đề form -->
                        <h4 class="text-center font-medium my-5">
                            <?= Flang::__('forgot_password_title') ?>
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

                        <!-- Form -->
                        <form
                            name="forgotPasssForm"
                            method="post"
                            action="<?= auth_url('forgot_password') ?>"
                            class="space-y-4"
                        >
                            <!-- CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                            <div>
                                <label for="email" class="block mb-2 font-medium text-sm leading-5 text-gray-900">
                                    <?= Flang::__('email') ?>
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    class="form-control"
                                    placeholder="<?= Flang::__('placeholder_email') ?>"
                                    required
                                />
                                <?php if (!empty($errors['email'])): ?>
                                    <div class="text-red-500 mt-2 text-sm">
                                        <?php foreach ($errors['email'] as $err): ?>
                                            <p><?= $err; ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Nút Submit -->
                            <div class="mt-4">
                                <button
                                    type="submit"
                                    class="btn btn-primary w-full"
                                >
                                    <?= Flang::__('submit_link') ?>
                                </button>
                            </div>
                        </form>

                        <!-- Gợi ý chuyển về trang Login -->
                        <div class="flex justify-between items-end flex-wrap mt-4">
                            <h6 class="f-w-500 mb-0">
                                <?= Flang::__('back_to_login_question') ?>
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
