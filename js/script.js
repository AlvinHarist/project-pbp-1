document.addEventListener('DOMContentLoaded', () => {

    // Select all "Add to cart" buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    // Add a click event listener to each button
    const loggedIn = document.body.dataset.loggedIn === '1';
    addToCartButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const bookCard = button.closest('.book-card');
            const title = bookCard.querySelector('h3') ? bookCard.querySelector('h3').textContent.trim() : '';

            if (!loggedIn) {
                alert('Silakan masuk dahulu untuk menambahkan barang ke keranjang.');
                window.location.href = 'login.php';
                return;
            }

            // Try to find a data-id on the card or image
            const bookId = bookCard.dataset.id || bookCard.querySelector('img')?.getAttribute('src')?.match(/([A-Za-z0-9_-]+)\.jpg$/)?.[1];

            if (!bookId) {
                alert('Gagal menambahkan ke keranjang: ID buku tidak diketahui.');
                return;
            }

            // create a small form and submit to keranjang.php?action=add
            const form = document.createElement('form');
            form.method = 'post';
            form.action = 'keranjang.php?action=add';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id_buku';
            idInput.value = bookId;
            form.appendChild(idInput);

            const qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = 'jumlah';
            qtyInput.value = 1;
            form.appendChild(qtyInput);

            document.body.appendChild(form);
            form.submit();
        });
    });

});

// --- Password Visibility Toggle for Sign Up Page ---
const togglePasswordIcons = document.querySelectorAll('.toggle-password');

if (togglePasswordIcons) {
    togglePasswordIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.previousElementSibling;
            
            // Toggle the input type between password and text
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Toggle the icon class
            this.classList.toggle('fa-eye-slash');
        });
    });
}