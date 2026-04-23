
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
                <?= Flang::__('profile_welcome') ?>
              </h1>

            <?php if (!empty($success)): ?>
            <div class="bg-green-100 text-green-800 p-4 mb-4 rounded">
                <?= $success; ?>
            </div>
            <?php elseif (!empty($errors)): ?>
                <div class="bg-red-200 text-red-800 p-4 mb-4 rounded">
                    <?= $errors; ?>
                </div>
            <?php endif; ?>

              <form class="space-y-4 md:space-y-6" action="<?= auth_url('profile') ?>" method="post">

                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                <div class="fieldset">
                  <label for="username" class="block mb-2 text-sm font-medium leading-5 text-gray-900"><?= Flang::__('username') ?></label>
                  <div class="field username">
                    <input type="text" value="<?= $me['username'] ?? '' ?>" id="username" disabled/>
                  </div>
                </div>
                <div class="fieldset">
                  <label
                    for="fullname"
                    class="block mb-2 text-sm font-medium leading-5 text-gray-900"
                    ><?= Flang::__('fullname') ?></label
                  >
                  <div class="field fullname">
                    <input type="text" name="fullname" value="<?= $me['fullname'] ?? '' ?>" id="fullname" placeholder="<?= Flang::__('placeholder_fullname') ?>" required="" />
                  </div>
                  <?php if (!empty($errors['fullname'])): ?>
                    <div class="text-red-500 mt-2 text-sm">
                        <?php foreach ($errors['fullname'] as $error): ?>
                            <p><?= $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                </div>
                <div class="fieldset">
                  <label for="email" class="block mb-2 text-sm font-medium leading-5 text-gray-900"><?= Flang::__('email') ?></label>
                  <div class="field email">
                    <input type="email"  value="<?= $me['email'] ?? '' ?>" id="email" disabled />
                  </div>
                </div>
                <div class="fieldset">
                  <label
                    for="phone"
                    class="block mb-2 text-sm font-medium leading-5 text-gray-900"
                    ><?= Flang::__('phone') ?></label
                  >
                  <div class="field phone">
                    <input
                      type="text"
                      name="phone"
                      id="phone"  
                      class=""
                      placeholder="<?= Flang::__('placeholder_phone') ?>"
                      value="<?= $me['phone'] ?? '' ?>"
                    />
                  </div>

                  <?php if (!empty($errors['phone'])): ?>
                    <div class="text-red-500 mt-2 text-sm">
                        <?php foreach ($errors['phone'] as $error): ?>
                            <p><?= $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                </div>
                <div>
                  <div
                    class="block mb-2 text-sm font-medium text-gray-900 "
                  >
                    <?= Flang::__('social_media') ?>
                  </div>
                  <div class="fieldset space-y-4 md:space-y-6">
                    <div class="field relative">
                      <input
                        type="text"
                        name="telegram"
                        id="telegram"
                        value="<?= $me['telegram'] ?? '' ?>"
                        placeholder="<?= Flang::__('placeholder_telegram') ?>"
                        class="!pl-10"
                      />
                      <svg
                        class="absolute top-[10px] left-[10px]"
                        width="21"
                        height="20"
                        viewBox="0 0 21 20"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <g clip-path="url(#clip0_355_17038)">
                          <path
                            d="M10.5 20C16.0228 20 20.5 15.5228 20.5 10C20.5 4.47715 16.0228 0 10.5 0C4.97715 0 0.5 4.47715 0.5 10C0.5 15.5228 4.97715 20 10.5 20Z"
                            fill="url(#paint0_linear_355_17038)"
                          />
                          <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M5.02635 9.89446C7.94156 8.62435 9.88548 7.78702 10.8581 7.38246C13.6352 6.22737 14.2123 6.02672 14.5884 6.02009C14.6711 6.01863 14.8561 6.03913 14.9759 6.13635C15.0771 6.21844 15.1049 6.32934 15.1182 6.40717C15.1316 6.485 15.1481 6.6623 15.135 6.80083C14.9845 8.38207 14.3333 12.2193 14.002 13.9903C13.8618 14.7397 13.5858 14.991 13.3186 15.0155C12.7379 15.069 12.2969 14.6318 11.7345 14.2631C10.8543 13.6861 10.3571 13.327 9.50279 12.764C8.51547 12.1134 9.15551 11.7558 9.71818 11.1714C9.86543 11.0184 12.4241 8.69115 12.4736 8.48002C12.4798 8.45362 12.4856 8.3552 12.4271 8.30322C12.3686 8.25125 12.2823 8.26903 12.22 8.28316C12.1318 8.30319 10.7257 9.23252 8.00198 11.0711C7.60288 11.3452 7.2414 11.4787 6.91752 11.4717C6.56046 11.464 5.87364 11.2698 5.36306 11.1039C4.73681 10.9003 4.23907 10.7927 4.28242 10.4469C4.30499 10.2669 4.55297 10.0827 5.02635 9.89446Z"
                            fill="white"
                          />
                        </g>
                        <defs>
                          <linearGradient
                            id="paint0_linear_355_17038"
                            x1="10.5"
                            y1="0"
                            x2="10.5"
                            y2="19.8517"
                            gradientUnits="userSpaceOnUse"
                          >
                            <stop stop-color="#2AABEE" />
                            <stop offset="1" stop-color="#229ED9" />
                          </linearGradient>
                          <clipPath id="clip0_355_17038">
                            <rect
                              width="20"
                              height="20"
                              fill="white"
                              transform="translate(0.5)"
                            />
                          </clipPath>
                        </defs>
                      </svg>
                      <?php if (!empty($errors['telegram'])): ?>
                    <div class="text-red-500 mt-2 text-sm">
                        <?php foreach ($errors['telegram'] as $error): ?>
                            <p><?= $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    </div>
                    <div class="field relative">
                      <input
                        type="text"
                        name="skype"
                        id="skype"
                        value="<?= $me['skype'] ?? '' ?>"
                        placeholder="<?= Flang::__('placeholder_skype') ?>"
                        class="!pl-10"
                      />
                      <svg
                        class="absolute top-[10px] left-[10px]"
                        width="21"
                        height="20"
                        viewBox="0 0 21 20"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <g clip-path="url(#clip0_355_17043)">
                          <path
                            d="M20.5 10C20.5 11.9778 19.9135 13.9112 18.8147 15.5557C17.7159 17.2002 16.1541 18.4819 14.3268 19.2388C12.4996 19.9957 10.4889 20.1937 8.5491 19.8079C6.60929 19.422 4.82746 18.4696 3.42894 17.0711C2.03041 15.6725 1.078 13.8907 0.692152 11.9509C0.306299 10.0111 0.504333 8.00043 1.26121 6.17317C2.01809 4.3459 3.29981 2.78412 4.9443 1.6853C6.58879 0.58649 8.52219 0 10.5 0C13.1506 0.00510569 15.6912 1.06031 17.5654 2.93457C19.4397 4.80883 20.4949 7.3494 20.5 10Z"
                            fill="#00A9F0"
                          />
                          <path
                            d="M16.2464 10.9534C16.0514 10.6763 15.9491 10.3445 15.9544 10.0057C15.9556 9.12721 15.7447 8.26141 15.3395 7.48195C14.9343 6.7025 14.3468 6.03244 13.627 5.52879C12.9073 5.02514 12.0765 4.70279 11.2054 4.58916C10.3343 4.47553 9.44859 4.57398 8.6237 4.87615C8.30753 4.99469 7.96213 5.01098 7.6362 4.92274C7.34081 4.83956 7.03319 4.80841 6.72711 4.83069C6.25356 4.86385 5.79747 5.02296 5.40605 5.29155C5.01463 5.56014 4.70211 5.92846 4.50082 6.35839C4.29953 6.78831 4.2168 7.26422 4.26118 7.73685C4.30556 8.20948 4.47544 8.66167 4.75325 9.0466C4.94902 9.3255 5.05128 9.65931 5.04529 10C5.0447 10.8783 5.25618 11.7437 5.66176 12.5226C6.06734 13.3016 6.65502 13.9712 7.37484 14.4743C8.09466 14.9775 8.92533 15.2994 9.79624 15.4127C10.6671 15.526 11.5525 15.4273 12.3771 15.125C12.6932 15.0061 13.0386 14.9894 13.3646 15.0773C13.6599 15.1612 13.9675 15.1931 14.2737 15.1716C14.7473 15.138 15.2033 14.9785 15.5946 14.7097C15.986 14.4408 16.2984 14.0723 16.4996 13.6423C16.7008 13.2123 16.7836 12.7363 16.7393 12.2636C16.695 11.7909 16.5252 11.3386 16.2476 10.9534H16.2464ZM10.4998 8.97728C10.5635 8.99319 10.6294 9.01024 10.6998 9.02615C11.7998 9.28751 13.4623 9.68297 13.4623 11.5148C13.4623 12.4171 13.018 13.1057 12.2123 13.4466C11.6635 13.683 11.0442 13.725 10.501 13.7273H10.4237C8.6987 13.7273 7.60893 12.3852 7.56348 12.3284C7.49435 12.2444 7.44263 12.1475 7.41132 12.0433C7.38001 11.939 7.36974 11.8297 7.38111 11.7214C7.39248 11.6132 7.42526 11.5084 7.47754 11.4129C7.52983 11.3175 7.60057 11.2334 7.68565 11.1656C7.77073 11.0978 7.86845 11.0476 7.97313 11.0179C8.07781 10.9882 8.18734 10.9796 8.29537 10.9926C8.40339 11.0057 8.50774 11.0401 8.60235 11.0938C8.69695 11.1475 8.77992 11.2196 8.84643 11.3057C8.84643 11.3057 9.50098 12.0568 10.4237 12.0864H10.4998C11.5453 12.1046 11.7885 11.7818 11.8214 11.5068C11.8589 11.1796 11.5851 10.9386 10.4998 10.6602C10.4419 10.6443 10.3862 10.6296 10.3203 10.6148C9.2737 10.3705 7.69188 10 7.69188 8.30797C7.69188 7.79206 7.89757 6.13638 10.4998 6.07047H10.6544C12.3896 6.07047 13.4135 7.4341 13.4567 7.49547C13.5863 7.67027 13.6411 7.88939 13.6091 8.10463C13.5772 8.31987 13.461 8.5136 13.2862 8.64319C13.1114 8.77279 12.8923 8.82763 12.677 8.79567C12.4618 8.7637 12.2681 8.64754 12.1385 8.47274C12.1192 8.44774 11.5317 7.79092 10.6544 7.71251C10.6021 7.71251 10.551 7.70456 10.4998 7.70342C9.89189 7.68751 9.33279 7.91706 9.33279 8.24319C9.33279 8.59319 9.49984 8.72728 10.4998 8.97728Z"
                            fill="white"
                          />
                        </g>
                        <defs>
                          <clipPath id="clip0_355_17043">
                            <rect
                              width="20"
                              height="20"
                              fill="white"
                              transform="translate(0.5)"
                            />
                          </clipPath>
                        </defs>
                      </svg>
                      <?php if (!empty($errors['skype'])): ?>
                    <div class="text-red-500 mt-2 text-sm">
                        <?php foreach ($errors['skype'] as $error): ?>
                            <p><?= $error; ?></p> 
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    </div>
                    <div class="field relative">
                      <input
                        type="text"
                        name="whatsapp"
                        id="whatsapp"
                        value="<?= $me['whatsapp'] ?? '' ?>"
                        placeholder="<?= Flang::__('placeholder_whatsapp') ?>"
                        class="!pl-10"
                      />
                      <svg class="absolute top-[10px] left-[10px]" fill="green" height="20px" width="21px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
                        viewBox="0 0 308 308" xml:space="preserve">
                      <g id="XMLID_468_">
                        <path id="XMLID_469_" d="M227.904,176.981c-0.6-0.288-23.054-11.345-27.044-12.781c-1.629-0.585-3.374-1.156-5.23-1.156
                          c-3.032,0-5.579,1.511-7.563,4.479c-2.243,3.334-9.033,11.271-11.131,13.642c-0.274,0.313-0.648,0.687-0.872,0.687
                          c-0.201,0-3.676-1.431-4.728-1.888c-24.087-10.463-42.37-35.624-44.877-39.867c-0.358-0.61-0.373-0.887-0.376-0.887
                          c0.088-0.323,0.898-1.135,1.316-1.554c1.223-1.21,2.548-2.805,3.83-4.348c0.607-0.731,1.215-1.463,1.812-2.153
                          c1.86-2.164,2.688-3.844,3.648-5.79l0.503-1.011c2.344-4.657,0.342-8.587-0.305-9.856c-0.531-1.062-10.012-23.944-11.02-26.348
                          c-2.424-5.801-5.627-8.502-10.078-8.502c-0.413,0,0,0-1.732,0.073c-2.109,0.089-13.594,1.601-18.672,4.802
                          c-5.385,3.395-14.495,14.217-14.495,33.249c0,17.129,10.87,33.302,15.537,39.453c0.116,0.155,0.329,0.47,0.638,0.922
                          c17.873,26.102,40.154,45.446,62.741,54.469c21.745,8.686,32.042,9.69,37.896,9.69c0.001,0,0.001,0,0.001,0
                          c2.46,0,4.429-0.193,6.166-0.364l1.102-0.105c7.512-0.666,24.02-9.22,27.775-19.655c2.958-8.219,3.738-17.199,1.77-20.458
                          C233.168,179.508,230.845,178.393,227.904,176.981z"/>
                        <path id="XMLID_470_" d="M156.734,0C73.318,0,5.454,67.354,5.454,150.143c0,26.777,7.166,52.988,20.741,75.928L0.212,302.716
                          c-0.484,1.429-0.124,3.009,0.933,4.085C1.908,307.58,2.943,308,4,308c0.405,0,0.813-0.061,1.211-0.188l79.92-25.396
                          c21.87,11.685,46.588,17.853,71.604,17.853C240.143,300.27,308,232.923,308,150.143C308,67.354,240.143,0,156.734,0z
                          M156.734,268.994c-23.539,0-46.338-6.797-65.936-19.657c-0.659-0.433-1.424-0.655-2.194-0.655c-0.407,0-0.815,0.062-1.212,0.188
                          l-40.035,12.726l12.924-38.129c0.418-1.234,0.209-2.595-0.561-3.647c-14.924-20.392-22.813-44.485-22.813-69.677
                          c0-65.543,53.754-118.867,119.826-118.867c66.064,0,119.812,53.324,119.812,118.867
                          C276.546,215.678,222.799,268.994,156.734,268.994z"/>
                      </g>
                      </svg>
                    </div>
                  </div>
                </div>
                <button type="submit" class="btn btn-primary w-full">
                  <?= Flang::__('update_submit') ?>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>