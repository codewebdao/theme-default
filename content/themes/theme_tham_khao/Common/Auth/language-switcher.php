<?php
// Language Switcher Component for Auth Pages
$current_lang = APP_LANG;
$available_languages = APP_LANGUAGES;
?>
<div class="relative" id="languageDropdown">
    <button
        type="button"
        class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        id="languageButton"
        onclick="toggleLanguageDropdown()">
        <!-- Current Language Flag -->
        <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
            <span class="text-lg"><?php echo lang_flag($current_lang); ?></span>
        </div>
        <span class="inline"><?php echo lang_name($current_lang); ?></span>
        <svg class="w-4 h-4 transition-transform duration-200" id="languageArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div
        id="languageDropdownMenu"
        class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-50 hidden opacity-0 transform scale-95 transition-all duration-200">
        <div class="py-1">
            <?php foreach ($available_languages as $lang_code => $lang_info): ?>
                <?php if ($lang_code !== $current_lang): ?>
                    <a
                        href="<?php echo lang_url($lang_code); ?>"
                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors duration-150">
                        <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                            <span class="text-lg"><?php echo lang_flag($lang_code); ?></span>
                        </div>
                        <span><?php echo $lang_info['name']; ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    function toggleLanguageDropdown() {
        const dropdown = document.getElementById('languageDropdownMenu');
        const arrow = document.getElementById('languageArrow');

        if (dropdown.classList.contains('hidden')) {
            // Show dropdown
            dropdown.classList.remove('hidden', 'opacity-0', 'scale-95');
            dropdown.classList.add('opacity-100', 'scale-100');
            arrow.style.transform = 'rotate(180deg)';
        } else {
            // Hide dropdown
            dropdown.classList.add('opacity-0', 'scale-95');
            dropdown.classList.remove('opacity-100', 'scale-100');
            arrow.style.transform = 'rotate(0deg)';

            setTimeout(() => {
                dropdown.classList.add('hidden');
            }, 200);
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('languageDropdown');
        const button = document.getElementById('languageButton');

        if (!dropdown.contains(event.target)) {
            const menu = document.getElementById('languageDropdownMenu');
            const arrow = document.getElementById('languageArrow');

            if (!menu.classList.contains('hidden')) {
                menu.classList.add('opacity-0', 'scale-95');
                menu.classList.remove('opacity-100', 'scale-100');
                arrow.style.transform = 'rotate(0deg)';

                setTimeout(() => {
                    menu.classList.add('hidden');
                }, 200);
            }
        }
    });
</script>