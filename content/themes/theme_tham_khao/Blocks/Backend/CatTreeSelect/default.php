<?php if (!empty($data['terms'])): ?>
    <div class="w-full mb-4">
        <!-- Tiêu đề nhóm -->
        <h3 class="text-lg font-semibold mb-2"><?= $title ?></h3>

        <!-- Ô tìm kiếm -->
        <div class="relative mb-2">
            <input
                type="text"
                class="form-control border px-3 py-2 w-full rounded-md hover:outline-blue-400 focus:outline-blue-600"
                placeholder="Search categories..."
                onkeyup="filterTerms('categories')"
            >
            <button
                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                onclick="clearSearch('categories')"
                type="button"
            >
                ×
            </button>
        </div>

        <!-- Khu vực hiển thị danh sách term, có thể cuộn -->
        <div
            class="ml-2 max-h-[200px] overflow-y-auto scrollbar-thin
                   scrollbar-thumb-blue-500 scrollbar-track-gray-100"
            id="categories-container"
        >
            <?php
            // Lặp qua toàn bộ dữ liệu mảng $data['terms']
            foreach ($data['terms'] as $term) {
                // Chỉ xử lý những term top-level (cấp 0) 
                // nếu nó không có parent (hoặc parent rỗng)
                if (empty($term['parent'])) {

                    // Dùng stack để mô phỏng “đệ quy”
                    // Mỗi phần tử stack gồm: ['term' => $termObj, 'level' => $cấp]
                    $stack = [
                        [
                            'term'  => $term,
                            'level' => 0
                        ]
                    ];

                    // Khi stack còn phần tử thì tiếp tục duyệt
                    while (!empty($stack)) {
                        // Lấy ra 1 phần tử (pop cuối mảng)
                        $current = array_pop($stack);
                        $currentTerm  = $current['term'];
                        $currentLevel = $current['level'];
                        if(!empty($data['active']) && (in_array($currentTerm['id'], $data['active']) || in_array($currentTerm['id_main'], $data['active']))) {
                            $currentTerm['active'] = true;
                        }
                        // Tính khoảng thụt lề: mỗi level là 24px
                        $indent = $currentLevel * 24;

                        ?>
                        <!-- Render HTML cho term hiện tại -->
                        <div class="form-check mb-2" style="margin-left: <?= $indent ?>px">
                            <input
                                class="form-check-input text-blue-600"
                                type="checkbox"
                                name="terms[categories][]"
                                value="<?= htmlspecialchars($currentTerm['id']) ?>"
                                id="term-<?= htmlspecialchars($currentTerm['id']) ?>"
                                data-term-id="<?= htmlspecialchars($currentTerm['id']) ?>"
                                data-level="<?= $currentLevel ?>"
                                <?php if (!empty($currentTerm['active'])) echo 'checked'; ?>
                            >
                            <label class="form-check-label " for="term-<?= htmlspecialchars($currentTerm['id']) ?>">
                                <?= htmlspecialchars($currentTerm['name']) ?>
                            </label>
                        </div>
                        <?php

                        // Nếu term này có children, ta đẩy chúng vào stack
                        // để hiển thị tiếp
                        if (!empty($currentTerm['children'])) {
                            // Muốn children hiển thị đúng thứ tự, ta đảo mảng
                            // rồi push từng child vào stack
                            // (vì stack pop ra phần tử cuối, nên cần đảo ngược 
                            // để khi pop ra thứ tự hiển thị như mong muốn)
                            $reversedChildren = array_reverse($currentTerm['children']);
                            foreach ($reversedChildren as $child) {
                                $stack[] = [
                                    'term'  => $child,
                                    'level' => $currentLevel + 1
                                ];
                            }
                        }
                    } // end while stack
                } // end if empty parent
            } // end foreach data
            ?>
        </div>
    </div>
<?php endif; ?>
