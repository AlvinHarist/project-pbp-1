document.addEventListener('DOMContentLoaded', () => {

    // Select all "Add to cart" buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    // Add a click event listener to each button
    addToCartButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            // Prevent the default button action
            event.preventDefault();

            // Get the book title from the parent card
            const bookCard = button.closest('.book-card');
            const bookTitle = bookCard.querySelector('h3').textContent;

            // Show a confirmation message (for demonstration)
            alert(`'${bookTitle}' has been added to your cart! (This is a demo)`);

            // In a real application, you would add the item to a cart object,
            // update the cart icon count, and maybe save it to localStorage.
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