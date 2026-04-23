<?php
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;
if (Session::has_flash('success')){
    $success = Session::flash('success');
}
if (Session::has_flash('error')){
    $error = Session::flash('error');
}
?>


<div class="page-wrapper">
      <div class="flex flex-wrap">
        <?php echo $sidebar; ?>
        <div class="w-full p-3 md:w-2/3 md:p-12">
          <div class="authorize-form w-full h-full bg-white flex items-center justify-center">
            <div class="w-full max-w-[470px] p-6 space-y-4 md:space-y-6 sm:p-8">
              <h1 class="font-semibold text-2xl leading-8 text-gray-900">
                <?= FLang::__('reset_password_title') ?>
              </h1>
              <form
                name="resetPassForm"
                class="space-y-4 md:space-y-6"
                action=""
                method="post"
              > 
                <div class="fieldset">
                  <label
                    for="password"
                    class="block mb-2 text-sm font-medium text-gray-900 "
                    ><?= Flang::__('new password') ?></label
                  >
                  <div class="field password relative">
                    <input
                      type="password"
                      name="password"
                      id="password"
                      placeholder="<?= Flang::__('new_password') ?>"
                      class=""
                      required=""
                    />
                    <span
                      id="togglePassword"
                      class="absolute inset-y-0 right-0 px-3 py-2 flex items-center focus:outline-none"
                    >
                      <svg
                        id="eyeIcon"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="size-5"
                      >
                        <path
                          d="M3.53 2.47a.75.75 0 0 0-1.06 1.06l18 18a.75.75 0 1 0 1.06-1.06l-18-18ZM22.676 12.553a11.249 11.249 0 0 1-2.631 4.31l-3.099-3.099a5.25 5.25 0 0 0-6.71-6.71L7.759 4.577a11.217 11.217 0 0 1 4.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113Z"
                        />
                        <path
                          d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A3.75 3.75 0 0 1 15.75 12ZM12.53 15.713l-4.243-4.244a3.75 3.75 0 0 0 4.244 4.243Z"
                        />
                        <path
                          d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 0 0-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 0 1 6.75 12Z"
                        />
                      </svg>
                    </span>
                  </div>
                </div>
                <button type="submit" class="btn btn-primary w-full" disabled>
                <?= Flang::__('change_password') ?>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>