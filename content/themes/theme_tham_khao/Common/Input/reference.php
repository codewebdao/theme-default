<!-- common/input/refderence.php -->
<div class="floating-label px-1 relative field px-1 w-full mb-6 wrap-<?= $name ?> field_reference <?= $id ?>" style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>">
    <?php if ($label): ?>
        <label for="<?= $id ?>" class="block mb-2 font-medium text-sm leading-5 z-10 text-theme-bodycolor bg-white dark:text-themedark-bodycolor hover:text-primary-500 dark:hover:text-primary-500 dark:bg-themedark-cardbg">
            <?= $label ?><?= $required ? '<span class="text-red-500 ml-1">*</span>' : '' ?>
        </label>
    <?php endif; ?>
    <?php
    $posts = isset($data) ? (array)$data : [];
    $listPostSelect = isset($value) ? (array)$value : [];
    $listPostSelectStr = implode(',', $listPostSelect);
    ?>
    <!-- Search Input and Dropdown -->
    <div class="relative mb-4">
        <input
            type="text"
            id="search-<?= $id ?>"
            class="form-control border px-3 py-2 w-full rounded-md hover:outline-blue-400 focus:outline-blue-600"
            placeholder="Find posts...."
            autocomplete="off"
        />
        <!-- Search Icon -->
        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
            </svg>
        </div>
        <!-- Dropdown Options -->
        <div
            id="dropdown-<?= $id ?>"
            class="absolute z-20 p-2 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-60 overflow-y-auto hidden transition-all duration-200 ease-in-out">
            <?php if (empty($posts)): ?>
                <div class="p-4 text-center text-gray-500">
                    Not available
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post):
                    $post_id = htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8');
                    $post_title = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
                    $is_selected = in_array($post_id, $listPostSelect);
                ?>
                    <label class="flex gap-2 label-popup line-clamp-1 mb-2 font-medium text-sm leading-5 text-theme-bodycolor bg-white dark:text-themedark-bodycolor hover:text-primary-500 dark:hover:text-primary-500 dark:bg-themedark-cardbg">
                        <input
                            type="checkbox"
                            class="form-checkbox h-4 !w-4 text-primary focus:ring-blue-500"
                            value="<?= $post_id ?>"
                            data-title="<?= $post_title ?>"
                            <?= $is_selected ? 'checked' : '' ?>
                            onchange="updateSelection('<?= $id ?>')"
                        />
                        <span class=""><?= $post_title ?></span>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- Selected Items Display -->
    <div class="flex flex-wrap gap-2" id="selected-display-<?= $id ?>">
        <?php foreach ($posts as $post):
            $post_id = htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8');
            $post_title = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
            if (in_array($post_id, $listPostSelect)): ?>
                <span class="inline-flex items-center px-3 py-1 bg-primary-100 text-primary rounded-full text-sm">
                    <?= $post_title ?>
                    <button type="button" class="ml-2 text-blue-500 hover:text-blue-700 focus:outline-none" onclick="removeSelection('<?= $id ?>', '<?= $post_id ?>')">
                        &times;
                    </button>
                </span>
        <?php endif; endforeach; ?>
    </div>
    <!-- Hidden Select Element -->
    <select
        id="<?= $id ?>"
        name="<?= $name ?>[]" 
        multiple
        class="!hidden"
        <?= $required ? 'required' : '' ?>
    >
        <?php foreach ($posts as $post):
            $post_id = htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8');
            $post_title = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
            $selected = in_array($post_id, $listPostSelect) ? 'selected' : '';
        ?>
            <option value="<?= $post_id ?>" <?= $selected ?>><?= $post_title ?></option>
        <?php endforeach; ?>
    </select>
    <!-- Hidden Input for Selected Posts -->
    <input type="hidden" id="hidden-<?= $id ?>" name="<?= $name ?>_hidden" value="<?= htmlspecialchars($listPostSelectStr, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($description): ?>
        <p class="mt-3 text-sm text-gray-500"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php 
    if (!empty($error_message)) {
        if (is_array($error_message)) {
            foreach ($error_message as $error): ?>
                <p class="mt-2 text-sm text-red-500"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach;
        } elseif (is_string($error_message)) {
            echo '<p class="mt-2 text-sm text-red-500">' . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . '</p>';
        }
    }
    ?>
</div>

<script>
    function updateSelection(id) {
        const dropdown = document.getElementById('dropdown-' + id);
        const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]');
        const select = document.getElementById(id);
        const display = document.getElementById('selected-display-' + id);
        const hiddenInput = document.getElementById('hidden-' + id);
        
        select.innerHTML = '';
        display.innerHTML = '';
        let selectedValues = [];

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selectedValues.push(checkbox.value);
                const title = checkbox.getAttribute('data-title');
                
                // Update hidden select
                const option = document.createElement('option');
                option.value = checkbox.value;
                option.selected = true;
                option.text = title;
                select.appendChild(option);
                
                // Create tag display
                const tag = document.createElement('span');
                tag.className = 'inline-flex items-center px-3 py-1 bg-primary-100 text-primary rounded-full text-sm';
                tag.innerHTML = `${title}
                    <button type="button" class="ml-2 text-blue-500 hover:text-blue-700 focus:outline-none" onclick="removeSelection('${id}', '${checkbox.value}')">&times;</button>`;
                display.appendChild(tag);
            }
        });

        // Update hidden input with comma-separated values
        if (selectedValues.length > 0){
            hiddenInput.value = selectedValues.join(',');
        }
    }

    function removeSelection(id, value) {
        const dropdown = document.getElementById('dropdown-' + id);
        const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            if (checkbox.value === value) {
                checkbox.checked = false;
            }
        });
        updateSelection(id);
    }

    function handleSearch(id, query) {
        const dropdown = document.getElementById('dropdown-' + id);
        const options = dropdown.querySelectorAll('label');
        const lowerQuery = query.toLowerCase();
        let anyVisible = false;
        
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(lowerQuery)) {
                option.classList.remove('hidden');
                anyVisible = true;
            } else {
                option.classList.add('hidden');
            }
        });

        // Handle no results message
        const noResults = dropdown.querySelector('#no-results-' + id);
        if (!anyVisible && query.trim() !== '') {
            if (!noResults) {
                const noResultDiv = document.createElement('div');
                noResultDiv.id = 'no-results-' + id;
                noResultDiv.className = 'px-4 py-2 text-gray-500';
                noResultDiv.textContent = 'No posts found.';
                dropdown.appendChild(noResultDiv);
            }
        } else {
            if (noResults) {
                noResults.remove();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input[id^="search-"]');
        inputs.forEach(input => {
            const id = input.id.replace('search-', '');
            const dropdown = document.getElementById('dropdown-' + id);
            
            // Initialize selected items on page load
            updateSelection(id);

            // Search input event handlers
            // input.addEventListener('click', function(e) {
            //     e.stopPropagation();
            //     dropdown.classList.remove('hidden');
            //     input.focus();
            // });

            input.addEventListener('focus', function(e) {
                e.stopPropagation();
                dropdown.classList.remove('hidden');
                input.focus();
            });

            document.addEventListener('mousedown', function (e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            input.addEventListener('input', function() {
                handleSearch(id, this.value);
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            // Handle Esc key
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    dropdown.classList.add('hidden');
                    input.blur();
                }
            });
        });
    });
</script>