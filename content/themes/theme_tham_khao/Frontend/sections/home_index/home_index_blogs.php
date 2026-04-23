<?php

// Get data from extracted variables
$title = $title ?? '';
$blogs = $blogs ?? [];


?>

<!-- Blog - News -->
<section id="blog" class="py-16 md:py-24 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-slate-800"><?php _e('blog_section.title') ?></h2>
            <p class="mt-4 text-lg text-slate-600"><?php _e('blog_section.description') ?></p>

        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

            <?php foreach ($blogs as $blog): ?>

                <div
                    class="rounded-lg border bg-card text-card-foreground shadow-lg hover:shadow-xl transition-shadow duration-300 flex flex-col">
                    <div class="flex flex-col space-y-1.5 p-0">
                        <a href="<?= base_url('blogs/' . $blog['slug'], APP_LANG) ?>" target="_blank">
                            <?= _img(
                                theme_assets(get_image_full($blog['thumb_url'])),
                                $blog['title'],
                                true,
                                'rounded-t-lg w-full h-52 object-cover'
                            ) ?>

                        </a>
                    </div>
                    <div class="p-4 flex-grow">
                        <p class="text-sm text-slate-500 mb-1"><?= $blog['created_at'] ?></p>
                        <div
                            class="font-semibold tracking-tight text-xl text-slate-800 mb-2 line-clamp-2 hover:text-blue-600 transition-colors">
                            <a href="<?= base_url('blogs/' . $blog['slug'], APP_LANG) ?>" class="inline"><?= $blog['title'] ?></a>
                        </div>
                        <p class="text-slate-600 line-clamp-3"><?= $blog['seo_desc'] ?></p>
                    </div>
                    <div class="flex items-center p-4 pt-0"><a
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 underline-offset-4 hover:underline h-10 text-blue-600 p-0 hover:text-blue-700"
                            href="" target="_blank">Read more <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right ml-1">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg></a></div>
                </div>

            <?php endforeach; ?>
        </div>
        <div class="text-center mt-12">
            <a href="<?= base_url('blogs'); ?>">
                <button aria-label="<?php _e('button.view_all_blogs') ?>"
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-11 rounded-md px-8 text-blue-600 border-blue-600 hover:bg-blue-50"><?php _e('button.view_all_blogs') ?>
                </button></a>
        </div>
    </div>
</section>
