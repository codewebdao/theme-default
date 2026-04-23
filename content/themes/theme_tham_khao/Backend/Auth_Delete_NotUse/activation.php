<?php
use System\Libraries\Session;
use App\Libraries\Fastlang as Flang;

if (Session::has_flash('error')){
    $error = Session::flash('error') ?? '';
}
if (Session::has_flash('success')){
    $success = Session::flash('success') ?? '';
}
?>


<div class="page-wrapper">
      <div class="flex flex-wrap">
        <?php echo $sidebar; ?>
      
        <div class="w-full p-3 md:w-2/3">
          <div class="authorize-form w-full h-full bg-white flex items-center justify-center">
            <div class="w-full max-w-[470px] p-6 space-y-4 md:space-y-6 sm:p-8">
              <h1 class="font-semibold text-2xl leading-8 text-gray-900">
              <?php Flang::_('active_welcome') ?>
              </h1>
              <form class="space-y-4 md:space-y-6" action="<?php echo auth_url('activation/' . $user_id); ?>" method="post">
              <!-- CSRF Token -->
               <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">  
              
              <div class="fieldset">
                  <label
                    for="activation_no"
                    class="block mb-2 font-medium text-sm leading-5 text-gray-900"
                    ><?php Flang::_('active_number') ?>:</label
                  >
                  <div class="field email">
                    <input
                      type="text"
                      name="activation_no"  
                      id="activation_no"
                      class=""
                       placeholder="<?= Flang::__('active placeholder') ?>"
                      required=""
                    />
                  </div>
                </div>
                
                <button
                  type="submit"
                  class="btn btn-primary w-full">
                  <?= Flang::__('active') ?>
                </button>
              </form> 

              <form action="<?php echo auth_url('activation/' . $user_id); ?>" method="POST" class="mt-4">
                <button type="submit" name="activation_resend"  class="btn btn-primary w-full">
                    <?php Flang::_('resend_activation') ?>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>