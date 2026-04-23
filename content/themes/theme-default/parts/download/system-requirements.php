<?php
if (!function_exists('__')) {
    load_helpers(['languages']);
}
\App\Libraries\Fastlang::load('Download', APP_LANG);
?>
<section class="py-12 sm:py-24 bg-white container">
    <div class="max-w-5xl mx-auto">
        <div class="text-center px-4">
            <h2 class="w-full font-space text-[30px] sm:text-3xl md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-home-heading mb-8 sm:mb-12">
                <?php echo e(__('download.sysreq.title')); ?>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 sm:gap-10 max-w-5xl mx-auto">
            <!-- OS -->
            <div class="flex flex-col items-center gap-4 p-6 flex-1 self-stretch rounded-home-lg bg-gray-100">
                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M24 34V42M28.61 15.06L30.456 14.296M30.456 9.70403L28.61 8.93803M33.704 6.45603L32.938 4.60803M33.704 17.544L32.938 19.39M38.296 6.45603L39.062 4.60803M39.06 19.392L38.296 17.544M41.544 9.70403L43.392 8.93803M41.544 14.296L43.392 15.062M44 26V30C44 31.0609 43.5786 32.0783 42.8284 32.8285C42.0783 33.5786 41.0609 34 40 34H8C6.93913 34 5.92172 33.5786 5.17157 32.8285C4.42143 32.0783 4 31.0609 4 30V10C4 8.93917 4.42143 7.92175 5.17157 7.17161C5.92172 6.42146 6.93913 6.00003 8 6.00003H22M16 42H32M42 12C42 15.3137 39.3137 18 36 18C32.6863 18 30 15.3137 30 12C30 8.68632 32.6863 6.00003 36 6.00003C39.3137 6.00003 42 8.68632 42 12Z" stroke="var(--home-body)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    
                <h3 class="text-center text-2xl font-semibold leading-9 text-home-heading font-plus"><?php echo e(__('download.sysreq.os')); ?></h3>
                <p class="text-md text-center text-home-body font-plus"><?php echo e(__('download.sysreq.os_value')); ?></p>
            </div>

            <!-- Architecture -->
            <div class="flex flex-col items-center gap-4 p-6 flex-1 self-stretch rounded-home-lg bg-gray-100">
                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M24 40V44M24 4V8M34 40V44M34 4V8M4 24H8M4 34H8M4 14H8M40 24H44M40 34H44M40 14H44M14 40V44M14 4V8M12 8H36C38.2091 8 40 9.79086 40 12V36C40 38.2091 38.2091 40 36 40H12C9.79086 40 8 38.2091 8 36V12C8 9.79086 9.79086 8 12 8ZM18 16H30C31.1046 16 32 16.8954 32 18V30C32 31.1046 31.1046 32 30 32H18C16.8954 32 16 31.1046 16 30V18C16 16.8954 16.8954 16 18 16Z" stroke="var(--home-body)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    
                    
                <h3 class="text-center text-2xl font-semibold leading-9 text-home-heading font-plus"><?php echo e(__('download.sysreq.arch')); ?></h3>
                <p class="text-md text-center text-home-body font-plus"><?php echo e(__('download.sysreq.arch_value')); ?></p>
            </div>


            <!-- Hardware -->
            <div class="flex flex-col items-center gap-4 p-6 flex-1 self-stretch rounded-home-lg bg-gray-100">
                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M44 24H4M44 24V36C44 37.0609 43.5786 38.0783 42.8284 38.8284C42.0783 39.5786 41.0609 40 40 40H8C6.93913 40 5.92172 39.5786 5.17157 38.8284C4.42143 38.0783 4 37.0609 4 36V24M44 24L37.1 10.22C36.7688 9.55357 36.2584 8.99274 35.6259 8.60056C34.9935 8.20838 34.2642 8.00039 33.52 8H14.48C13.7358 8.00039 13.0065 8.20838 12.3741 8.60056C11.7417 8.99274 11.2312 9.55357 10.9 10.22L4 24M12 32H12.02M20 32H20.02" stroke="var(--home-body)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    
                <h3 class="text-center text-2xl font-semibold leading-9 text-home-heading font-plus"><?php echo e(__('download.sysreq.hardware')); ?></h3>
                <p class="text-md text-center text-home-body font-plus"><?php echo e(__('download.sysreq.hardware_line1')); ?>
                    <br>
                    <?php echo e(__('download.sysreq.hardware_line2')); ?></p>
            </div>
        </div>
    </div>
</section>

