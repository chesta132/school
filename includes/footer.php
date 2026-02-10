    </main>
    
    <script src="/assets/js/modal.js"></script>
    <script src="/assets/js/notification.js"></script>
    <script src="/assets/js/main.js"></script>
    <?php if (isset($additional_scripts)): ?>
        <?php foreach ($additional_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
