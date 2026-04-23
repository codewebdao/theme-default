<!-- common/input/user.php -->
<?php
// Check if required variables are set
$id = $id ?? 'user-select';
$name = $name ?? 'user';
$label = $label ?? 'Select User';
$required = $required ?? false;
$value = is_numeric($value) ? $value : null;
if (empty($value)){
    $value = \System\Libraries\Session::get('user_id');
}
$description = $description ?? '';
$error_message = $error_message ?? '';
?>

<div class="field w-full mb-6 field_user <?= $id ?>" style="<?= $visibility ? 'width:' . $width_value . $width_unit . ';' : 'display:none;' ?>">
    <?php if ($label): ?>
        <label for="<?= $id ?>" class="block mb-3 text-lg font-semibold ">
            <?= $label ?><?= $required ? '<span class="text-red-500 ml-1">*</span>' : '' ?>
        </label>
    <?php endif; ?>

    <!-- Search Input and Dropdown -->
    <div class="relative mb-4">
        <input
            type="text"
            id="search-<?= $id ?>"
            class="form-control border px-3 py-2 w-full rounded-md hover:outline-blue-400 focus:outline-blue-600"
            placeholder="Search users..."
            autocomplete="off"
        />
        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
            </svg>
        </div>

        <!-- Dropdown Options -->
        <div
            id="dropdown-<?= $id ?>"
            class="absolute z-20 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-60 overflow-y-auto hidden"
        >
            <?php if (empty($data)): ?>
                <div class="p-4 text-center text-gray-500">No users available</div>
            <?php else: ?>
                <?php foreach ($data as $user): ?>
                    <label class="flex items-center px-3 py-2 hover:bg-blue-50 cursor-pointer">
                        <input
                            type="radio"
                            name="selected-user"
                            class="form-radio !h-4 !w-4 text-blue-600"
                            value="<?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?>"
                            data-fullname="<?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?>"
                            <?= $value == $user['id'] ? 'checked' : '' ?>
                            onchange="updateSingleUserSelection('<?= $id ?>')"
                        />
                        <span class="ml-2 "><?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?></span>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Selected User Display -->
    <div id="selected-display-<?= $id ?>">
        <?php if ($value): ?>
            <span class="inline-flex items-center px-3 py-1 bg-primary-100 text-primary rounded-full text-sm">
                <?= htmlspecialchars($data[array_search($value, array_column($data, 'id'))]['fullname'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="ml-2 text-blue-500 hover:text-blue-700 focus:outline-none" 
                        onclick="clearUserSelection('<?= $id ?>')">
                    &times;
                </button>
            </span>
        <?php endif; ?>
    </div>

    <!-- Hidden Input -->
    <input type="hidden" id="<?= $id ?>" name="<?= $name ?>" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>

    <?php if ($description): ?>
        <p class="mt-3 text-sm text-gray-500"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <p class="mt-2 text-sm text-red-500"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initSingleUserSelect('<?= $id ?>');
});

function initSingleUserSelect(id) {
    const searchInput = document.getElementById('search-' + id);
    const dropdown = document.getElementById('dropdown-' + id);

    // Initialize selected item
    updateSingleUserSelection(id);

    // Toggle dropdown on search input click
    searchInput.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.remove('hidden');
    });

    // Handle search input
    searchInput.addEventListener('input', function() {
        handleUserSearch(id, this.value);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Handle Escape key
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdown.classList.add('hidden');
            searchInput.blur();
        }
    });
}

function handleUserSearch(id, query) {
    const dropdown = document.getElementById('dropdown-' + id);
    const options = dropdown.querySelectorAll('label');
    const lowerQuery = query.toLowerCase();

    options.forEach(option => {
        const text = option.textContent.toLowerCase();
        if (text.includes(lowerQuery)) {
            option.classList.remove('hidden');
        } else {
            option.classList.add('hidden');
        }
    });
}

function updateSingleUserSelection(id) {
    const dropdown = document.getElementById('dropdown-' + id);
    const radios = dropdown.querySelectorAll('input[type="radio"]');
    const hiddenInput = document.getElementById(id);
    const display = document.getElementById('selected-display-' + id);

    // Clear current selection
    display.innerHTML = '';

    // Update selection based on checked radio
    radios.forEach(radio => {
        if (radio.checked) {
            const fullname = radio.getAttribute('data-fullname');
            const value = radio.value;

            // Update hidden input
            hiddenInput.value = value;

            // Create tag display
            const tag = document.createElement('span');
            tag.className = 'inline-flex items-center px-3 py-1 bg-primary-100 text-primary rounded-full text-sm';
            tag.innerHTML = `${fullname}
                <button type="button" class="ml-2 text-blue-500 hover:text-blue-700 focus:outline-none" 
                        onclick="clearUserSelection('${id}')">&times;</button>`;
            display.appendChild(tag);

            // Hide dropdown
            dropdown.classList.add('hidden');
        }
    });
}

function clearUserSelection(id) {
    const dropdown = document.getElementById('dropdown-' + id);
    const radios = dropdown.querySelectorAll('input[type="radio"]');
    const hiddenInput = document.getElementById(id);
    const display = document.getElementById('selected-display-' + id);

    // Uncheck all radios
    radios.forEach(radio => radio.checked = false);

    // Clear hidden input and display
    hiddenInput.value = '';
    display.innerHTML = '';
}
</script>
