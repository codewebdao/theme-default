    <!-- Visual & Flexible Content Management -->
    <section id="content-management" class="py-16 md:py-24 bg-slate-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">

                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                    <?php _e('Intuitive & Flexible') ?><span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                        <?php _e('Content Management') ?></span>
                </h2>
                <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                    <?php _e('Experience a modern, user-friendly, and powerful content management system with CMS Full Form.') ?>
                </p>
            </div>
            <div class="grid md:grid-cols-2 gap-12 items-center">

                <div class="order-2 md:order-1">
                    <div class="space-y-8">
                        <div class="flex items-start space-x-4">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-pen-line">
                                    <path d="M12 20h9"></path>
                                    <path
                                        d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-slate-800 mb-2">
                                    <?php _e('Modern Block Editor') ?>
                                </h3>
                                <p class="text-slate-600"><?php _e('Create rich content with an intuitive block-based editor. Easily add images, videos, tables, lists, and more without needing HTML knowledge.') ?></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-layers">
                                    <path
                                        d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z">
                                    </path>
                                    <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"></path>
                                    <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-slate-800 mb-2">
                                    <?php _e('Multi-Content Type Management') ?>
                                </h3>
                                <p class="text-slate-600"><?php _e('Create and manage unlimited content types: posts, products, projects, events, and more. Customize fields and relationships to suit your needs.') ?></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-file-text">
                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                    <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                    <path d="M10 9H8"></path>
                                    <path d="M16 13H8"></path>
                                    <path d="M16 17H8"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-slate-800 mb-2">
                                    <?php _e('Revision History & Version Control') ?>
                                </h3>
                                <p class="text-slate-600"><?php _e('revisions_desc') ?></p>
                            </div>
                        </div>
                        <a href="<?= base_url('download') ?>">

                            <button aria-label="<?php _e('Discover More') ?>"
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0  h-10 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white mt-4"> <?php _e('Discover More') ?><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-arrow-right ml-2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg></button>
                        </a>
                    </div>
                </div>

                <div class="order-1 md:order-2">
                    <div class="rounded-lg bg-card text-card-foreground overflow-hidden shadow-xl border-0 w-fit">
                        <div class="flex flex-col space-y-1.5 p-0">
                            <?= _img(
                                theme_assets('images/editor.webp'),
                                __('editor_image_caption'),
                                true,
                                'w-auto h-auto object-cover'
                            ) ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
