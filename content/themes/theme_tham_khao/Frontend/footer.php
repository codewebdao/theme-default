</div>
</main>

<footer class="bg-gradient-to-br from-blue-700 via-indigo-800 to-purple-900 text-slate-200 py-16">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            <div>
                <h3 class="text-lg font-semibold text-white mb-5"><?= option('site_brand') ?></h3>
                <div class="flex items-center space-x-2 mb-4">
                    <a href="<?= base_url('/'); ?>">
                        <?= _img(theme_assets('images/logo/Logo-light.webp'), option('site_brand'), true, 'w-32 object-cover') ?>
                    </a>
                </div>
                <p class="text-sm text-slate-300 leading-relaxed"><?php _e('A next-generation AI-powered CMS solution, delivering unparalleled speed, flexibility, and security for your website.') ?></p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-white mb-5"><?php _e('Products') ?></h3>
                <ul class="space-y-3 text-sm">
                    <li><a class="hover:text-yellow-300 transition-colors" href="<?= base_url('features'); ?>"><?php _e('Features') ?></a></li>
                    <li><a class="hover:text-yellow-300 transition-colors" href="/#pricing"><?php _e('Pricing') ?></a></li>
                    <li><a class="hover:text-yellow-300 transition-colors" href="<?= download_url(); ?>"><?php _e('Download') ?></a></li>
                    <li><a class="hover:text-yellow-300 transition-colors" href="<?= demo_url(); ?>"><?php _e('footer.demo') ?></a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-white mb-5"><?php _e('Resources') ?></h3>
                <ul class="space-y-3 text-sm">
                    <li><a class="hover:text-yellow-300 transition-colors" href="<?= base_url('blogs'); ?>"><?php _e('footer.blog') ?></a></li>
                    <li><a class="hover:text-yellow-300 transition-colors" href="<?= docs_url(); ?>"><?php _e('footer.docs') ?></a></li>
                    <li><a class="hover:text-yellow-300 transition-colors" href="#support"><?php _e('Support') ?></a></li>
                    <li><a class="hover:text-yellow-300 transition-colors" href="/#faq"><?php _e('footer.faqs') ?></a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-white mb-5"><?php _e('footer.connect') ?></h3>
                <p class="text-sm text-slate-300 mb-4"><?php _e('footer.follow_us') ?></p>
                <div class="flex space-x-4">
                    <a aria-label="Facebook" class="text-slate-300 hover:text-yellow-300 transition-colors p-2 bg-white/10 rounded-full" href="#"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
                    <a aria-label="Twitter" class="text-slate-300 hover:text-yellow-300 transition-colors p-2 bg-white/10 rounded-full" href="#"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path></svg></a>
                    <a aria-label="LinkedIn" class="text-slate-300 hover:text-yellow-300 transition-colors p-2 bg-white/10 rounded-full" href="#"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect width="4" height="12" x="2" y="9"></rect><circle cx="4" cy="4" r="2"></circle></svg></a>
                    <a aria-label="GitHub" class="text-slate-300 hover:text-yellow-300 transition-colors p-2 bg-white/10 rounded-full" href="#"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"></path><path d="M9 18c-4.51 2-5-2-7-2"></path></svg></a>
                </div>
            </div>
        </div>
        <div class="border-t border-white/20 pt-10 text-center">
            <p class="text-sm text-slate-300">© 2025 CMS Full Form. <?php _e('Copyright © CMS Full Form.') ?></p>
        </div>
    </div>
</footer>
<?php assets_footer(); ?>

</body>
</html>
