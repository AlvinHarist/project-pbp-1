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