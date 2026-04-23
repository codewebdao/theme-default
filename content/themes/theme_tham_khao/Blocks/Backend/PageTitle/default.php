<!-- [ breadcrumb ] start -->
            <ul class="breadcrumb">
                <?php if(!empty($breadcrumb)) {
                    foreach($breadcrumb as $item): ?>
                        <li class="breadcrumb-item">
                            <a href="<?= $item['url'] ?? 'javascript:void(0)' ?>">
                                <?= $item['title'] ?? '' ?>
                            </a>
                        </li>
                    <?php endforeach;
                }?>
                <li class="breadcrumb-item" aria-current="page"><?= $title ?? '' ?></li>
            </ul>

<!-- [ breadcrumb ] end -->
