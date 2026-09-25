</div>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const button = document.querySelector('.menu-toggle');

    const isOpen = menu.classList.toggle('active');

    button.classList.toggle('active', isOpen);

    button.setAttribute('aria-expanded', isOpen);

    button.setAttribute(
        'aria-label',
        isOpen ? 'Close menu' : 'Open menu'
    );
}
</script>

</body>
</html>