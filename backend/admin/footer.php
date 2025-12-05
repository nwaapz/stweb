        <footer class="text-center text-muted py-4">
            <small>© <?= date('Y') ?> استارتک - تمامی حقوق محفوظ است</small>
        </footer>
    </div><!-- /.main-content -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirm delete
        function confirmDelete(message) {
            return confirm(message || 'آیا از حذف این مورد اطمینان دارید؟');
        }
        
        // Preview uploaded image
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
