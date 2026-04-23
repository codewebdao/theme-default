<?php

/**
 * Plugin Detail Sidebar Section
 * Displays download card, quick links, and plugin info
 */

$plugin = $data['plugin'] ?? [];
$info = json_decode($plugin['info'], true);
?>

<!-- Enhanced Sidebar -->
<div class="lg:col-span-1">
    <div class="sticky top-20 space-y-8">

        <!-- Download Card -->
        <div class="bg-gradient-to-br from-purple-600 to-pink-700 rounded-2xl p-6 text-white shadow-xl">
            <div class="text-center mb-6">
                <div class="text-3xl font-bold mb-2">
                    <?= (empty($plugin['price'])) ? 'FREE' : '$' . number_format($plugin['price']) ?>
                </div>
                <p class="text-purple-100"><?= __('plugin_detail.sidebar.open_source_plugin') ?></p>
            </div>

            <a href="<?= $plugin['install_url'] ?? '#' ?>"
                class="w-full inline-flex items-center justify-center px-6 py-4 bg-white text-purple-600 font-semibold rounded-xl hover:bg-gray-50 transition-all duration-200 shadow-lg mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <?= __('plugin_detail.sidebar.install_now') ?>
            </a>

            <div class="text-center text-purple-100 text-sm">
                <p><?= __('plugin_detail.sidebar.safe_secure_install') ?></p>
            </div>
        </div>

        <!-- Plugin Information -->
        <?php if (!empty($info)): ?>
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
                <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?= __('plugin_detail.sidebar.plugin_information') ?>
                </h3>

                <ul class="space-y-4">
                    <?php foreach ($info as $key => $value): ?>
                        <li class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-600 font-medium"><?= $value['title'] ?? '' ?></span>
                            <span class="font-semibold text-slate-900"><?= $value['desc'] ?? '' ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Support Links -->
        <?php if (!empty($plugin['support_url']) || !empty($plugin['docs_url'])): ?>
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
                <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <?= __('plugin_detail.sidebar.get_support') ?>
                </h3>

                <div class="space-y-3">
                    <?php if (!empty($plugin['support_url'])): ?>
                        <a href="<?= $plugin['support_url'] ?>" target="_blank"
                            class="w-full inline-flex items-center justify-center px-4 py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 font-medium rounded-lg transition-colors border border-slate-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?= __('plugin_detail.sidebar.get_support') ?>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($plugin['docs_url'])): ?>
                        <a href="<?= $plugin['docs_url'] ?>" target="_blank"
                            class="w-full inline-flex items-center justify-center px-4 py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 font-medium rounded-lg transition-colors border border-slate-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <?= __('plugin_detail.sidebar.documentation') ?>
                        </a>
                    <?php endif; ?>

                    <!-- <a href="#" id="writeReviewBtn"
                    class="w-full inline-flex items-center justify-center px-4 py-3 bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium rounded-lg transition-colors border border-gray-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <?= __('plugin_detail.sidebar.write_review') ?>
                </a> -->
                </div>
            </div>
        <?php endif; ?>
        <!-- Share Plugin -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                </svg>
                <?= __('plugin_detail.sidebar.share_plugin') ?>
            </h3>

            <div class="grid grid-cols-2 gap-3">
                <button class="share-btn flex items-center justify-center px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition-colors border border-blue-200" data-platform="facebook">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                    <span class="text-xs">Facebook</span>
                </button>

                <button class="share-btn flex items-center justify-center px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-400 rounded-lg transition-colors border border-blue-200" data-platform="twitter">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                    </svg>
                    <span class="text-xs">Twitter</span>
                </button>

                <button class="share-btn flex items-center justify-center px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors border border-blue-200" data-platform="linkedin">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                    </svg>
                    <span class="text-xs">LinkedIn</span>
                </button>

                <button class="share-btn flex items-center justify-center px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-lg transition-colors border border-gray-200" data-platform="copy">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs">Copy</span>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    // Share functionality for social media platforms using jFast
    $(function() {
        const shareButtons = $('.share-btn');
        const pluginTitle = '<?= htmlspecialchars($plugin['title'] ?? 'Plugin', ENT_QUOTES) ?>';
        const pluginUrl = window.location.href;
        const pluginDescription = '<?= htmlspecialchars($plugin['description'] ?? 'Check out this amazing plugin!', ENT_QUOTES) ?>';

        shareButtons.on('click', function(e) {
            e.preventDefault();
            const platform = $(this).attr('data-platform');
            let shareUrl = '';

            switch (platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(pluginUrl)}`;
                    break;

                case 'twitter':
                    const twitterText = `${pluginTitle} - ${pluginDescription}`;
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(twitterText)}&url=${encodeURIComponent(pluginUrl)}`;
                    break;

                case 'linkedin':
                    shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(pluginUrl)}`;
                    break;

                case 'copy':
                    // Copy URL to clipboard with proper error handling
                    const $button = $(this);
                    const $span = $button.find('span');
                    const originalText = $span.text();

                    // Function to show success message
                    const showSuccessMessage = () => {
                        $span.text('Copied!');
                        $button.css({
                            'background-color': '#10b981',
                            'color': 'white',
                            'border-color': '#10b981'
                        });

                        setTimeout(() => {
                            $span.text(originalText);
                            $button.css({
                                'background-color': '',
                                'color': '',
                                'border-color': ''
                            });
                        }, 2000);
                    };

                    // Try modern clipboard API first
                    if (navigator && navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(pluginUrl)
                            .then(() => {
                                showSuccessMessage();
                            })
                            .catch(err => {
                                console.error('Modern clipboard API failed: ', err);
                                // Fallback to old method
                                copyToClipboardFallback();
                            });
                    } else {
                        // Use fallback method for older browsers
                        copyToClipboardFallback();
                    }

                    // Fallback function for older browsers
                    function copyToClipboardFallback() {
                        try {
                            const textArea = document.createElement('textarea');
                            textArea.value = pluginUrl;
                            textArea.style.position = 'fixed';
                            textArea.style.left = '-999999px';
                            textArea.style.top = '-999999px';
                            document.body.appendChild(textArea);
                            textArea.focus();
                            textArea.select();

                            const successful = document.execCommand('copy');
                            document.body.removeChild(textArea);

                            if (successful) {
                                showSuccessMessage();
                            } else {
                                console.error('Fallback copy failed');
                                alert('Copy failed. Please copy the URL manually: ' + pluginUrl);
                            }
                        } catch (err) {
                            console.error('Copy fallback error: ', err);
                            alert('Copy failed. Please copy the URL manually: ' + pluginUrl);
                        }
                    }
                    return;

                default:
                    return;
            }

            // Open share URL in new window
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400,scrollbars=yes,resizable=yes');
            }
        });
    });
</script>
