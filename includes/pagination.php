<?php
/**
 * Pagination Render Helper
 * Renders pagination HTML with consistent styling
 */

function renderPagination($page, $total_pages, $hidden_inputs = []) {
    if ($total_pages <= 1) {
        return '';
    }
    
    ob_start();
    ?>
    <form method="GET" action="" id="paginationForm" class="pagination-form">
        <?php foreach ($hidden_inputs as $name => $value): ?>
            <input type="hidden" name="<?php echo htmlspecialchars($name); ?>" value="<?php echo htmlspecialchars($value); ?>">
        <?php endforeach; ?>
        
        <div class="pagination">
            <!-- Prev -->
            <?php if ($page > 1): ?>
                <button type="button" onclick="changePage(<?php echo $page - 1; ?>)" class="pag-prev">← Prev</button>
            <?php else: ?>
                <span class="disabled pag-prev">← Prev</span>
            <?php endif; ?>

            <!-- Pages -->
            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);

            if ($page <= 3) {
                $end_page = min($total_pages, 5);
            }
            if ($page >= $total_pages - 2) {
                $start_page = max(1, $total_pages - 4);
            }

            if ($start_page > 1) {
                echo '<button type="button" onclick="changePage(1)" class="pag-num">1</button>';
                if ($start_page > 2) echo '<span class="disabled">…</span>';
            }

            for ($i = $start_page; $i <= $end_page; $i++) {
                if ($i === $page) {
                    echo '<div class="pag-input-wrapper active">';
                    echo '<input type="number" name="page" value="' . $i . '" min="1" max="' . $total_pages . '" class="pag-input" data-original="' . $i . '" oninput="handlePageChange()">';
                    echo '</div>';
                } else {
                    echo '<button type="button" onclick="changePage(' . $i . ')" class="pag-num">' . $i . '</button>';
                }
            }

            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) echo '<span class="disabled">…</span>';
                echo '<button type="button" onclick="changePage(' . $total_pages . ')" class="pag-num">' . $total_pages . '</button>';
            }
            ?>

            <!-- Next -->
            <?php if ($page < $total_pages): ?>
                <button type="button" onclick="changePage(<?php echo $page + 1; ?>)" class="pag-next">Next →</button>
            <?php else: ?>
                <span class="disabled pag-next">Next →</span>
            <?php endif; ?>
        </div>

        <button type="submit" id="applyBtn" class="pag-apply-btn" disabled>
            <svg viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Apply
        </button>
    </form>
    <?php
    return ob_get_clean();
}

function renderPaginationScript($card_selector = '.card') {
    ob_start();
    ?>
    <script>
    function changePage(pageNum) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('page', pageNum);
        window.location.href = '?' + urlParams.toString();
    }

    function handlePageChange() {
        const input = document.querySelector('.pag-input');
        const applyBtn = document.getElementById('applyBtn');
        if (!input || !applyBtn) return;
        
        const originalValue = parseInt(input.dataset.original);
        const currentValue = parseInt(input.value);

        if (currentValue !== originalValue && currentValue >= 1) {
            applyBtn.disabled = false;
        } else {
            applyBtn.disabled = true;
        }
    }

    (function() {
        const cards = document.querySelectorAll('<?php echo $card_selector; ?>');
        const form = document.getElementById('paginationForm');
        
        if (cards.length > 0 && form) {
            // Use last card if multiple cards exist
            const card = cards[cards.length - 1];
            
            card.addEventListener('mouseenter', () => {
                form.classList.add('card-hover');
            });

            card.addEventListener('mouseleave', () => {
                form.classList.remove('card-hover');
            });
        }

        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const input = document.querySelector('.pag-input');
                if (!input || !input.value || input.value < 1) {
                    return;
                }
                changePage(input.value);
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}
