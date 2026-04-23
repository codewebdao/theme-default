<!DOCTYPE html>
<html lang="<?= lang_code() ?>" prefix="og: https://ogp.me/ns#">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $layout = $layout ?? '';
    // view_head() renders Head (title, meta, OG, Schema::get()), assets_head()
    view_head();
    ?>
</head>

<body>
    <!-- Header -->
    <header class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 h-20 flex items-center justify-between">
            <!-- Logo -->
            <a href="<?= base_url(); ?>" class="flex items-center space-x-2">
                <?= _img(
                    theme_assets('images/logo/Logo.webp'),
                    option('site_brand'),
                    false,
                    'w-28 object-cover'
                ) ?>
            </a>
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-1">
                <a href="<?= base_url('features'); ?>" class="<?= $layout == 'features' ? 'text-blue-600' : 'text-slate-600' ?> hover:text-blue-600 transition-colors px-3 py-2 rounded-md"><?php _e('Features'); ?></a>
                <a href="<?= base_url('libraries'); ?>" class="<?= in_array($layout, ['library', 'plugins', 'themes']) ? 'text-blue-600' : 'text-slate-600' ?> hover:text-blue-600 transition-colors px-3 py-2 rounded-md"><?php _e('Library'); ?></a>
                <a href="<?= base_url('blogs'); ?>" class="<?= in_array($layout, ['blogs', 'blog_detail']) ? 'text-blue-600' : 'text-slate-600' ?> hover:text-blue-600 transition-colors px-3 py-2 rounded-md"><?php _e('Blogs'); ?></a>
                <a href="<?= docs_url(); ?>" class="text-slate-600 hover:text-blue-600 transition-colors px-3 py-2 rounded-md"><?php _e('Documentation'); ?></a>
            </nav>

            <div class="flex items-center space-x-1">
                <!-- Search Dropdown -->
                <div class="relative">
                    <button id="searchDropdownToggle" aria-label="Search" class="flex items-center justify-center w-10 h-10 bg-white md:border border-slate-300 rounded-md text-slate-600 hover:text-blue-600 hover:border-blue-400 transition-colors duration-200 focus:outline-none focus:ring-0 focus:border-blue-500">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                    </button>
                    <div id="searchDropdownMenu" class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-lg shadow-xl z-50 invisible opacity-0 scale-95 origin-top-right transition-all duration-200">
                        <div class="p-4">
                            <form action="<?= base_url('search') ?>" method="GET" class="space-y-3">
                                <div class="relative">
                                    <input type="text" name="q" id="searchInput" placeholder="<?php _e('Search for themes, plugins, blogs...'); ?>" class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-0 focus:border-blue-500 transition-all duration-200" autocomplete="off" required>
                                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-slate-400" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                                </div>
                                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200 hover:shadow-md"><?php _e('search.search_button'); ?></button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Language Dropdown -->
                <div class="relative inline-block w-full max-w-xs">
                    <button id="languageDropdownToggle" class="flex items-center justify-between w-full bg-white md:border border-slate-300 rounded-md max-h-[40px] px-2 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer transition-colors hover:border-slate-400" style="max-height: 40px;">
                        <div class="flex items-center space-x-2">
                            <span class="text-lg"><?= lang_flag() ?></span>
                            <span class="hidden md:block text-sm font-medium"><?= lang_name() ?></span>
                        </div>
                        <svg id="languageDropdownArrow" class="w-4 h-4 text-slate-400 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div id="languageDropdownMenu" class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-md shadow-lg z-50 invisible opacity-0 scale-95 origin-top-right transition-all duration-100">
                        <div class="py-1">
                            <?php foreach (APP_LANGUAGES as $lang => $langData):
                                $isCurrent = (APP_LANG === $lang);
                                $url = lang_url($lang);
                            ?>
                                <a href="<?= $url ?>" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors duration-150 <?= $isCurrent ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-500' : '' ?>">
                                    <span class="text-lg"><?= lang_flag($lang) ?></span>
                                    <span class="font-medium <?= $isCurrent ? 'text-blue-700' : 'text-slate-700' ?>"><?= $langData['name'] ?></span>
                                    <?php if ($isCurrent): ?><svg class="w-4 h-4 ml-auto text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg><?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <a href="<?= base_url('download') ?>" class="hidden md:flex">
                    <button class="flex items-center justify-center bg-gradient-to-r from-blue-600 to-indigo-700 border border-blue-600 hover:from-blue-700 hover:to-indigo-800 text-white transition-all duration-300 shadow-md hover:shadow-lg px-4 py-2 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        <span class="whitespace-nowrap ml-2 hidden lg:inline"><?php _e('download'); ?></span>
                    </button>
                </a>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button id="mobileMenuToggle" aria-label="Menu" class="text-slate-700 p-2">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="2" y="4" width="16" height="2" rx="1" fill="currentColor" />
                            <rect x="2" y="9" width="16" height="2" rx="1" fill="currentColor" />
                            <rect x="2" y="14" width="16" height="2" rx="1" fill="currentColor" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="mobile-menu fixed inset-y-0 w-80 bg-white shadow-lg z-50 w-full hidden" id="mobileMenu">
            <div class="p-6 bg-white rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex justify-center items-center space-x-2">
                        <?= _img(theme_assets('images/logo/logo-icon.webp'), 'Logo CMS', true, 'mx-auto h-12') ?>
                        <span class="text-xl font-bold text-blue-600">CMS Full Form</span>
                    </div>
                    <button aria-label="Close" id="closeMobileMenu" class="text-slate-600 p-2">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
                    </button>
                </div>
                <nav class="space-y-2">
                    <a href="<?= base_url('features'); ?>" class="group flex items-center space-x-4 p-4 rounded-xl hover:bg-slate-50 transition-all duration-300"><?php _e('Features'); ?></a>
                    <a href="<?= base_url('libraries'); ?>" class="group flex items-center space-x-4 p-4 rounded-xl hover:bg-slate-50 transition-all duration-300"><?php _e('Library'); ?></a>
                    <a href="<?= base_url('blogs'); ?>" class="group flex items-center space-x-4 p-4 rounded-xl hover:bg-slate-50 transition-all duration-300"><?php _e('Blogs'); ?></a>
                    <div class="my-6"><div class="h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div></div>
                    <form action="<?= base_url('search') ?>" method="GET" id="mobileSearchForm">
                        <div class="relative mb-6">
                            <input type="text" name="q" id="mobileSearchInput" placeholder="<?php _e('Search for themes, plugins, blogs...'); ?>" class="w-full pl-12 pr-16 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-0 focus:border-blue-500 transition-all duration-200 bg-white" autocomplete="off" value="<?= S_GET('q', '') ?>" required>
                            <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-slate-400" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                            <button type="submit" id="mobileSearchButton" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium py-2 px-3 rounded-lg text-sm"><?php _e('search.search_button'); ?></button>
                        </div>
                    </form>
                    <a href="<?= base_url('download') ?>" class="block">
                        <button class="w-full flex items-center justify-center space-x-3 p-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-xl transition-all"><?php _e('download'); ?></button>
                    </a>
                </nav>
            </div>
        </div>
    </header>
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden md:hidden"></div>
    <main>
        <div class="bg-slate-50 text-slate-800 min-h-screen bg-background">
            <style>@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-5px)}75%{transform:translateX(5px)}}</style>
            <script>
            (function(){'use strict';
            function Dropdown(toggleId,menuId,arrowId){var t=document.getElementById(toggleId),m=document.getElementById(menuId),a=arrowId?document.getElementById(arrowId):null;if(!t||!m)return;t.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();m.classList.contains('invisible')?(m.classList.remove('opacity-0','invisible','scale-95'),m.classList.add('opacity-100','visible','scale-100'),a&&(a.style.transform='rotate(180deg)')):(m.classList.remove('opacity-100','visible','scale-100'),m.classList.add('opacity-0','invisible','scale-95'),a&&(a.style.transform='rotate(0deg)'));});
            document.addEventListener('click',function(e){if(!t.contains(e.target)&&!m.contains(e.target)){m.classList.remove('opacity-100','visible','scale-100');m.classList.add('opacity-0','invisible','scale-95');a&&(a.style.transform='rotate(0deg)');}});
            document.addEventListener('keydown',function(e){if(e.key==='Escape'){m.classList.remove('opacity-100','visible','scale-100');m.classList.add('opacity-0','invisible','scale-95');}});
            }
            function mobileMenu(){var btn=document.getElementById('mobileMenuToggle'),menu=document.getElementById('mobileMenu'),overlay=document.getElementById('mobileMenuOverlay'),close=document.getElementById('closeMobileMenu');
            if(!btn||!menu)return;function open(){menu.classList.remove('hidden');overlay&&overlay.classList.remove('hidden');}function shut(){menu.classList.add('hidden');overlay&&overlay.classList.add('hidden');}
            btn.addEventListener('click',open);close&&close.addEventListener('click',shut);overlay&&overlay.addEventListener('click',shut);
            }
            document.addEventListener('DOMContentLoaded',function(){Dropdown('languageDropdownToggle','languageDropdownMenu','languageDropdownArrow');Dropdown('searchDropdownToggle','searchDropdownMenu');mobileMenu();});
            })();
            </script>
